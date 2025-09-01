<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db.php";

// Verifica se está logado e é operador
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter") {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Acesso negado."]);
    exit;
}

$user_id = $_SESSION["user"]["id"];

$email = trim($_POST["email"] ?? "");
$telefone = trim($_POST["contacto_telefone"] ?? "");
$morada = trim($_POST["morada"] ?? "");
$nib = trim($_POST["nib"] ?? "");

$em_nome = trim($_POST["emergencia_nome"] ?? "");
$em_parentesco = trim($_POST["emergencia_parentesco"] ?? "");
$em_telefone = trim($_POST["emergencia_telefone"] ?? "");


if (!$email || !$telefone) {
    echo json_encode(["success" => false, "error" => "Todos os campos obrigatórios devem ser preenchidos."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Email inválido."]);
    exit;
}

if (!preg_match('/^\d{9,15}$/', $telefone)) {
    echo json_encode(["success" => false, "error" => "Telefone inválido. Deve conter apenas números."]);
    exit;
}

// Contacto de emergência opcional: validar só se algum campo preenchido
$tem_emergencia = ($em_nome !== '' || $em_parentesco !== '' || $em_telefone !== '');
if ($tem_emergencia) {
    if ($em_nome === '' || $em_telefone === '') {
        echo json_encode(["success" => false, "error" => "Contacto de emergência: preencha Nome e Telefone ou deixe tudo em branco."]);
        exit;
    }
    if (!preg_match('/^\d{9,15}$/', $em_telefone)) {
        echo json_encode(["success" => false, "error" => "Telefone de emergência inválido (9-15 dígitos)."]);
        exit;
    }
}

try {
    $conn = db_connect();

    // Obter dados atuais para comparar
    $stmt = $conn->prepare("SELECT nome, email, telefone FROM colaborador_dados WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $atuais = $stmt->fetch();

    if (!$atuais) {
        echo json_encode(["success" => false, "error" => "Colaborador não encontrado."]);
        exit;
    }

    // Verifica alterações
    $email_alterado = $email !== $atuais["email"];
    $telefone_alterado = $telefone !== $atuais["telefone"];

    $houve_alteracao =
        $email_alterado ||
        $telefone_alterado ||
        $morada ||
        $nib ||
        $em_nome || $em_parentesco || $em_telefone;

    if (!$houve_alteracao) {
        echo json_encode(["success" => false, "error" => "Nenhuma alteração detectada."]);
        exit;
    }

    if ($nib && !preg_match('/^\d{21}$/', $nib)) {
        echo json_encode(["success" => false, "error" => "NIB inválido. Deve conter 21 dígitos."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM colaborador_edicoes WHERE user_id = ? AND estado = 'pendente'");
    $stmt->execute([$user_id]);
    $pendente = $stmt->fetchColumn();

    if ($pendente > 0) {
        echo json_encode(["success" => false, "error" => "Já existe um pedido pendente. Aguarde a validação."]);
        exit;
    }


    // Usa os valores novos se alterados, senão mantém os antigos
    $stmt = $conn->prepare("
        INSERT INTO colaborador_edicoes (user_id, email, telefone, morada, nib, estado, criado_em)
        VALUES (?, ?, ?, ?, ?, 'pendente', NOW())
    ");
    $stmt->execute([$user_id, $email, $telefone, $morada, $nib]);

    if ($em_nome || $em_parentesco || $em_telefone) {
        $stmt_em = $conn->prepare("
        INSERT INTO contactos_emergencia_edicoes (user_id, nome, parentesco, telefone, estado)
        VALUES (?, ?, ?, ?, 'pendente')
    ");
        $stmt_em->execute([$user_id, $em_nome, $em_parentesco, $em_telefone]);
    }


    echo json_encode(["success" => true]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erro ao submeter pedido: " . $e->getMessage()
    ]);
}
