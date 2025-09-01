<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

$pdo = db_connect();

// Autenticação
if (!isset($_SESSION["user"]["id"])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado.']);
    exit;
}

$user_id = $_SESSION["user"]["id"];
$month = isset($_GET['month']) ? intval($_GET['month']) : null;
$year  = isset($_GET['year'])  ? intval($_GET['year'])  : null;

if (!$month || !$year) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros "month" e "year" são obrigatórios.']);
    exit;
}

// Datas do mês
$start_date = sprintf('%04d-%02d-01', $year, $month);
$end_date   = date('Y-m-t', strtotime($start_date));

// Buscar registos do mês (incluindo campos dos blocos)
$stmt = $pdo->prepare("
    SELECT data,
           hora_inicio, hora_fim,
           hora_extra_inicio, hora_extra_fim,
           hora_prevencao_inicio, hora_prevencao_fim,
           total_horas
    FROM horarios
    WHERE user_id = ? AND data BETWEEN ? AND ?
");
$stmt->execute([$user_id, $start_date, $end_date]);
$registos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helpers
$duracaoHoras = function(string $data, ?string $ini, ?string $fim): float {
    if (!$ini || !$fim) return 0.0;
    $s = strtotime("$data $ini");
    $e = strtotime("$data $fim");
    if ($e <= $s) {
        // permite turnos que passam da meia-noite
        $e = strtotime("$data $fim +1 day");
    }
    return max(0, round(($e - $s) / 3600, 2));
};

// Acumuladores
$totalHoras = 0.0;
$horasReg   = 0.0;
$horasExt   = 0.0;
$horasPrev  = 0.0;

// Índice de dias com registo (para ausências)
$registados = [];

// Processar
foreach ($registos as $linha) {
    $d = $linha['data'];

    $hReg  = $duracaoHoras($d, $linha['hora_inicio'],          $linha['hora_fim']);
    $hExt  = $duracaoHoras($d, $linha['hora_extra_inicio'],     $linha['hora_extra_fim']);
    $hPrev = $duracaoHoras($d, $linha['hora_prevencao_inicio'], $linha['hora_prevencao_fim']);

    $diaTotal = ($linha['total_horas'] !== null)
        ? floatval($linha['total_horas'])             // se já guardas total, usa-o
        : round($hReg + $hExt + $hPrev, 2);           // senão calcula

    $horasReg   += $hReg;
    $horasExt   += $hExt;
    $horasPrev  += $hPrev;
    $totalHoras += $diaTotal;

    // considera que há registo se qualquer bloco tiver duração > 0
    if ($hReg > 0 || $hExt > 0 || $hPrev > 0) {
        $registados[$d] = true;
    }
}

// Calcular ausências (dias úteis sem registo)
$ausencias = 0;
$periodo = new DatePeriod(
    new DateTime($start_date),
    new DateInterval('P1D'),
    (new DateTime($end_date))->modify('+1 day')
);
foreach ($periodo as $dia) {
    $data = $dia->format('Y-m-d');
    $dow  = intval($dia->format('N')); // 1..7
    if ($dow < 6 && empty($registados[$data])) {
        $ausencias++;
    }
}

// Resposta
echo json_encode([
    'total_horas_mensais' => round($totalHoras, 1),
    'horas_regulares'     => round($horasReg,   1),
    'horas_extra'         => round($horasExt,   1),
    'horas_prevencao'     => round($horasPrev,  1),
    'ausencias'           => $ausencias
]);
