import { getOvertimeHistory } from '../../app/api.js';

function getCurrentMonth() {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
}

export function initHistory({ horasState, esc, formatDate, formatMinutes, openModal }) {

    async function loadHistory() {
        const monthInput = document.getElementById('he-hist-month');
        const stateSelect = document.getElementById('he-hist-state');
        const searchInput = document.getElementById('he-hist-search');

        const month = monthInput?.value || getCurrentMonth();
        if (monthInput && !monthInput.value) monthInput.value = month;

        const state = stateSelect?.value || 'both';
        const q = searchInput?.value?.trim() || '';

        const tbody = document.getElementById('he-hist-tbody');
        const empty = document.getElementById('he-hist-empty');
        const stats = document.getElementById('he-hist-stats');
        if (!tbody) return;

        tbody.innerHTML = '';
        if (empty) { empty.style.display = 'block'; empty.textContent = 'A carregar...'; }
        if (stats) stats.innerHTML = '';

        try {
            const res = await getOvertimeHistory({ month, state, q });
            if (!res?.ok) throw new Error(res?.code || 'Erro');

            horasState.history = res.items;
            renderHistory(horasState.history);
            renderHistoryStats(res.totals);
        } catch {
            horasState.history = [];
            if (empty) { empty.style.display = 'block'; empty.textContent = 'Não foi possível carregar o histórico.'; }
        }
    }

    function renderHistory(items) {
        const tbody = document.getElementById('he-hist-tbody');
        const empty = document.getElementById('he-hist-empty');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            if (empty) { empty.style.display = 'block'; empty.textContent = 'Nenhum pedido encontrado para os filtros selecionados.'; }
            return;
        }
        if (empty) empty.style.display = 'none';

        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="he-collab-name">${esc(item.colaborador.nome)}</div>
                    <div class="he-collab-email">${esc(item.colaborador.email)}</div>
                </td>
                <td>${formatDate(item.dia)}</td>
                <td>${esc(item.hora_inicio)}</td>
                <td>${esc(item.hora_fim)}</td>
                <td>${formatMinutes(item.req_minutos)}</td>
                <td><span class="he-badge he-badge--${item.estado}">${item.estado === 'approved' ? 'Aprovado' : 'Recusado'}</span></td>
                <td>${item.decidido_por ? esc(item.decidido_por.nome) : '-'}</td>
                <td class="he-col-justif">${item.justificacao ? `<span class="he-justif-icon" data-tooltip="${esc(item.justificacao)}">i</span>` : '<span class="he-no-data">-</span>'}</td>
                <td class="he-col-justif">${item.comentario ? `<span class="he-justif-icon" data-tooltip="${esc(item.comentario)}">i</span>` : '<span class="he-no-data">-</span>'}</td>
                <td><button type="button" class="he-action-btn he-action-btn--detail" data-detail-id="${item.id}">Ver</button></td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderHistoryStats(totals) {
        const stats = document.getElementById('he-hist-stats');
        if (!stats || !totals) return;

        stats.innerHTML = `
            <div class="he-stat">
                <span>Total:</span>
                <span class="he-stat-number">${totals.all}</span>
            </div>
            <div class="he-stat">
                <span>Aprovados:</span>
                <span class="he-stat-number" style="color:#166534">${totals.approved}</span>
            </div>
            <div class="he-stat">
                <span>Recusados:</span>
                <span class="he-stat-number" style="color:#991b1b">${totals.rejected}</span>
            </div>
        `;
    }

    function openDetailModal(item) {
        const m = openModal({ title: 'Detalhe do pedido' });

        m.body.innerHTML = `
            <div class="ui-modal-detail-grid">
                <div class="ui-modal-detail-field">
                    <span class="ui-modal-detail-label">Colaborador</span>
                    <span class="ui-modal-detail-value">${esc(item.colaborador.nome)}</span>
                </div>
                <div class="ui-modal-detail-field">
                    <span class="ui-modal-detail-label">Email</span>
                    <span class="ui-modal-detail-value">${esc(item.colaborador.email)}</span>
                </div>
                ${item.colaborador.empresa ? `
                <div class="ui-modal-detail-field">
                    <span class="ui-modal-detail-label">Empresa</span>
                    <span class="ui-modal-detail-value">${esc(item.colaborador.empresa)}</span>
                </div>` : ''}
                <div class="ui-modal-detail-field">
                    <span class="ui-modal-detail-label">Dia</span>
                    <span class="ui-modal-detail-value">${formatDate(item.dia)}</span>
                </div>
                <div class="ui-modal-detail-field">
                    <span class="ui-modal-detail-label">Horário</span>
                    <span class="ui-modal-detail-value">${esc(item.hora_inicio)} — ${esc(item.hora_fim)} (${formatMinutes(item.req_minutos)})</span>
                </div>
                <div class="ui-modal-detail-field">
                    <span class="ui-modal-detail-label">Estado</span>
                    <span class="ui-modal-detail-value"><span class="he-badge he-badge--${item.estado}">${item.estado === 'approved' ? 'Aprovado' : 'Recusado'}</span></span>
                </div>
                <div class="ui-modal-detail-field">
                    <span class="ui-modal-detail-label">Decidido por</span>
                    <span class="ui-modal-detail-value">${item.decidido_por ? esc(item.decidido_por.nome) : '-'}</span>
                </div>
                <div class="ui-modal-detail-field">
                    <span class="ui-modal-detail-label">Data da decisão</span>
                    <span class="ui-modal-detail-value">${item.decidido_em || '-'}</span>
                </div>
                ${item.justificacao ? `
                <div class="ui-modal-detail-field full-width">
                    <span class="ui-modal-detail-label">Justificação</span>
                    <span class="ui-modal-detail-value">${esc(item.justificacao)}</span>
                </div>` : ''}
                ${item.comentario ? `
                <div class="ui-modal-detail-field full-width">
                    <span class="ui-modal-detail-label">Comentário</span>
                    <span class="ui-modal-detail-value">${esc(item.comentario)}</span>
                </div>` : ''}
            </div>
        `;

        m.footer.innerHTML = `<button type="button" class="btn-secondary he-modal-close-btn">Fechar</button>`;
        m.footer.querySelector('.he-modal-close-btn')?.addEventListener('click', m.close);
    }

    document.getElementById('he-hist-tbody')?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-detail-id]');
        if (!btn) return;
        const id = Number(btn.dataset.detailId);
        const item = horasState.history?.find(i => i.id === id);
        if (item) openDetailModal(item);
    });

    document.getElementById('he-hist-filter-btn')?.addEventListener('click', () => {
        horasState.history = null;
        loadHistory();
    });

    return { loadHistory };
}
