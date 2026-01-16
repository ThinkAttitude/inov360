import {logout} from "../../app/api.js";
import {User} from "../../shared/user_store.js";

export const CARD_TYPES = Object.freeze({
    INICIO: 'inicio',
    HORARIOS: 'horarios',
    APROVACAO_HORARIOS: 'aprov_horarios',
    MAPAS_HORARIOS: 'mapas_horarios',
    PEDIDOS_FERIAS: 'pedidos_ferias',
    APROVACAO_FERIAS: 'aprov_ferias',
    CONSULTA_PEDIDOS: 'consulta_pedidos',
    LISTA_INTERMEDIOS: 'lista_intermedios',
    CONTROLO_COLABS: 'controlo_colabs',
    PEDIDOS_HORAS_EXTRA: 'pedidos_horas_extras',
    APROVACAO_HORAS_EXTRA: 'aprov_horas_extras',
    MARCACAO_DIRETA: 'marcacao_direta',
    GESTAO_FICHAS: 'gestao_fichas',
    FINANCEIRA: 'financeira',
    FICHA_COLLAB: 'ficha_collab',
});

export const PERMISSIONS = Object.freeze({
    CONTROLO_COLABS: 1,
    MARCACAO_DIRETA: 2,
    MAPAS_HORARIOS: 3,
    PEDIDOS_HORAS_EXTRA: 4,
    APROVACAO_HORAS_EXTRA: 5,
    GESTAO_FICHAS: 6,
    FINANCEIRA: 7,
});

const SVG_ICONS = {
    HOME: `<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9,22 9,12 15,12 15,22"></polyline>`,
    CLOCK: `<circle cx="12" cy="12" r="10"></circle><polyline points="12,6 12,12 16,14"></polyline>`,
    CALENDAR: `<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>`,
    CALENDAR_ARROW: `<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><line x1="7" y1="16" x2="13" y2="16"></line><polyline points="13 14 17 16 13 18"></polyline>`,
    CHECK: `<polyline points="20,6 9,17 4,12"></polyline>`,
    CIRCLE_CHECK: `<circle cx="12" cy="12" r="10"></circle><polyline points="9 12 12 15 17 10"></polyline>`,
    FILE: `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>`,
    PEOPLE: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>`,
    DOCUMENT: `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline>`,
    BOOKING: `<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><path d="M8 2v4"></path><path d="M16 2v4"></path><path d="M21 10H3"></path><path d="M12 14l2 2 4-4"></path>`,
    COIN: `<circle cx="12" cy="12" r="9"></circle><path d="M8 10c1.5-1 4-1 6 0s1.5 3 0 4c-1.5 1-4 1-6 0" /><path d="M12 7v2" /><path d="M12 15v2" />`,
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
        ctaText: 'Ver Horários'
    },
    [CARD_TYPES.APROVACAO_HORARIOS]: {
        title: 'Aprovação de Horários',
        description: 'Aprove ou rejeite alterações de horários propostas por colaboradores',
        icon: SVG_ICONS.BOOKING,
        ctaText: 'Gerir Aprovações'
    },
    [CARD_TYPES.MAPAS_HORARIOS]: {
        title: 'Mapas de Horas',
        description: 'Gere e visualize mapas de horários para todos os colaboradores',
        icon: SVG_ICONS.CLOCK,
        permission: PERMISSIONS.MAPAS_HORARIOS,
        ctaText: 'Ver Mapas'
    },
    [CARD_TYPES.PEDIDOS_FERIAS]: {
        title: 'Pedido de Férias/Ausências',
        description: 'Solicite os seus próprios pedidos de férias e ausências como administrador',
        icon: SVG_ICONS.CALENDAR,
        ctaText: 'Gerir Pedidos'
    },
    [CARD_TYPES.APROVACAO_FERIAS]: {
        title: 'Aprovação de Férias/Ausências',
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
        icon: SVG_ICONS.PEOPLE,
        permission: PERMISSIONS.CONTROLO_COLABS,
        ctaText: 'Gerir Colaboradores'
    },
    [CARD_TYPES.PEDIDOS_HORAS_EXTRA]: {
        title: 'Propor Horas Extra',
        description: 'Solicite pedidos de horas extras ou ajustes de horário como administrador',
        icon: SVG_ICONS.CALENDAR,
        permission: PERMISSIONS.PEDIDOS_HORAS_EXTRA,
        ctaText: 'Gerir Pedidos'
    },
    [CARD_TYPES.APROVACAO_HORAS_EXTRA]: {
        title: 'Horas Extra',
        description: 'Aprove ou rejeite pedidos de horas extras ou ajustes de horário de todos os colaboradores',
        icon: SVG_ICONS.CIRCLE_CHECK,
        permission: PERMISSIONS.APROVACAO_HORAS_EXTRA,
        ctaText: 'Gerir Aprovações'
    },
    [CARD_TYPES.MARCACAO_DIRETA]: {
        title: 'Férias/Ausências Direta',
        description: 'Realize marcações diretas de ferias ou ausencias para colaboradores específicos',
        icon: SVG_ICONS.CALENDAR_ARROW,
        permission: PERMISSIONS.MARCACAO_DIRETA,
        ctaText: 'Fazer Marcação'
    },
    [CARD_TYPES.GESTAO_FICHAS]: {
        title: 'Gestão de Fichas',
        description: 'Gira as fichas pessoais de todos os colaboradores na organização',
        icon: SVG_ICONS.CLIPBOARD,
        permission: PERMISSIONS.GESTAO_FICHAS,
        ctaText: 'Gerir Fichas'
    },
    [CARD_TYPES.FINANCEIRA]: {
        title: 'Financeira',
        description: 'Acesse e gerencie informações financeiras relacionadas aos colaboradores',
        icon: SVG_ICONS.COIN,
        permission: PERMISSIONS.FINANCEIRA,
        ctaText: 'Ver Área'
    },
    [CARD_TYPES.FICHA_COLLAB]: {
        title: 'A Minha Ficha',
        description: 'Visualize e edite a sua própria ficha pessoal de colaborador',
        icon: SVG_ICONS.DOCUMENT,
        ctaText: 'Ver Ficha'
    },
};

/**
 * Mounts the dashboard *shell* (sidebar/footer/logout/user).
 * Call once at app startup.
 * @returns {() => void} cleanup
 */
export function mountDashboardShell() {
    const ctrl = new AbortController();
    const {signal} = ctrl;

    const footerYear = document.getElementById('yearSpan');
    if (footerYear) footerYear.textContent = String(new Date().getFullYear());

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener(
            'click',
            (ev) => {
                ev.preventDefault();
                logout().finally(() => User.clear());
            },
            {signal}
        );
    }

    const setActive = () => {
        const key = window.location.hash.replace(/^#/, '').trim();
        document.querySelectorAll('.sidebar-menu li').forEach((li) => li.classList.remove('active'));

        const sel = key
            ? `.sidebar-menu [data-content="${CSS.escape(key)}"]`
            : `.sidebar-menu [data-content="${CARD_TYPES.INICIO}"]`;

        document.querySelector(sel)?.closest('li')?.classList.add('active');
    };

    window.addEventListener('hashchange', setActive, {signal});
    setActive();

    return () => ctrl.abort();
}

function createWelcomeCard(key) {
    const def = CARD_DEFS[key];
    if (!def) return null;

    const tpl = document.getElementById("tpl-welcome-card");
    if (!tpl?.content) return null;

    const node = tpl.content.firstElementChild.cloneNode(true);

    node.dataset.dynamic = "1";     // mark as dynamic for later cleanup

    node.querySelector(".card-title")?.append(def.title);
    node.querySelector(".card-desc")?.append(def.description);

    const linkEl = node.querySelector(".card-link");
    if (linkEl) {
        linkEl.dataset.content = key;
        linkEl.textContent = def.ctaText;
        linkEl.setAttribute("href", `#${key}`);
    }

    const iconEl = node.querySelector(".card-icon");
    if (iconEl) iconEl.innerHTML = svg(def.icon, 24);

    return node;
}

