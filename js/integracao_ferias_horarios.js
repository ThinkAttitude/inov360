// Sistema de Integração Férias/Ausências com Marcação de Horários
// Este sistema sincroniza automaticamente pedidos aprovados com o calendário de marcações

class IntegracaoFeriasHorarios {
    constructor() {
        this.init();
    }

    init() {
        // Escutar mudanças em pedidos de férias/ausências
        this.watchFeriasAusenciasChanges();
        
        // Sincronizar dados existentes na inicialização
        this.syncExistingData();
    }

    // Observar mudanças no localStorage para pedidos de férias
    watchFeriasAusenciasChanges() {
        // Usar MutationObserver para detectar mudanças na UI de aprovação
        const observer = new MutationObserver(() => {
            this.syncExistingData();
        });

        // Observar mudanças na página de aprovação
        const targetNode = document.body;
        if (targetNode) {
            observer.observe(targetNode, {
                childList: true,
                subtree: true
            });
        }

        // Escutar eventos personalizados de aprovação
        document.addEventListener('pedidoAprovado', (event) => {
            this.handlePedidoAprovado(event.detail);
        });

        document.addEventListener('pedidoRejeitado', (event) => {
            this.handlePedidoRejeitado(event.detail);
        });
    }

    // Sincronizar dados existentes
    syncExistingData() {
        // Carregar pedidos processados (aprovados/rejeitados)
        const pedidosProcessados = this.loadPedidosProcessados();
        
        // Filtrar apenas os aprovados
        const pedidosAprovados = pedidosProcessados.filter(pedido => 
            pedido.estado === 'aprovado' && 
            (pedido.tipo === 'ferias' || this.isAusenciaType(pedido.tipo))
        );

        // Atualizar localStorage com dados sincronizados
        this.updateFeriasAusenciasData(pedidosAprovados);

        // Notificar sistema de marcação se estiver ativo
        if (window.marcacaoHorarios) {
            window.marcacaoHorarios.syncFeriasAusencias(pedidosAprovados);
        }
    }

    // Carregar pedidos processados de diferentes fontes
    loadPedidosProcessados() {
        const sources = [
            'pedidos_aprovados_admin',
            'pedidos_aprovados_inter2', 
            'pedidos_aprovados_inter',
            'pedidos_processados'
        ];

        let allPedidos = [];

        sources.forEach(source => {
            const data = JSON.parse(localStorage.getItem(source) || '[]');
            if (Array.isArray(data)) {
                allPedidos = allPedidos.concat(data);
            }
        });

        return allPedidos;
    }

    // Verificar se é tipo de ausência
    isAusenciaType(tipo) {
        const tiposAusencia = [
            'licenca_paternidade',
            'licenca_maternidade', 
            'baixa_medica',
            'baixa_seguro',
            'casamento',
            'consulta_medica',
            'luto',
            'falta_justificada'
        ];
        
        return tiposAusencia.includes(tipo);
    }

    // Lidar com pedido aprovado
    handlePedidoAprovado(pedido) {
        console.log('Pedido aprovado detectado:', pedido);
        
        // Adicionar à lista de pedidos aprovados
        let pedidosAprovados = JSON.parse(localStorage.getItem('pedidos_aprovados') || '[]');
        
        // Verificar se já existe
        const exists = pedidosAprovados.some(p => 
            p.id === pedido.id || 
            (p.userId === pedido.userId && p.dataInicio === pedido.dataInicio)
        );

        if (!exists) {
            pedidosAprovados.push({
                ...pedido,
                estado: 'aprovado',
                dataAprovacao: new Date().toISOString()
            });
            
            localStorage.setItem('pedidos_aprovados', JSON.stringify(pedidosAprovados));
            this.syncExistingData();
        }
    }

    // Lidar com pedido rejeitado
    handlePedidoRejeitado(pedido) {
        console.log('Pedido rejeitado detectado:', pedido);
        
        // Remover da lista de aprovados se existir
        let pedidosAprovados = JSON.parse(localStorage.getItem('pedidos_aprovados') || '[]');
        pedidosAprovados = pedidosAprovados.filter(p => 
            p.id !== pedido.id && 
            !(p.userId === pedido.userId && p.dataInicio === pedido.dataInicio)
        );
        
        localStorage.setItem('pedidos_aprovados', JSON.stringify(pedidosAprovados));
        this.syncExistingData();
    }

