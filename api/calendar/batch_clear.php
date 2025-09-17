<?php
// api/calendar/batch_clear.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== SEGURANÇA ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$selfId = (int)($_SESSION['user']['id'] ?? 0);

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== HELPERS ===== */
function json_input(): array { $raw=file_get_contents('php://input'); $d=json_decode($raw,true); return is_array($d)?$d:[]; }
function is_valid_date(string $d): bool { return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); }
function period_is_locked(PDO $pdo, int $uid, string $date): bool {
    $sql="SELECT 1 FROM timesheet_periods WHERE user_id=:u AND :d BETWEEN period_start AND period_end AND estado IN ('submitted','approved','locked') LIMIT 1";
    try{ $st=$pdo->prepare($sql); $st->execute([':u'=>$uid,':d'=>$date]); return (bool)$st->fetchColumn(); }catch(Throwable $e){ return false; }
}

/* ===== VERBO ===== */
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); echo json_encode(["ok"=>false,"code"=>"METHOD_NOT_ALLOWED"]); exit;
}

/* ===== INPUT ===== */
$in    = json_input();
$start = $in['start'] ?? '';
$end   = $in['end']   ?? '';
if (!is_valid_date($start) || !is_valid_date($end) || $start > $end) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"INVALID_RANGE"]); exit;
}
$applyWeekend = array_key_exists('applyWeekend',$in) ? (bool)$in['applyWeekend'] : True;

$userId = $selfId;

/* ===== EXEC ===== */
$deleted = 0; $skippedLocked=[];
try{
    $pdo->beginTransaction();

    $d = new DateTime($start);
    $endDt = new DateTime($end);
    $stmt = $pdo->prepare("
    DELETE FROM eventos
     WHERE user_id = ?
       AND DATE(inicio) = ?
       AND tipo IN ('WORK','OVERTIME','ONCALL','KM')
       AND status IN ('draft','rejected')
  ");

    while ($d <= $endDt) {
        $date = $d->format('Y-m-d');
        $dow  = (int)$d->format('N');
        if (!$applyWeekend && ($dow===6 || $dow===7)) { $d->modify('+1 day'); continue; }

        if (period_is_locked($pdo, $userId, $date)) {
            $skippedLocked[] = $date; $d->modify('+1 day'); continue;
        }
        $stmt->execute([$userId, $date]);
        $deleted += $stmt->rowCount();

        $d->modify('+1 day');
    }

    $pdo->commit();
    echo json_encode(["ok"=>true,"user_id"=>$userId,"range"=>[$start,$end],"deleted"=>$deleted,"skippedLocked"=>$skippedLocked]);
}catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500); echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
