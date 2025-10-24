<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin") {
    echo "<p>Acesso negado.</p>";
    exit;
}

$user_id = $_SESSION["user"]["id"];

try {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT * FROM colaborador_dados WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar contactos de emergência
    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $contactos_emergencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$dados) {
        echo "<p>Nenhuma ficha de colaborador encontrada.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p>Erro ao carregar dados: " . $e->getMessage() . "</p>";
    exit;
}

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
                <h2>Ficha de Colaborador</h2>
                <p>Visualize os seus dados pessoais. Para alterações, clique no botão abaixo.</p>
            </div>
        </div>
        <button id="editar_ficha_colaborador" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Editar Ficha
        </button>
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

<script src="../../js/legacy/dashboard_opera.js"></script>
