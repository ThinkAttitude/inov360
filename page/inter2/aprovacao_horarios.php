<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
    echo "<p>Acesso negado.</p>";
    exit;
}
?>

<link rel="stylesheet" href="../../css/horarios_common.css">
<link rel="stylesheet" href="../../css/aprovacao_horarios.css">

<div class="aprovacao-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20,6 9,17 4,12"></polyline>
                </svg>
            </div>
            <div class="header-text">
                <h2>Aprovação de Horários</h2>
                <p>Aprove ou rejeite as marcações de horários dos operadores da sua equipa.</p>
            </div>
        </div>
    </div>

    <!-- Filtros e Controles -->
    <div class="controls-section">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="pending" onclick="filterApprovals('pending')">
                Pendentes
                <span class="count-badge" id="pending-count">0</span>
            </button>
            <button class="filter-tab" data-filter="approved" onclick="filterApprovals('approved')">
                Aprovadas
                <span class="count-badge" id="approved-count">0</span>
            </button>
            <button class="filter-tab" data-filter="rejected" onclick="filterApprovals('rejected')">
                Rejeitadas
                <span class="count-badge" id="rejected-count">0</span>
            </button>
        </div>
        
        <div class="search-controls">
            <div class="search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <polyline points="21,21 16.65,16.65"></polyline>
                </svg>
                <input type="text" id="search-input" placeholder="Pesquisar por nome ou mês...">
            </div>
            <select id="month-filter" onchange="filterByMonth()">
                <option value="">Todos os meses</option>
            </select>
        </div>
    </div>

    <!-- Lista de Aprovações -->
    <div class="approvals-container" id="approvals-container">
        <!-- As aprovações serão carregadas aqui dinamicamente -->
    </div>

    <!-- Modal de Detalhes -->
    <div id="details-modal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Detalhes da Marcação de Horários</h3>
                <button class="close-btn" onclick="closeDetailsModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="modal-details-body">
                <!-- Conteúdo carregado dinamicamente -->
            </div>
            <div class="modal-footer" id="modal-actions">
                <!-- Ações carregadas dinamicamente -->
            </div>
        </div>
    </div>

    <!-- Modal de Rejeição -->
    <div id="rejection-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Rejeitar Marcação</h3>
                <button class="close-btn" onclick="closeRejectionModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Motivo da rejeição:</label>
                    <textarea id="rejection-reason" rows="4" placeholder="Indique o motivo da rejeição das marcações..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeRejectionModal()">Cancelar</button>
                <button class="btn-danger" onclick="confirmRejection()">Rejeitar</button>
            </div>
        </div>
    </div>
</div>

<script>
class AprovacaoHorarios {
    constructor() {
        this.currentFilter = 'pending';
        this.approvals = [];
        this.currentApprovalId = null;
        this.init();
    }

    async init() {
        this.approvals = await this.loadApprovals();
        this.populateMonthFilter();
        this.renderApprovals();
        this.updateCounts();
    // Prefetch and correct operator names that may be missing or set to 'Operador'
    this.prefetchUserNames().catch(() => {});
    }

    async loadApprovals() {
        const pendingApprovals = JSON.parse(localStorage.getItem('marcacoes_pending_approval') || '[]');
        const processedApprovals = JSON.parse(localStorage.getItem('marcacoes_processed') || '[]');
        return [
            ...pendingApprovals.map(a => ({...a, status: 'pending'})),
            ...processedApprovals
        ];
    }

    populateMonthFilter() {
        const monthFilter = document.getElementById('month-filter');
    // Clear all options except the first ("Todos os meses")
    while (monthFilter.options.length > 1) monthFilter.remove(1);
        const months = new Set();
        
        this.approvals.forEach(approval => {
            months.add(approval.month);
        });

        Array.from(months).sort().forEach(month => {
            const option = document.createElement('option');
            option.value = month;
            option.textContent = this.formatMonthYear(month);
            monthFilter.appendChild(option);
        });
    }

