import { getOvertimeHistory } from '../../app/api.js';
import { openHistoryDetailModal } from './horas_extra_history_modal.js';

function getCurrentMonth() {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
}

function getStateLabel(state) {
    return state === 'approved' ? 'Aprovado' : 'Recusado';
}

function setEmptyState(empty, message) {
    if (!empty) return;
    empty.style.display = 'block';
    empty.textContent = message;
}

function hideEmptyState(empty) {
    if (empty) empty.style.display = 'none';
}

function renderHistory({ items, tbody, empty, esc, formatDate, formatMinutes }) {
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        setEmptyState(empty, 'Nenhum pedido encontrado para os filtros selecionados.');
        return;
    }

    hideEmptyState(empty);

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
            <td><span class="he-badge he-badge--${item.estado}">${getStateLabel(item.estado)}</span></td>
            <td>${item.decidido_por ? esc(item.decidido_por.nome) : '-'}</td>
            <td class="he-col-justif">${item.justificacao ? `<span class="he-justif-icon" data-tooltip="${esc(item.justificacao)}">i</span>` : '<span class="he-no-data">-</span>'}</td>
            <td class="he-col-justif">${item.comentario ? `<span class="he-justif-icon" data-tooltip="${esc(item.comentario)}">i</span>` : '<span class="he-no-data">-</span>'}</td>
            <td><button type="button" class="he-action-btn he-action-btn--detail" data-detail-id="${item.id}">Ver</button></td>
        `;
        tbody.appendChild(tr);
    });
}

function renderHistoryStats(stats, totals) {
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

export function mountHistory({ horasState, esc, formatDate, formatMinutes, openModal }) {
    const monthInput = document.getElementById('he-hist-month');
    const stateSelect = document.getElementById('he-hist-state');
    const searchInput = document.getElementById('he-hist-search');
    const tbody = document.getElementById('he-hist-tbody');
    const empty = document.getElementById('he-hist-empty');
    const stats = document.getElementById('he-hist-stats');
    const filterBtn = document.getElementById('he-hist-filter-btn');

    async function loadHistory() {
        const month = monthInput?.value || getCurrentMonth();
        if (monthInput && !monthInput.value) monthInput.value = month;

        const state = stateSelect?.value || 'both';
        const q = searchInput?.value?.trim() || '';

        if (!tbody) return;

        tbody.innerHTML = '';
        if (stats) stats.innerHTML = '';
        setEmptyState(empty, 'A carregar...');

        try {
            const res = await getOvertimeHistory({ month, state, q });
            if (!res?.ok) throw new Error(res?.code || 'Erro');

            horasState.history = res.items;
            renderHistory({
                items: horasState.history,
                tbody,
                empty,
                esc,
                formatDate,
                formatMinutes,
            });
            renderHistoryStats(stats, res.totals);
        } catch {
            horasState.history = [];
            setEmptyState(empty, 'Não foi possível carregar o histórico.');
        }
    }

    tbody?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-detail-id]');
        if (!btn) return;

        const id = Number(btn.dataset.detailId);
        const item = horasState.history?.find(i => i.id === id);
        if (!item) return;

        openHistoryDetailModal({
            item,
            openModal,
            esc,
            formatDate,
            formatMinutes,
        });
    });

    filterBtn?.addEventListener('click', () => {
        horasState.history = null;
        loadHistory();
    });

    return { loadHistory };
}