<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado.']);
    exit;
}

require_once '../includes/db.php';
$pdo = db_connect();

$user_id = $_SESSION['user']['id'];
$month = isset($_GET['month']) ? intval($_GET['month']) : null;
$year = isset($_GET['year']) ? intval($_GET['year']) : null;

if (!$month || !$year) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros "month" e "year" são obrigatórios.']);
    exit;
}

// Gerar datas
$start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$end_date = date("Y-m-t", strtotime($start_date));

// Query
$stmt = $pdo->prepare("
    SELECT data, hora_inicio, hora_fim, total_horas
    FROM horarios
    WHERE user_id = ? AND data BETWEEN ? AND ?
    ORDER BY data ASC
");
$stmt->execute([$user_id, $start_date, $end_date]);
$registos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($registos);
