<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once "../includes/db.php";

try {
    $conn = db_connect();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $uid = (int)$_SESSION['user_id'];

    $stmtPerm = $conn->prepare("
    SELECT p.id
    FROM user_permission up
    JOIN permission p ON p.id = up.permission_id
    WHERE up.user_id = ?
  ");
    $stmtPerm->execute([$uid]);
    $permissions = array_map('intval', $stmtPerm->fetchAll(PDO::FETCH_COLUMN));

    $stmtResp = $conn->prepare("
    SELECT cr.responsavel_id
    FROM colaborador_responsaveis cr
    WHERE cr.colaborador_id = ?
      AND cr.ativo = 1
      AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
      AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
  ");
    $stmtResp->execute([$uid]);
    $responsaveis = array_map('intval', $stmtResp->fetchAll(PDO::FETCH_COLUMN));

    $stmtSub = $conn->prepare("
    SELECT cr.colaborador_id
    FROM colaborador_responsaveis cr
    WHERE cr.responsavel_id = ?
      AND cr.ativo = 1
      AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
      AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
  ");
    $stmtSub->execute([$uid]);
    $subordinados = array_map('intval', $stmtSub->fetchAll(PDO::FETCH_COLUMN));

    echo json_encode([
        'success' => true,
        'auth' => [
            'permissions' => $permissions,
            'responsaveis' => $responsaveis,
            'subordinados' => $subordinados,
        ]
    ]);
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    exit;
}