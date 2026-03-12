# Revisão Completa — INOV360

**Data:** 12 de março de 2026  
**Branch:** `ze`

---

## Legenda de estados

| Símbolo | Significado |
|---------|-------------|
| ✅ FE+BE | Frontend + Backend integrados e funcionais |
| 🟡 BE only | Backend pronto, frontend por fazer |
| 🟠 FE shell | Frontend é HTML estático sem JS, backend pode existir |
| 🚫 Rota morta | Rota no router aponta para ficheiro que **não existe** no filesystem |

---

## Autenticação

| Item | Estado | Detalhe |
|------|--------|---------|
| Login com permissões | ✅ FE+BE | `login.js` → `auth/login.php`. Carrega permissões da tabela `user_permission` para sessão |
| Controlo de acesso por permissões | ✅ FE+BE | `dashboard.js` filtra cards e sidebar via `computeCardsFromUser()`. Backend valida em cada endpoint |

---

## Início / Dashboard

| Item | Estado | Detalhe |
|------|--------|---------|
| Sidebar com tabs por módulo | ✅ FE+BE | Dinâmico com base nas permissões do user |
| Cartões por módulo | ✅ FE+BE | `inicio.html` + `dashboard.js` com cards clicáveis |
| Roteamento por hash | ✅ FE+BE | SPA completo via `router.js`, 8 rotas com mount functions |

---

## Consulta de Horários

| Item | Estado | Detalhe |
|------|--------|---------|
| Calendário (dia/semana/mês) | ✅ FE+BE | `horarios/` usa FullCalendar + `calendar/get_month.php` |
| Visualizar dias de trabalho/ausência/férias | 🟡 BE only | Backend `get_month.php` retorna eventos tipados. Frontend renderiza badges mas sem distinção visual completa por tipo |
| Marcar horário (horas + km) | ✅ FE+BE | `horarios_form.js` → `calendar/batch_apply.php` e `day_put.php` |
| Marcar horário em múltiplos dias | ✅ FE+BE | `createEventBatch(start, end, ...)` → `batch_apply.php` |
| Limpar horário | 🟡 BE only | Backend `batch_clear.php` e `day_delete.php` existem. Frontend não tem UI de "limpar" implementada |
| Limpar em múltiplos dias | 🟡 BE only | Backend `batch_clear.php` existe. Sem UI frontend |

---

## Pedidos de Férias / Ausências

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel de pedidos e estatísticas | 🟠 FE shell | `pedidos_ferias/view.html` é HTML estático. `mount: null` no router, **sem JS** |
| Obter estatísticas | 🟡 BE only | `leaves/collab_summary.php` existe |
| Listar pedidos do utilizador | 🟡 BE only | `leaves/collab_requests.php` existe |
| Formulário de criação | 🟡 BE only | `leaves/request.php` existe |
| Criar pedido | 🟡 BE only | `leaves/request.php` aceita POST |
| Anexar ficheiro comprovativo | 🟡 BE only | Suportado no backend |
| Filtrar por estado | 🟡 BE only | `leaves/collab_requests.php` aceita filtro `state` |

---

## Mapas de Horas

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel de extração | 🚫 Rota morta | Router → `mapas_horarios.php` que **não existe** no filesystem |
| Pesquisar por nome e mês | 🟡 BE only | `periods/periods_approved_list.php` existe |
| Converter para .xlsx | 🟡 BE only | `periods/periods_users_export.php` gera Excel |
| Exportar | 🟡 BE only | Mesmo endpoint |
| Nº registos aprovados/extraídos | 🟡 BE only | `periods/periods_approved_list.php` retorna contagens |

---

## Propor Horas Extra

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel de horas extra | ✅ FE+BE | `pedidos_horas_extras/` completo |
| Listar colaboradores | ✅ FE+BE | `getAllCollaborators()` → `collabs_list.php` |
| Submeter pedido | ✅ FE+BE | `requestOvertime()` → `request_overtime.php` |

---

