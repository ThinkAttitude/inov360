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
});
