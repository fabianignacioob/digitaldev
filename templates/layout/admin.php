<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($this->fetch('title') ?: 'Administración | CatOps') ?></title>
  <link rel="icon" type="image/x-icon" href="<?= h($this->versionedAsset('/favicon.ico')) ?>">
  <link rel="stylesheet" href="<?= h($this->versionedAsset('/css/bootstrap.min.css')) ?>">
  <link rel="stylesheet" href="<?= h($this->versionedAsset('/css/admin.min.css')) ?>">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark admin-nav sticky-top shadow-sm">
    <div class="container-fluid admin-page">
      <a class="navbar-brand" href="/admin"><span class="admin-brand-mark">CatOps</span> Admin</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminMenu" aria-controls="adminMenu" aria-expanded="false" aria-label="Abrir menú">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="adminMenu">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <?php foreach ([
              '/admin/users' => 'Usuarios', '/admin/sites' => 'Vitrinas', '/admin/subscriptions' => 'Suscripciones',
              '/admin/payments' => 'Pagos', '/admin/domains' => 'Dominios', '/admin/plans' => 'Planes',
              '/admin/audit-logs' => 'Auditoría', '/admin/system-status' => 'Sistema',
          ] as $url => $label): ?>
            <li class="nav-item"><a class="nav-link<?= str_starts_with((string)$this->getRequest()->getUri()->getPath(), $url) ? ' active' : '' ?>" href="<?= h($url) ?>"><?= h($label) ?></a></li>
          <?php endforeach; ?>
        </ul>
        <div class="d-flex align-items-lg-center gap-2">
          <span class="small text-white-50"><?= h($currentUser['name'] ?? '') ?></span>
          <a class="btn btn-sm btn-outline-light" href="/panel">Panel cliente</a>
          <a class="btn btn-sm btn-warning" href="/logout">Salir</a>
        </div>
      </div>
    </div>
  </nav>
  <main class="container-fluid admin-page py-4 py-lg-5">
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
  </main>
  <script src="/js/jquery-3.6.0.min.js"></script>
  <script src="/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('submit', function (event) {
      var form = event.target.closest('form[data-admin-confirm]');
      if (form && !window.confirm(form.dataset.adminConfirm)) {
        event.preventDefault();
      }
    });
  </script>
</body>
</html>
