import {getCollabRequests} from "../../app/api.js";

import "./styles.css"

const $ = (sel, root = document) => root.querySelector(sel)

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

const cardIconSvg = `
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M8 2v4"></path>
    <path d="M16 2v4"></path>
    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
    <path d="M3 10h18"></path>
</svg>
`

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
        $(".ferias-card-decider-name", node).textContent = it.decided_by

        frag.appendChild(node)
    }

    list.replaceChildren(frag)
}

export function mountPedidosFerias() {
    const root = $("#pedidos-ferias-page")
    if (!root) return

    const ac = new AbortController()

    ;(async () => {
        try {
            const res = await getCollabRequests({ signal: ac.signal })
            const items = res.data
            renderStats(root, items)
            renderCards(root, items)
        } catch (e) {
            if (e?.name === "AbortError") return
            console.error("Failed to load colab requests:", e)
            renderStats(root, [])
            $("#ferias-pedidos-list", root)?.replaceChildren()
        }
    })()

    return () => ac.abort()
}
