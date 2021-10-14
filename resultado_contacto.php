<?php
    $pt_name_letras = "/^([A-ZÑÁÉÍÓÚa-zñáéíóú]+[\s]*)+$/";
    $pt_correo      = "/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$/";
    $pt_numeros     = "/^([0-9]{1,10})$/";

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $errores    = "";

    $nombre     = $_POST['name'];
    $correo     = $_POST['email'];
    $mensaje    = $_POST['text'];
    

    if (!preg_match($pt_name_letras, $nombre)) {
        $errores .= "<div>Nombre no válido, no se permiten caracteres especiales.</div>";
    }

    if (!preg_match($pt_correo, $correo)) {
        $errores .= "<div>Correo no valido.</div>";
    }

    if ( strlen($mensaje) == 0 ) {
        $errores .= "<div>Ingrese un mensaje.</div>";
    }

    if ( strlen($errores) > 0 ) {
        response ([
            "status" => 0, 
            "msg" => $errores, 
            "ex" => ""
        ], 200);
    }else{
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();                                        // Set mailer to use SMTP
            $mail->Host = 'mail.digitaldev.cl';                           // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                                 // Enable SMTP authentication
            $mail->Username = 'contacto@digitaldev.cl';          // SMTP username
            $mail->Password = '7H5E*BMX&Nh*';                         // SMTP password
            $mail->SMTPSecure = 'tls';                              // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                      // TCP port to connect to
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('contacto@digitaldev.cl', 'DigitalDev');
            $mail->addAddress($correo, ucfirst($nombre));
            $mail->addAddress('folivas@digitaldev.cl', 'Fabián Olivas');
            $mail->isHTML(true);                                  
            $mail->Subject = 'Contacto DigitalDev';
            
            $mail->Body    =    "Estimado/a: " . ucfirst($nombre) . 
                                "<br><br> Agradezco que se haya puesto en contacto, Dentro de las próximas horas me comunicaré con usted." . 
                                "<br><br> Datos " . 
                                "<br><br> Nombre: " . $nombre .
                                "<br> Email: " . $correo .
                                "<br> Mensaje: " . $mensaje .
                                "<br><br> Saludos, ";

            $mail->AltBody =    "Estimado/a: " . ucfirst($nombre) . 
                                "\n \t Agradezco que se haya puesto en contacto, Dentro de las próximas horas me comunicaré con usted." . 
                                "\n Datos " . 
                                "\n Nombre: " . $nombre .
                                "\n Email: " . $correo .
                                "\n Mensaje: " . $mensaje.
                                "\n\n Saludos,";

            $mail->send();
            
            response ([
                "status" => 1, 
                "msg" => "Mensaje Enviado", 
                "code" => ""
            ], 200);

        } catch (Exception $e) {
            response ([
                "status" => 0, 
                "msg" => "El mensaje no se pudo enviar", 
                "ex" => $mail->ErrorInfo 
            ], 200);
        }
    }

    function response($obj, $code) {
        header("Content-Type:application/json");
        http_response_code($code);
        echo json_encode($obj);
    }
	
?>