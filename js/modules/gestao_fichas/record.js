import {getRecord, directEdit} from '../../api.js';

export const PROFILE_FIELD_LABELS = {
    nome: 'Nome',
    email: 'E-mail',
    telefone: 'Telefone',
    morada: 'Morada',
    codigo_postal: 'Código postal',
    freguesia: 'Freguesia',
    concelho: 'Concelho',
    distrito: 'Distrito',
    naturalidade: 'Naturalidade',
    habilitacoes: 'Habilitações',
    pai: 'Pai',
    mae: 'Mãe',
    estado_civil: 'Estado civil',
    data_nascimento: 'Data de nascimento',
    pais: 'País',
    tipo_documento: 'Tipo de documento',
    numero_documento: 'Número de documento',
    emitido_em: 'Emitido em',
    arquivo: 'Arquivo',
    validade_documento: 'Validade do documento',
    nif: 'NIF',
    numero_seg_social: 'Número de Segurança Social',
    descontos_fiscais: 'Descontos fiscais',
    reparticao_financas: 'Repartição de finanças',
    regiao: 'Região',
    estado_fiscal: 'Estado fiscal',
    deficiencia: 'Deficiência',
    conjugue_deficiente: 'Cônjuge deficiente',
    num_dependentes: 'N.º de dependentes',
    num_dependentes_deficientes: 'N.º de dependentes deficientes',
    pensionista: 'Pensionista',
    data_admissao: 'Data de admissão',
    tipo_contrato: 'Tipo de contrato',
    profissao: 'Profissão',
    categoria: 'Categoria',
    regime: 'Regime',
    horas_semana: 'Horas por semana',
    salario_base: 'Salário base',
    subsidio_alimentacao: 'Subsídio de alimentação',
    nib: 'NIB',
    ordenado_liquido: 'Ordenado líquido',
    validacao_empresa: 'Validação da empresa',
};

const DATE_FIELDS = new Set([
    'data_nascimento',
    'validade_documento',
    'data_admissao',
]);

const INT_FIELDS = new Set([
    'num_dependentes',
    'num_dependentes_deficientes',
    'conjugue_deficiente',
    'pensionista',
    'horas_semana',
]);

const DEC_FIELDS = new Set([
    'salario_base',
    'subsidio_alimentacao',
    'ordenado_liquido',
]);


const READONLY_FIELDS = new Set(['nome']);


const recordState = {
    userId: null,
    profile: {},
    isEditing: false,
};

function renderRecordTable(profile, editable = false) {
    const container = document.getElementById('gestao-record-table');
    if (!container) return;

    container.innerHTML = '';

    const record = profile || {};
    const keys = Object.keys(PROFILE_FIELD_LABELS);

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
        const label = PROFILE_FIELD_LABELS[key];
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

        if (!editable || READONLY_FIELDS.has(key)) {
            // modo leitura
            td.textContent = value == null ? '' : String(value);
        } else {
            // modo edição
            const input = document.createElement('input');
            input.className = 'gestao-record-input';
            input.dataset.field = key;  // para recolher depois
            input.value = value == null ? '' : String(value);

            if (DATE_FIELDS.has(key)) {
                input.type = 'date';
            } else if (INT_FIELDS.has(key) || DEC_FIELDS.has(key)) {
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


async function loadRecord(userId, name, email) {
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
        recordState.isEditing = false;

        renderRecordTable(recordState.profile, false);

        const editBtn = document.getElementById('gestao-record-edit');
        if (editBtn) {
            editBtn.disabled = false;
        }
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

function setEditingMode(isEditing) {
    recordState.isEditing = isEditing;

    const profile = recordState.profile || {};
    renderRecordTable(profile, isEditing);

    const editBtn = document.getElementById('gestao-record-edit');
    const saveBtn = document.getElementById('gestao-record-save');
    const cancelBtn = document.getElementById('gestao-record-cancel');

    if (editBtn) editBtn.style.display = isEditing ? 'none' : '';
    if (saveBtn) saveBtn.style.display = isEditing ? '' : 'none';
    if (cancelBtn) cancelBtn.style.display = isEditing ? '' : 'none';
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

        // valor original vindo da ficha carregada
        const originalRaw =
            recordState.profile && Object.prototype.hasOwnProperty.call(recordState.profile, key)
                ? recordState.profile[key]
                : null;

        const originalNormalized = normalizeFieldValue(key, originalRaw);

        const bothNull = normalized == null && originalNormalized == null;
        const equal = bothNull || normalized === originalNormalized;

        // só envia se realmente mudou
        if (!equal) {
            payload[key] = normalized;
        }
    });

    return payload;
}



async function saveRecordEdits() {
    if (!recordState.userId) return;

    const updates = collectEditsFromTable();

    console.log('DEBUG directEdit userId:', recordState.userId);
    console.log('DEBUG directEdit payload:', updates);

    try {
        const res = await directEdit(recordState.userId, updates, true);

        console.log('DEBUG directEdit response:', res);

        if (!res || res.success !== true) {
            throw new Error('Resposta inválida de direct_edit');
        }

        const updatedProfile =
            (res.updated && res.updated.profile) || recordState.profile;

        recordState.profile = updatedProfile;
        setEditingMode(false);
    } catch (err) {
        console.error('Falha ao guardar edição direta:', err);
        alert('Não foi possível guardar as alterações da ficha.');
    }
}

function normalizeFieldValue(key, rawValue) {
    if (rawValue == null) return null;

    let v = String(rawValue).trim();

    // vazio → null (é isto que queres)
    if (v === '') return null;

    // datas: assumimos yyyy-mm-dd vindo do input[type=date]
    if (DATE_FIELDS.has(key)) {
        return v; // o backend já valida o formato
    }

    // inteiros
    if (INT_FIELDS.has(key)) {
        const n = parseInt(v, 10);
        return Number.isNaN(n) ? null : n;
    }

    // decimais
    if (DEC_FIELDS.has(key)) {
        // podes melhorar isto se quiseres suportar vírgulas, etc.
        const n = parseFloat(v.replace(',', '.'));
        return Number.isNaN(n) ? null : n;
    }

    // por defeito, string normal (sem espaços extremos)
    return v;
}
