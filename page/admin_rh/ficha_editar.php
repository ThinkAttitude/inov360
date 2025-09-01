<?php
session_start();
require_once "../../api/includes/db.php";

if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    echo "<p>Acesso negado.</p>";
    exit;
}

if (!isset($_GET["user_id"])) {
    echo "<p>ID do colaborador não fornecido.</p>";
    exit;
}

$user_id = intval($_GET["user_id"]);

try {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT * FROM colaborador_dados WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar contacto de emergência (assume-se que existe só um)
    $stmt = $conn->prepare("SELECT nome, parentesco, telefone FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $contacto_emergencia = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$dados) {
        echo "<p>Ficha de colaborador não encontrada.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    exit;
}
?>
<button onclick="history.back()" class="btn" style="margin-bottom:20px;">⬅️ Voltar</button>

<h3>Editar Ficha do Colaborador</h3>

<form method="POST" action="../../api/pedidos/arh_guardar_ficha.php" id="formEditarSimples">
    <input type="hidden" name="user_id" value="<?= $user_id ?>">

    <?php foreach ($dados as $campo => $valor): ?>
        <?php if ($campo === "user_id") continue; ?>
        <div style="margin-bottom: 12px;">
            <label for="<?= $campo ?>"><strong><?= ucfirst(str_replace("_", " ", $campo)) ?>:</strong></label><br>

            <?php if ($campo === "estado_civil"): ?>
                <select name="<?= $campo ?>" id="<?= $campo ?>" style="width: 100%; padding: 8px;">
                    <?php
                    $opcoes_estado_civil = ['Solteiro', 'Casado', 'Viúvo', 'Divorciado', 'União de facto'];
                    foreach ($opcoes_estado_civil as $opcao) {
                        $selected = ($valor === $opcao) ? 'selected' : '';
                        echo "<option value=\"$opcao\" $selected>$opcao</option>";
                    }
                    ?>
                </select>
            <?php else: ?>
                <input
                        type="text"
                        id="<?= $campo ?>"
                        name="<?= $campo ?>"
                        value="<?= htmlspecialchars($valor ?? '') ?>"
                        style="width: 100%; padding: 8px;"
                >
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <hr>
    <h4>Contacto de Emergência</h4>

    <div style="margin-bottom: 12px;">
        <label for="emergencia_nome"><strong>Nome:</strong></label><br>
        <input type="text" id="emergencia_nome" name="emergencia_nome" value="<?= htmlspecialchars($contacto_emergencia["nome"] ?? '') ?>" style="width: 100%; padding: 8px;">
    </div>

    <div style="margin-bottom: 12px;">
        <label for="emergencia_parentesco"><strong>Parentesco:</strong></label><br>
        <input type="text" id="emergencia_parentesco" name="emergencia_parentesco" value="<?= htmlspecialchars($contacto_emergencia["parentesco"] ?? '') ?>" style="width: 100%; padding: 8px;">
    </div>

    <div style="margin-bottom: 12px;">
        <label for="emergencia_telefone"><strong>Telefone:</strong></label><br>
        <input type="text" id="emergencia_telefone" name="emergencia_telefone" value="<?= htmlspecialchars($contacto_emergencia["telefone"] ?? '') ?>" style="width: 100%; padding: 8px;">
    </div>

    <button class="editar-ficha-btn" data-user-id="1">Editar</button>
</form>
<script>
  (function(){
    const form = document.getElementById('formEditarSimples');
    if(!form) return;

    const moneyFields = ['salario_base','subsidio_alimentacao','ordenado_liquido'];

    function toCommaDecimal(v){
      if(v==null) return '';
      v = String(v).trim();
      if(v==='') return '';
      if(/,\d{1,2}$/.test(v)){
        v = v.replace(/\./g, '').replace(/,/g, '.');
      } else {
        v = v.replace(/\s+/g, '').replace(/,/g, '');
      }
      const n = parseFloat(v);
      if(isNaN(n)) return '';
      const result = n.toFixed(2).replace('.', ',');
      return result;
    }

    // Ensure better UX on numeric inputs
    moneyFields.forEach(name => {
      const input = form.querySelector(`[name="${name}"]`);
      if(input){
        input.setAttribute('inputmode','decimal');
        input.setAttribute('autocomplete','off');
        input.setAttribute('pattern','[0-9.,]*');
      }
    });

    // Format existing values on load
    moneyFields.forEach(name => {
      const input = form.querySelector(`[name="${name}"]`);
      if(input && input.value){
        const out = toCommaDecimal(input.value);
        if(out!=='') input.value = out;
      }
    });

    // Format on blur
    moneyFields.forEach(name => {
      const input = form.querySelector(`[name="${name}"]`);
      if(!input) return;
      input.addEventListener('blur', () => {
        const out = toCommaDecimal(input.value);
        if(out!=='') input.value = out;
      });
    });

    // Note: Form submission is handled by dashboard_admin_rh.js
    // which applies money field sanitization automatically
  })();
</script>
