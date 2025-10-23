// dashboard.js

import {getCollabsByUser} from "./api";

export const CARD_TYPES = Object.freeze({
    INICIO: 'inicio',
    HORARIOS: 'horarios',
    PEDIDOS_FERIAS: 'pedidos_ferias',
    APROVACAO_PEDIDOS: 'aprov_pedidos',
    CONSULTA_PEDIDOS: 'consulta_pedidos',
    LISTA_INTERMEDIOS: 'lista_intermedios',
    FICHA_COLAB: 'ficha_colab',
});

const SVG = {
    HOME: `
        <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9,22 9,12 15,12 15,22"></polyline>
        </svg>
`,
    CLOCK: `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12,6 12,12 16,14"></polyline>
        </svg>
`,
    CALENDAR: `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
`,
    CHECK: `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20,6 9,17 4,12"></polyline>
        </svg>
`,
    FILE: `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
`,
    PEOPLE: `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
`,
    DOCUMENT: `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10,9 9,9 8,9"></polyline>
        </svg>
`,
};

const CARD_DEFS = {
    [CARD_TYPES.INICIO]: {
        title: 'Início',
        description: 'Visão geral do painel.',
        svg: SVG.HOME,
        dataContent: CARD_TYPES.INICIO,
        ctaText: 'Ver Início'
    },
    [CARD_TYPES.HORARIOS]: {
        title: 'Consulta de Horários',
        description: 'Visualize e gerencie horários de todos os colaboradores da organização',
        svg: SVG.CLOCK,
        dataContent: CARD_TYPES.HORARIOS,
        ctaText: 'Ver Horários'
    },
    [CARD_TYPES.PEDIDOS_FERIAS]: {
        title: 'Férias e Ausências',
        description: 'Solicite os seus próprios pedidos de férias e ausências como administrador',
        svg: SVG.CALENDAR,
        dataContent: CARD_TYPES.PEDIDOS_FERIAS,
        ctaText: 'Gerir Pedidos'
    },
    [CARD_TYPES.APROVACAO_PEDIDOS]: {
        title: 'Aprovação de Pedidos',
        description: 'Aprove ou rejeite pedidos de férias e ausências de todos os colaboradores',
        svg: SVG.CHECK,
        dataContent: CARD_TYPES.APROVACAO_PEDIDOS,
        ctaText: 'Gerir Aprovações'
    },
    [CARD_TYPES.CONSULTA_PEDIDOS]: {
        title: 'Consulta de Pedidos',
        description: 'Acesse o histórico completo de todos os pedidos realizados no sistema',
        svg: SVG.FILE,
        dataContent: CARD_TYPES.CONSULTA_PEDIDOS,
        ctaText: 'Ver Pedidos'
    },
    [CARD_TYPES.LISTA_INTERMEDIOS]: {
        title: 'Lista de Intermédios',
        description: 'Visualize e gerencie informações de todos os colaboradores intermédios',
        svg: SVG.PEOPLE,
        dataContent: CARD_TYPES.LISTA_INTERMEDIOS,
        ctaText: 'Ver Lista'
    },
    [CARD_TYPES.FICHA_COLAB]: {
        title: 'A Minha Ficha',
        description: 'Visualize e edite a sua própria ficha pessoal de colaborador',
        svg: SVG.DOCUMENT,
        dataContent: CARD_TYPES.FICHA_COLAB,
        ctaText: 'Ver Ficha'
    },
};


/* Helpers */
function createWelcomeCard(def) {
    const tpl = document.getElementById('tpl-welcome-card');
    const node = tpl.content.firstElementChild.cloneNode(true);
    node.querySelector('.card-title').textContent = def.title;
    node.querySelector('.card-desc').textContent = def.description;
    node.querySelector('.card-link').dataset.content = def.dataContent;
    node.querySelector('.card-link').textContent = def.ctaText;
    node.querySelector('.card-icon').innerHTML = def.svg;
    return node;
}

function bindNav() {
    document.querySelectorAll('[data-content]').forEach(btn => {
        btn.onclick = e => {
            e.preventDefault();
            const key = btn.dataset.content;
            if (!key) return;
            document.querySelectorAll('.sidebar-menu li').forEach(li => li.classList.remove('active'));
            btn.closest('li')?.classList.add('active');
            document.querySelectorAll('.content-section').forEach(sec => {
                sec.hidden = sec.dataset.section !== key;
                sec.classList.toggle('active', sec.dataset.section === key);
            });
        };
    });
}

async function userHasCollabs() {
    const c = await getCollabsByUser()
    if (!c) throw new Error('Failed to fetch collaborators');
    return c.total > 0;
}


/* Public functions */
export function addCardsToWelcomeArea(requested = []) {
    const wc = document.querySelector('.welcome-content');
    if (!wc) return;
    const frag = document.createDocumentFragment();
    requested.forEach(key => {
        const def = CARD_DEFS[key];
        if (def) frag.appendChild(createWelcomeCard(def));
    });
    wc.appendChild(frag);
    bindNav();
}

export function addSidebarEntries(types = []) {
    const menu = document.querySelector('.sidebar-menu');
    if (!menu) return;
    const frag = document.createDocumentFragment();
    types.forEach(t => {
        const def = CARD_DEFS[t];
        if (!def) return;
        const li = document.createElement('li');
        li.innerHTML = `
      <a href="#" data-content="${def.dataContent}">
        <span class="menu-icon">${def.svg}</span>${def.title}
      </a>`;
        frag.appendChild(li);
    });
    menu.appendChild(frag);
    bindNav();
}


document.addEventListener('DOMContentLoaded', () => {
    addCardsToWelcomeArea([CARD_TYPES.INICIO, CARD_TYPES.FICHA_COLAB]);

    userHasCollabs()
        .then(hasCollabs => {
            if (hasCollabs) {
                addCardsToWelcomeArea([
                    CARD_TYPES.HORARIOS,
                    CARD_TYPES.APROVACAO_PEDIDOS,
                    CARD_TYPES.CONSULTA_PEDIDOS
                ]);
            } else {
                addCardsToWelcomeArea([CARD_TYPES.PEDIDOS_FERIAS]);
            }
        })
        .catch(err => {
            console.error('Failed to check collaborators:', err);
            // TODO: better error handling since here it should inform the user
        });

    const yearSpan = document.getElementById('yearSpan');
    if (yearSpan) yearSpan.textContent = new Date().getFullYear();
});
