export const TYPE_LABELS = {
    ferias: 'Férias',
    baixa_medica: 'Baixa médica',
    baixa_seguro: 'Baixa por seguro',
    licenca_paternidade: 'Licença de paternidade',
    licenca_maternidade: 'Licença de maternidade',
    casamento: 'Casamento',
    consulta_medica: 'Consulta médica',
    pessoal: 'Motivo pessoal',
};

export function typeLabel(t) {
    return TYPE_LABELS[t] || (t ? t.replace(/_/g, ' ') : '-');
}

export function typeChipClass(t) {
    if (t === 'ferias') return 'aprov-chip--ferias';
    if (t === 'baixa_medica' || t === 'baixa_seguro') return 'aprov-chip--baixa';
    return 'aprov-chip--licenca';
}
