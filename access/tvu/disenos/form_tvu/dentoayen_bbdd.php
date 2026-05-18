<?php
    $pt_name_letras = "/^([A-ZÑÁÉÍÓÚa-zñáéíóú]+[\s]*)+$/";
    $pt_correo      = "/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$/";
    $pt_numeros     = "/^([0-9]{1,10})$/";

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    //require 'PHPMailer/autoload.php';

    $apellidos		= $_POST['apellidos'];
    $nombres		= $_POST["nombres"];
    $mail	        = $_POST["email"];
    $telefono	    = $_POST["telefono"];

    $errores    = "";   

    if (!preg_match($pt_name_letras, $nombres)) {
        $errores .= "Nombres no válidos, no se permiten caracteres especiales.\n";
    }

    if (!preg_match($pt_name_letras, $apellidos)) {
        $errores .= "Apellidos no válidos, no se permiten caracteres especiales.\n";
    }

    if (!preg_match($pt_correo, $mail)) {
        $errores .= "Correo no valido.\n";
    }

    if (!preg_match($pt_numeros, $telefono)) {
        $errores .= "Teléfono no valido.\n";
    }

    if(strlen($telefono) < 9 || strlen($telefono) > 9){
        $errores .= "Largo del Teléfono incorrecto, Deben ser 9 dígitos.\n";
    }


    if ( strlen($errores) > 0 ) {
        response ([
            "status" => 0, 
            "msg" => $errores, 
            "ex" => ""
        ], 200);
    }else{

        $mHost = "54.39.37.193";
        $mUser = "digitald";
        $mPasswd = "RlhD9Jg.8L1d2*";
        $mDB = "digitald_dentoayen";
        $conn = mysqli_connect($mHost, $mUser, $mPasswd, $mDB);

        $conteo = "";
        $sql_consulta = "SELECT * FROM concurso_limpieza WHERE mail = '$mail'";
        
        
        foreach ($conn->query($sql_consulta) as $row){
            $conteo = $row["id"];
        }
        //var_dump($conteo);die;
        if(strlen($conteo) > 0){
            response ([
                "status" => 0, 
                "msg" => "Usuario ya registrado", 
                "ex" => ""
            ], 200);
        }else{
            $mail2 = new PHPMailer(true);                              
            try {

                //Copia a Dentoayen
                $mail2->isSMTP();                                        // Set mailer to use SMTP
                $mail2->Host = 'mail.digitaldev.cl';                           // Specify main and backup SMTP servers
                $mail2->SMTPAuth = true;                                 // Enable SMTP authentication
                $mail2->Username = 'contacto@digitaldev.cl';          // SMTP username
                $mail2->Password = 'abdiel22eliias';                         // SMTP password
                $mail2->SMTPSecure = 'tls';                              // Enable TLS encryption, `ssl` also accepted
                $mail2->Port = 587;                                      // TCP port to connect to
                $mail2->CharSet = 'UTF-8';
                $mail2->setFrom('contacto@digitaldev.cl', 'DigitalDev');
                $mail2->addAddress($mail, $nombres); 
                $mail2->isHTML(true);                                  
                $mail2->Subject = 'Nuevo Registro';
                
                $mail2->Body    =   "<br><br> Nuevo Registro Tvu." . 
                                    "<br><br> Datos Ingresados:" . 
                                    "<br><br> Nombre: " . $nombres .
                                    "<br>Apellido: " . $apellidos .
                                    "<br>Email: " . $mail .
                                    "<br>Teléfono: " . $telefono ;
                $mail2->AltBody =   "\n \t Nuevo Registro Tvu." . 
                                    "\n Datos Ingresados:" . 
                                    "\n Apellido: " . $apellidos .
                                    "\n Nombre: " . $nombres .
                                    "\n Email: " . $mail .
                                    "\n Teléfono: " . $telefono ;
                $mail2->send();
                
                $sql_rows = "SELECT MAX(id) AS MAXIMO FROM concurso_limpieza";
                $result = mysqli_query($conn, $sql_rows);
                $row = mysqli_fetch_object($result);
                $next_value = $row->MAXIMO;
                $nombres = utf8_decode($nombres);
                $apellidos = utf8_decode($apellidos);
                $query  =   "INSERT INTO concurso_limpieza (id, nombre, apellido, telefono, mail) VALUES ($next_value + 1, '$nombres', '$apellidos', $telefono, '$mail')";
                
                if ( mysqli_query($conn, $query) ) {
                    response ([
                        "status" => 1, 
                        "msg" => "Datos Enviados Exitosamente", 
                        "ex" => ""
                    ], 200);                    
                }else{
                    response ([
                        "status" => 0, 
                        "msg" => "Error", 
                        "ex" => mysqli_error($conn)
                    ], 200);
        
                }       

            } catch (Exception $e) {
                response ([
                    "status" => 0, 
                    "msg" => "El mensaje no se pudo enviar", 
                    "ex" => $mail2->ErrorInfo 
                ], 200);
            }            
        }        
    }

    function response($obj, $code) {
        header("Content-Type:application/json");
        http_response_code($code);
        echo json_encode($obj);
    }
	
?>
