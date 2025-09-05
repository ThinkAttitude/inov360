<?php
// api/finance/profile_get.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$role   = $_SESSION['user']['role'] ?? '';
$selfId = (int)($_SESSION['user']['id'] ?? 0);

function can_read(string $role, int $selfId, int $targetId): bool {
    if (in_array($role, ['adminrh','estrela'], true)) return true;
    return $selfId === $targetId; // o próprio
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"MISSING_USER"]); exit; }
if (!can_read($role,$selfId,$userId)) { http_response_code(403); echo json_encode(["ok"=>false,"code"=>"FORBIDDEN"]); exit; }

require_once __DIR__ . '/../includes/db.php';
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
