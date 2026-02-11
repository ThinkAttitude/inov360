<?php
// api/grupo_inov/associacoes/associate_remove.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(8, $perms, true)) { // sht_management
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN_PERMISSION']); exit;
}

/* ===== DB ===== */
require_once __DIR__ . '/../../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function bad_request(string $m){ http_response_code(422); echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST","message"=>$m]); exit; }
function body(): array {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct,'application/json')!==false) {
        $raw = file_get_contents('php://input'); $d = json_decode($raw,true);
        return is_array($d)?$d:[];
    }
    return $_POST ?: [];
}

/* ===== Input ===== */
$in        = body();
$obraPk    = (int)($in['id']       ?? $_GET['id']       ?? 0);                 // PK novo
$obraNum   = (int)($in['num_obra'] ?? $in['obra_id'] ?? $_GET['num_obra'] ?? $_GET['obra_id'] ?? 0); // legado
$subUserId = (int)($in['sub_user_id'] ?? $in['sub_id'] ?? $in['user_id'] ?? $_GET['sub_user_id'] ?? 0);

if ($subUserId <= 0)                         bad_request('Campo "sub_user_id" é obrigatório.');
if ($obraPk <= 0 && $obraNum <= 0)           bad_request('Forneça "id" (PK) ou "num_obra"/"obra_id".');

try {
    /* ===== Verificações de existência (obra + sub) ===== */
    if ($obraPk > 0) {
        $st = $pdo->prepare('SELECT id, id_obra, nome_obra, unidade FROM obra WHERE id=:id LIMIT 1');
        $st->execute([':id'=>$obraPk]);
    } else {
        $st = $pdo->prepare('SELECT id, id_obra, nome_obra, unidade FROM obra WHERE id_obra=:n LIMIT 1');
        $st->execute([':n'=>$obraNum]);
    }
    $obra = $st->fetch(PDO::FETCH_ASSOC);
    if (!$obra) { http_response_code(404); echo json_encode(["ok"=>false,"code"=>"OBRA_NOT_FOUND"]); exit; }
    $obraPk  = (int)$obra['id'];
    $obraNum = (int)$obra['id_obra'];

    $s = $pdo->prepare('SELECT user_id, nome_empresa, email_contacto, nif FROM subempreiteiro WHERE user_id=:u LIMIT 1');
    $s->execute([':u'=>$subUserId]);
    $sub = $s->fetch(PDO::FETCH_ASSOC);
    if (!$sub) { http_response_code(404); echo json_encode(["ok"=>false,"code"=>"SUB_NOT_FOUND"]); exit; }

    /* ===== Remoção (idempotente) ===== */
    $conds = [];
    $params = [':sub'=>$subUserId];
    if ($obraPk  > 0) { $conds[] = 'obra_pk = :pk';  $params[':pk']  = $obraPk; }
    if ($obraNum > 0) { $conds[] = 'obra_id = :num'; $params[':num'] = $obraNum; }
    $where = '('.implode(' OR ', $conds).')';

    $sqlDel = "DELETE FROM subs_obra WHERE sub_user_id = :sub AND $where LIMIT 1";
    $del = $pdo->prepare($sqlDel);
    $del->execute($params);
    $removed = $del->rowCount() > 0;

    /* ===== Contagem atual de associados ===== */
    $sqlCnt = "SELECT COUNT(*) FROM subs_obra WHERE $where";
    $cnt = $pdo->prepare($sqlCnt);
    $cnt->execute(array_diff_key($params, [':sub'=>true])); // mesmos :pk/:num
    $associados = (int)$cnt->fetchColumn();

    echo json_encode([
        "ok" => true,
        "data" => [
            "removed" => $removed,           // false se não existia associação
            "obra" => [
                "id"         => $obraPk,
                "num_obra"   => $obraNum,
                "nome_obra"  => $obra['nome_obra'],
                "unidade"    => $obra['unidade'],
                "associados" => $associados
            ],
            "empresa" => [
                "user_id"      => (int)$sub['user_id'],
                "nome_empresa" => $sub['nome_empresa'],
                "email"        => $sub['email_contacto'],
                "nif"          => $sub['nif']
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"SERVER_ERROR","detail"=>$e->getMessage()]);
}
