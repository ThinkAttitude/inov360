import {CARD_TYPES, mountDashboardShell, mountInicio} from "../modules/dashboard/dashboard.js";
import {initForm} from "../modules/marcacao_direta/marcacao_direta.js";
import {mountClbMngmt} from "../modules/controlo_colabs/controlo_colabs.js";
import {mountSchedule} from "../modules/horarios/horarios.js";
import {mountGstFchs} from "../modules/gestao_fichas/gestao_fichas.js";
import {mountFichaCollab} from "../modules/ficha_collab/ficha_collab.js";

export const MODULES_BASE = '/frontend/modules';

/**
 * @typedef {typeof Path[keyof typeof Path]} PathValue
 */

/**
 * Routes paths (which correspond to card types plus INICIO)
 * @type {Record<string, string>}
 */
export const Path = {
    ...CARD_TYPES,
    INICIO: '',
};

const Paths = new Set(Object.values(Path));

/**
 * Parses a URL and return an object with href, pathname and filename
 * @param href
 * @returns {Readonly<{href: string, readonly pathname: string, readonly filename: string}>|string}
 */
export function Route(href) {
    const url = new URL(href, window.location.href);
    return Object.freeze({
        href: url.href,
        get pathname() { return url.pathname; },
        get filename() { return url.pathname.split('/').pop(); },
    });
}


/**
 * Type guard to check if a value is a valid Path
 * @param {string} value
 * @returns {value is PathValue}
 */
export function isPath(value) {
    return Paths.has(value);
}

const container = document.getElementById("main-content");

/**
 * Check if we are in SPA context
 * @returns {boolean}
 */
function inSpa() { return !!container; }

export const ShellRoutes = Object.freeze({
    DASHBOARD: Route(`${MODULES_BASE}/dashboard/dashboard.html`),
});

export const Routes = {
    [Path.INICIO]: {
        html: Route(`${MODULES_BASE}/dashboard/inicio.html`),
        css: Route(`${MODULES_BASE}/dashboard/styles.css`),
        js: mountInicio
    },
    [Path.HORARIOS]: {
        html: Route(`${MODULES_BASE}/horarios/horarios.html`),
        css: Route(`${MODULES_BASE}/horarios/styles.css`),
        js: mountSchedule
    },
    [Path.APROVACAO_HORARIOS]: {
        html: Route(`${MODULES_BASE}/aprovacao_horarios/aprovacao_horarios.php`),
        js: null
    },
    [Path.MAPAS_HORARIOS]: {
        html: Route(`${MODULES_BASE}/mapas_horarios/mapas_horarios.php`),
        js: null
    },
    [Path.PEDIDOS_FERIAS]: {
        html: Route(`${MODULES_BASE}/pedidos_ferias/pedidos_ferias.php`),
        js: null
    },
    [Path.APROVACAO_FERIAS]: {
        html: Route(`${MODULES_BASE}/aprovacao_ferias/aprovacao_ferias.php`),
        js: null
    },
    [Path.CONSULTA_PEDIDOS]: {
        html: Route(`${MODULES_BASE}/consulta_pedidos/consulta_pedidos.html`),
        js: null
    },
    [Path.LISTA_INTERMEDIOS]: {
        html: Route(`${MODULES_BASE}/lista_intermedios/lista_intermedios.php`),
        js: null
    },
    [Path.CONTROLO_COLABS]: {
        html: Route(`${MODULES_BASE}/controlo_colabs/controlo_colabs.html`),
        css: Route(`${MODULES_BASE}/controlo_colabs/styles.css`),
        js: mountClbMngmt
    },
    [Path.PEDIDOS_HORAS_EXTRA]: {
        html: Route(`${MODULES_BASE}/pedidos_horas_extras/pedidos_horas_extras.php`),
        js: null
    },
    [Path.APROVACAO_HORAS_EXTRA]: {
        html: Route(`${MODULES_BASE}/aprovacao_horas_extras/aprovacao_horas_extras.php`),
        js: null
    },
    [Path.MARCACAO_DIRETA]: {
        html: Route(`${MODULES_BASE}/marcacao_direta/marcacao_direta.html`),
        css: Route(`${MODULES_BASE}/marcacao_direta/styles.css`),
        js: initForm
    },
    [Path.GESTAO_FICHAS]: {
        html: Route(`${MODULES_BASE}/gestao_fichas/gestao_fichas.html`),
        css: Route(`${MODULES_BASE}/gestao_fichas/styles.css`),
        js: mountGstFchs
    },
    [Path.FINANCEIRA]: {
        html: Route(`${MODULES_BASE}/financeira/financeira.html`),
        js: null
    },
    [Path.FICHA_COLLAB]: {
        html: Route(`${MODULES_BASE}/ficha_collab/ficha_collab.html`),
        css: Route(`${MODULES_BASE}/ficha_collab/styles.css`),
        js: mountFichaCollab
    }
};

const VIEW_CSS_ID = 'route-view-css';

function ensureViewCssLink() {
    let link = document.getElementById(VIEW_CSS_ID);
    if (link) return link;

    link = document.createElement('link');
    link.id = VIEW_CSS_ID;
    link.rel = 'stylesheet';
    document.head.appendChild(link);
    return link;
}

function setViewCss(route) {
    const href = route?.css?.href || '';
    const link = ensureViewCssLink();

    if (!href) {
        link.removeAttribute('href');
        return;
    }

    if (link.getAttribute('href') === href) return;
    link.setAttribute('href', href);
}

/**
 * Navigate to a given path
 * @param {PathValue} path
 */

export function navigate(path) {
    if (!isPath(path)) throw new Error('Invalid path');

    const nextHash = path ? `#${path}` : '';
    if (!inSpa()) {
        window.location.href = `${ShellRoutes.DASHBOARD.href}${nextHash}`;
        return;
    }

    if (window.location.hash === nextHash) {
        window.dispatchEvent(new HashChangeEvent('hashchange'));
        return;
    }

    window.location.hash = nextHash;
}

let cleanupView = null;
let cleanupShell = null;

async function render() {
    if (!inSpa()) return;

    // cleanup previous view
    if (typeof cleanupView === "function") cleanupView();
    cleanupView = null;

    const raw = window.location.hash.replace(/^#/, "").trim();
    const path = raw === "" ? Path.INICIO : raw;
    const route = isPath(path) ? Routes[path] : Routes[Path.INICIO];

    setViewCss(route);

    try {
        // TODO: evaluate if this is true SPA behavior or if we should use a proper router
        const res = await fetch(route.html.href, { credentials: "same-origin" });
        if (!res.ok) throw new Error(`HTTP ${res.status} for ${route.html.href}`);

        container.innerHTML = await res.text();

        const maybeCleanup = route.js?.();
        cleanupView = typeof maybeCleanup === "function" ? maybeCleanup : null;
    } catch (err) {
        console.error("Falha ao carregar vista:", err);
        container.innerHTML = `
      <div style="padding:1rem;">
        <p class="error">Não foi possível carregar esta secção.</p>
      </div>`;
    }

    window.dispatchEvent(new CustomEvent("view:loaded"));
}

function start() {
    // mount shell once
    if (!inSpa()) return;
    if (!cleanupShell) cleanupShell = mountDashboardShell();

    window.addEventListener("hashchange", render);
    render();
}

start();
