import {
    getLeaveApprovalSummary,
    getLeaveApprovalRequests,
    getLeaveApprovalHistory,
    decideLeaveRequest,
} from '../../app/api.js';
import { createOverlays } from '../../app/overlays.js';
import { TYPE_LABELS, typeLabel, typeChipClass } from './aprovacao_ferias_fields.js';
import './styles.css';
import './modal.css';

const state = {
    summary: null,
    pending: null,
    history: null,
    filterType: 'all',
};

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function formatDate(iso) {
    if (!iso) return '-';
    const onlyDate = String(iso).slice(0, 10);
    const [y, m, d] = onlyDate.split('-');
    if (!y || !m || !d) return iso;
    return `${d}/${m}/${y}`;
}

function setStat(value) {
    const el = document.getElementById('aprov-stat-pending');
    if (el) el.textContent = String(value ?? 0);
}

async function loadSummary() {
    try {
        const res = await getLeaveApprovalSummary();
        state.summary = res;
        setStat(res?.pending ?? 0);
    } catch {
        setStat('—');
    }
}

async function loadPending() {
    const list = document.getElementById('aprov-pending-list');
    const empty = document.getElementById('aprov-pending-empty');
    if (!list) return;

    list.innerHTML = '';
    if (empty) {
        list.appendChild(empty);
        empty.style.display = 'block';
        empty.textContent = 'A carregar...';
    }

    try {
        const res = await getLeaveApprovalRequests({ type: state.filterType });
        if (!res?.ok) throw new Error(res?.code || 'Erro');

        state.pending = res.items || [];

        if (res.has_subs === false) {
            empty.textContent = 'Não tem colaboradores a seu cargo.';
            return;
        }

        renderPending(state.pending);
    } catch {
        state.pending = [];
        if (empty) {
            list.innerHTML = '';
            list.appendChild(empty);
            empty.style.display = 'block';
            empty.textContent = 'Não foi possível carregar os pedidos.';
        }
    }
}

function renderPending(items) {
    const list = document.getElementById('aprov-pending-list');
    const empty = document.getElementById('aprov-pending-empty');
    if (!list) return;

    list.innerHTML = '';

    if (!items || items.length === 0) {
        if (empty) {
            list.appendChild(empty);
            empty.style.display = 'block';
            empty.textContent = 'Não existem pedidos pendentes.';
        }
        return;
    }

    items.forEach(item => list.appendChild(renderPendingCard(item)));
}

function renderPendingCard(item) {
    const card = document.createElement('article');
    card.className = 'aprov-card';
    card.dataset.id = String(item.pedido_id);

    const attach = item.comprovativo
        ? `<div class="aprov-card-attach"><a href="${esc(item.comprovativo)}" target="_blank" rel="noopener">Ver comprovativo</a></div>`
        : '';

    const justif = item.justificacao
        ? `<div class="aprov-card-justif">${esc(item.justificacao)}</div>`
        : '';

    card.innerHTML = `
        <div class="aprov-card-main">
            <div class="aprov-card-header">
                <h3 class="aprov-card-name">${esc(item.colaborador?.nome || '—')}</h3>
                <span class="aprov-chip ${typeChipClass(item.tipo)}">${esc(typeLabel(item.tipo))}</span>
            </div>
            <div class="aprov-card-meta">
                <span><strong>Início:</strong> ${formatDate(item.inicio)}</span>
                <span><strong>Fim:</strong> ${formatDate(item.fim)}</span>
                <span><strong>Pedido em:</strong> ${formatDate(item.pedido_em)}</span>
            </div>
            ${justif}
            ${attach}
        </div>
        <div class="aprov-card-actions">
            <button type="button" class="aprov-btn aprov-btn--approve" data-action="aprovar">Aprovar</button>
            <button type="button" class="aprov-btn aprov-btn--reject" data-action="rejeitar">Rejeitar</button>
        </div>
    `;
    return card;
}

async function loadHistory() {
    const list = document.getElementById('aprov-history-list');
    const empty = document.getElementById('aprov-history-empty');
    if (!list) return;

    list.innerHTML = '';
    if (empty) {
        list.appendChild(empty);
        empty.style.display = 'block';
        empty.textContent = 'A carregar...';
    }

    try {
        const res = await getLeaveApprovalHistory();
        if (!res?.ok) throw new Error(res?.code || 'Erro');

        state.history = res.items || [];

        if (res.has_subs === false) {
            empty.textContent = 'Não tem colaboradores a seu cargo.';
            return;
        }

        renderHistory(state.history);
    } catch {
        state.history = [];
        if (empty) {
            list.innerHTML = '';
            list.appendChild(empty);
            empty.style.display = 'block';
            empty.textContent = 'Não foi possível carregar o histórico.';
        }
    }
}

