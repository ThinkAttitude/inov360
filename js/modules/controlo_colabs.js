import {createCollaborator, getAllCollaborators, getSubsByUser, updateHierarchy, updatePermissions} from '../api.js';
import {PERMISSIONS} from "../dashboard.js";

export const PERM_LABELS = {
    [PERMISSIONS.CONTROLO_COLABS]: 'Controlo de Colaboradores',
    [PERMISSIONS.MARCACAO_DIRETA]: 'Marcação Direta de Férias/Ausências',
    [PERMISSIONS.MAPAS_HORARIOS]: 'Mapas de Horários',
    [PERMISSIONS.PEDIDOS_HORAS_EXTRA]: 'Pedidos de Horas Extra',
    [PERMISSIONS.APROVACAO_HORAS_EXTRA]: 'Aprovação de Horas Extra',
    [PERMISSIONS.GESTAO_FICHAS]: 'Gestão de Fichas',
    [PERMISSIONS.FINANCEIRA]: 'Financeira',
};

let collabs = [];

async function fillCollaborators() {
    const select = document.getElementById('controlo-user-select');
    if (!select) return;

    try {
        const res = await getAllCollaborators();
        if (!res || res.ok !== true || !Array.isArray(res.items)) return;

        collabs = res.items;

        res.items.forEach(item => {
            if (!item || item.id == null || !item.nome) return;
            const opt = document.createElement('option');
            opt.value = String(item.id);
            opt.textContent = item.email ? `${item.nome} (${item.email})` : item.nome;
            select.appendChild(opt);
        });
    } catch (e) {
        console.error('Erro ao carregar colaboradores:', e);
    }
}

function togglePermChips(ids = [], enabled = true) {
    const list = document.getElementById('perm-list');
    if (!list) return;

    let active = new Set(ids);
    list.querySelectorAll('.perm-item').forEach(li => {
        const permId = parseInt(li.dataset.perm, 10);
        const chip = li.querySelector('.chip-status');
        if (!chip || Number.isNaN(permId)) return;

        chip.disabled = !enabled;

        const isOn = enabled && active.has(permId);
        chip.classList.toggle('chip-on', isOn);
        chip.classList.toggle('chip-off', !isOn);
        chip.setAttribute('aria-pressed', String(isOn));

        if (!chip.dataset.bound) {
            chip.addEventListener('click', async () => {
                if (chip.disabled) return;

                const select = document.getElementById('controlo-user-select');
                if (!select || !select.value) return;

                const userId = parseInt(select.value, 10);
                if (!userId) return;

                const collab = collabs.find(c => c.id === userId);
                if (!collab) return;

                const wasOn = chip.classList.contains('chip-on');
                const newState = !wasOn;

                const current = Array.isArray(collab.permissoes)
                    ? collab.permissoes.slice()
                    : [];

                let nextPerms;
                if (newState) {
                    nextPerms = current.includes(permId)
                        ? current
                        : [...current, permId];
                } else {
                    nextPerms = current.filter(p => p !== permId);
                }

                chip.classList.toggle('chip-on', newState);
                chip.classList.toggle('chip-off', !newState);
                chip.setAttribute('aria-pressed', String(newState));

                try {
                    const resp = await updatePermissions(userId, nextPerms);
                    if (!resp || resp.ok !== true) {
                        throw new Error('Resposta inválida do servidor');
                    }
                    collab.permissoes = Array.isArray(resp.permissions)
                        ? resp.permissions
                        : nextPerms;
                } catch (err) {
                    console.error('Falha ao atualizar permissões:', err);
                    chip.classList.toggle('chip-on', wasOn);
                    chip.classList.toggle('chip-off', !wasOn);
                    chip.setAttribute('aria-pressed', String(wasOn));
                }
            });
            chip.dataset.bound = '1';
        }
    });
}

/**
 * Responsible for rendering the permissions UI based on selected collaborator
 * @param {number[]} ids - Array of permission IDs that the selected collaborator has
 * @param {boolean} hasUser - Whether a collaborator is currently selected
 */
