<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'CatOps') ?></title>
    <meta name="description" content="<?= h($this->fetch('metaDescription') ?: 'CatOps crea soluciones digitales simples para negocios que necesitan presencia web, sistemas y automatización.') ?>">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
      :root {
        --bg: #fbfaf7;
        --surface: #ffffff;
        --ink: #17202a;
        --muted: #65717c;
        --line: #e7e0d8;
        --blue: #0a2a66;
        --orange: #f36b16;
        --shadow: 0 18px 44px rgba(42, 54, 71, 0.09);
      }
      * { box-sizing: border-box; }
      html { scroll-behavior: smooth; }
      body {
        margin: 0;
        background: linear-gradient(135deg, #fbfaf7 0%, #fff7ed 48%, #f1fbff 100%);
        color: var(--ink);
        font-family: Inter, Arial, sans-serif;
      }
      a { color: inherit; }
      p {
        color: var(--muted);
        font-size: 17px;
        line-height: 1.7;
      }
      .container {
        width: min(1120px, calc(100% - 32px));
        margin: 0 auto;
      }
      .topbar {
        position: sticky;
        top: 0;
        z-index: 20;
        border-bottom: 1px solid rgba(231, 224, 216, 0.8);
        background: rgba(255, 252, 248, 0.9);
        box-shadow: 0 10px 28px rgba(50, 55, 64, 0.06);
        backdrop-filter: blur(18px);
      }
      .nav {
        min-height: 76px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
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
      .links {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
      }
      .links a {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 13px;
        border-radius: 999px;
        color: var(--ink);
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
      }
      .links a:hover {
        background: rgba(243, 107, 22, 0.1);
        color: var(--orange);
      }
      .button {
        display: inline-flex;
        min-height: 46px;
        align-items: center;
        justify-content: center;
        padding: 0 18px;
        border-radius: 8px;
        background: var(--orange);
        color: #fff;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 14px 28px rgba(243, 107, 22, 0.22);
        text-align: center;
      }
      .button.secondary {
        border: 1px solid var(--line);
        background: #fff;
        color: var(--ink);
        box-shadow: none;
      }
      .hero {
        padding: clamp(58px, 8vw, 96px) 0 46px;
      }
      .hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr);
        gap: 40px;
        align-items: center;
      }
      .kicker {
        display: inline-flex;
        margin-bottom: 16px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(243, 107, 22, 0.12);
        color: var(--orange);
        font-size: 13px;
        font-weight: 900;
      }
      h1,
      h2,
      h3 {
        margin: 0;
        color: var(--ink);
        letter-spacing: 0;
      }
      h1 {
        max-width: 780px;
        font-size: clamp(38px, 6vw, 72px);
        line-height: 0.98;
      }
      h2 {
        font-size: clamp(28px, 4vw, 46px);
        line-height: 1.08;
      }
      h3 {
        font-size: 21px;
      }
      .lead {
        max-width: 700px;
        font-size: 19px;
      }
      .actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 26px;
      }
      .actions.center {
        justify-content: center;
      }
      .visual {
        min-height: 370px;
        border: 1px solid rgba(255, 255, 255, 0.74);
        border-radius: 24px;
        background:
          linear-gradient(135deg, rgba(10, 42, 102, 0.78), rgba(243, 107, 22, 0.7)),
          url("/img/responsive2.png") center/cover;
        box-shadow: 0 28px 80px rgba(56, 80, 99, 0.16);
      }
      .visual.service-visual {
        background:
          linear-gradient(135deg, rgba(10, 42, 102, 0.52), rgba(243, 107, 22, 0.28)),
          url("/img/service-platform-bg.png") center/cover;
      }
      section {
        padding: 56px 0;
      }
      .section-head {
        max-width: 760px;
        margin: 0 auto 30px;
        text-align: center;
      }
      .grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
      }
      .grid.two {
        grid-template-columns: repeat(2, 1fr);
      }
      .card {
        padding: 24px;
        border: 1px solid rgba(231, 224, 216, 0.78);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.76);
        box-shadow: var(--shadow);
      }
      .card p:last-child {
        margin-bottom: 0;
      }
      .card ul {
        display: grid;
        gap: 10px;
        margin: 18px 0 0;
        padding-left: 18px;
        color: var(--muted);
        line-height: 1.55;
      }
      .price {
        margin: 18px 0 8px;
        color: var(--blue);
        font-size: clamp(30px, 4vw, 46px);
        font-weight: 900;
      }
      .note {
        display: block;
        margin-top: 8px;
        color: var(--muted);
        font-size: 14px;
      }
      .cta {
        margin: 34px 0 70px;
        padding: clamp(24px, 4vw, 38px);
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(255, 241, 231, 0.95), rgba(255, 255, 255, 0.84));
        box-shadow: var(--shadow);
        text-align: center;
      }
      .cta.container {
        margin: 34px auto 70px;
      }
      .cta h2,
      .cta p {
        max-width: 760px;
        margin-left: auto;
        margin-right: auto;
      }
      footer {
        padding: 34px 0;
        border-top: 1px solid var(--line);
        background: #fff;
        text-align: center;
      }
      footer img { width: 112px; }
      @media (max-width: 820px) {
        .nav {
          align-items: center;
          flex-direction: column;
          padding: 12px 0;
        }
        .links {
          width: 100%;
          justify-content: center;
        }
        .hero-grid,
        .grid,
        .grid.two {
          grid-template-columns: 1fr;
        }
        .visual {
          min-height: 240px;
        }
      }
      @media (max-width: 520px) {
        .links {
          gap: 6px;
        }
        .links a {
          min-height: 34px;
          padding: 0 10px;
          font-size: 13px;
        }
        .hero {
          padding-top: 38px;
        }
        h1 {
          font-size: clamp(34px, 13vw, 48px);
        }
        .actions,
        .actions.center {
          align-items: stretch;
          flex-direction: column;
        }
        .button {
          width: 100%;
        }
        .card {
          padding: 20px;
        }
      }
    </style>
  </head>
  <body>
    <header class="topbar">
      <div class="container nav">
        <a class="brand" href="/"><img src="/img/catops-logo.png" alt="CatOps"></a>
        <nav class="links" aria-label="Navegación principal">
          <a href="/">Inicio</a>
          <a href="/servicio">Servicio</a>
          <a href="/planes">Planes</a>
          <a href="/login">Sistema</a>
        </nav>
      </div>
    </header>

    <main>
      <?= $this->fetch('content') ?>
    </main>

    <footer>
      <div class="container">
        <img src="/img/catops-logo.png" alt="CatOps">
        <p>Soluciones digitales simples para negocios que quieren vender, operar y crecer mejor.</p>
      </div>
    </footer>
  </body>
</html>
