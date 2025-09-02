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
        this.approvals = this.loadApprovals();
        this.currentApprovalId = null;
        this.init();
    }

    init() {
        this.populateMonthFilter();
        this.renderApprovals();
        this.updateCounts();
    }

    loadApprovals() {
        // Carregar dados de localStorage (simulação)
        const pendingApprovals = JSON.parse(localStorage.getItem('marcacoes_pending_approval') || '[]');
        const processedApprovals = JSON.parse(localStorage.getItem('marcacoes_processed') || '[]');
        
        return [...pendingApprovals.map(a => ({...a, status: 'pending'})), ...processedApprovals];
    }

    populateMonthFilter() {
        const monthFilter = document.getElementById('month-filter');
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
        const userName = this.getUserName(approval.userId);
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
                this.getUserName(a.userId).toLowerCase().includes(searchTerm) ||
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

    showApprovalDetails(approvalId) {
        const approval = this.findApproval(approvalId);
        if (!approval) return;

        const modal = document.getElementById('details-modal');
        const body = document.getElementById('modal-details-body');
        const actions = document.getElementById('modal-actions');

        // Criar calendário detalhado
        body.innerHTML = this.createDetailedCalendar(approval);

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

    createDetailedCalendar(approval) {
        const userName = this.getUserName(approval.userId);
        const monthName = this.formatMonthYear(approval.month);

        let html = `
            <div class="details-header">
                <h4>${userName} - ${monthName}</h4>
                <div class="summary-stats">
                    <div class="stat">
                        <span class="stat-label">Total de Horas:</span>
                        <span class="stat-value">${approval.summary.horasTotais}h</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Horas Extra:</span>
                        <span class="stat-value">${this.formatMinutes(approval.summary.horasExtra)}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Dias Trabalhados:</span>
                        <span class="stat-value">${approval.summary.diasTrabalhados}</span>
                    </div>
                </div>
            </div>
            
            <div class="detailed-calendar">
                <div class="calendar-headers">
                    <div class="header">Data</div>
                    <div class="header">Horário</div>
                    <div class="header">H. Extra</div>
                    <div class="header">Prevenção</div>
                    <div class="header">KM</div>
                    <div class="header">Observações</div>
                </div>
        `;

        Object.entries(approval.marcacoes).forEach(([date, marcacao]) => {
            const dateObj = new Date(date);
            const formattedDate = dateObj.toLocaleDateString('pt-PT', {
                day: '2-digit',
                month: '2-digit',
                weekday: 'short'
            });

            html += `
                <div class="calendar-row">
                    <div class="cell date-cell">${formattedDate}</div>
                    <div class="cell">
                        ${marcacao.tipo === 'trabalho' ? 
                            `${marcacao.horaInicio} - ${marcacao.horaFim}` : 
                            'Descanso'
                        }
                    </div>
                    <div class="cell">${marcacao.horasExtra ? this.formatMinutes(marcacao.horasExtra) : '-'}</div>
                    <div class="cell">${marcacao.horasPrevencao ? this.formatMinutes(marcacao.horasPrevencao) : '-'}</div>
                    <div class="cell">${marcacao.kmViatura || '-'}</div>
                    <div class="cell observacoes">${marcacao.observacoes || '-'}</div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    approveMarking(approvalId) {
        if (confirm('Deseja aprovar estas marcações de horários?')) {
            this.processApproval(approvalId, 'approved');
        }
    }

    rejectMarking(approvalId) {
        this.currentApprovalId = approvalId;
        document.getElementById('rejection-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    confirmRejection() {
        const reason = document.getElementById('rejection-reason').value.trim();
        if (!reason) {
            alert('Por favor, indique o motivo da rejeição.');
            return;
        }

        this.processApproval(this.currentApprovalId, 'rejected', reason);
        this.closeRejectionModal();
    }

    processApproval(approvalId, status, reason = null) {
        const approval = this.findApproval(approvalId);
        if (!approval) return;

        // Atualizar status
        approval.status = status;
        approval.processedDate = new Date().toISOString();
        approval.processedBy = this.getCurrentUserName();
        
        if (reason) {
            approval.rejectionReason = reason;
        }

        // Mover para processados
        let pendingApprovals = JSON.parse(localStorage.getItem('marcacoes_pending_approval') || '[]');
        let processedApprovals = JSON.parse(localStorage.getItem('marcacoes_processed') || '[]');

        // Remover dos pendentes
        pendingApprovals = pendingApprovals.filter(a => `${a.userId}-${a.month}` !== approvalId);
        
        // Adicionar aos processados
        processedApprovals.push(approval);

        // Salvar
        localStorage.setItem('marcacoes_pending_approval', JSON.stringify(pendingApprovals));
        localStorage.setItem('marcacoes_processed', JSON.stringify(processedApprovals));

        // Atualizar interface
        this.approvals = this.loadApprovals();
        this.renderApprovals();
        this.updateCounts();

        // Feedback
        const statusLabel = status === 'approved' ? 'aprovadas' : 'rejeitadas';
        this.showToast(`Marcações ${statusLabel} com sucesso!`, status === 'approved' ? 'success' : 'info');
    }

    // Funções auxiliares
    findApproval(approvalId) {
        return this.approvals.find(a => `${a.userId}-${a.month}` === approvalId);
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
    aprovacaoHorarios.renderApprovals();
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
