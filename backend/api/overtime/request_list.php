<?php
// api/overtime/request_list.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* Auth */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$meId    = (int)($_SESSION['user']['id'] ?? 0);
$myPerms = $_SESSION['user']['permissions'] ?? [];
$hasP4   = is_array($myPerms) && in_array(4, $myPerms, true); // request_overtime
$hasP5   = is_array($myPerms) && in_array(5, $myPerms, true); // approve_overtime
if (!$hasP4 && !$hasP5) { http_response_code(403); json_error('FORBIDDEN_PERMISSION', 403); }

/* DB */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* nome em user: nome|name */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); } catch(Throwable $e){ $nameCol = 'name'; }

/* Inputs */
$state  = isset($_GET['state']) ? strtolower(trim((string)$_GET['state'])) : 'all';
$valid  = ['requested','approved','rejected','cancelled','all'];
if (!in_array($state, $valid, true)) $state = 'all';

$month  = isset($_GET['month']) ? trim((string)$_GET['month']) : '';
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;   // só usado se $hasP5
$q      = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$limit  = max(1, (int)($_GET['limit']  ?? 200));
$offset = max(0, (int)($_GET['offset'] ?? 0));

if ($month !== '' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) { http_response_code(400); json_error('INVALID_MONTH_FORMAT'); }
if ($userId > 0 && !$hasP5) { http_response_code(403); json_error('FORBIDDEN_SCOPE'); }

/* limites do mês */
$d1 = $d2 = null;
if ($month !== '') {
    $first = new DateTime("$month-01");
    $last  = (clone $first)->modify('last day of this month');
    $d1 = $first->format('Y-m-d');
    $d2 = $last->format('Y-m-d');
}

/* WHERE */
$where  = [];
$params = [];

if ($state !== 'all') { $where[] = "ro.estado = :st"; $params[':st'] = $state; }
if ($d1 && $d2) { $where[] = "DATE(ro.data_inicio) BETWEEN :d1 AND :d2"; $params[':d1']=$d1; $params[':d2']=$d2; }
if ($hasP5 && $userId > 0) { $where[] = "ro.user_id = :uid"; $params[':uid']=$userId; }

/* pesquisa textual — placeholders distintos */
if ($q !== '') {
    $where[] = "(u.$nameCol LIKE :q1 OR u.email LIKE :q2 OR cb.$nameCol LIKE :q3)";
    $params[':q1'] = "%$q%";
    $params[':q2'] = "%$q%";
    $params[':q3'] = "%$q%";
}

/* escopo para quem NÃO tem perm 5: NÃO repetir o mesmo placeholder */
if (!$hasP5) {
    $where[] = "(ro.user_id = :me1 OR ro.criado_por = :me2)";
    $params[':me1'] = $meId;
    $params[':me2'] = $meId;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* Query principal */
$sql = "
SELECT
  ro.id,
  ro.user_id,
  u.$nameCol           AS user_name,
  u.email              AS user_email,
  ro.data_inicio,
  ro.data_fim,
  DATE(ro.data_inicio) AS dia,
  TIMESTAMPDIFF(MINUTE, ro.data_inicio, ro.data_fim) AS minutos,
  ro.estado,
  ro.justificacao,
  ro.criado_em,
  ro.criado_por,
  cb.$nameCol          AS criado_por_nome,
  ro.decidido_por,
  db.$nameCol          AS decidido_por_nome,
  ro.decidido_em,
  ro.comentario,
  ro.ficheiro
FROM request_overtime ro
JOIN user u   ON u.id  = ro.user_id
JOIN user cb  ON cb.id = ro.criado_por
LEFT JOIN user db ON db.id = ro.decidido_por
$whereSql
ORDER BY ro.criado_em DESC, ro.id DESC
LIMIT :limit OFFSET :offset
";
$sth = $pdo->prepare($sql);
foreach ($params as $k=>$v) { $sth->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR); }
$sth->bindValue(':limit', $limit, PDO::PARAM_INT);
$sth->bindValue(':offset', $offset, PDO::PARAM_INT);
$sth->execute();
$rows = $sth->fetchAll(PDO::FETCH_ASSOC);

/* Total */
$sqlCount = "
SELECT COUNT(*)
FROM request_overtime ro
JOIN user u  ON u.id  = ro.user_id
JOIN user cb ON cb.id = ro.criado_por
LEFT JOIN user db ON db.id = ro.decidido_por
$whereSql
";
$stc = $pdo->prepare($sqlCount);
foreach ($params as $k=>$v) { $stc->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR); }
$stc->execute();
$total = (int)$stc->fetchColumn();

/* Resposta */
$items = [];
foreach ($rows as $r) {
    $items[] = [
        'id'      => (int)$r['id'],
        'colaborador' => [
            'id'    => (int)$r['user_id'],
            'nome'  => $r['user_name'],
            'email' => $r['user_email'],
        ],
        'dia'          => $r['dia'],
        'hora_inicio'  => substr($r['data_inicio'], 11, 5),
        'hora_fim'     => substr($r['data_fim'], 11, 5),
        'data_inicio'  => $r['data_inicio'],
        'data_fim'     => $r['data_fim'],
        'minutos'      => (int)$r['minutos'],
        'estado'       => $r['estado'],
        'isApproved'   => ($r['estado'] === 'approved'),
        'criado_em'    => $r['criado_em'],
        'criado_por'   => ['id'=>(int)$r['criado_por'], 'nome'=>$r['criado_por_nome']],
        'decidido_por' => $r['decidido_por'] ? ['id'=>(int)$r['decidido_por'], 'nome'=>$r['decidido_por_nome']] : null,
        'decidido_em'  => $r['decidido_em'],
        'justificacao' => $r['justificacao'] ?? null,
        'comentario'   => $r['comentario'],
        'ficheiro'     => $r['ficheiro'],
    ];
}

echo json_encode([
    'ok' => true,
    'filters' => [
        'state'   => $state,
        'month'   => $month ?: null,
        'user_id' => ($hasP5 && $userId>0) ? $userId : null,
        'q'       => $q ?: null,
        'limit'   => $limit,
        'offset'  => $offset,
    ],
    'total' => $total,
    'items' => $items,
], JSON_UNESCAPED_UNICODE);
