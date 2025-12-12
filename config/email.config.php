<?php

define('USE_LOCAL_TESTING', false); 


define('SMTP_USERNAME', 'adm.soludesk@gmail.com'); 
define('SMTP_PASSWORD', 'mdyu uahk erxh obln'); 

define('FROM_EMAIL', 'adm.soludesk@gmail.com'); 
define('FROM_NAME', 'Soludesk');
define('REPLY_TO_EMAIL', 'adm.soludesk@gmail.com'); 
define('REPLY_TO_NAME', 'Soporte Técnico');


define('SMTP_HOST', 'smtp.gmail.com'); 
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true);
define('LOCAL_EMAIL_PATH', dirname(__FILE__) . '/../emails_test/');


function configurarPHPMailer($mail) {
    $mail->CharSet = "UTF-8";
    
    if (USE_LOCAL_TESTING) {
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->SMTPAuth = false;
        $mail->Port = 25;
        
        if (!file_exists(LOCAL_EMAIL_PATH)) {
            mkdir(LOCAL_EMAIL_PATH, 0777, true);
        }
    } else {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = SMTP_AUTH;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    }
    
    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addReplyTo(REPLY_TO_EMAIL, REPLY_TO_NAME);
    
    return $mail;
}


function enviarEmail($destinatario, $nombreDestinatario, $asunto, $mensaje, $esHTML = true) {
    try {
        $mail = new PHPMailer(true);
        configurarPHPMailer($mail);
        
        $mail->addAddress($destinatario, $nombreDestinatario);
        $mail->Subject = $asunto;
        
        if ($esHTML) {
            $mail->isHTML(true);
            $mail->Body = $mensaje;
        } else {
            $mail->Body = $mensaje;
        }
        
        if (USE_LOCAL_TESTING) {
            $filename = LOCAL_EMAIL_PATH . 'email_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
            $contenido = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>$asunto</title>
</head>
<body>
    <h2>Email de Prueba - HelpDesk System</h2>
    <p><strong>Para:</strong> $nombreDestinatario ($destinatario)</p>
    <p><strong>Asunto:</strong> $asunto</p>
    <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
    <hr>
    $mensaje
</body>
</html>";
            
            file_put_contents($filename, $contenido);
            
            return [
                'success' => true,
                'message' => "Email guardado como archivo: " . basename($filename),
                'file' => $filename
            ];
        } else {
            $mail->send();
            return [
                'success' => true,
                'message' => "Email enviado exitosamente"
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Error al enviar email: " . $e->getMessage(),
            'error' => $mail->ErrorInfo
        ];
    }
}
?>
