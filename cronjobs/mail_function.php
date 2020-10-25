<?php

require 'class.phpmailer.php';

function sendMail($message = null)
{
    $mail = new PHPMailer();

    // constants defined in a system-configuration file

    $mail->From = MAIL_SETTINGS_FROM;

    $mail->FromName = MAIL_SETTINGS_FROM_NAME;

    $mail->Host = MAIL_SETTINGS_HOST;

    $mail->Mailer = MAIL_SETTINGS_MAILER;

    $recipients = $message['toemail'];

    $subject = $message['subject'];

    $body = $message['body'];

    $mail->Subject = $subject;

    $mail->Body = $body;

    $mail->AltBody = $text_body;

    $mail->AddAddress($recipients);

    if (!$mail->Send()) {
        echo 'There has been a mail error sending to ' . $row['email'] . '<br>';
    }

    // Clear all addresses and attachments for next loop

    $mail->ClearAddresses();

    $mail->ClearAttachments();
}
