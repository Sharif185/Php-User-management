<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'sharifssentaayi1@gmail.com';  // Your Gmail address
    $mail->Password = 'jeksdikvjtopdjzr';    // Use App Password, NOT your Gmail password
    $mail->SMTPSecure = 'ssl'; 
    $mail->Port = 465;
    $mail->SMTPDebug = 2; // Enable debugging to see errors
    $mail->SMTPDebug = 3;  // Debug mode (use 2, 3, or 4 for different levels)
    $mail->Debugoutput = 'html';


    $mail->setFrom('your-email@gmail.com', 'Your Name');
    $mail->addAddress('recipient-email@example.com'); 
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email from PHPMailer.';

    $mail->send();
    echo 'Message has been sent successfully!';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
