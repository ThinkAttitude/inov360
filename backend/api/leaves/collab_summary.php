<?php
// api/leaves/collab_summary.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Confirmar que o colaborador tem responsáveis ativos/válidos ===== */
$userRole = $_SESSION['user']['role'] ?? '';
if ($userRole !== 'admin_rh') {
    $hasResp = $pdo->prepare("
      SELECT 1
      FROM colaborador_responsaveis
      WHERE colaborador_id = ?
        AND ativo = 1
        AND (valido_desde IS NULL OR valido_desde <= NOW())
        AND (valido_ate   IS NULL OR valido_ate   >= NOW())
      LIMIT 1
    ");
    $hasResp->execute([$userId]);
    if (!$hasResp->fetchColumn()) {
        http_response_code(400);
        echo json_encode(["ok"=>false,"code"=>"NO_RESPONSAVEIS"]);
        exit;
    }
}

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
