<?php
session_start();
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}
?>

<div class="lista-colaboradores-page">
    <!-- Modern Header -->
    <div class="page-header">
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
                <h2>Lista de Colaboradores</h2>
                <p>Gerencie e visualize informações de todos os colaboradores da organização.</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="page-content">
        <div class="content-section">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                    </div>
                    <h3>Opções de Gestão</h3>
                </div>
            </div>

            <div class="action-grid">
                <div class="action-card" id="visualizar_fichas">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h4>Visualizar Colaboradores</h4>
                        <p>Consulte informações detalhadas de todos os colaboradores</p>
                    </div>
                    <div class="card-arrow">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div id="gestao-content" class="dynamic-content"></div>
    </div>
</div>

<style>
    /* Lista Colaboradores Page Styles */
    .lista-colaboradores-page {
        padding: 0;
        background: #f8fafc;
        min-height: 100vh;
    }

    .page-header {
        background: white;
        padding: 2rem 2.5rem;
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

    .page-content {
        padding: 2rem 2.5rem;
    }

    .content-section {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .section-header {
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

    .action-grid {
        padding: 2rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .action-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    background: linear-gradient(90deg, var(--gradient-1), var(--gradient-2));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: var(--light-blue);
    }

    .action-card:hover::before {
        transform: scaleX(1);
    }

    .card-icon {
        width: 48px;
        height: 48px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
    }

    .card-content {
        flex: 1;
    }

    .card-content h4 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--navy-blue);
        margin-bottom: 0.5rem;
    }

    .card-content p {
        color: var(--text-light);
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .card-arrow {
        color: var(--light-blue);
        transition: transform 0.2s ease;
    }

    .action-card:hover .card-arrow {
        transform: translateX(4px);
    }

    .dynamic-content {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }

    .dynamic-content.loaded {
        opacity: 1;
        transform: translateY(0);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }

        .page-content {
            padding: 1rem 1.5rem;
        }

        .action-grid {
            grid-template-columns: 1fr;
            padding: 1.5rem;
        }

        .header-left {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .action-card {
            padding: 1.25rem;
        }
    }
</style>
