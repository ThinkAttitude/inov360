// Page Login - Modern UI/UX JavaScript

class LoginPage {
    constructor() {
        this.form = document.getElementById('loginForm');
        this.emailInput = document.getElementById('email');
        this.passwordInput = document.getElementById('password');
        this.submitBtn = document.getElementById('submitBtn');
        this.passwordToggle = document.getElementById('passwordToggle');

        this.init();
    }

    init() {
        // Clear any persisted company theme when arriving at login
        try {
            document.cookie = 'rh360_company=; Max-Age=0; path=/';
        } catch(e) {}
        this.setupEventListeners();
        this.setupFormValidation();
        this.createToastContainer();
    }

    setupEventListeners() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Password toggle
        this.passwordToggle.addEventListener('click', () => this.togglePassword());

        // Input focus effects
        this.setupInputEffects();

        // Enter key handling
        this.emailInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.passwordInput.focus();
            }
        });
    }

    setupInputEffects() {
        [this.emailInput, this.passwordInput].forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('focused');
            });
        });
    }

    togglePassword() {
        const type = this.passwordInput.type === 'password' ? 'text' : 'password';
        this.passwordInput.type = type;

        const icon = this.passwordToggle.querySelector('svg');
        if (type === 'text') {
            icon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <path d="M1 1l22 22"></path>
            `;
        } else {
            icon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        }
    }

    setupFormValidation() {
        // Real-time validation
        this.emailInput.addEventListener('input', () => this.validateEmail());
        this.passwordInput.addEventListener('input', () => this.validatePassword());
    }

    validateEmail() {
        const email = this.emailInput.value;
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        this.toggleFieldValidation(this.emailInput, isValid || email === '');
        return isValid;
    }

    validatePassword() {
        const password = this.passwordInput.value;
        const isValid = password.length >= 1; // Mantendo validação simples para não alterar funcionamento

        this.toggleFieldValidation(this.passwordInput, isValid || password === '');
        return isValid;
    }

    toggleFieldValidation(field, isValid) {
        const wrapper = field.closest('.input-wrapper');
        wrapper.classList.toggle('error', !isValid);
        wrapper.classList.toggle('valid', isValid && field.value !== '');
    }

    async handleSubmit(e) {
        e.preventDefault();

        // Validate form
        const isEmailValid = this.validateEmail();
        const isPasswordValid = this.validatePassword();

        if (!isEmailValid || !isPasswordValid) {
            this.showToast('error', 'Erro de Validação', 'Por favor, verifique os dados inseridos.');
            return;
        }

        // Show loading state
        this.setLoadingState(true);

        try {
            // Submit form data
            const formData = new FormData(this.form);
            const response = await fetch(this.form.action, {
                method: 'POST',
                body: formData,
                credentials: 'include' // ensure session cookie is stored
            });

            // Parse JSON response
            const result = await response.json();

        if (result.success) {
                // Login successful
                this.showToast('success', 'Login Realizado', result.message || 'A redireccionar...');
                setTimeout(() => {
            const buster = 'r=' + Date.now();
            const sep = result.redirect.includes('?') ? '&' : '?';
            // Add clearTheme=1 to instruct theme.js to ignore cookie fallback on first load
            window.location.href = `${result.redirect}${sep}clearTheme=1&${buster}`;
                }, 1000);
            } else {
                // Login failed
                this.showToast('error', 'Erro de Login', result.message || 'Credenciais inválidas.');
            }

        } catch (error) {
            console.error('Login error:', error);
            this.showToast('error', 'Erro de Conexão', 'Não foi possível conectar ao servidor. Tente novamente.');
        } finally {
            this.setLoadingState(false);
        }
    }

    setLoadingState(loading) {
        this.submitBtn.disabled = loading;
        this.submitBtn.classList.toggle('loading', loading);

        if (loading) {
            this.submitBtn.querySelector('.btn-text').style.display = 'none';
            this.submitBtn.querySelector('.spinner').style.display = 'block';
        } else {
            this.submitBtn.querySelector('.btn-text').style.display = 'flex';
            this.submitBtn.querySelector('.spinner').style.display = 'none';
        }
    }

    createToastContainer() {
        if (!document.querySelector('.toast-container')) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
    }

    showToast(type, title, message, duration = 5000) {
        const container = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const icons = {
            success: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"></polyline></svg>`,
            error: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
            warning: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`
        };

        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;

        // Close button functionality
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.removeToast(toast);
        });

        container.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
            this.removeToast(toast);
        }, duration);
    }

    removeToast(toast) {
        if (toast && toast.parentElement) {
            toast.classList.add('removing');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, 300);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new LoginPage();
});

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
