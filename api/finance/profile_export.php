<?php
// api/finance/profile_export.php
declare(strict_types=1);
session_start();

/* --------- auth --------- */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) { http_response_code(401); echo "UNAUTHENTICATED"; exit; }
$role   = $_SESSION['user']['role'] ?? '';
$selfId = (int)($_SESSION['user']['id'] ?? 0);
function can_read(string $role, int $selfId, int $targetId): bool {
    if (in_array($role, ['admin_rh','*'], true)) return true;
    return $selfId === $targetId;
}
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) { http_response_code(400); echo "MISSING_USER"; exit; }
if (!can_read($role,$selfId,$userId)) { http_response_code(403); echo "FORBIDDEN"; exit; }

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

/* --------- meta do utilizador --------- */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }
$uq = $pdo->prepare("SELECT $nameCol AS nome,email FROM user WHERE id=:id LIMIT 1");
$uq->execute([':id'=>$userId]);
$u = $uq->fetch(PDO::FETCH_ASSOC) ?: ['nome'=>"user_$userId", 'email'=>''];

/* --------- dados da ficha --------- */
$st = $pdo->prepare("SELECT * FROM finance_profiles WHERE user_id=:u LIMIT 1");
$st->execute([':u'=>$userId]);
$row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

/* --------- especificação CAMPO/TIPO/NOTAS + mapeamento a colunas --------- */
$spec = [
    // label, tipo, notas, coluna DB, render (money|int|text|bool|date)
    ['Nº',                          'Número',           '',                                                'numero',                  'text'],
    ['Nome Completo',               'Texto',            '',                                                'nome_completo',          'text'],

    ['Vencimento Estimado',         'Euro',             '',                                                'vencimento_estimado',    'money'],
    ['Vencimento Base',             'Euro',             '',                                                'vencimento_base',        'money'],
    ['Valor Sub Alimentação',       'Euro',             '',                                                'valor_sub_alimentacao',  'money'],
    ['Dias c/ sub Alimentação',     'Número (Dias)',    '',                                                'dias_sub_alimentacao',   'int'],

    ['KM’s Estimados',              'Euro',             '',                                                'kms_estimados',          'money'],
    ['Valor por KM',                'Euro',             '',                                                'valor_por_km',           'money'],

    ['Prevenções',                  'Escolha Sim/Não',  '',                                                'prevencoes_sn',          'bool'], // fallback se existir
    ['Valor Prevenções',            'Euro',             '',                                                'valor_prevencoes',       'money'],
    ['Valor passe transporte',      'Euro',             '',                                                'valor_passe_transporte', 'money'],

    ['Duodécimos Sim/Não',          'Escolha Sim/Não',  '',                                                'duodecimos',             'bool'],
    ['IHT',                         'Euro',             '',                                                'iht',                    'money'],

    ['Ajudas de custo estimado',    'Euro',             '',                                                'ajuda_custo_estimado',   'money'],
    ['Subsídio Noturno',            'Euro',             '',                                                'subsidio_noturno',       'money'],
    ['Subsídio de Turno',           'Euro',             '',                                                'subsidio_turno',         'money'],
    ['Ajudas de Custos a deduzir',  'Euro',             '',                                                'ajudas_custos_deduce',   'money'], // usar exatamente o nome da tua coluna

    ['Adiantamentos ao vencimento a deduzir','Euro',    '',                                                'adiantamentos_deduzir',  'money'],
    ['Bónus/Bonificações',          'Euro',             '',                                                'bonus_bonificacoes',     'money'],

    ['Prevenções (Sim/Não)',        'Escolha Sim/Não',  '',                                                'prevencoes',             'bool'],
    ['Penhoras',                    'Escolha Sim/Não',  '',                                                'penhoras_sn',            'bool'],

    ['Férias',                      'Data',             'Criar duas ref’s na DB, DataStart + DataEnd (Aqui aparece texto composto ( "De" DataStart "a" DataEnd) )', null, 'holiday_period'],
    ['Faltas Justificadas Não Remuneradas','Número (Dias)', '',                                            'faltas_nao_rem',         'int'],
    ['Faltas Justificadas Remuneradas','Número (Dias)',  '',                                               'faltas_rem_just',        'int'],
    ['Faltas Injustificadas Não Remuneradas','Número (Dias)','',                                           'faltas_nao_rem_just',    'int'],

    ['Baixa médica',                'Data',             'Criar duas ref’s na DB, DataStart + DataEnd (Aqui aparece texto composto ("De" DataStart "a" DataEnd))', null, 'sickleave_period'],

    ['Observações',                 'Caixa Texto',      '',                                                'observacoes',            'text'],
    ['Ajustes Vencimento',          'Caixa Texto',      '',                                                'ajustes_vencimento',     'text'],
];

