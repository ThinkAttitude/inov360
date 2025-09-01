<?php
require_once "../includes/db.php";

header('Content-Type: application/json');

try {
    $conn = db_connect();

    $stmt = $conn->prepare("SELECT id, name, slug, logo_path FROM company WHERE active = 1 ORDER BY name ASC");
    $stmt->execute();
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "empresas" => $empresas,
        "message" => "Empresas carregadas sem verificação de sessão"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erro ao obter lista de empresas: " . $e->getMessage()
    ]);
}
