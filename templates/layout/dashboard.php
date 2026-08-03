<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'Panel CatOps') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= h($this->versionedAsset('/favicon.ico')) ?>">
    <link rel="stylesheet" href="<?= h($this->versionedAsset('/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= h($this->versionedAsset('/css/dashboard.min.css')) ?>">
    <?= $this->fetch('css') ?>
</head>

<body>
    <?php $path = (string)$this->getRequest()->getUri()->getPath(); ?>
    <a class="skip-link" href="#contenido-principal">Saltar al contenido</a>
    <header class="panel-topbar">
        <div class="panel-shell panel-nav" data-panel-nav>
            <a class="panel-brand" href="/panel" aria-label="Ir a mis vitrinas"><img src="/img/catops-logo.png" alt="CatOps"></a>
            <button class="panel-nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="panel-menu" data-panel-nav-toggle>☰</button>
            <nav class="panel-links" id="panel-menu" aria-label="Navegación principal">
                <a href="/panel" <?= $path === '/panel' ? ' aria-current="page"' : '' ?>>Mis vitrinas</a>
                <a href="/sitios/nuevo" <?= $path === '/sitios/nuevo' ? ' aria-current="page"' : '' ?>>Nueva vitrina</a>
                <a href="/planes" <?= $path === '/planes' ? ' aria-current="page"' : '' ?>>Planes</a>
                <a class="nav-logout" href="/logout">Salir</a>
            </nav>
        </div>
    </header>
    <main class="panel-shell" id="contenido-principal">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/bootstrap.bundle.js"></script>
    <script>
        (() => {
            const nav = document.querySelector('[data-panel-nav]');
            const toggle = document.querySelector('[data-panel-nav-toggle]');
            if (!nav || !toggle) return;

            toggle.addEventListener('click', () => {
                const isOpen = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
                toggle.textContent = isOpen ? '×' : '☰';
            });
            nav.querySelectorAll('.panel-links a').forEach((link) => link.addEventListener('click', () => {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Abrir menú');
                toggle.textContent = '☰';
            }));
        })();
    </script>
</body>

</html>
