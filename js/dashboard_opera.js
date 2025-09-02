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
                url = "../opera/horarios_new.php";
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

                // Inicializar calendário de horários se necessário
                if (content === "horarios") {
                    console.log('Inicializando sistema de horários...');
                    
                    // Aguardar um pouco para garantir que o DOM está pronto
                    setTimeout(function() {
                        window.initializeHorariosCalendar();
                    }, 100);
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

// Função global para inicializar calendário de horários
window.initializeHorariosCalendar = function() {
    console.log('Inicializando calendário de horários...');
    
    // Verificar se os elementos existem
    const currentMonthEl = document.getElementById('current-month');
    const calendarGridEl = document.getElementById('calendar-grid');
    
    if (!currentMonthEl || !calendarGridEl) {
        console.error('Elementos do calendário não encontrados');
        // Tentar novamente em 200ms
        setTimeout(window.initializeHorariosCalendar, 200);
        return;
    }
    
    console.log('Elementos encontrados, definindo funções globais...');
    
    // Variáveis globais
    window.currentMonth = new Date().getMonth();
    window.currentYear = new Date().getFullYear();
    window.marcacoes = JSON.parse(localStorage.getItem('marcacoes_horarios') || '{}');
    window.ferias = {}; // Férias aprovadas carregadas da API

    // Variáveis para seleção múltipla
    window.isBulkMode = false;
    window.selectedDays = new Set();

    // Event listeners para seleção múltipla
    const bulkSelectBtn = document.getElementById('bulkSelectBtn');
    const bulkModal = document.getElementById('bulkModal');
    const closeBulkBtn = document.querySelector('.close-bulk-modal');
    const cancelBulkBtn = document.getElementById('cancelBulkBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const applyBulkBtn = document.getElementById('applyBulkBtn');

    if (bulkSelectBtn) bulkSelectBtn.addEventListener('click', window.openBulkModal);
    if (closeBulkBtn) closeBulkBtn.addEventListener('click', window.closeBulkModal);
    if (cancelBulkBtn) cancelBulkBtn.addEventListener('click', window.closeBulkModal);
    if (clearSelectionBtn) clearSelectionBtn.addEventListener('click', window.clearSelection);
    if (applyBulkBtn) applyBulkBtn.addEventListener('click', window.applyBulkSelection);

    // Fechar modal ao clicar fora
    if (bulkModal) {
        bulkModal.addEventListener('click', (e) => {
            if (e.target === bulkModal) window.closeBulkModal();
        });
    }

    // Carregar férias aprovadas
    window.loadFerias = function() {
        fetch('../../api/pedidos/listar_ferias_aprovadas.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.ferias = data.ferias;
                    console.log('Férias carregadas:', window.ferias);
                    window.renderCalendar(); // Re-renderizar calendário com férias
                } else {
                    console.error('Erro ao carregar férias:', data.error);
                }
            })
            .catch(error => {
                console.error('Erro na requisição de férias:', error);
            });
    };

    // Carregar férias na inicialização
    window.loadFerias();

    // Nomes dos meses
    window.monthNames = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];

    // Função para renderizar calendário - GLOBAL
    window.renderCalendar = function() {
        console.log('Renderizando calendário...');
        
        const currentMonthEl = document.getElementById('current-month');
        const calendarGridEl = document.getElementById('calendar-grid');
        
        if (!currentMonthEl || !calendarGridEl) {
            console.error('Elementos não encontrados durante renderização');
            return;
        }
        
        // Atualizar cabeçalho do mês
        currentMonthEl.textContent = `${window.monthNames[window.currentMonth]} ${window.currentYear}`;
        
        const firstDay = new Date(window.currentYear, window.currentMonth, 1);
        const lastDay = new Date(window.currentYear, window.currentMonth + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startDayOfWeek = firstDay.getDay();
        
        let html = '';
        
        // Cabeçalho dos dias da semana
        const weekDays = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        weekDays.forEach(day => {
            html += `<div class="day-header">${day}</div>`;
        });
        
        // Dias vazios no início
        for (let i = 0; i < startDayOfWeek; i++) {
            html += `<div class="day-cell empty"></div>`;
        }
        
        // Dias do mês
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const dateKey = `${window.currentYear}-${(window.currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            const isToday = day === today.getDate() && window.currentMonth === today.getMonth() && window.currentYear === today.getFullYear();
            const hasMarcacao = window.marcacoes[dateKey];
            const hasFerias = window.ferias && window.ferias[dateKey];
            
            let cellClass = 'day-cell';
            let statusContent = '';
            
            if (isToday) {
                cellClass += ' today';
            } else if (hasFerias) {
                // Dia com férias aprovadas
                cellClass += ' ferias';
                const feriaData = hasFerias;
                
                // Mostrar tipo de férias/ausências
                let feriaText = '';
                switch(feriaData.tipo) {
                    case 'ferias':
                        feriaText = 'FÉRIAS';
                        break;
                    case 'licenca_paternidade':
                        feriaText = 'LIC. PATERNIDADE';
                        break;
                    case 'licenca_maternidade':
                        feriaText = 'LIC. MATERNIDADE';
                        break;
                    case 'baixa_medica':
                        feriaText = 'BAIXA MÉDICA';
                        break;
                    case 'baixa_seguro':
                        feriaText = 'BAIXA SEGURO';
                        break;
                    case 'casamento':
                        feriaText = 'CASAMENTO';
                        break;
                    case 'consulta_medica':
                        feriaText = 'CONSULTA MÉDICA';
                        break;
                    case 'assunto_pessoal':
                        feriaText = 'ASSUNTO PESSOAL';
                        break;
                    case 'ausencia_justificada':
                        feriaText = 'AUSÊNCIA JUSTIFICADA';
                        break;
                    case 'luto':
                        feriaText = 'LUTO';
                        break;
                    case 'formacao':
                        feriaText = 'FORMAÇÃO';
                        break;
                    case 'doenca_familiar':
                        feriaText = 'DOENÇA FAMILIAR';
                        break;
                    case 'assistencia_familia':
                        feriaText = 'ASSISTÊNCIA FAMÍLIA';
                        break;
                    default:
                        // Para tipos não mapeados, usar o valor original formatado
                        feriaText = feriaData.tipo.replace(/_/g, ' ').toUpperCase();
                }
                
                statusContent = `
                    <div class="day-details">
                        <div class="ferias-badge">${feriaText}</div>
                    </div>
                `;
            } else if (hasMarcacao) {
                cellClass += ' marked';
                
                // Criar badges com horas diretas
                const data = hasMarcacao;
                let badges = '';
                
                // Horas trabalhadas (verde)
                if (data.horasTrabalhadas && data.horasTrabalhadas !== '00:00') {
                    const [hours, minutes] = data.horasTrabalhadas.split(':');
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
                if (data.horasExtra && data.horasExtra !== '00:00') {
                    const [hours, minutes] = data.horasExtra.split(':');
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
                if (data.horasPrevencao && data.horasPrevencao !== '00:00') {
                    const [hours, minutes] = data.horasPrevencao.split(':');
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
                if (data.kmViatura && data.kmViatura > 0) {
                    badges += `<span class="hour-badge km">${data.kmViatura}km</span>`;
                }
                
                statusContent = `
                    <div class="day-details">
                        <div class="badges">${badges}</div>
                    </div>
                `;
            }
            
            // Determinar se o dia pode ser clicado (não deve ser clicável se tem férias)
            
            // Verificar se está selecionado para bulk
            if (window.selectedDays.has(dateKey)) {
                cellClass += ' bulk-selected';
            }
            
            // Adicionar classe para modo bulk
            if (window.isBulkMode && !hasFerias) {
                cellClass += ' bulk-selectable';
            }
            
            let clickAction = '';
            if (!hasFerias) {
                if (window.isBulkMode) {
                    // No modo bulk, clique seleciona/deseleciona o dia
                    clickAction = `onclick="window.toggleDaySelection('${dateKey}')"`;
                } else {
                    // Modo normal, clique abre modal
                    clickAction = `onclick="window.openDayModal(${day})"`;
                }
            }
            
            const clickCursor = hasFerias ? 'cursor: default;' : '';
            
            html += `
                <div class="${cellClass}" ${clickAction} style="${clickCursor}">
                    <div class="day-number">${day}</div>
                    ${statusContent}
                </div>
            `;
        }
        
        calendarGridEl.innerHTML = html;
        console.log('Calendário renderizado com sucesso');
    };

    // Função para navegar mês - GLOBAL
    window.navigateMonth = function(direction) {
        console.log('Navegando mês:', direction);
        window.currentMonth += direction;
        if (window.currentMonth > 11) {
            window.currentMonth = 0;
            window.currentYear++;
        } else if (window.currentMonth < 0) {
            window.currentMonth = 11;
            window.currentYear--;
        }
        window.renderCalendar();
    };

    // Função para abrir modal do dia - GLOBAL
    window.openDayModal = function(day) {
        console.log('Abrindo modal para dia:', day);
        const dateKey = `${window.currentYear}-${(window.currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        
        // Verificar se o dia tem férias aprovadas
        if (window.ferias && window.ferias[dateKey]) {
            alert('Não é possível marcar horários em dias com férias/ausências aprovadas.');
            return;
        }
        
        const existingData = window.marcacoes[dateKey] || {};
        
        const modalHtml = `
            <div class="modal" id="day-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Marcação de Horário - Dia ${day}</h3>
                        <button class="horarios-close-btn" onclick="window.closeDayModal()">×</button>
                    </div>
                    
                    <form id="day-form">
                        <div class="form-group">
                            <label>Horas Trabalhadas (HH:MM)</label>
                            <input type="text" id="horas-trabalhadas" placeholder="08:00" value="${existingData.horasTrabalhadas || ''}">
                        </div>
                        
                        <div class="form-group">
                            <label>Horas Extra (HH:MM)</label>
                            <input type="text" id="horas-extra" placeholder="00:00" value="${existingData.horasExtra || ''}">
                        </div>
                        
                        <div class="form-group">
                            <label>Horas de Prevenção (HH:MM)</label>
                            <input type="text" id="horas-prevencao" placeholder="00:00" value="${existingData.horasPrevencao || ''}">
                        </div>
                        
                        <div class="form-group">
                            <label>Quilómetros Viatura Própria</label>
                            <input type="number" id="km-viatura" placeholder="0" value="${existingData.kmViatura || ''}">
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" onclick="window.saveDayData(${day})">Guardar</button>
                            <button type="button" class="btn btn-secondary" onclick="window.closeDayModal()">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    };

    // Função para fechar modal - GLOBAL
    window.closeDayModal = function() {
        const modal = document.getElementById('day-modal');
        if (modal) {
            modal.remove();
        }
    };

    // Função para guardar dados do dia - GLOBAL
    window.saveDayData = function(day) {
        console.log('Guardando dados para dia:', day);
        const dateKey = `${window.currentYear}-${(window.currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        
        const data = {
            horasTrabalhadas: document.getElementById('horas-trabalhadas').value,
            horasExtra: document.getElementById('horas-extra').value,
            horasPrevencao: document.getElementById('horas-prevencao').value,
            kmViatura: document.getElementById('km-viatura').value,
            dataModificacao: new Date().toISOString()
        };
        
        // Guardar no localStorage
        window.marcacoes[dateKey] = data;
        localStorage.setItem('marcacoes_horarios', JSON.stringify(window.marcacoes));
        
        // Fechar modal e atualizar calendário
        window.closeDayModal();
        window.renderCalendar();
        
        // Mostrar confirmação
        alert(`Marcação guardada para o dia ${day}!\n\nResumo:\n• Horas trabalhadas: ${data.horasTrabalhadas || 'Não definido'}\n• Horas extra: ${data.horasExtra || 'Não definido'}\n• Horas prevenção: ${data.horasPrevencao || 'Não definido'}\n• KM viatura: ${data.kmViatura || '0'} km`);
    };

    // Função para submeter mês - GLOBAL
    window.submitMonth = function() {
        console.log('Submetendo mês...');
        const monthKey = `${window.currentYear}-${(window.currentMonth + 1).toString().padStart(2, '0')}`;
        const monthMarcacoes = {};
        
        // Filtrar marcações do mês atual
        Object.keys(window.marcacoes).forEach(dateKey => {
            if (dateKey.startsWith(monthKey)) {
                monthMarcacoes[dateKey] = window.marcacoes[dateKey];
            }
        });
        
        if (Object.keys(monthMarcacoes).length === 0) {
            alert('Não há marcações para submeter neste mês.');
            return;
        }
        
        // Calcular resumo
        let diasTrabalhados = 0;
        let horasTotais = 0;
        let horasExtra = 0;
        let horasPrevencao = 0;
        let kmTotal = 0;
        
        Object.values(monthMarcacoes).forEach(marcacao => {
            if (marcacao.horasTrabalhadas) {
                diasTrabalhados++;
                const [h, m] = marcacao.horasTrabalhadas.split(':');
                horasTotais += parseInt(h) + parseInt(m || 0) / 60;
            }
            if (marcacao.horasExtra) {
                const [h, m] = marcacao.horasExtra.split(':');
                horasExtra += parseInt(h) + parseInt(m || 0) / 60;
            }
            if (marcacao.horasPrevencao) {
                const [h, m] = marcacao.horasPrevencao.split(':');
                horasPrevencao += parseInt(h) + parseInt(m || 0) / 60;
            }
            if (marcacao.kmViatura) {
                kmTotal += parseInt(marcacao.kmViatura);
            }
        });
        
        const confirmMsg = `Deseja submeter as marcações de ${window.monthNames[window.currentMonth]} ${window.currentYear}?\n\nResumo:\n• Dias trabalhados: ${diasTrabalhados}\n• Horas totais: ${horasTotais.toFixed(1)}h\n• Horas extra: ${horasExtra.toFixed(1)}h\n• Horas prevenção: ${horasPrevencao.toFixed(1)}h\n• KM total: ${kmTotal} km\n\nApós a submissão, as marcações não poderão ser alteradas.`;
        
        if (confirm(confirmMsg)) {
            alert('Marcações submetidas com sucesso!\n\nStatus: Aguardando aprovação do supervisor\nSerá notificado quando aprovado');
        }
    };
    
    // Funções para seleção múltipla
    window.openBulkModal = function() {
        console.log('openBulkModal chamada');
        const bulkModal = document.getElementById('bulkModal');
        console.log('bulkModal encontrado:', bulkModal);
        if (bulkModal) {
            // Ativar modo de seleção múltipla
            window.isBulkMode = true;
            window.selectedDays.clear();
            
            // Adicionar classe ao body para indicar modo bulk
            document.body.classList.add('bulk-mode');
            
            // Adicionar indicador visual
            window.createBulkModeIndicator();
            
            // Re-renderizar calendário para mostrar modo de seleção
            window.renderCalendar();
            
            // Limpar formulário
            document.getElementById('bulkForm').reset();
            window.updateSelectedDaysDisplay();
            
            bulkModal.style.display = 'block';
        }
    };

    window.closeBulkModal = function() {
        const bulkModal = document.getElementById('bulkModal');
        if (bulkModal) {
            // Desativar modo de seleção múltipla
            window.isBulkMode = false;
            window.selectedDays.clear();
            
            // Remover classe do body
            document.body.classList.remove('bulk-mode');
            
            // Remover indicador visual
            window.removeBulkModeIndicator();
            
            // Re-renderizar calendário
            window.renderCalendar();
            
            bulkModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    window.createBulkModeIndicator = function() {
        // Remover indicador existente
        window.removeBulkModeIndicator();
        
        const indicator = document.createElement('div');
        indicator.id = 'bulkModeIndicator';
        indicator.className = 'bulk-mode-indicator';
        indicator.innerHTML = `
            <div>Modo Seleção Múltipla Ativo</div>
            <div style="font-size: 0.8rem; opacity: 0.8;">Clique nos dias para selecionar</div>
        `;
        document.body.appendChild(indicator);
    };

    window.removeBulkModeIndicator = function() {
        const indicator = document.getElementById('bulkModeIndicator');
        if (indicator) {
            indicator.remove();
        }
    };

    window.toggleDaySelection = function(dateKey) {
        if (window.selectedDays.has(dateKey)) {
            window.selectedDays.delete(dateKey);
        } else {
            window.selectedDays.add(dateKey);
        }
        
        // Re-renderizar apenas os dias afetados
        window.renderCalendar();
        window.updateSelectedDaysDisplay();
    };

    window.updateSelectedDaysDisplay = function() {
        const countElement = document.getElementById('selectedDaysCount');
        const listElement = document.getElementById('selectedDaysList');
        
        if (countElement) {
            countElement.textContent = window.selectedDays.size;
        }
        
        if (listElement) {
            listElement.innerHTML = '';
            
            // Converter datas para array e ordenar
            const sortedDays = Array.from(window.selectedDays).sort();
            
            sortedDays.forEach(dateKey => {
                const [year, month, day] = dateKey.split('-');
                const dayTag = document.createElement('div');
                dayTag.className = 'selected-day-tag';
                dayTag.innerHTML = `
                    ${day}/${month}
                    <span class="remove-day" onclick="window.removeSelectedDay('${dateKey}')">&times;</span>
                `;
                listElement.appendChild(dayTag);
            });
        }
    };

    window.removeSelectedDay = function(dateKey) {
        window.selectedDays.delete(dateKey);
        window.renderCalendar();
        window.updateSelectedDaysDisplay();
    };

    window.clearSelection = function() {
        window.selectedDays.clear();
        window.renderCalendar();
        window.updateSelectedDaysDisplay();
    };

    window.applyBulkSelection = function() {
        if (window.selectedDays.size === 0) {
            alert('Por favor, selecione pelo menos um dia.');
            return;
        }
        
        // Obter valores do formulário
        const bulkData = {
            horasTrabalhadas: document.getElementById('bulkHorasNormais').value,
            horasExtra: document.getElementById('bulkHorasExtra').value,
            horasPrevencao: document.getElementById('bulkHorasPrevencao').value,
            kmViatura: document.getElementById('bulkKmViatura').value,
            observacoes: document.getElementById('bulkObservacoes').value
        };
        
        // Validar se pelo menos um campo está preenchido
        const hasData = bulkData.horasTrabalhadas || bulkData.horasExtra || bulkData.horasPrevencao || bulkData.kmViatura;
        
        if (!hasData) {
            alert('Por favor, preencha pelo menos um campo de horas ou quilómetros.');
            return;
        }
        
        // Aplicar aos dias selecionados
        let appliedCount = 0;
        window.selectedDays.forEach(dateKey => {
            // Verificar se o dia não tem férias
            if (!window.ferias[dateKey]) {
                window.marcacoes[dateKey] = bulkData;
                appliedCount++;
            }
        });
        
        // Salvar no localStorage
        localStorage.setItem('marcacoes_horarios', JSON.stringify(window.marcacoes));
        
        console.log(`Marcação em lote aplicada a ${appliedCount} dias:`, bulkData);
        
        // Fechar modal e atualizar calendário
        window.closeBulkModal();
        
        // Mostrar confirmação
        window.showNotification(`Marcação aplicada a ${appliedCount} dias com sucesso!`, 'success');
    };

    window.showNotification = function(message, type = 'info') {
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
    };
    
    // Renderizar calendário inicial
    console.log('Renderizando calendário inicial...');
    window.renderCalendar();
};
