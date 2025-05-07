<?php
// app/Helpers/Mailer.php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public static function send($to, $subject, $message)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.sendgrid.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'apikey';
            $mail->Password   = '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';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            $mail->setFrom('atthaadvisa@apps.ipb.ac.id', 'SWAT Monitoring System');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->isHTML(true);
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}