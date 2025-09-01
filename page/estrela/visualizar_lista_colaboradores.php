<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "*") {
    echo "<p>Acesso negado.</p>";
    exit;
}

if (!isset($_GET["user_id"])) {
    echo "<p>ID do colaborador não fornecido.</p>";
    exit;
}

$user_id = intval($_GET["user_id"]);

try {
    $conn = db_connect();

    // Buscar dados do colaborador incluindo informações do user
    $stmt = $conn->prepare("
        SELECT cd.*, u.name as user_name, u.email as user_email 
        FROM colaborador_dados cd
        JOIN user u ON cd.user_id = u.id
        WHERE cd.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar contactos de emergência
    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $contactos_emergencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$dados) {
        echo "<p>Ficha de colaborador não encontrada.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>

<div class="ficha-page">
    <!-- Modern Header -->
    <div class="ficha-header">
        <div class="header-left">
            <div class="back-button" onclick="window.location.href='dashboard_estrela.php'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15,18 9,12 15,6"></polyline>
                </svg>
            </div>
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="header-text">
                <h2>Ficha do Colaborador</h2>
                <p><?= htmlspecialchars($dados["user_name"] ?? $dados["nome"] ?? "Colaborador") ?></p>
            </div>
        </div>
        <div class="role-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Painel Estrela
        </div>
    </div>

    <!-- Content Area -->
    <div class="ficha-content">
        <!-- Dados Pessoais -->
        <div class="info-section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <h3>Dados Pessoais</h3>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <span>Email</span>
                    </div>
                    <div class="info-card-content">
                        <?= htmlspecialchars($dados["user_email"] ?? $dados["email"] ?? "Não informado") ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <span>Telefone</span>
                    </div>
                    <div class="info-card-content">
                        <?= htmlspecialchars($dados["telefone"] ?? "Não informado") ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>Morada</span>
                    </div>
                    <div class="info-card-content">
                        <?= htmlspecialchars($dados["morada"] ?? "Não informado") ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                        <span>Profissão</span>
                    </div>
                    <div class="info-card-content">
                        <?= htmlspecialchars($dados["profissao"] ?? "Não informado") ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        <span>Categoria</span>
                    </div>
                    <div class="info-card-content">
                        <?= htmlspecialchars($dados["categoria"] ?? "Não informado") ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                        <span>NIB</span>
                    </div>
                    <div class="info-card-content">
                        <?= htmlspecialchars($dados["nib"] ?? "Não informado") ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contactos de Emergência -->
        <div class="info-section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                </div>
                <h3>Contactos de Emergência</h3>
            </div>

            <?php if (empty($contactos_emergencia)): ?>
                <div class="empty-contacts">
                    <div class="empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <h4>Nenhum contacto registado</h4>
                    <p>Este colaborador ainda não possui contactos de emergência cadastrados.</p>
                </div>
            <?php else: ?>
                <div class="contacts-grid">
                    <?php foreach ($contactos_emergencia as $index => $c): ?>
                        <div class="contact-card">
                            <div class="contact-header">
                                <div class="contact-avatar">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                                <div class="contact-info">
                                    <div class="contact-name"><?= htmlspecialchars($c['nome'] ?? 'Nome não informado') ?></div>
                                    <div class="contact-relation"><?= htmlspecialchars($c['parentesco'] ?? 'Parentesco não informado') ?></div>
                                </div>
                            </div>
                            <div class="contact-phone">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <?= htmlspecialchars($c['telefone'] ?? 'Telefone não informado') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Ficha Page Styles */
.ficha-page {
    padding: 0;
    background: #f8fafc;
    min-height: 100vh;
}

.ficha-header {
    background: white;
    padding: 2rem 2.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.back-button {
    width: 40px;
    height: 40px;
    background: #f1f5f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--navy-blue);
    cursor: pointer;
    transition: all 0.2s ease;
}

.back-button:hover {
    background: var(--light-blue);
    color: white;
}

.page-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 8px 24px rgba(62, 132, 242, 0.25);
}

.header-text h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
    letter-spacing: -0.5px;
}

.header-text p {
    color: var(--text-light);
    font-size: 1rem;
    margin: 0;
}

.role-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    border-radius: 12px;
    font-weight: 500;
    font-size: 0.875rem;
}

.ficha-content {
    padding: 2rem 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.info-section {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
}

.section-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.section-header h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin: 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.info-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.info-card:hover {
    background: #f1f5f9;
    border-color: var(--light-blue);
}

.info-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    color: var(--navy-blue);
    font-weight: 600;
    font-size: 0.875rem;
}

.info-card-content {
    color: var(--text-dark);
    font-weight: 500;
    font-size: 1rem;
    word-break: break-word;
}

.contacts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.contact-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.contact-card:hover {
    background: #f1f5f9;
    border-color: var(--light-blue);
}

.contact-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.contact-avatar {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--navy-blue), #1a365d);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.contact-name {
    font-weight: 600;
    color: var(--navy-blue);
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.contact-relation {
    color: var(--text-light);
    font-size: 0.875rem;
}

.contact-phone {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-dark);
    font-weight: 500;
    font-size: 0.925rem;
}

.empty-contacts {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-light);
}

.empty-contacts .empty-icon {
    width: 80px;
    height: 80px;
    background: #f1f5f9;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem auto;
    color: #9ca3af;
}

.empty-contacts h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 0.5rem;
}

.empty-contacts p {
    font-size: 1rem;
    line-height: 1.6;
    max-width: 400px;
    margin: 0 auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .ficha-header {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
        padding: 1.5rem;
    }

    .header-left {
        justify-content: center;
    }

    .ficha-content {
        padding: 1rem;
    }

    .info-section {
        padding: 1.5rem;
    }

    .info-grid,
    .contacts-grid {
        grid-template-columns: 1fr;
    }

    .section-header {
        flex-direction: column;
        text-align: center;
    }
}
</style>
