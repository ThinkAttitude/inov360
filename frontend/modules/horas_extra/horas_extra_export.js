import { getOvertimeHistory, exportOvertimeSheets } from '../../app/api.js';

export function initExport({ horasState, esc, formatDate, formatMinutes }) {

    async function loadExportPreview() {
        const monthInput = document.getElementById('he-export-month');
        const month = monthInput?.value;
        if (!month) { alert('Selecione um mês.'); return; }

        const preview = document.getElementById('he-export-preview');
        const tbody = document.getElementById('he-export-tbody');
        const empty = document.getElementById('he-export-empty');
        const subtitle = document.getElementById('he-export-preview-subtitle');
        const xlsxBtn = document.getElementById('he-export-xlsx-btn');

        if (!tbody || !preview) return;

        preview.style.display = 'block';
        tbody.innerHTML = '';
        if (empty) { empty.style.display = 'block'; empty.textContent = 'A carregar...'; }
        if (xlsxBtn) xlsxBtn.disabled = true;

        try {
            const res = await getOvertimeHistory({ month, state: 'approved' });
            if (!res?.ok) throw new Error(res?.code || 'Erro');

            horasState.export = res.items;
            if (subtitle) subtitle.textContent = `${res.items.length} registo(s) aprovado(s) em ${month}`;
            renderExportTable(horasState.export);
        } catch {
            horasState.export = [];
            if (empty) { empty.style.display = 'block'; empty.textContent = 'Não foi possível carregar os dados.'; }
        }
    }

    function renderExportTable(items) {
        const tbody = document.getElementById('he-export-tbody');
        const empty = document.getElementById('he-export-empty');
        const xlsxBtn = document.getElementById('he-export-xlsx-btn');
        const selectAll = document.getElementById('he-export-select-all');

        if (!tbody) return;
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            if (empty) { empty.style.display = 'block'; empty.textContent = 'Nenhum pedido aprovado encontrado para este mês.'; }
            if (xlsxBtn) xlsxBtn.disabled = true;
            return;
        }
        if (empty) empty.style.display = 'none';
        if (selectAll) selectAll.checked = true;

        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="checkbox" class="he-export-check" data-user-id="${item.colaborador.id}" checked></td>
                <td>
                    <div class="he-collab-name">${esc(item.colaborador.nome)}</div>
                    <div class="he-collab-email">${esc(item.colaborador.email)}</div>
                </td>
                <td>${formatDate(item.dia)}</td>
                <td>${esc(item.hora_inicio)}</td>
                <td>${esc(item.hora_fim)}</td>
                <td>${formatMinutes(item.req_minutos)}</td>
            `;
            tbody.appendChild(tr);
        });

        updateExportButton();
    }

    function updateExportButton() {
        const checks = document.querySelectorAll('.he-export-check:checked');
        const xlsxBtn = document.getElementById('he-export-xlsx-btn');
        if (xlsxBtn) xlsxBtn.disabled = checks.length === 0;
    }

    document.getElementById('he-export-preview-btn')?.addEventListener('click', loadExportPreview);

    document.getElementById('he-export-select-all')?.addEventListener('change', (e) => {
        document.querySelectorAll('.he-export-check').forEach(cb => { cb.checked = e.target.checked; });
        updateExportButton();
    });

    document.getElementById('he-export-tbody')?.addEventListener('change', () => {
        const all = document.querySelectorAll('.he-export-check');
        const checked = document.querySelectorAll('.he-export-check:checked');
        const selectAll = document.getElementById('he-export-select-all');
        if (selectAll) selectAll.checked = all.length === checked.length;
        updateExportButton();
    });

    document.getElementById('he-export-xlsx-btn')?.addEventListener('click', async () => {
        const month = document.getElementById('he-export-month')?.value;
        if (!month) return;

        const checks = document.querySelectorAll('.he-export-check:checked');
        const userIds = [...new Set([...checks].map(cb => cb.dataset.userId))];

        const btn = document.getElementById('he-export-xlsx-btn');
        if (btn) { btn.disabled = true; btn.textContent = 'A exportar...'; }

        try {
            const res = await exportOvertimeSheets(month, userIds);
            if (res instanceof Response) {
                const blob = await res.blob();
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `horas_extra_${month}.xlsx`;
                a.click();
                URL.revokeObjectURL(url);
            }
        } catch {
            alert('Erro ao exportar o ficheiro.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Exportar Excel
                `;
            }
        }
    });
}
