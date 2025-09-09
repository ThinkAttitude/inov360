<?php
// api/timesheets/periods_approved_list.php
declare(strict_types=1);
session_start();

/* Auth */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']); exit;
}
$role = $_SESSION['user']['role'] ?? '';
// Aceitar tanto a convenção documental (admin_rh) como a usada no restante código (adminrh)
$allowed = ['admin_rh','adminrh'];
if (!in_array($role, $allowed, true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN']); exit;
}

/* Deps & DB */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* nome em user: nome|name */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

/* Inputs */
$month  = isset($_GET['month']) ? trim((string)$_GET['month']) : '';
$q      = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$limit  = max(1, (int)($_GET['limit']  ?? 200));
$offset = max(0, (int)($_GET['offset'] ?? 0));

if ($month !== '' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'INVALID_MONTH_FORMAT (use YYYY-MM)']); exit;
}

/* WHERE */
$where = ["tp.estado = 'approved'"];
$params = [];

$monthExpr = "DATE_FORMAT(tp.period_start, '%Y-%m')";
if ($month !== '') {
    $where[] = "$monthExpr = :month";
    $params[':month'] = $month;
}
if ($q !== '') {
    $where[] = "(u.$nameCol LIKE :q OR u.email LIKE :q)";
    $params[':q'] = "%$q%";
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

/* Query */
$sql = "
SELECT
    tp.id        AS period_id,
    tp.user_id   AS user_id,
    u.$nameCol   AS nome,
    u.email      AS email,
    c.name       AS company_name,
    $monthExpr   AS month,
    tp.period_start,
    tp.period_end
FROM timesheet_periods tp
JOIN user u         ON u.id = tp.user_id
LEFT JOIN company c ON c.id = u.company_id
$whereSql
ORDER BY month ASC, nome ASC, user_id ASC
LIMIT :limit OFFSET :offset
";
$sth = $pdo->prepare($sql);
foreach ($params as $k=>$v) $sth->bindValue($k,$v);
$sth->bindValue(':limit',$limit,PDO::PARAM_INT);
$sth->bindValue(':offset',$offset,PDO::PARAM_INT);
$sth->execute();
$rows = $sth->fetchAll(PDO::FETCH_ASSOC);

/* Total para paginação */
$sqlCount = "
SELECT COUNT(*)
FROM timesheet_periods tp
JOIN user u ON u.id = tp.user_id
" . $whereSql;
$stc = $pdo->prepare($sqlCount);
foreach ($params as $k=>$v) $stc->bindValue($k,$v);
$stc->execute();
$total = (int)$stc->fetchColumn();

/* Resposta */
echo json_encode([
    'success' => true,
    'filters' => [
        'month'  => $month ?: null,
        'q'      => $q ?: null,
        'limit'  => $limit,
        'offset' => $offset,
    ],
    'total' => $total,
    'rows'  => $rows,
], JSON_UNESCAPED_UNICODE);
