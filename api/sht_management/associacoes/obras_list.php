<?php
// api/grupo_inov/associacoes/obras_list.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
if (($_SESSION['user']['role'] ?? '') !== 'colab') {
    http_response_code(403);
    echo json_encode(["ok"=>false,"code"=>"FORBIDDEN","message"=>"Apenas 'colab' pode listar obras."]);
    exit;
}

/* ===== DB ===== */
require_once __DIR__ . '/../../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function is_empty(?string $v): bool { return $v === null || trim($v) === ''; }
function expired(?string $dateYmd): bool {
    if (is_empty($dateYmd)) return true;
    $ts = strtotime($dateYmd); if ($ts === false) return true;
    return $ts < strtotime('today');
}

/* ===== Input ===== */
$q = trim((string)($_GET['q'] ?? ''));

try {
    /* ===== 1) Obras (agora com PK) ===== */
    if ($q !== '') {
        $sql = "SELECT id, id_obra, nome_obra, unidade
                  FROM obra
                 WHERE CAST(id_obra AS CHAR) LIKE :q
                    OR nome_obra LIKE :q
                    OR unidade   LIKE :q
                 ORDER BY id_obra DESC";
        $st = $pdo->prepare($sql);
        $st->execute([':q'=>"%{$q}%"]);
    } else {
        $st = $pdo->query("SELECT id, id_obra, nome_obra, unidade FROM obra ORDER BY id_obra DESC");
    }
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) { echo json_encode(["ok"=>true,"data"=>[]], JSON_UNESCAPED_UNICODE); exit; }

    // estruturas base: chaveamos por PK
    $obras = [];
    $obraPks  = [];
    $obraNums = [];
    $mapNumToPk = [];
    foreach ($rows as $r) {
        $pk  = (int)$r['id'];
        $num = (int)$r['id_obra'];
        $obraPks[]  = $pk;
        $obraNums[] = $num;
        $mapNumToPk[$num] = $pk;

        $obras[$pk] = [
            "id"         => $pk,       // novo PK
            "num_obra"   => $num,      // número legado
            "nome_obra"  => $r['nome_obra'],
            "unidade"    => $r['unidade'],
            "associados" => 0,
            "estado"     => "sem_associados",
            "empresas"   => [],
        ];
    }

    /* ===== 2) Associações + docs (suporta obra_pk e obra_id) ===== */
    $assoc = [];
    if ($obraPks) {
        $inPk  = implode(',', array_fill(0, count($obraPks),  '?'));
        $inNum = implode(',', array_fill(0, count($obraNums), '?'));

        $sqlA = "
            SELECT
              so.obra_pk, so.obra_id,
              s.user_id, s.nome_empresa, s.email_contacto, s.nif,
              de.sat_comp, de.sat_n_apolice, de.sat_validade, de.sat_mod_seguro,
              de.src_comp, de.src_n_apolice, de.src_validade,
              de.dec_ss, de.dec_finan
            FROM subs_obra so
            JOIN subempreiteiro s     ON s.user_id = so.sub_user_id
            LEFT JOIN doc_empresas de ON de.user_id = s.user_id
            WHERE (so.obra_pk IN ($inPk) OR so.obra_id IN ($inNum))
            ORDER BY COALESCE(so.obra_pk, so.obra_id), s.nome_empresa
        ";
        $stA = $pdo->prepare($sqlA);
        $stA->execute(array_merge($obraPks, $obraNums));
        $assoc = $stA->fetchAll(PDO::FETCH_ASSOC);
    }

    // Vamos recolher todos os sub_user_id envolvidos para verificar colaboradores
    $subIds = [];
    foreach ($assoc as $a) {
        $subIds[(int)$a['user_id']] = true;
    }
    $subIds = array_keys($subIds);

    /* ===== 2.1) Colaboradores incompletos por sub ===== */
    $colabIncBySub = [];
    if ($subIds) {
        $inSubs = implode(',', array_fill(0, count($subIds), '?'));
        $c = $pdo->prepare("
            SELECT sub_user_id, SUM(estado = 'incompleto') AS incompletos
              FROM doc_colaboradores
             WHERE sub_user_id IN ($inSubs)
             GROUP BY sub_user_id
        ");
        $c->execute($subIds);
        foreach ($c->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $colabIncBySub[(int)$r['sub_user_id']] = (int)$r['incompletos']; // >0 => tem incompletos
        }
    }

    // flags por obra
    $faltaEmpresaEmObra    = []; // pk => bool
    $colabIncompletoEmObra = []; // pk => bool

    foreach ($assoc as $a) {
        // escolhe a PK; se vier só o número, mapeia para a PK
        $pk = (int)($a['obra_pk'] ?? 0);
        if ($pk <= 0) {
            $num = (int)($a['obra_id'] ?? 0);
            $pk  = $mapNumToPk[$num] ?? 0;
            if ($pk <= 0) continue; // segurança
        }
        if (!isset($obras[$pk])) continue; // fora do filtro/página

        // calcula faltas de docs (empresa)
        $faltas = [];
        $sat_missing = is_empty($a['sat_comp']) || is_empty($a['sat_n_apolice']) || expired($a['sat_validade']) || is_empty($a['sat_mod_seguro']);
        if ($sat_missing) $faltas[] = 'Seguro AT';
        $src_missing = is_empty($a['src_comp']) || is_empty($a['src_n_apolice']) || expired($a['src_validade']);
        if ($src_missing) $faltas[] = 'Seguro RC';
        if (is_empty($a['dec_ss']))    $faltas[] = 'Declaração SS';
        if (is_empty($a['dec_finan'])) $faltas[] = 'Declaração Finanças';

        $obras[$pk]["empresas"][] = [
            "user_id"        => (int)$a['user_id'],
            "nome_empresa"   => $a['nome_empresa'],
            "email_contacto" => $a['email_contacto'],
            "nif"            => $a['nif'],
            "em_falta"       => $faltas,
            "status"         => empty($faltas) ? "ok" : "empresa_em_falta",
        ];
        $obras[$pk]["associados"]++;

        if (!empty($faltas)) $faltaEmpresaEmObra[$pk] = true;

        // marca se este sub tem colaboradores incompletos
        $sid = (int)$a['user_id'];
        if (!empty($colabIncBySub[$sid])) {
            $colabIncompletoEmObra[$pk] = true;
        }
    }

    // estado da obra com a nova regra
    foreach ($obras as $pk => &$o) {
        if ($o["associados"] === 0) {
            $o["estado"] = "sem_associados";
        } elseif (!empty($faltaEmpresaEmObra[$pk])) {
            $o["estado"] = "empresa_em_falta";
        } elseif (!empty($colabIncompletoEmObra[$pk])) {
            // só cai aqui se todas as empresas estavam OK
            $o["estado"] = "doc_colabs_incompleta";
        } else {
            $o["estado"] = "ok";
        }
    }
    unset($o);

    echo json_encode(["ok"=>true, "data"=>array_values($obras)], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"SERVER_ERROR","detail"=>$e->getMessage()]);
}
