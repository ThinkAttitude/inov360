<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
$user = $_SESSION['user'];

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(8, $perms, true)) { // record_managment
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN_PERMISSION']); exit;
}

/* ===== DB ===== */
require_once __DIR__ . '/../../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function is_empty(?string $v): bool { return $v === null || trim($v) === ''; }
function expired(?string $dateYmd): bool {
    if (is_empty($dateYmd)) return true;
    $ts = strtotime($dateYmd);
    if ($ts === false) return true;
    // expira no fim do dia anterior
    return $ts < strtotime('today');
}

/* ===== Query + Resposta ===== */
try {
    // 1) Subempreiteiros
    $sql = "SELECT user_id, nome_empresa, email_contacto
              FROM subempreiteiro
             ORDER BY nome_empresa ASC";
    $subs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if (!$subs) {
        echo json_encode(["ok"=>true, "data"=>[]], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2) Documentos por user_id (doc_empresas)
    $ids = array_map('intval', array_column($subs, 'user_id'));
    $in  = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT user_id, atividade, num_alvara, niss_alvara,
               sat_comp, sat_n_apolice, sat_validade, sat_mod_seguro,
               src_comp, src_n_apolice, src_validade,
               dec_ss, dec_finan
          FROM sub_doc_empresas
         WHERE user_id IN ($in)
    ");
    $stmt->execute($ids);
    $docs = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $docs[(int)$row['user_id']] = $row;
    }

    // 3) Colaboradores incompletos por sub_user_id (doc_colaboradores)
    $stmt = $pdo->prepare("
        SELECT sub_user_id, SUM(estado = 'incompleto') AS incompletos
          FROM sub_doc_colaboradores
         WHERE sub_user_id IN ($in)
         GROUP BY sub_user_id
    ");
    $stmt->execute($ids);
    $colabIncomp = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $colabIncomp[(int)$r['sub_user_id']] = (int)$r['incompletos']; // >0 => tem incompletos
    }

    // 4) Montar saída
    $out = [];
    foreach ($subs as $s) {
        $uid = (int)$s['user_id'];
        $d   = $docs[$uid] ?? null;
        $faltas = [];

        // Seguro AT
        $sat_missing = !$d
            || is_empty($d['sat_comp'])
            || is_empty($d['sat_n_apolice'])
            || expired($d['sat_validade'])
            || is_empty($d['sat_mod_seguro']);
        if ($sat_missing) $faltas[] = 'Seguro AT';

        // Seguro RC
        $src_missing = !$d
            || is_empty($d['src_comp'])
            || is_empty($d['src_n_apolice'])
            || expired($d['src_validade']);
        if ($src_missing) $faltas[] = 'Seguro RC';

        // Declarações
        if (!$d || is_empty($d['dec_ss']))    $faltas[] = 'Declaração SS';
        if (!$d || is_empty($d['dec_finan'])) $faltas[] = 'Declaração Finanças';

        // Estado base (empresa)
        $status = empty($faltas) ? "completo" : "em_falta";

        // Se a empresa está completa mas há colaboradores incompletos,
        // muda para "doc_colabs_incompleta"
        if ($status === "completo" && !empty($colabIncomp[$uid])) {
            $status = "doc_colabs_incompleta";
        }

        $out[] = [
            "user_id"        => $uid,
            "nome_empresa"   => $s['nome_empresa'],
            "email_contacto" => $s['email_contacto'],
            "em_falta"       => $faltas,
            "status"         => $status,
        ];
    }

    echo json_encode(["ok"=>true, "data"=>$out], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok"=>false, "code"=>"SERVER_ERROR", "detail"=>$e->getMessage()]);
}
