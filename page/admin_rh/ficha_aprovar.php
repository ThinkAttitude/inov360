<?php
require_once "../../api/includes/db.php";
session_start();

// Apenas administrador
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

if (!isset($_GET["id"])) {
    echo "<p>ID do pedido não fornecido.</p>";
    exit;
}

$pedido_id = intval($_GET["id"]);

try {
    $conn = db_connect();

    // Buscar pedido pendente
    $stmt = $conn->prepare("
        SELECT ce.*, u.name AS nome_utilizador
        FROM colaborador_edicoes ce
        JOIN user u ON ce.user_id = u.id
        WHERE ce.id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        echo "<p>Pedido não encontrado.</p>";
        exit;
    }

    // Buscar contacto de emergência proposto
    $stmt = $conn->prepare("SELECT * FROM contactos_emergencia_edicoes WHERE user_id = ? AND estado = 'pendente' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$pedido["user_id"]]);
    $emergencia = $stmt->fetch();

    // Buscar ficha atual do colaborador
    $stmtAtual = $conn->prepare("SELECT * FROM colaborador_dados WHERE user_id = ?");
    $stmtAtual->execute([$pedido["user_id"]]);
    $atual = $stmtAtual->fetch();

    // Buscar contacto de emergência atual
    $stmtAtualCE = $conn->prepare("SELECT * FROM contactos_emergencia WHERE user_id = ? LIMIT 1");
    $stmtAtualCE->execute([$pedido["user_id"]]);
    $emergencia_atual = $stmtAtualCE->fetch();

    // Função para verificar se um campo foi alterado
    function isFieldChanged($current, $proposed) {
        return trim($current ?? '') !== trim($proposed ?? '');
    }

    // Verificar quais campos foram alterados
    $emailChanged = isFieldChanged($atual["email"] ?? '', $pedido["email"] ?? '');
    $telefoneChanged = isFieldChanged($atual["telefone"] ?? '', $pedido["telefone"] ?? '');
    $moradaChanged = isFieldChanged($atual["morada"] ?? '', $pedido["morada"] ?? '');
    $nibChanged = isFieldChanged($atual["nib"] ?? '', $pedido["nib"] ?? '');

    // Verificar mudanças no contacto de emergência
    $emergenciaNomeChanged = false;
    $emergenciaParentescoChanged = false;
    $emergenciaTelefoneChanged = false;

    if ($emergencia && $emergencia_atual) {
        $emergenciaNomeChanged = isFieldChanged($emergencia_atual["nome"] ?? '', $emergencia["nome"] ?? '');
        $emergenciaParentescoChanged = isFieldChanged($emergencia_atual["parentesco"] ?? '', $emergencia["parentesco"] ?? '');
        $emergenciaTelefoneChanged = isFieldChanged($emergencia_atual["telefone"] ?? '', $emergencia["telefone"] ?? '');
    } elseif ($emergencia && !$emergencia_atual) {
        // Novo contacto de emergência
        $emergenciaNomeChanged = true;
        $emergenciaParentescoChanged = true;
        $emergenciaTelefoneChanged = true;
    }

} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>

<link rel="stylesheet" href="../../css/fichas_colaboradores.css">

<div class="page-header">
    <h2>Análise de Pedido de Edição</h2>
    <p><strong>Colaborador:</strong> <?= htmlspecialchars($pedido["nome_utilizador"]) ?></p>
</div>

<!-- Legenda das Mudanças -->
<div class="changes-legend">
    <h4 style="color: var(--navy-blue); margin-bottom: 1rem; font-size: 1rem; font-weight: 600;">Legenda das Alterações:</h4>
    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <div class="legend-item">
            <div class="legend-indicator current"></div>
            <span class="legend-text">Valor atual (será substituído)</span>
        </div>
        <div class="legend-item">
            <div class="legend-indicator proposed"></div>
            <span class="legend-text">Valor proposto (novo)</span>
        </div>
    </div>
</div>

<div class="comparison-table">
    <!-- Ficha Atual -->
    <div class="comparison-section current">
        <div class="comparison-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
            </svg>
            Ficha Atual
        </div>
        <div class="comparison-content">
            <div class="comparison-field <?= $emailChanged ? 'field-changed-current' : '' ?>">
                <span class="field-label">Email:</span>
                <span class="field-value"><?= htmlspecialchars($atual["email"] ?? '—') ?></span>
                <?php if ($emailChanged): ?>
                    <span class="change-indicator">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>
            <div class="comparison-field <?= $telefoneChanged ? 'field-changed-current' : '' ?>">
                <span class="field-label">Telefone:</span>
                <span class="field-value"><?= htmlspecialchars($atual["telefone"] ?? '—') ?></span>
                <?php if ($telefoneChanged): ?>
                    <span class="change-indicator">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>
            <div class="comparison-field <?= $moradaChanged ? 'field-changed-current' : '' ?>">
                <span class="field-label">Morada:</span>
                <span class="field-value"><?= htmlspecialchars($atual["morada"] ?? '—') ?></span>
                <?php if ($moradaChanged): ?>
                    <span class="change-indicator">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>
            <div class="comparison-field <?= $nibChanged ? 'field-changed-current' : '' ?>">
                <span class="field-label">NIB:</span>
                <span class="field-value"><?= htmlspecialchars($atual["nib"] ?? '—') ?></span>
                <?php if ($nibChanged): ?>
                    <span class="change-indicator">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($emergencia_atual): ?>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-gray);">
                    <h4 style="color: var(--navy-blue); margin-bottom: 1rem; font-size: 1.125rem;">Contacto de Emergência Atual</h4>
                    <div class="comparison-field <?= $emergenciaNomeChanged ? 'field-changed-current' : '' ?>">
                        <span class="field-label">Nome:</span>
                        <span class="field-value"><?= htmlspecialchars($emergencia_atual["nome"] ?? '—') ?></span>
                        <?php if ($emergenciaNomeChanged): ?>
                            <span class="change-indicator">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="comparison-field <?= $emergenciaParentescoChanged ? 'field-changed-current' : '' ?>">
                        <span class="field-label">Parentesco:</span>
                        <span class="field-value"><?= htmlspecialchars($emergencia_atual["parentesco"] ?? '—') ?></span>
                        <?php if ($emergenciaParentescoChanged): ?>
                            <span class="change-indicator">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="comparison-field <?= $emergenciaTelefoneChanged ? 'field-changed-current' : '' ?>">
                        <span class="field-label">Telefone:</span>
                        <span class="field-value"><?= htmlspecialchars($emergencia_atual["telefone"] ?? '—') ?></span>
                        <?php if ($emergenciaTelefoneChanged): ?>
                            <span class="change-indicator">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ficha Proposta -->
    <div class="comparison-section proposed">
        <div class="comparison-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            Ficha Proposta
        </div>
        <div class="comparison-content">
            <div class="comparison-field <?= $emailChanged ? 'field-changed-proposed' : '' ?>">
                <span class="field-label">Email:</span>
                <span class="field-value"><?= htmlspecialchars($pedido["email"] ?? '—') ?></span>
                <?php if ($emailChanged): ?>
                    <span class="change-indicator change-new">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12l2 2 4-4"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>
            <div class="comparison-field <?= $telefoneChanged ? 'field-changed-proposed' : '' ?>">
                <span class="field-label">Telefone:</span>
                <span class="field-value"><?= htmlspecialchars($pedido["telefone"] ?? '—') ?></span>
                <?php if ($telefoneChanged): ?>
                    <span class="change-indicator change-new">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12l2 2 4-4"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>
            <div class="comparison-field <?= $moradaChanged ? 'field-changed-proposed' : '' ?>">
                <span class="field-label">Morada:</span>
                <span class="field-value"><?= htmlspecialchars($pedido["morada"] ?? '—') ?></span>
                <?php if ($moradaChanged): ?>
                    <span class="change-indicator change-new">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12l2 2 4-4"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>
            <div class="comparison-field <?= $nibChanged ? 'field-changed-proposed' : '' ?>">
                <span class="field-label">NIB:</span>
                <span class="field-value"><?= htmlspecialchars($pedido["nib"] ?? '—') ?></span>
                <?php if ($nibChanged): ?>
                    <span class="change-indicator change-new">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12l2 2 4-4"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($emergencia): ?>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-gray);">
                    <h4 style="color: var(--navy-blue); margin-bottom: 1rem; font-size: 1.125rem;">Contacto de Emergência Proposto</h4>
                    <div class="comparison-field <?= $emergenciaNomeChanged ? 'field-changed-proposed' : '' ?>">
                        <span class="field-label">Nome:</span>
                        <span class="field-value"><?= htmlspecialchars($emergencia["nome"] ?? '—') ?></span>
                        <?php if ($emergenciaNomeChanged): ?>
                            <span class="change-indicator change-new">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M8 12l2 2 4-4"/>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="comparison-field <?= $emergenciaParentescoChanged ? 'field-changed-proposed' : '' ?>">
                        <span class="field-label">Parentesco:</span>
                        <span class="field-value"><?= htmlspecialchars($emergencia["parentesco"] ?? '—') ?></span>
                        <?php if ($emergenciaParentescoChanged): ?>
                            <span class="change-indicator change-new">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M8 12l2 2 4-4"/>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="comparison-field <?= $emergenciaTelefoneChanged ? 'field-changed-proposed' : '' ?>">
                        <span class="field-label">Telefone:</span>
                        <span class="field-value"><?= htmlspecialchars($emergencia["telefone"] ?? '—') ?></span>
                        <?php if ($emergenciaTelefoneChanged): ?>
                            <span class="change-indicator change-new">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M8 12l2 2 4-4"/>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Botões de Ação -->
