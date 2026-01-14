import {updateRecord, getRecord} from '../../app/api.js';

// TODO: Incorporate with the fields for ficha_collabs module if possible
// TODO: Replace filtering readonly fields logic with iterating over a subset of editable fields
const PROFILE_FIELDS = {
    nome: {label: 'Nome', type: 'text', readonly: true},
    email: {label: 'E-mail', type: 'text'},
    telefone: {label: 'Telefone', type: 'text'},
    morada: {label: 'Morada', type: 'text'},
    codigo_postal: {label: 'Código postal', type: 'text'},
    freguesia: {label: 'Freguesia', type: 'text'},
    concelho: {label: 'Concelho', type: 'text'},
    distrito: {label: 'Distrito', type: 'text'},
    naturalidade: {label: 'Naturalidade', type: 'text'},
    habilitacoes: {label: 'Habilitações', type: 'text'},
    pai: {label: 'Pai', type: 'text'},
    mae: {label: 'Mãe', type: 'text'},
    estado_civil: {label: 'Estado civil', type: 'text'},
    data_nascimento: {label: 'Data de nascimento', type: 'date'},
    pais: {label: 'País', type: 'text'},
    tipo_documento: {label: 'Tipo de documento', type: 'text'},
    numero_documento: {label: 'Número de documento', type: 'text'},
    emitido_em: {label: 'Emitido em', type: 'text'},
    arquivo: {label: 'Arquivo', type: 'text'},
    validade_documento: {label: 'Validade do documento', type: 'date'},
    nif: {label: 'NIF', type: 'text'},
    numero_seg_social: {label: 'Número de Segurança Social', type: 'text'},
    descontos_fiscais: {label: 'Descontos fiscais', type: 'text'},
    reparticao_financas: {label: 'Repartição de finanças', type: 'text'},
    regiao: {label: 'Região', type: 'text'},
    estado_fiscal: {label: 'Estado fiscal', type: 'text'},
    deficiencia: {label: 'Deficiência', type: 'text'},
    conjugue_deficiente: {label: 'Cônjuge deficiente', type: 'int'},
    num_dependentes: {label: 'N.º de dependentes', type: 'int'},
    num_dependentes_deficientes: {label: 'N.º de dependentes deficientes', type: 'int'},
    pensionista: {label: 'Pensionista', type: 'int'},
    data_admissao: {label: 'Data de admissão', type: 'date'},
    tipo_contrato: {label: 'Tipo de contrato', type: 'text'},
    profissao: {label: 'Profissão', type: 'text'},
    categoria: {label: 'Categoria', type: 'text'},
    regime: {label: 'Regime', type: 'text'},
    horas_semana: {label: 'Horas por semana', type: 'int'},
    salario_base: {label: 'Salário base', type: 'decimal'},
    subsidio_alimentacao: {label: 'Subsídio de alimentação', type: 'decimal'},
    nib: {label: 'NIB', type: 'text'},
    ordenado_liquido: {label: 'Ordenado líquido', type: 'decimal'},
    validacao_empresa: {label: 'Validação da empresa', type: 'text'},
};

export const PROFILE_FIELD_LABELS = Object.fromEntries(
    Object.entries(PROFILE_FIELDS).map(([key, meta]) => [key, meta.label])
);

const recordState = {
    userId: null,
    profile: {},
    isEditing: false,
};

function renderRecordTable(profile, editable) {
    const container = document.getElementById('gestao-record-table');
    if (!container) return;

    container.innerHTML = '';

    const record = profile || {};
    const keys = Object.keys(PROFILE_FIELDS);

    const wrapper = document.createElement('div');
    wrapper.className = 'gestao-table-wrapper gestao-table-wrapper--record';

    const table = document.createElement('table');
    table.className = 'gestao-table gestao-record-table';

    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    const thField = document.createElement('th');
    thField.textContent = 'Campo';
    const thValue = document.createElement('th');
    thValue.textContent = editable ? 'Valor (edição)' : 'Valor';
    headerRow.appendChild(thField);
    headerRow.appendChild(thValue);
    thead.appendChild(headerRow);

    const tbody = document.createElement('tbody');

    keys.forEach(key => {
        const meta = PROFILE_FIELDS[key];
        const label = meta.label;
        if (!label) return;

        const tr = document.createElement('tr');

        const th = document.createElement('th');
        th.className = 'gestao-record-cell-label';
        th.textContent = label;

        const td = document.createElement('td');
        td.className = 'gestao-record-cell-value';

        const value = Object.prototype.hasOwnProperty.call(record, key)
            ? record[key]
            : null;

        const isReadonly = !editable || meta.readonly;

        if (isReadonly) {
            td.textContent = value == null ? '' : String(value);
        } else {
            const input = document.createElement('input');
            input.className = 'gestao-record-input';
            input.dataset.field = key;
            input.value = value == null ? '' : String(value);

            if (meta.type === 'date') {
                input.type = 'date';
            } else if (meta.type === 'int' || meta.type === 'decimal') {
                input.type = 'number';
                input.step = 'any';
            } else {
                input.type = 'text';
            }

            td.appendChild(input);
        }

        tr.appendChild(th);
        tr.appendChild(td);
        tbody.appendChild(tr);
    });

    table.appendChild(thead);
    table.appendChild(tbody);
    wrapper.appendChild(table);
    container.appendChild(wrapper);
}

