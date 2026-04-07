import {getMyLeaveRequests, getMyLeaveSummary, submitLeaveRequest} from '../../app/api.js';
import {createOverlays} from '../../app/overlays.js';
import './styles.css';

const ERROR_MESSAGES = {
    NO_RESPONSAVEIS: 'Não tem responsáveis (superiores) atribuídos. Contacte o administrador.',
    MISSING_FIELDS: 'Preencha todos os campos obrigatórios.',
    INVALID_DATE: 'Formato de data inválido.',
    RANGE_ERROR: 'A data de início não pode ser posterior à data de fim.',
    DOC_REQUIRED: 'Este tipo de pedido requer comprovativo.',
    BAD_FILETYPE: 'Tipo de ficheiro inválido. Use PDF, JPG ou PNG.',
    FILE_TOO_LARGE: 'Ficheiro demasiado grande (máx. 5MB).',
};

function friendlyError(rawMsg) {
    if (!rawMsg) return 'Ocorreu um erro.';
    for (const [code, msg] of Object.entries(ERROR_MESSAGES)) {
        if (rawMsg.includes(code)) return msg;
    }
    return rawMsg;
}

const TIPO_LABELS = {
    licenca_paternidade: 'Licença de Paternidade',
    licenca_maternidade: 'Licença de Maternidade',
    baixa_medica: 'Baixa Médica',
    baixa_seguro: 'Baixa por Seguro',
    casamento: 'Casamento',
    consulta_medica: 'Consulta Médica',
    ferias: 'Férias',
    pessoal: 'Pessoal',
};

const TIPOS_COM_COMPROVATIVO = [
    'licenca_paternidade', 'licenca_maternidade', 'baixa_medica',
    'baixa_seguro', 'casamento', 'consulta_medica',
];

const STATUS_MAP = {
    pendente: {key: 'pending', label: 'PENDENTE'},
    aprovado: {key: 'approved', label: 'APROVADO'},
    rejeitado: {key: 'rejected', label: 'REJEITADO'},
};

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function formatDate(iso) {
    if (!iso) return '-';
    const [y, m, d] = iso.split('-');
    return `${d}/${m}/${y}`;
}

let allRequests = [];
let activeFilter = 'all';

function updateSummary(summary) {
    const el = (id, val) => {
        const e = document.getElementById(id);
        if (e) e.textContent = String(val);
    };
    el('ferias-total-value', summary.total);
    el('ferias-pendentes-value', summary.pendentes);
    el('ferias-aprovados-value', summary.aprovados);
    el('ferias-rejeitados-value', summary.rejeitados);

    el('ferias-count-all', summary.total);
    el('ferias-count-pending', summary.pendentes);
    el('ferias-count-approved', summary.aprovados);
    el('ferias-count-rejected', summary.rejeitados);
}

function renderCards(items) {
    const list = document.getElementById('ferias-pedidos-list');
    const empty = document.getElementById('ferias-empty-state');
    const template = document.getElementById('ferias-pedido-template');
    if (!list) return;

    list.innerHTML = '';

    if (!items || items.length === 0) {
        if (empty) empty.style.display = 'flex';
        return;
    }
    if (empty) empty.style.display = 'none';

    items.forEach(item => {
        const st = STATUS_MAP[item.estado] || STATUS_MAP.pendente;
        const clone = template.content.cloneNode(true);
        const card = clone.querySelector('.ferias-card');

        card.dataset.status = st.key;
        card.dataset.id = String(item.id);

        card.querySelector('.ferias-card-title').textContent = TIPO_LABELS[item.tipo] || item.tipo;
        card.querySelector('.ferias-card-created').textContent = formatDate(item.criado_em?.split(' ')[0]);

        const statusEl = card.querySelector('.ferias-card-status');
        statusEl.classList.add(`ferias-status-${st.key}`);
        card.querySelector('.ferias-card-status-text').textContent = st.label;

        card.querySelector('.ferias-card-start').textContent = formatDate(item.inicio);
        card.querySelector('.ferias-card-end').textContent = formatDate(item.fim);
        card.querySelector('.ferias-card-justification').textContent = item.justificacao || '-';

        const deciderWrap = card.querySelector('.ferias-card-footer');
        if (item.decidido_por) {
            card.querySelector('.ferias-card-decider-name').textContent = item.decidido_por_nome || `#${item.decidido_por}`;
        } else {
            deciderWrap.style.display = 'none';
        }

        list.appendChild(clone);
    });
}

function applyFilter(filter) {
    activeFilter = filter;
    const filterMap = {all: null, pending: 'pendente', approved: 'aprovado', rejected: 'rejeitado'};
    const estado = filterMap[filter];
    const filtered = estado ? allRequests.filter(r => r.estado === estado) : allRequests;
    renderCards(filtered);
}

function bindTabs() {
    const tabs = document.querySelectorAll('.ferias-tab[data-filter]');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => {
                t.classList.remove('is-active');
                t.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');
            applyFilter(tab.dataset.filter);
        });
    });
}

async function loadData() {
    const list = document.getElementById('ferias-pedidos-list');
    const empty = document.getElementById('ferias-empty-state');
    if (list) list.innerHTML = '';
    if (empty) {
        empty.style.display = 'flex';
        empty.querySelector('.ferias-empty-text').textContent = 'A carregar...';
    }

    try {
        const [summaryRes, requestsRes] = await Promise.all([
            getMyLeaveSummary(),
            getMyLeaveRequests(),
        ]);

        if (summaryRes?.ok) updateSummary(summaryRes.summary);
        if (requestsRes?.ok) {
            allRequests = requestsRes.items || [];
            applyFilter(activeFilter);
            if (empty && allRequests.length === 0) {
                empty.querySelector('.ferias-empty-text').textContent = 'Sem pedidos registados.';
            }
        }
    } catch (e) {
        allRequests = [];
        updateSummary({total: 0, pendentes: 0, aprovados: 0, rejeitados: 0});
        if (empty) {
            empty.style.display = 'flex';
            empty.querySelector('.ferias-empty-text').textContent = friendlyError(e.message);
        }
    }
}

