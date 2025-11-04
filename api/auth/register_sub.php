<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

function read_input(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST ?: [];
}
function json_fail(int $status, string $message, array $extra = []): void {
    http_response_code($status);
    echo json_encode(['success' => false, 'message' => $message] + $extra);
    exit;
}

$input = read_input();

$email_login      = trim($input['email'] ?? $input['email_login'] ?? '');
$password         = (string)($input['password'] ?? '');
$confirm_password = (string)($input['confirm_password'] ?? $input['password_confirm'] ?? '');

$name             = trim($input['name'] ?? $input['nome_contacto'] ?? '');

// Subempreiteiro
$nome_empresa   = trim($input['nome_empresa'] ?? '');
$nome_contacto  = trim($input['nome_contacto'] ?? '');
$telemovel      = trim($input['telemovel'] ?? $input['contacto'] ?? '');
$nif            = trim($input['nif'] ?? '');
$morada         = trim($input['morada'] ?? '');
$email_empresa  = trim($input['email_empresa'] ?? $input['email_geral'] ?? '');
$zona_atuacao   = trim($input['zona_atuacao'] ?? ''); // Norte/Centro/Sul/Nacional

// Validações básicas
if ($email_login === '' || !filter_var($email_login, FILTER_VALIDATE_EMAIL)) {
    json_fail(400, 'Email de login inválido.');
}
if (strlen($password) < 8) {
    json_fail(400, 'Password deve ter pelo menos 8 caracteres.');
}
if ($password !== $confirm_password) {
    json_fail(400, 'As passwords não coincidem.');
}

// Validações sub
if ($nome_empresa === '' || $nif === '' || $morada === '' || $nome_contacto === '' || $telemovel === '' || $zona_atuacao === '') {
    json_fail(400, 'Campos obrigatórios do subempreiteiro em falta (nome_empresa, nif, morada, nome_contacto, telemovel, zona_atuacao).');
}
$zonaValid = ['Norte','Centro','Sul','Nacional'];
if (!in_array($zona_atuacao, $zonaValid, true)) {
    json_fail(400, 'zona_atuacao inválida. Use: Norte, Centro, Sul ou Nacional.');
}


// Nome que vai para a tabela users
$userName = $name;

try {
    $pdo = db_connect();

    // Duplicados
    $stmt = $pdo->prepare('SELECT 1 FROM user_sub WHERE email = ? LIMIT 1');
    $stmt->execute([$email_login]);
    if ($stmt->fetchColumn()) {
        json_fail(409, 'Já existe um utilizador com esse email.');
    }

    // Transação
    $pdo->beginTransaction();

    // Inserir user (inclui NAME)
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insUser = $pdo->prepare('INSERT INTO user_sub (name, email, password) VALUES (?, ?, ?)');
    $insUser->execute([$userName, $email_login, $hash]);
    $userId = (int)$pdo->lastInsertId();


    $insSub = $pdo->prepare(
        'INSERT INTO subempreiteiro (user_id, nome_empresa, nif, morada, contacto, nome_contacto, email_contacto, zona_atuacao)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
    $insSub->execute([
        $userId,
        $nome_empresa,
        $nif,
        $morada,
        $telemovel,
        $nome_contacto,
        $email_login,
        $zona_atuacao
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Registo efetuado com sucesso.',
        'data' => [
            'user_id' => $userId,
            'name'    => $userName
        ]
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    json_fail(500, 'Falha ao registar utilizador.', ['erro_debug' => $e]);
}
