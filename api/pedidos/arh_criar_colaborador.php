<?php
session_start();
require_once "../includes/db.php";

// Validação de Role
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Acesso negado."]);
    exit;
}

header('Content-Type: application/json');

// Debug: Log dos dados recebidos
error_log("=== DEBUG CRIAR COLABORADOR ===");
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION data: " . print_r($_SESSION, true));

try {
    $pdo = db_connect();

    $nome       = trim($_POST['nome'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $role       = trim($_POST['role'] ?? '');
    $senha      = $_POST['senha'] ?? null; // senha não sofre trim
    $company_id = intval($_POST['company_id'] ?? 0);

    error_log("Dados processados - Nome: $nome, Email: $email, Role: $role, Empresa: $company_id");

    // Validação geral
    if (!$nome || !$email || !$role || !$senha || $company_id <= 0) {
        throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido.");
    }

    if (!in_array($role, ['opera', 'inter2', 'inter', 'admin'])) {
        throw new Exception("Role inválido.");
    }

    // Verificar se empresa existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM company WHERE id = ?");
    $stmt->execute([$company_id]);
    if ($stmt->fetchColumn() === 0) {
        throw new Exception("Empresa não encontrada.");
    }

    // Verifica se já existe email
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception("Já existe um utilizador com este email.");
    }

    error_log("Verificações passaram, criando utilizador...");

    // Cria utilizador
    $hash = password_hash($senha, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO user (name, email, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
    $result = $stmt->execute([$nome, $email, $hash, $role, $company_id]);

    if (!$result) {
        error_log("Erro ao inserir user: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Erro ao criar utilizador na base de dados.");
    }

    $userId = $pdo->lastInsertId();
    error_log("User criado com ID: $userId");

    // Dados colaborador
    $stmt = $pdo->prepare("INSERT INTO colaborador_dados (user_id, nome, email) VALUES (?, ?, ?)");
    $result = $stmt->execute([$userId, $nome, $email]);

    if (!$result) {
        error_log("Erro ao inserir colaborador_dados: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Erro ao criar dados do colaborador.");
    }

    // Contacto de emergência vazio
    $stmt = $pdo->prepare("INSERT INTO contactos_emergencia (user_id, nome, parentesco, telefone) VALUES (?, '', '', '')");
    $stmt->execute([$userId]);

    error_log("Colaborador criado com sucesso!");

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Colaborador criado com sucesso."
    ]);
    exit;

} catch (Exception $e) {
    error_log("ERRO: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro: " . $e->getMessage()
    ]);
    exit;
}
