<?php
// api/subempreiteiros/colab_create.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$user   = $_SESSION['user'];
$userId = (int)($user['id'] ?? 0);

/* ===== DB ===== */
require_once "../includes/db.php";
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Config ===== */
$BASE_UPLOAD_DIR = __DIR__ . '/../../uploads/sub_colaboradores';
$MAX_MB = 15;
$ALLOWED = [
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png'
];

/* ===== Helpers ===== */
function bad_request(string $m, int $code = 400){
    http_response_code($code);
    json_error('BAD_REQUEST', 200, ["message"=>$m]);
}
function s($v){ return is_null($v) ? null : trim((string)$v); }
function ensure_dir($p){ if(!is_dir($p)) mkdir($p,0775,true); }
function save_upload_optional(string $key, string $destDir, array $allowed, int $maxMb): ?string {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$key];
    if ($f['error'] !== UPLOAD_ERR_OK) bad_request("Upload falhou em '$key' (erro {$f['error']}).", 422);
    if ($f['size'] > $maxMb * 1024 * 1024) bad_request("'$key' excede {$maxMb}MB.", 422);
    $mime = mime_content_type($f['tmp_name']);
    if (!isset($allowed[$mime])) bad_request("Tipo inválido em '$key' ($mime). Aceites: PDF/JPG/PNG.", 422);
    ensure_dir($destDir);
    $ext  = $allowed[$mime];
    $name = $key.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.'.$ext;
    $dest = rtrim($destDir, '/') . 'colab_create.php/' .$name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) bad_request("Não consegui gravar '$key'.", 500);
    // caminho relativo guardado na BD
    return 'uploads/sub_colaboradores/'.basename($destDir).'/'.$name;
}
function save_upload_required(string $key, string $destDir, array $allowed, int $maxMb): string {
    $path = save_upload_optional($key, $destDir, $allowed, $maxMb);
    if ($path === null) bad_request("Ficheiro obrigatório em '$key'.", 422);
    return $path;
}

/* ===== Apenas multipart/form-data ===== */
if (!isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === false) {
    bad_request('Esta rota requer multipart/form-data (form-data).');
}

/* ===== Campos de texto ===== */
$nome_colab    = s($_POST['nome_colab'] ?? '');
$nif           = s($_POST['nif'] ?? '');
$categ_prof    = s($_POST['categ_prof'] ?? null);
$form_esp_desc = s($_POST['form_esp_desc'] ?? null);

// validações
if ($nome_colab === '') {
    bad_request('Nome do colaborador é obrigatório.', 422);
}

if ($nif === '' || !preg_match('~^\d{9,20}$~', $nif)) {
    bad_request('NIF obrigatório/inválido (9-20 dígitos).', 422);
}

/* ===== Diretório de uploads do sub ===== */
$subDirName = 'u'.$userId;
$destDir = rtrim($BASE_UPLOAD_DIR, '/') . 'colab_create.php/' .$subDirName;

/* ===== Uploads ===== */
// obrigatórios
$doc_id_path = save_upload_required('doc_id_path', $destDir, $ALLOWED, $MAX_MB);
$cv_ss_path  = save_upload_required('cv_ss_path',  $destDir, $ALLOWED, $MAX_MB);
$fam_path    = save_upload_required('fam_path',    $destDir, $ALLOWED, $MAX_MB);
// opcionais
$entrega_epi_path = save_upload_optional('entrega_epi_path', $destDir, $ALLOWED, $MAX_MB);
$form_esp_path    = save_upload_optional('form_esp_path',    $destDir, $ALLOWED, $MAX_MB);
$ficha_trab_path  = save_upload_optional('ficha_trab_path',  $destDir, $ALLOWED, $MAX_MB);

// helper
function filled($v): bool { return !is_null($v) && trim((string)$v) !== ''; }

// ...depois de teres $nome_colab, $nif, uploads, etc.

$estado = (
    filled($nome_colab) &&
    filled($doc_id_path) &&
    filled($cv_ss_path)  &&
    filled($categ_prof)  &&
    filled($nif)         &&
    filled($fam_path)    &&
    filled($entrega_epi_path) &&
    filled($form_esp_path)    &&
    filled($form_esp_desc)    &&
    filled($ficha_trab_path)
) ? 'completo' : 'incompleto';


/* ===== Insert ===== */
try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO sub_doc_colaboradores
 (sub_user_id, nome_colab, doc_id_path, cv_ss_path, categ_prof, nif, fam_path,
  entrega_epi_path, form_esp_path, form_esp_desc, ficha_trab_path, estado)
VALUES
 (:sub_user_id, :nome_colab, :doc_id_path, :cv_ss_path, :categ_prof, :nif, :fam_path,
  :entrega_epi_path, :form_esp_path, :form_esp_desc, :ficha_trab_path, :estado)";

    $st = $pdo->prepare($sql);
    $st->execute([
        ':sub_user_id'=>$userId,
        ':nome_colab'=>$nome_colab,
        ':doc_id_path'=>$doc_id_path,
        ':cv_ss_path'=>$cv_ss_path,
        ':categ_prof'=>$categ_prof,
        ':nif'=>$nif,
        ':fam_path'=>$fam_path,
        ':entrega_epi_path'=>$entrega_epi_path,
        ':form_esp_path'=>$form_esp_path,
        ':form_esp_desc'=>$form_esp_desc,
        ':ficha_trab_path'=>$ficha_trab_path,
        ':estado'=>$estado
    ]);



    $newId = (int)$pdo->lastInsertId();
    $pdo->commit();

    echo json_encode([
        "ok" => true,
        "id" => $newId,
        "saved" => [
            "sub_user_id"      => $userId,
            "nome_colab" => $nome_colab,
            "doc_id_path"      => $doc_id_path,
            "cv_ss_path"       => $cv_ss_path,
            "categ_prof"       => $categ_prof,
            "nif"              => $nif,
            "fam_path"         => $fam_path,
            "entrega_epi_path" => $entrega_epi_path,
            "form_esp_path"    => $form_esp_path,
            "form_esp_desc"    => $form_esp_desc,
            "ficha_trab_path"  => $ficha_trab_path
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    http_response_code(500);
    json_error('DB_ERROR', 500, ["sqlstate"  => $e->getCode(),
        "errorInfo" => $e->errorInfo,
        "message"   => $e->getMessage()]);
}
