<?php
// api/leaves/collab_requests.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}


$userId = (int)($_SESSION['user']['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Confirmar que o colaborador tem responsáveis ativos/válidos ===== */
$hasResp = $pdo->prepare("
  SELECT 1
  FROM colaborador_responsaveis
  WHERE colaborador_id = ?
    AND ativo = 1
    AND (valido_desde IS NULL OR valido_desde <= NOW())
    AND (valido_ate   IS NULL OR valido_ate   >= NOW())
  LIMIT 1
");
$hasResp->execute([$userId]);
if (!$hasResp->fetchColumn()) {
    http_response_code(400);
    json_error('NO_RESPONSAVEIS');
}

$sql = "
  SELECT pf.id, pf.tipo, pf.data_inicio, pf.data_fim, pf.justificacao, pf.ficheiro,
         pf.estado, pf.criado_em, pf.decidido_por, pf.responsavel_id,
         u.name AS decidido_por_nome
    FROM pedidos_ferias pf
    LEFT JOIN `user` u ON u.id = pf.decidido_por
   WHERE pf.user_id = :u
   ORDER BY pf.criado_em DESC, pf.id DESC
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
        "id"                 => (int)$r['id'],
        "tipo"               => $r['tipo'],
        "inicio"             => $r['data_inicio'],
        "fim"                => $r['data_fim'],
        "justificacao"       => $r['justificacao'],
        "responsavel_id"     => $r['responsavel_id'] ? (int)$r['responsavel_id'] : null,
        "comprovativo"       => $r['ficheiro'] ? $baseUrl . '/uploads/' . $r['ficheiro'] : null,
        "estado"             => $r['estado'],
        "criado_em"          => $r['criado_em'],
        "decidido_por"       => $r['decidido_por'] ? (int)$r['decidido_por'] : null,
        "decidido_por_nome"  => $r['decidido_por_nome'] ?? null,
    ];
}

echo json_encode([
    "ok"    => true,
    "total" => count($rows),
    "items" => $rows
]);
