<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

require_once "../includes/db.php";

$input = json_decode(file_get_contents('php://input'), true);
$email = $input["email"] ?? '';
$password = $input["password"] ?? '';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Email e palavra-passe são obrigatórios.']);
    exit;
}

try {
    $conn = db_connect();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT id, name, email, password FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password"])) {
        echo json_encode(['success' => false, 'message' => 'Email ou palavra-passe incorretos.']);
        exit;
    }

    $_SESSION["is_login"] = true;
    $_SESSION["user_id"] = (int)$user["id"];

    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso.',
        'user' => [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ]
    ]);
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    exit;
}
