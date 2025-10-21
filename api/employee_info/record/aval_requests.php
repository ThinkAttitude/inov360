<?php
// api/employee_record/list_requests.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// 1) Auth + permission record_managment (id 6)
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']); exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(6, $perms, true)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN']); exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2) Query params (GET)
    $status     = isset($_GET['status']) ? strtolower(trim((string)$_GET['status'])) : 'pendente'; // pendente|aprovado|recusado|all
    $type       = isset($_GET['type'])   ? strtolower(trim((string)$_GET['type']))   : 'all';      // profile|emergency|all
    $userId     = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;                          // filtra por colaborador
    $q          = isset($_GET['q']) ? trim((string)$_GET['q']) : '';                                // pesquisa por nome/email
    $dateFrom   = isset($_GET['date_from']) ? trim((string)$_GET['date_from']) : '';                // YYYY-MM-DD
    $dateTo     = isset($_GET['date_to']) ? trim((string)$_GET['date_to']) : '';                    // YYYY-MM-DD
    $page       = max(1, (int)($_GET['page'] ?? 1));
    $pageSize   = min(100, max(1, (int)($_GET['page_size'] ?? 20)));
    $offset     = ($page - 1) * $pageSize;

    // 3) Montar WHERE dinâmico para cada origem
    $where = [];
    $params = [];

    if ($status && $status !== 'all') {
        $where[] = "t.estado = ?";
        $params[] = $status;
    }
    if (!empty($userId)) {
        $where[] = "t.user_id = ?";
        $params[] = $userId;
    }
    if ($dateFrom !== '') {
        $where[] = "t.criado_em >= ?";
        $params[] = $dateFrom . " 00:00:00";
    }
    if ($dateTo !== '') {
        $where[] = "t.criado_em <= ?";
        $params[] = $dateTo . " 23:59:59";
    }
    // Filtro por tipo (aplicado na camada externa)
    $typeFilter = '';
    if ($type === 'profile')   $typeFilter = "WHERE t.tipo = 'profile'";
    if ($type === 'emergency') $typeFilter = "WHERE t.tipo = 'emergency'";

    // 4) UNION normalizado (colunas iguais para ambas as tabelas)
    //    - Campos de perfil: email, telefone, morada, nib
    //    - Campos de emergência: em_nome, em_parentesco, em_telefone
    //    - Outros: estado, criado_em, avaliado_por, avaliado_em
    $baseUnion = "
        SELECT
            ce.id              AS req_id,
            ce.user_id         AS user_id,
            'profile'          AS tipo,
            ce.estado          AS estado,
            ce.criado_em       AS criado_em,
            ce.avaliado_por    AS avaliado_por,
            ce.avaliado_em     AS avaliado_em,

            ce.email           AS email,
            ce.telefone        AS telefone,
            ce.morada          AS morada,
            ce.nib             AS nib,

            NULL               AS em_nome,
            NULL               AS em_parentesco,
            NULL               AS em_telefone
        FROM inov360.colaborador_edicoes ce

        UNION ALL

        SELECT
            ee.id              AS req_id,
            ee.user_id         AS user_id,
            'emergency'        AS tipo,
            ee.estado          AS estado,
            ee.criado_em       AS criado_em,
            ee.avaliado_por    AS avaliado_por,
            ee.avaliado_em     AS avaliado_em,

            NULL               AS email,
            NULL               AS telefone,
            NULL               AS morada,
            NULL               AS nib,

            ee.nome            AS em_nome,
            ee.parentesco      AS em_parentesco,
            ee.telefone        AS em_telefone
        FROM inov360.contactos_emergencia_edicoes ee
    ";

    // 5) WHERE aplicado à união (estado, user, datas)
    $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

    // 6) Envolver a união + filtros e juntar com utilizador (para nome/email)
    $sqlMain = "
        SELECT
            t.req_id, t.tipo, t.estado, t.criado_em, t.avaliado_por, t.avaliado_em,
            t.user_id,
            u.name  AS user_name,
            u.email AS user_email,

            t.email, t.telefone, t.morada, t.nib,
            t.em_nome, t.em_parentesco, t.em_telefone,

            u2.name AS avaliado_por_name
        FROM (
            SELECT * FROM (
                $baseUnion
            ) AS t
            $sqlWhere
        ) AS t
        LEFT JOIN inov360.`user` u  ON u.id = t.user_id
        LEFT JOIN inov360.`user` u2 ON u2.id = t.avaliado_por
        $typeFilter
        ORDER BY t.criado_em DESC, t.req_id DESC
        LIMIT $pageSize OFFSET $offset
    ";

    $stmt = $pdo->prepare($sqlMain);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7) Total para paginação (mesmo WHERE e typeFilter)
    $sqlCount = "
        SELECT COUNT(*) AS total FROM (
            SELECT * FROM (
                $baseUnion
            ) AS t
            $sqlWhere
        ) AS t
        $typeFilter
    ";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetchColumn();
    $totalPages = (int)ceil($total / $pageSize);

    // 8) Pesquisa por q (nome/email) — aplico no PHP para não complicar params do UNION;
    //    se precisares que seja no SQL, refaço com subselects nomeados.
    if ($q !== '') {
        $qLower = mb_strtolower($q);
        $rows = array_values(array_filter($rows, function($r) use ($qLower) {
            return (strpos(mb_strtolower($r['user_name'] ?? ''), $qLower) !== false) ||
                (strpos(mb_strtolower($r['user_email'] ?? ''), $qLower) !== false);
        }));
        // Nota: o total deixa de refletir o filtro 'q' aqui. Se precisares do total já filtrado por 'q',
        // posso mover o filtro para SQL.
    }

    echo json_encode([
        'success'     => true,
        'page'        => $page,
        'page_size'   => $pageSize,
        'total'       => $total,
        'total_pages' => $totalPages,
        'items'       => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    // error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'SERVER_ERROR']);
}
