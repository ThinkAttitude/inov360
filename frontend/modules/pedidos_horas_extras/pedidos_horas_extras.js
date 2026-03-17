import { getAllCollaborators, requestOvertime } from '../../app/api.js';
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
    } catch {
        if (feedbackDiv) {
            feedbackDiv.innerHTML = '<div class="feedback-message error">Erro ao carregar colaboradores</div>';
        }
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
                form.reset();
            } else {
                const msgs = {
                    MISSING_FIELDS: 'Campos obrigatórios em falta.',
                    INVALID_DATE: 'Data inválida.',
                    INVALID_TIME_FORMAT: 'Formato de hora inválido.',
                    INVALID_TIME_RANGE: 'Intervalo de horas inválido.',
                    INVALID_GRANULARITY_15MIN: 'O intervalo deve ser em blocos de 15 minutos.',
                    MAX_HOURS_EXCEEDED: 'Máximo de 12 horas excedido.',
                    USER_NOT_FOUND: 'Colaborador não encontrado.',
                    DUPLICATE_REQUEST_OVERLAP: 'Já existe um pedido pendente nesse período.',
                    ALREADY_HAS_APPROVED_OVERTIME: 'Já existem horas extra aprovadas nesse período.',
                    OVERTIME_CLOSED: 'O período de horas extra está encerrado para este dia.',
                };
                const msg = msgs[res?.code] || 'Erro ao submeter o pedido.';
                feedbackDiv.innerHTML = `<div class="feedback-message error">${msg}</div>`;
            }
        } catch {
            feedbackDiv.innerHTML = '<div class="feedback-message error">Falha na comunicação com o servidor.</div>';
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