<div class="form-container">
    <form id="form-aprovar-ficha" action="../../api/pedidos/arh_aprovar_ficha.php" method="POST" style="text-align: center;">
        <input type="hidden" name="edicao_id" value="<?= $pedido["id"] ?>">

        <div class="action-buttons">
            <button class="btn btn-success" name="acao" value="aprovar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20,6 9,17 4,12"></polyline>
                </svg>
                Aprovar Alterações
            </button>
            <button class="btn btn-danger" name="acao" value="recusar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Recusar Pedido
            </button>
        </div>
    </form>
</div>

<script>
// Debug script para verificar se as classes estão a ser aplicadas
document.addEventListener('DOMContentLoaded', function() {
    console.log('Verificando campos alterados...');

    // Verificar campos atuais
    const currentFields = document.querySelectorAll('.field-changed-current');
    console.log('Campos atuais alterados:', currentFields.length);
    currentFields.forEach((field, index) => {
        console.log(`Campo atual ${index}:`, field);
        // Aplicar estilos diretamente como fallback
        field.style.backgroundColor = '#fef2f2';
        field.style.borderColor = '#dc2626';
        field.style.borderWidth = '2px';
        const valueSpan = field.querySelector('.field-value');
        if (valueSpan) {
            valueSpan.style.color = '#b91c1c';
            valueSpan.style.fontWeight = '600';
            valueSpan.style.textDecoration = 'line-through';
        }
    });

    // Verificar campos propostos
    const proposedFields = document.querySelectorAll('.field-changed-proposed');
    console.log('Campos propostos alterados:', proposedFields.length);
    proposedFields.forEach((field, index) => {
        console.log(`Campo proposto ${index}:`, field);
        // Aplicar estilos diretamente como fallback
        field.style.backgroundColor = '#f0fdf4';
        field.style.borderColor = '#16a34a';
        field.style.borderWidth = '2px';
        const valueSpan = field.querySelector('.field-value');
        if (valueSpan) {
            valueSpan.style.color = '#15803d';
            valueSpan.style.fontWeight = '600';
        }
    });

    // Aplicar estilos aos indicadores
    const indicators = document.querySelectorAll('.change-indicator');
    indicators.forEach(indicator => {
        indicator.style.display = 'inline-flex';
        indicator.style.width = '24px';
        indicator.style.height = '24px';
        indicator.style.borderRadius = '50%';
        indicator.style.marginLeft = '0.5rem';

        if (indicator.parentElement.classList.contains('field-changed-current')) {
            indicator.style.backgroundColor = '#dc2626';
            indicator.style.color = 'white';
        } else if (indicator.parentElement.classList.contains('field-changed-proposed')) {
            indicator.style.backgroundColor = '#16a34a';
            indicator.style.color = 'white';
        }
    });

    // Aplicar estilos à legenda
    const legendCurrent = document.querySelector('.legend-indicator.current');
    const legendProposed = document.querySelector('.legend-indicator.proposed');

    if (legendCurrent) {
        legendCurrent.style.backgroundColor = '#dc2626';
    }
    if (legendProposed) {
        legendProposed.style.backgroundColor = '#16a34a';
    }
});
</script>

