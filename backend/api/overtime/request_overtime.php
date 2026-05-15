<?php
// api/overtime/request_overtime.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../lib/helper/periods.php';

/* === Auth === */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$meId    = (int)($_SESSION['user']['id'] ?? 0);
$myPerms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($myPerms) || !in_array(4, $myPerms, true)) { // perm 4: request_overtime
    http_response_code(403);
    json_error('FORBIDDEN_PERMISSION', 403);
}

/* === DB === */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* === Helpers === */
function json_input(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}
function is_valid_date(string $d): bool {
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
}
function is_valid_time(string $t): bool {
    return (bool)preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t); // HH:MM 24h
}

/* === Input === */
$in = json_input();
$userId       = isset($in['user_id']) ? (int)$in['user_id'] : 0;
$dia          = isset($in['dia']) ? trim((string)$in['dia']) : '';
$horaIni      = isset($in['hora_inicio']) ? trim((string)$in['hora_inicio']) : '';
$horaFim      = isset($in['hora_fim']) ? trim((string)$in['hora_fim']) : '';
$justificacao = trim((string)($in['justificacao'] ?? ''));
$ficheiro     = isset($in['ficheiro']) ? trim((string)$in['ficheiro']) : null; // referência/URL (não binário)

/* === Validações === */
if ($userId <= 0 || $dia === '' || $horaIni === '' || $horaFim === '' || $justificacao === '') {
    http_response_code(400);
    json_error('MISSING_FIELDS');
}
if (!is_valid_date($dia)) {
    http_response_code(400);
    json_error('INVALID_DATE');
}
if (!is_valid_time($horaIni) || !is_valid_time($horaFim)) {
    http_response_code(400);
    json_error('INVALID_TIME_FORMAT');
}

// Bloquear Pedidos
if (!ot_is_open_for_day($dia)) {
    http_response_code(409);
    json_error('OVERTIME_CLOSED');
}


$inicio = $dia . ' ' . $horaIni . ':00';
$fim    = $dia . ' ' . $horaFim . ':00';

$tsIni = strtotime($inicio);
$tsFim = strtotime($fim);
if ($tsIni === false || $tsFim === false || $tsFim <= $tsIni) {
    http_response_code(400);
    json_error('INVALID_TIME_RANGE');
}

/* granularidade: 15 min */
$mins = (int)round(($tsFim - $tsIni) / 60);
if ($mins % 15 !== 0) {
    http_response_code(400);
    json_error('INVALID_GRANULARITY_15MIN');
}
/* limite diário opcional: 12h */
if ($mins > 12*60) {
    http_response_code(400);
    json_error('MAX_HOURS_EXCEEDED');
}

/* === Exec === */
try {
    $pdo->beginTransaction();

    // user existe?
    $chkUser = $pdo->prepare("SELECT 1 FROM user WHERE id=? LIMIT 1");
    $chkUser->execute([$userId]);
    if (!$chkUser->fetchColumn()) {
        $pdo->rollBack();
        http_response_code(404);
        json_error('USER_NOT_FOUND', 404);
    }

    // sobreposição com pedidos PENDENTES (requested) no mesmo user
    $pend = $pdo->prepare("
        SELECT 1
          FROM request_overtime
         WHERE user_id = :u
           AND estado = 'requested'
           AND NOT (data_fim <= :ini OR data_inicio >= :fim)
         LIMIT 1
    ");
    $pend->execute([':u'=>$userId, ':ini'=>$inicio, ':fim'=>$fim]);
    if ($pend->fetchColumn()) {
        $pdo->rollBack();
        http_response_code(409);
        json_error('DUPLICATE_REQUEST_OVERLAP');
    }

    // sobreposição com overtime APROVADO existente
    $aprov = $pdo->prepare("
        SELECT 1
          FROM overtime
         WHERE user_id = :u
           AND NOT (fim <= :ini OR inicio >= :fim)
         LIMIT 1
    ");
    $aprov->execute([':u'=>$userId, ':ini'=>$inicio, ':fim'=>$fim]);
    if ($aprov->fetchColumn()) {
        $pdo->rollBack();
        http_response_code(409);
        json_error('ALREADY_HAS_APPROVED_OVERTIME');
    }

    // inserir pedido
    $ins = $pdo->prepare("
        INSERT INTO request_overtime
          (user_id, data_inicio, data_fim, justificacao, ficheiro, estado, criado_em, criado_por)
        VALUES
          (:u, :ini, :fim, :jus, :fic, 'requested', NOW(), :by)
    ");
    $ins->execute([
        ':u'=>$userId, ':ini'=>$inicio, ':fim'=>$fim,
        ':jus'=>$justificacao, ':fic'=>$ficheiro, ':by'=>$meId
    ]);
    $id = (int)$pdo->lastInsertId();

    // devolver a linha (com minutos calculados)
    $row = $pdo->prepare("
        SELECT id, user_id, data_inicio, data_fim,
               TIMESTAMPDIFF(MINUTE, data_inicio, data_fim) AS minutos,
               DATE(data_inicio) AS dia,
               estado, criado_em, criado_por
          FROM request_overtime
         WHERE id = ?
         LIMIT 1
    ");
    $row->execute([$id]);
    $req = $row->fetch(PDO::FETCH_ASSOC) ?: [];

    $pdo->commit();

    echo json_encode([
        'ok'      => true,
        'request' => [
            'id'           => (int)$req['id'],
            'user_id'      => (int)$req['user_id'],
            'dia'          => $req['dia'],
            'hora_inicio'  => substr($req['data_inicio'], 11, 5),
            'hora_fim'     => substr($req['data_fim'], 11, 5),
            'data_inicio'  => $req['data_inicio'],
            'data_fim'     => $req['data_fim'],
            'minutos'      => (int)$req['minutos'],
            'estado'       => $req['estado'],
            'criado_em'    => $req['criado_em'],
            'criado_por'   => (int)$req['criado_por'],
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    json_error('DB_ERROR', 500, ['msg'=>$e->getMessage()]);}
