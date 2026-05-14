<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'UNAUTHENTICATED']);
    exit;
}

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(6, $perms, true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'FORBIDDEN']);
    exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $page = max(1, (int)($_GET['page'] ?? 1));
    $pageSize = min(200, max(1, (int)($_GET['page_size'] ?? 25)));
    $offset = ($page - 1) * $pageSize;

    $q = trim((string)($_GET['q'] ?? ''));
    $companyId = isset($_GET['company_id']) ? (int)$_GET['company_id'] : null;
    $orderBy = strtolower((string)($_GET['order_by'] ?? 'name'));
    $orderDir = strtolower((string)($_GET['order_dir'] ?? 'asc'));

    $orderCol = 'cd.nome';
    if ($orderBy === 'email') $orderCol = 'cd.email';
    if ($orderBy === 'id') $orderCol = 'u.id';
    if ($orderBy === 'company') $orderCol = 'c.name';

    $orderDir = $orderDir === 'desc' ? 'DESC' : 'ASC';

    $where = [];
    $params = [];

    if (!empty($companyId)) {
        $where[] = 'u.company_id = :company_id';
        $params[':company_id'] = ['value' => $companyId, 'type' => PDO::PARAM_INT];
    }

    if ($q !== '') {
        $where[] = '(cd.nome LIKE :q_name OR cd.email LIKE :q_email)';
        $params[':q_name'] = ['value' => "%{$q}%", 'type' => PDO::PARAM_STR];
        $params[':q_email'] = ['value' => "%{$q}%", 'type' => PDO::PARAM_STR];
    }

    $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT
            u.id,
            cd.nome AS name,
            cd.email AS email,
            u.company_id,
            c.name AS company_name,
            c.slug AS company_slug,
            c.logo_path AS company_logo
        FROM `user` u
        INNER JOIN colaborador_dados cd ON cd.user_id = u.id
        LEFT JOIN `company` c ON c.id = u.company_id
        $sqlWhere
        ORDER BY $orderCol $orderDir, u.id ASC
        LIMIT :lim OFFSET :off
    ";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $name => $p) {
        $stmt->bindValue($name, $p['value'], $p['type']);
    }

    $stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlCount = "
        SELECT COUNT(*)
        FROM `user` u
        INNER JOIN colaborador_dados cd ON cd.user_id = u.id
        LEFT JOIN `company` c ON c.id = u.company_id
        $sqlWhere
    ";

    $stmtCount = $pdo->prepare($sqlCount);

    foreach ($params as $name => $p) {
        $stmtCount->bindValue($name, $p['value'], $p['type']);
    }

    $stmtCount->execute();

    $total = (int)$stmtCount->fetchColumn();
    $totalPages = (int)ceil($total / $pageSize);

    $items = array_map(function ($r) {
        return [
            'id' => (int)$r['id'],
            'name' => $r['name'],
            'email' => $r['email'],
            'company' => [
                'id' => isset($r['company_id']) ? (int)$r['company_id'] : null,
                'name' => $r['company_name'] ?? null,
                'slug' => $r['company_slug'] ?? null,
                'logo' => $r['company_logo'] ?? null,
            ],
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'page' => $page,
        'page_size' => $pageSize,
        'total' => $total,
        'total_pages' => $totalPages,
        'items' => $items,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'SERVER_ERROR']);
}