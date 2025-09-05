<?php
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

function subroles(string $r): array {
    return match ($r) {
        'inter2'  => ['opera'],
        'inter'   => ['inter2'],
        'admin'   => ['inter'],
        'estrela' => ['admin','adminrh'],
        default   => [], // adminrh não valida ninguém
    };
}
$subs = subroles($role);
if (!$subs) { echo json_encode(["ok"=>true,"items"=>[]]); exit; }

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

// descobrir coluna de nome (nome|name)
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

$wheres = [];
$params = [];

// Estado
if ($state !== 'all') {
    $wheres[] = "p.estado = ?";
    $params[] = $state;
} else {
    $wheres[] = "p.estado IN ('submitted','approved','rejected')";
}

// Subordinados
$wheres[] = "u.role IN (" . implode(',', array_fill(0, count($subs), '?')) . ")";
$params = array_merge($params, $subs);

// Mês (opcional)
if ($monthStart && $monthEnd) {
    $wheres[] = "p.period_start = ? AND p.period_end = ?";
    $params[] = $monthStart;
    $params[] = $monthEnd;
}

$whereSql = implode(' AND ', $wheres);

$sql = "
  SELECT
    p.id, p.user_id, p.period_start, p.period_end, p.estado, p.created_at,
    u.$nameCol AS user_name, u.role AS user_role
  FROM timesheet_periods p
  JOIN user u ON u.id = p.user_id
  WHERE $whereSql
  ORDER BY p.created_at ASC, p.id ASC
";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

// query para o resumo do período
$sumQ = $pdo->prepare("
  SELECT
    SUM(CASE WHEN tipo='WORK'     THEN minutos ELSE 0 END) AS workMin,
    SUM(CASE WHEN tipo='OVERTIME' THEN minutos ELSE 0 END) AS otMin,
    SUM(CASE WHEN tipo='KM'       THEN km      ELSE 0 END) AS km,
    COUNT(DISTINCT CASE WHEN tipo='WORK' AND minutos>0 THEN DATE(inicio) END) AS workedDays
  FROM eventos
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

    // etiqueta de mês/ano
    $m = (new DateTime($r['period_start']));
    $items[] = [
        "period_id"     => (int)$r['id'],
        "colaborador"   => ["id"=>(int)$r['user_id'], "nome"=>$r['user_name'], "role"=>$r['user_role']],
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
        "estado"        => $r['estado'],            // submitted|approved|rejected
        "submetido_em"  => $r['created_at']
    ];
}

echo json_encode(["ok"=>true, "state"=>$state, "items"=>$items, "total"=>count($items)]);
