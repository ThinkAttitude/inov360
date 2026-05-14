/**
 * Toast notification utility.
 * Singleton container in the top-right of the viewport, vertical stack.
 * All toasts auto-dismiss after `duration` ms (default 4000).
 *
 * Usage:
 *   import { toast } from '../../shared/ui/toast.js';
 *   toast.success('Pedido submetido.');
 *   toast.error('Não foi possível guardar.');
 *   toast.warning('Sessão prestes a expirar.');
 *   toast.info('A sincronizar...');
 *
 * Or detailed:
 *   toast.show({ type: 'error', title: 'Falha', message: '...', duration: 6000 });
 */

import './toast.css';

const CONTAINER_ID = 'app-toast-container';
const DEFAULT_DURATION = 4000;
const ICONS = {
    success: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    error: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
    warning: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
    info: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
};
const CLOSE_ICON = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

function getContainer() {
    let el = document.getElementById(CONTAINER_ID);
    if (!el) {
        el = document.createElement('div');
        el.id = CONTAINER_ID;
        el.className = 'app-toast-container';
        el.setAttribute('role', 'region');
        el.setAttribute('aria-label', 'Notificações');
        document.body.appendChild(el);
    }
    return el;
}

function removeToast(node) {
    if (!node || !node.parentNode) return;
    node.classList.add('app-toast--leaving');
    setTimeout(() => {
        if (node.parentNode) node.parentNode.removeChild(node);
    }, 220);
}

export function show(options = {}) {
    const {
        type = 'info',
        title = '',
        message = '',
        duration = DEFAULT_DURATION,
        dismissible = true,
    } = options;

    const container = getContainer();
    const toastEl = document.createElement('div');
    toastEl.className = `app-toast app-toast--${type}`;
    toastEl.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toastEl.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');

    const icon = document.createElement('div');
    icon.className = 'app-toast__icon';
    icon.innerHTML = ICONS[type] || ICONS.info;

    const body = document.createElement('div');
    body.className = 'app-toast__body';

    if (title) {
        const titleEl = document.createElement('div');
        titleEl.className = 'app-toast__title';
        titleEl.textContent = title;
        body.appendChild(titleEl);
    }
    if (message) {
        const msgEl = document.createElement('div');
        msgEl.className = 'app-toast__message';
        msgEl.textContent = message;
        body.appendChild(msgEl);
    }

    toastEl.appendChild(icon);
    toastEl.appendChild(body);

    if (dismissible) {
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'app-toast__close';
        closeBtn.setAttribute('aria-label', 'Fechar notificação');
        closeBtn.innerHTML = CLOSE_ICON;
        closeBtn.addEventListener('click', () => removeToast(toastEl));
        toastEl.appendChild(closeBtn);
    }

    container.appendChild(toastEl);

    let timeoutId = null;
    if (duration > 0) {
        timeoutId = setTimeout(() => removeToast(toastEl), duration);
        toastEl.addEventListener('mouseenter', () => {
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
        });
        toastEl.addEventListener('mouseleave', () => {
            if (!timeoutId) timeoutId = setTimeout(() => removeToast(toastEl), 1500);
        });
    }

    return {
        dismiss: () => removeToast(toastEl),
        element: toastEl,
    };
}

function variant(type) {
    return (messageOrOpts, maybeOpts) => {
        if (typeof messageOrOpts === 'string') {
            return show({ type, message: messageOrOpts, ...(maybeOpts || {}) });
        }
        return show({ type, ...(messageOrOpts || {}) });
    };
}

export const toast = {
    show,
    success: variant('success'),
    error: variant('error'),
    warning: variant('warning'),
    info: variant('info'),
};

export default toast;
