<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'UNAUTHENTICATED']);
    exit;
}

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(6, $perms, true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'FORBIDDEN']);
    exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $pageSize = min(100, max(1, (int)($_GET['page_size'] ?? 20)));
    $offset = ($page - 1) * $pageSize;

    $whereSearch = '';

    if ($q !== '') {
        $whereSearch = 'WHERE cd.nome LIKE :q_nome OR cd.email LIKE :q_email';
    }

    $sql = "
        SELECT
            p.user_id,
            cd.nome AS user_name,
            cd.email AS user_email,

            ce.id AS profile_req_id,
            CASE WHEN p.profile_req_id IS NULL THEN cd.email ELSE ce.email END AS profile_email,
            CASE WHEN p.profile_req_id IS NULL THEN cd.telefone ELSE ce.telefone END AS profile_telefone,
            CASE WHEN p.profile_req_id IS NULL THEN cd.morada ELSE ce.morada END AS profile_morada,
            CASE WHEN p.profile_req_id IS NULL THEN cd.nib ELSE ce.nib END AS profile_nib,
            ce.criado_em AS profile_created_at,

            ee.id AS emergency_req_id,
            CASE WHEN p.emergency_req_id IS NULL THEN em.nome ELSE ee.nome END AS emergency_nome,
            CASE WHEN p.emergency_req_id IS NULL THEN em.parentesco ELSE ee.parentesco END AS emergency_parentesco,
            CASE WHEN p.emergency_req_id IS NULL THEN em.telefone ELSE ee.telefone END AS emergency_telefone,
            CASE WHEN p.emergency_req_id IS NULL THEN em.grupo_sanguineo ELSE ee.grupo_sanguineo END AS emergency_grupo_sanguineo,
            ee.criado_em AS emergency_created_at

        FROM (
            SELECT
                user_id,
                MAX(profile_req_id) AS profile_req_id,
                MAX(emergency_req_id) AS emergency_req_id,
                MAX(latest_at) AS latest_at
            FROM (
                SELECT
                    user_id,
                    MAX(id) AS profile_req_id,
                    NULL AS emergency_req_id,
                    MAX(criado_em) AS latest_at
                FROM colaborador_edicoes
                WHERE estado = 'pendente'
                GROUP BY user_id

                UNION ALL

                SELECT
                    user_id,
                    NULL AS profile_req_id,
                    MAX(id) AS emergency_req_id,
                    MAX(criado_em) AS latest_at
                FROM contactos_emergencia_edicoes
                WHERE estado = 'pendente'
                GROUP BY user_id
            ) pending
            GROUP BY user_id
        ) p

        INNER JOIN colaborador_dados cd ON cd.user_id = p.user_id

        LEFT JOIN (
            SELECT em1.*
            FROM contactos_emergencia em1
            JOIN (
                SELECT user_id, MAX(id) AS max_id
                FROM contactos_emergencia
                GROUP BY user_id
            ) m ON m.user_id = em1.user_id AND m.max_id = em1.id
        ) em ON em.user_id = p.user_id

        LEFT JOIN colaborador_edicoes ce ON ce.id = p.profile_req_id
        LEFT JOIN contactos_emergencia_edicoes ee ON ee.id = p.emergency_req_id

        $whereSearch

        ORDER BY p.latest_at DESC, p.user_id DESC
        LIMIT :lim OFFSET :off
    ";

    $stmt = $pdo->prepare($sql);

    if ($q !== '') {
        $stmt->bindValue(':q_nome', "%{$q}%", PDO::PARAM_STR);
        $stmt->bindValue(':q_email', "%{$q}%", PDO::PARAM_STR);
    }

    $stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlCount = "
        SELECT COUNT(*)
        FROM (
            SELECT user_id
            FROM (
                SELECT user_id
                FROM colaborador_edicoes
                WHERE estado = 'pendente'
                GROUP BY user_id

                UNION ALL

                SELECT user_id
                FROM contactos_emergencia_edicoes
                WHERE estado = 'pendente'
                GROUP BY user_id
            ) pending
            GROUP BY user_id
        ) p

        INNER JOIN colaborador_dados cd ON cd.user_id = p.user_id

        $whereSearch
    ";

    $stmtCount = $pdo->prepare($sqlCount);

    if ($q !== '') {
        $stmtCount->bindValue(':q_nome', "%{$q}%", PDO::PARAM_STR);
        $stmtCount->bindValue(':q_email', "%{$q}%", PDO::PARAM_STR);
    }

    $stmtCount->execute();

    $total = (int)$stmtCount->fetchColumn();
    $totalPages = (int)ceil($total / $pageSize);

    echo json_encode([
        'success' => true,
        'page' => $page,
        'page_size' => $pageSize,
        'total' => $total,
        'total_pages' => $totalPages,
        'items' => $rows,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Throwable $e) {
    error_log('aval_requests error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'SERVER_ERROR']);
    exit;
}