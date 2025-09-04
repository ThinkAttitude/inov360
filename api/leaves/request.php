<?php
// api/leaves/request.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Sessão & Roles ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false, "code"=>"UNAUTHENTICATED"]);
    exit;
}
$role = $_SESSION['user']['role'] ?? '';
if ($role === 'estrela') { // única role que não pode pedir
    http_response_code(403);
    echo json_encode(["ok"=>false, "code"=>"FORBIDDEN_ROLE"]);
    exit;
}

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Inputs (form-data) ===== */
$userId        = (int)($_SESSION['user']['id'] ?? 0);
$tipo          = $_POST['tipo']          ?? '';
$data_inicio   = $_POST['data_inicio']   ?? '';
$data_fim      = $_POST['data_fim']      ?? '';
$justificacao  = $_POST['justificacao']  ?? '';
$responsavel_id= isset($_POST['responsavel_id']) && $_POST['responsavel_id'] !== '' ? (int)$_POST['responsavel_id'] : null;

/* Tipos que exigem comprovativo */
$tipos_com_comprovativo = [
    'licenca_paternidade',
    'licenca_maternidade',
    'baixa_medica',
    'baixa_seguro',
    'casamento',
    'consulta_medica'
];

/* ===== Validações ===== */
if (!$tipo || !$data_inicio || !$data_fim || !$justificacao) {
    http_response_code(400);
    echo json_encode(["ok"=>false, "code"=>"MISSING_FIELDS"]);
    exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
    http_response_code(400);
    echo json_encode(["ok"=>false, "code"=>"INVALID_DATE"]);
    exit;
}
$di = DateTime::createFromFormat('Y-m-d', $data_inicio);
$df = DateTime::createFromFormat('Y-m-d', $data_fim);
if (!$di || !$df || $di > $df) {
    http_response_code(400);
    echo json_encode(["ok"=>false, "code"=>"RANGE_ERROR"]);
    exit;
}

/* ===== Upload opcional (ou obrigatório consoante tipo) ===== */
$ficheiro_nome = null;

if (in_array($tipo, $tipos_com_comprovativo, true)) {
    if (!isset($_FILES['ficheiro']) || $_FILES['ficheiro']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["ok"=>false, "code"=>"DOC_REQUIRED"]);
        exit;
    }
}

if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['ficheiro']['tmp_name'];
    $ext  = strtolower(pathinfo($_FILES['ficheiro']['name'], PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    $tipos_permitidos_mime = ['application/pdf','image/jpeg','image/png'];
    $extensoes_permitidas  = ['pdf','jpg','jpeg','png'];

    if (!in_array($mime, $tipos_permitidos_mime, true) || !in_array($ext, $extensoes_permitidas, true)) {
        http_response_code(400);
        echo json_encode(["ok"=>false, "code"=>"BAD_FILETYPE"]);
        exit;
    }
    if (($_FILES['ficheiro']['size'] ?? 0) > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(["ok"=>false, "code"=>"FILE_TOO_LARGE"]);
        exit;
    }

    $uploads = __DIR__ . '/../../uploads';
    if (!is_dir($uploads)) {
        @mkdir($uploads, 0777, true);
    }
    $ficheiro_nome = 'comprovativo_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
    if (!move_uploaded_file($tmp, $uploads . '/' . $ficheiro_nome)) {
        http_response_code(500);
        echo json_encode(["ok"=>false, "code"=>"FILE_MOVE_ERROR"]);
        exit;
    }
}

/* ===== Insert ===== */
try {
    $stmt = $pdo->prepare("
    INSERT INTO pedidos_ferias
      (user_id, tipo, data_inicio, data_fim, justificacao, ficheiro, responsavel_id, estado)
    VALUES
      (:u, :t, :di, :df, :j, :f, :r, 'pendente')
  ");
    $stmt->execute([
        ':u' => $userId,
        ':t' => $tipo,
        ':di'=> $di->format('Y-m-d'),
        ':df'=> $df->format('Y-m-d'),
        ':j' => $justificacao,
        ':f' => $ficheiro_nome,
        ':r' => $responsavel_id
    ]);

    echo json_encode([
        "ok" => true,
        "request_id" => (int)$pdo->lastInsertId()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok"=>false, "code"=>"DB_ERROR", "msg"=>$e->getMessage()]);
}
