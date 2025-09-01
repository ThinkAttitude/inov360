<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
    echo "<p>Acesso negado.</p>";
    exit;
}

try {
    $conn = db_connect();

    // Buscar todos os utilizadores com role 'opera'
    $stmt = $conn->prepare("
        SELECT u.id AS user_id, u.name, u.email, 
               d.profissao, d.categoria, d.telefone, d.data_admissao
        FROM user u
        LEFT JOIN colaborador_dados d ON u.id = d.user_id
        WHERE u.role = 'opera'
        ORDER BY u.name
    ");
    $stmt->execute();
    $operadores = $stmt->fetchAll();

    // Contar pedidos pendentes por operador
    $stmt_pedidos = $conn->prepare("
        SELECT user_id, COUNT(*) as pendentes
        FROM pedidos_ferias 
        WHERE estado = 'pendente' AND user_id IN (
            SELECT id FROM user WHERE role = 'opera'
        )
        GROUP BY user_id
    ");
    $stmt_pedidos->execute();
    $pedidos_pendentes = [];
    foreach ($stmt_pedidos->fetchAll() as $p) {
        $pedidos_pendentes[$p['user_id']] = $p['pendentes'];
    }

} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>

<div class="operadores-page">
    <!-- Modern Header -->
    <div class="operadores-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="header-text">
                <h2>Lista de Operadores</h2>
                <p>Visualize e gerencie informações dos operadores da sua equipa.</p>
            </div>
        </div>
        <div class="header-stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($operadores) ?></div>
                <div class="stat-label">Total Operadores</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= array_sum($pedidos_pendentes) ?></div>
                <div class="stat-label">Pedidos Pendentes</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="operadores-content">
        <?php if (count($operadores) > 0): ?>
            <!-- Search and Filter Bar -->
            <div class="filter-section">
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                        <input type="text" id="search-input" placeholder="Pesquisar por nome ou email...">
                    </div>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" data-view="cards">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Cards
                    </button>
                    <button class="view-btn" data-view="table">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h18v18H3zM21 9H3M9 21V9"></path>
                        </svg>
                        Tabela
                    </button>
                </div>
            </div>

            <!-- Cards View -->
            <div id="cards-view" class="operadores-grid">
                <?php foreach ($operadores as $op): ?>
                    <div class="operador-card" data-name="<?= strtolower(htmlspecialchars($op["name"])) ?>" data-email="<?= strtolower(htmlspecialchars($op["email"])) ?>">
                        <div class="card-header">
                            <div class="operador-avatar">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="operador-info">
                                <h3 class="operador-name"><?= htmlspecialchars($op["name"]) ?></h3>
                                <div class="operador-role">Operador</div>
                            </div>
                            <?php if (isset($pedidos_pendentes[$op["user_id"]])): ?>
                                <div class="status-badge pendente">
                                    <?= $pedidos_pendentes[$op["user_id"]] ?> pendente<?= $pedidos_pendentes[$op["user_id"]] > 1 ? 's' : '' ?>
                                </div>
                            <?php else: ?>
                                <div class="status-badge ok">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20,6 9,17 4,12"></polyline>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="operador-details">
                            <div class="detail-row">
                                <div class="detail-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value"><?= htmlspecialchars($op["email"]) ?></span>
                                </div>
                            </div>

                            <?php if ($op["telefone"]): ?>
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Telefone</span>
                                        <span class="detail-value"><?= htmlspecialchars($op["telefone"]) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($op["profissao"]): ?>
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                            <line x1="8" y1="21" x2="16" y2="21"></line>
                                            <line x1="12" y1="17" x2="12" y2="21"></line>
                                        </svg>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Profissão</span>
                                        <span class="detail-value"><?= htmlspecialchars($op["profissao"]) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($op["data_admissao"]): ?>
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Admitido em</span>
                                        <span class="detail-value"><?= date("d/m/Y", strtotime($op["data_admissao"])) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-actions">
                            <button class="btn-action primary" onclick="verFichaCompleta(<?= $op['user_id'] ?>)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Ver Ficha Completa
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Table View -->
            <div id="table-view" class="operadores-table" style="display: none;">
                <div class="table-container">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Operador</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Profissão</th>
                                <th>Categoria</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($operadores as $op): ?>
                                <tr class="table-row" data-name="<?= strtolower(htmlspecialchars($op["name"])) ?>" data-email="<?= strtolower(htmlspecialchars($op["email"])) ?>">
                                    <td>
                                        <div class="table-user">
                                            <div class="table-avatar">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                            </div>
                                            <div class="table-user-info">
                                                <div class="table-user-name"><?= htmlspecialchars($op["name"]) ?></div>
                                                <div class="table-user-role">Operador</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($op["email"]) ?></td>
                                    <td><?= htmlspecialchars($op["telefone"] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($op["profissao"] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($op["categoria"] ?? '—') ?></td>
                                    <td>
                                        <?php if (isset($pedidos_pendentes[$op["user_id"]])): ?>
                                            <span class="table-status pendente">
                                                <?= $pedidos_pendentes[$op["user_id"]] ?> pendente<?= $pedidos_pendentes[$op["user_id"]] > 1 ? 's' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="table-status ok">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20,6 9,17 4,12"></polyline>
                                                </svg>
                                                OK
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-table" onclick="verFichaCompleta(<?= $op['user_id'] ?>)">
                                            Ver Ficha
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3>Nenhum operador encontrado</h3>
                <p>Não existem operadores registados na sua equipa.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Ficha Completa -->
<div id="fichaModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Ficha Completa do Colaborador</h3>
            <button class="modal-close" onclick="fecharModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-content" id="fichaConteudo">
            <!-- Conteúdo será carregado dinamicamente -->
        </div>
    </div>
</div>

<script>
// Funcionalidades standalone para lista de operadores
document.addEventListener('DOMContentLoaded', function() {

    // Função para pesquisa
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            // Filter cards
            const cards = document.querySelectorAll('.operador-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const email = card.getAttribute('data-email');

                if (name && email) {
                    if (name.includes(searchTerm) || email.includes(searchTerm)) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                }
            });

            // Filter table rows
            const rows = document.querySelectorAll('.table-row');
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');

                if (name && email) {
                    if (name.includes(searchTerm) || email.includes(searchTerm)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                }
            });
        });
    }

    // Função para toggle de views (cards/tabela)
    const viewBtns = document.querySelectorAll('.view-btn');
    const cardsView = document.getElementById('cards-view');
    const tableView = document.getElementById('table-view');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');

            // Update active button
            viewBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Toggle views
            if (view === 'cards') {
                if (cardsView) cardsView.style.display = 'grid';
                if (tableView) tableView.style.display = 'none';
            } else {
                if (cardsView) cardsView.style.display = 'none';
                if (tableView) tableView.style.display = 'block';
            }
        });
    });

    // Modal backdrop clicks
    const fichaModal = document.getElementById('fichaModal');
    if (fichaModal) {
        fichaModal.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });
    }
});

