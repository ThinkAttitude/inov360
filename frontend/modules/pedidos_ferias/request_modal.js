import { submitLeaveRequest } from '../../app/api.js';
import { TYPE_LABELS, TYPES_REQUIRING_PROOF } from './pedidos_ferias_fields.js';

function esc(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}

function renderModalContent(m, hoje) {
    m.body.innerHTML = `
        <div class="ferias-form">
            <div class="ferias-form-row">
                <label class="field-label" for="ferias-form-tipo">Tipo de ausência</label>
                <select class="field-input" id="ferias-form-tipo">
                    <option value="">Selecione...</option>
                    ${Object.entries(TYPE_LABELS).map(([v, l]) =>
                        `<option value="${esc(v)}">${esc(l)}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="ferias-form-row ferias-form-dates">
                <div>
                    <label class="field-label" for="ferias-form-inicio">Data de início</label>
                    <input class="field-input" type="date" id="ferias-form-inicio" min="${hoje}">
                </div>
                <div>
                    <label class="field-label" for="ferias-form-fim">Data de fim</label>
                    <input class="field-input" type="date" id="ferias-form-fim" min="${hoje}">
                </div>
            </div>
            <div class="ferias-form-row">
                <label class="field-label" for="ferias-form-justificacao">Justificação</label>
                <textarea class="field-input ferias-form-textarea" id="ferias-form-justificacao"
                    rows="3" placeholder="Descreva o motivo do pedido..."></textarea>
            </div>
            <div class="ferias-form-row" id="ferias-form-ficheiro-row" style="display:none">
                <label class="field-label" for="ferias-form-ficheiro">
                    Comprovativo <span class="ferias-form-required">*</span>
                    <span class="ferias-form-hint">(PDF, JPG ou PNG, máx. 2 MB)</span>
                </label>
                <input class="field-input" type="file" id="ferias-form-ficheiro"
                    accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <p class="ferias-form-error" id="ferias-form-error" style="display:none"></p>
        </div>
    `;

    m.footer.innerHTML = `
        <button type="button" class="btn-secondary ferias-form-cancel">Cancelar</button>
        <button type="button" class="btn-primary ferias-form-submit">Submeter pedido</button>
    `;
}

function validateForm(data) {
    const { tipo, data_inicio, data_fim, justificacao, ficheiro } = data;

    if (!tipo)         return 'Selecione o tipo de ausência.';
    if (!data_inicio)  return 'Indique a data de início.';
    if (!data_fim)     return 'Indique a data de fim.';
    if (data_fim < data_inicio) return 'A data de fim deve ser posterior à de início.';
    if (!justificacao) return 'A justificação é obrigatória.';

    if (TYPES_REQUIRING_PROOF.has(tipo) && !ficheiro) {
        return 'É necessário anexar um comprovativo para este tipo de ausência.';
    }

    return null; // valid
}

async function submitForm(data) {
    const fd = new FormData();
    fd.append('tipo', data.tipo);
    fd.append('data_inicio', data.data_inicio);
    fd.append('data_fim', data.data_fim);
    fd.append('justificacao', data.justificacao);
    if (data.ficheiro) fd.append('ficheiro', data.ficheiro);

    const res = await submitLeaveRequest(fd);
    if (!res?.ok) throw new Error(res?.code || 'Erro ao submeter pedido');
    return res;
}

function setupModalEvents(m, hoje, onSuccess) {
    const el = (id) => m.body.querySelector(`#${id}`);

    const tipoSel   = el('ferias-form-tipo');
    const inicioIn  = el('ferias-form-inicio');
    const fimIn     = el('ferias-form-fim');
    const justIn    = el('ferias-form-justificacao');
    const fileRow   = el('ferias-form-ficheiro-row');
    const fileIn    = el('ferias-form-ficheiro');
    const errorEl   = el('ferias-form-error');
    const submitBtn = m.footer.querySelector('.ferias-form-submit');
    const cancelBtn = m.footer.querySelector('.ferias-form-cancel');

    const showError = (msg) => {
        if (!errorEl) return;
        errorEl.textContent = msg;
        errorEl.style.display = msg ? '' : 'none';
    };

    tipoSel?.addEventListener('change', () => {
        const needsDoc = TYPES_REQUIRING_PROOF.has(tipoSel.value);
        if (fileRow) fileRow.style.display = needsDoc ? '' : 'none';
        showError('');
    });

    inicioIn?.addEventListener('change', () => {
        if (fimIn && inicioIn.value && fimIn.value < inicioIn.value) {
            fimIn.value = inicioIn.value;
        }
        if (fimIn) fimIn.min = inicioIn.value || hoje;
    });

    cancelBtn?.addEventListener('click', () => m.close());

    submitBtn?.addEventListener('click', async () => {
        showError('');

        const formData = {
            tipo: tipoSel?.value || '',
            data_inicio: inicioIn?.value || '',
            data_fim: fimIn?.value || '',
            justificacao: justIn?.value.trim() || '',
            ficheiro: fileIn?.files?.[0] ?? null
        };

        const errorMsg = validateForm(formData);
        if (errorMsg) return showError(errorMsg);

        submitBtn.disabled = true;
        submitBtn.textContent = 'A submeter...';

        try {
            await submitForm(formData);
            m.close();
            if (onSuccess) await onSuccess();
        } catch (err) {
            showError(err?.message || 'Não foi possível submeter o pedido. Tente novamente.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submeter pedido';
        }
    });
}

export function openNewRequestModal(openModal, onSuccess) {
    const m = openModal({ title: 'Novo pedido de férias/ausência' });
    const hoje = new Date().toISOString().split('T')[0];

    renderModalContent(m, hoje);
    setupModalEvents(m, hoje, onSuccess);
}