    renderApprovals() {
        const container = document.getElementById('approvals-container');
        const filteredApprovals = this.getFilteredApprovals();

        if (filteredApprovals.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path>
                            <line x1="9" y1="9" x2="9.01" y2="9"></line>
                            <line x1="15" y1="9" x2="15.01" y2="9"></line>
                        </svg>
                    </div>
                    <h3>Nenhuma marcação encontrada</h3>
                    <p>Não há marcações ${this.getFilterLabel()} no momento.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = filteredApprovals.map(approval => this.createApprovalCard(approval)).join('');
    }

    createApprovalCard(approval) {
    const userName = approval.userName || approval.submittedBy || this.getUserName(approval.userId);
        const statusClass = approval.status || 'pending';
        const statusLabel = this.getStatusLabel(approval.status);
        
        return `
            <div class="approval-card ${statusClass}" data-approval-id="${approval.userId}-${approval.month}">
                <div class="card-header">
                    <div class="user-info">
                        <div class="user-avatar">
                            ${userName.charAt(0).toUpperCase()}
                        </div>
                        <div class="user-details">
                            <h4>${userName}</h4>
                            <span class="month-label">${this.formatMonthYear(approval.month)}</span>
                        </div>
                    </div>
                    <div class="status-badge ${statusClass}">
                        ${statusLabel}
                    </div>
                </div>
                
                <div class="card-summary">
                    <div class="summary-grid">
                        <div class="summary-item">
                            <span class="label">Dias Trabalhados</span>
                            <span class="value">${approval.summary.diasTrabalhados}</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Horas Totais</span>
                            <span class="value">${approval.summary.horasTotais}h</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Horas Extra</span>
                            <span class="value">${this.formatMinutes(approval.summary.horasExtra)}</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">KM Total</span>
                            <span class="value">${approval.summary.kmTotal} km</span>
                        </div>
                    </div>
                </div>
                
                <div class="card-actions">
                    <button class="btn-details" onclick="showApprovalDetails('${approval.userId}-${approval.month}')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6m6-6H6"></path>
                        </svg>
                        Ver Detalhes
                    </button>
                    
                    ${approval.status === 'pending' ? `
                        <button class="btn-approve" onclick="approveMarking('${approval.userId}-${approval.month}')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20,6 9,17 4,12"></polyline>
                            </svg>
                            Aprovar
                        </button>
                        <button class="btn-reject" onclick="rejectMarking('${approval.userId}-${approval.month}')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Rejeitar
                        </button>
                    ` : ''}
                    
                    ${approval.status === 'rejected' && approval.rejectionReason ? `
                        <div class="rejection-reason">
                            <strong>Motivo:</strong> ${approval.rejectionReason}
                        </div>
                    ` : ''}
                </div>
                
                <div class="card-footer">
                    <span class="submission-date">
                        Submetido em ${new Date(approval.dataExportacao).toLocaleDateString('pt-PT')}
                    </span>
                    ${approval.processedDate ? `
                        <span class="processed-date">
                            ${approval.status === 'approved' ? 'Aprovado' : 'Rejeitado'} em ${new Date(approval.processedDate).toLocaleDateString('pt-PT')}
                        </span>
                    ` : ''}
                </div>
            </div>
        `;
    }

    getFilteredApprovals() {
        let filtered = this.approvals;

        // Filtrar por status
        if (this.currentFilter !== 'all') {
            filtered = filtered.filter(a => (a.status || 'pending') === this.currentFilter);
        }

        // Filtrar por mês
        const monthFilter = document.getElementById('month-filter').value;
        if (monthFilter) {
            filtered = filtered.filter(a => a.month === monthFilter);
        }

        // Filtrar por pesquisa
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        if (searchTerm) {
            filtered = filtered.filter(a => 
                (a.userName || this.getUserName(a.userId)).toLowerCase().includes(searchTerm) ||
                this.formatMonthYear(a.month).toLowerCase().includes(searchTerm)
            );
        }

        return filtered.sort((a, b) => new Date(b.dataExportacao) - new Date(a.dataExportacao));
    }

    updateCounts() {
        const counts = {
            pending: this.approvals.filter(a => (a.status || 'pending') === 'pending').length,
            approved: this.approvals.filter(a => a.status === 'approved').length,
            rejected: this.approvals.filter(a => a.status === 'rejected').length
        };

        document.getElementById('pending-count').textContent = counts.pending;
        document.getElementById('approved-count').textContent = counts.approved;
        document.getElementById('rejected-count').textContent = counts.rejected;
    }

    async showApprovalDetails(approvalId) {
        const approval = this.findApproval(approvalId);
        if (!approval) return;

        const modal = document.getElementById('details-modal');
        const body = document.getElementById('modal-details-body');
        const actions = document.getElementById('modal-actions');

        // Loading state
        body.innerHTML = '<div style="padding:1rem;">A carregar…</div>';

    // Resolve name in background if missing/placeholder
    try { this.ensureUserName(approval.userId).catch(()=>{}); } catch(e) {}

    // Buscar detalhes do mês e férias/ausências do operador em paralelo
        try {
            const params = new URLSearchParams({ month: approval.month, user_id: String(approval.userId) });
            const [resMonth, resFer] = await Promise.all([
                fetch(`../../api/calendar/get_month.php?${params.toString()}`, { credentials: 'same-origin' }),
                fetch(`../../api/pedidos/listar_ferias_aprovadas_inter2.php?user_id=${encodeURIComponent(String(approval.userId))}`, { credentials: 'same-origin' })
            ]);

            const data = await resMonth.json();
            let feriasOverride = null;
            try {
                const ferJson = await resFer.json();
                if (ferJson && (ferJson.success || ferJson.ok) && ferJson.ferias && typeof ferJson.ferias === 'object') {
                    const labelMap = {
                        licenca_paternidade: 'Lic. Paternidade',
                        licenca_maternidade: 'Lic. Maternidade',
                        baixa_medica: 'Baixa Médica',
                        baixa_seguro: 'Baixa Seguro',
                        casamento: 'Casamento',
                        consulta_medica: 'Consulta Médica',
                        luto: 'Luto',
                        falta_justificada: 'Falta Justificada',
                        ferias: 'Férias'
                    };
                    feriasOverride = {};
                    Object.entries(ferJson.ferias).forEach(([date, v]) => {
                        const tipoRaw = (v && v.tipo) ? String(v.tipo) : '';
                        const isFerias = tipoRaw === 'ferias' || tipoRaw === 'vacation';
                        feriasOverride[date] = {
                            tipo: isFerias ? 'vacation' : 'absence',
                            label: isFerias ? 'Férias' : (labelMap[tipoRaw] || 'Ausência')
                        };
                    });
                }
            } catch (_) { /* ignore */ }

            if (!data.ok) throw new Error(data.code || 'API_ERROR');
            body.innerHTML = this.createDetailedCalendarFromApi(approval, data, feriasOverride);
        } catch (e) {
            console.error('Erro ao carregar detalhes do mês:', e);
            if (approval.marcacoes) {
                // Tentar ainda obter férias/ausências via API para o fallback local
                let feriasOverride = null;
                try {
                    const resFer = await fetch(`../../api/pedidos/listar_ferias_aprovadas_inter2.php?user_id=${encodeURIComponent(String(approval.userId))}`, { credentials: 'same-origin' });
                    const ferJson = await resFer.json();
                    if (ferJson && (ferJson.success || ferJson.ok) && ferJson.ferias) {
                        const labelMap = {
                            licenca_paternidade: 'Lic. Paternidade',
                            licenca_maternidade: 'Lic. Maternidade',
                            baixa_medica: 'Baixa Médica',
                            baixa_seguro: 'Baixa Seguro',
                            casamento: 'Casamento',
                            consulta_medica: 'Consulta Médica',
                            luto: 'Luto',
                            falta_justificada: 'Falta Justificada',
                            ferias: 'Férias'
                        };
                        feriasOverride = {};
                        Object.entries(ferJson.ferias).forEach(([date, v]) => {
                            const tipoRaw = (v && v.tipo) ? String(v.tipo) : '';
                            const isFerias = tipoRaw === 'ferias' || tipoRaw === 'vacation';
                            feriasOverride[date] = {
                                tipo: isFerias ? 'vacation' : 'absence',
                                label: isFerias ? 'Férias' : (labelMap[tipoRaw] || 'Ausência')
                            };
                        });
                    }
                } catch (_) {}
                body.innerHTML = this.createDetailedCalendarLocal(approval, feriasOverride);
            } else {
                body.innerHTML = '<div style="padding:1rem;color:#b91c1c;">Erro ao carregar detalhes.</div>';
            }
        }

        // Configurar ações
        if (approval.status === 'pending') {
            actions.innerHTML = `
                <button class="btn-secondary" onclick="closeDetailsModal()">Fechar</button>
                <button class="btn-danger" onclick="rejectMarkingFromModal('${approvalId}')">Rejeitar</button>
                <button class="btn-success" onclick="approveMarkingFromModal('${approvalId}')">Aprovar</button>
            `;
        } else {
            actions.innerHTML = `
                <button class="btn-secondary" onclick="closeDetailsModal()">Fechar</button>
            `;
        }

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // removed old createDetailedCalendar (replaced by API-driven version)

    async approveMarking(approvalId) {
        if (!confirm('Deseja aprovar estas marcações de horários?')) return;
        this.processApprovalLocal(approvalId, 'approved');
        await this.refresh();
    }

    rejectMarking(approvalId) {
        this.currentApprovalId = approvalId;
        document.getElementById('rejection-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    async confirmRejection() {
    const reason = document.getElementById('rejection-reason').value.trim();
    if (!reason) { alert('Por favor, indique o motivo da rejeição.'); return; }
    // Remover imediatamente da lista visual (UX imediato)
    try {
        const card = document.querySelector(`[data-approval-id="${this.currentApprovalId}"]`);
        if (card && card.parentNode) card.parentNode.removeChild(card);
    } catch (e) {}
    this.processApprovalLocal(this.currentApprovalId, 'rejected', reason);
    closeRejectionModal();
    await this.refresh();
    }

    async refresh() {
        this.approvals = await this.loadApprovals();
    this.populateMonthFilter();
        this.renderApprovals();
        this.updateCounts();
    }

    createDetailedCalendarFromApi(approval, data, feriasOverride = null) {
        const userName = approval.userName || approval.submittedBy || this.getUserName(approval.userId);
        const monthName = this.formatMonthYear(approval.month);
        const fmtHM = (min) => this.formatMinutes(min || 0);
        const [yearStr, monthStr] = approval.month.split('-');
        const year = parseInt(yearStr, 10);
        const month = parseInt(monthStr, 10) - 1; // 0-based

        // Index by YYYY-MM-DD
        const byDate = {};
        (data.days || []).forEach(d => { byDate[d.date] = d; });

        // Férias/Ausências aprovadas para o utilizador e mês atual
        const feriasMonth = (feriasOverride && Object.keys(feriasOverride).length)
            ? feriasOverride
            : this.getFeriasForMonth(approval.userId, year, month);

        const dayNames = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startDow = firstDay.getDay();

        const totalWork = data.totals?.workMin || 0;
        const totalExtra = data.totals?.otMin || 0;
        const diasTrabalhados = approval.summary?.diasTrabalhados ?? 0;
    let html = `
            <style>
                .aprov-cal .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; background: #e2e8f0; border-radius: 12px; overflow: hidden; }
                .aprov-cal .day-header { background: #0A2240; color: #fff; padding: 0.75rem; text-align: center; font-weight: 600; font-size: 0.9rem; }
                .aprov-cal .day-cell { background: #fff; min-height: 78px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.5rem; position: relative; }
                .aprov-cal .day-cell.marked { background: #065f46; color: #fff; border: 2px solid #059669; }
                .aprov-cal .day-number { font-weight: 700; }
                .aprov-cal .day-details { margin-top: .25rem; display:flex; flex-direction:column; align-items:center; width:100%; }
                .aprov-cal .badges { display:flex; flex-wrap:wrap; gap:.25rem; justify-content:center; }
                .aprov-cal .hour-badge { font-size:.7rem; padding:.2rem .45rem; border-radius: 999px; font-weight:700; line-height:1; }
                .aprov-cal .hour-badge.work { background:#10b981; color:#fff; }
                .aprov-cal .hour-badge.extra { background:#f59e0b; color:#fff; }
                .aprov-cal .hour-badge.prevention { background:#ef4444; color:#fff; }
                .aprov-cal .hour-badge.km { background:#3b82f6; color:#fff; }
                .aprov-cal .day-cell.ferias { background:#B91C1C; color:#fff; border: 2px solid #DC2626; }
                .aprov-cal .ferias-badge { margin-top: .35rem; background: rgba(255,255,255,.15); color:#fff; font-size:.68rem; padding:.15rem .5rem; border-radius:999px; font-weight:700; text-transform: uppercase; letter-spacing:.02em; }
            </style>
            <div class="details-header">
                <h4>${userName} - ${monthName}</h4>
                <div class="summary-stats">
                    <div class="stat"><span class="stat-label">Total de Horas:</span><span class="stat-value">${fmtHM(totalWork)}</span></div>
                    <div class="stat"><span class="stat-label">Horas Extra:</span><span class="stat-value">${fmtHM(totalExtra)}</span></div>
                    <div class="stat"><span class="stat-label">Dias Trabalhados:</span><span class="stat-value">${diasTrabalhados}</span></div>
                </div>
            </div>
            <div class="aprov-cal">
                <div class="calendar-grid">
                    ${dayNames.map(d => `<div class=\"day-header\">${d}</div>`).join('')}
        `;

    for (let i = 0; i < startDow; i++) html += `<div class="day-cell empty"></div>`;
    for (let day = 1; day <= daysInMonth; day++) {
            const dateKey = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            const d = byDate[dateKey];
            const work = d ? (d.workMin||0) : 0;
            const extra = d ? (d.otMin||0) : 0;
            const prev = d ? (d.oncallMin||0) : 0;
            const km = d && d.km ? d.km : 0;
            const fer = feriasMonth[dateKey];
            const has = work>0 || extra>0 || prev>0 || km>0;

            if (fer) {
                const label = fer.label || (fer.tipo === 'vacation' ? 'FÉRIAS' : 'AUSÊNCIA');
                html += `<div class=\"day-cell ferias\">`
                      + `<div class=\"day-number\">${day}</div>`
                      + `<div class=\"ferias-badge\">${label.toUpperCase()}</div>`
                      + `</div>`;
            } else {
                html += `<div class=\"day-cell ${has?'marked':''}\">`
                      + `<div class=\"day-number\">${day}</div>`
                      + `<div class=\"day-details\">`
                      +   `<div class=\"badges\">`
                      +     `${work?`<span class=\"hour-badge work\">${fmtHM(work)}</span>`:''}`
                      +     `${extra?`<span class=\"hour-badge extra\">${fmtHM(extra)}</span>`:''}`
                      +     `${prev?`<span class=\"hour-badge prevention\">${fmtHM(prev)}</span>`:''}`
                      +     `${km?`<span class=\"hour-badge km\">${km}km</span>`:''}`
                      +   `</div>`
                      + `</div>`
                      + `</div>`;
            }
        }

        html += `</div></div>`;
        return html;
    }

    // Funções auxiliares
    findApproval(approvalId) {
        return this.approvals.find(a => `${a.userId}-${a.month}` === approvalId);
    }

    processApprovalLocal(approvalId, status, reason = null) {
        let pending = JSON.parse(localStorage.getItem('marcacoes_pending_approval') || '[]');
        let processed = JSON.parse(localStorage.getItem('marcacoes_processed') || '[]');

        const idx = pending.findIndex(a => `${a.userId}-${a.month}` === approvalId);
        let item;
        if (idx !== -1) {
            item = pending.splice(idx, 1)[0];
        } else {
            const j = processed.findIndex(a => `${a.userId}-${a.month}` === approvalId);
            item = j !== -1 ? processed.splice(j, 1)[0] : null;
        }
        if (!item) return;

        item.status = status;
        item.processedDate = new Date().toISOString();
        if (reason) item.rejectionReason = reason;
        processed.push(item);

        localStorage.setItem('marcacoes_pending_approval', JSON.stringify(pending));
        localStorage.setItem('marcacoes_processed', JSON.stringify(processed));

        const label = status === 'approved' ? 'aprovadas' : 'rejeitadas';
        this.showToast(`Marcações ${label} (local)`, status === 'approved' ? 'success' : 'info');
    }

    createDetailedCalendarLocal(approval, feriasOverride = null) {
        const userName = approval.userName || approval.submittedBy || this.getUserName(approval.userId);
        const monthName = this.formatMonthYear(approval.month);
        const [yearStr, monthStr] = approval.month.split('-');
        const year = parseInt(yearStr, 10);
        const month = parseInt(monthStr, 10) - 1;

        const toMinutes = (hhmm) => {
            if (!hhmm || typeof hhmm !== 'string') return 0;
            const parts = hhmm.split(':');
            const h = parseInt(parts[0] || '0', 10);
            const m = parseInt(parts[1] || '0', 10);
            if (isNaN(h) && isNaN(m)) return 0;
            return (isNaN(h) ? 0 : h) * 60 + (isNaN(m) ? 0 : m);
        };

        const dayNames = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startDow = firstDay.getDay();

        const totalWorkH = approval.summary?.horasTotais ?? 0; // horas
        const totalExtraM = approval.summary?.horasExtra ?? 0; // minutos
        const diasTrabalhados = approval.summary?.diasTrabalhados ?? 0;

        // Férias/Ausências aprovadas para o utilizador e mês atual (fallback)
        const feriasMonth = (feriasOverride && Object.keys(feriasOverride).length)
            ? feriasOverride
            : this.getFeriasForMonth(approval.userId, year, month);

        let html = `
            <style>
                .aprov-cal .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; background: #e2e8f0; border-radius: 12px; overflow: hidden; }
                .aprov-cal .day-header { background: #0A2240; color: #fff; padding: 0.75rem; text-align: center; font-weight: 600; font-size: 0.9rem; }
                .aprov-cal .day-cell { background: #fff; min-height: 78px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.5rem; position: relative; }
                .aprov-cal .day-cell.marked { background: #065f46; color: #fff; border: 2px solid #059669; }
                .aprov-cal .day-number { font-weight: 700; }
                .aprov-cal .day-details { margin-top: .25rem; display:flex; flex-direction:column; align-items:center; width:100%; }
                .aprov-cal .badges { display:flex; flex-wrap:wrap; gap:.25rem; justify-content:center; }
                .aprov-cal .hour-badge { font-size:.7rem; padding:.2rem .45rem; border-radius: 999px; font-weight:700; line-height:1; }
                .aprov-cal .hour-badge.work { background:#10b981; color:#fff; }
                .aprov-cal .hour-badge.extra { background:#f59e0b; color:#fff; }
                .aprov-cal .hour-badge.prevention { background:#ef4444; color:#fff; }
                .aprov-cal .hour-badge.km { background:#3b82f6; color:#fff; }
                .aprov-cal .day-cell.ferias { background:#B91C1C; color:#fff; border: 2px solid #DC2626; }
                .aprov-cal .ferias-badge { margin-top: .35rem; background: rgba(255,255,255,.15); color:#fff; font-size:.68rem; padding:.15rem .5rem; border-radius:999px; font-weight:700; text-transform: uppercase; letter-spacing:.02em; }
            </style>
            <div class="details-header">
                <h4>${userName} - ${monthName}</h4>
                <div class="summary-stats">
                    <div class="stat"><span class="stat-label">Total de Horas:</span><span class="stat-value">${Number(totalWorkH).toFixed(1)}h</span></div>
                    <div class="stat"><span class="stat-label">Horas Extra:</span><span class="stat-value">${this.formatMinutes(totalExtraM)}</span></div>
                    <div class="stat"><span class="stat-label">Dias Trabalhados:</span><span class="stat-value">${diasTrabalhados}</span></div>
                </div>
            </div>
            <div class="aprov-cal">
                <div class="calendar-grid">
                    ${dayNames.map(d => `<div class=\"day-header\">${d}</div>`).join('')}
        `;

    for (let i = 0; i < startDow; i++) html += `<div class=\"day-cell empty\"></div>`;
    for (let day = 1; day <= daysInMonth; day++) {
            const dateKey = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            const m = approval.marcacoes ? approval.marcacoes[dateKey] : null;
            const workM = m ? toMinutes(m.horasTrabalhadas) : 0;
            const extraM = m ? toMinutes(m.horasExtra) : 0;
            const prevM = m ? toMinutes(m.horasPrevencao) : 0;
            const km = m && m.kmViatura ? parseInt(m.kmViatura,10) : 0;
            const fer = feriasMonth[dateKey];
            const has = workM>0 || extraM>0 || prevM>0 || km>0;

            if (fer) {
                const label = fer.label || (fer.tipo === 'vacation' ? 'FÉRIAS' : 'AUSÊNCIA');
                html += `<div class=\"day-cell ferias\">`
                      + `<div class=\"day-number\">${day}</div>`
                      + `<div class=\"ferias-badge\">${label.toUpperCase()}</div>`
                      + `</div>`;
            } else {
                html += `<div class=\"day-cell ${has?'marked':''}\">`
                      + `<div class=\"day-number\">${day}</div>`
                      + `<div class=\"day-details\">`
                      +   `<div class=\"badges\">`
                      +     `${workM?`<span class=\"hour-badge work\">${this.formatMinutes(workM)}</span>`:''}`
                      +     `${extraM?`<span class=\"hour-badge extra\">${this.formatMinutes(extraM)}</span>`:''}`
                      +     `${prevM?`<span class=\"hour-badge prevention\">${this.formatMinutes(prevM)}</span>`:''}`
                      +     `${km?`<span class=\"hour-badge km\">${km}km</span>`:''}`
                      +   `</div>`
                      + `</div>`
                      + `</div>`;
            }
        }

        html += `</div></div>`;
        return html;
    }

    getUserName(userId) {
        // Simulação - em produção viria da base de dados
        const users = {
            'user_1': 'João Silva',
            'user_2': 'Maria Santos',
            'user_3': 'Carlos Oliveira'
        };
        return users[userId] || `Operador ${userId.slice(-3)}`;
    }

    // ========= Name resolution & caching =========
    async prefetchUserNames() {
        try {
            const need = Array.from(new Set(this.approvals
                .filter(a => !a.userName || /^\s*operador\s*$/i.test(a.userName))
                .map(a => a.userId)
            ));
            if (!need.length) return;
            await Promise.allSettled(need.map(uid => this.ensureUserName(uid)));
            // Reload approvals after patching storage
            this.approvals = await this.loadApprovals();
            this.renderApprovals();
            this.updateCounts();
        } catch (e) { /* ignore */ }
    }

    async ensureUserName(userId) {
        const cached = this.getCachedUserName(userId);
        if (cached) return cached;
        const name = await this.resolveUserNameFromPage(userId);
        if (name) {
            this.cacheUserName(userId, name);
            this.patchApprovalsWithName(userId, name);
            // Update any rendered card for this user
            try {
                document.querySelectorAll(`[data-approval-id^="${CSS.escape(String(userId))}-"] .user-details h4`).forEach(el => el.textContent = name);
                document.querySelectorAll(`[data-approval-id^="${CSS.escape(String(userId))}-"] .user-avatar`).forEach(el => el.textContent = name.charAt(0).toUpperCase());
            } catch (_) {}
            return name;
        }
        return null;
    }

    getCachedUserName(userId) {
        try {
            const map = JSON.parse(localStorage.getItem('user_names_map') || '{}');
            return map[String(userId)] || null;
        } catch { return null; }
    }

    cacheUserName(userId, name) {
        try {
            const key = 'user_names_map';
            const map = JSON.parse(localStorage.getItem(key) || '{}');
            map[String(userId)] = name;
            localStorage.setItem(key, JSON.stringify(map));
        } catch(_) {}
    }

    patchApprovalsWithName(userId, name) {
        try {
            const keyPending = 'marcacoes_pending_approval';
            const keyProcessed = 'marcacoes_processed';
            const pending = JSON.parse(localStorage.getItem(keyPending) || '[]');
            const processed = JSON.parse(localStorage.getItem(keyProcessed) || '[]');
            let changed = false;
            pending.forEach(r => { if (String(r.userId) === String(userId) && (!r.userName || /^\s*operador\s*$/i.test(r.userName))) { r.userName = name; changed = true; } });
            processed.forEach(r => { if (String(r.userId) === String(userId) && (!r.userName || /^\s*operador\s*$/i.test(r.userName))) { r.userName = name; changed = true; } });
            if (changed) {
                localStorage.setItem(keyPending, JSON.stringify(pending));
                localStorage.setItem(keyProcessed, JSON.stringify(processed));
            }
        } catch (_) {}
    }

    async resolveUserNameFromPage(userId) {
        try {
            const res = await fetch(`../inter2/visualizar_lista_operadores.php?user_id=${encodeURIComponent(String(userId))}`, { credentials: 'same-origin' });
            if (!res.ok) return null;
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            // Primary: header h3
            let name = (doc.querySelector('.ficha-header h3') || {}).textContent || '';
            name = name ? name.trim() : '';
            if (!name) {
                // Fallback: Nome field value
                const labelEls = Array.from(doc.querySelectorAll('.info-item .label'));
                for (const lab of labelEls) {
                    if (lab.textContent && lab.textContent.trim().toLowerCase() === 'nome') {
                        const val = lab.parentElement && lab.parentElement.querySelector('.value');
                        if (val && val.textContent) { name = val.textContent.trim(); break; }
                    }
                }
            }
            if (name) return name;
        } catch (_) { /* ignore */ }
        return null;
    }

    getCurrentUserName() {
        return 'Supervisor'; // Em produção viria da sessão
    }

    formatMonthYear(monthKey) {
        const [year, month] = monthKey.split('-');
        const months = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];
        return `${months[parseInt(month) - 1]} ${year}`;
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

    getStatusLabel(status) {
        const labels = {
            pending: 'Pendente',
            approved: 'Aprovado',
            rejected: 'Rejeitado'
        };
        return labels[status] || 'Pendente';
    }

    getFilterLabel() {
        const labels = {
            pending: 'pendentes',
            approved: 'aprovadas',
            rejected: 'rejeitadas'
        };
        return labels[this.currentFilter] || '';
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

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // ======= Férias/Ausências helpers =======
    getFeriasForMonth(userId, year, monthZeroBased) {
        const monthKey = `${year}-${String(monthZeroBased + 1).padStart(2, '0')}`;
        const all = this.getFeriasByUser(userId);
        const filtered = {};
        Object.entries(all).forEach(([date, obj]) => {
            if (date.startsWith(monthKey)) filtered[date] = obj;
        });
        return filtered;
    }

    getFeriasByUser(userId) {
        try {
            if (window.integracaoFeriasHorarios && typeof window.integracaoFeriasHorarios.getUserFeriasAusencias === 'function') {
                const data = window.integracaoFeriasHorarios.getUserFeriasAusencias(userId);
                if (data && typeof data === 'object') return data;
            }
        } catch (e) { /* ignore */ }

        // Fallback ao localStorage cru
        let all = {};
        try { all = JSON.parse(localStorage.getItem('ferias_ausencias_aprovadas') || '{}'); } catch { all = {}; }
        const result = {};
        Object.entries(all).forEach(([date, v]) => {
            if (v && v.userId === userId) result[date] = v;
        });
        return result;
    }
}

// Funções globais
let aprovacaoHorarios;

document.addEventListener('DOMContentLoaded', function() {
    aprovacaoHorarios = new AprovacaoHorarios();
    
    // Event listeners
    document.getElementById('search-input').addEventListener('input', () => {
        aprovacaoHorarios.renderApprovals();
    });
});

function filterApprovals(filter) {
    // Atualizar botões ativos
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
    
    aprovacaoHorarios.currentFilter = filter;
    aprovacaoHorarios.refresh();
}

function filterByMonth() {
    aprovacaoHorarios.renderApprovals();
}

function showApprovalDetails(approvalId) {
    aprovacaoHorarios.showApprovalDetails(approvalId);
}

function approveMarking(approvalId) {
    aprovacaoHorarios.approveMarking(approvalId);
}

function rejectMarking(approvalId) {
    aprovacaoHorarios.rejectMarking(approvalId);
}

function approveMarkingFromModal(approvalId) {
    aprovacaoHorarios.approveMarking(approvalId);
    closeDetailsModal();
}

function rejectMarkingFromModal(approvalId) {
    closeDetailsModal();
    aprovacaoHorarios.rejectMarking(approvalId);
}

function closeDetailsModal() {
    document.getElementById('details-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function closeRejectionModal() {
    document.getElementById('rejection-modal').style.display = 'none';
    document.getElementById('rejection-reason').value = '';
    document.body.style.overflow = '';
}

function confirmRejection() {
    aprovacaoHorarios.confirmRejection();
}
</script>

<script src="../../js/horarios_init.js"></script>
<script src="../../js/integracao_ferias_horarios.js"></script>
<script src="../../js/dados_demonstracao.js"></script>
