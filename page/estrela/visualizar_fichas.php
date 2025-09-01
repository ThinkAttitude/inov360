<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "*") {
    echo "<p>Acesso negado.</p>";
    exit;
}

try {
    $conn = db_connect();

    $stmt = $conn->prepare("
        SELECT u.id AS user_id, u.name, u.email, u.role, d.profissao, d.categoria
        FROM user u
        LEFT JOIN colaborador_dados d ON u.id = d.user_id
        WHERE u.role IN ('admin', 'admin_rh')
        ORDER BY u.role, u.name
    ");
    $stmt->execute();
    $lista = $stmt->fetchAll();
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>

<div class="colaboradores-page">
    <!-- Modern Header -->
    <div class="colaboradores-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="header-text">
                <h2>Lista de Administradores</h2>
                <p>Selecione um colaborador para visualizar e analisar os seus dados pessoais.</p>
            </div>
        </div>
        <div class="header-stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($lista) ?></div>
                <div class="stat-label">Colaboradores</div>
            </div>
            <div class="role-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Painel Estrela
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="colaboradores-content">
        <?php if (count($lista) > 0): ?>
            <div class="colaboradores-grid">
                <?php foreach ($lista as $col): ?>
                    <div class="colaborador-card">
                        <div class="card-header">
                            <div class="colaborador-info">
                                <div class="colaborador-avatar">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                                <div class="colaborador-details">
                                    <div class="colaborador-name"><?= htmlspecialchars($col["name"]) ?></div>
                                    <div class="colaborador-email"><?= htmlspecialchars($col["email"]) ?></div>
                                </div>
                            </div>
                            <div class="role-badge-card <?= $col['role'] ?>">
                                <?= $col["role"] === 'admin' ? 'Admin' : 'Admin RH' ?>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                            <line x1="8" y1="21" x2="16" y2="21"/>
                                            <line x1="12" y1="17" x2="12" y2="21"/>
                                        </svg>
                                    </div>
                                    <div class="info-details">
                                        <span class="info-label">Profissão</span>
                                        <span class="info-value"><?= htmlspecialchars($col["profissao"] ?? 'Não definida') ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <polyline points="14,2 14,8 20,8"/>
                                            <line x1="16" y1="13" x2="8" y2="13"/>
                                            <line x1="16" y1="17" x2="8" y2="17"/>
                                        </svg>
                                    </div>
                                    <div class="info-details">
                                        <span class="info-label">Categoria</span>
                                        <span class="info-value"><?= htmlspecialchars($col["categoria"] ?? 'Não definida') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button class="btn-analisar analisar-ficha-btn" data-user-id="<?= $col["user_id"] ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Analisar Ficha Completa
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Nenhum colaborador encontrado</h3>
                <p>Não foram encontrados administradores no sistema para visualizar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Colaboradores Page Styles */
.colaboradores-page {
    padding: 0;
    background: #f8fafc;
    min-height: 100vh;
}

.colaboradores-header {
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
    align-items: center;
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
    color: #065f46;
    margin: 0;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #047857;
    margin-top: 0.25rem;
    font-weight: 500;
}

.role-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    border-radius: 12px;
    font-weight: 500;
    font-size: 0.875rem;
}

.colaboradores-content {
    padding: 2rem 2.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.colaboradores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 1.5rem;
}

.colaborador-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.colaborador-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.card-header {
    padding: 1.5rem 1.5rem 0 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.colaborador-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.colaborador-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--navy-blue), #1a365d);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.colaborador-name {
    font-weight: 600;
    color: var(--navy-blue);
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.colaborador-email {
    color: var(--text-light);
    font-size: 0.875rem;
}

.role-badge-card {
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-badge-card.admin {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
}

.role-badge-card.admin_rh {
    background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
    color: #7c2d12;
}

.card-body {
    padding: 1rem 1.5rem;
}

.info-row {
    display: flex;
    gap: 1rem;
}

.info-item {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 12px;
}

.info-icon {
    width: 36px;
    height: 36px;
    background: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--light-blue);
    flex-shrink: 0;
}

.info-details {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.info-label {
    font-size: 0.75rem;
    color: var(--text-light);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.125rem;
}

.info-value {
    font-weight: 600;
    color: var(--navy-blue);
    font-size: 0.875rem;
    word-break: break-word;
}

.card-footer {
    padding: 1rem 1.5rem 1.5rem 1.5rem;
    border-top: 1px solid #f1f5f9;
}

.btn-analisar {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.925rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(62, 132, 242, 0.2);
}

.btn-analisar:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(62, 132, 242, 0.3);
    background: linear-gradient(135deg, #2563eb, var(--light-blue));
}

.btn-analisar:active {
    transform: translateY(0);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    max-width: 600px;
    margin: 0 auto;
}

.empty-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem auto;
    color: #16a34a;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-light);
    font-size: 1rem;
    line-height: 1.6;
    max-width: 400px;
    margin: 0 auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .colaboradores-header {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
        padding: 1.5rem;
    }

    .header-stats {
        flex-direction: column;
        width: 100%;
    }

    .colaboradores-content {
        padding: 1rem;
    }

    .colaboradores-grid {
        grid-template-columns: 1fr;
    }

    .info-row {
        flex-direction: column;
    }

    .colaborador-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }

    .card-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>
