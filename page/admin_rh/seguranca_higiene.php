<?php
// Página Segurança e Higiene (demo estático)
?>
<section class="shst">
  <div class="main-header">
    <h2>Segurança e Higiene</h2>
    <p>Estado documental de Segurança e Higiene no Trabalho por colaborador.</p>
  </div>

  <style>
    /* Estilos locais mínimos (não intrusivos) */
    .sh-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
    .sh-card { background: #fff; border-radius: 14px; padding: 14px; box-shadow: 0 8px 22px rgba(0,0,0,0.06); border: 1px solid rgba(17,24,39,0.06); display: flex; flex-direction: column; min-height: 260px; }
    .sh-head { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
    .sh-avatar { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #dbeafe, #e9d5ff); display: grid; place-items: center; font-weight: 600; color: #111827; }
    .sh-name { margin: 0; font-size: 16px; color: #111827; }
    .sh-role { margin: 0; color: #6b7280; font-size: 13px; }
    .sh-fields { margin-top: 8px; display: grid; gap: 6px; }
    .sh-field { display: flex; align-items: center; gap: 8px; font-size: 13px; }
    .sh-dot { width: 10px; height: 10px; border-radius: 50%; flex: 0 0 10px; }
    .ok .sh-dot { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
    .ok .sh-label { color: #065f46; }
    .missing .sh-dot { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.15); }
    .missing .sh-label { color: #7f1d1d; }
    .sh-footer { margin-top: auto; display:flex; justify-content: flex-end; }
    .sh-badge { font-size: 12px; color: #374151; background: #f3f4f6; border-radius: 999px; padding: 4px 8px; }
  </style>

  <div class="sh-grid" id="sh-grid">
    <?php
      $colabs = [
        ['nome' => 'João Silva', 'funcao' => 'Operacional'],
        ['nome' => 'Maria Pereira', 'funcao' => 'Técnica'],
        ['nome' => 'Rui Gomes', 'funcao' => 'Gruísta'],
        ['nome' => 'Ana Costa', 'funcao' => 'Manobradora'],
        ['nome' => 'Pedro Fernandes', 'funcao' => 'Eletricista'],
        ['nome' => 'Sofia Martins', 'funcao' => 'HST']
      ];
      $campos = [
  'Documento de identificação',
        'Comunicação de Vínculo SS',
        'Categoria profissional',
        'NIF',
  'FAM',
        'Registo de entrega de EPI',
        'Ficha de Trabalhador'
      ];
      foreach ($colabs as $c) {
        $iniciais = array_map(fn($p) => mb_substr($p,0,1), explode(' ', $c['nome']));
        $iniciais = mb_strtoupper(implode('', array_slice($iniciais,0,2)));
    ?>
    <article class="sh-card">
      <div class="sh-head">
        <div class="sh-avatar"><?php echo htmlspecialchars($iniciais); ?></div>
        <div>
          <h4 class="sh-name"><?php echo htmlspecialchars($c['nome']); ?></h4>
          <p class="sh-role"><?php echo htmlspecialchars($c['funcao']); ?></p>
        </div>
      </div>
      <div class="sh-fields">
        <?php foreach ($campos as $idx => $campo) { ?>
          <div class="sh-field" data-field-index="<?php echo $idx; ?>">
            <span class="sh-dot"></span>
            <span class="sh-label"><?php echo htmlspecialchars($campo); ?></span>
          </div>
        <?php } ?>
      </div>
      <div class="sh-footer">
        <span class="sh-badge" data-compliance>—</span>
      </div>
    </article>
    <?php } ?>
  </div>

  <script>
    // Random demo: marca alguns campos a verde/vermelho e calcula %
    (function(){
      const cards = document.querySelectorAll('.sh-card');
      cards.forEach(card => {
        const fields = card.querySelectorAll('.sh-field');
        let ok = 0;
        fields.forEach((f, i) => {
          const isOk = Math.random() > 0.35; // ~65% OK
          f.classList.toggle('ok', isOk);
          f.classList.toggle('missing', !isOk);
          if (isOk) ok++;
        });
        const pct = Math.round((ok / fields.length) * 100);
        const badge = card.querySelector('[data-compliance]');
        if (badge) badge.textContent = pct + '% completo';
      });
    })();
  </script>
</section>
