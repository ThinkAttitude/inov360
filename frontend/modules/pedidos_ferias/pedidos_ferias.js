import {getCollabLeaveSummary, getCollabLeaveRequests} from '../../app/api.js';
import {createOverlays} from '../../app/overlays.js';
import { typeLabel } from './pedidos_ferias_fields.js';
import './styles.css';
import './request_form.css';
import {openNewRequestModal} from "./request_modal.js";


function fmtDate(iso) {
    if (!iso) return '-';
    const [y, m, d] = iso.split('T')[0].split('-');
    return `${d}/${m}/${y}`;
}

const STATUS_LABELS = {
    pendente:  'PENDENTE',
    aprovado:  'APROVADO',
    rejeitado: 'REJEITADO',
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

let allItems = [];
let currentFilter = 'all';

function buildCard(item) {
    const tpl = document.getElementById('ferias-pedido-template');
    if (!tpl?.content) return null;

    const node = tpl.content.firstElementChild.cloneNode(true);

    const statusLabel = STATUS_LABELS[item.estado] || item.estado.toUpperCase();

    node.dataset.status = item.estado;
    node.dataset.id = String(item.id);

    node.querySelector('.ferias-card-type-icon').innerHTML = typeIcon(item.tipo);
    node.querySelector('.ferias-card-title').textContent = typeLabel(item.tipo);
    node.querySelector('.ferias-card-created').textContent = fmtDate(item.criado_em);

    const statusEl = node.querySelector('.ferias-card-status');
    if (statusEl) {
        statusEl.querySelector('.ferias-card-status-text').textContent = statusLabel;
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

export function mountPedidosFerias() {
    const ol = createOverlays();

    bindTabs();

    document.getElementById('ferias-novo-pedido-btn')
        ?.addEventListener('click', () => openNewRequestModal(ol.openModal));

    loadData();

    return () => ol.destroy?.();
}
