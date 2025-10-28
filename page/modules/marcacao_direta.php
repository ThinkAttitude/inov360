<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcação Direta - RH360</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="../../css/marcacao_direta.css">
</head>
<body>
<div class="container">
    <h2>Marcação Direta de Férias / Ausências</h2>
    <p>Utilize este formulário para marcar férias ou ausências diretamente para colaboradores.</p>

    <div class="form-card">
        <form id="form-marcacao" >
            <div class="input-group">
                <label for="colaborador">Colaborador *</label>
                <select name="colaborador_id" id="colaborador" required>
                    <option value="">-- Selecione o colaborador --</option>
                </select>
            </div>

            <div class="input-group">
                <label for="tipo">Tipo de Pedido *</label>
                <select name="tipo" id="tipo" required>
                    <option value="">-- Selecione o tipo --</option>
                    <option value="licenca_paternidade">Licença de Paternidade</option>
                    <option value="licenca_maternidade">Licença de Maternidade</option>
                    <option value="baixa_medica">Baixa Médica</option>
                    <option value="baixa_seguro">Baixa Seguro</option>
                    <option value="casamento">Casamento</option>
                    <option value="consulta_medica">Consulta Médica</option>
                    <option value="ferias">Férias</option>
                    <option value="pessoal">Assunto Pessoal</option>
                </select>
            </div>

            <div class="form-grid">
                <div class="input-group">
                    <label for="data_inicio">Data de Início *</label>
                    <input type="date" name="data_inicio" id="data_inicio" required>
                </div>

                <div class="input-group">
                    <label for="data_fim">Data de Fim *</label>
                    <input type="date" name="data_fim" id="data_fim" required>
                </div>
            </div>

            <div class="input-group">
                <label for="justificacao">Justificação *</label>
                <textarea name="justificacao" id="justificacao" rows="3" placeholder="Descreva a justificação para a ausência..." required></textarea>
            </div>

            <div class="input-group">
                <label for="ficheiro">Comprovativo (Obrigatório)</label>
                <input type="file" name="ficheiro" id="ficheiro" accept=".pdf,.jpg,.png,.jpeg" required>
                <small style="color: var(--text-light); font-size: 0.75rem; margin-top: 0.5rem; line-height:1.3; display:block;">
                    Formatos aceitos: PDF, JPG, PNG (máximo 5MB). Campo obrigatório em marcação direta RH.
                </small>
            </div>

            <button type="submit" class="btn">
                <span class="btn-text">Marcar Ausência</span>
            </button>
        </form>

        <div id="feedback-message"></div>
    </div>
</div>

<script>
    // Load collaborators via API
    async function loadColaboradores() {
        try {
            const response = await fetch('../../api/users/get_colaboradores.php');
            const data = await response.json();

            const select = document.getElementById('colaborador');
            data.forEach(col => {
                const option = document.createElement('option');
                option.value = col.id;
                option.textContent = `${col.name} (${col.role.charAt(0).toUpperCase() + col.role.slice(1)})`;
                select.appendChild(option);
            });
        } catch (error) {
            document.getElementById('feedback-message').innerHTML =
                '<div class="feedback-message error">Erro ao carregar colaboradores: ' + error.message + '</div>';
        }
    }

    document.getElementById('form-marcacao').addEventListener('submit', function(e) {
        const btn = this.querySelector('.btn');
        const btnText = btn.querySelector('.btn-text');
        const feedbackDiv = document.getElementById('feedback-message');

        // Loading state
        btn.classList.add('loading');
        btnText.textContent = 'Processando...';
        feedbackDiv.innerHTML = '';

        // Validação básica de datas
        const dataInicio = document.getElementById('data_inicio').value;
        const dataFim = document.getElementById('data_fim').value;

        if (dataInicio && dataFim && new Date(dataInicio) > new Date(dataFim)) {
            e.preventDefault();
            feedbackDiv.innerHTML = '<div class="feedback-message error">A data de início deve ser anterior à data de fim.</div>';
            btn.classList.remove('loading');
            btnText.textContent = 'Marcar Ausência';
            return;
        }

        // O formulário será enviado normalmente via POST
        // Removemos o loading state após um tempo para permitir o redirect
        setTimeout(() => {
            btn.classList.remove('loading');
            btnText.textContent = 'Marcar Ausência';
        }, 1000);
    });

    // Validação de arquivo (apenas se fornecido ou se for obrigatório)
    document.getElementById('ficheiro').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const feedbackDiv = document.getElementById('feedback-message');

        if (file) {
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!allowedTypes.includes(file.type)) {
                feedbackDiv.innerHTML = '<div class="feedback-message error">Tipo de arquivo não permitido. Use PDF, JPG ou PNG.</div>';
                e.target.value = '';
                return;
            }

            if (file.size > maxSize) {
                feedbackDiv.innerHTML = '<div class="feedback-message error">Arquivo muito grande. Máximo 5MB permitido.</div>';
                e.target.value = '';
                return;
            }

            feedbackDiv.innerHTML = '<div class="feedback-message success">Arquivo válido selecionado.</div>';
        }
    });

    // Auto-preenchimento da data mínima (hoje)
    document.addEventListener('DOMContentLoaded', function() {
        loadColaboradores();

        const hoje = new Date().toISOString().split('T')[0];
        document.getElementById('data_inicio').setAttribute('min', hoje);
        document.getElementById('data_fim').setAttribute('min', hoje);
    });

    // Atualizar data mínima de fim baseada na data de início
    document.getElementById('data_inicio').addEventListener('change', function() {
        const dataInicio = this.value;
        document.getElementById('data_fim').setAttribute('min', dataInicio);
    });
</script>
</body>
</html>
