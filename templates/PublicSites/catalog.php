<?php
use App\Service\CatalogTypography;

$setting = $site->catalog_setting ?? null;
$theme = $site->theme ?? null;
$primary = $theme->primary_color ?? '#f36b16';
$secondary = $theme->secondary_color ?? '#0a2a66';
$backgroundColor = $setting->background_color ?? ($theme->background_color ?? '#fbfaf7');
$backgroundImage = $setting && $setting->background_type === 'image' && $setting->background_image_path
    ? '/' . $setting->background_image_path
    : null;
$headingFont = CatalogTypography::normalize($setting->heading_font ?? null);
$titleFont = CatalogTypography::normalize($setting->title_font ?? $headingFont);
$sloganFont = CatalogTypography::normalize($setting->slogan_font ?? null);
$titleColor = $setting->title_color ?? '#ffffff';
$sloganColor = $setting->slogan_color ?? '#ffffff';
$validColor = static function (?string $color, string $fallback): string {
    $color = trim((string)$color);

    return preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $color) ? $color : $fallback;
};
$backgroundColor = $validColor((string)$backgroundColor, '#fbfaf7');
$titleColor = $validColor((string)$titleColor, '#17202a');
$sloganColor = $validColor((string)$sloganColor, '#17202a');
$relativeLuminance = static function (string $hex): float {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = implode('', array_map(static fn(string $value): string => $value . $value, str_split($hex)));
    }
    if (!preg_match('/^[0-9a-f]{6}$/i', $hex)) {
        return 0.0;
    }
    $channels = array_map(static function (string $channel): float {
        $value = hexdec($channel) / 255;

        return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
    }, str_split($hex, 2));

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
};
$contrastRatio = static function (string $foreground, string $background) use ($relativeLuminance): float {
    $lighter = max($relativeLuminance($foreground), $relativeLuminance($background));
    $darker = min($relativeLuminance($foreground), $relativeLuminance($background));

    return ($lighter + 0.05) / ($darker + 0.05);
};
$accessibleColor = static function (string $preferred, string $background, float $minimum) use ($contrastRatio): string {
    if ($contrastRatio($preferred, $background) >= $minimum) {
        return $preferred;
    }

    return $contrastRatio('#17202a', $background) >= $contrastRatio('#ffffff', $background) ? '#17202a' : '#ffffff';
};
$actionColor = $contrastRatio((string)$primary, '#ffffff') >= 4.5 ? (string)$primary : '#c6530b';
$titleColor = $backgroundImage ? $titleColor : $accessibleColor($titleColor, $backgroundColor, 3.0);
$sloganColor = $backgroundImage ? $sloganColor : $accessibleColor($sloganColor, $backgroundColor, 4.5);
$pageBackground = $backgroundImage
    ? 'url("' . h($backgroundImage) . '") center / cover fixed no-repeat'
    : h($backgroundColor);
