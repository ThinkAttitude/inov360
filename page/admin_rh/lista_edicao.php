<?php
require_once "../../api/includes/db.php";
session_start();

// Apenas utilizadores com role 'admin_rh' podem aceder
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

try {
    $conn = db_connect();

    $stmt = $conn->query("
        SELECT ce.id, ce.criado_em, u.name AS nome_utilizador 
        FROM colaborador_edicoes ce
        JOIN user u ON ce.user_id = u.id
        WHERE ce.estado = 'pendente'
        ORDER BY ce.criado_em DESC
    ");
    $pedidos = $stmt->fetchAll();
} catch (Exception $e) {
    echo "<p>Erro ao buscar pedidos: " . $e->getMessage() . "</p>";
    exit;
}
?>

<link rel="stylesheet" href="../../css/legacy/fichas_colaboradores.css">

<div class="page-header">
    <h2>Pedidos Pendentes de Edição</h2>
    <p>Analise e aprove as alterações solicitadas pelos colaboradores nas suas fichas pessoais.</p>
</div>

<?php if (count($pedidos) > 0): ?>
    <div id="gestao-content">
        <table class="table">
            <thead>
            <tr>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Colaborador
                </th>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Data do Pedido
                </th>
                <th>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                    Estado
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
            <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.75rem;">
                                <?= strtoupper(substr($p["nome_utilizador"], 0, 2)) ?>
                            </div>
                            <span><?= htmlspecialchars($p["nome_utilizador"]) ?></span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 600;"><?= date("d/m/Y", strtotime($p["criado_em"])) ?></span>
                            <span style="font-size: 0.875rem; color: var(--text-light);"><?= date("H:i", strtotime($p["criado_em"])) ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.25rem;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            Pendente
                        </span>
                    </td>
                    <td>
                        <button class="btn analisar-ficha-btn analisar-btn" data-id="<?= $p["id"] ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            Analisar Pedido
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
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-light); margin-bottom: 1rem;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            <h3>Nenhum Pedido Pendente</h3>
            <p>Não existem pedidos de edição de ficha pendentes de aprovação no momento.</p>
        </div>
    </div>
<?php endif; ?>