function renderPerms(ids = [], hasUser = false) {
    const permEmpty = document.getElementById('perm-empty');

    if (permEmpty) {
        if (!hasUser) {
            permEmpty.style.display = '';
            permEmpty.textContent = 'Nenhum colaborador selecionado.';
        } else {
            permEmpty.style.display = 'none';
        }
    }

    togglePermChips(ids, hasUser);
}

async function renderSubs(userId = null) {
    const listEl = document.getElementById('hier-subs-list');
    const emptyEl = document.getElementById('hier-empty');
    if (!listEl || !emptyEl) return;

    listEl.innerHTML = '';

    if (!userId) {
        emptyEl.style.display = '';
        renderHierarchyPreview();
        return;
    }

    try {
        const res = await getSubsByUser(userId);
        if (!res || res.ok !== true || res.items.length === 0) {
            emptyEl.style.display = '';
            renderHierarchyPreview();
            return;
        }

        emptyEl.style.display = 'none';

        res.items.forEach(item => {
            const c = item.colaborador;
            if (!c) return;

            const avatarUrl =
                'https://ui-avatars.com/api/?' +
                `name=${encodeURIComponent(c.nome || 'Colaborador')}` +
                '&background=0F172A&color=FFFFFF&size=64&bold=true';

            const card = document.createElement('div');
            card.className = 'hierarchy-item';
            card.innerHTML = `
                    <div class="avatar-wrapper">
                        <div class="avatar-small">
                            <img src="${avatarUrl}" alt="${c.nome}">
                        </div>
                        <div class="hier-remove-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="6"></circle>
                                <line x1="9" y1="12" x2="15" y2="12"></line>
                            </svg>
                        </div>
                    </div>
                    <div class="hierarchy-text">
                        <div class="h-name">${c.nome}</div>
                        <div class="h-sub">${c.email || 'Colaborador'}</div>
                    </div>
                    <div class="hier-tooltip">Remover</div>
                `;

            card.addEventListener('click', (ev) => {
                ev.preventDefault();
                ev.stopPropagation();

                if (!confirm('Remover este colaborador desta hierarquia?')) return;

                updateHierarchy({
                    user_id: c.id,
                    responsaveis: []
                })
                    .then(() => renderSubs(userId))
                    .catch(err => {
                        console.error('Erro ao remover colaborador da hierarquia:', err);
                    });
            });
            listEl.appendChild(card);
        });

        renderHierarchyPreview();
    } catch (e) {
        console.error('Erro ao carregar subs:', e);
        emptyEl.style.display = '';
        renderHierarchyPreview();
    }
}

/**
 * Mirror the main hierarchy diagram into the preview viewport.
 * Clones existing rows from the modal DOM and scales them down.
 * No listeners are cloned; preview is read-only.
 */
