<?php
// api/finance/profile_export.php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); echo "UNAUTHENTICATED"; exit;
}
$role   = $_SESSION['user']['role'] ?? '';
$selfId = (int)($_SESSION['user']['id'] ?? 0);

function can_read(string $role, int $selfId, int $targetId): bool {
    if (in_array($role, ['adminrh','estrela'], true)) return true;
    return $selfId === $targetId;
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) { http_response_code(400); echo "MISSING_USER"; exit; }
if (!can_read($role,$selfId,$userId)) { http_response_code(403); echo "FORBIDDEN"; exit; }

require_once __DIR__ . '/../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* tentar obter nome do utilizador */
$nameCol = 'nome';
try { $pdo->query("SELECT $nameCol FROM user LIMIT 1"); }
catch(Throwable $e){ $nameCol = 'name'; }
$uq = $pdo->prepare("SELECT $nameCol AS nome,email FROM user WHERE id=:id LIMIT 1");
$uq->execute([':id'=>$userId]);
$u = $uq->fetch(PDO::FETCH_ASSOC) ?: ['nome'=>"user_$userId",'email'=>''];

/* fetch da ficha */
$st = $pdo->prepare("SELECT * FROM finance_profiles WHERE user_id=:u LIMIT 1");
$st->execute([':u'=>$userId]);
$row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

/* headers CSV */
$filename = "ficha_financeira_{$userId}.csv";
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

$fp = fopen('php://output', 'w');

/* linha de identificação */
fputcsv($fp, ['Colaborador', $u['nome']]);
fputcsv($fp, ['Email', $u['email']]);
fputcsv($fp, []);

/* colunas */
if (!$row) {
    fputcsv($fp, ['Sem dados de ficha financeira.']);
    fclose($fp); exit;
}
fputcsv($fp, array_keys($row));
fputcsv($fp, array_map(fn($v)=>is_bool($v)?(int)$v:$v, array_values($row)));
fclose($fp);
