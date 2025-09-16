<?php
// api/leaves/aval_history_summary.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Tenho subs diretos? ===== */
$hasSubsStmt = $pdo->prepare("
  SELECT 1
  FROM inov360.colaborador_responsaveis cr
  WHERE cr.responsavel_id = ?
    AND cr.ativo = 1
    AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
    AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
  LIMIT 1
");
$hasSubsStmt->execute([$userId]);
if (!$hasSubsStmt->fetchColumn()) {
    echo json_encode([
        "ok"         => true,
        "has_subs"   => false,
        "team_total" => 0,
        "aprovados"  => 0,
        "rejeitados" => 0
    ]);
    exit;
}

/* ===== Contagens em histórico (subs diretos) ===== */
$countStmt = $pdo->prepare("
  SELECT p.estado, COUNT(*) AS cnt
  FROM inov360.pedidos_ferias p
  JOIN inov360.colaborador_responsaveis cr
    ON cr.colaborador_id = p.user_id
   AND cr.responsavel_id = ?
   AND cr.ativo = 1
   AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
   AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
  WHERE p.estado IN ('aprovado','rejeitado')
  GROUP BY p.estado
");
$countStmt->execute([$userId]);

$approved = 0; $rejected = 0;
while ($r = $countStmt->fetch(PDO::FETCH_ASSOC)) {
    if ($r['estado'] === 'aprovado')  $approved = (int)$r['cnt'];
    if ($r['estado'] === 'rejeitado') $rejected = (int)$r['cnt'];
}

echo json_encode([
    "ok"         => true,
    "has_subs"   => true,
    "team_total" => $approved + $rejected,
    "aprovados"  => $approved,
    "rejeitados" => $rejected
]);
