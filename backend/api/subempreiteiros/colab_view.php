<?php
// api/subempreiteiros/colab_view.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}
$user   = $_SESSION['user'];
$userId = (int)($user['id'] ?? 0);

/* ===== DB ===== */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../lib/helper/responses.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Input ===== */
function bad_request(string $m, int $code=400){
    http_response_code($code);
    json_error('BAD_REQUEST', 200, ["message"=>$m]);
}

$raw = file_get_contents('php://input');
$body = $raw ? json_decode($raw, true) : null;
$colabId = (int)($body['id'] ?? ($_GET['id'] ?? 0));
if ($colabId <= 0) bad_request('Parâmetro "id" é obrigatório.', 422);

/* ===== Query ===== */
$sql = "SELECT
          dc.id,
          dc.sub_user_id,
          dc.nome_colab,
          dc.nif,
          dc.categ_prof,
          dc.form_esp_desc,
          dc.estado,
          dc.doc_id_path,
          dc.cv_ss_path,
          dc.fam_path,
          dc.entrega_epi_path,
          dc.ficha_trab_path
        FROM sub_doc_colaboradores dc
        WHERE dc.id = :id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $colabId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    json_error('NOT_FOUND', 404, ["message"=>"Colaborador não encontrado."]);
}
if ((int)$row['sub_user_id'] !== $userId) {
    http_response_code(403);
    json_error('FORBIDDEN', 403, ["message"=>"Sem acesso a este colaborador."]);
}

/* ===== Response ===== */
echo json_encode([
    "ok" => true,
    "data" => [
        "id"              => (int)$row['id'],
        "nome_colab"      => $row['nome_colab'],
        "nif"             => $row['nif'],
        "categ_prof"      => $row['categ_prof'],
        "form_esp_desc"   => $row['form_esp_desc'],
        "estado"          => $row['estado'],
        "doc_id_path"     => $row['doc_id_path'],
        "cv_ss_path"      => $row['cv_ss_path'],
        "fam_path"        => $row['fam_path'],
        "entrega_epi_path"=> $row['entrega_epi_path'],
        "ficha_trab_path" => $row['ficha_trab_path']
    ]
], JSON_UNESCAPED_UNICODE);
