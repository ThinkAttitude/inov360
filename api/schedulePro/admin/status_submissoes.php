<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$ano = $_GET['ano'] ?? null;
$mes = $_GET['mes'] ?? null;

if (!$ano || !$mes) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ano e mês são obrigatórios']);
    exit;
}

try {
    $pdo = db_connect();

    // Buscar todos os utilizadores colaboradores
    $stmtUsers = $pdo->prepare("SELECT id, name, email, company FROM user WHERE role = 'employee'");
    $stmtUsers->execute();
    $users = $stmtUsers->fetchAll();

    $total = count($users);
    $submetidos = 0;
    $lista = [];

    foreach ($users as $user) {
        $stmtCheck = $pdo->prepare("SELECT * FROM confirmacoes WHERE user_id = ? AND ano = ? AND mes = ?");
        $stmtCheck->execute([$user['id'], $ano, $mes]);
        $confirm = $stmtCheck->fetch();

        $estado = $confirm ? 'Submetido' : 'Pendente';
        $detalhe = $confirm ? 'Aguardando validação do administrador' : 'Aguarda submissão do utilizador';

        if ($confirm) $submetidos++;

        $lista[] = [
            'name' => $user['name'],
            'email' => $user['email'],
            'company' => $user['company'],
            'estado' => $estado,
            'detalhes' => $detalhe
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'total_utilizadores' => $total,
            'total_submetidos' => $submetidos,
            'total_pendentes' => $total - $submetidos,
            'progresso_percentual' => $total > 0 ? round(($submetidos / $total) * 100) : 0,
            'utilizadores' => $lista
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
