// Sistema de Marcação de Horários
// Implementa o novo sistema de marcação de horas trabalhadas, horas extra, prevenção e km

class MarcacaoHorarios {
    constructor() {
        this.currentMonth = new Date().getMonth();
        this.currentYear = new Date().getFullYear();
        this.marcacoes = this.loadMarcacoes();
        this.feriasAusencias = this.loadFeriasAusencias();
        this.init();
    }

    init() {
        this.createCalendarContainer();
        this.createMarcacaoModal();
        this.renderCalendar();
        this.bindEvents();
    }

    createCalendarContainer() {
        console.log('[MARCACAO] Criando container do calendário...');
        const container = document.getElementById('marcacao-calendar-container');
        if (!container) {
            console.error('[MARCACAO] Container não encontrado!');
            return;
        }

        console.log('[MARCACAO] Container encontrado, criando HTML...');
        container.innerHTML = `
            <div class="marcacao-header">
                <div class="month-navigation">
                    <button class="nav-btn" id="prev-month">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                    </button>
                    <h3 id="current-month-year">${this.getMonthName(this.currentMonth)} ${this.currentYear}</h3>
                    <button class="nav-btn" id="next-month">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                </div>
                <div class="legend">
                    <div class="legend-item">
                        <span class="legend-dot work-day"></span>
                        <span>Trabalho</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot overtime"></span>
                        <span>Horas Extra</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot prevention"></span>
                        <span>Prevenção</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot vacation"></span>
                        <span>Férias</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot absence"></span>
                        <span>Ausência</span>
                    </div>
                </div>
                <div class="summary-stats">
                    <div class="stat-item">
                        <span class="stat-label">Horas Trabalhadas:</span>
                        <span class="stat-value" id="total-hours">0h</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Horas Extra:</span>
                        <span class="stat-value" id="total-overtime">0h</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">KM Total:</span>
                        <span class="stat-value" id="total-km">0 km</span>
                    </div>
                </div>
            </div>
            <div class="marcacao-calendar" id="marcacao-calendar"></div>
        `;
        
        console.log('[MARCACAO] Container HTML criado com sucesso!');
        console.log('[MARCACAO] Container visível:', container.offsetHeight > 0);
    }

    createMarcacaoModal() {
        const modal = document.createElement('div');
        modal.id = 'marcacao-modal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Marcação de Horário</h3>
                    <button class="close-btn" onclick="fecharMarcacaoModal()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="date-info">
                        <span id="modal-date"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo de Dia:</label>
                        <select id="tipo-dia" onchange="updateFormVisibility()">
                            <option value="trabalho">Dia de Trabalho</option>
                            <option value="descanso">Dia de Descanso</option>
                        </select>
                    </div>

                    <div id="trabalho-fields">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Hora Início:</label>
                                <input type="time" id="hora-inicio" value="09:00">
                            </div>
                            <div class="form-group">
                                <label>Hora Fim:</label>
                                <input type="time" id="hora-fim" value="17:00">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Horas Extra (minutos):</label>
                                <input type="number" id="horas-extra" min="0" max="480" step="15" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label>Horas Prevenção (minutos):</label>
                                <input type="number" id="horas-prevencao" min="0" max="480" step="15" placeholder="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>KM em Viatura Própria:</label>
                            <input type="number" id="km-viatura" min="0" step="1" placeholder="0">
                        </div>

                        <div class="form-group">
                            <label>Observações:</label>
                            <textarea id="observacoes" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary" onclick="fecharMarcacaoModal()">Cancelar</button>
                    <button class="btn-primary" onclick="salvarMarcacao()">Guardar</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    }

    renderCalendar() {
        const calendar = document.getElementById('marcacao-calendar');
        if (!calendar) return;

        const firstDay = new Date(this.currentYear, this.currentMonth, 1);
        const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());

        let calendarHTML = `
            <div class="calendar-header">
                <div class="day-header">Dom</div>
                <div class="day-header">Seg</div>
                <div class="day-header">Ter</div>
                <div class="day-header">Qua</div>
                <div class="day-header">Qui</div>
                <div class="day-header">Sex</div>
                <div class="day-header">Sáb</div>
            </div>
            <div class="calendar-body">
        `;

        let currentDate = new Date(startDate);
        let totalHours = 0;
        let totalOvertime = 0;
        let totalKm = 0;

