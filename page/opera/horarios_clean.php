<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "opera") {
    echo "<p>Acesso negado.</p>";
    exit;
}
?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.horarios-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.horarios-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-icon {
    background: linear-gradient(135deg, #3E84F2, #5a9ff7);
    border-radius: 12px;
    padding: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.page-icon svg {
    color: white;
}

.header-text h2 {
    margin: 0 0 0.25rem 0;
    color: #0A2240;
    font-weight: 600;
    font-size: 1.5rem;
}

.header-text p {
    margin: 0;
    color: #777777;
    font-size: 0.875rem;
}

.submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.75rem;
    background: linear-gradient(135deg, #1f2937, #374151);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(31, 41, 55, 0.25);
    font-size: 0.95rem;
}

.submit-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(31, 41, 55, 0.35);
    background: linear-gradient(135deg, #374151, #4b5563);
}

.calendar-container {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.month-nav {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.nav-btn {
    background: #4b5563;
    color: white;
    border: none;
    padding: 0.75rem 1.25rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.nav-btn:hover {
    background: #374151;
    transform: translateY(-1px);
}

.current-month {
    font-size: 1.5rem;
    font-weight: 600;
    color: #0A2240;
    min-width: 200px;
    text-align: center;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    background: #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.day-header {
    background: #0A2240;
    color: white;
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.day-cell {
    background: white;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
}

.day-cell:hover {
    background: #f8fafc;
    transform: scale(1.02);
}

.day-cell.today {
    background: #1f2937;
    color: white;
    font-weight: bold;
    border: 2px solid #374151;
}

.day-cell.marked {
    background: #065f46;
    color: white;
    font-weight: 600;
    border: 2px solid #059669;
}

.day-cell.empty {
    background: #f8fafc;
    cursor: default;
}

.day-cell.empty:hover {
    transform: none;
    background: #f8fafc;
}

.day-number {
    font-size: 1.2rem;
    font-weight: 600;
}

.day-status {
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.modal-header h3 {
    color: #0A2240;
    font-size: 1.5rem;
}

.close-btn {
    background: #ef4444;
    color: white;
    border: none;
    padding: 0.5rem;
    border-radius: 50%;
    cursor: pointer;
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-group input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #4b5563;
}

.form-actions {
    display: flex;
    gap: 1rem;
}

.btn {
    flex: 1;
    padding: 1rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #1f2937;
    color: white;
}

.btn-primary:hover {
    background: #374151;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

@media (max-width: 768px) {
    .horarios-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .calendar-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .month-nav {
        flex-direction: column;
        width: 100%;
    }
    
    .current-month {
        order: -1;
    }
    
    .day-cell {
        min-height: 60px;
        font-size: 0.9rem;
    }
    
    .modal-content {
        padding: 1rem;
    }
}
</style>

<div class="horarios-page">
    <!-- Header -->
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
                <h2>Marcação de Horários</h2>
                <p>Clique em qualquer dia para marcar as suas horas trabalhadas</p>
            </div>
        </div>
        <button class="submit-btn" onclick="window.submitMonth()">
            Submeter Mês
        </button>
    </div>

    <!-- Calendário -->
    <div class="calendar-container">
        <div class="calendar-header">
            <div class="month-nav">
                <button class="nav-btn" onclick="window.navigateMonth(-1)">← Anterior</button>
                <div class="current-month" id="current-month">Setembro 2025</div>
                <button class="nav-btn" onclick="window.navigateMonth(1)">Próximo →</button>
            </div>
        </div>

        <div class="calendar-grid" id="calendar-grid">
            <!-- Calendário será gerado aqui -->
        </div>
    </div>
</div>

<script>
console.log('Página de horários carregada via AJAX');
// A inicialização será feita pelo dashboard_opera.js
</script>
