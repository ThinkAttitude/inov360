<?php
// api/leaves/colab_summary.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
if (($_SESSION['user']['role'] ?? '') === 'estrela') {
    http_response_code(403); echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]); exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT estado, COUNT(*) cnt FROM pedidos_ferias WHERE user_id = :u GROUP BY estado";
$stmt = $pdo->prepare($sql);
$stmt->execute([':u'=>$userId]);

$c = ['pendente'=>0,'aprovado'=>0,'rejeitado'=>0];
foreach ($stmt as $r) {
    $k = $r['estado'];
    if (isset($c[$k])) $c[$k] = (int)$r['cnt'];
}
$total = $c['pendente'] + $c['aprovado'] + $c['rejeitado'];

echo json_encode([
    "ok"      => true,
    "summary" => [
        "total"      => $total,
        "pendentes"  => $c['pendente'],
        "aprovados"  => $c['aprovado'],
        "rejeitados" => $c['rejeitado']
    ]
]);
