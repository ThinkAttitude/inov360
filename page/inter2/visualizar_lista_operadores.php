<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
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
    $stmt = $conn->prepare("SELECT * FROM colaborador_dados WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar contactos de emergência
    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $contactos_emergencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar dados do utilizador
    $stmt = $conn->prepare("SELECT name, email FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados && !$user_dados) {
        echo "<p>Colaborador não encontrado.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}

function safe($value) {
    return htmlspecialchars($value ?? 'Não especificado', ENT_QUOTES, 'UTF-8');
}
?>

<div class="ficha-completa">
    <div class="ficha-header">
        <div class="operador-avatar-large">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <h3><?= safe($dados["nome"] ?? $user_dados["name"]) ?></h3>
        <p>Operador</p>
    </div>

    <div class="ficha-content">
        <div class="section">
            <h4>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Dados Pessoais
            </h4>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Nome</span>
                    <span class="value"><?= safe($dados["nome"] ?? $user_dados["name"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Email</span>
                    <span class="value"><?= safe($dados["email"] ?? $user_dados["email"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Telefone</span>
                    <span class="value"><?= safe($dados["telefone"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Morada</span>
                    <span class="value"><?= safe($dados["morada"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Data de Nascimento</span>
                    <span class="value"><?= $dados["data_nascimento"] ? date("d/m/Y", strtotime($dados["data_nascimento"])) : 'Não especificado' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Estado Civil</span>
                    <span class="value"><?= safe($dados["estado_civil"]) ?></span>
                </div>
            </div>
        </div>

        <?php if ($dados): ?>
        <div class="section">
            <h4>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                Dados Profissionais
            </h4>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Profissão</span>
                    <span class="value"><?= safe($dados["profissao"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Categoria</span>
                    <span class="value"><?= safe($dados["categoria"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Data de Admissão</span>
                    <span class="value"><?= $dados["data_admissao"] ? date("d/m/Y", strtotime($dados["data_admissao"])) : 'Não especificado' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Tipo de Contrato</span>
                    <span class="value"><?= safe($dados["tipo_contrato"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Regime</span>
                    <span class="value"><?= safe($dados["regime"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Horas/Semana</span>
                    <span class="value"><?= safe($dados["horas_semana"]) ?></span>
                </div>
            </div>
        </div>

        <div class="section">
            <h4>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                Dados Financeiros
            </h4>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Salário Base</span>
                    <span class="value"><?= $dados["salario_base"] ? safe($dados["salario_base"]) . ' €' : 'Não especificado' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Sub. Alimentação</span>
                    <span class="value"><?= $dados["subsidio_alimentacao"] ? safe($dados["subsidio_alimentacao"]) . ' €' : 'Não especificado' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">NIB</span>
                    <span class="value"><?= safe($dados["nib"]) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">NIF</span>
                    <span class="value"><?= safe($dados["nif"]) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="section">
            <h4>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                Contactos de Emergência
            </h4>
            <?php if (empty($contactos_emergencia)): ?>
                <p class="no-data">Nenhum contacto de emergência registado.</p>
            <?php else: ?>
                <div class="contactos-grid">
                    <?php foreach ($contactos_emergencia as $contacto): ?>
                        <div class="contacto-card">
                            <div class="contacto-info">
                                <span class="contacto-nome"><?= safe($contacto['nome']) ?></span>
                                <span class="contacto-parentesco"><?= safe($contacto['parentesco']) ?></span>
                            </div>
                            <span class="contacto-telefone"><?= safe($contacto['telefone']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.ficha-completa {
    max-width: 100%;
}

.ficha-header {
    text-align: center;
    padding: 1.5rem 0;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
}

.operador-avatar-large {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin: 0 auto 1rem;
}

.ficha-header h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
}

.ficha-header p {
    color: var(--text-light);
    font-weight: 500;
}

.section {
    margin-bottom: 2rem;
}

.section h4 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f1f5f9;
}

.section h4 svg {
    color: var(--light-blue);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.label {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.value {
    font-weight: 600;
    color: var(--navy-blue);
    word-break: break-word;
}

.no-data {
    color: var(--text-light);
    font-style: italic;
    text-align: center;
    padding: 2rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.contactos-grid {
    display: grid;
    gap: 0.75rem;
}

.contacto-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.contacto-info {
    display: flex;
    flex-direction: column;
}

.contacto-nome {
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 0.125rem;
}

.contacto-parentesco {
    font-size: 0.875rem;
    color: var(--text-light);
}

.contacto-telefone {
    font-weight: 500;
    color: var(--light-blue);
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }

    .contacto-card {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
}
</style>
