<?php
session_start();
require_once "../includes/db.php";

// Validação de Role
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Acesso negado."]);
    exit;
}

if (!isset($_POST["edicao_id"], $_POST["acao"])) {
    echo json_encode(["success" => false, "message" => "Parâmetros inválidos."]);
    exit;
}

$edicaoId = intval($_POST["edicao_id"]);

$acao = $_POST["acao"] ?? '';
if (!in_array($acao, ['aprovar', 'recusar'])) {
    echo json_encode(["success" => false, "message" => "Ação inválida."]);
    exit;
}

$avaliadorId = $_SESSION["user"]["id"] ?? null;

try {
    $conn = db_connect();

    // Buscar dados da edição
    $stmt = $conn->prepare("SELECT * FROM colaborador_edicoes WHERE id = ?");
    $stmt->execute([$edicaoId]);
    $edicao = $stmt->fetch();

    if (!$edicao) {
        echo json_encode(["success" => false, "message" => "Edição não encontrada."]);
        exit;
    }

    // Atualizar estado da edição principal
    $estado = $acao === "aprovar" ? "aprovado" : "recusado";
    $stmt = $conn->prepare("
        UPDATE colaborador_edicoes 
        SET estado = ?, avaliado_por = ?, avaliado_em = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$estado, $avaliadorId, $edicaoId]);

    // Se aprovado, atualizar dados em colaborador_dados
    if ($estado === "aprovado") {
        $stmt = $conn->prepare("
            UPDATE colaborador_dados SET 
                email = ?, 
                telefone = ?, 
                morada = ?, 
                nib = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $edicao["email"],
            $edicao["telefone"],
            $edicao["morada"],
            $edicao["nib"],
            $edicao["user_id"]
        ]);

        // Aplicar contacto de emergência se existir um pendente
        $stmt = $conn->prepare("
            SELECT * FROM contactos_emergencia_edicoes 
            WHERE user_id = ? AND estado = 'pendente'
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$edicao["user_id"]]);
        $emergencia = $stmt->fetch();

        if ($emergencia) {
            // Substituir contacto atual
            $conn->prepare("DELETE FROM contactos_emergencia WHERE user_id = ?")
                ->execute([$edicao["user_id"]]);

            $conn->prepare("
                INSERT INTO contactos_emergencia (user_id, nome, parentesco, telefone)
                VALUES (?, ?, ?, ?)
            ")->execute([
                $edicao["user_id"],
                $emergencia["nome"],
                $emergencia["parentesco"],
                $emergencia["telefone"]
            ]);

            // Marcar edição de contacto como aprovada
            $conn->prepare("
                UPDATE contactos_emergencia_edicoes 
                SET estado = 'aprovado', avaliado_por = ?, avaliado_em = NOW() 
                WHERE id = ?
            ")->execute([$avaliadorId, $emergencia["id"]]);
        }
    }

    echo json_encode(["success" => true, "message" => "Ficha " . $estado . " com sucesso."]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    exit;
}
