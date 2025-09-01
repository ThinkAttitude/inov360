<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

// Permitir apenas método DELETE
if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Capturar dados do body (por ser DELETE, vem via php://input)
$input = json_decode(file_get_contents("php://input"), true);

$user_id = isset($input['user_id']) ? intval($input['user_id']) : null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetro obrigatório: user_id']);
    exit;
}

try {
    $pdo = db_connect();

    $ano = date('Y');

    // Eliminar os registos do utilizador para o ano atual
    $stmt = $pdo->prepare("DELETE FROM horarios WHERE user_id = ? AND YEAR(data) = ?");
    $stmt->execute([$user_id, $ano]);

    $count = $stmt->rowCount();

    echo json_encode([
        'success' => true,
        'message' => "Foram apagados $count registos do calendário do utilizador para $ano."
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
