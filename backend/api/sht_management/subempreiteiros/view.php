<?php
// api/grupo_inov/subempreiteiros/view.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]);
    exit;
}
$user = $_SESSION['user'];

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(8, $perms, true)) { // sht_management
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN_PERMISSION']); exit;
}

/* ===== DB ===== */
require_once __DIR__ . '/../../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Helpers ===== */
function bad_request(string $m){ http_response_code(422); echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST","message"=>$m]); exit; }
function body(): array {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct,'application/json')!==false) {
        $raw = file_get_contents('php://input'); $d = json_decode($raw,true);
        return is_array($d)?$d:[];
    }
    return $_POST ?: $_GET;
}
function is_empty($v): bool {
    if ($v === null) return true;
    if (is_string($v)) return trim($v) === '';
    return $v === '' || $v === [];
}
function expired(?string $dateYmd): bool {
    if (is_empty($dateYmd)) return true;
    $ts = strtotime($dateYmd);
    if ($ts === false) return true;
    return $ts < strtotime('today'); // expira no fim do dia anterior
}

/* ===== Input ===== */
// aceita user_id OU nif OU email_contacto para conveniência de pesquisa
$in = body();
$userId = (int)($in['user_id'] ?? 0);
$nif    = trim((string)($in['nif'] ?? ''));
$email  = trim((string)($in['email'] ?? $in['email_contacto'] ?? ''));

if ($userId <= 0 && $nif === '' && $email === '') {
    bad_request('Envie "user_id" ou "nif" ou "email".');
}

try {
    /* ===== 1) Obter subempreiteiro ===== */
    if ($userId > 0) {
        $st = $pdo->prepare('SELECT * FROM subempreiteiro WHERE user_id = :id LIMIT 1');
        $st->execute([':id'=>$userId]);
    } elseif ($nif !== '') {
        $st = $pdo->prepare('SELECT * FROM subempreiteiro WHERE nif = :nif LIMIT 1');
        $st->execute([':nif'=>$nif]);
    } else {
        $st = $pdo->prepare('SELECT * FROM subempreiteiro WHERE email_contacto = :e LIMIT 1');
        $st->execute([':e'=>$email]);
    }
    $sub = $st->fetch(PDO::FETCH_ASSOC);
    if (!$sub) { http_response_code(404); echo json_encode(["ok"=>false,"code"=>"SUB_NOT_FOUND"]); exit; }

    $uid = (int)$sub['user_id'];

    /* ===== 2) Documentação da empresa ===== */
    $d = $pdo->prepare('
        SELECT user_id, atividade, num_alvara, niss_alvara,
               sat_comp, sat_n_apolice, sat_validade, sat_mod_seguro,
               src_comp, src_n_apolice, src_validade,
               dec_ss, dec_finan, created_at
          FROM sub_doc_empresas
         WHERE user_id = :u
         LIMIT 1
    ');
    $d->execute([':u'=>$uid]);
    $docs = $d->fetch(PDO::FETCH_ASSOC) ?: [];

    $faltas = [];
    // Seguro AT
    $sat_missing = is_empty($docs['sat_comp'] ?? null)
        || is_empty($docs['sat_n_apolice'] ?? null)
        || expired($docs['sat_validade'] ?? null)
        || is_empty($docs['sat_mod_seguro'] ?? null);
    if ($sat_missing) $faltas[] = 'Seguro AT';

    // Seguro RC
    $src_missing = is_empty($docs['src_comp'] ?? null)
        || is_empty($docs['src_n_apolice'] ?? null)
        || expired($docs['src_validade'] ?? null);
    if ($src_missing) $faltas[] = 'Seguro RC';

    // Declarações
    if (is_empty($docs['dec_ss'] ?? null))    $faltas[] = 'Declaração SS';
    if (is_empty($docs['dec_finan'] ?? null)) $faltas[] = 'Declaração Finanças';

    $estadoEmpresa = empty($faltas) ? 'ok' : 'empresa_em_falta';

    /* ===== 3) Colaboradores desse subempreiteiro ===== */
    $c = $pdo->prepare('SELECT * FROM sub_doc_colaboradores WHERE sub_user_id = :u ORDER BY nome_colab ASC, id ASC');
    $c->execute([':u'=>$uid]);
    $rows = $c->fetchAll(PDO::FETCH_ASSOC);

    // alguns ambientes têm colunas adicionais (ex.: vinc_path, epi_path, ficha_path).
    // Vamos detectar dinamicamente os campos de ficheiros presentes na linha.
    $KNOWN_FILE_FIELDS = [
        'doc_id_path','cv_ss_path','fam_path','entrega_epi_path',
        'form_desc_path','form_esp_desc_path','vinc_path','epi_path','ficha_path'
    ];

    $colaboradores = [];
    foreach ($rows as $r) {
        $files = [];
        $presentFields = array_intersect($KNOWN_FILE_FIELDS, array_keys($r));
        $missing = [];

        foreach ($presentFields as $f) {
            $has = !is_empty($r[$f]);
            $files[$f] = [
                'path' => $r[$f],
                'has'  => $has
            ];
            if (!$has) $missing[] = $f;
        }

        // estado: usar a coluna 'estado' se existir, senão calcular
        $estado = $r['estado'] ?? (empty($missing) ? 'completo' : 'incompleto');

        $colaboradores[] = [
            'id'         => (int)$r['id'],
            'nome'       => $r['nome_colab'] ?? null,
            'nif'        => $r['nif'] ?? null,
            'categ_prof' => $r['categ_prof'] ?? null,
            'estado'     => $estado,
            'ficheiros'  => $files,
            'em_falta'   => $missing,
            'created_at' => $r['created_at'] ?? null,
            'updated_at' => $r['updated_at'] ?? null,
        ];
    }

    /* ===== 4) Saída ===== */
    echo json_encode([
        "ok"   => true,
        "data" => [
            "empresa" => [
                "user_id"       => $uid,
                "nome_empresa"  => $sub['nome_empresa'] ?? null,
                "email"         => $sub['email_contacto'] ?? null,
                "nif"           => $sub['nif'] ?? null,
                "morada"        => $sub['morada'] ?? null,
                "zona_atuacao"  => $sub['zona_atuacao'] ?? null,
                "atividade"     => $docs['atividade'] ?? null,
                "num_alvara"    => $docs['num_alvara'] ?? null,
                "niss_alvara"   => $docs['niss_alvara'] ?? null,
                "sat" => [
                    "companhia"   => $docs['sat_comp'] ?? null,
                    "n_apolice"   => $docs['sat_n_apolice'] ?? null,
                    "validade"    => $docs['sat_validade'] ?? null,
                    "modalidade"  => $docs['sat_mod_seguro'] ?? null,
                ],
                "src" => [
                    "companhia"   => $docs['src_comp'] ?? null,
                    "n_apolice"   => $docs['src_n_apolice'] ?? null,
                    "validade"    => $docs['src_validade'] ?? null,
                ],
                "declaracoes" => [
                    "ss"    => $docs['dec_ss'] ?? null,
                    "finan" => $docs['dec_finan'] ?? null,
                ],
                "estado"     => $estadoEmpresa,
                "em_falta"   => $faltas
            ],
            "colaboradores" => $colaboradores
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok"=>false,"code"=>"SERVER_ERROR","detail"=>$e->getMessage()]);
}
