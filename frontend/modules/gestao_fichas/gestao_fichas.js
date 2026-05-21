import {getAllPendingRequests, getAllRecords} from '../../app/api.js'
import {openDiffForRequest, mountGstFchsDiff} from './gestao_fichas_diff.js'
import {openRecordForUser, mountGstFchsRecord} from './gestao_fichas_records.js'
import { toast } from '../../shared/ui/toast/toast.js'

import './styles.css'
import './records.css'
import './diff.css'

let approvalsCache = null
let allRecsCache = null

const ALLRECS_PAGE_SIZE = 25
const allRecsState = {
    page: 1,
    pageSize: ALLRECS_PAGE_SIZE,
    q: '',
    total: 0,
    totalPages: 1,
    loading: false,
}
let allRecsSearchTimer = null

function formatRequestDate(raw) {
    if (!raw) return '-'

    const date = new Date(raw.replace(' ', 'T'))
    if (Number.isNaN(date.getTime())) return raw

    const now = new Date()
    const diffMs = now.getTime() - date.getTime()
    const dayMs = 24 * 60 * 60 * 1000
    const days = Math.floor(diffMs / dayMs)
    const timeStr = date.toLocaleTimeString('pt-PT', {hour: '2-digit', minute: '2-digit'})

    if (days === 0) return `Hoje às ${timeStr}`
    if (days === 1) return `Ontem às ${timeStr}`
    if (days < 7) return `Há ${days} dia${days > 1 ? 's' : ''} às ${timeStr}`

    const dateStr = date.toLocaleDateString('pt-PT')
    return `${dateStr} às ${timeStr}`
}

function setActiveTab(which) {
    const tabPedidos = document.getElementById('gestao-aprovacoes')
    const tabFichas = document.getElementById('gestao-visualizar-fichas')

    if (!tabPedidos || !tabFichas) return

    const isPedidos = which === 'pedidos'
    tabPedidos.classList.toggle('gestao-tab--active', isPedidos)
    tabFichas.classList.toggle('gestao-tab--active', !isPedidos)
}

function updateNavButtons() {
    const navBack = document.getElementById('gestao-nav-back')
    const navForward = document.getElementById('gestao-nav-forward')
    if (!navBack || !navForward) return

    const approvalsView = document.getElementById('gestao-view-approvals')
    const allrecsView = document.getElementById('gestao-view-allrecs')
    const diffView = document.getElementById('gestao-diff-view')
    const recordView = document.getElementById('gestao-record-view')
    const approvalsList = document.getElementById('gestao-approvals-list')
    const allrecsList = document.getElementById('gestao-allrecs-list')

    const approvalsActive = approvalsView && approvalsView.classList.contains('gestao-view--active')
    const allrecsActive = allrecsView && allrecsView.classList.contains('gestao-view--active')
    const diffActive = diffView && diffView.classList.contains('gestao-diff-view--active')
    const recordActive = recordView && recordView.classList.contains('gestao-record-view--active')

    const approvalsListVisible = approvalsList && !approvalsList.classList.contains('gestao-approvals-list--hidden')
    const allrecsListVisible = allrecsList && !allrecsList.classList.contains('gestao-allrecs-list--hidden')

    const canBackFromDetail = diffActive || recordActive
    const canBackFromTab = allrecsActive && allrecsListVisible && !recordActive
    const canBack = canBackFromDetail || canBackFromTab

    const canForwardFromPedidos = approvalsActive && approvalsListVisible && Array.isArray(approvalsCache) && approvalsCache.length > 0
    const canForwardFromFichas = allrecsActive && allrecsListVisible && Array.isArray(allRecsCache) && allRecsCache.length > 0

    navBack.disabled = !canBack
    navForward.disabled = !(canForwardFromPedidos || canForwardFromFichas)
}

