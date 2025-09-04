<?php
// api/leaves/aval_summary.php
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
if (!$subRoles) { echo json_encode(["ok"=>true,"pending"=>0]); exit; }

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// tabela de utilizadores é 'user'
$inPlaceholders = implode(',', array_fill(0, count($subRoles), '?'));
$sql = "
  SELECT COUNT(*) AS c
    FROM pedidos_ferias p
    JOIN user u ON u.id = p.user_id
   WHERE p.estado = 'pendente'
     AND u.role IN ($inPlaceholders)
";
$st = $pdo->prepare($sql);
$st->execute($subRoles);
$pending = (int)$st->fetchColumn();

echo json_encode(["ok"=>true, "pending"=>$pending]);
