<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$date = $_GET['date'] ?? null;

if (!$date) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data não fornecida']);
    exit;
}

try {
    $pdo = db_connect();

    $stmt = $pdo->prepare("SELECT * FROM horarios WHERE user_id = ? AND data = ?");
    $stmt->execute([$user_id, $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            'success' => true,
            'data' => null
        ]);
        exit;
    }

    $data = [
        'horas_regulares' => [
            'inicio' => $row['hora_inicio'],
            'fim' => $row['hora_fim']
        ],
        'horas_extra' => [
            'inicio' => $row['hora_extra_inicio'],
            'fim' => $row['hora_extra_fim']
        ],
        'horas_prevencao' => [
            'inicio' => $row['hora_prevencao_inicio'],
            'fim' => $row['hora_prevencao_fim']
        ],
        'ausencia' => [
            'ativa' => !empty($row['tipo_ausencia']),
            'tipo' => $row['tipo_ausencia']
        ],
        'quilometros' => (float)$row['quilometros']
    ];

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
