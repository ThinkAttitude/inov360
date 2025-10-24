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
                <h2>Bem-vindo ao Painel Estrela!</h2>
                <p>Gerencie aprovações de férias, consulte pedidos e administre colaboradores com facilidade.</p>
            </div>
            
            <div class="welcome-content">
                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,11 12,14 22,4"></polyline>
                            <path d="M21,12v7a2,2 0,0 1,-2,2H5a2,2 0,0 1,-2,-2V5a2,2 0,0 1,2,-2h11"></path>
                        </svg>
                    </div>
                    <h3>Aprovação de Férias/Ausências</h3>
                    <p>Analise e aprove pedidos de férias e ausências dos colaboradores sob sua supervisão.</p>
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
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <h3>Consulta Histórico de Pedidos</h3>
                    <p>Visualize o histórico completo de todos os pedidos processados no sistema.</p>
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
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Lista de Colaboradores</h3>
                    <p>Consulte informações detalhadas dos colaboradores sob sua supervisão.</p>
                    <a href="#" class="card-link" data-content="consulta_lista">
                        Ver Lista
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
            case "aprovacao_ferias_ausencias":
                url = "../estrela/aprovacao_ferias_ausencias.php";
                break;
            case "consulta_pedidos":
                url = "../estrela/consulta_pedidos.php";
                break;
            case "consulta_lista":
                url = "../estrela/lista_colaboradores.php";
                break;
            default:
                setTimeout(showWelcome, 300);
                return;
        }

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error("Erro ao carregar módulo.");
                return response.text();
            })
            .then(html => {
                mainContent.innerHTML = html;

                // Inicializar funcionalidades específicas
                initializeSpecificFeatures(content);
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

    // Função para inicializar funcionalidades específicas de cada módulo
    function initializeSpecificFeatures(content) {
        switch (content) {
            case "consulta_lista":
                initializeListaColaboradoresModule();
                break;
            case "aprovacao_ferias_ausencias":
                initializeAprovacaoFeriasModule();
                ensureLeavesApprovalHandlers();
                break;
            case "consulta_pedidos":
                initializeConsultaPedidosModule();
                break;
        }
    }

    // Inicializar módulo de lista de colaboradores
    function initializeListaColaboradoresModule() {
        const contentContainer = document.getElementById("gestao-content");
        const visualizarBtn = document.getElementById("visualizar_fichas");

        if (visualizarBtn) {
            visualizarBtn.addEventListener("click", function () {
                // Mostrar loading no container correto
                if (contentContainer) {
                    contentContainer.innerHTML = `
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Carregando lista de colaboradores...</p>
                        </div>
                    `;
                } else {
                    showLoading();
                }

                fetch("../estrela/visualizar_fichas.php")
                    .then(res => {
                        if (!res.ok) throw new Error("Erro ao carregar fichas.");
                        return res.text();
                    })
                    .then(html => {
                        if (contentContainer) {
                            contentContainer.innerHTML = html;
                            bindAnalisarFichaBtns();
                        } else {
                            mainContent.innerHTML = html;
                            bindAnalisarFichaBtns();
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao carregar fichas:', err);
                        const targetContainer = contentContainer || mainContent;
                        targetContainer.innerHTML = `
                            <div class="error-state">
                                <h3>Erro ao carregar fichas</h3>
                                <p>${err.message}</p>
                                <button onclick="location.reload()" class="btn-retry">Tentar Novamente</button>
                            </div>
                        `;
                    });
            });
        } else {
            console.log('Botão visualizar_fichas não encontrado');
        }
    }

    // Função para vincular botões de análise de ficha
    function bindAnalisarFichaBtns() {
        document.querySelectorAll(".analisar-ficha-btn").forEach(btn => {
            btn.addEventListener("click", function () {
                const userId = btn.getAttribute("data-user-id");

                if (userId) {
                    showLoading();

                    fetch(`../estrela/visualizar_lista_colaboradores.php?user_id=${userId}`)
                        .then(res => {
                            if (!res.ok) throw new Error("Erro ao carregar ficha do colaborador.");
                            return res.text();
                        })
                        .then(html => {
                            const container = document.getElementById("gestao-content") || mainContent;
                            container.innerHTML = html;
                        })
                        .catch(err => {
                            console.error(err);
                            const container = document.getElementById("gestao-content") || mainContent;
                            container.innerHTML = `
                                <div class="error-state">
                                    <h3>Erro ao carregar ficha</h3>
                                    <p>${err.message}</p>
                                    <button onclick="location.reload()" class="btn-retry">Tentar Novamente</button>
                                </div>
                            `;
                        });
                }
            });
        });
    }

    // Inicializar módulo de aprovação de férias
    function initializeAprovacaoFeriasModule() {
        // Adicionar funcionalidades específicas para aprovação de férias se necessário
    }

    // Garante aprovação a funcionar mesmo com HTML injetado
    function ensureLeavesApprovalHandlers(){
        if (!window.__leavesApprovalInit && !document.querySelector('script[data-leaves-approval]')){
            try {
                const s = document.createElement('script');
                s.src = '/js/leaves_approval.js';
                s.async = true;
                s.setAttribute('data-leaves-approval','1');
                s.onload = () => { try{ console.debug('leaves_approval loaded (estrela)'); }catch(_){} };
                s.onerror = () => { try{ console.warn('falha a carregar leaves_approval.js'); }catch(_){} };
                document.body.appendChild(s);
            } catch(_) {}
        }

        if (!window.__leavesApprovalFallbackBound__) {
            window.__leavesApprovalFallbackBound__ = true;
            const resolveBtn = (ev)=>{
                const path = typeof ev.composedPath==='function'?ev.composedPath():[];
                for(const el of path){ if(el && el.closest){ const t=el.closest('.js-approve[data-pedido-id], .js-reject[data-pedido-id]'); if(t) return t; } }
                return ev.target && ev.target.closest? ev.target.closest('.js-approve[data-pedido-id], .js-reject[data-pedido-id]') : null;
            };
            document.addEventListener('click', function(ev){
                if (window.__leavesApprovalInit) return;
                const btn = resolveBtn(ev);
                if(!btn) return;
                const id = btn.getAttribute('data-pedido-id');
                if(!id || btn.dataset.processing==='1') return;
                const isApprove = btn.classList.contains('js-approve');
                if (isApprove){
                    if (!confirm('Aprovar pedido?')) return;
                    btn.dataset.processing='1';
                    postDecisionFallback(id, 'aprovar').finally(()=>{ delete btn.dataset.processing; });
                } else {
                    if (!confirm('Rejeitar pedido?')) return;
                    const motivo = prompt('Motivo da rejeição (obrigatório):');
                    if (motivo===null) return;
                    if (!String(motivo).trim()) { (window.showToast?showToast('error','Motivo é obrigatório.'):alert('Motivo é obrigatório.')); return; }
                    btn.dataset.processing='1';
                    postDecisionFallback(id, 'rejeitar', String(motivo).trim()).finally(()=>{ delete btn.dataset.processing; });
                }
            }, true);
        }

        window.approveLeave = function(id){
            if (window.__leavesApprovalInit) return; // principal já trata
            if (!confirm('Aprovar pedido?')) return;
            postDecisionFallback(id, 'aprovar');
        };
        window.rejectLeave = function(id){
            if (window.__leavesApprovalInit) return; // principal já trata
            if (!confirm('Rejeitar pedido?')) return;
            const motivo = prompt('Motivo da rejeição (obrigatório):');
            if (motivo===null) return;
            if (!String(motivo).trim()) { (window.showToast?showToast('error','Motivo é obrigatório.'):alert('Motivo é obrigatório.')); return; }
            postDecisionFallback(id, 'rejeitar', String(motivo).trim());
        };

        function postDecisionFallback(pedidoId, acao, comentario){
            const payload = { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, credentials:'same-origin', body: JSON.stringify({ pedido_id:Number(pedidoId), acao, comentario }) };
            const info = window.showToast? showToast('info','A enviar decisão...') : null;
            return fetch('/api/leaves/decision.php', payload)
              .catch(()=> fetch('../../api/leaves/decision.php', payload))
              .then(async resp=>{
                  if(!resp) throw new Error('Sem resposta');
                  const ct = resp.headers.get('content-type')||'';
                  let data=null; if(ct.includes('application/json')) data=await resp.json(); else { const t=await resp.text(); try{ data=JSON.parse(t);}catch{ data={ok:false,error:t}; } }
                  if(!resp.ok || !data || data.ok===false){
                      const m = (data && (data.error||data.message||data.code)) || ('HTTP '+resp.status);
                      (window.showToast? showToast('error','Falha: '+m) : alert('Falha: '+m));
                      return;
                  }
                  (window.showToast? showToast('success','Decisão efetuada.') : alert('Decisão efetuada.'));
                  setTimeout(()=> window.location.reload(), 800);
              })
              .catch(err=>{ (window.showToast? showToast('error','Erro: '+(err&&err.message?err.message:err)) : alert('Erro: '+err)); });
        }
    }

    // Inicializar módulo de consulta de pedidos
    function initializeConsultaPedidosModule() {
        // Adicionar funcionalidades específicas para consulta de pedidos se necessário
    }

    // Adicionar event listeners iniciais
    links.forEach(link => {
        link.addEventListener("click", handleNavigation);
    });

    // Mostrar página inicial por padrão
    setTimeout(showWelcome, 100);

    // Toast infra + captura global de submissões para /api/leaves/request.php
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

    try {
        if (!window.__leavesSubmitCapture__) {
            document.addEventListener('submit', function(ev){
                const form = ev.target;
                if (form && form.action && form.action.includes('/api/leaves/request.php')){
                    ev.preventDefault();
                    if (form.__leavesSubmitting) return;
                    form.__leavesSubmitting = true;
                    const fd = new FormData(form);
                    try {
                        const di=form.querySelector('#data_inicio');
                        const df=form.querySelector('#data_fim');
                        const norm=v=>(/^\d{2}\/\d{2}\/\d{4}$/.test(v)?`${v.slice(6,10)}-${v.slice(3,5)}-${v.slice(0,2)}`:v);
                        if(di&&di.value) fd.set('data_inicio', norm(di.value));
                        if(df&&df.value) fd.set('data_fim', norm(df.value));
                    }catch(_){ }
                    const btn = form.querySelector('button[type="submit"], .btn-submit');
                    const original = btn ? btn.innerHTML : '';
                    if (btn){ btn.disabled = true; btn.innerText = 'A enviar...'; }
                    fetch(form.action, { method:'POST', body: fd, credentials: 'same-origin' })
                      .then(async resp=>{
                        const ct = resp.headers.get('content-type')||'';
                        let data=null; if(ct.includes('application/json')) data = await resp.json(); else { const t = await resp.text(); try{ data=JSON.parse(t);}catch{ data={ ok:false, error:t||'Erro ao processar resposta.'}; } }
                        if(!resp.ok || !data || data.ok===false){
                            const code = data && (data.code || data.error || data.message);
                            const map = { MISSING_FIELDS:'Preencha todos os campos obrigatórios.', INVALID_DATE:'Data inválida.', RANGE_ERROR:'Data de início deve ser anterior à data de fim.', DOC_REQUIRED:'Este tipo exige comprovativo (PDF/JPG/PNG).', BAD_FILETYPE:'Tipo de ficheiro inválido (PDF, JPG, PNG).', FILE_TOO_LARGE:'Ficheiro maior que 5MB.', FILE_MOVE_ERROR:'Erro ao guardar o ficheiro no servidor.', UNAUTHENTICATED:'Sessão expirada. Faça login novamente.', FORBIDDEN_ROLE:'Perfil sem permissão para criar pedidos.', DB_ERROR:'Erro interno ao gravar o pedido.' };
                            showToast('error', `Falha ao submeter pedido: ${map[code] || code || ('HTTP '+resp.status)}`);
                        } else {
                            showToast('success', 'Pedido submetido com sucesso.');
                            window.fecharModalPedido && window.fecharModalPedido();
                            setTimeout(()=>window.location.reload(), 1200);
                        }
                      })
                      .catch(err=> showToast('error', `Erro inesperado: ${err && err.message ? err.message : err}`))
                      .finally(()=>{ if(btn){ btn.disabled=false; btn.innerHTML = original || 'Submeter Pedido'; } form.__leavesSubmitting=false; });
                }
            }, true);
            window.__leavesSubmitCapture__ = true;
        }
    } catch (_) { /* noop */ }
});
