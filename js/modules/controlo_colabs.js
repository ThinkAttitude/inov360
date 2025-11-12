import { getAllCollaborators } from '../api.js';
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

        // disable chip when no user is selected
        chip.disabled = !enabled;

        const isOn = enabled && active.has(permId);
        chip.classList.toggle('chip-on', isOn);
        chip.classList.toggle('chip-off', !isOn);
        chip.setAttribute('aria-pressed', String(isOn));

        if (!chip.dataset.bound) {
            chip.addEventListener('click', () => {
                if (chip.disabled) return;

                const currentlyOn = chip.classList.contains('chip-on');
                const newState = !currentlyOn;

                chip.classList.toggle('chip-on', newState);
                chip.classList.toggle('chip-off', !newState);
                chip.setAttribute('aria-pressed', String(newState));

                // TODO: integração com API para guardar alteração
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

function bindSelect() {
    const select = document.getElementById('controlo-user-select');
    if (!select) return;

    select.addEventListener('change', () => {
        const val = select.value;

        if (!val) {
            // nenhum colaborador selecionado
            renderPerms([], false);
            return;
        }

        const id = parseInt(val, 10);
        const c = collabs.find(u => u.id === id);

        renderPerms(c?.permissoes || [], true);
    });
}

export async function mountClbMngmt() {
    await fillCollaborators();
    renderPerms([], false);
    bindSelect();
}