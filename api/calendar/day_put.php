<?php
// api/calendar/day_put.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== SEGURANÇA ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$role   = $_SESSION['user']['role'] ?? '';
$selfId = (int)($_SESSION['user']['id'] ?? 0);
if ($role === 'estrela') {
    http_response_code(403);
    echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]); exit;
}
$isMgr = in_array($role, ['inter2','inter','admin','adminrh'], true);

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== HELPERS ===== */
function json_input(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}
function is_valid_date(string $d): bool { return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); }
function period_is_locked(PDO $pdo, int $uid, string $date): bool {
    $sql = "SELECT 1 FROM timesheet_periods
          WHERE user_id=:u AND :d BETWEEN period_start AND period_end
            AND estado IN ('submitted','approved','locked') LIMIT 1";
    try { $st=$pdo->prepare($sql); $st->execute([':u'=>$uid, ':d'=>$date]); return (bool)$st->fetchColumn(); }
    catch(Throwable $e){ return false; }
}
function smin($v){ return ($v===null||$v==='')?null:max(0,(int)$v); }
function skm($v){  return ($v===null||$v==='')?null:max(0.0,(float)$v); }

/* ===== VERBO ===== */
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["ok"=>false,"code"=>"METHOD_NOT_ALLOWED"]); exit;
}

/* ===== INPUT ===== */
$in   = json_input();
$date = $in['date'] ?? '';
if (!is_valid_date($date)) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"INVALID_DATE"]); exit; }

/* user alvo: sessão por defeito; só manager pode indicar outro user_id */
$userId = $selfId;
if ($isMgr && isset($in['user_id']) && (int)$in['user_id']>0) $userId = (int)$in['user_id'];

if (period_is_locked($pdo, $userId, $date)) {
    http_response_code(409); echo json_encode(["ok"=>false,"code"=>"PERIOD_LOCKED"]); exit;
}

$work   = array_key_exists('workMin',$in)   ? smin($in['workMin'])   : null;
$ot     = array_key_exists('otMin',$in)     ? smin($in['otMin'])     : null;
$oncall = array_key_exists('oncallMin',$in) ? smin($in['oncallMin']) : null;
$km     = array_key_exists('km',$in)        ? skm($in['km'])         : null;
$clear  = !empty($in['clear']);

if ($work===null && $ot===null && $oncall===null && $km===null && !$clear) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"NO_FIELDS"]); exit;
}

/* ===== SQL (corrigido: :d1 e :d2 ao invés de :d repetido) ===== */
$upsertWork = $pdo->prepare("
  INSERT INTO eventos (user_id, titulo, tipo, inicio, fim, minutos, status, source)
  VALUES (:uid, :title, :tipo, CONCAT(:d1,' 00:00:00'), CONCAT(:d2,' 23:59:59'), :min, 'draft', 'manual')
  ON DUPLICATE KEY UPDATE
    minutos=VALUES(minutos), inicio=VALUES(inicio), fim=VALUES(fim),
    status='draft', updated_at=CURRENT_TIMESTAMP
");
$upsertKm = $pdo->prepare("
  INSERT INTO eventos (user_id, titulo, tipo, inicio, fim, km, status, source)
  VALUES (:uid, 'KM', 'KM', CONCAT(:d1,' 00:00:00'), CONCAT(:d2,' 23:59:59'), :km, 'draft', 'manual')
  ON DUPLICATE KEY UPDATE
    km=VALUES(km), status='draft', updated_at=CURRENT_TIMESTAMP
");

/* ===== EXEC ===== */
$updated=[]; $cleared=[];
try{
    $pdo->beginTransaction();

    if($work!==null){
        $upsertWork->execute([':uid'=>$userId, ':title'=>'WORK',    ':tipo'=>'WORK',    ':d1'=>$date, ':d2'=>$date, ':min'=>$work]);
        $updated[]='WORK';
    }
    if($ot!==null){
        $upsertWork->execute([':uid'=>$userId, ':title'=>'OVERTIME',':tipo'=>'OVERTIME',':d1'=>$date, ':d2'=>$date, ':min'=>$ot]);
        $updated[]='OVERTIME';
    }
    if($oncall!==null){
        $upsertWork->execute([':uid'=>$userId, ':title'=>'ONCALL',  ':tipo'=>'ONCALL',  ':d1'=>$date, ':d2'=>$date, ':min'=>$oncall]);
        $updated[]='ONCALL';
    }
    if($km!==null){
        $upsertKm->execute([':uid'=>$userId, ':d1'=>$date, ':d2'=>$date, ':km'=>$km]);
        $updated[]='KM';
    }

    if($clear){
        $sent=['WORK'=>$work!==null,'OVERTIME'=>$ot!==null,'ONCALL'=>$oncall!==null,'KM'=>$km!==null];
        $toClear=array_keys(array_filter($sent, fn($v)=>!$v));
        if($toClear){
            $ph=implode(',', array_fill(0,count($toClear),'?'));
            $sql="DELETE FROM eventos WHERE user_id=? AND dia=? AND tipo IN ($ph) AND status IN ('draft','rejected')";
            $st=$pdo->prepare($sql);
            $st->execute(array_merge([$userId,$date],$toClear));
            $cleared=$toClear;
        }
    }

    $pdo->commit();
    echo json_encode(["ok"=>true,"user_id"=>$userId,"date"=>$date,"updated"=>$updated,"cleared"=>$cleared,"status"=>"draft"]);
}catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
