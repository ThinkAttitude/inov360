import { login } from '../../app/api.js';
import {navigate, Path} from "../../app/router.js";
import {User} from "../../shared/user_store.js";

import './styles.css';

const state = {
    isLoading: false
};

const getElements = () => ({
    form: document.querySelector('#loginForm'),
    emailInput: document.querySelector('#email'),
    passwordInput: document.querySelector('#password'),
    submitButton: document.querySelector('#submitBtn')
});

const setLoadingState = (isLoading) => {
    state.isLoading = isLoading;
    const { submitButton } = getElements();
    submitButton.disabled = isLoading;
    submitButton.textContent = isLoading ? 'Logging in...' : 'Login';
};

// TODO: Implement a better error display mechanism
const showError = (message) => {
    console.error(message);
};

const handleSubmit = async (event) => {
    event.preventDefault();
    if (state.isLoading) return;

    const { emailInput, passwordInput } = getElements();
    setLoadingState(true);

    try {
        // TODO: Get user via response instead of using me()
        const response = await login(emailInput.value, passwordInput.value);
        User.set((response.user))
        if (response.success) navigate(Path.INICIO);
    } catch (error) {
        showError(error.message);
    } finally {
        setLoadingState(false);
    }
};

const init = () => {
    const { form } = getElements();
    form?.addEventListener('submit', handleSubmit);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Add some CSS classes dynamically for validation states
const style = document.createElement('style');
style.textContent = `
    .input-wrapper.error .form-input {
        border-color: #ef4444;
        background-color: #fef2f2;
    }
    
    .input-wrapper.error .input-icon {
        color: #ef4444;
    }
    
    .input-wrapper.valid .form-input {
        border-color: #10b981;
    }
    
    .input-wrapper.valid .input-icon {
        color: #10b981;
    }
    
    .input-wrapper.focused .form-input {
        transform: translateY(-1px);
    }
`;
document.head.appendChild(style);
