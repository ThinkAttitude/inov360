<?php
session_start();
header('Content-Type: application/json;');

require_once "../includes/db.php";

function read_input(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST ?: [];
}
function json_fail(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$input = read_input();
$email = trim($input['email'] ?? $input['email_login'] ?? '');
$password = (string)($input['password'] ?? '');

if ($email === '' || $password === '') {
    json_fail(400, 'Email e palavra-passe são obrigatórios.');
}

try {
    $pdo = db_connect();

    $stmt = $pdo->prepare('SELECT id, email, password, name FROM user_sub WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        json_fail(401, 'Email ou palavra-passe incorretos.');
    }

// Sessão
    $_SESSION['is_login'] = true;
    $_SESSION['user'] = [
        'id'    => (int)$user['id'],
        'email' => $user['email'],
        'name'  => $user['name'],
    ];

    echo json_encode([
        'success'  => true,
        'message'  => 'Login realizado com sucesso.',
        'is_login' => $_SESSION['is_login'],
        'user'     => [
            'id'    => (int)$user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
        ]
    ]);


} catch (Throwable $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e]);
    exit;
}
