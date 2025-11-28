import {CARD_TYPES} from "./dashboard.js";
import {mountCalendar} from "./modules/horarios.js";
import {initForm} from "./modules/marcacao_direta.js";
import {mountClbMngmt} from "./modules/controlo_colabs.js";
import {mountGstFchs} from "./modules/gestao_fichas.js";

export const Paths = {
    ...CARD_TYPES,
    INICIO: '',
};

export const Routes = {
    [Paths.INICIO]: {
        html: '/page/modules/inicio.html',
        js: null
    },
    [Paths.HORARIOS]: {
        html: '/page/modules/horarios.html',
        js: mountCalendar
    },
    [Paths.APROVACAO_HORARIOS]: {
        html: '/page/modules/aprovacao_horarios.php',
        js: null
    },
    [Paths.MAPAS_HORARIOS]: {
        html: '/page/modules/mapas_horarios.php',
        js: null
    },
    [Paths.PEDIDOS_FERIAS]: {
        html: '/page/modules/pedidos_ferias.php',
        js: null
    },
    [Paths.APROVACAO_FERIAS]: {
        html: '/page/modules/aprovacao_ferias.php',
        js: null
    },
    [Paths.CONSULTA_PEDIDOS]: {
        html: '/page/modules/consulta_pedidos.html',
        js: null
    },
    [Paths.LISTA_INTERMEDIOS]: {
        html: '/page/modules/lista_intermedios.php',
        js: null
    },
    [Paths.CONTROLO_COLABS]: {
        html: '/page/modules/controlo_colabs.html',
        js: mountClbMngmt
    },
    [Paths.PEDIDOS_HORAS_EXTRA]: {
        html: '/page/modules/pedidos_horas_extras.php',
        js: null
    },
    [Paths.APROVACAO_HORAS_EXTRA]: {
        html: '/page/modules/aprovacao_horas_extras.php',
        js: null
    },
    [Paths.MARCACAO_DIRETA]: {
        html: '/page/modules/marcacao_direta.html',
        js: initForm
    },
    [Paths.GESTAO_FICHAS]: {
        html: '/page/modules/gestao_fichas.html',
        js: mountGstFchs
    },
    [Paths.FINANCEIRA]: {
        html: '/page/modules/financeira.html',
        js: null
    },
    [Paths.FICHA_COLLAB]: {
        html: '/page/modules/ficha_collab.html',
        js: null
    }
};

const container = document.getElementById('main-content');

async function render() {
    if (!container) return;

    // get the current route from the hash, e.g. "#horarios" -> "horarios"
    const raw = window.location.hash.replace(/^#/, '').trim();

    // if hash is empty, use Paths.INICIO (which is '')
    const path = raw === '' ? Paths.INICIO : raw;
    const route = Routes[path] || Routes[Paths.INICIO];

    try {
        const res = await fetch(route.html, { credentials: 'same-origin' });
        if (!res.ok) throw new Error(`HTTP ${res.status} for ${route}`);
        container.innerHTML = await res.text();
        if (typeof route.js === 'function') route.js();

    } catch (err) {
        console.error('Falha ao carregar vista:', err);
        container.innerHTML = `
            <div style="padding:1rem;">
                <p class="error">Não foi possível carregar esta secção.</p>
            </div>`;
    }

    window.dispatchEvent(new CustomEvent('view:loaded'));
}


window.addEventListener('hashchange', render);
document.addEventListener('DOMContentLoaded', render);
