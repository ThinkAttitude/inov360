<?php
// api/leaves/decision.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Sessão & Roles (avaliador) ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
$role = $_SESSION['user']['role'] ?? '';
$allowed = ['inter2','inter','admin','admin_rh','*'];
if (!in_array($role, $allowed, true)) {
    http_response_code(403);
    echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]);
    exit;
}

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Input (JSON) ===== */
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$pedidoId   = (int)($in['pedido_id'] ?? 0);
$acao       = $in['acao'] ?? '';                  // 'aprovar' | 'rejeitar'
$comentario = isset($in['comentario']) ? trim((string)$in['comentario']) : null;

if (!$pedidoId || !in_array($acao, ['aprovar','rejeitar'], true)) {
    http_response_code(400);
    echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST"]);
    exit;
}
if ($acao === 'rejeitar' && ($comentario === null || $comentario === '')) {
    http_response_code(400);
    echo json_encode(["ok"=>false,"code"=>"COMMENT_REQUIRED"]);
    exit;
}

/* ===== Fluxo ===== */
try {
    $pdo->beginTransaction();

    // Carregar pedido
    $q = $pdo->prepare("SELECT * FROM pedidos_ferias WHERE id=:id LIMIT 1");
    $q->execute([':id'=>$pedidoId]);
    $ped = $q->fetch(PDO::FETCH_ASSOC);
    if (!$ped) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["ok"=>false,"code"=>"REQUEST_NOT_FOUND"]);
        exit;
    }

    // Atualizar estado do pedido
    $novoEstado = $acao === 'aprovar' ? 'aprovado' : 'rejeitado';
    $upd = $pdo->prepare("
        UPDATE pedidos_ferias
           SET estado=:e, decidido_por=:dp, comentario = COALESCE(:c, comentario)
         WHERE id=:id
    ");
    $upd->execute([
        ':e'  => $novoEstado,
        ':dp' => (int)$_SESSION['user']['id'],
        ':c'  => $comentario,
        ':id' => $pedidoId
    ]);

    $evento = null;

    if ($acao === 'aprovar') {
        // Evitar duplicados (se não tiveres UNIQUE em leave_request_id)
        $del = $pdo->prepare("DELETE FROM eventos WHERE leave_request_id=:rid");
        $del->execute([':rid'=>$pedidoId]);

        // Criar evento LEAVE (um único evento com o intervalo completo)
        $ins = $pdo->prepare("
            INSERT INTO eventos
              (user_id, titulo, tipo, inicio, fim, minutos, km, status, source, leave_request_id, period_id, created_at, updated_at)
            VALUES
              (:u, :title, 'LEAVE', CONCAT(:di,' 00:00:00'), CONCAT(:df,' 23:59:59'), NULL, NULL, 'approved', 'approval', :rid, NULL, NOW(), NOW())
        ");
        $ins->execute([
            ':u'     => (int)$ped['user_id'],
            ':title' => (string)$ped['tipo'],      // subtipo (ferias, baixa_medica, ...)
            ':di'    => (string)$ped['data_inicio'],
            ':df'    => (string)$ped['data_fim'],
            ':rid'   => (int)$ped['id']
        ]);

        // Zerar minutos dos eventos que colidem com o LEAVE aprovado
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

        // Ler o evento LEAVE criado (opcional, para devolver no JSON)
        $sel = $pdo->prepare("
            SELECT id, user_id, titulo, tipo, inicio, fim, status, leave_request_id
              FROM eventos
             WHERE leave_request_id=:rid
             ORDER BY id DESC LIMIT 1
        ");
        $sel->execute([':rid'=>$pedidoId]);
        $evento = $sel->fetch(PDO::FETCH_ASSOC) ?: null;

    } else {
        // Rejeitado: remover qualquer evento associado a este pedido
        $pdo->prepare("DELETE FROM eventos WHERE leave_request_id=:rid")->execute([':rid'=>$pedidoId]);
    }

    $pdo->commit();

    echo json_encode([
        "ok"        => true,
        "pedido_id" => $pedidoId,
        "estado"    => $novoEstado,
        "comentario"=> $comentario,
        "evento"    => $evento
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
