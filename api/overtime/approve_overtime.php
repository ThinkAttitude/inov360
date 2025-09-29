<?php
// api/overtime/approve_overtime.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* === Auth === */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'code'=>'UNAUTHENTICATED']); exit;
}
$meId    = (int)($_SESSION['user']['id'] ?? 0);
$myPerms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($myPerms) || !in_array(5, $myPerms, true)) { // perm 5: approve_overtime
    http_response_code(403);
    echo json_encode(['ok'=>false,'code'=>'FORBIDDEN_PERMISSION']); exit;
}

/* === Helpers / DB === */
require_once __DIR__ . '/../lib/helper/periods.php';
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* === Utils === */
function json_input(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}

/* === Input ===
   body JSON:
   - request_id (int, obrigatório)
   - decision  ('approve' | 'reject', obrigatório)
   - comentario (opcional)
*/
$in = json_input();
$reqId      = isset($in['request_id']) ? (int)$in['request_id'] : 0;
$decision   = strtolower(trim((string)($in['decision'] ?? '')));
$comentario = isset($in['comentario']) ? trim((string)$in['comentario']) : null;

if ($reqId <= 0 || ($decision !== 'approve' && $decision !== 'reject')) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'code'=>'MISSING_FIELDS']); exit;
}

try {
    $pdo->beginTransaction();

    // 1) Ler pedido (tem de existir e estar 'requested')
    $q = $pdo->prepare("
        SELECT id, user_id, data_inicio, data_fim, estado
          FROM request_overtime
         WHERE id = :id
         FOR UPDATE
    ");
    $q->execute([':id'=>$reqId]);
    $req = $q->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['ok'=>false,'code'=>'REQUEST_NOT_FOUND']); exit;
    }

    // 2) Bloqueio pelo deadline (vale para approve e reject)
    $dia = substr($req['data_inicio'], 0, 10);
    if (!ot_is_open_for_day($dia)) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['ok'=>false,'code'=>'OVERTIME_CLOSED']); exit;
    }

    if ($req['estado'] !== 'requested') {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['ok'=>false,'code'=>'ALREADY_DECIDED','estado'=>$req['estado']]); exit;
    }

    $userId = (int)$req['user_id'];
    $inicio = $req['data_inicio'];
    $fim    = $req['data_fim'];

    if ($decision === 'reject') {
        // 3A) Rejeitar: só atualiza o pedido
        $upd = $pdo->prepare("
            UPDATE request_overtime
               SET estado='rejected', decidido_por=:me, decidido_em=NOW(), comentario=:c
             WHERE id=:id
        ");
        $upd->execute([':me'=>$meId, ':c'=>$comentario, ':id'=>$reqId]);

        // Ler decidido_em real da BD para responder com precisão
        $getReq = $pdo->prepare("
            SELECT decidido_em FROM request_overtime WHERE id=:id
        ");
        $getReq->execute([':id'=>$reqId]);
        $decididoEm = $getReq->fetchColumn() ?: date('Y-m-d H:i:s');

        $pdo->commit();
        echo json_encode([
            'ok'=>true,
            'decision'=>'rejected',
            'request'=>[
                'id'=>$reqId,'user_id'=>$userId,'dia'=>$dia,
                'data_inicio'=>$inicio,'data_fim'=>$fim,'estado'=>'rejected',
                'decidido_por'=>$meId,'decidido_em'=>$decididoEm,'comentario'=>$comentario
            ]
        ]);
        exit;
    }

    // 3B) Aprovar: garantir que não há overlap com overtime existente
    $overlap = $pdo->prepare("
        SELECT id FROM overtime
         WHERE user_id = :u
           AND NOT (fim <= :ini OR inicio >= :fim)
         LIMIT 1
    ");
    $overlap->execute([':u'=>$userId, ':ini'=>$inicio, ':fim'=>$fim]);
    if ($overlap->fetchColumn()) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['ok'=>false,'code'=>'OVERTIME_CONFLICT']); exit;
    }

    // 4) Materializar no overtime (preferir ligar request_id a um registo igual, senão inserir)
    $findExact = $pdo->prepare("
        SELECT id FROM overtime
         WHERE user_id=:u AND inicio=:ini AND fim=:fim
         LIMIT 1
    ");
    $findExact->execute([':u'=>$userId, ':ini'=>$inicio, ':fim'=>$fim]);
    $otId = (int)($findExact->fetchColumn() ?: 0);

    if ($otId) {
        $upOt = $pdo->prepare("
            UPDATE overtime SET request_id=:rid
             WHERE id=:id AND (request_id IS NULL OR request_id<>:rid)
        ");
        $upOt->execute([':rid'=>$reqId, ':id'=>$otId]);
    } else {
        $insOt = $pdo->prepare("
            INSERT INTO overtime (user_id, inicio, fim, request_id, origem, criado_por)
            VALUES (:u, :ini, :fim, :rid, 'approval', :me)
        ");
        $insOt->execute([':u'=>$userId, ':ini'=>$inicio, ':fim'=>$fim, ':rid'=>$reqId, ':me'=>$meId]);
        $otId = (int)$pdo->lastInsertId();
    }

    // 5) Atualizar o pedido para 'approved'
    $updReq = $pdo->prepare("
        UPDATE request_overtime
           SET estado='approved', decidido_por=:me, decidido_em=NOW(), comentario=:c
         WHERE id=:id
    ");
    $updReq->execute([':me'=>$meId, ':c'=>$comentario, ':id'=>$reqId]);

    // 6) Buscar overtime e decidido_em para responder
    $getOt = $pdo->prepare("
        SELECT id, user_id, inicio, fim,
               TIMESTAMPDIFF(MINUTE, inicio, fim) AS minutos,
               dia, request_id
          FROM overtime
         WHERE id=:id
         LIMIT 1
    ");
    $getOt->execute([':id'=>$otId]);
    $ot = $getOt->fetch(PDO::FETCH_ASSOC);

    $getReq = $pdo->prepare("SELECT decidido_em FROM request_overtime WHERE id=:id");
    $getReq->execute([':id'=>$reqId]);
    $decididoEm = $getReq->fetchColumn() ?: date('Y-m-d H:i:s');

    $pdo->commit();

    echo json_encode([
        'ok'=>true,
        'decision'=>'approved',
        'request'=>[
            'id'=>$reqId,'user_id'=>$userId,'dia'=>$dia,
            'data_inicio'=>$inicio,'data_fim'=>$fim,'estado'=>'approved',
            'decidido_por'=>$meId,'decidido_em'=>$decididoEm,'comentario'=>$comentario
        ],
        'overtime'=>[
            'id'=>(int)$ot['id'],'user_id'=>(int)$ot['user_id'],
            'dia'=>$ot['dia'],'inicio'=>$ot['inicio'],'fim'=>$ot['fim'],
            'minutos'=>(int)$ot['minutos'],'request_id'=>(int)$ot['request_id']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok'=>false,'code'=>'DB_ERROR','msg'=>$e->getMessage()]);
}
