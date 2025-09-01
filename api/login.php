<?php
session_start();
header('Content-Type: application/json');

$erro = $_SESSION["login_error"] ?? "";
unset($_SESSION["login_error"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "includes/db.php";

    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    // Validação básica
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email e palavra-passe são obrigatórios.'
        ]);
        exit;
    }

    try {
        $conn = db_connect();

        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["is_login"] = true;
            $_SESSION["user"] = [
                "id" => $user["id"],
                "name" => $user["name"],
                "email" => $user["email"],
                "role" => $user["role"],
            ];

            // Determinar URL de redirecionamento baseado no role
            $redirectUrl = "";
            switch ($user["role"]) {
                case "*":
                    $redirectUrl = "../page/estrela/dashboard_estrela.php";
                    break;
                case "admin_rh":
                    $redirectUrl = "../page/admin_rh/dashboard_admin_rh.php";
                    break;
                case "admin":
                    $redirectUrl = "../page/admin/dashboard_admin.php";
                    break;
                case "inter":
                    $redirectUrl = "../page/inter/dashboard_inter.php";
                    break;
                case "inter2":
                    $redirectUrl = "../page/inter2/dashboard_inter2.php";
                    break;
                case "opera":
                    $redirectUrl = "../page/opera/dashboard_opera.php";
                    break;
                default:
                    echo json_encode([
                        'success' => false,
                        'message' => 'Tipo de utilizador inválido.'
                    ]);
                    exit;
            }

            // Retornar sucesso com URL de redirecionamento
            echo json_encode([
                'success' => true,
                'message' => 'Login realizado com sucesso.',
                'redirect' => $redirectUrl
            ]);
            exit;

        } else {
            // Credenciais inválidas
            echo json_encode([
                'success' => false,
                'message' => 'Email ou palavra-passe incorretos.'
            ]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro interno do servidor. Tente novamente mais tarde.'
        ]);
        exit;
    }
} else {
    // Método não permitido
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
    exit;
}
