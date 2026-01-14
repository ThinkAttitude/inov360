<?php
// api/leaves/direct_leave.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Sessão & Role ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}

$myPerms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($myPerms) || !in_array(2, $myPerms, true)) { // exige permissão 2
    http_response_code(403);
    echo json_encode(['ok'=>false,'code'=>'FORBIDDEN_PERMISSION']); exit;
}

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function norm_date(?string $v): ?string {
    if (!$v) return null;
    $v = trim($v);
    // dd/mm/aaaa -> Y-m-d
    if (preg_match('~^(\d{2})/(\d{2})/(\d{4})$~', $v, $m)) {
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    }
    // Y-m-d
    if (preg_match('~^\d{4}-\d{2}-\d{2}$~', $v)) return $v;
    return null;
}
function fail(int $http, string $code, array $extra=[]): void {
    http_response_code($http);
    echo json_encode(["ok"=>false,"code"=>$code] + $extra);
    exit;
}

/* ===== Input (multipart/form-data) ===== */
$userId       = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$tipo         = isset($_POST['tipo']) ? trim((string)$_POST['tipo']) : '';
$dataInicio   = norm_date($_POST['data_inicio'] ?? null);
$dataFim      = norm_date($_POST['data_fim'] ?? null);
$justificacao = isset($_POST['justificacao']) ? trim((string)$_POST['justificacao']) : '';

if ($userId <= 0 || $tipo === '' || !$dataInicio || !$dataFim) {
    fail(400, "BAD_REQUEST", ["msg"=>"Campos obrigatórios: user_id, tipo, data_inicio, data_fim"]);
}
if ($dataInicio > $dataFim) {
    fail(400, "BAD_DATES", ["msg"=>"data_inicio não pode ser posterior a data_fim"]);
}

/* ===== Comprovativo (OBRIGATÓRIO) ===== */
if (empty($_FILES['ficheiro']) || ($_FILES['ficheiro']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    fail(400, "FILE_REQUIRED");
}
$f = $_FILES['ficheiro'];
if ($f['error'] !== UPLOAD_ERR_OK) {
    fail(400, "FILE_UPLOAD_ERROR", ["php_upload_error"=>$f['error']]);
}
$maxBytes = 5 * 1024 * 1024; // 5MB
if ($f['size'] > $maxBytes) {
    fail(400, "FILE_TOO_LARGE", ["limit"=>"5MB"]);
}
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$allowedExt = ['pdf','jpg','jpeg','png'];
if (!in_array($ext, $allowedExt, true)) {
    fail(400, "FILE_INVALID_TYPE", ["allowed"=>"pdf,jpg,jpeg,png"]);
}

/* ===== Validação de TIPO (ler ENUM da tabela para evitar erro 1265) ===== */
try {
    $col = $pdo->query("SHOW COLUMNS FROM pedidos_ferias LIKE 'tipo'")->fetch(PDO::FETCH_ASSOC);
    if ($col && preg_match("/^enum\((.*)\)$/i", $col['Type'] ?? '', $m)) {
        $allowed = array_map(function($v){ return trim($v, " '"); }, explode(',', $m[1]));
        if (!in_array($tipo, $allowed, true)) {
            fail(400, "INVALID_TIPO", ["allowed"=>$allowed]);
        }
    }
} catch (Throwable $e) {
    // se der erro, seguimos sem bloquear; o INSERT acusará se for inválido
}

/* ===== Guardar ficheiro ===== */
$rootDir    = dirname(__DIR__, 2);              // /var/www/html
$relDir     = '/uploads/leaves/' . date('Y') . '/' . date('m');
$absDir     = $rootDir . $relDir;
if (!is_dir($absDir) && !mkdir($absDir, 0775, true) && !is_dir($absDir)) {
    fail(500, "STORAGE_ERROR", ["msg"=>"Não foi possível criar diretório de uploads"]);
}
$slugBase   = preg_replace('~[^a-z0-9]+~i', '-', pathinfo($f['name'], PATHINFO_FILENAME));
$fname      = $slugBase . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
$absPath    = $absDir . '/' . $fname;
$relPath    = $relDir . '/' . $fname;          // o que vai para a BD (caminho relativo web)

if (!move_uploaded_file($f['tmp_name'], $absPath)) {
    fail(500, "STORAGE_ERROR", ["msg"=>"Falha ao mover ficheiro"]);
}

/* ===== Criar pedido + eventos ===== */
try {
    $pdo->beginTransaction();

    // inserir pedido (já aprovado)
    $insPed = $pdo->prepare("
        INSERT INTO pedidos_ferias
            (user_id, tipo, data_inicio, data_fim, justificacao, ficheiro, estado, criado_em, decidido_por, comentario, responsavel_id)
        VALUES
            (:u, :t, :di, :df, :j, :f, 'aprovado', NOW(), :decisor, 'Marcação direta RH', NULL)
    ");
    $insPed->execute([
        ':u'       => $userId,
        ':t'       => $tipo,
        ':di'      => $dataInicio,
        ':df'      => $dataFim,
        ':j'       => $justificacao,
        ':f'       => $relPath,
        ':decisor' => (int)$_SESSION['user']['id'],
    ]);
    $pedidoId = (int)$pdo->lastInsertId();

    // evitar duplicados (respeita índice unique leave_request_id,tipo)
    $pdo->prepare("DELETE FROM eventos WHERE leave_request_id=:rid")->execute([':rid'=>$pedidoId]);

    // evento LEAVE
    $insEvt = $pdo->prepare("
        INSERT INTO eventos
            (user_id, titulo, tipo, inicio, fim, minutos, km, status, source, leave_request_id, period_id, created_at, updated_at)
        VALUES
            (:u, :title, 'LEAVE',
             CONCAT(:di,' 00:00:00'), CONCAT(:df,' 23:59:59'),
             NULL, NULL, 'approved', 'approval', :rid, NULL, NOW(), NOW())
    ");
    $insEvt->execute([
        ':u'     => $userId,
        ':title' => $tipo,
        ':di'    => $dataInicio,
        ':df'    => $dataFim,
        ':rid'   => $pedidoId,
    ]);

    // zerar minutos de eventos de trabalho que colidem
    $zero = $pdo->prepare("
        UPDATE eventos
           SET minutos = CASE WHEN tipo IN ('WORK','OVERTIME','ONCALL') THEN 0 ELSE minutos END,
               updated_at = NOW()
         WHERE user_id = :u
           AND tipo IN ('WORK','OVERTIME','ONCALL')
           AND DATE(inicio) <= :df
           AND DATE(fim)    >= :di
    ");
    $zero->execute([
        ':u'  => $userId,
        ':di' => $dataInicio,
        ':df' => $dataFim,
    ]);

    // retornar evento
    $sel = $pdo->prepare("
        SELECT id, user_id, titulo, tipo, inicio, fim, status, leave_request_id, source
          FROM eventos
         WHERE leave_request_id=:rid AND tipo='LEAVE'
         ORDER BY id DESC LIMIT 1
    ");
    $sel->execute([':rid'=>$pedidoId]);
    $evento = $sel->fetch(PDO::FETCH_ASSOC) ?: null;

    $pdo->commit();

    echo json_encode([
        "ok"        => true,
        "pedido_id" => $pedidoId,
        "ficheiro"  => $relPath,
        "evento"    => $evento
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // tentativa de rollback do ficheiro se falhar a BD
    if (is_file($absPath)) @unlink($absPath);
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
