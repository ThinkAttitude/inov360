import { createDirectLeave, getCollaborators } from '../api.js';

function setMinDates() {
    const today = new Date().toISOString().split('T')[0];
    const start = document.getElementById('data_inicio');
    const end = document.getElementById('data_fim');

    if (start) start.setAttribute('min', today);
    if (end) end.setAttribute('min', today);

    if (start && end) {
        start.addEventListener('change', () => {
            if (start.value) end.setAttribute('min', start.value);
        });
    }
}

function validateFiles() {
    const fileInput = document.getElementById('ficheiro');
    const feedbackDiv = document.getElementById('feedback-message');
    if (!fileInput || !feedbackDiv) return;

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        const maxSize = 5 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            feedbackDiv.innerHTML = '<div class="feedback-message error">Tipo de ficheiro não permitido. Use PDF, JPG ou PNG.</div>';
            e.target.value = '';
            return;
        }
        if (file.size > maxSize) {
            feedbackDiv.innerHTML = '<div class="feedback-message error">Ficheiro demasiado grande. Máximo 5MB.</div>';
            e.target.value = '';
            return;
        }
        feedbackDiv.innerHTML = '<div class="feedback-message success">Ficheiro válido selecionado.</div>';
    });
}

export function submit() {
    const form = document.getElementById('form-marcacao');
    const feedbackDiv = document.getElementById('feedback-message');
    const btn = form?.querySelector('.btn');                  // <-- matches your HTML
    const btnText = btn?.querySelector('.btn-text');          // <-- matches your HTML
    if (!form || !btn || !btnText || !feedbackDiv) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const dataInicio = document.getElementById('data_inicio')?.value;
        const dataFim = document.getElementById('data_fim')?.value;

        btn.classList.add('loading');
        btnText.textContent = 'Processando...';
        feedbackDiv.textContent = '';

        if (dataInicio && dataFim && new Date(dataInicio) > new Date(dataFim)) {
            feedbackDiv.innerHTML = '<div class="feedback-message error">A data de início deve ser anterior à data de fim.</div>';
            btn.classList.remove('loading');
            btnText.textContent = 'Marcar Ausência';
            return;
        }

        const fd = new FormData();
        fd.append('user_id', document.getElementById('colaborador')?.value || '');
        fd.append('tipo', document.getElementById('tipo')?.value || '');
        fd.append('data_inicio', dataInicio || '');
        fd.append('data_fim', dataFim || '');
        fd.append('justificacao', document.getElementById('justificacao')?.value || '');
        const file = document.getElementById('ficheiro')?.files?.[0];
        if (file) fd.append('ficheiro', file);

        try {
            const resp = await createDirectLeave(fd);
            if (resp?.ok) {
                feedbackDiv.innerHTML = `<div class="feedback-message success">Pedido criado (ID ${resp.pedido_id}).</div>`;
                form.reset();
            } else {
                feedbackDiv.innerHTML = `<div class="feedback-message error">${resp?.error || 'Erro ao submeter o pedido.'}</div>`;
            }
        } catch {
            feedbackDiv.innerHTML = '<div class="feedback-message error">Falha na comunicação com o servidor.</div>';
        } finally {
            btn.classList.remove('loading');
            btnText.textContent = 'Marcar Ausência';
        }
    });
}

async function fillCollaborators() {
    const select = document.getElementById('colaborador');
    const feedbackDiv = document.getElementById('feedback-message');
    if (!select) return;

    try {
        const res = await getCollaborators(); // { ok, total, items: [...] }
        if (!res || res.ok !== true || !Array.isArray(res.items)) {
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
        if (feedbackDiv) {
            feedbackDiv.innerHTML = `<div class="feedback-message error">Erro ao carregar colaboradores: ${err.message}</div>`;
        }
    }
}

export function initForm() {
    fillCollaborators();
    setMinDates();
    validateFiles();
    submit();
}
