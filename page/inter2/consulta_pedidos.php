<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
    echo "<p>Acesso negado.</p>";
    exit;
}

$user_id = $_SESSION['user']['id'];

try {
    $conn = db_connect();

    // Pedidos dos subordinados (opera)
    $stmt1 = $conn->prepare("
    SELECT p.*, u.name AS operador_nome, d.name AS decidido_por_nome, r.name AS responsavel_nome
    FROM pedidos_ferias p
    JOIN user u ON p.user_id = u.id
    LEFT JOIN user d ON p.decidido_por = d.id
    LEFT JOIN user r ON p.responsavel_id = r.id
    WHERE p.estado IN ('aprovado', 'rejeitado') AND u.role = 'opera'
    ORDER BY p.criado_em DESC
");
    $stmt1->execute();
    $pedidos_subordinados = $stmt1->fetchAll();

    // Meus pedidos pessoais
    $stmt2 = $conn->prepare("
    SELECT p.*, u.name AS operador_nome, d.name AS decidido_por_nome, r.name AS responsavel_nome
    FROM pedidos_ferias p
    JOIN user u ON p.user_id = u.id
    LEFT JOIN user d ON p.decidido_por = d.id
    LEFT JOIN user r ON p.responsavel_id = r.id
    WHERE p.estado IN ('aprovado', 'rejeitado') AND p.user_id = ?
    ORDER BY p.criado_em DESC
");
    $stmt2->execute([$user_id]);
    $meus_pedidos = $stmt2->fetchAll();

} catch (Exception $e) {
    echo "<p>Erro ao carregar pedidos: " . $e->getMessage() . "</p>";
    $pedidos_subordinados = $meus_pedidos = [];
}
?>

<div class="consulta-page">
    <!-- Modern Header -->
    <div class="consulta-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="header-text">
                <h2>Consulta de Pedidos</h2>
                <p>Visualize o histórico de pedidos processados da sua equipa e os seus próprios pedidos.</p>
            </div>
        </div>
        <div class="header-stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($pedidos_subordinados) ?></div>
                <div class="stat-label">Pedidos da Equipa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($meus_pedidos) ?></div>
                <div class="stat-label">Meus Pedidos</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="consulta-content">
        <!-- Team Requests Section -->
        <div class="requests-section">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Pedidos de Férias / Ausências (Operadores)</h3>
                </div>
                <div class="filter-toggle">
                    <button class="toggle-btn active" data-section="team">Equipa</button>
                    <button class="toggle-btn" data-section="personal">Pessoais</button>
                </div>
            </div>

            <?php if (count($pedidos_subordinados) > 0): ?>
                <div class="requests-grid" id="team-requests">
                    <?php foreach ($pedidos_subordinados as $p): ?>
                        <div class="request-card">
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
                                    <span class="status-badge <?= $p['estado'] ?>"><?= ucfirst($p["estado"]) ?></span>
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

                            <div class="request-footer">
                                <div class="request-info-row">
                                    <div class="info-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12,6 12,12 16,14"></polyline>
                                        </svg>
                                        Pedido em <?= date("d/m/Y", strtotime($p["criado_em"])) ?>
                                    </div>
                                    <?php if ($p["ficheiro"]): ?>
                                        <a href="../../uploads/<?= $p["ficheiro"] ?>" target="_blank" class="attachment-link">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                            </svg>
                                            Ver comprovativo
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="justificacao-block">
                                    <span class="label">Descrição:</span>
                                    <div class="justificacao-text"><?= $p["justificacao"] !== '' ? nl2br(htmlspecialchars($p["justificacao"])) : '<em>Sem descrição</em>' ?></div>
                                </div>
                                <?php if (!empty($p["responsavel_nome"])): ?>
                                    <div class="decided-by">
                                        Responsável/Substituto: <strong><?= htmlspecialchars($p["responsavel_nome"]) ?></strong>
                                    </div>
                                <?php endif; ?>

                                <div class="decided-by">
                                    Decidido por: <strong><?= htmlspecialchars($p["decidido_por_nome"] ?? "—") ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" id="team-requests">
                    <div class="empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Nenhum pedido da equipa</h3>
                    <p>Não existem pedidos processados dos operadores da sua equipa.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Personal Requests Section -->
        <div class="requests-section" style="display: none;" id="personal-section">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h3>Os Meus Pedidos Pessoais</h3>
                </div>
            </div>

            <?php if (count($meus_pedidos) > 0): ?>
                <div class="requests-grid" id="personal-requests">
                    <?php foreach ($meus_pedidos as $p): ?>
                        <div class="request-card personal">
                            <div class="card-header">
                                <div class="request-info">
                                    <div class="operador-avatar personal">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                    <div class="operador-details">
                                        <div class="operador-name">Meu Pedido</div>
                                        <div class="request-type"><?= ucfirst(str_replace('_', ' ', $p["tipo"])) ?></div>
                                    </div>
                                </div>
                                <div class="request-badge">
                                    <span class="status-badge <?= $p['estado'] ?>"><?= ucfirst($p["estado"]) ?></span>
                                </div>
                            </div>

                            <div class="request-dates">
                                <div class="date-item">
                                    <div class="date-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x="16" y="2" x2="16" y2="6"></line>
                                            <line x="8" y="2" x2="8" y2="6"></line>
                                            <line x="3" y="10" x2="21" y2="10"></line>
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
                                            <line x="16" y="2" x2="16" y2="6"></line>
                                            <line x="8" y="2" x2="8" y2="6"></line>
                                            <line x="3" y="10" x2="21" y2="10"></line>
                                        </svg>
                                    </div>
                                    <div class="date-details">
                                        <span class="date-label">Fim</span>
                                        <span class="date-value"><?= date("d/m/Y", strtotime($p["data_fim"])) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="request-footer">
                                <div class="request-info-row">
                                    <div class="info-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12,6 12,12 16,14"></polyline>
                                        </svg>
                                        Pedido em <?= date("d/m/Y", strtotime($p["criado_em"])) ?>
                                    </div>
                                    <?php if ($p["ficheiro"]): ?>
                                        <a href="../../uploads/<?= $p["ficheiro"] ?>" target="_blank" class="attachment-link">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                            </svg>
                                            Ver comprovativo
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="justificacao-block">
                                    <span class="label">Descrição:</span>
                                    <div class="justificacao-text"><?= $p["justificacao"] !== '' ? nl2br(htmlspecialchars($p["justificacao"])) : '<em>Sem descrição</em>' ?></div>
                                </div>
                                <div class="decided-by">
                                    Decidido por: <strong><?= htmlspecialchars($p["decidido_por_nome"] ?? "—") ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" id="personal-requests">
                    <div class="empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h3>Nenhum pedido pessoal</h3>
                    <p>Não existem pedidos seus concluídos.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Consulta Page Styles */
.consulta-page {
    padding: 0;
    background: #f8fafc;
    min-height: 100vh;
}

.consulta-header {
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

.consulta-content {
    padding: 2rem 2.5rem;
}

.requests-section {
    background: white;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.section-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.section-icon {
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

.section-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin: 0;
}

.filter-toggle {
    display: flex;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 4px;
}

.toggle-btn {
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

.toggle-btn.active {
    background: white;
    color: var(--light-blue);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
    background: linear-gradient(90deg, var(--gradient-1), var(--gradient-2));
}

.request-card.personal::before {
    background: linear-gradient(90deg, #8b5cf6, #7c3aed);
}

.request-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
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

.operador-avatar.personal {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
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

.status-badge.aprovado {
    background: #d1fae5;
    color: #059669;
}

.status-badge.rejeitado {
    background: #fee2e2;
    color: #dc2626;
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

.request-footer {
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
}

.request-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-light);
}

.attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--light-blue);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.875rem;
    transition: color 0.2s ease;
}

.attachment-link:hover {
    color: #3573d5;
}

.decided-by {
    font-size: 0.875rem;
    color: var(--text-light);
}

.decided-by strong {
    color: var(--navy-blue);
}

.justificacao-block {
    margin: 0.25rem 0 0.75rem;
}
.justificacao-block .label {
    display: block;
    font-size: 0.65rem;
    text-transform: uppercase;
    color: var(--text-light);
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}
.justificacao-text {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 0.5rem 0.6rem;
    border-radius: 6px;
    max-height: 90px;
    overflow-y: auto;
    font-size: 0.7rem;
    line-height: 1.2;
    color: #334155;
    white-space: pre-wrap;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
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
    .consulta-header {
        padding: 1.5rem 2rem;
    }

    .consulta-content {
        padding: 1.5rem 2rem;
    }

    .requests-grid {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
}

@media (max-width: 768px) {
    .consulta-header {
        flex-direction: column;
        gap: 1rem;
        padding: 1.5rem;
        text-align: center;
    }

    .consulta-content {
        padding: 1rem 1.5rem;
    }

    .section-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
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

    .request-info-row {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
}
</style>
