const API_BASE_URL = '/../../backend/api/';

/**
 * Generic function to make API requests
 * @param endpoint - The API endpoint to call, e.g., 'login.php'
 * @param options - Fetch options like method, headers, body, etc.
 * @returns {Promise<any>} The response Promise
 */
async function apiFetch(endpoint, options = {}) {
    const { body, headers, ...rest } = options;
    const h = new Headers(headers || {});
    const isPlainObject = (v) => {
        return v !== null && typeof v === 'object' && Object.getPrototypeOf(v) === Object.prototype;
    };

    if (isPlainObject(body)) {
        h.set('Content-Type', 'application/json');
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            ...rest,
            headers: h,
            body: options.body,
        });

        const ct = response.headers.get('Content-Type') || '';
        if (ct.includes('application/json')) return await response.json();

        return response;
    } catch (error) {
        console.error(`API Error (${endpoint}):`, error);
        throw error;
    }
}

export async function login(email, password) {
    return apiFetch('auth/login.php', {
        method: 'POST',
        body: { email, password }
    });
}

export function logout() {
    window.location.href = '../../backend/api/auth/logout.php';
}

export async function register(userData) {
    return apiFetch('auth/register.php', {
        method: 'POST',
        body: userData
    });
}

export async function me() {
    return apiFetch('auth/me.php', {
        method: 'GET',
    });
}

/* Collaborator Management (controlo_collabs) */
export async function getAllCollaborators() {
    return apiFetch('collab_management/collabs_list.php', {
        method: 'GET',
    });
}

export async function createCollaborator(formData) {
    return apiFetch('collab_management/create_collabs.php', {
        method: 'POST',
        body: formData,
    });
}

export async function updatePermissions(userId, permissions = []) {
    return apiFetch('collab_management/permissions_update.php', {
        method: 'POST',
        body: {
            user_id: userId,
            permissions
        }
    });
}

export async function updateHierarchy(userId, responsaveis = [], subs = []) {
    return apiFetch('collab_management/hierarchy_update.php', {
        method: 'POST',
        body: {
            user_id: userId,
            responsaveis,
            subs,
        }
    });
}

export async function createDirectLeave(formData) {
    return apiFetch('leaves/direct_leave.php', {
        method: 'POST',
        body: formData
    });
}

export async function getHierarchyByUser(userId = null) {
    const params = new URLSearchParams();
    if (userId) params.append('user_id', String(userId));

    return apiFetch(`collab_management/get_hierarchy.php?${params.toString()}`, {
        method: 'GET'
    });
}

/* Record Management (gestao_fichas) */
export async function getAllPendingRequests(q = '', page = 1, pageSize = 20) {
    const params = new URLSearchParams();

    if (q && q.trim() !== '') params.append('q', q.trim());
    params.append('page', String(page));
    params.append('page_size', String(pageSize));

    return apiFetch(`employee_info/record/aval_requests.php?${params.toString()}`, {
        method: 'GET',
    });
}

export async function getAllRecords({
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

    if (q && q.trim() !== '') {
        params.append('q', q.trim());
    }

    if (companyId != null) {
        params.append('company_id', String(companyId));
    }

    if (orderBy) {
        params.append('order_by', orderBy);
    }

    if (orderDir) {
        params.append('order_dir', orderDir);
    }

    return apiFetch(`employee_info/aval_list_all.php?${params.toString()}`, {
        method: 'GET',
    });
}

export async function getRecord(userId) {
    const params = new URLSearchParams();
    params.append('user_id', String(userId));

    return apiFetch(`employee_info/record/aval_view_record.php?${params.toString()}`, {
        method: 'GET',
    });
}

export async function getRecordChanges(userId) {
    const params = new URLSearchParams();
    params.append('user_id', String(userId));

    return apiFetch(`employee_info/record/aval_requests.php?${params.toString()}`, {
        method: 'GET',
    });
}

export async function createDecision(userId, decision) {
    return apiFetch('employee_info/record/aval_decision.php', {
        method: 'POST',
        body: {
            user_id: userId,
            decision
        }
    });
}

export async function updateRecord(userId, data = {}, syncUserEmail = false) {
    const body = {
        user_id: userId,
        ...data,
    };

    if (syncUserEmail) {
        body.sync_user_email = 1;
    }

    return apiFetch('employee_info/record/direct_edit.php', {
        method: 'POST',
        body,
    });
}

/* My Record (ficha_collabs) */
export async function getSelfRecord() {
    return apiFetch('employee_info/view_self.php', {
        method: 'GET'
    });
}

export async function createRecordRequest(payload) {
    return apiFetch('employee_info/record/collab_request.php', {
        method: 'POST',
        body: payload
    });
}

/* Schedule Management (horarios) */
export async function getCalendarTimeframe(from, to) {
    const params = new URLSearchParams();

    if (from) params.append('from', String(from)); // YYYY-MM-DD
    if (to) params.append('to', String(to));       // YYYY-MM-DD

    const qs = params.toString();
    return apiFetch(`calendar/get_month.php${qs ? `?${qs}` : ''}`, {
        method: 'GET',
    });
}

export async function createEventBatch(start, end, {workMin, km, applyWeekend = false, overwrite = true} = {}) {
    return apiFetch('calendar/batch_apply.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: {
            start,
            end,
            workMin,
            km,
            applyWeekend,
            overwrite
        },
    });
}

export async function createEventByDay(date, opts) {
    const d = String(date);
    return createEventBatch(d, d, opts);
}