function buildPendingReqs(rows) {
    const tbody = document.getElementById('gestao-approvals-tbody')
    const emptyEl = document.getElementById('gestao-approvals-empty')
    if (!tbody || !emptyEl) return

    tbody.innerHTML = ''

    if (!Array.isArray(rows) || rows.length === 0) {
        emptyEl.textContent = 'Não existem pedidos pendentes de aprovação.'
        emptyEl.style.display = 'block'
        return
    }

    emptyEl.style.display = 'none'

    rows.forEach(row => {
        const tr = document.createElement('tr')
        const requestedAt = row.profile_created_at || row.emergency_created_at || ''
        const formattedDate = formatRequestDate(requestedAt)

        tr.dataset.userId = String(row.user_id)

        if (row.profile_req_id != null) tr.dataset.profileReqId = String(row.profile_req_id)
        if (row.emergency_req_id != null) tr.dataset.emergencyReqId = String(row.emergency_req_id)

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
        `

        tbody.appendChild(tr)
    })
}

function bindPendingRequestsInteraction() {
    const tbody = document.getElementById('gestao-approvals-tbody')
    if (!tbody) return

    tbody.addEventListener('click', event => {
        const row = event.target.closest('tr[data-user-id]')
        if (!row) return

        const userId = row.dataset.userId
        const profileReqId = row.dataset.profileReqId || null
        const emergencyReqId = row.dataset.emergencyReqId || null

        openDiffForRequest(userId, profileReqId, emergencyReqId, approvalsCache)
        updateNavButtons()
    })
}

async function renderPendingReqs() {
    const emptyEl = document.getElementById('gestao-approvals-empty')
    const btn = document.getElementById('gestao-aprovacoes')

    if (btn) btn.disabled = true

    if (emptyEl) {
        emptyEl.textContent = 'A carregar pedidos pendentes...'
        emptyEl.style.display = 'block'
    }

    try {
        const res = await getAllPendingRequests()

        if (!res || res.success !== true || !Array.isArray(res.items)) {
            throw new Error('Invalid response')
        }

        approvalsCache = res.items
        buildPendingReqs(approvalsCache)
    } catch (err) {
        console.error('Failed to load pending requests:', err)
        approvalsCache = []
        buildPendingReqs(approvalsCache)

        if (emptyEl) {
            emptyEl.textContent = 'Não foi possível carregar os pedidos pendentes.'
            emptyEl.style.display = 'block'
        }
        toast.error(err?.message || 'Não foi possível carregar os pedidos pendentes.')
    } finally {
        if (btn) btn.disabled = false
        updateNavButtons()
    }
}

function showPendingRecsView() {
    const approvalsView = document.getElementById('gestao-view-approvals')
    const recordsView = document.getElementById('gestao-view-allrecs')
    if (!approvalsView) return

    setActiveTab('pedidos')

    const alreadyActive = approvalsView.classList.contains('gestao-view--active')

    if (!alreadyActive) {
        approvalsView.classList.add('gestao-view--active')
        if (recordsView) recordsView.classList.remove('gestao-view--active')
    }

    if (approvalsCache !== null) {
        buildPendingReqs(approvalsCache)
        updateNavButtons()
        return
    }

    updateNavButtons()
    renderPendingReqs()
}

function buildAllRecs(rows) {
    const tbody = document.getElementById('gestao-allrecs-tbody')
    const emptyEl = document.getElementById('gestao-allrecs-empty')
    if (!tbody || !emptyEl) return

    tbody.innerHTML = ''

    if (!Array.isArray(rows) || rows.length === 0) {
        emptyEl.textContent = 'Não existem colaboradores para apresentar.'
        emptyEl.style.display = 'block'
        return
    }

    emptyEl.style.display = 'none'

    rows.forEach(row => {
        const tr = document.createElement('tr')
        const collabName = row.name || ''
        const companyName = row.company?.name || ''

        tr.dataset.userId = String(row.id ?? '')
        tr.dataset.userName = collabName
        tr.dataset.userEmail = row.email || ''

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
        `

        tbody.appendChild(tr)
    })
}

