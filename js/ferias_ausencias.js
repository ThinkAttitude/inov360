// Ferias e Ausencias - Opera
function __initFeriasAusenciasOnce(){
    if (window.__feriasAusenciasInited__) return;
    window.__feriasAusenciasInited__ = true;
    ensureToastInfra();
    initFeriasAusenciasForm();
    initFilterButtons();
    wireSubmitHandler();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', __initFeriasAusenciasOnce);
} else {
    __initFeriasAusenciasOnce();
}

// Minimal toast system (self-contained; no external CSS needed)
function ensureToastInfra(){
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
        .toast-container{position:fixed;top:16px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:8px}
        .toast{min-width:260px;max-width:420px;padding:12px 14px;border-radius:10px;color:#0b1220;background:#0b1220;box-shadow:0 6px 16px rgba(0,0,0,.18);display:flex;align-items:flex-start;gap:10px;opacity:0;transform:translateY(-6px);animation:toast-in .2s ease forwards}
        .toast.success{background:linear-gradient(135deg,#10b981,#34d399);color:#062d1f}
        .toast.error{background:linear-gradient(135deg,#ef4444,#f59e0b);color:#2b0b0b}
        .toast.info{background:linear-gradient(135deg,#3e84f2,#7aa8f9);color:#041935}
        .toast .t-icon{font-size:18px;line-height:18px;margin-top:2px}
        .toast .t-msg{flex:1;font-weight:600}
        .toast .t-close{background:transparent;border:none;color:inherit;cursor:pointer;font-size:16px;opacity:.8}
        @keyframes toast-in{to{opacity:1;transform:translateY(0)}}
        @keyframes toast-out{to{opacity:0;transform:translateY(-6px)}}`;
        document.head.appendChild(style);
    }
    if (!document.querySelector('.toast-container')){
        const c = document.createElement('div');
        c.className = 'toast-container';
        document.body.appendChild(c);
    }
    if (!window.showToast){
        window.showToast = function(type, message, opts={}){
            const container = document.querySelector('.toast-container');
            const t = document.createElement('div');
            t.className = `toast ${type||'info'}`;
            const icon = type==='success'?'✓':type==='error'?'✗':'ℹ';
            t.innerHTML = `<span class="t-icon">${icon}</span><div class="t-msg">${message}</div><button class="t-close" aria-label="Fechar">×</button>`;
            container.appendChild(t);
            const ttl = Number(opts.duration||2500);
            const close = ()=>{ t.style.animation = 'toast-out .18s ease forwards'; setTimeout(()=>t.remove(), 200); };
            t.querySelector('.t-close').addEventListener('click', close);
            setTimeout(close, ttl);
            return t;
        };
    }
}

// Modal functions
function abrirModalPedido() {
    const modal = document.getElementById('modalPedido');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function fecharModalPedido() {
    const modal = document.getElementById('modalPedido');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Reset comprovativo status
            const comprovativoStatus = document.getElementById('comprovativo-status');
            if (comprovativoStatus) {
                comprovativoStatus.textContent = '(opcional)';
            }
            const comprovativoInput = document.getElementById('ficheiro');
            if (comprovativoInput) {
                comprovativoInput.required = false;
            }
        }
    }
}

// Form functionality
function initFeriasAusenciasForm() {
    const tipoSelect = document.getElementById("tipo");
    const comprovativoInput = document.getElementById("ficheiro");
    const comprovativoStatus = document.getElementById("comprovativo-status");

    if (!tipoSelect || !comprovativoInput || !comprovativoStatus) return;

    const obrigatorios = [
        "licenca_paternidade",
        "licenca_maternidade",
        "baixa_medica",
        "baixa_seguro",
        "casamento",
        "consulta_medica"
    ];

    function atualizarObrigatoriedade() {
        const tipoSelecionado = tipoSelect.value;

        if (obrigatorios.includes(tipoSelecionado)) {
            comprovativoInput.required = true;
            comprovativoStatus.textContent = "(obrigatório)";
            comprovativoStatus.style.color = "#dc2626";
        } else {
            comprovativoInput.required = false;
            comprovativoStatus.textContent = "(opcional)";
            comprovativoStatus.style.color = "#6b7280";
        }
    }

    tipoSelect.addEventListener("change", atualizarObrigatoriedade);
    atualizarObrigatoriedade();

    // File input styling
    if (comprovativoInput) {
        comprovativoInput.addEventListener('change', function() {
            const wrapper = this.closest('.file-input-wrapper');
            const content = wrapper.querySelector('.file-input-content span');

            if (this.files.length > 0) {
                content.textContent = `Ficheiro selecionado: ${this.files[0].name}`;
                wrapper.style.borderColor = '#10b981';
                wrapper.style.backgroundColor = '#ecfdf5';
            } else {
                content.textContent = 'Clique para selecionar ficheiro';
                wrapper.style.borderColor = '#d1d5db';
                wrapper.style.backgroundColor = '';
            }
        });
    }
}

// Filter functionality
function initFilterButtons() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const pedidoCards = document.querySelectorAll('.pedido-card');

    if (!filterButtons.length || !pedidoCards.length) return;

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;

            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Filter cards
            pedidoCards.forEach(card => {
                const estado = card.dataset.estado;

                if (filter === 'all' || estado === filter) {
                    card.style.display = 'block';
                    card.classList.remove('hidden');
                } else {
                    card.style.display = 'none';
                    card.classList.add('hidden');
                }
            });
        });
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modalPedido');
    if (modal && e.target === modal) {
        fecharModalPedido();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModalPedido();
    }
});

// Make functions global so they can be called from HTML onclick attributes
window.abrirModalPedido = abrirModalPedido;
window.fecharModalPedido = fecharModalPedido;

// AJAX submit to new API (works for all role pages using same modal markup)
function wireSubmitHandler() {
    const modal = document.getElementById('modalPedido');
    if (!modal) return;
    const form = modal.querySelector('form.modal-form');
    // Interceptador global como fallback (idempotente)
    if (!window.__leavesSubmitCapture__) {
        document.addEventListener('submit', globalLeavesSubmitInterceptor, true);
        window.__leavesSubmitCapture__ = true;
    }
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        try {
            e.preventDefault();
            const submitBtn = form.querySelector('.btn-submit');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'A enviar...';
            }

            // Debounce
            if (form.__leavesSubmitting) return;
            form.__leavesSubmitting = true;
            // Debounce
            if (form.__leavesSubmitting) return;
            form.__leavesSubmitting = true;
            const fd = new FormData(form);
            // Normalizar datas (dd/mm/yyyy -> yyyy-mm-dd) se necessário
            try {
                const diEl = form.querySelector('#data_inicio');
                const dfEl = form.querySelector('#data_fim');
                const norm = v => (/^\d{2}\/\d{2}\/\d{4}$/.test(v) ? `${v.slice(6,10)}-${v.slice(3,5)}-${v.slice(0,2)}` : v);
                if (diEl && diEl.value) fd.set('data_inicio', norm(diEl.value));
                if (dfEl && dfEl.value) fd.set('data_fim', norm(dfEl.value));
            } catch(_) {}
            const resp = await fetch(form.action, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            });

            const contentType = resp.headers.get('content-type') || '';
            let data = null;
            if (contentType.includes('application/json')) {
                data = await resp.json();
            } else {
                // Fallback: try text and attempt JSON parse
                const text = await resp.text();
                try { data = JSON.parse(text); } catch { data = { ok: false, error: text || 'Erro ao processar resposta.' }; }
            }

            if (!resp.ok || !data || data.ok === false) {
                const code = data && (data.code || data.error || data.message);
                const codeMap = {
                    MISSING_FIELDS: 'Preencha todos os campos obrigatórios.',
                    INVALID_DATE: 'Data inválida.',
                    RANGE_ERROR: 'Data de início deve ser anterior à data de fim.',
                    DOC_REQUIRED: 'Este tipo exige comprovativo (PDF/JPG/PNG).',
                    BAD_FILETYPE: 'Tipo de ficheiro inválido (PDF, JPG, PNG).',
                    FILE_TOO_LARGE: 'Ficheiro maior que 5MB.',
                    FILE_MOVE_ERROR: 'Erro ao guardar o ficheiro no servidor.',
                    UNAUTHENTICATED: 'Sessão expirada. Faça login novamente.',
                    FORBIDDEN_ROLE: 'Perfil sem permissão para criar pedidos.',
                    DB_ERROR: 'Erro interno ao gravar o pedido.'
                };
                const friendly = codeMap[code] || (code ? String(code) : `HTTP ${resp.status}`);
                showToast('error', `Falha ao submeter pedido: ${friendly}`);
            } else {
                showToast('success', 'Pedido submetido com sucesso.');
                fecharModalPedido();
                // Refresh após breve delay para permitir ver o toast
                setTimeout(() => window.location.reload(), 1200);
            }
        } catch (err) {
            showToast('error', `Erro inesperado: ${err && err.message ? err.message : err}`);
        } finally {
            const submitBtn = form.querySelector('.btn-submit');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText || 'Submeter Pedido';
            }
            form.__leavesSubmitting = false;
        }
    });

    // Capturar clique em qualquer botão submit dentro do modal para garantir hijack
    modal.addEventListener('click', function(ev){
        const target = ev.target.closest('button');
        if (!target) return;
        if (target.type === 'submit') {
            // O submit será tratado pelo listener do form ou pelo interceptador global
        }
    }, true);
}

// Interceptador global de submits para o endpoint de leaves (caso algum formulário fuja ao seletor do modal)
function globalLeavesSubmitInterceptor(ev){
    const form = ev.target;
    try{
        if (form && form.action && form.action.includes('/api/leaves/request.php')){
            ev.preventDefault();
            if (form.__leavesSubmitting) return;
            form.__leavesSubmitting = true;
            const fd = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"], .btn-submit');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn){ submitBtn.disabled = true; submitBtn.innerText = 'A enviar...'; }
            // Normalizar datas (dd/mm/yyyy -> yyyy-mm-dd) se necessário
            try {
                const diEl = form.querySelector('#data_inicio');
                const dfEl = form.querySelector('#data_fim');
                const norm = v => (/^\d{2}\/\d{2}\/\d{4}$/.test(v) ? `${v.slice(6,10)}-${v.slice(3,5)}-${v.slice(0,2)}` : v);
                if (diEl && diEl.value) fd.set('data_inicio', norm(diEl.value));
                if (dfEl && dfEl.value) fd.set('data_fim', norm(dfEl.value));
            } catch(_) {}
            fetch(form.action, { method:'POST', body: fd, credentials: 'same-origin' })
                .then(async resp => {
                    const ct = resp.headers.get('content-type')||'';
                    let data=null;
                    if (ct.includes('application/json')) data = await resp.json();
                    else { const txt = await resp.text(); try{ data=JSON.parse(txt);}catch{ data={ ok:false, error:txt||'Erro ao processar resposta.'}; } }
                    if (!resp.ok || !data || data.ok===false){
                        const code = data && (data.code || data.error || data.message);
                        const codeMap = {
                            MISSING_FIELDS: 'Preencha todos os campos obrigatórios.',
                            INVALID_DATE: 'Data inválida.',
                            RANGE_ERROR: 'Data de início deve ser anterior à data de fim.',
                            DOC_REQUIRED: 'Este tipo exige comprovativo (PDF/JPG/PNG).',
                            BAD_FILETYPE: 'Tipo de ficheiro inválido (PDF, JPG, PNG).',
                            FILE_TOO_LARGE: 'Ficheiro maior que 5MB.',
                            FILE_MOVE_ERROR: 'Erro ao guardar o ficheiro no servidor.',
                            UNAUTHENTICATED: 'Sessão expirada. Faça login novamente.',
                            FORBIDDEN_ROLE: 'Perfil sem permissão para criar pedidos.',
                            DB_ERROR: 'Erro interno ao gravar o pedido.'
                        };
                        const friendly = codeMap[code] || (code ? String(code) : `HTTP ${resp.status}`);
                        showToast('error', `Falha ao submeter pedido: ${friendly}`);
                    } else {
                        showToast('success', 'Pedido submetido com sucesso.');
                        setTimeout(()=>window.location.reload(), 1200);
                    }
                })
                .catch(err => showToast('error', `Erro inesperado: ${err && err.message ? err.message : err}`))
                .finally(()=>{ if (submitBtn){ submitBtn.disabled=false; submitBtn.innerHTML = originalText || 'Submeter Pedido'; } form.__leavesSubmitting = false; });
        }
    }catch(_){/* noop */}
}

