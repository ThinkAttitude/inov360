<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "*") {
    echo "<p>Acesso negado.</p>";
    exit;
}

try {
    $conn = db_connect();

    // Pedidos de admin e adminrh
    $stmt = $conn->prepare("
        SELECT p.*, u.name AS operador_nome, d.name AS decidido_por_nome, r.name AS responsavel_nome
        FROM pedidos_ferias p
        JOIN user u ON p.user_id = u.id
        LEFT JOIN user d ON p.decidido_por = d.id
        LEFT JOIN user r ON p.responsavel_id = r.id
        WHERE p.estado IN ('aprovado', 'rejeitado') AND u.role IN ('admin', 'admin_rh')
        ORDER BY p.criado_em DESC
    ");
    $stmt->execute();
    $pedidos = $stmt->fetchAll();

} catch (Exception $e) {
    echo "<p>Erro ao carregar pedidos: " . $e->getMessage() . "</p>";
    $pedidos = [];
}
?>

<link rel="stylesheet" href="../../css/legacy/fichas_colaboradores.css">

<div class="estrela-consulta">
    <div class="estrela-header">
        <div class="eh-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="eh-text">
            <h2>Histórico de Pedidos (Admin & Admin RH)</h2>
            <p>Pedidos aprovados ou rejeitados com detalhes, responsáveis e comprovativos.</p>
        </div>
        <div class="eh-stat">
            <span class="eh-number"><?= count($pedidos) ?></span>
            <span class="eh-label">Total</span>
        </div>
    </div>

    <?php if (count($pedidos) > 0): ?>
        <div class="cards-grid">
            <?php foreach ($pedidos as $p): ?>
                <div class="pedido-card <?= $p['estado'] ?>">
                    <div class="pc-top">
                        <div class="pc-user">
                            <div class="avatar">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                            <div class="u-meta">
                                <span class="u-name"><?= htmlspecialchars($p['operador_nome']) ?></span>
                                <span class="u-tipo"><?= ucfirst(str_replace('_',' ',$p['tipo'])) ?></span>
                            </div>
                        </div>
                        <span class="status <?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span>
                    </div>
                    <div class="pc-dates">
                        <div><span class="lbl">Início</span><span class="val"><?= date('d/m/Y', strtotime($p['data_inicio'])) ?></span></div>
                        <div><span class="lbl">Fim</span><span class="val"><?= date('d/m/Y', strtotime($p['data_fim'])) ?></span></div>
                        <div><span class="lbl">Pedido</span><span class="val"><?= date('d/m/Y', strtotime($p['criado_em'])) ?></span></div>
                    </div>
                    <?php if (!empty($p['responsavel_nome'])): ?>
                    <div class="pc-row resp">
                        <span class="lbl">Responsável</span>
                        <span class="val"><?= htmlspecialchars($p['responsavel_nome']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="pc-row">
                        <span class="lbl">Decidido por</span>
                        <span class="val"><?= htmlspecialchars($p['decidido_por_nome'] ?? '—') ?></span>
                    </div>
                    <div class="pc-just">
                        <span class="lbl">Descrição</span>
                        <div class="just-box"><?= $p['justificacao'] !== '' ? nl2br(htmlspecialchars($p['justificacao'])) : '<em>Sem descrição</em>' ?></div>
                    </div>
                    <div class="pc-actions">
                        <?php if ($p['ficheiro']): ?>
                            <a class="proof-link" target="_blank" href="../../uploads/<?= $p['ficheiro'] ?>">Comprovativo</a>
                        <?php else: ?>
                            <span class="no-proof">Sem comprovativo</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state modern">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <h3>Sem pedidos</h3>
                <p>Não existem pedidos concluídos para mostrar.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.estrela-consulta {padding:1.5rem 1.75rem; background:#f8fafc;}
.estrela-header {display:flex; align-items:center; background:#fff; border:1px solid #e2e8f0; padding:1.25rem 1.5rem; border-radius:16px; gap:1.25rem; margin-bottom:1.75rem; box-shadow:0 2px 8px rgba(0,0,0,.04);} 
.eh-icon {width:54px; height:54px; background:linear-gradient(135deg,#fbbf24,#f59e0b); border-radius:14px; display:flex; align-items:center; justify-content:center; color:#fff; box-shadow:0 6px 18px rgba(245,158,11,.35);} 
.eh-text h2 {margin:0 0 .35rem; font-size:1.4rem; font-weight:700; color:#0f172a; letter-spacing:-.5px;} 
.eh-text p {margin:0; font-size:.85rem; color:#475569;} 
.eh-stat {margin-left:auto; text-align:center;} 
.eh-number {display:block; font-size:1.9rem; font-weight:700; background:linear-gradient(135deg,#fbbf24,#f59e0b); -webkit-background-clip:text; color:transparent;} 
.eh-label {font-size:.65rem; text-transform:uppercase; letter-spacing:.08em; color:#92400e; font-weight:600;} 
.cards-grid {display:grid; grid-template-columns:repeat(auto-fill,minmax(330px,1fr)); gap:1.25rem;} 
.pedido-card {background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:1.1rem 1.1rem 0.9rem; display:flex; flex-direction:column; gap:.65rem; position:relative; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); transition:.25s;} 
.pedido-card::before {content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg,#fbbf24,#f59e0b);} 
.pedido-card.aprovado::before {background:linear-gradient(90deg,#16a34a,#4ade80);} 
.pedido-card.rejeitado::before {background:linear-gradient(90deg,#dc2626,#f87171);} 
.pedido-card:hover {transform:translateY(-3px); box-shadow:0 6px 16px rgba(0,0,0,.12);} 
.pc-top {display:flex; justify-content:space-between; align-items:center;} 
.pc-user {display:flex; gap:.65rem; align-items:center;} 
.avatar {width:38px; height:38px; background:linear-gradient(135deg,#334155,#0f172a); border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff;} 
.u-name {display:block; font-size:.85rem; font-weight:600; color:#0f172a;} 
.u-tipo {font-size:.65rem; text-transform:uppercase; letter-spacing:.06em; color:#64748b; font-weight:600;} 
.status {font-size:.6rem; text-transform:uppercase; letter-spacing:.09em; font-weight:700; padding:.35rem .6rem; border-radius:999px; background:#e2e8f0; color:#334155;} 
.status.aprovado {background:#dcfce7; color:#166534;} 
.status.rejeitado {background:#fee2e2; color:#b91c1b;} 
.pc-dates {display:grid; grid-template-columns:repeat(3,1fr); gap:.5rem; background:#f1f5f9; border:1px solid #e2e8f0; padding:.55rem .65rem; border-radius:10px;} 
.pc-dates .lbl {display:block; font-size:.55rem; text-transform:uppercase; letter-spacing:.08em; color:#64748b; font-weight:700; margin-bottom:2px;} 
.pc-dates .val {font-size:.7rem; font-weight:600; color:#0f172a;} 
.pc-row {display:flex; justify-content:space-between; font-size:.7rem; padding:.25rem .5rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;} 
.pc-row.resp {background:#fff3e6; border-color:#fed7aa;} 
.pc-row .lbl {font-weight:600; color:#475569;} 
.pc-row .val {font-weight:600; color:#0f172a;} 
.pc-just {display:flex; flex-direction:column; gap:.3rem;} 
.pc-just .lbl {font-size:.55rem; text-transform:uppercase; letter-spacing:.08em; font-weight:700; color:#64748b;} 
.just-box {background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:.5rem .6rem; font-size:.65rem; line-height:1.1rem; max-height:80px; overflow:auto; color:#334155;} 
.pc-actions {margin-top:.2rem; display:flex; justify-content:flex-end;} 
.proof-link {display:inline-flex; align-items:center; gap:.4rem; font-size:.6rem; padding:.4rem .65rem; border-radius:8px; background:#1e3a8a; color:#fff; text-decoration:none; font-weight:600; letter-spacing:.05em; box-shadow:0 2px 6px rgba(30,58,138,.35); transition:.2s;} 
.proof-link:hover {background:#1d4ed8;} 
.no-proof {font-size:.55rem; color:#94a3b8; font-style:italic;} 
.empty-state.modern {background:#fff; border:1px dashed #cbd5e1; border-radius:16px; padding:3rem 1.5rem; box-shadow:0 2px 8px rgba(0,0,0,.04);} 
.empty-state.modern h3 {margin:.75rem 0 .35rem; font-size:1.1rem; font-weight:600; color:#0f172a;} 
.empty-state.modern p {margin:0; font-size:.75rem; color:#475569;} 
@media (max-width:700px){ .pc-dates {grid-template-columns:repeat(2,1fr);} .cards-grid {grid-template-columns:1fr;} }
</style>