function renderHierarchyPreview() {
    const previewBtn    = document.getElementById('hier-preview');
    const previewMeta   = document.getElementById('hier-preview-meta');
    const previewCounts = document.getElementById('hier-preview-counts');
    const previewRoot   = document.getElementById('hier-preview-diagram');
    if (!previewRoot || !previewBtn) return;

    const select = document.getElementById('controlo-user-select');
    const hasUser = !!(select && select.value);

    if (!hasUser) {
        previewBtn.disabled = true;
        previewRoot.innerHTML = '';
        if (previewMeta) {
            previewMeta.textContent = 'Selecione um colaborador para ver o mapa.';
        }
        if (previewCounts) {
            previewCounts.textContent = '0 responsáveis · 0 subordinados';
        }
        return;
    }

    previewBtn.disabled = false;

    // modal diagram as source of truth
    const diagramInner = document.getElementById('hier-diagram-inner');
    if (!diagramInner) return;

    const topRow    = diagramInner.querySelector('.hier-diagram-row--top');
    const centerRow = diagramInner.querySelector('.hier-diagram-row--center');
    const bottomRow = diagramInner.querySelector('.hier-diagram-row--bottom');

    if (!centerRow) return;

    const superList = document.getElementById('hier-super-list');
    const subsList  = document.getElementById('hier-subs-list');
    const superCount = superList ? superList.children.length : 0;
    const subsCount  = subsList ? subsList.children.length : 0;

    if (previewCounts) {
        previewCounts.textContent = `${superCount} responsáveis · ${subsCount} subordinados`;
    }
    if (previewMeta) {
        previewMeta.textContent = 'Arraste para navegar pelo mapa. Clique para ver em detalhe.';
    }

    // build in fragment to minimize reflows
    const frag  = document.createDocumentFragment();
    const inner = document.createElement('div');
    inner.className = 'hier-preview-diagram-inner';

    const rows = [topRow, centerRow, bottomRow];

    rows.forEach((row) => {
        if (!row) return;

        // clone entire row because it contains all needed structure/styles
        const clone = row.cloneNode(true);

        clone.removeAttribute('id');
        clone.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));

        // limit of MAX_NODES per row in preview
        const nodesContainer = clone.querySelector('.hier-diagram-row-nodes');
        if (nodesContainer) {
            const children = Array.from(nodesContainer.children);
            const MAX_NODES = 10;

            if (children.length > MAX_NODES) {
                children.slice(MAX_NODES).forEach(el => nodesContainer.removeChild(el));

                const extra = document.createElement('div');
                extra.className = 'hierarchy-item hierarchy-item--more';
                extra.innerHTML = `
                    <div class="hierarchy-text">
                        <div class="h-name">+${children.length - MAX_NODES}</div>
                        <div class="h-sub">mais colaboradores</div>
                    </div>
                `;
                nodesContainer.appendChild(extra);
            }
        }

        inner.appendChild(clone);
    });

    frag.appendChild(inner);

    // applying atomically to improve rendering performance
    previewRoot.innerHTML = '';
    previewRoot.appendChild(frag);

    // drag position reset
    previewRoot.style.transform = 'translate(0px, 0px)';
}

function bindSelect() {
    const select = document.getElementById('controlo-user-select');
    if (!select) return;

    const previewBtn = document.getElementById('hier-preview');
    const previewMeta = document.getElementById('hier-preview-meta');
    const currentNameEl = document.getElementById('hier-node-current-name');
    const currentEmailEl = document.getElementById('hier-node-current-email');
    const modalSubtitle = document.getElementById('hierarchy-modal-subtitle');

    select.addEventListener('change', () => {
        const val = select.value;

        if (!val) {
            renderPerms([]);
            renderSubs(null);
            renderHierarchyPreview();

            if (previewBtn) {
                previewBtn.disabled = true;
            }
            if (previewMeta) {
                previewMeta.textContent = 'Selecione um colaborador para ver o mapa.';
            }
            if (currentNameEl) {
                currentNameEl.textContent = 'Nenhum colaborador selecionado';
            }
            if (currentEmailEl) {
                currentEmailEl.textContent = '';
            }
            if (modalSubtitle) {
                modalSubtitle.textContent = 'Visualize e ajuste quem responde a quem para este colaborador.';
            }
            return;
        }

        const id = parseInt(val, 10);
        const c = collabs.find(u => u.id === id);

        renderPerms(c?.permissoes || [], true);
        renderSubs(id);

        if (previewBtn) {
            previewBtn.disabled = false;
        }
        if (previewMeta) {
            previewMeta.textContent = 'Clique para abrir o mapa completo.';
        }
        if (currentNameEl) {
            currentNameEl.textContent = c?.nome || 'Colaborador';
        }
        if (currentEmailEl) {
            currentEmailEl.textContent = c?.email || '';
        }
        if (modalSubtitle && c?.nome) {
            modalSubtitle.textContent = `Mapa de hierarquia de ${c.nome}.`;
        }
    });
}


