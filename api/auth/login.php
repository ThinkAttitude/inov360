<?php
session_start();

require_once __DIR__ . '/../includes/api_error.php';
$requestId = api_request_id();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    api_json_error(405, 'METHOD NOT ALLOWED', 'Método não permitido.');
}

require_once "../includes/db.php";

$input = json_decode(file_get_contents('php://input'), true);
$email = $input["email"] ?? '';
$password = $input["password"] ?? '';

if ($email === '' || $password === '') {
    api_json_error(401, 'UNAUTHORIZED', 'Email e palavra-passe são obrigatórios.');
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) Buscar utilizador (sem role)
    $stmt = $pdo->prepare("SELECT id, name, email, password FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password"])) {
        api_json_error(401, 'UNAUTHORIZED', 'Email ou palavra-passe incorretos.');
    }

    // 2) Permissões (array de IDs)
    $stmtPerm = $pdo->prepare("
        SELECT p.id
        FROM user_permission up
        JOIN permission p ON p.id = up.permission_id
        WHERE up.user_id = ?
    ");
    $stmtPerm->execute([$user["id"]]);
    $permissions = array_map('intval', $stmtPerm->fetchAll(PDO::FETCH_COLUMN));

    // 3) Responsáveis (array de IDs)
    $stmtResp = $pdo->prepare("
        SELECT cr.responsavel_id
        FROM colaborador_responsaveis cr
        WHERE cr.colaborador_id = ?
          AND cr.ativo = 1
          AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
          AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
    ");
    $stmtResp->execute([$user["id"]]);
    $responsaveis = array_map('intval', $stmtResp->fetchAll(PDO::FETCH_COLUMN));

    // 3b) Subordinados (array de IDs)  // >> ADICIONADO <<
    $stmtSub = $pdo->prepare("
        SELECT cr.colaborador_id
        FROM colaborador_responsaveis cr
        WHERE cr.responsavel_id = ?
          AND cr.ativo = 1
          AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
          AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
    ");
    $stmtSub->execute([$user["id"]]);
    $subordinados = array_map('intval', $stmtSub->fetchAll(PDO::FETCH_COLUMN));
    // << FIM ADIÇÃO >>

    // 4) Guardar sessão (sem role, sem redirect)
    $_SESSION["is_login"] = true;
    $_SESSION["user"] = [
        "id"           => (int)$user["id"],
        "name"         => $user["name"],
        "email"        => $user["email"],
        "permissions"  => $permissions,
        "responsaveis" => $responsaveis,
        "subordinados" => $subordinados,
    ];

    echo json_encode([
        'user' => [
            'id'           => (int)$user['id'],
            'name'         => $user['name'],
            'email'        => $user['email'],
            'permissions'  => $permissions,
            'responsaveis' => $responsaveis,
            'subordinados' => $subordinados,
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;

} catch (Throwable $e) {
    api_log_exception($e, $requestId, [
        'endpoint' => '.../login.php',
    ]);

    api_json_error(500, 'INTERNAL_ERROR', 'Ocorreu um erro inesperado.', $requestId);
}
