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
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error(`API Error (${endpoint}):`, error);
        throw error;
    }
}

export async function login(email, password) {
    return apiFetch('login.php', {
        method: 'POST',
        body: JSON.stringify({ email, password })
    });
}

export async function register(userData) {
    return apiFetch('register.php', {
        method: 'POST',
        body: JSON.stringify(userData)
    });
}

export async function getCollabsByUser(state = 'active', searchQuery = '') {
    const params = new URLSearchParams();
    if (state) params.append('state', state);
    if (searchQuery) params.append('q', searchQuery);

    return apiFetch(`collab_management/get_collabs_of_user.php${params.toString()}`, {
        method: 'GET'
    });
}