<?php
session_start();

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $colaborador_id = $_POST["colaborador_id"] ?? '';
    $tipo = $_POST["tipo"] ?? '';
    $data_inicio = $_POST["data_inicio"] ?? '';
    $data_fim = $_POST["data_fim"] ?? '';
    $justificacao = $_POST["justificacao"] ?? '';
    $ficheiro_nome = null;

    if (!$colaborador_id || !$tipo || !$data_inicio || !$data_fim || !$justificacao) {
        echo "Todos os campos obrigatórios devem ser preenchidos.";
        exit;
    }

    try {
        $conn = db_connect();

        // Verificar se o colaborador existe e tem role permitido
        $stmt = $conn->prepare("SELECT role FROM user WHERE id = ?");
        $stmt->execute([$colaborador_id]);
        $role = $stmt->fetchColumn();

        if (!in_array($role, ['opera', 'inter2', 'inter'])) {
            echo "Role do colaborador não autorizado para marcação direta.";
            exit;
        }

        // Validar e guardar o comprovativo (obrigatório)
        if (!isset($_FILES["ficheiro"]) || $_FILES["ficheiro"]["error"] !== UPLOAD_ERR_OK) {
            echo "É obrigatório anexar um comprovativo.";
            exit;
        }

        $ficheiro_tmp = $_FILES["ficheiro"]["tmp_name"];
        $extensao = strtolower(pathinfo($_FILES["ficheiro"]["name"], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $ficheiro_tmp);
        finfo_close($finfo);

        $tipos_permitidos = ['application/pdf', 'image/jpeg', 'image/png'];
        $extensoes_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($mime, $tipos_permitidos) || !in_array($extensao, $extensoes_permitidas)) {
            echo "Tipo de ficheiro inválido.";
            exit;
        }

        if ($_FILES["ficheiro"]["size"] > 5 * 1024 * 1024) {
            echo "Ficheiro demasiado grande. Máximo: 5MB.";
            exit;
        }

        $ficheiro_nome = "comprovativo_" . time() . "_" . rand(1000, 9999) . "." . $extensao;
        $destino = "../../uploads/" . $ficheiro_nome;

        if (!is_dir("../../uploads")) {
            mkdir("../../uploads", 0777, true);
        }

        if (!move_uploaded_file($ficheiro_tmp, $destino)) {
            echo "Erro ao guardar comprovativo.";
            exit;
        }

        // Inserção direta como aprovado
        $stmt = $conn->prepare("
            INSERT INTO pedidos_ferias (user_id, tipo, data_inicio, data_fim, justificacao, ficheiro, estado, decidido_por)
            VALUES (?, ?, ?, ?, ?, ?, 'aprovado', ?)
        ");
        $stmt->execute([
            $colaborador_id, $tipo, $data_inicio, $data_fim,
            $justificacao, $ficheiro_nome, $_SESSION["user"]["id"]
        ]);

        // Sincroniza com calendário
        require_once "sincronizar_pedido_evento.php";
        sincronizarPedidoEvento($conn->lastInsertId());

    // Redireciona para o dashboard com flag de sucesso para exibir toast
    header("Location: ../../page/admin_rh/dashboard_admin_rh.php?marcacao=sucesso");
        exit;

    } catch (Exception $e) {
        echo "Erro ao marcar ausência: " . $e->getMessage();
        exit;
    }
} else {
    echo "Método inválido.";
    exit;
}
