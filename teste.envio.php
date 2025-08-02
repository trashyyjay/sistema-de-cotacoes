<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'marketing@innovasell.com.br';
    $mail->Password   = 'rqwu hpog vkjb zogr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('cotacoes@innovasell.com.br', 'Teste');
    $mail->addAddress('seuemail@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Teste de envio';
    $mail->Body    = 'Isso é um teste de envio via PHPMailer.';

    $mail->send();
    echo '✅ E-mail enviado com sucesso.';
} catch (Exception $e) {
    echo "❌ Erro ao enviar: {$mail->ErrorInfo}";
}
