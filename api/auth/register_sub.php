<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/api_error.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    api_json_error(405, 'METHOD_NOT_ALLOWED', 'Método não permitido.');
}

function read_input(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST ?: [];
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
    api_json_error(400, 'BAD_REQUEST', 'Email de login inválido.');
}
if (strlen($password) < 8) {
    api_json_error(400, 'BAD_REQUEST', 'Password deve ter pelo menos 8 caracteres.');
}
if ($password !== $confirm_password) {
    api_json_error(400, 'BAD_REQUEST', 'As passwords não coincidem.');
}

// Validações sub
if ($nome_empresa === '' || $nif === '' || $morada === '' || $nome_contacto === '' || $telemovel === '' || $zona_atuacao === '') {
    api_json_error(400, 'BAD_REQUEST', 'Campos obrigatórios do subempreiteiro em falta (nome_empresa, nif, morada, nome_contacto, telemovel, zona_atuacao).');
}
$zonaValid = ['Norte','Centro','Sul','Nacional'];
if (!in_array($zona_atuacao, $zonaValid, true)) {
    api_json_error(400, 'BAD_REQUEST', 'zona_atuacao inválida. Use: Norte, Centro, Sul ou Nacional.');
}


// Nome que vai para a tabela users
$userName = $name;

try {
    $pdo = db_connect();

    // Duplicados
    $stmt = $pdo->prepare('SELECT 1 FROM user_sub WHERE email = ? LIMIT 1');
    $stmt->execute([$email_login]);
    if ($stmt->fetchColumn()) {
        api_json_error(409, 'CONFLIT', 'Já existe um utilizador com esse email.');
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
        'data' => [
            'user_id' => $userId,
            'name'    => $userName
        ]
    ]);
} catch (Throwable $e) {
    $requestId = api_request_id();

    api_log_exception($e, $requestId, [
        'endpoint' => 'auth/register_sub.php',
    ]);

    api_json_error(500, 'INTERNAL_ERROR', 'Erro interno do servidor.', $requestId);
}