function upsertWelcomeCards(keys) {
    const wc = document.querySelector(".welcome-content");
    if (!wc) return;

    wc.querySelectorAll('[data-dynamic="1"]').forEach((n) => n.remove());

    const frag = document.createDocumentFragment();
    keys.forEach((k) => {
        const card = createWelcomeCard(k);
        if (card) frag.appendChild(card);
    });

    const anchor = wc.querySelector(`[data-anchor="${CARD_TYPES.FICHA_COLLAB}"]`);
    wc.insertBefore(frag, anchor || null);
}

function upsertSidebarEntries(keys) {
    const menu = document.querySelector(".sidebar-menu");
    if (!menu) return;

    menu.querySelectorAll('li[data-dynamic="1"]').forEach((li) => li.remove());

    const existing = new Set(Array.from(menu.querySelectorAll("[data-content]")).map((a) => a.dataset.content));

    const frag = document.createDocumentFragment();
    keys.forEach((t) => {
        if (existing.has(t)) return;

        const def = CARD_DEFS[t];
        if (!def) return;

        const li = document.createElement("li");
        li.dataset.dynamic = "1";
        li.innerHTML = `
      <a href="#${t}" data-content="${t}">
        <span class="menu-icon">${svg(def.icon, 20, "menu-icon")}</span>${def.title}
      </a>`;
        frag.appendChild(li);
    });

    const anchor = menu.querySelector(`[data-anchor="${CARD_TYPES.FICHA_COLLAB}"]`);
    menu.insertBefore(frag, anchor || null);
}

function computeCardsFromUser(auth) {
    const a = auth || {};
    const perms = new Set(a.permissions || []);
    const hasSubs = (a.subordinados?.length ?? 0) > 0;

    const permCards = Object.entries(CARD_DEFS)
        .filter(([_, def]) => def.permission && perms.has(def.permission))
        .map(([key]) => key);

    const sideCards = new Set(permCards);
    const welcomeCards = new Set(permCards);

    if (hasSubs) {
        welcomeCards.add(CARD_TYPES.APROVACAO_HORARIOS);
        welcomeCards.add(CARD_TYPES.APROVACAO_FERIAS);
        welcomeCards.add(CARD_TYPES.CONSULTA_PEDIDOS);

        sideCards.add(CARD_TYPES.APROVACAO_HORARIOS);
        sideCards.add(CARD_TYPES.APROVACAO_FERIAS);
        sideCards.add(CARD_TYPES.CONSULTA_PEDIDOS);
    }

    return {sideCards, welcomeCards};
}

/**
 * Mounts the "Início" view contents (cards, dynamic sidebar entries).
 * Called by the router after injecting inicio.html.
 * @returns {() => void} cleanup
 */
export function mountInicio() {
    const ctrl = new AbortController();

    const user = User.get();
    const el = document.getElementById("userName");
    if (el) el.textContent = user?.name || "";

    User.getPrivileges()
        .then((userInfo) => {
            const {sideCards, welcomeCards} = computeCardsFromUser(userInfo);

            if (welcomeCards.size) upsertWelcomeCards([...welcomeCards]);
            if (sideCards.size) upsertSidebarEntries([...sideCards]);
        })
        .catch((err) => console.error('Failed to load user for inicio:', err));

    return () => ctrl.abort();
}
