<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

// Apenas POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Body (JSON ou x-www-form-urlencoded)
$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

$user_id    = isset($input['user_id']) ? intval($input['user_id']) : null;
$start_time = $input['start_time'] ?? null; // "HH:MM"
$end_time   = $input['end_time']   ?? null; // "HH:MM"

// Validação básica
$timeRegex = '/^\d{2}:\d{2}$/';
if (!$user_id || !$start_time || !$end_time || !preg_match($timeRegex, $start_time) || !preg_match($timeRegex, $end_time)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Parâmetros obrigatórios inválidos: user_id, start_time (HH:MM), end_time (HH:MM)'
    ]);
    exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ano alvo = ano atual (ajuste se precisares receber por parâmetro)
    $ano = date('Y');

    $startDate = new DateTime("$ano-01-01");
    $endDate   = new DateTime("$ano-12-31");

    // Transação para ser mais rápido/atómico
    $pdo->beginTransaction();

    // Apagar horários existentes desse ano
    $del = $pdo->prepare("DELETE FROM horarios WHERE user_id = ? AND YEAR(data) = ?");
    $del->execute([$user_id, $ano]);

    $ins = $pdo->prepare("
        INSERT INTO horarios (user_id, data, hora_inicio, hora_fim, total_horas)
        VALUES (?, ?, ?, ?, ?)
    ");

    $diasInseridos = 0;

    // Itera dia a dia (inclui endDate)
    $periodo = new DatePeriod($startDate, new DateInterval('P1D'), (clone $endDate)->modify('+1 day'));

    foreach ($periodo as $dia) {
        $dow = (int)$dia->format('N'); // 1..7 (Seg..Dom)
        if ($dow >= 6) {
            continue; // ignora fins de semana
        }

        $dataStr = $dia->format('Y-m-d');

        // Calcula total_horas (em horas decimais), tolerando virada após meia-noite
        $inicioSeg = strtotime($dataStr . ' ' . $start_time);
        $fimSeg    = strtotime($dataStr . ' ' . $end_time);

        if ($fimSeg <= $inicioSeg) {
            // se end <= start, considera que terminou no dia seguinte
            $fimSeg = strtotime($dataStr . ' ' . $end_time . ' +1 day');
        }

        $totalHoras = round(($fimSeg - $inicioSeg) / 3600, 2);
        if ($totalHoras < 0) { $totalHoras = 0; } // salvaguarda

        $ins->execute([
            $user_id,
            $dataStr,
            $start_time . ':00',
            $end_time . ':00',
            $totalHoras
        ]);

        $diasInseridos++;
    }

    $pdo->commit();

    echo json_encode([
        'success'     => true,
        'message'     => "Horários preenchidos com sucesso para $diasInseridos dias úteis em $ano.",
        'total_dias'  => $diasInseridos
    ]);
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
