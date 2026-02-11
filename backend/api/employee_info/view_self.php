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
    echo json_encode(['success' => false, 'error' => 'UNAUTHENTICATED']);
    exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = (int) $_SESSION['user']['id'];

    // 2) Dados base do utilizador (inclui empresa)
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

    // 6) Normalizações / mapeamentos

    $userOut = [
        'id'      => (int) $user['id'],
        'name'    => $user['name'],
        'email'   => $user['email'],
        'company' => [
            'id'   => $user['company_id'] !== null ? (int) $user['company_id'] : null,
            'name' => $user['company_name'] ?? null,
            'slug' => $user['company_slug'] ?? null,
            'logo' => $user['company_logo'] ?? null,
        ],
    ];

    // Campos usados nas labels (PERSONAL/FAMILY/FISCAL/CONTRACT)
    $profileFields = [
        // pessoais
        'user_id',
        'nome',
        'email',
        'telefone',
        'morada',
        'codigo_postal',
        'freguesia',
        'concelho',
        'distrito',
        'naturalidade',
        'habilitacoes',

        // familiares / identificação
        'pai',
        'mae',
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

        // fiscais
        'descontos_fiscais',
        'reparticao_financas',
        'regiao',
        'estado_fiscal',
        'deficiencia',
        'conjugue_deficiente',
        'num_dependentes',
        'num_dependentes_deficientes',
        'pensionista',

        // contratuais
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
        'validacao_empresa',
    ];

    $profileOut = [];
    foreach ($profileFields as $field) {
        switch ($field) {
            case 'user_id':
                $profileOut['user_id'] = $profile['user_id'] ?? $userOut['id'];
                break;
            case 'nome':
                // preferir nome da ficha, mas cair para user.name
                $profileOut['nome'] = $profile['nome'] ?? $userOut['name'] ?? null;
                break;
            case 'email':
                // preferir email da ficha, mas cair para user.email
                $profileOut['email'] = $profile['email'] ?? $userOut['email'] ?? null;
                break;
            default:
                $profileOut[$field] = $profile[$field] ?? null;
                break;
        }
    }

    // Emergência com nomes coerentes com o frontend (emergencia_nome, etc.)
    $emergencyOut = null;
    if ($emergency) {
        $emergencyOut = [
            'id'                   => (int) $emergency['id'],
            'user_id'              => (int) $emergency['user_id'],
            'emergencia_nome'      => $emergency['nome'],
            'emergencia_parentesco'=> $emergency['parentesco'],
            'emergencia_telefone'  => $emergency['telefone'],
        ];
    }

    http_response_code(200);
    echo json_encode([
        'success'   => true,
        'user'      => $userOut,
        'profile'   => $profileOut,
        'emergency' => $emergencyOut,
        'finance'   => $finance,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'SERVER_ERROR']);
}