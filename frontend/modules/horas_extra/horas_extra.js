import {
    getOvertimeRequests,
    approveOvertime,
    getOvertimeHistory,
    exportOvertimeSheets
} from '../../app/api.js';
import './styles.css';

/* ===== State ===== */
let pendingCache = null;
let historyCache = null;
let exportCache = null;

/* ===== Helpers ===== */
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

/* ===== Tabs ===== */
function bindTabs() {
    const tabs = {
        'he-tab-pendentes': 'he-view-pendentes',
        'he-tab-historico': 'he-view-historico',
        'he-tab-exportar': 'he-view-exportar',
    };

    Object.entries(tabs).forEach(([tabId, viewId]) => {
        const tab = document.getElementById(tabId);
        if (!tab) return;
        tab.addEventListener('click', () => {
            // Deactivate all
            Object.entries(tabs).forEach(([tId, vId]) => {
                document.getElementById(tId)?.classList.remove('he-tab--active');
                document.getElementById(vId)?.classList.remove('he-view--active');
            });
            tab.classList.add('he-tab--active');
            document.getElementById(viewId)?.classList.add('he-view--active');

            // Lazy load
            if (viewId === 'he-view-pendentes' && pendingCache === null) loadPending();
            if (viewId === 'he-view-historico' && historyCache === null) loadHistory();
        });
    });
}

/* ===== PENDENTES ===== */
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

        pendingCache = res.items;
        renderPending(pendingCache);
    } catch (err) {
        pendingCache = [];
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

function bindPendingActions() {
    const tbody = document.getElementById('he-pend-tbody');
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;

        const action = btn.dataset.action;
        const id = Number(btn.dataset.id);
        const item = pendingCache?.find(i => i.id === id);
        if (!item) return;

        openDecisionModal(item, action);
    });
}

function bindPendingSearch() {
    const input = document.getElementById('he-pend-search');
    if (!input) return;

    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            pendingCache = null;
            loadPending(input.value.trim());
        }, 400);
    });
}

/* ===== Decision Modal ===== */
function openDecisionModal(item, action) {
    const overlay = document.getElementById('he-modal-overlay');
    const title = document.getElementById('he-modal-title');
    const body = document.getElementById('he-modal-body');
    const footer = document.getElementById('he-modal-footer');
    if (!overlay || !body || !footer) return;

    const isApprove = action === 'approve';
    if (title) title.textContent = isApprove ? 'Aprovar pedido' : 'Recusar pedido';

    body.innerHTML = `
        <div class="he-detail-grid">
            <div class="he-detail-field">
                <span class="he-detail-label">Colaborador</span>
                <span class="he-detail-value">${esc(item.colaborador.nome)}</span>
            </div>
            <div class="he-detail-field">
                <span class="he-detail-label">Email</span>
                <span class="he-detail-value">${esc(item.colaborador.email)}</span>
            </div>
            <div class="he-detail-field">
                <span class="he-detail-label">Dia</span>
                <span class="he-detail-value">${formatDate(item.dia)}</span>
            </div>
            <div class="he-detail-field">
                <span class="he-detail-label">Horário</span>
                <span class="he-detail-value">${esc(item.hora_inicio)} — ${esc(item.hora_fim)} (${formatMinutes(item.minutos)})</span>
            </div>
            <div class="he-detail-field full-width">
                <span class="he-detail-label">Proposto por</span>
                <span class="he-detail-value">${esc(item.criado_por.nome)}</span>
            </div>
        </div>
        <textarea id="he-modal-comment" placeholder="Comentário (opcional)..."></textarea>
    `;

    footer.innerHTML = `
        <button type="button" class="btn-secondary" id="he-modal-cancel">Cancelar</button>
        <button type="button" class="btn-primary" id="he-modal-confirm"
                style="${isApprove ? '' : 'background:linear-gradient(135deg,#ef4444,#dc2626);'}">
            ${isApprove ? 'Aprovar' : 'Recusar'}
        </button>
    `;

    overlay.classList.add('he-modal-overlay--active');

    document.getElementById('he-modal-cancel')?.addEventListener('click', closeModal);
    document.getElementById('he-modal-close')?.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

    document.getElementById('he-modal-confirm')?.addEventListener('click', async () => {
        const confirmBtn = document.getElementById('he-modal-confirm');
        if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.textContent = 'A processar...'; }

        const comentario = document.getElementById('he-modal-comment')?.value?.trim() || null;

        try {
            const res = await approveOvertime({
                request_id: item.id,
                decision: action,
                comentario
            });

            if (!res?.ok) throw new Error(res?.code || 'Erro');

            closeModal();
            pendingCache = null;
            historyCache = null;
            loadPending();
        } catch (err) {
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = isApprove ? 'Aprovar' : 'Recusar'; }
            const errMap = {
                REQUEST_NOT_FOUND: 'Pedido não encontrado.',
                ALREADY_DECIDED: 'Este pedido já foi decidido.',
                OVERTIME_CLOSED: 'O período está encerrado.',
                OVERTIME_CONFLICT: 'Conflito com horas extra já existentes.',
            };
            alert(errMap[res?.code] || err.message || 'Erro ao processar a decisão.');
        }
    });
}

