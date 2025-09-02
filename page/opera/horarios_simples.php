<?php
session_start();

// Verificar se está logado e tem permissão
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'opera') {
    header('Location: ../page_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcação de Horários - INOV360</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            padding: 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #0A2240;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
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
            background: #3b82f6;
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .nav-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .current-month {
            font-size: 1.5rem;
            font-weight: 600;
            color: #0A2240;
            min-width: 200px;
            text-align: center;
        }

        .submit-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .submit-btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
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
            background: #f0f9ff;
            transform: scale(1.05);
        }

        .day-cell.today {
            background: #3b82f6;
            color: white;
            font-weight: bold;
        }

        .day-cell.marked {
            background: #10b981;
            color: white;
            font-weight: 600;
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
            border-color: #3b82f6;
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
            background: #10b981;
            color: white;
        }

        .btn-primary:hover {
            background: #059669;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .instructions {
            margin-top: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-radius: 12px;
            border: 1px solid #bfdbfe;
        }

        .instructions h4 {
            color: #0A2240;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .instructions ul {
            color: #475569;
            line-height: 1.6;
        }

        .instructions li {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
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
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Marcação de Horários</h1>
            <p>Clique em qualquer dia para marcar as suas horas trabalhadas</p>
        </div>

        <div class="calendar-container">
            <div class="calendar-header">
                <div class="month-nav">
                    <button class="nav-btn" onclick="navigateMonth(-1)">← Anterior</button>
                    <div class="current-month" id="current-month"></div>
                    <button class="nav-btn" onclick="navigateMonth(1)">Próximo →</button>
                </div>
                <button class="submit-btn" onclick="submitMonth()">🚀 Submeter Mês</button>
            </div>

            <div class="calendar-grid" id="calendar-grid">
                <!-- Calendário será gerado aqui -->
            </div>

            <div class="instructions">
                <h4>💡 Como usar o sistema</h4>
                <ul>
                    <li><strong>Clique num dia</strong> para marcar horas trabalhadas, horas extra, horas de prevenção e quilómetros</li>
                    <li><strong>Dias marcados</strong> aparecem a verde no calendário</li>
                    <li><strong>Submeta o mês</strong> quando todas as marcações estiverem completas</li>
                    <li><strong>Após submissão</strong> aguarde aprovação do supervisor</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Variáveis globais
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        let marcacoes = JSON.parse(localStorage.getItem('marcacoes_horarios') || '{}');

        // Nomes dos meses
        const monthNames = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

        // Inicializar quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🟢 Inicializando calendário...');
            renderCalendar();
        });

        function renderCalendar() {
            // Atualizar cabeçalho do mês
            document.getElementById('current-month').textContent = `${monthNames[currentMonth]} ${currentYear}`;
            
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
            
            document.getElementById('calendar-grid').innerHTML = html;
            console.log('✅ Calendário renderizado:', monthNames[currentMonth], currentYear);
        }

        function navigateMonth(direction) {
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
                    submittedBy: '<?php echo $_SESSION['username']; ?>',
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
            }
        }
    </script>
</body>
</html>