function openNewRequestModal(openModal) {
    const m = openModal({title: 'Novo Pedido de Férias/Ausência'});

    m.body.innerHTML = `
        <form id="ferias-new-form" class="ferias-modal-form">
            <div class="ferias-form-grid">
                <div class="ferias-form-field">
                    <label for="ferias-tipo">Tipo <span class="ferias-required">*</span></label>
                    <select id="ferias-tipo" required>
                        <option value="">Selecione o tipo...</option>
                        ${Object.entries(TIPO_LABELS).map(([k, v]) => `<option value="${k}">${esc(v)}</option>`).join('')}
                    </select>
                </div>

                <div class="ferias-form-field">
                    <label for="ferias-inicio">Data Início <span class="ferias-required">*</span></label>
                    <input type="date" id="ferias-inicio" required />
                </div>

                <div class="ferias-form-field">
                    <label for="ferias-fim">Data Fim <span class="ferias-required">*</span></label>
                    <input type="date" id="ferias-fim" required />
                </div>

                <div class="ferias-form-field ferias-form-full">
                    <label for="ferias-justificacao">Justificação <span class="ferias-required">*</span></label>
                    <textarea id="ferias-justificacao" rows="3" placeholder="Motivo do pedido..." required></textarea>
                </div>

                <div class="ferias-form-field ferias-form-full" id="ferias-ficheiro-wrap" style="display:none;">
                    <label for="ferias-ficheiro">Comprovativo <span class="ferias-required">*</span></label>
                    <input type="file" id="ferias-ficheiro" accept=".pdf,.jpg,.jpeg,.png" />
                    <span class="ferias-form-hint">PDF, JPG ou PNG (máx. 5MB)</span>
                </div>
            </div>
            <div id="ferias-form-feedback"></div>
        </form>
    `;

    m.footer.innerHTML = `
        <button type="button" class="btn-secondary ferias-modal-cancel">Cancelar</button>
        <button type="button" class="btn-primary ferias-modal-submit">Submeter Pedido</button>
    `;

    const tipoSelect = m.body.querySelector('#ferias-tipo');
    const ficheiroWrap = m.body.querySelector('#ferias-ficheiro-wrap');
    const inicioInput = m.body.querySelector('#ferias-inicio');
    const fimInput = m.body.querySelector('#ferias-fim');

    tipoSelect.addEventListener('change', () => {
        const needsFile = TIPOS_COM_COMPROVATIVO.includes(tipoSelect.value);
        ficheiroWrap.style.display = needsFile ? '' : 'none';
        const fileInput = m.body.querySelector('#ferias-ficheiro');
        if (fileInput) fileInput.required = needsFile;
    });

    inicioInput.addEventListener('change', () => {
        if (inicioInput.value) fimInput.setAttribute('min', inicioInput.value);
    });

    m.footer.querySelector('.ferias-modal-cancel')?.addEventListener('click', m.close);

    m.footer.querySelector('.ferias-modal-submit')?.addEventListener('click', async () => {
        const feedback = m.body.querySelector('#ferias-form-feedback');
        const tipo = tipoSelect.value;
        const inicio = inicioInput.value;
        const fim = fimInput.value;
        const justificacao = m.body.querySelector('#ferias-justificacao')?.value?.trim();
        const fileInput = m.body.querySelector('#ferias-ficheiro');
        const file = fileInput?.files?.[0];

        if (!tipo || !inicio || !fim || !justificacao) {
            feedback.innerHTML = '<div class="feedback-message error">Preencha todos os campos obrigatórios.</div>';
            return;
        }

        if (inicio > fim) {
            feedback.innerHTML = '<div class="feedback-message error">A data de início não pode ser posterior à data de fim.</div>';
            return;
        }

        if (TIPOS_COM_COMPROVATIVO.includes(tipo) && !file) {
            feedback.innerHTML = '<div class="feedback-message error">Este tipo de pedido requer comprovativo.</div>';
            return;
        }

        if (file) {
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                feedback.innerHTML = '<div class="feedback-message error">Tipo de ficheiro inválido. Use PDF, JPG ou PNG.</div>';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                feedback.innerHTML = '<div class="feedback-message error">Ficheiro demasiado grande (máx. 5MB).</div>';
                return;
            }
        }

        const submitBtn = m.footer.querySelector('.ferias-modal-submit');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'A submeter...';
        }
        feedback.innerHTML = '';

        const fd = new FormData();
        fd.append('tipo', tipo);
        fd.append('data_inicio', inicio);
        fd.append('data_fim', fim);
        fd.append('justificacao', justificacao);
        if (file) fd.append('ficheiro', file);

        try {
            const res = await submitLeaveRequest(fd);
            if (res?.ok) {
                m.close();
                loadData();
            } else {
                feedback.innerHTML = `<div class="feedback-message error">${esc(friendlyError(res?.code))}</div>`;
            }
        } catch (e) {
            feedback.innerHTML = `<div class="feedback-message error">${esc(friendlyError(e.message))}</div>`;
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submeter Pedido';
            }
        }
    });
}

export function mountPedidosFerias() {
    const ol = createOverlays();

    bindTabs();
    loadData();

    document.getElementById('ferias-novo-pedido-btn')?.addEventListener('click', () => {
        openNewRequestModal(ol.openModal);
    });

    return () => ol.closeActive();
}
