<?php
// mail_tester.php
declare(strict_types=1);

// Para ver o resultado no browser
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/includes/mailer.php'; // ajusta o caminho se necessário

// Para onde queres receber o teste
$toEmail = $_GET['to'] ?? 'miguel.cordeiro@drawline.pt'; // muda para o teu email
$toName  = 'Teste SMTP';

$subject = 'Teste SMTP INOV360';
$html    = '<p>Este é um email de <strong>teste</strong> enviado pela INOV360 via PHPMailer.</p>';

echo "A enviar email de teste para: {$toEmail}\n\n";

try {
    // usar diretamente o PHPMailer para conseguir ver o ErrorInfo
    $mail = make_mailer();
    $mail->addAddress($toEmail, $toName);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $html;
    $mail->AltBody = 'Este é um email de teste enviado pela INOV360 via PHPMailer.';

    if ($mail->send()) {
        echo "✅ Email enviado com sucesso.\n";
    } else {
        echo "❌ Falha ao enviar.\n";
        echo "ErrorInfo: " . $mail->ErrorInfo . "\n";
    }

    // Mostrar configuração básica (sem password) para confirmar
    echo "\n--- Config utilizada ---\n";
    echo "Host: " . ($_ENV['SMTP_HOST'] ?? 'n/a') . "\n";
    echo "Port: " . ($_ENV['SMTP_PORT'] ?? 'n/a') . "\n";
    echo "Encryption: " . ($_ENV['SMTP_ENCRYPTION'] ?? 'n/a') . "\n";
    echo "Username: " . ($_ENV['SMTP_USERNAME'] ?? 'n/a') . "\n";
    echo "From: " . ($_ENV['SMTP_FROM_EMAIL'] ?? 'n/a') . " (" . ($_ENV['SMTP_FROM_NAME'] ?? '') . ")\n";

} catch (Throwable $e) {
    echo "❌ Excepção ao enviar email:\n";
    echo $e->getMessage() . "\n";
}
