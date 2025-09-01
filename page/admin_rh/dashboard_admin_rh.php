<?php
session_start();
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}
// Evitar cache do HTML para refletir empresa/tema logo no primeiro load
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once "../../api/includes/db.php";
$company = null;
try {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT c.name, c.slug, c.logo_path FROM user u LEFT JOIN company c ON c.id = u.company_id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Exception $e) { /* ignore */ }
function norm_slug($s){ $s = strtolower($s ?? ''); $s = preg_replace('/[^a-z0-9\-]+/','-',$s); if($s==='thinkattitude'||$s==='think-attitude'){return 'think-attitude';} return trim($s,'-'); }
$slug = norm_slug($company['slug'] ?? '');
$baseUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$logoUrl = !empty($company['logo_path']) ? ($baseUrl . $company['logo_path']) : null;
$logoUrlBusted = $logoUrl ? ($logoUrl . (strpos($logoUrl,'?')!==false ? '&' : '?') . '_=' . time()) : null;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RH360 - Painel Admin RH</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="../../css/dashboard_admin.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<script>
    window.__COMPANY__ = <?= json_encode([
        'name' => $company['name'] ?? null,
        'slug' => $slug ?: null,
        'logo' => $logoUrl,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    (function(){ try { if(window.__COMPANY__ && window.__COMPANY__.slug){ document.body.classList.add('theme-'+window.__COMPANY__.slug); } } catch(e){} })();
</script>
<div class="dashboard-container">
    <!-- Modern Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo<?= $logoUrl ? ' has-company-logo' : '' ?>">
                <?php if ($logoUrlBusted): ?>
                    <img src="<?= htmlspecialchars($logoUrlBusted) ?>" alt="<?= htmlspecialchars($company['name'] ?? 'Company Logo') ?>" />
                <?php else: ?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                <?php endif; ?>
            </div>
            <h3><?= htmlspecialchars($company['name'] ?? 'RH360') ?></h3>
            <p class="subtitle">Painel Admin RH</p>
        </div>

        <ul class="sidebar-menu">
            <li class="active">
                <a href="#" data-content="inicio">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9,22 9,12 15,12 15,22"></polyline>
                    </svg>
                    Início
                </a>
            </li>
            <li>
                <a href="#" data-content="horarios">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12,6 12,12 16,14"></polyline>
                    </svg>
                    Consulta de Horários
                </a>
            </li>
            <li>
                <a href="#" data-content="pedidos_ferias">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Férias/Ausências
                </a>
            </li>
            <li>
                <a href="#" data-content="gestao_fichas_colaboradores">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                    Gestão de Fichas
                </a>
            </li>
            <li>
                <a href="#" data-content="marcacao_direta_ferias">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <calendar />
                        <path d="M8 2v4"></path>
                        <path d="M16 2v4"></path>
                        <path d="M21 10H3"></path>
                        <path d="M12 14l2 2 4-4"></path>
                    </svg>
                    Marcação Direta
                </a>
            </li>
            <li>
                <a href="#" data-content="criar_colaborador">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" y1="8" x2="19" y2="14"></line>
                        <line x1="22" y1="11" x2="16" y2="11"></line>
                    </svg>
                    Criar Colaborador
                </a>
            </li>
            <li>
                <a href="#" data-content="consulta_pedidos">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    Consulta de Pedidos
                </a>
            </li>
            <li>
                <a href="#" data-content="ficha_colab">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10,9 9,9 8,9"></polyline>
                    </svg>
                    A Minha Ficha
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="role-indicator">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 0.5rem;">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
                <?= ucfirst($_SESSION['user']['role'] ?? 'Utilizador') ?>
            </div>

            <a href="../../api/logout.php" class="logout-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16,17 21,12 16,7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Terminar Sessão
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content" id="main-content">
        <div class="main-header">
            <h2>Bem-vindo, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin RH') ?>!</h2>
            <p>Gerencie recursos humanos, fichas de colaboradores e administre todo o sistema RH360.</p>
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
                <p>Visualize e gerencie horários de todos os colaboradores da organização.</p>
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
                <p>Solicite e gerencie pedidos de férias e ausências de todos os colaboradores.</p>
                <a href="#" class="card-link" data-content="pedidos_ferias">
                    Gerir Pedidos
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </a>
            </div>

            <div class="welcome-card">
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                </div>
                <h3>Gestão de Fichas</h3>
                <p>Gerencie e aprove alterações das fichas de colaboradores de toda a organização.</p>
                <a href="#" class="card-link" data-content="gestao_fichas_colaboradores">
                    Gerir Fichas
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
                        <path d="M12 14l2 2 4-4"></path>
                    </svg>
                </div>
                <h3>Marcação Direta</h3>
                <p>Marque férias e ausências diretamente para qualquer colaborador.</p>
                <a href="#" class="card-link" data-content="marcacao_direta_ferias">
                    Marcação Direta
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </a>
            </div>

            <div class="welcome-card">
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" y1="8" x2="19" y2="14"></line>
                        <line x1="22" y1="11" x2="16" y2="11"></line>
                    </svg>
                </div>
                <h3>Criar Colaborador</h3>
                <p>Adicione novos colaboradores ao sistema e configure suas informações.</p>
                <a href="#" class="card-link" data-content="criar_colaborador">
                    Criar Colaborador
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
                <h3>Consulta de Pedidos</h3>
                <p>Acesse o histórico completo de todos os pedidos realizados no sistema.</p>
                <a href="#" class="card-link" data-content="consulta_pedidos">
                    Ver Histórico
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </a>
            </div>

            <div class="welcome-card">
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3>A Minha Ficha</h3>
                <p>Visualize e edite a sua própria ficha pessoal de colaborador.</p>
                <a href="#" class="card-link" data-content="ficha_colab">
                    Ver Ficha
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </main>
</div>

<script src="../../js/dashboard_admin_rh.js?v=20250825"></script>
<script src="../../js/theme.js?v=20250825"></script>
<script>
// Mostrar toast após marcação direta bem sucedida
document.addEventListener('DOMContentLoaded', function(){
    const params = new URLSearchParams(window.location.search);
    if(params.get('marcacao') === 'sucesso'){
        if(typeof showToast === 'function'){
            showToast('✅ Marcação direta registada com sucesso!', 'success');
        }
        // Limpa o parâmetro da URL (sem recarregar)
        const url = new URL(window.location.href);
        url.searchParams.delete('marcacao');
        window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
    }
});
</script>
</body>
</html>
