import {getAllPendingRequests, getAllRecords} from '../../api.js';
import {openDiffForRequest, mountGstFchsDiff} from './diff.js';
import {openRecordForUser, mountGstFchsRecord} from './record.js';

/** @type {Array<any> | null} */
let approvalsCache = null;
/** @type {Array<any> | null} */
let allRecsCache = null;

function formatRequestDate(raw) {
    if (!raw) return '-';

    const date = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return raw;

    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const dayMs = 24 * 60 * 60 * 1000;
    const days = Math.floor(diffMs / dayMs);
    const timeStr = date.toLocaleTimeString('pt-PT', {hour: '2-digit', minute: '2-digit'});

    if (days === 0) return `Hoje às ${timeStr}`;
    if (days === 1) return `Ontem às ${timeStr}`;
    if (days < 7) return `Há ${days} dia${days > 1 ? 's' : ''} às ${timeStr}`;
    const dateStr = date.toLocaleDateString('pt-PT');

    return `${dateStr} às ${timeStr}`;
}

function setActiveTab(which) {
    const tabPedidos = document.getElementById('gestao-aprovacoes');
    const tabFichas = document.getElementById('gestao-visualizar-fichas');

    if (!tabPedidos || !tabFichas) return;

    const isPedidos = which === 'pedidos';
    tabPedidos.classList.toggle('gestao-tab--active', isPedidos);
    tabFichas.classList.toggle('gestao-tab--active', !isPedidos);
}

function updateNavButtons() {
    const navBack = document.getElementById('gestao-nav-back');
    const navForward = document.getElementById('gestao-nav-forward');
    if (!navBack || !navForward) return;

    const approvalsView = document.getElementById('gestao-view-approvals');
    const allrecsView = document.getElementById('gestao-view-allrecs');
    const diffView = document.getElementById('gestao-diff-view');
    const recordView = document.getElementById('gestao-record-view');
    const approvalsList = document.getElementById('gestao-approvals-list');
    const allrecsList = document.getElementById('gestao-allrecs-list');

    const approvalsActive = approvalsView && approvalsView.classList.contains('gestao-view--active');
    const allrecsActive = allrecsView && allrecsView.classList.contains('gestao-view--active');
    const diffActive = diffView && diffView.classList.contains('gestao-diff-view--active');
    const recordActive = recordView && recordView.classList.contains('gestao-record-view--active');

    const approvalsListVisible = approvalsList && !approvalsList.classList.contains('gestao-approvals-list--hidden');
    const allrecsListVisible = allrecsList && !allrecsList.classList.contains('gestao-allrecs-list--hidden');

    const canBackFromDetail = diffActive || recordActive;
    const canBackFromTab = allrecsActive && allrecsListVisible && !recordActive;
    const canBack = canBackFromDetail || canBackFromTab;

    const canForwardFromPedidos = approvalsActive && approvalsListVisible && Array.isArray(approvalsCache) && approvalsCache.length > 0;

    const canForwardFromFichas = allrecsActive && allrecsListVisible && Array.isArray(allRecsCache) && allRecsCache.length > 0;

    const canForward = canForwardFromPedidos || canForwardFromFichas;

    navBack.disabled = !canBack;
    navForward.disabled = !canForward;
}

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

        const requestedAt = row.profile_created_at || row.emergency_created_at || '';

        const formattedDate = formatRequestDate(requestedAt);

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
            <td>${formattedDate}</td>
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
        updateNavButtons();
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
        updateNavButtons();
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
        updateNavButtons();
        return;
    }

    updateNavButtons();
    renderPendingReqs();
}

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
        tr.dataset.userName = collabName;
        tr.dataset.userEmail = row.email || '';

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

function bindAllRecsInteraction() {
    const tbody = document.getElementById('gestao-allrecs-tbody');
    if (!tbody) return;

    tbody.addEventListener('click', event => {
        const row = event.target.closest('tr[data-user-id]');
        if (!row) return;

        const userId = row.dataset.userId || null;
        const name = row.dataset.userName || '';
        const email = row.dataset.userEmail || '';

        openRecordForUser(userId, name, email);
        updateNavButtons();
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
    } finally {
        updateNavButtons();
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
        updateNavButtons();
        return;
    }

    updateNavButtons();
    renderAllRecs();
}

function handleNavBack() {
    const diffView = document.getElementById('gestao-diff-view');
    const recordView = document.getElementById('gestao-record-view');
    const approvalsList = document.getElementById('gestao-approvals-list');
    const allrecsList = document.getElementById('gestao-allrecs-list');

    if (diffView && diffView.classList.contains('gestao-diff-view--active')) {
        diffView.classList.remove('gestao-diff-view--active');
        if (approvalsList) approvalsList.classList.remove('gestao-approvals-list--hidden');
        updateNavButtons();
        return;
    }

    if (recordView && recordView.classList.contains('gestao-record-view--active')) {
        recordView.classList.remove('gestao-record-view--active');
        if (allrecsList) allrecsList.classList.remove('gestao-allrecs-list--hidden');
        updateNavButtons();
        return;
    }

    const allrecsView = document.getElementById('gestao-view-allrecs');
    if (allrecsView && allrecsView.classList.contains('gestao-view--active')) {
        showPendingRecsView();
        return;
    }
}

function handleNavForward() {
    const approvalsView = document.getElementById('gestao-view-approvals');
    const allrecsView = document.getElementById('gestao-view-allrecs');

    const approvalsActive = approvalsView && approvalsView.classList.contains('gestao-view--active');
    const allrecsActive = allrecsView && allrecsView.classList.contains('gestao-view--active');

    if (approvalsActive && Array.isArray(approvalsCache) && approvalsCache.length > 0) {
        const first = approvalsCache[0];
        openDiffForRequest(first.user_id, first.profile_req_id != null ? first.profile_req_id : null, first.emergency_req_id != null ? first.emergency_req_id : null, approvalsCache);
        updateNavButtons();
        return;
    }

    if (allrecsActive && Array.isArray(allRecsCache) && allRecsCache.length > 0) {
        const first = allRecsCache[0];
        openRecordForUser(first.id ?? null, first.name || '', first.email || '');
        updateNavButtons();
    }
}

function bindNavButtons() {
    const backBtn = document.getElementById('gestao-nav-back');
    const forwardBtn = document.getElementById('gestao-nav-forward');

    if (backBtn) {
        backBtn.addEventListener('click', () => {
            if (backBtn.disabled) return;
            handleNavBack();
        });
    }

    if (forwardBtn) {
        forwardBtn.addEventListener('click', () => {
            if (forwardBtn.disabled) return;
            handleNavForward();
        });
    }
}

function bindTabs() {
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
    bindTabs();
    bindNavButtons();
    bindPendingRequestsInteraction();
    bindAllRecsInteraction();
    mountGstFchsDiff();
    mountGstFchsRecord();
    updateNavButtons();
}