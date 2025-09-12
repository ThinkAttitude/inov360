<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth & Roles ===== */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$role = $_SESSION['user']['role'] ?? '';
$allowed = ['inter2','inter','admin','adminrh','estrela'];
if (!in_array($role, $allowed, true)) {
    http_response_code(403); echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]); exit;
}
$uid = (int)($_SESSION['user']['id'] ?? 0);

/* ===== Input ===== */
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$periodId = (int)($in['period_id'] ?? 0);
$action   = $in['action'] ?? ''; // approve | reject
$comment  = isset($in['comment']) ? trim((string)$in['comment']) : null;

if (!$periodId || !in_array($action, ['approve','reject'], true)) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST"]); exit;
}
/* >>> NOVO: comentário obrigatório quando rejeita <<< */
if ($action === 'reject' && ($comment === null || $comment === '')) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"COMMENT_REQUIRED"]); exit;
}

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Hierarquia ===== */
function can_review(string $r, string $target): bool {
    return match ($r) {
        'inter2'  => $target==='opera',
        'inter'   => $target==='inter2',
        'admin'   => $target==='inter',
        'estrela' => in_array($target, ['admin','adminrh'], true),
        'adminrh' => false,
        default   => false,
    };
}

/* Nome do utilizador (nome|name) */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

/* ===== Carregar período ===== */
$q = $pdo->prepare("
  SELECT p.*, u.role AS urole, u.$nameCol AS uname
    FROM timesheet_periods p
    JOIN user u ON u.id=p.user_id
   WHERE p.id=:id
   LIMIT 1
");
$q->execute([':id'=>$periodId]);
$P = $q->fetch(PDO::FETCH_ASSOC);
if (!$P) { http_response_code(404); echo json_encode(["ok"=>false,"code"=>"PERIOD_NOT_FOUND"]); exit; }
if ($P['estado'] !== 'submitted') { http_response_code(409); echo json_encode(["ok"=>false,"code"=>"NOT_SUBMITTED"]); exit; }
if (!can_review($role, $P['urole'])) { http_response_code(403); echo json_encode(["ok"=>false,"code"=>"HIERARCHY_FORBIDDEN"]); exit; }

/* ===== Transação ===== */
$pdo->beginTransaction();
try {
    $newState = $action==='approve' ? 'approved' : 'rejected';

    // Atualiza período (guarda comentário quando enviado)
    $upd = $pdo->prepare("
        UPDATE timesheet_periods
           SET estado=:e,
               decidido_por=:dp,
               decidido_em=NOW(),
               comentario = COALESCE(:c, comentario)
         WHERE id=:id
    ");
    $upd->execute([
        ':e'  => $newState,
        ':dp' => $uid,
        ':c'  => $comment,  // se approve sem comment, mantém o existente
        ':id' => $periodId
    ]);

    // Atualiza eventos submetidos do mês
    $evt = $pdo->prepare("
        UPDATE eventos
           SET status=:st, source='approval', updated_at=NOW()
         WHERE user_id=:u
           AND DATE(inicio) BETWEEN :s AND :e
           AND status='submitted'
    ");
    $evt->execute([
        ':st' => $action==='approve' ? 'approved' : 'draft',
        ':u'  => (int)$P['user_id'],
        ':s'  => $P['period_start'],
        ':e'  => $P['period_end']
    ]);

    $pdo->commit();
    echo json_encode([
        "ok"           => true,
        "period_id"    => $periodId,
        "novo_estado"  => $newState,
        "comentario"   => $comment // útil para eco no front
    ]);

} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
