import {mountDashboardShell, mountInicio} from "../modules/dashboard/dashboard.js"
import {initForm} from "../modules/marcacao_direta/marcacao_direta.js"
import {mountClbMngmt} from "../modules/controlo_colabs/controlo_colabs.js"
import {mountSchedule} from "../modules/horarios"
import {mountGstFchs} from "../modules/gestao_fichas/gestao_fichas.js"
import {mountFichaCollab} from "../modules/ficha_collab/ficha_collab.js"
import {mountPedidosHorasExtra} from "../modules/pedidos_horas_extras/pedidos_horas_extras.js"
import {mountHorasExtra} from "../modules/horas_extra/horas_extra.js"
import {handleAuthStatus, setApiAuthHandlers} from "./api";
import {User} from "../shared/user_store";
import {CARD_TYPES, guardDashboardRoute} from "../modules/dashboard/dashboard_access.js";

export const MODULES_BASE = "/frontend/modules";

export const Path = Object.freeze({...CARD_TYPES, INICIO: ""});
const Paths = new Set(Object.values(Path));

export function isPath(value) {
    return Paths.has(value);
}

export function Route(href) {
    const url = new URL(href, window.location.href);
    return Object.freeze({
        href: url.href,
        get pathname() {
            return url.pathname;
        },
        get filename() {
            return url.pathname.split("/").pop();
        }
    });
}

const container = document.getElementById("main-content");
const inSpa = () => !!container;

export const ShellRoutes = Object.freeze({
    DASHBOARD: Route(`${MODULES_BASE}/dashboard/dashboard.html`)
});
export const SharedRoutes = Object.freeze({
    WIP: {html: Route(`/frontend/shared/ui/pages/wip/view.html`), mount: null}
});

export const Routes = Object.freeze({
    [Path.INICIO]: {
        html: Route(`${MODULES_BASE}/dashboard/inicio.html`),
        mount: mountInicio,
        maintenance: false,
    },
    [Path.HORARIOS]: {
        html: Route(`${MODULES_BASE}/horarios/view.html`),
        mount: mountSchedule,
        maintenance: true,
    },
    [Path.APROVACAO_HORARIOS]: {
        html: Route(`${MODULES_BASE}/aprovacao_horarios/aprovacao_horarios.php`),
        mount: null,
        maintenance: true,
    },
    [Path.MAPAS_HORARIOS]: {
        html: Route(`${MODULES_BASE}/mapas_horarios/mapas_horarios.php`),
        mount: null,
        maintenance: true,
    },
    [Path.PEDIDOS_FERIAS]: {
        html: Route(`${MODULES_BASE}/pedidos_ferias/view.html`),
        mount: null,
        maintenance: true,
    },
    [Path.APROVACAO_FERIAS]: {
        html: Route(`${MODULES_BASE}/aprovacao_ferias/aprovacao_ferias.php`),
        mount: null,
        maintenance: true,
    },
    [Path.CONSULTA_PEDIDOS]: {
        html: Route(`${MODULES_BASE}/consulta_pedidos/view.html`),
        mount: null,
        maintenance: true,
    },
    [Path.LISTA_INTERMEDIOS]: {
        html: Route(`${MODULES_BASE}/lista_intermedios/lista_intermedios.php`),
        mount: null,
        maintenance: true,
    },
    [Path.CONTROLO_COLABS]: {
        html: Route(`${MODULES_BASE}/controlo_colabs/view.html`),
        mount: mountClbMngmt,
        maintenance: false,
    },
    [Path.PEDIDOS_HORAS_EXTRA]: {
        html: Route(`${MODULES_BASE}/pedidos_horas_extras/view.html`),
        mount: mountPedidosHorasExtra,
        maintenance: true,
    },
    [Path.APROVACAO_HORAS_EXTRA]: {
        html: Route(`${MODULES_BASE}/horas_extra/view.html`),
        mount: mountHorasExtra,
        maintenance: true,
    },
    [Path.MARCACAO_DIRETA]: {
        html: Route(`${MODULES_BASE}/marcacao_direta/marcacao_direta.html`),
        mount: initForm,
        maintenance: true,
    },
    [Path.GESTAO_FICHAS]: {
        html: Route(`${MODULES_BASE}/gestao_fichas/view.html`),
        mount: mountGstFchs,
        maintenance: false,
    },
    [Path.FICHA_COLLAB]: {
        html: Route(`${MODULES_BASE}/ficha_collab/view.html`),
        mount: mountFichaCollab,
        maintenance: false,
    },
});

export function navigate(path) {
    if (!isPath(path)) throw new Error("Invalid path");

    const nextHash = path ? `#${path}` : "";

    if (!inSpa()) {
        window.location.href = `${ShellRoutes.DASHBOARD.href}${nextHash}`;
        return;
    }

    if (window.location.hash === nextHash) {
        window.dispatchEvent(new HashChangeEvent("hashchange"));
        return;
    }

    window.location.hash = nextHash;
}

let cleanupView = null;
let cleanupShell = null;
let renderVersion = 0;
let started = false;

async function render() {
    if (!inSpa()) return;

    const currentRender = ++renderVersion;

    if (typeof cleanupView === "function") cleanupView();
    cleanupView = null;

    const raw = window.location.hash.replace(/^#/, "").trim();
    const requestedPath = raw === "" ? Path.INICIO : raw;
    const normalizedPath = isPath(requestedPath) ? requestedPath : Path.INICIO;
    const guardedPath = await guardDashboardRoute(normalizedPath);

    if (guardedPath === null) return;

    const pageRoute = Routes[guardedPath] || Routes[Path.INICIO];
    const route = pageRoute.maintenance ? SharedRoutes.WIP : pageRoute;

    try {
        const res = await fetch(route.html.href.toString(), {credentials: "same-origin"})

        if (await handleAuthStatus(res.status)) return

        if (!res.ok) throw new Error(`HTTP ${res.status} for ${route.html.href}`)

        const html = await res.text();

        if (currentRender !== renderVersion) return;

        container.innerHTML = html;

        const maybeCleanup = route.mount?.();
        cleanupView = typeof maybeCleanup === "function" ? maybeCleanup : null;
    } catch (err) {
        if (currentRender !== renderVersion) return;

        console.error("Falha ao carregar a pagina:", err);
        container.innerHTML = `<div style="padding:1rem;"><p class="error">Não foi possível carregar esta secção.</p></div>`;
    }

    window.dispatchEvent(new CustomEvent("view:loaded"));
}

async function start() {
    if (!inSpa() || started) return;
    started = true;

    if (!cleanupShell) {
        const maybeCleanup = mountDashboardShell();
        cleanupShell = typeof maybeCleanup === "function" ? maybeCleanup : null;
    }

    window.addEventListener("hashchange", render);
    await render();
}

setApiAuthHandlers({
    unauthorized() {
        User.clear();
        window.location.replace('/frontend/modules/login/view.html');
    },
    forbidden() {
        navigate(Path.INICIO);
    },
});

start();
