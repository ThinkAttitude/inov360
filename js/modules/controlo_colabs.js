import {
    createCollaborator,
    getAllCollaborators,
    getHierarchyByUser,
    updateHierarchy,
    updatePermissions
} from '../api.js';
import { PERMISSIONS } from '../dashboard.js';

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

const hierarchyState = {
    userId: null,
    responsaveis: [],
    subs: [],
    baseRespCount: 0,
    baseSubCount: 0,
};

function isHierarchyDirty() {
    return (
        hierarchyState.responsaveis.length !== hierarchyState.baseRespCount ||
        hierarchyState.subs.length !== hierarchyState.baseSubCount
    );
}

function updateHierarchyControls() {
    const saveBtn = document.getElementById('hierarchy-modal-save');
    const closeFooter = document.getElementById('hierarchy-modal-close-footer');
    const dirty = isHierarchyDirty();

    if (saveBtn) saveBtn.disabled = !dirty;
    if (closeFooter) closeFooter.textContent = dirty ? 'Cancelar' : 'Fechar';
}

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

    const active = new Set(ids);

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

async function renderDiagram(userId = null) {
    const superList = document.getElementById('hier-super-list');
    const subsList = document.getElementById('hier-subs-list');
    const emptyEl = document.getElementById('hier-empty');
    if (!superList || !subsList || !emptyEl) return;

    superList.innerHTML = '';
    subsList.innerHTML = '';

    if (!userId) {
        hierarchyState.userId = null;
        hierarchyState.responsaveis = [];
        hierarchyState.subs = [];
        hierarchyState.baseRespCount = 0;
        hierarchyState.baseSubCount = 0;
        emptyEl.style.display = '';
        updateHierarchyControls();
        renderHierarchyPreview();
        return;
    }

    hierarchyState.userId = userId;

    const res = await getHierarchyByUser(userId).catch(err => {
        console.error('Erro ao carregar hierarquia:', err);
        emptyEl.style.display = '';
        hierarchyState.responsaveis = [];
        hierarchyState.subs = [];
        hierarchyState.baseRespCount = 0;
        hierarchyState.baseSubCount = 0;
        updateHierarchyControls();
        renderHierarchyPreview();
        return null;
    });

    if (!res || res.ok !== true) {
        emptyEl.style.display = '';
        hierarchyState.responsaveis = [];
        hierarchyState.subs = [];
        hierarchyState.baseRespCount = 0;
        hierarchyState.baseSubCount = 0;
        updateHierarchyControls();
        renderHierarchyPreview();
        return;
    }

    hierarchyState.responsaveis = Array.isArray(res.responsaveis) ? res.responsaveis : [];
    hierarchyState.subs = Array.isArray(res.subordinados) ? res.subordinados : [];
    hierarchyState.baseRespCount = hierarchyState.responsaveis.length;
    hierarchyState.baseSubCount = hierarchyState.subs.length;

    const hasAny = hierarchyState.responsaveis.length > 0 || hierarchyState.subs.length > 0;
    emptyEl.style.display = hasAny ? 'none' : '';

    function buildCard(u, type) {
        const card = document.createElement('div');
        card.className = 'hierarchy-item';

        const avatarUrl =
            'https://ui-avatars.com/api/?' +
            `name=${encodeURIComponent(u.nome || 'Colaborador')}` +
            '&background=0F172A&color=FFFFFF&size=64&bold=true';

        card.innerHTML = `
            <div class="avatar-wrapper">
                <div class="avatar-small">
                    <img src="${avatarUrl}" alt="${u.nome}">
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
                <div class="h-name">${u.nome}</div>
                <div class="h-sub">${u.email || 'Colaborador'}</div>
            </div>
            <div class="hier-tooltip">Remover</div>
        `;

        card.addEventListener('click', ev => {
            ev.preventDefault();
            ev.stopPropagation();

            if (!confirm('Remover este colaborador desta hierarquia?')) return;

            if (type === 'super') {
                hierarchyState.responsaveis = hierarchyState.responsaveis.filter(
                    item => item.user && item.user.id !== u.id
                );
            } else {
                hierarchyState.subs = hierarchyState.subs.filter(
                    item => item.user && item.user.id !== u.id
                );
            }

            rebuildRows();
            updateHierarchyControls();
            renderHierarchyPreview();
        });

        return card;
    }

    function appendRowWithConnectors(container, users, type) {
        const frag = document.createDocumentFragment();
        users.forEach((obj, index) => {
            const u = obj.user;
            if (!u) return;
            if (index > 0) {
                const connector = document.createElement('div');
                connector.className = 'hier-connector';
                frag.appendChild(connector);
            }
            frag.appendChild(buildCard(u, type));
        });
        container.appendChild(frag);
    }

    function rebuildRows() {
        superList.innerHTML = '';
        subsList.innerHTML = '';
        appendRowWithConnectors(superList, hierarchyState.responsaveis, 'super');
        appendRowWithConnectors(subsList, hierarchyState.subs, 'sub');
    }

    rebuildRows();
    updateHierarchyControls();
    renderHierarchyPreview();
}