// Função global para ver ficha completa
function verFichaCompleta(userId) {
    const modal = document.getElementById('fichaModal');
    const conteudo = document.getElementById('fichaConteudo');

    if (!modal || !conteudo) {
        console.error('Modal elements not found');
        return;
    }

    // Show loading
    conteudo.innerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; height: 200px;">
            <div style="width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: var(--light-blue); border-radius: 50%; animation: spin 1s linear infinite;"></div>
        </div>
    `;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Load content - ajuste o caminho conforme necessário
    fetch(`../inter2/visualizar_lista_operadores.php?user_id=${userId}`)
        .then(response => {
            if (!response.ok) throw new Error('Erro na resposta do servidor');
            return response.text();
        })
        .then(html => {
            conteudo.innerHTML = html;
        })
        .catch(error => {
            console.error('Erro ao carregar ficha:', error);
            conteudo.innerHTML = '<p>Erro ao carregar a ficha do colaborador.</p>';
        });
}

// Função global para fechar modal
function fecharModal() {
    const modal = document.getElementById('fichaModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
</script>

<style>
/* Operadores Page Styles */
.operadores-page {
    padding: 0;
    background: #f8fafc;
    min-height: 100vh;
}

.operadores-header {
    background: white;
    padding: 2rem 2.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.page-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 8px 24px rgba(62, 132, 242, 0.25);
}

.header-text h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
    letter-spacing: -0.5px;
}

.header-text p {
    color: var(--text-light);
    font-size: 1rem;
    margin: 0;
}

.header-stats {
    display: flex;
    gap: 1rem;
}

.stat-card {
    background: linear-gradient(135deg, #eff6ff, #f0f9ff);
    padding: 1.5rem 2rem;
    border-radius: 16px;
    border: 1px solid #dbeafe;
    text-align: center;
    min-width: 120px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--light-blue);
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #1e40af;
    font-weight: 500;
}

.operadores-content {
    padding: 2rem 2.5rem;
}

/* Filter Section */
.filter-section {
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.search-container {
    flex: 1;
    max-width: 400px;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input-wrapper svg {
    position: absolute;
    left: 1rem;
    color: var(--text-light);
}

.search-input-wrapper input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.search-input-wrapper input:focus {
    outline: none;
    border-color: var(--light-blue);
    box-shadow: 0 0 0 3px rgba(62, 132, 242, 0.1);
}

.view-toggle {
    display: flex;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 4px;
}

.view-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: transparent;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    color: var(--text-light);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.view-btn.active {
    background: white;
    color: var(--light-blue);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Cards View */
.operadores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 1.5rem;
}

.operador-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.operador-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gradient-1), var(--gradient-2));
}

.operador-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    border-color: var(--light-blue);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.operador-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.operador-info {
    flex: 1;
}

.operador-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
}

.operador-role {
    font-size: 0.875rem;
    color: var(--text-light);
    font-weight: 500;
}

.status-badge {
    position: absolute;
    top: 0;
    right: 0;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.pendente {
    background: #fef3c7;
    color: #d97706;
}

.status-badge.ok {
    background: #d1fae5;
    color: #059669;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.operador-details {
    margin-bottom: 1.5rem;
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.detail-icon {
    color: var(--light-blue);
    flex-shrink: 0;
}

.detail-content {
    flex: 1;
    min-width: 0;
}

.detail-label {
    display: block;
    font-size: 0.75rem;
    color: var(--text-light);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.detail-value {
    font-weight: 500;
    color: var(--navy-blue);
    word-break: break-word;
}

.card-actions {
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    width: 100%;
    justify-content: center;
}

.btn-action.primary {
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color: white;
    box-shadow: 0 2px 8px rgba(62, 132, 242, 0.2);
}

.btn-action.primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
}

/* Table View */
.operadores-table {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.table-container {
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table th {
    background: #f8fafc;
    padding: 1rem 1.5rem;
    text-align: left;
    font-weight: 600;
    color: var(--navy-blue);
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.875rem;
}

.modern-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-row:hover {
    background: #f8fafc;
}

.table-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.table-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.table-user-name {
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 0.125rem;
}

.table-user-role {
    font-size: 0.75rem;
    color: var(--text-light);
}

.table-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-status.pendente {
    background: #fef3c7;
    color: #d97706;
}

.table-status.ok {
    background: #d1fae5;
    color: #059669;
}

.btn-table {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.75rem;
}

.btn-table:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(62, 132, 242, 0.3);
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 16px;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.modal-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: #f1f5f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--text-light);
}

.modal-close:hover {
    background: #e2e8f0;
    color: var(--navy-blue);
}

.modal-content {
    padding: 2rem;
    overflow-y: auto;
    max-height: calc(90vh - 80px);
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.empty-icon {
    color: var(--light-blue);
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-light);
    max-width: 400px;
    line-height: 1.5;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .operadores-header {
        padding: 1.5rem 2rem;
    }

    .operadores-content {
        padding: 1.5rem 2rem;
    }

    .operadores-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    }
}

@media (max-width: 768px) {
    .operadores-header {
        flex-direction: column;
        gap: 1rem;
        padding: 1.5rem;
        text-align: center;
    }

    .operadores-content {
        padding: 1rem 1.5rem;
    }

    .filter-section {
        flex-direction: column;
        gap: 1rem;
    }

    .search-container {
        max-width: none;
    }

    .operadores-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .modal-container {
        margin: 1rem;
        max-height: calc(100vh - 2rem);
    }

    .modal-content {
        padding: 1.5rem;
    }

    .modern-table th,
    .modern-table td {
        padding: 0.75rem;
        font-size: 0.875rem;
    }
}

/* Hidden class for filtering */
.hidden {
    display: none !important;
}

/* Animação para loading */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
