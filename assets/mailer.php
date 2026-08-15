<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug  = SMTP::DEBUG_OFF; // Set to SMTP::DEBUG_SERVER for detailed logs
    $mail->isSMTP();
    $mail->Host       = 'mail.6bytes.site';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@6bytes.site';
    $mail->Password   = 'Qwertyuiop@1?';
    
    // Specify Implicit TLS / SSL encryption for Port 465
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port       = 465;

    // Sender & Recipient setup
    $mail->setFrom('info@6bytes.site', 'Oprle Bank');

 } catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