## Horas Extra (Aprovação)

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel de listagem/aprovação/rejeição | ✅ FE+BE | `horas_extra/` — tab Pendentes com tabela |
| Listar todos os pedidos | ✅ FE+BE | `getOvertimeRequests()` → `request_list.php` |
| Aprovar ou recusar | ✅ FE+BE | Modal com comentário → `approve_overtime.php` |
| Histórico de pedidos | ✅ FE+BE | Tab Histórico → `sheets_review.php` |
| Listar anteriores (aprovados/recusados) | ✅ FE+BE | Filtros por mês/estado/texto |
| Visualizar pedido aprovado | ✅ FE+BE | Modal de detalhe |
| Selecionar pedidos aprovados | ✅ FE+BE | Tab Exportar com checkboxes |
| Exportar para .xlsx | ✅ FE+BE | `exportOvertimeSheets()` → `sheets_export.php` |

---

## Férias / Ausências Diretas

| Item | Estado | Detalhe |
|------|--------|---------|
| Formulário com campos obrigatórios | ✅ FE+BE | `marcacao_direta/` completo |
| Listar colaboradores | ✅ FE+BE | `getAllCollaborators()` |
| Importar comprovativo | ✅ FE+BE | Upload com validação (PDF/JPG/PNG, 5MB) |
| Submeter marcação | ✅ FE+BE | `createDirectLeave()` → `leaves/direct_leave.php` |

---

## Gestão de Fichas

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel + tabela de colaboradores | ✅ FE+BE | `gestao_fichas/` com tabs Pedidos/Fichas |
| Listar colaboradores | ✅ FE+BE | `getAllRecords()` → `aval_list_all.php` |
| Exportar para .xlsx | 🟡 BE only | Backend `profile_export.php` existe. Sem botão de export no frontend |
| Lista de pedidos pendentes | ✅ FE+BE | `getAllPendingRequests()` + diff viewer |
| Aprovar/recusar pedido | ✅ FE+BE | `createDecision()` → `aval_decision.php` |
| Edição direta de ficha | ✅ FE+BE | `updateRecord()` → `direct_edit.php` + modo edição no frontend |

---

## Controlo de Colaboradores

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel de controlo | ✅ FE+BE | `controlo_colabs/` com dropdown + cards |
| Criar colaboradores | ✅ FE+BE | `createCollaborator()` → `create_collabs.php` |
| Alterar permissões | ✅ FE+BE | Toggle chips → `permissions_update.php` |
| Alterar hierarquia | ✅ FE+BE | Modal com drag → `hierarchy_update.php` |
| Diagrama hierárquico | ✅ FE+BE | Org chart SVG → `get_hierarchy.php` |
| Registar árvore hierárquica | ✅ FE+BE | `updateHierarchy()` via modal |

---

## Aprovação de Horários

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel de aprovação | 🚫 Rota morta | Router → `aprovacao_horarios.php` que **não existe** |
| Listar pendentes | 🟡 BE only | `periods/aval_periods.php` existe |
| Aprovar/recusar | 🟡 BE only | `periods/aval_decision.php` existe |

---

## Aprovação de Férias / Ausências

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel de aprovação | 🚫 Rota morta | Router → `aprovacao_ferias.php` que **não existe** |
| Listar pendentes | 🟡 BE only | `leaves/aval_requests.php` existe |
| Aprovar/recusar | 🟡 BE only | `leaves/decision.php` existe |

---

## Consulta de Pedidos

| Item | Estado | Detalhe |
|------|--------|---------|
| Painel do histórico | 🚫 Rota morta | Router → `consulta_pedidos.html` que **não existe** |
| Listar processados | 🟡 BE only | `leaves/aval_history_list.php` existe |
| Visualizar comprovativo | 🟡 BE only | Ficheiro referenciado nos dados |
| Nº pedidos processados | 🟡 BE only | `leaves/aval_history_summary.php` existe |

---

## A Minha Ficha

| Item | Estado | Detalhe |
|------|--------|---------|
| Painéis separados por categoria | ✅ FE+BE | `ficha_collab/` com secções (pessoal, familiar, fiscal, etc.) |
| Obter e listar informação | ✅ FE+BE | `getSelfRecord()` → `view_self.php` |
| Alterar dados e registar | ✅ FE+BE | `createRecordRequest()` → cria pedido de alteração |