/* helpers para campos compostos (períodos) – ajusta nomes conforme a tua tabela */
$periods = [
    'holiday_period'   => ['start' => 'ferias_start',       'end' => 'ferias_end'],
    'sickleave_period' => ['start' => 'baixa_medica_start',  'end' => 'baixa_medica_end'],
];

/* --------- construir o Excel --------- */
$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$sheet->setTitle('Ficha Financeira');

/* estilos base */
$thFill = [ 'fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6EEF8'] ];
$thStyle = [
    'font' => ['bold'=>true],
    'fill' => $thFill,
    'borders' => ['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['argb'=>'FFB7B7B7']]],
    'alignment' => ['vertical'=>Alignment::VERTICAL_CENTER]
];
$tdBorders = ['borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['argb'=>'FFE0E0E0']]]];

$r = 1;

/* identificação do colaborador */
$sheet->setCellValue("A{$r}", 'Colaborador'); $sheet->setCellValue("B{$r}", $u['nome']); $r++;
$sheet->setCellValue("A{$r}", 'Email');       $sheet->setCellValue("B{$r}", $u['email']); $r += 2;

/* cabeçalho tabela especificação */
$sheet->setCellValue("A{$r}", 'CAMPO');
$sheet->setCellValue("B{$r}", 'TIPO');
$sheet->setCellValue("C{$r}", 'NOTAS');
$sheet->setCellValue("D{$r}", 'VALOR');
$sheet->getStyle("A{$r}:D{$r}")->applyFromArray($thStyle);
$sheet->getRowDimension($r)->setRowHeight(22);
$r++;

/* preencher linhas */
foreach ($spec as [$label, $tipo, $notas, $col, $render]) {
    $sheet->setCellValue("A{$r}", $label);
    $sheet->setCellValue("B{$r}", $tipo);
    $sheet->setCellValue("C{$r}", $notas);

    $val = null;
    if ($render === 'holiday_period' || $render === 'sickleave_period') {
        $startKey = $periods[$render]['start'];
        $endKey   = $periods[$render]['end'];
        $start = $row[$startKey] ?? null;
        $end   = $row[$endKey] ?? null;
        if ($start || $end) {
            // texto composto "De dd/mm/yyyy a dd/mm/yyyy"
            $fmt = static function($d){ if(!$d) return ''; $t=strtotime((string)$d); return $t?date('d/m/Y',$t):$d; };
            $val = 'De '.($fmt($start) ?: '—').' a '.($fmt($end) ?: '—');
        } else {
            $val = '';
        }
    } else {
        $val = ($col !== null && array_key_exists($col,$row)) ? $row[$col] : null;
    }

    // render por tipo
    $cell = "D{$r}";
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

    // bordas
    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray($tdBorders);
    // notas com wrap
    $sheet->getStyle("C{$r}")->getAlignment()->setWrapText(true);
    $r++;
}

/* larguras */
$sheet->getColumnDimension('A')->setWidth(36);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(60);
$sheet->getColumnDimension('D')->setWidth(26);

/* congelar cabeçalho da tabela */
$sheet->freezePane('A'.($r > 5 ? 5 : 4));

/* output */
$filename = "ficha_financeira_{$userId}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($ss);
$writer->save('php://output');
exit;
