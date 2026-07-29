<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'CatOps') ?></title>
    <meta name="description" content="<?= h($this->fetch('metaDescription') ?: 'CatOps permite crear cartas, catálogos y páginas de servicios fáciles de administrar.') ?>">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="/css/marketing.css">
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
  </head>
  <body class="marketing-page">
    <header class="topbar">
      <div class="container nav" data-marketing-nav>
        <a class="brand" href="/" aria-label="Ir al inicio de CatOps"><img src="/img/catops-logo.png" alt="CatOps"></a>
        <button class="nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="marketing-menu" data-marketing-toggle>☰</button>
        <nav aria-label="Navegación principal">
          <ul class="nav-links" id="marketing-menu">
            <li><a href="/#solucion">Solución</a></li>
            <li><a href="/#para-quien">Para quién es</a></li>
            <li><a href="/#como-funciona">Cómo funciona</a></li>
            <li><a href="/#ejemplos">Ejemplos</a></li>
            <li><a href="/planes">Planes</a></li>
            <li><a href="/#preguntas">Preguntas frecuentes</a></li>
            <li><a href="/login">Ingresar</a></li>
            <li><a class="nav-cta" href="/registro">Crear mi sitio</a></li>
          </ul>
        </nav>
      </div>
    </header>
    <main><?= $this->Flash->render() ?><?= $this->fetch('content') ?></main>
    <footer class="marketing-footer">
      <div class="container footer-layout">
        <a href="/" aria-label="Ir al inicio de CatOps"><img src="/img/catops-logo.png" alt="CatOps"></a>
        <nav class="footer-links" aria-label="Enlaces secundarios">
          <a href="/#como-funciona">Cómo funciona</a>
          <a href="/planes">Planes</a>
          <a href="/#preguntas">Preguntas frecuentes</a>
          <a href="/servicio">Servicio</a>
          <a href="https://www.instagram.com/catops.cl" target="_blank" rel="noopener">Instagram</a>
        </nav>
        <span>CatOps · Simple · Rápido · Efectivo</span>
      </div>
    </footer>
    <script>
      (() => {
        const nav = document.querySelector('[data-marketing-nav]');
        const toggle = document.querySelector('[data-marketing-toggle]');
        if (!nav || !toggle) return;
        toggle.addEventListener('click', () => {
          const open = nav.classList.toggle('is-open');
          toggle.setAttribute('aria-expanded', String(open));
          toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
          toggle.textContent = open ? '×' : '☰';
        });
        nav.querySelectorAll('.nav-links a').forEach((link) => link.addEventListener('click', () => {
          nav.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
          toggle.setAttribute('aria-label', 'Abrir menú');
          toggle.textContent = '☰';
        }));
      })();
    </script>
    <?= $this->fetch('script') ?>
  </body>
</html>
