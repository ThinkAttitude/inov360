const field = (label, options = {}) => Object.freeze({
    label,
    type: 'text',
    editable: false,
    ...options,
});

export const PERSONAL_FIELDS = {
    nome: field('Nome'),
    email: field('Email', {editable: true}),
    telefone: field('Telefone', {editable: true}),
    morada: field('Morada', {editable: true}),
    codigo_postal: field('Código Postal'),
    concelho: field('Concelho'),
    distrito: field('Distrito'),
    naturalidade: field('Naturalidade'),
    habilitacoes: field('Habilitações'),
};

export const FAMILY_FIELDS = {
    estado_civil: field('Estado Civil'),
    data_nascimento: field('Data Nascimento', {type: 'date'}),
    pais: field('País'),
    tipo_documento: field('Tipo Documento'),
    numero_documento: field('Número Documento'),
    emitido_em: field('Emitido em (Data)', {type: 'date'}),
    arquivo: field('Arquivo'),
    validade_documento: field('Validade Documento', {type: 'date'}),
    nif: field('NIF'),
    numero_seg_social: field('Número Segurança Social'),
};

export const FISCAL_FIELDS = {
    estado_fiscal: field('Estado Fiscal'),
    deficiencia: field('Deficiência'),
    conjugue_deficiente: field('Cônjuge Deficiente', {type: 'int'}),
    num_dependentes: field('Nº Dependentes', {type: 'int'}),
    num_dependentes_deficientes: field('Nº Dependentes Deficientes', {type: 'int'}),
    pensionista: field('Pensionista', {type: 'int'}),
};

export const CONTRACT_FIELDS = {
    data_admissao: field('Data Admissão', {type: 'date'}),
    tipo_contrato: field('Tipo de Contrato'),
    profissao: field('Profissão'),
    categoria: field('Categoria'),
    regime: field('Regime'),
    horas_semana: field('Horas/Semana', {type: 'int'}),
    salario_base: field('Salário Base', {type: 'decimal', suffix: ' €', highlight: true}),
    subsidio_alimentacao: field('Sub. Alimentação', {type: 'decimal', suffix: ' €'}),
    nib: field('NIB', {editable: true}),
    ordenado_liquido: field('Ordenado Líquido', {type: 'decimal', suffix: ' €', highlight: true}),
};

export const EMERGENCY_FIELDS = {
    emergency_nome: field('Nome', {editable: true}),
    emergency_parentesco: field('Parentesco', {editable: true}),
    emergency_telefone: field('Telefone', {editable: true}),
    emergency_grupo_sanguineo: field('Grupo sanguíneo', {editable: true}),
};

export const FICHA_SECTIONS = {
    'dados-pessoais': PERSONAL_FIELDS,
    'dados-familiares': FAMILY_FIELDS,
    'dados-fiscais': FISCAL_FIELDS,
    'dados-contratuais': CONTRACT_FIELDS,
};

export const FICHA_FIELD_META = Object.freeze({
    ...PERSONAL_FIELDS,
    ...FAMILY_FIELDS,
    ...FISCAL_FIELDS,
    ...CONTRACT_FIELDS,
});

export const FICHA_RECORD_FIELD_META = Object.freeze({
    ...FICHA_FIELD_META,
    ...EMERGENCY_FIELDS,
});

export const FICHA_FIELD_LABELS = Object.freeze(
    Object.fromEntries(
        Object.entries(FICHA_RECORD_FIELD_META).map(([key, meta]) => [key, meta.label])
    )
);

export const FICHA_EDITABLE_FIELD_META = Object.freeze(
    Object.fromEntries(
        Object.entries(FICHA_RECORD_FIELD_META).filter(([, meta]) => meta.editable)
    )
);