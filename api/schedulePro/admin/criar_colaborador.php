<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

// Apenas admin pode aceder
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado.'
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
    exit;
}

// Suporte para JSON e form-urlencoded
$data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$company = trim($data['company'] ?? '');

// Role fixada como employee
$role = 'employee';

// Validação
if (empty($name) || empty($email) || empty($password) || empty($company)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Nome, email, palavra-passe e empresa são obrigatórios.'
    ]);
    exit;
}

try {
    $conn = db_connect();

    // Verificar duplicação de email
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Email já está em uso.'
        ]);
        exit;
    }

    // Inserir novo colaborador
    $stmt = $conn->prepare("
        INSERT INTO user (name, email, password, role, company)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role,
        $company
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Colaborador criado com sucesso.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor.'
    ]);
}
