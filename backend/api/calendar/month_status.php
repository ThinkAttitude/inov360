<?php
// api/timesheets/month_status.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* --- segurança --- */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); json_error('UNAUTHENTICATED', 401);
}

$selfId = (int)($_SESSION['user']['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* --- helpers --- */
function ym_bounds(string $ym): array {
    if (!preg_match('/^\d{4}-\d{2}$/', $ym)) return [null,null];
    $first = new DateTime($ym.'-01');
    $last  = (clone $first)->modify('last day of this month');
    return [$first->format('Y-m-d'), $last->format('Y-m-d')];
}
function is_date($d){ return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/',$d); }

/* --- input --- */
$month = $_GET['month'] ?? null;
if (!$month && !empty($_GET['date']) && is_date($_GET['date'])) {
    $month = (new DateTime($_GET['date']))->format('Y-m');
}
if (!$month) { http_response_code(400); json_error('MISSING_MONTH'); }

[$start,$end] = ym_bounds($month);
if (!$start) { http_response_code(400); json_error('INVALID_MONTH'); }

$userId = $selfId;

/* --- lê período, se existir --- */
$period = null;
$estado = 'open';
try {
    $st = $pdo->prepare("
    SELECT id, estado, period_start AS start, period_end AS end, decidido_por, decidido_em, comentario
      FROM timesheet_periods
     WHERE user_id=:u AND period_start=:s AND period_end=:e
     LIMIT 1
  ");
    $st->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
    if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $period = $row;
        $estado = $row['estado'];
    }
} catch (Throwable $e) {
    // se ainda não tens a tabela, ignora
}

/* --- flags e resumo --- */
$sumQ = $pdo->prepare("
  SELECT
    SUM(CASE WHEN tipo='WORK'     THEN minutos ELSE 0 END) AS workMin,
    SUM(CASE WHEN tipo='ONCALL'   THEN minutos ELSE 0 END) AS oncallMin,
    SUM(CASE WHEN tipo='KM'       THEN km      ELSE 0 END) AS km,
    COUNT(DISTINCT CASE WHEN tipo='WORK' AND minutos>0 THEN DATE(inicio) END) AS workedDays,
    SUM(CASE WHEN status IN ('draft','rejected') THEN 1 ELSE 0 END) AS draftItems
  FROM eventos
  WHERE user_id=:u
    AND DATE(inicio) BETWEEN :s AND :e
    AND tipo IN ('WORK','ONCALL','KM')
");
$sumQ->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
$sum = $sumQ->fetch(PDO::FETCH_ASSOC) ?: ["workMin"=>0,"oncallMin"=>0,"km"=>0,"workedDays"=>0,"draftItems"=>0];

$hasDraft = ((int)$sum['draftItems'] > 0);

/* estado → flags para o frontend */
$isLockedStates = ['submitted','approved','locked']; // impede edição
$isLocked  = in_array($estado, $isLockedStates, true);
$canSubmit = !$isLocked && ($estado === 'open' || $estado === 'rejected') && $hasDraft;

echo json_encode([
    "ok"     => true,
    "month"  => $month,
    "user_id"=> $userId,
    "period" => $period,               // null se nunca criado
    "estado" => $estado,               // 'open' se não existe período
    "flags"  => [
        "canSubmit" => $canSubmit,       // mostra botão “Submeter Mês”
        "isLocked"  => $isLocked,        // desativa edições
        "hasDraft"  => $hasDraft         // se há algo “por submeter”
    ],
    "summary" => [
        "workedDays" => (int)$sum['workedDays'],
        "workMin"    => (int)$sum['workMin'],
        "oncallMin"  => (int)$sum['oncallMin'],
        "km"         => (float)$sum['km']
    ],
    "range"  => [$start, $end]
]);
