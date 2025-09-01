<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

// Verificar se é para visualizar ficha de outro colaborador ou própria ficha
$target_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION["user"]["id"];

try {
    $conn = db_connect();

    // Buscar dados do colaborador alvo
    $stmt = $conn->prepare("SELECT u.name, u.email FROM user u WHERE u.id = ?");
    $stmt->execute([$target_user_id]);
    $user_info = $stmt->fetch();

    if (!$user_info) {
        echo "<p>Colaborador não encontrado.</p>";
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM colaborador_dados WHERE user_id = ?");
    $stmt->execute([$target_user_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar contactos de emergência
    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$target_user_id]);
    $contactos_emergencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$dados) {
        // Se não existe ficha, criar dados vazios para exibição
        $dados = [
            'email' => $user_info['email'],
            'telefone' => '',
            'morada' => '',
            'nib' => '',
            'profissao' => '',
            'categoria' => ''
        ];
    }
} catch (Exception $e) {
    echo "<p>Erro ao carregar dados: " . $e->getMessage() . "</p>";
    exit;
}

$is_own_profile = ($target_user_id == $_SESSION["user"]["id"]);

function safe($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="ficha-page">
    <!-- Modern Header -->
    <div class="ficha-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="header-text">
                <h2><?= $is_own_profile ? 'Minha Ficha de Colaborador' : 'Ficha de Colaborador' ?></h2>
                <p><strong>Colaborador:</strong> <?= safe($user_info["name"]) ?> (<?= safe($user_info["email"]) ?>)</p>
                <?php if ($is_own_profile): ?>
                    <p class="note">Visualize os seus dados pessoais. Para alterações, clique no botão abaixo.</p>
                <?php else: ?>
                    <p class="note">Como Admin RH, pode visualizar e editar os dados deste colaborador.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-primary" id="abrirEdicaoCompleta" style="background:linear-gradient(135deg,#10b981,#059669);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edição Completa
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ficha-content">
        <!-- Personal Info Section -->
        <div class="ficha-section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h3>Dados Pessoais</h3>
            </div>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Nome</div>
                    <div class="info-value"><?= safe($dados["nome"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= safe($dados["email"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Telefone</div>
                    <div class="info-value"><?= safe($dados["telefone"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Morada</div>
                    <div class="info-value"><?= safe($dados["morada"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Código Postal</div>
                    <div class="info-value"><?= safe($dados["codigo_postal"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Freguesia</div>
                    <div class="info-value"><?= safe($dados["freguesia"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Concelho</div>
                    <div class="info-value"><?= safe($dados["concelho"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Distrito</div>
                    <div class="info-value"><?= safe($dados["distrito"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Naturalidade</div>
                    <div class="info-value"><?= safe($dados["naturalidade"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Habilitações</div>
                    <div class="info-value"><?= safe($dados["habilitacoes"]) ?></div>
                </div>
            </div>
        </div>

        <!-- Family & ID Info Section -->
        <div class="ficha-section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3>Informação Familiar e Identificação</h3>
            </div>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Pai</div>
                    <div class="info-value"><?= safe($dados["pai"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Mãe</div>
                    <div class="info-value"><?= safe($dados["mae"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Estado Civil</div>
                    <div class="info-value"><?= safe($dados["estado_civil"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Data Nascimento</div>
                    <div class="info-value"><?= safe($dados["data_nascimento"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">País</div>
                    <div class="info-value"><?= safe($dados["pais"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Tipo Documento</div>
                    <div class="info-value"><?= safe($dados["tipo_documento"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Número Documento</div>
                    <div class="info-value"><?= safe($dados["numero_documento"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Emitido em</div>
                    <div class="info-value"><?= safe($dados["emitido_em"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Arquivo</div>
                    <div class="info-value"><?= safe($dados["arquivo"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Validade Documento</div>
                    <div class="info-value"><?= safe($dados["validade_documento"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">NIF</div>
                    <div class="info-value"><?= safe($dados["nif"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Número Segurança Social</div>
                    <div class="info-value"><?= safe($dados["numero_seg_social"]) ?></div>
                </div>
            </div>
        </div>

        <!-- Tax Information Section -->
        <div class="ficha-section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <h3>Dados Fiscais</h3>
            </div>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Descontos Fiscais</div>
                    <div class="info-value"><?= safe($dados["descontos_fiscais"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Repartição Finanças</div>
                    <div class="info-value"><?= safe($dados["reparticao_financas"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Região</div>
                    <div class="info-value"><?= safe($dados["regiao"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Estado Fiscal</div>
                    <div class="info-value"><?= safe($dados["estado_fiscal"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Deficiência</div>
                    <div class="info-value"><?= safe($dados["deficiencia"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Cônjuge Deficiente</div>
                    <div class="info-value"><?= isset($dados["conjugue_deficiente"]) && $dados["conjugue_deficiente"] ? 'Sim' : 'Não' ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Nº Dependentes</div>
                    <div class="info-value"><?= safe($dados["num_dependentes"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Nº Dependentes Deficientes</div>
                    <div class="info-value"><?= safe($dados["num_dependentes_deficientes"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Pensionista</div>
                    <div class="info-value"><?= isset($dados["pensionista"]) && $dados["pensionista"] ? 'Sim' : 'Não' ?></div>
                </div>
            </div>
        </div>

        <!-- Contract Information Section -->
        <div class="ficha-section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <h3>Dados Contratuais</h3>
            </div>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Data Admissão</div>
                    <div class="info-value"><?= safe($dados["data_admissao"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Tipo de Contrato</div>
                    <div class="info-value"><?= safe($dados["tipo_contrato"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Profissão</div>
                    <div class="info-value"><?= safe($dados["profissao"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Categoria</div>
                    <div class="info-value"><?= safe($dados["categoria"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Regime</div>
                    <div class="info-value"><?= safe($dados["regime"]) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Horas/Semana</div>
                    <div class="info-value"><?= safe($dados["horas_semana"]) ?></div>
                </div>
                <div class="info-card highlight">
                    <div class="info-label">Salário Base</div>
                    <div class="info-value"><?= safe($dados["salario_base"]) ?> €</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Sub. Alimentação</div>
                    <div class="info-value"><?= safe($dados["subsidio_alimentacao"]) ?> €</div>
                </div>
                <div class="info-card">
                    <div class="info-label">NIB</div>
                    <div class="info-value"><?= safe($dados["nib"]) ?></div>
                </div>
                <div class="info-card highlight">
                    <div class="info-label">Ordenado Líquido</div>
                    <div class="info-value"><?= safe($dados["ordenado_liquido"]) ?> €</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Validação Empresa</div>
                    <div class="info-value"><?= safe($dados["validacao_empresa"]) ?></div>
                </div>
            </div>
        </div>

        <!-- Emergency Contacts Section -->
        <div class="ficha-section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <h3>Contactos de Emergência</h3>
            </div>
            <?php if (empty($contactos_emergencia)): ?>
                <div class="empty-contacts">
                    <div class="empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <p>Sem contactos de emergência registados.</p>
                </div>
            <?php else: ?>
                <div class="contacts-grid">
                    <?php foreach ($contactos_emergencia as $c): ?>
                        <div class="contact-card">
                            <div class="contact-avatar">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="contact-info">
                                <div class="contact-name"><?= safe($c['nome']) ?></div>
                                <div class="contact-relation"><?= safe($c['parentesco']) ?></div>
                                <div class="contact-phone">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    <?= safe($c['telefone']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* Modern Ficha Page Styles */
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

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(62, 132, 242, 0.4);
    }

    .ficha-content {
        padding: 2rem 2.5rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .ficha-section {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        animation: slideIn 0.5s ease-out;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .section-icon {
        width: 40px;
        height: 40px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .section-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--navy-blue);
        margin: 0;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        padding: 2rem;
    }

    .info-card {
        background: #f8fafc;
        padding: 1.25rem;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: var(--light-blue);
    }

    .info-card.highlight {
        background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
        border-color: #d1fae5;
    }

    .info-card.highlight:hover {
        border-color: #10b981;
    }

    .info-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--navy-blue);
        word-break: break-word;
    }

    .info-card.highlight .info-value {
        color: #059669;
        font-size: 1.125rem;
    }

    /* Emergency Contacts */
    .empty-contacts {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 2rem;
        text-align: center;
    }

    .empty-icon {
        color: var(--text-light);
        margin-bottom: 1rem;
    }

    .empty-contacts p {
        color: var(--text-light);
        font-size: 1rem;
    }

    .contacts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
    }

    .contact-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .contact-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        border-color: var(--light-blue);
    }

    .contact-avatar {
        width: 48px;
        height: 48px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .contact-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--navy-blue);
        margin-bottom: 0.25rem;
    }

    .contact-relation {
        font-size: 0.875rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }

    .contact-phone {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-dark);
        font-weight: 500;
    }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ficha-page {
        animation: fadeInUp 0.4s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .ficha-header {
            padding: 1.5rem 2rem;
        }

        .ficha-content {
            padding: 1.5rem 2rem;
        }

        .info-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .ficha-header {
            flex-direction: column;
            gap: 1rem;
            padding: 1.5rem;
            text-align: center;
        }

        .ficha-content {
            padding: 1rem 1.5rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
            padding: 1.5rem;
        }

        .contacts-grid {
            grid-template-columns: 1fr;
            padding: 1.5rem;
        }

        .section-header {
            padding: 1rem 1.5rem;
        }
    }
</style>

<!-- Modal Edição Completa -->
<div id="modalEdicaoCompleta" class="modal-overlay" style="display:none;align-items:center;justify-content:center;">
    <div class="modal-container" style="background:#fff;width:90%;max-width:1100px;max-height:90vh;overflow:auto;border-radius:16px;padding:2rem;position:relative;">
        <button type="button" id="fecharModalEdicao" style="position:absolute;top:12px;right:12px;background:#f1f5f9;border:none;width:36px;height:36px;border-radius:8px;font-size:20px;cursor:pointer;">×</button>
        <h3 style="margin-top:0;">Edição Completa da Ficha</h3>
        <form id="formEdicaoCompleta" action="../../api/pedidos/arh_guardar_ficha.php" method="POST" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;">
            <input type="hidden" name="user_id" value="<?= $target_user_id ?>">
            <?php
            $dateFields = ['data_nascimento','emitido_em','validade_documento','data_admissao'];
            $moneyFields = ['salario_base','subsidio_alimentacao','ordenado_liquido'];
            $enumOptions = [
                // Alinhado com ENUMs MySQL
                'estado_civil' => ['Solteiro','Casado','Viuvo','Divorciado','Uniao de Facto','Separado Judicialmente'],
                'tipo_documento' => ['CC','Titulo Residencia','Passaporte'],
                // Campos não ENUM mas mantemos lista controlada
                'tipo_contrato' => ['Sem termo','Termo certo','Termo incerto','Estágio','Prestação serviços'],
                'regime' => ['Tempo Inteiro','Tempo Parcial'],
                'estado_fiscal' => ['Nao Casado','Casado 1 Titular','Casado 2 Titulares'],
                'deficiencia' => ['Nao Deficiente','Deficiente','Defic. F.Armadas'],
                'conjugue_deficiente' => ['Sim','Não'],
                'pensionista' => ['Sim','Não']
            ];
            foreach($dados as $campo=>$valor): if($campo==='user_id') continue; ?>
                <label style="display:flex;flex-direction:column;font-size:.75rem;font-weight:600;color:#475569;gap:4px;">
                    <span><?= ucfirst(str_replace('_',' ', $campo)) ?></span>
                    <?php if(isset($enumOptions[$campo])): ?>
                        <select name="<?= $campo ?>" style="padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:8px;">
                            <?php foreach($enumOptions[$campo] as $opt): $sel = ($valor==$opt)?'selected':''; echo "<option value=\"$opt\" $sel>$opt</option>"; endforeach; ?>
                        </select>
                    <?php elseif(in_array($campo,$dateFields)): ?>
                        <input type="date" name="<?= $campo ?>" value="<?= htmlspecialchars($valor??'') ?>" style="padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:8px;" />
                    <?php elseif(in_array($campo,$moneyFields)): ?>
                        <input type="text" inputmode="decimal" name="<?= $campo ?>" value="<?= htmlspecialchars($valor??'') ?>" style="padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:8px;" />
                    <?php else: ?>
                        <input name="<?= $campo ?>" value="<?= htmlspecialchars($valor??'') ?>" style="padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:8px;" />
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
            <fieldset style="grid-column:1/-1;border:1px solid #e2e8f0;padding:1rem 1.25rem;border-radius:12px;">
                <legend style="padding:0 .5rem;font-weight:600;color:#334155;">Contacto de Emergência</legend>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
                    <label style="display:flex;flex-direction:column;font-size:.75rem;font-weight:600;color:#475569;gap:4px;">
                        <span>Nome</span>
                        <input name="emergencia_nome" value="<?= htmlspecialchars($contactos_emergencia[0]['nome'] ?? '') ?>" style="padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:8px;" />
                    </label>
                    <label style="display:flex;flex-direction:column;font-size:.75rem;font-weight:600;color:#475569;gap:4px;">
                        <span>Parentesco</span>
                        <input name="emergencia_parentesco" value="<?= htmlspecialchars($contactos_emergencia[0]['parentesco'] ?? '') ?>" style="padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:8px;" />
                    </label>
                    <label style="display:flex;flex-direction:column;font-size:.75rem;font-weight:600;color:#475569;gap:4px;">
                        <span>Telefone</span>
                        <input name="emergencia_telefone" value="<?= htmlspecialchars($contactos_emergencia[0]['telefone'] ?? '') ?>" style="padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:8px;" />
                    </label>
                </div>
            </fieldset>
            <div style="grid-column:1/-1;display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" id="cancelarEdicaoCompleta" style="padding:.75rem 1.25rem;border:1px solid #e2e8f0;background:#f1f5f9;border-radius:10px;font-weight:500;cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-primary" style="padding:.75rem 1.5rem;border:none;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.5rem;">
                    <span class="label-text">Guardar</span>
                    <span class="loading-spinner" style="display:none;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes spin{to{transform:rotate(360deg)}}
    .modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);z-index:4000;padding:1rem;}
</style>
<script>
    (function initEdicaoCompleta(){
        const moneyFields = ['salario_base','subsidio_alimentacao','ordenado_liquido'];
        function sanitizarValor(v){
            if(v==null) return '';
            v = v.toString().trim();
            if(v==='') return '';
            // remover espaços
            v = v.replace(/\s+/g,'')
            // trocar vírgula decimal por ponto, remover separadores de milhar comuns
            // Ex: 1.234,56 -> 1234.56 ; 1 234,56 -> 1234.56 ; 1,234.56 (en) -> 1234.56
            if(/,\d{2}$/.test(v)){ // formato PT
                v = v.replace(/\./g,'').replace(/,/g,'.');
            } else if(/\.\d{2}$/.test(v) && v.indexOf(',')>-1){ // mistura
                v = v.replace(/,/g,'')
            } else {
                // remover separadores de milhar vírgula em formato EN
                const parts = v.split('.');
                if(parts.length>2){
                    const last = parts.pop();
                    if(/^\d{2}$/.test(last)){
                        v = parts.join('') + '.' + last;
                    } else {
                        v = parts.join('') + last;
                    }
                }
                v = v.replace(/,/g,'');
            }
            // agora só aceitar padrão número decimal
            const num = parseFloat(v);
            if(isNaN(num)) return '';
            // manter 2 casas e devolver com vírgula decimal para compatibilidade com backend
            return num.toFixed(2).replace('.', ',');
        }
        const btnOpen = document.getElementById('abrirEdicaoCompleta');
        const modal = document.getElementById('modalEdicaoCompleta');
        if(!modal) return;
        const closeBtns = [document.getElementById('fecharModalEdicao'), document.getElementById('cancelarEdicaoCompleta')];
        if(btnOpen && !btnOpen.__edFullBound){
            btnOpen.addEventListener('click', ()=>{ modal.style.display='flex'; document.body.style.overflow='hidden'; });
            btnOpen.__edFullBound=true;
        }
        closeBtns.forEach(b=> b && !b.__edFullBound && (b.addEventListener('click', ()=>{ modal.style.display='none'; document.body.style.overflow=''; }), b.__edFullBound=true));
        const form = document.getElementById('formEdicaoCompleta');
        if(form && !form.__edFullBound){
            // aplicar blur/input listeners
            moneyFields.forEach(name=>{
                const input = form.querySelector(`[name="${name}"]`);
                if(!input) return;
                input.addEventListener('blur', ()=>{ const v = sanitizarValor(input.value); if(v!=='') input.value = v; });
            });
            form.addEventListener('submit', function(e){
                e.preventDefault();
                // sanitizar antes de enviar
                moneyFields.forEach(name=>{ const input = form.querySelector(`[name="${name}"]`); if(input){ const v = sanitizarValor(input.value); input.value = v; }});
                const fd = new FormData(form);
                const btn = form.querySelector('button[type="submit"]');
                const label = btn.querySelector('.label-text');
                const spinner = btn.querySelector('.loading-spinner');
                label.style.display='none'; spinner.style.display='inline-block'; btn.disabled=true;
                fetch(form.action,{method:'POST',body:fd,credentials:'same-origin',cache:'no-store'})
                    .then(r=>r.json())
                    .then(data=>{
                        if(typeof showToast==='function'){ showToast(data.success? '✅ Ficha guardada.' : '❌ '+(data.message||'Erro ao guardar'), data.success? 'success':'error'); }
                        if(data.success){ setTimeout(()=>{ modal.style.display='none'; document.body.style.overflow=''; location.reload(); },900); }
                    })
                    .catch(()=>{ if(typeof showToast==='function') showToast('❌ Erro de rede.','error'); })
                    .finally(()=>{ label.style.display=''; spinner.style.display='none'; btn.disabled=false; });
            });
            form.__edFullBound=true;
        }
    })();
</script>
