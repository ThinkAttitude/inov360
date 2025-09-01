<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Verifica se está autenticado
if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : null;
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : null;

if (!$ano || !$mes || $mes < 1 || $mes > 12) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ano e mês válidos são obrigatórios']);
    exit;
}

try {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT aprovado FROM confirmacoes WHERE user_id = ? AND ano = ? AND mes = ?");
    $stmt->execute([$user_id, $ano, $mes]);
    $row = $stmt->fetch();

    if ($row) {
        echo json_encode([
            'success' => true,
            'data' => [
                'submetido' => true,
                'aprovado' => $row['aprovado'] === null ? null : (bool)$row['aprovado']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [
                'submetido' => false,
                'aprovado' => null
            ]
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