<style>
/* Notificação elegante para aprovação */
.ficha-toast-container { position:fixed; top:1.25rem; right:1.25rem; z-index:1200; display:flex; flex-direction:column; gap:.75rem; }
.ficha-toast { background:rgba(255,255,255,.95); backdrop-filter:blur(14px); border-radius:14px; padding:1rem 1.25rem; min-width:300px; box-shadow:0 8px 28px rgba(0,0,0,.18), 0 0 0 1px rgba(255,255,255,.3); display:flex; gap:.75rem; align-items:flex-start; border-left:4px solid; animation:fichaSlideIn .4s cubic-bezier(.16,1,.3,1); font-size:.85rem; }
.ficha-toast.success { border-left-color:#10b981; }
.ficha-toast.error { border-left-color:#ef4444; }
.ficha-toast svg { flex-shrink:0; }
.ficha-toast .ft-content { flex:1; }
.ficha-toast .ft-title { font-weight:600; color:#0f172a; margin-bottom:2px; font-size:.8rem; text-transform:uppercase; letter-spacing:.5px; }
.ficha-toast .ft-msg { color:#475569; line-height:1.2; }
.ficha-toast button { background:transparent; border:none; color:#64748b; cursor:pointer; padding:2px; border-radius:6px; }
.ficha-toast button:hover { color:#0f172a; }
@keyframes fichaSlideIn { from { opacity:0; transform:translateX(60%); } to { opacity:1; transform:translateX(0); } }
</style>

<script>
// Interceptar submissão imediatamente (sem depender de DOMContentLoaded, que pode já ter ocorrido)
(function(){
    const form = document.getElementById('form-aprovar-ficha');
    if(!form) return; // nada a fazer

    function ensureToastContainer(){
        let c = document.querySelector('.ficha-toast-container');
        if(!c){
            c = document.createElement('div');
            c.className = 'ficha-toast-container';
            document.body.appendChild(c);
        }
        return c;
    }

    function showToast(type, title, msg, auto=4500){
        const c = ensureToastContainer();
        const div = document.createElement('div');
        div.className = `ficha-toast ${type}`;
        const icons = {
            success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg>',
            error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
        };
        div.innerHTML = `${icons[type] || ''}<div class="ft-content"><div class="ft-title">${title}</div><div class="ft-msg">${msg}</div></div><button aria-label="Fechar">&times;</button>`;
        div.querySelector('button').addEventListener('click', () => div.remove());
        c.appendChild(div);
        if(auto){ setTimeout(()=> div.remove(), auto); }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form); // inclui o botão clicado (acao)
        const acao = fd.get('acao');
        if(!acao) { showToast('error','Erro','Ação não reconhecida.'); return; }
        if(!confirm(`Tem certeza que deseja ${acao === 'aprovar' ? 'aprovar' : 'recusar'} este pedido?`)) return;

        const buttons = form.querySelectorAll('button[name="acao"]');
        buttons.forEach(b => { b.disabled = true; b.dataset.originalText = b.innerHTML; b.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .9s linear infinite;"></span>'; });

        try {
            const resp = await fetch(form.action, { method:'POST', body: fd });
            let data; try { data = await resp.json(); } catch(e2){ throw new Error('Resposta inválida do servidor'); }
            if(data.success){
                showToast('success','Sucesso', data.message || 'Ficha processada.');
                setTimeout(()=>{ if(window.navigateToContent){ window.navigateToContent('gestao_fichas_colaboradores'); } else { window.history.back(); } }, 1200);
            } else {
                showToast('error','Erro', data.message || data.error || 'Não foi possível processar.');
            }
        } catch(err){
            console.error(err);
            showToast('error','Erro', err.message);
        } finally {
            buttons.forEach(b => { b.disabled = false; if(b.dataset.originalText) b.innerHTML = b.dataset.originalText; });
        }
    });
})();
</script>
