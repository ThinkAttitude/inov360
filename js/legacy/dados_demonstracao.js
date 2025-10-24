// Sistema de Demonstração - Dados de Teste
// Este arquivo simula dados para demonstrar o funcionamento do sistema

class DadosDemonstracao {
    constructor() {
        this.init();
    }

    init() {
        this.createSampleUsers();
        this.createSampleFeriasRequests();
        this.createSampleMarcacoes();
        this.setupDemoMode();
    }

    createSampleUsers() {
        const users = [
            {
                id: 'user_opera_001',
                nome: 'João Silva',
                email: 'joao.silva@empresa.com',
                role: 'opera',
                supervisor: 'user_inter2_001'
            },
            {
                id: 'user_opera_002', 
                nome: 'Maria Santos',
                email: 'maria.santos@empresa.com',
                role: 'opera',
                supervisor: 'user_inter2_001'
            },
            {
                id: 'user_opera_003',
                nome: 'Carlos Oliveira',
                email: 'carlos.oliveira@empresa.com', 
                role: 'opera',
                supervisor: 'user_inter2_002'
            },
            {
                id: 'user_inter2_001',
                nome: 'Ana Costa',
                email: 'ana.costa@empresa.com',
                role: 'inter2',
                supervisor: 'user_inter_001'
            },
            {
                id: 'user_inter2_002',
                nome: 'Pedro Martins',
                email: 'pedro.martins@empresa.com',
                role: 'inter2', 
                supervisor: 'user_inter_001'
            }
        ];

        localStorage.setItem('demo_users', JSON.stringify(users));
    }

    createSampleFeriasRequests() {
        const today = new Date();
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();

        const requests = [
            {
                id: 'req_001',
                userId: 'user_opera_001',
                tipo: 'ferias',
                dataInicio: `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-15`,
                dataFim: `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-19`,
                estado: 'aprovado',
                motivo: 'Férias de verão',
                dataSubmissao: new Date(currentYear, currentMonth, 1).toISOString(),
                dataAprovacao: new Date(currentYear, currentMonth, 3).toISOString()
            },
            {
                id: 'req_002',
                userId: 'user_opera_002',
                tipo: 'baixa_medica',
                dataInicio: `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-08`,
                dataFim: `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-10`,
                estado: 'aprovado',
                motivo: 'Consulta médica',
                dataSubmissao: new Date(currentYear, currentMonth, 5).toISOString(),
                dataAprovacao: new Date(currentYear, currentMonth, 6).toISOString()
            },
            {
                id: 'req_003',
                userId: 'user_opera_003',
                tipo: 'ferias',
                dataInicio: `${currentYear}-${(currentMonth + 2).toString().padStart(2, '0')}-01`,
                dataFim: `${currentYear}-${(currentMonth + 2).toString().padStart(2, '0')}-05`,
                estado: 'pendente',
                motivo: 'Férias familiares',
                dataSubmissao: new Date().toISOString()
            }
        ];

        localStorage.setItem('pedidos_aprovados', JSON.stringify(requests.filter(r => r.estado === 'aprovado')));
        localStorage.setItem('pedidos_pendentes', JSON.stringify(requests.filter(r => r.estado === 'pendente')));
    }

    createSampleMarcacoes() {
        const today = new Date();
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();

        // Marcações para o mês atual - João Silva
        const marcacoesJoao = {};
        for (let day = 1; day <= 14; day++) { // Até o dia 14 (antes das férias no dia 15)
            const dateStr = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            const dayOfWeek = new Date(currentYear, currentMonth, day).getDay();
            
            // Não marcar fins de semana
            if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                marcacoesJoao[dateStr] = {
                    tipo: 'trabalho',
                    horaInicio: '09:00',
                    horaFim: '18:00',
                    horasExtra: day % 3 === 0 ? 60 : 0, // Horas extra de vez em quando
                    horasPrevencao: day % 5 === 0 ? 30 : 0, // Prevenção ocasional
                    kmViatura: day % 4 === 0 ? 25 : 0, // KM ocasionais
                    observacoes: day % 7 === 0 ? 'Reunião de equipa' : '',
                    dataRegisto: new Date(currentYear, currentMonth, day - 1).toISOString()
                };
            }
        }

