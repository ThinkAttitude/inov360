<?php
// api/leaves/decision.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Sessão ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Input (JSON) ===== */
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$pedidoId   = (int)($in['pedido_id'] ?? 0);
$acao       = $in['acao'] ?? '';                  // 'aprovar' | 'rejeitar'
$comentario = isset($in['comentario']) ? trim((string)$in['comentario']) : null;

if (!$pedidoId || !in_array($acao, ['aprovar','rejeitar'], true)) {
    http_response_code(400);
    json_error('BAD_REQUEST');
}
if ($acao === 'rejeitar' && ($comentario === null || $comentario === '')) {
    http_response_code(400);
    json_error('COMMENT_REQUIRED');
}

$avaliadorId = (int)$_SESSION['user']['id'];

try {
    /* ===== Carregar pedido ===== */
    $q = $pdo->prepare("SELECT * FROM pedidos_ferias WHERE id = ? LIMIT 1");
    $q->execute([$pedidoId]);
    $ped = $q->fetch(PDO::FETCH_ASSOC);
    if (!$ped) {
        http_response_code(404);
        json_error('REQUEST_NOT_FOUND', 404);
    }
    if ($ped['estado'] !== 'pendente') {
        http_response_code(409);
        echo json_encode(["ok"=>false,"code"=>"ALREADY_DECIDED","estado"=>$ped['estado']]);
        exit;
    }

    $colabId = (int)$ped['user_id'];

    /* ===== NOVO: validar hierarquia (sou responsável do colaborador?) ===== */
    $chk = $pdo->prepare("
        SELECT 1
          FROM colaborador_responsaveis
         WHERE colaborador_id = ?
           AND responsavel_id = ?
           AND ativo = 1
           AND (valido_desde IS NULL OR valido_desde <= NOW())
           AND (valido_ate   IS NULL OR valido_ate   >= NOW())
         LIMIT 1
    ");
    $chk->execute([$colabId, $avaliadorId]);
    if (!$chk->fetchColumn()) {
        http_response_code(403);
        json_error('NOT_RESPONSAVEL');
    }

    $pdo->beginTransaction();

    /* ===== Atualizar estado do pedido ===== */
    $novoEstado = $acao === 'aprovar' ? 'aprovado' : 'rejeitado';
    $upd = $pdo->prepare("
        UPDATE pedidos_ferias
           SET estado = :e,
               decidido_por = :dp,
               comentario = COALESCE(:c, comentario)
         WHERE id = :id
    ");
    $upd->execute([
        ':e'  => $novoEstado,
        ':dp' => $avaliadorId,
        ':c'  => $comentario,
        ':id' => $pedidoId
    ]);

    /* ===== Eventos (mantém a tua lógica antiga) ===== */
    // limpa qualquer evento anterior associado ao pedido
    $pdo->prepare("DELETE FROM eventos WHERE leave_request_id = ?")
        ->execute([$pedidoId]);

    $eventoLeave = null;
    $eventoSub   = null;

    if ($acao === 'aprovar') {
        // criar LEAVE para o colaborador
        $ins = $pdo->prepare("
            INSERT INTO eventos
              (user_id, titulo, tipo, inicio, fim, minutos, km, status, source, leave_request_id, period_id, created_at, updated_at)
            VALUES
              (:u, :title, 'LEAVE',
               CONCAT(:di,' 00:00:00'), CONCAT(:df,' 23:59:59'),
               NULL, NULL, 'approved', 'approval', :rid, NULL, NOW(), NOW())
        ");
        $ins->execute([
            ':u'     => (int)$ped['user_id'],
            ':title' => (string)$ped['tipo'],
            ':di'    => (string)$ped['data_inicio'],
            ':df'    => (string)$ped['data_fim'],
            ':rid'   => (int)$ped['id']
        ]);

        // zerar minutos de WORK/OVERTIME/ONCALL que colidam
        $zero = $pdo->prepare("
            UPDATE eventos
               SET minutos = CASE WHEN tipo IN ('WORK','OVERTIME','ONCALL') THEN 0 ELSE minutos END,
                   updated_at = NOW()
             WHERE user_id = :u
               AND tipo IN ('WORK','OVERTIME','ONCALL')
               AND DATE(inicio) <= :df
               AND DATE(fim)    >= :di
        ");
        $zero->execute([
            ':u'  => (int)$ped['user_id'],
            ':di' => (string)$ped['data_inicio'],
            ':df' => (string)$ped['data_fim'],
        ]);

        // (opcional legacy) criar SUBSTITUTION se o pedido tiver responsavel_id preenchido
        if (!empty($ped['responsavel_id'])) {
            $uStmt = $pdo->prepare("SELECT `name` FROM `user` WHERE id = ? LIMIT 1");
            $uStmt->execute([(int)$ped['user_id']]);
            $reqNome = (string)($uStmt->fetchColumn() ?: 'utilizador');

            $insSub = $pdo->prepare("
                INSERT INTO eventos
                  (user_id, titulo, tipo, inicio, fim, minutos, km, status, source, leave_request_id, period_id, created_at, updated_at)
                VALUES
                  (:u, :title, 'SUBSTITUTION',
                   CONCAT(:di,' 00:00:00'), CONCAT(:df,' 23:59:59'),
                   NULL, NULL, 'approved', 'system', :rid, NULL, NOW(), NOW())
            ");
            $insSub->execute([
                ':u'     => (int)$ped['responsavel_id'],
                ':title' => 'Substituição ' . $reqNome,
                ':di'    => (string)$ped['data_inicio'],
                ':df'    => (string)$ped['data_fim'],
                ':rid'   => (int)$ped['id']
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "ok"         => true,
        "pedido_id"  => $pedidoId,
        "estado"     => $novoEstado
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    json_error('DB_ERROR', 500);}
