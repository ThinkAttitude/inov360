import {createDecision, getRecord, getRecordChanges} from '../../app/api.js';
import {FICHA_FIELD_LABELS as DIFF_FIELD_LABELS} from '../ficha_collab/ficha_collab_fields.js'
import { toast } from '../../shared/ui/toast/toast.js'


const diffState = {
    userId: null,
    profileReqId: null,
    emergencyReqId: null,
};

function isEmergencyField(key) {
    return key.startsWith('emergency_')
}

function renderDiffView(oldProfile, newProfile, changed) {
    const oldEl = document.getElementById('gestao-diff-old')
    const newEl = document.getElementById('gestao-diff-new')
    if (!oldEl || !newEl) return

    oldEl.innerHTML = ''
    newEl.innerHTML = ''

    const old = oldProfile || {}
    const neu = newProfile || {}
    const keys = Object.keys(DIFF_FIELD_LABELS)
    const firstEmergencyKey = keys.find(isEmergencyField)
    const lastEmergencyKey = keys.findLast(isEmergencyField)

    if (keys.length === 0) {
        oldEl.textContent = 'Nenhum campo para apresentar.'
        newEl.textContent = 'Nenhum campo para apresentar.'
        return
    }

    const oldWrapper = document.createElement('div')
    oldWrapper.className = 'gestao-table-wrapper gestao-table-wrapper--diff'

    const newWrapper = document.createElement('div')
    newWrapper.className = 'gestao-table-wrapper gestao-table-wrapper--diff'

    const oldTable = document.createElement('table')
    oldTable.className = 'gestao-table gestao-diff-table'

    const oldHead = document.createElement('thead')
    oldHead.innerHTML = '<tr><th>Campo</th><th>Valor atual</th></tr>'

    const oldBody = document.createElement('tbody')

    const newTable = document.createElement('table')
    newTable.className = 'gestao-table gestao-diff-table'

    const newHead = document.createElement('thead')
    newHead.innerHTML = '<tr><th>Campo</th><th>Novo valor</th></tr>'

    const newBody = document.createElement('tbody')

    keys.forEach(key => {
        const label = DIFF_FIELD_LABELS[key]
        if (!label) return

        const oldValRaw = Object.prototype.hasOwnProperty.call(old, key) ? old[key] : null
        const newValRaw = Object.prototype.hasOwnProperty.call(neu, key) ? neu[key] : null

        const oldVal = oldValRaw == null ? '' : String(oldValRaw)
        const newVal = newValRaw == null ? '' : String(newValRaw)
        const isChanged = !!(changed && changed[key])
        const isEmergency = isEmergencyField(key)
        const isFirstEmergency = key === firstEmergencyKey
        const isLastEmergency = key === lastEmergencyKey

        const trOld = document.createElement('tr')
        if (isChanged) trOld.classList.add('gestao-diff-row--changed-old')
        if (isEmergency) trOld.classList.add('gestao-diff-row--emergency')
        if (isFirstEmergency) trOld.classList.add('gestao-diff-row--emergency-first')
        if (isLastEmergency) trOld.classList.add('gestao-diff-row--emergency-last')

        const thOld = document.createElement('th')
        thOld.scope = 'row'
        thOld.className = 'gestao-diff-cell-label'

        if (isChanged) {
            thOld.innerHTML = `
                <span class="gestao-diff-label-inner">
                    <span class="gestao-diff-icon gestao-diff-icon--old" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="gestao-diff-icon-svg">
                            <path
                                d="M18 6L6 18M6 6l12 12"
                                stroke="currentColor"
                                stroke-width="2.2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>
                    <span class="gestao-diff-label-text">${label}</span>
                </span>
            `
        } else {
            thOld.textContent = label
        }

        const tdOld = document.createElement('td')
        tdOld.className = 'gestao-diff-cell-value'
        tdOld.textContent = oldVal

        trOld.appendChild(thOld)
        trOld.appendChild(tdOld)
        oldBody.appendChild(trOld)

        const trNew = document.createElement('tr')
        if (isChanged) trNew.classList.add('gestao-diff-row--changed-new')
        if (isEmergency) trNew.classList.add('gestao-diff-row--emergency')
        if (isFirstEmergency) trNew.classList.add('gestao-diff-row--emergency-first')
        if (isLastEmergency) trNew.classList.add('gestao-diff-row--emergency-last')

        const thNew = document.createElement('th')
        thNew.scope = 'row'
        thNew.className = 'gestao-diff-cell-label'

        if (isChanged) {
            thNew.innerHTML = `
                <span class="gestao-diff-label-inner">
                    <span class="gestao-diff-icon gestao-diff-icon--new" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="gestao-diff-icon-svg">
                            <path
                                d="M5 13l4 4L19 7"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>
                    <span class="gestao-diff-label-text">${label}</span>
                </span>
            `
        } else {
            thNew.textContent = label
        }

        const tdNew = document.createElement('td')
        tdNew.className = 'gestao-diff-cell-value'
        tdNew.textContent = newVal

        trNew.appendChild(thNew)
        trNew.appendChild(tdNew)
        newBody.appendChild(trNew)
    })

    oldTable.appendChild(oldHead)
    oldTable.appendChild(oldBody)
    newTable.appendChild(newHead)
    newTable.appendChild(newBody)

    oldWrapper.appendChild(oldTable)
    newWrapper.appendChild(newTable)

    oldEl.appendChild(oldWrapper)
    newEl.appendChild(newWrapper)
}

