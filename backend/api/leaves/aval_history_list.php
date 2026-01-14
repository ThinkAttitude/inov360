<?php
// api/leaves/aval_history_list.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 0);

require_once __DIR__ . '/../includes/db.php';
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
    echo json_encode(["ok"=>true, "has_subs"=>false, "items"=>[], "total"=>0]);
    exit;
}

/* ===== SQL (fallback para coluna de nome: nome|name) ===== */
$whereSql = "
  p.estado IN ('aprovado','rejeitado')
  AND cr.responsavel_id = :rid
  AND cr.ativo = 1
  AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
  AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
";

$sqlTpl = fn(string $nameCol) => "
  SELECT
    p.id,
    p.user_id,
    u.$nameCol  AS colaborador_nome,
    p.tipo,
    p.estado,
    p.data_inicio,
    p.data_fim,
    p.justificacao,
    p.criado_em,
    p.decidido_por,
    du.$nameCol AS decidido_por_nome
  FROM pedidos_ferias p
  JOIN colaborador_responsaveis cr
    ON cr.colaborador_id = p.user_id
  JOIN user u
    ON u.id = p.user_id
  LEFT JOIN user du
    ON du.id = p.decidido_por
  WHERE $whereSql
  ORDER BY p.criado_em DESC, p.id DESC
";

$params = [':rid'=>$userId];

try {
    $st = $pdo->prepare($sqlTpl('nome'));
    $st->execute($params);
} catch (PDOException $e) {
    if (strpos($e->getMessage(), '1054') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
        $st = $pdo->prepare($sqlTpl('name'));
        $st->execute($params);
    } else {
        http_response_code(500);
        echo json_encode(["ok"=>false,"code"=>"DB_ERROR"]);
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
        "estado"      => $r['estado'], // 'aprovado' | 'rejeitado'
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

echo json_encode([
    "ok"=>true,
    "has_subs"=>true,
    "items"=>$items,
    "total"=>count($items)
]);
