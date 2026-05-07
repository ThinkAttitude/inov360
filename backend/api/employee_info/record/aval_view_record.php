<?php
// api/employee_info/record/aval_view_record.php
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

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'BAD_REQUEST', 'hint' => 'Provide ?user_id=INT']);
    exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtUser = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.company_id,
               c.name AS company_name, c.slug AS company_slug, c.logo_path AS company_logo
        FROM `user` u
        LEFT JOIN `company` c ON c.id = u.company_id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmtUser->execute([$userId]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'USER_NOT_FOUND']);
        exit;
    }

    $stmtProfile = $pdo->prepare("
        SELECT *
        FROM colaborador_dados
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmtProfile->execute([$userId]);
    $profile = $stmtProfile->fetch(PDO::FETCH_ASSOC) ?: null;

    $stmtEmerg = $pdo->prepare("
        SELECT id, user_id, nome, parentesco, telefone, grupo_sanguineo
        FROM contactos_emergencia
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmtEmerg->execute([$userId]);
    $emergency = $stmtEmerg->fetch(PDO::FETCH_ASSOC) ?: null;

    $userOut = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'company' => [
            'id' => $user['company_id'] !== null ? (int)$user['company_id'] : null,
            'name' => $user['company_name'] ?? null,
            'slug' => $user['company_slug'] ?? null,
            'logo' => $user['company_logo'] ?? null,
        ],
    ];

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'user' => $userOut,
        'profile' => $profile,
        'emergency' => $emergency,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'SERVER_ERROR']);
}