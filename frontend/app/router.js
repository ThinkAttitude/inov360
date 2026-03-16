import {CARD_TYPES, mountDashboardShell, mountInicio} from "../modules/dashboard/dashboard.js"
import {initForm} from "../modules/marcacao_direta/marcacao_direta.js"
import {mountClbMngmt} from "../modules/controlo_colabs/controlo_colabs.js"
import {mountSchedule} from "../modules/horarios"
import {mountGstFchs} from "../modules/gestao_fichas/gestao_fichas.js"
import {mountFichaCollab} from "../modules/ficha_collab/ficha_collab.js"
import {mountPedidosHorasExtra} from "../modules/pedidos_horas_extras/pedidos_horas_extras.js"
import {mountHorasExtra} from "../modules/horas_extra/horas_extra.js"

export const MODULES_BASE = "/frontend/modules"

export const Path = Object.freeze({...CARD_TYPES, INICIO: ""})
const Paths = new Set(Object.values(Path))

export function isPath(value) {
    return Paths.has(value)
}

export function Route(href) {
    const url = new URL(href, window.location.href)
    return Object.freeze({
        href: url.href,
        get pathname() {
            return url.pathname
        },
        get filename() {
            return url.pathname.split("/").pop()
        }
    })
}

const container = document.getElementById("main-content")
const inSpa = () => !!container

export const ShellRoutes = Object.freeze({
    DASHBOARD: Route(`${MODULES_BASE}/dashboard/dashboard.html`)
})

export const Routes = Object.freeze({
    [Path.INICIO]: {html: Route(`${MODULES_BASE}/dashboard/inicio.html`), mount: mountInicio},
    [Path.HORARIOS]: {html: Route(`${MODULES_BASE}/horarios/view.html`), mount: mountSchedule},
    [Path.APROVACAO_HORARIOS]: {html: Route(`${MODULES_BASE}/aprovacao_horarios/aprovacao_horarios.php`), mount: null, placeholder: true},
    [Path.MAPAS_HORARIOS]: {html: Route(`${MODULES_BASE}/mapas_horarios/mapas_horarios.php`), mount: null, placeholder: true},
    [Path.PEDIDOS_FERIAS]: {html: Route(`${MODULES_BASE}/pedidos_ferias/view.html`), mount: null},
    [Path.APROVACAO_FERIAS]: {html: Route(`${MODULES_BASE}/aprovacao_ferias/aprovacao_ferias.php`), mount: null, placeholder: true},
    [Path.CONSULTA_PEDIDOS]: {html: Route(`${MODULES_BASE}/consulta_pedidos/consulta_pedidos.html`), mount: null, placeholder: true},
    [Path.LISTA_INTERMEDIOS]: {html: Route(`${MODULES_BASE}/lista_intermedios/lista_intermedios.php`), mount: null, placeholder: true},
    [Path.CONTROLO_COLABS]: {html: Route(`${MODULES_BASE}/controlo_colabs/view.html`), mount: mountClbMngmt},
    [Path.PEDIDOS_HORAS_EXTRA]: {
        html: Route(`${MODULES_BASE}/pedidos_horas_extras/view.html`),
        mount: mountPedidosHorasExtra
    },
    [Path.APROVACAO_HORAS_EXTRA]: {
        html: Route(`${MODULES_BASE}/horas_extra/view.html`),
        mount: mountHorasExtra
    },
    [Path.MARCACAO_DIRETA]: {html: Route(`${MODULES_BASE}/marcacao_direta/marcacao_direta.html`), mount: initForm},
    [Path.GESTAO_FICHAS]: {html: Route(`${MODULES_BASE}/gestao_fichas/view.html`), mount: mountGstFchs},
    [Path.FINANCEIRA]: {html: Route(`${MODULES_BASE}/financeira/financeira.html`), mount: null},
    [Path.FICHA_COLLAB]: {html: Route(`${MODULES_BASE}/ficha_collab/view.html`), mount: mountFichaCollab}
})

export function navigate(path) {
    if (!isPath(path)) throw new Error("Invalid path")
    const nextHash = path ? `#${path}` : ""
    if (!inSpa()) {
        window.location.href = `${ShellRoutes.DASHBOARD.href}${nextHash}`
        return
    }
    if (window.location.hash === nextHash) {
        window.dispatchEvent(new HashChangeEvent("hashchange"))
        return
    }
    window.location.hash = nextHash
}

let cleanupView = null
let cleanupShell = null

async function render() {
    if (!inSpa()) return

    if (typeof cleanupView === "function") cleanupView()
    cleanupView = null

    const raw = window.location.hash.replace(/^#/, "").trim()
    const path = raw === "" ? Path.INICIO : raw
    const route = isPath(path) ? Routes[path] : Routes[Path.INICIO]

    try {
        if (route.placeholder) {
            container.innerHTML = `<div style="padding:2.5rem;text-align:center;"><h3 style="color:#64748b;font-weight:600;">Módulo em desenvolvimento</h3><p style="color:#94a3b8;">Esta secção será disponibilizada em breve.</p></div>`
        } else {
            const res = await fetch(route.html.href, {credentials: "same-origin"})
            if (!res.ok) throw new Error(`HTTP ${res.status} for ${route.html.href}`)
            container.innerHTML = await res.text()
            const maybeCleanup = route.mount?.()
            cleanupView = typeof maybeCleanup === "function" ? maybeCleanup : null
        }
    } catch (err) {
        console.error("Falha ao carregar vista:", err)
        container.innerHTML = `<div style="padding:1rem;"><p class="error">Não foi possível carregar esta secção.</p></div>`
    }

    window.dispatchEvent(new CustomEvent("view:loaded"))
}

function start() {
    if (!inSpa()) return
    if (!cleanupShell) {
        const maybeCleanup = mountDashboardShell()
        cleanupShell = typeof maybeCleanup === "function" ? maybeCleanup : null
    }
    window.addEventListener("hashchange", render)
    render()
}

start()
