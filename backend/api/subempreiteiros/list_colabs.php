<?php
// api/subempreiteiros/list.colabs.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* Auth */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$user   = $_SESSION['user'];
$userId = (int)($user['id'] ?? 0);

/* DB */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Query params: paginação + pesquisa opcional */
$limit = max(1, (int)($_GET['limit'] ?? 20));
$limit = min($limit, 100);
$page  = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$q = trim((string)($_GET['q'] ?? '')); // pesquisa por nome/nif

/* SQL base */
$where = "dc.sub_user_id = :uid";
$params = [':uid' => $userId];

if ($q !== '') {
    $where .= " AND (dc.nome_colab LIKE :q OR dc.nif LIKE :q)";
    $params[':q'] = '%'.$q.'%';
}

/* total */
$sqlCount = "SELECT COUNT(*) FROM sub_doc_colaboradores dc WHERE $where";
$total = (int)$pdo->prepare($sqlCount)->execute($params) ?: 0;
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total = (int)$stmtCount->fetchColumn();

/* listagem */
$sql = "SELECT 
            dc.id,
            dc.sub_user_id,
            dc.nome_colab,
            dc.nif,
            dc.categ_prof,
            dc.form_esp_desc,
            dc.doc_id_path,
            dc.cv_ss_path,
            dc.fam_path,
            dc.entrega_epi_path,
            dc.ficha_trab_path,
            dc.estado,
            dc.created_at,
            dc.updated_at
        FROM sub_doc_colaboradores dc
        WHERE $where
        ORDER BY dc.created_at DESC, dc.id DESC
        LIMIT :lim OFFSET :off";
$stmt = $pdo->prepare($sql);
foreach ($params as $k=>$v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "ok"    => true,
    "page"  => $page,
    "limit" => $limit,
    "total" => $total,
    "data"  => array_map(function ($r) {
        // fallback de estado
        if (empty($r['estado'])) $r['estado'] = 'incompleto';
        return [
            "id"               => (int)$r['id'],
            "sub_user_id"      => (int)$r['sub_user_id'],
            "nome_colab"       => $r['nome_colab'],
            "nif"              => $r['nif'],
            "categ_prof"       => $r['categ_prof'],
            "form_esp_desc"    => $r['form_esp_desc'],
            "doc_id_path"      => $r['doc_id_path'],
            "cv_ss_path"       => $r['cv_ss_path'],
            "fam_path"         => $r['fam_path'],
            "entrega_epi_path" => $r['entrega_epi_path'],
            "ficha_trab_path"  => $r['ficha_trab_path'],
            "estado"           => $r['estado'],
            "created_at"       => $r['created_at'],
            "updated_at"       => $r['updated_at']
        ];
    }, $rows)
], JSON_UNESCAPED_UNICODE);
