<?php
// api/collab_management/collab_org_tree.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* --- Auth --- */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'code'=>'UNAUTHENTICATED']); exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(1, $perms, true)) { // ajusta o ID de permissão
    http_response_code(403);
    echo json_encode(['ok'=>false,'code'=>'FORBIDDEN_PERMISSION']); exit;
}

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $format = ($_GET['format'] ?? 'nested') === 'flat' ? 'flat' : 'nested';

    // Detectar coluna de nome (nome|name), tal como fazes na outra API
    $nameCol = 'nome';
    try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
    catch(Throwable $e){ $nameCol = 'name'; }

    // 1) Users
    $users = $pdo->query("SELECT id, $nameCol AS nome, email FROM `user` ORDER BY $nameCol ASC")
        ->fetchAll(PDO::FETCH_ASSOC);
    if (!$users) { echo json_encode(['ok'=>true,'data'=>[]]); exit; }

    // Mapa base
    $map = [];
    $ids = [];
    foreach ($users as $u) {
        $id = (int)$u['id'];
        $ids[] = $id;
        $map[$id] = [
            'id'         => $id,
            'nome'       => $u['nome'],
            'email'      => $u['email'] ?? null,
            'manager_id' => null,
            'children'   => [],
        ];
    }
    $in = implode(',', array_fill(0, count($ids), '?'));

    // 2) Relações ativas (responsável)
    $sqlEdges = "
        SELECT cr.colaborador_id AS employee_id, cr.responsavel_id AS manager_id
        FROM colaborador_responsaveis cr
        WHERE cr.ativo = 1
          AND cr.colaborador_id IN ($in)
    ";
    $st = $pdo->prepare($sqlEdges);
    $st->execute($ids);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $e) {
        $eid = (int)$e['employee_id'];
        $mid = $e['manager_id'] !== null ? (int)$e['manager_id'] : null;
        if (isset($map[$eid])) $map[$eid]['manager_id'] = $mid;
    }

    if ($format === 'flat') {
        $flat = [];
        foreach ($map as $node) {
            $flat[] = [
                'id'         => $node['id'],
                'manager_id' => $node['manager_id'],
                'nome'       => $node['nome'],
                'email'      => $node['email'],
            ];
        }
        echo json_encode(['ok'=>true,'data'=>$flat], JSON_UNESCAPED_UNICODE); exit;
    }

    // 3) Montar nested
    $roots = [];
    foreach ($map as $id => &$node) {
        $m = $node['manager_id'];
        if ($m === null || !isset($map[$m])) $roots[] = &$node;
        else $map[$m]['children'][] = &$node;
    }
    unset($node);

    echo json_encode(['ok'=>true,'data'=>$roots], JSON_UNESCAPED_UNICODE); exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'code'=>'SERVER_ERROR','msg'=>$e->getMessage()]); exit;
}
