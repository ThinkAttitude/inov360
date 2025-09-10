<?php
// Página Frota - Admin RH (estático para já)
?>
<section class="fleet">
  <div class="main-header">
    <h2>Frota</h2>
    <p>Gestão da frota de veículos da organização.</p>
    <div class="header-actions">
      <button class="btn btn-primary" id="fleet-create-btn">Adicionar Veículo</button>
    </div>
  </div>

  <div class="fleet-grid">
    <article class="fleet-card" data-id="1">
  <span class="fleet-status status-atribuido">Atribuido</span>
      <img src="../../assets/carros/bmw_520d.png" alt="Carro 1" loading="lazy" />
      <div class="fleet-meta">
        <h4>BMW 520d</h4>
        <p class="muted">Matrícula: AA-00-AA</p>
      </div>
      <button class="btn btn-primary fleet-detail-btn" data-id="1">Ver detalhes</button>
    </article>

    <article class="fleet-card" data-id="2">
  <span class="fleet-status status-inspecao">Inspeção</span>
      <img src="../../assets/carros/opel_movano.png" alt="Carro 2" loading="lazy" />
      <div class="fleet-meta">
        <h4>Opel Movano</h4>
        <p class="muted">Matrícula: BB-11-BB</p>
      </div>
      <button class="btn btn-primary fleet-detail-btn" data-id="2">Ver detalhes</button>
    </article>

    <article class="fleet-card" data-id="3">
  <span class="fleet-status status-livre">Livre</span>
      <img src="../../assets/carros/skoda_kamiq.png" alt="Carro 3" loading="lazy" />
      <div class="fleet-meta">
        <h4>Skoda Kamiq</h4>
        <p class="muted">Matrícula: CC-22-CC</p>
      </div>
      <button class="btn btn-primary fleet-detail-btn" data-id="3">Ver detalhes</button>
    </article>
  </div>

  

  <!-- Modal Criar Veículo -->
  <div class="fleet-modal" id="fleet-create-modal" aria-hidden="true" style="display:none;">
    <div class="fleet-modal-backdrop" data-close></div>
    <div class="fleet-modal-content" role="dialog" aria-modal="true" aria-labelledby="fleetCreateTitle">
      <button class="fleet-modal-close" data-close aria-label="Fechar">×</button>
      <h3 id="fleetCreateTitle">Novo Veículo</h3>

      <form id="fleet-create-form">
        <div class="fleet-sections">
          <section>
            <h4>Imagem</h4>
            <div class="form-group">
              <label class="form-label" for="fleet-image-file">Carregar imagem (PNG/JPG)</label>
              <input class="form-input" type="file" id="fleet-image-file" accept="image/*">
            </div>
            <div class="form-group">
              <label class="form-label" for="fleet-image-url">Ou URL da imagem</label>
              <input class="form-input" type="url" id="fleet-image-url" placeholder="https://...">
            </div>
            <div class="form-group">
              <img id="fleet-create-preview" alt="Pré-visualização" style="max-width:100%;max-height:180px;border-radius:12px;background:#ffffff66;display:none;" />
            </div>
          </section>

          <section>
            <h4>Dados do Veículo</h4>
            <div class="form-group">
              <label class="form-label" for="marca">Marca</label>
              <input class="form-input" type="text" id="marca" name="marca" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="modelo">Modelo</label>
              <input class="form-input" type="text" id="modelo" name="modelo" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="matricula">Matrícula</label>
              <input class="form-input" type="text" id="matricula" name="matricula" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="ano">Ano Matrícula</label>
              <input class="form-input" type="text" id="ano" name="ano" required>
            </div>
          </section>

          <section>
            <h4>Dados Financeiros do Veículo</h4>
            <div class="form-group">
              <label class="form-label" for="tipo_contrato">Tipo de Contrato</label>
              <select class="form-input" id="tipo_contrato" name="tipo_contrato" required>
                <option value="Leasing">Leasing</option>
                <option value="ALD">ALD</option>
                <option value="Próprio">Próprio</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="num_contrato">Nº Contrato</label>
              <input class="form-input" type="text" id="num_contrato" name="num_contrato">
            </div>
            <div class="form-group">
              <label class="form-label" for="locadora">Locadora</label>
              <input class="form-input" type="text" id="locadora" name="locadora">
            </div>
          </section>

          <section>
            <h4>Seguros</h4>
            <div class="form-group"><label class="form-label" for="seguradora">Seguradora</label><input class="form-input" type="text" id="seguradora" name="seguradora"></div>
            <div class="form-group"><label class="form-label" for="apolice">Apólice Nº</label><input class="form-input" type="text" id="apolice" name="apolice"></div>
            <div class="form-group"><label class="form-label" for="carta_verde">Nº Carta Verde</label><input class="form-input" type="text" id="carta_verde" name="carta_verde"></div>
            <div class="form-group"><label class="form-label" for="valido_de">Válida de</label><input class="form-input" type="date" id="valido_de" name="valido_de"></div>
            <div class="form-group"><label class="form-label" for="valido_ate">Válida até</label><input class="form-input" type="date" id="valido_ate" name="valido_ate"></div>
            <div class="form-group"><label class="form-label" for="agencia">Agência</label><input class="form-input" type="text" id="agencia" name="agencia"></div>
            <div class="form-group"><label class="form-label" for="ag_nome">Nome</label><input class="form-input" type="text" id="ag_nome" name="ag_nome"></div>
            <div class="form-group"><label class="form-label" for="ag_morada">Morada</label><input class="form-input" type="text" id="ag_morada" name="ag_morada"></div>
            <div class="form-group"><label class="form-label" for="ag_cp">Cod. Postal</label><input class="form-input" type="text" id="ag_cp" name="ag_cp"></div>
            <div class="form-group"><label class="form-label" for="ag_tel">Tel.</label><input class="form-input" type="text" id="ag_tel" name="ag_tel"></div>
            <div class="form-group"><label class="form-label" for="ag_mail">Mail</label><input class="form-input" type="email" id="ag_mail" name="ag_mail"></div>
            <div class="form-group">
              <label class="form-label" for="danos_materiais">Danos Materiais cobertos?</label>
              <select class="form-input" id="danos_materiais" name="danos_materiais">
                <option value="true">Sim</option>
                <option value="false">Não</option>
              </select>
            </div>
          </section>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;">
          <button type="button" class="btn btn-secondary" data-close>Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Veículo</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Detalhes do Veículo -->
  <div class="fleet-modal" id="fleet-modal" aria-hidden="true" style="display:none;">
    <div class="fleet-modal-backdrop" data-close></div>
    <div class="fleet-modal-content" role="dialog" aria-modal="true" aria-labelledby="fleetModalTitle">
      <button class="fleet-modal-close" data-close aria-label="Fechar">×</button>
      <h3 id="fleetModalTitle">Detalhes do Veículo</h3>

      <div class="fleet-sections">
        <section>
          <h4>Dados do Veículo</h4>
          <dl class="kv">
            <div><dt>Veículo</dt><dd data-field="veiculo"></dd></div>
            <div><dt>Marca</dt><dd data-field="marca"></dd></div>
            <div><dt>Modelo</dt><dd data-field="modelo"></dd></div>
            <div><dt>Matrícula</dt><dd data-field="matricula"></dd></div>
            <div><dt>Ano Matrícula</dt><dd data-field="ano"></dd></div>
          </dl>
        </section>

        <section>
          <h4>Dados Financeiros do Veículo</h4>
          <dl class="kv">
            <div><dt>Tipo de Contrato</dt><dd data-field="tipo_contrato"></dd></div>
            <div><dt>Nº Contrato</dt><dd data-field="num_contrato"></dd></div>
            <div><dt>Locadora</dt><dd data-field="locadora"></dd></div>
          </dl>
        </section>

        <section>
          <h4>Seguros</h4>
          <dl class="kv">
            <div><dt>Seguradora</dt><dd data-field="seguradora"></dd></div>
            <div><dt>Apólice Nº</dt><dd data-field="apolice"></dd></div>
            <div><dt>Nº Carta Verde</dt><dd data-field="carta_verde"></dd></div>
            <div><dt>Válida de</dt><dd data-field="valido_de"></dd></div>
            <div><dt>Válida até</dt><dd data-field="valido_ate"></dd></div>
            <div><dt>Agência</dt><dd data-field="agencia"></dd></div>
            <div><dt>Nome</dt><dd data-field="ag_nome"></dd></div>
            <div><dt>Morada</dt><dd data-field="ag_morada"></dd></div>
            <div><dt>Cod. Postal</dt><dd data-field="ag_cp"></dd></div>
            <div><dt>Tel.</dt><dd data-field="ag_tel"></dd></div>
            <div><dt>Mail</dt><dd data-field="ag_mail"></dd></div>
            <div><dt>Danos Materiais Cobertos?</dt><dd data-field="danos_materiais"></dd></div>
          </dl>
        </section>
      </div>
    </div>
  </div>

  <script type="application/json" id="fleet-data">[
    {"id":1,"veiculo":"BMW 520d","marca":"BMW","modelo":"520d","matricula":"AA-00-AA","ano":"2020","tipo_contrato":"Leasing","num_contrato":"LS-2020-1001","locadora":"LeasePlan","seguradora":"Fidelidade","apolice":"AP-520-0001","carta_verde":"CV-520-0001","valido_de":"01/01/2025","valido_ate":"31/12/2025","agencia":"Agência Central","ag_nome":"João Silva","ag_morada":"Av. Central 100","ag_cp":"1000-001 Lisboa","ag_tel":"+351 210 000 000","ag_mail":"joao.silva@agencia.pt","danos_materiais":true,"status":"Atribuido"},
    {"id":2,"veiculo":"Opel Movano","marca":"Opel","modelo":"Movano","matricula":"BB-11-BB","ano":"2021","tipo_contrato":"ALD","num_contrato":"ALD-2021-0442","locadora":"ALD Automotive","seguradora":"Allianz","apolice":"ALL-556677","carta_verde":"CV-556677","valido_de":"15/02/2025","valido_ate":"14/02/2026","agencia":"Agência Norte","ag_nome":"Maria Pereira","ag_morada":"Rua das Flores 25","ag_cp":"4000-222 Porto","ag_tel":"+351 220 000 000","ag_mail":"maria.pereira@agencia.pt","danos_materiais":false,"status":"Inspeção"},
    {"id":3,"veiculo":"Skoda Kamiq","marca":"Skoda","modelo":"Kamiq","matricula":"CC-22-CC","ano":"2022","tipo_contrato":"Próprio","num_contrato":"-","locadora":"-","seguradora":"Tranquilidade","apolice":"TR-998877","carta_verde":"CV-998877","valido_de":"01/04/2025","valido_ate":"31/03/2026","agencia":"Agência Sul","ag_nome":"Rui Gomes","ag_morada":"Praça do Sul 12","ag_cp":"8000-100 Faro","ag_tel":"+351 289 000 000","ag_mail":"rui.gomes@agencia.pt","danos_materiais":true,"status":"Livre"}
  ]</script>
  
  <!-- Script de controlo dos modais da frota -->
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const createBtn = document.getElementById('fleet-create-btn');
    const createModal = document.getElementById('fleet-create-modal');
    const detailModal = document.getElementById('fleet-modal');

    function anyModalOpen() {
      return Array.from(document.querySelectorAll('.fleet-modal')).some(m => m.style.display === 'flex');
    }

    function openFleetModal(modal) {
      if (!modal) return;
      modal.style.display = 'flex';
      modal.setAttribute('aria-hidden', 'false');
      body.classList.add('modal-open');
    }

    function closeFleetModal(modal) {
      if (!modal) return;
      modal.style.display = 'none';
      modal.setAttribute('aria-hidden', 'true');
      if (!anyModalOpen()) body.classList.remove('modal-open');
    }

    // Botão criar
    createBtn?.addEventListener('click', () => openFleetModal(createModal));

    // Botões detalhes (placeholder: apenas abre modal; lógica de preenchimento pode ser adicionada depois)
    document.querySelectorAll('.fleet-detail-btn').forEach(btn => {
      btn.addEventListener('click', () => openFleetModal(detailModal));
    });

    // Fechar via elementos com data-close (botão X e backdrop)
    document.querySelectorAll('.fleet-modal [data-close]').forEach(el => {
      el.addEventListener('click', (e) => {
        const modal = el.closest('.fleet-modal');
        closeFleetModal(modal);
      });
    });

    // ESC fecha todos
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.fleet-modal').forEach(m => {
          if (m.style.display === 'flex') closeFleetModal(m);
        });
      }
    });
  });
  </script>
</section>
