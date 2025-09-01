<?php
session_start();
require_once "includes/db.php";

header('Content-Type: application/json');

try {
    if (!isset($_SESSION["is_login"]) || !isset($_SESSION["user"]["id"])) {
        http_response_code(401);
        echo json_encode(["sucesso" => false, "mensagem" => "Não autenticado."]);
        exit;
    }

    $pdo = db_connect();
    $userId = $_SESSION["user"]["id"];

    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.role,
               c.name AS company_name, c.slug, c.logo_path
        FROM user u
        LEFT JOIN company c ON c.id = u.company_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
        throw new Exception("Utilizador não encontrado.");
    }

    $baseUrl = (!empty($_SERVER['HTTPS']) ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";

    echo json_encode([
        "sucesso" => true,
        "user" => [
            "id"      => $dados['id'],
            "name"    => $dados['name'],
            "email"   => $dados['email'],
            "role"    => $dados['role'],
            "company" => [
                "name" => $dados['company_name'],
                "slug" => $dados['slug'],
                "logo" => $dados['logo_path'] ? $baseUrl . $dados['logo_path'] : null
            ]
        ]
    ]);

} catch (Exception $e) {
    error_log("ERRO ME: " . $e->getMessage());
    echo json_encode(["sucesso" => false, "mensagem" => $e->getMessage()]);
    exit;
}
