document.addEventListener("DOMContentLoaded", function () {
    const links = document.querySelectorAll(".sidebar-menu a, .card-link");
    const mainContent = document.getElementById("main-content");

    // Função para mostrar loading
    function showLoading() {
        mainContent.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
        `;
    }

    // Badge de pendentes (avaliador)
    async function updatePendingBadge(){
        try{
            const link = document.querySelector('.sidebar-menu a[data-content="aprovacao_ferias_ausencias"]');
            if(!link) return;
            const resp = await fetch('../../api/leaves/aval_summary.php', { credentials: 'include' });
            if(!resp.ok) return;
            const data = await resp.json();
            if(!data || data.ok!==true || typeof data.pending !== 'number') return;
            let badge = link.querySelector('.pending-badge');
            if(!badge){
                badge = document.createElement('span');
                badge.className = 'pending-badge';
                badge.style.cssText = 'margin-left:8px;display:inline-flex;min-width:18px;height:18px;padding:0 6px;border-radius:9px;background:#ef4444;color:#fff;font-size:12px;line-height:18px;align-items:center;justify-content:center;font-weight:700;';
                link.appendChild(badge);
            }
            badge.textContent = String(data.pending);
            badge.style.display = data.pending>0 ? 'inline-flex' : 'none';
        }catch(_){/* ignore */}
    }

    // Função para resetar à página inicial
    function showWelcome() {
        mainContent.innerHTML = `
            <div class="main-header">
                <h2>Bem-vindo, ${document.querySelector('.sidebar-header h3').textContent}!</h2>
                <p>Gerencie pedidos de férias, operadores e consulte informações importantes do RH.</p>
            </div>
            
            <div class="welcome-content">
                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20,6 9,17 4,12"></polyline>
                        </svg>
                    </div>
                    <h3>Aprovação de Férias/Ausências</h3>
                    <p>Aprove ou rejeite pedidos de férias e ausências dos operadores sob sua supervisão.</p>
                    <a href="#" class="card-link" data-content="aprovacao_ferias_ausencias">
                        Gerir Aprovações
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12,6 12,12 16,14"></polyline>
                        </svg>
                    </div>
                    <h3>Aprovação de Horários</h3>
                    <p>Aprove ou rejeite as marcações de horários dos operadores da sua equipa.</p>
                    <a href="#" class="card-link" data-content="aprovacao_horarios">
                        Gerir Horários
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </a>
                </div>
                
                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Lista de Operadores</h3>
                    <p>Visualize e gerencie informações dos operadores da sua equipa.</p>
                    <a href="#" class="card-link" data-content="lista_operadores">
                        Ver Operadores
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </a>
                </div>
                
                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <h3>Consulta de Pedidos</h3>
                    <p>Consulte o histórico de todos os pedidos processados e os seus próprios pedidos.</p>
                    <a href="#" class="card-link" data-content="consulta_pedidos">
                        Ver Histórico
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <h3>Férias e Ausências</h3>
                    <p>Solicite as suas próprias férias e ausências e acompanhe o estado dos pedidos.</p>
                    <a href="#" class="card-link" data-content="ferias">
                        Gerir Pedidos
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12,6 12,12 16,14"></polyline>
                        </svg>
                    </div>
                    <h3>Consulta de Horários</h3>
                    <p>Visualize os seus horários de trabalho e eventos importantes do calendário.</p>
                    <a href="#" class="card-link" data-content="horarios">
                        Ver Horários
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h3>A Minha Ficha</h3>
                    <p>Consulte e atualize os seus dados pessoais e informações de contacto.</p>
                    <a href="#" class="card-link" data-content="ficha_colaborador">
                        Ver Ficha
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        `;

        // Reaplicar event listeners aos novos card-links
    attachCardLinkListeners();
    updatePendingBadge();
    }

    // Função para anexar listeners aos card-links
    function attachCardLinkListeners() {
        const cardLinks = document.querySelectorAll(".card-link");
        cardLinks.forEach(link => {
            link.addEventListener("click", handleNavigation);
        });
    updatePendingBadge();
    }

    // Função principal de navegação
    function handleNavigation(e) {
        e.preventDefault();
        const content = this.getAttribute("data-content");

        // Atualizar sidebar ativa
        document.querySelectorAll(".sidebar-menu li").forEach(li => li.classList.remove("active"));
        const sidebarLink = document.querySelector(`.sidebar-menu a[data-content="${content}"]`);
        if (sidebarLink) {
            sidebarLink.parentElement.classList.add("active");
        }

        // Mostrar loading
        showLoading();

        let url = "";
        switch (content) {
            case "inicio":
                setTimeout(showWelcome, 300);
                return;
            case "horarios":
                url = "../inter2/horarios_new.php";
                break;
            case "aprovacao_ferias_ausencias":
                url = "../inter2/aprovacao_ferias_ausencias.php";
                break;
            case "aprovacao_horarios":
                url = "../inter2/aprovacao_horarios.php";
                break;
            case "consulta_pedidos":
                url = "../inter2/consulta_pedidos.php";
                break;
            case "lista_operadores":
                url = "../inter2/lista_operadores.php";
                break;
            case "ficha_colaborador":
                url = "../inter2/ficha_colaborador.php";
                break;
            case "ferias":
                url = "../inter2/ferias_ausencias.php";
                break;
            default:
                setTimeout(showWelcome, 300);
                return;
        }

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error("Erro ao carregar o módulo.");
                return response.text();
            })
            .then(html => {
                mainContent.innerHTML = html;

                // Inicializar funcionalidades dinâmicas após carregar conteúdo
                window.initializeDynamicContent();

                // Garantir que o script de aprovação de férias/ausências está carregado quando este módulo é aberto
                if (content === 'aprovacao_ferias_ausencias') {
                    (function ensureLeavesApproval(){
                        try { console.debug('inter2: ensuring leaves_approval.js'); } catch(_) {}
                        if (!window.__leavesApprovalInit) {
                            const s = document.createElement('script');
                            s.src = '/js/leaves_approval.js?v=20250905';
                            s.async = true;
                            s.onload = function(){ try { console.debug('leaves_approval: loaded via dashboard'); } catch(_) {} };
                            s.onerror = function(){ console.error('Falha ao carregar /js/leaves_approval.js'); };
                            document.body.appendChild(s);
                            // Fallback: se após um curto período ainda não inicializou, ligar handlers mínimos aqui
                            setTimeout(function(){
                                if (!window.__leavesApprovalInit) {
                                    try { console.warn('leaves_approval: fallback binder active'); } catch(_) {}
                                    const onClick = async function(ev){
                                        const t = ev.target && ev.target.closest && ev.target.closest('.js-approve[data-pedido-id], .js-reject[data-pedido-id]');
                                        if (!t) return;
                                        const id = t.getAttribute('data-pedido-id');
                                        if (!id) return;
                                        if (t.dataset.processing === '1') return;
                                        const isApprove = t.classList.contains('js-approve');
                                        const proceed = window.confirm((isApprove?'Aprovar':'Rejeitar') + ' pedido\n\nTem certeza que deseja ' + (isApprove?'aprovar?':'rejeitar?'));
                                        if (!proceed) return;
                                        let comentario = undefined;
                                        if (!isApprove) {
                                            const c = window.prompt('Motivo da rejeição\n\nIndique o motivo da rejeição (obrigatório):');
                                            if (c === null) return; // cancel
                                            comentario = String(c||'').trim();
                                            if (!comentario) { if (window.showToast) showToast('error','Motivo de rejeição é obrigatório.'); else alert('Motivo de rejeição é obrigatório.'); return; }
                                        }
                                        t.dataset.processing = '1';
                                        if (window.showToast) showToast('info','A enviar decisão...');
                                        try{
                                            const resp = await fetch('/api/leaves/decision.php', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'Accept':'application/json' },
                                                credentials: 'same-origin',
                                                body: JSON.stringify({ pedido_id: Number(id), acao: isApprove?'aprovar':'rejeitar', comentario })
                                            });
                                            const ct = resp.headers.get('content-type')||'';
                                            let data = null;
                                            if (ct.includes('application/json')) data = await resp.json(); else { const txt=await resp.text(); try{data=JSON.parse(txt);}catch{data={ok:false,error:txt||('HTTP '+resp.status)}} }
                                            if (!resp.ok || !data || data.ok!==true){
                                                const code = data && (data.code||data.error||data.message||data.msg);
                                                if (code === 'LEAVE_CONFLICT_DAYS' && Array.isArray(data.dates) && data.dates.length){
                                                    const list = data.dates.join(', ');
                                                    if (window.showToast) showToast('error','Já existe uma ausência aprovada para: '+list); else alert('Conflito: '+list);
                                                } else {
                                                    const msg = code || ('HTTP '+resp.status);
                                                    if (window.showToast) showToast('error','Falha ao processar decisão: '+msg); else alert('Erro: '+msg);
                                                }
                                                return;
                                            }
                                            if (window.showToast) showToast('success','Decisão efetuada com sucesso.');
                                            setTimeout(()=>window.location.reload(), 900);
                                        }catch(e){
                                            if (window.showToast) showToast('error','Erro inesperado: '+(e && e.message ? e.message : e)); else alert('Erro: '+e);
                                        } finally {
                                            delete t.dataset.processing;
                                        }
                                    };
                                    document.addEventListener('click', onClick, { capture: true });
                                }
                            }, 500);
                        } else {
                            try { console.debug('leaves_approval: already initialized'); } catch(_) {}
                        }
                    })();
                    // Fallback globals for inline onclick
                    window.approveLeave = async function(id){
                        try { if (window.__leavesApprovalInit) return; } catch(_) {}
                        const ok = window.confirm('Aprovar pedido\n\nTem certeza que deseja aprovar este pedido?');
                        if (!ok) return;
                        // Pre-check conflicts using aval_requests + get_month
                        try{
                            const ar = await fetch('/api/leaves/aval_requests.php', { credentials:'same-origin' });
                            if (ar.ok){
                                const data = await ar.json();
                                const item = data && Array.isArray(data.items) ? data.items.find(it => Number(it.pedido_id)===Number(id)) : null;
                                if (item && item.colaborador && item.colaborador.id && item.inicio && item.fim){
                                    const userId = Number(item.colaborador.id);
                                    const start = String(item.inicio).slice(0,10);
                                    const end   = String(item.fim).slice(0,10);
                                    const toDate = s => { const [y,m,d] = s.split('-').map(Number); return new Date(y,m-1,d); };
                                    const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
                                    const months=[]; const cs=new Date(toDate(start).getFullYear(), toDate(start).getMonth(), 1); const ce=new Date(toDate(end).getFullYear(), toDate(end).getMonth(), 1);
                                    while(cs<=ce){ months.push(`${cs.getFullYear()}-${String(cs.getMonth()+1).padStart(2,'0')}`); cs.setMonth(cs.getMonth()+1); }
                                    const results = await Promise.all(months.map(m => fetch(`/api/calendar/get_month.php?month=${encodeURIComponent(m)}&user_id=${encodeURIComponent(userId)}`, { credentials:'same-origin' }).then(r=>r.ok?r.json():null).catch(()=>null)));
                                    const conflictMap = Object.create(null);
                                    results.forEach(res=>{ if(res&&res.ok&&Array.isArray(res.days)){ res.days.forEach(d=>{ if (Array.isArray(d.leaves)&&d.leaves.length){ const dateKey=String(d.date); const conflict=d.leaves.some(lv=>{ const rid=typeof lv.requestId==='number'?lv.requestId:(lv.requestId?Number(lv.requestId):null); return rid===null || rid!==Number(id); }); if (conflict) conflictMap[dateKey]=true; } }); } });
                                    const sDate=toDate(start), eDate=toDate(end); const conflicts=[]; for(let cur=new Date(sDate); cur<=eDate; cur.setDate(cur.getDate()+1)){ const key=fmt(cur); if (conflictMap[key]) conflicts.push(key); }
                                    if (conflicts.length){
                                        const override = window.confirm('Foram detetadas ausências já aprovadas em:\n\n'+conflicts.join(', ')+'\n\nPretende aprovar o pedido mesmo assim?');
                                        if (!override) return;
                                    }
                                }
                            }
                        }catch(_){ /* ignore */ }
                        if (window.showToast) showToast('info','A enviar decisão...');
                        try{
                            const resp = await fetch('/api/leaves/decision.php', {
                                method: 'POST', headers: { 'Content-Type': 'application/json','Accept':'application/json' }, credentials: 'same-origin', body: JSON.stringify({ pedido_id:Number(id), acao:'aprovar' })
                            });
                            const ct = resp.headers.get('content-type')||''; let data=null;
                            if (ct.includes('application/json')) data = await resp.json(); else { const txt=await resp.text(); try{ data=JSON.parse(txt);}catch{ data={ ok:false, error:txt||('HTTP '+resp.status) }; } }
                            if (!resp.ok || !data || data.ok!==true){ const code=(data&&(data.code||data.error||data.message||data.msg))||('HTTP '+resp.status); const msgTxt=(data&&(data.msg||data.error||data.message))||''; const isDup=String(code).includes('DB_ERROR') && /Duplicate entry|uq_evento_user_tipo_dia/i.test(msgTxt||''); if (isDup){ if (window.showToast) showToast('success','Pedido aprovado (evento já existia no calendário).'); setTimeout(()=>window.location.reload(),900); return; } if (code==='LEAVE_CONFLICT_DAYS' && Array.isArray(data.dates)&&data.dates.length){ const list=data.dates.join(', '); if (window.showToast) showToast('error','Já existe uma ausência aprovada para: '+list); else alert('Conflito: '+list);} else { if (window.showToast) showToast('error','Falha: '+code); else alert('Erro: '+code);} return; }
                            if (window.showToast) showToast('success','Decisão efetuada com sucesso.'); setTimeout(()=>window.location.reload(), 900);
                        }catch(e){ if (window.showToast) showToast('error','Erro: '+(e&&e.message?e.message:e)); else alert('Erro: '+e); }
                    };
                    window.rejectLeave = async function(id){
                        try { if (window.__leavesApprovalInit) return; } catch(_) {}
                        const ok = window.confirm('Rejeitar pedido\n\nTem certeza que deseja rejeitar este pedido?');
                        if (!ok) return;
                        const c = window.prompt('Motivo da rejeição\n\nIndique o motivo da rejeição (obrigatório):');
                        if (c === null) return; const comentario = String(c||'').trim(); if (!comentario){ if (window.showToast) showToast('error','Motivo de rejeição é obrigatório.'); else alert('Motivo de rejeição é obrigatório.'); return; }
                        if (window.showToast) showToast('info','A enviar decisão...');
                        try{
                            const resp = await fetch('/api/leaves/decision.php', {
                                method: 'POST', headers: { 'Content-Type': 'application/json','Accept':'application/json' }, credentials: 'same-origin', body: JSON.stringify({ pedido_id:Number(id), acao:'rejeitar', comentario })
                            });
                            const ct = resp.headers.get('content-type')||''; let data=null;
                            if (ct.includes('application/json')) data = await resp.json(); else { const txt=await resp.text(); try{ data=JSON.parse(txt);}catch{ data={ ok:false, error:txt||('HTTP '+resp.status) }; } }
                            if (!resp.ok || !data || data.ok!==true){ const code=(data&&(data.code||data.error||data.message||data.msg))||('HTTP '+resp.status); if (code==='LEAVE_CONFLICT_DAYS' && Array.isArray(data.dates)&&data.dates.length){ const list=data.dates.join(', '); if (window.showToast) showToast('error','Já existe uma ausência aprovada para: '+list); else alert('Conflito: '+list);} else { if (window.showToast) showToast('error','Falha: '+code); else alert('Erro: '+code);} return; }
                            if (window.showToast) showToast('success','Decisão efetuada com sucesso.'); setTimeout(()=>window.location.reload(), 900);
                        }catch(e){ if (window.showToast) showToast('error','Erro: '+(e&&e.message?e.message:e)); else alert('Erro: '+e); }
                    };
                }

                // Executar scripts inline da página de aprovação de horários apenas uma vez (evitar redeclarações)
                (function executePageScriptsOnce(container, content) {
                    if (content === 'aprovacao_horarios') {
                        if (!window.__aprovacao_horarios_inline_loaded__) {
                            const inlineScripts = Array.from(container.querySelectorAll('script:not([src])'));
                            inlineScripts.forEach(oldScript => {
                                const newScript = document.createElement('script');
                                newScript.textContent = oldScript.textContent;
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });
                            window.__aprovacao_horarios_inline_loaded__ = true;
                        }
                    }
                    // Nunca reexecutar scripts com src para evitar "already been declared" (integracoes/dados)
                })(mainContent, content);

                // Tratamento especial para a página de horários (calendário)
                if (content === "horarios") {
                    if (typeof window.initializeHorariosCalendarInter2 === 'function') {
                        window.initializeHorariosCalendarInter2();
                    } else {
                        initializeCalendar();
                    }
                }

                // Caso seja a página de aprovação de horários, se a função global existir, força um refresh inicial
                if (content === 'aprovacao_horarios') {
                    // Inicializa a instância e cria shims globais caso necessários
                    const initScript = document.createElement('script');
                    initScript.textContent = `
                        try {
                            // Garante instância
                            if (typeof AprovacaoHorarios === 'function') {
                                if (typeof aprovacaoHorarios === 'undefined' || !aprovacaoHorarios) {
                                    aprovacaoHorarios = new AprovacaoHorarios();
                                }
                                if (typeof aprovacaoHorarios.refresh === 'function') {
                                    aprovacaoHorarios.refresh();
                                }
                            }
                            // Ligar pesquisa por texto sem depender do DOMContentLoaded da página injetada
                            (function(){
                                var si = document.getElementById('search-input');
                                if (si && !si.__aprov_search_bound__) {
                                    si.addEventListener('input', function(){ try { if (aprovacaoHorarios) aprovacaoHorarios.renderApprovals(); } catch(e){} });
                                    si.__aprov_search_bound__ = true;
                                }
                            })();
                            // Shims de funções globais caso não existam
                            if (typeof filterApprovals !== 'function') {
                                window.filterApprovals = function(filter){
                                    try {
                                        if (typeof aprovacaoHorarios === 'undefined' || !aprovacaoHorarios) {
                                            if (typeof AprovacaoHorarios === 'function') aprovacaoHorarios = new AprovacaoHorarios();
                                        }
                                        if (aprovacaoHorarios) {
                                            aprovacaoHorarios.currentFilter = filter;
                                            if (typeof aprovacaoHorarios.refresh === 'function') aprovacaoHorarios.refresh();
                                        }
                                    } catch(e){}
                                };
                            }
                            if (typeof filterByMonth !== 'function') {
                                window.filterByMonth = function(){
                                    try { if (aprovacaoHorarios) aprovacaoHorarios.renderApprovals(); } catch(e){}
                                };
                            }
                            if (typeof showApprovalDetails !== 'function') {
                                window.showApprovalDetails = function(id){ try { if (aprovacaoHorarios) aprovacaoHorarios.showApprovalDetails(id);} catch(e){} };
                            }
                            if (typeof approveMarking !== 'function') {
                                window.approveMarking = function(id){ try { if (aprovacaoHorarios) aprovacaoHorarios.approveMarking(id);} catch(e){} };
                            }
                            if (typeof rejectMarking !== 'function') {
                                window.rejectMarking = function(id){ try { if (aprovacaoHorarios) aprovacaoHorarios.rejectMarking(id);} catch(e){} };
                            }
                            if (typeof approveMarkingFromModal !== 'function') {
                                window.approveMarkingFromModal = function(id){ try { if (aprovacaoHorarios) aprovacaoHorarios.approveMarking(id); closeDetailsModal(); } catch(e){} };
                            }
                            if (typeof rejectMarkingFromModal !== 'function') {
                                window.rejectMarkingFromModal = function(id){ try { closeDetailsModal(); if (aprovacaoHorarios) aprovacaoHorarios.rejectMarking(id);} catch(e){} };
                            }
                            if (typeof closeDetailsModal !== 'function') {
                                window.closeDetailsModal = function(){ try { document.getElementById('details-modal').style.display='none'; document.body.style.overflow=''; } catch(e){} };
                            }
                            if (typeof closeRejectionModal !== 'function') {
                                window.closeRejectionModal = function(){ try { document.getElementById('rejection-modal').style.display='none'; var t=document.getElementById('rejection-reason'); if(t) t.value=''; document.body.style.overflow=''; } catch(e){} };
                            }
                            if (typeof confirmRejection !== 'function') {
                                window.confirmRejection = function(){ try { if (aprovacaoHorarios) aprovacaoHorarios.confirmRejection(); } catch(e){} };
                            }
                        } catch (e) { /* noop */ }
                    `;
                    mainContent.appendChild(initScript);
                }

                // Tratamento especial para a página de ficha colaborador
                if (content === "ficha_colaborador") {
                    initializeFichaColaborador();
                }
            })
            .catch(error => {
                mainContent.innerHTML = `
                    <div class="error-state">
                        <h3>Erro ao carregar conteúdo</h3>
                        <p>${error.message}</p>
                        <button onclick="location.reload()" class="btn-retry">Tentar Novamente</button>
                    </div>
                `;
            });
    }

    // Função para inicializar o calendário
    function initializeCalendar() {
        // Verificar se FullCalendar já está carregado
        if (typeof FullCalendar === "undefined") {
            // Carregar CSS do FullCalendar se não estiver carregado
            if (!document.querySelector('link[href*="fullcalendar"]')) {
                const cssLink = document.createElement("link");
                cssLink.rel = "stylesheet";
                cssLink.href = "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css";
                document.head.appendChild(cssLink);
            }

            // Carregar JavaScript do FullCalendar
            const script = document.createElement("script");
            script.src = "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js";
            script.onload = function() {
                renderCalendar();
            };
            document.body.appendChild(script);
        } else {
            renderCalendar();
        }
    }

    // Função para renderizar o calendário
    function renderCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    week: 'Semana',
                    day: 'Dia'
                },
                height: 'auto',
                events: function(fetchInfo, successCallback, failureCallback) {
                    // Carregar eventos (férias/ausências) via calendar/get_month para todos os meses no intervalo
                    try {
                        const start = new Date(fetchInfo.startStr);
                        const end = new Date(fetchInfo.endStr);
                        const months = [];
                        const cursor = new Date(start.getFullYear(), start.getMonth(), 1);
                        while (cursor <= end) {
                            const ym = `${cursor.getFullYear()}-${String(cursor.getMonth() + 1).padStart(2, '0')}`;
                            if (!months.includes(ym)) months.push(ym);
                            cursor.setMonth(cursor.getMonth() + 1);
                        }
                        Promise.all(
                            months.map(m => fetch(`../../api/calendar/get_month.php?month=${encodeURIComponent(m)}`, { credentials: 'same-origin' }).then(r => r.json()).catch(() => null))
                        ).then(results => {
                            const events = [];
                            results.forEach(res => {
                                if (res && res.ok && Array.isArray(res.days)) {
                                    res.days.forEach(d => {
                                        const leaves = Array.isArray(d.leaves) ? d.leaves : [];
                                        if (leaves.length > 0) {
                                            leaves.forEach(lv => {
                                                const title = lv.label || lv.titulo || lv.title || (lv.tipo === 'ferias' || lv.type === 'vacation' ? 'Férias' : 'Ausência');
                                                events.push({ title, start: d.date, allDay: true });
                                            });
                                        }
                                    });
                                }
                            });
                            successCallback(events);
                        }).catch(err => {
                            console.error('Erro ao carregar eventos do calendário:', err);
                            failureCallback(err);
                        });
                    } catch (e) {
                        console.error('Erro inesperado ao preparar eventos:', e);
                        failureCallback(e);
                    }
                },
                eventDisplay: 'block',
                eventClick: function(info) {
                    alert('Evento: ' + info.event.title + '\nData: ' + info.event.start.toLocaleDateString());
                }
            });

            calendar.render();
        }
    }

    // Função para inicializar funcionalidades da ficha colaborador
    function initializeFichaColaborador() {
        const editarLink = document.getElementById('editar_ficha_colaborador');
        if (editarLink) {
            editarLink.addEventListener('click', function(e) {
                e.preventDefault();

                // Mostrar loading
                showLoading();

                // Carregar página de edição
                fetch('../inter2/editar_ficha_colaborador.php')
                    .then(response => response.text())
                    .then(html => {
                        mainContent.innerHTML = html;
                        initializeEditForm();
                    })
                    .catch(error => {
                        console.error('Erro ao carregar edição:', error);
                        mainContent.innerHTML = '<p>Erro ao carregar formulário de edição.</p>';
                    });
            });
        }
    }

    // Função para inicializar o formulário de edição
    function initializeEditForm() {
        const form = document.querySelector('form[action*="i2_ficha_colaborador"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch(this.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pedido de alteração submetido com sucesso! Aguarde aprovação.');
                        // Voltar para a ficha
                        handleNavigationDirect('ficha_colaborador');
                    } else {
                        alert('Erro: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao submeter pedido.');
                });
            });
        }
    }

    // Função auxiliar para navegação direta
    function handleNavigationDirect(content) {
        document.querySelectorAll(".sidebar-menu li").forEach(li => li.classList.remove("active"));
        const sidebarLink = document.querySelector(`.sidebar-menu a[data-content="${content}"]`);
        if (sidebarLink) {
            sidebarLink.parentElement.classList.add("active");
        }

        showLoading();

        let url = "";
        switch (content) {
            case "ficha_colaborador":
                url = "../inter2/ficha_colaborador.php";
                break;
            default:
                showWelcome();
                return;
        }

        fetch(url)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
                if (content === "ficha_colaborador") {
                    initializeFichaColaborador();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mainContent.innerHTML = '<p>Erro ao carregar conteúdo.</p>';
            });
    }

    // Adicionar event listeners iniciais
    links.forEach(link => {
        link.addEventListener("click", handleNavigation);
    });

    // Adicionar estilos para estados de erro e loading
    if (!document.querySelector('#dashboard-styles')) {
        const style = document.createElement('style');
        style.id = 'dashboard-styles';
        style.textContent = `
            .loading-spinner {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 200px;
            }

            .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #e2e8f0;
                border-top-color: var(--light-blue);
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            .error-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 4rem 2rem;
                text-align: center;
                background: white;
                border-radius: 16px;
                border: 1px solid #e2e8f0;
            }

            .error-state h3 {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--navy-blue);
                margin-bottom: 0.5rem;
            }

            .error-state p {
                color: var(--text-light);
                margin-bottom: 2rem;
            }

            .btn-retry {
                padding: 0.75rem 1.5rem;
                background: 'linear-gradient(135deg, var(--gradient-1), var(--gradient-2))';
                color: white;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .btn-retry:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
            }
        `;
        document.head.appendChild(style);
    }
});

// Funções globais para serem usadas pelos módulos carregados dinamicamente
window.abrirModalPedido = function() {
    const modal = document.getElementById('modalPedido');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
};

window.fecharModalPedido = function() {
    const modal = document.getElementById('modalPedido');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';

        // Reset form
        const form = document.querySelector('.modal-form');
        if (form) {
            form.reset();
            const comprovantivoStatus = document.getElementById('comprovativo-status');
            const fileContent = document.querySelector('.file-input-content span');

            if (comprovantivoStatus) {
                comprovantivoStatus.textContent = '(opcional)';
                comprovantivoStatus.classList.add('optional');
                comprovantivoStatus.style.color = '';
            }

            if (fileContent) {
                fileContent.textContent = 'Clique para selecionar ficheiro';
            }

            const ficheiro = document.getElementById('ficheiro');
            if (ficheiro) {
                ficheiro.required = false;
            }
        }
    }
};

window.verFichaCompleta = function(userId) {
    const modal = document.getElementById('fichaModal');
    const conteudo = document.getElementById('fichaConteudo');

    if (!modal || !conteudo) {
        console.error('Modal elements not found');
        return;
    }

    // Show loading
    conteudo.innerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; height: 200px;">
            <div style="width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: var(--light-blue); border-radius: 50%; animation: spin 1s linear infinite;"></div>
        </div>
    `;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Load content
    fetch(`../inter2/visualizar_lista_operadores.php?user_id=${userId}`)
        .then(response => {
            if (!response.ok) throw new Error('Erro na resposta do servidor');
            return response.text();
        })
        .then(html => {
            conteudo.innerHTML = html;
        })
        .catch(error => {
            console.error('Erro ao carregar ficha:', error);
            conteudo.innerHTML = '<p>Erro ao carregar a ficha do colaborador.</p>';
        });
};

