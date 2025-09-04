<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Acesso negado."]);
    exit;
}

header('Content-Type: application/json');

$user_id = $_SESSION["user"]["id"];
$target_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $user_id;

try {
    $conn = db_connect();
    
    // Buscar férias/ausências aprovadas do utilizador inter2
    $stmt = $conn->prepare("
        SELECT 
            tipo,
            data_inicio,
            data_fim,
            justificacao
        FROM pedidos_ferias 
        WHERE user_id = ? AND estado = 'aprovado'
        ORDER BY data_inicio ASC
    ");
    $stmt->execute([$target_user_id]);
    $ferias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Criar um array com as datas de férias para facilitar a consulta no JavaScript
    $ferias_por_data = [];
    
    foreach ($ferias as $feria) {
        $data_inicio = new DateTime($feria['data_inicio']);
        $data_fim = new DateTime($feria['data_fim']);
        
        // Iterar por todos os dias entre início e fim
        while ($data_inicio <= $data_fim) {
            $dateKey = $data_inicio->format('Y-m-d');
            $ferias_por_data[$dateKey] = [
                'tipo' => $feria['tipo'],
                'justificacao' => $feria['justificacao']
            ];
            $data_inicio->add(new DateInterval('P1D'));
        }
    }

    echo json_encode([
        "success" => true,
        "ferias" => $ferias_por_data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erro ao buscar férias: " . $e->getMessage()
    ]);
}
?>
