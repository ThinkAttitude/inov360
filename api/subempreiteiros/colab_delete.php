<?php
// api/subempreiteiros/colab_delete.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
$user   = $_SESSION['user'];
$userId = (int)($user['id'] ?? 0);

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Input ===== */
function bad_request(string $m, int $code=400){ http_response_code($code); echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST","message"=>$m]); exit; }

$colabId = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (is_array($body)) $colabId = (int)($body['id'] ?? 0);
    } else {
        $colabId = (int)($_POST['id'] ?? 0);
    }
} else {
    // opcionalmente aceitar DELETE/GET ?id=
    $colabId = (int)($_GET['id'] ?? 0);
}
if ($colabId <= 0) bad_request('Parâmetro "id" é obrigatório.', 422);

/* ===== Buscar registo e validar pertença ===== */
$st = $pdo->prepare("SELECT *
                     FROM sub_doc_colaboradores
                     WHERE id = :id
                     LIMIT 1");
$st->execute([':id'=>$colabId]);
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    echo json_encode(["ok"=>false,"code"=>"NOT_FOUND","message"=>"Colaborador não encontrado."]);
    exit;
}
if ((int)$row['sub_user_id'] !== $userId) {
    http_response_code(403);
    echo json_encode(["ok"=>false,"code"=>"FORBIDDEN","message"=>"Sem acesso a este colaborador."]);
    exit;
}

/* ===== Guardar paths para tentar apagar depois ===== */
$fileFields = [
    'doc_id_path','cv_ss_path','fam_path',
    'entrega_epi_path','form_esp_path','ficha_trab_path'
];
$paths = [];
foreach ($fileFields as $f) {
    if (!empty($row[$f])) $paths[] = $row[$f];
}

/* ===== Remover da BD ===== */
try {
    $pdo->beginTransaction();

    $del = $pdo->prepare("DELETE FROM sub_doc_colaboradores
                          WHERE id = :id AND sub_user_id = :uid
                          LIMIT 1");
    $del->execute([':id'=>$colabId, ':uid'=>$userId]);

    if ($del->rowCount() !== 1) {
        // algo falhou; abortar
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(["ok"=>false,"code"=>"CONFLICT","message"=>"Não foi possível remover o colaborador."]);
        exit;
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"SERVER_ERROR","message"=>$e->getMessage()]);
    exit;
}

/* ===== Tentar apagar ficheiros (best-effort) ===== */
$deleted = [];
$failed  = [];

foreach ($paths as $rel) {
    // Segurança: só apaga dentro de uploads/colaboradores/u{userId}
    $base = realpath(__DIR__ . '/../../uploads/sub_colaboradores');
    $full = realpath(__DIR__ . '/../../' . $rel);
    if ($full === false) { $failed[] = $rel; continue; }
    if ($base === false || strpos($full, $base . DIRECTORY_SEPARATOR . 'u'.$userId) !== 0) {
        // fora da pasta do utilizador -> não apaga
        $failed[] = $rel; continue;
    }
    if (is_file($full)) {
        @unlink($full) ? $deleted[] = $rel : $failed[] = $rel;
    } else {
        $failed[] = $rel;
    }
}

/* ===== Resposta ===== */
echo json_encode([
    "ok" => true,
    "id" => (int)$colabId,
    "files" => [
        "deleted" => $deleted,
        "failed"  => $failed
    ]
], JSON_UNESCAPED_UNICODE);