function bindAllRecsInteraction() {
    const tbody = document.getElementById('gestao-allrecs-tbody')
    if (!tbody) return

    tbody.addEventListener('click', event => {
        const row = event.target.closest('tr[data-user-id]')
        if (!row) return

        const userId = row.dataset.userId || null
        const name = row.dataset.userName || ''
        const email = row.dataset.userEmail || ''

        openRecordForUser(userId, name, email)
        updateNavButtons()
    })
}

async function renderAllRecs() {
    const emptyEl = document.getElementById('gestao-allrecs-empty')

    if (emptyEl) {
        emptyEl.textContent = 'A carregar colaboradores...'
        emptyEl.style.display = 'block'
    }

    allRecsState.loading = true
    updateAllRecsPagination()

    try {
        const res = await getAllRecords({
            page: allRecsState.page,
            pageSize: allRecsState.pageSize,
            q: allRecsState.q,
        })

        if (!res || res.success !== true || !Array.isArray(res.items)) {
            throw new Error('Invalid response')
        }

        allRecsState.total = Number.isFinite(res.total) ? res.total : res.items.length
        allRecsState.totalPages = Math.max(1, Number.isFinite(res.total_pages) ? res.total_pages : 1)

        if (allRecsState.page > allRecsState.totalPages) {
            allRecsState.page = allRecsState.totalPages
        }

        allRecsCache = res.items
        buildAllRecs(allRecsCache)

        if (allRecsCache.length === 0 && emptyEl) {
            emptyEl.textContent = allRecsState.q
                ? 'Nenhum colaborador corresponde à pesquisa.'
                : 'Não existem colaboradores para apresentar.'
            emptyEl.style.display = 'block'
        }
    } catch (err) {
        console.error('Failed to load all records:', err)
        allRecsCache = []
        allRecsState.total = 0
        allRecsState.totalPages = 1
        buildAllRecs(allRecsCache)

        if (emptyEl) {
            emptyEl.textContent = 'Não foi possível carregar os colaboradores.'
            emptyEl.style.display = 'block'
        }
        toast.error(err?.message || 'Não foi possível carregar a lista de colaboradores.')
    } finally {
        allRecsState.loading = false
        updateAllRecsPagination()
        updateNavButtons()
    }
}

function updateAllRecsPagination() {
    const prevBtn = document.getElementById('gestao-allrecs-prev')
    const nextBtn = document.getElementById('gestao-allrecs-next')
    const pageInfo = document.getElementById('gestao-allrecs-page-info')
    const summary = document.getElementById('gestao-allrecs-summary')
    const pagination = document.getElementById('gestao-allrecs-pagination')

    const {page, totalPages, total, pageSize, loading} = allRecsState

    if (pagination) {
        pagination.style.display = total > 0 ? 'flex' : 'none'
    }

    if (pageInfo) pageInfo.textContent = `Página ${page} de ${totalPages}`
    if (prevBtn) prevBtn.disabled = loading || page <= 1
    if (nextBtn) nextBtn.disabled = loading || page >= totalPages

    if (summary) {
        if (total === 0) {
            summary.textContent = ''
        } else {
            const start = (page - 1) * pageSize + 1
            const end = Math.min(page * pageSize, total)
            summary.textContent = `A mostrar ${start}–${end} de ${total}`
        }
    }
}

function bindAllRecsControls() {
    const searchInput = document.getElementById('gestao-allrecs-search')
    const prevBtn = document.getElementById('gestao-allrecs-prev')
    const nextBtn = document.getElementById('gestao-allrecs-next')

    if (searchInput) {
        searchInput.addEventListener('input', event => {
            const value = String(event.target.value || '')
            if (allRecsSearchTimer) clearTimeout(allRecsSearchTimer)
            allRecsSearchTimer = setTimeout(() => {
                allRecsState.q = value
                allRecsState.page = 1
                renderAllRecs()
            }, 300)
        })
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (allRecsState.loading || allRecsState.page <= 1) return
            allRecsState.page -= 1
            renderAllRecs()
        })
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (allRecsState.loading || allRecsState.page >= allRecsState.totalPages) return
            allRecsState.page += 1
            renderAllRecs()
        })
    }
}

