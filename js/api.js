const API_BASE_URL = '/api/';

/**
 * Generic function to make API requests
 * @param endpoint - The API endpoint to call, e.g., 'login.php'
 * @param options - Fetch options like method, headers, body, etc.
 * @returns {Promise<any>} The response Promise
 */
async function apiFetch(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            headers: {
                ...options.headers
            },
            ...options
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
        body: JSON.stringify({ email, password })
    });
}

export function logout() {
    window.location.href = '../api/auth/logout.php';
}

export async function register(userData) {
    return apiFetch('register.php', {
        method: 'POST',
        body: JSON.stringify(userData)
    });
}

export async function getAllCollaborators() {
    return apiFetch('collab_management/collabs_list.php', {
        method: 'GET',
    });
}

export async function createDirectLeave(formData) {
    return apiFetch('leaves/direct_leave.php', {
        method: 'POST',
        body: formData
    });
}

export async function getSubsByUser(state = 'active', searchQuery = '') {
    const params = new URLSearchParams();
    if (state) params.append('state', state);
    if (searchQuery) params.append('q', searchQuery);

    return apiFetch(`collab_management/return_sub.php?${params.toString()}`, {
        method: 'GET'
    });
}