<?php
session_start();
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter") {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user"]["id"];
    $tipo = $_POST["tipo"] ?? '';
    $data_inicio = $_POST["data_inicio"] ?? '';
    $data_fim = $_POST["data_fim"] ?? '';
    $justificacao = $_POST["justificacao"] ?? '';
    $ficheiro_nome = null;
    $responsavel_id = $_POST["responsavel_id"] ?? null;
    if ($responsavel_id === "") {
        $responsavel_id = null;
    }

    // Tipos que exigem comprovativo
    $tipos_com_comprovativo = [
        'licenca_paternidade',
        'licenca_maternidade',
        'baixa_medica',
        'baixa_seguro',
        'casamento',
        'consulta_medica'
    ];

    // Validações básicas
    if (!$tipo || !$data_inicio || !$data_fim || !$justificacao) {
        echo "Todos os campos obrigatórios devem ser preenchidos.";
        exit;
    }

    // Validação das datas
    $input_inicio = $_POST["data_inicio"] ?? '';
    $input_fim = $_POST["data_fim"] ?? '';
    $data_ini = DateTime::createFromFormat('Y-m-d', $input_inicio);
    $data_fim = DateTime::createFromFormat('Y-m-d', $input_fim);

    if (!$data_ini || !$data_fim) {
        echo "Formato de data inválido.";
        exit;
    }

    if ($data_ini > $data_fim) {
        echo "Data de início não pode ser após a data de fim.";
        exit;
    }

    $data_inicio = $data_ini->format('Y-m-d');
    $data_fim = $data_fim->format('Y-m-d');

    // Se tipo exige comprovativo mas não foi enviado
    if (in_array($tipo, $tipos_com_comprovativo)) {
        if (!isset($_FILES["ficheiro"]) || $_FILES["ficheiro"]["error"] !== UPLOAD_ERR_OK) {
            echo "Este tipo de pedido exige o envio de um comprovativo.";
            exit;
        }
    }


    // Upload de ficheiro (se existir)
    if (isset($_FILES["ficheiro"]) && $_FILES["ficheiro"]["error"] === UPLOAD_ERR_OK) {
        $ficheiro_tmp = $_FILES["ficheiro"]["tmp_name"];
        $extensao = strtolower(pathinfo($_FILES["ficheiro"]["name"], PATHINFO_EXTENSION));

// Validar tipo MIME real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $ficheiro_tmp);
        finfo_close($finfo);

// Tipos permitidos
        $tipos_permitidos = [
            'application/pdf',
            'image/jpeg',
            'image/png'
        ];

// Extensões permitidas
        $extensoes_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($mime, $tipos_permitidos) || !in_array($extensao, $extensoes_permitidas)) {
            echo "Tipo de ficheiro inválido. Apenas PDF, JPG ou PNG são permitidos.";
            exit;
        }

// Opcional: tamanho máximo 5MB
        if ($_FILES["ficheiro"]["size"] > 5 * 1024 * 1024) {
            echo "O ficheiro é demasiado grande. Máximo: 5MB.";
            exit;
        }

        $ficheiro_nome = "comprovativo_" . time() . "_" . rand(1000,9999) . "." . $extensao;
        $destino = "../../uploads/" . $ficheiro_nome;

        if (!is_dir("../../uploads")) {
            mkdir("../../uploads", 0777, true);
        }

        if (!move_uploaded_file($ficheiro_tmp, $destino)) {
            echo "Erro ao mover o ficheiro enviado.";
            exit;
        }

    }

    try {
        $conn = db_connect();
        $stmt = $conn->prepare("
            INSERT INTO pedidos_ferias (user_id, tipo, data_inicio, data_fim, justificacao, ficheiro, responsavel_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $tipo, $data_inicio, $data_fim, $justificacao, $ficheiro_nome, $responsavel_id]);

        header("Location: ../../page/inter/dashboard_inter.php");
        exit;
    } catch (Exception $e) {
        echo "Erro ao submeter o pedido: " . $e->getMessage();
        exit;
    }
} else {
    echo "Método inválido.";
    exit;
}
?>
