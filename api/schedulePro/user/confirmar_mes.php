<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Verifica login
if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Verifica método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ano = isset($input['ano']) ? intval($input['ano']) : null;
$mes = isset($input['mes']) ? intval($input['mes']) : null;
$user_id = $_SESSION['user']['id'];

if (!$ano || !$mes || $mes < 1 || $mes > 12) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ano e mês são obrigatórios e válidos']);
    exit;
}

try {
    $pdo = db_connect();

    // UPSERT
    $stmt = $pdo->prepare("
        INSERT INTO confirmacoes (user_id, ano, mes)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE confirmado_em = NOW(), aprovado = NULL
    ");
    $stmt->execute([$user_id, $ano, $mes]);

    echo json_encode(['success' => true, 'message' => 'Horário submetido com sucesso para aprovação.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
