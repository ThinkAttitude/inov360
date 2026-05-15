import { createDirectLeave, getAllCollaborators } from '../../app/api.js';
import { toast } from '../../shared/ui/toast/toast.js';

import './styles.css';

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
    if (!fileInput) return;

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        const maxSize = 5 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            toast.error('Tipo de ficheiro não permitido. Use PDF, JPG ou PNG.');
            e.target.value = '';
            return;
        }
        if (file.size > maxSize) {
            toast.error('Ficheiro demasiado grande. Máximo 5MB.');
            e.target.value = '';
        }
    });
}

export function submit() {
    const form = document.getElementById('form-marcacao');
    const btn = form?.querySelector('.btn');
    const btnText = btn?.querySelector('.btn-text');
    if (!form || !btn || !btnText) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const dataInicio = document.getElementById('data_inicio')?.value;
        const dataFim = document.getElementById('data_fim')?.value;

        btn.classList.add('loading');
        btnText.textContent = 'Processando...';

        if (dataInicio && dataFim && new Date(dataInicio) > new Date(dataFim)) {
            toast.error('A data de início deve ser anterior à data de fim.');
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
                toast.success(`Ausência marcada com sucesso (ID ${resp.pedido_id}).`);
                form.reset();
            } else {
                toast.error(resp?.error || 'Erro ao submeter o pedido.');
            }
        } catch (err) {
            console.error('Erro ao marcar ausência:', err);
            toast.error(err.message || 'Falha na comunicação com o servidor.');
        } finally {
            btn.classList.remove('loading');
            btnText.textContent = 'Marcar Ausência';
        }
    });
}

async function fillCollaborators() {
    const select = document.getElementById('colaborador');
    if (!select) return;

    try {
        const res = await getAllCollaborators();
        if (!res || res.ok !== true) {
            throw new Error(res?.error || 'Resposta inesperada da API.');
        }

        res.items.forEach(item => {
            if (!item || item.id == null || !item.nome) return;
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.email ? `${item.nome} (${item.email})` : item.nome;
            select.appendChild(opt);
        });

        if (res.items.length === 0) {
            toast.warning('Nenhum colaborador encontrado.');
        }
    } catch (err) {
        console.error('Erro ao carregar colaboradores:', err);
        toast.error(err.message || 'Não foi possível carregar a lista de colaboradores.');
    }
}

export function initForm() {
    fillCollaborators();
    setMinDates();
    validateFiles();
    submit();
}
