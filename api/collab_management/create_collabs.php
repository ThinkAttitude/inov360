<?php
// api/create_collabs.php
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
//require_once __DIR__ . '/../includes/mailer.php'; TODO: temporarily disabled email sending

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

function send_collab_credentials_email(string $toEmail, string $toName, string $plainPassword, array $creator = []): void
{
    // URL da plataforma – podes pôr APP_URL no .env; se não existir, usa este fallback.
    $appUrl = $_ENV['APP_URL'] ?? 'https://inov360.pt';

    $subject = 'Credenciais de acesso à plataforma INOV360';

    $safeToName  = $toName !== '' ? $toName : $toEmail;
    $safeEmail   = htmlspecialchars($toEmail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeName    = htmlspecialchars($safeToName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safePass    = htmlspecialchars($plainPassword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeAppUrl  = htmlspecialchars($appUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $year        = date('Y');

    $html = <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo à INOV360</title>
    <style>
        /* Reset e estilos base */
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; }
        a { text-decoration: none; }
        
        /* Estilos responsivos */
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .content { padding: 20px !important; }
            .header { padding: 30px 20px !important; }
        }
    </style>
</head>
<body style="background-color: #f3f4f6; margin: 0; padding: 40px 0;">

    <!-- Container Principal -->
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); overflow: hidden; margin: 0 auto;">
        
                <!-- Cabeçalho -->
        <tr>
            <td class="header"
                bgcolor="#2563EB"
                style="background-color:#2563EB;
                       background-image:linear-gradient(135deg,#2563EB 0%,#1d4ed8 100%);
                       padding:40px;
                       text-align:center;">
                <!-- Logo / Nome da Marca -->
                <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">INOV360</h1>
            </td>
        </tr>


        <!-- Conteúdo -->
        <tr>
            <td class="content" style="padding: 40px;">
                <!-- Saudação e Logo -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px;">
                    <tr>
                        <td style="vertical-align: middle;">
                            <h2 style="margin: 0; color: #111827; font-size: 22px; font-weight: 600;">Olá, {$safeName}</h2>
                        </td>
                        <td style="vertical-align: middle; text-align: right; width: 180px;">
                            <img src="cid:grupoinov_logo" alt="Grupo INOV360" width="160" style="display: block; border: 0; margin-left: auto;">
                        </td>
                    </tr>
                </table>
                
                <p style="margin: 0 0 25px 0; color: #4b5563; font-size: 16px; line-height: 1.6;">
                    Seja muito bem-vindo! A sua conta na plataforma <strong>INOV360</strong> está pronta. Preparámos tudo para que possa começar a utilizar os nossos serviços imediatamente.
                </p>

                <!-- Caixa de Credenciais -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 25px;">
                            <p style="margin: 0 0 5px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 600;">Email de Acesso</p>
                            <p style="margin: 0 0 20px 0; font-size: 16px; color: #1e293b; font-weight: 500;">{$safeEmail}</p>
                            
                            <p style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 600;">Password Temporária</p>
                            <div style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 15px; display: inline-block;">
                                <code style="font-family: 'Courier New', Courier, monospace; font-size: 18px; color: #2563EB; font-weight: 700; letter-spacing: 1px;">{$safePass}</code>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Botão de Ação -->
                <div style="text-align: center; margin-bottom: 35px;">
                    <a href="{$safeAppUrl}" target="_blank" style="background-color: #2563EB; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);">
                        Aceder à Plataforma
                    </a>
                </div>

                <!-- Aviso de Segurança -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-top: 1px solid #f1f5f9;">
                    <tr>
                        <td style="padding-top: 25px;">
                            <p style="margin: 0; font-size: 14px; color: #64748b; line-height: 1.5;">
                                <strong style="color: #ef4444;">Atenção:</strong> Por motivos de segurança, recomendamos que altere a sua palavra-passe no primeiro acesso através das definições de perfil.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Rodapé -->
        <tr>
            <td style="background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                <p style="margin: 0 0 10px 0; font-size: 14px; color: #94a3b8;">
                    Precisa de ajuda? Contacte o nosso suporte.
                </p>
                <p style="margin: 0; font-size: 12px; color: #cbd5e1;">
                    &copy; {$year} INOV360. Todos os direitos reservados.
                </p>
            </td>
        </tr>
    </table>
    
    <!-- Espaçador final para mobile -->
    <div style="height: 40px; font-size: 0; line-height: 0;">&nbsp;</div>

</body>
</html>
HTML;

    try {
        $mail = make_mailer(); // from includes/mailer.php

        // EMBED da imagem do logo
        $logoPath = __DIR__ . '/../../assets/logos/grupoinovblack.png'; // api/ -> ../assets/logos
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'grupoinov_logo'); // mesmo ID do src="cid:..."
        } else {
            error_log('Logo INOV360 não encontrado em ' . $logoPath);
        }

        // Destinatário principal (colaborador)
        $mail->addAddress($toEmail, $safeToName);

        // BCC para o utilizador que criou
        $creatorEmail = $creator['email'] ?? '';
        if ($creatorEmail !== '') {
            $creatorName = $creator['name'] ?? $creatorEmail;
            $mail->addBCC($creatorEmail, $creatorName);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        // versão de texto simples
        $alt = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html));
        $mail->AltBody = $alt;

        $mail->send();
    } catch (\Throwable $e) {
        // Não deve rebentar o endpoint se o email falhar.
        error_log('Erro ao enviar credenciais do colaborador: ' . $e->getMessage());
    }
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

    $creator = $_SESSION['user'] ?? [];

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

            // send_collab_credentials_email($email, $nome, $passPlain, $creator); TODO: temporarily disabled email sending

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