function setEditingMode(isEditing) {
    recordState.isEditing = isEditing;

    renderRecordTable(recordState.profile, isEditing);

    const editBtn = document.getElementById('gestao-record-edit');
    const saveBtn = document.getElementById('gestao-record-save');
    const cancelBtn = document.getElementById('gestao-record-cancel');

    if (editBtn) {
        editBtn.style.display = isEditing ? 'none' : '';
        editBtn.disabled = !recordState.userId;
    }
    if (saveBtn) saveBtn.style.display = isEditing ? '' : 'none';
    if (cancelBtn) cancelBtn.style.display = isEditing ? '' : 'none';
}

function normalizeFieldValue(key, rawValue) {
    if (rawValue == null) return null;

    let v = String(rawValue).trim();
    if (v === '') return null;

    const field = PROFILE_FIELDS[key];

    if (field.type === 'date') return v;

    if (field.type === 'int') {
        const n = parseInt(v, 10);
        return Number.isNaN(n) ? null : n;
    }

    if (field.type === 'decimal') {
        const n = parseFloat(v.replace(',', '.'));
        return Number.isNaN(n) ? null : n;
    }

    return v;
}

function collectEditsFromTable() {
    const container = document.getElementById('gestao-record-table');
    if (!container) return {};

    const inputs = container.querySelectorAll('input[data-field]');
    const payload = {};

    inputs.forEach(input => {
        const key = input.dataset.field;
        const raw = input.value;

        const normalized = normalizeFieldValue(key, raw);

        const originalRaw =
            recordState.profile && Object.prototype.hasOwnProperty.call(recordState.profile, key)
                ? recordState.profile[key]
                : null;

        const originalNormalized = normalizeFieldValue(key, originalRaw);

        const bothNull = normalized == null && originalNormalized == null;
        const equal = bothNull || normalized === originalNormalized;

        if (!equal) {
            payload[key] = normalized;
        }
    });

    return payload;
}

async function saveRecordEdits() {
    if (!recordState.userId) return;

    const updates = collectEditsFromTable();
    if (!updates || Object.keys(updates).length === 0) {
        setEditingMode(false);
        return;
    }

    try {
        const res = await updateRecord(recordState.userId, updates, true);
        if (!res || res.success !== true) {
            throw new Error('Resposta inválida de direct_edit');
        }

        recordState.profile = (res.updated && res.updated.profile) || recordState.profile;
        setEditingMode(false);
    } catch (err) {
        console.error('Falha ao guardar edição direta:', err);
        alert('Não foi possível guardar as alterações da ficha.');
    }
}

async function loadRecord(userId, name) {
    const container = document.getElementById('gestao-record-table');
    const subtitle = document.getElementById('gestao-record-subtitle');

    if (container) container.textContent = 'A carregar ficha...';
    if (subtitle) {
        const who = name || 'colaborador';
        subtitle.textContent = `Ficha completa de ${who}.`;
    }

    try {
        const res = await getRecord(userId);
        if (!res || res.success !== true) {
            throw new Error('Resposta inválida de aval_view_record');
        }

        recordState.profile = res.profile || {};
        setEditingMode(false);
    } catch (err) {
        console.error('Falha ao carregar ficha completa:', err);
        if (container) container.textContent = 'Não foi possível carregar a ficha.';
    }
}

function showRecordView(userId, name, email) {
    recordState.userId = userId;

    const listEl = document.getElementById('gestao-allrecs-list');
    const recordEl = document.getElementById('gestao-record-view');

    if (listEl) listEl.classList.add('gestao-allrecs-list--hidden');
    if (recordEl) recordEl.classList.add('gestao-record-view--active');

    loadRecord(userId, name, email);
}

function showAllRecsListFromRecord() {
    const listEl = document.getElementById('gestao-allrecs-list');
    const recordEl = document.getElementById('gestao-record-view');

    if (recordEl) recordEl.classList.remove('gestao-record-view--active');
    if (listEl) listEl.classList.remove('gestao-allrecs-list--hidden');
}

function bindRecordControls() {
    const backBtn = document.getElementById('gestao-record-back');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            showAllRecsListFromRecord();
        });
    }

    const editBtn = document.getElementById('gestao-record-edit');
    if (editBtn) {
        editBtn.addEventListener('click', () => {
            if (!recordState.userId) return;
            setEditingMode(true);
        });
    }

    const saveBtn = document.getElementById('gestao-record-save');
    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            saveRecordEdits();
        });
    }

    const cancelBtn = document.getElementById('gestao-record-cancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            setEditingMode(false);
        });
    }
}

export function openRecordForUser(userId, name, email) {
    showRecordView(userId, name, email);
}

export function mountGstFchsRecord() {
    bindRecordControls();
}