---

## Resumo Quantitativo

| Estado | Contagem |
|--------|----------|
| ✅ **Integrado FE+BE** | ~30 items |
| 🟡 **Backend pronto, sem frontend** | ~18 items |
| 🟠 **Frontend shell sem JS** | 1 (pedidos_ferias) |
| 🚫 **Rotas mortas (ficheiros não existem)** | 5 rotas |

---

## Problemas Críticos

1. **5 rotas mortas no router** — apontam para ficheiros PHP/HTML que não existem no filesystem:
   - `aprovacao_horarios/aprovacao_horarios.php`
   - `mapas_horarios/mapas_horarios.php`
   - `aprovacao_ferias/aprovacao_ferias.php`
   - `consulta_pedidos/consulta_pedidos.html`
   - `lista_intermedios/lista_intermedios.php`

2. **`ferias_ausencias/` contém um ficheiro JS vazio** — possível duplicação com `pedidos_ferias/`

3. **Todos os backends de férias, mapas de horas, aprovação de horários e financeira estão prontos** mas sem frontend SPA integrado

---

## Inventário de Módulos Frontend

| Módulo | Tipo | Mount | API Integrada |
|--------|------|-------|---------------|
| `dashboard/` | SPA | `mountDashboardShell` + `mountInicio` | `me()` |
| `login/` | Standalone | Webpack entry point | `login()` |
| `horarios/` | SPA | `mountSchedule` | `getCalendarTimeframe`, `createEventBatch` |
| `controlo_colabs/` | SPA | `mountClbMngmt` | `getAllCollaborators`, `updatePermissions`, `updateHierarchy`, `getHierarchyByUser` |
| `marcacao_direta/` | SPA | `initForm` | `createDirectLeave`, `getAllCollaborators` |
| `gestao_fichas/` | SPA | `mountGstFchs` | `getAllPendingRequests`, `getAllRecords`, `createDecision`, `updateRecord` |
| `ficha_collab/` | SPA | `mountFichaCollab` | `getSelfRecord`, `createRecordRequest` |
| `pedidos_horas_extras/` | SPA | `mountPedidosHorasExtra` | `requestOvertime`, `getAllCollaborators` |
| `horas_extra/` | SPA | `mountHorasExtra` | `getOvertimeRequests`, `approveOvertime`, `getOvertimeHistory`, `exportOvertimeSheets` |
| `pedidos_ferias/` | Shell | `null` | Nenhuma |
| `ferias_ausencias/` | Vazio | N/A | Nenhuma |

---

## Backend — 53 Endpoints (todos funcionais)

| Área | Endpoints | Permissão |
|------|-----------|-----------|
| Auth | 5 (`login`, `login_sub`, `logout`, `me`, `register_sub`) | — |
| Calendar | 7 (`batch_apply`, `batch_clear`, `day_delete`, `day_put`, `get_month`, `month_status`, `submit_month`) | — |
| Leaves | 9 (`request`, `decision`, `aval_summary`, `aval_requests`, `collab_requests`, `collab_summary`, `direct_leave`, `aval_history_list`, `aval_history_summary`) | 2 (direct_leave) |
| Employee Info | 12 (records + finance sub-endpoints) | 6, 7 |
| Collab Management | 6 (`collabs_list`, `collab_org_tree`, `create_collabs`, `get_hierarchy`, `hierarchy_update`, `permissions_update`) | 1 |
| Overtime | 5 (`request_overtime`, `approve_overtime`, `request_list`, `sheets_export`, `sheets_review`) | 4, 5 |
| Timesheet Review | 5 (`aval_decision`, `aval_month`, `aval_periods`, `periods_approved_list`, `periods_users_export`) | 3 |
| Obras/SHT | 12 (associações, obras, subempreiteiros) | 8 |
| Outros | 2 (`listar_empresas`, `listar_eventos`) | — |
