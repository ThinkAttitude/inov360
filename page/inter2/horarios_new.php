<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
    echo "<p>Acesso negado.</p>";
    exit;
}
?>

<!-- ESTILOS CRÍTICOS - CARREGADOS POR ÚLTIMO PARA SOBRESCREVER TEMAS -->
<style>
/* ANULA COMPLETAMENTE OS ESTILOS DE BOTÕES DA ALMALUSA E OUTROS TEMAS */
.horarios-nav-btn {
    background: #4b5563 !important;
    background-color: #4b5563 !important;
    background-image: none !important;
    color: white !important;
    border: none !important;
    padding: 0.75rem 1.25rem !important;
    border-radius: 6px !important;
    cursor: pointer !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    font-size: 0.9rem !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    display: inline-flex !important;
    align-items: center !important;
    text-decoration: none !important;
    margin: 0 !important;
    font-family: inherit !important;
}

.horarios-nav-btn:hover {
    background: #374151 !important;
    background-color: #374151 !important;
    background-image: none !important;
    color: white !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
}

/* Garante que mesmo com temas específicos da empresa, os botões mantenham o estilo */
html body.theme-almalusa .horarios-nav-btn,
html body.theme-inov .horarios-nav-btn,
html body .horarios-nav-btn {
    background: #4b5563 !important;
    background-color: #4b5563 !important;
    background-image: none !important;
    color: white !important;
}

html body.theme-almalusa .horarios-nav-btn:hover,
html body.theme-inov .horarios-nav-btn:hover,
html body .horarios-nav-btn:hover {
    background: #374151 !important;
    background-color: #374151 !important;
    background-image: none !important;
    color: white !important;
}

.horarios-close-btn {
    position: absolute !important;
    top: 1rem !important;
    right: 1rem !important;
    background: #6b7280 !important;
    background-color: #6b7280 !important;
    background-image: none !important;
    color: white !important;
    border: none !important;
    padding: 0.5rem !important;
    border-radius: 50% !important;
    cursor: pointer !important;
    width: 40px !important;
    height: 40px !important;
    font-size: 1.2rem !important;
    transition: all 0.2s ease !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    margin: 0 !important;
    z-index: 1001 !important;
    font-family: inherit !important;
    text-decoration: none !important;
}

.horarios-close-btn:hover {
    background: #374151 !important;
    background-color: #374151 !important;
    background-image: none !important;
    color: white !important;
    transform: scale(1.05) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
}
</style>

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
    font-family: 'Roboto', Arial, sans-serif;
    background: #f8fafc;
    min-height: 100vh;
}

.horarios-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #ffffff, #f8fafc);
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-icon {
    background: linear-gradient(135deg, #1f2937, #374151);
    color: white;
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(31, 41, 55, 0.25);
}

.header-text h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
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
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
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
    background: #4b5563 !important;
    color: white !important;
    border: none !important;
    padding: 0.75rem 1.25rem !important;
    border-radius: 6px !important;
    cursor: pointer !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    font-size: 0.9rem !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    display: inline-flex !important;
    align-items: center !important;
    text-decoration: none !important;
}

.nav-btn:hover {
    background: #374151 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
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

.calendar-day-header {
    background: #0A2240;
    color: white;
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.calendar-day {
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

.calendar-day:hover {
    background: #f8fafc;
    transform: scale(1.02);
}

.calendar-day.today {
    background: #1f2937;
    color: white;
    font-weight: bold;
    border: 2px solid #374151;
}

.calendar-day.has-data {
    background: #065f46;
    color: white;
    font-weight: 600;
    border: 2px solid #059669;
    padding: 0.5rem;
}

.calendar-day.vacation-day {
    background: #dc2626;
    color: white;
    font-weight: 600;
    border: 2px solid #ef4444;
    padding: 0.5rem;
    cursor: default;
}

.calendar-day.vacation-ferias {
    background: #dc2626;
    color: white;
    font-weight: 600;
    border: 2px solid #ef4444;
    padding: 0.5rem;
    cursor: default;
}

.calendar-day.vacation-ausencia {
    background: #f59e0b;
    color: white;
    font-weight: 600;
    border: 2px solid #d97706;
    padding: 0.5rem;
    cursor: default;
}

.calendar-day.vacation-day:hover,
.calendar-day.vacation-ferias:hover,
.calendar-day.vacation-ausencia:hover {
    transform: none;
}

.ferias-badge {
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.2rem 0.3rem;
    border-radius: 6px;
    margin-top: 0.25rem;
    text-align: center;
    line-height: 1.1;
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}

.calendar-day.empty {
    background: #f8fafc;
    cursor: default;
}

.calendar-day.empty:hover {
    transform: none;
    background: #f8fafc;
}

.day-number {
    font-size: 1.2rem;
    font-weight: 600;
}

.day-badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
    border-radius: 10px;
    font-weight: 700;
    line-height: 1;
    background: #065f46;
    color: white;
    margin-top: 0.25rem;
}
    background: #f8fafc;
}

.day-number {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
}

.day-cell.today .day-number {
    color: white;
}

.day-cell.marked .day-number {
    color: white;
    font-size: 1.25rem;
}

.day-cell.ferias .day-number {
    color: white;
    font-size: 1.25rem;
}

.day-details {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    width: 100%;
    margin-top: 0.25rem;
}

.badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.2rem;
    justify-content: center;
    align-items: center;
}