function showAllRecsView() {
    const approvalsView = document.getElementById('gestao-view-approvals')
    const otherView = document.getElementById('gestao-view-allrecs')
    if (!otherView) return

    setActiveTab('fichas')

    const alreadyActive = otherView.classList.contains('gestao-view--active')

    if (!alreadyActive) {
        otherView.classList.add('gestao-view--active')
        if (approvalsView) approvalsView.classList.remove('gestao-view--active')
    }

    if (allRecsCache !== null) {
        buildAllRecs(allRecsCache)
        updateAllRecsPagination()
        updateNavButtons()
        return
    }

    updateNavButtons()
    renderAllRecs()
}

function handleNavBack() {
    const diffView = document.getElementById('gestao-diff-view')
    const recordView = document.getElementById('gestao-record-view')
    const approvalsList = document.getElementById('gestao-approvals-list')
    const allrecsList = document.getElementById('gestao-allrecs-list')

    if (diffView && diffView.classList.contains('gestao-diff-view--active')) {
        diffView.classList.remove('gestao-diff-view--active')
        if (approvalsList) approvalsList.classList.remove('gestao-approvals-list--hidden')
        updateNavButtons()
        return
    }

    if (recordView && recordView.classList.contains('gestao-record-view--active')) {
        recordView.classList.remove('gestao-record-view--active')
        if (allrecsList) allrecsList.classList.remove('gestao-allrecs-list--hidden')
        updateNavButtons()
        return
    }

    const allrecsView = document.getElementById('gestao-view-allrecs')

    if (allrecsView && allrecsView.classList.contains('gestao-view--active')) {
        showPendingRecsView()
    }
}

function handleNavForward() {
    const approvalsView = document.getElementById('gestao-view-approvals')
    const allrecsView = document.getElementById('gestao-view-allrecs')

    const approvalsActive = approvalsView && approvalsView.classList.contains('gestao-view--active')
    const allrecsActive = allrecsView && allrecsView.classList.contains('gestao-view--active')

    if (approvalsActive && Array.isArray(approvalsCache) && approvalsCache.length > 0) {
        const first = approvalsCache[0]

        openDiffForRequest(
            first.user_id,
            first.profile_req_id != null ? first.profile_req_id : null,
            first.emergency_req_id != null ? first.emergency_req_id : null,
            approvalsCache
        )

        updateNavButtons()
        return
    }

    if (allrecsActive && Array.isArray(allRecsCache) && allRecsCache.length > 0) {
        const first = allRecsCache[0]
        openRecordForUser(first.id ?? null, first.name || '', first.email || '')
        updateNavButtons()
    }
}

function bindNavButtons() {
    const backBtn = document.getElementById('gestao-nav-back')
    const forwardBtn = document.getElementById('gestao-nav-forward')

    if (backBtn) {
        backBtn.addEventListener('click', () => {
            if (backBtn.disabled) return
            handleNavBack()
        })
    }

    if (forwardBtn) {
        forwardBtn.addEventListener('click', () => {
            if (forwardBtn.disabled) return
            handleNavForward()
        })
    }
}

function bindTabs() {
    const btnApprovals = document.getElementById('gestao-aprovacoes')
    const btnRecords = document.getElementById('gestao-visualizar-fichas')

    if (btnApprovals) {
        btnApprovals.addEventListener('click', () => {
            showPendingRecsView()
        })
    }

    if (btnRecords) {
        btnRecords.addEventListener('click', () => {
            showAllRecsView()
        })
    }
}

export function mountGstFchs() {
    bindTabs()
    bindNavButtons()
    bindPendingRequestsInteraction()
    bindAllRecsInteraction()
    bindAllRecsControls()
    mountGstFchsDiff()
    mountGstFchsRecord()
    updateAllRecsPagination()
    updateNavButtons()
}