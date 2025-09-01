<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["error" => "Sessão não iniciada."]);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$operador_id = $_SESSION['user']['id'];

try {
    $pdo = db_connect();

    $stmt = $pdo->prepare("
        SELECT id, titulo, data_inicio, data_fim, tipo 
        FROM eventos 
        WHERE operador_id = ?
    ");
    $stmt->execute([$operador_id]);

    $cores = [
        'ferias' => '#28a745',
        'evento' => '#007bff',
        'baixa_medica' => '#ffc107',
        'baixa_seguro' => '#17a2b8',
        'casamento' => '#6610f2',
        'consulta_medica' => '#20c997',
        'licenca_paternidade' => '#fd7e14',
        'licenca_maternidade' => '#e83e8c',
        'substituicao' => '#6f42c1',
        'pessoal' => '#6c757d',
    ];


    $eventos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipo = $row['tipo'];
        $endDate = (new DateTime($row['data_fim']))->modify('+1 day')->format('Y-m-d');

        $eventos[] = [
            'id' => $row['id'],
            'title' => $row['titulo'],
            'start' => $row['data_inicio'],
            'end' => $endDate,
            'color' => $cores[$tipo] ?? '#343a40'
        ];

    }

    echo json_encode($eventos);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}