<?php
// api/timesheets/aval_decision.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$uid = (int)($_SESSION['user']['id'] ?? 0);

/* DB + helper 25..24 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/periods.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Input */
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$periodId = (int)($in['period_id'] ?? 0);
$action   = $in['action'] ?? ''; // 'approve' | 'reject'
$comment  = isset($in['comment']) ? trim((string)$in['comment']) : null;

if (!$periodId || !in_array($action, ['approve','reject'], true)) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST"]); exit;
}
if ($action === 'reject' && ($comment === null || $comment === '')) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"COMMENT_REQUIRED"]); exit;
}

/* Carregar período */
$q = $pdo->prepare("
  SELECT p.id, p.user_id, p.period_start, p.period_end, p.estado
    FROM inov360.timesheet_periods p
   WHERE p.id = :id
   LIMIT 1
");
$q->execute([':id'=>$periodId]);
$P = $q->fetch(PDO::FETCH_ASSOC);

if (!$P) { http_response_code(404); echo json_encode(["ok"=>false,"code"=>"PERIOD_NOT_FOUND"]); exit; }
if ($P['estado'] !== 'submitted') {
    http_response_code(409); echo json_encode(["ok"=>false,"code"=>"NOT_SUBMITTED"]); exit;
}

/* Gate de hierarquia (responsável ativo/válido AGORA) */
$gate = $pdo->prepare("
  SELECT 1
    FROM inov360.colaborador_responsaveis
   WHERE colaborador_id = :target
     AND responsavel_id  = :me
     AND ativo = 1
     AND (valido_desde IS NULL OR valido_desde <= NOW())
     AND (valido_ate   IS NULL OR valido_ate   >= NOW())
   LIMIT 1
");
$gate->execute([':target'=>(int)$P['user_id'], ':me'=>$uid]);
if (!$gate->fetchColumn()) {
    http_response_code(403);
    echo json_encode(["ok"=>false,"code"=>"NOT_RESPONSAVEL"]); exit;
}

/* BLOQUEIO 25(M) 00:00 */
$label = substr($P['period_end'], 0, 7); // YYYY-MM do mês M
if ((new DateTime()) >= ts_lock_at($label)) {
    http_response_code(409);
    echo json_encode(["ok"=>false,"code"=>"PERIOD_CLOSED"]); exit;
}

/* Transação */
try {
    $pdo->beginTransaction();

    $newState = $action === 'approve' ? 'approved' : 'rejected';

    // Atualiza período
    $upd = $pdo->prepare("
        UPDATE inov360.timesheet_periods
           SET estado      = :e,
               decidido_por= :dp,
               decidido_em = NOW(),
               comentario  = COALESCE(:c, comentario)
         WHERE id = :id
    ");
    $upd->execute([
        ':e'  => $newState,
        ':dp' => $uid,
        ':c'  => $comment,
        ':id' => $periodId
    ]);

    // Atualiza eventos submetidos no intervalo (só WORK/ONCALL/KM)
    $evt = $pdo->prepare("
        UPDATE inov360.eventos
           SET status    = :st,
               source    = 'approval',
               updated_at= NOW()
         WHERE user_id   = :u
           AND DATE(inicio) BETWEEN :s AND :e
           AND tipo IN ('WORK','ONCALL','KM')
           AND status    = 'submitted'
    ");
    $evt->execute([
        ':st' => ($action === 'approve') ? 'approved' : 'draft',
        ':u'  => (int)$P['user_id'],
        ':s'  => $P['period_start'],
        ':e'  => $P['period_end']
    ]);

    $pdo->commit();

    echo json_encode([
        "ok"          => true,
        "period_id"   => (int)$P['id'],
        "novo_estado" => $newState,
        "comentario"  => $comment
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
