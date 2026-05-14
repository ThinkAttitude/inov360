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

const showToast = (message, type = 'error') => {
    if (!message) return;
    const existing = document.querySelector('.toast.login-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast ${type} login-toast`;
    toast.setAttribute('role', 'alert');
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 320);
    }, 3500);
};

const showError = (message) => {
    showToast(message || 'Ocorreu um erro ao iniciar sessão.', 'error');
};

const handleSubmit = async (event) => {
    event.preventDefault();
    if (state.isLoading) return;

    const { emailInput, passwordInput } = getElements();
    setLoadingState(true);

    try {
        const response = await login(emailInput.value, passwordInput.value);
        if (response && response.success) {
            User.set(response.user);
            navigate(Path.INICIO);
            return;
        }
        showError(response?.message || 'Email ou palavra-passe incorretos.');
        passwordInput.value = '';
        passwordInput.focus();
    } catch (error) {
        showError(error.message);
        passwordInput.value = '';
        passwordInput.focus();
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
