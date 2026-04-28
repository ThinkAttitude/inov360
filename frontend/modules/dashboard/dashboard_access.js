import {User} from "../../shared/user_store.js";

export const CARD_TYPES = Object.freeze({
    INICIO: "inicio",
    HORARIOS: "horarios",
    APROVACAO_HORARIOS: "aprov_horarios",
    MAPAS_HORARIOS: "mapas_horarios",
    PEDIDOS_FERIAS: "pedidos_ferias",
    APROVACAO_FERIAS: "aprov_ferias",
    CONSULTA_PEDIDOS: "consulta_pedidos",
    LISTA_INTERMEDIOS: "lista_intermedios",
    CONTROLO_COLABS: "controlo_colabs",
    PEDIDOS_HORAS_EXTRA: "pedidos_horas_extras",
    APROVACAO_HORAS_EXTRA: "aprov_horas_extras",
    MARCACAO_DIRETA: "marcacao_direta",
    GESTAO_FICHAS: "gestao_fichas",
    FICHA_COLLAB: "ficha_collab",
});

export const PERMISSIONS = Object.freeze({
    CONTROLO_COLABS: 1,
    MARCACAO_DIRETA: 2,
    MAPAS_HORARIOS: 3,
    PEDIDOS_HORAS_EXTRA: 4,
    APROVACAO_HORAS_EXTRA: 5,
    GESTAO_FICHAS: 6,
});

const CARDS = new Set([
    CARD_TYPES.INICIO,
    CARD_TYPES.HORARIOS,
    CARD_TYPES.PEDIDOS_FERIAS,
    CARD_TYPES.LISTA_INTERMEDIOS,
    CARD_TYPES.FICHA_COLLAB,
]);

function hasPermission(auth, permission) {
    return new Set(auth?.permissions || []).has(permission);
}

function hasSubordinates(auth) {
    return (auth?.subordinados?.length ?? 0) > 0;
}

const CARD_RULES = Object.freeze({
    [CARD_TYPES.APROVACAO_HORARIOS]: hasSubordinates,
    [CARD_TYPES.APROVACAO_FERIAS]: hasSubordinates,
    [CARD_TYPES.CONSULTA_PEDIDOS]: hasSubordinates,
    [CARD_TYPES.CONTROLO_COLABS]: (auth) => hasPermission(auth, PERMISSIONS.CONTROLO_COLABS),
    [CARD_TYPES.MARCACAO_DIRETA]: (auth) => hasPermission(auth, PERMISSIONS.MARCACAO_DIRETA),
    [CARD_TYPES.MAPAS_HORARIOS]: (auth) => hasPermission(auth, PERMISSIONS.MAPAS_HORARIOS),
    [CARD_TYPES.PEDIDOS_HORAS_EXTRA]: (auth) => hasPermission(auth, PERMISSIONS.PEDIDOS_HORAS_EXTRA),
    [CARD_TYPES.APROVACAO_HORAS_EXTRA]: (auth) => hasPermission(auth, PERMISSIONS.APROVACAO_HORAS_EXTRA),
    [CARD_TYPES.GESTAO_FICHAS]: (auth) => hasPermission(auth, PERMISSIONS.GESTAO_FICHAS),
});

export function canAccessCard(auth, key) {
    if (CARDS.has(key)) return true;

    const rule = CARD_RULES[key];
    return typeof rule === "function" ? Boolean(rule(auth)) : false;
}

export function getCardsFromUser(auth) {
    const dynamicCards = Object.keys(CARD_RULES).filter((key) => canAccessCard(auth, key));

    return {
        sideCards: new Set(dynamicCards),
        welcomeCards: new Set(dynamicCards),
    };
}

export async function guardDashboardRoute(rawKey) {
    const key = rawKey || CARD_TYPES.INICIO;
    const auth = await User.ensure();

    if (!auth) {
        window.location.replace("/login.html");
        return null;
    }

    if (!canAccessCard(auth, key)) {
        window.location.hash = `#${CARD_TYPES.INICIO}`;
        return null;
    }

    return key;
}