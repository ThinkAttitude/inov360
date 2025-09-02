<?php
require_once '../includes/db.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verifica se é admin
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$pdo = db_connect();

// Inputs
$periodo = $_GET['periodo'] ?? 'm'; // m = mês atual, t = trimestre, a = ano, p = personalizado
$ano = $_GET['ano'] ?? date('Y');
$mes = $_GET['mes'] ?? date('n');
$inicio = $_GET['inicio'] ?? null;
$fim = $_GET['fim'] ?? null;
$ids = $_GET['ids'] ?? [];

if (empty($ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nenhum colaborador selecionado.']);
    exit;
}

// Calcula intervalo de datas
try {
    $startDate = null;
    $endDate = null;

    if ($periodo === 'm') {
        $startDate = new DateTime("$ano-$mes-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
    } elseif ($periodo === 't') {
        $mes = (int)$mes;
        $startDate = new DateTime("$ano-$mes-01");
        $endDate = clone $startDate;
        $endDate->modify('+2 months')->modify('last day of this month');
    } elseif ($periodo === 'a') {
        $startDate = new DateTime("$ano-01-01");
        $endDate = new DateTime("$ano-12-31");
    } elseif ($periodo === 'p' && $inicio && $fim) {
        $startDate = new DateTime($inicio);
        $endDate = new DateTime($fim);
    } else {
        throw new Exception('Período inválido.');
    }

    $data_inicio = $startDate->format('Y-m-d');
    $data_fim = $endDate->format('Y-m-d');

    // Prepara Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Resumo');

    // Cabeçalhos
    $sheet->fromArray([
        "Nome", "Email", "Empresa", "Dias com Evento",
        "Horas Trabalhadas", "Horas Extra", "Horas Prevenção",
        "Total Férias", "Total Faltas", "Quilómetros"
    ], null, 'A1');

    $linha = 2;

    foreach ($ids as $userId) {
        // Info do utilizador
        $stmtUser = $pdo->prepare("SELECT name, email, company FROM user WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if (!$user) continue;

        // Eventos dentro do período
        $stmtEventos = $pdo->prepare("
            SELECT * FROM horarios 
            WHERE user_id = ? AND data BETWEEN ? AND ?
        ");
        $stmtEventos->execute([$userId, $data_inicio, $data_fim]);
        $registos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

        $dias_com_evento = count($registos);
        $horas_trabalhadas = 0;
        $horas_extra = 0;
        $horas_prevencao = 0;
        $total_ferias = 0;
        $total_faltas = 0;
        $quilometros_total = 0;

        foreach ($registos as $reg) {
            if (!empty($reg['hora_inicio']) && !empty($reg['hora_fim'])) {
                $inicioTrabalho = new DateTime($reg['hora_inicio']);
                $fimTrabalho = new DateTime($reg['hora_fim']);
                $intervalo = $inicioTrabalho->diff($fimTrabalho);
                $horas_trabalhadas += ($intervalo->h + $intervalo->i / 60);
            }

            // Horas extra
            if (!empty($reg['hora_extra_inicio']) && !empty($reg['hora_extra_fim'])) {
                $inicioExtra = new DateTime($reg['hora_extra_inicio']);
                $fimExtra = new DateTime($reg['hora_extra_fim']);
                $intervalo = $inicioExtra->diff($fimExtra);
                $horas_extra += ($intervalo->h + $intervalo->i / 60);
            }

            // Horas prevenção
            if (!empty($reg['hora_prevencao_inicio']) && !empty($reg['hora_prevencao_fim'])) {
                $inicioPrev = new DateTime($reg['hora_prevencao_inicio']);
                $fimPrev = new DateTime($reg['hora_prevencao_fim']);
                $intervalo = $inicioPrev->diff($fimPrev);
                $horas_prevencao += ($intervalo->h + $intervalo->i / 60);
            }

            // Ausências
            if ($reg['tipo_ausencia'] === 'ferias') $total_ferias++;
            if ($reg['tipo_ausencia'] === 'falta') $total_faltas++;

            // Quilómetros
            if (!empty($reg['quilometros'])) {
                $quilometros_total += (float)$reg['quilometros'];
            }
        }

        $sheet->fromArray([
            $user['name'],
            $user['email'],
            $user['company'],
            $dias_com_evento,
            $horas_trabalhadas,
            $horas_extra,
            $horas_prevencao,
            $total_ferias,
            $total_faltas,
            $quilometros_total
        ], null, 'A' . $linha);

        $linha++;
    }

    // Gerar e enviar o Excel
    $fileName = "resumo_eventos_" . $startDate->format('Y_m_d') . "_a_" . $endDate->format('Y_m_d') . ".xlsx";
    $tempFile = sys_get_temp_dir() . "export_excel.php/" . $fileName;

    $writer = new Xlsx($spreadsheet);
    $writer->save($tempFile);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    readfile($tempFile);
    unlink($tempFile);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
