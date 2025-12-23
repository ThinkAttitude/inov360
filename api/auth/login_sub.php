<?php
session_start();

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
$email = trim($input['email'] ?? $input['email_login'] ?? '');
$password = (string)($input['password'] ?? '');

if ($email === '' || $password === '') {
    api_json_error(400, 'MISSING_FIELDS', 'Email e palavra-passe são obrigatórios.');
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('SELECT id, email, password, name FROM user_sub WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        api_json_error(401, 'UNAUTHORIZED', 'Email ou palavra-passe incorretos.');
    }

// Sessão
    $_SESSION['is_login'] = true;
    $_SESSION['user'] = [
        'id'    => (int)$user['id'],
        'email' => $user['email'],
        'name'  => $user['name'],
    ];

    echo json_encode([
        'user' => [
            'id'    => (int)$user['id'],
            'email' => (string)$user['email'],
            'name'  => (string)$user['name'],
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;


} catch (Throwable $e) {
    $requestId = api_request_id();

    api_log_exception($e, $requestId, [
        'endpoint' => 'auth/login_sub.php',
        'email' => $email,
    ]);

    api_json_error(500, 'INTERNAL_ERROR', 'Erro interno do servidor.', $requestId);
}
