<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../lib/helper/responses.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['is_login']) || empty($_SESSION['user']['id'])) {
    http_response_code(401);
    json_error('UNAUTHENTICATED', 401);
}

$userId = (int)$_SESSION['user']['id'];

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$payload = $_POST;

if (empty($payload) && stripos($contentType, 'application/json') !== false) {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
}

$profileFields = ['email', 'telefone', 'morada', 'nib'];

$emergencyFields = [
    'emergencia_nome' => 'nome',
    'emergencia_parentesco' => 'parentesco',
    'emergencia_telefone' => 'telefone',
    'emergencia_grupo_sanguineo' => 'grupo_sanguineo',
];

$profileInput = [];
$emergencyInput = [];

foreach ($profileFields as $field) {
    if (array_key_exists($field, $payload)) {
        $value = trim((string)$payload[$field]);
        $profileInput[$field] = $value === '' ? null : $value;
    }
}

foreach ($emergencyFields as $payloadField => $dbField) {
    if (array_key_exists($payloadField, $payload)) {
        $value = trim((string)$payload[$payloadField]);
        $emergencyInput[$dbField] = $value === '' ? null : $value;
    }
}

if (!$profileInput && !$emergencyInput) {
    json_error('NO_FIELDS');
}

if (array_key_exists('email', $profileInput) && $profileInput['email'] !== null && !filter_var($profileInput['email'], FILTER_VALIDATE_EMAIL)) {
    json_error('EMAIL_INVALID');
}

$validatePhone = function ($value): bool {
    if ($value === null || $value === '') return true;

    $digits = preg_replace('/\D+/', '', (string)$value);
    return strlen($digits) >= 9 && strlen($digits) <= 15;
};

if (array_key_exists('telefone', $profileInput) && !$validatePhone($profileInput['telefone'])) {
    json_error('PHONE_INVALID');
}

if (array_key_exists('telefone', $emergencyInput) && !$validatePhone($emergencyInput['telefone'])) {
    json_error('EMERGENCY_PHONE_INVALID');
}

if (array_key_exists('nib', $profileInput) && $profileInput['nib'] !== null && !preg_match('/^\d{21}$/', $profileInput['nib'])) {
    json_error('NIB_INVALID');
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $qPending = $pdo->prepare("
        SELECT COUNT(*)
        FROM colaborador_edicoes
        WHERE user_id = ?
          AND estado = 'pendente'
    ");
    $qPending->execute([$userId]);

    if ((int)$qPending->fetchColumn() > 0 && $profileInput) {
        json_error('PENDING_EXISTS');
    }

    $qPendingEm = $pdo->prepare("
        SELECT COUNT(*)
        FROM contactos_emergencia_edicoes
        WHERE user_id = ?
          AND estado = 'pendente'
    ");
    $qPendingEm->execute([$userId]);

    if ((int)$qPendingEm->fetchColumn() > 0 && $emergencyInput) {
        json_error('PENDING_EMERGENCY_EXISTS');
    }

    $st = $pdo->prepare("
        SELECT email, telefone, morada, nib
        FROM colaborador_dados
        WHERE user_id = ?
        LIMIT 1
    ");
    $st->execute([$userId]);
    $currentProfile = $st->fetch(PDO::FETCH_ASSOC);

    if (!$currentProfile) {
        http_response_code(404);
        json_error('PROFILE_NOT_FOUND', 404);
    }

    $st2 = $pdo->prepare("
        SELECT nome, parentesco, telefone, grupo_sanguineo
        FROM contactos_emergencia
        WHERE user_id = ?
        LIMIT 1
    ");
    $st2->execute([$userId]);
    $currentEmergency = $st2->fetch(PDO::FETCH_ASSOC) ?: [
        'nome' => null,
        'parentesco' => null,
        'telefone' => null,
        'grupo_sanguineo' => null,
    ];

    $isDifferent = function ($next, $current): bool {
        return (string)($next ?? '') !== (string)($current ?? '');
    };

    $changedProfile = [];

    foreach ($profileInput as $field => $value) {
        if ($isDifferent($value, $currentProfile[$field] ?? null)) {
            $changedProfile[$field] = $value;
        }
    }

    $changedEmergency = [];

    foreach ($emergencyInput as $field => $value) {
        if ($isDifferent($value, $currentEmergency[$field] ?? null)) {
            $changedEmergency[$field] = $value;
        }
    }

    if (!$changedProfile && !$changedEmergency) {
        json_error('NO_CHANGES');
    }

    $valueFor = function (array $changes, array $current, string $field) {
        return array_key_exists($field, $changes)
            ? $changes[$field]
            : ($current[$field] ?? null);
    };

    $pdo->beginTransaction();

    if ($changedProfile) {
        $ins = $pdo->prepare("
            INSERT INTO colaborador_edicoes
                (user_id, email, telefone, morada, nib, estado)
            VALUES
                (?, ?, ?, ?, ?, 'pendente')
        ");

        $ins->execute([
            $userId,
            $valueFor($changedProfile, $currentProfile, 'email'),
            $valueFor($changedProfile, $currentProfile, 'telefone'),
            $valueFor($changedProfile, $currentProfile, 'morada'),
            $valueFor($changedProfile, $currentProfile, 'nib'),
        ]);
    }

    if ($changedEmergency) {
        $insEm = $pdo->prepare("
            INSERT INTO contactos_emergencia_edicoes
                (user_id, nome, parentesco, telefone, grupo_sanguineo, estado)
            VALUES
                (?, ?, ?, ?, ?, 'pendente')
        ");

        $insEm->execute([
            $userId,
            $valueFor($changedEmergency, $currentEmergency, 'nome'),
            $valueFor($changedEmergency, $currentEmergency, 'parentesco'),
            $valueFor($changedEmergency, $currentEmergency, 'telefone'),
            $valueFor($changedEmergency, $currentEmergency, 'grupo_sanguineo'),
        ]);
    }

    $pdo->commit();

    echo json_encode(['success' => true]);
    exit;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('collab_request error: ' . $e->getMessage());

    http_response_code(500);
    json_error('SERVER_ERROR', 500);
}