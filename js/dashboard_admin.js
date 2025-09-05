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
            const link = document.querySelector('.sidebar-menu a[data-content="aprovar_ferias"], .sidebar-menu a[data-content="aprovacao_ferias_ausencias"]');
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
                <h2>Bem-vindo ao Painel Administrador!</h2>
                <p>Gerencie o sistema, aprove pedidos e supervisione todas as operações do RH360.</p>
            </div>
            
            <div class="welcome-content">
                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12,6 12,12 16,14"></polyline>
                        </svg>
                    </div>
                    <h3>Consulta de Horários</h3>
                    <p>Visualize e gerencie horários de todos os colaboradores da organização.</p>
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
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <h3>Férias e Ausências</h3>
                    <p>Solicite os seus próprios pedidos de férias e ausências como administrador.</p>
                    <a href="#" class="card-link" data-content="pedidos_ferias">
                        Gerir Pedidos
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </a>
                </div>
                
                <div class="welcome-card">
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20,6 9,17 4,12"></polyline>
                        </svg>
                    </div>
                    <h3>Aprovação de Pedidos</h3>
                    <p>Aprove ou rejeite pedidos de férias e ausências de todos os colaboradores.</p>
                    <a href="#" class="card-link" data-content="aprovar_ferias">
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
                    <h3>Consulta de Pedidos</h3>
                    <p>Acesse o histórico completo de todos os pedidos realizados no sistema.</p>
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
                    <h3>Lista de Intermédios</h3>
                    <p>Visualize e gerencie informações de todos os colaboradores intermédios.</p>
                    <a href="#" class="card-link" data-content="consulta_lista">
                        Ver Lista
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
                            <polyline points="10,9 9,9 8,9"></polyline>
                        </svg>
                    </div>
                    <h3>A Minha Ficha</h3>
                    <p>Visualize e edite a sua própria ficha pessoal de colaborador.</p>
                    <a href="#" class="card-link" data-content="ficha_colab">
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
                url = "../admin/horarios.php";
                break;
            case "pedidos_ferias":
                url = "../admin/ferias_ausencias.php";
                break;
            case "aprovar_ferias":
                url = "../admin/aprovacao_ferias_ausencias.php";
                break;
            case "consulta_pedidos":
                url = "../admin/consulta_pedidos.php";
                break;
            case "consulta_lista":
                url = "../admin/lista_intermedios.php";
                break;
            case "ficha_colab":
                url = "../admin/ficha_colaborador.php";
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
            case "pedidos_ferias":
            case "ferias":
                initializeFeriasModule();
                break;
            case "aprovar_ferias":
            case "aprovacao_ferias_ausencias":
                ensureLeavesApprovalHandlers();
                break;
            case "horarios":
                initializeHorariosModule();
                break;
            case "consulta_pedidos":
                initializeConsultaPedidosModule();
                break;
            case "lista_intermedios2":
                initializeListaIntermedios2Module();
                break;
            case "ficha_colab":
            case "ficha_colaborador":
                initializeFichaColaboradorModule();
                break;
        }
    }

    function ensureLeavesApprovalHandlers(){
        if (!window.__leavesApprovalInit && !document.querySelector('script[data-leaves-approval]')){
            try {
                const s = document.createElement('script');
                s.src = '/js/leaves_approval.js';
                s.async = true;
                s.setAttribute('data-leaves-approval','1');
                s.onload = () => { try{ console.debug('leaves_approval loaded (admin)'); }catch(_){} };
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

    // Inicializar módulo de férias
    function initializeFeriasModule() {
        // Funções globais para modal
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

        // Inicializar dropdown de tipo
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

        // File input handler
        const fileInput = document.getElementById('ficheiro');
        const fileContent = document.querySelector('.file-input-content span');
        const fileWrapper = document.querySelector('.file-input-wrapper');

        if (fileInput && fileContent) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileContent.textContent = this.files[0].name;
                    if (fileWrapper) {
                        fileWrapper.style.borderColor = '#10b981';
                        fileWrapper.style.backgroundColor = '#ecfdf5';
                    }
                } else {
                    fileContent.textContent = 'Clique para selecionar ficheiro';
                    if (fileWrapper) {
                        fileWrapper.style.borderColor = '#d1d5db';
                        fileWrapper.style.backgroundColor = '';
                    }
                }
            });
        }

        if (fileWrapper && fileInput) {
            fileWrapper.addEventListener('click', function() {
                fileInput.click();
            });
        }

        // Modal backdrop
        const modalPedido = document.getElementById('modalPedido');
        if (modalPedido) {
            modalPedido.addEventListener('click', function(e) {
                if (e.target === this) {
                    window.fecharModalPedido();
                }
            });
        }

        // Inicializar filtros
        initializeFilterButtons();
    }

    // Função para inicializar filtros de férias/ausências
    function initializeFilterButtons() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const pedidoCards = document.querySelectorAll('.pedido-card');

        if (!filterButtons.length || !pedidoCards.length) return;

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;

                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Filter cards
                pedidoCards.forEach(card => {
                    const estado = card.dataset.estado;

                    if (filter === 'all' || estado === filter) {
                        card.style.display = 'block';
                        card.classList.remove('hidden');
                    } else {
                        card.style.display = 'none';
                        card.classList.add('hidden');
                    }
                });
            });
        });
    }

    // Inicializar módulo de horários
    function initializeHorariosModule() {
        // Carregar CSS do FullCalendar se necessário
        if (!document.querySelector('link[href*="fullcalendar"]')) {
            const cssLink = document.createElement("link");
            cssLink.rel = "stylesheet";
            cssLink.href = "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css";
            document.head.appendChild(cssLink);
        }

        // Carregar JS do FullCalendar se necessário
        if (typeof FullCalendar === "undefined") {
            const script = document.createElement("script");
            script.src = "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js";
            script.onload = function() {
                renderCalendar();
            };
            document.body.appendChild(script);
        } else {
            renderCalendar();
        }

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
                        const start = fetchInfo.start;
                        const monthKey = `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2,'0')}`;
                        fetch(`../../api/calendar/get_month.php?month=${encodeURIComponent(monthKey)}`, { credentials: 'same-origin' })
                          .then(r => r.json())
                          .then(data => {
                            if (!data || data.ok !== true || !Array.isArray(data.days)) { failureCallback(new Error('Resposta inesperada')); return; }
                            const events = [];
                            data.days.forEach(d => {
                                const dateStr = d.date;
                                const leaves = Array.isArray(d.leaves) ? d.leaves : [];
                                leaves.forEach(lv => {
                                    const title = (lv.label || lv.titulo || lv.title || lv.tipo || lv.type || 'Ausência');
                                    events.push({ title, start: dateStr, allDay: true, extendedProps: { type: 'LEAVE', raw: lv } });
                                });
                            });
                            successCallback(events);
                          })
                          .catch(err => failureCallback(err));
                    },
                    eventClick: function(info) {
                        alert('Evento: ' + info.event.title + '\nData: ' + info.event.start.toLocaleDateString());
                    }
                });
                calendar.render();
            }
        }
    }

    // Inicializar módulo de consulta de pedidos
    function initializeConsultaPedidosModule() {
        const toggleBtns = document.querySelectorAll('.toggle-btn');
        const teamSection = document.querySelector('.requests-section:first-of-type');
        const personalSection = document.getElementById('personal-section');

        // Garantir que as secções estão correctamente inicializadas
        if (teamSection) teamSection.style.display = 'block';
        if (personalSection) personalSection.style.display = 'none';

        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const section = this.getAttribute('data-section');

                // Atualizar botão ativo
                toggleBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Mostrar/esconder secções
                if (section === 'team') {
                    if (teamSection) teamSection.style.display = 'block';
                    if (personalSection) personalSection.style.display = 'none';
                } else {
                    if (teamSection) teamSection.style.display = 'none';
                    if (personalSection) personalSection.style.display = 'block';
                }
            });
        });
    }

    // Inicializar módulo de lista de intermédios 2
    function initializeListaIntermedios2Module() {
        // Inicializar pesquisa
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

        // Inicializar toggle de views (cards/tabela)
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

        // Inicializar modal backdrop clicks
        const fichaModal = document.getElementById('fichaModal');
        if (fichaModal) {
            fichaModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    window.fecharModal();
                }
            });
        }
    }

    // Inicializar módulo de ficha colaborador
    function initializeFichaColaboradorModule() {
        const editarLink = document.getElementById('editar_ficha_colaborador');
        if (editarLink) {
            editarLink.addEventListener('click', function(e) {
                e.preventDefault();
                showLoading();

                fetch('../admin/editar_ficha_colaborador.php')
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

        function initializeEditForm() {
            const form = document.querySelector('form[action*="a_ficha_colaborador"]');
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
                                handleNavigationDirect('ficha_colab');
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
            case "ficha_colab":
            case "ficha_colaborador":
                url = "../admin/ficha_colaborador.php";
                break;
            default:
                showWelcome();
                return;
        }

        fetch(url)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
                initializeSpecificFeatures(content);
            })
            .catch(error => {
                console.error('Error loading content:', error);
                mainContent.innerHTML = '<p>Erro ao carregar conteúdo.</p>';
            });
    }

    // Adicionar event listeners iniciais
    links.forEach(link => {
        link.addEventListener("click", handleNavigation);
    });

    // Mostrar página inicial por padrão
    setTimeout(showWelcome, 100);

    // Ensure toast infra + global capture always active
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
                t.innerHTML = `<span class=\"t-icon\">${icon}</span><div class=\"t-msg\">${message}</div><button class=\"t-close\" aria-label=\"Fechar\">×</button>`;
                container.appendChild(t);
                const ttl = Number(opts.duration||2500);
                const close = ()=>{ t.style.animation = 'toast-out .18s ease forwards'; setTimeout(()=>t.remove(), 200); };
                t.querySelector('.t-close').addEventListener('click', close);
                setTimeout(close, ttl);
                return t;
            };
        }
    })();

    try{
        if(!window.__leavesSubmitCapture__){
            document.addEventListener('submit', function(ev){
                const form = ev.target;
                if(form && form.action && form.action.includes('/api/leaves/request.php')){
                    ev.preventDefault();
                    if(form.__leavesSubmitting) return; form.__leavesSubmitting = true;
                    const fd = new FormData(form);
                    try{
                        const di = form.querySelector('#data_inicio');
                        const df = form.querySelector('#data_fim');
                        const norm = v => (/^\d{2}\/\d{2}\/\d{4}$/.test(v) ? `${v.slice(6,10)}-${v.slice(3,5)}-${v.slice(0,2)}` : v);
                        if(di && di.value) fd.set('data_inicio', norm(di.value));
                        if(df && df.value) fd.set('data_fim', norm(df.value));
                    }catch(_){}
                    const btn = form.querySelector('button[type="submit"], .btn-submit');
                    const original = btn ? btn.innerHTML : '';
                    if(btn){ btn.disabled = true; btn.innerText = 'A enviar...'; }
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
    }catch(_){/* noop */}
});

// Funções globais para Férias e Ausências
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

// Função para ver ficha completa (para lista de intermédios 2)
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
    fetch(`../admin/visualizar_lista_intermedios.php?user_id=${userId}`)
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
    // Setup toast infra if not present
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
                fileWrapper.style.borderColor = '#10b981';
                fileWrapper.style.backgroundColor = '#ecfdf5';
            } else {
                fileContent.textContent = 'Clique para selecionar ficheiro';
                fileWrapper.style.borderColor = '#d1d5db';
                fileWrapper.style.backgroundColor = '';
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

    // Prevenir envio de form com Enter (exceto textarea)
    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    });

    // Global capture to keep requests in-page
    try{
        if(!window.__leavesSubmitCapture__){
            document.addEventListener('submit', function(ev){
                const form = ev.target;
                if(form && form.action && form.action.includes('/api/leaves/request.php')){
                    ev.preventDefault();
                    if(form.__leavesSubmitting) return; form.__leavesSubmitting = true;
                    const fd = new FormData(form);
                    try{
                        const di = form.querySelector('#data_inicio');
                        const df = form.querySelector('#data_fim');
                        const norm = v => (/^\d{2}\/\d{2}\/\d{4}$/.test(v) ? `${v.slice(6,10)}-${v.slice(3,5)}-${v.slice(0,2)}` : v);
                        if(di && di.value) fd.set('data_inicio', norm(di.value));
                        if(df && df.value) fd.set('data_fim', norm(df.value));
                    }catch(_){}
                    const btn = form.querySelector('button[type="submit"], .btn-submit');
                    const original = btn ? btn.innerHTML : '';
                    if(btn){ btn.disabled = true; btn.innerText = 'A enviar...'; }
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
    }catch(_){/* noop */}
};