window.fecharModal = function() {
    const modal = document.getElementById('fichaModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
};

// Função para inicializar eventos após carregamento dinâmico de conteúdo
window.initializeDynamicContent = function() {
    // Garantir infra de toast disponível
    (function ensureToastInfra(){
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
            .toast-container{position:fixed;top:16px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:8px}
            .toast{min-width:260px;max-width:420px;padding:12px 14px;border-radius:10px;color:#0b1220;background:#0b1220;box-shadow:0 6px 16px rgba(0,0,0,.18);display:flex;align-items:flex-start;gap:10px;opacity:0;transform:translateY(-6px);animation:toast-in .2s ease forwards}
            .toast.success{background:linear-gradient(135deg,#10b981,#34d399);color:#062d1f}
            .toast.error{background:linear-gradient(135deg,#ef4444,#f59e0b);color:#2b0b0b}
            .toast.info{background:linear-gradient(135deg,#3e84f2,#7aa8f9);color:#041935}
            .toast .t-icon{font-size:18px;line-height:18px;margin-top:2px}
            .toast .t-msg{flex:1;font-weight:600}
            .toast .t-close{background:transparent;border:none;color:inherit;cursor:pointer;font-size:16px;opacity:.8}
            @keyframes toast-in{to{opacity:1;transform:translateY(0)}}
            @keyframes toast-out{to{opacity:0;transform:translateY(-6px)}}`;
            document.head.appendChild(style);
        }
        if (!document.querySelector('.toast-container')){
            const c = document.createElement('div');
            c.className = 'toast-container';
            document.body.appendChild(c);
        }
        if (!window.showToast){
            window.showToast = function(type, message, opts={}){
                const container = document.querySelector('.toast-container');
                const t = document.createElement('div');
                t.className = `toast ${type||'info'}`;
                const icon = type==='success'?'✓':type==='error'?'✗':'ℹ';
                t.innerHTML = `<span class="t-icon">${icon}</span><div class="t-msg">${message}</div><button class="t-close" aria-label="Fechar">×</button>`;
                container.appendChild(t);
                const ttl = Number(opts.duration||2500);
                const close = ()=>{ t.style.animation = 'toast-out .18s ease forwards'; setTimeout(()=>t.remove(), 200); };
                t.querySelector('.t-close').addEventListener('click', close);
                setTimeout(close, ttl);
                return t;
            };
        }
    })();
    // Inicializar filtros de férias/ausências
    const filterBtns = document.querySelectorAll('.filter-btn');
    const pedidoCards = document.querySelectorAll('.pedido-card');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');

            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Filter cards
            pedidoCards.forEach(card => {
                const estado = card.getAttribute('data-estado');

                if (filter === 'all') {
                    card.classList.remove('hidden');
                } else if (estado === filter) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });

    // Inicializar pesquisa de operadores
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            // Filter cards
            const cards = document.querySelectorAll('.operador-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const email = card.getAttribute('data-email');

                if (name && email) {
                    if (name.includes(searchTerm) || email.includes(searchTerm)) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                }
            });

            // Filter table rows
            const rows = document.querySelectorAll('.table-row');
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');

                if (name && email) {
                    if (name.includes(searchTerm) || email.includes(searchTerm)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                }
            });
        });
    }

    // Inicializar toggle de views
    const viewBtns = document.querySelectorAll('.view-btn');
    const cardsView = document.getElementById('cards-view');
    const tableView = document.getElementById('table-view');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');

            // Update active button
            viewBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Toggle views
            if (view === 'cards') {
                if (cardsView) cardsView.style.display = 'grid';
                if (tableView) tableView.style.display = 'none';
            } else {
                if (cardsView) cardsView.style.display = 'none';
                if (tableView) tableView.style.display = 'block';
            }
        });
    });

    // Inicializar dropdown de tipo de pedido
    const tipoSelect = document.getElementById('tipo');
    const comprovantivoStatus = document.getElementById('comprovativo-status');
    const ficheiro = document.getElementById('ficheiro');

    if (tipoSelect && comprovantivoStatus) {
        tipoSelect.addEventListener('change', function() {
            const tiposComComprovativo = [
                'licenca_paternidade',
                'licenca_maternidade',
                'baixa_medica',
                'baixa_seguro',
                'casamento',
                'consulta_medica'
            ];

            if (tiposComComprovativo.includes(this.value)) {
                comprovantivoStatus.textContent = '(obrigatório)';
                comprovantivoStatus.classList.remove('optional');
                comprovantivoStatus.style.color = '#dc2626';
                if (ficheiro) ficheiro.required = true;
            } else {
                comprovantivoStatus.textContent = '(opcional)';
                comprovantivoStatus.classList.add('optional');
                comprovantivoStatus.style.color = '';
                if (ficheiro) ficheiro.required = false;
            }
        });
    }

    // Inicializar file input display
    const fileInput = document.getElementById('ficheiro');
    const fileContent = document.querySelector('.file-input-content span');
    const fileWrapper = document.querySelector('.file-input-wrapper');

    if (fileInput && fileContent) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileContent.textContent = this.files[0].name;
            } else {
                fileContent.textContent = 'Clique para selecionar ficheiro';
            }
        });
    }

    // Adicionar evento de clique na wrapper para abrir o file selector
    if (fileWrapper && fileInput) {
        fileWrapper.addEventListener('click', function() {
            fileInput.click();
        });
    }

    // Inicializar modal backdrop clicks
    const modalPedido = document.getElementById('modalPedido');
    if (modalPedido) {
        modalPedido.addEventListener('click', function(e) {
            if (e.target === this) {
                window.fecharModalPedido();
            }
        });
    }

    // Interceptar submissão do formulário de Férias/Ausências (quando carregado dinamicamente)
    try {
        if (!window.__leavesSubmitCapture__) {
            document.addEventListener('submit', function(ev){
                const form = ev.target;
                if (form && form.action && form.action.includes('/api/leaves/request.php')){
                    ev.preventDefault();
                    if (form.__leavesSubmitting) return;
                    form.__leavesSubmitting = true;
                    const fd = new FormData(form);
                    // Normalizar datas caso venham como dd/mm/yyyy
                    try {
                        const diEl = form.querySelector('#data_inicio');
                        const dfEl = form.querySelector('#data_fim');
                        const norm = v => (/^\d{2}\/\d{2}\/\d{4}$/.test(v) ? `${v.slice(6,10)}-${v.slice(3,5)}-${v.slice(0,2)}` : v);
                        if (diEl && diEl.value) fd.set('data_inicio', norm(diEl.value));
                        if (dfEl && dfEl.value) fd.set('data_fim', norm(dfEl.value));
                    } catch(_) {}
                    const submitBtn = form.querySelector('button[type="submit"], .btn-submit');
                    const originalText = submitBtn ? submitBtn.innerHTML : '';
                    if (submitBtn){ submitBtn.disabled = true; submitBtn.innerText = 'A enviar...'; }
                    fetch(form.action, { method:'POST', body: fd, credentials: 'same-origin' })
                        .then(async resp => {
                            const ct = resp.headers.get('content-type')||'';
                            let data=null;
                            if (ct.includes('application/json')) data = await resp.json();
                            else { const txt = await resp.text(); try{ data=JSON.parse(txt);}catch{ data={ ok:false, error:txt||'Erro ao processar resposta.'}; } }
                            if (!resp.ok || !data || data.ok===false){
                                const code = data && (data.code || data.error || data.message);
                                const codeMap = {
                                    MISSING_FIELDS: 'Preencha todos os campos obrigatórios.',
                                    INVALID_DATE: 'Data inválida.',
                                    RANGE_ERROR: 'Data de início deve ser anterior à data de fim.',
                                    DOC_REQUIRED: 'Este tipo exige comprovativo (PDF/JPG/PNG).',
                                    BAD_FILETYPE: 'Tipo de ficheiro inválido (PDF, JPG, PNG).',
                                    FILE_TOO_LARGE: 'Ficheiro maior que 5MB.',
                                    FILE_MOVE_ERROR: 'Erro ao guardar o ficheiro no servidor.',
                                    UNAUTHENTICATED: 'Sessão expirada. Faça login novamente.',
                                    FORBIDDEN_ROLE: 'Perfil sem permissão para criar pedidos.',
                                    DB_ERROR: 'Erro interno ao gravar o pedido.'
                                };
                                const friendly = codeMap[code] || (code ? String(code) : `HTTP ${resp.status}`);
                                showToast('error', `Falha ao submeter pedido: ${friendly}`);
                            } else {
                                showToast('success', 'Pedido submetido com sucesso.');
                                window.fecharModalPedido && window.fecharModalPedido();
                                setTimeout(()=>window.location.reload(), 1200);
                            }
                        })
                        .catch(err => showToast('error', `Erro inesperado: ${err && err.message ? err.message : err}`))
                        .finally(()=>{ if (submitBtn){ submitBtn.disabled=false; submitBtn.innerHTML = originalText || 'Submeter Pedido'; } form.__leavesSubmitting = false; });
                }
            }, true);
            window.__leavesSubmitCapture__ = true;
        }
    } catch (e) { /* noop */ }

    const fichaModal = document.getElementById('fichaModal');
    if (fichaModal) {
        fichaModal.addEventListener('click', function(e) {
            if (e.target === this) {
                window.fecharModal();
            }
        });
    }

    // Prevenir envio de form com Enter (exceto textarea)
    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    });

    // Inicializar filtros de aprovação
    const approvalFilterBtns = document.querySelectorAll('.filter-buttons .filter-btn');
    const requestCards = document.querySelectorAll('.request-card');

    approvalFilterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');

            // Update active button
            approvalFilterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Filter cards
            requestCards.forEach(card => {
                const cardType = card.getAttribute('data-type');

                if (filter === 'all') {
                    card.style.display = 'block';
                } else if (filter === 'ferias' && cardType === 'ferias') {
                    card.style.display = 'block';
                } else if (filter === 'baixa' && (cardType.includes('baixa') || cardType.includes('medica'))) {
                    card.style.display = 'block';
                } else if (filter === 'licenca' && cardType.includes('licenca')) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Toggle de consulta pedidos removido (sem separador Pessoais)
};

// Funções globais para compatibilidade
window.navigateMonthInter2 = navigateMonthInter2;
window.goToTodayInter2 = goToTodayInter2;
window.openModalInter2 = openModalInter2;
window.closeModalInter2 = closeModalInter2;

// Funções específicas para o sistema de horários do inter2
let currentMonthInter2 = new Date().getMonth();
let currentYearInter2 = new Date().getFullYear();
let horariosDataInter2 = {}; // mapa YYYY-MM-DD -> { horas_normais, horas_extra, horas_prevencao, km_viatura }
let feriasDataInter2 = {};
let inter2IsMonthLocked = false; // bloqueio de edição (submitted/aprovado/locked)

// Helpers de conversão
function minToHHMMInter2(min){
    const m = Math.max(0, parseInt(min || 0, 10));
    const h = Math.floor(m / 60);
    const r = m % 60;
    return `${String(h).padStart(2,'0')}:${String(r).padStart(2,'0')}`;
}
function hhmmToMinInter2(hhmm){
    if (!hhmm || typeof hhmm !== 'string') return 0;
    const match = hhmm.match(/^(\d{1,2}):(\d{2})$/);
    if (!match) return 0;
    const h = parseInt(match[1],10);
    const mi = parseInt(match[2],10);
    if (Number.isNaN(h) || Number.isNaN(mi)) return 0;
    return Math.max(0,h)*60 + Math.max(0,mi);
}

// Variáveis para seleção múltipla
let isBulkModeInter2 = false;
let selectedDaysInter2 = new Set();

// Função principal para inicializar o calendário de horários do inter2
window.initializeHorariosCalendarInter2 = function() {
    console.log('Sistema de horários inicializado para Inter2');
    console.log('Verificando se elementos existem...');
    
    const calendarGrid = document.querySelector('.calendar-grid');
    const monthYear = document.getElementById('monthYear');
    console.log('Calendar grid:', calendarGrid);
    console.log('Month year element:', monthYear);
    
    // Carregar todos os dados iniciais em paralelo e renderizar uma vez
    Promise.all([
        loadFeriasInter2(),
        loadHorariosDataInter2(),
        refreshMonthStatusInter2()
    ]).then(() => {
        try { renderCalendarInter2(); } catch (e) {}
    }).catch(() => {
        try { renderCalendarInter2(); } catch (e) {}
    });
    
    // Event listeners para navegação
    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');
    const todayBtn = document.getElementById('todayBtn');
    
    if (prevBtn) prevBtn.addEventListener('click', () => navigateMonthInter2(-1));
    if (nextBtn) nextBtn.addEventListener('click', () => navigateMonthInter2(1));
    if (todayBtn) todayBtn.addEventListener('click', goToTodayInter2);

    // Event listeners para o modal
    const modal = document.getElementById('horariosModal');
    const closeBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancelBtn');
    const clearDayBtn = document.getElementById('clearDayBtn');
    const saveBtn = document.getElementById('saveBtn');

    if (closeBtn) closeBtn.addEventListener('click', closeModalInter2);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModalInter2);
    if (clearDayBtn) clearDayBtn.addEventListener('click', clearDayDataInter2);
    if (saveBtn) saveBtn.addEventListener('click', saveHorariosInter2);

    // Event listeners para seleção múltipla
    const bulkSelectBtn = document.getElementById('bulkSelectBtn');
    const bulkModal = document.getElementById('bulkModal');
    const closeBulkBtn = document.querySelector('.close-bulk-modal');
    const cancelBulkBtn = document.getElementById('cancelBulkBtn');
    const clearRangeBtn = document.getElementById('clearRangeBtn');
    const applyBulkBtn = document.getElementById('applyBulkBtn');

    if (bulkSelectBtn) bulkSelectBtn.addEventListener('click', openBulkModalInter2);
    if (closeBulkBtn) closeBulkBtn.addEventListener('click', closeBulkModalInter2);
    if (cancelBulkBtn) cancelBulkBtn.addEventListener('click', closeBulkModalInter2);
    if (clearRangeBtn) clearRangeBtn.addEventListener('click', clearRangeInter2);
    if (applyBulkBtn) applyBulkBtn.addEventListener('click', applyBulkSelectionInter2);

    // Fechar modals ao clicar fora
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModalInter2();
        });
    }

    if (bulkModal) {
        bulkModal.addEventListener('click', (e) => {
            if (e.target === bulkModal) closeBulkModalInter2();
        });
    }

    // Dados do mês são carregados acima
};

// Carregar férias/ausências do mês atual (via calendar/get_month)
function loadFeriasInter2(renderNow = true) {
    const monthKey = `${currentYearInter2}-${String(currentMonthInter2 + 1).padStart(2, '0')}`;
    return fetch(`../../api/calendar/get_month.php?month=${encodeURIComponent(monthKey)}`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (!data || data.ok !== true || !Array.isArray(data.days)) {
                console.warn('Resposta inesperada de get_month.php', data);
                feriasDataInter2 = {};
                if (renderNow) renderCalendarInter2();
                return;
            }
            const map = {};
            data.days.forEach(d => {
                const leaves = Array.isArray(d.leaves) ? d.leaves : [];
                if (leaves.length > 0) {
                    const lv = leaves[0] || {};
                    map[d.date] = { tipo: lv.tipo || lv.type || 'ferias', label: lv.label || lv.titulo || lv.title || null };
                }
            });
            feriasDataInter2 = map;
            console.log('Férias/Ausências carregadas (calendar):', feriasDataInter2);
            if (renderNow) renderCalendarInter2();
        })
    .catch(err => { console.error('Erro ao carregar calendário/leaves:', err); feriasDataInter2 = {}; if (renderNow) renderCalendarInter2(); });
}

// Renderizar calendário do inter2
function renderCalendarInter2() {
    const monthNames = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    
    const dayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

    // Atualizar título
    const monthYearDisplay = document.getElementById('monthYear');
    if (monthYearDisplay) {
        monthYearDisplay.textContent = `${monthNames[currentMonthInter2]} ${currentYearInter2}`;
    }

    // Gerar calendário
    const calendarGrid = document.querySelector('.calendar-grid');
    if (!calendarGrid) return;

    // Limpar grid
    calendarGrid.innerHTML = '';

    // Adicionar cabeçalhos dos dias
    dayNames.forEach(day => {
        const dayHeader = document.createElement('div');
        dayHeader.className = 'calendar-day-header';
        dayHeader.textContent = day;
        calendarGrid.appendChild(dayHeader);
    });

    // Calcular dias do mês
    const firstDay = new Date(currentYearInter2, currentMonthInter2, 1);
    const lastDay = new Date(currentYearInter2, currentMonthInter2 + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    // Adicionar dias vazios do início
    for (let i = 0; i < startingDayOfWeek; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day empty';
        calendarGrid.appendChild(emptyDay);
    }

    // Adicionar dias do mês
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        
        const dateKey = `${currentYearInter2}-${String(currentMonthInter2 + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const today = new Date().toISOString().split('T')[0];
        
        // Verificar se é hoje
        if (dateKey === today) {
            dayElement.classList.add('today');
        }

        // Verificar se há férias aprovadas
        if (feriasDataInter2[dateKey]) {
            const feriaType = feriasDataInter2[dateKey].tipo;
            dayElement.classList.add('vacation-day');
            
            // Determinar classe baseada no tipo
            if (feriaType === 'ferias') {
                dayElement.classList.add('vacation-ferias');
            } else {
                dayElement.classList.add('vacation-ausencia');
            }
        }

        // Verificar se há horários marcados (via API carregada)
        const data = horariosDataInter2[dateKey];
        if (data) {
            dayElement.classList.add('has-data');
            dayElement.classList.add('marked'); // Adicionar classe marked para cores de fundo
            
            // Criar badges com horas diretas (mesmo sistema do operador)
            let badges = '';
            
            // Horas trabalhadas (verde)
            if (data.horas_normais && data.horas_normais !== '00:00' && data.horas_normais !== '0') {
                const [hours, minutes] = data.horas_normais.includes(':') ? data.horas_normais.split(':') : [data.horas_normais, '0'];
                const h = parseInt(hours);
                const m = parseInt(minutes);
                let timeText = '';
                if (h > 0 && m > 0) {
                    timeText = `${h}h${m}m`;
                } else if (h > 0) {
                    timeText = `${h}h`;
                } else if (m > 0) {
                    timeText = `${m}m`;
                }
                if (timeText) {
                    badges += `<span class="hour-badge work">${timeText}</span>`;
                }
            }
            
            // Horas extra (amarelo)
            if (data.horas_extra && data.horas_extra !== '00:00' && data.horas_extra !== '0') {
                const [hours, minutes] = data.horas_extra.includes(':') ? data.horas_extra.split(':') : [data.horas_extra, '0'];
                const h = parseInt(hours);
                const m = parseInt(minutes);
                let timeText = '';
                if (h > 0 && m > 0) {
                    timeText = `${h}h${m}m`;
                } else if (h > 0) {
                    timeText = `${h}h`;
                } else if (m > 0) {
                    timeText = `${m}m`;
                }
                if (timeText) {
                    badges += `<span class="hour-badge extra">${timeText}</span>`;
                }
            }
            
            // Horas de prevenção (vermelho)
            if (data.horas_prevencao && data.horas_prevencao !== '00:00' && data.horas_prevencao !== '0') {
                const [hours, minutes] = data.horas_prevencao.includes(':') ? data.horas_prevencao.split(':') : [data.horas_prevencao, '0'];
                const h = parseInt(hours);
                const m = parseInt(minutes);
                let timeText = '';
                if (h > 0 && m > 0) {
                    timeText = `${h}h${m}m`;
                } else if (h > 0) {
                    timeText = `${h}h`;
                } else if (m > 0) {
                    timeText = `${m}m`;
                }
                if (timeText) {
                    badges += `<span class="hour-badge prevention">${timeText}</span>`;
                }
            }
            
            // Quilómetros (azul)
            if (data.km_viatura && data.km_viatura !== '0') {
                badges += `<span class="hour-badge km">${data.km_viatura}km</span>`;
            }

            // Se há badges, adicionar ao dia
            if (badges) {
                const statusContent = document.createElement('div');
                statusContent.className = 'day-details';
                statusContent.innerHTML = `<div class="badges">${badges}</div>`;
                dayElement.appendChild(statusContent);
            }
        }

        // Número do dia
        const dayNumber = document.createElement('span');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day;
        dayElement.appendChild(dayNumber);

        // Verificar se está selecionado para bulk
        if (selectedDaysInter2.has(dateKey)) {
            dayElement.classList.add('bulk-selected');
        }

        // Adicionar classe para modo bulk
        if (isBulkModeInter2 && !feriasDataInter2[dateKey]) {
            dayElement.classList.add('bulk-selectable');
        }

        // Event listener para abrir modal (apenas se não for férias)
        if (!feriasDataInter2[dateKey]) {
            if (isBulkModeInter2) {
                // No modo bulk, clique seleciona/deseleciona o dia
                dayElement.addEventListener('click', () => toggleDaySelectionInter2(dateKey));
            } else if (!inter2IsMonthLocked) {
                // Modo normal, clique abre modal (apenas se não estiver bloqueado)
                dayElement.addEventListener('click', () => {
                    console.log('Dia clicado:', dateKey);
                    openModalInter2(dateKey);
                });
            }
            dayElement.style.cursor = inter2IsMonthLocked ? 'not-allowed' : 'pointer';
            // Alternativa: adicionar onclick diretamente
            if (!isBulkModeInter2) {
                if (!inter2IsMonthLocked) dayElement.setAttribute('onclick', `window.openModalInter2('${dateKey}')`);
            }
        } else {
            dayElement.style.cursor = 'not-allowed';
            dayElement.title = `Dia de ${feriasDataInter2[dateKey].tipo}`;
        }

        calendarGrid.appendChild(dayElement);
    }
}

// Navegação entre meses do inter2
function navigateMonthInter2(direction) {
    currentMonthInter2 += direction;
    
    if (currentMonthInter2 > 11) {
        currentMonthInter2 = 0;
        currentYearInter2++;
    } else if (currentMonthInter2 < 0) {
        currentMonthInter2 = 11;
        currentYearInter2--;
    }
    // Recarregar dados do mês novo em paralelo e depois renderizar
    Promise.all([
    loadFeriasInter2(false),
    loadHorariosDataInter2(false),
    refreshMonthStatusInter2(false)
    ]).then(() => {
        try { renderCalendarInter2(); } catch (_) {}
    }).catch(() => {
        try { renderCalendarInter2(); } catch (_) {}
    });
}

// Ir para hoje do inter2
function goToTodayInter2() {
    const today = new Date();
    currentMonthInter2 = today.getMonth();
    currentYearInter2 = today.getFullYear();
    Promise.all([
        loadFeriasInter2(),
        loadHorariosDataInter2(),
        refreshMonthStatusInter2()
    ]).then(() => {
        try { renderCalendarInter2(); } catch (_) {}
    }).catch(() => {
        try { renderCalendarInter2(); } catch (_) {}
    });
}

// Abrir modal do inter2
function openModalInter2(dateKey) {
    console.log('Abrindo modal inter2 para:', dateKey);
    const modal = document.getElementById('horariosModal');
    
    if (!modal) {
        console.error('Modal não encontrado!');
        alert('Erro: Modal não encontrado');
        return;
    }
    
    const dateDisplay = document.getElementById('selectedDate');
    if (dateDisplay) {
        // Formatar data para exibição
        const [year, month, day] = dateKey.split('-');
        const date = new Date(year, month - 1, day);
        const formattedDate = date.toLocaleDateString('pt-PT', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        dateDisplay.textContent = formattedDate;
    }
    
    modal.dataset.selectedDate = dateKey;

    // Carregar dados existentes do mapa carregado da API
    const data = horariosDataInter2[dateKey];
    if (data) {
        const horasNormais = document.getElementById('horasNormais');
        const horasExtra = document.getElementById('horasExtra');
        const horasPrevencao = document.getElementById('horasPrevencao');
        const kmViatura = document.getElementById('kmViatura');
        
        if (horasNormais) horasNormais.value = data.horas_normais || '';
        if (horasExtra) horasExtra.value = data.horas_extra || '';
        if (horasPrevencao) horasPrevencao.value = data.horas_prevencao || '';
        if (kmViatura) kmViatura.value = data.km_viatura || '';
    } else {
        // Reset form
        const form = document.getElementById('horariosForm');
        if (form) form.reset();
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    console.log('Modal aberto com sucesso');
}

// Fechar modal do inter2
function closeModalInter2() {
    const modal = document.getElementById('horariosModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Limpar dados do dia - inter2
function clearDayDataInter2() {
    const modal = document.getElementById('horariosModal');
    const dateKey = modal.dataset.selectedDate;
    
    if (!dateKey) return;

    // Confirmar ação
    if (!confirm('Tem certeza que deseja limpar todas as horas deste dia?')) {
        return;
    }

    fetch('../../api/calendar/day_delete.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ date: dateKey })
    })
    .then(r=>r.json().catch(()=>({ok:false, code:'BAD_JSON'})).then(data=>({httpOk:r.ok, data})))
    .then(({httpOk, data})=>{
        if (!httpOk || !data || data.ok!==true) {
            const code = data && (data.code || data.msg || data.error);
            showNotificationInter2(`Falha ao limpar: ${code||'Erro'}`, 'error');
            return;
        }
        // Limpar formulário
        const form = document.getElementById('horariosForm');
        if (form) form.reset();
        // Atualizar dados e UI
        closeModalInter2();
        loadHorariosDataInter2().then(()=>renderCalendarInter2());
        showNotificationInter2('Dados do dia removidos com sucesso!', 'success');
    })
    .catch(err=>{
        console.error('Erro ao limpar dia (inter2):', err);
        showNotificationInter2('Erro de rede ao limpar dia.', 'error');
    });
}

// Salvar horários do inter2
function saveHorariosInter2() {
    const modal = document.getElementById('horariosModal');
    const dateKey = modal.dataset.selectedDate;
    
    if (!dateKey) return;

    // Coletar dados do formulário
    const data = {
        horas_normais: document.getElementById('horasNormais').value,
        horas_extra: document.getElementById('horasExtra').value,
        horas_prevencao: document.getElementById('horasPrevencao').value,
        km_viatura: document.getElementById('kmViatura').value,
        data_marcacao: new Date().toISOString(),
        status: 'pendente_aprovacao_inter'
    };

    // Validar se pelo menos um campo está preenchido
    const hasData = data.horas_normais || data.horas_extra || data.horas_prevencao || data.km_viatura;
    
    if (!hasData) {
        alert('Por favor, preencha pelo menos um campo de horas ou quilómetros.');
        return;
    }

    // Enviar para API day_put
    const payload = {
        date: dateKey,
        workMin: data.horas_normais ? hhmmToMinInter2(data.horas_normais) : null,
        otMin: data.horas_extra ? hhmmToMinInter2(data.horas_extra) : null,
        oncallMin: data.horas_prevencao ? hhmmToMinInter2(data.horas_prevencao) : null,
        km: data.km_viatura ? Number(data.km_viatura) : null,
        clear: true
    };

    fetch('../../api/calendar/day_put.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    })
    .then(r=>r.json().catch(()=>({ok:false, code:'BAD_JSON'})).then(data=>({httpOk:r.ok, data})))
    .then(({httpOk, data})=>{
        if (!httpOk || !data || data.ok!==true){
            const code = data && (data.code || data.msg || data.error);
            showNotificationInter2(`Falha ao guardar: ${code||'Erro'}`, 'error');
            return;
        }
        closeModalInter2();
        loadHorariosDataInter2().then(()=>renderCalendarInter2());
        showNotificationInter2('Horários guardados com sucesso.', 'success');
    })
    .catch(err=>{
        console.error('Erro ao guardar dia (inter2):', err);
        showNotificationInter2('Erro de rede ao guardar dia.', 'error');
    });
}

// Carregar dados de horários do inter2
function loadHorariosDataInter2(renderNow = true) {
    const monthKey = `${currentYearInter2}-${String(currentMonthInter2 + 1).padStart(2, '0')}`;
    return fetch(`../../api/calendar/get_month.php?month=${encodeURIComponent(monthKey)}`, { credentials:'same-origin' })
        .then(r=>r.json())
        .then(data=>{
            if (!data || data.ok!==true || !Array.isArray(data.days)){
                console.warn('Resposta inesperada de get_month (inter2):', data);
                horariosDataInter2 = {};
                return;
            }
            const map = {};
            data.days.forEach(d => {
                const hasAny = (d.workMin||0) > 0 || (d.otMin||0) > 0 || (d.oncallMin||0) > 0 || (d.km||0) > 0;
                if (hasAny){
                    map[d.date] = {
                        horas_normais: minToHHMMInter2(d.workMin||0),
                        horas_extra: minToHHMMInter2(d.otMin||0),
                        horas_prevencao: minToHHMMInter2(d.oncallMin||0),
                        km_viatura: (d.km||0)
                    };
                }
            });
            horariosDataInter2 = map;
            console.log('Dados de horários (inter2):', horariosDataInter2);
        })
    .catch(err=>{ console.error('Erro ao carregar dados mês (inter2):', err); horariosDataInter2 = {}; })
    .finally(()=>{ if (renderNow) { try { renderCalendarInter2(); } catch(e){} } });
}

// Mostrar notificação do inter2
function showNotificationInter2(message, type = 'info') {
    // Remover notificação existente
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Criar nova notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">
                ${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}
            </span>
            <span class="notification-message">${message}</span>
        </div>
    `;

    // Adicionar ao DOM
    document.body.appendChild(notification);

    // Remover após 4 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }
    }, 4000);
}

// Funções para seleção múltipla
function openBulkModalInter2() {
    const bulkModal = document.getElementById('bulkModal');
    if (bulkModal) {
        // Limpar formulário
        document.getElementById('bulkForm').reset();
        
        // Limpar date range
        clearRangeInter2();
        
        // Configurar datas padrão (mês atual)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        document.getElementById('bulkStartDate').value = firstDay.toISOString().split('T')[0];
        document.getElementById('bulkEndDate').value = lastDay.toISOString().split('T')[0];
        
        // Configurar event listeners para os date pickers
        setupDateRangeListeners();
        
        // Atualizar display do período
        updateRangeDisplay();
        
        bulkModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeBulkModalInter2() {
    const bulkModal = document.getElementById('bulkModal');
    if (bulkModal) {
        bulkModal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Configurar event listeners para date range
function setupDateRangeListeners() {
    const startDate = document.getElementById('bulkStartDate');
    const endDate = document.getElementById('bulkEndDate');
    
    if (startDate) {
        startDate.addEventListener('change', updateRangeDisplay);
    }
    if (endDate) {
        endDate.addEventListener('change', updateRangeDisplay);
    }
}

// Atualizar display do período selecionado
function updateRangeDisplay() {
    const startDate = document.getElementById('bulkStartDate').value;
    const endDate = document.getElementById('bulkEndDate').value;
    const rangeDisplay = document.getElementById('selectedRangeDisplay');
    const rangeDetails = document.getElementById('selectedRangeDetails');
    
    if (!startDate || !endDate) {
        rangeDisplay.textContent = 'Nenhum período selecionado';
        rangeDetails.innerHTML = '';
        return;
    }
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (start > end) {
        rangeDisplay.textContent = 'Período inválido';
        rangeDetails.innerHTML = '<span style="color: #dc2626;">Data de início deve ser anterior à data de fim</span>';
        return;
    }
    
    // Calcular número de dias
    const diffTime = end.getTime() - start.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    
    // Formatar datas
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    const startFormatted = start.toLocaleDateString('pt-PT', options);
    const endFormatted = end.toLocaleDateString('pt-PT', options);
    
    rangeDisplay.textContent = `${startFormatted} - ${endFormatted}`;
    rangeDetails.innerHTML = `<strong>${diffDays}</strong> dias selecionados`;
}

// Limpar período selecionado
function clearRangeInter2() {
    document.getElementById('bulkStartDate').value = '';
    document.getElementById('bulkEndDate').value = '';
    updateRangeDisplay();
}

function createBulkModeIndicator() {
    // Remover indicador existente
    removeBulkModeIndicator();
    
    const indicator = document.createElement('div');
    indicator.id = 'bulkModeIndicator';
    indicator.className = 'bulk-mode-indicator';
    indicator.innerHTML = `
        <div>Modo Seleção Múltipla Ativo</div>
        <div style="font-size: 0.8rem; opacity: 0.8;">Clique nos dias para selecionar</div>
    `;
    document.body.appendChild(indicator);
}

function removeBulkModeIndicator() {
    const indicator = document.getElementById('bulkModeIndicator');
    if (indicator) {
        indicator.remove();
    }
}

function toggleDaySelectionInter2(dateKey) {
    if (selectedDaysInter2.has(dateKey)) {
        selectedDaysInter2.delete(dateKey);
    } else {
        selectedDaysInter2.add(dateKey);
    }
    
    // Re-renderizar apenas os dias afetados
    renderCalendarInter2();
    updateSelectedDaysDisplay();
}

function updateSelectedDaysDisplay() {
    const countElement = document.getElementById('selectedDaysCount');
    const listElement = document.getElementById('selectedDaysList');
    
    if (countElement) {
        countElement.textContent = selectedDaysInter2.size;
    }
    
    if (listElement) {
        listElement.innerHTML = '';
        
        // Converter datas para array e ordenar
        const sortedDays = Array.from(selectedDaysInter2).sort();
        
        sortedDays.forEach(dateKey => {
            const [year, month, day] = dateKey.split('-');
            const dayTag = document.createElement('div');
            dayTag.className = 'selected-day-tag';
            dayTag.innerHTML = `
                ${day}/${month}
                <span class="remove-day" onclick="removeSelectedDay('${dateKey}')">&times;</span>
            `;
            listElement.appendChild(dayTag);
        });
    }
}

function removeSelectedDay(dateKey) {
    selectedDaysInter2.delete(dateKey);
    renderCalendarInter2();
    updateSelectedDaysDisplay();
}

function clearSelectionInter2() {
    selectedDaysInter2.clear();
    renderCalendarInter2();
    updateSelectedDaysDisplay();
}

function applyBulkSelectionInter2() {
    // Validar período selecionado
    const startDate = document.getElementById('bulkStartDate').value;
    const endDate = document.getElementById('bulkEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Por favor, selecione um período de datas.');
        return;
    }
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (start > end) {
        alert('Data de início deve ser anterior à data de fim.');
        return;
    }
    
    // Obter valores do formulário
    const bulkData = {
        horas_normais: document.getElementById('bulkHorasNormais').value,
        horas_extra: document.getElementById('bulkHorasExtra').value,
        horas_prevencao: document.getElementById('bulkHorasPrevencao').value,
        km_viatura: document.getElementById('bulkKmViatura').value,
        data_marcacao: new Date().toISOString(),
        status: 'pendente_aprovacao_inter'
    };
    
    // Validar se pelo menos um campo está preenchido
    const hasData = bulkData.horas_normais || bulkData.horas_extra || bulkData.horas_prevencao || bulkData.km_viatura;
    
    if (!hasData) {
        alert('Por favor, preencha pelo menos um campo de horas ou quilómetros.');
        return;
    }
    
    // Enviar via API batch_apply
    const payload = {
        start: startDate,
        end: endDate,
        workMin: bulkData.horas_normais ? hhmmToMinInter2(bulkData.horas_normais) : undefined,
        otMin: bulkData.horas_extra ? hhmmToMinInter2(bulkData.horas_extra) : undefined,
        oncallMin: bulkData.horas_prevencao ? hhmmToMinInter2(bulkData.horas_prevencao) : undefined,
        km: bulkData.km_viatura ? Number(bulkData.km_viatura) : undefined,
        applyWeekend: true,
        overwrite: true
    };

    fetch('../../api/calendar/batch_apply.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    })
    .then(r=>r.json().catch(()=>({ok:false, code:'BAD_JSON'})).then(data=>({httpOk:r.ok, data})))
    .then(({httpOk, data})=>{
        if (!httpOk || !data || data.ok!==true){
            const code = data && (data.code || data.msg || data.error);
            showNotificationInter2(`Falha ao aplicar período: ${code||'Erro'}`, 'error');
            return;
        }
        const applied = (data.summary && data.summary.daysApplied) || 0;
        const skipped = Array.isArray(data.skippedLocked) ? data.skippedLocked.length : 0;
        closeBulkModalInter2();
        loadHorariosDataInter2().then(()=>renderCalendarInter2());
        let message = `Marcação aplicada a ${applied} dia(s).`;
        if (skipped>0) message += ` Ignorados (bloqueado): ${skipped}.`;
        showNotificationInter2(message, 'success');
    })
    .catch(err=>{
        console.error('Erro no batch_apply (inter2):', err);
        showNotificationInter2('Erro de rede ao aplicar período.', 'error');
    });
}

// Expor funções globalmente
window.removeSelectedDay = removeSelectedDay;

// Estado do período (bloqueio e badge local se existir)
function refreshMonthStatusInter2(renderNow = true){
    const monthKey = `${currentYearInter2}-${String(currentMonthInter2 + 1).padStart(2,'0')}`;
    return fetch(`../../api/calendar/month_status.php?month=${encodeURIComponent(monthKey)}`, { credentials:'same-origin' })
        .then(r=>r.json())
        .then(data=>{
            if (!data || data.ok!==true) return;
            const estado = data.estado || 'open';
            const isLocked = data.flags && data.flags.isLocked ? true : false;
            inter2IsMonthLocked = !!isLocked;
            // opcional: atualizar algum badge específico desta página, se existir
        })
        .catch(()=>{})
    .finally(()=>{ if (renderNow) { try { renderCalendarInter2(); } catch(e){} } });
}

// Submeter mês (inter2)
window.submitMonth = function(){
    const monthKey = `${currentYearInter2}-${String(currentMonthInter2 + 1).padStart(2, '0')}`;
    if (!confirm(`Deseja submeter as marcações de ${monthKey}?\n\nApós a submissão, as marcações não poderão ser alteradas.`)) return;
    fetch('../../api/calendar/submit_month.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ month: monthKey })
    })
    .then(r=>r.json().catch(()=>({ok:false, code:'BAD_JSON'})).then(data=>({httpOk:r.ok, data})))
    .then(({httpOk, data})=>{
        if (!httpOk || !data || data.ok!==true){
            const code = data && (data.code || data.msg || data.error);
            showNotificationInter2(`Falha ao submeter mês: ${code||'Erro'}`, 'error');
            return;
        }
        showNotificationInter2('Mês submetido com sucesso.', 'success');
        refreshMonthStatusInter2();
    })
    .catch(err=>{
        console.error('Erro ao submeter mês (inter2):', err);
        showNotificationInter2('Erro de rede ao submeter mês.', 'error');
    });
};
