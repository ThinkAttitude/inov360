<?php
// api/leaves/aval_summary.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

$userId = (int)($_SESSION['user']['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Verificar se o utilizador é responsável de alguém (subs) ===== */
$hasSubsStmt = $pdo->prepare("
  SELECT 1
  FROM colaborador_responsaveis cr
  WHERE cr.responsavel_id = ?
    AND cr.ativo = 1
    AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
    AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
  LIMIT 1
");
$hasSubsStmt->execute([$userId]);
$hasSubs = (bool)$hasSubsStmt->fetchColumn();

if (!$hasSubs) {
    echo json_encode(["ok"=>true, "has_subs"=>false, "pending"=>0]);
    exit;
}

/* ===== Contar pedidos pendentes dos meus subs diretos ===== */
$pendingStmt = $pdo->prepare("
  SELECT COUNT(*) AS c
  FROM pedidos_ferias p
  JOIN colaborador_responsaveis cr
    ON cr.colaborador_id = p.user_id
   AND cr.responsavel_id = ?
   AND cr.ativo = 1
   AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
   AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
  WHERE p.estado = 'pendente'
");
$pendingStmt->execute([$userId]);
$pending = (int)$pendingStmt->fetchColumn();

echo json_encode([
    "ok" => true,
    "has_subs" => true,
    "pending" => $pending
]);