.hour-badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
    border-radius: 10px;
    font-weight: 700;
    line-height: 1;
    text-transform: lowercase;
}

.hour-badge.work {
    background: #10b981;
    color: white;
}

.hour-badge.extra {
    background: #f59e0b;
    color: white;
}

.hour-badge.prevention {
    background: #ef4444;
    color: white;
}

.hour-badge.km {
    background: #3b82f6;
    color: white;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
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
    color: #1f2937;
    font-size: 1.5rem;
    font-weight: 600;
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
    border-color: #065f46;
    box-shadow: 0 0 0 3px rgba(6, 95, 70, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 2px solid #e2e8f0;
}

.btn {
    padding: 0.875rem 1.75rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.btn-primary {
    background: #065f46;
    color: white;
}

.btn-primary:hover {
    background: #047857;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-1px);
}

/* Estilos do Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    position: relative;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
    border-radius: 12px 12px 0 0;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-body {
    padding: 1.5rem;
}

.date-display {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    text-align: center;
    color: #374151;
    font-size: 1rem;
}

.horarios-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    color: #374151;
    font-weight: 500;
    font-size: 0.9rem;
}

.form-group input,
.form-group textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.2s ease;
    background: white;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 1.5rem;
}

/* Estilos para seleção múltipla */
.bulk-instructions {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    color: #0c4a6e;
}

.bulk-instructions p {
    margin: 0.5rem 0;
    font-size: 0.9rem;
}

.calendar-day.bulk-selected {
    background: #3b82f6 !important;
    color: white !important;
    border: 2px solid #1d4ed8 !important;
    transform: scale(0.95);
}

.calendar-day.bulk-selectable {
    transition: all 0.2s ease;
}

.calendar-day.bulk-selectable:hover {
    background: #dbeafe !important;
    border: 2px solid #3b82f6 !important;
}

.bulk-mode .calendar-day:not(.vacation-day):not(.empty) {
    cursor: pointer !important;
}

.selected-days-info {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.selected-days-list {
    margin-top: 0.5rem;
    max-height: 100px;
    overflow-y: auto;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.selected-day-tag {
    background: #3b82f6;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.selected-day-tag .remove-day {
    cursor: pointer;
    font-weight: bold;
    opacity: 0.7;
}

.selected-day-tag .remove-day:hover {
    opacity: 1;
}

.bulk-mode-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #3b82f6;
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    z-index: 1001;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Estilos para notificações */
.notification {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 1002;
    min-width: 300px;
    overflow: hidden;
    animation: slideDown 0.3s ease;
}

.notification-success {
    border-left: 4px solid #10b981;
}

.notification-error {
    border-left: 4px solid #ef4444;
}

.notification-info {
    border-left: 4px solid #3b82f6;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
}

.notification-icon {
    font-weight: bold;
    font-size: 1.2rem;
}

.notification-success .notification-icon {
    color: #10b981;
}

.notification-error .notification-icon {
    color: #ef4444;
}

.notification-info .notification-icon {
    color: #3b82f6;
}

.notification-message {
    color: #374151;
    font-weight: 500;
}

.notification.fade-out {
    animation: slideUp 0.3s ease forwards;
}

@keyframes slideDown {
    from {
        transform: translateX(-50%) translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    to {
        transform: translateX(-50%) translateY(-20px);
        opacity: 0;
    }
}

@media (max-width: 768px) {
    .horarios-page {
        padding: 0.5rem;
    }
    
    .horarios-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .calendar-container {
        padding: 1rem;
    }
    
    .calendar-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .day-cell {
        min-height: 60px;
        padding: 0.5rem;
    }
    
    .day-number {
        font-size: 1.2rem;
    }
    
    .hour-badge {
        font-size: 0.55rem;
        padding: 0.15rem 0.3rem;
    }
}

/* CSS para Date Range Picker */
.date-range-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.date-input {
    flex: 1;
    min-width: 150px;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.date-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.date-separator {
    font-weight: 500;
    color: #6b7280;
    white-space: nowrap;
}

.selected-range-info {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.range-details {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #6b7280;
}

@media (max-width: 768px) {
    .date-range-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .date-separator {
        text-align: center;
        margin: 0.5rem 0;
    }
}
</style>

<div class="horarios-page">
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
                <h2>Marcação de Horários</h2>
                <p>Clique em qualquer dia para marcar as suas horas trabalhadas</p>
            </div>
        </div>
        <button class="submit-btn" onclick="window.submitMonth()">
            Submeter Mês
        </button>
        
        <!-- Botão para seleção múltipla -->
        <button class="horarios-nav-btn" id="bulkSelectBtn" style="background: #3b82f6 !important; margin-left: 1rem;">
            Marcação em Lote
        </button>
    </div>

    <!-- Calendário -->
    <div class="calendar-container">
        <div class="calendar-header">
            <div class="month-nav">
                <button class="horarios-nav-btn" id="prevMonth">← Anterior</button>
                <div class="current-month" id="monthYear">Setembro 2025</div>
                <button class="horarios-nav-btn" id="nextMonth">Próximo →</button>
            </div>
            <button class="horarios-nav-btn" id="todayBtn">Hoje</button>
        </div>

        <div class="calendar-grid" id="calendar-grid">
            <!-- Calendário será gerado aqui -->
        </div>
    </div>
</div>

<!-- Modal de Horários -->
<div id="horariosModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Marcar Horários</h3>
            <button class="horarios-close-btn close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="date-display">
                <strong id="selectedDate">Data selecionada</strong>
            </div>
            
            <form id="horariosForm" class="horarios-form">
                <div class="form-group">
                    <label for="horasNormais">Horas Trabalhadas (HH:MM):</label>
                    <input type="text" id="horasNormais" name="horas_normais" placeholder="08:00">
                </div>
                
                <div class="form-group">
                    <label for="horasExtra">Horas Extra (HH:MM):</label>
                    <input type="text" id="horasExtra" name="horas_extra" placeholder="00:00">
                </div>
                
                <div class="form-group">
                    <label for="horasPrevencao">Horas Prevenção (HH:MM):</label>
                    <input type="text" id="horasPrevencao" name="horas_prevencao" placeholder="00:00">
                </div>
                
                <div class="form-group">
                    <label for="kmViatura">Quilómetros Viatura Própria:</label>
                    <input type="number" id="kmViatura" name="km_viatura" step="1" min="0" placeholder="0">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="horarios-nav-btn" id="cancelBtn">Cancelar</button>
                    <button type="button" class="horarios-nav-btn" id="clearDayBtn" style="background: #dc2626 !important;">Limpar Dia</button>
                    <button type="button" class="horarios-nav-btn" id="saveBtn" style="background: #065f46 !important;">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Seleção em Lote -->
<div id="bulkModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Marcação em Lote</h3>
            <button class="horarios-close-btn close-bulk-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="bulk-instructions">
                <p><strong>Instruções:</strong> Selecione o período de datas e preencha os campos desejados. Os valores serão aplicados a todos os dias do período selecionado.</p>
            </div>
            
            <form id="bulkForm" class="horarios-form">
                <div class="form-group">
                    <label for="bulkDateRange">Período de Datas:</label>
                    <div class="date-range-container">
                        <input type="date" id="bulkStartDate" class="date-input">
                        <span class="date-separator">até</span>
                        <input type="date" id="bulkEndDate" class="date-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="bulkHorasNormais">Horas Trabalhadas (HH:MM):</label>
                    <input type="text" id="bulkHorasNormais" placeholder="08:00">
                </div>
                
                <div class="form-group">
                    <label for="bulkHorasExtra">Horas Extra (HH:MM):</label>
                    <input type="text" id="bulkHorasExtra" placeholder="00:00">
                </div>
                
                <div class="form-group">
                    <label for="bulkHorasPrevencao">Horas Prevenção (HH:MM):</label>
                    <input type="text" id="bulkHorasPrevencao" placeholder="00:00">
                </div>
                
                <div class="form-group">
                    <label for="bulkKmViatura">Quilómetros Viatura Própria:</label>
                    <input type="number" id="bulkKmViatura" step="1" min="0" placeholder="0">
                </div>
                
                <div class="selected-range-info">
                    <strong>Período selecionado: <span id="selectedRangeDisplay">Nenhum período selecionado</span></strong>
                    <div id="selectedRangeDetails" class="range-details"></div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="horarios-nav-btn" id="cancelBulkBtn">Cancelar</button>
                    <button type="button" class="horarios-nav-btn" id="clearRangeBtn" style="background: #f59e0b !important;">Limpar Período</button>
                    <button type="button" class="horarios-nav-btn" id="applyBulkBtn" style="background: #065f46 !important;">Aplicar ao Período</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Garantir que o script só executa quando a página está carregada
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, inicializando calendário...');
    if (typeof window.initializeHorariosCalendarInter2 === 'function') {
        window.initializeHorariosCalendarInter2();
    } else {
        // Se a função ainda não existir, tentar novamente
        setTimeout(() => {
            if (typeof window.initializeHorariosCalendarInter2 === 'function') {
                window.initializeHorariosCalendarInter2();
            }
        }, 100);
    }
});
</script>
