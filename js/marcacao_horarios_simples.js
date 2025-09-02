// Sistema de Marcação de Horários
document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 Sistema de horários inicializado');
    initializeHorarios();
});

// Variáveis globais
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let marcacoes = JSON.parse(localStorage.getItem('marcacoes_horarios') || '{}');

// Nomes dos meses
const monthNames = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

function initializeHorarios() {
    console.log('🔄 Inicializando calendário de horários...');
    renderCalendar();
}

function renderCalendar() {
    console.log('📅 Renderizando calendário para:', monthNames[currentMonth], currentYear);
    
    const currentMonthEl = document.getElementById('current-month');
    const calendarGridEl = document.getElementById('calendar-grid');
    
    if (!currentMonthEl || !calendarGridEl) {
        console.error('❌ Elementos do calendário não encontrados');
        return;
    }
    
    // Atualizar cabeçalho do mês
    currentMonthEl.textContent = `${monthNames[currentMonth]} ${currentYear}`;
    
    const firstDay = new Date(currentYear, currentMonth, 1);
    const lastDay = new Date(currentYear, currentMonth + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startDayOfWeek = firstDay.getDay();
    
    let html = '';
    
    // Cabeçalho dos dias da semana
    const weekDays = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    weekDays.forEach(day => {
        html += `<div class="day-header">${day}</div>`;
    });
    
    // Dias vazios no início
    for (let i = 0; i < startDayOfWeek; i++) {
        html += `<div class="day-cell empty"></div>`;
    }
    
    // Dias do mês
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const dateKey = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        const isToday = day === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear();
        const hasMarcacao = marcacoes[dateKey];
        
        let cellClass = 'day-cell';
        let statusText = '';
        
        if (isToday) {
            cellClass += ' today';
        } else if (hasMarcacao) {
            cellClass += ' marked';
            statusText = '<div class="day-status">✓ Marcado</div>';
        }
        
        html += `
            <div class="${cellClass}" onclick="openDayModal(${day})">
                <div class="day-number">${day}</div>
                ${statusText}
            </div>
        `;
    }
    
    calendarGridEl.innerHTML = html;
    console.log('✅ Calendário renderizado com sucesso');
}

function navigateMonth(direction) {
    console.log('🔄 Navegando mês:', direction > 0 ? 'próximo' : 'anterior');
    currentMonth += direction;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    } else if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar();
}

