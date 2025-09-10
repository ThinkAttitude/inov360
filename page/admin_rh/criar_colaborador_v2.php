<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Criar Colaborador V2</title>
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="../../css/criar_colaborador_v2.css">
</head>
<body>
<div class="ccv2-wrapper">
    <div class="ccv2-header">
        <h2>Criar Colaborador V2</h2>
    </div>

    <div class="ccv2-layout">
        <div class="ccv2-panel ccv2-create">
            <h3 class="panel-title">1. Criar Conta Básica</h3>
            <form id="ccv2-form" autocomplete="off" action="javascript:void(0);">
                <div class="form-grid">
                    <label class="fg-item">
                        <span>Nome Completo</span>
                        <input type="text" name="nome" placeholder="Ex: Ana Silva" required>
                    </label>
                    <label class="fg-item">
                        <span>Email</span>
                        <input type="email" name="email" placeholder="email@empresa.com" required>
                    </label>
                    <label class="fg-item">
                        <span>Password</span>
                        <input type="password" name="password" placeholder="••••••" required>
                    </label>
                </div>
                <button type="submit" class="btn-primary ccv2-submit">
                    <span class="label">Criar Conta</span>
                    <span class="spinner" aria-hidden="true"></span>
                </button>
                <p class="help-text">Ao criar a conta são atribuídas automaticamente as permissões base.</p>
            </form>
        </div>

        <div class="ccv2-panel ccv2-list">
            <h3 class="panel-title">2. Colaboradores Criados</h3>
            <div id="ccv2-empty" class="empty-state">
                <p>Sem colaboradores ainda.</p>
                <p class="hint">Crie um na esquerda para começar.</p>
            </div>
            <div id="ccv2-cards" class="ccv2-cards"></div>
        </div>
    </div>

    <!-- Modal Detalhe / Permissões -->
    <div id="ccv2-modal" class="ccv2-modal" aria-hidden="true">
        <div class="ccv2-modal-backdrop" data-close></div>
        <div class="ccv2-modal-dialog" role="dialog" aria-modal="true">
            <div class="modal-header">
                <h4 id="ccv2-modal-title">Permissões do Colaborador</h4>
                <button class="close-btn" data-close>&times;</button>
            </div>
            <div class="modal-body">
                <div class="perm-block">
                    <h5>Permissões Base (sempre ativas)</h5>
                    <ul class="perm-list base">
                        <li>Início</li>
                        <li>Consulta de Horários</li>
                        <li>Férias/Ausências</li>
                        <li>A Minha Ficha</li>
                        <li>Consulta de Pedidos</li>
                    </ul>
                </div>
                <div class="perm-block">
                    <h5>Permissões Adicionais</h5>
                    <form id="ccv2-perms-form">
                        <div class="perm-grid">
                            <label><input type="checkbox" name="criar_users" value="1"> Criar Users</label>
                            <label><input type="checkbox" name="aprovar_alteracoes_ficha" value="1"> Aprovar Alterações em Ficha</label>
                            <label><input type="checkbox" name="edicao_completa_ficha" value="1"> Edição Completa Ficha Colaborador</label>
                            <label><input type="checkbox" name="edicao_financeira_ficha" value="1"> Edição Completa Ficha Financeira</label>
                            <label><input type="checkbox" name="download_mapa_horarios" value="1"> Download Excel Mapa Horários</label>
                            <label><input type="checkbox" name="marcacao_direta_fa" value="1"> Marcação Direta Férias/Ausências</label>
                            <label><input type="checkbox" name="gestao_frota" value="1"> Gestão de Frota</label>
                            <label><input type="checkbox" name="higiene_seguranca" value="1"> Higiene e Segurança no Trabalho</label>
                        </div>
                        <button type="submit" class="btn-save-perms">
                            <span class="label">Guardar Alterações</span>
                            <span class="spinner" aria-hidden="true"></span>
                        </button>
                        <p class="save-status" id="ccv2-save-status"></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="ccv2-hierarchy-wrapper">
        <div class="hier-toolbar">
            <div class="left">
                <button id="ccv2-link-mode" class="btn-tool" data-active="false" title="Ativar/Desativar modo ligação">
                    <span class="btn-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </span>
                    <span class="btn-label">Modo Ligação</span>
                </button>
                <button id="ccv2-save-hierarchy" class="btn-tool primary" title="Guardar hierarquia (demo)">
                    <span class="btn-ico" aria-hidden="true">
                        <!-- Novo ícone: combinação de 'guardar' + mini hierarquia -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <!-- Corpo (disquete/documento) -->
                            <path d="M5 3h11l4 4v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                            <path d="M5 8h14"/>
                            <!-- Hierarquia simplificada -->
                            <circle cx="12" cy="13" r="2"/>
                            <circle cx="8.5" cy="18" r="1.5"/>
                            <circle cx="15.5" cy="18" r="1.5"/>
                            <path d="M12 15v1.5"/>
                            <path d="M11 18h-1.5"/>
                            <path d="M13 18h1.5"/>
                        </svg>
                    </span>
                    <span class="btn-label">Guardar Hierarquia</span>
                </button>
                <button id="ccv2-clear-hierarchy" class="btn-tool danger" title="Limpar apenas o canvas (hierarquia guardada mantém-se)">
                    <span class="btn-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </span>
                    <span class="btn-label">Limpar Canvas</span>
                </button>
                <button id="ccv2-clear-links" class="btn-tool danger" title="Remover ligações hierárquicas dos colaboradores presentes no canvas (mantém os cartões lá)">
                    <span class="btn-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3v2"/><path d="M15 3v2"/><path d="M4 7h16"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M5 7l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/></svg>
                    </span>
                    <span class="btn-label">Limpar Hierarquia</span>
                </button>
            </div>
            <div class="right">
                <div class="zoom-controls" aria-label="Zoom">
                    <button type="button" class="zoom-btn" id="ccv2-zoom-out" title="Zoom Out" aria-label="Reduzir (-)">−</button>
                    <button type="button" class="zoom-btn" id="ccv2-zoom-in" title="Zoom In" aria-label="Ampliar (+)">+</button>
                </div>
                <span class="hint">Arraste cartões para a área abaixo. Duplo clique num supervisor e depois clique no subordinado para ligar. Pode ter vários supervisores.</span>
            </div>
        </div>
        <div id="ccv2-hierarchy-canvas" class="hier-canvas" tabindex="0">
            <svg id="ccv2-hierarchy-links" class="hier-links" xmlns="http://www.w3.org/2000/svg"></svg>
            <div class="hier-placeholder" id="ccv2-hier-placeholder">Arraste aqui os colaboradores…</div>
        </div>
    <!-- Legend removida conforme pedido -->
    </div>
</div>
</body>
</html>
