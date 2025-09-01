<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "inter2") {
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

<div class="edit-ficha-wrapper">
    <div class="edit-ficha-header">
        <div class="icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
            </svg>
        </div>
        <div>
            <h2>Editar Ficha de Colaborador</h2>
            <p>Atualize os seus dados. O pedido seguirá para aprovação.</p>
        </div>
    </div>

    <form action="../../api/pedidos/i2_ficha_colaborador.php" method="POST" class="edit-ficha-form" novalidate>
        <fieldset class="ef-section">
            <legend>Dados Pessoais</legend>
            <div class="ef-grid">
                <label class="ef-field">
                    <span>Email</span>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($dados["email"] ?? "") ?>" required>
                </label>
                <label class="ef-field">
                    <span>Telefone</span>
                    <input type="text" name="contacto_telefone" id="contacto_telefone" value="<?= htmlspecialchars($dados["telefone"] ?? "") ?>" required>
                </label>
                <label class="ef-field ef-field-wide">
                    <span>Morada</span>
                    <input type="text" name="morada" id="morada" value="<?= htmlspecialchars($dados["morada"] ?? "") ?>">
                </label>
                <label class="ef-field">
                    <span>NIB <small>(opcional)</small></span>
                    <input type="text" name="nib" id="nib" maxlength="21" inputmode="numeric" pattern="\d{21}" placeholder="21 dígitos" value="<?= htmlspecialchars($dados["nib"] ?? "") ?>">
                </label>
            </div>
        </fieldset>

        <fieldset class="ef-section">
            <legend>Contacto de Emergência</legend>
            <div class="ef-grid">
                <label class="ef-field">
                    <span>Nome</span>
                    <input type="text" name="emergencia_nome" id="emergencia_nome" value="<?= htmlspecialchars($contacto_emergencia["nome"] ?? "") ?>">
                </label>
                <label class="ef-field">
                    <span>Parentesco</span>
                    <input type="text" name="emergencia_parentesco" id="emergencia_parentesco" value="<?= htmlspecialchars($contacto_emergencia["parentesco"] ?? "") ?>">
                </label>
                <label class="ef-field">
                    <span>Telefone</span>
                    <input type="text" name="emergencia_telefone" id="emergencia_telefone" value="<?= htmlspecialchars($contacto_emergencia["telefone"] ?? "") ?>">
                </label>
            </div>
        </fieldset>

        <div class="ef-actions">
            <button type="submit" class="btn-primary ef-submit-btn">
                <span class="ef-label">Submeter Pedido</span>
                <span class="ef-spinner" aria-hidden="true"></span>
            </button>
        </div>
    </form>
</div>

<style>
.edit-ficha-wrapper{background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:2.25rem 2.5rem;box-shadow:0 8px 32px -8px rgba(0,0,0,.08);max-width:980px;margin:0 auto;animation:ef-fadeIn .4s ease;} 
.edit-ficha-header{display:flex;align-items:flex-start;gap:1.25rem;margin-bottom:1.5rem;} 
.edit-ficha-header .icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--gradient-1),var(--gradient-2));color:#fff;box-shadow:0 6px 18px rgba(62,132,242,.35);} 
.edit-ficha-header h2{margin:0;font-size:1.65rem;font-weight:700;color:var(--navy-blue);} 
.edit-ficha-header p{margin:.35rem 0 0;font-size:.95rem;color:var(--text-light);} 
.edit-ficha-form{display:flex;flex-direction:column;gap:2rem;} 
.ef-section{border:1px solid #e2e8f0;border-radius:20px;padding:1.5rem 1.75rem;margin:0;position:relative;background:#f8fafc;} 
.ef-section legend{font-size:.9rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;padding:0 .65rem;color:#334155;} 
.ef-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.1rem;} 
.ef-field{display:flex;flex-direction:column;gap:6px;font-size:.7rem;font-weight:600;color:#475569;} 
.ef-field-wide{grid-column:1/-1;} 
.ef-field span small{font-weight:400;color:#64748b;font-size:.7rem;margin-left:.25rem;} 
.ef-field input{padding:.65rem .85rem;border:1px solid #cbd5e1;border-radius:10px;font-size:.9rem;font-weight:500;background:#fff;transition:.15s;border-top-color:#d5dee9;} 
.ef-field input:focus{outline:none;border-color:var(--light-blue);box-shadow:0 0 0 3px rgba(56,132,255,.15);} 
.ef-actions{display:flex;justify-content:flex-end;margin-top:-.5rem;} 
.ef-submit-btn{display:inline-flex;align-items:center;gap:.55rem;padding:.9rem 1.75rem;font-weight:600;border:none;border-radius:14px;cursor:pointer;position:relative;overflow:hidden;box-shadow:0 8px 20px -4px rgba(0,0,0,.25);background:linear-gradient(135deg,var(--gradient-1),var(--gradient-2));color:#fff;font-size:.95rem;transition:.18s ease;} 
.ef-submit-btn:hover{transform:translateY(-2px);box-shadow:0 12px 28px -6px rgba(0,0,0,.35);filter:brightness(1.05);} 
.ef-submit-btn:active{transform:translateY(0);box-shadow:0 6px 16px -4px rgba(0,0,0,.4);} 
.ef-submit-btn:focus-visible{outline:3px solid rgba(0,0,0,.25);outline-offset:3px;} 
.ef-submit-btn[disabled]{opacity:.65;cursor:not-allowed;transform:none;box-shadow:0 4px 14px -4px rgba(0,0,0,.25);} 
.ef-submit-btn .ef-spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.65);border-top-color:transparent;border-radius:50%;animation:ef-spin .7s linear infinite;} 
@keyframes ef-spin{to{transform:rotate(360deg)}} 
@media (max-width:720px){.edit-ficha-wrapper{padding:1.5rem 1.25rem;border-radius:20px;} .ef-section{padding:1.25rem 1.1rem;} }
@keyframes ef-fadeIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
</style>

<script>
(function(){
  const form=document.querySelector('.edit-ficha-form');
  if(!form) return;
  form.addEventListener('submit',function(){
      const btn=form.querySelector('.ef-submit-btn');
      const label=btn.querySelector('.ef-label');
      const spin=btn.querySelector('.ef-spinner');
      label.style.display='none';
      spin.style.display='inline-block';
      btn.disabled=true;
      setTimeout(()=>{btn.disabled=false;label.style.display='';spin.style.display='none';},8000); // fallback
  });
  // Enforcing numeric pattern for NIB gracefully
  const nib=document.getElementById('nib');
  if(nib){
    nib.addEventListener('input',()=>{nib.value=nib.value.replace(/[^0-9]/g,'').slice(0,21);});
  }
})();
</script>
