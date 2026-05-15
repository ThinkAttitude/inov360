import { getAllCollaborators, requestOvertime } from '../../app/api.js';
import { toast } from '../../shared/ui/toast/toast.js';
import './styles.css';

async function fillCollaborators() {
    const select = document.getElementById('colaborador');
    const feedbackDiv = document.getElementById('feedback-message');
    if (!select) return;

    try {
        const res = await getAllCollaborators();
        if (!res || res.ok !== true) {
            throw new Error('Resposta inesperada da API');
        }

        res.items.forEach(item => {
            if (!item || item.id == null || !item.nome) return;
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.email ? `${item.nome} (${item.email})` : item.nome;
            select.appendChild(opt);
        });

        if (res.items.length === 0 && feedbackDiv) {
            feedbackDiv.innerHTML = '<div class="feedback-message error">Nenhum colaborador encontrado.</div>';
        }
    } catch (err) {
        console.error('Erro ao carregar colaboradores:', err);
        if (feedbackDiv) {
            feedbackDiv.innerHTML = '<div class="feedback-message error">Erro ao carregar colaboradores</div>';
        }
        toast.error(err?.message || 'Não foi possível carregar a lista de colaboradores.');
    }
}

function setupForm() {
    const form = document.getElementById('form-horas-extra');
    const feedbackDiv = document.getElementById('feedback-message');
    const btn = document.getElementById('phe-submit-btn');
    const btnText = btn?.querySelector('.btn-text');
    if (!form || !btn || !btnText || !feedbackDiv) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const userId = document.getElementById('colaborador')?.value;
        const dia = document.getElementById('dia')?.value;
        const hInicioH = document.getElementById('hora_inicio_h')?.value ?? '';
        const hInicioM = document.getElementById('hora_inicio_m')?.value ?? '';
        const hFimH = document.getElementById('hora_fim_h')?.value ?? '';
        const hFimM = document.getElementById('hora_fim_m')?.value ?? '';
        const horaInicio = (hInicioH !== '' && hInicioM !== '') ? `${hInicioH}:${hInicioM}` : '';
        const horaFim = (hFimH !== '' && hFimM !== '') ? `${hFimH}:${hFimM}` : '';
        const justificacao = document.getElementById('justificacao')?.value?.trim();

        if (!userId || !dia || !horaInicio || !horaFim || !justificacao) {
            feedbackDiv.innerHTML = '<div class="feedback-message error">Preencha todos os campos obrigatórios.</div>';
            return;
        }

        if (horaInicio >= horaFim) {
            feedbackDiv.innerHTML = '<div class="feedback-message error">A hora de início deve ser anterior à hora de fim.</div>';
            return;
        }

        btn.classList.add('loading');
        btnText.textContent = 'A submeter...';
        feedbackDiv.textContent = '';

        try {
            const res = await requestOvertime({
                user_id: Number(userId),
                dia,
                hora_inicio: horaInicio,
                hora_fim: horaFim,
                justificacao
            });

            if (res?.ok) {
                feedbackDiv.innerHTML = '<div class="feedback-message success">Pedido de horas extra criado com sucesso.</div>';
                toast.success('Pedido de horas extra criado.');
                form.reset();
            } else {
                const msg = res?.error || res?.message || 'Erro ao submeter o pedido.';
                feedbackDiv.innerHTML = `<div class="feedback-message error">${msg}</div>`;
                toast.error(msg);
            }
        } catch (err) {
            console.error('Erro ao submeter horas extra:', err);
            const msg = err?.message || 'Falha na comunicação com o servidor.';
            feedbackDiv.innerHTML = `<div class="feedback-message error">${msg}</div>`;
            toast.error(msg);
        } finally {
            btn.classList.remove('loading');
            btnText.textContent = 'Submeter Pedido';
        }
    });
}

function fillHourSelects() {
    ['hora_inicio_h', 'hora_fim_h'].forEach(id => {
        const sel = document.getElementById(id);
        if (!sel) return;
        for (let h = 0; h < 24; h++) {
            const val = String(h).padStart(2, '0');
            const opt = document.createElement('option');
            opt.value = val;
            opt.textContent = val;
            sel.appendChild(opt);
        }
    });
}

export function mountPedidosHorasExtra() {
    fillCollaborators();
    fillHourSelects();
    setupForm();
}
