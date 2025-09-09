<?php
// api/timesheets/diagnostic_approved.php
// Script para diagnosticar problemas com horários aprovados
declare(strict_types=1);
session_start();

/* Auth */
if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'UNAUTHENTICATED']); exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/role_utils.php';
header('Content-Type: application/json; charset=utf-8');

$user_id = (int)($_SESSION['user']['id'] ?? 0);
$user_role = $_SESSION['user']['role'] ?? '';
$user_name = $_SESSION['user']['nome'] ?? $_SESSION['user']['name'] ?? 'N/A';

// Informações da sessão
$session_info = [
    'user_id' => $user_id,
    'user_role' => $user_role,
    'user_name' => $user_name,
    'normalized_role' => normalize_role($user_role),
    'has_admin_permission' => has_permission($user_role, ['admin_rh', 'adminrh'])
];

// Se não tem permissão de admin, mostrar apenas info da sessão
if (!has_permission($user_role, ['admin_rh', 'adminrh', 'admin', 'estrela'])) {
    echo json_encode([
        'success' => true,
        'session_info' => $session_info,
        'message' => 'Diagnóstico limitado - sem permissões de administrador'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Verificar nome da coluna no user
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }

// Estatísticas de períodos
$stats_periods = $pdo->query("
    SELECT 
        estado,
        COUNT(*) as count,
        MIN(period_start) as earliest_start,
        MAX(period_end) as latest_end
    FROM timesheet_periods 
    GROUP BY estado
")->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas de eventos  
$stats_events = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count,
        MIN(dia) as earliest_date,
        MAX(dia) as latest_date
    FROM eventos 
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Períodos aprovados recentes (últimos 3 meses)
$approved_periods = $pdo->query("
    SELECT 
        tp.id,
        tp.user_id,
        u.$nameCol as user_name,
        tp.period_start,
        tp.period_end,
        tp.decidido_em,
        decidido_por.nome as decidido_por_nome
    FROM timesheet_periods tp
    JOIN user u ON u.id = tp.user_id
    LEFT JOIN user decidido_por ON decidido_por.id = tp.decidido_por
    WHERE tp.estado = 'approved'
      AND tp.period_start >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    ORDER BY tp.decidido_em DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Eventos aprovados recentes
$approved_events = $pdo->query("
    SELECT 
        e.user_id,
        u.$nameCol as user_name,
        COUNT(*) as event_count,
        MIN(e.dia) as start_date,
        MAX(e.dia) as end_date
    FROM eventos e
    JOIN user u ON u.id = e.user_id
    WHERE e.status = 'approved'
      AND e.dia >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    GROUP BY e.user_id, u.$nameCol
    ORDER BY event_count DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Resposta completa
echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'session_info' => $session_info,
    'database_stats' => [
        'periods_by_status' => $stats_periods,
        'events_by_status' => $stats_events
    ],
    'recent_approved_data' => [
        'periods' => $approved_periods,
        'events_summary' => $approved_events
    ],
    'troubleshooting_tips' => [
        'check_session_role' => 'Verificar se o papel da sessão é exatamente "adminrh" ou "admin_rh"',
        'check_approved_periods' => 'Verificar se existem períodos com estado="approved"',
        'check_approved_events' => 'Verificar se existem eventos com status="approved"',
        'check_date_format' => 'Usar formato YYYY-MM para filtros de mês',
        'check_user_ids' => 'Fornecer IDs válidos na exportação'
    ]
], JSON_UNESCAPED_UNICODE);