<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "opera") {
    echo "<p>Acesso negado.</p>";
    exit;
}

$user_id = $_SESSION["user"]["id"];

try {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT * FROM colaborador_dados WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $dados = $stmt->fetch();
    // Buscar contacto de emergência (caso exista)
    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $contacto_emergencia = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<p>Erro ao carregar dados.</p>";
    exit;
}
?>

<div class="edit-ficha-page">
    <!-- Modern Header -->
    <div class="edit-header">
        <div class="header-left">
            <div class="page-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </div>
            <div class="header-text">
                <h2>Editar Ficha de Colaborador</h2>
                <p>Atualize os seus dados pessoais. Após submeter, será necessária aprovação do superior.</p>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-wrapper">
        <form action="../../api/pedidos/o_ficha_colaborador.php" method="POST" class="edit-form">
            <!-- Contact Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <h3>Contacto</h3>
                </div>
                <div class="form-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                Email
                                <span class="required">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($dados["email"] ?? "") ?>" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="contacto_telefone">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                Telefone
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="contacto_telefone" id="contacto_telefone" value="<?= htmlspecialchars($dados["telefone"] ?? "") ?>" required class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9,22 9,12 15,12 15,22"></polyline>
                        </svg>
                    </div>
                    <h3>Morada</h3>
                </div>
                <div class="form-content">
                    <div class="form-group">
                        <label for="morada">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            Morada
                        </label>
                        <input type="text" name="morada" id="morada" value="<?= htmlspecialchars($dados["morada"] ?? "") ?>" class="form-input">
                    </div>
                </div>
            </div>

            <!-- Banking Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    </div>
                    <h3>Dados Bancários</h3>
                </div>
                <div class="form-content">
                    <div class="form-group">
                        <label for="nib">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                <line x1="1" y1="10" x2="23" y2="10"></line>
                            </svg>
                            NIB <span class="optional-text" style="font-weight:400;color:var(--text-light);">(opcional)</span>
                        </label>
                        <input type="text" name="nib" id="nib" maxlength="21" pattern="\d{21}" placeholder="Apenas números (21 dígitos)" value="<?= htmlspecialchars($dados["nib"] ?? "") ?>" class="form-input">
                        <div class="field-help">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9,12l2,2 4,-4"></path>
                            </svg>
                            Formato: 21 dígitos numéricos
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <h3>Contacto de Emergência</h3>
                </div>
                <div class="form-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergencia_nome">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Nome
                            </label>
                            <input type="text" name="emergencia_nome" id="emergencia_nome" value="<?= htmlspecialchars($contacto_emergencia["nome"] ?? "") ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="emergencia_parentesco">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                Parentesco
                            </label>
                            <input type="text" name="emergencia_parentesco" id="emergencia_parentesco" value="<?= htmlspecialchars($contacto_emergencia["parentesco"] ?? "") ?>" class="form-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="emergencia_telefone">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            Telefone
                        </label>
                        <input type="text" name="emergencia_telefone" id="emergencia_telefone" value="<?= htmlspecialchars($contacto_emergencia["telefone"] ?? "") ?>" class="form-input">
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <span class="btn-content">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20,6 9,17 4,12"></polyline>
                        </svg>
                        Submeter
                    </span>
                    <span class="btn-loading" style="display: none;">
                        <div class="spinner"></div>
                        A submeter...
                    </span>
                </button>
                <div class="form-notice">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    As alterações serão enviadas para aprovação do seu superior hierárquico
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Modern Edit Ficha Page Styles */
.edit-ficha-page {
    padding: 0;
    background: #f8fafc;
    min-height: 100vh;
}

.edit-header {
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

.form-wrapper {
    padding: 2rem 2.5rem;
    max-width: 800px;
    margin: 0 auto;
}

.edit-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-section {
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

.form-content {
    padding: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.required {
    color: #ef4444;
    font-weight: 700;
}

.form-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    font-family: var(--font-family);
    background: #f8fafc;
    transition: all 0.2s ease;
    color: var(--text-dark);
}

.form-input:focus {
    outline: none;
    border-color: var(--light-blue);
    background: white;
    box-shadow: 0 0 0 3px rgba(62, 132, 242, 0.1);
    transform: translateY(-1px);
}

.form-input::placeholder {
    color: var(--text-light);
}

.field-help {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: var(--text-light);
}

.form-actions {
    background: white;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    padding: 2rem;
    text-align: center;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(62, 132, 242, 0.3);
    margin-bottom: 1.5rem;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(62, 132, 242, 0.4);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.form-notice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: var(--text-light);
    font-size: 0.9rem;
    background: #f8fafc;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
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

.edit-ficha-page {
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
@media (max-width: 768px) {
    .edit-header {
        padding: 1.5rem;
        text-align: center;
    }

    .form-wrapper {
        padding: 1rem 1.5rem;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .form-content {
        padding: 1.5rem;
    }

    .section-header {
        padding: 1rem 1.5rem;
    }

    .form-actions {
        padding: 1.5rem;
    }
}

/* Form Validation States */
.form-input:invalid:not(:focus):not(:placeholder-shown) {
    border-color: #ef4444;
    background: #fef2f2;
}

.form-input:valid:not(:focus):not(:placeholder-shown) {
    border-color: #10b981;
}

/* Focus States for Accessibility */
.btn-submit:focus-visible {
    outline: 2px solid var(--light-blue);
    outline-offset: 2px;
}

.form-input:focus-visible {
    outline: 2px solid var(--light-blue);
    outline-offset: 2px;
}
</style>
