<?php
// api/collabs_list.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
/*$myPerms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($myPerms) || !in_array(1, $myPerms, true)) { // exige permissão 1
    http_response_code(403);
    json_error('FORBIDDEN_PERMISSION', 403);
}*/

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // filtros opcionais
    $q         = trim((string)($_GET['q'] ?? '')); // busca por nome/email
    $companyId = isset($_GET['company_id']) && $_GET['company_id'] !== '' ? (int)$_GET['company_id'] : null;
    $limit     = max(1, min(500, (int)($_GET['limit'] ?? 200)));
    $page      = max(1, (int)($_GET['page'] ?? 1));
    $offset    = ($page - 1) * $limit;

    $where = [];
    $params = [];
    if ($q !== '') {
        $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
        $like = '%'.$q.'%'; $params[] = $like; $params[] = $like;
    }
    if (!is_null($companyId)) { $where[] = 'u.company_id = ?'; $params[] = $companyId; }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // lista base de users
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email
        FROM `user` u
        $whereSql
        ORDER BY u.name ASC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$users) { echo json_encode(['ok'=>true, 'total'=>0, 'items'=>[]]); exit; }

    // total para paginação
    $stmtTot = $pdo->prepare("SELECT COUNT(*) FROM `user` u $whereSql");
    $stmtTot->execute($params);
    $total = (int)$stmtTot->fetchColumn();

    // mapear permissões por utilizador
    $ids = array_map(fn($r)=>(int)$r['id'], $users);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $permStmt = $pdo->prepare("
        SELECT up.user_id, p.id AS permission_id
        FROM `user_permission` up
        JOIN `permission` p ON p.id = up.permission_id
        WHERE up.user_id IN ($in)
        ORDER BY p.id
    ");
    $permStmt->execute($ids);
    $permsByUser = [];
    while ($row = $permStmt->fetch(PDO::FETCH_ASSOC)) {
        $permsByUser[(int)$row['user_id']][] = (int)$row['permission_id'];
    }

    // montar resposta
    $items = array_map(function($u) use ($permsByUser) {
        $uid = (int)$u['id'];
        return [
            'id'         => $uid,
            'nome'       => $u['name'],
            'email'      => $u['email'],
            'permissoes' => $permsByUser[$uid] ?? []
        ];
    }, $users);

    echo json_encode(['ok'=>true, 'total'=>$total, 'items'=>$items]); exit;

} catch (Throwable $e) {
    http_response_code(500);
    json_error('SERVER_ERROR', 500);
}