function closeModal() {
    document.getElementById('he-modal-overlay')?.classList.remove('he-modal-overlay--active');
}

/* ===== HISTÓRICO ===== */
function getCurrentMonth() {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
}

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

        historyCache = res.items;
        renderHistory(historyCache);
        renderHistoryStats(res.totals);
    } catch {
        historyCache = [];
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

function bindHistoryActions() {
    document.getElementById('he-hist-tbody')?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-detail-id]');
        if (!btn) return;
        const id = Number(btn.dataset.detailId);
        const item = historyCache?.find(i => i.id === id);
        if (item) openDetailModal(item);
    });

    document.getElementById('he-hist-filter-btn')?.addEventListener('click', () => {
        historyCache = null;
        loadHistory();
    });
}

function openDetailModal(item) {
    const overlay = document.getElementById('he-modal-overlay');
    const title = document.getElementById('he-modal-title');
    const body = document.getElementById('he-modal-body');
    const footer = document.getElementById('he-modal-footer');
    if (!overlay || !body) return;

    if (title) title.textContent = 'Detalhe do pedido';

    body.innerHTML = `
        <div class="he-detail-grid">
            <div class="he-detail-field">
                <span class="he-detail-label">Colaborador</span>
                <span class="he-detail-value">${esc(item.colaborador.nome)}</span>
            </div>
            <div class="he-detail-field">
                <span class="he-detail-label">Email</span>
                <span class="he-detail-value">${esc(item.colaborador.email)}</span>
            </div>
            ${item.colaborador.empresa ? `
            <div class="he-detail-field">
                <span class="he-detail-label">Empresa</span>
                <span class="he-detail-value">${esc(item.colaborador.empresa)}</span>
            </div>` : ''}
            <div class="he-detail-field">
                <span class="he-detail-label">Dia</span>
                <span class="he-detail-value">${formatDate(item.dia)}</span>
            </div>
            <div class="he-detail-field">
                <span class="he-detail-label">Horário</span>
                <span class="he-detail-value">${esc(item.hora_inicio)} — ${esc(item.hora_fim)} (${formatMinutes(item.req_minutos)})</span>
            </div>
            <div class="he-detail-field">
                <span class="he-detail-label">Estado</span>
                <span class="he-detail-value"><span class="he-badge he-badge--${item.estado}">${item.estado === 'approved' ? 'Aprovado' : 'Recusado'}</span></span>
            </div>
            <div class="he-detail-field">
                <span class="he-detail-label">Decidido por</span>
                <span class="he-detail-value">${item.decidido_por ? esc(item.decidido_por.nome) : '-'}</span>
            </div>
            <div class="he-detail-field">
                <span class="he-detail-label">Data da decisão</span>
                <span class="he-detail-value">${item.decidido_em || '-'}</span>
            </div>
            ${item.comentario ? `
            <div class="he-detail-field full-width">
                <span class="he-detail-label">Comentário</span>
                <span class="he-detail-value">${esc(item.comentario)}</span>
            </div>` : ''}
        </div>
    `;

    if (footer) footer.innerHTML = `<button type="button" class="btn-secondary" id="he-modal-cancel">Fechar</button>`;

    overlay.classList.add('he-modal-overlay--active');

    document.getElementById('he-modal-cancel')?.addEventListener('click', closeModal);
    document.getElementById('he-modal-close')?.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
}

