<?php
// api/users/get_hierarchy.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* --- Auth --- */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'code'=>'UNAUTHENTICATED']); exit;
}

/*
 * --- user_id ---
 * Se vier no query ‘string’, usamos esse.
 * Se não vier (ou vier vazio), usamos o utilizador autenticado da sessão.
 */
$sessionUserId = 0;
if (!empty($_SESSION['user']) && is_array($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
    $sessionUserId = (int)$_SESSION['user']['id'];
}

if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
    $userId = (int)$_GET['user_id'];
} else {
    $userId = $sessionUserId;
}

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'code'=>'MISSING_USER_ID']); exit;
}

/* --- DB --- */
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* Nome do colaborador: 'nome' ou 'name' */
    $nameCol = 'nome';
    try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
    catch(Throwable $e){ $nameCol = 'name'; }

    /* Filtro: só relações ativas e dentro da validade */
    $validWhere = "
        cr.ativo = 1
        AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
        AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
    ";

    /* --- RESPONSÁVEIS (onde o user é colaborador) --- */
    $sqlResp = "
        SELECT
            cr.id      AS rel_id,
            u.id       AS user_id,
            u.$nameCol AS nome,
            u.email    AS email
        FROM colaborador_responsaveis cr
        JOIN user u ON u.id = cr.responsavel_id
        WHERE cr.colaborador_id = :uid
          AND $validWhere
        ORDER BY u.$nameCol ASC, u.id ASC
    ";
    $stResp = $pdo->prepare($sqlResp);
    $stResp->execute([':uid' => $userId]);
    $responsaveisRows = $stResp->fetchAll(PDO::FETCH_ASSOC);

    /* --- SUBORDINADOS (onde o user é responsável) --- */
    $sqlSub = "
        SELECT
            cr.id      AS rel_id,
            u.id       AS user_id,
            u.$nameCol AS nome,
            u.email    AS email
        FROM colaborador_responsaveis cr
        JOIN user u ON u.id = cr.colaborador_id
        WHERE cr.responsavel_id = :uid
          AND $validWhere
        ORDER BY u.$nameCol ASC, u.id ASC
    ";
    $stSub = $pdo->prepare($sqlSub);
    $stSub->execute([':uid' => $userId]);
    $subordinadosRows = $stSub->fetchAll(PDO::FETCH_ASSOC);

    /* --- Montar listas no formato simples --- */
    $responsaveis = array_map(function($r){
        return [
            'rel_id' => (int)$r['rel_id'],
            'user'   => [
                'id'    => (int)$r['user_id'],
                'nome'  => $r['nome'],
                'email' => $r['email'] ?? null,
            ],
        ];
    }, $responsaveisRows);

    $subordinados = array_map(function($r){
        return [
            'rel_id' => (int)$r['rel_id'],
            'user'   => [
                'id'    => (int)$r['user_id'],
                'nome'  => $r['nome'],
                'email' => $r['email'] ?? null,
            ],
        ];
    }, $subordinadosRows);

    echo json_encode([
        'ok'           => true,
        'user_id'      => $userId,
        'responsaveis' => $responsaveis,
        'subordinados' => $subordinados,
    ], JSON_UNESCAPED_UNICODE); exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'code'=>'SERVER_ERROR']); exit;
}
