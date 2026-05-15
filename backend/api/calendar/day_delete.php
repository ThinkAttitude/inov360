<?php
// api/calendar/day_delete.php  (versão simples: limpa TUDO do dia)
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== SEGURANÇA ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

$selfId = (int)($_SESSION['user']['id'] ?? 0);

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== HELPERS ===== */
function json_input(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}
function is_valid_date(string $d): bool {
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
}
function period_is_locked(PDO $pdo, int $uid, string $date): bool {
    // Se não usares timesheet_periods, devolve false
    $sql = "SELECT 1 FROM timesheet_periods
          WHERE user_id=:u AND :d BETWEEN period_start AND period_end
            AND estado IN ('submitted','approved','locked')
          LIMIT 1";
    try { $st=$pdo->prepare($sql); $st->execute([':u'=>$uid, ':d'=>$date]); return (bool)$st->fetchColumn(); }
    catch(Throwable $e){ return false; }
}

/* ===== VERBO ===== */
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    json_error('METHOD_NOT_ALLOWED', 405);
}

/* ===== INPUT ===== */
$in   = json_input();
$date = $in['date'] ?? '';
if (!is_valid_date($date)) {
    http_response_code(400);
    json_error('INVALID_DATE');
}

/* User alvo: sessão por defeito; só manager pode indicar outro user_id */
$userId = $selfId;

if (period_is_locked($pdo, $userId, $date)) {
    http_response_code(409);
    json_error('PERIOD_LOCKED', 409);
}

/* ===== EXECUTA: limpa TUDO do dia (WORK, ONCALL, KM) ===== */
/* Se tens a coluna gerada `dia` em eventos, podes trocar DATE(inicio)=? por dia=? */
try {
    $sql = "DELETE FROM eventos
           WHERE user_id = ?
             AND DATE(inicio) = ?
             AND tipo IN ('WORK','ONCALL','KM')
             AND status IN ('draft','rejected')";
    $st = $pdo->prepare($sql);
    $st->execute([$userId, $date]);

    echo json_encode([
        "ok"      => true,
        "user_id" => $userId,
        "date"    => $date,
        "deleted" => $st->rowCount()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    json_error('DB_ERROR', 500, ["msg"=>$e->getMessage()]);}
