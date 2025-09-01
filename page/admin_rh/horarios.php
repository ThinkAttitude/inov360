<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

// Lógica específica do opera (se necessária)
?>

<link rel="stylesheet" href="../../css/horarios_common.css">

<div class="horarios-page calendar-only">
    <!-- Modern Header -->
    <div class="horarios-header">
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
                <h2>Consulta de Horários</h2>
                <p>Este é o módulo onde o operador pode consultar os seus horários atribuídos.</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="horarios-content">
        <!-- Calendar Section -->
        <div class="calendar-section">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <h3>Calendário de Eventos</h3>
                </div>
                <div class="calendar-legend">
                    <div class="legend-item">
                        <span class="legend-dot" style="background-color: #3788d8;"></span>
                        <span>Eventos</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background-color: #10b981;"></span>
                        <span>Férias</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background-color: #f59e0b;"></span>
                        <span>Ausências</span>
                    </div>
                </div>
            </div>

            <div class="calendar-container">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Opera specific styles for legend */
    .calendar-legend {
        display: flex;
        gap: 1.5rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-dark);
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>
