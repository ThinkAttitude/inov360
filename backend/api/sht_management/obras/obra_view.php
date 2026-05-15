<?php
// api/grupo_inov/obras/obra_view.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(8, $perms, true)) { // sht_management
    http_response_code(403);
    json_error('FORBIDDEN_PERMISSION', 403);
}

/* ===== DB ===== */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function bad_request(string $m, int $code=422){ http_response_code($code); json_error('BAD_REQUEST', 200, ["msg"=>$m]); }
function is_empty(?string $v): bool { return $v === null || trim($v) === ''; }
function expired(?string $dateYmd): bool {
    if (is_empty($dateYmd)) return true;
    $ts = strtotime($dateYmd); if ($ts === false) return true;
    return $ts < strtotime('today');
}

/* ===== Input ===== */
$raw  = file_get_contents('php://input');
$body = $raw ? json_decode($raw, true) : [];
$idPk    = (int)($body['id'] ?? $_GET['id'] ?? 0);                     // PK novo
$idObra  = (int)($body['num_obra'] ?? $body['id_obra'] ?? $_GET['num_obra'] ?? $_GET['id_obra'] ?? 0); // número legado
if ($idPk <= 0 && $idObra <= 0) bad_request('Forneça "id" (PK) ou "num_obra"/"id_obra".');

/* ===== Query ===== */
try {
    // 1) Obra (por PK preferido; fallback por número)
    if ($idPk > 0) {
        $sql = "SELECT id, id_obra, nome_obra, `do`, encarregado, unidade FROM obra WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idPk]);
    } else {
        $sql = "SELECT id, id_obra, nome_obra, `do`, encarregado, unidade FROM obra WHERE id_obra = :num LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':num' => $idObra]);
    }
    $obra = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$obra) {
        http_response_code(404);
        json_error('NOT_FOUND', 404, ["message"=>"Obra não encontrada."]);
    }
    $idPk   = (int)$obra['id'];
    $idObra = (int)$obra['id_obra'];

    // 2) Associações (preferir obra_pk; fallback obra_id durante migração)
    $st = $pdo->prepare("
        SELECT so.sub_user_id,
               s.nome_empresa,
               s.email_contacto,
               s.nif
          FROM subs_obra so
          JOIN subempreiteiro s ON s.user_id = so.sub_user_id
         WHERE (so.obra_pk = :pk OR so.obra_id = :num)
         ORDER BY s.nome_empresa ASC
    ");
    $st->execute([':pk'=>$idPk, ':num'=>$idObra]);
    $assocs = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $subIds = array_map(fn($a)=>(int)$a['sub_user_id'], $assocs);

    // 3) Documentos das empresas desses subempreiteiros
    $docsBySub = [];
    if ($subIds) {
        $in = implode(',', array_fill(0, count($subIds), '?'));
        $d = $pdo->prepare("
            SELECT user_id,
                   sat_comp, sat_n_apolice, sat_validade, sat_mod_seguro,
                   src_comp, src_n_apolice, src_validade,
                   dec_ss, dec_finan
              FROM doc_empresas
             WHERE user_id IN ($in)
        ");
        $d->execute($subIds);
        foreach ($d->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $docsBySub[(int)$row['user_id']] = $row;
        }
    }

    // 4) Montar lista com estado por subempreiteiro
    $items = [];
    $todosOk = !empty($assocs);
    foreach ($assocs as $a) {
        $uid = (int)$a['sub_user_id'];
        $d = $docsBySub[$uid] ?? null;
        $faltas = [];

        $sat_missing = !$d || is_empty($d['sat_comp']) || is_empty($d['sat_n_apolice']) || expired($d['sat_validade']) || is_empty($d['sat_mod_seguro']);
        if ($sat_missing) $faltas[] = 'Seguro AT';

        $src_missing = !$d || is_empty($d['src_comp']) || is_empty($d['src_n_apolice']) || expired($d['src_validade']);
        if ($src_missing) $faltas[] = 'Seguro RC';

        if (!$d || is_empty($d['dec_ss']))    $faltas[] = 'Declaração SS';
        if (!$d || is_empty($d['dec_finan'])) $faltas[] = 'Declaração Finanças';

        $okSub = empty($faltas);
        if (!$okSub) $todosOk = false;

        $items[] = [
            "user_id"      => $uid,
            "nome_empresa" => $a['nome_empresa'],
            "email"        => $a['email_contacto'],
            "nif"          => $a['nif'],
            "estado"       => $okSub ? "ok" : "em_falta",
            "em_falta"     => $faltas
        ];
    }

    // 5) Saída (inclui PK e número)
    echo json_encode([
        "ok" => true,
        "data" => [
            "id"          => $idPk,             // PK novo
            "num_obra"    => $idObra,           // número legado
            "nome_obra"   => $obra['nome_obra'],
            "diretor"     => $obra['do'],
            "encarregado" => $obra['encarregado'],
            "unidade"     => $obra['unidade'],
            "estado_geral"=> (!empty($assocs) && $todosOk) ? "ok" : "em_falta",
            "associacoes" => [
                "total" => count($items),
                "items" => $items
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    json_error('SERVER_ERROR', 500, ["detail"=>$e->getMessage()]);}
