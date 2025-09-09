document.addEventListener("DOMContentLoaded", function () {
    const links = document.querySelectorAll(".sidebar-menu a, .card-link");
    const mainContent = document.getElementById("main-content");

    // Variável global para armazenar o user_id atual
    let currentAnalyzedUserId = null;

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
            // Tenta preferencialmente um link explícito de aprovação; caso não exista, usa o de pedidos_ferias
            const link = document.querySelector(
                '.sidebar-menu a[data-content="aprovacao_ferias_ausencias"], .sidebar-menu a[data-content="aprovar_ferias"], .sidebar-menu a[data-content="pedidos_ferias"]'
            );
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
        }catch(_){ /* ignore */ }
    }

    // ---------- EDIÇÃO COMPLETA FICHA (ADMIN RH) ----------
    function buildFichaEditarUrl(){
        // tenta usar user id do botão clicado anteriormente ou global
        const explicitBtn = document.querySelector('.btn-edicao-completa[data-user-id]');
        let uid = null;
        if(explicitBtn){ uid = explicitBtn.getAttribute('data-user-id'); }
        if(!uid && window.currentAnalyzedUserId){ uid = window.currentAnalyzedUserId; }
        return uid ? `../admin_rh/ficha_editar.php?user_id=${uid}` : '../admin_rh/ficha_editar.php';
    }

    function initializeFichaEditarCompletaModule(){
        const form = document.querySelector('form[action*="arh_guardar_ficha"]');
        if(!form) return;

        form.addEventListener('submit', function(e){
            e.preventDefault();
            // Sanitize money fields before sending (accepts 1.234,56 / 1 234,56 / 1,234.56 / 8.5 / 6,5)
            ['salario_base','subsidio_alimentacao','ordenado_liquido'].forEach(name=>{
                const input = form.querySelector(`[name="${name}"]`);
                if(!input) return;
                let v = (input.value || '').toString().trim();
                if(!v) return;
                v = v.replace(/\s+/g,'');
                // Normalize to a NUMBER
                if(/,\d{1,2}$/.test(v)) { // pt format with comma decimal (1-2 digits)
                    v = v.replace(/\./g,'').replace(/,/g,'.');
                } else if(/\.\d{1,2}$/.test(v) && !/,/.test(v)) { // en format with dot decimal (1-2 digits) and no commas
                    v = v.replace(/,/g,''); // remove thousand commas only
                } else { // plain integer or other
                    v = v.replace(/,/g,''); // remove thousand commas
                }
                if(!isNaN(parseFloat(v))) {
                    // Send as comma-decimal string so backend doesn't strip the decimal point
                    const result = parseFloat(v).toFixed(2).replace('.', ',');
                    input.value = result;
                }
            });
            const submitBtn = form.querySelector('button[type="submit"], .btn-primary, .editar-ficha-btn');
            const original = submitBtn ? submitBtn.innerHTML : null;
            if(submitBtn){
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span> Guardando...';
            }

            const fd = new FormData(form);
            fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin', cache: 'no-store' })
                .then(r=>r.json())
                .then(data=>{
                    if(submitBtn){ submitBtn.disabled = false; submitBtn.innerHTML = original; }
                    if(typeof showToast === 'function'){
                        showToast(data.success ? '✅ Ficha guardada com sucesso.' : ('❌ ' + (data.message || 'Erro ao guardar.')), data.success ? 'success' : 'error');
                    } else {
                        alert(data.message || (data.success ? 'Guardado.' : 'Erro.'));
                    }
                    if(data.success){
                        setTimeout(()=>{ navigateToContent('inicio'); },1200);
                    }
                })
                .catch(err=>{
                    if(submitBtn){ submitBtn.disabled = false; submitBtn.innerHTML = original; }
                    if(typeof showToast === 'function'){
                        showToast('❌ Erro de rede ao guardar.', 'error');
                    } else { alert('Erro de rede.'); }
                });
        });
    }

    // Função para resetar à página inicial
    function showWelcome() {
        mainContent.innerHTML = `
            <div class="main-header">
                <h2>Bem-vindo ao Painel Admin RH!</h2>
                <p>Gerencie recursos humanos, fichas de colaboradores e administre todo o sistema RH360.</p>
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
                    <p>Solicite e gerencie pedidos de férias e ausências de todos os colaboradores.</p>
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
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                    </div>
                    <h3>Gestão de Fichas</h3>
                    <p>Gerencie e aprove alterações das fichas de colaboradores de toda a organização.</p>
                    <a href="#" class="card-link" data-content="gestao_fichas_colaboradores">
                        Gerir Fichas
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
                            <path d="M12 14l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3>Marcação Direta</h3>
                    <p>Marque férias e ausências diretamente para qualquer colaborador.</p>
                    <a href="#" class="card-link" data-content="marcacao_direta_ferias">
                        Marcação Direta
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
        navigateToContent(content);
    }

    // Nova função reutilizável para navegação programática
    function navigateToContent(content){
        if(!content) return;

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
            case "ficha_editar":
                // edição completa admin RH
                url = buildFichaEditarUrl();
                break;
            case "horarios":
                url = "../admin_rh/horarios.php";
                break;
            case "pedidos_ferias":
                url = "../admin_rh/ferias_ausencias.php";
                break;
            case "gestao_fichas_colaboradores":
                url = "../admin_rh/fichas_colaboradores.php";
                break;
            case "marcacao_direta_ferias":
                url = "../admin_rh/marcacao_direta_fa.php";
                break;
            case "criar_colaborador":
                url = "../admin_rh/criar_colaborador.php";
                break;
            case "consulta_pedidos":
                url = "../admin_rh/consulta_pedidos.php";
                break;
            case "extracao_horarios":
                url = "../admin_rh/extracao_horarios.php";
                break;
            case "ficha_colab":
                url = "../admin_rh/ficha_colaborador.php";
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
                initializeFeriasModule();
                break;
            case "ficha_editar":
                initializeFichaEditarCompletaModule();
                break;
            case "horarios":
                initializeHorariosModule();
                break;
            case "consulta_pedidos":
                initializeConsultaPedidosModule();
                break;
            case "gestao_fichas_colaboradores":
                initializeGestaoFichasModule();
                break;
            case "ficha_colab":
                initializeFichaColaboradorModule();
                break;
            case "marcacao_direta_ferias":
                initializeMarcacaoDiretaModule();
                break;
            case "criar_colaborador":
                initializeCriarColaboradorModule();
                break;
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
                // Reset form logic here
            }
        };

        // Initialize other ferias functionality
        initializeFilterButtons();
        initializeComprovativoLogic();
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

    // Funções auxiliares para módulos específicos
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

    function initializeComprovativoLogic() {
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
    }

    // Inicializar módulo de consulta de pedidos
    function initializeConsultaPedidosModule() {
        // Adicionar funcionalidades específicas para consulta de pedidos se necessário
    }

    // Inicializar módulo de gestão de fichas
    function initializeGestaoFichasModule() {
        // Botão "Aprovar Alterações de Colaboradores"
        const verAprovacoesBtn = document.getElementById('ver_aprovacoes');
        if (verAprovacoesBtn) {
            verAprovacoesBtn.addEventListener('click', function() {
                showLoading();

                fetch('../admin_rh/lista_edicao.php')
                    .then(response => {
                        if (!response.ok) throw new Error("Erro ao carregar lista de aprovações.");
                        return response.text();
                    })
                    .then(html => {
                        const gestaoContent = document.getElementById('gestao-content');
                        if (gestaoContent) {
                            gestaoContent.innerHTML = html;
                            gestaoContent.classList.add('loaded');

                            // Inicializar botões de análise de pedidos
                            initializeAnalisarPedidoButtons();
                        } else {
                            mainContent.innerHTML = html;
                            initializeAnalisarPedidoButtons();
                        }
                    })
                    .catch(error => {
                        const contentArea = document.getElementById('gestao-content') || mainContent;
                        contentArea.innerHTML = `
                            <div class="error-state">
                                <h3>Erro ao carregar aprovações</h3>
                                <p>${error.message}</p>
                                <button onclick="location.reload()" class="btn-retry">Tentar Novamente</button>
                            </div>
                        `;
                    });
            });
        }

        // Botão "Lista de Colaboradores"
        const visualizarFichasBtn = document.getElementById('visualizar_fichas');
        if (visualizarFichasBtn) {
            visualizarFichasBtn.addEventListener('click', function() {
                showLoading();

                fetch('../admin_rh/visualizar_fichas.php')
                    .then(response => {
                        if (!response.ok) throw new Error("Erro ao carregar fichas de colaboradores.");
                        return response.text();
                    })
                    .then(html => {
                        const gestaoContent = document.getElementById('gestao-content');
                        if (gestaoContent) {
                            gestaoContent.innerHTML = html;
                            gestaoContent.classList.add('loaded');

                            // Adicionar funcionalidade aos botões "Analisar Ficha"
                            initializeAnalisarFichaButtons();
                        } else {
                            mainContent.innerHTML = html;
                            initializeAnalisarFichaButtons();
                        }
                    })
                    .catch(error => {
                        const contentArea = document.getElementById('gestao-content') || mainContent;
                        contentArea.innerHTML = `
                            <div class="error-state">
                                <h3>Erro ao carregar fichas</h3>
                                <p>${error.message}</p>
                                <button onclick="location.reload()" class="btn-retry">Tentar Novamente</button>
                            </div>
                        `;
                    });
            });
        }

        // Botão Exportar Excel (todas as fichas financeiras)
        const exportBtn = document.getElementById('exportar_fichas_financeiras');
        if (exportBtn && !exportBtn.__bound){
            exportBtn.addEventListener('click', function(ev){
                // Allow default if opened directly via anchor in a new tab
                ev.preventDefault();
                const url = this.href || '/api/finance/profiles_export_all.php';
                // Try to open in new tab to stream file
                const win = window.open(url, '_blank');
                if (!win) {
                    // Popup blocked: fallback to navigate current window
                    window.location.href = url;
                }
            });
            exportBtn.__bound = true;
        }
    }


    // Função para inicializar botões "Analisar" da lista de pedidos pendentes
    function initializeAnalisarPedidoButtons() {
        const analisarBtns = document.querySelectorAll('.analisar-btn');

        analisarBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const pedidoId = this.getAttribute('data-id');

                if (pedidoId) {
                    showLoading();

                    fetch(`../admin_rh/ficha_aprovar.php?id=${pedidoId}`)
                        .then(response => {
                            if (!response.ok) throw new Error("Erro ao carregar pedido para aprovação.");
                            return response.text();
                        })
                        .then(html => {
                            mainContent.innerHTML = html;
                            // Inicializar funcionalidades da página de aprovação se necessário
                            initializeAprovarFichaModule();
                        })
                        .catch(error => {
                            mainContent.innerHTML = `
                                <div class="error-state">
                                    <h3>Erro ao carregar pedido</h3>
                                    <p>${error.message}</p>
                                    <button onclick="location.reload()" class="btn-retry">Tentar Novamente</button>
                                </div>
                            `;
                        });
                }
            });
        });
    }

    // Inicializar botões "Analisar Ficha"
    function initializeAnalisarFichaButtons() {
        const analisarBtns = document.querySelectorAll('.analisar-ficha-btn');
        const analisarFinanceBtns = document.querySelectorAll('.analisar-ficha-financeira-btn');

        // Exportar Excel (todas as fichas financeiras)
        const exportBtn = document.getElementById('exportar_fichas_financeiras');
        if (exportBtn && !exportBtn.__bound){
            exportBtn.addEventListener('click', function(ev){
                ev.preventDefault();
                const url = this.href || '/api/finance/profiles_export_all.php';
                const win = window.open(url, '_blank');
                if (!win) window.location.href = url;
            });
            exportBtn.__bound = true;
        }

        analisarBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');

                if (userId) {
                    // Armazenar o user_id globalmente
                    currentAnalyzedUserId = userId;

                    showLoading();

                    fetch(`../admin_rh/ficha_colaborador.php?user_id=${userId}`)
                        .then(response => {
                            if (!response.ok) throw new Error("Erro ao carregar ficha do colaborador.");
                            return response.text();
                        })
                        .then(html => {
                            mainContent.innerHTML = html;
                            // Inicializar funcionalidades da ficha do colaborador
                            initializeFichaColaboradorModule();
                        })
                        .catch(error => {
                            mainContent.innerHTML = `
                                <div class="error-state">
                                    <h3>Erro ao carregar ficha</h3>
                                    <p>${error.message}</p>
                                    <button onclick="location.reload()" class="btn-retry">Tentar Novamente</button>
                                </div>
                            `;
                        });
                }
            });
        });

        // Abrir Ficha Financeira dentro do dashboard
        analisarFinanceBtns.forEach(btn => {
            if (btn.__boundFinance) return;
            btn.__boundFinance = true;
            btn.addEventListener('click', function(){
                const userId = this.getAttribute('data-user-id');
                if (!userId) return;
                currentAnalyzedUserId = userId;
                showLoading();
                fetch(`../admin_rh/ficha_financeira.php?user_id=${userId}`)
                    .then(r=>{ if(!r.ok) throw new Error('Erro ao carregar ficha financeira.'); return r.text(); })
                    .then(html=>{
                        mainContent.innerHTML = html;
                        initializeFichaFinanceiraModule();
                    })
                    .catch(error=>{
                        mainContent.innerHTML = `
                            <div class="error-state">
                                <h3>Erro ao carregar ficha financeira</h3>
                                <p>${error.message}</p>
                                <button onclick=\"location.reload()\" class=\"btn-retry\">Tentar Novamente</button>
                            </div>`;
                    });
            });
        });
    }

    // Inicializar módulo de ficha do colaborador
    function initializeFichaColaboradorModule() {
        const editarLink = document.getElementById('editar_ficha_colaborador');
        if (editarLink) {
            editarLink.addEventListener('click', function(e) {
                e.preventDefault();
                showLoading();

                // Usar o user_id armazenado globalmente se existir
                const editUrl = currentAnalyzedUserId ?
                    `../admin_rh/editar_ficha_colaborador.php?user_id=${currentAnalyzedUserId}` :
                    '../admin_rh/editar_ficha_colaborador.php';

                fetch(editUrl)
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
            const form = document.querySelector('form[action*="arh_ficha_colaborador"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    // Show loading state on submit button
                    const submitBtn = form.querySelector('.btn-primary');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin">
                            <path d="M21 12a9 9 0 11-6.219-8.56"/>
                        </svg>
                        Processando...
                    `;
                    submitBtn.disabled = true;

                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        cache: 'no-store'
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Reset button state
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;

                        // Show message
                        showMessage(data.success ? 'success' : 'error', data.message || (data.success ? 'Alterações guardadas com sucesso!' : 'Erro: ' + (data.error || 'Erro desconhecido')));

                        if (data.success) {
                            // Wait a moment and go to Início
                            setTimeout(() => {
                                navigateToContent('inicio');
                            }, 1500);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);

                        // Reset button state
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;

                        showMessage('error', 'Erro ao submeter pedido. Tente novamente.');
                    });
                });
            }

            // Add form validation
            addFormValidation();
        }

        // Add message display function
        function showMessage(type, text) {
            const messageDiv = document.getElementById('message') || createMessageDiv();
            messageDiv.textContent = text;
            messageDiv.className = `message ${type}`;
            messageDiv.style.display = 'block';

            // Auto hide after 4 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 4000);
        }

        function createMessageDiv() {
            const div = document.createElement('div');
            div.id = 'message';
            div.className = 'message';
            div.style.display = 'none';
            document.body.appendChild(div);
            return div;
        }

        // Add form validation
        function addFormValidation() {
            const form = document.querySelector('form[action*="arh_ficha_colaborador"]');
            if (!form) return;

            // NIB validation
            const nibInput = form.querySelector('#nib');
            if (nibInput) {
                nibInput.addEventListener('input', function() {
                    // Remove non-digits
                    this.value = this.value.replace(/\D/g, '');

                    // Validate length
                    if (this.value.length > 0 && this.value.length !== 21) {
                        this.setCustomValidity('NIB deve ter exatamente 21 dígitos');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // Phone validation
            const phoneInput = form.querySelector('#contacto_telefone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    // Basic phone validation (9 digits starting with 9, or international format)
                    const phonePattern = /^(\+351\s?)?[29]\d{8}$/;
                    if (this.value && !phonePattern.test(this.value.replace(/\s/g, ''))) {
                        this.setCustomValidity('Formato de telefone inválido');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        }

        // --- Modal Edição Completa (in-page) ---
        const btnOpenFull = document.getElementById('abrirEdicaoCompleta');
        const modalFull = document.getElementById('modalEdicaoCompleta');
        const closeFull = document.getElementById('fecharModalEdicao');
        const cancelFull = document.getElementById('cancelarEdicaoCompleta');
        if(btnOpenFull && modalFull && !btnOpenFull.__bound){
            btnOpenFull.addEventListener('click', ()=>{ modalFull.style.display='flex'; document.body.style.overflow='hidden'; });
            [closeFull,cancelFull].forEach(b=> b && b.addEventListener('click', ()=>{ modalFull.style.display='none'; document.body.style.overflow=''; }));
            btnOpenFull.__bound = true;
        }
        // Submit form (full edit)
        const fullForm = document.getElementById('formEdicaoCompleta');
        if(fullForm && !fullForm.__bound){
            fullForm.addEventListener('submit', function(e){
                e.preventDefault();
                const submitBtn = fullForm.querySelector('button[type="submit"]');
                const label = submitBtn.querySelector('.label-text');
                const spinner = submitBtn.querySelector('.loading-spinner');
                label.style.display='none'; spinner.style.display='inline-block'; submitBtn.disabled = true;
                const fd = new FormData(fullForm);
                fetch(fullForm.action,{method:'POST',body:fd,credentials:'same-origin',cache:'no-store'})
                  .then(r=>r.json())
                  .then(data=>{
                     showToast(data.success? '✅ Ficha guardada.' : ('❌ '+(data.message||'Erro ao guardar')), data.success? 'success':'error');
                     if(data.success){ setTimeout(()=>{ modalFull.style.display='none'; document.body.style.overflow=''; navigateToContent('inicio'); },900); }
                  })
                  .catch(()=>{ showToast('❌ Erro de rede','error'); })
                  .finally(()=>{ label.style.display=''; spinner.style.display='none'; submitBtn.disabled=false; });
            });
            fullForm.__bound = true;
        }
    }

    // Função para inicializar funcionalidades da página de aprovação
    function initializeAprovarFichaModule() {
        // Novo fluxo: formulário de aprovação da ficha carregado via AJAX
        const formFicha = document.getElementById('form-aprovar-ficha') || document.querySelector('form[action*="arh_aprovar_ficha"]');
        if (formFicha) {
            // Interceptar submit para evitar navegação para JSON
            formFicha.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitter = e.submitter; // botão que originou o submit (moderno)
                const acao = submitter ? submitter.value : (formFicha.querySelector('button[name="acao"]:focus') || {}).value;
                if (!acao) {
                    if (typeof showToast === 'function') showToast('Ação não reconhecida.', 'error');
                    return;
                }

                if (!confirm(`Tem certeza que deseja ${acao === 'aprovar' ? 'aprovar' : 'recusar'} este pedido?`)) return;

                const fd = new FormData();
                fd.append('edicao_id', formFicha.querySelector('input[name="edicao_id"]').value);
                fd.append('acao', acao);

                // Desabilitar botões durante processamento
                const buttons = formFicha.querySelectorAll('button[name="acao"]');
                buttons.forEach(b => { b.disabled = true; b.dataset.originalText = b.innerHTML; b.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .9s linear infinite;"></span>'; });

                fetch('../../api/pedidos/arh_aprovar_ficha.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof showToast === 'function') {
                                showToast(`✅ ${data.message || 'Ficha processada com sucesso.'}`, 'success');
                            } else {
                                alert(data.message || 'Sucesso');
                            }
                            setTimeout(() => {
                                // Voltar para lista de pedidos de fichas
                                navigateToContent('gestao_fichas_colaboradores');
                            }, 1200);
                        } else {
                            if (typeof showToast === 'function') {
                                showToast('❌ ' + (data.message || data.error || 'Erro ao processar.'), 'error');
                            } else {
                                alert(data.message || data.error || 'Erro');
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (typeof showToast === 'function') showToast('❌ Erro de rede', 'error'); else alert('Erro de rede');
                    })
                    .finally(() => {
                        buttons.forEach(b => { b.disabled = false; if (b.dataset.originalText) b.innerHTML = b.dataset.originalText; });
                    });
            }, { once: true }); // garantir que não duplica listeners ao recarregar
        }
    }

    // --------- FICHA FINANCEIRA (in-dash) ---------
    function initializeFichaFinanceiraModule(){
        const container = document.getElementById('finance-form');
        if(!container) return;
        const uid = Number(container.dataset.userId || window.currentAnalyzedUserId || 0);
        const btnGuardar = document.getElementById('btn-guardar');

        function toast(msg, type){ if (typeof showToast==='function') showToast(msg, type==='error'?'error':'success'); else alert(msg); }

        function setField(id, val){ const el = document.getElementById(id); if(!el) return; if(el.type==='checkbox') el.checked = String(val)==='1' || val===1 || val===true; else el.value = (val==null? '': val); }
        function getField(id){ const el = document.getElementById(id); if(!el) return null; return el.type==='checkbox' ? (el.checked?1:0) : el.value; }

        function parseJSONLoose(raw){
            try { return JSON.parse(raw); } catch(_) {}
            const start = raw.search(/\{[\s\r\n]*\"/);
            if (start === -1) return null;
            let depth = 0, inStr = false, esc = false;
            for (let i=start; i<raw.length; i++){
                const ch = raw[i];
                if (inStr){
                    if (esc){ esc=false; continue; }
                    if (ch==='\\'){ esc=true; continue; }
                    if (ch==='"'){ inStr=false; continue; }
                    continue;
                }
                if (ch==='"'){ inStr=true; continue; }
                if (ch==='{') depth++;
                else if (ch==='}'){ depth--; if (depth===0){ const slice = raw.slice(start, i+1); try { return JSON.parse(slice); } catch(_) { return null; } } }
            }
            return null;
        }

        async function load(){
            try{
                const r = await fetch(`/api/finance/profile_get.php?user_id=${encodeURIComponent(uid)}`, { credentials:'same-origin', headers:{ 'Accept':'application/json' } });
                const raw = await r.text();
                const d = parseJSONLoose(raw) || { ok:false };
                                if(!r.ok || !d || d.ok!==true){
                    if (/^\s*</.test(raw) && raw.toLowerCase().includes('<html')) throw new Error('UNAUTHENTICATED');
                    throw new Error((d&&d.code)||'API');
                }
                                const v = d.data||{};
                                // Set fields (prefer new names; keep fallback for API variations)
                                if (Object.prototype.hasOwnProperty.call(v,'duodecimos')) setField('duodecimos', v['duodecimos']);
                                else if (Object.prototype.hasOwnProperty.call(v,'duodecimos_sn')) setField('duodecimos', v['duodecimos_sn'] ? 2 : 1);
                                if (Object.prototype.hasOwnProperty.call(v,'ferias_start')) setField('ferias_start', v['ferias_start']);
                                if (Object.prototype.hasOwnProperty.call(v,'ferias_end')) setField('ferias_end', v['ferias_end']);
                                if (Object.prototype.hasOwnProperty.call(v,'baixa_medica_start')) setField('baixa_medica_start', v['baixa_medica_start']);
                                if (Object.prototype.hasOwnProperty.call(v,'baixa_medica_end')) setField('baixa_medica_end', v['baixa_medica_end']);
                [
                                    'numero','nome_completo','vencimento_estimado','vencimento_base','valor_sub_alimentacao','dias_sub_alimentacao','kms_estimados','valor_por_km','valor_prevencoes','valor_passe_transporte','iht','ajuda_custo_estimado','subsidio_noturno','subsidio_turno','ajudas_custos_deduc','adiantamentos_deduzir','bonus_bonificacoes','prevencoes_sn','penhoras_sn','ferias_sn','faltas_nao_rem','faltas_nao_rem_just','faltas_rem_just','observacoes','ajustes_vencimento'
                ].forEach(k=>{ if (k in v) setField(k, v[k]); });
            }catch(e){ console.error(e); toast('Falha ao carregar ficha financeira','error'); }
        }

                async function save(){
                        const payload = { user_id: uid };
                        const assign = (k)=>{
                                const el = document.getElementById(k);
                                if (!el) return;
                                if (el.type==='checkbox'){ payload[k] = el.checked?1:0; return; }
                                const v = (el.value ?? '').toString().trim();
                                if (v==='') return; // skip empty
                                if (el.type==='number') { const n = Number(v); if (!Number.isNaN(n)) payload[k] = n; } else { payload[k] = v; }
                        };
                        [
                                        'numero','nome_completo','vencimento_estimado','vencimento_base','valor_sub_alimentacao','dias_sub_alimentacao','kms_estimados','valor_por_km','valor_prevencoes','valor_passe_transporte','iht','ajuda_custo_estimado','subsidio_noturno','subsidio_turno','ajudas_custos_deduc','adiantamentos_deduzir','bonus_bonificacoes','duodecimos','prevencoes_sn','penhoras_sn','ferias_sn','faltas_nao_rem','faltas_nao_rem_just','faltas_rem_just','baixa_medica_start','baixa_medica_end','ferias_start','ferias_end','observacoes','ajustes_vencimento'
                        ].forEach(assign);
                                    // Map/augment for API compatibility: also send duodecimos_sn
                                    const duoEl = document.getElementById('duodecimos');
                                    if (duoEl){
                                        const n = parseInt((duoEl.value||'').toString().trim(),10);
                                        if (!Number.isNaN(n)) {
                                            payload.duodecimos = n;
                                            payload.duodecimos_sn = (n===2?1:0);
                                        }
                                    }
            try{
                const r = await fetch('/api/finance/profile_update.php', { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, credentials:'same-origin', body: JSON.stringify(payload)});
                const raw = await r.text();
                const d = parseJSONLoose(raw) || { ok:false };
                if(!r.ok || !d || d.ok!==true){
                    if (/^\s*</.test(raw) && raw.toLowerCase().includes('<html')) throw new Error('UNAUTHENTICATED');
                    throw new Error((d&&d.code)||'API');
                }
                toast('✅ Ficha financeira guardada.','success');
            }catch(e){ console.error(e); toast('❌ Falha ao guardar: '+(e && e.message ? e.message : ''),'error'); }
        }

        if(btnGuardar && !btnGuardar.__bound){ btnGuardar.addEventListener('click', save); btnGuardar.__bound=true; }
        // Bind export button (download individual finance profile as Excel)
        const btnExport = document.getElementById('btn-exportar');
        if (btnExport && !btnExport.__bound){
            btnExport.addEventListener('click', async function(){
                try{
                    btnExport.disabled = true;
                    const url = `/api/finance/profile_export.php?user_id=${encodeURIComponent(uid)}`;
                    const resp = await fetch(url, { credentials:'same-origin' });
                    const ct = (resp.headers.get('content-type')||'').toLowerCase();
                    if (!resp.ok || (!ct.includes('sheet') && !ct.includes('excel') && !ct.includes('octet'))){
                        const txt = await resp.text();
                        const code = (txt||'').trim();
                        if (code === 'UNAUTHENTICATED') throw new Error('Sessão expirada. Faça login.');
                        if (code === 'FORBIDDEN') throw new Error('Sem permissão.');
                        throw new Error(code || ('HTTP '+resp.status));
                    }
                    const blob = await resp.blob();
                    let fname = 'ficha_financeira_'+uid+'.xlsx';
                    const cd = resp.headers.get('content-disposition') || '';
                    const m = cd.match(/filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i);
                    if (m){ fname = decodeURIComponent(m[1] || m[2] || fname); }
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = fname;
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(()=>{ URL.revokeObjectURL(a.href); a.remove(); }, 1000);
                }catch(e){ console.error(e); if (typeof showToast==='function') showToast('❌ Exportação falhou: '+(e&&e.message?e.message:String(e)),'error'); else alert('Exportação falhou'); }
                finally{ btnExport.disabled = false; }
            });
            btnExport.__bound = true;
        }
        load();
    }

    // Função para lidar com aprovação/rejeição de pedidos
    function handleAprovacaoPedido(pedidoId, acao) {
        const formData = new FormData();
        formData.append('pedido_id', pedidoId);
        formData.append('acao', acao);

        fetch('../../api/pedidos/arh_aprovar_ficha.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Pedido ${acao === 'aprovar' ? 'aprovado' : 'rejeitado'} com sucesso!`);
                // Voltar para a lista de aprovações
                navigateToContent('gestao_fichas_colaboradores');
            } else {
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar pedido.');
        });
    }

    // Inicializar módulo de marcação direta
    function initializeMarcacaoDiretaModule() {
        // Adicionar funcionalidades específicas para marcação direta
    }

    // Inicializar módulo de criar colaborador
    function initializeCriarColaboradorModule() {
        console.log('Inicializando módulo criar colaborador');

        // Função para carregar empresas
        function carregarEmpresas() {
            console.log('Carregando empresas...');
            const selectEmpresa = document.getElementById('empresa');
            
            if (!selectEmpresa) {
                console.log('Select de empresas não encontrado');
                return;
            }

            fetch('../../api/pedidos/listar_empresas_simples.php')
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text);
                    
                    try {
                        const data = JSON.parse(text);
                        console.log('Empresas carregadas:', data);
                        
                        if (data.success && data.empresas && data.empresas.length > 0) {
                            selectEmpresa.innerHTML = '<option value="">Selecione uma empresa</option>';
                            
                            data.empresas.forEach(empresa => {
                                const option = document.createElement('option');
                                option.value = empresa.id;
                                option.textContent = empresa.name;
                                selectEmpresa.appendChild(option);
                            });
                            
                            console.log(`${data.empresas.length} empresas carregadas com sucesso`);
                        } else {
                            selectEmpresa.innerHTML = '<option value="">Nenhuma empresa encontrada</option>';
                            console.error('Nenhuma empresa encontrada:', data);
                        }
                    } catch (e) {
                        console.error('Erro ao fazer parse JSON:', e);
                        selectEmpresa.innerHTML = '<option value="">Erro ao carregar empresas</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar empresas:', error);
                    selectEmpresa.innerHTML = '<option value="">Erro de conexão</option>';
                });
        }

        // Procurar o formulário na página carregada via AJAX
        const form = document.getElementById('form-colaborador');

        if (form) {
            console.log('Formulário encontrado no dashboard');
            
            // Carregar empresas quando o formulário é encontrado
            carregarEmpresas();

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                console.log('Submit interceptado no dashboard');

                const btn = this.querySelector('.btn-success');
                const btnText = btn.querySelector('.btn-text');
                const mensagemDiv = document.getElementById('mensagem-criacao');

                // Loading state
                if (btn && btnText) {
                    btn.classList.add('loading');
                    btnText.textContent = 'Criando...';
                }

                if (mensagemDiv) {
                    mensagemDiv.innerHTML = '';
                }

                try {
                    const formData = new FormData(this);
                    
                    // Validar se empresa foi selecionada
                    const empresaValue = formData.get('empresa');
                    if (!empresaValue) {
                        throw new Error('Por favor, selecione uma empresa.');
                    }
                    
                    // Ajustar o nome do campo para company_id que a API espera
                    formData.delete('empresa');
                    formData.append('company_id', empresaValue);

                    console.log('Enviando dados para API...');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }

                    const response = await fetch('../../api/pedidos/arh_criar_colaborador.php', {
                        method: 'POST',
                        body: formData
                    });

                    console.log('Status da resposta:', response.status);

                    const responseText = await response.text();
                    console.log('Resposta bruta:', responseText);

                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('Erro ao fazer parse do JSON:', parseError);
                        throw new Error('Resposta inválida do servidor: ' + responseText.substring(0, 100));
                    }

                    if (result.sucesso) {
                        if (mensagemDiv) {
                            mensagemDiv.innerHTML = `<div class="mensagem-sucesso">${result.mensagem}</div>`;
                        }

                        // Mostrar toast de sucesso
                        showToast('✅ Colaborador criado com sucesso!', 'success');

                        this.reset();
                        // Recarregar as empresas após reset
                        carregarEmpresas();
                    } else {
                        if (mensagemDiv) {
                            mensagemDiv.innerHTML = `<div class="mensagem-erro">${result.mensagem}</div>`;
                        }

                        // Mostrar toast de erro
                        showToast('❌ ' + result.mensagem, 'error');
                    }

                } catch (error) {
                    console.error('Erro completo:', error);
                    if (mensagemDiv) {
                        mensagemDiv.innerHTML = `<div class="mensagem-erro">Erro: ${error.message}</div>`;
                    }

                    // Mostrar toast de erro
                    showToast('❌ Erro ao criar colaborador: ' + error.message, 'error');
                } finally {
                    // Remove loading state
                    if (btn && btnText) {
                        btn.classList.remove('loading');
                        btnText.textContent = 'Criar Colaborador';
                    }
                }
            });
        } else {
            console.error('Formulário não encontrado no dashboard');
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
                url = "../admin_rh/ficha_colaborador.php";
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

    // Captura global para impedir navegação para /api/leaves/request.php em conteúdos carregados via AJAX
    try {
        if (!window.__leavesSubmitCapture__) {
            document.addEventListener('submit', function(ev){
                const form = ev.target;
                if (form && form.action && form.action.includes('/api/leaves/request.php')){
                    ev.preventDefault();
                    if (form.__leavesSubmitting) return;
                    form.__leavesSubmitting = true;
                    const fd = new FormData(form);
                    // Normalizar datas
                    try {
                        const di = form.querySelector('#data_inicio');
                        const df = form.querySelector('#data_fim');
                        const norm = v => (/^\d{2}\/\d{2}\/\d{4}$/.test(v) ? `${v.slice(6,10)}-${v.slice(3,5)}-${v.slice(0,2)}` : v);
                        if (di && di.value) fd.set('data_inicio', norm(di.value));
                        if (df && df.value) fd.set('data_fim', norm(df.value));
                    } catch(_) {}
                    const btn = form.querySelector('button[type="submit"], .btn-submit');
                    const original = btn ? btn.innerHTML : '';
                    if (btn){ btn.disabled = true; btn.innerText = 'A enviar...'; }
                    fetch(form.action, { method:'POST', body: fd, credentials: 'same-origin' })
                      .then(async resp => {
                          const ct = resp.headers.get('content-type')||'';
                          let data=null;
                          if (ct.includes('application/json')) data = await resp.json();
                          else { const txt = await resp.text(); try{ data=JSON.parse(txt);}catch{ data={ ok:false, error:txt||'Erro ao processar resposta.'}; } }
                          if (!resp.ok || !data || data.ok===false){
                              const code = data && (data.code || data.error || data.message);
                              const map = { MISSING_FIELDS:'Preencha todos os campos obrigatórios.', INVALID_DATE:'Data inválida.', RANGE_ERROR:'Data de início deve ser anterior à data de fim.', DOC_REQUIRED:'Este tipo exige comprovativo (PDF/JPG/PNG).', BAD_FILETYPE:'Tipo de ficheiro inválido (PDF, JPG, PNG).', FILE_TOO_LARGE:'Ficheiro maior que 5MB.', FILE_MOVE_ERROR:'Erro ao guardar o ficheiro no servidor.', UNAUTHENTICATED:'Sessão expirada. Faça login novamente.', FORBIDDEN_ROLE:'Perfil sem permissão para criar pedidos.', DB_ERROR:'Erro interno ao gravar o pedido.' };
                              if (typeof showToast === 'function') showToast('❌ '+(map[code] || code || ('HTTP '+resp.status)), 'error'); else alert(map[code] || code || ('HTTP '+resp.status));
                          } else {
                              if (typeof showToast === 'function') showToast('✅ Pedido submetido com sucesso.', 'success'); else alert('Pedido submetido com sucesso');
                              window.fecharModalPedido && window.fecharModalPedido();
                              setTimeout(()=>window.location.reload(), 1200);
                          }
                      })
                      .catch(err => { if (typeof showToast === 'function') showToast('❌ '+(err && err.message ? err.message : err), 'error'); else alert(err && err.message ? err.message : String(err)); })
                      .finally(()=>{ if (btn){ btn.disabled=false; btn.innerHTML = original || 'Submeter Pedido'; } form.__leavesSubmitting=false; });
                }
            }, true);
            window.__leavesSubmitCapture__ = true;
        }
    } catch (_) { /* noop */ }
});

// Funções globais para compatibilidade
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
    }
};

// Função para mostrar toasts
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;

    document.body.appendChild(toast);

    // Remover toast após 3 segundos
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

