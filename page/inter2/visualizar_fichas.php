<?php
session_start();
require_once "../../api/includes/db.php";

// Apenas utilizadores intermédio podem aceder
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
    echo "<p>Acesso negado.</p>";
    exit;
}

try {
    $conn = db_connect();

    // Buscar todos os utilizadores com role 'opera'
    $stmt = $conn->prepare("
        SELECT u.id AS user_id, u.name, u.email, d.profissao, d.categoria
        FROM user u
        LEFT JOIN colaborador_dados d ON u.id = d.user_id
        WHERE u.role = 'opera'
        ORDER BY u.name
    ");
    $stmt->execute();
    $operadores = $stmt->fetchAll();
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>

<h2>Alterar Fichas dos Colaboradores</h2>
<p>Selecione um operador para visualizar ou editar a ficha de colaborador.</p>

<?php if (count($operadores) > 0): ?>
    <table class="table">
        <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Profissão</th>
            <th>Categoria</th>
            <th>Ação</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($operadores as $op): ?>
            <tr>
                <td><?= htmlspecialchars($op["name"]) ?></td>
                <td><?= htmlspecialchars($op["email"]) ?></td>
                <td><?= htmlspecialchars($op["profissao"] ?? '—') ?></td>
                <td><?= htmlspecialchars($op["categoria"] ?? '—') ?></td>
                <td>
                    <button class="btn analisar-ficha-btn" data-user-id="<?= $op["user_id"] ?>">Analisar Ficha</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhum operador encontrado.</p>
<?php endif; ?>
