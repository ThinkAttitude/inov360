<?php
// api/subempreiteiros/update_company_doc.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}

$user   = $_SESSION['user'];
$userId = (int)($user['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Config ===== */
$UPLOAD_DIR = __DIR__ . '/../../uploads/subempreiteiros';

$FIELDS_SUB = ['nome_empresa','morada','nif','zona_atuacao'];
$FIELDS_DOC = [
    'atividade','num_alvara','niss_alvara',
    'sat_n_apolice','sat_validade','sat_mod_seguro','sat_comp',
    'src_n_apolice','src_validade','src_comp',
    'dec_ss','dec_finan'
];

$FIELD_LABELS = [
    'sat_validade' => 'Validade do SAT',
    'src_validade' => 'Validade do SRC',
    'nome_empresa' => 'Nome da Empresa',
    'sat_comp' => 'Documento do SAT',
    'src_comp' => 'Documento do SRC',
    'dec_ss' => 'Declaração da Segurança Social',
    'dec_finan' => 'Declaração das Finanças'
];

/* ===== Helpers ===== */
function bad_request(string $m){ http_response_code(400); echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST","message"=>$m]); exit; }
function s($v){ return is_null($v) ? null : trim((string)$v); }
function d($v){
    $v = s($v);
    if ($v===""||$v===null) return null;
    if (preg_match('~^\d{2}/\d{2}/\d{4}$~',$v)){ [$dd,$mm,$yy]=explode('/',$v); return "$yy-$mm-$dd"; }
    if (preg_match('~^\d{4}-\d{2}-\d{2}$~',$v)) return $v;
    return null;
}
function ensure_dir($p){ if(!is_dir($p)) mkdir($p,0775,true); }
function save_upload($key, $uid, $dir): ?string
{
    global $FIELD_LABELS;
    $label = $FIELD_LABELS[$key] ?? $key;

    if (!isset($_FILES[$key]) || $_FILES[$key]['error']===UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$key];
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException("O ficheiro $label excede o tamanho máximo permitido.");
    }
    if ($f['error']!==UPLOAD_ERR_OK) throw new RuntimeException("Upload do $label falhou.");
    $allowed=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'];
    $mime=mime_content_type($f['tmp_name']);
    if(!isset($allowed[$mime])) throw new RuntimeException("O tipo de ficheiro para $label é inválido. Apenas PDF/JPG/PNG são aceites.");
    ensure_dir($dir);
    $name=$key."_u{$uid}_".date('Ymd_His').'.'.$allowed[$mime];
    $dest=rtrim($dir,'/').'/'.$name;
    if(!move_uploaded_file($f['tmp_name'],$dest)) throw new RuntimeException("Não foi possível guardar $label.");
    return 'uploads/subempreiteiros/'.$name;
}

/* ===== Input: SEM user_id ===== */
// aceita JSON ou multipart/form-data
$in = (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'],'application/json'))
    ? (json_decode(file_get_contents('php://input'), true) ?: [])
    : $_POST;

if (!is_array($in)) bad_request('Corpo inválido.');
// se alguém enviar user_id, ignoramos
unset($in['user_id']);
$targetUserId = $userId;

/* ===== Normalização & validação ===== */
$sub = [];
foreach ($FIELDS_SUB as $f) if (array_key_exists($f,$in)) {
    $v = s($in[$f]);
    if ($f==='nif' && $v!==null && $v!=='' && !preg_match('~^\d{9,20}$~',$v)) bad_request('NIF inválido.');
    $sub[$f]=$v;
}

$doc = [];
foreach ($FIELDS_DOC as $f) if (array_key_exists($f,$in)) {
    $v = s($in[$f]);
    if (in_array($f,['sat_validade','src_validade'],true)) {
        $v = d($in[$f]);
        $label = $FIELD_LABELS[$f] ?? $f;

        if ($v === null && $in[$f] !== null && $in[$f] !== '') {
            bad_request("$label inválida (formato esperado: dd-mm-yyyy).");
        }

        if ($v !== null) {
            $dateObj = DateTime::createFromFormat('Y-m-d', $v);
            $today = new DateTime('today');

            if (!$dateObj) {
                bad_request("$label inválida (data inválida).");
            }

            if ($dateObj < $today) {
                bad_request("$label inválida (a data não pode ser anterior a hoje).");
            }

            $maxFuture = (clone $today)->modify('+10 years');
            if ($dateObj > $maxFuture) {
                bad_request("$label inválida (data demasiado distante no futuro).");
            }
        }
    }
    $doc[$f]=$v;
}

// uploads
try {
    foreach (['sat_comp','src_comp','dec_ss','dec_finan'] as $k) {
        if (!empty($_FILES[$k])) {
            $path = save_upload($k, $targetUserId, $UPLOAD_DIR);
            if ($path) $doc[$k] = $path;
        }
    }
} catch (Throwable $e) { bad_request($e->getMessage()); }

if (!$sub && !$doc) bad_request('Sem campos para atualizar.');

/* ===== Persist ===== */
try {
    $pdo->beginTransaction();

    // subempreiteiro
    if ($sub) {
        $exists = $pdo->prepare("SELECT 1 FROM subempreiteiro WHERE user_id=? LIMIT 1");
        $exists->execute([$targetUserId]);
        if ($exists->fetchColumn()) {
            $sets = [];
            $params = [':user_id' => $targetUserId];
            foreach ($sub as $k => $v) {
                $sets[] = "$k=:$k";
                $params[":$k"] = $v;
            }
            $sql = "UPDATE subempreiteiro SET " . implode(',', $sets) . " WHERE user_id=:user_id";
            $pdo->prepare($sql)->execute($params);
        } else {
            $cols = array_keys($sub);
            $place = array_map(fn($c) => ":$c", $cols);
            $sql = "INSERT INTO subempreiteiro (user_id," . implode(',', $cols) . ") VALUES (:user_id," . implode(',', $place) . ")";
            $pdo->prepare($sql)->execute([':user_id' => $targetUserId] + array_combine($place, array_values($sub)));
        }
    }

    // doc_empresas (UPSERT por user_id) — precisa UNIQUE(user_id)
    if ($doc) {
        $cols = array_keys($doc);
        $place = array_map(fn($c) => ":$c", $cols);
        $updates = array_map(fn($c) => "$c=VALUES($c)", $cols);
        $sql = "INSERT INTO sub_doc_empresas (user_id," . implode(',', $cols) . ")
          VALUES (:user_id," . implode(',', $place) . ")
          ON DUPLICATE KEY UPDATE " . implode(',', $updates);
        $pdo->prepare($sql)->execute([':user_id' => $targetUserId] + array_combine($place, array_values($doc)));
    }

    $pdo->commit();
    echo json_encode([
        "ok" => true,
        "user_id" => $targetUserId,
        "updated" => [
            "subempreiteiro" => array_keys($sub),
            "doc_empresas" => array_keys($doc)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) { // Error data truncation for zona_atuacao (value is invalid or too long)
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (
        isset($e->errorInfo[1]) &&
        (int)$e->errorInfo[1] === 1265 &&
        strpos($e->getMessage(), "zona_atuacao") !== false
    ) {
        http_response_code(400);
        echo json_encode([
            "ok"=>false,
            "code"=>"BAD_REQUEST",
            "message"=>"Zona de atuação inválida. Escolha uma das opções disponíveis."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"SERVER_ERROR","message"=>$e->getMessage()]);
} catch (Throwable $e) { // Fallback
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"SERVER_ERROR","message"=>$e->getMessage()]);
}
