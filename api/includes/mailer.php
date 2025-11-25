<?php
declare(strict_types=1);

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Caminhos das pastas privadas (igual ao db.php)
$localPrivate = '/var/www/private';                     // Local (Docker)
$devPrivate   = '/home/drawline/inov360_private/env_dev'; // Develop
$prodPrivate  = '/home/inovpt/inov360_private/env_prod';  // Production

// Deteção do ambiente / localização do .env
$runningInDocker = getenv('DOCKER_ENV') || file_exists('/.dockerenv');

if ($runningInDocker && is_dir($localPrivate)) {
    // Ambiente LOCAL (Docker)
    $basePath = $localPrivate;
    $envFile  = file_exists("$basePath/.env.local") ? '.env.local' : '.env';
} elseif (is_dir($prodPrivate)) {
    $basePath = $prodPrivate;
    $envFile  = '.env';
} elseif (is_dir($devPrivate)) {
    $basePath = $devPrivate;
    $envFile  = '.env';
} else {
    http_response_code(500);
    exit('Ficheiro .env não encontrado (nenhuma pasta privada detectada).');
}

// Carrega o .env (se ainda não tiver sido carregado noutro sítio)
if (empty($_ENV['SMTP_HOST']) && empty($_ENV['SMTP_USERNAME'])) {
    $dotenv = Dotenv::createImmutable($basePath, $envFile);
    $dotenv->load();
}

/**
 * Cria e configura uma instância do PHPMailer com os dados de SMTP do .env
 */
function make_mailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    // === CONFIG SMTP ===
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST']       ?? 'localhost';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USERNAME']   ?? '';
    $mail->Password   = $_ENV['SMTP_PASSWORD']   ?? '';

    // Encriptação: tls / ssl / none
    $encryption = strtolower($_ENV['SMTP_ENCRYPTION'] ?? 'tls');
    if ($encryption === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($encryption === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure = '';
    }

    $mail->Port = isset($_ENV['SMTP_PORT']) ? (int)$_ENV['SMTP_PORT'] : 587;

    // === REMETENTE POR OMISSÃO ===
    $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'no-reply@example.com';
    $fromName  = $_ENV['SMTP_FROM_NAME']  ?? 'Inov360';

    $mail->CharSet = 'UTF-8';
    $mail->setFrom($fromEmail, $fromName);

    return $mail;
}

/**
 * Função genérica para enviar emails na aplicação.
 */
function send_app_mail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    ?string $altBody = null
): bool {
    $mail = make_mailer();

    try {
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody ?? strip_tags($htmlBody);

        return $mail->send();
    } catch (Exception $e) {
        // Log interno para debug
        error_log('Erro ao enviar email: ' . $mail->ErrorInfo);
        return false;
    }
}
