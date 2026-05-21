<?php
// api/calendar/get_month.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ==== Segurança ==== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

$selfId = (int)($_SESSION['user']['id'] ?? 0);

/* ==== DB ==== */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ==== Helpers ==== */
function is_date($d): bool {
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$d);
}

/* ==== Input ==== */
$from = $_GET['from'] ?? null;
$to   = $_GET['to'] ?? null;

if (!is_date($from) || !is_date($to)) {
    http_response_code(400);
    json_error('INVALID_TIMEFRAME');
}

$start = (string)$from;
$end   = (string)$to;

if ($start > $end) {
    http_response_code(400);
    json_error('INVALID_TIMEFRAME_ORDER');
}

$userId = $selfId;

/* ==== Constrói a grelha base de dias ==== */
$days = [];
$cursor = new DateTime($start);
$last   = new DateTime($end);
while ($cursor <= $last) {
    $d = $cursor->format('Y-m-d');
    $days[$d] = [
        "date"      => $d,
        "workMin"   => 0,
        "oncallMin" => 0,
        "km"        => 0.0,
        "statuses"  => [],
        "leaves"    => []
    ];
    $cursor->modify('+1 day');
}

/* ==== Agregados por dia (WORK/ONCALL/KM) ==== */
$agg = $pdo->prepare("
  SELECT DATE(inicio) AS dia,
         SUM(CASE WHEN tipo='WORK'     THEN minutos ELSE 0 END) AS workMin,
         SUM(CASE WHEN tipo='ONCALL'   THEN minutos ELSE 0 END) AS oncallMin,
         SUM(CASE WHEN tipo='KM'       THEN km      ELSE 0 END) AS km
    FROM eventos
   WHERE user_id=:u
     AND DATE(inicio) BETWEEN :s AND :e
     AND tipo IN ('WORK','ONCALL','KM')
GROUP BY DATE(inicio)
");
$agg->execute([':u' => $userId, ':s' => $start, ':e' => $end]);
foreach ($agg as $row) {
    $d = $row['dia'];
    if (!isset($days[$d])) continue;
    $days[$d]['workMin']   = (int)$row['workMin'];
    $days[$d]['oncallMin'] = (int)$row['oncallMin'];
    $days[$d]['km']        = (float)$row['km'];
}

/* ==== Status por tipo e dia (draft/submitted/approved/...) ==== */
$sts = $pdo->prepare("
  SELECT DATE(inicio) AS dia, tipo, status
    FROM eventos
   WHERE user_id=:u
     AND DATE(inicio) BETWEEN :s AND :e
     AND tipo IN ('WORK','ONCALL','KM')
");
$sts->execute([':u' => $userId, ':s' => $start, ':e' => $end]);
foreach ($sts as $r) {
    $d = $r['dia'];
    if (!isset($days[$d])) continue;
    $days[$d]['statuses'][(string)$r['tipo']] = $r['status'];
}

/* ==== Férias/Ausências + Substituições (LEAVE e SUBSTITUTION) ==== */
$leaveQ = $pdo->prepare("
  SELECT id, titulo, inicio, fim, leave_request_id, tipo
    FROM eventos
   WHERE user_id=:u
     AND tipo IN ('LEAVE','SUBSTITUTION')
     AND DATE(fim)   >= :s
     AND DATE(inicio) <= :e
");
$leaveQ->execute([':u' => $userId, ':s' => $start, ':e' => $end]);

foreach ($leaveQ as $lv) {
    $lvStart = substr((string)$lv['inicio'], 0, 10);
    $lvEnd   = substr((string)$lv['fim'], 0, 10);

    $ls = new DateTime(max($start, $lvStart));
    $le = new DateTime(min($end, $lvEnd));

    while ($ls <= $le) {
        $d = $ls->format('Y-m-d');
        if (isset($days[$d])) {
            $days[$d]['leaves'][] = [
                "id"        => (int)$lv['id'],
                "requestId" => $lv['leave_request_id'] ? (int)$lv['leave_request_id'] : null,
                "title"     => $lv['titulo'] ?? ($lv['tipo'] === 'SUBSTITUTION' ? 'Substituição' : 'LEAVE'),
                "kind"      => $lv['tipo']
            ];
        }
        $ls->modify('+1 day');
    }
}

/* ==== Totais do intervalo ==== */
$totals = ["workMin" => 0, "oncallMin" => 0, "km" => 0.0];
foreach ($days as $d) {
    $totals['workMin']   += $d['workMin'];
    $totals['oncallMin'] += $d['oncallMin'];
    $totals['km']        += $d['km'];
}

/* ==== Resposta ==== */
echo json_encode([
    "ok"     => true,
    "from"   => $start,
    "to"     => $end,
    "days"   => array_values($days),
    "totals" => $totals
]);