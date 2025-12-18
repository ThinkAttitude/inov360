<?php
// api/employee_info/record/direct_edit.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/api_error.php';
$requestId = api_request_id();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// ---------- Auth + perm (record_managment = 6)
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    api_json_error(401, 'UNAUTHENTICATED', 'Não autenticado.');
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(6, $perms, true)) {
    api_json_error(403, 'FORBIDDEN', 'Sem permissões.');
}
$actorId = (int)$_SESSION['user']['id'];

// ---------- Input (JSON ou form)
$ctype   = $_SERVER['CONTENT_TYPE'] ?? '';
$payload = $_POST;
if (empty($payload) && stripos((string)$ctype, 'application/json') !== false) {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
}

$userId        = isset($payload['user_id']) ? (int)$payload['user_id'] : 0;
$syncUserEmail = !empty($payload['sync_user_email']);

// ---------- Whitelists dos campos (colaborador_dados + contactos_emergencia)
$CD_ALLOWED = [
    // identificação/contactos
    'email','telefone','morada','codigo_postal','freguesia','concelho','distrito','naturalidade',
    // familiares/estado civil
    'habilitacoes','pai','mae','estado_civil','data_nascimento','pais',
    // documentos
    'tipo_documento','numero_documento','emitido_em','arquivo','validade_documento',
    // fiscais / SS
    'nif','numero_seg_social','descontos_fiscais','reparticao_financas','regiao','estado_fiscal',
    // dependentes / pensões
    'deficiencia','conjugue_deficiente','num_dependentes','num_dependentes_deficientes','pensionista',
    // contrato / funções
    'data_admissao','tipo_contrato','profissao','categoria','regime','horas_semana',
    // remunerações
    'salario_base','subsidio_alimentacao','nib','ordenado_liquido',
    // validação
    'validacao_empresa',
];
$EM_ALLOWED = ['nome','parentesco','telefone']; // contactos_emergencia

// ---------- Construir arrays de update
$cdUpdates = [];
$emUpdates = [];

// aceitar também keys com prefixo emergencia_*
$EM_MAP = [
    'emergencia_nome'       => 'nome',
    'emergencia_parentesco' => 'parentesco',
    'emergencia_telefone'   => 'telefone',
];

foreach ($payload as $k => $v) {
    if ($k === 'user_id' || $k === 'sync_user_email') continue;

    // colaborador_dados
    if (in_array($k, $CD_ALLOWED, true)) {
        $cdUpdates[$k] = is_string($v) ? trim((string)$v) : $v;
        continue;
    }

    // contactos_emergencia — formato direto
    if (in_array($k, $EM_ALLOWED, true)) {
        $emUpdates[$k] = is_string($v) ? trim((string)$v) : $v;
        continue;
    }
    // contactos_emergencia — formato emergencia_*
    if (isset($EM_MAP[$k])) {
        $emUpdates[$EM_MAP[$k]] = is_string($v) ? trim((string)$v) : $v;
        continue;
    }
}

// ---------- validações base
if ($userId <= 0) {
    api_json_error(400, 'BAD_REQUEST', 'Provide user_id.');
}
if (!$cdUpdates && !$emUpdates) {
    api_json_error(400, 'NO_FIELDS', 'Não foram enviados campos para atualizar.');
}

// ---------- normalização de tipos
$CD_DATE_FIELDS = ['data_nascimento','validade_documento','data_admissao'];
$CD_INT_FIELDS  = ['num_dependentes','num_dependentes_deficientes','conjugue_deficiente','pensionista','horas_semana'];
$CD_DEC_FIELDS  = ['salario_base','subsidio_alimentacao','ordenado_liquido'];
$CD_STR_FIELDS  = ['email','telefone','morada','codigo_postal','freguesia','concelho','distrito','naturalidade','habilitacoes','pai','mae','estado_civil','pais','tipo_documento','numero_documento','emitido_em','arquivo','nif','numero_seg_social','descontos_fiscais','reparticao_financas','regiao','estado_fiscal','deficiencia','tipo_contrato','profissao','categoria','regime','nib','validacao_empresa'];

