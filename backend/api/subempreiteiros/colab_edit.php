<?php
// api/subempreiteiros/colab_edit.php
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
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Config ===== */
$BASE_UPLOAD_DIR = __DIR__ . '/../../uploads/sub_colaboradores'; // garantir permissões
$MAX_MB = 15;
$ALLOWED = [
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png'
];

/* ===== Helpers ===== */
function bad_request(string $m, int $code=400){ http_response_code($code); json_error('BAD_REQUEST', 200, ["message"=>$m]); }
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
    $dest = rtrim($destDir, '/') . 'colab_edit.php/' .$name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) bad_request("Não consegui gravar '$key'.", 500);
    return 'uploads/sub_colaboradores/'.basename($destDir).'/'.$name;
}

function save_upload_replace(
    string $key,
    string $destDir,
    array $allowed,
    int $maxMb,
    ?string $oldRelativePath
): ?string {
    // se não veio ficheiro novo, mantém o antigo
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldRelativePath;
    }

    // guarda o novo ficheiro (usa a tua função atual)
    $newPath = save_upload_optional($key, $destDir, $allowed, $maxMb);

    // se correu bem e havia ficheiro antigo, apaga-o
    if ($newPath && $oldRelativePath) {
        $oldAbs = __DIR__ . '/../INOV360/' . ltrim($oldRelativePath, '/');
        if (is_file($oldAbs)) {
            @unlink($oldAbs);
        }
    }

    return $newPath;
}

function filled($v): bool { return !is_null($v) && trim((string)$v) !== ''; }

/* ===== Apenas multipart/form-data ===== */
if (!isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === false) {
    bad_request('Esta rota requer multipart/form-data (form-data).');
}

/* ===== Input ===== */
$colabId = (int)($_POST['id'] ?? 0);
if ($colabId <= 0) bad_request('Campo "id" é obrigatório.', 422);

// textos (enviados opcionalmente)
$nome_colab    = array_key_exists('nome_colab', $_POST)    ? s($_POST['nome_colab'])    : null;
$nif           = array_key_exists('nif', $_POST)           ? s($_POST['nif'])           : null;
$categ_prof    = array_key_exists('categ_prof', $_POST)    ? s($_POST['categ_prof'])    : null;
$form_esp_desc = array_key_exists('form_esp_desc', $_POST) ? s($_POST['form_esp_desc']) : null;

// validações básicas de texto se vierem
if ($nome_colab !== null && $nome_colab === '') bad_request('nome_colab não pode ser vazio.', 422);
if ($nif !== null && ($nif === '' || !preg_match('~^\d{9,20}$~', $nif))) bad_request('NIF inválido (9–20 dígitos).', 422);

/* ===== Check ownership & get current row ===== */
$st = $pdo->prepare("SELECT * FROM sub_doc_colaboradores WHERE id = :id LIMIT 1");
$st->execute([':id'=>$colabId]);
$cur = $st->fetch(PDO::FETCH_ASSOC);
if (!$cur) { http_response_code(404); json_error('NOT_FOUND', 404, ["message"=>"Colaborador não encontrado."]); }
if ((int)$cur['sub_user_id'] !== $userId) { http_response_code(403); json_error('FORBIDDEN', 403, ["message"=>"Sem acesso a este colaborador."]); }

/* ===== Upload dir ===== */
$destDir = rtrim($BASE_UPLOAD_DIR,'/').'/u'.$userId;

/* ===== Files (opcionais; só substituem se enviados) ===== */
$doc_id_path      = save_upload_replace('doc_id',      $destDir, $ALLOWED, $MAX_MB, $cur['doc_id_path']);
$cv_ss_path       = save_upload_replace('cv_ss',       $destDir, $ALLOWED, $MAX_MB, $cur['cv_ss_path']);
$fam_path         = save_upload_replace('fam',         $destDir, $ALLOWED, $MAX_MB, $cur['fam_path']);
$entrega_epi_path = save_upload_replace('entrega_epi', $destDir, $ALLOWED, $MAX_MB, $cur['entrega_epi_path']);
$form_esp_path    = save_upload_replace('form_esp',    $destDir, $ALLOWED, $MAX_MB, $cur['form_esp_path']);
$ficha_trab_path  = save_upload_replace('ficha_trab',  $destDir, $ALLOWED, $MAX_MB, $cur['ficha_trab_path']);


/* ===== Merge final values ===== */
$final = [
    'nome_colab'       => $nome_colab    !== null ? $nome_colab    : $cur['nome_colab'],
    'nif'              => $nif           !== null ? $nif           : $cur['nif'],
    'categ_prof'       => $categ_prof    !== null ? $categ_prof    : $cur['categ_prof'],
    'form_esp_desc'    => $form_esp_desc !== null ? $form_esp_desc : $cur['form_esp_desc'],
    'doc_id_path'      => $doc_id_path,
    'cv_ss_path'       => $cv_ss_path,
    'fam_path'         => $fam_path,
    'entrega_epi_path' => $entrega_epi_path,
    'form_esp_path'    => $form_esp_path,
    'ficha_trab_path'  => $ficha_trab_path
];

/* ===== Recalcular estado ===== */
$estado = (
    filled($final['nome_colab']) &&
    filled($final['doc_id_path']) &&
    filled($final['cv_ss_path'])  &&
    filled($final['categ_prof'])  &&
    filled($final['nif'])         &&
    filled($final['fam_path'])    &&
    filled($final['entrega_epi_path']) &&
    filled($final['form_esp_path'])    &&
    filled($final['form_esp_desc'])    &&
    filled($final['ficha_trab_path'])
) ? 'completo' : 'incompleto';

/* ===== Persist ===== */
try {
    $pdo->beginTransaction();

    $sql = "UPDATE sub_doc_colaboradores
          SET nome_colab = :nome_colab,
              nif = :nif,
              categ_prof = :categ_prof,
              form_esp_desc = :form_esp_desc,
              doc_id_path = :doc_id_path,
              cv_ss_path = :cv_ss_path,
              fam_path = :fam_path,
              entrega_epi_path = :entrega_epi_path,
              form_esp_path = :form_esp_path,
              ficha_trab_path = :ficha_trab_path,
              estado = :estado
          WHERE id = :id AND sub_user_id = :uid
          LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome_colab'        => $final['nome_colab'],
        ':nif'               => $final['nif'],
        ':categ_prof'        => $final['categ_prof'],
        ':form_esp_desc'     => $final['form_esp_desc'],
        ':doc_id_path'       => $final['doc_id_path'],
        ':cv_ss_path'        => $final['cv_ss_path'],
        ':fam_path'          => $final['fam_path'],
        ':entrega_epi_path'  => $final['entrega_epi_path'],
        ':form_esp_path'     => $final['form_esp_path'],
        ':ficha_trab_path'   => $final['ficha_trab_path'],
        ':estado'            => $estado,
        ':id'                => $colabId,
        ':uid'               => $userId
    ]);

    $pdo->commit();

    echo json_encode([
        "ok" => true,
        "id" => $colabId,
        "estado" => $estado,
        "updated" => array_keys($final)
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    if ($e instanceof PDOException && $e->getCode() === '23000') {
        http_response_code(409);
        json_error('CONFLICT', 409, ["message"=>"NIF em conflito (já existe para este sub)."]);
    }

    http_response_code(500);
    json_error('SERVER_ERROR', 500, ["message"=>$e->getMessage()]);}
