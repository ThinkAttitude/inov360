// dashboard.js

import {getCollabsByUser} from "./api.js";

export const CARD_TYPES = Object.freeze({
    INICIO: 'inicio',
    HORARIOS: 'horarios',
    PEDIDOS_FERIAS: 'pedidos_ferias',
    APROVACAO_FERIAS: 'aprov_ferias',
    CONSULTA_PEDIDOS: 'consulta_pedidos',
    LISTA_INTERMEDIOS: 'lista_intermedios',
    CONTROLO_COLABS: 'controlo_colabs',
    PEDIDOS_HORAS: 'pedidos_horas',
    APROVACAO_HORAS: 'aprov_horas',
    MARCACAO_DIRETA: 'marcacao_direta',
    GESTAO_FICHAS: 'gestao_fichas',
    FICHA_COLLAB: 'ficha_collab',
});

const SVG_ICONS = {
    HOME: `<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9,22 9,12 15,12 15,22"></polyline>`,
    CLOCK: `<circle cx="12" cy="12" r="10"></circle><polyline points="12,6 12,12 16,14"></polyline>`,
    CALENDAR: `<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>`,
    CHECK: `<polyline points="20,6 9,17 4,12"></polyline>`,
    FILE: `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>`,
    PEOPLE: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>`,
    DOCUMENT: `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline>`,
    BOOKING: `<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><path d="M8 2v4"></path><path d="M16 2v4"></path><path d="M21 10H3"></path><path d="M12 14l2 2 4-4"></path>`,
    CLIPBOARD: `<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>`,
};

function svg(icon, size = 24, cls = '') {
    return `<svg ${cls ? `class="${cls}" ` : ''}width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icon}</svg>`;
}

const CARD_DEFS = {
    [CARD_TYPES.INICIO]: {
        title: 'Início',
        description: 'Visão geral do painel.',
        icon: SVG_ICONS.HOME,
        ctaText: 'Ver Início'
    },
    [CARD_TYPES.HORARIOS]: {
        title: 'Consulta de Horários',
        description: 'Visualize e gerencie horários de todos os colaboradores da organização',
        icon: SVG_ICONS.CLOCK,
        permission: 3,
        ctaText: 'Ver Horários'
    },
    [CARD_TYPES.PEDIDOS_FERIAS]: {
        title: 'Férias e Ausências',
        description: 'Solicite os seus próprios pedidos de férias e ausências como administrador',
        icon: SVG_ICONS.CALENDAR,
        ctaText: 'Gerir Pedidos'
    },
    [CARD_TYPES.APROVACAO_FERIAS]: {
        title: 'Aprovação de Pedidos',
        description: 'Aprove ou rejeite pedidos de férias e ausências de todos os colaboradores',
        icon: SVG_ICONS.CHECK,
        ctaText: 'Gerir Aprovações'
    },
    [CARD_TYPES.CONSULTA_PEDIDOS]: {
        title: 'Consulta de Pedidos',
        description: 'Acesse o histórico completo de todos os pedidos realizados no sistema',
        icon: SVG_ICONS.FILE,
        ctaText: 'Ver Pedidos'
    },
    [CARD_TYPES.LISTA_INTERMEDIOS]: {
        title: 'Lista de Intermédios',
        description: 'Visualize e gira informações de todos os colaboradores intermédios',
        icon: SVG_ICONS.PEOPLE,
        ctaText: 'Ver Lista'
    },
    [CARD_TYPES.CONTROLO_COLABS]: {
        title: 'Controlo de Colaboradores',
        description: 'Monitore e gerencie a presença e atividades dos colaboradores em tempo real',
        icon: SVG_ICONS.CLOCK,
        permission: 1,
        ctaText: 'Gerir Colaboradores'
    },
    [CARD_TYPES.PEDIDOS_HORAS]: {
        title: 'Pedidos de Horas',
        description: 'Solicite pedidos de horas extras ou ajustes de horário como administrador',
        icon: SVG_ICONS.CALENDAR,
        permission: 4,
        ctaText: 'Gerir Pedidos'
    },
    [CARD_TYPES.APROVACAO_HORAS]: {
        title: 'Aprovação de Horas',
        description: 'Aprove ou rejeite pedidos de horas extras ou ajustes de horário de todos os colaboradores',
        icon: SVG_ICONS.CHECK,
        permission: 5,
        ctaText: 'Gerir Aprovações'
    },
    [CARD_TYPES.MARCACAO_DIRETA]: {
        title: 'Marcações Diretas',
        description: 'Realize marcações diretas de ferias ou ausencias para colaboradores específicos',
        icon: SVG_ICONS.BOOKING,
        permission: 2,
        ctaText: 'Fazer Marcação'
    },
    [CARD_TYPES.GESTAO_FICHAS]: {
        title: 'Gestão de Fichas',
        description: 'Gira as fichas pessoais de todos os colaboradores na organização',
        icon: SVG_ICONS.CLIPBOARD,
        permission: 6,
        ctaText: 'Gerir Fichas'
    },
    [CARD_TYPES.FICHA_COLLAB]: {
        title: 'A Minha Ficha',
        description: 'Visualize e edite a sua própria ficha pessoal de colaborador',
        icon: SVG_ICONS.DOCUMENT,
        ctaText: 'Ver Ficha'
    },
};


/* Helpers */
function createWelcomeCard(key) {
    const def = CARD_DEFS[key];
    const tpl = document.getElementById('tpl-welcome-card');
    const node = tpl.content.firstElementChild.cloneNode(true);
    node.querySelector('.card-title').textContent = def.title;
    node.querySelector('.card-desc').textContent = def.description;
    node.querySelector('.card-link').dataset.content = key;
    node.querySelector('.card-link').textContent = def.ctaText;
    node.querySelector('.card-icon').innerHTML = svg(def.icon, 24);
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
        frag.appendChild(createWelcomeCard(key));
    });

    wc.insertBefore(frag, wc.querySelector(`[data-anchor="${CARD_TYPES.FICHA_COLLAB}"]`));
    bindNav();
}

export function addSidebarEntries(types = []) {
    const menu = document.querySelector('.sidebar-menu');
    if (!menu) return;

    const existing = new Set(
        Array.from(menu.querySelectorAll('[data-content]'))
            .map(a => a.dataset.content)
    );

    const frag = document.createDocumentFragment();

    types.forEach(t => {
        if (existing.has(t)) return;

        const def = CARD_DEFS[t];
        if (!def) return;

        const li = document.createElement('li');
        li.innerHTML = `
            <a href="#" data-content="${t}">
                <span class="menu-icon">${svg(def.icon, 20, 'menu-icon')}</span>${def.title}
            </a>`;
        frag.appendChild(li);
    });

    menu.insertBefore(frag, menu.querySelector(`[data-anchor="${CARD_TYPES.FICHA_COLLAB}"]`));
    bindNav();
}



document.addEventListener('DOMContentLoaded', () => {
    const user = window.CURRENT_USER || {};
    const userPerms = new Set(user.permissions || []);

    const permCards = Object.entries(CARD_DEFS)
        .filter(([_, def]) => userPerms.has(def.permission))
        .map(([key, _]) => key);

    const sideCards = new Set(permCards);
    const welcomeCards = new Set(permCards);

    userHasCollabs()
        .then(hasCollabs => {
            if (hasCollabs) {
                welcomeCards.add(CARD_TYPES.APROVACAO_FERIAS);
                welcomeCards.add(CARD_TYPES.CONSULTA_PEDIDOS);
                sideCards.add(CARD_TYPES.APROVACAO_FERIAS);
                sideCards.add(CARD_TYPES.CONSULTA_PEDIDOS);
            }

            if (welcomeCards.size > 0) addCardsToWelcomeArea([...welcomeCards]);
            if (sideCards.size > 0) addSidebarEntries([...sideCards]);
        })
        .catch(err => {
            console.error('Failed to check collaborators:', err);
            // TODO: better error handling since here it should inform the user
        });

    const yearSpan = document.getElementById('yearSpan');
    if (yearSpan) yearSpan.textContent = new Date().getFullYear().toString();
});