    // Atualizar dados de férias/ausências para o sistema de marcação
    updateFeriasAusenciasData(pedidosAprovados) {
        const feriasAusencias = {};
        
        pedidosAprovados.forEach(pedido => {
            if (pedido.dataInicio && pedido.dataFim) {
                const startDate = new Date(pedido.dataInicio);
                const endDate = new Date(pedido.dataFim);
                
                // Iterar por cada dia do período
                for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                    const dateStr = this.formatDate(d);
                    
                    feriasAusencias[dateStr] = {
                        tipo: pedido.tipo === 'ferias' ? 'vacation' : 'absence',
                        label: pedido.tipo === 'ferias' ? 'Férias' : this.getAusenciaLabel(pedido.tipo),
                        pedidoId: pedido.id,
                        userId: pedido.userId,
                        original: pedido
                    };
                }
            }
        });

        localStorage.setItem('ferias_ausencias_aprovadas', JSON.stringify(feriasAusencias));
    }

    // Obter label para tipo de ausência
    getAusenciaLabel(tipo) {
        const labels = {
            'licenca_paternidade': 'Lic. Paternidade',
            'licenca_maternidade': 'Lic. Maternidade',
            'baixa_medica': 'Baixa Médica',
            'baixa_seguro': 'Baixa Seguro',
            'casamento': 'Casamento',
            'consulta_medica': 'Consulta Médica',
            'luto': 'Luto',
            'falta_justificada': 'Falta Justificada'
        };
        
        return labels[tipo] || 'Ausência';
    }

    // Formatar data
    formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    // Método público para forçar sincronização
    forceSyncData() {
        this.syncExistingData();
    }

    // Obter dados sincronizados para um usuário específico
    getUserFeriasAusencias(userId = null) {
        const allData = JSON.parse(localStorage.getItem('ferias_ausencias_aprovadas') || '{}');
        
        if (!userId) {
            return allData;
        }

        const userData = {};
        Object.entries(allData).forEach(([date, data]) => {
            if (data.userId === userId) {
                userData[date] = data;
            }
        });

        return userData;
    }

    // Verificar se uma data está bloqueada para marcação
    isDateBlocked(dateStr, userId = null) {
        const feriasAusencias = this.getUserFeriasAusencias(userId);
        return !!feriasAusencias[dateStr];
    }

    // Obter resumo mensal de férias/ausências
    getMonthlySummary(month, year, userId = null) {
        const monthKey = `${year}-${(month + 1).toString().padStart(2, '0')}`;
        const feriasAusencias = this.getUserFeriasAusencias(userId);
        
        const summary = {
            totalDias: 0,
            diasFerias: 0,
            diasAusencia: 0,
            detalhes: []
        };

        Object.entries(feriasAusencias).forEach(([date, data]) => {
            if (date.startsWith(monthKey)) {
                summary.totalDias++;
                
                if (data.tipo === 'vacation') {
                    summary.diasFerias++;
                } else {
                    summary.diasAusencia++;
                }

                summary.detalhes.push({
                    data: date,
                    tipo: data.tipo,
                    label: data.label
                });
            }
        });

        return summary;
    }
}

// Funções auxiliares para emitir eventos personalizados
window.emitPedidoAprovado = function(pedido) {
    const event = new CustomEvent('pedidoAprovado', {
        detail: pedido
    });
    document.dispatchEvent(event);
};

window.emitPedidoRejeitado = function(pedido) {
    const event = new CustomEvent('pedidoRejeitado', {
        detail: pedido
    });
    document.dispatchEvent(event);
};

// Inicializar sistema de integração quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    window.integracaoFeriasHorarios = new IntegracaoFeriasHorarios();
});

// Exportar para uso em outros módulos
window.IntegracaoFeriasHorarios = IntegracaoFeriasHorarios;
