<?php
// api/timesheets/aval_periods.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}

$selfId = (int)($_SESSION['user']['id'] ?? 0);

// filtros
$state = $_GET['state'] ?? 'submitted'; // submitted|approved|rejected|all
$validStates = ['submitted','approved','rejected','all'];
if (!in_array($state, $validStates, true)) $state = 'submitted';

$month = $_GET['month'] ?? null; // opcional YYYY-MM
$monthStart = $monthEnd = null;
if ($month && preg_match('/^\d{4}-\d{2}$/',$month)) {
    $d = new DateTime("$month-01");
    $monthStart = $d->format('Y-m-d');
    $monthEnd   = $d->modify('last day of this month')->format('Y-m-d');
}

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// confirmar se tem pelo menos um sub (ativo/válido)
$hasSubs = $pdo->prepare("
  SELECT 1
    FROM inov360.colaborador_responsaveis cr
   WHERE cr.responsavel_id = :me
     AND cr.ativo = 1
     AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
     AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
   LIMIT 1
");
$hasSubs->execute([':me'=>$selfId]);
if (!$hasSubs->fetchColumn()) {
    echo json_encode(["ok"=>true,"items"=>[],"total"=>0]); exit;
}

// descobrir coluna de nome (nome|name)
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM inov360.user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

// montar WHERE
$where = [];
$params = [':me'=>$selfId];

if ($state !== 'all') {
    $where[] = "p.estado = :st";
    $params[':st'] = $state;
} else {
    $where[] = "p.estado IN ('submitted','approved','rejected')";
}

if ($monthStart && $monthEnd) {
    $where[] = "p.period_start = :ps AND p.period_end = :pe";
    $params[':ps'] = $monthStart;
    $params[':pe'] = $monthEnd;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// períodos dos MEUS subordinados diretos (relação ativa/válida AGORA)
$sql = "
  SELECT
    p.id, p.user_id, p.period_start, p.period_end, p.estado, p.created_at, u.$nameCol AS user_name
  FROM inov360.timesheet_periods p
  JOIN inov360.user u ON u.id = p.user_id
  JOIN inov360.colaborador_responsaveis cr
       ON cr.colaborador_id = p.user_id
      AND cr.responsavel_id = :me
      AND cr.ativo = 1
      AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
      AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
  $whereSql
  ORDER BY p.created_at ASC, p.id ASC
";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

// resumo do período (WORK, OVERTIME, KM) — igual ao teu original
$sumQ = $pdo->prepare("
  SELECT
    SUM(CASE WHEN tipo='WORK'     THEN minutos ELSE 0 END) AS workMin,
    SUM(CASE WHEN tipo='OVERTIME' THEN minutos ELSE 0 END) AS otMin,
    SUM(CASE WHEN tipo='KM'       THEN km      ELSE 0 END) AS km,
    COUNT(DISTINCT CASE WHEN tipo='WORK' AND minutos>0 THEN DATE(inicio) END) AS workedDays
  FROM inov360.eventos
  WHERE user_id=:u
    AND DATE(inicio) BETWEEN :s AND :e
    AND tipo IN ('WORK','OVERTIME','KM')
");

$items = [];
foreach ($rows as $r) {
    $sumQ->execute([
        ':u' => (int)$r['user_id'],
        ':s' => $r['period_start'],
        ':e' => $r['period_end']
    ]);
    $s = $sumQ->fetch(PDO::FETCH_ASSOC) ?: ["workMin"=>0,"otMin"=>0,"km"=>0,"workedDays"=>0];

    $m = (new DateTime($r['period_start']));
    $items[] = [
        "period_id"     => (int)$r['id'],
        "colaborador"   => [
            "id"   => (int)$r['user_id'],
            "nome" => $r['user_name'],
        ],
        "mes"           => [
            "year"  => (int)$m->format('Y'),
            "month" => (int)$m->format('m'),
            "start" => $r['period_start'],
            "end"   => $r['period_end']
        ],
        "resumo"        => [
            "workedDays" => (int)$s['workedDays'],
            "workMin"    => (int)$s['workMin'],
            "otMin"      => (int)$s['otMin'],
            "km"         => (float)$s['km']
        ],
        "estado"        => $r['estado'],   // submitted|approved|rejected
        "submetido_em"  => $r['created_at']
    ];
}

echo json_encode(["ok"=>true, "state"=>$state, "items"=>$items, "total"=>count($items)]);
