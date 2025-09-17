<?php
// api/timesheets/aval_month.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ==== Sessão ==== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
$selfId = (int)($_SESSION['user']['id'] ?? 0);

/* ==== Helpers ==== */
function ym_bounds(string $ym): array {
    if (!preg_match('/^\d{4}-\d{2}$/', $ym)) return [null, null];
    $first = new DateTime($ym . '-01');
    $last  = (clone $first)->modify('last day of this month');
    return [$first->format('Y-m-d'), $last->format('Y-m-d')];
}
function is_date($d){ return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/',$d); }

/* ==== Input ==== */
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"MISSING_USER"]); exit; }

$month = $_GET['month'] ?? null;
if (!$month && !empty($_GET['date']) && is_date($_GET['date'])) {
    $month = (new DateTime($_GET['date']))->format('Y-m');
}
if (!$month) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"MISSING_MONTH"]); exit; }
[$start,$end] = ym_bounds($month);
if (!$start) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"INVALID_MONTH"]); exit; }

/* ==== DB ==== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ==== Coluna de nome (nome|name) ==== */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM inov360.user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

/* ==== Carregar utilizador alvo ==== */
$uq = $pdo->prepare("SELECT id, $nameCol AS name FROM inov360.user WHERE id=:id LIMIT 1");
$uq->execute([':id'=>$userId]);
$u = $uq->fetch(PDO::FETCH_ASSOC);
if (!$u) { http_response_code(404); echo json_encode(["ok"=>false,"code"=>"USER_NOT_FOUND"]); exit; }

/* ==== Gate de hierarquia (novo modelo) ==== */
/* Tem de ser responsável ativo/válido do utilizador-alvo. */
$gate = $pdo->prepare("
  SELECT 1
    FROM inov360.colaborador_responsaveis
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
    echo json_encode(["ok"=>false,"code"=>"NOT_RESPONSAVEL"]);
    exit;
}

/* ==== Grelha base de dias ==== */
$days = [];
$cursor = new DateTime($start);
$last   = new DateTime($end);
while ($cursor <= $last) {
    $d = $cursor->format('Y-m-d');
    $days[$d] = [
        "date"      => $d,
        "workMin"   => 0,
        "otMin"     => 0,
        "oncallMin" => 0,
        "km"        => 0.0,
        "statuses"  => [],   // {"WORK":"draft",...}
        "leaves"    => []    // [{"id":..., "requestId":..., "title":"..." , "kind":"LEAVE|SUBSTITUTION"}]
    ];
    $cursor->modify('+1 day');
}

/* ==== Período do mês (se existir) ==== */
$period = null;
$p = $pdo->prepare("
  SELECT id, estado, period_start AS start, period_end AS end, created_at
    FROM inov360.timesheet_periods
   WHERE user_id=:u AND period_start=:s AND period_end=:e
   LIMIT 1
");
$p->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
$period = $p->fetch(PDO::FETCH_ASSOC) ?: null;

/* ==== Agregados por dia (WORK/OVERTIME/ONCALL/KM) ==== */
$agg = $pdo->prepare("
  SELECT DATE(inicio) AS dia,
         SUM(CASE WHEN tipo='WORK'     THEN minutos ELSE 0 END) AS workMin,
         SUM(CASE WHEN tipo='OVERTIME' THEN minutos ELSE 0 END) AS otMin,
         SUM(CASE WHEN tipo='ONCALL'   THEN minutos ELSE 0 END) AS oncallMin,
         SUM(CASE WHEN tipo='KM'       THEN km      ELSE 0 END) AS km
    FROM inov360.eventos
   WHERE user_id=:u
     AND DATE(inicio) BETWEEN :s AND :e
     AND tipo IN ('WORK','OVERTIME','ONCALL','KM')
GROUP BY DATE(inicio)
");
$agg->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
foreach ($agg as $row) {
    $d = $row['dia'];
    if (!isset($days[$d])) continue;
    $days[$d]['workMin']   = (int)$row['workMin'];
    $days[$d]['otMin']     = (int)$row['otMin'];
    $days[$d]['oncallMin'] = (int)$row['oncallMin'];
    $days[$d]['km']        = (float)$row['km'];
}

/* ==== Status por tipo e dia ==== */
$sts = $pdo->prepare("
  SELECT DATE(inicio) AS dia, tipo, status
    FROM inov360.eventos
   WHERE user_id=:u
     AND DATE(inicio) BETWEEN :s AND :e
     AND tipo IN ('WORK','OVERTIME','ONCALL','KM')
");
$sts->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
foreach ($sts as $r) {
    $d = $r['dia']; if (!isset($days[$d])) continue;
    $days[$d]['statuses'][$r['tipo']] = $r['status'];
}

/* ==== Férias/Ausências + Substituições (LEAVE e SUBSTITUTION) ==== */
$leaveQ = $pdo->prepare("
  SELECT id, titulo, inicio, fim, leave_request_id, tipo
    FROM inov360.eventos
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

/* ==== Totais ==== */
$totals = ["workMin"=>0,"otMin"=>0,"oncallMin"=>0,"km"=>0.0];
foreach ($days as $d) {
    $totals['workMin']   += $d['workMin'];
    $totals['otMin']     += $d['otMin'];
    $totals['oncallMin'] += $d['oncallMin'];
    $totals['km']        += $d['km'];
}

/* ==== Resposta ==== */
echo json_encode([
    "ok"     => true,
    "user"   => ["id"=>(int)$u['id'], "name"=>$u['name']],
    "month"  => $month,
    "period" => $period,          // null se não existir
    "days"   => array_values($days),
    "totals" => $totals
]);
