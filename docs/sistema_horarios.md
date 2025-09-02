# Sistema de Marcação e Aprovação de Horários

## Visão Geral

O novo sistema de marcação e aprovação de horários permite que operadores registem as suas horas de trabalho, horas extra, horas de prevenção e quilómetros em viatura própria, enquanto os supervisores (inter2) podem aprovar ou rejeitar essas marcações.

## Funcionalidades Implementadas

### Para Operadores (Role: opera)

#### 1. Marcação de Horários
- **Calendário Interativo**: Visualização mensal com possibilidade de navegar entre meses
- **Marcação por Dia**: Clique em qualquer dia para registar:
  - Hora de início e fim de trabalho
  - Horas extra (em minutos)
  - Horas de prevenção (em minutos) 
  - Quilómetros em viatura própria
  - Observações (opcional)

#### 2. Tipos de Dia
- **Dia de Trabalho**: Com marcação completa de horários
- **Dia de Descanso**: Apenas marcação de não trabalho

#### 3. Integração com Férias/Ausências
- Dias com pedidos aprovados de férias ou ausências aparecem automaticamente bloqueados
- Não é possível editar marcações em dias com férias/ausências aprovadas
- Sincronização automática quando pedidos são aprovados

#### 4. Resumo Mensal
- Total de horas trabalhadas
- Total de horas extra
- Total de quilómetros
- Dias trabalhados

#### 5. Submissão para Aprovação
- Botão "Submeter Mês" envia todas as marcações do mês para aprovação
- Após submissão, as marcações ficam bloqueadas para edição
- Notificação visual do status

### Para Supervisores (Role: inter2)

#### 1. Painel de Aprovação
- **Visualização por Abas**: Pendentes, Aprovadas, Rejeitadas
- **Contadores**: Número de aprovações em cada categoria
- **Pesquisa e Filtros**: Por nome, mês, etc.

#### 2. Gestão de Aprovações
- **Ver Detalhes**: Calendário detalhado com todas as marcações do mês
- **Aprovar**: Aprovação com um clique
- **Rejeitar**: Rejeição com motivo obrigatório
- **Histórico**: Visualização de todas as aprovações processadas

#### 3. Calendário Detalhado
- Visualização completa de todas as marcações do operador
- Informações de horários, horas extra, prevenção e KM por dia
- Observações registadas pelo operador

## Estrutura de Dados (LocalStorage)

### Marcações de Horários
```javascript
// Estrutura por operador
'marcacoes_user_[userId]': {
  "2024-09-15": {
    tipo: "trabalho",
    horaInicio: "09:00",
    horaFim: "17:00", 
    horasExtra: 60,      // minutos
    horasPrevencao: 30,  // minutos
    kmViatura: 25,
    observacoes: "Reunião de equipa",
    dataRegisto: "2024-09-15T08:00:00.000Z"
  }
}
```

### Aprovações Pendentes
```javascript
'marcacoes_pending_approval': [
  {
    userId: "user_opera_001",
    month: "2024-09",
    marcacoes: { /* dados do mês */ },
    summary: {
      diasTrabalhados: 20,
      horasTotais: 160,
      horasExtra: 120,    // minutos
      horasPrevencao: 60, // minutos
      kmTotal: 300
    },
    dataExportacao: "2024-09-30T17:00:00.000Z"
  }
]
```

### Integração Férias/Ausências
```javascript
'ferias_ausencias_aprovadas': {
  "2024-09-15": {
    tipo: "vacation",     // ou "absence"
    label: "Férias",      // ou tipo específico de ausência
    pedidoId: "req_001",
    userId: "user_opera_001"
  }
}
```

## Fluxo de Trabalho

### 1. Operador Marca Horários
1. Acede ao módulo "Consulta de Horários"
2. Clica num dia do calendário
3. Preenche os dados de trabalho
4. Guarda a marcação (armazenada localmente)
5. Repete para todos os dias do mês

### 2. Submissão Mensal
1. No final do mês, clica em "Submeter Mês"
2. Revê o resumo das marcações
3. Confirma a submissão
4. Dados são enviados para aprovação
5. Marcações ficam bloqueadas para edição

### 3. Aprovação pelo Supervisor
1. Supervisor acede ao painel "Aprovação de Horários"
2. Vê lista de marcações pendentes
3. Clica em "Ver Detalhes" para análise completa
4. Aprova ou rejeita (com motivo)
5. Operador é notificado do resultado

### 4. Integração com Férias
- Sistema monitora aprovações de férias/ausências
- Atualiza automaticamente o calendário de marcações
- Bloqueia dias com férias/ausências aprovadas

## Arquivos Implementados

### JavaScript
- `js/marcacao_horarios.js` - Sistema principal de marcação
- `js/integracao_ferias_horarios.js` - Integração com férias/ausências
- `js/dados_demonstracao.js` - Dados de teste e demonstração

### CSS
- `css/marcacao_horarios.css` - Estilos para sistema de marcação
- `css/aprovacao_horarios.css` - Estilos para painel de aprovação

### PHP
- `page/opera/horarios_new.php` - Nova página de marcação para operadores
- `page/inter2/aprovacao_horarios.php` - Painel de aprovação para supervisores

### Atualizações
- `js/dashboard_opera.js` - Atualizado para usar nova página de horários
- `js/dashboard_inter2.js` - Adicionado link para aprovação de horários

## Modo Demonstração

O sistema inclui um modo de demonstração que:
- Cria utilizadores de teste
- Gera marcações de exemplo
- Simula pedidos de férias aprovados
- Fornece dados realistas para teste

### Ativar/Desativar Demo
O modo demo ativa automaticamente se não houver dados reais. Inclui:
- Indicador visual "MODO DEMONSTRAÇÃO"
- Botão de reset dos dados demo
- Console logs informativos

## Integração com Backend

O sistema foi desenvolvido usando localStorage para armazenamento local. Para integração com o backend:

1. **Substituir localStorage** por chamadas AJAX aos endpoints PHP
2. **Criar APIs** para:
   - Guardar/carregar marcações
   - Submeter para aprovação
   - Processar aprovações
   - Sincronizar com férias/ausências

3. **Endpoints Sugeridos**:
   - `api/horarios/save_marcacao.php`
   - `api/horarios/submit_month.php`
   - `api/horarios/list_approvals.php`
   - `api/horarios/approve_reject.php`

## Segurança e Validação

### Client-side
- Validação de horários (início < fim)
- Prevenção de edição de dias com férias/ausências
- Bloqueio de marcações após submissão

### Server-side (a implementar)
- Verificação de permissões por role
- Validação de dados submetidos
- Auditoria de alterações
- Prevenção de manipulação de dados

## Responsividade

O sistema é totalmente responsivo:
- **Desktop**: Layout de grid completo
- **Tablet**: Adaptação de colunas
- **Mobile**: Layout vertical, modais full-screen

## Browser Support

Compatível com:
- Chrome/Edge (últimas versões)
- Firefox (últimas versões)
- Safari (últimas versões)

Utiliza:
- ES6+ features
- CSS Grid/Flexbox
- LocalStorage API
- Fetch API

## Futuras Melhorias

1. **Notificações Push** quando pedidos são aprovados/rejeitados
2. **Exportação para Excel** dos dados mensais
3. **Gráficos e Analytics** de produtividade
4. **Marcação por GPS** para validação de localização
5. **Integração com sistemas de ponto** existentes
6. **API mobile** para app dedicada
