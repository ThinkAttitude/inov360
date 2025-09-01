<?php
session_start();

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin") {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
    $novo_estado = filter_input(INPUT_POST, 'novo_estado', FILTER_UNSAFE_RAW);
    $novo_estado = is_string($novo_estado) ? trim($novo_estado) : null;

    if (!$pedido_id || !in_array($novo_estado, ["aprovado", "rejeitado"])) {
        echo "Dados inválidos.";
        exit;
    }

    try {
        $conn = db_connect();

        // Confirma que o pedido pertence a um utilizador do role 'inter'
        $stmt = $conn->prepare("
            SELECT u.role 
            FROM pedidos_ferias p
            JOIN user u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pedido_id]);
        $rolePedido = $stmt->fetchColumn();

        if ($rolePedido !== 'inter') {
            echo "Não tem permissão para aprovar este pedido.";
            exit;
        }

        // Atualiza o estado do pedido
        $stmt = $conn->prepare("
            UPDATE pedidos_ferias 
            SET estado = ?, decidido_por = ? 
            WHERE id = ?
        ");
        $stmt->execute([$novo_estado, $_SESSION["user"]["id"], $pedido_id]);

        // Se aprovado, sincroniza evento
        if ($novo_estado === "aprovado") {
            require_once "sincronizar_pedido_evento.php";
            sincronizarPedidoEvento($pedido_id);
        }

        header("Location: ../../page/admin/dashboard_admin.php");
        exit;

    } catch (Exception $e) {
        echo "Erro ao atualizar estado: " . $e->getMessage();
        exit;
    }
} else {
    echo "Método inválido.";
    exit;
}
