# APIs para Extração de Dados de Pedidos Aprovados

## Visão Geral

Este documento descreve as APIs implementadas para permitir que o papel "adminrh" acesse e exporte dados de pedidos/horários aprovados pelo papel "inter2".

## Fluxo de Aprovação

1. **Submissão**: Usuários "opera" submetem períodos de timesheet com `estado='submitted'`
2. **Aprovação**: Usuários "inter2" aprovam os períodos usando `aval_decide.php`
3. **Resultado**: Períodos aprovados ficam com `estado='approved'` e eventos com `status='approved'`
4. **Acesso**: Usuários "adminrh" podem listar e exportar os dados aprovados

## APIs Disponíveis

### 1. Listagem de Períodos Aprovados

**Endpoint**: `api/timesheets/periods_approved_list.php`

**Método**: GET

**Autenticação**: Papel 'adminrh' ou 'admin_rh'

**Parâmetros**:
- `month` (opcional): Formato YYYY-MM para filtrar por mês
- `q` (opcional): Pesquisa por nome ou email do usuário
- `limit` (opcional): Número de resultados por página (padrão: 200)
- `offset` (opcional): Offset para paginação (padrão: 0)

**Exemplo de uso**:
```
GET api/timesheets/periods_approved_list.php?month=2024-01&limit=50
```

**Resposta**:
```json
{
  "success": true,
  "filters": {
    "month": "2024-01",
    "q": null,
    "limit": 50,
    "offset": 0
  },
  "total": 25,
  "rows": [
    {
      "period_id": 123,
      "user_id": 45,
      "nome": "João Silva",
      "email": "joao@empresa.com",
      "company_name": "Empresa XYZ",
      "month": "2024-01",
      "period_start": "2024-01-01",
      "period_end": "2024-01-31"
    }
  ]
}
```

### 2. Exportação para Excel

**Endpoint**: `api/timesheets/periods_users_export.php`

**Método**: GET

**Autenticação**: Papel 'adminrh' ou 'admin_rh'

**Parâmetros**:
- `month` (obrigatório): Formato YYYY-MM
- `user_ids` (obrigatório): Array ou CSV de IDs de usuários

**Exemplo de uso**:
```
GET api/timesheets/periods_users_export.php?month=2024-01&user_ids[]=45&user_ids[]=67
```
ou
```
GET api/timesheets/periods_users_export.php?month=2024-01&user_ids=45,67
```

**Resposta**: Arquivo Excel (.xlsx) com dados dos colaboradores

## Normalização de Papéis

O sistema agora inclui uma função de normalização (`api/includes/role_utils.php`) que aceita tanto:
- `admin_rh` (convenção documental)
- `adminrh` (implementação existente)

## Tratamento de Erros

Todas as APIs agora retornam erros padronizados em formato JSON:

```json
{
  "success": false,
  "error": "CODIGO_ERRO",
  "message": "Descrição do erro (opcional)"
}
```

**Códigos de erro comuns**:
- `UNAUTHENTICATED`: Usuário não autenticado
- `FORBIDDEN`: Usuário sem permissão para a ação
- `INVALID_MONTH_FORMAT`: Formato de mês inválido (use YYYY-MM)
- `MISSING_USER_IDS`: IDs de usuários não fornecidos

## Resolução de Problemas

Se os horários aprovados não estão aparecendo:

1. **Verificar autenticação**: Confirme que o usuário tem papel 'adminrh' ou 'admin_rh'
2. **Verificar dados**: Confirme que existem registros com `timesheet_periods.estado='approved'`
3. **Verificar eventos**: Confirme que existem `eventos.status='approved'` para o período
4. **Verificar formato**: Use formato YYYY-MM para o parâmetro month
5. **Verificar IDs**: Forneça IDs válidos de usuários na exportação

## Exemplo de Workflow Completo

1. **Listar períodos aprovados**:
```bash
curl -X GET "api/timesheets/periods_approved_list.php?month=2024-01" \
  -H "Cookie: session_cookie"
```

2. **Extrair IDs dos usuários da resposta**

3. **Exportar para Excel**:
```bash
curl -X GET "api/timesheets/periods_users_export.php?month=2024-01&user_ids=45,67" \
  -H "Cookie: session_cookie" \
  -o "relatorio_2024-01.xlsx"
```