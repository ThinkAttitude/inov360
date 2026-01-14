<?php
// api/grupo_inov/obras/obra_list.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* Auth */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(8, $perms, true)) { // sht_management
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN_PERMISSION']); exit;
}

/* DB */
require_once __DIR__ . '/../../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Helpers */
function is_empty(?string $v): bool { return $v === null || trim($v) === ''; }
function expired(?string $dateYmd): bool {
    if (is_empty($dateYmd)) return true;
    $ts = strtotime($dateYmd); if ($ts === false) return true;
    return $ts < strtotime('today');
}

/* Input */
$q = trim((string)($_GET['q'] ?? ''));

try {
    /* 1) Obras (com PK e número) */
    if ($q !== '') {
        $sql = "SELECT id, id_obra, nome_obra, `do`, encarregado, unidade
                  FROM obra
                 WHERE CAST(id_obra AS CHAR) LIKE :q
                    OR nome_obra LIKE :q
                    OR unidade   LIKE :q
                 ORDER BY id_obra DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':q' => "%{$q}%"]);
    } else {
        $stmt = $pdo->query("SELECT id, id_obra, nome_obra, `do`, encarregado, unidade FROM obra ORDER BY id_obra DESC");
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    if (!$rows) { echo json_encode(["ok"=>true, "data"=>[]], JSON_UNESCAPED_UNICODE); exit; }

    // Base de agregação por PK
    $obraPks  = [];
    $obraNums = [];
    $mapNumToPk = [];
    foreach ($rows as $r) {
        $pk  = (int)$r['id'];
        $num = (int)$r['id_obra'];
        $obraPks[]  = $pk;
        $obraNums[] = $num;
        $mapNumToPk[$num] = $pk;
    }

    /* 2) Associações (obra_pk preferido; obra_id como fallback durante transição) */
    $assoc = [];
    if ($obraPks) {
        $inPk  = implode(',', array_fill(0, count($obraPks),  '?'));
        $inNum = implode(',', array_fill(0, count($obraNums), '?'));
        $as = $pdo->prepare("
            SELECT obra_pk, obra_id, sub_user_id
              FROM subs_obra
             WHERE (obra_pk IN ($inPk) OR obra_id IN ($inNum))
        ");
        $as->execute(array_merge($obraPks, $obraNums));
        $assoc = $as->fetchAll(PDO::FETCH_ASSOC);
    }

    // Map pk -> lista de sub_user_id
    $obraToSubs = [];
    $allSubIds = [];
    foreach ($assoc as $a) {
        $pk = (int)($a['obra_pk'] ?? 0);
        if ($pk <= 0) {
            $num = (int)($a['obra_id'] ?? 0);
            $pk  = $mapNumToPk[$num] ?? 0;
        }
        if ($pk <= 0) continue;
        $sid = (int)$a['sub_user_id'];
        $obraToSubs[$pk][] = $sid;
        $allSubIds[$sid] = true;
    }
    $subIds = array_keys($allSubIds);

    /* 3) Documentos das empresas relevantes (doc_empresas) */
    $docsBySub = [];
    if ($subIds) {
        $inSubs = implode(',', array_fill(0, count($subIds), '?'));
        $d = $pdo->prepare("
            SELECT user_id, sat_comp, sat_n_apolice, sat_validade, sat_mod_seguro,
                   src_comp, src_n_apolice, src_validade, dec_ss, dec_finan
              FROM doc_empresas
             WHERE user_id IN ($inSubs)
        ");
        $d->execute($subIds);
        foreach ($d->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $docsBySub[(int)$r['user_id']] = $r;
        }
    }

    /* 3.1) Colaboradores incompletos por sub (doc_colaboradores) */
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

    /* 4) Função para avaliar se sub tem docs de empresa ok */
    $subOk = function(int $uid) use ($docsBySub): bool {
        $d = $docsBySub[$uid] ?? null;
        if (!$d) return false;
        $sat_missing = is_empty($d['sat_comp']) || is_empty($d['sat_n_apolice']) || expired($d['sat_validade']) || is_empty($d['sat_mod_seguro']);
        $src_missing = is_empty($d['src_comp']) || is_empty($d['src_n_apolice']) || expired($d['src_validade']);
        $dec_missing = is_empty($d['dec_ss']) || is_empty($d['dec_finan']);
        return !$sat_missing && !$src_missing && !$dec_missing;
    };

    /* 5) Calcular estado por obra */
    $data = [];
    foreach ($rows as $r) {
        $pk    = (int)$r['id'];
        $subs  = $obraToSubs[$pk] ?? [];
        $temAssociados = count($subs) > 0;

        // Empresa completa = todos os subs associados têm docs OK
        $todosOk = $temAssociados;
        if ($temAssociados) {
            foreach ($subs as $sid) {
                if (!$subOk($sid)) { $todosOk = false; break; }
            }
        }

        $estado = ($temAssociados && $todosOk) ? 'completo' : 'em_falta';

        // Se a obra estaria "completo", mas existe colaborador incompleto em algum sub -> "doc_colabs_incompleta"
        if ($estado === 'completo') {
            foreach ($subs as $sid) {
                if (!empty($colabIncBySub[$sid])) { // há >=1 colaborador incompleto neste sub
                    $estado = 'doc_colabs_incompleta';
                    break;
                }
            }
        }

        $data[] = [
            "id"          => $pk,
            "num_obra"    => (int)$r['id_obra'],
            "nome_obra"   => $r['nome_obra'],
            "diretor"     => $r['do'],
            "encarregado" => $r['encarregado'],
            "unidade"     => $r['unidade'],
            "estado"      => $estado,
        ];
    }

    echo json_encode(["ok"=>true, "data"=>$data], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"SERVER_ERROR","detail"=>$e->getMessage()]);
}
