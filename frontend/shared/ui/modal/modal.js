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
    dialog.setAttribute('tabindex', '-1')

    const header = document.createElement('div')
    header.className = 'ui-modal-header'

    const titleEl = document.createElement('h3')
    titleEl.className = 'ui-modal-title'
    titleEl.textContent = opts.title || ''
    const titleId = `ui-modal-title-${Math.random().toString(36).slice(2)}`
    titleEl.id = titleId
    dialog.setAttribute('aria-labelledby', titleId)

    const closeBtn = document.createElement('button')
    closeBtn.type = 'button'
    closeBtn.className = 'ui-modal-close'
    closeBtn.setAttribute('aria-label', 'Fechar')
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

    // Close with Escape key, similar to popover behavior
    overlay.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' || e.key === 'Esc') {
            e.preventDefault()
            e.stopPropagation()
            close()
        }
    })

    // If an AbortSignal is provided, close immediately if already aborted,
    // otherwise close when the abort event fires.
    if (opts.signal) {
        if (opts.signal.aborted) {
            close()
        } else {
            opts.signal.addEventListener('abort', close, { once: true })
        }
    }

    // Focus the first focusable element inside the dialog, or the dialog itself
    const autofocusTarget = dialog.querySelector(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    )
    if (autofocusTarget && autofocusTarget instanceof HTMLElement) {
        autofocusTarget.focus()
    } else {
        dialog.focus()
    }

    requestAnimationFrame(() => overlay.classList.add('ui-modal-overlay--active'))

    return { overlay, body, footer, titleEl, close }
}
