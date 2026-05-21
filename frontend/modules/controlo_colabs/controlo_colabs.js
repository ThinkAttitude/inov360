import {
    createCollaborator,
    getAllCollaborators,
    getHierarchyByUser,
    updateHierarchy,
    updatePermissions
} from '../../app/api.js';
import { toast } from '../../shared/ui/toast/toast.js';
import {generateSecurePassword} from '../../shared/security/password.js';


import './styles.css';
import './modal.css';

function bindPasswordGenerator(btn, pwdInput) {
    if (!btn || !pwdInput) return;
    btn.addEventListener('click', () => {
        pwdInput.value = generateSecurePassword();
    });
}

const hierarchyState = {
    userId: null,
    responsaveis: [],
    subs: [],
    // Snapshot of resp/sub IDs at load time — used to detect unsaved changes
    // before the user navigates away or submits (dirty-state check for hierarchy editor).
    baseRespIds: [],
    baseSubIds: [],
};

const setHierarchyState = (patch) => Object.assign(hierarchyState, patch);

const idsOfEntries = (list) =>
    (Array.isArray(list) ? list : [])
        .map(item => (item && item.user ? item.user.id : null))
        .filter(id => id != null)
        .sort((a, b) => a - b);

const sameIds = (a, b) => {
    if (a.length !== b.length) return false;
    for (let i = 0; i < a.length; i++) {
        if (a[i] !== b[i]) return false;
    }
    return true;
};

const isHierarchyDirty = () => {
    const {responsaveis, subs, baseRespIds, baseSubIds} = hierarchyState;
    return (
        !sameIds(idsOfEntries(responsaveis), baseRespIds) ||
        !sameIds(idsOfEntries(subs), baseSubIds)
    );
};

function updateHierarchyControls() {
    const saveBtn = document.getElementById('hierarchy-modal-save');
    const closeFooter = document.getElementById('hierarchy-modal-close-footer');
    const statusEl = document.getElementById('hierarchy-modal-status');
    const dirty = isHierarchyDirty();

    if (saveBtn) saveBtn.disabled = !dirty;
    if (closeFooter) closeFooter.textContent = dirty ? 'Cancelar' : 'Fechar';
    if (dirty && statusEl?.classList.contains('hier-modal-status--success')) {
        statusEl.textContent = '';
        statusEl.classList.remove(
            'hier-modal-status--success',
            'hier-modal-status--visible',
        );
    }
}

async function fillCollaborators() {
    const select = document.getElementById('controlo-user-select');
    if (!select) return;

    const currentValue = select.value;

    try {
        const res = await getAllCollaborators();
        if (!res || res.ok !== true || !Array.isArray(res.items)) return;

        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecione um colaborador';
        select.appendChild(placeholder);

        res.items.forEach(item => {
            if (!item || item.id == null || !item.nome) return;
            const opt = document.createElement('option');
            opt.value = String(item.id);
            opt.textContent = item.email ? `${item.nome} (${item.email})` : item.nome;
            select.appendChild(opt);
        });

        if (currentValue && Array.from(select.options).some(opt => opt.value === currentValue)) {
            select.value = currentValue;
        }
    } catch (e) {
        console.error('Erro ao carregar colaboradores:', e);
        toast.error(e?.message || 'Não foi possível carregar a lista de colaboradores.');
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

                const res = await getAllCollaborators();
                if (!res || res.ok !== true || !Array.isArray(res.items)) return;

                const collab = res.items.find(c => c.id === userId);
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
                    toast.success(newState ? 'Permissão atribuída.' : 'Permissão removida.');
                } catch (err) {
                    console.error('Falha ao atualizar permissões:', err);
                    chip.classList.toggle('chip-on', wasOn);
                    chip.classList.toggle('chip-off', !wasOn);
                    chip.setAttribute('aria-pressed', String(wasOn));
                    toast.error(err?.message || 'Não foi possível atualizar a permissão.');
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

    if (!userId) {
        setHierarchyState({
            userId: null,
            responsaveis: [],
            subs: [],
            baseRespIds: [],
            baseSubIds: [],
        });
        emptyEl.style.display = '';
        updateHierarchyControls();
        rebuildRows();
        renderHierarchyPreview();
        return;
    }

    setHierarchyState({ userId });

    const res = await getHierarchyByUser(userId).catch(err => {
        console.error('Erro ao carregar hierarquia:', err);
        toast.error(err?.message || 'Não foi possível carregar a hierarquia.');
        setHierarchyState({
            responsaveis: [],
            subs: [],
            baseRespIds: [],
            baseSubIds: [],
        });
        emptyEl.style.display = '';
        updateHierarchyControls();
        rebuildRows();
        renderHierarchyPreview();
        return null;
    });

    if (!res || res.ok !== true) {
        setHierarchyState({
            responsaveis: [],
            subs: [],
            baseRespIds: [],
            baseSubIds: [],
        });
        emptyEl.style.display = '';
        updateHierarchyControls();
        rebuildRows();
        renderHierarchyPreview();
        return;
    }

    const responsaveis = Array.isArray(res.responsaveis) ? res.responsaveis : [];
    const subs = Array.isArray(res.subordinados) ? res.subordinados : [];

    setHierarchyState({
        responsaveis,
        subs,
        baseRespIds: idsOfEntries(responsaveis),
        baseSubIds: idsOfEntries(subs),
    });

    const hasAny = responsaveis.length > 0 || subs.length > 0;
    emptyEl.style.display = hasAny ? 'none' : '';

    rebuildRows();
    updateHierarchyControls();
    renderHierarchyPreview();
}

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

        if (!hierarchyState.userId) return;
        if (!confirm('Remover este colaborador desta hierarquia?')) return;

        if (type === 'super') {
            setHierarchyState({
                responsaveis: hierarchyState.responsaveis.filter(
                    item => item.user && item.user.id !== u.id
                ),
            });
        } else {
            setHierarchyState({
                subs: hierarchyState.subs.filter(
                    item => item.user && item.user.id !== u.id
                ),
            });
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
        const user = obj.user;
        if (!user) return;

        if (index > 0) {
            const connector = document.createElement('div');
            connector.className = 'hier-connector';
            frag.appendChild(connector);
        }

        frag.appendChild(buildCard(user, type));
    });

    container.appendChild(frag);
}

