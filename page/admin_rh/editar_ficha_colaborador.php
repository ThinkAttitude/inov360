<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

// Verificar se é para editar ficha de outro colaborador ou própria ficha
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
    $dados = $stmt->fetch();

    // Buscar contacto de emergência (caso exista)
    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$target_user_id]);
    $contacto_emergencia = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<p>Erro ao carregar dados.</p>";
    exit;
}

$is_editing_self = ($target_user_id == $_SESSION["user"]["id"]);
?>

<div class="editar-ficha-page">
    <!-- Modern Header -->
    <div class="editar-ficha-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </div>
            <div class="header-text">
                <h2>Editar Ficha de Colaborador</h2>
                <p><strong>Colaborador:</strong> <?= htmlspecialchars($user_info["name"]) ?> (<?= htmlspecialchars($user_info["email"]) ?>)</p>
                <?php if ($is_editing_self): ?>
                    <p class="note">Atualize os seus dados pessoais. Após submeter, será necessária aprovação do superior.</p>
                <?php else: ?>
                    <p class="note">Como Admin RH, pode editar diretamente os dados deste colaborador.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="editar-ficha-content">
        <div class="form-container">
            <form id="editarFichaForm" action="../../api/pedidos/arh_ficha_colaborador.php" method="POST">
                <input type="hidden" name="target_user_id" value="<?= $target_user_id ?>">

                <!-- Dados Pessoais Section -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <h3>Dados Pessoais</h3>
                    </div>

                    <div class="form-grid">
                        <div class="input-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($dados["email"] ?? "") ?>" required>
                        </div>

                        <div class="input-group">
                            <label for="contacto_telefone">Telefone *</label>
                            <input type="text" name="contacto_telefone" id="contacto_telefone" value="<?= htmlspecialchars($dados["telefone"] ?? "") ?>" required>
                        </div>

                        <div class="input-group full-width">
                            <label for="morada">Morada</label>
                            <input type="text" name="morada" id="morada" value="<?= htmlspecialchars($dados["morada"] ?? "") ?>">
                        </div>

                        <div class="input-group">
                            <label for="nib">NIB (opcional)</label>
                            <input type="text" name="nib" id="nib" maxlength="21" pattern="\d{21}" placeholder="Apenas números (21 dígitos)" value="<?= htmlspecialchars($dados["nib"] ?? "") ?>">
                        </div>
                    </div>
                </div>

                <!-- Contacto de Emergência Section -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </div>
                        <h3>Contacto de Emergência</h3>
                    </div>

                    <div class="form-grid">
                        <div class="input-group">
                            <label for="emergencia_nome">Nome</label>
                            <input type="text" name="emergencia_nome" id="emergencia_nome" value="<?= htmlspecialchars($contacto_emergencia["nome"] ?? "") ?>">
                        </div>

                        <div class="input-group">
                            <label for="emergencia_parentesco">Parentesco</label>
                            <input type="text" name="emergencia_parentesco" id="emergencia_parentesco" value="<?= htmlspecialchars($contacto_emergencia["parentesco"] ?? "") ?>">
                        </div>

                        <div class="input-group">
                            <label for="emergencia_telefone">Telefone</label>
                            <input type="text" name="emergencia_telefone" id="emergencia_telefone" value="<?= htmlspecialchars($contacto_emergencia["telefone"] ?? "") ?>">
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20,6 9,17 4,12"></polyline>
                        </svg>
                        <?= $is_editing_self ? 'Submeter' : 'Guardar Alterações' ?>
                    </button>
                    <button type="button" class="btn-secondary" onclick="history.back()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H6m6-6l-6 6 6 6"></path>
                        </svg>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="message" class="message" style="display: none;"></div>

<style>
    /* Modern Editar Ficha Page Styles */
    .editar-ficha-page {
        padding: 0;
        background: #f8fafc;
        min-height: 100vh;
    }

    .editar-ficha-header {
        background: white;
        padding: 2rem 2.5rem;
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
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
    }

    .header-text h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
        letter-spacing: -0.5px;
    }

    .header-text p {
        color: #64748b;
        font-size: 1rem;
        margin: 0.25rem 0;
    }

    .header-text .note {
        color: #475569;
        font-size: 0.875rem;
        font-style: italic;
    }

    .editar-ficha-content {
        padding: 2.5rem;
    }

    .form-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .form-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .section-icon {
        width: 40px;
        height: 40px;
        background: #f1f5f9;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3b82f6;
    }

    .section-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        align-items: start;
    }

    .input-group.full-width {
        grid-column: 1 / -1;
    }

    .input-group label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .input-group input,
    .input-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
    }

    .input-group input:focus,
    .input-group textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .btn-primary,
    .btn-secondary {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        cursor: pointer;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35);
    }

    .btn-secondary {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .message {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .message.success {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .message.error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    @media (max-width: 768px) {
        .editar-ficha-header {
            padding: 1.5rem;
        }

        .header-left {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .editar-ficha-content {
            padding: 1.5rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
