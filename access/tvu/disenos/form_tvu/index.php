<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <title>Formulario TVU</title>
        <style>
            @import url('https://fonts.googleapis.com/css?family=Comfortaa&display=swap');
            .contenedor_padre{
                font-family: 'Comfortaa', cursive;
                letter-spacing: 1px;
                /*border-top: 5px solid #2a3054;*/
                height: 250px;
                width: 300px;
                background-color: #004074;
                /*padding-top: 5px;*/
                padding-bottom: 15px;
                padding-left: 15px;
                padding-right: 15px;
                color: #FFF;
            }

            .titulo{
                margin-top: 10px;
                font-weight: 900;
                font-size: 14px;
            }

            .sub_titulo{
                font-weight: normal;
            }

            .info_importante{
                font-weight: 900;
                font-size: 14px;
            }

            .text_pie{
                font-size: 8px;
                letter-spacing: normal;
            }

            .mas_info{
                font-size: 9px;
                font-weight: normal;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col d-flex justify-content-center">
                    <div class="contenedor_padre">
                        <div class="contenedor_hijo">
                            <!--<div class="imagenes d-flex justify-content-around align-items-center">
                                <img height="80px" src="logo.png" alt="Logo">
                                <img height="70px" src="sonrisa.jpg" alt="sonrisa">
                            </div>-->
                            <div class="texto text-center">
                                <div class="titulo">TITULO</h5>
                                <div class="sub_titulo">Sub titulo</div>
                                <div class="info_importante">Información Importante</div>
                                <div class="mas_info">Más Información</div>
                            </div>
                            <form class="mt-3" id="form_dentoayen" method="POST" action="index.php">
                                <div class="form-row">
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm" name="nombres" placeholder="(*) Nombres">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm" name="apellidos" placeholder="(*) Apellidos">
                                    </div>
                                </div>
                                <div class="form-row mt-1">
                                    <div class="col-6">
                                        <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" class="form-control form-control-sm" name="telefono" placeholder="(*) Teléfono">
                                    </div>
                                    <div class="col-6">
                                        <input type="email" class="form-control form-control-sm" name="email" placeholder="(*) Mail">
                                    </div>
                                </div>
                                <div class="row d-flex align-items-center mt-2">
                                    <div class="col-7 text-left">
                                        <div class="text_pie mb-1">Enlace de Descarga para las bases. <a href="#">Pinche Aquí</a></div>
                                        <img height="40px" src="logo-header.png" alt="logo_tvu">
                                    </div>
                                    <div class="col-5">
                                        <input type="button" style="background-color: #FFF; color:#004074;" class="btn" id="boton_enviar" value="Enviar">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript" src="js/jquery-3.3.1.slim.min.js"></script>
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/popper.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>

        <script>
            $(document).ready(function(){
                $('#boton_enviar').click(function(){                    
                    var datos = $('#form_dentoayen').serialize();
                    var url = "validador_mail.php";
                    $.ajax({                        
                        type: "POST",                 
                        url: url,     
                        dataType: "json",                
                        data: datos, 
                        success: function(data)             
                        {
                            if(data.valid == true){
                                var datos = $('#form_dentoayen').serialize();
                                var url = "dentoayen_bbdd.php";
                                $.ajax({                        
                                    type: "POST",                 
                                    url: url,     
                                    dataType: "json",                
                                    data: datos, 
                                    success: function(data)             
                                    {
                                        if(data.status == 1){
                                            alert(data.msg)
                                        }else{
                                            alert(data.msg)
                                            console.log(data.ex)
                                        }
                                    }
                                });
                            }else{
                                alert(data.message + " no existe, Favor corregir y reenviar.")
                                console.log(data.ex)
                            }
                        }
                    });
                });
            });
        </script>
        <script type="text/javascript">
   <!--
     function loadIFrameScript() {
       try {
         var globalTemplateVersion = '';
         var searchString = document.location.search.substr(1);
         var parameters = searchString.split('&');

         for (var i = 0; i < parameters.length; i++) {
           var keyValuePair = parameters[i].split('=');
           var parameterName = keyValuePair[0];
           var parameterValue = keyValuePair[1];

           if (parameterName == 'gtVersion') {
             globalTemplateVersion = unescape(parameterValue);
           }
         }
         generateScriptBlock(globalTemplateVersion);
       }
       catch(e) {
       }
     }

     function generateScriptBlock(gtVersion) {
       if(!isValid(gtVersion)) {
         reportError();
         return;
       }
       var url = window.location.protocol +
         '//s0.2mdn.net/879366/DARTIFrame_' + gtVersion + '.js';
       document.write('<scr' + 'ipt src="' + url + '"></scr' + 'ipt>');
     }

     // Validation routine for parameters passed on the URL.
     // The parameter should contain only letters, numbers, and underscores
     // and should not exceed 15 characters in length.
     function isValid(stringValue) {
       var isValid = false;

       if (stringValue.length <= 15 &&
           stringValue.search(new RegExp('[^A-Za-z0-9_]')) == -1) {
         isValid = true;
       }
       return isValid;
     }

     // Report error to the DoubleClick ad server.
     function reportError() {
       var publisherProtocol = window.location.protocol + '//';
       document.write('<img src="' + publisherProtocol +
           'ad.doubleclick.net/activity;badserve=1" ' +
           'style="visibility:hidden" width=1 height=1>');
     }

     loadIFrameScript();
   -->
  </script>
    </body>
</html>

