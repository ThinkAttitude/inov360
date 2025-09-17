<?php
// api/timesheets/periods_users_export.php
declare(strict_types=1);
session_start();

/* --- Auth --- */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) { http_response_code(401); exit; }

$myPerms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($myPerms) || !in_array(3, $myPerms, true)) { // exige permissão 3
    http_response_code(403);
    echo json_encode(['ok'=>false,'code'=>'FORBIDDEN_PERMISSION']); exit;
}

/* --- Deps & DB --- */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* nome em user: nome|name */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

/* --- Inputs --- */
$month = trim((string)($_GET['month'] ?? ''));
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) { http_response_code(400); echo 'INVALID month (use YYYY-MM)'; exit; }

/* user_ids: aceita array (user_ids[]=) ou CSV (user_ids=1,2) */
$userIdsRaw = $_GET['user_ids'] ?? '';
if (!is_array($userIdsRaw)) {
    $userIdsRaw = preg_split('/[,\s]+/', (string)$userIdsRaw, -1, PREG_SPLIT_NO_EMPTY);
}
$userIds = array_values(array_unique(array_map('intval', $userIdsRaw)));
$userIds = array_values(array_filter($userIds, fn($v)=>$v>0));
if (!$userIds) { http_response_code(400); echo 'Missing user_ids'; exit; }

/* --- Palavras-chave para LEAVE (no título do evento) --- */
$FERIAS_LIKE = ["%FERIA%", "%FÉRIA%", "%VACATION%"];
$FALTA_LIKE  = ["%FALTA%", "%ABSENCE%"];

/* --- WHERE e parâmetros --- */
$params = [':month' => $month];
$inPlaceholders = [];
foreach ($userIds as $i => $id) {
    $ph = ":u{$i}";
    $inPlaceholders[] = $ph;
    $params[$ph] = $id;
}
$where = "
    e.status = 'approved'
    AND DATE_FORMAT(e.dia, '%Y-%m') = :month
    AND e.user_id IN (" . implode(',', $inPlaceholders) . ")
";

/* montar condições LIKE */
$ferConds = [];
foreach ($FERIAS_LIKE as $i => $kw) {
    $ph = ":fer{$i}";
    $ferConds[] = "UPPER(e.titulo) LIKE $ph";
    $params[$ph] = strtoupper($kw);
}
$faltaConds = [];
foreach ($FALTA_LIKE as $i => $kw) {
    $ph = ":fal{$i}";
    $faltaConds[] = "UPPER(e.titulo) LIKE $ph";
    $params[$ph] = strtoupper($kw);
}
$feriasLikeSql = '(' . implode(' OR ', $ferConds) . ')';
$faltaLikeSql  = '(' . implode(' OR ', $faltaConds) . ')';

/* --- Query (agregação por colaborador) --- */
$sql = "
SELECT
    c.name                                     AS empresa,
    u.$nameCol                                 AS nome,
    u.email                                    AS email,
    COUNT(DISTINCT e.dia)                      AS dias_com_registo,
    COALESCE(SUM(CASE WHEN e.tipo='WORK'     THEN e.minutos ELSE 0 END),0) AS m_trab,
    COALESCE(SUM(CASE WHEN e.tipo='OVERTIME' THEN e.minutos ELSE 0 END),0) AS m_extra,
    COALESCE(SUM(CASE WHEN e.tipo='ONCALL'   THEN e.minutos ELSE 0 END),0) AS m_pres,
    COALESCE(SUM(CASE WHEN e.tipo='LEAVE' AND $feriasLikeSql THEN 1 ELSE 0 END),0) AS total_ferias,
    COALESCE(SUM(CASE WHEN e.tipo='LEAVE' AND $faltaLikeSql  THEN 1 ELSE 0 END),0) AS total_faltas,
    COALESCE(SUM(CASE WHEN e.tipo='KM' THEN e.km ELSE 0 END),0) AS kms
FROM eventos e
JOIN user u         ON u.id = e.user_id
LEFT JOIN company c ON c.id = u.company_id
WHERE $where
GROUP BY empresa, nome, email
ORDER BY empresa ASC, nome ASC
";

$sth = $pdo->prepare($sql);
foreach ($params as $k=>$v) {
    $sth->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$sth->execute();
$rows = $sth->fetchAll(PDO::FETCH_ASSOC);

/* --- Excel --- */
$spread = new Spreadsheet();
$sheet  = $spread->getActiveSheet();
$sheet->setTitle('Mapa ' . $month);

$headers = [
    'Empresa','Nome','Email',
    'Dias com registo',
    'Horas Trab.','Horas Extr.','Horas Pres.',
    'Total Férias','Total Faltas',
    'Quilómetros'
];
$sheet->fromArray($headers, null, 'A1');

$r = 2;
foreach ($rows as $row) {
    $sheet->fromArray([
        $row['empresa'] ?? '',
        $row['nome']    ?? '',
        $row['email']   ?? '',
        (int)$row['dias_com_registo'],
        intdiv((int)$row['m_trab'], 60),
        intdiv((int)$row['m_extra'], 60),
        intdiv((int)$row['m_pres'], 60),
        (int)$row['total_ferias'],
        (int)$row['total_faltas'],
        (float)$row['kms'],
    ], null, "A{$r}");
    $r++;
}
$lastRow = max(1, $r-1);

/* Aparência mínima + AutoFilter */
foreach (range('A','J') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
$sheet->freezePane('A2');
$sheet->getStyle("A1:J1")->getFont()->setBold(true);

if ($lastRow >= 2) {
    $sheet->setAutoFilter("A1:J{$lastRow}");
} else {
    $sheet->setAutoFilter("A1:J1");
}

/* --- Output --- */
$filename = 'mapa_colaboradores_'.$month.'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spread);
$writer->save('php://output');
exit;
