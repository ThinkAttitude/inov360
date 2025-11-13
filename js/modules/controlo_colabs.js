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
        return;
    }

    try {
        const res = await getSubsByUser(userId);
        if (!res || res.ok !== true || res.items.length === 0) {
            emptyEl.style.display = '';
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
    } catch (e) {
        console.error('Erro ao carregar subs:', e);
        emptyEl.style.display = '';
    }
}

function bindSelect() {
    const select = document.getElementById('controlo-user-select');
    if (!select) return;

    select.addEventListener('change', () => {
        const val = select.value;
        if (!val) {
            renderPerms([]);
            renderSubs(null);
            return;
        }
        const id = parseInt(val, 10);
        const c = collabs.find(u => u.id === id);
        renderPerms(c?.permissoes || [], true);
        renderSubs(id);
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

export async function mountClbMngmt() {
    await fillCollaborators();
    renderPerms([], false);
    renderSubs();
    bindSelect();
    renderCreateModal();
}

// TODO: Remover colaboradores (clicar no cartao e remover)