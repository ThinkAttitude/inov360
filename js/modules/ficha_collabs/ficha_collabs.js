import {getSelfRecord, createRecordRequest} from '../../api.js';
import {FICHA_SECTIONS} from './ficha_collabs_fields.js';
import {initEditToggle} from './edit_toggle.js';

let fichaBaseState = null;
let fichaToggle = null;

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

function renderHeader(profile) {
    const nameEl = document.getElementById('user-name-display');
    if (nameEl) nameEl.textContent = profile && profile.name ? profile.name : '';
}

function renderSections(mode, sectionsConfig, state, onChange) {
    Object.entries(sectionsConfig).forEach(function ([containerId, fieldMap]) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const sourceKey = 'profile';
        const source = state[sourceKey] || {};
        const parts = [];

        Object.entries(fieldMap).forEach(function ([field, config]) {
            let label;
            let suffix = '';
            let highlight = false;
            let editable = false;

            if (typeof config === 'string') {
                label = config;
            } else {
                label = config.label;
                suffix = config.suffix || '';
                highlight = !!config.highlight;
                editable = !!config.editable;
            }

            const raw = source[field];
            const baseValue = raw === null || raw === undefined ? '' : String(raw);

            const isRequestField =
                field === 'email' ||
                field === 'telefone' ||
                field === 'morada' ||
                field === 'nib';

            const inputId = isRequestField
                ? field
                : 'ficha-input-' + containerId + '-' + field;

            if (mode === 'edit' && editable) {
                const classes = 'ficha-info-card' + (highlight ? ' ficha-highlight' : '');
                const suffixSpan = suffix
                    ? '<span class="ficha-info-suffix">' + escapeHtml(suffix) + '</span>'
                    : '';
                const collabAttr = isRequestField
                    ? ' data-collab-field="' + field + '"'
                    : '';

                parts.push(
                    '<div class="' + classes + '">' +
                    '<label class="ficha-info-label" for="' + inputId + '">' + escapeHtml(label) + '</label>' +
                    '<div class="ficha-info-edit-wrapper">' +
                    '<input id="' + inputId + '" class="ficha-info-input"' +
                    ' data-section="' + sourceKey + '"' +
                    ' data-field="' + field + '"' +
                    collabAttr +
                    ' data-original="' + escapeHtml(baseValue) + '"' +
                    ' value="' + escapeHtml(baseValue) + '">' +
                    suffixSpan +
                    '</div>' +
                    '</div>'
                );
            } else {
                let value = baseValue;
                if (!value) value = '-';
                if (value !== '-' && suffix) value += suffix;

                const classes = 'ficha-info-card' + (highlight ? ' ficha-highlight' : '');

                parts.push(
                    '<div class="' + classes + '">' +
                    '<div class="ficha-info-label">' + escapeHtml(label) + '</div>' +
                    '<div class="ficha-info-value">' + escapeHtml(value) + '</div>' +
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
                    if (sectionKey && field) {
                        if (!state[sectionKey]) state[sectionKey] = {};
                        state[sectionKey][field] = input.value;
                        if (typeof onChange === 'function') onChange(state);
                    }
                });
            });
        }
    });
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

        const nome = escapeHtml(emergency.emergencia_nome || emergency.nome || '');
        const parentesco = escapeHtml(emergency.emergencia_parentesco || emergency.parentesco || '');
        const telefone = escapeHtml(emergency.emergencia_telefone || emergency.telefone || '');

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

    const nome = emergency.emergencia_nome || emergency.nome || '';
    const parentesco = emergency.emergencia_parentesco || emergency.parentesco || '';
    const telefone = emergency.emergencia_telefone || emergency.telefone || '';

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
        '<div class="ficha-info-group">' +
        '<label class="ficha-info-label" for="emergencia_nome">Nome</label>' +
        '<input class="ficha-info-input" id="emergencia_nome" data-section="emergency" data-field="emergencia_nome" data-collab-field="emergencia_nome" data-original="' + escapeHtml(nome) + '" value="' + escapeHtml(nome) + '">' +
        '</div>' +
        '<div class="ficha-info-group">' +
        '<label class="ficha-info-label" for="emergencia_parentesco">Parentesco</label>' +
        '<input class="ficha-info-input" id="emergencia_parentesco" data-section="emergency" data-field="emergencia_parentesco" data-collab-field="emergencia_parentesco" data-original="' + escapeHtml(parentesco) + '" value="' + escapeHtml(parentesco) + '">' +
        '</div>' +
        '<div class="ficha-info-group">' +
        '<label class="ficha-info-label" for="emergencia_telefone">Telefone</label>' +
        '<input class="ficha-info-input" id="emergencia_telefone" data-section="emergency" data-field="emergencia_telefone" data-collab-field="emergencia_telefone" data-original="' + escapeHtml(telefone) + '" value="' + escapeHtml(telefone) + '">' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    const inputs = container.querySelectorAll('.ficha-info-input');
    inputs.forEach(function (input) {
        input.addEventListener('input', function () {
            const field = input.getAttribute('data-field');
            if (!state.emergency) state.emergency = {};
            state.emergency[field] = input.value;
            if (typeof onChange === 'function') onChange(state);
        });
    });
}

function renderFicha(mode, state, onChange) {
    const viewState = mode === 'view' ? fichaBaseState : state;
    const profile = viewState.profile || {};
    renderHeader(profile);
    renderSections(mode, FICHA_SECTIONS, viewState, onChange);
    renderEmergency(mode, viewState, onChange);
}

function getInputValue(id) {
    const el = document.getElementById(id);
    if (!el) return '';
    return el.value == null ? '' : String(el.value);
}

function buildCollabRequestPayload() {
    const payload = {
        email: getInputValue('email'),
        telefone: getInputValue('telefone'),
        morada: getInputValue('morada'),
        nib: getInputValue('nib'),
        emergencia_nome: getInputValue('emergencia_nome'),
        emergencia_parentesco: getInputValue('emergencia_parentesco'),
        emergencia_telefone: getInputValue('emergencia_telefone')
    };

    Object.keys(payload).forEach(function (key) {
        if (payload[key] === '') delete payload[key];
    });

    return payload;
}

function cloneState(obj) {
    return JSON.parse(JSON.stringify(obj || {}));
}

async function loadAndInitFicha() {
    try {
        const res = await getSelfRecord();
        if (!res || res.success !== true) return;

        fichaBaseState = {
            profile: res.profile || {},
            finance: res.finance || {},
            emergency: res.emergency || {}
        };

        const button = document.getElementById('abrir-edicao-completa');
        if (!button) {
            renderFicha('view', fichaBaseState, function () {
            });
            return;
        }

        if (!fichaToggle) {
            fichaToggle = initEditToggle(button, {
                render: function (mode, state, onChange) {
                    renderFicha(mode, state, onChange);
                },
                onSave: async function () {
                    const payload = buildCollabRequestPayload();
                    const keys = Object.keys(payload);
                    if (!keys.length) {
                        if (typeof showToast === 'function') {
                            showToast('Nenhuma alteração para guardar.', 'info');
                        }
                        return false;
                    }

                    return createRecordRequest(payload)
                        .then( (r) => {
                            return !(!r || r.success !== true);
                        })
                        .catch(function (e) {
                            console.error('Erro ao submeter pedido:', e);
                            return false;
                        });
                }
            });
        }

        fichaToggle.setState(cloneState(fichaBaseState));
    } catch (e) {
        console.error('Erro ao carregar ficha:', e);
    }
}

export async function mountFichaCollab() {
    await loadAndInitFicha();
}