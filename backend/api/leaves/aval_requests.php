<?php
// api/leaves/aval_requests.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

$userId = (int)($_SESSION['user']['id'] ?? 0);
$type   = $_GET['type'] ?? 'all'; // all|ferias|baixas|licencas

$map = [
    'ferias'   => ['ferias'],
    'baixas'   => ['baixa_medica','baixa_seguro'],
    'licencas' => ['licenca_paternidade','licenca_maternidade','casamento','consulta_medica','pessoal'],
];
$filterTipos = $type === 'all' ? [] : ($map[$type] ?? []);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Verificar se tenho subs diretos ===== */
$hasSubsStmt = $pdo->prepare("
  SELECT 1
  FROM colaborador_responsaveis cr
  WHERE cr.responsavel_id = ?
    AND cr.ativo = 1
    AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
    AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
  LIMIT 1
");
$hasSubsStmt->execute([$userId]);
if (!$hasSubsStmt->fetchColumn()) {
    echo json_encode(["ok"=>true, "has_subs"=>false, "items"=>[]]);
    exit;
}

/* ===== Filtros ===== */
$params = [$userId];
$wheres = [];
$wheres[] = "p.estado = 'pendente'";
$wheres[] = "cr.responsavel_id = ?";
$wheres[] = "cr.ativo = 1";
$wheres[] = "(cr.valido_desde IS NULL OR cr.valido_desde <= NOW())";
$wheres[] = "(cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())";

if ($filterTipos) {
    $phTipos = implode(',', array_fill(0, count($filterTipos), '?'));
    $wheres[] = "p.tipo IN ($phTipos)";
    $params = array_merge($params, $filterTipos);
}
$whereSql = implode(' AND ', $wheres);

/* ===== SQL com fallback para coluna de nome (nome|name) ===== */
$sqlTpl = fn(string $nameCol) => "
  SELECT
    p.id,
    p.user_id,
    u.$nameCol     AS colaborador_nome,
    p.tipo,
    p.data_inicio,
    p.data_fim,
    p.justificacao,
    p.ficheiro,
    p.criado_em
  FROM pedidos_ferias p
  JOIN colaborador_responsaveis cr
    ON cr.colaborador_id = p.user_id
  JOIN user u
    ON u.id = p.user_id
  WHERE $whereSql
  ORDER BY p.criado_em ASC, p.id ASC
";

try {
    $st = $pdo->prepare($sqlTpl('nome'));
    $st->execute($params);
} catch (PDOException $e) {
    if (strpos($e->getMessage(), '1054') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
        $st = $pdo->prepare($sqlTpl('name'));
        $st->execute($params);
    } else {
        http_response_code(500);
        json_error('DB_ERROR', 500);
    }
}

/* ===== Montar resposta ===== */
$baseUrl = rtrim(
    (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' .
    ($_SERVER['HTTP_HOST'] ?? ''), '/'
);

$items = [];
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $items[] = [
        "pedido_id"    => (int)$r['id'],
        "colaborador"  => [
            "id"   => (int)$r['user_id'],
            "nome" => $r['colaborador_nome'] ?? null,
        ],
        "tipo"         => $r['tipo'],
        "inicio"       => $r['data_inicio'],
        "fim"          => $r['data_fim'],
        "justificacao" => $r['justificacao'],
        // Nota: agora não atribuímos responsável no pedido; mantemos apenas o comprovativo
        "comprovativo" => $r['ficheiro'] ? $baseUrl . '/uploads/' . $r['ficheiro'] : null,
        "pedido_em"    => $r['criado_em'],
    ];
}

echo json_encode([
    "ok" => true,
    "has_subs" => true,
    "type" => $type,
    "items" => $items
]);
