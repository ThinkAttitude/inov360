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

    $checkEmail = $pdo->prepare("SELECT 1 FROM `inov360`.`user` WHERE email=? LIMIT 1");
    $checkCompany = $pdo->prepare("SELECT 1 FROM `inov360`.`company` WHERE id=? LIMIT 1");
    $ins = $pdo->prepare("INSERT INTO `inov360`.`user` (name,email,password,company_id) VALUES (?,?,?,?)");

    foreach ($items as $i => $row) {
        $nome  = trim((string)($row['nome'] ?? ''));
        $email = trim((string)($row['email'] ?? ''));
        $pass  = (string)($row['password'] ?? '');
        $companyId = to_company_id($row['company_id'] ?? ($row['company'] ?? null));

        $errs = [];
        if ($nome === '') $errs[] = 'NOME_REQUIRED';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'EMAIL_INVALID';
        if (strlen($pass) < 6) $errs[] = 'PASSWORD_TOO_SHORT';

        // se vier company_id, validar existência
        if (!is_null($companyId)) {
            $checkCompany->execute([$companyId]);
            if (!$checkCompany->fetchColumn()) $errs[] = 'COMPANY_NOT_FOUND';
        }

        if ($errs) { $errors[] = ['index'=>$i,'email'=>$email,'errors'=>$errs]; continue; }

        $checkEmail->execute([$email]);
        if ($checkEmail->fetchColumn()) {
            $errors[] = ['index'=>$i,'email'=>$email,'errors'=>['EMAIL_IN_USE']];
            continue;
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        try {
            $ins->execute([$nome, $email, $hash, $companyId]); // $companyId pode ser NULL
            $created[] = [
                'id'         => (int)$pdo->lastInsertId(),
                'nome'       => $nome,
                'email'      => $email,
                'company_id' => $companyId
            ];
        } catch (Throwable $e) {
            $errors[] = ['index'=>$i,'email'=>$email,'errors'=>['INSERT_FAILED']];
        }
    }

    echo json_encode(['ok'=>true,'created'=>$created,'errors'=>$errors]); exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'code'=>'SERVER_ERROR']); exit;
}
