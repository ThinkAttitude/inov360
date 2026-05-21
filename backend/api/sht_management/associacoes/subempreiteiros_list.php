<?php
// api/grupo_inov/associacoes/subempreiteiros_list.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$user = $_SESSION['user'];

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(8, $perms, true)) { // sht_management
    http_response_code(403);
    json_error('FORBIDDEN_PERMISSION', 403);
}

/* ===== DB ===== */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function is_empty(?string $v): bool { return $v === null || trim($v) === ''; }
function expired(?string $dateYmd): bool {
    if (is_empty($dateYmd)) return true;
    $ts = strtotime($dateYmd);
    if ($ts === false) return true;
    return $ts < strtotime('today'); // expira no fim do dia anterior
}

/* ===== Input ===== */
$q = trim((string)($_GET['q'] ?? ''));

/* ===== Query ===== */
try {
    if ($q !== '') {
        $sql = "
            SELECT
                s.user_id,
                s.nome_empresa,
                s.email_contacto,
                s.nif,
                de.sat_comp, de.sat_n_apolice, de.sat_validade, de.sat_mod_seguro,
                de.src_comp, de.src_n_apolice, de.src_validade,
                de.dec_ss, de.dec_finan
            FROM subempreiteiro s
            LEFT JOIN sub_doc_empresas de ON de.user_id = s.user_id
            WHERE s.nome_empresa   LIKE :q
               OR s.nif            LIKE :q
               OR s.email_contacto LIKE :q
            ORDER BY s.nome_empresa ASC";
        $st = $pdo->prepare($sql);
        $st->execute([':q' => "%{$q}%"]);
    } else {
        $sql = "
            SELECT
                s.user_id,
                s.nome_empresa,
                s.email_contacto,
                s.nif,
                de.sat_comp, de.sat_n_apolice, de.sat_validade, de.sat_mod_seguro,
                de.src_comp, de.src_n_apolice, de.src_validade,
                de.dec_ss, de.dec_finan
            FROM subempreiteiro s
            LEFT JOIN sub_doc_empresas de ON de.user_id = s.user_id
            ORDER BY s.nome_empresa ASC";
        $st = $pdo->query($sql);
    }

    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

    if (!$rows) {
        echo json_encode(["ok"=>true, "data"=>[]], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* === Buscar num único passo se há colaboradores 'incompleto' por sub === */
    $userIds = array_map('intval', array_column($rows, 'user_id'));
    $colabIncBySub = [];
    if ($userIds) {
        $in = implode(',', array_fill(0, count($userIds), '?'));
        $c = $pdo->prepare("
            SELECT sub_user_id, SUM(estado = 'incompleto') AS incompletos
              FROM sub_doc_colaboradores
             WHERE sub_user_id IN ($in)
             GROUP BY sub_user_id
        ");
        $c->execute($userIds);
        foreach ($c->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $colabIncBySub[(int)$r['sub_user_id']] = (int)$r['incompletos']; // >0 => tem incompletos
        }
    }

    /* === Montar resposta === */
    $data = [];
    foreach ($rows as $r) {
        // Avaliar documentação da EMPRESA
        $sat_missing = is_empty($r['sat_comp']) || is_empty($r['sat_n_apolice']) || expired($r['sat_validade']) || is_empty($r['sat_mod_seguro']);
        $src_missing = is_empty($r['src_comp']) || is_empty($r['src_n_apolice']) || expired($r['src_validade']);
        $ss_missing  = is_empty($r['dec_ss']);
        $fin_missing = is_empty($r['dec_finan']);

        $estado = ($sat_missing || $src_missing || $ss_missing || $fin_missing) ? 'em_falta' : 'ok';

        // Se estaria "ok" mas existe colaborador incompleto -> "doc_colabs_incompleta"
        $uid = (int)$r['user_id'];
        if ($estado === 'ok' && !empty($colabIncBySub[$uid])) {
            $estado = 'doc_colabs_incompleta';
        }

        $data[] = [
            "user_id"      => $uid,
            "nome_empresa" => $r['nome_empresa'],
            "email"        => $r['email_contacto'],
            "nif"          => $r['nif'],
            "estado"       => $estado
        ];
    }

    echo json_encode(["ok"=>true, "data"=>$data], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    json_error('SERVER_ERROR', 500, ["detail"=>$e->getMessage()]);}
