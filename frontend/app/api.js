const API_BASE_URL = '/backend/api/';

let authHandlers = null;
let handlingAuthFailure = false;

function assertFunction(value) {
    if (typeof value === 'function') return value;
    throw new Error('API auth handlers must be functions');
}

export function setApiAuthHandlers(handlers) {
    authHandlers = Object.freeze({
        unauthorized: assertFunction(handlers?.unauthorized),
        forbidden: assertFunction(handlers?.forbidden),
    });
}

export async function handleAuthStatus(status) {
    if (status !== 401 && status !== 403) return false;
    if (handlingAuthFailure) return true;
    if (!authHandlers) throw new Error('API auth handlers were not configured');

    handlingAuthFailure = true;

    try {
        if (status === 401) await authHandlers.unauthorized();
        else await authHandlers.forbidden();
    } finally {
        handlingAuthFailure = false;
    }

    return true;
}

function createHttpError(message, status) {
    const error = new Error(typeof message === 'string' ? message : JSON.stringify(message || 'Ocorreu um erro.'));
    error.status = status;
    return error;
}

async function getErrorMessage(response) {
    const contentType = response.headers.get('Content-Type') || '';
    const bodyText = await response.text();

    if (!contentType.includes('application/json')) {
        return bodyText.trim() || 'Ocorreu um erro.';
    }

    try {
        const data = JSON.parse(bodyText);
        return data?.message || data?.error || bodyText.trim() || 'Ocorreu um erro.';
    } catch {
        return bodyText.trim() || 'Ocorreu um erro.';
    }
}

async function parseResponse(response, responseType) {
    if (responseType === 'blob') return response.blob();
    if (responseType === 'text') return response.text();
    if (responseType === 'response') return response;

    const contentType = response.headers.get('Content-Type') || '';

    if (responseType === 'json' || contentType.includes('application/json')) {
        return response.json();
    }

    return response;
}

export async function apiFetch(endpoint, options = {}) {
    const {
        body,
        headers,
        responseType = 'auto',
        credentials = 'same-origin',
        ...rest
    } = options;

    const requestHeaders = new Headers(headers || {});
    const isJsonBody = body !== null && typeof body === 'object' && Object.getPrototypeOf(body) === Object.prototype;
    const requestBody = isJsonBody ? JSON.stringify(body) : body;

    if (isJsonBody) requestHeaders.set('Content-Type', 'application/json');
    if (!requestHeaders.has('Accept')) requestHeaders.set('Accept', 'application/json');

    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
        ...rest,
        credentials,
        headers: requestHeaders,
        body: requestBody,
    });

    if (await handleAuthStatus(response.status)) {
        throw createHttpError(await getErrorMessage(response), response.status);
    }

    if (!response.ok) {
        throw createHttpError(await getErrorMessage(response), response.status);
    }

    return parseResponse(response, responseType);
}

export function login(email, password) {
    return apiFetch('auth/login.php', {
        method: 'POST',
        body: {email, password},
    });
}

export function logout() {
    return apiFetch('auth/logout.php', {
        method: 'POST',
    });
}

export function register(userData) {
    return apiFetch('auth/register.php', {
        method: 'POST',
        body: userData,
    });
}

export function me() {
    return apiFetch('auth/me.php', {
        method: 'GET',
    });
}

export function updatePassword({old_password, new_password, confirm_password}) {
    return apiFetch('auth/password_update.php', {
        method: 'POST',
        body: {old_password, new_password, confirm_password},
    });
}

export function getAllCollaborators() {
    return apiFetch('collab_management/collabs_list.php', {
        method: 'GET',
    });
}

export function createCollaborator(formData) {
    return apiFetch('collab_management/create_collabs.php', {
        method: 'POST',
        body: formData,
    });
}

export function updatePermissions(userId, permissions = []) {
    return apiFetch('collab_management/permissions_update.php', {
        method: 'POST',
        body: {
            user_id: userId,
            permissions,
        },
    });
}

export function updateHierarchy(userId, responsaveis = [], subs = []) {
    return apiFetch('collab_management/hierarchy_update.php', {
        method: 'POST',
        body: {
            user_id: userId,
            responsaveis,
            subs,
        },
    });
}

export function createDirectLeave(formData) {
    return apiFetch('leaves/direct_leave.php', {
        method: 'POST',
        body: formData,
    });
}

export function getHierarchyByUser(userId = null) {
    const params = new URLSearchParams();
    if (userId) params.append('user_id', String(userId));

    return apiFetch(`collab_management/get_hierarchy.php?${params.toString()}`, {
        method: 'GET',
    });
}

