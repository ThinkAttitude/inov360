import {getRecord} from '../../api.js';

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

const recordState = {
    userId: null,
};

function renderRecordTable(profile) {
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
    thead.innerHTML = '<tr><th>Campo</th><th>Valor</th></tr>';

    const tbody = document.createElement('tbody');

    keys.forEach(key => {
        const label = PROFILE_FIELD_LABELS[key];
        if (!label) return;

        const tr = document.createElement('tr');

        const th = document.createElement('th');
        th.scope = 'row';
        th.className = 'gestao-record-cell-label';
        th.textContent = label;

        const td = document.createElement('td');
        td.className = 'gestao-record-cell-value';
        const value = Object.prototype.hasOwnProperty.call(record, key) ? record[key] : null;
        td.textContent = value == null ? '' : String(value);

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
        renderRecordTable(res.profile || {});
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
}

export function openRecordForUser(userId, name, email) {
    showRecordView(userId, name, email);
}

export function mountGstFchsRecord() {
    bindRecordControls();
}