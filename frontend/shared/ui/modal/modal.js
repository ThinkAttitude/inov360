import './modal.css'

/**
 * Opens a modal dialog dynamically.
 * Each call creates a fresh DOM tree — no listener accumulation.
 * @param {{title?: string, signal?: AbortSignal}} opts
 * @returns {{overlay: HTMLElement, body: HTMLElement, footer: HTMLElement, titleEl: HTMLElement, close: () => void}}
 */
export function openModal(opts = {}) {
    const overlay = document.createElement('div')
    overlay.className = 'ui-modal-overlay'

    const dialog = document.createElement('div')
    dialog.className = 'ui-modal'
    dialog.setAttribute('role', 'dialog')
    dialog.setAttribute('aria-modal', 'true')

    const header = document.createElement('div')
    header.className = 'ui-modal-header'

    const titleEl = document.createElement('h3')
    titleEl.className = 'ui-modal-title'
    titleEl.textContent = opts.title || ''

    const closeBtn = document.createElement('button')
    closeBtn.type = 'button'
    closeBtn.className = 'ui-modal-close'
    closeBtn.textContent = '\u00d7'

    header.append(titleEl, closeBtn)

    const body = document.createElement('div')
    body.className = 'ui-modal-body'

    const footer = document.createElement('div')
    footer.className = 'ui-modal-footer'

    dialog.append(header, body, footer)
    overlay.appendChild(dialog)
    document.body.appendChild(overlay)

    let closed = false

    const close = () => {
        if (closed) return
        closed = true
        overlay.remove()
    }

    closeBtn.addEventListener('click', close)
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close() })

    if (opts.signal) opts.signal.addEventListener('abort', close, { once: true })

    requestAnimationFrame(() => overlay.classList.add('ui-modal-overlay--active'))

    return { overlay, body, footer, titleEl, close }
}
