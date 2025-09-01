<?php
session_start();
require_once "../includes/db.php";

// Apenas utilizadores autenticados (opcionalmente podes limitar por role se quiseres)
if (!isset($_SESSION["is_login"])) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Acesso negado."]);
    exit;
}

header('Content-Type: application/json');

try {
    $conn = db_connect();

    $stmt = $conn->prepare("SELECT id, name, slug, logo_path FROM company WHERE active = 1 ORDER BY name ASC");
    $stmt->execute();
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "empresas" => $empresas
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erro ao obter lista de empresas: " . $e->getMessage()
    ]);
}
