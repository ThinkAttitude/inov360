<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Colaborador - RH360</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="../../css/legacy/criar_colaborador.css">
</head>
<body>
    <div class="container">
        <h2>Criar Colaborador</h2>

        <div class="form-card">
            <form id="form-colaborador" action="javascript:void(0);">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nome">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" placeholder="Digite o nome completo" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="exemplo@empresa.com" name="email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="role">Função</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Selecione a função</option>
                            <option value="opera">Operador</option>
                            <option value="inter2">Intermédio 2</option>
                            <option value="inter">Intermédio</option>
                            <option value="admin">Admin</option>
                            <option value="*">Estrela (*)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="empresa">Empresa</label>
                        <select class="form-control" id="empresa" name="empresa" required>
                            <option value="">A Carregar empresas...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="senha">Senha</label>
                        <input type="password" class="form-control" id="senha" placeholder="Digite a senha" name="senha" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <span class="btn-text">Criar Colaborador</span>
                        </button>
                    </div>
                </div>
            </form>

            <div id="mensagem-criacao"></div>
        </div>
    </div>

    <!-- JavaScript é gerido pelo dashboard_admin_rh.js -->
</body>
</html>
