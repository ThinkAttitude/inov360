<?php
session_start();
require_once "../includes/db.php";

header('Content-Type: application/json');

// Debug da sessão
$debug_info = [
    "session_started" => session_status() === PHP_SESSION_ACTIVE,
    "is_login" => isset($_SESSION["is_login"]) ? $_SESSION["is_login"] : "not_set",
    "user_role" => isset($_SESSION["user"]["role"]) ? $_SESSION["user"]["role"] : "not_set",
    "session_id" => session_id(),
    "all_session" => $_SESSION
];

try {
    // Tentar conectar à base de dados
    $conn = db_connect();
    $debug_info["db_connection"] = "success";
    
    // Tentar buscar empresas sem verificação de sessão
    $stmt = $conn->prepare("SELECT id, name, slug, logo_path FROM company WHERE active = 1 ORDER BY name ASC");
    $stmt->execute();
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $debug_info["empresas_count"] = count($empresas);
    $debug_info["empresas"] = $empresas;
    
} catch (Exception $e) {
    $debug_info["db_error"] = $e->getMessage();
}

echo json_encode($debug_info, JSON_PRETTY_PRINT);
