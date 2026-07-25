<?php

declare(strict_types=1);

use App\Service\PlanService;

$plans = $plans ?? [];
$planService = new PlanService();
$trialPlan = null;
$commercialPlans = [];
foreach ($plans as $plan) {
    if ($planService->isTrialPlan($plan)) {
        $trialPlan = $plan;
        continue;
    }
    $commercialPlans[] = $plan;
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Crea un catálogo digital, carta digital o página de servicios para tu negocio. Actualiza productos, precios e imágenes y comparte un solo enlace por WhatsApp o Instagram.">
    <title>CatOps | Catálogo digital, carta digital y servicios para negocios</title>
    <link rel="canonical" href="https://catops.cl/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_CL">
    <meta property="og:url" content="https://catops.cl/">
    <meta property="og:title" content="CatOps | Tu catálogo, carta o servicios en un solo enlace">
    <meta property="og:description" content="Crea, actualiza y comparte una página para mostrar tus productos, precios y datos de contacto.">
    <meta property="og:image" content="https://catops.cl/img/responsive2.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="CatOps | Catálogo digital para pequeños negocios">
    <meta name="twitter:description" content="Carta digital, catálogo de productos o página de servicios fácil de actualizar.">
    <meta name="twitter:image" content="https://catops.cl/img/responsive2.png">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="/css/bootstrap.css">
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "CatOps",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "url": "https://catops.cl/",
            "description": "Plataforma para crear y compartir cartas digitales, catálogos de productos y páginas de servicios para pequeños negocios en Chile.",
            "areaServed": "Chile"
        }
    </script>
    <style>
        :root {
            --catops-blue: #0a2a66;
            --catops-blue-deep: #061b45;
            --catops-orange: #f36b16;
            --ink: #17202a;
            --muted: #5f6b76;
            --line: #dfe7ed;
            --surface: #ffffff;
            --soft-blue: #edf7ff;
            --soft-orange: #fff1e7;
            --soft-mint: #effcf7;
            --shadow: 0 18px 44px rgba(16, 42, 74, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: #fbfdff;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            letter-spacing: 0;
        }

        img {
            max-width: 100%;
        }

        a {
            color: inherit;
        }

        section {
            padding: 72px 0;
            scroll-margin-top: 88px;
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        h1,
        h2,
        h3 {
            color: var(--ink);
            letter-spacing: 0;
        }

        p {
            color: var(--muted);
            font-size: 16px;
            line-height: 1.65;
        }

        .container {
            width: min(1140px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(223, 231, 237, 0.9);
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(14px);
        }

        .nav-wrap {
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .brand img {
            display: block;
            width: 54px;
            height: 54px;
            border: 1px solid rgba(243, 107, 22, 0.25);
            border-radius: 50%;
            background: #fff;
            object-fit: cover;
        }

        .nav-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(243, 107, 22, 0.35);
            border-radius: 8px;
            background: #fff;
            color: var(--catops-orange);
            font-size: 22px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-links a {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            padding: 0 10px;
            color: #3d4a56;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .nav-links a:hover,
        .nav-links a:focus-visible {
            color: var(--catops-orange);
        }

        .nav-links .nav-cta {
            margin-left: 6px;
            padding: 0 15px;
            border-radius: 8px;
            background: var(--catops-orange);
            color: #fff;
            box-shadow: 0 10px 20px rgba(243, 107, 22, 0.2);
        }

        .nav-links .nav-cta:hover,
        .nav-links .nav-cta:focus-visible {
            background: #d9570d;
            color: #fff;
        }

        .hero {
            padding: clamp(48px, 7vw, 92px) 0 56px;
            background: linear-gradient(135deg, #f2f9ff 0%, #fff9f3 56%, #f5fffb 100%);
        }

        .hero-grid {
            display: grid;
            gap: 14px;
            align-items: center;
        }

        .eyebrow {
            display: inline-flex;
            margin-bottom: 16px;
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(243, 107, 22, 0.12);
            color: #bd4708;
            font-size: 13px;
            font-weight: 800;
        }

        .hero h1 {
            max-width: 670px;
            font-size: 58px;
            line-height: 1.02;
            font-weight: 800;
        }

        .hero-copy {
            max-width: 645px;
            margin-top: 18px;
            font-size: 18px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 26px;
        }

        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            padding: 0 18px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: var(--catops-orange);
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 12px 24px rgba(243, 107, 22, 0.22);
        }

        .button:hover,
        .button:focus-visible {
            background: #d9570d;
            color: #fff;
            transform: translateY(-1px);
        }

        .button-secondary {
            border-color: #b8c9d7;
            background: #fff;
            color: var(--catops-blue);
            box-shadow: none;
        }

        .button-secondary:hover,
        .button-secondary:focus-visible {
            background: var(--soft-blue);
            color: var(--catops-blue);
        }

        .support-line {
            margin-top: 18px;
            color: #52616c;
            font-size: 14px;
            font-weight: 700;
        }

        .hero-product {
            position: relative;
            overflow: hidden;
            /* padding: 12px; */
            border: 1px solid rgba(207, 222, 232, 0.9);
            border-radius: 14px;
            background: #fff;
            box-shadow: var(--shadow);
        }

        .hero-product img {
            display: block;
            width: 100%;
            border-radius: 8px;
        }

        .hero-caption {
            position: absolute;
            right: 26px;
            bottom: 26px;
            max-width: 220px;
            padding: 12px;
            border-radius: 8px;
            background: rgba(6, 27, 69, 0.9);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
        }

        .section-soft {
            background: #fff;
        }

        .section-tint {
            background: linear-gradient(180deg, var(--soft-blue), #fff);
        }

        .section-head {
            max-width: 720px;
            margin: 0 auto 32px;
            text-align: center;
        }

        .section-head h2 {
            font-size: clamp(28px, 3.6vw, 46px);
            line-height: 1.1;
            font-weight: 800;
        }

        .section-head p {
            margin: 14px auto 0;
        }

        .grid {
            display: grid;
            gap: 16px;
        }

        .card {
            min-height: 100%;
            padding: 24px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--surface);
        }

        .card h3 {
            margin-bottom: 10px;
            font-size: 20px;
            font-weight: 800;
        }

        .icon-number {
            display: inline-grid;
            width: 34px;
            height: 34px;
            place-items: center;
            margin-bottom: 14px;
            border-radius: 50%;
            background: var(--soft-orange);
            color: var(--catops-orange);
            font-weight: 900;
        }

        .type-card {
            display: flex;
            flex-direction: column;
        }

        .type-card .type-label {
            color: var(--catops-orange);
            font-size: 13px;
            font-weight: 800;
        }

        .type-card .type-link {
            margin-top: auto;
            padding-top: 16px;
            color: var(--catops-blue);
            font-weight: 800;
            text-decoration: none;
        }

        .type-card .type-link:hover {
            color: var(--catops-orange);
        }

        .steps {
            counter-reset: step;
        }

        .step {
            position: relative;
            padding-left: 58px;
        }

        .step::before {
            content: counter(step);
            counter-increment: step;
            position: absolute;
            top: 22px;
            left: 22px;
            display: grid;
            width: 24px;
            height: 24px;
            place-items: center;
            border-radius: 50%;
            background: var(--catops-blue);
            color: #fff;
            font-size: 13px;
            font-weight: 800;
        }

        .check-list {
            display: grid;
            gap: 12px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .check-list li {
            position: relative;
            padding-left: 26px;
            color: #35434f;
            font-weight: 700;
            line-height: 1.5;
        }

        .check-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--catops-orange);
            font-weight: 900;
        }

        .demo-layout {
            display: grid;
            gap: 26px;
            align-items: center;
        }

        .demo-media {
            padding: 10px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            box-shadow: var(--shadow);
        }

        .demo-media img {
            display: block;
            width: 100%;
            border-radius: 7px;
        }

        .demo-copy h2 {
            font-size: clamp(28px, 3.4vw, 42px);
            font-weight: 800;
        }

        .demo-copy p {
            margin-top: 14px;
        }

        .demo-note {
            padding: 14px;
            border-left: 3px solid var(--catops-orange);
            background: var(--soft-orange);
            color: #4d3a2f;
            font-size: 14px;
            line-height: 1.55;
        }

        .plans-grid {
            display: grid;
            gap: 16px;
        }

        .plan-card {
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .plan-card.is-recommended {
            border: 2px solid #f36b16;
            box-shadow: 0 16px 32px rgba(243, 107, 22, 0.12);
        }

        .plan-badge {
            position: absolute;
            top: -2px;
            right: 18px;
            padding: 5px 10px;
            border-radius: 0 0 7px 7px;
            background: var(--catops-orange);
            color: #fff;
            font-size: 12px;
            font-weight: 800;
        }

        .plan-card h3 {
            margin-bottom: 8px;
        }

        .plan-price {
            margin: 14px 0 6px;
            color: var(--catops-blue);
            font-size: 34px;
            font-weight: 900;
        }

        .plan-price small {
            font-size: 15px;
        }

        .plan-list {
            display: grid;
            gap: 8px;
            margin: 18px 0;
            padding: 0;
            list-style: none;
            color: var(--muted);
            line-height: 1.45;
        }

        .plan-list li::before {
            content: "•";
            margin-right: 8px;
            color: var(--catops-orange);
        }

        .plan-list .coming-soon {
            color: #826b58;
        }

        .plan-list .coming-soon::after {
            content: "Beta";
            display: inline-block;
            margin-left: 7px;
            padding: 2px 6px;
            border-radius: 999px;
            background: var(--soft-orange);
            color: #9b4b17;
            font-size: 11px;
            font-weight: 800;
        }

        .plan-card .button {
            margin-top: auto;
            align-self: flex-start;
        }

        .trial-callout {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin: 0 auto 28px;
            padding: 22px 26px;
            border: 1px solid rgba(243, 107, 22, .34);
            border-radius: 8px;
            background: #fff8f2;
        }

        .trial-callout h3 {
            margin: 0 0 5px;
            font-size: 20px;
        }

        .trial-callout p {
            margin: 0;
            font-size: 14px;
        }

        .plan-notice {
            margin: 22px auto 0;
            max-width: 700px;
            text-align: center;
            color: #46545f;
            font-size: 14px;
            font-weight: 700;
        }

        .trust-grid {
            display: grid;
            gap: 14px;
        }

        .trust-item {
            padding: 18px;
            border-radius: 8px;
            background: var(--soft-mint);
            color: #25473d;
            font-weight: 700;
        }

        .faq-list {
            max-width: 850px;
            margin: 0 auto;
        }

        .faq-list details {
            border-bottom: 1px solid var(--line);
        }

        .faq-list summary {
            padding: 18px 4px;
            color: var(--ink);
            font-weight: 800;
            cursor: pointer;
        }

        .faq-list p {
            padding: 0 4px 18px;
        }

        .final-cta {
            background: linear-gradient(135deg, var(--catops-blue), #164c98);
            color: #fff;
            text-align: center;
        }

        .final-cta h2 {
            color: #fff;
            font-size: clamp(30px, 4vw, 48px);
            font-weight: 800;
        }

        .final-cta p {
            max-width: 650px;
            margin: 14px auto 0;
            color: rgba(255, 255, 255, 0.86);
        }

        .final-cta .actions {
            justify-content: center;
        }

        .final-cta .button-secondary {
            border-color: rgba(255, 255, 255, 0.75);
            background: #fff;
            color: var(--catops-blue);
        }

        footer {
            padding: 36px 0;
            background: #fff;
            border-top: 1px solid var(--line);
        }

        .footer-layout {
            display: grid;
            gap: 20px;
            align-items: center;
        }

        .footer-brand img {
            width: 110px;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
        }

        .footer-links a {
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: var(--catops-orange);
        }

        .footer-note {
            color: var(--muted);
            font-size: 13px;
        }

        @media (min-width: 700px) {
            .hero-grid {
                grid-template-columns: minmax(0, 1fr) minmax(380px, 0.95fr);
            }

            .grid.three,
            .trust-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .plans-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .demo-layout {
                grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
            }

            .footer-layout {
                grid-template-columns: 150px 1fr auto;
            }
        }

        @media (max-width: 980px) {
            .nav-toggle {
                display: inline-grid;
                place-items: center;
            }

            .nav-wrap {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 12px;
            }

            .nav-wrap>nav {
                grid-column: 1 / -1;
                min-width: 0;
            }

            .nav-links {
                display: none;
                flex: 0 0 100%;
                width: 100%;
                align-items: stretch;
                padding: 8px 0 16px;
            }

            .nav-wrap.is-open .nav-links {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .nav-links a {
                justify-content: center;
                border-radius: 7px;
            }

            .nav-links .nav-cta {
                margin: 0;
            }
        }

        @media (max-width: 640px) {
            .trial-callout {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 520px) {
            section {
                padding: 56px 0;
            }

            .container {
                width: min(100% - 28px, 1140px);
            }

            .hero {
                padding-top: 40px;
            }

            .hero h1 {
                font-size: 38px;
            }

            .hero-caption {
                right: 20px;
                bottom: 20px;
                max-width: 185px;
            }

            .actions .button {
                width: 100%;
            }

            .nav-wrap.is-open .nav-links {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="container nav-wrap">
            <a class="brand" href="#inicio" aria-label="Ir al inicio"><img src="/img/catops-logo.png" alt="CatOps"></a>
            <button class="nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="primary-menu">☰</button>
            <nav aria-label="Navegación principal">
                <ul class="nav-links" id="primary-menu">
                    <li><a href="#como-funciona">Cómo funciona</a></li>
                    <li><a href="#para-quien">Para quién es</a></li>
                    <li><a href="#ejemplos">Ejemplos</a></li>
                    <li><a href="#planes">Planes</a></li>
                    <li><a href="#preguntas">Preguntas frecuentes</a></li>
                    <li><a href="/login">Ingresar</a></li>
                    <li><a class="nav-cta" href="/registro">Crear mi sitio</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero" id="inicio">
            <div class="container hero-grid">
                <div>
                    <span class="eyebrow">Para pequeños negocios en Chile</span>
                    <h1>Tu catálogo, carta o servicios en un solo enlace</h1>
                    <p class="hero-copy">Crea una página sencilla para mostrar tus productos, precios, imágenes y datos de contacto. Actualízala cuando quieras y compártela por WhatsApp, Instagram o donde vendas.</p>
                    <div class="actions">
                        <a class="button" href="/registro">Crear mi sitio</a>
                        <a class="button button-secondary" href="#ejemplos">Ver un ejemplo</a>
                    </div>
                    <p class="support-line">Sin conocimientos técnicos · Adaptado a celular · Fácil de actualizar</p>
                </div>
                <div class="hero-product">
                    <img src="/img/headbanner.png" alt="CatOps mostrado en notebook, celular y tablet">
                    <!-- <span class="hero-caption">Un solo enlace para mostrar lo que vendes y recibir consultas.</span> -->
                </div>
            </div>
        </section>

        <section class="section-soft" id="problema">
            <div class="container">
                <div class="section-head">
                    <h2>Deja de enviar fotos, precios y menús uno por uno</h2>
                    <p>Reúne la información de tu negocio en una página siempre disponible para tus clientes.</p>
                </div>
                <div class="grid three">
                    <article class="card"><span class="icon-number">1</span>
                        <h3>Todo ordenado</h3>
                        <p>Productos, servicios, precios, imágenes y contacto en un lugar fácil de revisar.</p>
                    </article>
                    <article class="card"><span class="icon-number">2</span>
                        <h3>Siempre actualizado</h3>
                        <p>Cambia un precio, agrega una foto o edita un servicio desde tu panel cuando lo necesites.</p>
                    </article>
                    <article class="card"><span class="icon-number">3</span>
                        <h3>Fácil de compartir</h3>
                        <p>Envía el mismo enlace por WhatsApp, Instagram, bio, QR o redes sociales.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section-tint" id="para-quien">
            <div class="container">
                <div class="section-head">
                    <h2>Elige el tipo de sitio que necesita tu negocio</h2>
                    <p>Una base simple para restaurantes, tiendas, emprendimientos y servicios que venden por mensaje.</p>
                </div>
                <div class="grid three">
                    <article class="card type-card"><span class="type-label">Para restaurantes y cafeterías</span>
                        <h3>Carta digital</h3>
                        <p>Muestra preparaciones, precios e imágenes en una carta que puedes actualizar sin reenviar archivos.</p><a class="type-link" href="/registro">Crear una carta →</a>
                    </article>
                    <article class="card type-card"><span class="type-label">Para tiendas y emprendimientos</span>
                        <h3>Catálogo de productos</h3>
                        <p>Ordena productos, fotos, descripciones y precios para que tus clientes consulten desde un solo enlace.</p><a class="type-link" href="/registro">Crear un catálogo →</a>
                    </article>
                    <article class="card type-card"><span class="type-label">Para profesionales y prestadores</span>
                        <h3>Catálogo de servicios</h3>
                        <p>Presenta lo que haces, valores referenciales y formas de contacto para recibir consultas por WhatsApp.</p><a class="type-link" href="/registro">Crear mis servicios →</a>
                    </article>
                </div>
            </div>
        </section>

        <section class="section-soft" id="como-funciona">
            <div class="container">
                <div class="section-head">
                    <h2>Publica en tres pasos</h2>
                    <p>Sin instalar programas ni depender de conocimientos técnicos.</p>
                </div>
                <div class="grid three steps">
                    <article class="card step">
                        <h3>Elige el tipo de sitio</h3>
                        <p>Parte con una carta digital, catálogo de productos o catálogo de servicios.</p>
                    </article>
                    <article class="card step">
                        <h3>Agrega tu contenido</h3>
                        <p>Sube tu logo, fotos, productos, precios, descripciones y datos de contacto.</p>
                    </article>
                    <article class="card step">
                        <h3>Publica y comparte</h3>
                        <p>Activa tu enlace y compártelo donde tus clientes ya te encuentran.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section-tint" id="beneficios">
            <div class="container">
                <div class="section-head">
                    <h2>Hecho para el día a día de un negocio pequeño</h2>
                    <p>Lo importante es que tu información esté clara, disponible y sea fácil de mantener.</p>
                </div>
                <div class="grid three">
                    <article class="card">
                        <ul class="check-list">
                            <li>No necesitas programación.</li>
                            <li>Funciona en celulares.</li>
                        </ul>
                    </article>
                    <article class="card">
                        <ul class="check-list">
                            <li>Actualizas desde tu panel.</li>
                            <li>Compartes un solo enlace.</li>
                        </ul>
                    </article>
                    <article class="card">
                        <ul class="check-list">
                            <li>Recibes consultas por WhatsApp.</li>
                            <li>Mantienes precios y servicios al día.</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        <section class="section-soft" id="ejemplos">
            <div class="container demo-layout">
                <div class="demo-media"><img src="/img/responsive2.png" alt="Ejemplo de un sitio CatOps adaptado a notebook, celular y tablet" loading="lazy"></div>
                <div class="demo-copy">
                    <span class="eyebrow">Así se ve tu información</span>
                    <h2>Tu negocio se ve bien desde cualquier pantalla</h2>
                    <p>La misma página se adapta para quien la abre desde un celular, tablet o computador. Es el formato que usan tus clientes cuando llegan desde WhatsApp o Instagram.</p>
                    <p class="demo-note">La imagen muestra una vista real del formato responsivo de CatOps. El contenido, logo y colores se configuran desde el panel.</p>
                    <div class="actions"><a class="button button-secondary" href="/servicio">Ver cómo funciona el servicio</a></div>
                </div>
            </div>
        </section>

        <section class="section-tint" id="planes">
            <div class="container">
                <div class="section-head">
                    <h2>Parte gratis y escala cuando tu negocio lo necesite</h2>
                    <p>La prueba comienza cuando publicas. Revisa con claridad qué incluye cada plan hoy y qué herramientas están en modalidad Beta.</p>
                </div>
                <?php if ($trialPlan): ?>
                    <div class="trial-callout">
                        <div>
                            <h3>Solicita tu prueba gratuita del plan Básico por 7 días</h3>
                            <p>Sin tarjeta. El período comienza cuando publiques tu primer sitio.</p>
                        </div>
                        <a class="button" href="/registro?plan=<?= rawurlencode((string)$trialPlan->slug) ?>">Solicitar prueba</a>
                    </div>
                <?php endif; ?>
                <div class="plans-grid">
                    <?php foreach ($commercialPlans as $plan): ?>
                        <?php
                        $benefits = $planService->commercialBenefitRows($plan);
                        $todayBenefits = array_filter($benefits, static fn (array $row): bool => $row['status'] === 'available');
                        $futureBenefits = array_filter($benefits, static fn (array $row): bool => $row['status'] === 'coming_soon');
                        $badge = trim((string)($plan->commercial_badge ?? ''));
                        ?>
                        <article class="card plan-card<?= $badge !== '' ? ' is-recommended' : '' ?>">
                            <?php if ($badge !== ''): ?><span class="plan-badge"><?= h($badge) ?></span><?php endif; ?>
                            <h3><?= h($plan->name) ?></h3>
                            <p><?= h((string)($plan->commercial_description ?: 'Para publicar y mantener tu información actualizada.')) ?></p>
                            <div class="plan-price">$<?= number_format((int)$plan->monthly_price, 0, ',', '.') ?><small>/mes</small></div>
                            <p class="small fw-bold mb-1">Incluye hoy</p>
                            <ul class="plan-list"><?php foreach ($todayBenefits as $benefit): ?><li><strong><?= h($benefit['label']) ?>:</strong> <?= h($benefit['value']) ?></li><?php endforeach; ?></ul>
                            <?php if ($futureBenefits): ?><ul class="plan-list"><?php foreach ($futureBenefits as $benefit): ?><li class="coming-soon"><strong><?= h($benefit['label']) ?>:</strong> <?= h($benefit['value']) ?></li><?php endforeach; ?></ul><?php endif; ?>
                            <a class="button" href="/registro?plan=<?= rawurlencode((string)$plan->slug) ?>">Elegir <?= h($plan->name) ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>
                <p class="plan-notice">Renovación mensual o anual mediante pago seguro. Sin cobros automáticos.</p>
            </div>
        </section>

        <section class="section-soft" id="confianza">
            <div class="container">
                <div class="section-head">
                    <h2>Una herramienta simple para mostrar lo que haces</h2>
                    <p>CatOps se enfoca en darte una página clara para que tus clientes entiendan tu oferta y puedan contactarte.</p>
                </div>
                <div class="trust-grid">
                    <div class="trust-item">Tu contenido se administra desde un panel privado.</div>
                    <div class="trust-item">Tu enlace se puede compartir por WhatsApp o Instagram. El código QR estará disponible en Beta.</div>
                    <div class="trust-item">Puedes comenzar con un subdominio de CatOps.</div>
                </div>
            </div>
        </section>

        <section class="section-tint" id="preguntas">
            <div class="container">
                <div class="section-head">
                    <h2>Preguntas frecuentes</h2>
                    <p>Lo esencial antes de crear tu sitio.</p>
                </div>
                <div class="faq-list">
                    <details>
                        <summary>¿Necesito saber programación?</summary>
                        <p>No. El panel está pensado para editar textos, productos, precios, imágenes y datos de contacto sin programar.</p>
                    </details>
                    <details>
                        <summary>¿Puedo actualizar mis productos o servicios?</summary>
                        <p>Sí. Puedes mantener precios, descripciones, fotos y disponibilidad de contenido desde tu panel.</p>
                    </details>
                    <details>
                        <summary>¿Puedo usar mi propio dominio?</summary>
                        <p>Puedes comenzar con un subdominio de CatOps. La conexión de un dominio propio se revisa y configura antes de activarla; no se realiza automáticamente hoy.</p>
                    </details>
                    <details>
                        <summary>¿Se ve bien en celulares?</summary>
                        <p>Sí. Las páginas se construyen para funcionar en celular, tablet y computador.</p>
                    </details>
                    <details>
                        <summary>¿Incluye carrito o compras online?</summary>
                        <p>No. CatOps está pensado para mostrar tu oferta y recibir consultas por WhatsApp; no incluye carrito ni pagos en línea para clientes finales.</p>
                    </details>
                    <details>
                        <summary>¿Cómo funciona la renovación?</summary>
                        <p>La suscripción cubre períodos mensuales y se renueva después de la confirmación del pago. No existen cobros automáticos.</p>
                    </details>
                    <details>
                        <summary>¿Qué pasa si necesito ayuda?</summary>
                        <p>Puedes contactarnos para resolver dudas sobre la configuración y el uso de tu sitio.</p>
                    </details>
                </div>
            </div>
        </section>

        <section class="final-cta" id="crear">
            <div class="container">
                <h2>Publica lo que vendes en un solo enlace</h2>
                <p>Parte con una carta, catálogo o página de servicios que puedas actualizar cuando quieras.</p>
                <div class="actions"><a class="button" href="/registro">Crear mi sitio</a><a class="button button-secondary" href="/login">Ingresar</a></div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container footer-layout">
            <a class="footer-brand" href="#inicio" aria-label="Ir al inicio"><img src="/img/catops-logo.png" alt="CatOps"></a>
            <nav class="footer-links" aria-label="Enlaces secundarios"><a href="#como-funciona">Cómo funciona</a><a href="#planes">Planes</a><a href="#preguntas">Preguntas frecuentes</a><a href="/servicio">¿Necesitas algo a medida?</a><a href="https://www.instagram.com/catops.cl" target="_blank" rel="noopener">Instagram</a></nav>
            <span class="footer-note">© <span id="current-year"></span> CatOps</span>
        </div>
    </footer>

    <script>
        document.getElementById('current-year').textContent = new Date().getFullYear();
        const navWrap = document.querySelector('.nav-wrap');
        const toggle = document.querySelector('.nav-toggle');
        const links = document.querySelectorAll('.nav-links a');
        toggle.addEventListener('click', () => {
            const open = navWrap.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
            toggle.textContent = open ? '×' : '☰';
        });
        links.forEach((link) => link.addEventListener('click', () => {
            navWrap.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Abrir menú');
            toggle.textContent = '☰';
        }));
    </script>
</body>

</html>