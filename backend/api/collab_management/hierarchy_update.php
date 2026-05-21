<?php
// api/hierarchy_update.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(1, $perms, true)) {
    http_response_code(403);
    json_error('FORBIDDEN_PERMISSION', 403);
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';

function read_json_or_post(): array {
    $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ctype, 'application/json') !== false) {
        $d = json_decode(file_get_contents('php://input'), true);
        return is_array($d) ? $d : [];
    }
    return $_POST;
}
function to_int_array($v): array {
    if (is_null($v)) return [];
    if (!is_array($v)) $v = [$v];
    $v = array_map('intval', $v);
    $v = array_filter($v, fn($x)=>$x>0);
    return array_values(array_unique($v));
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $in = read_json_or_post();

    $userId       = (int)($in['user_id'] ?? 0);
    $responsaveis = to_int_array($in['responsaveis'] ?? ($in['responsaveis[]'] ?? null));
    $subs         = to_int_array($in['subs'] ?? ($in['subs[]'] ?? ($in['sub'] ?? ($in['sub[]'] ?? null))));

    if ($userId <= 0) {
        http_response_code(400);
        json_error('USER_ID_REQUIRED');
    }
    if (in_array($userId, $responsaveis, true) || in_array($userId, $subs, true)) {
        http_response_code(400);
        json_error('SELF_REFERENCE');
    }
    if (array_intersect($responsaveis, $subs)) {
        http_response_code(400);
        json_error('RESP_SUB_CONFLICT', 409);
    }

    $idsToCheck = array_values(array_unique(array_merge([$userId], $responsaveis, $subs)));
    $ph = implode(',', array_fill(0, count($idsToCheck), '?'));
    $q = $pdo->prepare("SELECT id FROM `user` WHERE id IN ($ph)");
    $q->execute($idsToCheck);
    $found = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
    $missing = array_values(array_diff($idsToCheck, $found));
    if ($missing) {
        http_response_code(400);
        json_error('USER_NOT_FOUND', 404, ['missing'=>$missing]);
    }

    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM `colaborador_responsaveis` WHERE colaborador_id = ?")
        ->execute([$userId]);

    if ($responsaveis) {
        $insResp = $pdo->prepare("
            INSERT IGNORE INTO `colaborador_responsaveis` (colaborador_id, responsavel_id, created_by)
            VALUES (?, ?, ?)
        ");
        $createdBy = (int)($_SESSION['user']['id'] ?? 0);
        foreach ($responsaveis as $rid) {
            if ($rid === $userId) continue;
            $insResp->execute([$userId, $rid, $createdBy]);
        }
    }

    $pdo->prepare("DELETE FROM `colaborador_responsaveis` WHERE responsavel_id = ?")
        ->execute([$userId]);

    if ($subs) {
        $insSub = $pdo->prepare("
            INSERT IGNORE INTO `colaborador_responsaveis` (colaborador_id, responsavel_id, created_by)
            VALUES (?, ?, ?)
        ");
        $createdBy = (int)($_SESSION['user']['id'] ?? 0);
        foreach ($subs as $sid) {
            if ($sid === $userId) continue;
            $insSub->execute([$sid, $userId, $createdBy]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'ok'=>true,
        'updated'=>[
            'user_id'=>$userId,
            'responsaveis'=>$responsaveis,
            'subs'=>$subs
        ]
    ]);
    exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    json_error('SERVER_ERROR', 500, ['message' => $e->getMessage()]);
}
