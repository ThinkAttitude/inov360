import { getAllPendingRequests } from '../api.js';

let approvalsCache = null; // null = not loaded yet

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
                <div class="gestao-colab-name">${row.user_name}</div>
                ${row.user_email ? `<div class="gestao-colab-email">${row.user_email}</div>` : ''}
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

        // Later: switch to diff view for this request
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
    const otherView = document.getElementById('gestao-view-other');
    if (!approvalsView) return;

    const alreadyActive = approvalsView.classList.contains('gestao-view--active');
    if (!alreadyActive) {
        approvalsView.classList.add('gestao-view--active');
        if (otherView) otherView.classList.remove('gestao-view--active');
    }

    // If we already have data, just re-render without hitting the API again
    if (approvalsCache !== null) {
        buildPendingReqs(approvalsCache);
        return;
    }

    // First time: actually call the API
    renderPendingReqs();
}

function showAllRecsView() {
    const approvalsView = document.getElementById('gestao-view-approvals');
    const otherView = document.getElementById('gestao-view-other');
    if (!otherView) return;

    const alreadyActive = otherView.classList.contains('gestao-view--active');
    if (!alreadyActive) {
        otherView.classList.add('gestao-view--active');
        if (approvalsView) approvalsView.classList.remove('gestao-view--active');
    }

    // TODO: render the second view here when it exists
}

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
}
