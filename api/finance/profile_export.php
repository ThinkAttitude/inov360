<?php
// api/finance/profile_export.php
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

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'MISSING_USER']);
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

$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* --------- meta do utilizador + empresa --------- */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

$uq = $pdo->prepare("SELECT $nameCol AS nome,email,company_id FROM user WHERE id=:id LIMIT 1");
$uq->execute([':id'=>$userId]);
$u = $uq->fetch(PDO::FETCH_ASSOC) ?: ['nome'=>"user_$userId", 'email'=>'', 'company_id'=>null];

$companyName = '';
if (!empty($u['company_id'])) {
    $cq = $pdo->prepare("SELECT name FROM company WHERE id=:cid LIMIT 1");
    $cq->execute([':cid' => (int)$u['company_id']]);
    $companyName = (string)($cq->fetchColumn() ?: '');
}

/* --------- dados da ficha --------- */
$st = $pdo->prepare("SELECT * FROM finance_profiles WHERE user_id=:u LIMIT 1");
$st->execute([':u'=>$userId]);
$row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

/* --------- especificação (apenas CAMPO + VALOR) --------- */
/* [ label, coluna DB | null p/ compostos, render: money|int|text|bool|date|holiday_period|sickleave_period ] */
$spec = [
    ['Nº',                           'numero',                  'text'],
    ['Nome Completo',                'nome_completo',           'text'],

    ['Vencimento Estimado',          'vencimento_estimado',     'money'],
    ['Vencimento Base',              'vencimento_base',         'money'],
    ['Valor Sub Alimentação',        'valor_sub_alimentacao',   'money'],
    ['Dias c/ sub Alimentação',      'dias_sub_alimentacao',    'int'],

    ['KM’s Estimados',               'kms_estimados',           'money'],
    ['Valor por KM',                 'valor_por_km',            'money'],

    ['Prevenções',                   'prevencoes_sn',           'bool'],
    ['Valor Prevenções',             'valor_prevencoes',        'money'],
    ['Valor passe transporte',       'valor_passe_transporte',  'money'],

    ['Duodécimos',                   'duodecimos',              'bool'],
    ['IHT',                          'iht',                     'money'],

    ['Ajudas de custo estimado',     'ajuda_custo_estimado',    'money'],
    ['Subsídio Noturno',             'subsidio_noturno',        'money'],
    ['Subsídio de Turno',            'subsidio_turno',          'money'],
    ['Ajudas de Custos a deduzir',   'ajudas_custos_deduce',    'money'],

    ['Adiantamentos a deduzir',      'adiantamentos_deduzir',   'money'],
    ['Bónus/Bonificações',           'bonus_bonificacoes',      'money'],

    ['Prevenções (Sim/Não)',         'prevencoes',              'bool'],
    ['Penhoras',                     'penhoras_sn',             'bool'],

    ['Férias',                        null,                     'holiday_period'],
    ['Faltas Justificadas Não Remuneradas','faltas_nao_rem',    'int'],
    ['Faltas Justificadas Remuneradas','faltas_rem_just',       'int'],
    ['Faltas Injustificadas Não Remuneradas','faltas_nao_rem_just','int'],

    ['Baixa médica',                  null,                     'sickleave_period'],

    ['Observações',                  'observacoes',             'text'],
    ['Ajustes Vencimento',           'ajustes_vencimento',      'text'],
];

$periods = [
    'holiday_period'   => ['start' => 'ferias_start',       'end' => 'ferias_end'],
    'sickleave_period' => ['start' => 'baixa_medica_start', 'end' => 'baixa_medica_end'],
];

/* --------- construir o Excel --------- */
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$sheet->setTitle('Ficha Financeira');

/* estilos */
$thFill = [ 'fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6EEF8'] ];
$thStyle = [
    'font' => ['bold'=>true],
    'fill' => $thFill,
    'borders' => ['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['argb'=>'FFB7B7B7']]],
    'alignment' => ['vertical'=>Alignment::VERTICAL_CENTER]
];
$tdBorders = ['borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['argb'=>'FFE0E0E0']]]];

$r = 1;

/* identificação do colaborador (mantido) */
$sheet->setCellValue("A{$r}", 'Colaborador'); $sheet->setCellValue("B{$r}", $u['nome']); $r++;
$sheet->setCellValue("A{$r}", 'Email');       $sheet->setCellValue("B{$r}", $u['email']); $r += 2;

/* cabeçalho: só CAMPO | VALOR */
$sheet->setCellValue("A{$r}", 'CAMPO');
$sheet->setCellValue("B{$r}", 'VALOR');
$sheet->getStyle("A{$r}:B{$r}")->applyFromArray($thStyle);
$sheet->getRowDimension($r)->setRowHeight(22);
$r++;

/* 1ª linha da tabela: Empresa (antes do “Nº”) */
$sheet->setCellValue("A{$r}", 'Empresa');
$sheet->setCellValue("B{$r}", $companyName);
$sheet->getStyle("A{$r}:B{$r}")->applyFromArray($tdBorders);
$r++;

/* restantes linhas (segundo $spec) */
foreach ($spec as [$label, $col, $render]) {
    $sheet->setCellValue("A{$r}", $label);

    $val = null;
    if ($render === 'holiday_period' || $render === 'sickleave_period') {
        $startKey = $periods[$render]['start'];
        $endKey   = $periods[$render]['end'];
        $start = $row[$startKey] ?? null;
        $end   = $row[$endKey] ?? null;
        if ($start || $end) {
            $fmt = static function($d){ if(!$d) return ''; $t=strtotime((string)$d); return $t?date('d/m/Y',$t):$d; };
            $val = 'De '.($fmt($start) ?: '—').' a '.($fmt($end) ?: '—');
        } else {
            $val = '';
        }
    } else {
        $val = ($col !== null && array_key_exists($col,$row)) ? $row[$col] : null;
    }

    $cell = "B{$r}";
    switch ($render) {
        case 'money':
            if ($val === null || $val === '') { $sheet->setCellValue($cell, ''); break; }
            $sheet->setCellValueExplicit($cell, (float)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('"€" #,##0.00');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            break;
        case 'int':
            $sheet->setCellValue($cell, ($val !== null && $val !== '') ? (int)$val : '');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            break;
        case 'bool':
            $sheet->setCellValue($cell, ((string)$val === '1' || $val === 1 || $val === true) ? 'Sim' : 'Não');
            break;
        case 'date':
            if ($val) {
                $ts = strtotime((string)$val);
                if ($ts) {
                    $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($ts);
                    $sheet->setCellValue($cell, $excelDate);
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                } else {
                    $sheet->setCellValue($cell, (string)$val);
                }
            } else {
                $sheet->setCellValue($cell, '');
            }
            break;
        case 'text':
        default:
            $sheet->setCellValue($cell, (string)($val ?? ''));
            break;
    }

    $sheet->getStyle("A{$r}:B{$r}")->applyFromArray($tdBorders);
    $r++;
}

/* larguras e congelar */
$sheet->getColumnDimension('A')->setWidth(42);
$sheet->getColumnDimension('B')->setWidth(32);
$sheet->freezePane('A'.($r > 5 ? 5 : 4));

/* output */
$filename = "ficha_financeira_{$userId}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($ss);
$writer->save('php://output');
exit;
