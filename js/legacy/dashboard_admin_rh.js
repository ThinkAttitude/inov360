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
            case "seguranca_higiene":
                url = "../admin_rh/seguranca_higiene.php";
                break;
            case "ficha_editar":
                // edição completa admin RH
                url = buildFichaEditarUrl();
                break;
            case "horarios":
                url = "../admin_rh/horarios.html";
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
            case "criar_colaborador_v2":
                url = "../admin_rh/criar_colaborador_v2.php";
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
            case "frota":
                url = "../admin_rh/frota.php";
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
            case "seguranca_higiene":
                initializeSegurancaHigieneModule();
                break;
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
            case "criar_colaborador_v2":
                initializeCriarColaboradorV2Module();
                break;
            case "frota":
                initializeFrotaModule();
                break;
        }
    }

    // Inicializar módulo de Segurança e Higiene (cores aleatórias por campo)
    function initializeSegurancaHigieneModule(){
        try{
            const scope = document.getElementById('main-content') || document;
            const cards = scope.querySelectorAll('.sh-card');
            if(!cards.length) return;
            cards.forEach(card => {
                const fields = card.querySelectorAll('.sh-field');
                let okCount = 0;
                fields.forEach(f => {
                    const isOk = Math.random() > 0.35; // ~65% OK
                    f.classList.toggle('ok', isOk);
                    f.classList.toggle('missing', !isOk);
                    if(isOk) okCount++;
                });
                const pct = Math.round((okCount / Math.max(1, fields.length)) * 100);
                const badge = card.querySelector('[data-compliance]');
                if(badge) badge.textContent = pct + '% completo';
            });
        }catch(_){ /* noop */ }
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

    // --- Criar Colaborador V2 (DEMO FRONTEND) ---
    function initializeCriarColaboradorV2Module(){
        const root = document.querySelector('.ccv2-wrapper');
        if(!root){ return; }
    // Força aparência clara (independente de prefers-color-scheme) para manter consistência com restante dashboard
    // Caso no futuro se queira permitir dark, remover esta linha ou adicionar a classe 'allow-dark-ccv2' ao body e ajustar CSS.
    root.classList.add('ccv2-force-light');

    // --- TOASTS DESATIVADOS NESTA PÁGINA ---
    // Pedido: "remover" os toasts porque estão a piscar. Em vez de editar/remover
    // cada chamada espalhada, fazemos shadow da função showToast dentro deste
    // escopo, tornando-as no-ops sem afetar outras páginas/módulos.
    const showToast = undefined; // qualquer "showToast && showToast(...)" fica silencioso

        if(!document.querySelector('link[href*="criar_colaborador_v2.css"]')){
            const link = document.createElement('link');
            link.rel='stylesheet';
            link.href='../../css/criar_colaborador_v2.css';
            document.head.appendChild(link);
        }
        // Carregar Lucide (ícones) apenas uma vez nesta página
        if(!window.__lucideLoaded){
            const s = document.createElement('script');
            s.src='https://unpkg.com/lucide@latest/dist/umd/lucide.min.js';
            s.defer = true;
            s.onload = ()=>{ window.__lucideLoaded = true; try { if(window.lucide){ lucide.createIcons(); } } catch(_){} };
            document.head.appendChild(s);
        } else {
            try { if(window.lucide){ lucide.createIcons(); } } catch(_){}
        }

        const STORAGE_KEY = 'demo_colaboradores_v2';
        const basePerms = ['inicio','horarios','ferias_ausencias','ficha_colab','consulta_pedidos'];
        // NOVAS PERMISSÕES (2025-09): substituir anteriores
        const extraDefs = [
            { key:'criar_users',               label:'Criar Users' },
            { key:'aprovar_alteracoes_ficha',  label:'Aprovar Alterações Ficha' },
            { key:'edicao_completa_ficha',     label:'Edição Completa Ficha' },
            { key:'edicao_financeira_ficha',   label:'Edição Ficha Financeira' },
            { key:'download_mapa_horarios',    label:'Download Mapa Horários' },
            { key:'marcacao_direta_fa',        label:'Marcação Direta Férias/Ausências' },
            { key:'gestao_frota',              label:'Gestão de Frota' },
            { key:'higiene_seguranca',         label:'Higiene & Segurança' }
        ];

        // Mapeamento antigo->novo para migração automática de dados demo guardados no localStorage
        const legacyMap = {
            gestao_fichas: 'edicao_completa_ficha',
            extracao_horarios: 'download_mapa_horarios',
            marcacao_direta: 'marcacao_direta_fa',
            criar_colaborador: 'criar_users',
            frota: 'gestao_frota'
        };

        function loadUsers(){
            try { const raw = localStorage.getItem(STORAGE_KEY); return raw? JSON.parse(raw): []; } catch(_) { return []; }
        }
        function saveUsers(arr){ try { localStorage.setItem(STORAGE_KEY, JSON.stringify(arr)); } catch(_) {} }

        let users = loadUsers();
        // Migrar permissões legado -> novas chaves
        let migrated = false;
        users.forEach(u => {
            if(!Array.isArray(u.extra_perms)) return;
            const updated = new Set();
            u.extra_perms.forEach(p => {
                if(legacyMap[p]) { updated.add(legacyMap[p]); migrated = true; }
                else if(extraDefs.some(d=>d.key===p)) { updated.add(p); }
            });
            u.extra_perms = Array.from(updated);
        });
        if(migrated) { try { localStorage.setItem('demo_colaboradores_v2', JSON.stringify(users)); } catch(_){} }

        // ------------- GRUPOS / PASTAS (NOVA FUNCIONALIDADE) -------------
        // Cada grupo representa uma "pasta" contendo alguns utilizadores.
        // Um utilizador pertence no máximo a UM grupo (simplificação inicial).
        // Estrutura: { id:number, name:string, userIds:number[] }
        const GROUPS_KEY = 'demo_colaboradores_v2_groups';
        function loadGroups(){
            try { const raw = localStorage.getItem(GROUPS_KEY); return raw? JSON.parse(raw): []; } catch(_){ return []; }
        }
        function saveGroups(arr){ try { localStorage.setItem(GROUPS_KEY, JSON.stringify(arr)); } catch(_){} }
        let groups = loadGroups();
        let groupOpenState = {}; // estado expandido/colapsado em memória
        groups.forEach(g=>{ if(!(g.id in groupOpenState)) groupOpenState[g.id]=true; });
        let activeGroupId = null; // null => Global (todos)

    // Arrays da hierarquia devem existir antes de qualquer renderList para evitar erro de TDZ
    let nodes = [];      // { id, x, y }
    let draftEdges = []; // ligações em rascunho
    let savedEdges = []; // ligações persistidas usadas nas relações dos cards

        function createGroup(name){
            name = (name||'').trim(); if(!name) return;
            const id = Date.now();
            groups.push({ id, name, userIds: [] });
            groupOpenState[id] = true;
            saveGroups(groups);
            rebuildGroupSelect();
            renderList();
        }
        function assignUserToGroup(userId, groupId){
            // remover de grupo atual
            groups.forEach(g=>{ const i = g.userIds.indexOf(userId); if(i!==-1) g.userIds.splice(i,1); });
            const g = groups.find(g=>g.id===groupId); if(g && !g.userIds.includes(userId)) g.userIds.push(userId);
            saveGroups(groups);
            renderList();
        }
        function removeUserFromGroup(userId){
            let changed=false; groups.forEach(g=>{ const i=g.userIds.indexOf(userId); if(i!==-1){ g.userIds.splice(i,1); changed=true; } });
            if(changed){ saveGroups(groups); renderList(); }
        }
        function getUserGroupId(userId){
            const g = groups.find(g=> g.userIds.includes(userId)); return g? g.id : null;
        }

        // Barra de controlo de grupos
        function ensureGroupBar(){
            if(document.getElementById('ccv2-group-bar')) return;
            const bar = document.createElement('div');
            bar.id='ccv2-group-bar';
            bar.innerHTML = `
                <div class="gb-left">
                    <button type="button" class="gb-btn" id="ccv2-new-group">+ Nova Pasta</button>
                    <select id="ccv2-group-select" class="gb-select" title="Selecionar pasta para hierarquia">
                        <option value="">Global (todos)</option>
                    </select>
                </div>
                <div class="gb-right" id="ccv2-active-group-label">Hierarquia: Global</div>
            `;
            // Inserir antes da lista de cards
            const parent = cardsEl.parentElement || root;
            parent.insertBefore(bar, parent.firstChild);
            const btnNew = bar.querySelector('#ccv2-new-group');
            const sel = bar.querySelector('#ccv2-group-select');
            btnNew.addEventListener('click', ()=>{
                const name = prompt('Nome da nova pasta:');
                if(name) createGroup(name);
            });
            sel.addEventListener('change', ()=>{
                const val = sel.value || '';
                const newGroupId = val? Number(val): null;
                if(newGroupId===activeGroupId) return;
                if(isHierarchyDirty()){
                    if(!confirm('Existe hierarquia não guardada nesta pasta. Trocar mesmo assim (perde rascunho)?')){
                        sel.value = activeGroupId||''; return; }
                }
                activeGroupId = newGroupId;
                updateActiveGroupLabel();
                loadHierarchy(true); // reload para nova pasta
                syncHierarchyUI();
                redrawLinks();
            });
            rebuildGroupSelect();
        }

        function rebuildGroupSelect(){
            const sel = document.getElementById('ccv2-group-select');
            if(!sel) return;
            const current = sel.value;
            // limpar excepto primeira option
            [...sel.querySelectorAll('option')].forEach((o,i)=>{ if(i>0) o.remove(); });
            groups.forEach(g=>{
                const opt = document.createElement('option');
                opt.value = g.id; opt.textContent = g.name;
                sel.appendChild(opt);
            });
            if(current && [...sel.options].some(o=>o.value===current)) sel.value=current; else sel.value='';
        }
        function updateActiveGroupLabel(){
            const lbl = document.getElementById('ccv2-active-group-label');
            if(!lbl) return;
            if(activeGroupId==null){ lbl.textContent='Hierarquia: Global'; }
            else { const g = groups.find(x=>x.id===activeGroupId); lbl.textContent = 'Hierarquia: '+ (g? g.name: '—'); }
        }

        function isHierarchyDirty(){
            // simples: diferença entre draft e saved ou nós adicionados
            if(nodes.length !== (savedEdges.__nodesCount||nodes.length)) return true; // sentinel nunca definido => false aqui
            if(draftEdges.length !== savedEdges.length) return true;
            // comparar sets
            const se = new Set(savedEdges.map(e=> e.parent+'=>'+e.child));
            for(const e of draftEdges){ if(!se.has(e.parent+'=>'+e.child)) return true; }
            return false;
        }

    const form = document.getElementById('ccv2-form');
    const cardsEl = document.getElementById('ccv2-cards');
    const emptyEl = document.getElementById('ccv2-empty');
    const modal = document.getElementById('ccv2-modal');
    const permsForm = document.getElementById('ccv2-perms-form');
    const saveStatus = document.getElementById('ccv2-save-status');
    let currentUserId = null;

    // Agora que cardsEl existe podemos montar a barra de grupos
    ensureGroupBar();
    updateActiveGroupLabel();

        function renderList(){
            cardsEl.innerHTML='';
            emptyEl.style.display = users.length? 'none':'block';

            // Mapas de relações hierárquicas
            let parentMap = {}, childParents = {};
            if(Array.isArray(savedEdges)){
                savedEdges.forEach(ed=>{
                    (parentMap[ed.parent] = parentMap[ed.parent] || []).push(ed.child);
                    (childParents[ed.child] = childParents[ed.child] || []).push(ed.parent);
                });
            }

            function buildCard(u){
                const card = document.createElement('div');
                card.className='ccv2-card';
                card.dataset.id = u.id;
                const extraBadges = (u.extra_perms||[]).length ? u.extra_perms.map(k=> `<span class="extra">${(extraDefs.find(d=>d.key===k)||{}).label||k}</span>`).join('') : '<span>Nenhuma permissão extra</span>';
                let relHtml='';
                if(childParents[u.id]){
                    const parents = childParents[u.id].map(pid=> users.find(x=>x.id===pid)).filter(Boolean);
                    if(parents.length){
                        relHtml += `<div class="rel rel-parent"><span class="rel-label">${parents.length>1? 'Superiores':'Superior'}</span><span class="rel-value">${parents.map(p=>`<span class=\"rel-badge\">${escapeHtml(p.nome)}</span>`).join('')}</span></div>`;
                    }
                }
                if(parentMap[u.id]){
                    const childs = parentMap[u.id].map(cid=> users.find(z=>z.id===cid)).filter(Boolean);
                    if(childs.length){
                        relHtml += `<div class="rel rel-super"><span class="rel-label">Supervisor de</span><span class="rel-value">${childs.map(c=>`<span class=\"rel-badge\">${escapeHtml(c.nome)}</span>`).join('')}</span></div>`;
                    }
                }
                const inGroup = getUserGroupId(u.id)!=null;
                // Botões posicionados via CSS (add-canvas no canto inferior direito, remove-group no topo direito)
                card.innerHTML = `
                    ${inGroup? '<button type="button" class="card-remove-group" title="Remover da pasta"><i data-lucide="x"></i></button>':''}
                    <button type="button" class="card-add-canvas" title="Adicionar à hierarquia"><i data-lucide="network"></i></button>
                    <h4>${escapeHtml(u.nome)}</h4>
                    <div class="email">${escapeHtml(u.email)}</div>
                    <div class="perm-badges">${extraBadges}</div>
                    ${relHtml}
                    <div class="meta"><span>ID: ${u.id}</span><span>${new Date(u.created_at).toLocaleDateString('pt-PT')}</span></div>`;
                card.addEventListener('click', e=>{ if(e.target.closest('.card-remove-group') || e.target.closest('.card-add-canvas')) return; openModal(u.id); });
                // Botão adicionar ao canvas
                const addBtn = card.querySelector('.card-add-canvas');
                if(addBtn){
                    addBtn.addEventListener('click', ev=>{
                        ev.stopPropagation();
                        if(typeof addUserToCanvasAuto==='function') addUserToCanvasAuto(u.id);
                    });
                }
                if(inGroup){
                    const rmBtn = card.querySelector('.card-remove-group');
                    // fallback opcional: se lucide não carregar, mostrar X
                    if(!rmBtn.querySelector('svg')) rmBtn.innerHTML = '<i data-lucide="x"></i>';
                    rmBtn.addEventListener('click', ev=>{ ev.stopPropagation(); removeUserFromGroup(u.id); });
                }
                card.draggable = true;
                card.addEventListener('dragstart', ev=>{ ev.dataTransfer.setData('text/plain', String(u.id)); });
                return card;
            }

            function groupHue(id){ return id % 360; }

            // Adiciona um único utilizador ao canvas numa posição automática em grelha (mantendo dentro do viewport)
            function addUserToCanvasAuto(userId){
                if(!canvas || nodes.some(n=>n.id===userId)){
                    // Já existe: highlight e centra na área visível se possível
                    const elExisting = canvas && canvas.querySelector(`.hier-node[data-id="${userId}"]`);
                    if(elExisting){
                        elExisting.classList.add('pulse');
                        setTimeout(()=>elExisting.classList.remove('pulse'),1600);
                        try { elExisting.scrollIntoView({behavior:'smooth', block:'nearest', inline:'nearest'}); } catch(_){}
                    }
                    return;
                }
                const baseX = 40;
                const baseY0 = 40;
                const spacingX = 160; // horizontal gap
                const spacingY = 140; // vertical gap
                const marginRight = 40;
                const marginBottom = 60;
                const canvasWidth = canvas.clientWidth || canvas.offsetWidth || 800;
                const canvasHeight = canvas.clientHeight || canvas.offsetHeight || 600;
                // Tentar obter dimensão real de um node existente para evitar overflow horizontal
                let sampleNode = canvas.querySelector('.hier-node');
                const nodeWidth = sampleNode? sampleNode.offsetWidth : 140;
                const nodeHeight = sampleNode? sampleNode.offsetHeight : 80;

                // Agrupar nós existentes por "linha" aproximada
                const rows = [];
                nodes.forEach(n=>{
                    let row = rows.find(r=> Math.abs(r.y - n.y) < spacingY/2);
                    if(!row){ row = { y: n.y, nodes: [] }; rows.push(row); }
                    row.nodes.push(n);
                });
                rows.sort((a,b)=> a.y - b.y);

                let nextX, nextY;
                if(!rows.length){
                    nextX = baseX; nextY = baseY0;
                } else {
                    const lastRow = rows[rows.length-1];
                    const rowNodeCount = lastRow.nodes.length;
                    nextY = lastRow.y; // tentar mesma linha
                    nextX = baseX + rowNodeCount * spacingX;
                    // Verificar se cabe na largura
                    if(nextX + nodeWidth > canvasWidth - marginRight){
                        // Nova linha
                        nextX = baseX;
                        nextY = lastRow.y + spacingY;
                    }
                }

                // Clamp dentro da largura
                if(nextX + nodeWidth > canvasWidth - marginRight){
                    nextX = Math.max(baseX, canvasWidth - marginRight - nodeWidth);
                }
                // Se passa a altura visível e canvas não tem scroll, aumentar altura mínima
                if(nextY + nodeHeight > canvasHeight - marginBottom){
                    // Expandir altura (sem encolher posteriormente) para manter drag possível
                    const newH = nextY + nodeHeight + marginBottom;
                    if(newH > canvasHeight){
                        canvas.style.minHeight = newH + 'px';
                    }
                }

                // Evitar colisão exacta: se já existir node nessa célula, deslocar para a direita até caber ou quebrar linha
                let safety = 20;
                while(safety-- > 0 && nodes.some(n=> Math.abs(n.x - nextX) < 5 && Math.abs(n.y - nextY) < 5)){
                    nextX += spacingX;
                    if(nextX + nodeWidth > canvasWidth - marginRight){
                        nextX = baseX; nextY += spacingY;
                        if(nextY + nodeHeight > canvasHeight - marginBottom){
                            const newH2 = nextY + nodeHeight + marginBottom;
                            if(newH2 > (canvas.clientHeight||0)) canvas.style.minHeight = newH2 + 'px';
                        }
                    }
                }

                nodes.push({ id: userId, x: nextX, y: nextY });
                syncHierarchyUI();
                redrawLinks();
                const el = canvas.querySelector(`.hier-node[data-id="${userId}"]`);
                if(el){
                    el.classList.add('pulse');
                    setTimeout(()=>el.classList.remove('pulse'),1600);
                    try { el.scrollIntoView({behavior:'smooth', block:'nearest', inline:'nearest'}); } catch(_){ }
                }
            }

            // Adiciona todos os utilizadores de uma pasta ao canvas (sem duplicar)
            function addGroupUsersToCanvas(group){
                if(!canvas || !group || !Array.isArray(group.userIds) || !group.userIds.length) return;
                // Calcular posição base abaixo dos nós existentes para evitar sobreposição grosseira
                let maxY = 0; nodes.forEach(n=>{ if(n.y>maxY) maxY = n.y; });
                const baseY = nodes.length? maxY + 140 : 40;
                const baseX = 40;
                const count = group.userIds.length;
                const cols = Math.ceil(Math.sqrt(count));
                const spacingX = 160; const spacingY = 110;
                group.userIds.forEach((uid, idx)=>{
                    if(nodes.some(n=> n.id===uid)) return; // já existe
                    const col = idx % cols; const row = Math.floor(idx/cols);
                    nodes.push({ id: uid, x: baseX + col*spacingX, y: baseY + row*spacingY });
                });
                syncHierarchyUI();
                redrawLinks();
            }

            function buildGroup(g){
                const open = groupOpenState[g.id]!==false;
                const wrap = document.createElement('div');
                wrap.className='ccv2-folder';
                wrap.dataset.groupId = g.id;
                wrap.style.setProperty('--folder-accent-h', groupHue(g.id));
                wrap.innerHTML = `
                    <div class="ccv2-folder-header">
                        <button class="fh-toggle" title="Expandir/Colapsar" draggable="true" data-state="${open? 'open':'closed'}"><i data-lucide="${open? 'chevron-down':'chevron-right'}"></i></button>
                        <div class="fh-title" title="Clique para renomear">${escapeHtml(g.name)}</div>
                        <div class="fh-actions">
                            <span class="fh-count" title="Colaboradores">${g.userIds.length}</span>
                            <button class="fh-activate" title="Ativar pasta para hierarquia"><i data-lucide="network"></i></button>
                            <button class="fh-rename" title="Renomear"><i data-lucide="edit"></i></button>
                            <button class="fh-delete" title="Apagar pasta"><i data-lucide="trash-2"></i></button>
                        </div>
                    </div>
                    <div class="ccv2-folder-body" style="display:${open?'grid':'none'}"></div>`;
                const header = wrap.querySelector('.ccv2-folder-header');
                const body = wrap.querySelector('.ccv2-folder-body');
                header.classList.toggle('active', activeGroupId===g.id);
                if(activeGroupId===g.id) wrap.classList.add('active-group');

                // Toggle open (evita reconstruir tudo para não perder listeners)
                const toggleBtn = header.querySelector('.fh-toggle');
                toggleBtn.addEventListener('click', e=>{
                    e.stopPropagation();
                    const isOpenNow = body.style.display !== 'none';
                    const newState = !isOpenNow;
                    groupOpenState[g.id] = newState;
                    body.style.display = newState ? 'grid' : 'none';
                    const iconI = toggleBtn.querySelector('i[data-lucide]');
                    if(iconI){
                        iconI.setAttribute('data-lucide', newState ? 'chevron-down' : 'chevron-right');
                        try { window.lucide && lucide.createIcons(toggleBtn); } catch(_){ }
                    }
                });
                // Ativar pasta e lançar todos os utilizadores no canvas
                header.querySelector('.fh-activate').addEventListener('click', e=>{
                    e.stopPropagation();
                    const sel = document.getElementById('ccv2-group-select');
                    const needsSwitch = activeGroupId !== g.id;
                    if(needsSwitch && sel){ sel.value = g.id; sel.dispatchEvent(new Event('change')); }
                    // Após possível switch (loadHierarchy é síncrono), adicionar users
                    addGroupUsersToCanvas(g);
                });
                // Rename inline
                function startRename(){
                    const titleEl = header.querySelector('.fh-title');
                    const prev = g.name;
                    const input = document.createElement('input');
                    input.type='text'; input.value=prev; input.className='fh-rename-input';
                    titleEl.replaceWith(input); input.focus(); input.select();
                    const commit=(save)=>{ if(save){ const v = input.value.trim(); if(v){ g.name=v; saveGroups(groups); } }
                        input.replaceWith(Object.assign(document.createElement('div'),{className:'fh-title',textContent:g.name,title:'Clique para renomear'}));
                    };
                    input.addEventListener('keydown', ev=>{ if(ev.key==='Enter'){ commit(true); } else if(ev.key==='Escape'){ commit(false); }});
                    input.addEventListener('blur', ()=>commit(true));
                }
                header.querySelector('.fh-rename').addEventListener('click', e=>{ e.stopPropagation(); startRename(); });
                header.addEventListener('dblclick', e=>{ if(e.target.classList.contains('fh-title')) startRename(); });
                // Delete group
                header.querySelector('.fh-delete').addEventListener('click', e=>{ e.stopPropagation(); if(!confirm('Apagar pasta? Os utilizadores ficarão sem pasta.')) return; g.userIds=[]; groups = groups.filter(x=>x.id!==g.id); saveGroups(groups); renderList(); });

                // Drag reorder groups (usar apenas o toggle como handle)
                toggleBtn.addEventListener('dragstart', ev=>{ ev.dataTransfer.setData('group-id', String(g.id)); ev.dataTransfer.effectAllowed='move'; wrap.classList.add('drag-origin'); });
                toggleBtn.addEventListener('dragend', ()=> wrap.classList.remove('drag-origin'));
                header.addEventListener('dragover', ev=>{ ev.preventDefault(); wrap.classList.add('drop-target'); });
                header.addEventListener('dragleave', ()=> wrap.classList.remove('drop-target'));
                header.addEventListener('drop', ev=>{ ev.preventDefault(); wrap.classList.remove('drop-target'); const fromId = Number(ev.dataTransfer.getData('group-id')); if(!fromId || fromId===g.id) return; const fromIdx = groups.findIndex(x=>x.id===fromId); const toIdx = groups.findIndex(x=>x.id===g.id); if(fromIdx===-1||toIdx===-1) return; const [moved] = groups.splice(fromIdx,1); groups.splice(toIdx,0,moved); saveGroups(groups); renderList(); });

                // Accept user cards drop
                function acceptUserDrop(target){
                    target.addEventListener('dragover', ev=>{ ev.preventDefault(); wrap.classList.add('drag-over'); });
                    target.addEventListener('dragleave', ev=>{ if(ev.relatedTarget && target.contains(ev.relatedTarget)) return; wrap.classList.remove('drag-over'); });
                    target.addEventListener('drop', ev=>{ ev.preventDefault(); wrap.classList.remove('drag-over'); const uid = Number(ev.dataTransfer.getData('text/plain')); if(uid){ assignUserToGroup(uid, g.id); }});
                }
                acceptUserDrop(header);
                acceptUserDrop(body);

                // Preencher cards
                g.userIds.map(uid=> users.find(u=>u.id===uid)).filter(Boolean).forEach(u=> body.appendChild(buildCard(u)));
                // marca para refresh posterior (chamada única)
                return wrap;
            }

            groups.forEach(g=> cardsEl.appendChild(buildGroup(g)));
            // Atualizar ícones apenas em <i data-lucide>. Mantém botões intactos.
            try { if(window.lucide){ lucide.createIcons(cardsEl); } } catch(_){ }

            // Colaboradores sem pasta
            const ungrouped = users.filter(u=> !getUserGroupId(u.id));
            if(ungrouped.length){
                const fake = { id:0, name:'(Sem Pasta)', userIds:[] };
                const wrap = document.createElement('div'); wrap.className='ccv2-folder ungrouped'; wrap.innerHTML = `<div class="ccv2-folder-header"><div class="fh-title">Sem Pasta</div><div class="fh-actions"><span class="fh-count">${ungrouped.length}</span></div></div><div class="ccv2-folder-body" style="display:grid"></div>`;
                const body = wrap.querySelector('.ccv2-folder-body');
                ungrouped.forEach(u=> body.appendChild(buildCard(u)));
                cardsEl.appendChild(wrap);
            }

            // Altura scroll adaptativa
            if(users.length>4){ cardsEl.classList.add('scroll-active'); } else { cardsEl.classList.remove('scroll-active'); }
        }

        function escapeHtml(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;' }[c])); }

        function openModal(id){
            const u = users.find(x=>x.id===id); if(!u) return;
            currentUserId = id;
            modal.setAttribute('aria-hidden','false');
            document.body.style.overflow='hidden';
            const title = document.getElementById('ccv2-modal-title');
            if(title) title.textContent = `Permissões: ${u.nome}`;
            permsForm.reset();
            (u.extra_perms||[]).forEach(k=>{ const cb = permsForm.querySelector(`input[name="${k}"]`); if(cb) cb.checked = true; });
            saveStatus.textContent=''; saveStatus.className='save-status';
        }
        function closeModal(){ modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; currentUserId=null; }
        modal.querySelectorAll('[data-close]').forEach(b=> b.addEventListener('click', closeModal));
        modal.addEventListener('click', e=>{ if(e.target.classList.contains('ccv2-modal-backdrop')) closeModal(); });
        document.addEventListener('keydown', e=>{ if(e.key==='Escape' && modal.getAttribute('aria-hidden')==='false') closeModal(); });

        if(form && !form.__bound){
            form.addEventListener('submit', function(ev){
                ev.preventDefault();
                const btn = form.querySelector('.ccv2-submit');
                btn && btn.classList.add('loading');
                const nome = form.nome.value.trim();
                const email = form.email.value.trim().toLowerCase();
                const password = form.password.value;
                if(!nome || !email || !password){ showToast && showToast('❌ Preencha todos os campos.','error'); btn && btn.classList.remove('loading'); return; }
                if(users.some(u=>u.email===email)){ showToast && showToast('❌ Email já existente nesta demo.','error'); btn && btn.classList.remove('loading'); return; }
                const id = Date.now();
                const newUser = { id, nome, email, password_demo: password, created_at: new Date().toISOString(), base_perms: basePerms.slice(), extra_perms: [] };
                users.push(newUser); saveUsers(users); renderList();
                form.reset();
                btn && btn.classList.remove('loading');
                showToast && showToast('✅ Conta demo criada. Agora configure permissões.','success');
            });
            form.__bound=true;
        }

        if(permsForm && !permsForm.__bound){
            permsForm.addEventListener('submit', function(ev){
                ev.preventDefault(); if(currentUserId==null) return;
                const btn = permsForm.querySelector('.btn-save-perms');
                btn && btn.classList.add('loading');
                const selected = Array.from(permsForm.querySelectorAll('input[type="checkbox"]:checked')).map(cb=>cb.name);
                const idx = users.findIndex(u=>u.id===currentUserId); if(idx!==-1){ users[idx].extra_perms = selected; saveUsers(users); }
                setTimeout(()=>{ btn && btn.classList.remove('loading'); saveStatus.textContent='Guardado'; saveStatus.className='save-status ok'; showToast && showToast('✅ Permissões atualizadas (demo).','success'); renderList(); }, 450);
            });
            permsForm.__bound=true;
        }

        // ---------------- HIERARCHY BUILDER DEMO ----------------
    const HIER_KEY_BASE = 'demo_colaboradores_v2_hierarchy';
    function getHierKey(){ return activeGroupId!=null? `${HIER_KEY_BASE}_${activeGroupId}` : HIER_KEY_BASE; }
        const canvas = document.getElementById('ccv2-hierarchy-canvas');
        const svg = document.getElementById('ccv2-hierarchy-links');
        const placeholder = document.getElementById('ccv2-hier-placeholder');
        const btnLinkMode = document.getElementById('ccv2-link-mode');
        const btnSaveHier = document.getElementById('ccv2-save-hierarchy');
    const btnClearHier = document.getElementById('ccv2-clear-hierarchy'); // Limpa só canvas
    const btnClearLinks = document.getElementById('ccv2-clear-links'); // Limpa ligações (edges) dos nodes presentes
    const btnZoomIn = document.getElementById('ccv2-zoom-in');
    const btnZoomOut = document.getElementById('ccv2-zoom-out');

    let zoom = 1; const ZOOM_MIN = 0.5; const ZOOM_MAX = 2; const ZOOM_STEP = 0.1;

        let linkMode = false; let selectedParent = null; let draggingNode = null; let dragOffset = {x:0,y:0};

    function loadHierarchy(fromGroupSwitch){
            try {
        const raw = localStorage.getItem(getHierKey()); if(!raw) { nodes=[]; draftEdges=[]; savedEdges=[]; return; }
                const obj = JSON.parse(raw);
                nodes = (obj.nodes||[]).filter(n=> users.some(u=>u.id===n.id));
                savedEdges = (obj.edges||[]).filter(e=> users.some(u=>u.id===e.parent)&& users.some(u=>u.id===e.child));
                draftEdges = [...savedEdges];
        if(fromGroupSwitch){ /* reset placeholder etc */ }
            } catch(_){ nodes=[]; draftEdges=[]; savedEdges=[]; }
        }
        function saveHierarchy(){
            try {
        localStorage.setItem(getHierKey(), JSON.stringify({nodes,edges:draftEdges}));
                savedEdges = [...draftEdges];
                showToast && showToast('✅ Hierarquia guardada (demo).','success');
                renderList(); // agora reflete ligações persistidas
            } catch(_){ showToast && showToast('❌ Falha ao guardar hierarquia.','error'); }
        }
    function clearHierarchy(){ nodes=[]; draftEdges=[]; savedEdges=[]; syncHierarchyUI(); redrawLinks(); renderList(); }

        function ensureSvgDefs(){ if(svg && !svg.querySelector('marker#hierArrow')){ const defs = document.createElementNS('http://www.w3.org/2000/svg','defs'); const marker = document.createElementNS('http://www.w3.org/2000/svg','marker'); marker.setAttribute('id','hierArrow'); marker.setAttribute('viewBox','0 0 10 10'); marker.setAttribute('refX','10'); marker.setAttribute('refY','5'); marker.setAttribute('markerWidth','8'); marker.setAttribute('markerHeight','8'); marker.setAttribute('orient','auto-start-reverse'); const path = document.createElementNS('http://www.w3.org/2000/svg','path'); path.setAttribute('d','M 0 0 L 10 5 L 0 10 z'); marker.appendChild(path); defs.appendChild(marker); svg.appendChild(defs);} }

        function syncHierarchyUI(){
            if(!canvas) return; placeholder.style.display = nodes.length? 'none':'flex';
            // Remove stale nodes
            canvas.querySelectorAll('.hier-node').forEach(el=>{ const id = Number(el.getAttribute('data-id')); if(!nodes.some(n=>n.id===id)) el.remove(); });
            // Add/update nodes
            nodes.forEach(n=>{
                let el = canvas.querySelector(`.hier-node[data-id="${n.id}"]`);
                const user = users.find(u=>u.id===n.id); if(!user) return;
                if(!el){
                    el = document.createElement('div');
                    el.className='hier-node';
                    el.setAttribute('data-id', n.id);
                    el.innerHTML = `<div class="hn-name">${escapeHtml(user.nome)}</div><div class="hn-email">${escapeHtml(user.email)}</div>`;
                    canvas.appendChild(el);
                    // Drag within canvas (pointer events for smoothness)
                    el.addEventListener('mousedown', e=>{ draggingNode = n; dragOffset.x = e.offsetX; dragOffset.y = e.offsetY; el.classList.add('dragging'); });
                    // Double-click para iniciar/alterar modo ligação escolhendo supervisor
                    el.addEventListener('dblclick', e=>{
                        e.stopPropagation();
                        if(!linkMode){
                            linkMode = true; selectedParent = n.id;
                            canvas.querySelectorAll('.hier-node').forEach(nd=>nd.classList.remove('selected-parent'));
                            el.classList.add('selected-parent');
                            showToast && showToast('Modo ligação ativo. Clique num subordinado.','success');
                        } else {
                            if(selectedParent === n.id){
                                linkMode=false; selectedParent=null;
                                canvas.querySelectorAll('.hier-node').forEach(nd=>nd.classList.remove('selected-parent'));
                                showToast && showToast('Modo ligação cancelado.','error');
                            } else {
                                selectedParent = n.id;
                                canvas.querySelectorAll('.hier-node').forEach(nd=>nd.classList.remove('selected-parent'));
                                el.classList.add('selected-parent');
                                showToast && showToast('Supervisor alterado. Clique num subordinado.','success');
                            }
                        }
                    });
                    // Clique simples quando em modo ligação escolhe o subordinado e termina
                    el.addEventListener('click', e=>{
                        if(!linkMode || selectedParent==null || selectedParent===n.id) return;
                        // Evitar duplicados
                        if(!draftEdges.some(ed=> ed.parent===selectedParent && ed.child===n.id)){
                            draftEdges.push({parent:selectedParent, child:n.id});
                        }
                        redrawLinks();
                        showToast && showToast('Ligação criada (rascunho). Guardar para persistir.','success');
                        linkMode=false; selectedParent=null;
                        canvas.querySelectorAll('.hier-node').forEach(nd=>nd.classList.remove('selected-parent'));
                        e.stopPropagation();
                    });
                }
                // Position
                el.style.transform = `translate(${n.x}px, ${n.y}px) scale(${zoom})`;
            });
            redrawLinks();
        }

    function redrawLinks(){
        if(!svg) return; ensureSvgDefs(); svg.querySelectorAll('path').forEach(p=>p.remove());
        const canvasRect = canvas.getBoundingClientRect();
        draftEdges.forEach(ed=>{
            const pNode = canvas.querySelector(`.hier-node[data-id="${ed.parent}"]`);
            const cNode = canvas.querySelector(`.hier-node[data-id="${ed.child}"]`);
            if(!pNode||!cNode) return;
            const pRect = pNode.getBoundingClientRect();
            const cRect = cNode.getBoundingClientRect();
            // Ajustar para zoom: posição central relativa ao canvas sem dividir por zoom (porque estamos a aplicar scale nos nodes)
            const px = (pRect.left + pRect.width/2 - canvasRect.left);
            const py = (pRect.top + pRect.height - canvasRect.top);
            const cx = (cRect.left + cRect.width/2 - canvasRect.left);
            const cy = (cRect.top - canvasRect.top);
            const midY = (py + cy)/2;
            const d = `M ${px} ${py} C ${px} ${midY}, ${cx} ${midY}, ${cx} ${cy}`;
            const path = document.createElementNS('http://www.w3.org/2000/svg','path');
            path.setAttribute('d', d);
            path.setAttribute('data-parent', ed.parent);
            path.setAttribute('data-child', ed.child);
            svg.appendChild(path);
        });
    }

        function addNodeForUser(userId, x, y){ if(nodes.some(n=>n.id===userId)) return; // default offset inside canvas
            const rect = canvas.getBoundingClientRect(); const nx = x - rect.left - 70; const ny = y - rect.top - 30; nodes.push({id:userId, x:Math.max(0,nx), y:Math.max(0,ny)}); syncHierarchyUI(); }

        if(canvas){
            canvas.addEventListener('dragover', e=>{ e.preventDefault(); canvas.classList.add('drag-over'); e.dataTransfer.dropEffect='copy'; });
            canvas.addEventListener('dragleave', ()=> canvas.classList.remove('drag-over'));
            canvas.addEventListener('drop', e=>{ e.preventDefault(); canvas.classList.remove('drag-over'); const id = Number(e.dataTransfer.getData('text/plain')); if(!id) return; addNodeForUser(id, e.clientX, e.clientY); });
            // Desenho otimizado durante drag: apenas move o node e atualiza as ligações ligadas
            function updateLinksFor(nodeId){
                const canvasRect = canvas.getBoundingClientRect();
                const paths = svg.querySelectorAll(`path[data-parent="${nodeId}"], path[data-child="${nodeId}"]`);
                paths.forEach(path=>{
                    const pId = Number(path.getAttribute('data-parent'));
                    const cId = Number(path.getAttribute('data-child'));
                    const pNode = canvas.querySelector(`.hier-node[data-id="${pId}"]`);
                    const cNode = canvas.querySelector(`.hier-node[data-id="${cId}"]`);
                    if(!pNode||!cNode) return;
                    const pRect = pNode.getBoundingClientRect();
                    const cRect = cNode.getBoundingClientRect();
                    const px = (pRect.left + pRect.width/2 - canvasRect.left);
                    const py = (pRect.top + pRect.height - canvasRect.top);
                    const cx = (cRect.left + cRect.width/2 - canvasRect.left);
                    const cy = (cRect.top - canvasRect.top);
                    const midY = (py + cy)/2;
                    const d = `M ${px} ${py} C ${px} ${midY}, ${cx} ${midY}, ${cx} ${cy}`;
                    path.setAttribute('d', d);
                });
            }
            let dragRaf = null;
            window.addEventListener('mousemove', e=>{
                if(!draggingNode) return;
                const rect = canvas.getBoundingClientRect();
                draggingNode.x = Math.min(rect.width-60, Math.max(0, e.clientX - rect.left - dragOffset.x));
                draggingNode.y = Math.min(rect.height-40, Math.max(0, e.clientY - rect.top - dragOffset.y));
                if(!dragRaf){
                    dragRaf = requestAnimationFrame(()=>{
                        dragRaf = null;
                        const el = canvas.querySelector(`.hier-node[data-id="${draggingNode.id}"]`);
                        if(el){ el.style.transform = `translate(${draggingNode.x}px, ${draggingNode.y}px) scale(${zoom})`; }
                        updateLinksFor(draggingNode.id);
                    });
                }
            });
            window.addEventListener('mouseup', ()=>{ if(draggingNode){ const el = canvas.querySelector(`.hier-node[data-id="${draggingNode.id}"]`); if(el) el.classList.remove('dragging'); draggingNode=null; } });
            canvas.addEventListener('click', ()=>{ if(linkMode){ linkMode=false; selectedParent=null; canvas.querySelectorAll('.hier-node').forEach(nd=>nd.classList.remove('selected-parent')); showToast && showToast('Modo ligação cancelado.','error'); }});
        }

        // Esconde botão antigo de modo ligação (agora via double-click)
        if(btnLinkMode){ btnLinkMode.style.display='none'; }
        if(btnSaveHier){ btnSaveHier.addEventListener('click', saveHierarchy); }
    if(btnZoomIn){ btnZoomIn.addEventListener('click', ()=>{ zoom = Math.min(ZOOM_MAX, +(zoom + ZOOM_STEP).toFixed(2)); syncHierarchyUI(); }); }
    if(btnZoomOut){ btnZoomOut.addEventListener('click', ()=>{ zoom = Math.max(ZOOM_MIN, +(zoom - ZOOM_STEP).toFixed(2)); syncHierarchyUI(); }); }
    if(btnClearHier){ btnClearHier.addEventListener('click', ()=>{ if(confirm('Limpar apenas o canvas? A hierarquia guardada mantém-se.')){
            // Limpa só nós e linhas visuais; mantém edges para relações e não sobrescreve storage
            nodes = [];
            syncHierarchyUI(); // isto remove nós existentes
            svg && svg.querySelectorAll('path').forEach(p=>p.remove());
            placeholder && (placeholder.style.display='flex');
            showToast && showToast('Canvas limpo. Hierarquia guardada intacta.','success');
        }}); }
    if(btnClearLinks){ btnClearLinks.addEventListener('click', ()=>{ if(!nodes.length){ showToast && showToast('Nenhum colaborador no canvas.','error'); return; }
        if(confirm('Remover apenas as ligações entre colaboradores atualmente no canvas? (Não remove ligações onde um dos lados não está visível)')){
            const idSet = new Set(nodes.map(n=>n.id));
            const beforeSaved = savedEdges.length;
            // Mantém edges onde pelo menos um dos lados NÃO está no canvas
            draftEdges = draftEdges.filter(e=> !(idSet.has(e.parent) && idSet.has(e.child)));
            savedEdges = savedEdges.filter(e=> !(idSet.has(e.parent) && idSet.has(e.child)));
            // Persistir novas edges guardadas
            try {
                const existingRaw = localStorage.getItem(getHierKey());
                let storedNodes = nodes.slice();
                if(existingRaw){
                    try { const parsed = JSON.parse(existingRaw)||{}; if(Array.isArray(parsed.nodes)) storedNodes = parsed.nodes; } catch(_){ }
                }
                localStorage.setItem(getHierKey(), JSON.stringify({ nodes: storedNodes, edges: savedEdges }));
            } catch(_){ }
            redrawLinks();
            renderList();
            const removed = beforeSaved - savedEdges.length;
            showToast && showToast(`Removidas ${removed} ligações entre colaboradores visíveis.`,`success`);
        }
    }); }

    // Carrega hierarquia ANTES da primeira renderList para que savedEdges seja populado
    loadHierarchy();
    syncHierarchyUI();
    redrawLinks();
    renderList();
    }

    // --- Fleet (Frota) Module ---
    function initializeFrotaModule(){
        const modal = document.getElementById('fleet-modal');
        if(!modal){ return; }

        // Global helpers that always read fresh data from the DOM
        window.__getFleetData = function(){
            let data = [];
            const jsonEl = document.querySelector('#fleet-data');
            if(jsonEl){
                try { data = JSON.parse(jsonEl.textContent || '[]'); } catch(e){ data = []; }
            }
            if(!Array.isArray(data) || !data.length){ data = (window.__FLEET__||[]); }
            return data;
        };

        window.__openFrotaModalFor = function(id){
            const data = window.__getFleetData();
            const v = (data||[]).find(x => String(x.id) === String(id));
            if(!v) return;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            const set = (sel, val)=>{ const el = modal.querySelector(sel); if(el) el.textContent = (val==null? '': String(val)); };
            set('[data-field="veiculo"]', v.veiculo || [v.marca, v.modelo].filter(Boolean).join(' '));
            set('[data-field="marca"]', v.marca);
            set('[data-field="modelo"]', v.modelo);
            set('[data-field="matricula"]', v.matricula);
            set('[data-field="ano"]', v.ano);
            set('[data-field="tipo_contrato"]', v.tipo_contrato);
            set('[data-field="num_contrato"]', v.num_contrato);
            set('[data-field="locadora"]', v.locadora);
            set('[data-field="seguradora"]', v.seguradora);
            set('[data-field="apolice"]', v.apolice);
            set('[data-field="carta_verde"]', v.carta_verde);
            set('[data-field="valido_de"]', v.valido_de);
            set('[data-field="valido_ate"]', v.valido_ate);
            set('[data-field="agencia"]', v.agencia);
            set('[data-field="ag_nome"]', v.ag_nome);
            set('[data-field="ag_morada"]', v.ag_morada);
            set('[data-field="ag_cp"]', v.ag_cp);
            set('[data-field="ag_tel"]', v.ag_tel);
            set('[data-field="ag_mail"]', v.ag_mail);
            set('[data-field="danos_materiais"]', v.danos_materiais ? 'Sim' : 'Não');
        };

        window.__bindFrotaCardClicks = function(scope){
            const root = scope || document;
            root.querySelectorAll('.fleet-detail-btn').forEach(btn=>{
                btn.addEventListener('click', function(){
                    const id = this.getAttribute('data-id') || this.getAttribute('data-index');
                    window.__openFrotaModalFor(id);
                });
            });
        };

        // Initial bind for existing cards
        window.__bindFrotaCardClicks(document);

        // Close handlers (idempotent)
        const close = ()=>{ modal.style.display='none'; document.body.style.overflow=''; };
        modal.querySelectorAll('[data-close]').forEach(el=> el.addEventListener('click', close));
        document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ close(); }});
    }

    // Add create-vehicle UI logic: open/close modal, preview image, submit; append new card and update JSON
    (function(){
        window.__wireFrotaCreate = function(container){
            if (!container) return;

            // Helpers
            const qs = (sel, root=container) => root.querySelector(sel);
            const qsa = (sel, root=container) => Array.from(root.querySelectorAll(sel));

            const createBtn = qs('#fleet-create-btn');
            const modal = qs('#fleet-create-modal');
            if (!createBtn || !modal) return;

            if (modal.dataset.wired === '1') return; // idempotent per modal instance
            modal.dataset.wired = '1';

            const showModal = () => { modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); document.body.classList.add('modal-open'); };
            const hideModal = () => { modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); document.body.classList.remove('modal-open'); };

            qsa('[data-close]', modal).forEach(el => el.addEventListener('click', hideModal));
            modal.addEventListener('click', (e) => {
                if (e.target.classList && e.target.classList.contains('fleet-modal-backdrop')) hideModal();
            });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideModal(); });

            createBtn.addEventListener('click', showModal);

            // Preview image logic
            const fileInput = qs('#fleet-image-file');
            const urlInput = qs('#fleet-image-url');
            const previewImg = qs('#fleet-create-preview');

            const updatePreview = (src) => {
                if (!src) { previewImg.style.display='none'; previewImg.src=''; return; }
                previewImg.src = src; previewImg.style.display='block';
            };

            if (fileInput) {
                fileInput.addEventListener('change', () => {
                    const f = fileInput.files && fileInput.files[0];
                    if (f) { const reader = new FileReader(); reader.onload = e => updatePreview(e.target.result); reader.readAsDataURL(f); }
                });
            }

            if (urlInput) {
                urlInput.addEventListener('input', () => updatePreview(urlInput.value.trim()));
            }

            // Submit -> push into JSON fleet array and append a new card
            const form = qs('#fleet-create-form');
            const grid = qs('.fleet-grid');

            const getFleetData = () => {
                const jsonEl = qs('#fleet-data', container) || document.getElementById('fleet-data');
                if (!jsonEl) return [];
                try { return JSON.parse(jsonEl.textContent || '[]'); } catch { return []; }
            };
            const setFleetData = (arr) => {
                const jsonEl = qs('#fleet-data', container) || document.getElementById('fleet-data');
                if (jsonEl) jsonEl.textContent = JSON.stringify(arr);
            };

            const appendCard = (car) => {
                if (!grid) return;
                const article = document.createElement('article');
                article.className = 'fleet-card';
                article.setAttribute('data-id', String(car.id));
                article.innerHTML = `
                <span class="fleet-status ${car.status === 'Atribuido' ? 'status-atribuido' : (car.status === 'Inspeção' ? 'status-inspecao' : 'status-livre')}">${car.status || 'Livre'}</span>
                    <img src="${car.image || '../../assets/logos/logo.png'}" alt="${car.marca} ${car.modelo}" loading="lazy" />
                    <div class="fleet-meta">
                        <h4>${car.marca} ${car.modelo}</h4>
                        <p class="muted">Matrícula: ${car.matricula}</p>
                    </div>
                    <button class="btn btn-primary fleet-detail-btn" data-id="${car.id}">Ver detalhes</button>
                `;
                grid.appendChild(article);
                // bind detail click
                article.querySelector('.fleet-detail-btn').addEventListener('click', function(){
                    if (typeof window.__openFrotaModalFor === 'function') window.__openFrotaModalFor(car.id);
                });
            };

            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const fd = new FormData(form);
                    const imageFromUrl = (urlInput && urlInput.value.trim()) || '';
                    const imageFromFile = (fileInput && fileInput.files && fileInput.files[0]) || null;

                    const proceed = (imageSrc) => {
                        const arr = getFleetData();
                        const maxId = Math.max(0, ...arr.map(x => Number(x && x.id) || 0));
                                    const newCar = {
                            id: maxId + 1,
                            veiculo: `${(fd.get('marca')||'').toString().trim()} ${(fd.get('modelo')||'').toString().trim()}`.trim(),
                            marca: (fd.get('marca')||'').toString().trim(),
                            modelo: (fd.get('modelo')||'').toString().trim(),
                            matricula: (fd.get('matricula')||'').toString().trim(),
                            ano: (fd.get('ano')||'').toString().trim(),
                            tipo_contrato: (fd.get('tipo_contrato')||'').toString(),
                            num_contrato: (fd.get('num_contrato')||'').toString(),
                            locadora: (fd.get('locadora')||'').toString(),
                            seguradora: (fd.get('seguradora')||'').toString(),
                            apolice: (fd.get('apolice')||'').toString(),
                            carta_verde: (fd.get('carta_verde')||'').toString(),
                            valido_de: (fd.get('valido_de')||'').toString(),
                            valido_ate: (fd.get('valido_ate')||'').toString(),
                            agencia: (fd.get('agencia')||'').toString(),
                            ag_nome: (fd.get('ag_nome')||'').toString(),
                            ag_morada: (fd.get('ag_morada')||'').toString(),
                            ag_cp: (fd.get('ag_cp')||'').toString(),
                            ag_tel: (fd.get('ag_tel')||'').toString(),
                            ag_mail: (fd.get('ag_mail')||'').toString(),
                            danos_materiais: (fd.get('danos_materiais')||'false').toString() === 'true',
                                        image: imageSrc || '',
                                        status: 'Livre'
                        };

                        arr.push(newCar);
                        setFleetData(arr);
                        appendCard(newCar);
                        hideModal();
                        form.reset();
                        updatePreview('');
                    };

                    if (imageFromUrl) return proceed(imageFromUrl);
                    if (imageFromFile) {
                        const reader = new FileReader();
                        reader.onload = ev => proceed(ev.target.result);
                        reader.readAsDataURL(imageFromFile);
                        return;
                    }
                    proceed('');
                });
            }
        };
    })();

    // Hook create-vehicle wiring into existing Frota initializer
    (function(){
      const origInit = window.initializeFrotaModule;
      window.initializeFrotaModule = function(){
        if (typeof origInit === 'function') origInit();
        const container = document.getElementById('main-content') || document;
        if (typeof window.__wireFrotaCreate === 'function') window.__wireFrotaCreate(container);
      };
    })();

        // Delegated fallback: open/close create modal even if specific wiring didn't attach
        (function(){
            if (window.__fleetCreateDelegated) return; window.__fleetCreateDelegated = true;
            function syncModalClass(){
                const anyVisible = document.querySelector('.fleet-modal[style*="display: flex"], .fleet-modal:not([style])') || document.querySelector('#modalPedido[style*="display: flex"]');
                if (anyVisible) document.body.classList.add('modal-open'); else document.body.classList.remove('modal-open');
            }
            document.addEventListener('click', function(ev){
                const openBtn = ev.target && ev.target.closest && ev.target.closest('#fleet-create-btn');
                if (openBtn){
                    const root = document.getElementById('main-content') || document;
                    const modal = root.querySelector('#fleet-create-modal') || document.querySelector('#fleet-create-modal');
                    if (modal){ modal.style.display='flex'; modal.setAttribute('aria-hidden','false'); document.body.classList.add('modal-open'); ev.preventDefault(); syncModalClass(); return; }
                }
                const closeEl = ev.target && (ev.target.matches('[data-close]') || ev.target.classList.contains('fleet-modal-backdrop'));
                if (closeEl){
                    const modal = (ev.target.closest && ev.target.closest('.fleet-modal')) || document.querySelector('#fleet-create-modal');
                    if (modal){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); document.body.classList.remove('modal-open'); ev.preventDefault(); syncModalClass(); }
                }
            }, true);
            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape'){
                    const modal = document.querySelector('#fleet-create-modal');
                    if (modal && modal.style.display !== 'none'){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); document.body.classList.remove('modal-open'); syncModalClass(); }
                }
            });
        })();

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
        document.body.classList.add('modal-open');
    }
};

window.fecharModalPedido = function() {
    const modal = document.getElementById('modalPedido');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        document.body.classList.remove('modal-open');
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

