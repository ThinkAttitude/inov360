<?php
// api/employee_info/view_self.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// 1) Auth
if (empty($_SESSION['is_login']) || empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false, 'error'=>'UNAUTHENTICATED']); exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = (int) $_SESSION['user']['id'];

    // 2) Dados base do utilizador (opcional: inclui empresa)
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
        echo json_encode(['success'=>false, 'error'=>'USER_NOT_FOUND']); exit;
    }

    // 3) Ficha de colaborador (perfil pessoal/contratual)
    $stmtProfile = $pdo->prepare("
        SELECT *
        FROM colaborador_dados
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmtProfile->execute([$userId]);
    $profile = $stmtProfile->fetch(PDO::FETCH_ASSOC) ?: null;

    // 4) Contacto de emergência
    $stmtEmerg = $pdo->prepare("
        SELECT id, user_id, nome, parentesco, telefone
        FROM contactos_emergencia
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmtEmerg->execute([$userId]);
    $emergency = $stmtEmerg->fetch(PDO::FETCH_ASSOC) ?: null;

    // 5) Perfil financeiro
    $stmtFin = $pdo->prepare("
        SELECT *
        FROM finance_profiles
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmtFin->execute([$userId]);
    $finance = $stmtFin->fetch(PDO::FETCH_ASSOC) ?: null;

    // (Opcional) normalizações leves
    $userOut = [
        'id'           => (int)$user['id'],
        'name'         => $user['name'],
        'email'        => $user['email'],
        'company'      => [
            'id'   => $user['company_id'] !== null ? (int)$user['company_id'] : null,
            'name' => $user['company_name'] ?? null,
            'slug' => $user['company_slug'] ?? null,
            'logo' => $user['company_logo'] ?? null,
        ],
    ];

    http_response_code(200);
    echo json_encode([
        'success'  => true,
        'user'     => $userOut,
        'profile'  => $profile,   // todas as colunas de colaborador_dados
        'emergency'=> $emergency, // nome/parentesco/telefone
        'finance'  => $finance    // todas as colunas de finance_profiles
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false, 'error'=>'SERVER_ERROR']);
}
