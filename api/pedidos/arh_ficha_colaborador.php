<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
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
    echo json_encode(["success" => false, "error" => "Email e telefone são obrigatórios."]);
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

// Contacto de emergência opcional
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

    // Atualiza ficha principal
    $stmt = $conn->prepare("
        UPDATE colaborador_dados
        SET email = ?, telefone = ?, morada = ?, nib = ?
        WHERE user_id = ?
    ");
    $stmt->execute([$email, $telefone, $morada, $nib, $user_id]);

    // Contacto de emergência: verifica se já existe
    $stmt = $conn->prepare("SELECT COUNT(*) FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existe = $stmt->fetchColumn();

    if ($em_nome || $em_parentesco || $em_telefone) {
        if ($existe > 0) {
            $stmt = $conn->prepare("
                UPDATE contactos_emergencia
                SET nome = ?, parentesco = ?, telefone = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$em_nome, $em_parentesco, $em_telefone, $user_id]);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO contactos_emergencia (user_id, nome, parentesco, telefone)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $em_nome, $em_parentesco, $em_telefone]);
        }
    }

    echo json_encode(["success" => true, "message" => "Ficha atualizada com sucesso."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro ao submeter ficha para avaliação. Tente novamente."]);
}
