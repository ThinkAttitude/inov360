<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

try {
    $conn = db_connect();

    $stmt = $conn->prepare("
        SELECT u.id AS user_id, u.name, u.email, u.role, d.profissao, d.categoria
        FROM user u
        LEFT JOIN colaborador_dados d ON u.id = d.user_id
        WHERE u.role IN ('opera', 'inter2', 'inter', 'admin')
        ORDER BY u.role, u.name
    ");
    $stmt->execute();
    $lista = $stmt->fetchAll();
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>

<link rel="stylesheet" href="../../css/fichas_colaboradores.css">

<div class="page-header">
    <h2>Fichas de Colaboradores</h2>
    <p>Consulte informações detalhadas de todos os colaboradores da organização.</p>
</div>

<?php if (count($lista) > 0): ?>
    <div id="gestao-content">
        <table class="table">
            <thead>
            <tr>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Nome
                </th>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    Email
                </th>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <shield width="16" height="16"/>
                    </svg>
                    Papel
                </th>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    Profissão
                </th>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                    </svg>
                    Categoria
                </th>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Ação
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($lista as $col): ?>
                <tr>
                    <td><?= htmlspecialchars($col["name"]) ?></td>
                    <td><?= htmlspecialchars($col["email"]) ?></td>
                    <td>
                        <span class="badge badge-<?= $col['role'] ?>">
                            <?= ucfirst($col["role"]) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($col["profissao"] ?? '—') ?></td>
                    <td><?= htmlspecialchars($col["categoria"] ?? '—') ?></td>
                    <td>
                        <button class="btn analisar-ficha-btn" data-user-id="<?= $col["user_id"] ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            Analisar Ficha de Colaborador
                        </button>
                        <button class="btn analisar-ficha-financeira-btn" data-user-id="<?= $col["user_id"] ?>" style="margin-left:8px; display:inline-flex; align-items:center; gap:6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                                <line x1="7" y1="15" x2="11" y2="15"></line>
                            </svg>
                            Analisar Ficha Financeira
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div id="gestao-content">
        <div class="empty-state">
            <h3>Nenhum Colaborador Encontrado</h3>
            <p>Não foram encontrados colaboradores no sistema.</p>
        </div>
    </div>
<?php endif; ?>
