<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) { echo "<p>Utilizador inválido.</p>"; exit; }

// Buscar info básica do colaborador
$user = null;
try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $nameCol = 'name';
    try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
    catch(Throwable $e){ $nameCol = 'name'; }
    $st = $pdo->prepare("SELECT id, $nameCol AS name, email, role FROM user WHERE id=:id LIMIT 1");
    $st->execute([':id'=>$userId]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if (!$user) { echo "<p>Colaborador não encontrado.</p>"; exit; }
} catch (Throwable $e) {
    echo "<p>Erro: ".$e->getMessage()."</p>"; exit;
}
?>

<link rel="stylesheet" href="../../css/global.css">
<link rel="stylesheet" href="../../css/fichas_colaboradores.css">

<div class="ficha-page">
    <div class="ficha-header">
        <div class="header-left">
            <div class="page-icon" style="background: linear-gradient(135deg,#F59E0B,#D97706);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="header-text">
                <h2>Ficha Financeira</h2>
                <p><strong>Colaborador:</strong> <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</p>
                <p class="note">Como Admin RH, pode visualizar e editar os dados deste colaborador.</p>
            </div>
        </div>
        <div class="header-actions">
            <button id="btn-guardar" class="btn-primary">Guardar Alterações</button>
        </div>
    </div>

    <div id="finance-form" class="ficha-content" data-user-id="<?= (int)$userId ?>">
        <div class="ficha-section">
            <div class="section-header"><h3>Identificação</h3></div>
            <div class="form-grid">
            <div class="field full">
                <label>Nome Completo</label>
                <input id="nome_completo" type="text" placeholder="Nome completo do colaborador">
            </div>
            </div>
        </div>

        <div class="ficha-section">
            <div class="section-header"><h3>Remunerações</h3></div>
            <div class="form-grid">
            <div class="field"><label>Vencimento Estimado</label><input id="vencimento_estimado" type="number" step="0.01"></div>
            <div class="field"><label>Vencimento Base</label><input id="vencimento_base" type="number" step="0.01"></div>
            <div class="field"><label>Duodécimos</label><input id="duodecimos" type="number" step="1"></div>
            <div class="field"><label>Bónus/Bonificações</label><input id="bonus_bonificacoes" type="number" step="0.01"></div>
            <div class="field"><label>Ajustes de Vencimento</label><input id="ajustes_vencimento" type="number" step="0.01"></div>
            <div class="field"><label>Ajudas de Custos a Deduzir</label><input id="ajudas_custos_deduc" type="number" step="0.01"></div>
            </div>
        </div>

        <div class="ficha-section">
            <div class="section-header"><h3>Subsídios & Benefícios</h3></div>
            <div class="form-grid">
            <div class="field"><label>Valor Subsídio Alimentação (€)</label><input id="valor_sub_alimentacao" type="number" step="0.01"></div>
            <div class="field"><label>Dias de Subsídio Alimentação</label><input id="dias_sub_alimentacao" type="number" step="1"></div>
            <div class="field"><label>Ajuda Custo Estimado</label><input id="ajuda_custo_estimado" type="number" step="0.01"></div>
            <div class="field"><label>Subsídio Noturno</label><input id="subsidio_noturno" type="number" step="0.01"></div>
            <div class="field"><label>Subsídio de Turno</label><input id="subsidio_turno" type="number" step="0.01"></div>
            <div class="field"><label>Prevencões (€)</label><input id="valor_prevencoes" type="number" step="0.01"></div>
            <div class="field inline-checkbox"><input id="prevencoes_sn" type="checkbox"><label for="prevencoes_sn">Tem prevencões?</label></div>
            <div class="field"><label>Valor Passe Transporte</label><input id="valor_passe_transporte" type="number" step="0.01"></div>
            </div>
        </div>

        <div class="ficha-section">
            <div class="section-header"><h3>Deslocações</h3></div>
            <div class="form-grid">
            <div class="field"><label>KMs Estimados</label><input id="kms_estimados" type="number" step="1"></div>
            <div class="field"><label>Valor por KM</label><input id="valor_por_km" type="number" step="0.01"></div>
            </div>
        </div>

        <div class="ficha-section">
            <div class="section-header"><h3>Deduções</h3></div>
            <div class="form-grid">
            <div class="field"><label>Adiantamentos a Deduzir</label><input id="adiantamentos_deduzir" type="number" step="0.01"></div>
            <div class="field inline-checkbox"><input id="penhoras_sn" type="checkbox"><label for="penhoras_sn">Tem penhoras?</label></div>
            <div class="field"><label>IHT</label><input id="iht" type="number" step="0.01"></div>
            </div>
        </div>

        <div class="ficha-section">
            <div class="section-header"><h3>Faltas / Férias / Doença</h3></div>
            <div class="form-grid">
            <div class="field"><label>Faltas Não Remuneradas</label><input id="faltas_nao_rem" type="number" step="1"></div>
            <div class="field"><label>Faltas Não Rem. Justificadas</label><input id="faltas_nao_rem_just" type="number" step="1"></div>
            <div class="field"><label>Faltas Rem. Justificadas</label><input id="faltas_rem_just" type="number" step="1"></div>
            <div class="field"><label>Baixa Médica - Início</label><input id="baixa_medica_start" type="date"></div>
            <div class="field"><label>Baixa Médica - Fim</label><input id="baixa_medica_end" type="date"></div>
            <div class="field inline-checkbox"><input id="ferias_sn" type="checkbox"><label for="ferias_sn">Em férias?</label></div>
            <div class="field"><label>Início Férias</label><input id="ferias_start" type="date"></div>
            <div class="field"><label>Fim Férias</label><input id="ferias_end" type="date"></div>
            </div>
        </div>

        <div class="ficha-section">
            <div class="section-header"><h3>Observações</h3></div>
            <div class="form-grid">
                <div class="field full"><label>Notas</label><textarea id="observacoes" rows="4" placeholder="Notas relevantes ao processamento salarial..."></textarea></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Reuse collaborator page look */
