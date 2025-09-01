<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}
if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$user_id        = $_SESSION['user']['id'];
$data           = $input['date'] ?? null;               // "YYYY-MM-DD"
$ausencia       = $input['ausencia'] ?? ['ativa'=>false,'tipo'=>null];
$reg            = $input['horas_regulares'] ?? null;    // {inicio:"HH:MM", fim:"HH:MM"}
$ext            = $input['horas_extra'] ?? null;        // idem
$prev           = $input['horas_prevencao'] ?? null;    // idem
$quilometros    = isset($input['quilometros']) ? (int)$input['quilometros'] : 0;

if (!$data) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Data é obrigatória']);
    exit;
}

function duracao_horas(?string $data, ?string $ini, ?string $fim): float {
    if (!$data || !$ini || !$fim) return 0.0;
    $s = strtotime("$data $ini:00");
    $e = strtotime("$data $fim:00");
    if ($e <= $s) { // permite virar para o dia seguinte
        $e = strtotime("$data $fim:00 +1 day");
    }
    return round(max(0, ($e - $s) / 3600), 2);
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Se ausência, grava só a ausência
    if (!empty($ausencia['ativa'])) {
        $tipo = $ausencia['tipo'] ?? 'falta';

        // upsert (precisa do UNIQUE (user_id, data))
        $sql = "
          INSERT INTO horarios (user_id, data, tipo_ausencia, quilometros,
                                hora_inicio, hora_fim, hora_extra_inicio, hora_extra_fim,
                                hora_prevencao_inicio, hora_prevencao_fim, total_horas)
          VALUES (:uid, :data, :tipo, :km, NULL, NULL, NULL, NULL, NULL, NULL, NULL)
          ON DUPLICATE KEY UPDATE
            tipo_ausencia = VALUES(tipo_ausencia),
            quilometros   = VALUES(quilometros),
            hora_inicio = NULL, hora_fim = NULL,
            hora_extra_inicio = NULL, hora_extra_fim = NULL,
            hora_prevencao_inicio = NULL, hora_prevencao_fim = NULL,
            total_horas = NULL
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid'=>$user_id, ':data'=>$data, ':tipo'=>$tipo, ':km'=>$quilometros
        ]);

        echo json_encode(['success'=>true,'message'=>'Ausência registada com sucesso.']);
        exit;
    }

    // calcular durações
    $hReg  = duracao_horas($data, $reg['inicio']  ?? null, $reg['fim']  ?? null);
    $hExt  = duracao_horas($data, $ext['inicio']  ?? null, $ext['fim']  ?? null);
    $hPrev = duracao_horas($data, $prev['inicio'] ?? null, $prev['fim'] ?? null);
    $total = round($hReg + $hExt + $hPrev, 2);

    // upsert do horário do dia
    $sql = "
      INSERT INTO horarios (
        user_id, data,
        hora_inicio, hora_fim,
        hora_extra_inicio, hora_extra_fim,
        hora_prevencao_inicio, hora_prevencao_fim,
        total_horas, quilometros, tipo_ausencia
      ) VALUES (
        :uid, :data,
        :ri, :rf,
        :ei, :ef,
        :pi, :pf,
        :total, :km, NULL
      )
      ON DUPLICATE KEY UPDATE
        hora_inicio = VALUES(hora_inicio),
        hora_fim = VALUES(hora_fim),
        hora_extra_inicio = VALUES(hora_extra_inicio),
        hora_extra_fim = VALUES(hora_extra_fim),
        hora_prevencao_inicio = VALUES(hora_prevencao_inicio),
        hora_prevencao_fim = VALUES(hora_prevencao_fim),
        total_horas = VALUES(total_horas),
        quilometros = VALUES(quilometros),
        tipo_ausencia = NULL
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid'=>$user_id, ':data'=>$data,
        ':ri'=>$reg['inicio']  ?? null, ':rf'=>$reg['fim']  ?? null,
        ':ei'=>$ext['inicio']  ?? null, ':ef'=>$ext['fim']  ?? null,
        ':pi'=>$prev['inicio'] ?? null, ':pf'=>$prev['fim'] ?? null,
        ':total'=>$total, ':km'=>$quilometros
    ]);

    echo json_encode(['success'=>true,'message'=>'Horário salvo com sucesso.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Erro: '.$e->getMessage()]);
}
