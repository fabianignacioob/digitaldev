<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XHNLYQS1HF"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-XHNLYQS1HF');
    </script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6282125682834865" crossorigin="anonymous"></script>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Dev</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link href="css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="css/jquery.animatedheadline.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/swiper.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <style>
        :root {
            --text-size: 45px;
            --gap: 14px;
            --wordDuration: 1s;
        }

        /* body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: Inter, system-ui;
        } */

        .hero {
            display: flex;
            align-items: center;
            gap: var(--gap);
            font-weight: 400;
            font-size: var(--text-size);
            color: white;
            letter-spacing: 2px;
        }

        .fixed {
            letter-spacing: 2px;
        }

        .rotator {
            position: relative;
            width: 100%;
            height: calc(var(--text-size)*1.15);
            overflow: hidden;
        }

        .rotator b p {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            opacity: 0;
            transform: translateY(18px);
            white-space: nowrap;
            color: white;
            font-size: 35px;
            margin: 0.5em 0;
        }

        @keyframes wAnim {
            0% {
                opacity: 0;
                transform: translateY(18px)
            }

            8% {
                opacity: 1;
                transform: translateY(0)
            }

            30% {
                opacity: 1
            }

            38% {
                opacity: 0;
                transform: translateY(-18px)
            }

            100% {
                opacity: 0
            }
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <!-- <div class="preloader-wrap">
        <div class="preloader">
            <div class="lines">
                <div class="line line-1"></div>
                <div class="line line-2"></div>
                <div class="line line-3"></div>
            </div>
        </div>
    </div> -->

    <!-- Header Section -->
    <div class="header" id="header">
        <div class="container">
            <div class="row">
                <nav class="navbar fixed-top navbar-expand-lg">
                    <div class="container">
                        <a class="navbar-brand" href="#header"><img src="img/marca-dv-color2.png" style="width: 30%; margin-left: 20px;" alt=""></a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"><i class="fa fa-navicon"></i></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav ml-auto mt-2 mt-lg-0 ">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#header">INICIO <span class="sr-only">(current)</span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#about">NOSOTROS</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#work">PROYECTOS</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#contact">CONTACTANOS</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                <div class="header-wrapper">
                    <div class="col-md-12 col-xs-12 text-center">
                        <div class="selector">
                            <div class="fixed">somos</div>
                            <div class="hero">
                                <div class="rotator" id="rot">
                                    <b>
                                        <p>Desarrolladores</p>
                                    </b>
                                    <b>
                                        <p>Ad Managers</p>
                                    </b>
                                    <b>
                                        <p>Especialistas</p>
                                    </b>
                                </div>
                            </div>
                        </div>
                        <!--<h4 class="sm-title">LOCATED IN NY</h4>-->
                    </div>
                    <!-- <div class="col-lg-12 text-center">
                        <a style="color: #17649f;" target="_blank" href="https://www.linkedin.com/in/fabianignacioob/"><i class="fab fa-linkedin fa-2x"></i></a>
                        <a style="color: #803080;" target="_blank" href="https://www.instagram.com/fabianignacioob/"><i class="fab fa-instagram fa-2x"></i></a>
                        <a style="color: #7d7574;" href="mailto:folivas@gmail.com"><i class="far fa-envelope fa-2x"></i></a>
                        <a style="color: #fffbf0;" target="_blank" href="https://github.com/fabianignacioob"><i class="fab fa-github-square fa-2x"></i></a>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <div class="main-wrapper">
        <!-- About Section -->
        <div class="about mt-5" id="about">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-xs-12 text-center">
                        <h4 class="title">NOSOTROS</h4>
                        <hr class="line">
                        <p class="main-text aniview slow" data-av-animation="slideInRight">
                            Somos un equipo apasionado por el desarrollo web, con una sólida base técnica y más de 7 años de experiencia creando soluciones digitales a medida, participado en proyectos que abarcan desde la creación de interfaces intuitivas hasta la modernización de sistemas y la optimización de infraestructuras de software.
                        </p>
                        <p class="main-text aniview slow" data-av-animation="slideInLeft">
                            Entendemos que cada cliente tiene necesidades únicas, por eso seleccionamos las tecnologías más adecuadas para cada proyecto, garantizando una experiencia de usuario ágil, segura y eficiente. Nos involucramos en todo el proceso de desarrollo, tanto en el frontend como en el backend, proponiendo ideas innovadoras y mejoras continuas que aportan valor real.
                        </p>
                        <p class="main-text aniview slow" data-av-animation="slideInRight">
                            Nuestra filosofía se basa en la búsqueda constante de nuevos desafíos y en la incorporación de tecnologías emergentes que nos permitan crecer y ofrecer soluciones cada vez más robustas, seguras y rápidas. Creemos en la transparencia, la comunicación efectiva y el compromiso con la excelencia, para que nuestros clientes no solo obtengan un producto, sino un aliado tecnológico de confianza.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="services aniview slow" data-av-animation="slideInUp" id="services">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <h5 class="title">SERVICIOS</h5>
                        <hr class="line">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <img src="img/fondo7.jpg" alt="">
                    </div>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-md-4 col-xs-12">
                                <p><i class="fa fa-crop" aria-hidden="true"></i> DISEÑO WEB</p>
                                <p class="text-justify">
                                    Diseños que se adaptan a ti: ya sea desde cero o con plantillas personalizadas, creamos la imagen perfecta para tu proyecto.
                                </p>
                            </div>
                            <div class="col-md-4 col-xs-12">
                                <p><i class="fa fa-laptop-code" aria-hidden="true"></i> BACKEND</p>
                                <p class="text-justify">
                                    Datos al instante y con seguridad: conecta tu interfaz con la información de manera veloz y totalmente protegida.
                                </p>
                            </div>
                            <div class="col-md-4 col-xs-12">
                                <p><i class="fa fa-code" aria-hidden="true"></i> FRONTEND</p>
                                <p class="text-justify">
                                    Experiencias digitales que enamoran: interfaces intuitivas y fáciles de usar para que tus usuarios se sientan como en casa.
                                </p>
                            </div>
                            <div class="col-md-4 col-xs-12">
                                <p><i class="fa fa-link" aria-hidden="true"></i> API's</p>
                                <p class="text-justify">
                                    API's que conectan el mundo: integramos y desarrollamos conexiones entre sistemas para que todo fluya sin límites.
                                </p>
                            </div>
                            <div class="col-md-4 col-xs-12">
                                <p><i class="fa fa-database" aria-hidden="true"></i> BASES DE DATOS</p>
                                <p class="text-justify">
                                    De la idea al negocio real: diseñamos el esquema completo de tu proyecto para que puedas verlo y hacerlo crecer.
                                </p>
                            </div>
                            <div class="col-md-4 col-xs-12">
                                <p><i class="fa fa-google" aria-hidden="true"></i> GOOGLE ADS</p>
                                <p class="text-justify">
                                    Publicidad que genera resultados: optimizamos tu sitio con las herramientas de Google para aumentar ingresos y oportunidades.
                                </p>
                            </div>
                            <div class="col-md-4 col-xs-12">
                                <p><i class="fa fa-headset" aria-hidden="true"></i> SOPORTE</p>
                                <p class="text-justify">
                                    Acompañamiento post-lanzamiento: soporte técnico de al menos 6 meses para que todo funcione como debe y sin sorpresas.
                                </p>
                            </div>
                            <div class="col-md-4 col-xs-12">
                                <p><i class="fa fa-cloud" aria-hidden="true"></i> DEPLOYS</p>
                                <p class="text-justify">
                                    Despliegue en la nube: llevamos tu proyecto a producción, asegurando que sea estable, seguro y optimo.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- proyectos Section -->
        <div class="work aniview slow" data-av-animation="slideInUp" id="work">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <h5 class="title">QUIENES HAN CONFIADO</h5>
                        <hr class="line">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-xs-12 my-2 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>LG Propiedades</p>
                            </div>
                            <div class="card-body">
                                <img style="max-height: 150px; width:auto" src="img/logo_lg.png" alt="">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-xs-12 my-2 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>Club LKF</p>
                            </div>
                            <div class="body">
                                <img style="max-height: 150px; width:auto; margin:20px" src="img/logo-lkf-new.png" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12 my-2 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>C Mas</p>
                            </div>
                            <div class="body">
                                <img style="max-height: 80px; width:auto; margin:55px 0px" src="img/logo-cmas.png" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12 my-2 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>Service Office</p>
                            </div>
                            <div class="body">
                                <img style="max-height: 150px; width:auto; margin:25px 0px" src="img/marca-so-color.png" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12 my-2 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>Diario Concepción</p>
                            </div>
                            <div class="body">
                                <img style="max-width: 200px; background-color: #00607a; padding: 5px; margin:80px 0px" src="img/dccp.png" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12 my-2 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>TVU</p>
                            </div>
                            <div class="body">
                                <img style="max-height: 100px; width:auto; margin:75px 0px" src="img/tvu.png" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12 my-2 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>La Discusión</p>
                            </div>
                            <div class="body">
                                <img style="width:250px; margin:80px 0px" src="img/ldcl.jpg" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>3E</p>
                            </div>
                            <div class="body">
                                <img style="max-width: 150px; margin:25px 0px" src="img/3e.png" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>Inntek</p>
                            </div>
                            <div class="body">
                                <img style="max-width: 200px; margin:45px 0px" src="img/Logo_Inntek.gif" alt="">
                            </div>
                        </div>
                    </div>
                    <!--
                    <div class="col-md-4 col-xs-12 text-center">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <p>Autismo Conecta</p>
                            </div>
                            <div class="body">
                                <img style="max-width: 200px; margin:60px 0px" src="img/marca-ac-color.png" alt="">
                            </div>
                        </div>
                    </div>
                    -->
                </div>
            </div>
            <div class="container mt-5">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <h5 class="title">ALGUNAS TECNOLOGIAS</h5>
                        <hr class="line">
                    </div>
                </div>
                <div class="row text-center owl-carousel owl-theme owl-loaded">
                    <div class="owl-stage-outer">
                        <div class="owl-stage">
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 25 12 C 18.507813 12 12.621094 13.359375 8.273438 15.628906 C 3.925781 17.898438 1 21.167969 1 25 C 1 28.832031 3.925781 32.101563 8.273438 34.371094 C 12.621094 36.640625 18.507813 38 25 38 C 31.492188 38 37.378906 36.640625 41.726563 34.371094 C 46.074219 32.101563 49 28.832031 49 25 C 49 21.167969 46.074219 17.898438 41.726563 15.628906 C 37.378906 13.359375 31.492188 12 25 12 Z M 25 14 C 31.210938 14 36.824219 15.324219 40.800781 17.402344 C 44.777344 19.476563 47 22.203125 47 25 C 47 27.796875 44.777344 30.523438 40.800781 32.597656 C 36.824219 34.675781 31.210938 36 25 36 C 18.789063 36 13.175781 34.675781 9.199219 32.597656 C 5.222656 30.523438 3 27.796875 3 25 C 3 22.203125 5.222656 19.476563 9.199219 17.402344 C 13.175781 15.324219 18.789063 14 25 14 Z M 22.507813 16 L 20 28 L 22.625 28 L 23.890625 22 L 25.988281 22 C 26.65625 22 27.101563 22.109375 27.308594 22.332031 C 27.511719 22.554688 27.554688 22.976563 27.4375 23.582031 L 26.480469 28 L 29.144531 28 L 30.183594 23.222656 C 30.40625 22.078125 30.238281 21.238281 29.683594 20.726563 C 29.117188 20.207031 28.121094 20 26.636719 20 L 24.296875 20 L 25.128906 16 Z M 11 20 L 8.972656 31 L 11.617188 31 L 12.144531 28 L 13.792969 28 C 17.238281 28 19.113281 27.203125 19.8125 24.246094 C 20.414063 21.703125 18.875 20 16.332031 20 Z M 32 20 L 29.972656 31 L 32.617188 31 L 33.144531 28 L 34.792969 28 C 38.238281 28 40.113281 27.203125 40.8125 24.246094 C 41.414063 21.703125 39.875 20 37.332031 20 Z M 13.273438 22 L 15.332031 22 C 17.042969 22 17.402344 22.769531 17.3125 23.625 C 17.082031 25.832031 15.707031 26 14.230469 26 L 12.515625 26 Z M 34.273438 22 L 36.332031 22 C 38.042969 22 38.402344 22.769531 38.3125 23.625 C 38.082031 25.832031 36.707031 26 35.230469 26 L 33.515625 26 Z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 39.375 3.9882812 A 1.0001 1.0001 0 0 0 39.205078 4.0019531 L 30.621094 4.0019531 A 1.0001 1.0001 0 0 0 29.757812 4.4960938 L 25 12.625 L 20.244141 4.4960938 A 1.0001 1.0001 0 0 0 19.380859 4.0019531 L 10.751953 4.0019531 A 1.0001 1.0001 0 0 0 10.542969 4.0019531 L 1.0039062 4.0019531 A 1.0001 1.0001 0 0 0 0.140625 5.5058594 L 24.138672 46.505859 A 1.0001 1.0001 0 0 0 25.863281 46.505859 L 49.863281 5.5058594 A 1.0001 1.0001 0 0 0 49.001953 4.0019531 L 39.519531 4.0019531 A 1.0001 1.0001 0 0 0 39.375 3.9882812 z M 2.7480469 6.0019531 L 10.074219 6.0019531 L 24.136719 30.029297 A 1.0001 1.0001 0 0 0 25.863281 30.029297 L 39.925781 6.0019531 L 47.257812 6.0019531 L 25.001953 44.023438 L 2.7480469 6.0019531 z M 12.390625 6.0019531 L 18.806641 6.0019531 L 24.138672 15.109375 A 1.0001 1.0001 0 0 0 25.863281 15.109375 L 31.195312 6.0019531 L 37.609375 6.0019531 L 25 27.544922 L 12.390625 6.0019531 z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 31.167969 8 C 30.699219 7.988281 30.289063 8.167969 30.078125 8.6875 C 29.71875 9.558594 30.613281 10.410156 30.933594 10.855469 C 31.15625 11.164063 31.445313 11.511719 31.605469 11.859375 C 31.710938 12.089844 31.726563 12.320313 31.816406 12.5625 C 32.039063 13.160156 32.394531 13.839844 32.679688 14.394531 C 32.824219 14.675781 32.984375 14.96875 33.167969 15.21875 C 33.28125 15.371094 33.472656 15.441406 33.503906 15.675781 C 33.316406 15.941406 33.304688 16.351563 33.199219 16.6875 C 32.722656 18.191406 32.902344 20.0625 33.59375 21.171875 C 33.808594 21.515625 34.3125 22.246094 35 21.96875 C 35.601563 21.722656 35.46875 20.960938 35.640625 20.285156 C 35.679688 20.136719 35.65625 20.023438 35.734375 19.921875 L 35.734375 19.953125 C 35.914063 20.320313 36.097656 20.6875 36.28125 21.050781 C 36.691406 21.707031 37.414063 22.390625 38.023438 22.855469 C 38.339844 23.09375 38.589844 23.507813 39 23.648438 L 39 23.617188 L 38.96875 23.617188 C 38.890625 23.492188 38.765625 23.441406 38.664063 23.34375 C 38.425781 23.109375 38.160156 22.816406 37.964844 22.546875 C 37.40625 21.792969 36.914063 20.964844 36.46875 20.105469 C 36.253906 19.695313 36.066406 19.242188 35.886719 18.824219 C 35.816406 18.660156 35.816406 18.417969 35.671875 18.332031 C 35.472656 18.640625 35.183594 18.886719 35.03125 19.25 C 34.789063 19.828125 34.753906 20.535156 34.664063 21.265625 C 34.609375 21.285156 34.632813 21.269531 34.605469 21.296875 C 34.179688 21.191406 34.027344 20.753906 33.871094 20.378906 C 33.472656 19.429688 33.394531 17.898438 33.75 16.808594 C 33.839844 16.523438 34.25 15.632813 34.085938 15.371094 C 34.007813 15.109375 33.742188 14.960938 33.597656 14.761719 C 33.414063 14.515625 33.234375 14.191406 33.109375 13.90625 C 32.78125 13.164063 32.472656 12.304688 32.125 11.554688 C 31.960938 11.195313 31.683594 10.835938 31.453125 10.515625 C 31.199219 10.164063 30.917969 9.90625 30.71875 9.476563 C 30.652344 9.328125 30.554688 9.085938 30.660156 8.929688 C 30.691406 8.824219 30.738281 8.78125 30.84375 8.746094 C 31.019531 8.609375 31.511719 8.789063 31.699219 8.867188 C 32.1875 9.070313 32.597656 9.265625 33.011719 9.539063 C 33.210938 9.671875 33.410156 9.925781 33.652344 10 L 33.925781 10 C 34.359375 10.097656 34.839844 10.027344 35.238281 10.152344 C 35.949219 10.367188 36.585938 10.703125 37.160156 11.066406 C 38.921875 12.175781 40.363281 13.757813 41.34375 15.644531 C 41.503906 15.949219 41.574219 16.242188 41.714844 16.5625 C 41.992188 17.210938 42.347656 17.882813 42.628906 18.515625 C 42.90625 19.152344 43.179688 19.789063 43.574219 20.316406 C 43.78125 20.59375 44.585938 20.746094 44.949219 20.898438 C 45.203125 21.007813 45.625 21.121094 45.863281 21.265625 C 46.328125 21.542969 46.773438 21.875 47.207031 22.183594 C 47.425781 22.335938 48.089844 22.667969 48.125 22.945313 C 47.050781 22.917969 46.230469 23.015625 45.53125 23.3125 C 45.332031 23.398438 45.011719 23.398438 44.980469 23.648438 C 45.085938 23.761719 45.105469 23.933594 45.191406 24.074219 C 45.359375 24.34375 45.640625 24.707031 45.894531 24.898438 C 46.171875 25.105469 46.453125 25.328125 46.75 25.511719 C 47.273438 25.828125 47.859375 26.011719 48.367188 26.332031 C 48.664063 26.523438 48.964844 26.761719 49.253906 26.972656 C 49.398438 27.082031 49.492188 27.246094 49.679688 27.3125 L 49.679688 27.28125 C 49.582031 27.15625 49.558594 26.984375 49.46875 26.855469 L 49.066406 26.453125 C 48.679688 25.941406 48.1875 25.488281 47.664063 25.113281 C 47.246094 24.8125 46.3125 24.40625 46.140625 23.921875 L 46.109375 23.890625 C 46.402344 23.859375 46.75 23.75 47.023438 23.675781 C 47.484375 23.554688 47.890625 23.585938 48.363281 23.464844 C 48.578125 23.402344 48.792969 23.339844 49.007813 23.28125 L 49.007813 23.15625 C 48.769531 22.914063 48.597656 22.585938 48.335938 22.363281 C 47.652344 21.78125 46.90625 21.199219 46.136719 20.714844 C 45.710938 20.445313 45.183594 20.269531 44.734375 20.042969 C 44.582031 19.964844 44.316406 19.925781 44.214844 19.796875 C 43.976563 19.496094 43.847656 19.113281 43.664063 18.761719 C 43.28125 18.023438 42.90625 17.21875 42.566406 16.441406 C 42.335938 15.914063 42.183594 15.390625 41.894531 14.914063 C 40.507813 12.636719 39.015625 11.257813 36.703125 9.90625 C 36.210938 9.617188 35.621094 9.507813 34.996094 9.359375 C 34.65625 9.335938 34.324219 9.316406 33.984375 9.296875 C 33.78125 9.210938 33.566406 8.960938 33.375 8.835938 C 32.894531 8.535156 31.949219 8.011719 31.167969 8 Z M 34.476563 11.3125 C 34.253906 11.308594 34.09375 11.339844 33.925781 11.375 L 33.925781 11.40625 L 33.957031 11.40625 C 34.0625 11.625 34.253906 11.765625 34.386719 11.953125 C 34.488281 12.167969 34.585938 12.382813 34.6875 12.597656 L 34.71875 12.566406 C 34.90625 12.433594 34.996094 12.21875 34.996094 11.894531 C 34.917969 11.816406 34.90625 11.714844 34.84375 11.621094 C 34.753906 11.492188 34.585938 11.421875 34.476563 11.3125 Z M 1.867188 23.996094 C 1.566406 24.007813 1.238281 24.066406 0.882813 24.179688 C 0.289063 24.359375 -0.00390625 24.714844 -0.00390625 25.4375 L -0.00390625 33 L 2 33 L 2 25.621094 L 4.777344 31.929688 C 5.121094 32.714844 5.589844 32.996094 6.507813 32.996094 C 7.429688 32.996094 7.878906 32.714844 8.222656 31.929688 L 11 25.78125 L 11 33 L 13 33 L 13 25.4375 C 13 24.714844 12.710938 24.359375 12.113281 24.179688 C 10.691406 23.730469 9.734375 24.117188 9.304688 25.089844 L 6.453125 31.503906 L 3.695313 25.089844 C 3.382813 24.359375 2.757813 23.960938 1.867188 23.996094 Z M 26.246094 24 C 25.457031 24 23 24.09375 23 26 L 23 27.234375 C 23 28.109375 23.769531 28.824219 25.4375 29 C 25.625 29.011719 25.8125 29.027344 26 29.027344 C 26 29.027344 27.945313 28.988281 28 29 C 29.125 29 29 29.875 29 30 L 29 31 C 29 31.136719 28.96875 32 27.988281 32 L 23 32 L 23 33 L 28.007813 33 C 28.664063 33 29.300781 32.863281 29.808594 32.625 C 30.652344 32.238281 31 31.714844 31 31.027344 L 31 29.597656 C 31 28.0625 29.09375 28 28 28 L 26 28 C 25.214844 28 25.09375 27.523438 25 27 L 25 26 C 25.09375 25.601563 25.269531 25.0625 25.964844 25 L 31 25 L 31 24 Z M 33.980469 24 C 32.503906 24.203125 31.984375 24.9375 31.984375 26 L 31.984375 31 C 31.984375 31.972656 32.527344 32.558594 33.644531 32.863281 C 34.019531 32.96875 34.359375 33.011719 34.679688 33.011719 L 36.90625 33 L 38.214844 33 L 39.328125 34 L 41.578125 34 L 40.03125 32.605469 C 40.757813 32.304688 40.984375 31.84375 40.984375 30.980469 L 40.984375 26 C 40.984375 24.9375 40.292969 24.203125 38.816406 24 Z M 42 24 L 42 30.957031 C 42 32.164063 42.683594 32.84375 44.492188 32.980469 C 44.660156 32.988281 44.832031 33 45 33 L 50 33 L 50 32 L 45.378906 32 C 44.347656 32 44 31.566406 44 30.949219 L 44 24 Z M 35.171875 25 L 37.746094 25 C 38.425781 25 38.882813 25.546875 38.984375 26 C 38.984375 26 39 30.65625 39 31 C 39 31.34375 38.808594 31.5 38.808594 31.5 L 38.265625 31 L 36 31 L 37.113281 32 L 35.171875 32 C 34.476563 32 34.085938 31.484375 33.984375 31 L 33.984375 26.101563 C 33.984375 25.570313 34.390625 25 35.171875 25 Z M 14 27 C 14.039063 27.039063 14 31.261719 14 31.34375 C 14.015625 32.21875 15.125 32.984375 16.863281 33 L 20 33 L 20 33.066406 C 20 33.253906 20.136719 33.878906 19 34 C 18.988281 34 14.011719 34 14 34 L 14 35 L 19.214844 35 C 20.097656 34.972656 22.011719 34.773438 22 33.242188 C 22 33.214844 22.007813 27 22 27 L 20 27 L 20 32 C 19.96875 32 17.523438 32.007813 17.03125 32 C 16.066406 31.984375 15.984375 31.433594 16 31.222656 L 16 27 Z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 34.902344 2 C 32.863281 2 31.097656 2.550781 29.875 3.09375 C 28.675781 2.691406 26.6875 2.003906 24.300781 2.5 C 22.910156 2.742188 21.632813 3.316406 20.460938 4.195313 C 18.53125 3.265625 16.515625 2.695313 14.402344 2.601563 C 13.101563 2.5 7.800781 3.101563 5.898438 5.898438 C 5.199219 7 4.5 8.398438 4.199219 10.097656 C 3.898438 11.597656 3.898438 13.300781 4.398438 16.800781 C 4.699219 19.199219 5.101563 20.800781 6 24.097656 C 6.101563 24.398438 6.601563 26 8.101563 30.402344 C 8.398438 31.199219 9 32.699219 10.199219 34.097656 C 11 35.097656 11.800781 35.699219 12.800781 35.699219 C 14.101563 35.699219 15 34.800781 15.800781 33.800781 C 15.859375 33.734375 15.921875 33.660156 15.980469 33.59375 C 15.90625 33.710938 15.839844 33.839844 15.800781 34 C 15.601563 35 16.800781 35.800781 17.800781 36.199219 C 18.601563 36.597656 19.5 36.699219 20.199219 36.699219 C 21.097656 36.699219 21.800781 36.5 22.199219 36.402344 C 22.542969 36.285156 23.285156 35.964844 24.078125 35.4375 C 24.113281 37.992188 24.136719 41.007813 24.199219 41.699219 C 24.5 44.300781 25.199219 46.097656 26.5 47.097656 C 27.5 47.898438 29.300781 48 29.402344 48 C 31.199219 48 34 46.800781 35.199219 44.902344 C 35.800781 44 36 43.199219 36.199219 42.097656 C 36.398438 41.5 36.597656 38 36.699219 37.300781 C 36.835938 36.195313 36.941406 35.105469 37.046875 34.046875 C 37.707031 34.21875 38.519531 34.402344 39.402344 34.402344 C 41 34.402344 43.101563 33.300781 43.5 33.097656 C 44.300781 32.5 45.898438 31.101563 45.199219 29.902344 C 44.800781 29.199219 44.199219 29.199219 42.699219 29.402344 C 42.699219 29.402344 40.300781 29.699219 40.097656 29.597656 C 39.953125 29.542969 39.730469 29.402344 39.480469 29.21875 C 40 28.414063 40.460938 27.617188 41 26.902344 C 42.199219 24.601563 42.902344 22.800781 43.402344 21.402344 C 44.300781 18.902344 44.800781 16.898438 45.097656 15.5 C 45.800781 12.5 46 11.101563 45.597656 9.5 C 44.800781 6.699219 41.898438 4.300781 40.699219 3.601563 C 39.898438 3.199219 37.902344 2 34.902344 2 Z M 34.902344 4 C 37.402344 4 39.101563 5 40 5.398438 C 41.101563 6 43.601563 8 43.800781 9.898438 C 43.902344 11 44 12.101563 43.300781 15 C 42.902344 16.398438 42.5 18.199219 41.597656 20.699219 C 41.097656 22.097656 40.5 23.800781 39.300781 25.902344 C 39.269531 25.953125 39.234375 26.007813 39.203125 26.0625 C 39.320313 25.640625 39.402344 25.300781 39.402344 25.300781 C 39.601563 24.300781 39.601563 23.5 39.5 22.300781 C 39.398438 21.699219 39.300781 20.199219 39.300781 19.597656 C 39.300781 19.300781 39.597656 16.199219 39.699219 15.097656 C 39.800781 13.300781 38.699219 11.097656 38.402344 10.699219 C 36.902344 8.398438 36.101563 7.101563 34.5 5.800781 C 34.101563 5.460938 33.417969 4.894531 32.488281 4.324219 C 33.222656 4.144531 34.035156 4 34.902344 4 Z M 26.066406 4.410156 C 27.371094 4.441406 28.476563 4.800781 29.300781 5.101563 C 31.402344 5.800781 32.699219 6.898438 33.300781 7.398438 C 34.601563 8.5 35.300781 9.601563 36.800781 11.902344 C 36.910156 12.121094 37.195313 12.585938 37.421875 13.234375 C 35.375 13.046875 34.015625 13.765625 33.199219 14.5 C 32 15.5 32.097656 17 32.199219 18.097656 C 32.199219 18.898438 32.402344 19.902344 33.902344 23.300781 C 34.5 24.800781 35.097656 26.398438 35.699219 27.5 C 36.011719 28.121094 36.394531 28.691406 36.804688 29.199219 C 36.566406 29.320313 36.324219 29.476563 36.097656 29.699219 C 35.5 30.398438 35.398438 31.101563 35.199219 32.402344 C 35 33.402344 34.800781 35.5 34.699219 37.199219 C 34.699219 37.898438 34.402344 41.300781 34.300781 41.800781 C 34 42.800781 33.898438 43.300781 33.5 43.902344 C 32.800781 45 30.601563 45.902344 29.300781 45.800781 C 28.902344 45.800781 28.300781 45.800781 27.800781 45.402344 C 26.699219 44.601563 26.300781 42.800781 26.199219 41.402344 C 26.097656 40.402344 26.199219 33.199219 26 31.597656 C 25.898438 31.199219 25.800781 30.199219 25 29.5 C 24.664063 29.21875 23.96875 29.074219 23.3125 28.976563 C 23.320313 28.640625 23.339844 28.304688 23.402344 28 C 23.5 27.398438 23.699219 27.097656 23.902344 26.597656 C 24 26.300781 24.199219 25.902344 24.402344 25.402344 C 25.300781 22.601563 25.097656 18.898438 24.597656 16.597656 C 24.5 16.398438 24.097656 14.800781 22.699219 13.902344 C 21.199219 13 19.597656 13.5 18.699219 13.800781 C 18.328125 13.902344 17.960938 14.0625 17.59375 14.25 C 17.65625 13.832031 17.710938 13.410156 17.800781 13 C 18.199219 11 18.601563 9.300781 19.902344 7.601563 C 21.300781 5.898438 22.898438 4.800781 24.699219 4.5 C 25.175781 4.425781 25.632813 4.398438 26.066406 4.410156 Z M 13.71875 4.585938 C 13.953125 4.582031 14.152344 4.585938 14.300781 4.601563 C 15.863281 4.683594 17.359375 5.050781 18.84375 5.675781 C 18.660156 5.878906 18.476563 6.082031 18.300781 6.300781 C 16.601563 8.300781 16.199219 10.398438 15.800781 12.5 C 15.300781 15 15.199219 17.597656 15.597656 20.199219 L 15.402344 22.097656 C 15.300781 23.097656 15.097656 25 16.199219 27 C 16.585938 27.664063 17.011719 28.226563 17.480469 28.707031 C 16.464844 30.050781 15.386719 31.320313 14.300781 32.5 C 13.699219 33.199219 13.199219 33.699219 12.800781 33.699219 C 12.699219 33.699219 12.300781 33.5 11.699219 32.800781 C 10.597656 31.601563 10.199219 30.300781 10 29.800781 C 8.800781 26.199219 8.101563 23.898438 8 23.597656 C 7.199219 20.398438 6.800781 18.800781 6.398438 16.5 C 5.898438 13.300781 5.898438 11.699219 6.199219 10.402344 C 6.5 9 7 7.898438 7.5 7.101563 C 8.726563 5.175781 12.09375 4.628906 13.71875 4.585938 Z M 36.664063 15.125 C 36.984375 15.125 37.332031 15.164063 37.699219 15.242188 C 37.6875 16.175781 37.300781 19.105469 37.300781 19.5 C 37.300781 20.300781 37.5 21.898438 37.5 22.5 C 37.601563 23.601563 37.601563 24.199219 37.5 25 C 37.5 25 37.351563 25.714844 37.199219 26.222656 C 36.734375 25.171875 36.21875 23.917969 35.597656 22.5 C 34.097656 19.101563 34 18.402344 34 17.902344 C 34 17.199219 34 16.300781 34.597656 15.902344 C 35.160156 15.402344 35.84375 15.132813 36.664063 15.125 Z M 21.140625 15.417969 C 21.339844 15.441406 21.523438 15.5 21.699219 15.597656 C 22.5 16 22.699219 17 22.699219 17 C 23.199219 19.199219 23.398438 22.5 22.597656 24.699219 C 22.5 25.097656 22.300781 25.398438 22.199219 25.699219 C 22 26.199219 21.800781 26.699219 21.597656 27.597656 C 21.546875 27.96875 21.523438 28.335938 21.511719 28.703125 C 20.820313 28.558594 20.167969 28.3125 19.699219 28 C 18.898438 27.601563 18.300781 26.898438 17.902344 26.097656 C 17.199219 24.597656 17.300781 23.199219 17.402344 22.402344 L 17.597656 20.097656 C 17.457031 18.972656 17.390625 17.84375 17.40625 16.722656 C 17.867188 16.328125 18.46875 15.925781 19.300781 15.699219 C 19.902344 15.550781 20.558594 15.34375 21.140625 15.417969 Z M 35.886719 16.089844 C 35.625 16.101563 35.347656 16.148438 35.199219 16.199219 C 34.800781 16.300781 34.699219 16.300781 34.597656 16.5 C 34.5 16.699219 34.800781 17 34.902344 17.199219 C 35 17.199219 35.300781 17.5 35.699219 17.402344 C 36 17.300781 36.199219 17.101563 36.300781 17 C 36.402344 16.898438 36.800781 16.398438 36.5 16.199219 C 36.398438 16.097656 36.148438 16.074219 35.886719 16.089844 Z M 20.914063 16.816406 C 20.804688 16.824219 20.699219 16.851563 20.597656 16.902344 C 20.5 16.902344 20.300781 17 20.199219 17.199219 C 20.097656 17.398438 20.199219 17.597656 20.300781 17.699219 C 20.5 18 20.800781 18.300781 21.300781 18.300781 C 21.402344 18.300781 21.800781 18.300781 22.097656 18 C 22.097656 18 22.402344 17.699219 22.402344 17.402344 C 22.300781 17.199219 22.101563 17.101563 21.800781 17 C 21.574219 16.925781 21.238281 16.792969 20.914063 16.816406 Z M 19.074219 29.902344 C 19.6875 30.230469 20.425781 30.496094 21.234375 30.679688 C 21.019531 31.109375 20.710938 31.464844 20.402344 31.699219 C 19.699219 32.199219 18.800781 32.5 17.902344 32.699219 C 17.699219 32.699219 17.601563 32.800781 17.402344 32.800781 C 16.917969 32.9375 16.449219 33.089844 16.128906 33.421875 C 17.109375 32.332031 18.09375 31.128906 19.074219 29.902344 Z M 38.355469 30.703125 C 38.792969 31.03125 39.222656 31.273438 39.597656 31.402344 C 40.199219 31.601563 40.699219 31.601563 42.5 31.402344 C 42.199219 31.699219 41.300781 32.199219 40 32.402344 C 39.164063 32.484375 38.125 32.285156 37.28125 32.042969 C 37.359375 31.476563 37.449219 31.152344 37.597656 31 C 37.652344 30.949219 37.984375 30.847656 38.355469 30.703125 Z M 23.296875 31.003906 C 23.503906 31.039063 23.667969 31.066406 23.699219 31.097656 C 23.898438 31.300781 24 31.800781 24 32 C 24.011719 32.171875 24.019531 32.605469 24.027344 32.90625 C 23.203125 33.871094 21.878906 34.40625 21.597656 34.5 C 21.199219 34.699219 20 34.898438 18.800781 34.5 C 19.800781 34.199219 20.800781 33.902344 21.597656 33.300781 C 22.296875 32.800781 22.898438 32.003906 23.296875 31.003906 Z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 25 2 C 12.311335 2 2 12.311335 2 25 C 2 37.688665 12.311335 48 25 48 C 37.688665 48 48 37.688665 48 25 C 48 12.311335 37.688665 2 25 2 z M 25 4 C 36.607335 4 46 13.392665 46 25 C 46 25.071371 45.994849 25.141688 45.994141 25.212891 C 45.354527 25.153853 44.615508 25.097776 43.675781 25.064453 C 42.347063 25.017336 40.672259 25.030987 38.773438 25.125 C 38.843852 24.634651 38.893205 24.137377 38.894531 23.626953 C 38.991361 21.754332 38.362521 20.002464 37.339844 18.455078 C 37.586913 17.601352 37.876747 16.515218 37.949219 15.283203 C 38.031819 13.878925 37.910599 12.321765 36.783203 11.269531 L 36.494141 11 L 36.099609 11 C 33.416539 11 31.580023 12.12321 30.457031 13.013672 C 28.835529 12.386022 27.01222 12 25 12 C 22.976367 12 21.135525 12.391416 19.447266 13.017578 C 18.324911 12.126691 16.486785 11 13.800781 11 L 13.408203 11 L 13.119141 11.267578 C 12.020956 12.287321 11.919778 13.801759 11.988281 15.199219 C 12.048691 16.431506 12.321732 17.552142 12.564453 18.447266 C 11.524489 20.02486 10.900391 21.822018 10.900391 23.599609 C 10.900391 24.111237 10.947969 24.610071 11.017578 25.101562 C 9.2118173 25.017808 7.6020996 25.001668 6.3242188 25.046875 C 5.3845143 25.080118 4.6454422 25.135713 4.0058594 25.195312 C 4.0052628 25.129972 4 25.065482 4 25 C 4 13.392665 13.392665 4 25 4 z M 14.396484 13.130859 C 16.414067 13.322043 17.931995 14.222972 18.634766 14.847656 L 19.103516 15.261719 L 19.681641 15.025391 C 21.263092 14.374205 23.026984 14 25 14 C 26.973016 14 28.737393 14.376076 30.199219 15.015625 L 30.785156 15.273438 L 31.263672 14.847656 C 31.966683 14.222758 33.487184 13.321554 35.505859 13.130859 C 35.774256 13.575841 36.007486 14.208668 35.951172 15.166016 C 35.883772 16.311737 35.577304 17.559658 35.345703 18.300781 L 35.195312 18.783203 L 35.494141 19.191406 C 36.483616 20.540691 36.988121 22.000937 36.902344 23.544922 L 36.900391 23.572266 L 36.900391 23.599609 C 36.900391 26.095064 36.00178 28.092339 34.087891 29.572266 C 32.174048 31.052199 29.152663 32 24.900391 32 C 20.648118 32 17.624827 31.052192 15.710938 29.572266 C 13.797047 28.092339 12.900391 26.095064 12.900391 23.599609 C 12.900391 22.134903 13.429308 20.523599 14.40625 19.191406 L 14.699219 18.792969 L 14.558594 18.318359 C 14.326866 17.530484 14.042825 16.254103 13.986328 15.101562 C 13.939338 14.14294 14.166221 13.537027 14.396484 13.130859 z M 8.8847656 26.021484 C 9.5914575 26.03051 10.40146 26.068656 11.212891 26.109375 C 11.290419 26.421172 11.378822 26.727898 11.486328 27.027344 C 8.178972 27.097092 5.7047309 27.429674 4.1796875 27.714844 C 4.1152068 27.214494 4.0638483 26.710021 4.0351562 26.199219 C 5.1622058 26.092262 6.7509972 25.994233 8.8847656 26.021484 z M 41.115234 26.037109 C 43.247527 26.010033 44.835728 26.108156 45.962891 26.214844 C 45.934234 26.718328 45.883749 27.215664 45.820312 27.708984 C 44.24077 27.41921 41.699674 27.086688 38.306641 27.033203 C 38.411945 26.739677 38.499627 26.438219 38.576172 26.132812 C 39.471291 26.084833 40.344564 26.046896 41.115234 26.037109 z M 11.912109 28.019531 C 12.508849 29.215327 13.361516 30.283019 14.488281 31.154297 C 16.028825 32.345531 18.031623 33.177838 20.476562 33.623047 C 20.156699 33.951698 19.86578 34.312595 19.607422 34.693359 L 19.546875 34.640625 C 19.552375 34.634325 19.04975 34.885878 18.298828 34.953125 C 17.547906 35.020374 16.621615 35 15.800781 35 C 14.575781 35 14.03621 34.42121 13.173828 33.367188 C 12.696283 32.72356 12.114101 32.202331 11.548828 31.806641 C 10.970021 31.401475 10.476259 31.115509 9.8652344 31.013672 L 9.7832031 31 L 9.6992188 31 C 9.2325521 31 8.7809835 31.03379 8.359375 31.515625 C 8.1485707 31.756544 8.003277 32.202561 8.0976562 32.580078 C 8.1920352 32.957595 8.4308563 33.189581 8.6445312 33.332031 C 10.011254 34.24318 10.252795 36.046511 11.109375 37.650391 C 11.909298 39.244315 13.635662 40 15.400391 40 L 18 40 L 18 44.802734 C 10.967811 42.320535 5.6646795 36.204613 4.3320312 28.703125 C 5.8629338 28.414776 8.4265387 28.068108 11.912109 28.019531 z M 37.882812 28.027344 C 41.445538 28.05784 44.08105 28.404061 45.669922 28.697266 C 44.339047 36.201504 39.034072 42.31987 32 44.802734 L 32 39.599609 C 32 38.015041 31.479642 36.267712 30.574219 34.810547 C 30.299322 34.368135 29.975945 33.949736 29.615234 33.574219 C 31.930453 33.11684 33.832364 32.298821 35.3125 31.154297 C 36.436824 30.284907 37.287588 29.220424 37.882812 28.027344 z M 23.699219 34.099609 L 26.5 34.099609 C 27.312821 34.099609 28.180423 34.7474 28.875 35.865234 C 29.569577 36.983069 30 38.484177 30 39.599609 L 30 45.398438 C 28.397408 45.789234 26.72379 46 25 46 C 23.27621 46 21.602592 45.789234 20 45.398438 L 20 39.599609 C 20 38.508869 20.467828 37.011307 21.208984 35.888672 C 21.950141 34.766037 22.886398 34.099609 23.699219 34.099609 z M 12.308594 35.28125 C 13.174368 36.179258 14.222525 37 15.800781 37 C 16.579948 37 17.552484 37.028073 18.476562 36.945312 C 18.479848 36.945018 18.483042 36.943654 18.486328 36.943359 C 18.36458 37.293361 18.273744 37.645529 18.197266 38 L 15.400391 38 C 14.167057 38 13.29577 37.55443 12.894531 36.751953 L 12.886719 36.738281 L 12.880859 36.726562 C 12.716457 36.421191 12.500645 35.81059 12.308594 35.28125 z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 24.960938 2 A 1.0001 1.0001 0 0 0 24.603516 2.0546875 L 3.671875 9.3398438 A 1.0001 1.0001 0 0 0 3.0078125 10.40625 L 6.3144531 37.529297 A 1.0001 1.0001 0 0 0 6.8300781 38.287109 L 24.476562 47.878906 A 1.0001 1.0001 0 0 0 25.435547 47.876953 L 43.173828 38.154297 A 1.0001 1.0001 0 0 0 43.685547 37.398438 L 46.992188 10.277344 A 1.0001 1.0001 0 0 0 46.322266 9.2089844 L 25.253906 2.0527344 A 1.0001 1.0001 0 0 0 24.960938 2 z M 24.9375 4.0585938 L 44.908203 10.841797 L 41.761719 36.648438 L 24.953125 45.861328 L 8.2382812 36.775391 L 5.0898438 10.964844 L 24.9375 4.0585938 z M 25.035156 6 A 1.0001 1.0001 0 0 0 24.09375 6.578125 L 11.09375 34.578125 A 1.0001 1.0001 0 0 0 12 36 L 16.4375 36 A 1.0001 1.0001 0 0 0 17.34375 35.421875 L 19.857422 30.007812 L 30.142578 30.007812 L 32.65625 35.421875 A 1.0001 1.0001 0 0 0 33.5625 36 L 38 36 A 1.0001 1.0001 0 0 0 38.90625 34.578125 L 25.90625 6.578125 A 1.0001 1.0001 0 0 0 25.035156 6 z M 25 9.3730469 L 36.433594 34 L 34.201172 34 L 31.6875 28.585938 A 1.0001 1.0001 0 0 0 30.78125 28.007812 L 19.21875 28.007812 A 1.0001 1.0001 0 0 0 18.3125 28.585938 L 15.796875 34 L 13.566406 34 L 25 9.3730469 z M 25.039062 15.5 A 1.0001 1.0001 0 0 0 24.091797 16.082031 L 20.166016 24.59375 A 1.0001 1.0001 0 0 0 21.074219 26.011719 L 28.925781 26.011719 A 1.0001 1.0001 0 0 0 29.833984 24.59375 L 25.908203 16.082031 A 1.0001 1.0001 0 0 0 25.039062 15.5 z M 25 18.886719 L 27.363281 24.011719 L 22.636719 24.011719 L 25 18.886719 z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 6.667969 4 C 5.207031 4 4 5.207031 4 6.667969 L 4 43.332031 C 4 44.792969 5.207031 46 6.667969 46 L 43.332031 46 C 44.792969 46 46 44.796875 46 43.332031 L 46 6.667969 C 46 5.207031 44.796875 4 43.332031 4 Z M 6.667969 6 L 43.332031 6 C 43.703125 6 44 6.296875 44 6.667969 L 44 43.332031 C 44 43.703125 43.703125 44 43.332031 44 L 6.667969 44 C 6.296875 44 6 43.703125 6 43.332031 L 6 6.667969 C 6 6.296875 6.296875 6 6.667969 6 Z M 23 23 L 23 35.574219 C 23 37.503906 22.269531 38 21 38 C 19.671875 38 18.75 37.171875 18.140625 36.097656 L 15 38 C 15.910156 39.925781 18.140625 42 21.234375 42 C 24.65625 42 27 40.179688 27 36.183594 L 27 23 Z M 35.453125 23 C 32.046875 23 29.863281 25.179688 29.863281 28.042969 C 29.863281 31.148438 31.695313 32.617188 34.449219 33.789063 L 35.402344 34.199219 C 37.140625 34.960938 38 35.425781 38 36.734375 C 38 37.824219 37.171875 38.613281 35.589844 38.613281 C 33.707031 38.613281 32.816406 37.335938 32 36 L 29 38 C 30.121094 40.214844 32.132813 42 35.675781 42 C 39.300781 42 42 40.117188 42 36.683594 C 42 33.496094 40.171875 32.078125 36.925781 30.6875 L 35.972656 30.28125 C 34.335938 29.570313 33.625 29.109375 33.625 27.964844 C 33.625 27.039063 34.335938 26.328125 35.453125 26.328125 C 36.550781 26.328125 37.253906 26.792969 37.90625 27.964844 L 40.878906 26.058594 C 39.625 23.84375 37.878906 23 35.453125 23 Z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 5 4 A 1.0001 1.0001 0 0 0 4 5 L 4 45 A 1.0001 1.0001 0 0 0 5 46 L 45 46 A 1.0001 1.0001 0 0 0 46 45 L 46 5 A 1.0001 1.0001 0 0 0 45 4 L 5 4 z M 6 6 L 44 6 L 44 44 L 6 44 L 6 6 z M 15 23 L 15 26.445312 L 20 26.445312 L 20 42 L 24 42 L 24 26.445312 L 29 26.445312 L 29 23 L 15 23 z M 36.691406 23.009766 C 33.576782 22.997369 30.017578 23.941219 30.017578 28.324219 C 30.017578 34.054219 37.738281 34.055625 37.738281 36.640625 C 37.738281 36.885625 37.842187 38.666016 35.117188 38.666016 C 32.392187 38.666016 30.121094 36.953125 30.121094 36.953125 L 30.121094 41.111328 C 30.121094 41.111328 42.001953 44.954062 42.001953 36.289062 C 42.000953 30.664063 34.208984 30.945391 34.208984 28.150391 C 34.208984 27.067391 34.978375 26.054687 37.109375 26.054688 C 39.240375 26.054688 41.126953 27.3125 41.126953 27.3125 L 41.267578 23.607422 C 41.267578 23.607422 39.113892 23.019408 36.691406 23.009766 z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0 0 50 50">
                                    <path d="M 25.167969 5.0058594 C 24.781877 4.9968865 24.394532 5.014912 24.007812 5.0625 C 22.976561 5.1894012 21.954004 5.5214624 21 6.0722656 C 19.738126 6.8008205 18.867614 7.8795934 18.189453 9.0625 L 18.074219 8.9960938 L 4.0742188 32.996094 L 4.1894531 33.0625 C 3.5013617 34.243015 3 35.539748 3 37 C 3 41.406432 6.5935644 45 11 45 C 13.946182 45 16.422265 43.319288 17.810547 40.9375 L 17.925781 41.003906 L 25 28.876953 L 32.074219 41.003906 C 34.278505 44.817 39.185255 46.130214 43 43.927734 C 46.816027 41.724515 48.130947 36.816015 45.927734 33 L 45.927734 32.998047 L 31.927734 9 C 30.481872 6.4957324 27.870611 5.0686695 25.167969 5.0058594 z M 25.130859 6.9960938 C 27.155284 7.0392142 29.104113 8.1100176 30.195312 10 L 30.197266 10.001953 L 30.197266 10.003906 L 44.197266 34.003906 C 45.856861 36.883192 44.878669 40.533286 42 42.195312 C 39.120015 43.8581 35.467467 42.879973 33.804688 40 L 33.802734 39.998047 L 19.804688 16 L 19.804688 15.998047 L 19.802734 15.996094 C 18.143139 13.116808 19.121331 9.4667144 22 7.8046875 C 22.719996 7.3889907 23.488252 7.1380832 24.261719 7.0410156 C 24.551769 7.0046153 24.841656 6.9899337 25.130859 6.9960938 z M 17.212891 14.441406 C 17.372969 15.315284 17.603702 16.188437 18.072266 17 L 18.072266 17.001953 L 23.841797 26.892578 L 18.728516 35.658203 C 18.065728 31.912371 14.931107 29 11 29 C 10.084587 29 9.236334 29.238188 8.4160156 29.523438 L 17.212891 14.441406 z M 11 31 C 14.325556 31 17 33.674446 17 37 C 17 40.325554 14.325556 43 11 43 C 7.6744439 43 5 40.325554 5 37 C 5 33.674446 7.6744439 31 11 31 z"></path>
                                </svg>
                            </div>
                            <div class="col-sm-12 owl-item">
                                <img style="width: 50px;" src="https://img.icons8.com/ios/50/laravel.png" alt="laravel" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact aniview" data-av-animation="slideInUp" id="contact">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <h3>CONTACTO</h3>
                        <hr class="line">
                    </div>
                    <div class="no-padding col-lg-6 col-md-12 col-xs-12">
                        <div class="main-text">
                            <div class="contact-wrap">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                                <h4>Email:</h4>
                                <a href="#">
                                    <p>contacto@digitaldev.cl</p>
                                </a>
                            </div>
                            <div class="contact-wrap">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                <h4>Ubicación:</h4>
                                <a href="#">
                                    <p>Talcahuano - Bio-Bio</p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="no-padding col-lg-6 col-md-12 col-xs-12">
                        <div class="form-bg">
                            <form id="form">
                                <input type="text" class="name" name="name" placeholder="Nombre" required /><br />
                                <input type="email" class="email" name="email" placeholder="Email" required /><br />
                                <textarea type="text" class="msg" name="text" placeholder="Mensaje" required></textarea>
                                <button type="button" id="boton_enviar_contacto" class="btn btn-block">ENVIAR MENSAJE</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <footer>
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-xs-12 text-center">
                        <img src="img/marca-dv-blanco2.png" style="width: 25%; margin-top:30px" alt="">
                        <h4>&copy; Todos los derechos reservados. <?php echo date('Y') ?></h4>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <div id="modalProyectos" class="modal fade" role="dialog" aria-hidden="true">
        <div class="modal-dialog classes-details modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 class="description-text text-justify"></h4>
                    <div class="row">
                        <div class="col-sm-5 order-sm-2 text-right">
                            <img class="modal-img" src="" alt="foto">
                        </div>
                        <div class="col-sm-7 order-sm-1">
                            <h5>Datos Generales</h5>
                            <ul class="timetable">
                                <li> Sitio Web: <a target="_blank" class="url" href=""></a> </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include 'modal_error_orden_alerta.php';
    include 'modal_bien_orden_alerta.php';
    ?>

    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.animatedheadline.min.js"></script>
    <script src="js/particles.js"></script>
    <script src="js/swiper.min.js"></script>
    <script src="js/common.js"></script>
    <script src="js/main.js"></script>
    <script src="https://kit.fontawesome.com/e0af1d2bbb.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/jquery-aniview/dist/jquery.aniview.js"></script>
    <script>
        $(document).ready(function() {
            // var owl = $('.owl-carousel');
        });
        $(".owl-carousel").owlCarousel({
            items: 7,
            dots: false,
            loop: true,
            margin: 50,
            autoplay: true,
            autoplayTimeout: 1000,
            autoplayHoverPause: true
        });
        // $('.aniview').AniView();
        $("#modalProyectos").on("show.bs.modal", function(t) {
            var e = $(t.relatedTarget),
                n = e.data("title"),
                i = e.data("img"),
                d = e.data("description"),
                u = e.data("url"),
                r = $(this);
            r.find(".modal-title").text(n), r.find(".description-text").text(d), r.find(".modal-img").attr("src", i), r.find(".url").attr("href", u), r.find(".url").text(u);
        });
        $('#boton_enviar_contacto').click(function() {
            var datos = $('#form').serialize();
            var url = "resultado_contacto.php";
            $.ajax({
                type: "POST",
                url: url,
                dataType: "json",
                data: datos,
                success: function(data) {
                    if (data.status == 1) {
                        $('#modal_bien_orden_alerta').modal("show");
                        $('#resp_bien_orden').html(data.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $('#modal_error_orden_alerta').modal("show");
                        $('#resp_error_orden').html(data.msg);
                    }
                }
            });
            return false;
        });
    </script>
    <script>
        (function() {
            const rot = document.getElementById('rot');
            const words = Array.from(rot.querySelectorAll('p'));
            const dur = 3; // segundos por palabra visible
            const total = dur * words.length;
            // Generar estilos dinámicos
            const style = document.createElement('style');
            words.forEach((w, i) => {
                w.style.animation = `wAnim ${total}s linear infinite`;
                w.style.animationDelay = (i * dur) + 's';
            });
            document.head.appendChild(style);
        })();
    </script>
</body>

</html>