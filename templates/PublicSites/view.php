<?php
$sections = [];
foreach ($site->site_sections ?? [] as $section) {
    $sections[$section->section_key] = $section;
}
$theme = $site->theme ?? null;
$primary = $theme->primary_color ?? '#f36b16';
$secondary = $theme->secondary_color ?? '#0a2a66';
$background = $theme->background_color ?? '#fbfaf7';
$font = $theme->font_family ?? 'Inter, Arial, sans-serif';
$title = $site->seo_title ?: $site->name;
$description = $site->seo_description ?: 'Sitio creado con CatOps.';
$hero = $sections['hero'] ?? null;
$services = $sections['services'] ?? null;
$benefits = $sections['benefits'] ?? null;
$contact = $sections['contact'] ?? null;
$businessAddress = trim((string)($site->business_address ?? ''));
$businessHours = trim((string)($site->business_hours ?? ''));
$publicPhone = trim((string)($site->public_phone ?? ''));
$publicEmail = trim((string)($site->public_email ?? ''));
$businessDetails = array_filter([
    'Dirección' => $businessAddress,
    'Horario' => $businessHours,
    'Teléfono' => $publicPhone,
    'Correo' => $publicEmail,
]);
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <meta name="description" content="<?= h($description) ?>">
    <?php if (!empty($canonicalUrl)): ?>
      <link rel="canonical" href="<?= h($canonicalUrl) ?>">
      <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= h($this->versionedAsset("/css/public-site.min.css")) ?>">
    <style>
      :root {
        --site-background: <?= h($background) ?>;
        --site-font: <?= h($font) ?>;
        --site-primary: <?= h($primary) ?>;
        --site-secondary: <?= h($secondary) ?>;
      }
    </style>
  </head>
  <body>
    <header>
      <nav class="site-wrap">
        <div>
          <?php if ($site->logo_path): ?>
            <img class="logo" src="/<?= h($site->logo_path) ?>" alt="<?= h($site->name) ?>">
          <?php else: ?>
            <span class="brand"><?= h($site->name) ?></span>
          <?php endif; ?>
        </div>
        <?php if ($site->whatsapp): ?>
          <a class="button" href="https://wa.me/<?= h($site->whatsapp) ?>" target="_blank" rel="noopener">WhatsApp</a>
        <?php endif; ?>
      </nav>
    </header>

    <main>
      <section class="site-wrap hero">
        <div>
          <h1><?= h($hero->title ?? $site->name) ?></h1>
          <p><?= h($hero->subtitle ?? $description) ?></p>
          <?php if (!empty($hero->content)): ?>
            <p><?= nl2br(h($hero->content)) ?></p>
          <?php endif; ?>
          <?php if ($site->whatsapp): ?>
            <a class="button" href="https://wa.me/<?= h($site->whatsapp) ?>" target="_blank" rel="noopener">Conversemos</a>
          <?php endif; ?>
        </div>
        <div class="visual" aria-hidden="true"></div>
      </section>

      <?php if ($services): ?>
        <section class="site-wrap">
          <h2><?= h($services->title) ?></h2>
          <p><?= h($services->subtitle) ?></p>
          <div class="cards">
            <?php foreach (array_filter(preg_split('/\r\n|\r|\n/', (string)$services->content)) as $item): ?>
              <article class="card">
                <strong><?= h($item) ?></strong>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($benefits): ?>
        <section class="site-wrap">
          <h2><?= h($benefits->title) ?></h2>
          <p><?= h($benefits->subtitle) ?></p>
          <div class="cards">
            <?php foreach (array_filter(preg_split('/\r\n|\r|\n/', (string)$benefits->content)) as $item): ?>
              <article class="card">
                <strong><?= h($item) ?></strong>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($contact): ?>
        <section class="site-wrap">
          <h2><?= h($contact->title) ?></h2>
          <p><?= h($contact->subtitle) ?></p>
          <p><?= nl2br(h($contact->content)) ?></p>
          <?php if ($site->whatsapp): ?>
            <a class="button" href="https://wa.me/<?= h($site->whatsapp) ?>" target="_blank" rel="noopener">Escribir ahora</a>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <?php if ($businessDetails): ?>
        <section class="site-wrap">
          <h2>Datos del negocio</h2>
          <div class="business-details">
            <?php foreach ($businessDetails as $label => $value): ?>
              <div>
                <b><?= h($label) ?></b>
                <?php if ($label === 'Correo'): ?>
                  <a href="mailto:<?= h($value) ?>"><?= h($value) ?></a>
                <?php else: ?>
                  <span><?= h($value) ?></span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </main>

    <footer class="site-wrap">
      <small><?= h($site->name) ?> · Sitio creado con CatOps</small>
    </footer>
  </body>
</html>
