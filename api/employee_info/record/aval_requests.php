<?php
// api/employee_record/aval_requests.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Auth + perm
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

    $q        = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $pageSize = min(100, max(1, (int)($_GET['page_size'] ?? 20)));
    $offset   = ($page - 1) * $pageSize;

    // Derived tables (sem CTE)
    $sql = "
      SELECT
        u.id          AS user_id,
        u.name        AS user_name,
        u.email       AS user_email,

        ce.id         AS profile_req_id,
        ce.email      AS profile_email,
        ce.telefone   AS profile_telefone,
        ce.morada     AS profile_morada,
        ce.nib        AS profile_nib,
        ce.criado_em  AS profile_created_at,

        ee.id         AS emergency_req_id,
        ee.nome       AS emergency_nome,
        ee.parentesco AS emergency_parentesco,
        ee.telefone   AS emergency_telefone,
        ee.criado_em  AS emergency_created_at

      FROM (
        SELECT user_id FROM inov360.colaborador_edicoes WHERE estado='pendente'
        UNION
        SELECT user_id FROM inov360.contactos_emergencia_edicoes WHERE estado='pendente'
      ) uw
      JOIN inov360.`user` u ON u.id = uw.user_id

      LEFT JOIN (
        SELECT ce1.*
        FROM inov360.colaborador_edicoes ce1
        JOIN (
          SELECT user_id, MAX(id) AS max_id
          FROM inov360.colaborador_edicoes
          WHERE estado='pendente'
          GROUP BY user_id
        ) m ON m.user_id = ce1.user_id AND m.max_id = ce1.id
      ) ce ON ce.user_id = uw.user_id

      LEFT JOIN (
        SELECT ee1.*
        FROM inov360.contactos_emergencia_edicoes ee1
        JOIN (
          SELECT user_id, MAX(id) AS max_id
          FROM inov360.contactos_emergencia_edicoes
          WHERE estado='pendente'
          GROUP BY user_id
        ) m ON m.user_id = ee1.user_id AND m.max_id = ee1.id
      ) ee ON ee.user_id = uw.user_id

      ORDER BY GREATEST(
        IFNULL(UNIX_TIMESTAMP(ce.criado_em), 0),
        IFNULL(UNIX_TIMESTAMP(ee.criado_em), 0)
      ) DESC, u.id DESC
      LIMIT :lim OFFSET :off
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // filtro q (nome/email) no PHP
    if ($q !== '') {
        $ql = mb_strtolower($q);
        $rows = array_values(array_filter($rows, function ($r) use ($ql) {
            return (strpos(mb_strtolower($r['user_name']), $ql) !== false)
                || (strpos(mb_strtolower($r['user_email']), $ql) !== false);
        }));
    }

    echo json_encode([
        'success'   => true,
        'page'      => $page,
        'page_size' => $pageSize,
        'items'     => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    // ajuda no debug
    error_log('aval_requests error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'SERVER_ERROR']);
}
