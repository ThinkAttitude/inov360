<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

require_once "includes/db.php";

$email = $_POST["email"] ?? '';
$password = $_POST["password"] ?? '';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Email e palavra-passe são obrigatórios.']);
    exit;
}

try {
    $conn = db_connect();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) Buscar utilizador (sem role)
    $stmt = $conn->prepare("SELECT id, name, email, password FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password"])) {
        echo json_encode(['success' => false, 'message' => 'Email ou palavra-passe incorretos.']);
        exit;
    }

    // 2) Permissões (array de IDs)
    $stmtPerm = $conn->prepare("
        SELECT p.id
        FROM inov360.user_permission up
        JOIN inov360.permission p ON p.id = up.permission_id
        WHERE up.user_id = ?
    ");
    $stmtPerm->execute([$user["id"]]);
    $permissions = array_map('intval', $stmtPerm->fetchAll(PDO::FETCH_COLUMN));

    // 3) Responsáveis (array de IDs)
    $stmtResp = $conn->prepare("
        SELECT cr.responsavel_id
        FROM inov360.colaborador_responsaveis cr
        WHERE cr.colaborador_id = ?
          AND cr.ativo = 1
          AND (cr.valido_desde IS NULL OR cr.valido_desde <= NOW())
          AND (cr.valido_ate   IS NULL OR cr.valido_ate   >= NOW())
    ");
    $stmtResp->execute([$user["id"]]);
    $responsaveis = array_map('intval', $stmtResp->fetchAll(PDO::FETCH_COLUMN));

    // 4) Guardar sessão (sem role, sem redirect)
    $_SESSION["is_login"] = true;
    $_SESSION["user"] = [
        "id"           => (int)$user["id"],
        "name"         => $user["name"],
        "email"        => $user["email"],
        "permissions"  => $permissions,
        "responsaveis" => $responsaveis,
    ];

    // 5) Resposta para o frontend decidir o fluxo
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso.',
        'user' => [
            'id'           => (int)$user['id'],
            'name'         => $user['name'],
            'email'        => $user['email'],
            'permissions'  => $permissions,
            'responsaveis' => $responsaveis,
        ]
    ]);
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    exit;
}
