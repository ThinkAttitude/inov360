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

/* === DB === */
require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* === Helpers === */
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
$reqId     = isset($in['request_id']) ? (int)$in['request_id'] : 0;
$decision  = strtolower(trim((string)($in['decision'] ?? '')));
$comentario= isset($in['comentario']) ? trim((string)$in['comentario']) : null;

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
    if ($req['estado'] !== 'requested') {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['ok'=>false,'code'=>'ALREADY_DECIDED','estado'=>$req['estado']]); exit;
    }

    $userId = (int)$req['user_id'];
    $inicio = $req['data_inicio'];
    $fim    = $req['data_fim'];
    $dia    = substr($inicio, 0, 10);

    if ($decision === 'reject') {
        // 2A) Rejeitar apenas atualiza o pedido
        $upd = $pdo->prepare("
            UPDATE request_overtime
               SET estado='rejected', decidido_por=:me, decidido_em=NOW(), comentario=:c
             WHERE id=:id
        ");
        $upd->execute([':me'=>$meId, ':c'=>$comentario, ':id'=>$reqId]);

        $pdo->commit();
        echo json_encode([
            'ok'=>true,
            'decision'=>'rejected',
            'request'=>[
                'id'=>$reqId,'user_id'=>$userId,'dia'=>$dia,
                'data_inicio'=>$inicio,'data_fim'=>$fim,'estado'=>'rejected',
                'decidido_por'=>$meId,'decidido_em'=>date('Y-m-d H:i:s'),'comentario'=>$comentario
            ]
        ]);
        exit;
    }

    // 2B) Aprovar: garantir que não há overlap com overtime existente
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

    // 3) Tentar materializar no overtime
    //    Preferimos ligar o request_id; se já existir o MESMO intervalo exacto, anexamos o request_id.
    $findExact = $pdo->prepare("
        SELECT id FROM overtime
         WHERE user_id=:u AND inicio=:ini AND fim=:fim
         LIMIT 1
    ");
    $findExact->execute([':u'=>$userId, ':ini'=>$inicio, ':fim'=>$fim]);
    $otId = (int)($findExact->fetchColumn() ?: 0);

    if ($otId) {
        // já existe exactamente o mesmo intervalo → só anexar o request_id (se ainda não tiver)
        $upOt = $pdo->prepare("UPDATE overtime SET request_id=:rid WHERE id=:id AND (request_id IS NULL OR request_id<>:rid)");
        $upOt->execute([':rid'=>$reqId, ':id'=>$otId]);
    } else {
        $insOt = $pdo->prepare("
            INSERT INTO overtime (user_id, inicio, fim, request_id, origem, criado_por)
            VALUES (:u, :ini, :fim, :rid, 'approval', :me)
        ");
        $insOt->execute([':u'=>$userId, ':ini'=>$inicio, ':fim'=>$fim, ':rid'=>$reqId, ':me'=>$meId]);
        $otId = (int)$pdo->lastInsertId();
    }

    // 4) Atualizar o pedido para 'approved'
    $updReq = $pdo->prepare("
        UPDATE request_overtime
           SET estado='approved', decidido_por=:me, decidido_em=NOW(), comentario=:c
         WHERE id=:id
    ");
    $updReq->execute([':me'=>$meId, ':c'=>$comentario, ':id'=>$reqId]);

    // 5) Buscar overtime para responder
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

    $pdo->commit();

    echo json_encode([
        'ok'=>true,
        'decision'=>'approved',
        'request'=>[
            'id'=>$reqId,'user_id'=>$userId,'dia'=>$dia,
            'data_inicio'=>$inicio,'data_fim'=>$fim,'estado'=>'approved',
            'decidido_por'=>$meId,'decidido_em'=>date('Y-m-d H:i:s'),'comentario'=>$comentario
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