$heroBackground = $backgroundImage ? 'transparent' : h($backgroundColor);
$title = $setting->title ?? $site->name;
$slogan = $setting->slogan ?? 'Nuestra carta';
$intro = $setting->intro_text ?? null;
$categorizedIds = [];
$templateSlug = $site->template->slug ?? 'carta-simple';
$usesCategories = in_array($templateSlug, ['carta-categorias', 'catalogo-categorias'], true);
$kindLabel = str_starts_with((string)$templateSlug, 'catalogo-') ? 'Catálogo' : 'Carta';
$whatsapp = ($site->whatsapp_country_code ?? '') . ($site->whatsapp_number ?? '');
if (!$whatsapp) {
    $whatsapp = $site->whatsapp ?? '';
}
$whatsapp = preg_replace('/\D+/', '', (string)$whatsapp);
$contactLabel = $kindLabel === 'Catálogo' ? 'Consultar' : 'Pedir por WhatsApp';
$siteNameForMessage = trim((string)$site->name);
$defaultContactMessage = rawurlencode('Hola, quiero consultar por ' . $siteNameForMessage . '.');
$productFallback = '/img/placeholder.png';
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($site->seo_title ?: $title) ?></title>
    <meta name="description" content="<?= h($site->seo_description ?: $slogan) ?>">
    <style>
      * { box-sizing: border-box; }
      body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background: <?= $pageBackground ?>;
        color: #17202a;
        font-family: Inter, Arial, sans-serif;
      }
      main { flex: 1; }
      .hero {
        position: relative;
        min-height: 34vh;
        display: grid;
        align-items: center;
        overflow: hidden;
        padding: 44px 0;
        background: <?= $heroBackground ?>;
      }
      .container {
        width: min(1060px, calc(100vw - 32px));
        margin: 0 auto;
      }
      .topbar {
        position: sticky;
        top: 0;
        z-index: 5;
        border-bottom: 1px solid rgba(255,255,255,.22);
        background: rgba(255,255,255,.82);
        backdrop-filter: blur(16px);
      }
      .skip-link {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 20;
        padding: 10px 14px;
        border-radius: 999px;
        background: <?= h($secondary) ?>;
        color: #fff;
        font-weight: 800;
        text-decoration: none;
        transform: translateY(-160%);
      }
      .skip-link:focus { transform: translateY(0); }
      a:focus-visible {
        outline: 3px solid <?= h($secondary) ?>;
        outline-offset: 3px;
      }
      .nav {
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
      .whatsapp {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
        border-radius: 999px;
        background: <?= h($actionColor) ?>;
        color: #fff;
        font-weight: 900;
        text-decoration: none;
      }
      h1,
      h2,
      h3 {
        margin: 0;
        letter-spacing: 0;
      }
      .catalog-title {
        color: <?= h($titleColor) ?>;
        font-family: <?= h($titleFont) ?>;
        font-size: clamp(32px, 6vw, 62px);
        line-height: 1;
        text-align: center;
      }
      .category-title {
        margin-bottom: 18px;
        color: <?= h($secondary) ?>;
        font-size: clamp(26px, 3vw, 40px);
      }
      .lead {
        max-width: 720px;
        margin-right: auto;
        margin-left: auto;
        color: <?= h($sloganColor) ?>;
        font-family: <?= h($sloganFont) ?>;
        font-size: 19px;
        line-height: 1.65;
        text-align: center;
      }
      section {
        padding: 44px 0;
      }
      .category {
        margin-bottom: 42px;
      }
      .products {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
      }
      .product {
        overflow: hidden;
        border: 1px solid rgba(20, 30, 45, .08);
        border-radius: 22px;
        background: rgba(255,255,255,.82);
        box-shadow: 0 18px 44px rgba(42,54,71,.08);
      }
      .product-image {
        aspect-ratio: 4 / 3;
        background: #fff1e7;
      }
      .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      .product-body {
        padding: 18px;
      }
      .product h3 {
        color: #17202a;
        font-size: 20px;
      }
      .product p {
        color: #65717c;
        line-height: 1.55;
      }
      .price-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-top: 14px;
      }
      .price {
        color: <?= h($secondary) ?>;
        font-size: 22px;
        font-weight: 900;
      }
      .discount {
        padding: 5px 9px;
        border-radius: 999px;
        background: rgba(243, 107, 22, .12);
        color: <?= h($actionColor) ?>;
        font-size: 13px;
        font-weight: 900;
      }
      .featured {
        display: inline-flex;
        margin-bottom: 10px;
        padding: 5px 9px;
        border-radius: 999px;
        background: rgba(10, 42, 102, .1);
        color: <?= h($secondary) ?>;
        font-size: 12px;
        font-weight: 900;
      }
      .duration {
        margin-top: 8px;
        color: <?= h($secondary) ?>;
        font-size: 14px;
        font-weight: 800;
      }
      .product-action {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        margin-top: 14px;
        padding: 0 13px;
        border-radius: 999px;
        background: <?= h($actionColor) ?>;
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        text-decoration: none;
      }
      .empty {
        padding: 24px;
        border: 1px dashed rgba(20,30,45,.16);
        border-radius: 20px;
        background: rgba(255,255,255,.64);
        color: #65717c;
        text-align: center;
      }
      footer {
        padding: 34px 0;
        border-top: 1px solid rgba(20,30,45,.08);
        background: rgba(255,255,255,.86);
        color: #65717c;
        text-align: center;
      }
      @media (max-width: 640px) {
        .nav {
          align-items: flex-start;
          flex-direction: column;
          padding: 12px 0;
        }
        .hero { min-height: 30vh; }
        .products {
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 12px;
        }
        .product-body { padding: 14px; }
        .product h3 { font-size: 17px; }
        .price { font-size: 18px; }
      }
      @media (max-width: 420px) {
        .products {
          grid-template-columns: 1fr;
        }
      }
    </style>
  </head>
  <body>
    <a class="skip-link" href="#contenido-principal">Saltar al contenido</a>
    <header class="topbar">
      <nav class="container nav" aria-label="Navegación principal">
        <div>
          <?php if ($site->logo_path): ?>
            <img class="logo" src="/<?= h($site->logo_path) ?>" alt="<?= h($site->name) ?>">
          <?php else: ?>
            <span class="brand"><?= h($site->name) ?></span>
          <?php endif; ?>
        </div>
        <?php if ($whatsapp): ?>
          <a class="whatsapp" href="https://wa.me/<?= h($whatsapp) ?>" target="_blank" rel="noopener">Pedir por WhatsApp</a>
        <?php endif; ?>
      </nav>
    </header>

    <main id="contenido-principal">
      <section class="hero">
        <div class="container">
          <h1 class="catalog-title"><?= h($title) ?></h1>
          <p class="lead"><?= h($slogan) ?></p>
          <?php if ($intro): ?>
            <p class="lead"><?= nl2br(h($intro)) ?></p>
          <?php endif; ?>
        </div>
      </section>

      <section>
        <div class="container">
          <?php if ($usesCategories): ?>
            <?php foreach ($site->catalog_categories ?? [] as $category): ?>
              <div class="category">
                <h2 class="category-title"><?= h($category->name) ?></h2>
                <div class="products">
                  <?php foreach ($category->catalog_products ?? [] as $product): ?>
                    <?php
                    if (!$product->active) {
                        continue;
                    }
                    $categorizedIds[] = $product->id;
                    ?>
                    <article class="product">
                      <div class="product-image">
                        <img src="<?= h($product->image_path ? '/' . $product->image_path : $productFallback) ?>" alt="<?= h($product->name) ?>" width="800" height="600" loading="lazy" decoding="async">
                      </div>
                      <div class="product-body">
                        <?php if ($product->featured): ?>
                          <span class="featured">Destacado</span>
                        <?php endif; ?>
                        <h3><?= h($product->name) ?></h3>
                        <?php if ($product->description): ?>
                          <p><?= h($product->description) ?></p>
                        <?php endif; ?>
                        <?php if ($product->duration): ?>
                          <div class="duration"><?= h($product->duration) ?></div>
                        <?php endif; ?>
                        <div class="price-row">
                          <?php if ($product->price !== null): ?>
                            <span class="price"><?= h($product->price_prefix ? $product->price_prefix . ' ' : '') ?>$<?= number_format((float)$product->price, 0, ',', '.') ?></span>
                          <?php else: ?>
                            <span class="price">Consultar</span>
                          <?php endif; ?>
                          <?php if ($product->discount): ?>
                            <span class="discount">Descuento $<?= number_format((float)$product->discount, 0, ',', '.') ?></span>
                          <?php endif; ?>
                        </div>
                        <?php if ($whatsapp): ?>
                          <?php $contactMessage = rawurlencode('Hola, quiero consultar por ' . trim((string)$product->name) . ' en ' . $siteNameForMessage . '.'); ?>
                          <a class="product-action" href="https://wa.me/<?= h($whatsapp) ?>?text=<?= $contactMessage ?: $defaultContactMessage ?>" target="_blank" rel="noopener"><?= h($product->item_type === 'service' ? 'Cotizar' : $contactLabel) ?></a>
                        <?php endif; ?>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>

            <?php
            $uncategorized = [];
            foreach ($site->catalog_products ?? [] as $product) {
                if ($product->active && !$product->catalog_category_id && !in_array($product->id, $categorizedIds, true)) {
                    $uncategorized[] = $product;
                }
            }
            ?>
            <?php if ($uncategorized): ?>
              <div class="category">
                <h2 class="category-title">Otros productos</h2>
                <div class="products">
                  <?php foreach ($uncategorized as $product): ?>
                    <article class="product">
                      <div class="product-image">
                        <img src="<?= h($product->image_path ? '/' . $product->image_path : $productFallback) ?>" alt="<?= h($product->name) ?>" width="800" height="600" loading="lazy" decoding="async">
                      </div>
                      <div class="product-body">
                        <?php if ($product->featured): ?>
                          <span class="featured">Destacado</span>
                        <?php endif; ?>
                        <h3><?= h($product->name) ?></h3>
                        <?php if ($product->description): ?>
                          <p><?= h($product->description) ?></p>
                        <?php endif; ?>
                        <?php if ($product->duration): ?>
                          <div class="duration"><?= h($product->duration) ?></div>
                        <?php endif; ?>
                        <div class="price-row">
                          <?php if ($product->price !== null): ?>
                            <span class="price"><?= h($product->price_prefix ? $product->price_prefix . ' ' : '') ?>$<?= number_format((float)$product->price, 0, ',', '.') ?></span>
                          <?php else: ?>
                            <span class="price">Consultar</span>
                          <?php endif; ?>
                          <?php if ($product->discount): ?>
                            <span class="discount">Descuento $<?= number_format((float)$product->discount, 0, ',', '.') ?></span>
                          <?php endif; ?>
                        </div>
                        <?php if ($whatsapp): ?>
                          <?php $contactMessage = rawurlencode('Hola, quiero consultar por ' . trim((string)$product->name) . ' en ' . $siteNameForMessage . '.'); ?>
                          <a class="product-action" href="https://wa.me/<?= h($whatsapp) ?>?text=<?= $contactMessage ?: $defaultContactMessage ?>" target="_blank" rel="noopener"><?= h($product->item_type === 'service' ? 'Cotizar' : $contactLabel) ?></a>
                        <?php endif; ?>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="category">
              <h2 class="category-title"><?= h($kindLabel === 'Catálogo' ? 'Productos' : 'Productos disponibles') ?></h2>
              <div class="products">
                <?php foreach ($site->catalog_products ?? [] as $product): ?>
                  <?php
                  if (!$product->active) {
                      continue;
                  }
                  $categorizedIds[] = $product->id;
                  ?>
                  <article class="product">
                    <div class="product-image">
                      <img src="<?= h($product->image_path ? '/' . $product->image_path : $productFallback) ?>" alt="<?= h($product->name) ?>" width="800" height="600" loading="lazy" decoding="async">
                    </div>
                    <div class="product-body">
                      <?php if ($product->featured): ?>
                        <span class="featured">Destacado</span>
                      <?php endif; ?>
                      <h3><?= h($product->name) ?></h3>
                      <?php if ($product->description): ?>
                        <p><?= h($product->description) ?></p>
                      <?php endif; ?>
                      <?php if ($product->duration): ?>
                        <div class="duration"><?= h($product->duration) ?></div>
                      <?php endif; ?>
                      <div class="price-row">
                        <?php if ($product->price !== null): ?>
                          <span class="price"><?= h($product->price_prefix ? $product->price_prefix . ' ' : '') ?>$<?= number_format((float)$product->price, 0, ',', '.') ?></span>
                        <?php else: ?>
                          <span class="price">Consultar</span>
                        <?php endif; ?>
                        <?php if ($product->discount): ?>
                          <span class="discount">Descuento $<?= number_format((float)$product->discount, 0, ',', '.') ?></span>
                        <?php endif; ?>
                      </div>
                      <?php if ($whatsapp): ?>
                        <?php $contactMessage = rawurlencode('Hola, quiero consultar por ' . trim((string)$product->name) . ' en ' . $siteNameForMessage . '.'); ?>
                        <a class="product-action" href="https://wa.me/<?= h($whatsapp) ?>?text=<?= $contactMessage ?: $defaultContactMessage ?>" target="_blank" rel="noopener"><?= h($product->item_type === 'service' ? 'Cotizar' : $contactLabel) ?></a>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (empty($site->catalog_products)): ?>
            <div class="empty">Este <?= h(strtolower($kindLabel)) ?> aún no tiene productos publicados.</div>
          <?php endif; ?>
        </div>
      </section>
    </main>

    <footer>
      <div class="container">
        <?= h($site->name) ?> - Impulsado por CatOps - © <?= date('Y') ?>
      </div>
    </footer>
  </body>
</html>
