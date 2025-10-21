// Handles approve/reject actions using /api/leaves/decision.php
(function(){
    if (window.__leavesApprovalInit) return; // prevent double init
    window.__leavesApprovalInit = true;
    try { console.debug('leaves_approval: init'); } catch(_) {}
    // Ensure toast infra available (shared with ferias_ausencias.js, but safe to init again)
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
    ensureToastInfra();
    function findDecisionTarget(ev){
        // Prefer composedPath to handle clicks on inner SVGs/icons
        const path = typeof ev.composedPath === 'function' ? ev.composedPath() : [];
        for (const el of path){
            if (el && el.closest){
                const t = el.closest('.js-approve[data-pedido-id], .js-reject[data-pedido-id]');
                if (t) return t;
            }
        }
        // Fallback to closest from target
        return ev.target && ev.target.closest ? ev.target.closest('.js-approve[data-pedido-id], .js-reject[data-pedido-id]') : null;
    }
    function onDecisionClick(ev){
        const target = findDecisionTarget(ev);
        if (!target) return;
        const id = target.getAttribute('data-pedido-id');
        if (!id) return;
        if (target.dataset.processing === '1') return;
        try { console.debug('leaves_approval: click', target.classList.contains('js-approve') ? 'approve' : 'reject', id); } catch(_) {}
        if (target.classList.contains('js-approve')){
            const proceed = window.confirm('Aprovar pedido\n\nTem certeza que deseja aprovar este pedido?');
            if (!proceed) return;
            target.dataset.processing = '1';
            decidirPedido(id, 'aprovar').finally(()=>{ delete target.dataset.processing; });
        } else if (target.classList.contains('js-reject')){
            const proceed = window.confirm('Rejeitar pedido\n\nTem certeza que deseja rejeitar este pedido?');
            if (!proceed) return;
            const comentario = window.prompt('Motivo da rejeição\n\nIndique o motivo da rejeição (obrigatório):');
            if (comentario === null) return; // cancel
            const c = (comentario || '').trim();
            if (!c) { showToast('error', 'Motivo de rejeição é obrigatório.'); return; }
            target.dataset.processing = '1';
            decidirPedido(id, 'rejeitar', c).finally(()=>{ delete target.dataset.processing; });
        }
    }
    function wireApprovalHandlers(){
        // Capture-phase delegation to survive stopPropagation on bubble
        document.addEventListener('click', onDecisionClick, { capture: true });
    }

    // Global in-flight guard to prevent duplicate submits per pedido
    window.__leavesInFlight = window.__leavesInFlight || new Set();

    // Pre-check for conflicts: see if target days already have LEAVE for that user
    async function preCheckLeaveConflicts(pedidoId){
        try{
            // Load pending requests and find this one
            let resp = await fetch('/api/leaves/aval_requests.php', { credentials: 'same-origin' });
            if (!resp.ok) return null; // don't block if we can't check
            let data = await resp.json();
            if (!data || data.ok!==true || !Array.isArray(data.items)) return null;
            const item = data.items.find(it => Number(it.pedido_id) === Number(pedidoId));
            if (!item || !item.colaborador || !item.colaborador.id || !item.inicio || !item.fim) return null;
            const userId = Number(item.colaborador.id);
            const start = String(item.inicio).slice(0,10);
            const end   = String(item.fim).slice(0,10);
            const toDate = s => { const [y,m,d] = s.split('-').map(Number); return new Date(y, m-1, d); };
            const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
            // Build months between start and end
            const months = [];
            const cs = new Date(toDate(start).getFullYear(), toDate(start).getMonth(), 1);
            const ce = new Date(toDate(end).getFullYear(), toDate(end).getMonth(), 1);
            while (cs <= ce) { months.push(`${cs.getFullYear()}-${String(cs.getMonth()+1).padStart(2,'0')}`); cs.setMonth(cs.getMonth()+1); }
            // Fetch calendar leaves for those months
            const monthResults = await Promise.all(months.map(m =>
                fetch(`/api/calendar/get_month.php?month=${encodeURIComponent(m)}&user_id=${encodeURIComponent(userId)}`, { credentials:'same-origin' })
                    .then(r => r.ok ? r.json() : null).catch(()=>null)
            ));
            // Build a map date -> hasConflict (true if any leave exists that is NOT from this pedidoId)
            const conflictMap = Object.create(null);
            monthResults.forEach(res => {
                if (res && res.ok && Array.isArray(res.days)){
                    res.days.forEach(d => {
                        if (!Array.isArray(d.leaves) || d.leaves.length===0) return;
                        const dateKey = String(d.date);
                        // A day is a conflict only if there's a leave with requestId != pedidoId (including null)
                        const conflict = d.leaves.some(lv => {
                            const rid = typeof lv.requestId === 'number' ? lv.requestId : (lv.requestId ? Number(lv.requestId) : null);
                            return rid === null || rid !== Number(pedidoId);
                        });
                        if (conflict) conflictMap[dateKey] = true;
                    });
                }
            });
            // Enumerate requested dates and check conflicts
            const sDate = toDate(start), eDate = toDate(end);
            const conflicts = [];
            for(let cur=new Date(sDate); cur<=eDate; cur.setDate(cur.getDate()+1)){
                const key = fmt(cur);
                if (conflictMap[key]) conflicts.push(key);
            }
            return conflicts.length ? conflicts : [];
        }catch(_){ return null; }
    }

    async function decidirPedido(pedidoId, acao, comentario){
        if (window.__leavesInFlight.has(String(pedidoId))) return;
        window.__leavesInFlight.add(String(pedidoId));
        try {
            const submittingToast = showToast('info', 'A enviar decisão...');
            // Pre-check only for approve flow (avoid server duplicate error)
            if (acao === 'aprovar') {
                const conflicts = await preCheckLeaveConflicts(pedidoId);
                if (Array.isArray(conflicts) && conflicts.length){
                    // Permitir override como no fluxo que o utilizador refere (aprovar mesmo com conflitos)
                    const proceedAnyway = window.confirm(
                        `Foram detetadas ausências já aprovadas em:\n\n${conflicts.join(', ')}\n\nPretende aprovar o pedido mesmo assim?`
                    );
                    if (!proceedAnyway) { return; }
                }
            }
            // Try absolute path first, then fallback to relative if the host serves under a sub-path
            const payload = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ pedido_id: Number(pedidoId), acao, comentario })
            };
            let resp;
            try {
                resp = await fetch('/api/leaves/decision.php', payload);
                if (resp.status === 404) throw new Error('NOT_FOUND');
            } catch(_) {
                resp = await fetch('../../api/leaves/decision.php', payload);
            }
            const contentType = resp.headers.get('content-type') || '';
            let data = null;
            if (contentType.includes('application/json')) {
                data = await resp.json();
            } else {
                const text = await resp.text();
                try { data = JSON.parse(text); } catch { data = { ok: false, error: text || 'Erro ao processar resposta.' }; }
            }
            if (!resp.ok || !data || data.ok === false) {
                const code = data && (data.code || data.error || data.message || data.msg);
                const msgText = (data && (data.msg || data.error || data.message)) || '';
                // Tratar duplicado como "aprovado" (comportamento observado: pedido fica aprovado apesar do erro no insert do evento)
                const isDuplicate = String(code).includes('DB_ERROR') && /Duplicate entry|uq_evento_user_tipo_dia/i.test(msgText || '');
                if (isDuplicate && acao === 'aprovar') {
                    showToast('success', 'Pedido aprovado (evento já existia no calendário).');
                    setTimeout(() => window.location.reload(), 900);
                    return;
                }
                if (code === 'LEAVE_CONFLICT_DAYS' && Array.isArray(data.dates) && data.dates.length) {
                    const list = data.dates.join(', ');
                    showToast('error', `Já existe uma ausência aprovada para: ${list}`);
                } else {
                    const msg = code || `Erro ${resp.status}`;
                    showToast('error', `Falha ao processar decisão: ${msg}`);
                }
                return;
            }
            showToast('success', 'Decisão efetuada com sucesso.');
            setTimeout(() => window.location.reload(), 900);
        } catch (err) {
            showToast('error', `Erro inesperado: ${err && err.message ? err.message : err}`);
        } finally {
            window.__leavesInFlight.delete(String(pedidoId));
        }
    }

    // Wire immediately if DOM is already ready; otherwise on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wireApprovalHandlers);
    } else {
        wireApprovalHandlers();
    }

    // Native confirm/prompt used directly in click handlers to preserve user gesture context
})();
