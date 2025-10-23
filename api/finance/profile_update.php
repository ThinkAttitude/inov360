<?php
// api/finance/profile_update.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* --------- auth --------- */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']);
    exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(7, $perms, true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'Do not have permission']);
    exit;
}

/* input */
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) { http_response_code(400); echo json_encode(["ok"=>false,"code"=>"BAD_JSON"]); exit; }
$userId = (int)($in['user_id'] ?? 0);
if ($userId <= 0) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'MISSING_USER']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* aliases para compatibilidade com payloads antigos */
$aliases = [
    'baixa_medica_dt'    => 'baixa_medica_start',   // se vier 1 data, mete como start
    'ferias_data_start'  => 'ferias_start',
    'ferias_data_end'    => 'ferias_end',
    'duodecimos'         => 'duodecimos_sn',
    'valor_km'           => 'valor_por_km',
    'bonus'              => 'bonus_bonificacoes',
    'adiantamentos'      => 'adiantamentos_deduzir',
    'ajudas_custos_deduzir' => 'ajudas_custos_deduc',
    'faltas_justificadas_nao_rem' => 'faltas_nao_rem_just',
    'faltas_justificadas_rem'     => 'faltas_rem_just'
];
foreach ($aliases as $old => $new) {
    if (array_key_exists($old, $in) && !array_key_exists($new, $in)) {
        $in[$new] = $in[$old];
    }
}

/* se vier só baixa_medica_start sem end, assume igual */
if (!empty($in['baixa_medica_start']) && empty($in['baixa_medica_end'])) {
    $in['baixa_medica_end'] = $in['baixa_medica_start'];
}

/* whitelist de colunas válidas da tabela finance_profiles */
$fields = [
    'numero','nome_completo',
    'vencimento_estimado','vencimento_base','valor_sub_alimentacao','dias_sub_alimentacao',
    'kms_estimados','valor_por_km','valor_prevencoes','valor_passe_transporte','iht',
    'ajuda_custo_estimado','subsidio_noturno','subsidio_turno','ajudas_custos_deduc',
    'adiantamentos_deduzir','bonus_bonificacoes',
    'duodecimos','prevencoes_sn','penhoras_sn','ferias_sn',
    'faltas_nao_rem','faltas_nao_rem_just','faltas_rem_just',
    'baixa_medica_start','baixa_medica_end',
    'ferias_start','ferias_end',
    'observacoes','ajustes_vencimento'
];

/* extrai apenas os campos permitidos */
$data = [];
foreach ($fields as $f) {
    if (array_key_exists($f, $in)) $data[$f] = $in[$f];
}
if (!$data) { echo json_encode(["ok"=>true,"user_id"=>$userId,"updated"=>0]); exit; }

/* UPSERT */
$cols = array_keys($data);
$placeholders = implode(',', array_fill(0, count($cols), '?'));
$updates = implode(',', array_map(fn($c)=>"$c=VALUES($c)", $cols));
$sql = "INSERT INTO finance_profiles (user_id,".implode(',', $cols).")
        VALUES (?,$placeholders)
        ON DUPLICATE KEY UPDATE $updates, updated_at=NOW()";
$st = $pdo->prepare($sql);
$params = array_merge([$userId], array_values($data));
$st->execute($params);

echo json_encode([
    "ok" => true,
    "user_id" => $userId,
    "updated" => 1,
    "fields" => array_keys($data)
]);
