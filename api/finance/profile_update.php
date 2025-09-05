<?php
// api/finance/profile_update.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}
$role = $_SESSION['user']['role'] ?? '';
if (!in_array($role, ['admin_rh','*'], true)) {
    http_response_code(403); echo json_encode(["ok"=>false,"code"=>"FORBIDDEN"]); exit;
}

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"BAD_JSON"]); exit; }
$userId = (int)($in['user_id'] ?? 0);
if ($userId <= 0) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"MISSING_USER"]); exit; }

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Campos válidos mapeados */
$fields = [
    'nome_completo','vencimento_estimado','vencimento_base','valor_sub_alimentacao',
    'dias_sub_alimentacao','kms_estimados','valor_por_km','valor_prevencoes',
    'valor_passe_transporte','iht','ajuda_custo_estimado','subsidio_noturno',
    'subsidio_turno','ajudas_custos_deduc','adiantamentos_deduzir','bonus_bonificacoes',
    'duodecimos','prevencoes_sn','penhoras_sn','ferias_sn',
    'faltas_nao_rem','faltas_nao_rem_just','faltas_rem_just',
    'baixa_medica_dt','ferias_data_start','ferias_data_end',
    'observacoes','ajustes_vencimento'
];
$data = [];
foreach ($fields as $f) {
    if (array_key_exists($f, $in)) $data[$f] = $in[$f];
}
if (!$data) { echo json_encode(["ok"=>true,"user_id"=>$userId,"updated"=>0]); exit; }

/* upsert */
$cols = array_keys($data);
$place = implode(',', array_fill(0, count($cols), '?'));
$updates = implode(',', array_map(fn($c)=>"$c=VALUES($c)", $cols));
$sql = "INSERT INTO finance_profiles (user_id,".implode(',', $cols).")
        VALUES (?,$place)
        ON DUPLICATE KEY UPDATE $updates, updated_at=NOW()";
$st = $pdo->prepare($sql);
$params = array_merge([$userId], array_values($data));
$st->execute($params);

echo json_encode(["ok"=>true,"user_id"=>$userId,"updated"=>1,"fields"=>array_keys($data)]);
