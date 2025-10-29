<?php
session_start();

// Prevent caching of this dynamic HTML to avoid showing stale company/theme
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
// Fetch company for current user to apply theme immediately (no API changes)
require_once "../../api/includes/db.php";
$company = null;
try {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT c.name, c.slug, c.logo_path FROM user u LEFT JOIN company c ON c.id = u.company_id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Exception $e) {
    // ignore
}
function norm_slug($s){
    $s = strtolower($s ?? '');
    $s = preg_replace('/[^a-z0-9\-]+/','-',$s);
    if ($s === 'thinkattitude' || $s === 'think-attitude' || $s === 'think-attitude-') { return 'think-attitude'; }
    return trim($s,'-');
}
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
    <title>RH360 - Painel Estrela</title>
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="../../css/legacy/dashboard_inter.css">
    <link rel="stylesheet" href="../../css/legacy/dashboard_estrela.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <script>
        // Inject company info early for theme
        window.__COMPANY__ = <?= json_encode([
            'name' => $company['name'] ?? null,
            'slug' => $slug ?: null,
            'logo' => $logoUrl,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
        (function(){
            try { if (window.__COMPANY__ && window.__COMPANY__.slug) { document.body.classList.add('theme-' + window.__COMPANY__.slug); } } catch(e){}
        })();
    </script>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo<?= $logoUrl ? ' has-company-logo' : '' ?>">
                    <?php if ($logoUrlBusted): ?>
                        <img src="<?= htmlspecialchars($logoUrlBusted) ?>" alt="<?= htmlspecialchars($company['name'] ?? 'Company Logo') ?>" />
                    <?php else: ?>
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <span>RH360</span>
                    <?php endif; ?>
                </div>
            </div>

            <ul class="sidebar-menu">
                <li class="active">
                    <a href="#" data-content="inicio">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9,22 9,12 15,12 15,22"/>
                        </svg>
                        Início
                    </a>
                </li>
                <li>
                    <a href="#" data-content="aprovacao_ferias_ausencias">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,11 12,14 22,4"/>
                            <path d="M21,12v7a2,2 0,0 1,-2,2H5a2,2 0,0 1,-2,-2V5a2,2 0,0 1,2,-2h11"/>
                        </svg>
                        Aprovação de Férias/Ausências
                    </a>
                </li>
                <li>
                    <a href="#" data-content="consulta_pedidos">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Consulta Histórico de Pedidos
                    </a>
                </li>
                <li>
                    <a href="#" data-content="consulta_lista">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Consulta Lista de Colaboradores
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Utilizador') ?></div>
                        <div class="user-role">Estrela</div>
                    </div>
                </div>
                <a href="../../api/auth/logout.php" class="logout-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16,17 21,12 16,7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Sair
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <!-- Content will be loaded here -->
        </main>
    </div>

    <script src="../../js/legacy/dashboard_estrela.js?v=20250825"></script>
    <script src="../../js/theme.js?v=20250825"></script>
</body>
</html>
