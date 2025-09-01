<?php
require_once __DIR__ . "/../includes/db.php";

function sincronizarPedidoEvento($pedidoId) {
    $pdo = db_connect();

    // Buscar o pedido aprovado com os dados do utilizador
    $stmt = $pdo->prepare("
        SELECT p.*, u.name AS nome_colaborador
        FROM pedidos_ferias p
        JOIN user u ON p.user_id = u.id
        WHERE p.id = ? AND p.estado = 'aprovado'
    ");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch();

    if ($pedido) {
        // Inserir evento no calendário do próprio utilizador (se não existir)
        $stmtCheck = $pdo->prepare("
            SELECT id 
            FROM eventos 
            WHERE operador_id = ? AND data_inicio = ? AND data_fim = ? AND tipo = ?
        ");
        $stmtCheck->execute([
            $pedido['user_id'],
            $pedido['data_inicio'],
            $pedido['data_fim'],
            $pedido['tipo']
        ]);

        if (!$stmtCheck->fetch()) {
            $stmtInsert = $pdo->prepare("
                INSERT INTO eventos (titulo, data_inicio, data_fim, tipo, operador_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $titulo = ucfirst($pedido['tipo']) . " aprovado(a)";
            $stmtInsert->execute([
                $titulo,
                $pedido['data_inicio'],
                $pedido['data_fim'],
                $pedido['tipo'],
                $pedido['user_id']
            ]);
        }

        // Inserir evento no calendário do responsável, se existir
        if (!empty($pedido['responsavel_id'])) {
            $stmtCheckResponsavel = $pdo->prepare("
                SELECT id 
                FROM eventos 
                WHERE operador_id = ? AND data_inicio = ? AND data_fim = ? AND tipo = ?
            ");
            $stmtCheckResponsavel->execute([
                $pedido['responsavel_id'],
                $pedido['data_inicio'],
                $pedido['data_fim'],
                'substituicao'
            ]);

            if (!$stmtCheckResponsavel->fetch()) {
                $stmtInsertResponsavel = $pdo->prepare("
                    INSERT INTO eventos (titulo, data_inicio, data_fim, tipo, operador_id)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $tituloResponsavel = "Substituição de " . $pedido['nome_colaborador'];
                $stmtInsertResponsavel->execute([
                    $tituloResponsavel,
                    $pedido['data_inicio'],
                    $pedido['data_fim'],
                    'substituicao',
                    $pedido['responsavel_id']
                ]);
            }
        }
    }
}

