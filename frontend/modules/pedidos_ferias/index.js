import { getCollabRequests, createLeaveRequest } from "../../app/api.js"
import { openModal } from "../../shared/ui/modal/modal.js"
import "./styles.css"

const $ = (sel, root = document) => root.querySelector(sel)

const cardIconSvg = `
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M8 2v4"></path>
    <path d="M16 2v4"></path>
    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
    <path d="M3 10h18"></path>
</svg>
`

const dtPt = new Intl.DateTimeFormat("pt-PT")

function formatDate(v) {
    const d = new Date(v)
    return Number.isNaN(d.getTime()) ? "" : dtPt.format(d)
}

function formatStatusLabel(status) {
    switch (status) {
        case "approved":
            return "APROVADO"
        case "rejected":
            return "REJEITADO"
        default:
            return "PENDENTE"
    }
}

function renderStats(root, items) {
    const s = { all: 0, pending: 0, approved: 0, rejected: 0 }

    for (const it of items) {
        s.all++
        switch (it.status) {
            case "approved":
                s.approved++
                break
            case "rejected":
                s.rejected++
                break
            default:
                s.pending++
        }
    }

    $("#ferias-total-value", root).textContent = s.all
    $("#ferias-pendentes-value", root).textContent = s.pending
    $("#ferias-aprovados-value", root).textContent = s.approved
    $("#ferias-rejeitados-value", root).textContent = s.rejected

    $("#ferias-count-all", root).textContent = s.all
    $("#ferias-count-pending", root).textContent = s.pending
    $("#ferias-count-approved", root).textContent = s.approved
    $("#ferias-count-rejected", root).textContent = s.rejected
}

function renderCards(root, items) {
    const list = $("#ferias-pedidos-list", root)
    const tpl = $("#ferias-pedido-template", root)
    if (!list || !tpl?.content?.firstElementChild) return

    const frag = document.createDocumentFragment()

    for (const it of items) {
        const node = tpl.content.firstElementChild.cloneNode(true)

        node.dataset.id = String(it.id)
        node.dataset.status = it.status

        $(".ferias-card-type-icon", node).innerHTML = cardIconSvg
        $(".ferias-card-title", node).textContent = it.type
        $(".ferias-card-created", node).textContent = formatDate(it.created_at)
        $(".ferias-card-status-text", node).textContent = formatStatusLabel(it.status)
        $(".ferias-card-start", node).textContent = formatDate(it.start_date)
        $(".ferias-card-end", node).textContent = formatDate(it.end_date)
        $(".ferias-card-justification", node).textContent = it.justification
        $(".ferias-card-decider-name", node).textContent = it.decided_by || ""

        frag.appendChild(node)
    }

    list.replaceChildren(frag)
}

function renderEmpty(root, items) {
    const empty = $("#ferias-empty", root)
    const list = $("#ferias-pedidos-list", root)
    if (!empty || !list) return
    const has = items.length > 0
    empty.hidden = has
    list.hidden = !has
}

function buildNewRequestForm({ onDone, signal }) {
    const form = document.createElement("form")
    form.className = "ferias-form"

    const row = (labelText, el) => {
        const label = document.createElement("label")
        label.className = "field-row"

        const t = document.createElement("span")
        t.className = "field-label"
        t.textContent = labelText

        el.classList.add("field-input")

        label.append(t, el)
        return label
    }

    const tipo = document.createElement("input")
    tipo.type = "text"
    tipo.name = "tipo"
    tipo.required = true

    const dataInicio = document.createElement("input")
    dataInicio.type = "date"
    dataInicio.name = "data_inicio"
    dataInicio.required = true

    const dataFim = document.createElement("input")
    dataFim.type = "date"
    dataFim.name = "data_fim"
    dataFim.required = true

    const justificacao = document.createElement("textarea")
    justificacao.name = "justificacao"
    justificacao.required = true
    justificacao.rows = 4

    const ficheiro = document.createElement("input")
    ficheiro.type = "file"
    ficheiro.name = "ficheiro"

    form.append(
        row("Tipo", tipo),
        row("Data início", dataInicio),
        row("Data fim", dataFim),
        row("Justificação", justificacao),
        row("Comprovativo", ficheiro)
    )

    form.addEventListener("submit", async (e) => {
        e.preventDefault()

        const fd = new FormData(form)
        const res = await createLeaveRequest({
            tipo: fd.get("tipo"),
            data_inicio: fd.get("data_inicio"),
            data_fim: fd.get("data_fim"),
            justificacao: fd.get("justificacao"),
            ficheiro: fd.get("ficheiro")
        })

        switch (res?.ok) {
            case true:
                onDone?.(res)
                break
            default:
                break
        }
    })

    if (signal) signal.addEventListener("abort", () => form.reset(), { once: true })

    return form
}

export function mountPedidosFerias() {
    const root = $("#pedidos-ferias-page")
    if (!root) return

    const ac = new AbortController()
    let modal = null

    const load = async () => {
        const res = await getCollabRequests({ signal: ac.signal })
        const items = res.items
        renderStats(root, items)
        renderCards(root, items)
        renderEmpty(root, items)
    }

    const onNewRequest = () => {
        const form = buildNewRequestForm({
            signal: ac.signal,
            onDone: async () => {
                modal?.close()
                modal = null
                await load()
            }
        })
        modal?.close()
        modal = openModal(form, { title: "Novo Pedido", signal: ac.signal })
    }

    const btn = $("#ferias-novo-pedido-btn", root)
    if (btn) btn.addEventListener("click", onNewRequest)

    ;(async () => {
        try {
            await load()
        } catch (e) {
            if (e?.name === "AbortError") return
            console.error("Failed to load colab requests:", e)
            renderStats(root, [])
            $("#ferias-pedidos-list", root)?.replaceChildren()
            renderEmpty(root, [])
        }
    })()

    return () => {
        if (btn) btn.removeEventListener("click", onNewRequest)
        modal?.close()
        ac.abort()
    }
}
