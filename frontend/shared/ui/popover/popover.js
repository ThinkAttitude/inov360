import './popover.css'

function buildFormButtons(form) {
    if (form.querySelector('button[type="submit"], input[type="submit"]')) return

    const actions = document.createElement('div')
    actions.className = 'ui-popover-buttons'

    const submit = document.createElement('button')
    submit.type = 'submit'
    submit.className = 'btn-primary btn-sm ui-popover-submit'
    submit.textContent = 'Guardar'

    actions.appendChild(submit)
    form.appendChild(actions)
}

/**
 * Opens a popover anchored to an element using <dialog>.
 * @param {HTMLElement} anchorEl
 * @param {HTMLElement} contentEl
 * @param {{offset?: number, className?: string, signal?: AbortSignal}} [opts]
 * @returns {{dialog: HTMLDialogElement, panel: HTMLDivElement, close: () => void, setContent: (el: HTMLElement) => void}}
 */
export function openPopover(anchorEl, contentEl, opts = {}) {
    const dialog = document.createElement('dialog')
    dialog.className = `ui-popover-dialog${opts.className ? ` ${opts.className}` : ''}`

    const panel = document.createElement('div')
    panel.className = 'ui-popover-panel'
    dialog.appendChild(panel)

    document.body.appendChild(dialog)

    const prevAnchorName = anchorEl.style.anchorName
    anchorEl.style.anchorName = '--ui-popover-anchor'

    let closed = false

    const setContent = (el) => {
        panel.replaceChildren(el)
        if (el.tagName === 'FORM') buildFormButtons(el)
        requestAnimationFrame(() => {
            const f = panel.querySelector('input,select,textarea,button,[tabindex]:not([tabindex="-1"])')
            if (f) f.focus({preventScroll: true})
        })
    }

    const close = () => {
        if (closed) return
        closed = true
        if (prevAnchorName) anchorEl.style.anchorName = prevAnchorName
        else anchorEl.style.removeProperty('anchor-name')
        dialog.close()
        dialog.remove()
    }

    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) close()
    })

    dialog.addEventListener('cancel', (e) => {
        e.preventDefault()
        close()
    })

    if (opts.signal) opts.signal.addEventListener('abort', close, {once: true})

    dialog.showModal()
    setContent(contentEl)

    return {dialog, panel, close, setContent}
}

