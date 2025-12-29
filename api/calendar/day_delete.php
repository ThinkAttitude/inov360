<?php
session_start();

require_once __DIR__ . '/../includes/api_error.php';
$requestId = api_request_id();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    api_json_error(405, 'METHOD_NOT_ALLOWED', 'Método não permitido.');
}

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    api_json_error(401, 'UNAUTHORIZED', 'Unauthenticated.');
}

$selfId = (int)($_SESSION['user']['id'] ?? 0);

require_once "../includes/db.php";
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// helpers
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
    catch(Throwable $e){
        if ($pdo->inTransaction()) $pdo->rollBack();

        $requestId = api_request_id();

        api_log_exception($e, $requestId, [
            'endpoint' => '.../day_delete.php',
        ]);

        api_json_error(500, 'INTERNAL_ERROR', 'Ocorreu um erro inesperado.', $requestId);
        return false; }
}

// input
$in   = json_input();
$date = $in['date'] ?? '';
if (!is_valid_date($date)) {
    api_json_error(400, 'BAD_REQUEST', 'Invalid date.');
}

/* User alvo: sessão por defeito; só manager pode indicar outro user_id */
$userId = $selfId;

if (period_is_locked($pdo, $userId, $date)) {
    api_json_error(409, 'CONFLIT', 'Periods Locked.');
}

// exec
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
    if ($pdo->inTransaction()) $pdo->rollBack();

    api_log_exception($e, $requestId, [
        'endpoint' => '.../day_delete.php',
    ]);

    api_json_error(500, 'INTERNAL_ERROR', 'Ocorreu um erro inesperado.', $requestId);
}
