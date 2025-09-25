<?php
// api/calendar/batch_apply.php  (placeholders corrigidos)
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
function smin($v){ return ($v===null||$v==='')?null:max(0,(int)$v); }
function skm($v){  return ($v===null||$v==='')?null:max(0.0,(float)$v); }
function period_is_locked(PDO $pdo, int $uid, string $date): bool {
    $sql="SELECT 1 FROM timesheet_periods WHERE user_id=:u AND :d BETWEEN period_start AND period_end AND estado IN ('submitted','approved','locked') LIMIT 1";
    try{ $st=$pdo->prepare($sql); $st->execute([':u'=>$uid, ':d'=>$date]); return (bool)$st->fetchColumn(); }catch(Throwable $e){ return false; }
}

/* ===== INPUT ===== */
$in    = json_input();
$start = $in['start'] ?? '';
$end   = $in['end']   ?? '';
if (!is_valid_date($start) || !is_valid_date($end) || $start > $end) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"INVALID_RANGE"]); exit;
}

$userId = $selfId; // por padrão edita o próprio

$work   = array_key_exists('workMin',$in)   ? smin($in['workMin'])   : null;
$oncall = array_key_exists('oncallMin',$in) ? smin($in['oncallMin']) : null;
$km     = array_key_exists('km',$in)        ? skm($in['km'])         : null;

$applyWeekend = !empty($in['applyWeekend']) || !empty($in['applyweekend']); // aceita as duas grafias
$overwrite    = array_key_exists('overwrite',$in) ? (bool)$in['overwrite'] : true;

if ($work===null && $oncall===null && $km===null) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"NO_FIELDS"]); exit;
}

/* ===== PREPARED (sem placeholders repetidos) ===== */
$upsertWork = $pdo->prepare("
  INSERT INTO eventos (user_id, titulo, tipo, inicio, fim, minutos, status, source)
  VALUES (:uid, :title, :tipo, CONCAT(:d1,' 00:00:00'), CONCAT(:d2,' 23:59:59'), :min, 'draft', 'manual')
  ON DUPLICATE KEY UPDATE
    minutos = IF(:ow1=1, VALUES(minutos), minutos),
    inicio  = IF(:ow2=1, VALUES(inicio),  inicio),
    fim     = IF(:ow3=1, VALUES(fim),     fim),
    status  = IF(:ow4=1, 'draft', status),
    updated_at = CURRENT_TIMESTAMP
");

$upsertKm = $pdo->prepare("
  INSERT INTO eventos (user_id, titulo, tipo, inicio, fim, km, status, source)
  VALUES (:uid, 'KM', 'KM', CONCAT(:d1,' 00:00:00'), CONCAT(:d2,' 23:59:59'), :km, 'draft', 'manual')
  ON DUPLICATE KEY UPDATE
    km     = IF(:okm1=1, VALUES(km), km),
    status = IF(:okm2=1, 'draft', status),
    updated_at = CURRENT_TIMESTAMP
");

/* ===== LOOP ===== */
$summary = ["daysApplied"=>0, "workMin"=>0, "oncallMin"=>0, "km"=>0.0];
$skippedLocked = [];

try {
    $pdo->beginTransaction();

    $d = new DateTime($start);
    $endDt = new DateTime($end);

    while ($d <= $endDt) {
        $date = $d->format('Y-m-d');
        $dow  = (int)$d->format('N'); // 6=Sat, 7=Sun
        if (!$applyWeekend && ($dow===6 || $dow===7)) { $d->modify('+1 day'); continue; }

        if (period_is_locked($pdo, $userId, $date)) { $skippedLocked[] = $date; $d->modify('+1 day'); continue; }

        $appliedToday = false;

        if ($work !== null) {
            $upsertWork->execute([
                ':uid'=>$userId, ':title'=>'WORK', ':tipo'=>'WORK',
                ':d1'=>$date, ':d2'=>$date, ':min'=>$work,
                ':ow1'=>$overwrite?1:0, ':ow2'=>$overwrite?1:0, ':ow3'=>$overwrite?1:0, ':ow4'=>$overwrite?1:0
            ]);
            $summary['workMin'] += $work; $appliedToday = true;
        }
        if ($oncall !== null) {
            $upsertWork->execute([
                ':uid'=>$userId, ':title'=>'ONCALL', ':tipo'=>'ONCALL',
                ':d1'=>$date, ':d2'=>$date, ':min'=>$oncall,
                ':ow1'=>$overwrite?1:0, ':ow2'=>$overwrite?1:0, ':ow3'=>$overwrite?1:0, ':ow4'=>$overwrite?1:0
            ]);
            $summary['oncallMin'] += $oncall; $appliedToday = true;
        }
        if ($km !== null) {
            $upsertKm->execute([
                ':uid'=>$userId, ':d1'=>$date, ':d2'=>$date, ':km'=>$km,
                ':okm1'=>$overwrite?1:0, ':okm2'=>$overwrite?1:0
            ]);
            $summary['km'] += $km; $appliedToday = true;
        }

        if ($appliedToday) $summary['daysApplied'] += 1;
        $d->modify('+1 day');
    }

    $pdo->commit();
    echo json_encode(["ok"=>true,"user_id"=>$userId,"range"=>[$start,$end],"summary"=>$summary,"skippedLocked"=>$skippedLocked]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500); echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
