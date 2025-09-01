<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

if (!isset($_GET["user_id"])) {
    echo "<p>ID do colaborador não fornecido.</p>";
    exit;
}

$user_id = intval($_GET["user_id"]);

try {
    $conn = db_connect();

    $stmt = $conn->prepare("SELECT nome, email, telefone FROM colaborador_dados WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $contactos_emergencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$dados) {
        echo "<p>Ficha de colaborador não encontrada.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>

<h3>Ficha do Colaborador</h3>
<table class="table">
    <tr><th>Nome</th><td><?= htmlspecialchars($dados["nome"] ?? "-") ?></td></tr>
    <tr><th>Email</th><td><?= htmlspecialchars($dados["email"] ?? "-") ?></td></tr>
    <tr><th>Telefone</th><td><?= htmlspecialchars($dados["telefone"] ?? "-") ?></td></tr>
</table>

<h4>Contactos de Emergência</h4>
<table class="table">
    <?php if (empty($contactos_emergencia)): ?>
        <tr><td colspan="2"><em>Sem contactos registados.</em></td></tr>
    <?php else: ?>
        <?php foreach ($contactos_emergencia as $c): ?>
            <tr><th>Nome</th><td><?= htmlspecialchars($c['nome'] ?? '-') ?></td></tr>
            <tr><th>Parentesco</th><td><?= htmlspecialchars($c['parentesco'] ?? '-') ?></td></tr>
            <tr><th>Telefone</th><td><?= htmlspecialchars($c['telefone'] ?? '-') ?></td></tr>
            <tr><td colspan="2"><hr></td></tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
