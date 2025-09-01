<?php
// Produção: evitar leak de notices que quebram JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

session_start();
require_once "../includes/db.php";
header('Content-Type: application/json; charset=utf-8');

// Verifica se está logado e é operador
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
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

    // Obter dados atuais para comparar
    // Obter dados atuais (inclui morada / nib para comparação real)
    $stmt = $conn->prepare("SELECT nome, email, telefone, morada, nib FROM colaborador_dados WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $atuais = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$atuais) {
        // Criar registo base caso ainda não exista (primeiro preenchimento)
        $stmtIns = $conn->prepare("INSERT INTO colaborador_dados (user_id, email, telefone, morada, nib) VALUES (?,?,?,?,?)");
        $stmtIns->execute([$user_id, $email ?: null, $telefone ?: null, $morada ?: null, $nib ?: null]);
        $atuais = [
            'email' => $email,
            'telefone' => $telefone,
            'morada' => $morada,
            'nib' => $nib,
            'nome' => null
        ];
        // Não criar pedido de edição nesta chamada se tudo corresponde (deixa seguir fluxo normal de alteração)
    }

    // Verifica alterações reais
    $email_alterado    = $email    !== ($atuais['email']    ?? '');
    $telefone_alterado = $telefone !== ($atuais['telefone'] ?? '');
    $morada_alterada   = $morada   !== trim((string)($atuais['morada'] ?? ''));
    $nib_alterado      = $nib      !== trim((string)($atuais['nib'] ?? ''));

    // Estado atual do contacto emergência para comparação
    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $em_atual = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['nome'=>'','parentesco'=>'','telefone'=>''];
    $em_nome_alt       = $em_nome       !== $em_atual['nome'];
    $em_parentesco_alt = $em_parentesco !== $em_atual['parentesco'];
    $em_telefone_alt   = $em_telefone   !== $em_atual['telefone'];

    $houve_alteracao = (
        $email_alterado || $telefone_alterado || $morada_alterada || $nib_alterado ||
        $em_nome_alt || $em_parentesco_alt || $em_telefone_alt
    );

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


    // Inserir pedido de edição somente com campos relevantes
    $stmt = $conn->prepare("INSERT INTO colaborador_edicoes (user_id, email, telefone, morada, nib, estado, criado_em) VALUES (?,?,?,?,?, 'pendente', NOW())");
    $stmt->execute([
        $user_id,
        $email_alterado ? $email : $atuais['email'],
        $telefone_alterado ? $telefone : $atuais['telefone'],
        $morada_alterada ? ($morada ?: null) : $atuais['morada'],
        $nib_alterado ? ($nib ?: null) : $atuais['nib']
    ]);

    if ($em_nome_alt || $em_parentesco_alt || $em_telefone_alt) {
        $stmt_em = $conn->prepare("INSERT INTO contactos_emergencia_edicoes (user_id, nome, parentesco, telefone, estado) VALUES (?,?,?,?, 'pendente')");
        $stmt_em->execute([
            $user_id,
            $em_nome_alt ? $em_nome : $em_atual['nome'],
            $em_parentesco_alt ? $em_parentesco : $em_atual['parentesco'],
            $em_telefone_alt ? $em_telefone : $em_atual['telefone']
        ]);
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
