<?php
$versionedAsset = static function (string $path): string {
    $normalized = '/' . ltrim($path, '/');
    $file = WWW_ROOT . str_replace('/', DS, ltrim($normalized, '/'));

    return is_file($file) ? $normalized . '?v=' . filemtime($file) : $normalized;
};
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'CatOps') ?></title>
    <meta name="description" content="<?= h($this->fetch('metaDescription') ?: 'CatOps permite crear cartas, catálogos y páginas de servicios fáciles de administrar.') ?>">
    <?php if (!empty($canonicalUrl)): ?>
      <link rel="canonical" href="<?= h($canonicalUrl) ?>">
      <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <?php endif; ?>
    <link rel="icon" type="image/x-icon" href="<?= h($versionedAsset('/favicon.ico')) ?>">
    <link rel="stylesheet" href="<?= h($versionedAsset('/css/marketing.css')) ?>">
    <link rel="stylesheet" href="<?= h($versionedAsset('/css/font-awesome.min.css')) ?>">
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
  </head>
  <body class="marketing-page">
    <header class="topbar">
      <div class="container nav" data-marketing-nav>
        <a class="brand" href="/" aria-label="Ir al inicio de CatOps"><img src="<?= h($versionedAsset('/img/catops-logo.png')) ?>" alt="CatOps"></a>
        <button class="nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="marketing-menu" data-marketing-toggle>
          <span class="nav-toggle-icon" aria-hidden="true">☰</span>
        </button>
        <nav aria-label="Navegación principal">
          <ul class="nav-links" id="marketing-menu">
            <li><a href="/#solucion"><i class="fa fa-lightbulb-o" aria-hidden="true"></i><span>Solución</span></a></li>
            <li><a href="/#para-quien"><i class="fa fa-users" aria-hidden="true"></i><span>Para quién es</span></a></li>
            <li><a href="/#como-funciona"><i class="fa fa-list-ol" aria-hidden="true"></i><span>Cómo funciona</span></a></li>
            <li><a href="/#ejemplos"><i class="fa fa-th-large" aria-hidden="true"></i><span>Ejemplos</span></a></li>
            <li><a href="/planes"><i class="fa fa-tag" aria-hidden="true"></i><span>Planes</span></a></li>
            <li><a href="/#preguntas"><i class="fa fa-question-circle" aria-hidden="true"></i><span>Preguntas frecuentes</span></a></li>
            <?php if (!empty($currentUser)): ?>
              <li><a href="/panel"><i class="fa fa-columns" aria-hidden="true"></i><span>Mis vitrinas</span></a></li>
              <li><a class="nav-cta" href="/sitios/nuevo"><i class="fa fa-plus-circle" aria-hidden="true"></i><span>Crear mi vitrina</span></a></li>
            <?php else: ?>
              <li><a href="/login"><i class="fa fa-sign-in" aria-hidden="true"></i><span>Ingresar</span></a></li>
              <li><a class="nav-cta" href="/registro"><i class="fa fa-plus-circle" aria-hidden="true"></i><span>Crear mi vitrina</span></a></li>
            <?php endif; ?>
            <li class="mobile-nav-brand" aria-hidden="true"><img src="<?= h($versionedAsset('/img/catops-logo-white.png')) ?>" alt=""></li>
          </ul>
        </nav>
      </div>
    </header>
    <div class="nav-backdrop" data-marketing-backdrop aria-hidden="true"></div>
    <main><?= $this->Flash->render() ?><?= $this->fetch('content') ?></main>
    <footer class="marketing-footer">
      <div class="container footer-layout">
        <section class="footer-brand" style="padding: 0;" aria-label="CatOps">
          <a href="/" aria-label="Ir al inicio de CatOps"><img src="<?= h($versionedAsset('/img/catops-logo-white.png')) ?>" alt="CatOps"></a>
          <!-- <p>Vitrinas digitales simples para que los pequeños negocios muestren, actualicen y compartan lo que hacen.</p> -->
          <a class="footer-instagram" href="https://www.instagram.com/catops.cl" target="_blank" rel="noopener">
            <i class="fa fa-instagram" aria-hidden="true"></i><span>Instagram</span>
          </a>
        </section>
        <nav class="footer-links" aria-label="CatOps">
          <h2>CatOps</h2>
          <a href="/#solucion">Solución</a>
          <a href="/#como-funciona">Cómo funciona</a>
          <a href="/planes">Planes</a>
          <a href="/servicio">Servicio</a>
        </nav>
        <nav class="footer-links" aria-label="Ayuda">
          <h2>Ayuda</h2>
          <a href="/#preguntas">Preguntas frecuentes</a>
          <a href="/login">Ingresar</a>
          <a href="/registro">Crear mi vitrina</a>
        </nav>
        <section class="footer-trust" style="padding: 0;" aria-label="Pagos y seguridad">
          <h2>Pagos y seguridad</h2>
          <div class="trust-item trust-item-webpay" aria-label="Webpay Plus de Transbank">
            <img src="<?= h($versionedAsset('/img/logo-webpay-plus/webpay-transbank.png')) ?>" alt="Webpay Plus de Transbank">
          </div>
          <div class="trust-item trust-item-ssl" aria-label="Conexión segura SSL/TLS">
            <span class="ssl-mark" aria-hidden="true"><i class="fa fa-lock"></i><b>SSL</b></span>
          </div>
        </section>
      </div>
      <div class="container footer-bottom">
        <span>&copy; <?= date('Y') ?> CatOps. Todos los derechos reservados.</span>
        <span>Vitrinas digitales para negocios en Chile.</span>
      </div>
    </footer>
    <script>
      (() => {
        const nav = document.querySelector('[data-marketing-nav]');
        const toggle = document.querySelector('[data-marketing-toggle]');
        const toggleIcon = toggle?.querySelector('.nav-toggle-icon');
        const backdrop = document.querySelector('[data-marketing-backdrop]');
        if (!nav || !toggle) return;
        const setOpen = (open) => {
          nav.classList.toggle('is-open', open);
          document.body.classList.toggle('marketing-nav-open', open);
          toggle.setAttribute('aria-expanded', String(open));
          toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
          if (toggleIcon) toggleIcon.textContent = open ? '×' : '☰';
        };
        toggle.addEventListener('click', () => setOpen(!nav.classList.contains('is-open')));
        backdrop?.addEventListener('click', () => setOpen(false));
        nav.querySelectorAll('.nav-links a').forEach((link) => link.addEventListener('click', () => {
          setOpen(false);
        }));
        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && nav.classList.contains('is-open')) setOpen(false);
        });
      })();
    </script>
    <?= $this->fetch('script') ?>
  </body>
</html>
