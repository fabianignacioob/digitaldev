<?php

// Include library file
require_once 'VerifyEmail.class.php'; 

// Initialize library class
$mail = new VerifyEmail();

// Set the timeout value on stream
$mail->setStreamTimeoutWait(20);

// Set debug output mode
$mail->Debug= TRUE; 
$mail->Debugoutput= 'html'; 

// Set email address for SMTP request
$mail->setEmailFrom('fabianolivas06@gmail.com');

// Email to check
$email = 'fabianolivas06@gmail.com'; 

// Verificar si el correo es valido y existe
if($mail->check($email)){ 
    echo 'Email <'.$email.'> El correo existe!'; 
}elseif(verifyEmail::validate($email)){ 
    echo 'Email <'.$email.'> El correo es valido pero no existe!'; 
}else{ 
    echo 'Email <'.$email.'> El correo no es valido!'; 
} 

?>