import {getCollabLeaveSummary, getCollabLeaveRequests, submitLeaveRequest} from '../../app/api.js';
import {createOverlays} from '../../app/overlays.js';
import './styles.css';

// ─── Tipos ───────────────────────────────────────────────────────────────────

const TIPO_LABEL = {
    ferias:               'Férias',
    baixa_medica:         'Baixa médica',
    baixa_seguro:         'Baixa por seguro',
    licenca_paternidade:  'Licença de paternidade',
    licenca_maternidade:  'Licença de maternidade',
    casamento:            'Casamento',
    consulta_medica:      'Consulta médica',
    pessoal:              'Motivo pessoal',
};

const TIPOS_COM_COMPROVATIVO = new Set([
    'licenca_paternidade', 'licenca_maternidade',
    'baixa_medica', 'baixa_seguro',
    'casamento', 'consulta_medica',
]);

function tipoLabel(t) {
    return TIPO_LABEL[t] || (t ? t.replace(/_/g, ' ') : '-');
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function esc(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}

function fmtDate(iso) {
    if (!iso) return '-';
    const [y, m, d] = iso.split('T')[0].split('-');
    return `${d}/${m}/${y}`;
}

const STATUS_META = {
    pendente:   { label: 'PENDENTE',   cls: 'ferias-status-pending'  },
    aprovado:   { label: 'APROVADO',   cls: 'ferias-status-approved' },
    rejeitado:  { label: 'REJEITADO',  cls: 'ferias-status-rejected' },
};

const TYPE_ICON = {
    ferias: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M3 10h18"></path></svg>`,
    baixa:  `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>`,
    other:  `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>`,
};

function typeIcon(tipo) {
    if (tipo === 'ferias') return TYPE_ICON.ferias;
    if (tipo === 'baixa_medica' || tipo === 'baixa_seguro') return TYPE_ICON.baixa;
    return TYPE_ICON.other;
}

// ─── State ───────────────────────────────────────────────────────────────────

let allItems = [];
let currentFilter = 'all';

// ─── Render ──────────────────────────────────────────────────────────────────

function buildCard(item) {
    const tpl = document.getElementById('ferias-pedido-template');
    if (!tpl?.content) return null;

    const node = tpl.content.firstElementChild.cloneNode(true);

    const meta = STATUS_META[item.estado] || { label: item.estado.toUpperCase(), cls: '' };

    node.dataset.status = item.estado;
    node.dataset.id = String(item.id);

    node.querySelector('.ferias-card-type-icon').innerHTML = typeIcon(item.tipo);
    node.querySelector('.ferias-card-title').textContent = tipoLabel(item.tipo);
    node.querySelector('.ferias-card-created').textContent = fmtDate(item.criado_em);

    const statusEl = node.querySelector('.ferias-card-status');
    if (statusEl) {
        if (meta.cls) statusEl.classList.add(meta.cls);
        statusEl.querySelector('.ferias-card-status-text').textContent = meta.label;
    }

    node.querySelector('.ferias-card-start').textContent = fmtDate(item.inicio);
    node.querySelector('.ferias-card-end').textContent = fmtDate(item.fim);
    node.querySelector('.ferias-card-justification').textContent = item.justificacao || '-';

    const footer = node.querySelector('.ferias-card-footer');
    if (item.estado === 'pendente' || !item.decidido_por_nome) {
        if (footer) footer.style.display = 'none';
    } else {
        node.querySelector('.ferias-card-decider-name').textContent = item.decidido_por_nome;
    }

    // comprovativo link
    if (item.comprovativo) {
        const body = node.querySelector('.ferias-card-body');
        if (body) {
            const link = document.createElement('a');
            link.href = item.comprovativo;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.className = 'ferias-field ferias-comprovativo-link';
            link.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Ver comprovativo`;
            body.appendChild(link);
        }
    }

    return node;
}

function renderList() {
    const list = document.getElementById('ferias-pedidos-list');
    if (!list) return;

    // Remove sample + previous dynamic cards
    list.querySelectorAll('.ferias-card').forEach(c => c.remove());

    const filtered = currentFilter === 'all'
        ? allItems
        : allItems.filter(i => {
            if (currentFilter === 'pending')  return i.estado === 'pendente';
            if (currentFilter === 'approved') return i.estado === 'aprovado';
            if (currentFilter === 'rejected') return i.estado === 'rejeitado';
            return true;
        });

    if (filtered.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'ferias-empty ferias-empty--dynamic';
        empty.textContent = 'Sem pedidos para mostrar.';
        list.appendChild(empty);
        return;
    }

    list.querySelectorAll('.ferias-empty--dynamic').forEach(e => e.remove());

    const frag = document.createDocumentFragment();
    filtered.forEach(item => {
        const card = buildCard(item);
        if (card) frag.appendChild(card);
    });
    list.appendChild(frag);
}

function updateCounts(items) {
    const total     = items.length;
    const pending   = items.filter(i => i.estado === 'pendente').length;
    const approved  = items.filter(i => i.estado === 'aprovado').length;
    const rejected  = items.filter(i => i.estado === 'rejeitado').length;

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = String(val); };

    set('ferias-count-all',      total);
    set('ferias-count-pending',  pending);
    set('ferias-count-approved', approved);
    set('ferias-count-rejected', rejected);
}

function updateStats(summary) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = String(val ?? 0); };
    set('ferias-total-value',     summary.total);
    set('ferias-pendentes-value', summary.pendentes);
    set('ferias-aprovados-value', summary.aprovados);
    set('ferias-rejeitados-value',summary.rejeitados);
}

// ─── Load data ────────────────────────────────────────────────────────────────

async function loadData() {
    try {
        const [summaryRes, requestsRes] = await Promise.all([
            getCollabLeaveSummary(),
            getCollabLeaveRequests(),
        ]);

        if (summaryRes?.ok) updateStats(summaryRes.summary);

        if (requestsRes?.ok && Array.isArray(requestsRes.items)) {
            allItems = requestsRes.items;
            updateCounts(allItems);
            renderList();
        }
    } catch (err) {
        console.error('Erro ao carregar pedidos:', err);
    }
}

// ─── New request modal ────────────────────────────────────────────────────────

function openNewRequestModal(openModal) {
    const m = openModal({ title: 'Novo pedido de férias/ausência' });

    const hoje = new Date().toISOString().split('T')[0];

    m.setContent(`
        <div class="ferias-form">
            <div class="ferias-form-row">
                <label class="field-label" for="ferias-form-tipo">Tipo de ausência</label>
                <select class="field-input" id="ferias-form-tipo">
                    <option value="">Selecione...</option>
                    ${Object.entries(TIPO_LABEL).map(([v, l]) =>
                        `<option value="${v}">${esc(l)}</option>`
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
                    <span class="ferias-form-hint">(PDF, JPG ou PNG, máx. 5 MB)</span>
                </label>
                <input class="field-input" type="file" id="ferias-form-ficheiro"
                    accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <p class="ferias-form-error" id="ferias-form-error" style="display:none"></p>
        </div>
        <div class="ferias-form-actions">
            <button type="button" class="btn-secondary ferias-form-cancel">Cancelar</button>
            <button type="button" class="btn-primary ferias-form-submit">Submeter pedido</button>
        </div>
    `);

    const el = (id) => m.element.querySelector(`#${id}`);

    const tipoSel   = el('ferias-form-tipo');
    const inicioIn  = el('ferias-form-inicio');
    const fimIn     = el('ferias-form-fim');
    const justIn    = el('ferias-form-justificacao');
    const fileRow   = el('ferias-form-ficheiro-row');
    const fileIn    = el('ferias-form-ficheiro');
    const errorEl   = el('ferias-form-error');
    const submitBtn = m.element.querySelector('.ferias-form-submit');
    const cancelBtn = m.element.querySelector('.ferias-form-cancel');

    const showError = (msg) => {
        if (!errorEl) return;
        errorEl.textContent = msg;
        errorEl.style.display = msg ? '' : 'none';
    };

    tipoSel?.addEventListener('change', () => {
        const needsDoc = TIPOS_COM_COMPROVATIVO.has(tipoSel.value);
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

        const tipo         = tipoSel?.value || '';
        const data_inicio  = inicioIn?.value || '';
        const data_fim     = fimIn?.value || '';
        const justificacao = justIn?.value.trim() || '';
        const ficheiro     = fileIn?.files?.[0] ?? null;

        if (!tipo)         return showError('Selecione o tipo de ausência.');
        if (!data_inicio)  return showError('Indique a data de início.');
        if (!data_fim)     return showError('Indique a data de fim.');
        if (data_fim < data_inicio) return showError('A data de fim deve ser posterior à de início.');
        if (!justificacao) return showError('A justificação é obrigatória.');
        if (TIPOS_COM_COMPROVATIVO.has(tipo) && !ficheiro)
            return showError('É necessário anexar um comprovativo para este tipo de ausência.');

        const fd = new FormData();
        fd.append('tipo', tipo);
        fd.append('data_inicio', data_inicio);
        fd.append('data_fim', data_fim);
        fd.append('justificacao', justificacao);
        if (ficheiro) fd.append('ficheiro', ficheiro);

        submitBtn.disabled = true;
        submitBtn.textContent = 'A submeter...';

        try {
            const res = await submitLeaveRequest(fd);
            if (!res?.ok) throw new Error(res?.code || 'Erro ao submeter pedido');
            m.close();
            await loadData();
        } catch (err) {
            const codeMsg = {
                NO_RESPONSAVEIS: 'Não tem um responsável hierárquico atribuído. Contacte o administrador.',
                MISSING_FIELDS:  'Preencha todos os campos obrigatórios.',
                INVALID_DATE:    'Data inválida.',
                RANGE_ERROR:     'O intervalo de datas é inválido.',
                DOC_REQUIRED:    'É necessário anexar um comprovativo.',
                BAD_FILETYPE:    'Tipo de ficheiro não permitido (use PDF, JPG ou PNG).',
                FILE_TOO_LARGE:  'O ficheiro é demasiado grande (máximo 5 MB).',
            };
            showError(codeMsg[err?.message] || 'Não foi possível submeter o pedido. Tente novamente.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submeter pedido';
        }
    });
}

// ─── Tabs ─────────────────────────────────────────────────────────────────────

function bindTabs() {
    document.querySelectorAll('.ferias-tab[data-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.ferias-tab').forEach(b => {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-selected', 'true');
            currentFilter = btn.dataset.filter;
            renderList();
        });
    });
}

// ─── Mount ────────────────────────────────────────────────────────────────────

export function mountPedidosFerias() {
    const ol = createOverlays();

    bindTabs();

    document.getElementById('ferias-novo-pedido-btn')
        ?.addEventListener('click', () => openNewRequestModal(ol.openModal));

    loadData();

    return () => ol.destroy?.();
}
