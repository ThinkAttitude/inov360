<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Verifica se é admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Parâmetros obrigatórios
$periodo = $_GET['periodo'] ?? null;
$ano = $_GET['ano'] ?? null;
$mes = $_GET['mes'] ?? null;
$inicio = $_GET['inicio'] ?? null;
$fim = $_GET['fim'] ?? null;

if (!$periodo || !in_array($periodo, ['m', 't', 'a', 'p'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetro de período inválido']);
    exit;
}

// Definir intervalo de datas com base no tipo de período
try {
    if ($periodo === 'm') {
        if (!$ano || !$mes) throw new Exception('Ano e mês são obrigatórios');
        $start = "$ano-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-01";
        $end = date("Y-m-t", strtotime($start));
    } elseif ($periodo === 't') {
        if (!$ano || !$mes) throw new Exception('Ano e mês são obrigatórios para trimestre');
        $startMonth = ceil($mes / 3) * 3 - 2;
        $start = "$ano-" . str_pad($startMonth, 2, "0", STR_PAD_LEFT) . "-01";
        $end = date("Y-m-t", strtotime("+2 months", strtotime($start)));
    } elseif ($periodo === 'a') {
        if (!$ano) throw new Exception('Ano é obrigatório');
        $start = "$ano-01-01";
        $end = "$ano-12-31";
    } elseif ($periodo === 'p') {
        if (!$inicio || !$fim) throw new Exception('Data de início e fim obrigatórias para período personalizado');
        $start = $inicio;
        $end = $fim;
    }

    $pdo = db_connect();

    // Buscar todos os utilizadores por empresa
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.company,
               EXISTS (
                   SELECT 1 FROM confirmacoes 
                   WHERE user_id = u.id 
                   AND confirmado_em BETWEEN ? AND ?
               ) AS submetido
        FROM user u
        WHERE u.role = 'employee'
        ORDER BY u.company, u.name
    ");
    $stmt->execute([$start, $end]);
    $utilizadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar por empresa
    $empresas = [];
    foreach ($utilizadores as $u) {
        $empresa = $u['company'] ?? 'Sem Empresa';
        if (!isset($empresas[$empresa])) {
            $empresas[$empresa] = ['empresa' => $empresa, 'utilizadores' => []];
        }

        $empresas[$empresa]['utilizadores'][] = [
            'id' => (int)$u['id'],
            'nome' => $u['name'],
            'email' => $u['email'],
            'estado' => 'Ativo',
            'submetido' => (bool)$u['submetido']
        ];
    }

    echo json_encode(['success' => true, 'data' => array_values($empresas)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