        for (let week = 0; week < 6; week++) {
            calendarHTML += '<div class="calendar-week">';
            
            for (let day = 0; day < 7; day++) {
                const dateStr = this.formatDate(currentDate);
                const isCurrentMonth = currentDate.getMonth() === this.currentMonth;
                const isToday = this.isToday(currentDate);
                const marcacao = this.marcacoes[dateStr];
                const feriaAusencia = this.feriasAusencias[dateStr];
                
                let dayClass = 'calendar-day';
                if (!isCurrentMonth) dayClass += ' other-month';
                if (isToday) dayClass += ' today';
                if (marcacao) dayClass += ' has-marcacao';
                if (feriaAusencia) dayClass += ` ${feriaAusencia.tipo}`;

                let dayContent = `<div class="day-number">${currentDate.getDate()}</div>`;
                
                if (feriaAusencia) {
                    dayContent += `<div class="day-status ${feriaAusencia.tipo}">${feriaAusencia.label}</div>`;
                } else if (marcacao) {
                    if (marcacao.tipo === 'trabalho') {
                        const horasRegulares = this.calculateHours(marcacao.horaInicio, marcacao.horaFim);
                        dayContent += `<div class="day-status work">${marcacao.horaInicio} - ${marcacao.horaFim}</div>`;
                        
                        if (marcacao.horasExtra > 0) {
                            dayContent += `<div class="day-extra overtime">+${this.formatMinutes(marcacao.horasExtra)}</div>`;
                        }
                        
                        if (marcacao.horasPrevencao > 0) {
                            dayContent += `<div class="day-extra prevention">Prev: ${this.formatMinutes(marcacao.horasPrevencao)}</div>`;
                        }
                        
                        if (marcacao.kmViatura > 0) {
                            dayContent += `<div class="day-extra km">${marcacao.kmViatura} km</div>`;
                        }

                        // Calcular totais para o mês atual
                        if (isCurrentMonth) {
                            totalHours += horasRegulares;
                            totalOvertime += marcacao.horasExtra || 0;
                            totalKm += marcacao.kmViatura || 0;
                        }
                    } else {
                        dayContent += `<div class="day-status rest">Descanso</div>`;
                    }
                }

                calendarHTML += `
                    <div class="${dayClass}" data-date="${dateStr}" onclick="abrirMarcacaoModal('${dateStr}')">
                        ${dayContent}
                    </div>
                `;

                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            calendarHTML += '</div>';
            
            // Se estamos no próximo mês, parar
            if (currentDate.getMonth() !== this.currentMonth && week > 3) {
                break;
            }
        }

        calendarHTML += '</div>';
        calendar.innerHTML = calendarHTML;

        // Atualizar estatísticas
        this.updateSummaryStats(totalHours, totalOvertime, totalKm);
    }

    updateSummaryStats(totalHours, totalOvertime, totalKm) {
        const totalHoursEl = document.getElementById('total-hours');
        const totalOvertimeEl = document.getElementById('total-overtime');
        const totalKmEl = document.getElementById('total-km');

        if (totalHoursEl) totalHoursEl.textContent = `${Math.round(totalHours)}h`;
        if (totalOvertimeEl) totalOvertimeEl.textContent = this.formatMinutes(totalOvertime);
        if (totalKmEl) totalKmEl.textContent = `${totalKm} km`;
    }

