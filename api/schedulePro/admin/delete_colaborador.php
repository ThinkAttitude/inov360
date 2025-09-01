<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

// Apenas admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? null) !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

// Aceita DELETE (ou POST, se precisares)
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido. Use DELETE.']);
    exit;
}

// ID pode vir em JSON, form ou query string
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) $in = $_POST;
$id = isset($in['id']) ? (int)$in['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetro obrigatório: id.']);
    exit;
}

// (Opcional) impedir apagar a própria conta admin logada
if (!empty($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === $id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Não pode eliminar a sua própria conta.']);
    exit;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Existe?
    $stmt = $pdo->prepare('SELECT id FROM user WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Colaborador não encontrado.']);
        exit;
    }

    // TODO: se tiveres referências (horários, etc.), decide política (cascade, bloqueio, anonimização)
    $stmt = $pdo->prepare('DELETE FROM user WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Colaborador eliminado com sucesso.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
}
