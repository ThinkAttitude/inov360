<?php
// api/users/my_subordinates.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* --- Auth --- */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
$selfId = (int)($_SESSION['user']['id'] ?? 0);
if ($selfId <= 0) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}

/* --- DB --- */
require_once __DIR__ . '/includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Nome do colaborador: 'nome' ou 'name' */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM inov360.user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

/* --- Filtros opcionais --- */
/* state=active|all|inactive (default: active) */
$state = $_GET['state'] ?? 'active';
$validStates = ['active','all','inactive'];
if (!in_array($state, $validStates, true)) $state = 'active';

/* --- WHERE dinâmico para a relação --- */
$where = ["cr.responsavel_id = :me"];
$params = [":me" => $selfId];

if ($state === 'active') {
    $where[] = "cr.ativo = 1";
    $where[] = "(cr.valido_desde IS NULL OR cr.valido_desde <= NOW())";
    $where[] = "(cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())";
} elseif ($state === 'inactive') {
    // qualquer relação não ativa OU expirada / ainda não válida
    $where[] = "(cr.ativo = 0
                OR (cr.valido_desde IS NOT NULL AND cr.valido_desde > NOW())
                OR (cr.valido_ate   IS NOT NULL AND cr.valido_ate   < NOW()))";
}

/* Pesquisa opcional por nome/email: q=... */
if (!empty($_GET['q'])) {
    $where[] = "(u.$nameCol LIKE :q OR u.email LIKE :q)";
    $params[':q'] = "%".trim((string)$_GET['q'])."%";
}

$whereSql = 'WHERE '.implode(' AND ', $where);

/* --- Query --- */
$sql = "
SELECT
    cr.id            AS rel_id,
    u.id             AS colaborador_id,
    u.$nameCol       AS nome,
    u.email          AS email,
    cr.ativo,
    cr.valido_desde,
    cr.valido_ate,
    cr.created_by,
    cr.created_at
FROM inov360.colaborador_responsaveis cr
JOIN inov360.user u ON u.id = cr.colaborador_id
$whereSql
ORDER BY u.$nameCol ASC, u.id ASC
";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

/* --- Resposta --- */
echo json_encode([
    "ok"    => true,
    "state" => $state,
    "total" => count($rows),
    "items" => array_map(function($r){
        return [
            "rel_id"         => (int)$r['rel_id'],
            "colaborador"    => [
                "id"    => (int)$r['colaborador_id'],
                "nome"  => $r['nome'],
                "email" => $r['email'] ?? null,
            ],
            "relacao"        => [
                "ativo"        => (int)$r['ativo'] === 1,
                "valido_desde" => $r['valido_desde'],
                "valido_ate"   => $r['valido_ate'],
                "created_by"   => isset($r['created_by']) ? (int)$r['created_by'] : null,
                "created_at"   => $r['created_at'],
            ],
        ];
    }, $rows),
], JSON_UNESCAPED_UNICODE);
