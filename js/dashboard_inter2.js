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
    }

    // Função para anexar listeners aos card-links
    function attachCardLinkListeners() {
        const cardLinks = document.querySelectorAll(".card-link");
        cardLinks.forEach(link => {
            link.addEventListener("click", handleNavigation);
        });
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
                    // Carregar eventos do servidor
                    fetch('../../api/eventos/listar_eventos.php')
                        .then(response => response.json())
                        .then(data => {
                            successCallback(data);
                        })
                        .catch(error => {
                            console.error('Erro ao carregar eventos:', error);
                            failureCallback(error);
                        });
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

    // Inicializar toggle de consulta pedidos
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const teamSection = document.querySelector('.requests-section:first-of-type');
    const personalSection = document.getElementById('personal-section');

    // Ensure sections are properly initialized
    if (teamSection) teamSection.style.display = 'block';
    if (personalSection) personalSection.style.display = 'none';

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const section = this.getAttribute('data-section');

            // Update active button
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Show/hide sections
            if (section === 'team') {
                if (teamSection) teamSection.style.display = 'block';
                if (personalSection) personalSection.style.display = 'none';
            } else {
                if (teamSection) teamSection.style.display = 'none';
                if (personalSection) personalSection.style.display = 'block';
            }
        });
    });
};

// Funções globais para compatibilidade
window.navigateMonthInter2 = navigateMonthInter2;
window.goToTodayInter2 = goToTodayInter2;
window.openModalInter2 = openModalInter2;
window.closeModalInter2 = closeModalInter2;

// Funções específicas para o sistema de horários do inter2
let currentMonthInter2 = new Date().getMonth();
let currentYearInter2 = new Date().getFullYear();
let horariosDataInter2 = {};
let feriasDataInter2 = {};

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
    
    loadFeriasInter2();
    renderCalendarInter2();
    
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

    // Carregar dados existentes
    loadHorariosDataInter2();
};

// Carregar férias aprovadas do inter2
function loadFeriasInter2() {
    fetch('../../api/pedidos/listar_ferias_aprovadas_inter2.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                feriasDataInter2 = data.ferias;
                console.log('Férias carregadas para Inter2:', feriasDataInter2);
                renderCalendarInter2(); // Re-render para mostrar férias
            }
        })
        .catch(error => console.error('Erro ao carregar férias Inter2:', error));
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

        // Verificar se há horários marcados
        const horariosKey = `horarios_inter2_${dateKey}`;
        const savedData = localStorage.getItem(horariosKey);
        
        if (savedData) {
            const data = JSON.parse(savedData);
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
            } else {
                // Modo normal, clique abre modal
                dayElement.addEventListener('click', () => {
                    console.log('Dia clicado:', dateKey);
                    openModalInter2(dateKey);
                });
            }
            dayElement.style.cursor = 'pointer';
            // Alternativa: adicionar onclick diretamente
            if (!isBulkModeInter2) {
                dayElement.setAttribute('onclick', `window.openModalInter2('${dateKey}')`);
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
    
    renderCalendarInter2();
}

// Ir para hoje do inter2
function goToTodayInter2() {
    const today = new Date();
    currentMonthInter2 = today.getMonth();
    currentYearInter2 = today.getFullYear();
    renderCalendarInter2();
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

    // Carregar dados existentes
    const horariosKey = `horarios_inter2_${dateKey}`;
    const savedData = localStorage.getItem(horariosKey);
    
    if (savedData) {
        const data = JSON.parse(savedData);
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

    // Remover dados do localStorage
    const horariosKey = `horarios_inter2_${dateKey}`;
    localStorage.removeItem(horariosKey);

    // Limpar formulário
    const form = document.getElementById('horariosForm');
    if (form) form.reset();

    // Atualizar calendário
    renderCalendarInter2();

    // Fechar modal
    closeModalInter2();

    // Mostrar notificação
    showNotificationInter2('Dados do dia removidos com sucesso!', 'success');
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

    // Salvar no localStorage
    const horariosKey = `horarios_inter2_${dateKey}`;
    localStorage.setItem(horariosKey, JSON.stringify(data));
    
    console.log(`Horários salvos para Inter2 em ${dateKey}:`, data);
    
    // Fechar modal e atualizar calendário
    closeModalInter2();
    renderCalendarInter2();
    
    // Mostrar confirmação
    showNotificationInter2('Horários guardados com sucesso! Aguarde aprovação do Inter.', 'success');
}

// Carregar dados de horários do inter2
function loadHorariosDataInter2() {
    const allKeys = Object.keys(localStorage);
    const horariosKeys = allKeys.filter(key => key.startsWith('horarios_inter2_'));
    
    horariosDataInter2 = {};
    horariosKeys.forEach(key => {
        const dateKey = key.replace('horarios_inter2_', '');
        horariosDataInter2[dateKey] = JSON.parse(localStorage.getItem(key));
    });
    
    console.log('Dados de horários carregados para Inter2:', horariosDataInter2);
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
    
    // Aplicar a todos os dias do período
    let appliedCount = 0;
    let skippedCount = 0;
    const currentDate = new Date(start);
    
    while (currentDate <= end) {
        const dateKey = currentDate.toISOString().split('T')[0];
        
        // Verificar se o dia não tem férias
        if (!feriasDataInter2[dateKey]) {
            const horariosKey = `horarios_inter2_${dateKey}`;
            localStorage.setItem(horariosKey, JSON.stringify(bulkData));
            appliedCount++;
        } else {
            skippedCount++;
        }
        
        // Avançar para o próximo dia
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    console.log(`Marcação em lote aplicada a ${appliedCount} dias:`, bulkData);
    
    // Atualizar calendário
    renderCalendarInter2();
    
    // Fechar modal
    closeBulkModalInter2();
    
    // Mostrar confirmação
    let message = `Marcação aplicada a ${appliedCount} dias com sucesso!`;
    if (skippedCount > 0) {
        message += ` ${skippedCount} dias foram ignorados (férias/ausências).`;
    }
    message += ' Aguarde aprovação do Inter.';
    
    showNotificationInter2(message, 'success');
}

// Expor funções globalmente
window.removeSelectedDay = removeSelectedDay;
