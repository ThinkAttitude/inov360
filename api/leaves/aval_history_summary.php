<?php
// api/leaves/aval_history_summary.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$role = $_SESSION['user']['role'] ?? '';
$allowed = ['inter2','inter','admin','adminrh','estrela'];
if (!in_array($role, $allowed, true)) {
    http_response_code(403); echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]); exit;
}

// quem valida quem
function subordinate_roles(string $r): array {
    return match ($r) {
        'inter2'  => ['opera'],
        'inter'   => ['inter2'],
        'admin'   => ['inter'],
        'estrela' => ['admin','adminrh'],
        default   => [], // adminrh não valida ninguém
    };
}
$subRoles = subordinate_roles($role);
if (!$subRoles) { echo json_encode(["ok"=>true,"team_total"=>0,"aprovados"=>0,"rejeitados"=>0]); exit; }

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$ph = implode(',', array_fill(0, count($subRoles), '?'));
$sql = "
  SELECT p.estado, COUNT(*) cnt
    FROM pedidos_ferias p
    JOIN user u ON u.id = p.user_id
   WHERE p.estado IN ('aprovado','rejeitado')
     AND u.role IN ($ph)
   GROUP BY p.estado
";
$st = $pdo->prepare($sql);
$st->execute($subRoles);

$approved = 0; $rejected = 0;
foreach ($st as $r) {
    if ($r['estado']==='aprovado')  $approved = (int)$r['cnt'];
    if ($r['estado']==='rejeitado') $rejected = (int)$r['cnt'];
}

echo json_encode([
    "ok"          => true,
    "team_total"  => $approved + $rejected,
    "aprovados"   => $approved,
    "rejeitados"  => $rejected
]);
