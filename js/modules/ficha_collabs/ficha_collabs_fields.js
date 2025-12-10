export const PERSONAL_FIELDS = {
    nome: 'Nome',
    email: { label: 'Email', editable: true },
    contacto_telefone: { label: 'Contacto telefone', editable: true },
    morada: { label: 'Morada', editable: true },
    codigo_postal: 'Código Postal',
    freguesia: 'Freguesia',
    concelho: 'Concelho',
    distrito: 'Distrito',
    naturalidade: 'Naturalidade',
    habilitacoes: 'Habilitações'
};

export const FAMILY_FIELDS = {
    pai: 'Pai',
    mae: 'Mãe',
    estado_civil: 'Estado Civil',
    data_nascimento: 'Data Nascimento',
    pais: 'País',
    tipo_documento: 'Tipo Documento',
    numero_documento: 'Número Documento',
    emitido_em: 'Emitido em',
    arquivo: 'Arquivo',
    validade_documento: 'Validade Documento',
    nif: 'NIF',
    numero_seg_social: 'Número Segurança Social'
};

export const FISCAL_FIELDS = {
    descontos_fiscais: 'Descontos Fiscais',
    reparticao_financas: 'Repartição Finanças',
    regiao: 'Região',
    estado_fiscal: 'Estado Fiscal',
    deficiencia: 'Deficiência',
    conjugue_deficiente: 'Cônjuge Deficiente',
    num_dependentes: 'Nº Dependentes',
    num_dependentes_deficientes: 'Nº Dependentes Deficientes',
    pensionista: 'Pensionista'
};

export const CONTRACT_FIELDS = {
    data_admissao: 'Data Admissão',
    tipo_contrato: 'Tipo de Contrato',
    profissao: 'Profissão',
    categoria: 'Categoria',
    regime: 'Regime',
    horas_semana: 'Horas/Semana',
    salario_base: { label: 'Salário Base', suffix: ' €', highlight: true },
    subsidio_alimentacao: { label: 'Sub. Alimentação', suffix: ' €' },
    nib: { label: 'NIB', editable: true },
    ordenado_liquido: { label: 'Ordenado Líquido', suffix: ' €', highlight: true },
    validacao_empresa: 'Validação Empresa'
};

export const FICHA_SECTIONS = {
    'dados-pessoais': PERSONAL_FIELDS,
    'dados-familiares': FAMILY_FIELDS,
    'dados-fiscais': FISCAL_FIELDS,
    'dados-contratuais': CONTRACT_FIELDS
};
