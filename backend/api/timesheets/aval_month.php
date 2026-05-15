<?php
// api/timesheets/aval_month.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$selfId = (int)($_SESSION['user']['id'] ?? 0);

/* Helpers */
require_once __DIR__ . '/../lib/helper/periods.php';
require_once __DIR__ . '/../lib/helper/responses.php';
function is_date($d){ return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/',$d); }

/* Input */
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) { http_response_code(400); json_error('MISSING_USER'); }

$month = $_GET['month'] ?? null; // label M = YYYY-MM
if (!$month && !empty($_GET['date']) && is_date($_GET['date'])) {
    // se vier uma data, usa o mês dela como label
    $month = (new DateTime($_GET['date']))->format('Y-m');
}
if (!$month || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$month)) {
    http_response_code(400); json_error('MISSING_OR_INVALID_MONTH');
}

/* Bounds 25..24 do mês M */
[$sDt,$eDt] = ts_bounds_from_label($month);
$start = $sDt->format('Y-m-d');
$end   = $eDt->format('Y-m-d');

/* DB */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Coluna de nome (nome|name) */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

/* Utilizador alvo */
$uq = $pdo->prepare("SELECT id, $nameCol AS name FROM user WHERE id=:id LIMIT 1");
$uq->execute([':id'=>$userId]);
$u = $uq->fetch(PDO::FETCH_ASSOC);
if (!$u) { http_response_code(404); json_error('USER_NOT_FOUND', 404); }

/* Gate de hierarquia (responsável ativo/válido) */
$gate = $pdo->prepare("
  SELECT 1
    FROM colaborador_responsaveis
   WHERE colaborador_id = :target
     AND responsavel_id  = :me
     AND ativo = 1
     AND (valido_desde IS NULL OR valido_desde <= NOW())
     AND (valido_ate   IS NULL OR valido_ate   >= NOW())
   LIMIT 1
");
$gate->execute([':target'=>$userId, ':me'=>$selfId]);
if (!$gate->fetchColumn()) {
    http_response_code(403);
    json_error('NOT_RESPONSAVEL');
}

/* Grelha base */
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

/* Período (se existir) — exatamente as mesmas fronteiras 25..24 */
$p = $pdo->prepare("
  SELECT id, estado, period_start AS start, period_end AS end, created_at
    FROM timesheet_periods
   WHERE user_id=:u AND period_start=:s AND period_end=:e
   LIMIT 1
");
$p->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
$period = $p->fetch(PDO::FETCH_ASSOC) ?: null;

/* Agregados (WORK/ONCALL/KM) */
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
$agg->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
foreach ($agg as $row) {
    $d = $row['dia']; if (!isset($days[$d])) continue;
    $days[$d]['workMin']   = (int)$row['workMin'];
    $days[$d]['oncallMin'] = (int)$row['oncallMin'];
    $days[$d]['km']        = (float)$row['km'];
}

/* Status por tipo/dia */
$sts = $pdo->prepare("
  SELECT DATE(inicio) AS dia, tipo, status
    FROM eventos
   WHERE user_id=:u
     AND DATE(inicio) BETWEEN :s AND :e
     AND tipo IN ('WORK','ONCALL','KM')
");
$sts->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
foreach ($sts as $r) {
    $d = $r['dia']; if (!isset($days[$d])) continue;
    $days[$d]['statuses'][$r['tipo']] = $r['status'];
}

/* LEAVE/SUBSTITUTION cruzando o período 25..24 */
$leaveQ = $pdo->prepare("
  SELECT id, titulo, inicio, fim, leave_request_id, tipo
    FROM eventos
   WHERE user_id=:u
     AND tipo IN ('LEAVE','SUBSTITUTION')
     AND DATE(fim)   >= :s
     AND DATE(inicio) <= :e
");
$leaveQ->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
foreach ($leaveQ as $lv) {
    $ls = new DateTime(max($start, substr($lv['inicio'],0,10)));
    $le = new DateTime(min($end,   substr($lv['fim'],0,10)));
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

/* Totais */
$totals = ["workMin"=>0,"oncallMin"=>0,"km"=>0.0];
foreach ($days as $d) {
    $totals['workMin']   += $d['workMin'];
    $totals['oncallMin'] += $d['oncallMin'];
    $totals['km']        += $d['km'];
}

/* Resposta */
echo json_encode([
    "ok"     => true,
    "user"   => ["id"=>(int)$u['id'], "name"=>$u['name']],
    "month"  => $month,
    "period" => $period,
    "days"   => array_values($days),
    "totals" => $totals
]);
