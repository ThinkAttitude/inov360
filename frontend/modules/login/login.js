import { login } from '../../app/api.js';
import {navigate, Path} from "../../app/router.js";
import {User} from "../../shared/user_store.js";
import { toast } from '../../shared/ui/toast/toast.js';

import './styles.css';

const state = {
    isLoading: false
};

const getElements = () => ({
    form: document.querySelector('#loginForm'),
    emailInput: document.querySelector('#email'),
    passwordInput: document.querySelector('#password'),
    passwordToggle: document.querySelector('#passwordToggle'),
    submitButton: document.querySelector('#submitBtn')
});

const EYE_OPEN_SVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
const EYE_OFF_SVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.77 19.77 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a19.86 19.86 0 0 1-4.06 4.94"></path><path d="M1 1l22 22"></path><path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"></path></svg>`;

const togglePasswordVisibility = () => {
    const { passwordInput, passwordToggle } = getElements();
    if (!passwordInput || !passwordToggle) return;
    const showing = passwordInput.type === 'text';
    passwordInput.type = showing ? 'password' : 'text';
    passwordToggle.innerHTML = showing ? EYE_OPEN_SVG : EYE_OFF_SVG;
    passwordToggle.setAttribute('aria-label', showing ? 'Mostrar palavra-passe' : 'Esconder palavra-passe');
};

const setLoadingState = (isLoading) => {
    state.isLoading = isLoading;
    const { submitButton } = getElements();
    submitButton.disabled = isLoading;
    submitButton.textContent = isLoading ? 'Logging in...' : 'Login';
};

const showError = (message) => {
    toast.error(message || 'Ocorreu um erro ao iniciar sessão.');
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
    const { form, passwordToggle } = getElements();
    form?.addEventListener('submit', handleSubmit);
    passwordToggle?.addEventListener('click', togglePasswordVisibility);
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
