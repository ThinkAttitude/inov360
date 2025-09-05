<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$role = $_SESSION['user']['role'] ?? '';
$allowed = ['inter2','inter','admin','adminrh','estrela'];
if (!in_array($role, $allowed, true)) {
    http_response_code(403); echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]); exit;
}
$uid = (int)($_SESSION['user']['id'] ?? 0);

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$periodId = (int)($in['period_id'] ?? 0);
$action   = $in['action'] ?? ''; // approve|reject
$comment  = isset($in['comment']) ? trim((string)$in['comment']) : null;

if (!$periodId || !in_array($action, ['approve','reject'], true)) {
    http_response_code(400); echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST"]); exit;
}

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// carregar período + role do colaborador para validar hierarquia
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

$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

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

$pdo->beginTransaction();
try {
    $newState = $action==='approve' ? 'approved' : 'rejected';

    // atualiza o período
    $upd = $pdo->prepare("
    UPDATE timesheet_periods
       SET estado=:e, decidido_por=:dp, decidido_em=NOW(), comentario=COALESCE(:c, comentario)
     WHERE id=:id
  ");
    $upd->execute([':e'=>$newState, ':dp'=>$uid, ':c'=>$comment, ':id'=>$periodId]);

    // atualiza eventos no mês
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
        "ok"=>true,
        "period_id"=>$periodId,
        "novo_estado"=>$newState
    ]);

} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
}
