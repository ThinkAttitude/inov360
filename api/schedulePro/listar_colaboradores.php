<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
    exit;
}

try {
    $conn = db_connect();

    $stmt = $conn->prepare("SELECT id, name, email, company FROM user WHERE role = 'employee' ORDER BY name ASC");
    $stmt->execute();
    $colaboradores = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'colaboradores' => $colaboradores
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor.',
        'error' => $e->getMessage()
    ]);
}