function renderHierarchyPreview() {
    const previewBtn = document.getElementById('hier-preview');
    const previewMeta = document.getElementById('hier-preview-meta');
    const previewCounts = document.getElementById('hier-preview-counts');
    const previewRoot = document.getElementById('hier-preview-diagram');
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

    const diagramInner = document.getElementById('hier-diagram-inner');
    if (!diagramInner) return;

    const topRow = diagramInner.querySelector('.hier-diagram-row--top');
    const centerRow = diagramInner.querySelector('.hier-diagram-row--center');
    const bottomRow = diagramInner.querySelector('.hier-diagram-row--bottom');

    if (!centerRow) return;

    const superCount = hierarchyState.responsaveis.length;
    const subsCount = hierarchyState.subs.length;

    if (previewCounts) {
        previewCounts.textContent = `${superCount} responsáveis · ${subsCount} subordinados`;
    }
    if (previewMeta) {
        previewMeta.textContent = 'Arraste para navegar pelo mapa. Clique para ver em detalhe.';
    }

    const frag = document.createDocumentFragment();
    const inner = document.createElement('div');
    inner.className = 'hier-preview-diagram-inner';

    const rows = [topRow, centerRow, bottomRow];

    rows.forEach(row => {
        if (!row) return;

        const clone = row.cloneNode(true);
        clone.removeAttribute('id');
        clone.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));

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

    previewRoot.innerHTML = '';
    previewRoot.appendChild(frag);

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
            renderDiagram(null);
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
        renderDiagram(id);

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
    const btnOpen = document.getElementById('controlo-add');
    const overlay = document.getElementById('controlo-create-modal');
    const btnClose = document.getElementById('controlo-create-close');
    const btnCancel = document.getElementById('controlo-create-cancel');
    const form = document.getElementById('controlo-create-form');

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

    overlay.addEventListener('click', e => {
        if (e.target === overlay) {
            closeModal();
        }
    });

    if (form) {
        form.addEventListener('submit', e => {
            e.preventDefault();
            const fd = new FormData(form);

            createCollaborator(fd)
                .then(async res => {
                    if (res && res.ok === true) {
                        closeModal();
                        await fillCollaborators();
                    } else {
                        console.error('Failed to create collaborator:', res?.error);
                    }
                })
                .catch(err => {
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
    const saveBtn = document.getElementById('hierarchy-modal-save');

    if (!previewBtn || !modal) return;

    const openModal = () => {
        if (previewBtn.disabled) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    const doCancel = () => {
        if (hierarchyState.userId && isHierarchyDirty()) {
            renderDiagram(hierarchyState.userId);
        }
    };

    const closeModal = () => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    previewBtn.addEventListener('click', openModal);

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            doCancel();
            closeModal();
        });
    }
    if (closeFooter) {
        closeFooter.addEventListener('click', () => {
            doCancel();
            closeModal();
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            if (!hierarchyState.userId || !isHierarchyDirty()) return;

            const respIds = hierarchyState.responsaveis
                .map(r => (r.user ? r.user.id : null))
                .filter(id => id != null);

            const subsIds = hierarchyState.subs
                .map(s => (s.user ? s.user.id : null))
                .filter(id => id != null);

            updateHierarchy(hierarchyState.userId, respIds, subsIds)
                .then(res => {
                    if (!res || res.ok !== true) {
                        throw new Error('Resposta inválida do servidor');
                    }
                    hierarchyState.baseRespCount = hierarchyState.responsaveis.length;
                    hierarchyState.baseSubCount = hierarchyState.subs.length;
                    updateHierarchyControls();
                })
                .catch(err => {
                    console.error('Falha ao guardar hierarquia:', err);
                });
        });
    }

    modal.addEventListener('click', e => {
        if (e.target === modal) {
            doCancel();
            closeModal();
        }
    });

    enableDiagramDrag();
}

function enableDiagramDrag() {
    const modalContainer = document.querySelector('.hier-diagram-container');
    const modalInner = document.getElementById('hier-diagram-inner');

    if (modalContainer && modalInner) {
        let isDown = false;
        let startX = 0;
        let startY = 0;
        let scrollLeft = 0;
        let scrollTop = 0;

        modalInner.addEventListener('mousedown', e => {
            if (e.button !== 0) return;
            isDown = true;
            startX = e.clientX;
            startY = e.clientY;
            scrollLeft = modalContainer.scrollLeft;
            scrollTop = modalContainer.scrollTop;
            e.preventDefault();
        });

        window.addEventListener('mousemove', e => {
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

    const previewCanvas = document.querySelector('.hier-preview-canvas');
    const previewDiagram = document.getElementById('hier-preview-diagram');
    const previewBtn = document.getElementById('hier-preview');

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

        previewCanvas.addEventListener('mousedown', e => {
            if (previewBtn && previewBtn.disabled) return;
            if (e.button !== 0) return;

            isDown = true;
            hasDragged = false;
            startX = e.clientX;
            startY = e.clientY;
            previewCanvas.classList.add('is-dragging');

            e.preventDefault();
            e.stopPropagation();
        });

        window.addEventListener('mousemove', e => {
            if (!isDown) return;

            const dx = e.clientX - startX;
            const dy = e.clientY - startY;

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

        previewCanvas.addEventListener('click', e => {
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
    renderDiagram();
    bindSelect();
    renderCreateModal();
    renderHierarchyModal();
}