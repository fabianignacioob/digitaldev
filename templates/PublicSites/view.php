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
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <meta name="description" content="<?= h($description) ?>">
    <style>
      * { box-sizing: border-box; }
      body {
        margin: 0;
        background: <?= h($background) ?>;
        color: #17202a;
        font-family: <?= h($font) ?>;
      }
      .site-wrap {
        width: min(1080px, calc(100vw - 32px));
        margin: 0 auto;
      }
      header {
        position: sticky;
        top: 0;
        z-index: 5;
        border-bottom: 1px solid rgba(20, 30, 45, 0.08);
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(16px);
      }
      nav {
        min-height: 72px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
      }
      .logo {
        width: 52px;
        height: 52px;
        border: 1px solid rgba(20, 30, 45, .1);
        border-radius: 50%;
        background: #fff;
        object-fit: cover;
      }
      .brand {
        color: <?= h($secondary) ?>;
        font-size: 24px;
        font-weight: 900;
      }
      .hero {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 36px;
        align-items: center;
        min-height: calc(100vh - 72px);
        padding: 70px 0;
      }
      h1 {
        max-width: 780px;
        margin: 0 0 18px;
        color: <?= h($secondary) ?>;
        font-size: clamp(38px, 6vw, 76px);
        line-height: 0.98;
      }
      h2 {
        margin: 0 0 14px;
        color: <?= h($secondary) ?>;
        font-size: clamp(28px, 4vw, 48px);
      }
      p {
        color: #65717c;
        font-size: 18px;
        line-height: 1.7;
      }
      .button {
        display: inline-flex;
        min-height: 48px;
        align-items: center;
        justify-content: center;
        padding: 0 20px;
        border-radius: 999px;
        background: <?= h($primary) ?>;
        color: #fff;
        font-weight: 900;
        text-decoration: none;
      }
      .visual {
        min-height: 340px;
        border-radius: 34px;
        background:
          radial-gradient(circle at 26% 24%, rgba(255,255,255,.9), transparent 28%),
          linear-gradient(135deg, <?= h($primary) ?>, <?= h($secondary) ?>);
        opacity: .92;
      }
      section {
        padding: 70px 0;
      }
      .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
      }
      .card {
        padding: 24px;
        border: 1px solid rgba(20, 30, 45, .08);
        border-radius: 24px;
        background: rgba(255, 255, 255, .74);
        box-shadow: 0 18px 44px rgba(42, 54, 71, .08);
      }
      footer {
        padding: 36px 0;
        color: #65717c;
      }
      @media (max-width: 760px) {
        .hero {
          grid-template-columns: 1fr;
          min-height: auto;
          padding: 48px 0;
        }
        .visual { min-height: 220px; }
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
    </main>

    <footer class="site-wrap">
      <small><?= h($site->name) ?> · Sitio creado con CatOps</small>
    </footer>
  </body>
</html>
