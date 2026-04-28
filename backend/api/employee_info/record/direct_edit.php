<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'UNAUTHENTICATED']);
    exit;
}

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(6, $perms, true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'FORBIDDEN']);
    exit;
}

$ctype = $_SERVER['CONTENT_TYPE'] ?? '';
$payload = $_POST;

if (empty($payload) && stripos((string)$ctype, 'application/json') !== false) {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
}

$userId = isset($payload['user_id']) ? (int)$payload['user_id'] : 0;
$syncUserEmail = !empty($payload['sync_user_email']);

$CD_ALLOWED = [
    'nome',
    'email',
    'telefone',
    'morada',
    'codigo_postal',
    'concelho',
    'distrito',
    'naturalidade',
    'habilitacoes',
    'estado_civil',
    'data_nascimento',
    'pais',
    'tipo_documento',
    'numero_documento',
    'emitido_em',
    'arquivo',
    'validade_documento',
    'nif',
    'numero_seg_social',
    'estado_fiscal',
    'deficiencia',
    'conjugue_deficiente',
    'num_dependentes',
    'num_dependentes_deficientes',
    'pensionista',
    'data_admissao',
    'tipo_contrato',
    'profissao',
    'categoria',
    'regime',
    'horas_semana',
    'salario_base',
    'subsidio_alimentacao',
    'nib',
    'ordenado_liquido',
];

$cdUpdates = [];

foreach ($payload as $k => $v) {
    if ($k === 'user_id' || $k === 'sync_user_email') {
        continue;
    }

    if (in_array($k, $CD_ALLOWED, true)) {
        $cdUpdates[$k] = is_string($v) ? trim((string)$v) : $v;
    }
}

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'BAD_REQUEST', 'hint' => 'Provide user_id']);
    exit;
}

if (!$cdUpdates) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'NO_FIELDS']);
    exit;
}

$CD_DATE_FIELDS = [
    'data_nascimento',
    'emitido_em',
    'validade_documento',
    'data_admissao',
];

$CD_INT_FIELDS = [
    'conjugue_deficiente',
    'num_dependentes',
    'num_dependentes_deficientes',
    'pensionista',
    'horas_semana',
];

$CD_DEC_FIELDS = [
    'salario_base',
    'subsidio_alimentacao',
    'ordenado_liquido',
];

$CD_STR_FIELDS = [
    'nome',
    'email',
    'telefone',
    'morada',
    'codigo_postal',
    'concelho',
    'distrito',
    'naturalidade',
    'habilitacoes',
    'estado_civil',
    'pais',
    'tipo_documento',
    'numero_documento',
    'arquivo',
    'nif',
    'numero_seg_social',
    'estado_fiscal',
    'deficiencia',
    'tipo_contrato',
    'profissao',
    'categoria',
    'regime',
    'nib',
];

foreach ($cdUpdates as $k => &$v) {
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
unset($v);

if (isset($cdUpdates['email']) && $cdUpdates['email'] !== null && $cdUpdates['email'] !== '' && !filter_var($cdUpdates['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'EMAIL_INVALID']);
    exit;
}

$validatePhone = function ($value): bool {
    if ($value === null || $value === '') {
        return true;
    }

    $digits = preg_replace('/\D+/', '', (string)$value);
    return strlen($digits) >= 9 && strlen($digits) <= 15;
};

if (isset($cdUpdates['telefone']) && !$validatePhone($cdUpdates['telefone'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'PHONE_INVALID']);
    exit;
}

if (isset($cdUpdates['nib']) && $cdUpdates['nib'] !== null && $cdUpdates['nib'] !== '' && !preg_match('/^\d{21}$/', (string)$cdUpdates['nib'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'NIB_INVALID']);
    exit;
}

$validateDate = function ($value): bool {
    if ($value === '' || $value === null) {
        return true;
    }

    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value);
};

foreach ($CD_DATE_FIELDS as $field) {
    if (isset($cdUpdates[$field]) && !$validateDate($cdUpdates[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'DATE_INVALID', 'field' => $field]);
        exit;
    }
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $st = $pdo->prepare("SELECT id, email FROM `user` WHERE id = ? LIMIT 1");
    $st->execute([$userId]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'USER_NOT_FOUND']);
        exit;
    }

    $pdo->beginTransaction();

    $ex = $pdo->prepare("SELECT user_id FROM colaborador_dados WHERE user_id = ? LIMIT 1");
    $ex->execute([$userId]);
    $exists = (bool)$ex->fetchColumn();

    if (!$exists) {
        $pdo->prepare("INSERT INTO colaborador_dados (user_id, email) VALUES (?, ?)")
            ->execute([$userId, $user['email'] ?? null]);
    }

    $set = [];
    $params = [];

    foreach ($cdUpdates as $col => $val) {
        $set[] = "`$col` = ?";
        $params[] = $val;
    }

    $params[] = $userId;

    $sql = "UPDATE colaborador_dados SET " . implode(', ', $set) . " WHERE user_id = ?";
    $pdo->prepare($sql)->execute($params);

    if ($syncUserEmail && array_key_exists('email', $cdUpdates)) {
        if ($cdUpdates['email'] === '') {
            throw new RuntimeException('USER_EMAIL_EMPTY_NOT_ALLOWED');
        }

        if ($cdUpdates['email'] !== null) {
            $chk = $pdo->prepare("SELECT id FROM `user` WHERE email = ? AND id <> ? LIMIT 1");
            $chk->execute([$cdUpdates['email'], $userId]);

            if ($chk->fetch()) {
                throw new RuntimeException('EMAIL_IN_USE');
            }

            $pdo->prepare("UPDATE `user` SET email = ? WHERE id = ?")
                ->execute([$cdUpdates['email'], $userId]);
        }
    }

    $pdo->commit();

    $stmtProfile = $pdo->prepare("SELECT * FROM colaborador_dados WHERE user_id = ? LIMIT 1");
    $stmtProfile->execute([$userId]);
    $profile = $stmtProfile->fetch(PDO::FETCH_ASSOC) ?: null;

    echo json_encode([
        'success' => true,
        'updated' => [
            'profile' => $profile,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('direct_edit error: ' . $e->getMessage());

    $msg = $e->getMessage();

    if ($msg === 'EMAIL_IN_USE') {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'EMAIL_IN_USE']);
        exit;
    }

    if ($msg === 'USER_EMAIL_EMPTY_NOT_ALLOWED') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'USER_EMAIL_EMPTY_NOT_ALLOWED']);
        exit;
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'INTERNAL_ERROR']);
    exit;
}