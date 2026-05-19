<?php
// api/grupo_inov/obras/obra_create.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$user = $_SESSION['user'];

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(8, $perms, true)) { // sht_management
    http_response_code(403);
    json_error('FORBIDDEN_PERMISSION', 403);
}

/* ===== DB ===== */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function bad_request(string $m, int $code=422){ http_response_code(422); json_error('BAD_REQUEST', 200, ["message"=>$m]); }
function read_input(): array {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST ?: [];
}

/* ===== Input =====
Expect:
- num_obra   -> inteiro > 0 (será usado em id_obra)
- nome_obra  -> string obrigatória
- do         -> string obrigatória (Diretor de Obra)
- encarregado-> string opcional
- unidade    -> string opcional
*/
$in = read_input();
$idObra      = (int)($in['num_obra'] ?? 0);
$nomeObra    = trim((string)($in['nome_obra'] ?? ''));
$dirObra     = trim((string)($in['do'] ?? ''));
$encarregado = trim((string)($in['encarregado'] ?? ''));
$unidade     = trim((string)($in['unidade'] ?? ''));

if ($idObra <= 0)                 bad_request('Campo "num_obra" é obrigatório e deve ser > 0.');
if ($nomeObra === '')             bad_request('Campo "nome_obra" é obrigatório.');
if ($dirObra === '')              bad_request('Campo "do" (Diretor de Obra) é obrigatório.');

/* ===== Create ===== */
try {
    // verificar duplicado
    $chk = $pdo->prepare('SELECT 1 FROM obra WHERE id_obra = :id LIMIT 1');
    $chk->execute([':id' => $idObra]);
    if ($chk->fetchColumn()) {
        http_response_code(409);
        json_error('ALREADY_EXISTS', 200, ["message"=>"Já existe uma obra com esse número."]);
    }

    $sql = "INSERT INTO obra (id_obra, nome_obra, do, encarregado, unidade)
            VALUES (:id_obra, :nome_obra, :do, :encarregado, :unidade)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_obra'     => $idObra,
        ':nome_obra'   => $nomeObra,
        ':do'          => $dirObra,
        ':encarregado' => ($encarregado !== '' ? $encarregado : null),
        ':unidade'     => ($unidade !== '' ? $unidade : null),
    ]);

    echo json_encode([
        "ok"   => true,
        "data" => [
            "id_obra"     => $idObra,
            "nome_obra"   => $nomeObra,
            "do"          => $dirObra,
            "encarregado" => ($encarregado !== '' ? $encarregado : null),
            "unidade"     => ($unidade !== '' ? $unidade : null),
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    json_error('SERVER_ERROR', 500, ["detail"=>$e->getMessage()]);}