function renderCreateModal() {
    const btnOpen   = document.getElementById('controlo-add');
    const overlay   = document.getElementById('controlo-create-modal');
    const btnClose  = document.getElementById('controlo-create-close');
    const btnCancel = document.getElementById('controlo-create-cancel');
    const form      = document.getElementById('controlo-create-form');

    if (!btnOpen || !overlay) return;

    const closeModal = () => {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    };

    const openModal = () => {
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    btnOpen.addEventListener('click', () => {
        openModal();
    });

    if (btnClose) {
        btnClose.addEventListener('click', () => {
            closeModal();
        });
    }

    if (btnCancel) {
        btnCancel.addEventListener('click', () => {
            closeModal();
        });
    }

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeModal();
        }
    });

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData(form);

            createCollaborator(fd)
                .then(async (res) => {
                    if (res && res.ok === true) {
                        closeModal();
                        await fillCollaborators();
                    } else {
                        console.error('Failed to create collaborator:', res?.error);
                    }
                })
                .catch((err) => {
                    console.error('Failed to create collaborator:', err);
                });
        });
    }
}

function renderHierarchyModal() {
    const previewBtn = document.getElementById('hier-preview');
    const modal = document.getElementById('hierarchy-modal');
    const closeBtn = document.getElementById('hierarchy-modal-close');
    const closeFooter = document.getElementById('hierarchy-modal-close-footer');

    if (!previewBtn || !modal) return;

    const openModal = () => {
        if (previewBtn.disabled) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    previewBtn.addEventListener('click', openModal);

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    if (closeFooter) {
        closeFooter.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    enableDiagramDrag();
}

function enableDiagramDrag() {
    // modal draggable
    const modalContainer = document.querySelector('.hier-diagram-container');
    const modalInner = document.getElementById('hier-diagram-inner');

    if (modalContainer && modalInner) {
        let isDown = false;
        let startX = 0;
        let startY = 0;
        let scrollLeft = 0;
        let scrollTop = 0;

        modalInner.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return; // only left button
            isDown = true;
            startX = e.clientX;
            startY = e.clientY;
            scrollLeft = modalContainer.scrollLeft;
            scrollTop = modalContainer.scrollTop;
            e.preventDefault();
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            modalContainer.scrollLeft = scrollLeft - dx;
            modalContainer.scrollTop = scrollTop - dy;
        });

        window.addEventListener('mouseup', () => {
            isDown = false;
        });
    }

    // preview draggable
    const previewCanvas  = document.querySelector('.hier-preview-canvas');
    const previewDiagram = document.getElementById('hier-preview-diagram');
    const previewBtn     = document.getElementById('hier-preview');

    if (previewCanvas && previewDiagram) {
        let isDown = false;
        let startX = 0;
        let startY = 0;
        let offsetX = 0;
        let offsetY = 0;
        let hasDragged = false;

        const applyTransform = () => {
            previewDiagram.style.transform = `translate(${offsetX}px, ${offsetY}px)`;
        };

        previewCanvas.addEventListener('mousedown', (e) => {
            if (previewBtn && previewBtn.disabled) return;
            if (e.button !== 0) return; // só botão esquerdo

            isDown = true;
            hasDragged = false;
            startX = e.clientX;
            startY = e.clientY;
            previewCanvas.classList.add('is-dragging');

            // não queremos que mousedown dentro da canvas suba para outros handlers
            e.preventDefault();
            e.stopPropagation();
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDown) return;

            const dx = e.clientX - startX;
            const dy = e.clientY - startY;

            // small threshold to distinguish drag from jitter
            if (!hasDragged && (Math.abs(dx) > 3 || Math.abs(dy) > 3)) {
                hasDragged = true;
            }

            offsetX += dx;
            offsetY += dy;
            applyTransform();

            startX = e.clientX;
            startY = e.clientY;
        });

        window.addEventListener('mouseup', () => {
            if (!isDown) return;
            isDown = false;
            previewCanvas.classList.remove('is-dragging');
        });

        previewCanvas.addEventListener('click', (e) => {
            if (hasDragged) {
                e.preventDefault();
                e.stopPropagation();
                hasDragged = false;
            }
        });
    }
}


export async function mountClbMngmt() {
    await fillCollaborators();
    renderPerms([], false);
    renderSubs();
    bindSelect();
    renderCreateModal();
    renderHierarchyModal();
}

// TODO: Remover colaboradores (clicar no cartao e remover)