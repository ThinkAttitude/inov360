<?php
// api/employees/aval_list_all.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Auth + perm (record_managment = 6)
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']); exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(6, $perms, true)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN']); exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query params
    $page      = max(1, (int)($_GET['page'] ?? 1));
    $pageSize  = min(200, max(1, (int)($_GET['page_size'] ?? 25)));
    $offset    = ($page - 1) * $pageSize;

    $q         = trim((string)($_GET['q'] ?? '')); // pesquisa por nome/email
    $companyId = isset($_GET['company_id']) ? (int)$_GET['company_id'] : null;
    $orderBy   = strtolower((string)($_GET['order_by'] ?? 'name')); // name|email|id|company
    $orderDir  = strtolower((string)($_GET['order_dir'] ?? 'asc')); // asc|desc

    // Sanitização de ordenação
    $orderCol = 'u.name';
    if ($orderBy === 'email')   $orderCol = 'u.email';
    if ($orderBy === 'id')      $orderCol = 'u.id';
    if ($orderBy === 'company') $orderCol = 'c.name';
    $orderDir = ($orderDir === 'desc') ? 'DESC' : 'ASC';

    // WHERE dinâmico
    $where   = [];
    $params  = [];
    if (!empty($companyId)) {
        $where[]  = 'u.company_id = ?';
        $params[] = $companyId;
    }
    if ($q !== '') {
        $where[]  = '(u.name LIKE ? OR u.email LIKE ?)';
        $params[] = "%{$q}%";
        $params[] = "%{$q}%";
    }
    $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Consulta principal (apenas dados essenciais + empresa)
    $sql = "
        SELECT
            u.id,
            u.name,
            u.email,
            u.company_id,
            c.name      AS company_name,
            c.slug      AS company_slug,
            c.logo_path AS company_logo
        FROM inov360.`user` u
        LEFT JOIN inov360.`company` c ON c.id = u.company_id
        $sqlWhere
        ORDER BY $orderCol $orderDir, u.id ASC
        LIMIT :lim OFFSET :off
    ";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $i => $v) {
        $stmt->bindValue($i+1, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total para paginação
    $sqlCount = "
        SELECT COUNT(*)
        FROM inov360.`user` u
        LEFT JOIN inov360.`company` c ON c.id = u.company_id
        $sqlWhere
    ";
    $stmtCount = $pdo->prepare($sqlCount);
    foreach ($params as $i => $v) {
        $stmtCount->bindValue($i+1, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmtCount->execute();
    $total = (int)$stmtCount->fetchColumn();
    $totalPages = (int)ceil($total / $pageSize);

    // Normalização
    $items = array_map(function($r) {
        return [
            'id'    => (int)$r['id'],
            'name'  => $r['name'],
            'email' => $r['email'],
            'company' => [
                'id'   => isset($r['company_id']) ? (int)$r['company_id'] : null,
                'name' => $r['company_name'] ?? null,
                'slug' => $r['company_slug'] ?? null,
                'logo' => $r['company_logo'] ?? null,
            ]
        ];
    }, $rows);

    echo json_encode([
        'success'     => true,
        'page'        => $page,
        'page_size'   => $pageSize,
        'total'       => $total,
        'total_pages' => $totalPages,
        'items'       => $items
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    // error_log('employees/list_all error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'SERVER_ERROR']);
}