function openDayModal(day) {
    console.log('📝 Abrindo modal para dia:', day);
    const dateKey = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    const existingData = marcacoes[dateKey] || {};
    
    const modalHtml = `
        <div class="modal" id="day-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>📅 Marcação - Dia ${day}</h3>
                    <button class="close-btn" onclick="closeDayModal()">✕</button>
                </div>
                
                <form id="day-form">
                    <div class="form-group">
                        <label>⏰ Horas Trabalhadas (HH:MM)</label>
                        <input type="text" id="horas-trabalhadas" placeholder="08:00" value="${existingData.horasTrabalhadas || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label>⚡ Horas Extra (HH:MM)</label>
                        <input type="text" id="horas-extra" placeholder="00:00" value="${existingData.horasExtra || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label>🛡️ Horas de Prevenção (HH:MM)</label>
                        <input type="text" id="horas-prevencao" placeholder="00:00" value="${existingData.horasPrevencao || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label>🚗 Quilómetros Viatura Própria</label>
                        <input type="number" id="km-viatura" placeholder="0" value="${existingData.kmViatura || ''}">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="saveDayData(${day})">💾 Guardar</button>
                        <button type="button" class="btn btn-secondary" onclick="closeDayModal()">❌ Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeDayModal() {
    const modal = document.getElementById('day-modal');
    if (modal) {
        modal.remove();
    }
}

function saveDayData(day) {
    console.log('💾 Guardando dados para dia:', day);
    const dateKey = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    
    const data = {
        horasTrabalhadas: document.getElementById('horas-trabalhadas').value,
        horasExtra: document.getElementById('horas-extra').value,
        horasPrevencao: document.getElementById('horas-prevencao').value,
        kmViatura: document.getElementById('km-viatura').value,
        dataModificacao: new Date().toISOString()
    };
    
    // Guardar no localStorage
    marcacoes[dateKey] = data;
    localStorage.setItem('marcacoes_horarios', JSON.stringify(marcacoes));
    
    // Fechar modal e atualizar calendário
    closeDayModal();
    renderCalendar();
    
    // Mostrar confirmação
    alert(`✅ Marcação guardada para o dia ${day}!\n\n📊 Resumo:\n• Horas trabalhadas: ${data.horasTrabalhadas || 'Não definido'}\n• Horas extra: ${data.horasExtra || 'Não definido'}\n• Horas prevenção: ${data.horasPrevencao || 'Não definido'}\n• KM viatura: ${data.kmViatura || '0'} km`);
}

function submitMonth() {
    console.log('🚀 Submetendo mês:', monthNames[currentMonth], currentYear);
    const monthKey = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}`;
    const monthMarcacoes = {};
    
    // Filtrar marcações do mês atual
    Object.keys(marcacoes).forEach(dateKey => {
        if (dateKey.startsWith(monthKey)) {
            monthMarcacoes[dateKey] = marcacoes[dateKey];
        }
    });
    
    if (Object.keys(monthMarcacoes).length === 0) {
        alert('❌ Não há marcações para submeter neste mês.');
        return;
    }
    
    // Calcular resumo
    let diasTrabalhados = 0;
    let horasTotais = 0;
    let horasExtra = 0;
    let horasPrevencao = 0;
    let kmTotal = 0;
    
    Object.values(monthMarcacoes).forEach(marcacao => {
        if (marcacao.horasTrabalhadas) {
            diasTrabalhados++;
            const [h, m] = marcacao.horasTrabalhadas.split(':');
            horasTotais += parseInt(h) + parseInt(m || 0) / 60;
        }
        if (marcacao.horasExtra) {
            const [h, m] = marcacao.horasExtra.split(':');
            horasExtra += parseInt(h) + parseInt(m || 0) / 60;
        }
        if (marcacao.horasPrevencao) {
            const [h, m] = marcacao.horasPrevencao.split(':');
            horasPrevencao += parseInt(h) + parseInt(m || 0) / 60;
        }
        if (marcacao.kmViatura) {
            kmTotal += parseInt(marcacao.kmViatura);
        }
    });
    
    const confirmMsg = `Deseja submeter as marcações de ${monthNames[currentMonth]} ${currentYear}?\n\n📊 Resumo:\n• Dias trabalhados: ${diasTrabalhados}\n• Horas totais: ${horasTotais.toFixed(1)}h\n• Horas extra: ${horasExtra.toFixed(1)}h\n• Horas prevenção: ${horasPrevencao.toFixed(1)}h\n• KM total: ${kmTotal} km\n\n⚠️ Após a submissão, as marcações não poderão ser alteradas.`;
    
    if (confirm(confirmMsg)) {
        // Guardar para aprovação
        const submissionData = {
            month: monthKey,
            marcacoes: monthMarcacoes,
            summary: {
                diasTrabalhados,
                horasTotais: horasTotais.toFixed(1),
                horasExtra: horasExtra.toFixed(1),
                horasPrevencao: horasPrevencao.toFixed(1),
                kmTotal
            },
            submittedAt: new Date().toISOString(),
            submittedBy: 'Operador',
            status: 'pending'
        };
        
        const pendingApprovals = JSON.parse(localStorage.getItem('marcacoes_pending_approval') || '[]');
        pendingApprovals.push(submissionData);
        localStorage.setItem('marcacoes_pending_approval', JSON.stringify(pendingApprovals));
        
        // Marcar mês como submetido
        const submittedMonths = JSON.parse(localStorage.getItem('submitted_months') || '[]');
        submittedMonths.push(monthKey);
        localStorage.setItem('submitted_months', JSON.stringify(submittedMonths));
        
        alert('✅ Marcações submetidas com sucesso!\n\n🔄 Status: Aguardando aprovação do supervisor\n📧 Será notificado quando aprovado');
        
        console.log('✅ Submissão concluída com sucesso');
    }
}

// Tornar funções disponíveis globalmente para os botões onclick
window.navigateMonth = navigateMonth;
window.openDayModal = openDayModal;
window.closeDayModal = closeDayModal;
window.saveDayData = saveDayData;
window.submitMonth = submitMonth;

// Inicializar quando conteúdo for carregado dinamicamente
window.initializeHorarios = initializeHorarios;
