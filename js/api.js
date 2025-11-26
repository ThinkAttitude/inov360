const API_BASE_URL = '/api/';

/**
 * Generic function to make API requests
 * @param endpoint - The API endpoint to call, e.g., 'login.php'
 * @param options - Fetch options like method, headers, body, etc.
 * @returns {Promise<any>} The response Promise
 */
async function apiFetch(endpoint, options = {}) {
    const { body, headers, ...rest } = options;
    const h = new Headers(headers || {});
    const isPlainObject = (v) => { return v !== null && typeof v === 'object' && Object.getPrototypeOf(v) === Object.prototype; };

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
    window.location.href = '../api/auth/logout.php';
}

export async function register(userData) {
    return apiFetch('register.php', {
        method: 'POST',
        body: userData
    });
}

/* Collaborator Management */
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