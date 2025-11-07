<?php
// api/grupo_inov/associacoes/associate_add.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "code" => "UNAUTHENTICATED"]);
    exit;
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
function bad_request(string $m): void {
    http_response_code(422);
    echo json_encode(["ok" => false, "code" => "BAD_REQUEST", "message" => $m]);
    exit;
}

function get_json_body(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST ?: [];
}

function is_empty(?string $v): bool {
    return $v === null || trim($v) === '';
}

function expired(?string $d): bool {
    if (is_empty($d)) return true;
    $ts = strtotime($d);
    return $ts === false || $ts < strtotime('today');
}

/* ===== Input ===== */
$input = get_json_body();

$fields = [
    'obra_pk'    => (int)($input['obra_pk'] ?? 0),
    'obra_id'    => (int)($input['obra_id'] ?? 0),
    'sub_user_id'=> (int)($input['sub_user_id'] ?? 0)
];

if ($fields['obra_pk'] <= 0 && $fields['obra_id'] <= 0) {
    bad_request('Forneça "obra_pk" ou "obra_id".');
}

if ($fields['sub_user_id'] <= 0) {
    bad_request('Campo "sub_user_id" é obrigatório.');
}

try {
    /* Verificar existência da obra */
    $obraQuery = $fields['obra_pk'] > 0
        ? 'SELECT id, id_obra, nome_obra, unidade FROM obra WHERE id = :id LIMIT 1'
        : 'SELECT id, id_obra, nome_obra, unidade FROM obra WHERE id_obra = :id LIMIT 1';

    $obraStmt = $pdo->prepare($obraQuery);
    $obraStmt->execute([':id' => $fields['obra_pk'] > 0 ? $fields['obra_pk'] : $fields['obra_id']]);
    $obra = $obraStmt->fetch(PDO::FETCH_ASSOC);

    if (!$obra) {
        http_response_code(404);
        echo json_encode(["ok" => false, "code" => "OBRA_NOT_FOUND"]);
        exit;
    }

    $fields['obra_pk']  = (int)$obra['id'];
    $fields['obra_id']  = (int)$obra['id_obra'];

    /* Verificar existência do subempreiteiro */
    $subStmt = $pdo->prepare('SELECT user_id, nome_empresa, email_contacto, nif FROM subempreiteiro WHERE user_id = :id LIMIT 1');
    $subStmt->execute([':id' => $fields['sub_user_id']]);
    $sub = $subStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sub) {
        http_response_code(404);
        echo json_encode(["ok" => false, "code" => "SUB_NOT_FOUND"]);
        exit;
    }

    /* Inserir associação (idempotente) */
    $insert = $pdo->prepare('
        INSERT INTO subs_obra (obra_pk, obra_id, sub_user_id)
        VALUES (:obra_pk, :obra_id, :sub_user_id)
    ');
    $created = true;

    try {
        $insert->execute([
            ':obra_pk'    => $fields['obra_pk'],
            ':obra_id'    => $fields['obra_id'],
            ':sub_user_id'=> $fields['sub_user_id']
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { // Duplicate key
            $created = false;
        } else {
            throw $e;
        }
    }

    /* Verificar documentos da empresa */
    $docStmt = $pdo->prepare('
        SELECT sat_comp, sat_n_apolice, sat_validade, sat_mod_seguro,
               src_comp, src_n_apolice, src_validade, dec_ss, dec_finan
        FROM sub_doc_empresas WHERE user_id = :id LIMIT 1
    ');
    $docStmt->execute([':id' => $fields['sub_user_id']]);
    $docs = $docStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $faltas = [];
    if (is_empty($docs['sat_comp'] ?? null) || is_empty($docs['sat_n_apolice'] ?? null) || expired($docs['sat_validade'] ?? null) || is_empty($docs['sat_mod_seguro'] ?? null)) {
        $faltas[] = 'Seguro AT';
    }
    if (is_empty($docs['src_comp'] ?? null) || is_empty($docs['src_n_apolice'] ?? null) || expired($docs['src_validade'] ?? null)) {
        $faltas[] = 'Seguro RC';
    }
    if (is_empty($docs['dec_ss'] ?? null)) $faltas[] = 'Declaração SS';
    if (is_empty($docs['dec_finan'] ?? null)) $faltas[] = 'Declaração Finanças';

    $estadoEmpresa = empty($faltas) ? 'ok' : 'empresa_em_falta';

    /* Nº de associados */
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM subs_obra WHERE obra_pk = :obra_pk');
    $countStmt->execute([':obra_pk' => $fields['obra_pk']]);
    $associados = (int)$countStmt->fetchColumn();

    echo json_encode([
        "ok" => true,
        "data" => [
            "created" => $created,
            "obra" => [
                "id" => $fields['obra_pk'],
                "num_obra" => $fields['obra_id'],
                "nome_obra" => $obra['nome_obra'],
                "unidade" => $obra['unidade'],
                "associados" => $associados
            ],
            "empresa" => [
                "user_id" => (int)$sub['user_id'],
                "nome_empresa" => $sub['nome_empresa'],
                "email" => $sub['email_contacto'],
                "nif" => $sub['nif'],
                "estado" => $estadoEmpresa,
                "em_falta" => $faltas
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "code" => "SERVER_ERROR", "detail" => $e->getMessage()]);
}