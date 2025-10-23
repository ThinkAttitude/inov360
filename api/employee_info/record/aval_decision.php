<?php
// api/employee_record/aval_decision.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Auth + perm record_managment (id=6)
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']); exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(6, $perms, true)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN']); exit;
}
$actorId = (int)$_SESSION['user']['id'];

// Input (JSON ou form)
$ctype = $_SERVER['CONTENT_TYPE'] ?? '';
$payload = $_POST;
if (empty($payload) && stripos($ctype, 'application/json') !== false) {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
}
$userId   = isset($payload['user_id']) ? (int)$payload['user_id'] : 0;
$decision = strtolower(trim((string)($payload['decision'] ?? ''))); // approve | reject
if ($userId <= 0 || !in_array($decision, ['approve','reject'], true)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'BAD_REQUEST']); exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar TODOS os pendentes do user (perfil e emergência)
    $stmt = $pdo->prepare("SELECT * FROM inov360.colaborador_edicoes WHERE user_id=? AND estado='pendente' ORDER BY id ASC");
    $stmt->execute([$userId]);
    $pendProfile = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM inov360.contactos_emergencia_edicoes WHERE user_id=? AND estado='pendente' ORDER BY id ASC");
    $stmt->execute([$userId]);
    $pendEmerg = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$pendProfile && !$pendEmerg) {
        http_response_code(404);
        echo json_encode(['success'=>false,'error'=>'NO_PENDING_REQUESTS']); exit;
    }

    // REJECT: marca tudo como recusado (sem aplicar alterações)
    if ($decision === 'reject') {
        if ($pendProfile) {
            $ids = implode(',', array_map('intval', array_column($pendProfile, 'id')));
            $pdo->exec("UPDATE inov360.colaborador_edicoes
                        SET estado='recusado', avaliado_por={$actorId}, avaliado_em=NOW()
                        WHERE id IN ($ids) AND estado='pendente'");
        }
        if ($pendEmerg) {
            $ids = implode(',', array_map('intval', array_column($pendEmerg, 'id')));
            $pdo->exec("UPDATE inov360.contactos_emergencia_edicoes
                        SET estado='recusado', avaliado_por={$actorId}, avaliado_em=NOW()
                        WHERE id IN ($ids) AND estado='pendente'");
        }
        echo json_encode(['success'=>true,'result'=>'rejected','affected'=>[
            'profile'=>count($pendProfile),'emergency'=>count($pendEmerg)
        ]]); exit;
    }

    // APPROVE: aplicar valores do pedido MAIS RECENTE de cada tipo e aprovar TODOS
    $pdo->beginTransaction();

    // PERFIL
    if ($pendProfile) {
        $lastP = end($pendProfile); // mais recente (maior id)

        // Se houver email novo, garantir unicidade no user
        if (!empty($lastP['email'])) {
            $chk = $pdo->prepare("SELECT id FROM inov360.`user` WHERE email=? AND id<>? LIMIT 1");
            $chk->execute([$lastP['email'], $userId]);
            if ($chk->fetch()) {
                $pdo->rollBack();
                http_response_code(409);
                echo json_encode(['success'=>false,'error'=>'EMAIL_IN_USE']); exit;
            }
            $updUser = $pdo->prepare("UPDATE inov360.`user` SET email=? WHERE id=?");
            $updUser->execute([$lastP['email'], $userId]);
        }

        // Aplicar na ficha principal
        $updProfile = $pdo->prepare("
            UPDATE inov360.colaborador_dados
            SET email    = COALESCE(?, email),
                telefone = COALESCE(?, telefone),
                morada   = COALESCE(?, morada),
                nib      = COALESCE(?, nib)
            WHERE user_id = ?
        ");
        $updProfile->execute([
            $lastP['email']    ?: null,
            $lastP['telefone'] ?: null,
            $lastP['morada']   ?: null,
            $lastP['nib']      ?: null,
            $userId
        ]);

        // Aprovar TODOS os pendentes desse tipo
        $ids = implode(',', array_map('intval', array_column($pendProfile, 'id')));
        $pdo->exec("UPDATE inov360.colaborador_edicoes
                    SET estado='aprovado', avaliado_por={$actorId}, avaliado_em=NOW()
                    WHERE id IN ($ids) AND estado='pendente'");
    }

    // EMERGÊNCIA
    if ($pendEmerg) {
        $lastE = end($pendEmerg);

        $updEm = $pdo->prepare("
            UPDATE inov360.contactos_emergencia
            SET nome       = COALESCE(?, nome),
                parentesco = COALESCE(?, parentesco),
                telefone   = COALESCE(?, telefone)
            WHERE user_id = ?
        ");
        $updEm->execute([
            $lastE['nome']       ?: null,
            $lastE['parentesco'] ?: null,
            $lastE['telefone']   ?: null,
            $userId
        ]);

        $ids = implode(',', array_map('intval', array_column($pendEmerg, 'id')));
        $pdo->exec("UPDATE inov360.contactos_emergencia_edicoes
                    SET estado='aprovado', avaliado_por={$actorId}, avaliado_em=NOW()
                    WHERE id IN ($ids) AND estado='pendente'");
    }

    $pdo->commit();

    echo json_encode(['success'=>true,'result'=>'approved','affected'=>[
        'profile'=>count($pendProfile),'emergency'=>count($pendEmerg)
    ]]); exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    // error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'SERVER_ERROR']);
}
