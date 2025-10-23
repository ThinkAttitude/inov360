<?php
// api/finance/profiles_export_all.php
declare(strict_types=1);
session_start();

/* --------- auth --------- */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']);
    exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(7, $perms, true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'Do not have permission']);
    exit;
}

/* --------- deps & db --------- */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* detectar coluna de nome em user (nome|name) */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

/* colunas do Excel */
$columns = [
    ['Empresa', 'company_name', 'text'],
    ['Nº', 'numero', 'text'],
    ['Nome Completo', 'nome_completo', 'text'],
    ['Vencimento Estimado', 'vencimento_estimado', 'money'],
    ['Vencimento Base', 'vencimento_base', 'money'],
    ['Valor Sub Alimentação', 'valor_sub_alimentacao', 'money'],
    ['Dias c/ sub Alimentação (dias)', 'dias_sub_alimentacao', 'int'],
    ['KM’s Estimados', 'kms_estimados', 'money'],
    ['Valor por KM', 'valor_por_km', 'money'],
    ['Prevenções', 'prevencoes_sn', 'bool'],
    ['Valor Prevenções', 'valor_prevencoes', 'money'],
    ['Valor passe transporte', 'valor_passe_transporte', 'money'],
    ['Duodécimos', 'duodecimos', 'bool'],
    ['IHT', 'iht', 'money'],
    ['Ajudas de custo estimado', 'ajuda_custo_estimado', 'money'],
    ['Subsídio Noturno', 'subsidio_noturno', 'money'],
    ['Subsídio de Turno', 'subsidio_turno', 'money'],
    ['Ajudas de Custos a deduzir', 'ajudas_custos_deduce', 'money'],
    ['Adiantamentos a deduzir', 'adiantamentos_deduzir', 'money'],
    ['Bónus/Bonificações', 'bonus_bonificacoes', 'money'],
    ['Prevenções (Sim/Não)', 'prevencoes', 'bool'],
    ['Penhoras', 'penhoras_sn', 'bool'],
    ['Férias (De–A)', null, 'period'],
    ['Faltas Justif. Não Rem. (dias)', 'faltas_nao_rem', 'int'],
    ['Faltas Justif. Rem. (dias)', 'faltas_rem_just', 'int'],
    ['Faltas Injustif. Não Rem. (dias)', 'faltas_nao_rem_just', 'int'],
    ['Baixa médica (De–A)', null, 'period_baixa'],
    ['Observações', 'observacoes', 'text'],
    ['Ajustes Vencimento', 'ajustes_vencimento', 'text'],
];

/* mapeamento de períodos */
$periodKeys = [
    'ferias' => ['start' => 'ferias_start',       'end' => 'ferias_end'],
    'baixa'  => ['start' => 'baixa_medica_start', 'end' => 'baixa_medica_end'],
];

/* query */
$sql = "
    SELECT 
        u.id AS user_id,
        u.$nameCol    AS user_name,
        u.email       AS user_email,
        c.name        AS company_name,
        fp.*
    FROM user u
    LEFT JOIN company c ON c.id = u.company_id
    LEFT JOIN finance_profiles fp ON fp.user_id = u.id
    ORDER BY u.$nameCol ASC, u.id ASC
";
$sth = $pdo->query($sql);

/* construir Excel */
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$sheet->setTitle('Colaboradores');

$thStyle = [
    'font' => ['bold'=>true],
    'fill' => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['argb'=>'FFE6EEF8']],
    'borders' => ['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['argb'=>'FFB7B7B7']]],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER, 'vertical'=>Alignment::VERTICAL_CENTER, 'wrapText'=>true],
];
$tdBorders = ['borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['argb'=>'FFE0E0E0']]]];

$r = 1;

/* cabeçalho */
foreach ($columns as $i => [$header]) {
    $colLetter = Coordinate::stringFromColumnIndex($i+1);
    $sheet->setCellValue($colLetter.$r, $header);
}
$lastColLetter = Coordinate::stringFromColumnIndex(count($columns));
$sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray($thStyle);
$sheet->getRowDimension($r)->setRowHeight(22);
$r++;

/* linhas */
while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    foreach ($columns as $i => [$header, $key, $type]) {
        $colLetter = Coordinate::stringFromColumnIndex($i+1);
        $cell = $colLetter.$r;

        $value = null;
        if ($type === 'period') {
            $s = $row[$periodKeys['ferias']['start']] ?? null;
            $e = $row[$periodKeys['ferias']['end']]   ?? null;
            $fmt = static function($d){ if(!$d) return ''; $t=strtotime((string)$d); return $t?date('d/m/Y',$t):$d; };
            $value = ($s || $e) ? ('De '.($fmt($s) ?: '—').' a '.($fmt($e) ?: '—')) : '';
        } elseif ($type === 'period_baixa') {
            $s = $row[$periodKeys['baixa']['start']] ?? null;
            $e = $row[$periodKeys['baixa']['end']]   ?? null;
            $fmt = static function($d){ if(!$d) return ''; $t=strtotime((string)$d); return $t?date('d/m/Y',$t):$d; };
            $value = ($s || $e) ? ('De '.($fmt($s) ?: '—').' a '.($fmt($e) ?: '—')) : '';
        } else {
            $value = ($key !== null && array_key_exists($key, $row)) ? $row[$key] : null;
        }

        switch ($type) {
            case 'money':
                if ($value === null || $value === '') { $sheet->setCellValue($cell, ''); break; }
                $sheet->setCellValueExplicit($cell, (float)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('"€" #,##0.00');
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                break;

            case 'int':
                $sheet->setCellValue($cell, ($value !== null && $value !== '') ? (int)$value : '');
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                break;

            case 'bool':
                $sheet->setCellValue($cell, ((string)$value === '1' || $value === 1 || $value === true) ? 'Sim' : 'Não');
                break;

            case 'date':
                if ($value) {
                    $ts = strtotime((string)$value);
                    if ($ts) {
                        $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($ts);
                        $sheet->setCellValue($cell, $excelDate);
                        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                    } else {
                        $sheet->setCellValue($cell, (string)$value);
                    }
                } else {
                    $sheet->setCellValue($cell, '');
                }
                break;

            case 'text':
            default:
                $sheet->setCellValue($cell, (string)($value ?? ''));
                break;
        }
    }

    // bordas da linha completa
    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray($tdBorders);
    $r++;
}

/* larguras e filtros */
$sheet->getColumnDimension('A')->setWidth(26);
$sheet->getColumnDimension('B')->setWidth(12);
$sheet->getColumnDimension('C')->setWidth(28);
for ($i = 4; $i <= count($columns); $i++) {
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
}
$sheet->setAutoFilter("A1:{$lastColLetter}1");

/* output */
$filename = 'fichas_financeiras_todos.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($ss);
$writer->save('php://output');
exit;
