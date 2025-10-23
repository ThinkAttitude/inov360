<?php
// api/employee_info/record/colab_request.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// 1) Sessão
if (empty($_SESSION['is_login']) || empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']); exit;
}
$userId = (int) $_SESSION['user']['id'];

// 2) Inputs (dos teus cards)
$payload = $_POST;
if (empty($payload) && $_SERVER['CONTENT_TYPE'] ?? '' && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
}

$email   = trim((string)($payload['email'] ?? ''));
$tel     = trim((string)($payload['contacto_telefone'] ?? ''));
$morada  = trim((string)($payload['morada'] ?? ''));
$nib     = trim((string)($payload['nib'] ?? '')); // (legacy; se fores para IBAN, trocamos)

$emNome  = trim((string)($payload['emergencia_nome'] ?? ''));
$emParen = trim((string)($payload['emergencia_parentesco'] ?? ''));
$emTel   = trim((string)($payload['emergencia_telefone'] ?? ''));

// 3) Pelo menos um campo enviado
if ($email==='' && $tel==='' && $morada==='' && $nib==='' && $emNome==='' && $emParen==='' && $emTel==='') {
    echo json_encode(['success'=>false,'error'=>'NO_FIELDS']); exit;
}

// 4) Validações simples
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success'=>false,'error'=>'EMAIL_INVALID']); exit;
}
$telDigits = preg_replace('/\D+/', '', $tel);
if ($tel !== '' && (strlen($telDigits) < 9 || strlen($telDigits) > 15)) {
    echo json_encode(['success'=>false,'error'=>'PHONE_INVALID']); exit;
}
if ($nib !== '' && !preg_match('/^\d{21}$/', $nib)) {
    echo json_encode(['success'=>false,'error'=>'NIB_INVALID']); exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 5) Impedir pedidos pendentes existentes (um de cada vez por tipo)
    $qPending = $pdo->prepare("SELECT COUNT(*) FROM inov360.colaborador_edicoes WHERE user_id=? AND estado='pendente'");
    $qPending->execute([$userId]);
    if ((int)$qPending->fetchColumn() > 0) {
        echo json_encode(['success'=>false,'error'=>'PENDING_EXISTS']); exit;
    }

    $qPendingEm = $pdo->prepare("SELECT COUNT(*) FROM inov360.contactos_emergencia_edicoes WHERE user_id=? AND estado='pendente'");
    $qPendingEm->execute([$userId]);
    $hasPendingEm = ((int)$qPendingEm->fetchColumn() > 0);

    // 6) Buscar atuais para comparação
    $st = $pdo->prepare("
        SELECT email, telefone, morada, nib
        FROM inov360.colaborador_dados
        WHERE user_id=? LIMIT 1
    ");
    $st->execute([$userId]);
    $atuais = $st->fetch(PDO::FETCH_ASSOC);
    if (!$atuais) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'PROFILE_NOT_FOUND']); exit; }

    $st2 = $pdo->prepare("
        SELECT nome, parentesco, telefone
        FROM inov360.contactos_emergencia
        WHERE user_id=? LIMIT 1
    ");
    $st2->execute([$userId]);
    $atualEm = $st2->fetch(PDO::FETCH_ASSOC) ?: ['nome'=>'','parentesco'=>'','telefone'=>''];

    // 7) Detectar alterações reais
    $chgProfile =
        ($email  !== '' && $email  !== ($atuais['email']    ?? '')) ||
        ($tel    !== '' && $tel    !== ($atuais['telefone'] ?? '')) ||
        ($morada !== '' && $morada !== ($atuais['morada']   ?? '')) ||
        ($nib    !== '' && $nib    !== ($atuais['nib']      ?? ''));

    $chgEmergency =
        ($emNome  !== '' && $emNome  !== ($atualEm['nome']       ?? '')) ||
        ($emParen !== '' && $emParen !== ($atualEm['parentesco']  ?? '')) ||
        ($emTel   !== '' && $emTel   !== ($atualEm['telefone']    ?? ''));

    if (!$chgProfile && !$chgEmergency) {
        echo json_encode(['success'=>false,'error'=>'NO_CHANGES']); exit;
    }
    if ($chgEmergency && $hasPendingEm) {
        echo json_encode(['success'=>false,'error'=>'PENDING_EMERGENCY_EXISTS']); exit;
    }

    // 8) Gravar pedidos (transação)
    $pdo->beginTransaction();

    if ($chgProfile) {
        // Tabela: colaborador_edicoes (id, user_id, email, telefone, estado, avaliado_por, avaliado_em, criado_em, morada, nib)
        $ins = $pdo->prepare("
            INSERT INTO inov360.colaborador_edicoes
                (user_id, email, telefone, morada, nib, estado)
            VALUES
                (?, ?, ?, ?, ?, 'pendente')
        ");
        $ins->execute([
            $userId,
            $email  !== '' ? $email  : ($atuais['email'] ?? null),
            $tel    !== '' ? $tel    : ($atuais['telefone'] ?? null),
            $morada !== '' ? $morada : ($atuais['morada'] ?? null),
            $nib    !== '' ? $nib    : ($atuais['nib'] ?? null),
        ]);
    }

    if ($chgEmergency) {
        // Tabela: contactos_emergencia_edicoes (id, user_id, nome, parentesco, telefone, estado, criado_em, avaliado_por, avaliado_em)
        $insEm = $pdo->prepare("
            INSERT INTO inov360.contactos_emergencia_edicoes
                (user_id, nome, parentesco, telefone, estado)
            VALUES
                (?, ?, ?, ?, 'pendente')
        ");
        $insEm->execute([
            $userId,
            $emNome  !== '' ? $emNome  : ($atualEm['nome'] ?? null),
            $emParen !== '' ? $emParen : ($atualEm['parentesco'] ?? null),
            $emTel   !== '' ? $emTel   : ($atualEm['telefone'] ?? null),
        ]);
    }

    $pdo->commit();

    echo json_encode(['success'=>true]); exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'SERVER_ERROR']);
}
