<?php
// api/overtime/sheets_export.php
declare(strict_types=1);
session_start();

/* Auth */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) { http_response_code(401); exit; }
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(5, $perms, true)) { // approve_overtime
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    json_error('FORBIDDEN_PERMISSION', 403);
}

/* Deps & DB */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/helper/responses.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* nome em user: nome|name */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); } catch(Throwable $e){ $nameCol = 'name'; }

/* Inputs */
$month = trim((string)($_GET['month'] ?? ''));
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) { http_response_code(400); echo 'INVALID month (use YYYY-MM)'; exit; }

/* user_ids opcional */
$userIdsRaw = $_GET['user_ids'] ?? '';
if (!is_array($userIdsRaw)) {
    $userIdsRaw = preg_split('/[,\s]+/', (string)$userIdsRaw, -1, PREG_SPLIT_NO_EMPTY);
}
$userIds = array_values(array_filter(array_map('intval', $userIdsRaw), fn($v)=>$v>0));

$params = [':m' => $month];
$in = '';
if ($userIds) {
    $ph = [];
    foreach ($userIds as $i=>$id){ $k=":u$i"; $ph[]=$k; $params[$k]=$id; }
    $in = ' AND o.user_id IN ('.implode(',',$ph).')';
}

/* ====== Query RESUMO (agregado por colaborador) ====== */
$sqlResumo = "
SELECT
  c.name AS empresa,
  u.$nameCol AS nome,
  u.email AS email,
  COUNT(*) AS registos,
  SUM(TIMESTAMPDIFF(MINUTE, o.inicio, o.fim)) AS min_extra,
  MIN(o.inicio) AS primeiro_inicio,
  MAX(o.fim)    AS ultimo_fim
FROM overtime o
JOIN user u         ON u.id = o.user_id
LEFT JOIN company c ON c.id = u.company_id
WHERE DATE_FORMAT(o.dia, '%Y-%m') = :m
$in
GROUP BY empresa, nome, email
ORDER BY empresa ASC, nome ASC
";
$st = $pdo->prepare($sqlResumo);
foreach ($params as $k=>$v) $st->bindValue($k, $v, is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR);
$st->execute();
$rowsResumo = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

/* ====== Query DETALHE (cada registo) ====== */
$sqlDet = "
SELECT
  c.name AS empresa,
  u.$nameCol AS nome,
  u.email AS email,
  o.dia,
  o.inicio,
  o.fim,
  TIMESTAMPDIFF(MINUTE, o.inicio, o.fim) AS min_extra
FROM overtime o
JOIN user u         ON u.id = o.user_id
LEFT JOIN company c ON c.id = u.company_id
WHERE DATE_FORMAT(o.dia, '%Y-%m') = :m
$in
ORDER BY empresa ASC, nome ASC, o.inicio ASC
";
$sd = $pdo->prepare($sqlDet);
foreach ($params as $k=>$v) $sd->bindValue($k, $v, is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR);
$sd->execute();
$rowsDet = $sd->fetchAll(PDO::FETCH_ASSOC) ?: [];

/* === Excel === */
$ss = new Spreadsheet();

/* Sheet 1: Resumo */
$sheet1 = $ss->getActiveSheet();
$sheet1->setTitle('Horas Extra '.$month);
$headers1 = [
    'Empresa','Nome','Email',
    'Nº Registos',
    'Min Extra',
    'Horas Extra (H:MM)',
    '1º Início',
    'Último Fim'
];
$sheet1->fromArray($headers1, null, 'A1');

$r=2;
foreach ($rowsResumo as $row){
    $min = (int)($row['min_extra'] ?? 0);
    $hh  = intdiv($min,60);
    $mm  = $min % 60;
    $primeiro = $row['primeiro_inicio'] ? date('Y-m-d H:i', strtotime($row['primeiro_inicio'])) : '';
    $ultimo   = $row['ultimo_fim']      ? date('Y-m-d H:i', strtotime($row['ultimo_fim']))     : '';

    $sheet1->fromArray([
        $row['empresa'] ?? '',
        $row['nome']    ?? '',
        $row['email']   ?? '',
        (int)$row['registos'],
        $min,
        sprintf('%d:%02d',$hh,$mm),
        $primeiro,
        $ultimo,
    ], null, "A{$r}");
    $r++;
}
foreach (range('A','H') as $col) $sheet1->getColumnDimension($col)->setAutoSize(true);
$sheet1->freezePane('A2'); $sheet1->getStyle('A1:H1')->getFont()->setBold(true);
$last = max(1,$r-1);
$sheet1->setAutoFilter("A1:H{$last}");

/* Sheet 2: Detalhe */
$sheet2 = $ss->createSheet(1);
$sheet2->setTitle('Detalhe '.$month);
$headers2 = ['Empresa','Nome','Email','Dia','Início','Fim','Minutos','Horas (H:MM)'];
$sheet2->fromArray($headers2, null, 'A1');

$r=2;
foreach ($rowsDet as $row){
    $min = (int)($row['min_extra'] ?? 0);
    $hh  = intdiv($min,60);
    $mm  = $min % 60;
    $sheet2->fromArray([
        $row['empresa'] ?? '',
        $row['nome']    ?? '',
        $row['email']   ?? '',
        $row['dia']     ?? '',
        $row['inicio']  ? date('Y-m-d H:i', strtotime($row['inicio'])) : '',
        $row['fim']     ? date('Y-m-d H:i', strtotime($row['fim']))    : '',
        $min,
        sprintf('%d:%02d',$hh,$mm),
    ], null, "A{$r}");
    $r++;
}
foreach (range('A','H') as $col) $sheet2->getColumnDimension($col)->setAutoSize(true);
$sheet2->freezePane('A2'); $sheet2->getStyle('A1:H1')->getFont()->setBold(true);
$last = max(1,$r-1);
$sheet2->setAutoFilter("A1:H{$last}");

/* Output */
$filename = 'mapa_overtime_'.$month.'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($ss);
$writer->save('php://output');
exit;
