<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'CatOps') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= h($this->versionedAsset('/favicon.ico')) ?>">
    <link rel="stylesheet" href="<?= h($this->versionedAsset('/css/auth.min.css')) ?>">
  </head>
  <body>
    <main class="auth-card">
      <a href="/"><img class="auth-logo" src="/img/catops-logo.png" alt="CatOps"></a>
      <?= $this->Flash->render() ?>
      <?= $this->fetch('content') ?>
    </main>
  </body>
</html>
