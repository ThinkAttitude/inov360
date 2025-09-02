// Inicialização do Sistema de Horários
// Este arquivo garante que todos os componentes são inicializados corretamente

(function() {
    'use strict';

    // Configuração global do sistema
    window.HORARIOS_CONFIG = {
        version: '1.0.0',
        debugMode: true,
        autoSync: true,
        maxHoursPerDay: 12,
        maxOvertimePerDay: 4 * 60, // 4 horas em minutos
        roles: {
            OPERA: 'opera',
            INTER2: 'inter2', 
            INTER: 'inter',
            ADMIN: 'admin',
            ADMIN_RH: 'admin_rh',
            ESTRELA: 'estrela'
        }
    };

    // Função para detectar role atual
    function getCurrentUserRole() {
        // Em produção, viria da sessão/API
        const path = window.location.pathname;
        
        if (path.includes('/opera/')) return 'opera';
        if (path.includes('/inter2/')) return 'inter2';
        if (path.includes('/inter/')) return 'inter';
        if (path.includes('/admin/')) return 'admin';
        if (path.includes('/admin_rh/')) return 'admin_rh';
        if (path.includes('/estrela/')) return 'estrela';
        
        return null;
    }

    // Função para log de debug
    function debugLog(message, data = null) {
        if (window.HORARIOS_CONFIG.debugMode) {
            console.log(`[HORARIOS] ${message}`, data || '');
        }
    }

    // Inicialização baseada no contexto
    function initializeForContext() {
        const role = getCurrentUserRole();
        const isHorariosPage = window.location.pathname.includes('horarios');
        const isAprovacaoPage = window.location.pathname.includes('aprovacao_horarios');

        debugLog('Inicializando sistema', { role, isHorariosPage, isAprovacaoPage });

        // Inicializar integração férias/horários em todas as páginas relevantes
        if (role === 'opera' || role === 'inter2') {
            debugLog('Carregando integração férias/horários');
            // A integração é carregada automaticamente via script tag
        }

        // Verificar se é página de marcação de horários
        if (isHorariosPage && role === 'opera') {
            debugLog('Página de marcação de horários detectada');
            
            // Aguardar carregamento dos scripts
            setTimeout(() => {
                if (window.marcacaoHorarios) {
                    debugLog('Sistema de marcação inicializado');
                } else {
                    console.warn('[HORARIOS] Sistema de marcação não encontrado');
                }
            }, 1000);
        }

        // Verificar se é página de aprovação
        if (isAprovacaoPage && role === 'inter2') {
            debugLog('Página de aprovação de horários detectada');
            
            setTimeout(() => {
                if (window.aprovacaoHorarios) {
                    debugLog('Sistema de aprovação inicializado');
                } else {
                    console.warn('[HORARIOS] Sistema de aprovação não encontrado');
                }
            }, 1000);
        }
    }

    // Função para verificar dependências
    function checkDependencies() {
        const required = {
            'localStorage': typeof Storage !== 'undefined',
            'fetch': typeof fetch !== 'undefined',
            'JSON': typeof JSON !== 'undefined'
        };

        const missing = Object.entries(required)
            .filter(([name, available]) => !available)
            .map(([name]) => name);

        if (missing.length > 0) {
            console.error('[HORARIOS] Dependências em falta:', missing.join(', '));
            return false;
        }

        debugLog('Todas as dependências estão disponíveis');
        return true;
    }

    // Função para setup de event listeners globais
    function setupGlobalListeners() {
        // Listener para mudanças no localStorage (sincronização entre abas)
        window.addEventListener('storage', function(e) {
            if (e.key && e.key.startsWith('marcacoes_') || e.key.startsWith('pedidos_')) {
                debugLog('Dados sincronizados entre abas', e.key);
                
                // Notificar componentes relevantes
                if (window.marcacaoHorarios && typeof window.marcacaoHorarios.syncExternalData === 'function') {
                    window.marcacaoHorarios.syncExternalData();
                }
                
                if (window.aprovacaoHorarios && typeof window.aprovacaoHorarios.refreshData === 'function') {
                    window.aprovacaoHorarios.refreshData();
                }
            }
        });

        // Listener para erros JavaScript
        window.addEventListener('error', function(e) {
            if (e.message && e.message.includes('HORARIOS')) {
                console.error('[HORARIOS] Erro capturado:', e.message, e.filename, e.lineno);
            }
        });

        debugLog('Event listeners globais configurados');
    }

    // Função principal de inicialização
    function initialize() {
        debugLog('Iniciando sistema de horários v' + window.HORARIOS_CONFIG.version);

        // Verificar dependências
        if (!checkDependencies()) {
            console.error('[HORARIOS] Sistema não pode ser inicializado devido a dependências em falta');
            return;
        }

        // Setup de listeners globais
        setupGlobalListeners();

        // Inicialização específica do contexto
        initializeForContext();

        // Marcar como inicializado
        window.HORARIOS_INITIALIZED = true;
        debugLog('Sistema inicializado com sucesso');
    }

    // Auto-inicialização quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    // Funções de utilitário globais
    window.HorariosUtils = {
        formatTime: function(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return hours > 0 ? `${hours}h${mins > 0 ? mins + 'm' : ''}` : `${mins}m`;
        },
        
        formatDate: function(date) {
            if (typeof date === 'string') date = new Date(date);
            return date.toLocaleDateString('pt-PT');
        },
        
        getCurrentRole: getCurrentUserRole,
        
        debugLog: debugLog,
        
        showNotification: function(message, type = 'info') {
            // Implementar sistema de notificações simples
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    };

})();
