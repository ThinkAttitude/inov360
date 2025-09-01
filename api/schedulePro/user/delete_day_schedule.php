<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

// Verifica se é DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Lê o corpo do pedido
$input = json_decode(file_get_contents('php://input'), true);
$data = $input['date'] ?? null;
$user_id = $_SESSION['user']['id'];

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data é obrigatória']);
    exit;
}

try {
    $pdo = db_connect();

    $stmt = $pdo->prepare("DELETE FROM horarios WHERE user_id = ? AND data = ?");
    $stmt->execute([$user_id, $data]);

    echo json_encode([
        'success' => true,
        'message' => "Horário removido com sucesso para o dia $data"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
