<?php
// api/leaves/request.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Sessão ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Inputs (form-data) ===== */
$userId        = (int)($_SESSION['user']['id'] ?? 0);
$tipo          = $_POST['tipo']         ?? '';
$data_inicio   = $_POST['data_inicio']  ?? '';
$data_fim      = $_POST['data_fim']     ?? '';
$justificacao  = $_POST['justificacao'] ?? '';

/* Tipos que exigem comprovativo */
$tipos_com_comprovativo = [
    'licenca_paternidade','licenca_maternidade','baixa_medica','baixa_seguro','casamento','consulta_medica'
];

/* ===== Validações básicas ===== */
if (!$tipo || !$data_inicio || !$data_fim || !$justificacao) {
    http_response_code(400); json_error('MISSING_FIELDS');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
    http_response_code(400); json_error('INVALID_DATE');
}
$di = DateTime::createFromFormat('Y-m-d', $data_inicio);
$df = DateTime::createFromFormat('Y-m-d', $data_fim);
if (!$di || !$df || $di > $df) {
    http_response_code(400); json_error('RANGE_ERROR');
}

/* ===== Confirmar que o colaborador tem responsáveis ativos/válidos ===== */
$hasResp = $pdo->prepare("
  SELECT 1
  FROM colaborador_responsaveis
  WHERE colaborador_id = ?
    AND ativo = 1
    AND (valido_desde IS NULL OR valido_desde <= NOW())
    AND (valido_ate   IS NULL OR valido_ate   >= NOW())
  LIMIT 1
");
$hasResp->execute([$userId]);
if (!$hasResp->fetchColumn()) {
    http_response_code(400);
    json_error('NO_RESPONSAVEIS');
}

/* ===== Upload (obrigatório para certos tipos) ===== */
$ficheiro_nome = null;

if (in_array($tipo, $tipos_com_comprovativo, true)) {
    if (!isset($_FILES['ficheiro']) || $_FILES['ficheiro']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400); json_error('DOC_REQUIRED');
    }
}

if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['ficheiro']['tmp_name'];
    $ext  = strtolower(pathinfo($_FILES['ficheiro']['name'], PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    $okMime = ['application/pdf','image/jpeg','image/png'];
    $okExt  = ['pdf','jpg','jpeg','png'];

    if (!in_array($mime, $okMime, true) || !in_array($ext, $okExt, true)) {
        http_response_code(400); json_error('BAD_FILETYPE');
    }
    if (($_FILES['ficheiro']['size'] ?? 0) > 5*1024*1024) {
        http_response_code(400); json_error('FILE_TOO_LARGE');
    }

    $uploads = __DIR__ . '/../../uploads';
    if (!is_dir($uploads)) { @mkdir($uploads, 0777, true); }
    $ficheiro_nome = 'comprovativo_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
    if (!move_uploaded_file($tmp, $uploads . '/' . $ficheiro_nome)) {
        http_response_code(500); json_error('FILE_MOVE_ERROR');
    }
}

/* ===== Insert (sempre sem responsavel_id; fica NULL) ===== */
try {
    $stmt = $pdo->prepare("
      INSERT INTO pedidos_ferias
        (user_id, tipo, data_inicio, data_fim, justificacao, ficheiro, estado)
      VALUES
        (:u, :t, :di, :df, :j, :f, 'pendente')
    ");
    $stmt->execute([
        ':u' => $userId,
        ':t' => $tipo,
        ':di'=> $di->format('Y-m-d'),
        ':df'=> $df->format('Y-m-d'),
        ':j' => $justificacao,
        ':f' => $ficheiro_nome
    ]);

    echo json_encode([
        "ok" => true,
        "request_id" => (int)$pdo->lastInsertId()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    json_error('DB_ERROR', 500);}
