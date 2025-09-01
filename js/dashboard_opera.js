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
                <h2>Bem-vindo!</h2>
                <p>Gerencie os seus dados e consulte informações importantes do RH.</p>
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
                setTimeout(showWelcome, 300); // Simular loading
                return;
            case "horarios":
                url = "../opera/horarios.php";
                break;
            case "ferias":
                url = "../opera/ferias_ausencias.php";
                break;
            case "ficha_colaborador":
                url = "../opera/ficha_colaborador.php";
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

                // Inicializar funcionalidades dinâmicas após carregar conteúdo
                window.initializeDynamicContent();

                if (content === "horarios") {
                    // Carregar CSS do FullCalendar
                    // Verifica se o CSS já foi carregado
                    if (!document.querySelector('link[href*="fullcalendar"]')) {
                        const scriptCSS = document.createElement("link");
                        scriptCSS.rel = "stylesheet";
                        scriptCSS.href = "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css";
                        document.head.appendChild(scriptCSS);
                    }

                    // Verifica se o JS já foi carregado
                    if (typeof FullCalendar === "undefined") {
                        const script = document.createElement("script");
                        script.src = "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js";
                        script.onload = () => {
                            iniciarCalendario(); // Só depois do carregamento
                        };
                        document.body.appendChild(script);
                    } else {
                        iniciarCalendario(); // Se já estiver carregado, inicia logo
                    }

                    // Função separada para inicializar
                    function iniciarCalendario() {
                        const calendarEl = document.getElementById('calendar');
                        if (calendarEl) {
                            const calendar = new FullCalendar.Calendar(calendarEl, {
                                initialView: 'dayGridMonth',
                                locale: 'pt',
                                events: '../../api/eventos/listar_eventos.php',
                                eventClick: function (info) {
                                    alert('Evento: ' + info.event.title);
                                }
                            });
                            calendar.render();
                        }
                    }
                }

                // Handler para edição de ficha
                const editarFichaLink = document.getElementById("editar_ficha_colaborador");
                if (editarFichaLink) {
                    editarFichaLink.addEventListener("click", function (e) {
                        e.preventDefault();
                        showLoading();

                        fetch("../opera/editar_ficha_colaborador.php")
                            .then(response => {
                                if (!response.ok) throw new Error("Erro ao carregar edição.");
                                return response.text();
                            })
                            .then(html => {
                                mainContent.innerHTML = html;

                                // Handler do formulário de edição
                                const form = mainContent.querySelector("form");
                                if (form) {
                                    form.addEventListener("submit", function (e) {
                                        e.preventDefault();

                                        const formData = new FormData(form);
                                        const submitBtn = form.querySelector('button[type="submit"]');

                                        // Feedback visual imediato - mudança de cor
                                        submitBtn.style.background = '#10b981';
                                        submitBtn.innerHTML = `
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20,6 9,17 4,12"></polyline>
                                            </svg>
                                            Submetido!
                                        `;

                                        fetch("../../api/pedidos/o_ficha_colaborador.php", {
                                            method: "POST",
                                            body: formData
                                        })
                                            .then(response => response.json())
                                            .then(result => {
                                                if (result.success) {
                                                    showToast('success', 'Pedido Submetido', 'Os dados foram submetidos para aprovação.');
                                                    setTimeout(() => {
                                                        // Voltar à ficha
                                                        fetch("../opera/ficha_colaborador.php")
                                                            .then(res => res.text())
                                                            .then(html => {
                                                                mainContent.innerHTML = html;
                                                                // Reaplicar handler
                                                                attachFichaEditHandler();
                                                            });
                                                    }, 1500);
                                                } else {
                                                    showToast('error', 'Erro', result.error || "Erro ao submeter pedido.");
                                                    // Voltar ao estado original em caso de erro
                                                    submitBtn.style.background = '';
                                                    submitBtn.innerHTML = `
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                        Submeter
                                                    `;
                                                }
                                            })
                                            .catch(error => {
                                                console.error("Erro:", error);
                                                showToast('error', 'Erro de Conexão', 'Tente novamente mais tarde.');
                                                // Voltar ao estado original em caso de erro
                                                submitBtn.style.background = '';
                                                submitBtn.innerHTML = `
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                    Submeter
                                                `;
                                            });
                                    });
                                }
                            })
                            .catch(error => {
                                mainContent.innerHTML = "<p>Erro ao carregar edição.</p>";
                                console.error(error);
                            });
                    });
                }
            })
            .catch(error => {
                mainContent.innerHTML = `
                    <div class="main-header">
                        <h2>Erro ao Carregar</h2>
                        <p>Não foi possível carregar o conteúdo. Tente novamente.</p>
                    </div>
                `;
                console.error(error);
            });
    }

    // Função para anexar handler à ficha
    function attachFichaEditHandler() {
        const editarFichaLink = document.getElementById("editar_ficha_colaborador");
        if (editarFichaLink) {
            editarFichaLink.addEventListener("click", handleNavigation);
        }
    }

    // Anexar listeners iniciais
    attachCardLinkListeners();
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
