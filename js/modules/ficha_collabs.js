import {getSelfRecord} from '../api.js';

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value).replace(/[&<>"']/g, function (c) {
        if (c === '&') return '&amp;';
        if (c === '<') return '&lt;';
        if (c === '>') return '&gt;';
        if (c === '"') return '&quot;';
        return '&#39;';
    });
}

function formatLabel(key) {
    return key.replace(/_/g, ' ').toUpperCase();
}

function filterFlatProps(source, { skipId = false } = {}) {
    return Object.fromEntries(
        Object.entries(source || {}).filter(([key, value]) => {
            if (skipId && key === 'id') return false;
            if (value == null) return false;
            if (typeof value === 'object') return false;
            return true;
        })
    );
}

function renderGrid(containerId, source) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const obj = filterFlatProps(source);
    const entries = Object.entries(obj);

    if (!entries.length) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = entries.map(function (pair) {
        const key = pair[0];
        const rawValue = pair[1];
        const label = formatLabel(key);
        const value = rawValue === '' ? '-' : String(rawValue);
        return (
            '<div class="ficha-info-card">' +
            '<div class="ficha-info-label">' + escapeHtml(label) + '</div>' +
            '<div class="ficha-info-value">' + escapeHtml(value) + '</div>' +
            '</div>'
        );
    }).join('');
}

function renderHeader(user) {
    const nameEl = document.getElementById('user-name-display');
    const emailEl = document.getElementById('user-email-display');
    if (nameEl) nameEl.textContent = user && user.name ? user.name : '';
    if (emailEl) emailEl.textContent = user && user.email ? user.email : '';
}

function renderEmergency(emergency) {
    const container = document.getElementById('contactos-container');
    if (!container) return;

    if (!emergency || Object.keys(emergency).length === 0) {
        container.innerHTML =
            '<div class="ficha-empty-contacts">' +
            '<div class="ficha-empty-icon">' +
            '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>' +
            '</svg>' +
            '</div>' +
            '<p>Sem contactos de emergência registados.</p>' +
            '</div>';
        return;
    }

    const nome = escapeHtml(emergency.nome || '');
    const parentesco = escapeHtml(emergency.parentesco || '');
    const telefone = escapeHtml(emergency.telefone || '');

    container.innerHTML =
        '<div class="ficha-contacts-grid">' +
        '<div class="ficha-contact-card">' +
        '<div class="ficha-contact-avatar">' +
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>' +
        '<circle cx="12" cy="7" r="4"></circle>' +
        '</svg>' +
        '</div>' +
        '<div class="ficha-contact-info">' +
        '<div class="ficha-contact-name">' + nome + '</div>' +
        '<div class="ficha-contact-relation">' + parentesco + '</div>' +
        '<div class="ficha-contact-phone">' +
        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>' +
        '</svg>' +
        telefone +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';
}

async function fetchFichaData() {
    const res = await getSelfRecord();

    const user = res.user || {};
    const profile = res.profile || {};
    const finance = res.finance || {};
    const emergency = res.emergency || {};

    const profilePersonal = profile.personal || profile;
    const profileFamily = profile.family || {};
    const profileContract = profile.contract || {};

    const userSimple = filterFlatProps(user, { skipId: true });
    const personalSource = Object.assign({}, userSimple, profilePersonal || {});

    return {
        user,
        personalSource,
        profileFamily,
        finance,
        profileContract,
        emergency
    };
}

async function loadAndRenderFicha() {
    try {
        const {
            user,
            personalSource,
            profileFamily,
            finance,
            profileContract,
            emergency
        } = await fetchFichaData();

        renderHeader(user);
        renderGrid('dados-pessoais', personalSource);
        renderGrid('dados-familiares', profileFamily);
        renderGrid('dados-fiscais', finance);
        renderGrid('dados-contratuais', profileContract);
        renderEmergency(emergency);
    } catch (e) {
        console.error('Erro ao carregar ficha:', e);
    }
}

export async function mountFichaCollab() {
    await loadAndRenderFicha();
}