function compare(oldRecord, row) {
    const base = oldRecord || {}
    const newRecord = {...base}
    const changed = {}

    if (!row) return {newRecord, changed}

    Object.keys(DIFF_FIELD_LABELS).forEach(key => {
        const apiKey = key.startsWith('emergency_') ? key : 'profile_' + key
        if (!(apiKey in row)) return

        const oldValRaw = base[key]
        const newValRaw = row[apiKey]

        const oldVal = oldValRaw == null ? '' : String(oldValRaw)
        const newVal = newValRaw == null ? '' : String(newValRaw)

        newRecord[key] = newValRaw

        if (oldVal !== newVal) {
            changed[key] = true
        }
    })

    return {newRecord, changed}
}

async function loadDiffForRequest(userId) {
    const oldEl = document.getElementById('gestao-diff-old')
    const newEl = document.getElementById('gestao-diff-new')

    if (oldEl) oldEl.textContent = 'A carregar ficha atual...'
    if (newEl) newEl.textContent = 'A carregar pedido de alteração...'

    try {
        const [oldRes, newRes] = await Promise.all([
            getRecord(userId),
            getRecordChanges(userId),
        ])

        if (!oldRes || oldRes.success !== true) {
            throw new Error('Resposta inválida de aval_view_record')
        }

        if (!newRes || newRes.success !== true) {
            throw new Error('Resposta inválida de aval_requests')
        }

        const oldRecord = {
            ...(oldRes.profile || {}),
            ...(oldRes.emergency || {}),
        }

        const items = Array.isArray(newRes.items) ? newRes.items : []
        const row = items.find(r => String(r.user_id) === String(userId)) || null

        const {newRecord, changed} = compare(oldRecord, row)

        renderDiffView(oldRecord, newRecord, changed)
    } catch (err) {
        console.error('Falha ao carregar dados para diff:', err)

        if (oldEl) oldEl.textContent = 'Não foi possível carregar a ficha atual.'
        if (newEl) newEl.textContent = 'Não foi possível carregar o pedido de alteração.'
        toast.error('Não foi possível carregar a comparação.')
    }
}

async function sendDecision(decision) {
    if (!diffState.userId) return;

    const acceptBtn = document.getElementById('gestao-diff-accept');
    const rejectBtn = document.getElementById('gestao-diff-reject');

    if (acceptBtn) acceptBtn.disabled = true;
    if (rejectBtn) rejectBtn.disabled = true;

    try {
        const res = await createDecision(diffState.userId, decision);
        if (!res || res.success !== true) {
            console.error('Falha ao gravar decisão:', res);
            toast.error('Não foi possível gravar a decisão.');
            return;
        }
        toast.success(decision === 'approve' ? 'Pedido aprovado.' : 'Pedido recusado.');
        showApprovalsListView();
    } catch (err) {
        console.error('Erro na decisão:', err);
        toast.error('Erro ao gravar a decisão.');
    } finally {
        if (acceptBtn) acceptBtn.disabled = false;
        if (rejectBtn) rejectBtn.disabled = false;
    }
}

function showApprovalsListView() {
    const listEl = document.getElementById('gestao-approvals-list');
    const diffEl = document.getElementById('gestao-diff-view');
    if (listEl) listEl.classList.remove('gestao-approvals-list--hidden');
    if (diffEl) diffEl.classList.remove('gestao-diff-view--active');
}

function showDiffViewForRequest(userId, profileReqId, emergencyReqId, approvalsCache) {
    const listEl = document.getElementById('gestao-approvals-list');
    const diffEl = document.getElementById('gestao-diff-view');
    const subtitleEl = document.getElementById('gestao-diff-subtitle');

    diffState.userId = userId;
    diffState.profileReqId = profileReqId;
    diffState.emergencyReqId = emergencyReqId;

    if (listEl) listEl.classList.add('gestao-approvals-list--hidden');
    if (diffEl) diffEl.classList.add('gestao-diff-view--active');

    let desc = 'Pedido de alteração de ficha.';
    if (Array.isArray(approvalsCache)) {
        const row = approvalsCache.find(r => String(r.user_id) === String(userId));
        if (row) {
            const who = row.user_name || 'colaborador';
            desc = `Pedido de alteração da ficha de ${who}.`;
        }
    }
    if (subtitleEl) subtitleEl.textContent = desc;

    loadDiffForRequest(userId);
}

function bindDiffViewControls() {
    const backBtn = document.getElementById('gestao-diff-back');
    const acceptBtn = document.getElementById('gestao-diff-accept');
    const rejectBtn = document.getElementById('gestao-diff-reject');

    if (backBtn) {
        backBtn.addEventListener('click', () => {
            showApprovalsListView();
        });
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', () => {
            sendDecision('approve');
        });
    }

    if (rejectBtn) {
        rejectBtn.addEventListener('click', () => {
            sendDecision('reject');
        });
    }
}

export function openDiffForRequest(userId, profileReqId, emergencyReqId, approvalsCache) {
    showDiffViewForRequest(userId, profileReqId, emergencyReqId, approvalsCache);
}

export function mountGstFchsDiff() {
    bindDiffViewControls();
}
