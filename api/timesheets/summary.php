<?php
// api/timesheets/summary.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ==== segurança ==== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
$role   = $_SESSION['user']['role'] ?? '';
$selfId = (int)($_SESSION['user']['id'] ?? 0);
$allowedRoles = ['inter2','inter','admin','adminrh'];
if (!in_array($role, $allowedRoles, true)) {
    http_response_code(403);
    echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ==== helpers ==== */
function ym_bounds(string $ym): array {
    if (!preg_match('/^\d{4}-\d{2}$/', $ym)) return [null,null];
    $first = new DateTime($ym.'-01');
    $last  = (clone $first)->modify('last day of this month');
    return [$first->format('Y-m-d'), $last->format('Y-m-d')];
}

/* ==== input ==== */
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$month  = $_GET['month'] ?? null;

if (!$userId || !$month) {
    http_response_code(400);
    echo json_encode(["ok"=>false,"code"=>"MISSING_PARAMS"]);
    exit;
}
[$start,$end] = ym_bounds($month);
if (!$start) {
    http_response_code(400);
    echo json_encode(["ok"=>false,"code"=>"INVALID_MONTH"]);
    exit;
}

/* ==== busca período ==== */
try {
    $p = $pdo->prepare("
        SELECT id, estado, period_start, period_end,
               created_at, updated_at,
               decidido_por, decidido_em, comentario
          FROM timesheet_periods
         WHERE user_id=:u AND period_start=:s AND period_end=:e
         LIMIT 1
    ");
    $p->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
    $period = $p->fetch(PDO::FETCH_ASSOC);

    if (!$period || $period['estado']!=='submitted') {
        http_response_code(404);
        echo json_encode(["ok"=>false,"code"=>"NOT_SUBMITTED"]);
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
    exit;
}

/* ==== sumário ==== */
$sumQ = $pdo->prepare("
  SELECT
    SUM(CASE WHEN tipo='WORK'     THEN minutos ELSE 0 END) AS workMin,
    SUM(CASE WHEN tipo='OVERTIME' THEN minutos ELSE 0 END) AS otMin,
    SUM(CASE WHEN tipo='ONCALL'   THEN minutos ELSE 0 END) AS oncallMin,
    SUM(CASE WHEN tipo='KM'       THEN km      ELSE 0 END) AS km,
    COUNT(DISTINCT CASE WHEN tipo='WORK' AND minutos>0 THEN DATE(inicio) END) AS workedDays
  FROM eventos
  WHERE user_id=:u
    AND DATE(inicio) BETWEEN :s AND :e
    AND tipo IN ('WORK','OVERTIME','ONCALL','KM')
");
$sumQ->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
$summary = $sumQ->fetch(PDO::FETCH_ASSOC) ?: ["workMin"=>0,"otMin"=>0,"oncallMin"=>0,"km"=>0,"workedDays"=>0];

/* ==== resposta ==== */
echo json_encode([
    "ok" => true,
    "user" => [
        "id"   => $userId
    ],
    "period" => [
        "id"          => (int)$period['id'],
        "estado"      => $period['estado'],
        "start"       => $period['period_start'],
        "end"         => $period['period_end'],
        "created_at"  => $period['created_at']  ?? null,
        "updated_at"  => $period['updated_at']  ?? null,
        "decidido_por"=> $period['decidido_por']?? null,
        "decidido_em" => $period['decidido_em'] ?? null,
        "comentario"  => $period['comentario']  ?? null
    ],
    "summary" => [
        "workedDays" => (int)$summary['workedDays'],           // Dias trabalhados
        "totalHours" => round(((int)$summary['workMin']) / 60), // Horas totais
        "extraHours" => round(((int)$summary['otMin']) / 60),   // Horas extra
        "km"         => (float)$summary['km']                   // KM total
    ]

]);
