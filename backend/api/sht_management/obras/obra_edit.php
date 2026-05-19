<?php
// api/grupo_inov/obras/obra_edit.php
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

/* DB */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function bad_request(string $m, int $code=422){
    http_response_code($code);
    json_error('BAD_REQUEST', 200, ["message"=>$m]);
}
function read_input(): array {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }
    return $_POST ?: [];
}

/*
Regra:
- Se vier id (PK), 'num_obra' no JSON é o NOVO número a gravar (opcional).
- Se não vier id, 'num_obra' (ou id_obra) identifica a obra (legado) e também pode ser alterado usando num_obra_edit/id_obra.
*/
$in        = read_input();
$idPk      = (int)($in['id'] ?? 0);                                   // PK novo (preferido)
$idObraIn  = (int)($in['num_obra'] ?? $in['id_obra'] ?? 0);           // número legado
$nomeObra  = trim((string)($in['nome_obra'] ?? ''));
$dirObra   = trim((string)($in['do'] ?? $in['diretor'] ?? ''));
$enc       = trim((string)($in['encarregado'] ?? ''));
$uni       = trim((string)($in['unidade'] ?? ''));

// Quando há PK, num_obra é interpretado como novo número
$wantsChangeNum = ($idPk > 0) && array_key_exists('num_obra', $in);
$newNum = null;
if ($wantsChangeNum) {
    $newNum = (int)$in['num_obra'];
} elseif (array_key_exists('num_obra_edit', $in) || array_key_exists('id_obra', $in)) {
    // Rotas alternativas para mudar número no modo legado
    $newNum = (int)($in['num_obra_edit'] ?? $in['id_obra']);
}

if ($idPk <= 0 && $idObraIn <= 0) bad_request('Forneça "id" (PK) ou "num_obra"/"id_obra".');
if ($nomeObra === '')             bad_request('Campo "nome_obra" é obrigatório.');
if ($dirObra === '')              bad_request('Campo "do" (Diretor de Obra) é obrigatório.');

try {
    // 1) Carregar a obra atual (preferir PK)
    if ($idPk > 0) {
        $sel = $pdo->prepare('SELECT id, id_obra FROM obra WHERE id = :id LIMIT 1');
        $sel->execute([':id' => $idPk]);
    } else {
        $sel = $pdo->prepare('SELECT id, id_obra FROM obra WHERE id_obra = :num LIMIT 1');
        $sel->execute([':num' => $idObraIn]);
    }
    $obra = $sel->fetch(PDO::FETCH_ASSOC);
    if (!$obra) {
        http_response_code(404);
        json_error('NOT_FOUND', 404, ["message"=>"Obra não encontrada."]);
    }

    $currentId   = (int)$obra['id'];
    $currentNum  = (int)$obra['id_obra'];

    // 2) Determinar número final (com validação de unicidade se mudou)
    $finalNum = $currentNum;
    if ($newNum !== null && $newNum !== $currentNum) {
        // verificar se já existe outra obra com esse número
        $chk = $pdo->prepare('SELECT COUNT(*) FROM obra WHERE id_obra = :num AND id <> :id');
        $chk->execute([':num' => $newNum, ':id' => $currentId]);
        if ((int)$chk->fetchColumn() > 0) {
            bad_request('Já existe uma obra com esse número.');
        }
        $finalNum = $newNum;
    }

    // 3) UPDATE (sempre por PK que já conhecemos)
    $up = $pdo->prepare('
        UPDATE obra
           SET id_obra = :num,
               nome_obra = :nome,
               `do` = :do,
               encarregado = :enc,
               unidade = :uni
         WHERE id = :id
         LIMIT 1
    ');
    $up->execute([
        ':num'  => $finalNum,
        ':nome' => $nomeObra,
        ':do'   => $dirObra,
        ':enc'  => ($enc !== '' ? $enc : null),
        ':uni'  => ($uni !== '' ? $uni : null),
        ':id'   => $currentId
    ]);

    echo json_encode([
        "ok"   => true,
        "data" => [
            "id"          => $currentId,
            "num_obra"    => $finalNum,
            "nome_obra"   => $nomeObra,
            "do"          => $dirObra,
            "encarregado" => ($enc !== '' ? $enc : null),
            "unidade"     => ($uni !== '' ? $uni : null)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    json_error('SERVER_ERROR', 500, ["detail"=>$e->getMessage()]);}
