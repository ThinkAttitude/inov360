<?php
// api/subempreiteiros/company_doc_view.php
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

/* ===== Helpers ===== */
function filled($v): bool { return !is_null($v) && trim((string)$v) !== ''; }

/* ===== Query ===== */
$sql = "
SELECT
  s.user_id,
  s.nome_empresa,
  s.morada,
  s.nif,
  s.zona_atuacao,
  s.created_at AS sub_created_at,

  d.atividade,
  d.num_alvara,
  d.niss_alvara,
  d.sat_comp,
  d.sat_n_apolice,
  d.sat_validade,
  d.sat_mod_seguro,
  d.src_comp,
  d.src_n_apolice,
  d.src_validade,
  d.dec_ss,
  d.dec_finan,
  d.created_at AS docs_created_at
FROM subempreiteiro s
LEFT JOIN sub_doc_empresas d ON d.user_id = s.user_id
WHERE s.user_id = :uid
LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([':uid' => $userId]);
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode([
        "ok" => true,
        "data" => null,
        "message" => "Nenhum registo de empresa encontrado para este utilizador."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ===== Calcular estado dos documentos (completo se TODOS preenchidos) ===== */
$allDocFields = [
    'atividade','num_alvara','niss_alvara',
    'sat_comp','sat_n_apolice','sat_validade','sat_mod_seguro',
    'src_comp','src_n_apolice','src_validade',
    'dec_ss','dec_finan'
];
$docsOk = true;
foreach ($allDocFields as $f) {
    if (!filled($row[$f] ?? null)) { $docsOk = false; break; }
}
$estado_docs = $docsOk ? 'completo' : 'incompleto';

/* ===== Response ===== */
echo json_encode([
    "ok" => true,
    "data" => [
        "user_id"       => (int)$row['user_id'],
        "nome_empresa"  => $row['nome_empresa'],
        "morada"        => $row['morada'],
        "nif"           => $row['nif'],
        "zona_atuacao"  => $row['zona_atuacao'],

        "atividade"       => $row['atividade'],
        "num_alvara"      => $row['num_alvara'],
        "niss_alvara"     => $row['niss_alvara'],
        "sat_comp"        => $row['sat_comp'],
        "sat_n_apolice"   => $row['sat_n_apolice'],
        "sat_validade"    => $row['sat_validade'],
        "sat_mod_seguro"  => $row['sat_mod_seguro'],
        "src_comp"        => $row['src_comp'],
        "src_n_apolice"   => $row['src_n_apolice'],
        "src_validade"    => $row['src_validade'],
        "dec_ss"          => $row['dec_ss'],
        "dec_finan"       => $row['dec_finan'],

        "estado_docs"   => $estado_docs,

        "audit" => [
            "sub_created_at"  => $row['sub_created_at'],
            "docs_created_at" => $row['docs_created_at']
        ]
    ]
], JSON_UNESCAPED_UNICODE);