function renderHistory(items) {
    const list = document.getElementById('aprov-history-list');
    const empty = document.getElementById('aprov-history-empty');
    if (!list) return;

    list.innerHTML = '';

    if (!items || items.length === 0) {
        if (empty) {
            list.appendChild(empty);
            empty.style.display = 'block';
            empty.textContent = 'Sem pedidos no histórico.';
        }
        return;
    }

    items.forEach(item => {
        const card = document.createElement('article');
        card.className = 'aprov-card';
        const statusChip = item.estado === 'aprovado'
            ? '<span class="aprov-chip aprov-chip--status-aprovado">Aprovado</span>'
            : '<span class="aprov-chip aprov-chip--status-rejeitado">Rejeitado</span>';
        const justif = item.descricao
            ? `<div class="aprov-card-justif">${esc(item.descricao)}</div>`
            : '';
        card.innerHTML = `
            <div class="aprov-card-main">
                <div class="aprov-card-header">
                    <h3 class="aprov-card-name">${esc(item.colaborador?.nome || '—')}</h3>
                    <span class="aprov-chip ${typeChipClass(item.tipo)}">${esc(typeLabel(item.tipo))}</span>
                    ${statusChip}
                </div>
                <div class="aprov-card-meta">
                    <span><strong>Início:</strong> ${formatDate(item.inicio)}</span>
                    <span><strong>Fim:</strong> ${formatDate(item.fim)}</span>
                    <span><strong>Decidido por:</strong> ${esc(item.decidido_por?.nome || '—')}</span>
                </div>
                ${justif}
            </div>
        `;
        list.appendChild(card);
    });
}

function bindTabs() {
    const tabs = document.querySelectorAll('.aprov-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            tabs.forEach(t => t.classList.toggle('aprov-tab--active', t === tab));

            document.getElementById('aprov-view-pendentes')
                ?.classList.toggle('aprov-view--active', target === 'pendentes');
            document.getElementById('aprov-view-historico')
                ?.classList.toggle('aprov-view--active', target === 'historico');

            if (target === 'historico' && state.history === null) loadHistory();
        });
    });
}

function bindTypeFilter() {
    const group = document.getElementById('aprov-type-filter');
    if (!group) return;

    group.addEventListener('click', (e) => {
        const btn = e.target.closest('.aprov-filter');
        if (!btn) return;
        const type = btn.dataset.type;
        if (!type || type === state.filterType) return;

        state.filterType = type;
        group.querySelectorAll('.aprov-filter').forEach(b => {
            b.classList.toggle('aprov-filter--active', b === btn);
        });
        loadPending();
    });
}

function bindActions(openModal) {
    const list = document.getElementById('aprov-pending-list');
    if (!list) return;

    list.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const card = btn.closest('.aprov-card');
        const id = Number(card?.dataset.id);
        const item = state.pending?.find(i => i.pedido_id === id);
        if (!item) return;
        openDecisionModal(item, btn.dataset.action, openModal);
    });
}

function openDecisionModal(item, acao, openModal) {
    const isApprove = acao === 'aprovar';
    const m = openModal({ title: isApprove ? 'Aprovar pedido' : 'Rejeitar pedido' });

    m.body.innerHTML = `
        <div class="ui-modal-detail-grid">
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Colaborador</span>
                <span class="ui-modal-detail-value">${esc(item.colaborador?.nome || '—')}</span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Tipo</span>
                <span class="ui-modal-detail-value">${esc(typeLabel(item.tipo))}</span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Início</span>
                <span class="ui-modal-detail-value">${formatDate(item.inicio)}</span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Fim</span>
                <span class="ui-modal-detail-value">${formatDate(item.fim)}</span>
            </div>
            ${item.justificacao ? `
            <div class="ui-modal-detail-field full-width">
                <span class="ui-modal-detail-label">Justificação</span>
                <span class="ui-modal-detail-value">${esc(item.justificacao)}</span>
            </div>` : ''}
        </div>
        <textarea id="aprov-modal-comment" class="aprov-modal-comment"
                  placeholder="${isApprove ? 'Comentário (opcional)...' : 'Justificação da rejeição (obrigatório)...'}"></textarea>
        <p class="aprov-modal-error" id="aprov-modal-error" style="display:none;"></p>
    `;

    m.footer.innerHTML = `
        <button type="button" class="btn-secondary aprov-modal-cancel">Cancelar</button>
        <button type="button" class="aprov-btn ${isApprove ? 'aprov-btn--approve' : 'aprov-btn--reject'} aprov-modal-confirm">
            ${isApprove ? 'Aprovar' : 'Rejeitar'}
        </button>
    `;

    const errEl = m.body.querySelector('#aprov-modal-error');
    const showError = (msg) => {
        if (!errEl) return;
        errEl.textContent = msg;
        errEl.style.display = 'block';
    };

    m.footer.querySelector('.aprov-modal-cancel')?.addEventListener('click', m.close);
    m.footer.querySelector('.aprov-modal-confirm')?.addEventListener('click', async () => {
        const confirmBtn = m.footer.querySelector('.aprov-modal-confirm');
        const comentario = m.body.querySelector('#aprov-modal-comment')?.value?.trim() || '';

        if (!isApprove && !comentario) {
            showError('Indique a justificação da rejeição.');
            return;
        }

        if (errEl) errEl.style.display = 'none';
        if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.textContent = 'A processar...'; }

        try {
            const res = await decideLeaveRequest({
                pedido_id: item.pedido_id,
                acao,
                comentario: comentario || null,
            });
            if (!res?.ok) throw new Error(res?.code || 'Erro');

            m.close();
            state.pending = null;
            state.history = null;
            await Promise.all([loadSummary(), loadPending()]);
        } catch (err) {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = isApprove ? 'Aprovar' : 'Rejeitar';
            }
            showError(err?.message || 'Erro ao processar a decisão.');
        }
    });
}

export function mountAprovacaoFerias() {
    state.summary = null;
    state.pending = null;
    state.history = null;
    state.filterType = 'all';

    const ol = createOverlays();

    bindTabs();
    bindTypeFilter();
    bindActions(ol.openModal);

    loadSummary();
    loadPending();

    return () => ol.closeActive();
}
