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
                <h2>Bem-vindo, RH360!</h2>
                <p>Gerencie pedidos de férias, supervise Intermédios 2 e consulte informações importantes do RH.</p>
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
                    <p>Visualize os seus horários de trabalho e planeie o seu dia com facilidade.</p>
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
                    <p>Solicite férias, ausências e acompanhe o estado dos seus pedidos.</p>
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
                            <polyline points="20,6 9,17 4,12"></polyline>
                        </svg>
                    </div>
                    <h3>Aprovação de Pedidos</h3>
                    <p>Aprove ou rejeite pedidos de férias e ausências dos Intermédios 2 sob sua supervisão.</p>
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
                    <h3>Consulta de Pedidos</h3>
                    <p>Consulte o histórico de pedidos processados da sua equipa.</p>
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
                    <h3>Lista de Intermédios 2</h3>
                    <p>Visualize e gerencie informações dos Intermédios 2 da sua equipa.</p>
                    <a href="#" class="card-link" data-content="lista_intermedios2">
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
                url = "../inter/horarios.php";
                break;
            case "ferias":
                url = "../inter/ferias_ausencias.php";
                break;
            case "aprovacao_ferias_ausencias":
                url = "../inter/aprovacao_ferias_ausencias.php";
                break;
            case "consulta_pedidos":
                url = "../inter/consulta_pedidos.php";
                break;
            case "lista_intermedios2":
                url = "../inter/lista_intermedios2.php";
                break;
            case "ficha_colaborador":
                url = "../inter/ficha_colaborador.php";
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
            case "ferias":
                initializeFeriasModule();
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
            case "ficha_colaborador":
                initializeFichaColaboradorModule();
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
                } else {
                    fileContent.textContent = 'Clique para selecionar ficheiro';
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
                    events: '../../api/eventos/listar_eventos.php',
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
    // Página agora só tem pedidos da equipa; não há toggles a inicializar.
    const teamSection = document.querySelector('.requests-section');
    if (teamSection) teamSection.style.display = 'block';
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

                fetch('../inter/editar_ficha_colaborador.php')
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
            const form = document.querySelector('form[action*="i_ficha_colaborador"]');
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
                url = "../inter/ficha_colaborador.php";
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
                console.error('Erro:', error);
                mainContent.innerHTML = '<p>Erro ao carregar conteúdo.</p>';
            });
    }

    // Adicionar event listeners iniciais
    links.forEach(link => {
        link.addEventListener("click", handleNavigation);
    });

    // Mostrar página inicial por padrão
    setTimeout(showWelcome, 100);
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
    fetch(`../inter/visualizar_lista_intermedios2.php?user_id=${userId}`)
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
};
