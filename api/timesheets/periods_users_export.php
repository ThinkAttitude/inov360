<?php
// api/timesheets/periods_users_export.php
declare(strict_types=1);
session_start();

/* Auth */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) { http_response_code(401); exit; }
$myPerms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($myPerms) || !in_array(3, $myPerms, true)) { // perm 3: periods_info (backoffice)
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'code'=>'FORBIDDEN_PERMISSION']); exit;
}

/* Deps & DB */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/periods.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* nome em user: nome|name */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

/* Inputs */
$month = trim((string)($_GET['month'] ?? ''));
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) { http_response_code(400); echo 'INVALID month (use YYYY-MM)'; exit; }

/* user_ids (opcional) */
$userIdsRaw = $_GET['user_ids'] ?? '';
if (!is_array($userIdsRaw)) {
    $userIdsRaw = preg_split('/[,\s]+/', (string)$userIdsRaw, -1, PREG_SPLIT_NO_EMPTY);
}
$userIds = array_values(array_filter(array_map('intval', $userIdsRaw), fn($v)=>$v>0));

/* Bounds 25..24 para a competência M */
[$sDt,$eDt] = ts_bounds_from_label($month);
$start = $sDt->format('Y-m-d');
$end   = $eDt->format('Y-m-d');

/* Palavras-chave para LEAVE no título */
$FERIAS_LIKE = ["%FERIA%", "%FÉRIA%", "%VACATION%"];
$FALTA_LIKE  = ["%FALTA%", "%ABSENCE%"];

/* WHERE */
$params = [':s'=>$start, ':e'=>$end];
$in = '';
if ($userIds) {
    $ph = [];
    foreach ($userIds as $i=>$id){ $k=":u$i"; $ph[]=$k; $params[$k]=$id; }
    $in = ' AND e.user_id IN ('.implode(',',$ph).')';
}

/* LIKEs */
$ferConds=[]; foreach($FERIAS_LIKE as $i=>$kw){ $k=":fer$i"; $ferConds[]="UPPER(e.titulo) LIKE $k"; $params[$k]=strtoupper($kw); }
$faltaConds=[]; foreach($FALTA_LIKE as $i=>$kw){ $k=":fal$i"; $faltaConds[]="UPPER(e.titulo) LIKE $k"; $params[$k]=strtoupper($kw); }
$feriasLikeSql = '('.implode(' OR ',$ferConds).')';
$faltaLikeSql  = '('.implode(' OR ',$faltaConds).')';

/* Query agregada (apenas eventos aprovados) */
$sql = "
SELECT
    c.name AS empresa,
    u.$nameCol AS nome,
    u.email AS email,
    COUNT(DISTINCT e.dia) AS dias_com_registo,
    COALESCE(SUM(CASE WHEN e.tipo='WORK'   THEN e.minutos ELSE 0 END),0) AS m_trab,
    COALESCE(SUM(CASE WHEN e.tipo='ONCALL' THEN e.minutos ELSE 0 END),0) AS m_pres,
    COALESCE(SUM(CASE WHEN e.tipo='LEAVE' AND $feriasLikeSql THEN 1 ELSE 0 END),0) AS total_ferias,
    COALESCE(SUM(CASE WHEN e.tipo='LEAVE' AND $faltaLikeSql  THEN 1 ELSE 0 END),0) AS total_faltas,
    COALESCE(SUM(CASE WHEN e.tipo='KM'     THEN e.km      ELSE 0 END),0) AS kms
FROM eventos e
JOIN user u         ON u.id = e.user_id
LEFT JOIN company c ON c.id = u.company_id
WHERE e.status = 'approved'
  AND e.dia BETWEEN :s AND :e
  $in
GROUP BY empresa, nome, email
ORDER BY empresa ASC, nome ASC
";
$sth = $pdo->prepare($sql);
foreach ($params as $k=>$v) $sth->bindValue($k, $v, is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR);
$sth->execute();
$rows = $sth->fetchAll(PDO::FETCH_ASSOC);

/* Excel */
$spread = new Spreadsheet();
$sheet  = $spread->getActiveSheet();
$sheet->setTitle('Mapa '.$month.' (25..24)');

$headers = [
    'Empresa','Nome','Email',
    'Dias com registo',
    'Horas Trab.','Horas Prev.','Total Férias','Total Faltas',
    'Quilómetros',
    'Período Início','Período Fim'
];
$sheet->fromArray($headers, null, 'A1');

$r=2;
foreach ($rows as $row) {
    $hTrab = intdiv((int)$row['m_trab'], 60);
    $mTrab = ((int)$row['m_trab']) % 60;
    $hPrev = intdiv((int)$row['m_pres'], 60);
    $mPrev = ((int)$row['m_pres']) % 60;

    $sheet->fromArray([
        $row['empresa'] ?? '',
        $row['nome']    ?? '',
        $row['email']   ?? '',
        (int)$row['dias_com_registo'],
        sprintf('%d:%02d',$hTrab,$mTrab),
        sprintf('%d:%02d',$hPrev,$mPrev),
        (int)$row['total_ferias'],
        (int)$row['total_faltas'],
        (float)$row['kms'],
        $start, $end
    ], null, "A{$r}");
    $r++;
}
foreach (range('A','K') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
$sheet->freezePane('A2'); $sheet->getStyle('A1:K1')->getFont()->setBold(true);
$last = max(1,$r-1); $sheet->setAutoFilter("A1:K{$last}");

/* Output */
$filename = 'mapa_timesheets_'.$month.'_25-24.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spread);
$writer->save('php://output');
exit;
