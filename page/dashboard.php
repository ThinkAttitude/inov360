<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$userName = $_SESSION['user']['name'] ?? '';
?>
<script>
    window.CURRENT_USER = <?= json_encode($_SESSION['user'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>RH360 — Painel</title>

    <!-- Replace these paths to match your project layout -->
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboard.css"/>

    <!-- Google font used by original dashboards -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">

    <!-- Sidebar -->
    <nav class="sidebar" aria-label="Navegação principal">
        <div class="sidebar-header">
            <div class="logo" id="companyLogo">
                <!-- default inline SVG logo; JS can replace with company image if available -->
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
            </div>

            <div class="company-title">
                <h3 id="companyName">RH360</h3>
                <p class="subtitle" id="companySubtitle">Painel</p>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="active">
                <a href="#" data-content="inicio">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9,22 9,12 15,12 15,22"></polyline>
                    </svg>
                    Início
                </a>
            </li>
            <li>
                <a href="#" data-content="horarios">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12,6 12,12 16,14"></polyline>
                    </svg>
                    Consulta de Horários
                </a>
            </li>
            <li>
                <a href="#" data-content="pedidos_ferias">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Pedidos de Férias/Ausências
                </a>
            </li>
            <li>
                <a href="#" data-content="ficha_colab">
                    <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            <div class="role-indicator" id="roleIndicator">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span id="roleLabel">Administrador</span>
            </div>

            <a href="#" id="logoutBtn" class="logout-btn">Terminar Sessão</a>
        </div>
    </nav>

    <!-- Main content -->
    <main class="main-content" id="main-content">
        <div class="main-header">
            <h2>Bem-vindo, <?= htmlspecialchars($userName) ?>!</h2>
            <p>Gerencie o sistema, aprove pedidos e supervisione todas as operações do RH360.</p>
        </div>

        <!-- template para welcome-card -->
        <template id="tpl-welcome-card">
            <article class="welcome-card" role="article" aria-live="polite">
                <div class="card-icon" aria-hidden="true"></div>
                <h3 class="card-title"></h3>
                <p class="card-desc"></p>
                <a href="#" class="card-link" data-content="">Ver</a>
            </article>
        </template>

        <div class="welcome-content">
            <div class="welcome-card">
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12,6 12,12 16,14"></polyline>
                    </svg>
                </div>
                <h3>Consulta de Horários</h3>
                <p>Visualize e gerencie horários de todos os colaboradores da organização.</p>
                <a href="#" class="card-link" data-content="horarios">
                    Ver Horários
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </a>
            </div>

            <div class="welcome-card">
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <h3>Férias e Ausências</h3>
                <p>Solicite os seus próprios pedidos de férias e ausências como administrador.</p>
                <a href="#" class="card-link" data-content="pedidos_ferias">
                    Gerir Pedidos
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </a>
            </div>
            <div class="welcome-card">
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10,9 9,9 8,9"></polyline>
                    </svg>
                </div>
                <h3>A Minha Ficha</h3>
                <p>Visualize e edite a sua própria ficha pessoal de colaborador.</p>
                <a href="#" class="card-link" data-content="ficha_colab">
                    Ver Ficha
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </a>
            </div>
        </div>
        <footer>
            <small>&copy; <span id="yearSpan"></span> RH360</small>
        </footer>

    </main>
</div>

<script src="../js/theme.js"></script>
<script type="module" src="../js/dashboard.js"></script>
</body>
</html>
