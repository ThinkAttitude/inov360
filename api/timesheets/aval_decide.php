<?php
// api/timesheets/aval_decision.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
$uid = (int)($_SESSION['user']['id'] ?? 0);

/* ===== Input ===== */
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

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Carregar período ===== */
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

/* ===== Gate de hierarquia (novo modelo) =====
   O revisor TEM de ser responsável ativo/válido do colaborador dono do período.
   Validação “no momento” (NOW()) — igual aos outros endpoints de avaliação/lista.
*/
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
    echo json_encode(["ok"=>false,"code"=>"NOT_RESPONSAVEL"]);
    exit;
}

/* ===== Transação ===== */
try {
    $pdo->beginTransaction();

    $newState = $action === 'approve' ? 'approved' : 'rejected';

    // Atualiza período (guarda comentário quando enviado)
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

    // Atualiza eventos submetidos no intervalo
    $evt = $pdo->prepare("
        UPDATE inov360.eventos
           SET status    = :st,
               source    = 'approval',
               updated_at= NOW()
         WHERE user_id   = :u
           AND DATE(inicio) BETWEEN :s AND :e
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
