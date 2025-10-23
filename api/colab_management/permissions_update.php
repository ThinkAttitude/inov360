<?php
// api/permissions_update.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'code'=>'UNAUTHENTICATED']); exit;
}
$myPerms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($myPerms) || !in_array(1, $myPerms, true)) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'code'=>'FORBIDDEN_PERMISSION']); exit;
}

require_once __DIR__ . '/../includes/db.php';

function read_input(): array {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $d = json_decode(file_get_contents('php://input'), true);
        return is_array($d) ? $d : [];
    }
    return $_POST;
}
function to_int_array($v): array {
    if ($v === null) return [];
    if (!is_array($v)) $v = [$v];
    $v = array_map('intval', $v);
    $v = array_filter($v, fn($x)=>$x>0);
    return array_values(array_unique($v));
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $in         = read_input();
    $userId     = (int)($in['user_id'] ?? 0);
    $permissionIds = to_int_array($in['permissions'] ?? ($in['permissions[]'] ?? null));

    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'code'=>'USER_ID_REQUIRED']); exit;
    }

    // user existe?
    $q = $pdo->prepare("SELECT id FROM `user` WHERE id=? LIMIT 1");
    $q->execute([$userId]);
    if (!$q->fetchColumn()) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'code'=>'USER_NOT_FOUND']); exit;
    }

    // validar permissões (se vier lista vazia, apagamos todas)
    if (!empty($permissionIds)) {
        $ph = implode(',', array_fill(0, count($permissionIds), '?'));
        $q = $pdo->prepare("SELECT id FROM `permission` WHERE id IN ($ph)");
        $q->execute($permissionIds);
        $found = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
        $missing = array_values(array_diff($permissionIds, $found));
        if ($missing) {
            http_response_code(400);
            echo json_encode(['ok'=>false,'code'=>'PERMISSION_NOT_FOUND','missing'=>$missing]); exit;
        }
    }

    // overwrite total das permissões do utilizador
    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM `inov360`.`user_permission` WHERE user_id=?")->execute([$userId]);

    if (!empty($permissionIds)) {
        $ins = $pdo->prepare("
            INSERT INTO `inov360`.`user_permission` (user_id, permission_id)
            VALUES (?, ?)
        ");
        foreach ($permissionIds as $pid) {
            $ins->execute([$userId, $pid]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'ok'=>true,
        'user_id'=>$userId,
        'permissions'=>$permissionIds
    ]);
    exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok'=>false,'code'=>'SERVER_ERROR']); exit;
}
