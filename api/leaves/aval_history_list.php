<?php
// api/leaves/aval_history_list.php
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

// hierarquia
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

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$phRoles = implode(',', array_fill(0, count($subRoles), '?'));
$whereSql = "p.estado IN ('aprovado','rejeitado') AND u.role IN ($phRoles)";
$params = $subRoles;

// template de SQL com a coluna do nome parametrizada
$sqlTpl = fn(string $nameCol) => "
  SELECT
    p.id,
    p.user_id,
    u.$nameCol    AS colaborador_nome,
    p.tipo,
    p.estado,
    p.data_inicio,
    p.data_fim,
    p.justificacao,
    p.criado_em,
    p.decidido_por,
    du.$nameCol   AS decidido_por_nome
  FROM pedidos_ferias p
  JOIN user u  ON u.id  = p.user_id
  LEFT JOIN user du ON du.id = p.decidido_por
  WHERE $whereSql
  ORDER BY p.criado_em DESC, p.id DESC
";

// tenta com `nome`, se der 1054 tenta com `name`
try {
    $st = $pdo->prepare($sqlTpl('nome'));
    $st->execute($params);
} catch (PDOException $e) {
    if (strpos($e->getMessage(), '1054') !== false) {
        $st = $pdo->prepare($sqlTpl('name'));
        $st->execute($params);
    } else {
        http_response_code(500);
        echo json_encode(["ok"=>false,"code"=>"DB_ERROR","msg"=>$e->getMessage()]);
        exit;
    }
}

$items = [];
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $items[] = [
        "pedido_id"   => (int)$r['id'],
        "colaborador" => [
            "id"   => (int)$r['user_id'],
            "nome" => $r['colaborador_nome'] ?? null
        ],
        "tipo"        => $r['tipo'],
        "estado"      => $r['estado'],              // 'aprovado' | 'rejeitado'
        "inicio"      => $r['data_inicio'],
        "fim"         => $r['data_fim'],
        "pedido_em"   => $r['criado_em'],
        "descricao"   => $r['justificacao'],
        "decidido_por"=> [
            "id"   => $r['decidido_por'] ? (int)$r['decidido_por'] : null,
            "nome" => $r['decidido_por_nome'] ?? null
        ]
    ];
}

echo json_encode(["ok"=>true, "items"=>$items, "total"=>count($items)]);