/* ===== EXPORTAR ===== */
async function loadExportPreview() {
    const monthInput = document.getElementById('he-export-month');
    const month = monthInput?.value;
    if (!month) { alert('Selecione um mês.'); return; }

    const preview = document.getElementById('he-export-preview');
    const tbody = document.getElementById('he-export-tbody');
    const empty = document.getElementById('he-export-empty');
    const subtitle = document.getElementById('he-export-preview-subtitle');
    const xlsxBtn = document.getElementById('he-export-xlsx-btn');

    if (!tbody || !preview) return;

    preview.style.display = 'block';
    tbody.innerHTML = '';
    if (empty) { empty.style.display = 'block'; empty.textContent = 'A carregar...'; }
    if (xlsxBtn) xlsxBtn.disabled = true;

    try {
        const res = await getOvertimeHistory({ month, state: 'approved' });
        if (!res?.ok) throw new Error(res?.code || 'Erro');

        exportCache = res.items;
        if (subtitle) subtitle.textContent = `${res.items.length} registo(s) aprovado(s) em ${month}`;
        renderExportTable(exportCache);
    } catch {
        exportCache = [];
        if (empty) { empty.style.display = 'block'; empty.textContent = 'Não foi possível carregar os dados.'; }
    }
}

function renderExportTable(items) {
    const tbody = document.getElementById('he-export-tbody');
    const empty = document.getElementById('he-export-empty');
    const xlsxBtn = document.getElementById('he-export-xlsx-btn');
    const selectAll = document.getElementById('he-export-select-all');

    if (!tbody) return;
    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        if (empty) { empty.style.display = 'block'; empty.textContent = 'Nenhum pedido aprovado encontrado para este mês.'; }
        if (xlsxBtn) xlsxBtn.disabled = true;
        return;
    }
    if (empty) empty.style.display = 'none';
    if (selectAll) selectAll.checked = true;

    items.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="checkbox" class="he-export-check" data-user-id="${item.colaborador.id}" checked></td>
            <td>
                <div class="he-collab-name">${esc(item.colaborador.nome)}</div>
                <div class="he-collab-email">${esc(item.colaborador.email)}</div>
            </td>
            <td>${formatDate(item.dia)}</td>
            <td>${esc(item.hora_inicio)}</td>
            <td>${esc(item.hora_fim)}</td>
            <td>${formatMinutes(item.req_minutos)}</td>
        `;
        tbody.appendChild(tr);
    });

    updateExportButton();
}

function updateExportButton() {
    const checks = document.querySelectorAll('.he-export-check:checked');
    const xlsxBtn = document.getElementById('he-export-xlsx-btn');
    if (xlsxBtn) xlsxBtn.disabled = checks.length === 0;
}

function bindExportActions() {
    document.getElementById('he-export-preview-btn')?.addEventListener('click', loadExportPreview);

    document.getElementById('he-export-select-all')?.addEventListener('change', (e) => {
        document.querySelectorAll('.he-export-check').forEach(cb => { cb.checked = e.target.checked; });
        updateExportButton();
    });

    document.getElementById('he-export-tbody')?.addEventListener('change', () => {
        const all = document.querySelectorAll('.he-export-check');
        const checked = document.querySelectorAll('.he-export-check:checked');
        const selectAll = document.getElementById('he-export-select-all');
        if (selectAll) selectAll.checked = all.length === checked.length;
        updateExportButton();
    });

    document.getElementById('he-export-xlsx-btn')?.addEventListener('click', async () => {
        const month = document.getElementById('he-export-month')?.value;
        if (!month) return;

        // Collect unique selected user IDs
        const checks = document.querySelectorAll('.he-export-check:checked');
        const userIds = [...new Set([...checks].map(cb => cb.dataset.userId))];

        const btn = document.getElementById('he-export-xlsx-btn');
        if (btn) { btn.disabled = true; btn.textContent = 'A exportar...'; }

        try {
            const res = await exportOvertimeSheets(month, userIds);
            // If it returns a Response (blob), trigger download
            if (res instanceof Response) {
                const blob = await res.blob();
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `horas_extra_${month}.xlsx`;
                a.click();
                URL.revokeObjectURL(url);
            }
        } catch {
            alert('Erro ao exportar o ficheiro.');
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Exportar Excel
            `; }
        }
    });
}

/* ===== Mount ===== */
export function mountHorasExtra() {
    bindTabs();
    bindPendingActions();
    bindPendingSearch();
    bindHistoryActions();
    bindExportActions();

    // Load pendentes by default
    loadPending();
}
