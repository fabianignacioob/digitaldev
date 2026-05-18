<?php

$pt_name_letras = "/^([A-ZÑÁÉÍÓÚa-zñáéíóú]+[\s]*)+$/";
$pt_correo      = "/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$/";
$pt_numeros     = "/^([0-9]{1,10})$/";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$box_consulta_codigo = $_POST['box_consulta_codigo'];
$box_consulta_nombre = $_POST["box_consulta_nombre"];
$box_consulta_email = $_POST["box_consulta_email"];
$box_consulta_telefono = $_POST["box_consulta_telefono"];
$box_consulta_texto = $_POST["box_consulta_texto"];

$errores = "";

if (!preg_match($pt_name_letras, $box_consulta_nombre)) {
    $errores .= "<div>Nombre no válido, no se permiten caracteres especiales.</div>";
}

if (!preg_match($pt_correo, $box_consulta_email)) {
    $errores .= "<div>Correo no valido.</div>";
}

if (!preg_match($pt_numeros, $box_consulta_telefono)) {
    $errores .= "<div>Teléfono no valido.</div>";
}

if (strlen($box_consulta_texto) == 0) {
    $errores .= "<div>Ingrese un mensaje.</div>";
}

if (strlen($errores) > 0) {
    response([
        "status" => 0,
        "msg" => $errores,
        "ex" => ""
    ], 200);
} else {
    $mail = new PHPMailer(true);
    $mail2 = new PHPMailer(true);
    try {
        $mail->isSMTP();                                        // Set mailer to use SMTP
        $mail->Host = 'mail.lgpropiedades.cl';                           // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                                 // Enable SMTP authentication
        $mail->Username = 'no-reply@lgpropiedades.cl';          // SMTP username
        $mail->Password = 'asdasd2019';                         // SMTP password
        $mail->SMTPSecure = 'tls';                              // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                      // TCP port to connect to
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('no-reply@lgpropiedades.cl', 'LG Propiedades');
        $mail->addAddress($box_consulta_email, ucfirst($box_consulta_nombre));
        $mail->isHTML(true);
        $mail->Subject = 'Contacto Lg Propiedades';

        $mail->Body    =    "Estimado/a: " . ucfirst($box_consulta_nombre) .
            "<br><br> Agradecemos que se haya puesto en contacto con nosotros, Uno de nuestros ejecutivos se pondrá en contacto con usted." .
            "<br><br> Datos " .
            "<br><br> Código: " . $box_consulta_codigo .
            "<br><br> Nombre: " . $box_consulta_nombre .
            "<br> Email: " . $box_consulta_email .
            "<br> Teléfono: " . $box_consulta_telefono .
            "<br> Mensaje: " . $box_consulta_texto;

        $mail->AltBody =    "Estimado/a: " . ucfirst($box_consulta_nombre) .
            "\n \t Agradecemos que se haya puesto en contacto con nosotros, Uno de nuestros ejecutivos se pondrá en contacto con usted." .
            "\n Datos " .
            "\n Código: " . $box_consulta_codigo .
            "\n Nombre: " . $box_consulta_nombre .
            "\n Email: " . $box_consulta_email .
            "\n Teléfono: " . $box_consulta_telefono .
            "\n Mensaje: " . $box_consulta_texto;

        $mail->send();

        //Copia a THORTIS
        $mail2->isSMTP();                                        // Set mailer to use SMTP
        $mail2->Host = 'mail.lgpropiedades.cl';                           // Specify main and backup SMTP servers
        $mail2->SMTPAuth = true;                                 // Enable SMTP authentication
        $mail2->Username = 'no-reply@lgpropiedades.cl';          // SMTP username
        $mail2->Password = 'asdasd2019';                         // SMTP password
        $mail2->SMTPSecure = 'tls';                              // Enable TLS encryption, `ssl` also accepted
        $mail2->Port = 587;                                      // TCP port to connect to
        $mail2->CharSet = 'UTF-8';
        $mail2->setFrom('no-reply@lgpropiedades.cl', 'LG Propiedades');
        $mail2->addAddress('visitas@lgpropiedades.cl', 'Visitas');
        $mail2->isHTML(true);
        $mail2->Subject = 'Nuevo Contacto Propiedad';

        $mail2->Body    =   "<br><br> Nuevo Contacto." .
            "<br><br> Datos " .
            "<br><br> Código: " . $box_consulta_codigo .
            "<br><br> Nombre: " . $box_consulta_nombre .
            "<br> Email: " . $box_consulta_email .
            "<br> Teléfono: " . $box_consulta_telefono .
            "<br> Mensaje: " . $box_consulta_texto;

        $mail2->AltBody =   "\n \t Nuevo Contacto." .
            "\n Datos " .
            "\n Código: " . $box_consulta_codigo .
            "\n Nombre: " . $box_consulta_nombre .
            "\n Email: " . $box_consulta_email .
            "\n Teléfono: " . $box_consulta_telefono .
            "\n Mensaje: " . $box_consulta_texto;

        $mail2->send();

        response([
            "status" => 1,
            "msg" => "Mensaje Enviado",
            "code" => ""
        ], 200);
    } catch (Exception $e) {
        response([
            "status" => 0,
            "msg" => "El mensaje no se pudo enviar",
            "ex" => $mail2->ErrorInfo
        ], 200);
    }
}

function response($obj, $code)
{
    header("Content-Type:application/json");
    http_response_code($code);
    echo json_encode($obj);
}