export function getAllPendingRequests(q = '', page = 1, pageSize = 20) {
    const params = new URLSearchParams();

    if (q.trim()) params.append('q', q.trim());
    params.append('page', String(page));
    params.append('page_size', String(pageSize));

    return apiFetch(`employee_info/record/aval_requests.php?${params.toString()}`, {
        method: 'GET',
    });
}

export function getAllRecords({
                                  page = 1,
                                  pageSize = 25,
                                  q = '',
                                  companyId = null,
                                  orderBy = 'name',
                                  orderDir = 'asc',
                              } = {}) {
    const params = new URLSearchParams();

    params.append('page', String(page));
    params.append('page_size', String(pageSize));

    if (q.trim()) params.append('q', q.trim());
    if (companyId != null) params.append('company_id', String(companyId));
    if (orderBy) params.append('order_by', orderBy);
    if (orderDir) params.append('order_dir', orderDir);

    return apiFetch(`employee_info/aval_list_all.php?${params.toString()}`, {
        method: 'GET',
    });
}

export function getRecord(userId) {
    const params = new URLSearchParams();
    params.append('user_id', String(userId));

    return apiFetch(`employee_info/record/aval_view_record.php?${params.toString()}`, {
        method: 'GET',
    });
}

export function getRecordChanges(userId) {
    const params = new URLSearchParams();
    params.append('user_id', String(userId));

    return apiFetch(`employee_info/record/aval_requests.php?${params.toString()}`, {
        method: 'GET',
    });
}

export function createDecision(userId, decision) {
    return apiFetch('employee_info/record/aval_decision.php', {
        method: 'POST',
        body: {
            user_id: userId,
            decision,
        },
    });
}

export function updateRecord(userId, data = {}, syncUserEmail = false) {
    const body = {
        user_id: userId,
        ...data,
    };

    if (syncUserEmail) body.sync_user_email = 1;

    return apiFetch('employee_info/record/direct_edit.php', {
        method: 'POST',
        body,
    });
}

export function getSelfRecord() {
    return apiFetch('employee_info/view_self.php', {
        method: 'GET',
    });
}

export function createRecordRequest(payload) {
    return apiFetch('employee_info/record/collab_request.php', {
        method: 'POST',
        body: payload,
    });
}

export function requestOvertime({user_id, dia, hora_inicio, hora_fim, justificacao}) {
    return apiFetch('overtime/request_overtime.php', {
        method: 'POST',
        body: {
            user_id,
            dia,
            hora_inicio,
            hora_fim,
            justificacao,
        },
    });
}

export function getOvertimeRequests({state = 'all', month, user_id, q, limit, offset} = {}) {
    const params = new URLSearchParams();

    if (state) params.append('state', state);
    if (month) params.append('month', month);
    if (user_id) params.append('user_id', String(user_id));
    if (q) params.append('q', q);
    if (limit) params.append('limit', String(limit));
    if (offset) params.append('offset', String(offset));

    return apiFetch(`overtime/request_list.php?${params.toString()}`, {
        method: 'GET',
    });
}

export function approveOvertime({request_id, decision, comentario}) {
    return apiFetch('overtime/approve_overtime.php', {
        method: 'POST',
        body: {
            request_id,
            decision,
            comentario,
        },
    });
}

export function getOvertimeHistory({month, state = 'both', q, user_id, limit, offset} = {}) {
    const params = new URLSearchParams();

    if (month) params.append('month', month);
    if (state) params.append('state', state);
    if (q) params.append('q', q);
    if (user_id) params.append('user_id', String(user_id));
    if (limit) params.append('limit', String(limit));
    if (offset) params.append('offset', String(offset));

    return apiFetch(`overtime/sheets_review.php?${params.toString()}`, {
        method: 'GET',
    });
}

export function exportOvertimeSheets(month, userIds = []) {
    const params = new URLSearchParams();

    params.append('month', month);
    if (userIds.length) params.append('user_ids', userIds.join(','));

    return apiFetch(`overtime/sheets_export.php?${params.toString()}`, {
        method: 'GET',
        responseType: 'blob',
    });
}

export function getCalendarTimeframe(from, to) {
    const params = new URLSearchParams();

    if (from) params.append('from', String(from));
    if (to) params.append('to', String(to));

    return apiFetch(`calendar/get_month.php${params.toString() ? `?${params.toString()}` : ''}`, {
        method: 'GET',
    });
}

export function createEventBatch(start, end, {workMin, km, applyWeekend = false, overwrite = true} = {}) {
    return apiFetch('calendar/batch_apply.php', {
        method: 'POST',
        body: {
            start,
            end,
            workMin,
            km,
            applyWeekend,
            overwrite,
        },
    });
}

export function createEventByDay(date, opts) {
    return createEventBatch(String(date), String(date), opts);
}