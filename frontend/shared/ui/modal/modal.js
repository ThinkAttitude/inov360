import "./modal.css"

function buildFormButtons(form, close) {
    if (form.querySelector('button[type="submit"], input[type="submit"]')) return

    const actions = document.createElement("div")
    actions.className = "ui-modal-buttons"

    const cancel = document.createElement("button")
    cancel.type = "button"
    cancel.className = "btn-secondary btn-sm ui-modal-cancel"
    cancel.textContent = "Cancelar"
    cancel.addEventListener("click", close, { once: true })

    const submit = document.createElement("button")
    submit.type = "submit"
    submit.className = "btn-primary btn-sm ui-modal-submit"
    submit.textContent = "Guardar"

    actions.append(cancel, submit)
    form.appendChild(actions)
}

/**
 * @param {HTMLElement} contentEl
 * @param {{className?:string,title?:string,signal?:AbortSignal}} [opts]
 * @returns {{dialog:HTMLDialogElement,panel:HTMLDivElement,close:()=>void,setContent:(el:HTMLElement)=>void}}
 */
export function openModal(contentEl, opts = {}) {
    const dialog = document.createElement("dialog")
    dialog.className = `ui-modal-dialog${opts.className ? ` ${opts.className}` : ""}`

    const panel = document.createElement("div")
    panel.className = "ui-modal-panel"

    const body = document.createElement("div")
    body.className = "ui-modal-body"

    if (opts.title) {
        const header = document.createElement("div")
        header.className = "ui-modal-header"

        const title = document.createElement("h3")
        title.className = "ui-modal-title"
        title.textContent = opts.title

        const closeBtn = document.createElement("button")
        closeBtn.type = "button"
        closeBtn.className = "ui-modal-close"
        closeBtn.setAttribute("aria-label", "Fechar")
        closeBtn.innerHTML = `
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M18 6 6 18"></path>
    <path d="M6 6l12 12"></path>
</svg>
`
        header.append(title, closeBtn)
        panel.appendChild(header)
    }

    panel.appendChild(body)
    dialog.appendChild(panel)
    document.body.appendChild(dialog)

    let closed = false

    const close = () => {
        if (closed) return
        closed = true
        dialog.close()
        dialog.remove()
    }

    const setContent = (el) => {
        body.replaceChildren(el)
        if (el.tagName === "FORM") buildFormButtons(el, close)

        const x = panel.querySelector(".ui-modal-close")
        if (x) x.addEventListener("click", close, { once: true })

        requestAnimationFrame(() => {
            const f = panel.querySelector('input,select,textarea,button,[tabindex]:not([tabindex="-1"])')
            if (f) f.focus({ preventScroll: true })
        })
    }

    dialog.addEventListener("click", (e) => {
        if (e.target === dialog) close()
    })

    dialog.addEventListener("cancel", (e) => {
        e.preventDefault()
        close()
    })

    if (opts.signal) opts.signal.addEventListener("abort", close, { once: true })

    dialog.showModal()
    setContent(contentEl)

    return { dialog, panel, close, setContent }
}
