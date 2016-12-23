<?php

require dirname(__FILE__).'/../../vendor/autoload.php';
//error_reporting(E_ALL);
//ini_set('display_errors', true);

function send($to, $from, $subject, $content, $server='localhost', $port=25, $login='test@example.org', $password='password')
{
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = $server;
    $mail->Port = $port;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = $login;
    $mail->Password = $password;
    $mail->setFrom($from);
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->msgHTML($content);
    $mail->AltBody = strip_tags($content);
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

#    $mail->SMTPDebug = 2;
    //Ask for HTML-friendly debug output
#    $mail->Debugoutput = 'html';
    
    if (!$mail->send()) {
//        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    } else {
        return true;
    }
}