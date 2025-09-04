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

    // Event listeners para seleção múltipla
    console.log('Configurando event listeners...');
    const bulkSelectBtn = document.getElementById('bulkSelectBtn');
    console.log('bulkSelectBtn encontrado:', bulkSelectBtn);
    const bulkModal = document.getElementById('bulkModal');
    const closeBulkBtn = document.querySelector('.close-bulk-modal');
    const cancelBulkBtn = document.getElementById('cancelBulkBtn');
    const clearSelectionBtn = document.getElementById('clearRangeBtn');
    const applyBulkBtn = document.getElementById('applyBulkBtn');

    if (bulkSelectBtn) bulkSelectBtn.addEventListener('click', () => window.openBulkModal && window.openBulkModal());
    if (closeBulkBtn) closeBulkBtn.addEventListener('click', () => window.closeBulkModal && window.closeBulkModal());
    if (cancelBulkBtn) cancelBulkBtn.addEventListener('click', () => window.closeBulkModal && window.closeBulkModal());
    if (clearSelectionBtn) clearSelectionBtn.addEventListener('click', clearRange);
    if (applyBulkBtn) applyBulkBtn.addEventListener('click', () => window.applyBulkSelection && window.applyBulkSelection());

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
        // Atualizar badge de estado de submissão para o mês atual
        try {
            const badge = document.getElementById('submission-status-badge');
            const reasonEl = document.getElementById('submission-rejection-reason');
            if (badge) {
                const monthKey = `${window.currentYear}-${(window.currentMonth + 1).toString().padStart(2, '0')}`;
                const userId = localStorage.getItem('current_user_id') || 'user_local';
                const idKey = `${userId}-${monthKey}`;
                const pending = JSON.parse(localStorage.getItem('marcacoes_pending_approval') || '[]');
                const processed = JSON.parse(localStorage.getItem('marcacoes_processed') || '[]');
                const hasAnyMark = Object.keys(window.marcacoes).some(k => k.startsWith(monthKey));
                let state = 'por_enviar';
                if (!hasAnyMark) state = 'por_enviar';
                if (pending.find(r => `${r.userId}-${r.month}` === idKey)) state = 'em_aprovacao';
                const proc = processed.find(r => `${r.userId}-${r.month}` === idKey);
                if (proc) state = proc.status === 'approved' ? 'aprovado' : 'rejeitado';
                // Atualizar texto e classes
                badge.classList.remove('badge-grey','badge-yellow','badge-green','badge-red');
                if (state === 'por_enviar') { badge.textContent = 'Por enviar'; badge.classList.add('badge-grey'); }
                if (state === 'em_aprovacao') { badge.textContent = 'Em aprovação'; badge.classList.add('badge-yellow'); }
                if (state === 'aprovado') { badge.textContent = 'Aprovado'; badge.classList.add('badge-green'); }
                if (state === 'rejeitado') { badge.textContent = 'Rejeitado'; badge.classList.add('badge-red'); }
                // Motivo da rejeição (abaixo do badge, apenas quando rejeitado)
                if (reasonEl) {
                    if (state === 'rejeitado' && proc && proc.rejectionReason) {
                        reasonEl.textContent = `Motivo: ${proc.rejectionReason}`;
                        reasonEl.style.display = '';
                    } else {
                        reasonEl.textContent = '';
                        reasonEl.style.display = 'none';
                    }
                }
            }
        } catch(e) { console.warn('Badge update failed', e); }
        
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
            
            let clickAction = '';
            if (!hasFerias) {
                // Modo normal, clique abre modal
                clickAction = `onclick="window.openDayModal(${day})"`;
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
                            <button type="button" class="btn btn-secondary" onclick="window.closeDayModal()">Cancelar</button>
                            <button type="button" class="btn btn-danger" onclick="window.clearDayData(${day})" style="background: #dc2626;">Limpar Dia</button>
                            <button type="button" class="btn btn-primary" onclick="window.saveDayData(${day})">Guardar</button>
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

    // Função para limpar dados do dia - GLOBAL
    window.clearDayData = function(day) {
        console.log('Limpando dados para dia:', day);
        
        // Confirmar ação
        if (!confirm('Tem certeza que deseja limpar todas as horas deste dia?')) {
            return;
        }

        const dateKey = `${window.currentYear}-${(window.currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        
        // Remover dados do localStorage
        delete window.marcacoes[dateKey];
        localStorage.setItem('marcacoes_horarios', JSON.stringify(window.marcacoes));
        
        // Fechar modal e atualizar calendário
        window.closeDayModal();
        window.renderCalendar();
        
        // Mostrar confirmação
        alert(`Dados do dia ${day} removidos com sucesso!`);
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
        
        if (!confirm(confirmMsg)) return;

        // Construir registo para aprovação local (Inter2)
        const toMinutes = (hhmm) => {
            if (!hhmm || typeof hhmm !== 'string') return 0;
            const [h, m] = hhmm.split(':');
            return (parseInt(h || '0', 10) * 60) + (parseInt(m || '0', 10));
        };

        const userId = localStorage.getItem('current_user_id') || 'user_local';
        const userName = localStorage.getItem('current_user_name') || 'Operador';

        // Calcular minutos de horas extra (para Inter2 formatar com formatMinutes)
        let horasExtraMin = 0;
        Object.values(monthMarcacoes).forEach(m => { horasExtraMin += toMinutes(m.horasExtra || '00:00'); });

        const approvalRecord = {
            userId,
            userName,
            month: monthKey,
            status: 'pending',
            summary: {
                diasTrabalhados,
                horasTotais: Number(horasTotais.toFixed(1)),
                horasExtra: horasExtraMin, // minutos totais
                kmTotal: kmTotal
            },
            dataExportacao: new Date().toISOString(),
            marcacoes: monthMarcacoes
        };

        // Guardar no localStorage em marcacoes_pending_approval
        const keyPending = 'marcacoes_pending_approval';
        const keyProcessed = 'marcacoes_processed';
        let pendingList = [];
        let processedList = [];
        try { pendingList = JSON.parse(localStorage.getItem(keyPending) || '[]'); } catch { pendingList = []; }
        try { processedList = JSON.parse(localStorage.getItem(keyProcessed) || '[]'); } catch { processedList = []; }

        // Remover duplicados do mesmo user/mês em pendentes e processados
        const idKey = (rec) => `${rec.userId}-${rec.month}`;
        pendingList = pendingList.filter(rec => idKey(rec) !== idKey(approvalRecord));
        processedList = processedList.filter(rec => idKey(rec) !== idKey(approvalRecord));

        pendingList.push(approvalRecord);
        localStorage.setItem(keyPending, JSON.stringify(pendingList));
        localStorage.setItem(keyProcessed, JSON.stringify(processedList));

        // Feedback ao utilizador
        window.showNotification('Marcações submetidas para aprovação do supervisor.', 'success');
        alert('Marcações submetidas com sucesso!\n\nStatus: Aguardando aprovação do supervisor');

    // Atualizar badge de estado
    try { if (typeof window.renderCalendar === 'function') window.renderCalendar(); } catch(e){}
    };
    
    // Funções para seleção múltipla
    window.openBulkModal = function() {
        console.log('openBulkModal chamada');
        const bulkModal = document.getElementById('bulkModal');
        console.log('bulkModal encontrado:', bulkModal);
        if (bulkModal) {
            console.log('Configurando modal...');
            // Limpar formulário
            const bulkForm = document.getElementById('bulkForm');
            if (bulkForm && typeof bulkForm.reset === 'function') {
                bulkForm.reset();
            }
            
            // Configurar date range para o mês atual
            const firstDay = new Date(window.currentYear, window.currentMonth, 1);
            const lastDay = new Date(window.currentYear, window.currentMonth + 1, 0);
            
            const startInput = document.getElementById('bulkStartDate');
            const endInput = document.getElementById('bulkEndDate');
            if (startInput) startInput.value = firstDay.toISOString().split('T')[0];
            if (endInput) endInput.value = lastDay.toISOString().split('T')[0];
            
            // Configurar listeners e display
            if (typeof setupDateRangeListeners === 'function') setupDateRangeListeners();
            if (typeof updateRangeDisplay === 'function') updateRangeDisplay();
            
            bulkModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeBulkModal = function() {
        const bulkModal = document.getElementById('bulkModal');
        if (bulkModal) {
            bulkModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    window.applyBulkSelection = function() {
        const startDate = document.getElementById('bulkStartDate').value;
        const endDate = document.getElementById('bulkEndDate').value;
        
        if (!startDate || !endDate) {
            alert('Por favor, selecione um período válido.');
            return;
        }
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (start > end) {
            alert('Data de início deve ser anterior à data de fim.');
            return;
        }
        
        // Obter valores do formulário
        const horasTrabalhadas = document.getElementById('bulkHorasNormais').value;
        const horasExtra = document.getElementById('bulkHorasExtra').value;
        const horasPrevencao = document.getElementById('bulkHorasPrevencao').value;
        const kmViatura = document.getElementById('bulkKmViatura').value;
        
        if (!horasTrabalhadas && !horasExtra && !horasPrevencao && !kmViatura) {
            alert('Por favor, preencha pelo menos um campo.');
            return;
        }
        
        // Aplicar aos dias do período
        const current = new Date(start);
        let diasAplicados = 0;
        
        while (current <= end) {
            const dateKey = `${current.getFullYear()}-${(current.getMonth() + 1).toString().padStart(2, '0')}-${current.getDate().toString().padStart(2, '0')}`;
            
            // Verificar se o dia tem férias
            if (!window.ferias || !window.ferias[dateKey]) {
                // Criar/atualizar dados do dia
                if (!window.marcacoes[dateKey]) {
                    window.marcacoes[dateKey] = {};
                }
                
                if (horasTrabalhadas) window.marcacoes[dateKey].horasTrabalhadas = horasTrabalhadas;
                if (horasExtra) window.marcacoes[dateKey].horasExtra = horasExtra;
                if (horasPrevencao) window.marcacoes[dateKey].horasPrevencao = horasPrevencao;
                if (kmViatura) window.marcacoes[dateKey].kmViatura = kmViatura;
                
                diasAplicados++;
            }
            
            current.setDate(current.getDate() + 1);
        }
        
        // Salvar no localStorage
        localStorage.setItem('marcacoes_horarios', JSON.stringify(window.marcacoes));
        
        alert(`Marcação aplicada a ${diasAplicados} dias com sucesso!`);
        
        // Fechar modal e re-renderizar calendário
        window.closeBulkModal();
        window.renderCalendar();
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
function clearRange() {
    document.getElementById('bulkStartDate').value = '';
    document.getElementById('bulkEndDate').value = '';
    updateRangeDisplay();
}
