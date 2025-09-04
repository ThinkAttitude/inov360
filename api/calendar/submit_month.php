<?php
// api/timesheets/submit_month.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== SEGURANÇA ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$role   = $_SESSION['user']['role'] ?? '';
$selfId = (int)($_SESSION['user']['id'] ?? 0);
if ($role === 'estrela') { http_response_code(403); echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]); exit; }
$isMgr  = in_array($role, ['inter2','inter','admin','adminrh'], true);

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== HELPERS ===== */
function json_input(): array { $raw=file_get_contents('php://input'); $d=json_decode($raw,true); return is_array($d)?$d:[]; }
function ym_bounds(string $ym): array {
    // $ym: "YYYY-MM"
    if (!preg_match('/^\d{4}-\d{2}$/', $ym)) return [null,null];
    $first = new DateTime($ym . '-01');
    $last  = (clone $first)->modify('last day of this month');
    return [$first->format('Y-m-d'), $last->format('Y-m-d')];
}

/* ===== INPUT ===== */
$in = json_input();

/*
  Aceitamos:
  - { "month": "2025-09" }
  (opcionalmente "user_id" se for manager)
*/
$month = $in['month'] ?? null;
if (!$month) { // fallback: se enviares uma data qualquer
    if (!empty($in['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $in['date'])) {
        $dt = new DateTime($in['date']); $month = $dt->format('Y-m');
    }
}
if (!$month) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"MISSING_MONTH"]); exit; }

[$start,$end] = ym_bounds($month);
if (!$start) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"INVALID_MONTH"]); exit; }

$userId = $selfId;
if ($isMgr && isset($in['user_id']) && (int)$in['user_id']>0) $userId = (int)$in['user_id'];

/* ===== OBTÉM/CRIA PERÍODO ===== */
try {
    // procura período existente
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
        // se estiver 'rejected', deixamos seguir (re-submissão)
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

    // marca eventos como submitted e liga ao período
    $upd = $pdo->prepare("
    UPDATE eventos
       SET status='submitted', period_id=:pid
     WHERE user_id=:u
       AND DATE(inicio) BETWEEN :s AND :e
       AND tipo IN ('WORK','OVERTIME','ONCALL','KM')
       AND status IN ('draft','rejected')
  ");

    $pdo->beginTransaction();
    $upd->execute([':pid'=>$periodId, ':u'=>$userId, ':s'=>$start, ':e'=>$end]);

    // muda estado do período para submitted
    $updP = $pdo->prepare("UPDATE timesheet_periods SET estado='submitted', updated_at=CURRENT_TIMESTAMP WHERE id=:id");
    $updP->execute([':id'=>$periodId]);
    $pdo->commit();

    echo json_encode([
        "ok" => true,
        "period" => ["id"=>$periodId, "estado"=>"submitted", "start"=>$start, "end"=>$end],
        "summary" => [
            "workedDays" => (int)$summary['workedDays'],
            "workMin"    => (int)$summary['workMin'],
            "otMin"      => (int)$summary['otMin'],
            "oncallMin"  => (int)$summary['oncallMin'],
            "km"         => (float)$summary['km']
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
