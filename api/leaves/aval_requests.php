<?php
// api/leaves/aval_requests.php
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

function subordinate_roles(string $r): array {
    return match ($r) {
        'inter2'  => ['opera'],
        'inter'   => ['inter2'],
        'admin'   => ['inter'],
        'estrela' => ['admin','adminrh'],
        default   => [],
    };
}
$subRoles = subordinate_roles($role);
if (!$subRoles) { echo json_encode(["ok"=>true,"items"=>[]]); exit; }

$type = $_GET['type'] ?? 'all'; // all|ferias|baixas|licencas

// Mapear categorias → tipos do enum `pedidos_ferias.tipo`
$map = [
    'ferias'   => ['ferias'],
    'baixas'   => ['baixa_medica','baixa_seguro'],
    'licencas' => ['licenca_paternidade','licenca_maternidade','casamento','consulta_medica','pessoal'],
];

$filterTipos = [];
if ($type !== 'all') {
    $filterTipos = $map[$type] ?? [];
}

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$params = [];
$wheres = [];

$wheres[] = "p.estado = 'pendente'";
$phSub = implode(',', array_fill(0, count($subRoles), '?'));
$wheres[] = "u.role IN ($phSub)";
$params = array_merge($params, $subRoles);

if ($filterTipos) {
    $phTipos = implode(',', array_fill(0, count($filterTipos), '?'));
    $wheres[] = "p.tipo IN ($phTipos)";
    $params = array_merge($params, $filterTipos);
}

$whereSql = implode(' AND ', $wheres);

$sql = "
  SELECT
    p.id,
    p.user_id,
    u.nome        AS colaborador_nome,
    u.role        AS colaborador_role,
    p.tipo,
    p.data_inicio,
    p.data_fim,
    p.justificacao,
    p.ficheiro,
    p.criado_em,
    p.responsavel_id,
    ur.nome       AS responsavel_nome
  FROM pedidos_ferias p
  JOIN user u  ON u.id  = p.user_id
  LEFT JOIN user ur ON ur.id = p.responsavel_id
  WHERE $whereSql
  ORDER BY p.criado_em ASC, p.id ASC
";
$st = $pdo->prepare($sql);
$st->execute($params);

$baseUrl = rtrim(
    (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' .
    ($_SERVER['HTTP_HOST'] ?? ''), '/'
);

$items = [];
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $items[] = [
        "pedido_id"     => (int)$r['id'],
        "colaborador"   => [
            "id"   => (int)$r['user_id'],
            "nome" => $r['colaborador_nome'],
            "role" => $r['colaborador_role']
        ],
        "tipo"          => $r['tipo'],
        "inicio"        => $r['data_inicio'],
        "fim"           => $r['data_fim'],
        "justificacao"  => $r['justificacao'],
        "substituicao"  => $r['responsavel_id'] ? [
            "id"   => (int)$r['responsavel_id'],
            "nome" => $r['responsavel_nome']
        ] : null,
        "comprovativo"  => $r['ficheiro'] ? $baseUrl . '/uploads/' . $r['ficheiro'] : null,
        "pedido_em"     => $r['criado_em']
    ];
}

echo json_encode(["ok"=>true, "type"=>$type, "items"=>$items]);
