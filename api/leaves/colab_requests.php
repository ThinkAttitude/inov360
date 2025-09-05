<?php
// api/leaves/colab_requests.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
if (($_SESSION['user']['role'] ?? '') === 'estrela') {
    http_response_code(403);
    echo json_encode(["ok"=>false,"code"=>"FORBIDDEN_ROLE"]);
    exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "
  SELECT id, tipo, data_inicio, data_fim, justificacao, ficheiro,
         estado, criado_em, decidido_por, responsavel_id
    FROM pedidos_ferias
   WHERE user_id = :u
   ORDER BY criado_em DESC, id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':u'=>$userId]);

$baseUrl = rtrim(
    (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' .
    ($_SERVER['HTTP_HOST'] ?? ''), '/'
);

$rows = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = [
        "id"            => (int)$r['id'],
        "tipo"          => $r['tipo'],
        "inicio"        => $r['data_inicio'],
        "fim"           => $r['data_fim'],
        "justificacao"  => $r['justificacao'],
        "responsavel_id"=> $r['responsavel_id'] ? (int)$r['responsavel_id'] : null,
        "comprovativo"  => $r['ficheiro'] ? $baseUrl . '/uploads/' . $r['ficheiro'] : null,
        "estado"        => $r['estado'],
        "criado_em"     => $r['criado_em'],
        "decidido_por"  => $r['decidido_por'] ? (int)$r['decidido_por'] : null
    ];
}

echo json_encode([
    "ok"    => true,
    "total" => count($rows),
    "items" => $rows
]);
