<?php
session_start();
require_once "../../api/includes/db.php";

// Garantir que só inter2 acedem
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter") {
    echo "<p>Acesso negado.</p>";
    exit;
}

$user_id = $_SESSION["user"]["id"];

try {
    $conn = db_connect();
    $stmt = $conn->prepare("
        SELECT 
            p.*, 
            u2.name AS decidido_por_nome,
            u3.name AS responsavel_nome
        FROM pedidos_ferias p
        LEFT JOIN user u2 ON p.decidido_por = u2.id
        LEFT JOIN user u3 ON p.responsavel_id = u3.id
        WHERE p.user_id = ?
        ORDER BY p.criado_em DESC
    ");
    $stmt->execute([$user_id]);
    $pedidos = $stmt->fetchAll();

    // Estatísticas
    $stats = [
        'total' => count($pedidos),
        'pendentes' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'pendente')),
        'aprovados' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'aprovado')),
        'rejeitados' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'rejeitado'))
    ];

} catch (Exception $e) {
    $pedidos = [];
    $stats = ['total' => 0, 'pendentes' => 0, 'aprovados' => 0, 'rejeitados' => 0];
    echo "<p>Erro ao carregar pedidos: " . $e->getMessage() . "</p>";
}
?>

<div class="ferias-page">
    <!-- Modern Header -->
    <div class="ferias-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="header-text">
                <h2>Férias e Ausências</h2>
                <p>Gerencie os seus pedidos de férias, licenças e ausências pessoais.</p>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-novo-pedido" onclick="abrirModalPedido()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                Novo Pedido
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card total">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Pedidos</div>
            </div>
        </div>

        <div class="stat-card pendente">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12,6 12,12 16,14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $stats['pendentes'] ?></div>
                <div class="stat-label">Pendentes</div>
            </div>
        </div>

        <div class="stat-card aprovado">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20,6 9,17 4,12"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $stats['aprovados'] ?></div>
                <div class="stat-label">Aprovados</div>
            </div>
        </div>

        <div class="stat-card rejeitado">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $stats['rejeitados'] ?></div>
                <div class="stat-label">Rejeitados</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ferias-content">
        <?php if (count($pedidos) > 0): ?>
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                        </svg>
                        Todos (<?= $stats['total'] ?>)
                    </button>
                    <button class="filter-btn" data-filter="pendente">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12,6 12,12 16,14"></polyline>
                        </svg>
                        Pendentes (<?= $stats['pendentes'] ?>)
                    </button>
                    <button class="filter-btn" data-filter="aprovado">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20,6 9,17 4,12"></polyline>
                        </svg>
                        Aprovados (<?= $stats['aprovados'] ?>)
                    </button>
                    <button class="filter-btn" data-filter="rejeitado">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        Rejeitados (<?= $stats['rejeitados'] ?>)
                    </button>
                </div>
            </div>

            <!-- Pedidos Grid -->
            <div class="pedidos-grid">
                <?php foreach ($pedidos as $p): ?>
                    <div class="pedido-card" data-estado="<?= $p['estado'] ?>">
                        <div class="card-header">
                            <div class="pedido-info">
                                <div class="tipo-icon">
                                    <?php
                                    $icon = match($p['tipo']) {
                                        'ferias' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>',
                                        'baixa_medica' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>',
                                        'licenca_maternidade', 'licenca_paternidade' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>',
                                        'consulta_medica' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.75 12l1.5 1.5L14.25 10"></path><circle cx="12" cy="12" r="9"></circle></svg>',
                                        default => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>'
                                    };
                                    echo $icon;
                                    ?>
                                </div>
                                <div class="pedido-details">
                                    <h3 class="tipo-titulo"><?= ucfirst(str_replace('_', ' ', $p["tipo"])) ?></h3>
                                    <div class="data-pedido">Pedido em <?= date("d/m/Y", strtotime($p["criado_em"])) ?></div>
                                </div>
                            </div>
                            <div class="status-badge <?= $p['estado'] ?>">
                                <?php if ($p['estado'] === 'pendente'): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12,6 12,12 16,14"></polyline>
                                    </svg>
                                <?php elseif ($p['estado'] === 'aprovado'): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20,6 9,17 4,12"></polyline>
                                    </svg>
                                <?php else: ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                <?php endif; ?>
                                <?= ucfirst($p["estado"]) ?>
                            </div>
                        </div>

                        <div class="periodo-container">
                            <div class="periodo-item">
                                <div class="periodo-label">Início</div>
                                <div class="periodo-data"><?= date("d/m/Y", strtotime($p["data_inicio"])) ?></div>
                            </div>
                            <div class="periodo-separator">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9,18 15,12 9,6"></polyline>
                                </svg>
                            </div>
                            <div class="periodo-item">
                                <div class="periodo-label">Fim</div>
                                <div class="periodo-data"><?= date("d/m/Y", strtotime($p["data_fim"])) ?></div>
                            </div>
                        </div>

                        <div class="justificacao-container">
                            <div class="justificacao-header">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14,2 14,8 20,8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                Justificação
                            </div>
                            <div class="justificacao-text"><?= nl2br(htmlspecialchars($p["justificacao"])) ?></div>
                        </div>

                        <?php if (!empty($p["responsavel_nome"])): ?>
                            <div class="justificacao-container">
                                <div class="justificacao-header">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M16 12l-4-4-4 4"></path>
                                        <path d="M12 16V8"></path>
                                    </svg>
                                    Responsável (Substituto)
                                </div>
                                <div class="justificacao-text">
                                    <?= htmlspecialchars($p["responsavel_nome"]) ?> ficará encarregado durante a ausência.
                                </div>
                            </div>
                        <?php endif; ?>


                        <div class="card-footer">
                            <div class="footer-info">
                                <?php if ($p["estado"] !== "pendente"): ?>
                                    <div class="decidido-info">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        Decidido por: <strong><?= htmlspecialchars($p["decidido_por_nome"] ?? "Desconhecido") ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="footer-actions">
                                <?php if ($p["ficheiro"]): ?>
                                    <a href="../../uploads/<?= $p["ficheiro"] ?>" target="_blank" class="btn-comprovativo">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                        </svg>
                                        Ver Comprovativo
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <h3>Nenhum pedido submetido</h3>
                <p>Ainda não fez nenhum pedido de férias ou ausências. Clique no botão acima para criar o seu primeiro pedido.</p>
                <button class="btn-empty-action" onclick="abrirModalPedido()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Criar Primeiro Pedido
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Novo Pedido -->
<div id="modalPedido" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Novo Pedido de Férias/Ausências</h3>
            <button class="modal-close" onclick="fecharModalPedido()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    <form action="../../api/leaves/request.php" method="POST" enctype="multipart/form-data" class="modal-form">
            <div class="form-section">
                <div class="input-group">
                    <label for="tipo">Tipo de Pedido *</label>
                    <select name="tipo" id="tipo" required>
                        <option value="">-- Selecione o tipo --</option>
                        <option value="ferias">Férias</option>
                        <option value="licenca_paternidade">Licença de Paternidade</option>
                        <option value="licenca_maternidade">Licença de Maternidade</option>
                        <option value="baixa_medica">Baixa Médica</option>
                        <option value="baixa_seguro">Baixa Seguro</option>
                        <option value="casamento">Casamento</option>
                        <option value="consulta_medica">Consulta Médica</option>
                        <option value="pessoal">Assunto Pessoal</option>
                    </select>
                </div>

                <div class="date-inputs">
                    <div class="input-group">
                        <label for="data_inicio">Data de Início *</label>
                        <input type="date" name="data_inicio" id="data_inicio" required>
                    </div>
                    <div class="input-group">
                        <label for="data_fim">Data de Fim *</label>
                        <input type="date" name="data_fim" id="data_fim" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="justificacao">Justificação *</label>
                    <textarea name="justificacao" id="justificacao" rows="4" placeholder="Descreva o motivo do seu pedido..." required></textarea>
                </div>

                <div class="input-group">
                    <label for="responsavel_id">Responsável (Substituto)</label>
                    <select name="responsavel_id" id="responsavel_id">
                        <option value="">-- Nenhum --</option>
                        <?php
                        // Buscar todos os operadores menos o próprio
                        $stmt = $conn->prepare("SELECT id, name FROM user WHERE role = 'inter' AND id != ?");
                        $stmt->execute([$user_id]);
                        while ($row = $stmt->fetch()):
                            ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>


                <div class="input-group">
                    <label for="ficheiro">
                        Comprovativo <span id="comprovativo-status" class="optional">(opcional)</span>
                    </label>
                    <div class="file-input-wrapper">
                        <input type="file" name="ficheiro" id="ficheiro" accept=".pdf,.jpg,.png,.jpeg">
                        <div class="file-input-content">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                            </svg>
                            <span>Clique para selecionar ficheiro</span>
                        </div>
                    </div>
                    <div class="file-info">PDF, JPG ou PNG (máx. 5MB)</div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="fecharModalPedido()">Cancelar</button>
                <button type="submit" class="btn-submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20,6 9,17 4,12"></polyline>
                    </svg>
                    Submeter Pedido
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Ferias Page Styles */
    .ferias-page {
        padding: 0;
        background: #f8fafc;
        min-height: 100vh;
    }

    .ferias-header {
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

    .btn-novo-pedido {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
    }

    .btn-novo-pedido:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(62, 132, 242, 0.4);
    }

    /* Statistics */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 2rem 2.5rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .stat-card.total .stat-icon {
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    }

    .stat-card.pendente .stat-icon {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .stat-card.aprovado .stat-icon {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .stat-card.rejeitado .stat-icon {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .stat-number {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--navy-blue);
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--text-light);
        font-weight: 500;
    }

    /* Content */
    .ferias-content {
        padding: 0 2.5rem 2rem;
    }

    .filter-section {
        background: white;
        padding: 1.5rem 2rem;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .filter-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        color: var(--text-dark);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .filter-btn:hover {
        border-color: var(--light-blue);
        background: #eff6ff;
    }

    .filter-btn.active {
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        border-color: var(--light-blue);
        color: white;
        box-shadow: 0 2px 8px rgba(62, 132, 242, 0.3);
    }

    /* Pedidos Grid */
    .pedidos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1.5rem;
    }

    .pedido-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .pedido-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    background: linear-gradient(90deg, var(--gradient-1), var(--gradient-2));
    }

    .pedido-card:hover {
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

    .pedido-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .tipo-icon {
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

    .tipo-titulo {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--navy-blue);
        margin-bottom: 0.25rem;
    }

    .data-pedido {
        font-size: 0.875rem;
        color: var(--text-light);
    }

    .status-badge {
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

    .status-badge.pendente {
        background: #fef3c7;
        color: #d97706;
    }

    .status-badge.aprovado {
        background: #d1fae5;
        color: #059669;
    }

    .status-badge.rejeitado {
        background: #fee2e2;
        color: #dc2626;
    }

    .periodo-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .periodo-item {
        text-align: center;
    }

    .periodo-label {
        font-size: 0.75rem;
        color: var(--text-light);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .periodo-data {
        font-weight: 600;
        color: var(--navy-blue);
    }

    .periodo-separator {
        color: var(--text-light);
    }

    .justificacao-container {
        margin-bottom: 1.5rem;
    }

    .justificacao-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.75rem;
    }

    .justificacao-text {
        color: var(--text-dark);
        line-height: 1.5;
        background: #f8fafc;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .decidido-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-light);
    }

    .btn-comprovativo {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .btn-comprovativo:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
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
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
    overflow: hidden; /* prevent lateral scroll */
    overflow-x: hidden;
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

    .modal-form {
        padding: 2rem;
        overflow-y: auto;
        overflow-x: hidden; /* remove horizontal scroll */
        max-height: calc(90vh - 140px);
        box-sizing: border-box;
    }

    .input-group {
        margin-bottom: 1.5rem;
    }

    .input-group label {
        display: block;
        font-weight: 600;
        color: var(--navy-blue);
        margin-bottom: 0.5rem;
    }

    .optional {
        font-weight: 400;
        color: var(--text-light);
    }

    .input-group input,
    .input-group select,
    .input-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .input-group input:focus,
    .input-group select:focus,
    .input-group textarea:focus {
        outline: none;
        border-color: var(--light-blue);
        box-shadow: 0 0 0 3px rgba(62, 132, 242, 0.1);
    }

    .date-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .file-input-wrapper {
        position: relative;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .file-input-wrapper:hover {
        border-color: var(--light-blue);
        background: #eff6ff;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .file-input-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-light);
    }

    .file-info {
        font-size: 0.75rem;
        color: var(--text-light);
        margin-top: 0.5rem;
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn-cancel {
        padding: 0.75rem 1.5rem;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        color: var(--text-dark);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
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
        margin-bottom: 2rem;
    }

    .btn-empty-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
    }

    .btn-empty-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(62, 132, 242, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .ferias-header,
        .ferias-content {
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .stats-container {
            padding: 1.5rem 2rem;
        }

        .pedidos-grid {
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .ferias-header {
            flex-direction: column;
            gap: 1rem;
            padding: 1.5rem;
            text-align: center;
        }

        .ferias-content {
            padding: 1rem 1.5rem;
        }

        .stats-container {
            grid-template-columns: 1fr;
            padding: 1.5rem;
            gap: 1rem;
        }

        .pedidos-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .date-inputs {
            grid-template-columns: 1fr;
        }

        .periodo-container {
            flex-direction: column;
            gap: 1rem;
        }

        .periodo-separator {
            transform: rotate(90deg);
        }

        .card-footer {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .modal-container {
            margin: 1rem;
            max-height: calc(100vh - 2rem);
        }

        .modal-form {
            padding: 1.5rem;
            overflow-x: hidden; /* ensure no horizontal scroll on mobile */
        }

        .modal-actions {
            flex-direction: column-reverse;
        }

        .filter-buttons {
            justify-content: center;
        }
    }

    /* Hidden class for filtering */
    .hidden {
        display: none !important;
    }
</style>

<script src="../../js/legacy/ferias_ausencias.js"></script>
