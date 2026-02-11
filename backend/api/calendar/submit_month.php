<?php
// api/timesheets/submit_month.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$selfId = (int)($_SESSION['user']['id'] ?? 0);

/* DB + helper 25..24 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/periods.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Helpers */
function json_input(): array { $raw=file_get_contents('php://input'); $d=json_decode($raw,true); return is_array($d)?$d:[]; }

/* Input */
$in = json_input();
$month = $in['month'] ?? null; // label YYYY-MM do mês M
if (!$month && !empty($in['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $in['date'])) {
    $dt = new DateTime($in['date']); $month = $dt->format('Y-m');
}
if (!$month || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$month)) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"MISSING_OR_INVALID_MONTH"]); exit;
}

/* BLOQUEIO 25(M) 00:00 */
if ((new DateTime()) >= ts_lock_at($month)) {
    http_response_code(409); echo json_encode(["ok"=>false,"code"=>"PERIOD_CLOSED"]); exit;
}

/* Bounds 25..24 para M */
[$sDt,$eDt] = ts_bounds_from_label($month);
$start = $sDt->format('Y-m-d');
$end   = $eDt->format('Y-m-d');

$userId = $selfId;

try {
    // procurar período existente
    $getP = $pdo->prepare("
      SELECT id, estado FROM timesheet_periods
       WHERE user_id=:u AND period_start=:s AND period_end=:e
       LIMIT 1
    ");
    $getP->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
    $period = $getP->fetch(PDO::FETCH_ASSOC);

    if ($period) {
        $periodId = (int)$period['id'];
        $estado   = $period['estado'];
        if (in_array($estado, ['submitted','approved','locked'], true)) {
            http_response_code(409);
            echo json_encode(["ok"=>false,"code"=>"ALREADY_SUBMITTED","period"=>["id"=>$periodId,"estado"=>$estado]]); exit;
        }
        // se estiver 'rejected' ou 'open', deixa seguir
    } else {
        $insP = $pdo->prepare("
          INSERT INTO timesheet_periods (user_id, period_start, period_end, estado)
          VALUES (:u, :s, :e, 'open')
        ");
        $insP->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
        $periodId = (int)$pdo->lastInsertId();
    }

    // resumo antes de submeter (opcional)
    $sumQ = $pdo->prepare("
      SELECT
        SUM(CASE WHEN tipo='WORK'     THEN minutos ELSE 0 END) AS workMin,
        SUM(CASE WHEN tipo='ONCALL'   THEN minutos ELSE 0 END) AS oncallMin,
        SUM(CASE WHEN tipo='KM'       THEN km      ELSE 0 END) AS km,
        COUNT(DISTINCT CASE WHEN tipo='WORK' AND minutos>0 THEN DATE(inicio) END) AS workedDays
      FROM eventos
      WHERE user_id=:u
        AND DATE(inicio) BETWEEN :s AND :e
        AND tipo IN ('WORK','ONCALL','KM')
    ");
    $sumQ->execute([':u'=>$userId, ':s'=>$start, ':e'=>$end]);
    $summary = $sumQ->fetch(PDO::FETCH_ASSOC) ?: ["workMin"=>0,"oncallMin"=>0,"km"=>0,"workedDays"=>0];

    // marcar eventos como submitted e ligar ao período
    $upd = $pdo->prepare("
      UPDATE eventos
         SET status='submitted', period_id=:pid
       WHERE user_id=:u
         AND DATE(inicio) BETWEEN :s AND :e
         AND tipo IN ('WORK','ONCALL','KM')
         AND status IN ('draft','rejected')
    ");

    $pdo->beginTransaction();
    $upd->execute([':pid'=>$periodId, ':u'=>$userId, ':s'=>$start, ':e'=>$end]);

    // muda estado do período para submitted
    $updP = $pdo->prepare("
      UPDATE timesheet_periods
         SET estado='submitted', updated_at=CURRENT_TIMESTAMP
       WHERE id=:id
    ");
    $updP->execute([':id'=>$periodId]);
    $pdo->commit();

    echo json_encode([
        "ok" => true,
        "period" => ["id"=>$periodId, "estado"=>"submitted", "start"=>$start, "end"=>$end],
        "summary" => [
            "workedDays" => (int)$summary['workedDays'],
            "workMin"    => (int)$summary['workMin'],
            "oncallMin"  => (int)$summary['oncallMin'],
            "km"         => (float)$summary['km']
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
