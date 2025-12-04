import { getAllPendingRequests, getAllRecords } from '../../api.js';
import { openDiffForRequest, mountGstFchsDiff } from './diff.js';

let approvalsCache = null;
let allRecsCache = null;

function setActiveTab(which) {
    const tabPedidos = document.getElementById('gestao-aprovacoes');
    const tabFichas = document.getElementById('gestao-visualizar-fichas');
    if (!tabPedidos || !tabFichas) return;

    const isPedidos = which === 'pedidos';

    tabPedidos.classList.toggle('gestao-tab--active', isPedidos);
    tabFichas.classList.toggle('gestao-tab--active', !isPedidos);
}

/* ---------- Pending requests (Pedidos) ---------- */

function buildPendingReqs(rows) {
    const tbody = document.getElementById('gestao-approvals-tbody');
    const emptyEl = document.getElementById('gestao-approvals-empty');
    if (!tbody || !emptyEl) return;

    tbody.innerHTML = '';

    if (!Array.isArray(rows) || rows.length === 0) {
        emptyEl.textContent = 'Não existem pedidos pendentes de aprovação.';
        emptyEl.style.display = 'block';
        return;
    }

    emptyEl.style.display = 'none';

    rows.forEach(row => {
        const tr = document.createElement('tr');

        const requestedAt =
            row.profile_created_at ||
            row.emergency_created_at ||
            '';

        tr.dataset.userId = String(row.user_id);
        if (row.profile_req_id != null) {
            tr.dataset.profileReqId = String(row.profile_req_id);
        }
        if (row.emergency_req_id != null) {
            tr.dataset.emergencyReqId = String(row.emergency_req_id);
        }

        tr.innerHTML = `
            <td>
                <div class="gestao-collab-name">${row.user_name}</div>
                ${row.user_email ? `<div class="gestao-collab-email">${row.user_email}</div>` : ''}
            </td>
            <td>${requestedAt || '-'}</td>
            <td>
                <span class="status-badge status-badge--pending">Pending</span>
            </td>
            <td>
                <button type="button" class="gestao-table-action">
                    Ver detalhes
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

function bindPendingRequestsInteraction() {
    const tbody = document.getElementById('gestao-approvals-tbody');
    if (!tbody) return;

    tbody.addEventListener('click', event => {
        const row = event.target.closest('tr[data-user-id]');
        if (!row) return;

        const userId = row.dataset.userId;
        const profileReqId = row.dataset.profileReqId || null;
        const emergencyReqId = row.dataset.emergencyReqId || null;

        openDiffForRequest(userId, profileReqId, emergencyReqId, approvalsCache);
    });
}

async function renderPendingReqs() {
    const emptyEl = document.getElementById('gestao-approvals-empty');
    const btn = document.getElementById('gestao-aprovacoes');

    if (btn) btn.disabled = true;
    if (emptyEl) {
        emptyEl.textContent = 'A carregar pedidos pendentes...';
        emptyEl.style.display = 'block';
    }

    try {
        const res = await getAllPendingRequests();

        if (!res || res.success !== true || !Array.isArray(res.items)) {
            throw new Error('Invalid response');
        }

        approvalsCache = res.items;
        buildPendingReqs(approvalsCache);
    } catch (err) {
        console.error('Failed to load pending requests:', err);
        approvalsCache = [];
        buildPendingReqs(approvalsCache);
        if (emptyEl) {
            emptyEl.textContent = 'Não foi possível carregar os pedidos pendentes.';
            emptyEl.style.display = 'block';
        }
    } finally {
        if (btn) btn.disabled = false;
    }
}

function showPendingRecsView() {
    const approvalsView = document.getElementById('gestao-view-approvals');
    const recordsView = document.getElementById('gestao-view-allrecs');
    if (!approvalsView) return;

    setActiveTab('pedidos');

    const alreadyActive = approvalsView.classList.contains('gestao-view--active');
    if (!alreadyActive) {
        approvalsView.classList.add('gestao-view--active');
        if (recordsView) recordsView.classList.remove('gestao-view--active');
    }

    if (approvalsCache !== null) {
        buildPendingReqs(approvalsCache);
        return;
    }

    renderPendingReqs();
}

/* ---------- All records (Fichas) ---------- */

function buildAllRecs(rows) {
    const tbody = document.getElementById('gestao-allrecs-tbody');
    const emptyEl = document.getElementById('gestao-allrecs-empty');
    if (!tbody || !emptyEl) return;

    tbody.innerHTML = '';

    if (!Array.isArray(rows) || rows.length === 0) {
        emptyEl.textContent = 'Não existem colaboradores para apresentar.';
        emptyEl.style.display = 'block';
        return;
    }

    emptyEl.style.display = 'none';

    rows.forEach(row => {
        const tr = document.createElement('tr');

        const collabName = row.name || '';
        const companyName = (row.company && row.company.name) || '';

        tr.dataset.userId = String(row.id ?? '');

        tr.innerHTML = `
            <td>
                <div class="gestao-collab-name">${collabName}</div>
                ${row.email ? `<div class="gestao-collab-email">${row.email}</div>` : ''}
            </td>
            <td>
                <div class="gestao-company-name">${companyName}</div>
            </td>
            <td>
                <button type="button" class="gestao-table-action">
                    Ver ficha
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

async function renderAllRecs() {
    const emptyEl = document.getElementById('gestao-allrecs-empty');

    if (emptyEl) {
        emptyEl.textContent = 'A carregar colaboradores...';
        emptyEl.style.display = 'block';
    }

    try {
        const res = await getAllRecords();

        if (!res || res.success !== true || !Array.isArray(res.items)) {
            throw new Error('Invalid response');
        }

        allRecsCache = res.items;
        buildAllRecs(allRecsCache);
    } catch (err) {
        console.error('Failed to load all records:', err);
        allRecsCache = [];
        buildAllRecs(allRecsCache);
        if (emptyEl) {
            emptyEl.textContent = 'Não foi possível carregar os colaboradores.';
            emptyEl.style.display = 'block';
        }
    }
}

function showAllRecsView() {
    const approvalsView = document.getElementById('gestao-view-approvals');
    const otherView = document.getElementById('gestao-view-allrecs');
    if (!otherView) return;

    setActiveTab('fichas');

    const alreadyActive = otherView.classList.contains('gestao-view--active');
    if (!alreadyActive) {
        otherView.classList.add('gestao-view--active');
        if (approvalsView) approvalsView.classList.remove('gestao-view--active');
    }

    if (allRecsCache !== null) {
        buildAllRecs(allRecsCache);
        return;
    }

    renderAllRecs();
}

/* ---------- Main tabs ---------- */

function bindMainBtns() {
    const btnApprovals = document.getElementById('gestao-aprovacoes');
    const btnRecords = document.getElementById('gestao-visualizar-fichas');

    if (btnApprovals) {
        btnApprovals.addEventListener('click', () => {
            showPendingRecsView();
        });
    }

    if (btnRecords) {
        btnRecords.addEventListener('click', () => {
            showAllRecsView();
        });
    }
}

export function mountGstFchs() {
    bindMainBtns();
    bindPendingRequestsInteraction();
    mountGstFchsDiff();
}