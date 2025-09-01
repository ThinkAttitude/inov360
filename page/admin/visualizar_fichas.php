<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin") {
    echo "<p>Acesso negado.</p>";
    exit;
}

try {
    $conn = db_connect();

    $stmt = $conn->prepare("
        SELECT u.id AS user_id, u.name, u.email, d.profissao, d.categoria
        FROM user u
        LEFT JOIN colaborador_dados d ON u.id = d.user_id
        WHERE u.role = 'inter'
        ORDER BY u.name
    ");
    $stmt->execute();
    $lista = $stmt->fetchAll();
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>

<h2>Ficha dos Intermédios</h2>
<p>Selecione um colaborador para visualizar os seus dados.</p>

<?php if (count($lista) > 0): ?>
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
        <?php foreach ($lista as $col): ?>
            <tr>
                <td><?= htmlspecialchars($col["name"]) ?></td>
                <td><?= htmlspecialchars($col["email"]) ?></td>
                <td><?= htmlspecialchars($col["profissao"] ?? '—') ?></td>
                <td><?= htmlspecialchars($col["categoria"] ?? '—') ?></td>
                <td>
                    <button class="btn analisar-ficha-btn" data-user-id="<?= $col["user_id"] ?>">Analisar Ficha</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhum colaborador encontrado.</p>
<?php endif; ?>
