<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

require_once "../includes/db.php";

if (empty($_SESSION["is_login"]) || empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada ou utilizador não autenticado.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$old_password     = $input["old_password"] ?? '';
$new_password     = $input["new_password"] ?? '';
$confirm_password = $input["confirm_password"] ?? '';

if ($old_password === '' || $new_password === '' || $confirm_password === '') {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'A nova palavra-passe e a confirmação não coincidem.']);
    exit;
}

$user_id = (int) $_SESSION["user_id"];

try {
    $conn = db_connect();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Sessão expirada ou utilizador não autenticado.']);
        exit;
    }

    if (!password_verify($old_password, $user["password"])) {
        echo json_encode(['success' => false, 'message' => 'A palavra-passe atual está incorreta.']);
        exit;
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $stmtUpd = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
    $stmtUpd->execute([$new_hash, $user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Palavra-passe atualizada com sucesso.'
    ]);
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    exit;
}
