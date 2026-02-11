<?php
// api/finance/profile_get.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* --------- auth --------- */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']);
    exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(7, $perms, true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'Do not have permission']);
    exit;
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'MISSING_USER']);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$st = $pdo->prepare("SELECT * FROM finance_profiles WHERE user_id=:u LIMIT 1");
$st->execute([':u'=>$userId]);
$row = $st->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "ok"    => true,
    "user_id" => $userId,
    "exists"  => (bool)$row,
    "data"    => $row ?: (object)[]
]);