        // Depois das férias (dia 20 em diante)
        for (let day = 20; day <= 30; day++) {
            const dateStr = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            const dayOfWeek = new Date(currentYear, currentMonth, day).getDay();
            
            if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                marcacoesJoao[dateStr] = {
                    tipo: 'trabalho',
                    horaInicio: '09:00',
                    horaFim: '17:30',
                    horasExtra: 0,
                    horasPrevencao: 0,
                    kmViatura: 0,
                    observacoes: '',
                    dataRegisto: new Date(currentYear, currentMonth, day - 1).toISOString()
                };
            }
        }

        // Marcações para Maria Santos
        const marcacoesMaria = {};
        for (let day = 1; day <= 7; day++) { // Antes da baixa médica
            const dateStr = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            const dayOfWeek = new Date(currentYear, currentMonth, day).getDay();
            
            if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                marcacoesMaria[dateStr] = {
                    tipo: 'trabalho',
                    horaInicio: '08:30',
                    horaFim: '17:00',
                    horasExtra: 0,
                    horasPrevencao: day % 3 === 0 ? 45 : 0,
                    kmViatura: 15,
                    observacoes: '',
                    dataRegisto: new Date(currentYear, currentMonth, day - 1).toISOString()
                };
            }
        }

        // Depois da baixa (dia 11 em diante)
        for (let day = 11; day <= 30; day++) {
            const dateStr = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            const dayOfWeek = new Date(currentYear, currentMonth, day).getDay();
            
            if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                marcacoesMaria[dateStr] = {
                    tipo: 'trabalho',
                    horaInicio: '08:30',
                    horaFim: '17:00',
                    horasExtra: day % 4 === 0 ? 30 : 0,
                    horasPrevencao: 0,
                    kmViatura: 15,
                    observacoes: '',
                    dataRegisto: new Date(currentYear, currentMonth, day - 1).toISOString()
                };
            }
        }

        // Salvar marcações por usuário
        localStorage.setItem('marcacoes_user_opera_001', JSON.stringify(marcacoesJoao));
        localStorage.setItem('marcacoes_user_opera_002', JSON.stringify(marcacoesMaria));

        // Criar dados para aprovação (simulando submissão)
        const monthKey = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}`;
        
        const pendingApprovals = [
            {
                userId: 'user_opera_001',
                month: monthKey,
                marcacoes: marcacoesJoao,
                summary: this.calculateSummary(marcacoesJoao),
                dataExportacao: new Date().toISOString()
            },
            {
                userId: 'user_opera_002',
                month: monthKey,
                marcacoes: marcacoesMaria,
                summary: this.calculateSummary(marcacoesMaria),
                dataExportacao: new Date().toISOString()
            }
        ];

        localStorage.setItem('marcacoes_pending_approval', JSON.stringify(pendingApprovals));
    }

    calculateSummary(marcacoes) {
        let totalHours = 0;
        let totalOvertime = 0;
        let totalPrevention = 0;
        let totalKm = 0;
        let workDays = 0;

        Object.values(marcacoes).forEach(marcacao => {
            if (marcacao.tipo === 'trabalho') {
                workDays++;
                totalHours += this.calculateHours(marcacao.horaInicio, marcacao.horaFim);
                totalOvertime += marcacao.horasExtra || 0;
                totalPrevention += marcacao.horasPrevencao || 0;
                totalKm += marcacao.kmViatura || 0;
            }
        });

        return {
            diasTrabalhados: workDays,
            horasTotais: Math.round(totalHours * 100) / 100,
            horasExtra: totalOvertime,
            horasPrevencao: totalPrevention,
            kmTotal: totalKm
        };
    }

    calculateHours(inicio, fim) {
        const [horaInicio, minInicio] = inicio.split(':').map(Number);
        const [horaFim, minFim] = fim.split(':').map(Number);
        
        const inicioMinutos = horaInicio * 60 + minInicio;
        const fimMinutos = horaFim * 60 + minFim;
        
        return (fimMinutos - inicioMinutos) / 60;
    }

    setupDemoMode() {
        // Criar indicador de modo demo
        const demoIndicator = document.createElement('div');
        demoIndicator.id = 'demo-indicator';
        demoIndicator.innerHTML = `
            <div style="
                position: fixed;
                top: 10px;
                right: 10px;
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 600;
                z-index: 10000;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                animation: pulse 2s infinite;
            ">
                MODO DEMONSTRAÇÃO
            </div>
            <style>
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.7; }
                }
            </style>
        `;

        document.body.appendChild(demoIndicator);

        // Adicionar botão para resetar dados demo
        const resetButton = document.createElement('button');
        resetButton.innerHTML = '🔄 Reset Demo';
        resetButton.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            z-index: 10000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        `;

        resetButton.addEventListener('click', () => {
            if (confirm('Resetar todos os dados de demonstração?')) {
                this.resetDemoData();
                location.reload();
            }
        });

        document.body.appendChild(resetButton);

        console.log('🎬 Modo Demonstração Ativado');
        console.log('📊 Dados de teste criados:');
        console.log('- Usuários: 5');
        console.log('- Pedidos de férias: 3'); 
        console.log('- Marcações de horários: 2 operadores');
        console.log('- Aprovações pendentes: 2');
    }

    resetDemoData() {
        const keysToRemove = [
            'demo_users',
            'pedidos_aprovados',
            'pedidos_pendentes', 
            'marcacoes_user_opera_001',
            'marcacoes_user_opera_002',
            'marcacoes_pending_approval',
            'marcacoes_processed',
            'ferias_ausencias_aprovadas',
            'marcacoes_horarios',
            'submitted_months'
        ];

        keysToRemove.forEach(key => {
            localStorage.removeItem(key);
        });

        console.log('🗑️ Dados de demonstração resetados');
    }

    // Método para popular com mais dados se necessário
    addMoreSampleData() {
        // Adicionar mais operadores
        // Adicionar mais marcações
        // Adicionar histórico de meses anteriores
        console.log('🔄 Adicionando mais dados de demonstração...');
        
        // Implementar conforme necessidade
    }
}

// Inicializar dados demo quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Só ativar demo se não houver dados reais
    const hasRealData = localStorage.getItem('marcacoes_horarios') || 
                       localStorage.getItem('pedidos_aprovados');
    
    if (!hasRealData) {
        window.dadosDemo = new DadosDemonstracao();
    }
});

// Exportar para uso manual
window.DadosDemonstracao = DadosDemonstracao;
