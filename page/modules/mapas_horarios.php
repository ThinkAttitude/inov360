<?php
session_start();
$role = $_SESSION['user']['role'] ?? '';
if (!isset($_SESSION['is_login'])) {
    echo "<p>Acesso negado.</p>";
    exit;
}
?>

<link rel="stylesheet" href="../../css/legacy/horarios_common.css">
<link rel="stylesheet" href="../../css/legacy/aprovacao_horarios.css">

<div class="aprovacao-page">
    <div class="page-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M8 12h8M8 16h8M8 8h8"></path>
                </svg>
            </div>
            <div class="header-text">
                <h2>Extração de Horários Aprovados</h2>
                <p>Selecione os colaboradores com horários aprovados e prepare a extração (PDF em construção).</p>
            </div>
        </div>
    </div>

    <div class="controls-section">
        <div class="search-controls">
            <div class="search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <polyline points="21,21 16.65,16.65"></polyline>
                </svg>
                <input type="text" id="extract-search" placeholder="Pesquisar por nome ou mês...">
            </div>
            <select id="extract-month">
                <option value="">Todos os meses</option>
            </select>
            <div class="actions-group">
                <button id="btn-refresh-extract" class="btn-primary" style="border-radius:8px;">Sincronizar</button>
                <button id="btn-load-local" class="btn-secondary" style="border-radius:8px;">Importar do navegador</button>
            </div>
        </div>
    </div>

    <div class="summary-cards" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:12px;">
        <div class="welcome-card" style="padding:12px;">
            <h4 style="margin:0 0 6px;">Prontos para Extrair</h4>
            <div><strong id="count-ready">0</strong> registos aprovados</div>
        </div>
        <div class="welcome-card" style="padding:12px;">
            <h4 style="margin:0 0 6px;">Já Extraídos</h4>
            <div><strong id="count-extracted">0</strong> registos</div>
        </div>
    </div>

    <div class="bulk-actions" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:8px;">
        <button id="btn-generate-pdf" class="btn-success">Gerar PDFs Selecionados</button>
        <button id="btn-export-zip" class="btn-secondary">Exportar ZIP</button>
    <button id="btn-mark-extracted" class="btn-primary" style="border-radius:8px;">Marcar como Extraído</button>
        <span id="selection-count" style="color:#64748b;">0 selecionados</span>
    </div>

    <div id="extract-table-container" class="approvals-container" style="padding:0;">
        <!-- Tabela renderizada via JS -->
    </div>

    <!-- Modal de Detalhes -->
    <div id="extract-details-modal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Detalhes do Colaborador</h3>
                <button class="close-btn" onclick="(function(){const m=document.getElementById('extract-details-modal');m.style.display='none';document.body.style.overflow='';})()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="extract-details-body"></div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="(function(){const m=document.getElementById('extract-details-modal');m.style.display='none';document.body.style.overflow='';})()">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // Utilidades simples
    const fmtMin = (m)=>{ if(!m) return '0h'; const h=Math.floor(m/60),mi=m%60; return h? (mi?`${h}h${mi}m`:`${h}h`) : `${mi}m`; };
    const monthName = (key)=>{ if(!key) return '—'; const [y,m]=key.split('-'); const arr=['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']; return `${arr[parseInt(m)-1]} ${y}`; };

    // Estado local do módulo
    const state = { rows: [], selected: new Set(), extracted: new Set() };

    function loadExtractedSet(){
        try { const ids = JSON.parse(localStorage.getItem('horarios_extraidos_ids')||'[]'); state.extracted = new Set(ids); } catch(_){}
    }
    function saveExtractedSet(){ try { localStorage.setItem('horarios_extraidos_ids', JSON.stringify(Array.from(state.extracted))); } catch(_){}
    }

    function populateMonths(){
        const sel = document.getElementById('extract-month');
        if(!sel) return;
        // Clear except first
        while(sel.options.length>1) sel.remove(1);
        const now = new Date();
        for(let i=0;i<12;i++){
            const d = new Date(now.getFullYear(), now.getMonth()-i, 1);
            const mk = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
            const opt = document.createElement('option');
            opt.value = mk; opt.textContent = monthName(mk);
            sel.appendChild(opt);
        }
    }

    function syncFromServer(){
        if(syncFromServer._loading){ return; }
        syncFromServer._loading = true;
        notify('A sincronizar períodos aprovados do servidor...', 'info');
        const monthSel = (document.getElementById('extract-month')||{}).value||'';
        const url = `/api/timesheets/aval_periods.php?state=approved${monthSel?`&month=${encodeURIComponent(monthSel)}`:''}`;
        fetch(url, {credentials:'same-origin'})
          .then(r=>r.json().catch(()=>null))
          .then(async data => {
            if(!data || data.ok!==true || !Array.isArray(data.items)){
                notify('Falha ao sincronizar (lista).', 'error');
                return;
            }
            // Mapear diretamente da API existente (sem segunda chamada por sumários)
            const baseRows = data.items.map(it => {
                const mKey = `${it.mes?.year ?? ''}-${String(it.mes?.month ?? '').padStart(2,'0')}`;
                return {
                    id: `${it.colaborador?.id}-${mKey}`,
                    userId: it.colaborador?.id,
                    userName: it.colaborador?.nome || it.colaborador?.name || it.colaborador?.email || it.colaborador?.id,
                    month: mKey,
                    dias: it.resumo?.workedDays || 0,
                    horasTotais: (it.resumo?.workMin || 0)/60,
                    horasExtraMin: it.resumo?.otMin || 0,
                    km: it.resumo?.km || 0,
                    processedDate: it.submetido_em || null
                };
            });
            state.rows = baseRows;
            render();
            notify('Sincronização concluída.', 'success');
          })
          .catch(()=>notify('Erro de rede ao sincronizar.', 'error'))
          .finally(()=>{ syncFromServer._loading=false; });
    }

    async function fetchMonthSummary(row){
        const url = `/api/calendar/get_month.php?user_id=${encodeURIComponent(row.userId)}&month=${encodeURIComponent(row.month)}`;
        const res = await fetch(url, {credentials:'same-origin'});
        if(!res.ok) return;
        const data = await res.json().catch(()=>null);
        if(!data || data.ok!==true) return;
        let workedDays = 0;
        (data.days||[]).forEach(d=>{ if((d.workMin||0)>0) workedDays++; });
        const workMin = data.totals?.workMin || 0;
        const extraMin = data.totals?.otMin || 0;
        const km = data.totals?.km || 0;
        row.dias = workedDays;
        row.horasTotais = (workMin/60); // horas (decimal)
        row.horasExtraMin = extraMin;   // minutos
        row.km = km;
        delete row._needsSummary;
    }

    async function runLimited(tasks, limit){
        let i=0; const running=[];
        const launch = () => {
            if(i>=tasks.length) return Promise.resolve();
            const t = tasks[i++]();
            const p = t.then(()=>{
                running.splice(running.indexOf(p),1);
                render(); // atualizar progressivamente
            });
            running.push(p);
            const next = running.length>=limit ? Promise.race(running) : Promise.resolve();
            return next.then(launch);
        };
        await launch();
        await Promise.all(running);
    }

    function loadFromLocalStorage(){
        // Ler do navegador (caso o fluxo de aprovação tenha ocorrido neste browser)
        let processed = [];
        try { processed = JSON.parse(localStorage.getItem('marcacoes_processed')||'[]'); } catch(_){}
        // Filtrar aprovados
        const approved = processed.filter(r => {
            if(!r) return false;
            const s = (r.status==null? '': String(r.status)).toLowerCase().trim();
            return s === 'approved' || s === 'aprovado';
        });
        // Mapear para linhas da tabela
        state.rows = approved.map(r => ({
            id: `${r.userId}-${r.month}`,
            userId: r.userId,
            userName: r.userName || r.submittedBy || r.userId,
            month: r.month,
            dias: r.summary?.diasTrabalhados || 0,
            horasTotais: r.summary?.horasTotais || 0,
            horasExtraMin: r.summary?.horasExtra || 0,
            km: r.summary?.kmTotal || 0,
            processedDate: r.processedDate || null
        }));
        render();
    }

    function render(){
        const container = document.getElementById('extract-table-container');
        const search = (document.getElementById('extract-search')||{}).value?.toLowerCase()||'';
        const month = (document.getElementById('extract-month')||{}).value||'';

        let rows = state.rows.slice();
        if(month) rows = rows.filter(r => r.month === month);
        if(search) rows = rows.filter(r => (r.userName||'').toLowerCase().includes(search) || monthName(r.month).toLowerCase().includes(search));
        rows.sort((a,b)=> (b.processedDate||'').localeCompare(a.processedDate||''));

        // Summary
        const ready = rows.filter(r=>!state.extracted.has(r.id)).length;
        const already = rows.filter(r=>state.extracted.has(r.id)).length;
        const allExtracted = state.rows.filter(r=>state.extracted.has(r.id)).length;
        document.getElementById('count-ready').textContent = String(ready);
        document.getElementById('count-extracted').textContent = String(allExtracted);

        // Build table
        if(!rows.length){ container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                </div>
                <h3>Sem registos para mostrar</h3>
                <p>Use "Importar do navegador" para carregar aprovações locais ou aguarde a integração do servidor.</p>
            </div>`; updateSelectionCount(); return; }

        let html = '<div class="table-wrapper">';
        html += '<table class="data-table" style="width:100%;border-collapse:collapse;">';
        html += '<thead><tr>'+
            '<th style="width:36px;"><input type="checkbox" id="chk-all"></th>'+
            '<th>Colaborador</th>'+
            '<th>Mês</th>'+
            '<th>Dias</th>'+
            '<th>Horas</th>'+
            '<th>Extra</th>'+
            '<th>KM</th>'+
            '<th>Aprovado em</th>'+
            '<th>Estado</th>'+
            '<th>Ações</th>'+
            '</tr></thead><tbody>';

        rows.forEach(r => {
            const selected = state.selected.has(r.id);
            const isExtracted = state.extracted.has(r.id);
            html += '<tr data-id="'+r.id+'">'+
                '<td><input type="checkbox" class="row-chk" '+(selected?'checked':'')+'></td>'+
                '<td>'+escapeHtml(r.userName)+'</td>'+
                '<td>'+escapeHtml(monthName(r.month))+'</td>'+
                '<td style="text-align:right;">'+r.dias+'</td>'+
                '<td style="text-align:right;">'+Number(r.horasTotais).toFixed(1)+'h</td>'+
                '<td style="text-align:right;">'+fmtMin(r.horasExtraMin)+'</td>'+
                '<td style="text-align:right;">'+(r.km||0)+' km</td>'+
                '<td>'+(r.processedDate? new Date(r.processedDate).toLocaleDateString('pt-PT') : '—')+'</td>'+
                '<td>'+(isExtracted? '<span class="status-badge approved">Extraído</span>' : '<span class="status-badge pending">Por extrair</span>')+'</td>'+
                '<td>'+
                    '<button class="btn-details" data-action="detalhes">Ver Detalhes</button> '
                    +'<button class="btn-secondary" data-action="preparar">Preparar PDF</button>'+
                '</td>'+
            '</tr>';
        });
        html += '</tbody></table></div>';
        container.innerHTML = html;

        // Bind events
        const chkAll = document.getElementById('chk-all');
        if(chkAll){ chkAll.addEventListener('change', function(){
            const ids = rows.map(r=>r.id);
            if(this.checked){ ids.forEach(id=>state.selected.add(id)); }
            else { ids.forEach(id=>state.selected.delete(id)); }
            render();
        });}
        container.querySelectorAll('.row-chk').forEach(inp=>{
            inp.addEventListener('change', function(){
                const id = this.closest('tr').dataset.id;
                if(this.checked) state.selected.add(id); else state.selected.delete(id);
                updateSelectionCount();
            });
        });
        container.querySelectorAll('button[data-action]').forEach(btn=>{
            btn.addEventListener('click', function(){
                const tr = this.closest('tr');
                const id = tr.dataset.id;
                const row = state.rows.find(x=>x.id===id);
                const action = this.getAttribute('data-action');
                if(action==='detalhes') showDetails(row);
                if(action==='preparar') notify('A geração de PDF será integrada em breve.', 'info');
            });
        });

        updateSelectionCount();
    }

    function updateSelectionCount(){
        const el = document.getElementById('selection-count');
        if(el) el.textContent = `${state.selected.size} selecionados`;
    }

    function showDetails(row){
        const body = document.getElementById('extract-details-body');
        if(!body) return;
        body.innerHTML = `
            <div class="details-header">
                <h4>${escapeHtml(row.userName)} - ${escapeHtml(monthName(row.month))}</h4>
                <div class="summary-stats">
                    <div class="stat"><span class="stat-label">Dias Trabalhados:</span><span class="stat-value">${row.dias}</span></div>
                    <div class="stat"><span class="stat-label">Horas Totais:</span><span class="stat-value">${Number(row.horasTotais).toFixed(1)}h</span></div>
                    <div class="stat"><span class="stat-label">Horas Extra:</span><span class="stat-value">${fmtMin(row.horasExtraMin)}</span></div>
                    <div class="stat"><span class="stat-label">KM Total:</span><span class="stat-value">${row.km||0} km</span></div>
                </div>
            </div>
            <div style="padding:0.75rem 0;color:#64748b;">Pré-visualização do PDF e calendário detalhado serão adicionados nesta área.</div>
        `;
        const modal = document.getElementById('extract-details-modal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function notify(message, type='info'){
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<div class="toast-content"><span class="toast-icon">${type==='success'?'✓':type==='error'?'✗':'ℹ'}</span><span class="toast-message">${escapeHtml(message)}</span></div>`;
        document.body.appendChild(toast);
        setTimeout(()=>toast.remove(), 3000);
    }

    function escapeHtml(s){ return (s==null?'':String(s)).replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c])); }

    // Bind top buttons
    function bindTopActions(){
        const btnPdf = document.getElementById('btn-generate-pdf');
        const btnZip = document.getElementById('btn-export-zip');
        const btnMark = document.getElementById('btn-mark-extracted');
        const btnLocal = document.getElementById('btn-load-local');
        const btnRefresh = document.getElementById('btn-refresh-extract');
        const search = document.getElementById('extract-search');
        const monthSel = document.getElementById('extract-month');

        if(search) search.addEventListener('input', render);
        if(monthSel) monthSel.addEventListener('change', render);
        if(btnLocal) btnLocal.addEventListener('click', ()=>{ loadFromLocalStorage(); notify('Registos carregados do navegador.', 'success'); });
    if(btnRefresh) btnRefresh.addEventListener('click', ()=>{ syncFromServer(); });
        if(btnPdf) btnPdf.addEventListener('click', ()=>{ if(!state.selected.size) return notify('Selecione pelo menos um registo.', 'error'); notify('Geração de PDFs em construção.', 'info'); });
        if(btnZip) btnZip.addEventListener('click', ()=>{ if(!state.selected.size) return notify('Selecione pelo menos um registo.', 'error'); notify('Exportação ZIP em construção.', 'info'); });
        if(btnMark) btnMark.addEventListener('click', ()=>{
            if(!state.selected.size) return notify('Selecione pelo menos um registo.', 'error');
            state.selected.forEach(id=> state.extracted.add(id));
            saveExtractedSet();
            render();
            notify('Registos marcados como extraídos.', 'success');
        });
    }

    // Inicialização
    populateMonths();
    loadExtractedSet();
    bindTopActions();
    // Carrega primeiro localStorage (fallback) e em seguida sincroniza do servidor
    try { loadFromLocalStorage(); } catch(_) { render(); }
    try { syncFromServer(); } catch(_) {}
})();
</script>
