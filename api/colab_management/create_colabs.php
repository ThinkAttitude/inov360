<?php
// api/create_colabs.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'code'=>'UNAUTHENTICATED']); exit;
}
$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(1, $perms, true)) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'code'=>'FORBIDDEN_PERMISSION']); exit;
}

require_once __DIR__ . '/../includes/db.php';

function read_payload(): array {
    $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ctype, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }
    // form-data: um único registo
    return [
        'nome'       => $_POST['nome']       ?? null,
        'email'      => $_POST['email']      ?? null,
        'password'   => $_POST['password']   ?? null,
        'company_id' => $_POST['company_id'] ?? ($_POST['company'] ?? null),
    ];
}
function norm_items($in): array {
    // aceita objeto único ou array de objetos
    if (isset($in['nome']) || isset($in['email']) || isset($in['password']) || isset($in['company_id']) || isset($in['company'])) {
        return [$in];
    }
    return is_array($in) ? $in : [];
}
function to_company_id($v): ?int {
    if ($v === '' || $v === null) return null;
    $n = (int)$v;
    return $n > 0 ? $n : null;
}

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $items = norm_items(read_payload());
    if (!$items) { http_response_code(400); echo json_encode(['ok'=>false,'code'=>'EMPTY_INPUT']); exit; }

    $created = [];
    $errors  = [];

    // prepared statements reutilizáveis
    $checkEmail   = $pdo->prepare("SELECT 1 FROM `user` WHERE email=? LIMIT 1");
    $checkCompany = $pdo->prepare("SELECT 1 FROM `company` WHERE id=? LIMIT 1");

    $insUser = $pdo->prepare("
        INSERT INTO `user` (name,email,password,company_id)
        VALUES (?,?,?,?)
    ");

    $insFicha = $pdo->prepare("
        INSERT INTO colaborador_dados (user_id, email)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE email = VALUES(email)
    ");

    $insEmerg = $pdo->prepare("
        INSERT INTO contactos_emergencia (user_id, nome, parentesco, telefone)
        VALUES (?, '', '', '')
        ON DUPLICATE KEY UPDATE user_id = user_id
    ");

    $insFinance = $pdo->prepare("
        INSERT INTO finance_profiles (user_id, created_at, updated_at)
        VALUES (?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");

    foreach ($items as $i => $row) {
        $nome      = trim((string)($row['nome'] ?? ''));
        $email     = trim((string)($row['email'] ?? ''));
        $passPlain = (string)($row['password'] ?? '');
        $companyId = to_company_id($row['company_id'] ?? ($row['company'] ?? null));

        // validações
        $errs = [];
        if ($nome === '') $errs[] = 'NOME_REQUIRED';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'EMAIL_INVALID';
        if (strlen($passPlain) < 6) $errs[] = 'PASSWORD_TOO_SHORT';

        if (!is_null($companyId)) {
            $checkCompany->execute([$companyId]);
            if (!$checkCompany->fetchColumn()) $errs[] = 'COMPANY_NOT_FOUND';
        }

        if ($errs) { $errors[] = ['index'=>$i,'email'=>$email,'errors'=>$errs]; continue; }

        // email único
        $checkEmail->execute([$email]);
        if ($checkEmail->fetchColumn()) {
            $errors[] = ['index'=>$i,'email'=>$email,'errors'=>['EMAIL_IN_USE']];
            continue;
        }

        $hash = password_hash($passPlain, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            // 1) user
            $insUser->execute([$nome, $email, $hash, $companyId]); // $companyId pode ser NULL
            $newUserId = (int)$pdo->lastInsertId();

            // 2) fichas associadas
            $insFicha->execute([$newUserId, $email]);
            $insEmerg->execute([$newUserId]);
            $insFinance->execute([$newUserId]);

            $pdo->commit();

            $created[] = [
                'id'         => $newUserId,
                'nome'       => $nome,
                'email'      => $email,
                'company_id' => $companyId
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = ['index'=>$i,'email'=>$email,'errors'=>['INSERT_FAILED']];
        }
    }

    echo json_encode(['ok'=>true,'created'=>$created,'errors'=>$errors]); exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'code'=>'SERVER_ERROR']); exit;
}
