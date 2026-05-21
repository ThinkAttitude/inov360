<?php
    declare(strict_types=1);

    session_start();

    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../lib/helper/responses.php';

    if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
        json_error('UNAUTHENTICATED', 401);
    }

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

    // Read Max file limit dynamically from PHP settings (e.g. "5M" or "20M")
    $phpMaxUpload = ini_get('upload_max_filesize');

    if (in_array($tipo, $tipos_com_comprovativo, true)) {
        if (!isset($_FILES['ficheiro']) || $_FILES['ficheiro']['error'] === UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            json_error('DOC_REQUIRED');
        }

        // Checking if PHP natively rejected it as too large before any custom logic runs
        if ($_FILES['ficheiro']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['ficheiro']['error'] === UPLOAD_ERR_FORM_SIZE) {
            http_response_code(400);
            json_error('FILE_TOO_LARGE', 400, [], ['size' => (int)$phpMaxUpload]);
        }

        if ($_FILES['ficheiro']['error'] !== UPLOAD_ERR_OK) {
            error_log('Upload error in leaves/request.php: ' . $_FILES['ficheiro']['error']);
            http_response_code(400);
            json_error('FILE_UPLOAD_ERROR');
        }
    }

    if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] !== UPLOAD_ERR_OK && $_FILES['ficheiro']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['ficheiro']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['ficheiro']['error'] === UPLOAD_ERR_FORM_SIZE) {
            http_response_code(400);
            json_error('FILE_TOO_LARGE', 400, [], ['size' => (int)$phpMaxUpload]);
        }
        error_log('Upload error in leaves/request.php: ' . $_FILES['ficheiro']['error']);
        http_response_code(400);
        json_error('FILE_UPLOAD_ERROR');
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

        // Double check size from what was actually uploaded natively vs evaluated PHP string
        function return_bytes($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val)-1]);
            $val = (int)$val;
            switch($last) {
                case 'g': $val *= 1024;
                case 'm': $val *= 1024;
                case 'k': $val *= 1024;
            }
            return $val;
        }

        $max_bytes = return_bytes($phpMaxUpload);

        if (($_FILES['ficheiro']['size'] ?? 0) > $max_bytes) {
            http_response_code(400);
            json_error('FILE_TOO_LARGE', 400, [], ['size' => (int)$phpMaxUpload]);
        }

        $rootDir = dirname(__DIR__, 3);
        $uploads = $rootDir . '/uploads/leaves';

        if (!is_dir($uploads) && !mkdir($uploads, 0775, true) && !is_dir($uploads)) {
            error_log('Failed to create uploads directory: ' . $uploads);
            json_error('STORAGE_ERROR', 500);
        }

        $ficheiro_nome = 'leaves/comprovativo_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $targetPath = $rootDir . '/uploads/' . $ficheiro_nome;

        if (!move_uploaded_file($tmp, $targetPath)) {
            error_log('Failed to move uploaded file to: ' . $targetPath);
            json_error('FILE_MOVE_ERROR', 500);
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
        error_log('leaves/request.php error: ' . $e->getMessage());
        json_error('DB_ERROR', 500);
    }