.ficha-page{padding:0;background:#f8fafc;min-height:100vh;}
.ficha-header{background:#fff;padding:2rem 2.5rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.header-left{display:flex;align-items:center;gap:1.5rem;}
.page-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 8px 24px rgba(62,132,242,.25)}
.header-text h2{font-size:1.75rem;font-weight:700;color:var(--navy-blue);margin-bottom:.25rem;letter-spacing:-.5px}
.header-text p{color:var(--text-light);font-size:1rem;margin:0}
.btn-primary{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:linear-gradient(135deg,var(--gradient-1),var(--gradient-2));color:#fff;border:none;border-radius:12px;font-weight:600;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:0 4px 12px rgba(62,132,242,.3)}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(62,132,242,.4)}
.ficha-content{padding:2rem 2.5rem;display:flex;flex-direction:column;gap:2rem}
.ficha-section{background:#fff;border-radius:20px;border:1px solid #e2e8f0;box-shadow:0 4px 20px rgba(0,0,0,.05);overflow:hidden;animation:slideIn .5s ease-out}
.section-header{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0;background:#f8fafc}
.section-header h3{font-size:1.1rem;font-weight:600;color:var(--navy-blue);margin:0}
.form-grid{display:grid;grid-template-columns: repeat(2, minmax(260px, 1fr)); gap:14px;padding:1rem 1.25rem 1.5rem}
.form-grid .field{display:flex;flex-direction:column}
.form-grid .field label{font-size:.9rem;color:#334155;margin-bottom:6px}
.form-grid .field input[type=text],
.form-grid .field input[type=number],
.form-grid .field input[type=date],
.form-grid .field textarea{border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px;font-size:.95rem}
.full{grid-column:1 / -1}
.inline-checkbox{display:flex;align-items:center;gap:8px;padding:10px 0}
.toast{position:fixed;right:20px;bottom:20px;background:#0A2240;color:#fff;padding:10px 14px;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,.15)}
@keyframes slideIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@media (max-width: 1200px){.ficha-header{padding:1.5rem 2rem}.ficha-content{padding:1.5rem 2rem}.form-grid{grid-template-columns:1fr}}
@media (max-width: 768px){.ficha-header{flex-direction:column;gap:1rem;padding:1.5rem;text-align:center}.ficha-content{padding:1rem 1.5rem}.section-header{padding:1rem 1.25rem}}
</style>

<script>
const userId = Number(document.getElementById('finance-form').dataset.userId);

function toast(msg, kind='info'){
  const n = document.createElement('div'); n.className='toast'; n.textContent=msg; document.body.appendChild(n); setTimeout(()=>n.remove(), 2600);
}

function parseJSONLoose(raw){
    try { return JSON.parse(raw); } catch(_) {}
    const start = raw.search(/\{[\s\r\n]*\"/);
    if (start === -1) return null;
    let depth = 0; let inStr = false; let esc = false;
    for (let i = start; i < raw.length; i++){
        const ch = raw[i];
        if (inStr){
            if (esc) { esc = false; continue; }
            if (ch === '\\') { esc = true; continue; }
            if (ch === '"') { inStr = false; continue; }
            continue;
        }
        if (ch === '"') { inStr = true; continue; }
        if (ch === '{') depth++;
        else if (ch === '}') { depth--; if (depth === 0){ const slice = raw.slice(start, i+1); try { return JSON.parse(slice); } catch(_) { return null; } } }
    }
    return null;
}

async function loadFinanceProfile(){
        try {
        const r = await fetch(`/api/finance/profile_get.php?user_id=${encodeURIComponent(userId)}`, { credentials:'same-origin', headers:{ 'Accept':'application/json' } });
        if (!r.ok) throw new Error('HTTP '+r.status);
        const raw = await r.text();
        const d = parseJSONLoose(raw) || { ok:false, code:'BAD_JSON' };
        if (!d.ok) throw new Error(d.code||'API');
            const v = d.data || {};
            const set = (id, val)=>{ const el=document.getElementById(id); if(!el) return; if(el.type==='checkbox'){ el.checked = String(val)==='1' || val===1 || val===true; } else { el.value = (val==null? '': val); } };
            [
                'nome_completo','vencimento_estimado','vencimento_base','duodecimos',
                'valor_sub_alimentacao','dias_sub_alimentacao','kms_estimados','valor_por_km','valor_prevencoes','valor_passe_transporte','iht','ajuda_custo_estimado','subsidio_noturno','subsidio_turno','ajudas_custos_deduc','adiantamentos_deduzir','bonus_bonificacoes','prevencoes_sn','penhoras_sn','ferias_sn','faltas_nao_rem','faltas_nao_rem_just','faltas_rem_just','baixa_medica_start','baixa_medica_end','ferias_start','ferias_end','observacoes','ajustes_vencimento'
            ].forEach(k=> set(k, v[k]));
  } catch(e){ console.error(e); toast('Falha ao carregar ficha financeira','error'); }
}

async function saveFinanceProfile(){
    const getEl = (id)=> document.getElementById(id);
    const putIf = (obj, key, el)=>{
        if(!el) return;
        if(el.type==='checkbox'){ obj[key] = el.checked?1:0; return; }
        const v = (el.value ?? '').toString().trim();
        if(v==='') return; // skip empty values to avoid DB strict errors
        if(el.type==='number'){ const n = Number(v); if(!Number.isNaN(n)) obj[key] = n; }
        else { obj[key] = v; }
    };
    const payload = { user_id: userId };
    [
        'numero','nome_completo','vencimento_estimado','vencimento_base','duodecimos',
        'valor_sub_alimentacao','dias_sub_alimentacao','kms_estimados','valor_por_km','valor_prevencoes','valor_passe_transporte','iht','ajuda_custo_estimado','subsidio_noturno','subsidio_turno','ajudas_custos_deduc','adiantamentos_deduzir','bonus_bonificacoes','prevencoes_sn','penhoras_sn','ferias_sn','faltas_nao_rem','faltas_nao_rem_just','faltas_rem_just','baixa_medica_start','baixa_medica_end','ferias_start','ferias_end','observacoes','ajustes_vencimento'
    ].forEach(k=> putIf(payload, k, getEl(k)));
        try {
            const r = await fetch('/api/finance/profile_update.php', {
                method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, credentials:'same-origin',
            body: JSON.stringify(payload)
        });
        const raw = await r.text();
        const d = parseJSONLoose(raw) || { ok:false, code:'BAD_JSON' };
            if (!r.ok || !d.ok) throw new Error(d.code||'API');
        toast('Ficha financeira guardada.','success');
        } catch(e){ console.error(e); toast('Falha ao guardar: '+(e && e.message ? e.message : ''),'error'); }
}

document.getElementById('btn-guardar').addEventListener('click', saveFinanceProfile);
document.addEventListener('DOMContentLoaded', loadFinanceProfile);
</script>
