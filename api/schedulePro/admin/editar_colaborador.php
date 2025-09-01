<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

// Apenas admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? null) !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // permite POST caso seja difícil enviar PUT do front
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido. Use PUT.']);
    exit;
}

// Body (JSON ou form)
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) $in = $_POST;

$id       = isset($in['id']) ? (int)$in['id'] : 0;
$name     = trim($in['name']    ?? '');
$email    = trim($in['email']   ?? '');
$company  = trim($in['company'] ?? '');
$password = $in['password']     ?? null; // opcional

if ($id <= 0 || $name === '' || $email === '' || $company === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios: id, name, email, company.']);
    exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Existe?
    $stmt = $pdo->prepare('SELECT id, email FROM user WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Colaborador não encontrado.']);
        exit;
    }

    // Email já usado por outro?
    $stmt = $pdo->prepare('SELECT id FROM user WHERE email = ? AND id <> ?');
    $stmt->execute([$email, $id]);
    if ($stmt->fetchColumn()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email já está em uso por outro colaborador.']);
        exit;
    }

    // Montar UPDATE (com ou sem password)
    if ($password !== null && $password !== '') {
        $stmt = $pdo->prepare('
            UPDATE user
               SET name = ?, email = ?, company = ?, password = ?
             WHERE id = ?
        ');
        $stmt->execute([$name, $email, $company, password_hash($password, PASSWORD_DEFAULT), $id]);
    } else {
        $stmt = $pdo->prepare('
            UPDATE user
               SET name = ?, email = ?, company = ?
             WHERE id = ?
        ');
        $stmt->execute([$name, $email, $company, $id]);
    }

    echo json_encode(['success' => true, 'message' => 'Colaborador atualizado com sucesso.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
}