$normCd = function (&$arr) use ($CD_DATE_FIELDS,$CD_INT_FIELDS,$CD_DEC_FIELDS,$CD_STR_FIELDS) {
    foreach ($arr as $k => &$v) {
        if (in_array($k, $CD_DATE_FIELDS, true)) {
            $v = ($v === '' || $v === null) ? null : (string)$v;
        } elseif (in_array($k, $CD_INT_FIELDS, true)) {
            $v = ($v === '' || $v === null) ? null : (int)$v;
        } elseif (in_array($k, $CD_DEC_FIELDS, true)) {
            $v = ($v === '' || $v === null) ? null : (float)$v;
        } elseif (in_array($k, $CD_STR_FIELDS, true)) {
            $v = ($v === null) ? null : trim((string)$v);
        }
    }
};
$normCd($cdUpdates);

foreach ($emUpdates as $k => &$v) {
    $v = ($v === null) ? null : trim((string)$v);
    if ($v === '') $v = null;
}
unset($v);

// ---------- validações simples
if (isset($cdUpdates['email']) && $cdUpdates['email'] !== null && $cdUpdates['email'] !== '' && !filter_var($cdUpdates['email'], FILTER_VALIDATE_EMAIL)) {
    api_json_error(400, 'EMAIL_INVALID', 'Email inválido.');

}
$validaTelefone = function ($t) {
    if ($t === null || $t === '') return true;
    $d = preg_replace('/\D+/', '', (string)$t);
    return strlen($d) >= 9 && strlen($d) <= 15;
};
if (isset($cdUpdates['telefone']) && !$validaTelefone($cdUpdates['telefone'])) {
    api_json_error(400, 'PHONE_INVALID', 'Telefone inválido.');

}
if (isset($emUpdates['telefone']) && !$validaTelefone($emUpdates['telefone'])) {
    api_json_error(400, 'EM_PHONE_INVALID', 'Telefone de emergência inválido.');

}
if (isset($cdUpdates['nib']) && $cdUpdates['nib'] !== null && $cdUpdates['nib'] !== '' && !preg_match('/^\d{21}$/', (string)$cdUpdates['nib'])) {
    api_json_error(400, 'NIB_INVALID', 'NIB inválido.');

}
$checkDate = function ($v) {
    if ($v === '' || $v === null) return true;
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$v);
};
foreach (['data_nascimento','validade_documento','data_admissao'] as $dk) {
    if (isset($cdUpdates[$dk]) && !$checkDate($cdUpdates[$dk])) {
        api_json_error(400, 'DATE_INVALID', 'Formato de data inválido (YYYY-MM-DD).', null, ['field' => $dk]);


    }
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // garantir user
    $st = $pdo->prepare("SELECT id, email FROM `user` WHERE id=? LIMIT 1");
    $st->execute([$userId]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    if (!$u) {
        api_json_error(404, 'USER_NOT_FOUND', 'Utilizador não encontrado.');
    }


    $pdo->beginTransaction();

    // -------- colaborador_dados: UPDATE-first; senão INSERT
    if ($cdUpdates) {
        $ex = $pdo->prepare("SELECT user_id FROM colaborador_dados WHERE user_id=? LIMIT 1");
        $ex->execute([$userId]);
        $exists = (bool)$ex->fetchColumn();

        if ($exists) {
            $set = [];
            $params = [];
            foreach ($cdUpdates as $col => $val) {
                $set[] = "`$col` = ?";
                $params[] = $val;
            }
            if ($set) {
                $params[] = $userId;
                $sql = "UPDATE colaborador_dados SET ".implode(', ', $set)." WHERE user_id = ?";
                $pdo->prepare($sql)->execute($params);
            }
        } else {
            $cols = ['user_id'];
            $vals = ['?'];
            $params = [$userId];

            if (!array_key_exists('email', $cdUpdates)) {
                $cdUpdates['email'] = $u['email'] ?? null; // pode ser NULL
            }
            foreach ($cdUpdates as $col => $val) {
                $cols[] = "`$col`"; $vals[] = '?'; $params[] = $val;
            }
            $sql = "INSERT INTO colaborador_dados (".implode(',', $cols).") VALUES (".implode(',', $vals).")";
            $pdo->prepare($sql)->execute($params);
        }

        // sincronizar email na tabela user, se pedido
        if ($syncUserEmail && array_key_exists('email', $cdUpdates)) {
            if ($cdUpdates['email'] === '') {
                throw new RuntimeException('USER_EMAIL_EMPTY_NOT_ALLOWED');
            }
            if ($cdUpdates['email'] !== null) {
                $chk = $pdo->prepare("SELECT id FROM `user` WHERE email=? AND id<>? LIMIT 1");
                $chk->execute([$cdUpdates['email'], $userId]);
                if ($chk->fetch()) throw new RuntimeException('EMAIL_IN_USE');

                $pdo->prepare("UPDATE `user` SET email=? WHERE id=?")
                    ->execute([$cdUpdates['email'], $userId]);
            }
        }
    }

    // -------- contactos_emergencia: UPDATE-first; senão INSERT
    if ($emUpdates) {
        $ex = $pdo->prepare("SELECT id FROM contactos_emergencia WHERE user_id=? LIMIT 1");
        $ex->execute([$userId]);
        $row = $ex->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $set = [];
            $params = [];
            foreach ($emUpdates as $col => $val) {
                $set[] = "`$col` = ?";
                $params[] = $val;
            }
            if ($set) {
                $params[] = $userId;
                $sql = "UPDATE contactos_emergencia SET ".implode(', ', $set)." WHERE user_id = ?";
                $pdo->prepare($sql)->execute($params);
            }
        } else {
            $cols = ['user_id'];
            $vals = ['?'];
            $params = [$userId];
            foreach ($emUpdates as $col => $val) {
                $cols[] = "`$col`"; $vals[] = '?'; $params[] = $val;
            }
            $sql = "INSERT INTO contactos_emergencia (".implode(',', $cols).") VALUES (".implode(',', $vals).")";
            $pdo->prepare($sql)->execute($params);
        }
    }

    $pdo->commit();

    // -------- devolver ficha atualizada
    $stmtProfile = $pdo->prepare("SELECT * FROM colaborador_dados WHERE user_id=? LIMIT 1");
    $stmtProfile->execute([$userId]);
    $profile = $stmtProfile->fetch(PDO::FETCH_ASSOC) ?: null;

    $stmtEmerg = $pdo->prepare("SELECT id, user_id, nome, parentesco, telefone FROM contactos_emergencia WHERE user_id=? LIMIT 1");
    $stmtEmerg->execute([$userId]);
    $emergency = $stmtEmerg->fetch(PDO::FETCH_ASSOC) ?: null;

    echo json_encode([
        'success' => true,
        'updated' => ['profile'=>$profile, 'emergency'=>$emergency]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();

    api_log_exception($e, $requestId, [
        'endpoint' => 'employee_info/record/direct_edit.php',
        'actor_id' => $actorId ?? null,
        'user_id'  => $userId ?? null,
    ]);

    $msg = $e->getMessage();

    if ($msg === 'EMAIL_IN_USE') {
        api_json_error(409, 'EMAIL_IN_USE', 'Email já está em uso.', $requestId);
    }
    if ($msg === 'USER_EMAIL_EMPTY_NOT_ALLOWED') {
        api_json_error(400, 'USER_EMAIL_EMPTY_NOT_ALLOWED', 'Email não pode ser vazio.', $requestId);
    }

    api_json_error(500, 'INTERNAL_ERROR', 'Ocorreu um erro inesperado.', $requestId);
}
