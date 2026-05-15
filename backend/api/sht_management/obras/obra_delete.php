<?php
// api/grupo_inov/obras/obra_delete.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

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

/* ===== Input ===== */
function bad_request(string $m, int $code=422){ http_response_code($code); json_error('BAD_REQUEST', 200, ["message"=>$m]); }
$raw  = file_get_contents('php://input');
$body = $raw ? json_decode($raw, true) : [];
$idPk     = (int)($body['id'] ?? ($_GET['id'] ?? 0));
$idObra   = (int)($body['num_obra'] ?? $body['id_obra'] ?? ($_GET['num_obra'] ?? $_GET['id_obra'] ?? 0));

if ($idPk <= 0 && $idObra <= 0) {
    bad_request('Parâmetro "id" (PK) ou "num_obra"/"id_obra" é obrigatório.');
}

/* ===== Delete ===== */
try {
    // 1) Resolver a obra (por PK ou por número)
    if ($idPk > 0) {
        $chk = $pdo->prepare('SELECT id, id_obra FROM obra WHERE id = :id LIMIT 1');
        $chk->execute([':id' => $idPk]);
    } else {
        $chk = $pdo->prepare('SELECT id, id_obra FROM obra WHERE id_obra = :num LIMIT 1');
        $chk->execute([':num' => $idObra]);
    }
    $obra = $chk->fetch(PDO::FETCH_ASSOC);
    if (!$obra) {
        http_response_code(404);
        json_error('NOT_FOUND', 404, ["message"=>"Obra não encontrada."]);
    }

    // 2) Apagar por PK (garante ON DELETE CASCADE nas associações que apontam para obra.id)
    $del = $pdo->prepare('DELETE FROM obra WHERE id = :id LIMIT 1');
    $del->execute([':id' => (int)$obra['id']]);
    $deleted = $del->rowCount() > 0;

    echo json_encode([
        "ok" => true,
        "data" => [
            "deleted"  => $deleted,
            "id"       => (int)$obra['id'],
            "num_obra" => (int)$obra['id_obra']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    json_error('SERVER_ERROR', 500, ["detail"=>$e->getMessage()]);}