function rebuildRows() {
    const superList = document.getElementById('hier-super-list');
    const subsList = document.getElementById('hier-subs-list');

    superList.innerHTML = '';
    subsList.innerHTML = '';
    appendRowWithConnectors(superList, hierarchyState.responsaveis, 'super');
    appendRowWithConnectors(subsList, hierarchyState.subs, 'sub');
}

async function openHierarchyAddSelect(type, areaEl) {
    if (!hierarchyState.userId || !areaEl) return;

    const modal = document.getElementById('hierarchy-modal');
    const dialog = modal.querySelector('.controlo-modal');
    if (!modal || !dialog) return;

    const existing = modal.querySelector('.hier-add-overlay');
    if (existing) existing.remove();

    const res = await getAllCollaborators();
    if (!res || res.ok !== true || !Array.isArray(res.items)) return;

    const overlay = document.createElement('div');
    overlay.className = 'hier-add-overlay';

    const panel = document.createElement('div');
    panel.className = 'hier-add-panel';

    const label = document.createElement('div');
    label.className = 'field-label';
    label.textContent = type === 'super'
        ? 'Adicionar responsável'
        : 'Adicionar subordinado';

    const select = document.createElement('select');
    select.className = 'field-select hier-add-select';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Selecione um colaborador';
    select.appendChild(placeholder);

    const usedIds = new Set();
    usedIds.add(hierarchyState.userId);
    hierarchyState.responsaveis.forEach(r => {
        if (r.user.id) usedIds.add(r.user.id);
    });
    hierarchyState.subs.forEach(s => {
        if (s.user.id) usedIds.add(s.user.id);
    });

    res.items.forEach(c => {
        if (!c.id || usedIds.has(c.id)) return;
        const opt = document.createElement('option');
        opt.value = String(c.id);
        opt.textContent = c.email ? `${c.nome} (${c.email})` : c.nome;
        select.appendChild(opt);
    });

    if (select.options.length === 1) return;

    const actions = document.createElement('div');
    actions.className = 'hier-add-actions';

    const cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'btn-secondary';
    cancelBtn.textContent = 'Cancelar';

    actions.appendChild(cancelBtn);

    panel.appendChild(label);
    panel.appendChild(select);
    panel.appendChild(actions);
    overlay.appendChild(panel);
    modal.appendChild(overlay);

    const dialogRect = dialog.getBoundingClientRect();
    const areaRect = areaEl.getBoundingClientRect();
    const top = areaRect.top - dialogRect.top - 8;
    const left = areaRect.left - dialogRect.left + areaRect.width / 2;

    panel.style.top = `${Math.max(8, top)}px`;
    panel.style.left = `${left}px`;
    panel.style.transform = 'translate(-50%, -100%)';

    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.remove();
    });

    cancelBtn.addEventListener('click', () => {
        overlay.remove();
    });

    panel.addEventListener('click', e => {
        e.stopPropagation();
    });

    select.addEventListener('change', () => {
        const id = parseInt(select.value, 10);
        if (!id) return;

        const chosen = res.items.find(c => c.id === id);
        if (!chosen) return;

        const entry = { user: chosen };

        if (type === 'super') {
            setHierarchyState({
                responsaveis: [...hierarchyState.responsaveis, entry],
            });
        } else {
            setHierarchyState({
                subs: [...hierarchyState.subs, entry],
            });
        }

        rebuildRows();
        updateHierarchyControls();
        renderHierarchyPreview();
        overlay.remove();
    });

    select.focus();
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

    select.addEventListener('change', async () => {
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
        const res = await getAllCollaborators();
        const c = res && res.ok === true && Array.isArray(res.items)
            ? res.items.find(u => u.id === id)
            : null;

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

    const btnGenPwd = document.getElementById('create-password-generate');
    const pwdInput = document.getElementById('create-password');
    bindPasswordGenerator(btnGenPwd, pwdInput);

    const btnTogglePwd = document.getElementById('create-password-toggle');
    if (btnTogglePwd && pwdInput) {
        const iconEye = btnTogglePwd.querySelector('.icon-eye');
        const iconEyeOff = btnTogglePwd.querySelector('.icon-eye-off');
        btnTogglePwd.addEventListener('click', () => {
            const isHidden = pwdInput.type === 'password';
            pwdInput.type = isHidden ? 'text' : 'password';
            btnTogglePwd.setAttribute('aria-pressed', String(isHidden));
            btnTogglePwd.setAttribute(
                'aria-label',
                isHidden ? 'Esconder palavra-passe' : 'Mostrar palavra-passe'
            );
            if (iconEye && iconEyeOff) {
                iconEye.style.display = isHidden ? 'none' : '';
                iconEyeOff.style.display = isHidden ? '' : 'none';
            }
        });
    }

    if (form) {
        form.addEventListener('submit', e => {
            e.preventDefault();
            const fd = new FormData(form);

            createCollaborator(fd)
                .then(async res => {
                    if (res && res.ok === true) {
                        closeModal();
                        await fillCollaborators();
                        toast.success('Colaborador criado com sucesso.');
                    } else {
                        console.error('Failed to create collaborator:', res?.error);
                        toast.error(res?.error || 'Não foi possível criar o colaborador.');
                    }
                })
                .catch(err => {
                    console.error('Failed to create collaborator:', err);
                    toast.error(err.message || 'Erro ao criar o colaborador.');
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

            const statusEl = document.getElementById('hierarchy-modal-status');
            const originalLabel = saveBtn.textContent;

            const setStatus = (msg, kind) => {
                if (!statusEl) return;
                statusEl.textContent = msg || '';
                statusEl.classList.remove(
                    'hier-modal-status--success',
                    'hier-modal-status--error',
                    'hier-modal-status--info',
                    'hier-modal-status--visible',
                );
                if (msg) {
                    statusEl.classList.add(`hier-modal-status--${kind}`, 'hier-modal-status--visible');
                }
            };

            saveBtn.disabled = true;
            saveBtn.textContent = 'A guardar...';
            setStatus('A guardar...', 'info');

            updateHierarchy(hierarchyState.userId, respIds, subsIds)
                .then(res => {
                    if (!res || res.ok !== true) {
                        throw new Error('Resposta inválida do servidor');
                    }
                    setHierarchyState({
                        baseRespIds: idsOfEntries(hierarchyState.responsaveis),
                        baseSubIds: idsOfEntries(hierarchyState.subs),
                    });
                    saveBtn.textContent = originalLabel;
                    updateHierarchyControls();
                    toast.success('Hierarquia guardada com sucesso.');
                })
                .catch(err => {
                    console.error('Falha ao guardar hierarquia:', err);
                    toast.error(err?.message || 'Não foi possível guardar a hierarquia.');
                });
        });
    }

    modal.addEventListener('click', e => {
        if (e.target === modal) {
            doCancel();
            closeModal();
        }
    });

    const topRowArea = modal.querySelector('.hier-diagram-row--top');
    const bottomRowArea = modal.querySelector('.hier-diagram-row--bottom');

    if (topRowArea) {
        topRowArea.addEventListener('click', e => {
            if (e.target.closest('.hierarchy-item')) return;
            if (e.target.closest('.hier-add-select')) return;
            openHierarchyAddSelect('super', topRowArea);
        });
    }

    if (bottomRowArea) {
        bottomRowArea.addEventListener('click', e => {
            if (e.target.closest('.hierarchy-item')) return;
            if (e.target.closest('.hier-add-select')) return;
            openHierarchyAddSelect('sub', bottomRowArea);
        });
    }

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