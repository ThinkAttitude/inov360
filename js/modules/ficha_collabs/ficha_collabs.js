import {getSelfRecord} from '../../api.js';
import {FICHA_SECTIONS} from './ficha_collabs_fields.js';
import {initEditToggle} from "./edit_toggle.js";

let fichaState = null;
let editToggle = null;

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

function renderSections(mode, sectionsConfig, state, onChange) {
    Object.entries(sectionsConfig).forEach(function ([containerId, fieldMap]) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const sourceKey = containerId === 'dados-fiscais' ? 'finance' : 'profile';
        const source = state[sourceKey] || {};
        const parts = [];

        Object.entries(fieldMap).forEach(function ([field, config]) {
            let label;
            let suffix = '';
            let highlight = false;

            if (typeof config === 'string') {
                label = config;
            } else {
                label = config.label;
                suffix = config.suffix || '';
                highlight = !!config.highlight;
            }

            const raw = source[field];

            if (mode === 'view') {
                let value = raw === null || raw === undefined || raw === '' ? '-' : String(raw);
                if (value !== '-' && suffix) value += suffix;

                const classes = 'ficha-info-card' + (highlight ? ' ficha-highlight' : '');

                parts.push(
                    '<div class="' + classes + '">' +
                    '<div class="ficha-info-label">' + escapeHtml(label) + '</div>' +
                    '<div class="ficha-info-value">' + escapeHtml(value) + '</div>' +
                    '</div>'
                );
            } else {
                const baseValue = raw === null || raw === undefined ? '' : String(raw);
                const classes = 'ficha-info-card' + (highlight ? ' ficha-highlight' : '');
                const inputAttrs =
                    'class="ficha-info-input" ' +
                    'data-section="' + sourceKey + '" ' +
                    'data-field="' + field + '" ' +
                    'value="' + escapeHtml(baseValue) + '"';

                let suffixSpan = '';
                if (suffix) {
                    suffixSpan = '<span class="ficha-info-suffix">' + escapeHtml(suffix) + '</span>';
                }

                parts.push(
                    '<div class="' + classes + '">' +
                    '<div class="ficha-info-label">' + escapeHtml(label) + '</div>' +
                    '<div class="ficha-info-edit-wrapper">' +
                    '<input ' + inputAttrs + '>' +
                    suffixSpan +
                    '</div>' +
                    '</div>'
                );
            }
        });

        container.innerHTML = parts.join('');

        if (mode === 'edit') {
            const inputs = container.querySelectorAll('.ficha-info-input');
            inputs.forEach(function (input) {
                input.addEventListener('input', function () {
                    const sectionKey = input.getAttribute('data-section');
                    const field = input.getAttribute('data-field');
                    if (!state[sectionKey]) state[sectionKey] = {};
                    state[sectionKey][field] = input.value;
                    if (typeof onChange === 'function') onChange(state);
                });
            });
        }
    });
}

function renderHeader(profile) {
    const nameEl = document.getElementById('user-name-display');
    if (nameEl) nameEl.textContent = profile && profile.nome ? profile.nome : '';
}

function renderEmergency(mode, state, onChange) {
    const container = document.getElementById('contactos-container');
    if (!container) return;

    const emergency = state.emergency || {};

    if (mode === 'view') {
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
        return;
    }

    const nome = emergency.nome || '';
    const parentesco = emergency.parentesco || '';
    const telefone = emergency.telefone || '';

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
        '<div class="ficha-info-label">Nome</div>' +
        '<input class="ficha-info-input" data-emergency="nome" value="' + escapeHtml(nome) + '">' +
        '<div class="ficha-info-label">Parentesco</div>' +
        '<input class="ficha-info-input" data-emergency="parentesco" value="' + escapeHtml(parentesco) + '">' +
        '<div class="ficha-info-label">Telefone</div>' +
        '<input class="ficha-info-input" data-emergency="telefone" value="' + escapeHtml(telefone) + '">' +
        '</div>' +
        '</div>' +
        '</div>';

    const inputs = container.querySelectorAll('.ficha-info-input[data-emergency]');
    inputs.forEach(function (input) {
        input.addEventListener('input', function () {
            const field = input.getAttribute('data-emergency');
            if (!state.emergency) state.emergency = {};
            state.emergency[field] = input.value;
            if (typeof onChange === 'function') onChange(state);
        });
    });
}

function renderFicha(mode, state, onChange) {
    renderHeader(state.profile);
    renderSections(mode, FICHA_SECTIONS, state, onChange);
    renderEmergency(mode, state, onChange);
}

async function loadAndRenderFicha() {
    try {
        const res = await getSelfRecord();
        if (!res || res.success !== true) return;

        fichaState = {
            profile: res.profile || {},
            finance: res.finance || {},
            emergency: res.emergency || {}
        };

        const button = document.getElementById('abrir-edicao-completa');
        if (!button) {
            renderFicha('view', fichaState, function () {});
            return;
        }

        if (!editToggle) {
            editToggle = initEditToggle(button, {
                render: function (mode, state, onChange) {
                    renderFicha(mode, state, onChange);
                },
                onSave: async function (state) {
                    return true;
                }
            });
        }

        editToggle.setState(fichaState);
    } catch (e) {
        console.error('Erro ao carregar ficha:', e);
    }
}

export async function mountFichaCollab() {
    await loadAndRenderFicha();
}