import {
    getOvertimeRequests,
    approveOvertime,
} from '../../app/api.js';
import { createOverlays } from '../../app/overlays.js';
import { mountHistory } from './horas_extra_history.js';
import { mountExport } from './horas_extra_export.js';
import './styles.css';

const horasState = { pending: null, history: null, export: null };

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function formatDate(iso) {
    if (!iso) return '-';
    const [y, m, d] = iso.split('-');
    return `${d}/${m}/${y}`;
}

function formatMinutes(m) {
    const h = Math.floor(m / 60);
    const min = m % 60;
    return h > 0 ? `${h}h${min ? String(min).padStart(2, '0') + 'm' : ''}` : `${min}m`;
}

function bindTabs(onHistoryTab) {
    const tabs = {
        'he-tab-pendentes': 'he-view-pendentes',
        'he-tab-historico': 'he-view-historico',
        'he-tab-exportar': 'he-view-exportar',
    };

    Object.entries(tabs).forEach(([tabId, viewId]) => {
        const tab = document.getElementById(tabId);
        if (!tab) return;
        tab.addEventListener('click', () => {
            Object.entries(tabs).forEach(([tId, vId]) => {
                document.getElementById(tId)?.classList.remove('he-tab--active');
                document.getElementById(vId)?.classList.remove('he-view--active');
            });
            tab.classList.add('he-tab--active');
            document.getElementById(viewId)?.classList.add('he-view--active');

            if (viewId === 'he-view-pendentes' && horasState.pending === null) loadPending();
            if (viewId === 'he-view-historico' && horasState.history === null) onHistoryTab();
        });
    });
}

async function loadPending(q = '') {
    const tbody = document.getElementById('he-pend-tbody');
    const empty = document.getElementById('he-pend-empty');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (empty) { empty.style.display = 'block'; empty.textContent = 'A carregar...'; }

    try {
        const params = { state: 'requested' };
        if (q) params.q = q;
        const res = await getOvertimeRequests(params);
        if (!res?.ok) throw new Error(res?.code || 'Erro');

        horasState.pending = res.items;
        renderPending(horasState.pending);
    } catch {
        horasState.pending = [];
        if (empty) { empty.style.display = 'block'; empty.textContent = 'Não foi possível carregar os pedidos.'; }
    }
}

function renderPending(items) {
    const tbody = document.getElementById('he-pend-tbody');
    const empty = document.getElementById('he-pend-empty');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        if (empty) { empty.style.display = 'block'; empty.textContent = 'Não existem pedidos pendentes.'; }
        return;
    }
    if (empty) empty.style.display = 'none';

    items.forEach(item => {
        const tr = document.createElement('tr');
        tr.dataset.id = String(item.id);
        tr.innerHTML = `
            <td>
                <div class="he-collab-name">${esc(item.colaborador.nome)}</div>
                <div class="he-collab-email">${esc(item.colaborador.email)}</div>
            </td>
            <td>${formatDate(item.dia)}</td>
            <td>${esc(item.hora_inicio)}</td>
            <td>${esc(item.hora_fim)}</td>
            <td>${formatMinutes(item.minutos)}</td>
            <td>${esc(item.criado_por.nome)}</td>
            <td class="he-col-justif">${item.justificacao ? `<span class="he-justif-icon" data-tooltip="${esc(item.justificacao)}">i</span>` : '<span class="he-no-data">-</span>'}</td>
            <td>
                <div class="he-action-group">
                    <button type="button" class="he-action-btn he-action-btn--approve" data-action="approve" data-id="${item.id}">Aprovar</button>
                    <button type="button" class="he-action-btn he-action-btn--reject" data-action="reject" data-id="${item.id}">Recusar</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function bindPendingActions(openModal) {
    const tbody = document.getElementById('he-pend-tbody');
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const action = btn.dataset.action;
        const id = Number(btn.dataset.id);
        const item = horasState.pending?.find(i => i.id === id);
        if (!item) return;
        openDecisionModal(item, action, openModal);
    });
}

function bindPendingSearch() {
    const input = document.getElementById('he-pend-search');
    if (!input) return;

    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            horasState.pending = null;
            loadPending(input.value.trim());
        }, 400);
    });
}

function openDecisionModal(item, action, openModal) {
    const isApprove = action === 'approve';
    const m = openModal({ title: isApprove ? 'Aprovar pedido' : 'Recusar pedido' });

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
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Dia</span>
                <span class="ui-modal-detail-value">${formatDate(item.dia)}</span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Horário</span>
                <span class="ui-modal-detail-value">${esc(item.hora_inicio)} — ${esc(item.hora_fim)} (${formatMinutes(item.minutos)})</span>
            </div>
            <div class="ui-modal-detail-field full-width">
                <span class="ui-modal-detail-label">Proposto por</span>
                <span class="ui-modal-detail-value">${esc(item.criado_por.nome)}</span>
            </div>
            ${item.justificacao ? `
            <div class="ui-modal-detail-field full-width">
                <span class="ui-modal-detail-label">Justificação</span>
                <span class="ui-modal-detail-value">${esc(item.justificacao)}</span>
            </div>` : ''}
        </div>
        <textarea id="he-modal-comment" placeholder="Comentário (opcional)..."></textarea>
    `;

    m.footer.innerHTML = `
        <button type="button" class="btn-secondary he-modal-cancel">Cancelar</button>
        <button type="button" class="btn-primary he-modal-confirm"
                style="${isApprove ? '' : 'background:linear-gradient(135deg,#ef4444,#dc2626);'}">
            ${isApprove ? 'Aprovar' : 'Recusar'}
        </button>
    `;

    m.footer.querySelector('.he-modal-cancel')?.addEventListener('click', m.close);

    m.footer.querySelector('.he-modal-confirm')?.addEventListener('click', async () => {
        const confirmBtn = m.footer.querySelector('.he-modal-confirm');
        if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.textContent = 'A processar...'; }

        const comentario = m.body.querySelector('#he-modal-comment')?.value?.trim() || null;

        let res;
        try {
            res = await approveOvertime({
                request_id: item.id,
                decision: action,
                comentario
            });

            if (!res?.ok) throw new Error('Erro');

            m.close();
            horasState.pending = null;
            horasState.history = null;
            loadPending();
        } catch {
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = isApprove ? 'Aprovar' : 'Recusar'; }
            alert('Erro ao processar a decisão.');
        }
    });
}

export function mountHorasExtra() {
    const ol = createOverlays();
    const deps = { horasState, esc, formatDate, formatMinutes, openModal: ol.openModal };

    const { loadHistory } = mountHistory(deps);
    mountExport(deps);

    bindTabs(loadHistory);
    bindPendingActions(ol.openModal);
    bindPendingSearch();
    loadPending();

    return () => ol.closeActive();
}
