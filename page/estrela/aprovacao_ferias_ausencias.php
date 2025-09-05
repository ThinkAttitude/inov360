<?php
session_start();
require_once "../../api/includes/db.php";

// Nota: O papel "estrela" é representado por "*" em $_SESSION['user']['role']
if (!isset($_SESSION["is_login"]) || ($_SESSION["user"]["role"] ?? null) !== "*") {
    echo "<p>Acesso negado.</p>";
    exit;
}

try {
    $conn = db_connect();
    $stmt = $conn->prepare("
        SELECT 
            p.*, 
            u.name AS operador_nome, 
            s.name AS substituto_nome
        FROM pedidos_ferias p
        JOIN user u ON p.user_id = u.id
        LEFT JOIN user s ON p.responsavel_id = s.id
        WHERE p.estado = 'pendente' AND (u.role = 'admin' OR u.role = 'admin_rh')
        ORDER BY p.criado_em DESC
        ");

    $stmt->execute();

    $pedidos = $stmt->fetchAll();
} catch (Exception $e) {
    $pedidos = [];
    echo "<p>Erro ao carregar pedidos: " . $e->getMessage() . "</p>";
}
?>

<div class="approval-page">
    <!-- Modern Header -->
    <div class="approval-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20,6 9,17 4,12"></polyline>
                </svg>
            </div>
            <div class="header-text">
                <h2>Aprovação de Pedidos</h2>
                <p>Aprove ou rejeite pedidos de férias e ausências dos operadores sob sua supervisão.</p>
            </div>
        </div>
        <div class="header-stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($pedidos) ?></div>
                <div class="stat-label">Pedidos Pendentes</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="approval-content">
        <?php if (count($pedidos) > 0): ?>
            <div class="requests-container">
                <div class="requests-header">
                    <h3>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        Pedidos Aguardando Aprovação
                    </h3>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">Todos</button>
                        <button class="filter-btn" data-filter="ferias">Férias</button>
                        <button class="filter-btn" data-filter="baixa">Baixas</button>
                        <button class="filter-btn" data-filter="licenca">Licenças</button>
                    </div>
                </div>

                <div class="requests-grid">
                    <?php foreach ($pedidos as $p): ?>
                        <div class="request-card" data-type="<?= $p["tipo"] ?>">
                            <div class="card-header">
                                <div class="request-info">
                                    <div class="operador-avatar">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                    <div class="operador-details">
                                        <div class="operador-name"><?= htmlspecialchars($p["operador_nome"]) ?></div>
                                        <div class="request-type"><?= ucfirst(str_replace('_', ' ', $p["tipo"])) ?></div>
                                    </div>
                                </div>
                                <div class="request-badge">
                                    <span class="status-badge pendente">Pendente</span>
                                </div>
                            </div>

                            <div class="request-dates">
                                <div class="date-item">
                                    <div class="date-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                    </div>
                                    <div class="date-details">
                                        <span class="date-label">Início</span>
                                        <span class="date-value"><?= date("d/m/Y", strtotime($p["data_inicio"])) ?></span>
                                    </div>
                                </div>
                                <div class="date-separator">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9,18 15,12 9,6"></polyline>
                                    </svg>
                                </div>
                                <div class="date-item">
                                    <div class="date-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                    </div>
                                    <div class="date-details">
                                        <span class="date-label">Fim</span>
                                        <span class="date-value"><?= date("d/m/Y", strtotime($p["data_fim"])) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="request-details">
                                <div class="justification">
                                    <div class="justification-header">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14,2 14,8 20,8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                        </svg>
                                        Justificação
                                    </div>
                                    <div class="justification-text"><?= nl2br(htmlspecialchars($p["justificacao"])) ?></div>
                                </div>

                                <?php if (!empty($p["substituto_nome"])): ?>
                                    <div class="justification">
                                        <div class="justification-header">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M16 12l-4-4-4 4"></path>
                                                <path d="M12 16V8"></path>
                                            </svg>
                                            Substituição
                                        </div>
                                        <div class="justification-text">
                                            <strong><?= htmlspecialchars($p["substituto_nome"]) ?></strong> ficará responsável pelas tarefas durante esta ausência.
                                        </div>
                                    </div>
                                <?php endif; ?>


                                <?php if ($p["ficheiro"]): ?>
                                    <div class="attachment">
                                        <div class="attachment-header">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                            </svg>
                                            Comprovativo
                                        </div>
                                        <a href="../../uploads/<?= $p["ficheiro"] ?>" target="_blank" class="attachment-link">
                                            Ver documento
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                <polyline points="15,3 21,3 21,9"></polyline>
                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                            </svg>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="request-footer">
                                <div class="request-date">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12,6 12,12 16,14"></polyline>
                                    </svg>
                                    Pedido em <?= date("d/m/Y", strtotime($p["criado_em"])) ?>
                                </div>
                                <div class="action-buttons">
                                    <form action="#" method="POST" style="display:inline;">
                                        <input type="hidden" name="pedido_id" value="<?= $p["id"] ?>">
                                        <input type="hidden" name="novo_estado" value="rejeitado">
                                        <button type="button" class="btn-reject js-reject" data-pedido-id="<?= $p["id"] ?>" onclick="window.rejectLeave && window.rejectLeave(<?= (int)$p['id'] ?>)">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                            </svg>
                                            Rejeitar
                                        </button>
                                    </form>
                                    <form action="#" method="POST" style="display:inline;">
                                        <input type="hidden" name="pedido_id" value="<?= $p["id"] ?>">
                                        <input type="hidden" name="novo_estado" value="aprovado">
                                        <button type="button" class="btn-approve js-approve" data-pedido-id="<?= $p["id"] ?>" onclick="window.approveLeave && window.approveLeave(<?= (int)$p['id'] ?>)">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20,6 9,17 4,12"></polyline>
                                            </svg>
                                            Aprovar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20,6 9,17 4,12"></polyline>
                    </svg>
                </div>
                <h3>Nenhum pedido pendente</h3>
                <p>Todos os pedidos de férias e ausências foram processados ou não existem novos pedidos para aprovar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Approval Page Styles */
    .approval-page {
        padding: 0;
        background: #f8fafc;
        min-height: 100vh;
    }

    .approval-header {
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
        background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
        padding: 1.5rem 2rem;
        border-radius: 16px;
        border: 1px solid #d1fae5;
        text-align: center;
        min-width: 120px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #059669;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #047857;
        font-weight: 500;
    }

    .approval-content {
        padding: 2rem 2.5rem;
    }

    .requests-container {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .requests-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .requests-header h3 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--navy-blue);
        margin: 0;
    }

    .filter-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-weight: 500;
        color: var(--text-dark);
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .filter-btn:hover {
        border-color: var(--light-blue);
        color: var(--light-blue);
    }

    .filter-btn.active {
        background: var(--light-blue);
        border-color: var(--light-blue);
        color: white;
    }

    .requests-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
    }

    .request-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .request-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f59e0b, #d97706);
    }

    .request-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        border-color: var(--light-blue);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .request-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .operador-avatar {
        width: 40px;
        height: 40px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .operador-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--navy-blue);
        margin-bottom: 0.25rem;
    }

    .request-type {
        font-size: 0.875rem;
        color: var(--text-light);
        font-weight: 500;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
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

    .request-dates {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .date-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .date-icon {
        color: var(--light-blue);
    }

    .date-label {
        display: block;
        font-size: 0.75rem;
        color: var(--text-light);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .date-value {
        font-weight: 600;
        color: var(--navy-blue);
    }

    .date-separator {
        color: var(--text-light);
    }

    .request-details {
        margin-bottom: 1.5rem;
    }

    .justification {
        margin-bottom: 1rem;
    }

    .justification-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.75rem;
    }

    .justification-text {
        color: var(--text-dark);
        line-height: 1.5;
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .attachment {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .attachment-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .attachment-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--light-blue);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .attachment-link:hover {
        color: #3573d5;
    }

    .request-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .request-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-light);
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .btn-approve, .btn-reject {
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
    }

    .btn-approve {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
    }

    .btn-approve:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-reject {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }

    .btn-reject:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
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
        color: #10b981;
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
        .approval-header {
            padding: 1.5rem 2rem;
        }

        .approval-content {
            padding: 1.5rem 2rem;
        }

        .requests-grid {
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .approval-header {
            flex-direction: column;
            gap: 1rem;
            padding: 1.5rem;
            text-align: center;
        }

        .approval-content {
            padding: 1rem 1.5rem;
        }

        .requests-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .filter-buttons {
            flex-wrap: wrap;
            justify-content: center;
        }

        .requests-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            padding: 1.5rem;
        }

        .request-dates {
            flex-direction: column;
            gap: 1rem;
        }

        .date-separator {
            transform: rotate(90deg);
        }

        .request-footer {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .action-buttons {
            width: 100%;
            justify-content: center;
        }
    }
</style>
<script src="../../js/leaves_approval.js"></script>