<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Verifica se é admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

try {
    $pdo = db_connect();

    // Total de utilizadores
    $stmtUsers = $pdo->query("SELECT COUNT(*) as total FROM user WHERE role = 'employee'");
    $totalUsers = $stmtUsers->fetchColumn();

    // Total de empresas distintas
    $stmtEmpresas = $pdo->query("SELECT COUNT(DISTINCT company) as total FROM user WHERE role = 'employee'");
    $totalEmpresas = $stmtEmpresas->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => [
            'total_utilizadores' => (int)$totalUsers,
            'total_empresas' => (int)$totalEmpresas
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
