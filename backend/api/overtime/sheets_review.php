<?php
// api/overtime/sheets_review.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* Auth */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(['ok'=>false,'code'=>'UNAUTHENTICATED']); exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(5, $perms, true)) { // approve_overtime
    http_response_code(403); echo json_encode(['ok'=>false,'code'=>'FORBIDDEN_PERMISSION']); exit;
}

/* Deps & DB */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* nome em user: nome|name */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); } catch(Throwable $e){ $nameCol = 'name'; }

/* Inputs */
$month  = trim((string)($_GET['month'] ?? ''));            // YYYY-MM (obrigatório)
$state  = strtolower(trim((string)($_GET['state'] ?? 'both'))); // approved|rejected|both
$userId = (int)($_GET['user_id'] ?? 0);                    // opcional
$q      = trim((string)($_GET['q'] ?? ''));                // opcional (nome/email)
$limit  = max(1, (int)($_GET['limit']  ?? 200));
$offset = max(0, (int)($_GET['offset'] ?? 0));

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) { http_response_code(400); echo json_encode(['ok'=>false,'code'=>'INVALID_MONTH']); exit; }
if (!in_array($state, ['approved','rejected','both'], true)) $state = 'both';

/* WHERE */
$where = ["DATE_FORMAT(ro.data_inicio,'%Y-%m') = :m"];
$params = [':m'=>$month];

if ($state !== 'both') { $where[] = "ro.estado = :st"; $params[':st'] = $state; }
else                   { $where[] = "ro.estado IN ('approved','rejected')"; }

if ($userId > 0) { $where[] = "ro.user_id = :uid"; $params[':uid'] = $userId; }

if ($q !== '') {
    // placeholders distintos para evitar HY093
    $where[] = "(u.$nameCol LIKE :q1 OR u.email LIKE :q2)";
    $params[':q1'] = "%$q%";
    $params[':q2'] = "%$q%";
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

/* Query principal: pedidos decididos + join ao overtime (se approved) */
$sql = "
SELECT
  ro.id,
  ro.user_id,
  u.$nameCol                 AS user_name,
  u.email                    AS user_email,
  c.name                     AS company_name,
  DATE(ro.data_inicio)        AS dia,
  ro.data_inicio,
  ro.data_fim,
  TIMESTAMPDIFF(MINUTE, ro.data_inicio, ro.data_fim) AS req_minutos,
  ro.estado,
  ro.justificacao,
  ro.decidido_por,
  db.$nameCol                AS decidido_por_nome,
  ro.decidido_em,
  ro.comentario,
  ro.ficheiro,
  ot.id                      AS overtime_id,
  ot.inicio                  AS ot_inicio,
  ot.fim                     AS ot_fim,
  CASE WHEN ot.id IS NULL THEN NULL
       ELSE TIMESTAMPDIFF(MINUTE, ot.inicio, ot.fim)
  END                        AS ot_minutos
FROM request_overtime ro
JOIN user u          ON u.id = ro.user_id
LEFT JOIN company c  ON c.id = u.company_id
LEFT JOIN user db    ON db.id = ro.decidido_por
LEFT JOIN overtime ot ON ot.request_id = ro.id
$whereSql
ORDER BY ro.decidido_em DESC, ro.id DESC
LIMIT :limit OFFSET :offset
";
$st = $pdo->prepare($sql);
foreach ($params as $k=>$v) $st->bindValue($k, $v, is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR);
$st->bindValue(':limit',$limit,PDO::PARAM_INT);
$st->bindValue(':offset',$offset,PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

/* Totais (approved vs rejected) para o mesmo filtro (sem limit/offset) */
$sqlCnt = "
SELECT ro.estado, COUNT(*) AS n
FROM request_overtime ro
JOIN user u ON u.id = ro.user_id
LEFT JOIN company c ON c.id = u.company_id
$whereSql
GROUP BY ro.estado
";
$sc = $pdo->prepare($sqlCnt);
foreach ($params as $k=>$v) $sc->bindValue($k, $v, is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR);
$sc->execute();
$tot = ['approved'=>0,'rejected'=>0];
foreach ($sc as $r) { $tot[$r['estado']] = (int)$r['n']; }

/* Transformação */
$items = [];
foreach ($rows as $r) {
    $items[] = [
        'id' => (int)$r['id'],
        'colaborador' => [
            'id'    => (int)$r['user_id'],
            'nome'  => $r['user_name'],
            'email' => $r['user_email'],
            'empresa' => $r['company_name'],
        ],
        'dia'          => $r['dia'],
        'hora_inicio'  => substr($r['data_inicio'], 11, 5),
        'hora_fim'     => substr($r['data_fim'], 11, 5),
        'req_minutos'  => (int)$r['req_minutos'],
        'estado'       => $r['estado'], // approved|rejected
        'justificacao' => $r['justificacao'] ?? null,
        'decidido_por' => $r['decidido_por'] ? [
            'id'   => (int)$r['decidido_por'],
            'nome' => $r['decidido_por_nome']
        ] : null,
        'decidido_em'  => $r['decidido_em'],
        'comentario'   => $r['comentario'],
        'ficheiro'     => $r['ficheiro'],
        'overtime'     => $r['overtime_id'] ? [
            'id'       => (int)$r['overtime_id'],
            'inicio'   => $r['ot_inicio'],
            'fim'      => $r['ot_fim'],
            'minutos'  => (int)$r['ot_minutos']
        ] : null,
        'consistencia' => $r['overtime_id'] ? ((int)$r['ot_minutos'] === (int)$r['req_minutos']) : null
    ];
}

/* Resposta */
echo json_encode([
    'ok' => true,
    'filters' => [
        'month'  => $month,
        'state'  => $state,
        'user_id'=> $userId ?: null,
        'q'      => $q ?: null,
        'limit'  => $limit,
        'offset' => $offset,
    ],
    'totals' => [
        'approved' => $tot['approved'],
        'rejected' => $tot['rejected'],
        'all'      => $tot['approved'] + $tot['rejected']
    ],
    'items' => $items
], JSON_UNESCAPED_UNICODE);