    bindEvents() {
        // Navegação de meses
        const prevBtn = document.getElementById('prev-month');
        const nextBtn = document.getElementById('next-month');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                this.currentMonth--;
                if (this.currentMonth < 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                }
                this.updateMonthDisplay();
                this.renderCalendar();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.currentMonth++;
                if (this.currentMonth > 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                }
                this.updateMonthDisplay();
                this.renderCalendar();
            });
        }
    }

    updateMonthDisplay() {
        const monthYearEl = document.getElementById('current-month-year');
        if (monthYearEl) {
            monthYearEl.textContent = `${this.getMonthName(this.currentMonth)} ${this.currentYear}`;
        }
    }

    // Funções auxiliares
    formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }

    getMonthName(month) {
        const months = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];
        return months[month];
    }

    calculateHours(inicio, fim) {
        const [horaInicio, minInicio] = inicio.split(':').map(Number);
        const [horaFim, minFim] = fim.split(':').map(Number);
        
        const inicioMinutos = horaInicio * 60 + minInicio;
        const fimMinutos = horaFim * 60 + minFim;
        
        return (fimMinutos - inicioMinutos) / 60;
    }

    formatMinutes(minutes) {
        if (!minutes) return '0h';
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        
        if (hours > 0 && mins > 0) {
            return `${hours}h${mins}m`;
        } else if (hours > 0) {
            return `${hours}h`;
        } else {
            return `${mins}m`;
        }
    }

    // Gestão de dados (localStorage)
    loadMarcacoes() {
        const stored = localStorage.getItem('marcacoes_horarios');
        return stored ? JSON.parse(stored) : {};
    }

    saveMarcacoes() {
        localStorage.setItem('marcacoes_horarios', JSON.stringify(this.marcacoes));
    }

    loadFeriasAusencias() {
        const stored = localStorage.getItem('ferias_ausencias_aprovadas');
        return stored ? JSON.parse(stored) : {};
    }

    // Modal functions
    openMarcacaoModal(dateStr) {
        const modal = document.getElementById('marcacao-modal');
        const modalDate = document.getElementById('modal-date');
        
        if (!modal || !modalDate) return;

        const date = new Date(dateStr);
        modalDate.textContent = date.toLocaleDateString('pt-PT', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });

        // Verificar se é férias/ausência aprovada
        const feriaAusencia = this.feriasAusencias[dateStr];
        if (feriaAusencia) {
            modalDate.innerHTML += `<br><span class="status-badge ${feriaAusencia.tipo}">${feriaAusencia.label}</span>`;
            
            // Desabilitar formulário se for férias/ausência
            const inputs = modal.querySelectorAll('input, select, textarea');
            inputs.forEach(input => input.disabled = true);
            
            modal.querySelector('.btn-primary').style.display = 'none';
        } else {
            // Habilitar formulário
            const inputs = modal.querySelectorAll('input, select, textarea');
            inputs.forEach(input => input.disabled = false);
            
            modal.querySelector('.btn-primary').style.display = 'block';

            // Carregar dados existentes se houver
            const marcacao = this.marcacoes[dateStr];
            if (marcacao) {
                document.getElementById('tipo-dia').value = marcacao.tipo;
                document.getElementById('hora-inicio').value = marcacao.horaInicio || '09:00';
                document.getElementById('hora-fim').value = marcacao.horaFim || '17:00';
                document.getElementById('horas-extra').value = marcacao.horasExtra || '';
                document.getElementById('horas-prevencao').value = marcacao.horasPrevencao || '';
                document.getElementById('km-viatura').value = marcacao.kmViatura || '';
                document.getElementById('observacoes').value = marcacao.observacoes || '';
            } else {
                // Reset para valores padrão
                document.getElementById('tipo-dia').value = 'trabalho';
                document.getElementById('hora-inicio').value = '09:00';
                document.getElementById('hora-fim').value = '17:00';
                document.getElementById('horas-extra').value = '';
                document.getElementById('horas-prevencao').value = '';
                document.getElementById('km-viatura').value = '';
                document.getElementById('observacoes').value = '';
            }
        }

        modal.dataset.date = dateStr;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        updateFormVisibility();
    }

    closeMarcacaoModal() {
        const modal = document.getElementById('marcacao-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    saveMarcacao() {
        const modal = document.getElementById('marcacao-modal');
        const dateStr = modal.dataset.date;
        
        const tipo = document.getElementById('tipo-dia').value;
        
        if (tipo === 'trabalho') {
            const horaInicio = document.getElementById('hora-inicio').value;
            const horaFim = document.getElementById('hora-fim').value;
            const horasExtra = parseInt(document.getElementById('horas-extra').value) || 0;
            const horasPrevencao = parseInt(document.getElementById('horas-prevencao').value) || 0;
            const kmViatura = parseInt(document.getElementById('km-viatura').value) || 0;
            const observacoes = document.getElementById('observacoes').value;

            // Validação básica
            if (horaInicio >= horaFim) {
                alert('A hora de fim deve ser posterior à hora de início!');
                return;
            }

            this.marcacoes[dateStr] = {
                tipo: 'trabalho',
                horaInicio,
                horaFim,
                horasExtra,
                horasPrevencao,
                kmViatura,
                observacoes,
                dataRegisto: new Date().toISOString()
            };
        } else {
            this.marcacoes[dateStr] = {
                tipo: 'descanso',
                dataRegisto: new Date().toISOString()
            };
        }

        this.saveMarcacoes();
        this.renderCalendar();
        this.closeMarcacaoModal();

        // Feedback visual
        this.showToast('Marcação guardada com sucesso!', 'success');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">
                    ${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}
                </span>
                <span class="toast-message">${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Remover após 3 segundos
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Método para sincronizar com pedidos de férias aprovados
    syncFeriasAusencias(pedidosAprovados) {
        this.feriasAusencias = {};
        
        pedidosAprovados.forEach(pedido => {
            if (pedido.estado === 'aprovado') {
                const startDate = new Date(pedido.dataInicio);
                const endDate = new Date(pedido.dataFim);
                
                for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                    const dateStr = this.formatDate(d);
                    this.feriasAusencias[dateStr] = {
                        tipo: pedido.tipo === 'ferias' ? 'vacation' : 'absence',
                        label: pedido.tipo === 'ferias' ? 'Férias' : 'Ausência',
                        pedidoId: pedido.id
                    };
                }
            }
        });

        localStorage.setItem('ferias_ausencias_aprovadas', JSON.stringify(this.feriasAusencias));
        this.renderCalendar();
    }

    // Método para exportar dados do mês para aprovação
    exportMontlyData(month = this.currentMonth, year = this.currentYear) {
        const monthKey = `${year}-${(month + 1).toString().padStart(2, '0')}`;
        const monthData = {};

        Object.keys(this.marcacoes).forEach(dateStr => {
            if (dateStr.startsWith(monthKey)) {
                monthData[dateStr] = this.marcacoes[dateStr];
            }
        });

        return {
            userId: this.getCurrentUserId(),
            month: monthKey,
            marcacoes: monthData,
            summary: this.calculateMonthlySummary(monthData),
            dataExportacao: new Date().toISOString()
        };
    }

    calculateMonthlySummary(monthData) {
        let totalHours = 0;
        let totalOvertime = 0;
        let totalPrevention = 0;
        let totalKm = 0;
        let workDays = 0;

        Object.values(monthData).forEach(marcacao => {
            if (marcacao.tipo === 'trabalho') {
                workDays++;
                totalHours += this.calculateHours(marcacao.horaInicio, marcacao.horaFim);
                totalOvertime += marcacao.horasExtra || 0;
                totalPrevention += marcacao.horasPrevencao || 0;
                totalKm += marcacao.kmViatura || 0;
            }
        });

        return {
            diasTrabalhados: workDays,
            horasTotais: Math.round(totalHours * 100) / 100,
            horasExtra: totalOvertime,
            horasPrevencao: totalPrevention,
            kmTotal: totalKm
        };
    }

    getCurrentUserId() {
        // Esta função seria implementada para obter o ID do usuário atual
        return localStorage.getItem('current_user_id') || 'user_' + Date.now();
    }
}

// Funções globais para compatibilidade
window.abrirMarcacaoModal = function(dateStr) {
    if (window.marcacaoHorarios) {
        window.marcacaoHorarios.openMarcacaoModal(dateStr);
    }
};

window.fecharMarcacaoModal = function() {
    if (window.marcacaoHorarios) {
        window.marcacaoHorarios.closeMarcacaoModal();
    }
};

window.salvarMarcacao = function() {
    if (window.marcacaoHorarios) {
        window.marcacaoHorarios.saveMarcacao();
    }
};

window.updateFormVisibility = function() {
    const tipo = document.getElementById('tipo-dia').value;
    const trabalhoFields = document.getElementById('trabalho-fields');
    
    if (trabalhoFields) {
        trabalhoFields.style.display = tipo === 'trabalho' ? 'block' : 'none';
    }
};

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    console.log('[MARCACAO] DOM carregado, procurando container...');
    
    const container = document.getElementById('marcacao-calendar-container');
    if (container) {
        console.log('[MARCACAO] Container encontrado, inicializando sistema...');
        window.marcacaoHorarios = new MarcacaoHorarios();
        console.log('[MARCACAO] Sistema inicializado com sucesso!');
    } else {
        console.warn('[MARCACAO] Container não encontrado!');
        // Tentar novamente após um pequeno delay
        setTimeout(function() {
            const retryContainer = document.getElementById('marcacao-calendar-container');
            if (retryContainer) {
                console.log('[MARCACAO] Container encontrado na segunda tentativa!');
                window.marcacaoHorarios = new MarcacaoHorarios();
            }
        }, 500);
    }
});
