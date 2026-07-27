<?php

use App\Service\CatalogTypography;

$setting = $site->catalog_setting ?? null;
$theme = $site->theme ?? null;
$primary = $theme->primary_color ?? '#d06b2c';
$secondary = $theme->secondary_color ?? '#071735';
$backgroundColor = $setting->background_color ?? ($theme->background_color ?? '#f8f5ed');
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
$hexToRgb = static function (string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = implode('', array_map(static fn (string $value): string => $value . $value, str_split($hex)));
    }
    if (!preg_match('/^[0-9a-f]{6}$/i', $hex)) {
        return '208, 107, 44';
    }
    $channels = array_map(static fn (string $channel): int => hexdec($channel), str_split($hex, 2));

    return implode(', ', $channels);
};
$backgroundColor = $validColor((string)$backgroundColor, '#faf7ef');
$primary = $validColor((string)$primary, '#d06b2c');
$secondary = $validColor((string)$secondary, '#071735');
$titleColor = $validColor((string)$titleColor, '#ffffff');
$sloganColor = $validColor((string)$sloganColor, '#ffffff');
$relativeLuminance = static function (string $hex): float {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = implode('', array_map(static fn (string $value): string => $value . $value, str_split($hex)));
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

    return $contrastRatio('#071735', $background) >= $contrastRatio('#ffffff', $background) ? '#071735' : '#ffffff';
};
$actionColor = $contrastRatio($primary, '#ffffff') >= 4.5 ? $primary : '#c6530b';
$pageBackground = h($backgroundColor);
$title = $setting->title ?? $site->name;
$slogan = $setting->slogan ?? 'Nuestra carta';
$intro = $setting->intro_text ?? null;
$categorizedIds = [];
$templateSlug = $site->template->slug ?? 'carta-simple';
$usesCategories = in_array($templateSlug, ['carta-categorias', 'catalogo-categorias'], true);
$isCatalog = str_starts_with((string)$templateSlug, 'catalogo-');
$kindLabel = $isCatalog ? 'Catálogo' : 'Carta';
$elementLabel = $isCatalog ? 'productos' : 'preparaciones';
$sectionTitle = $isCatalog ? 'Productos destacados' : 'Sabores disponibles';
$whatsapp = ($site->whatsapp_country_code ?? '') . ($site->whatsapp_number ?? '');
if (!$whatsapp) {
    $whatsapp = $site->whatsapp ?? '';
}
$whatsapp = preg_replace('/\D+/', '', (string)$whatsapp);
$contactLabel = $isCatalog ? 'Consultar' : 'Pedir';
$siteNameForMessage = trim((string)$site->name);
$defaultContactMessage = rawurlencode('Hola, quiero consultar por ' . $siteNameForMessage . '.');
$productFallback = '/img/placeholder.png';
$businessAddress = trim((string)($site->business_address ?? ''));
$businessHours = trim((string)($site->business_hours ?? ''));
$publicPhone = trim((string)($site->public_phone ?? ''));
$publicEmail = trim((string)($site->public_email ?? ''));
$displayPhone = $publicPhone ?: ($whatsapp ? '+' . $whatsapp : '');
$contactItems = [];
if ($businessAddress) {
    $contactItems[] = ['label' => 'Dónde estamos', 'value' => $businessAddress];
}
if ($businessHours) {
    $contactItems[] = ['label' => 'Horario', 'value' => $businessHours];
}
if ($displayPhone) {
    $contactItems[] = ['label' => 'Teléfono', 'value' => $displayPhone];
}
if ($publicEmail) {
    $contactItems[] = ['label' => 'Correo', 'value' => $publicEmail, 'href' => 'mailto:' . $publicEmail];
}
$activeProducts = [];
foreach ($site->catalog_products ?? [] as $product) {
    if ($product->active) {
        $activeProducts[] = $product;
    }
}
$heroImage = $backgroundImage;
if (!$heroImage) {
    foreach ($activeProducts as $product) {
        if (!empty($product->image_path)) {
            $heroImage = '/' . $product->image_path;
            break;
        }
    }
}
$heroHasImage = (bool)$heroImage;
$heroTitleColor = $heroHasImage ? $titleColor : $accessibleColor($titleColor, $backgroundColor, 3.0);
$heroTextColor = $heroHasImage ? $sloganColor : $accessibleColor($sloganColor, $backgroundColor, 4.5);
$primaryRgb = $hexToRgb($actionColor);
$secondaryRgb = $hexToRgb($secondary);
$brandInitial = strtoupper(substr(trim((string)$site->name), 0, 1)) ?: 'C';
$ctaHref = $whatsapp ? 'https://wa.me/' . h($whatsapp) . '?text=' . $defaultContactMessage : '#contenido-principal';
$ctaText = $isCatalog ? 'Consultar' : '';
$availabilityLabels = [
    'available' => 'Disponible',
    'unavailable' => 'Agotado',
    'coming_soon' => 'Próximamente',
];
$formatMeasure = static function ($variant): string {
    $parts = [];
    if ($variant->measurement_value !== null && $variant->measurement_value !== '') {
        $parts[] = rtrim(rtrim(number_format((float)$variant->measurement_value, 2, '.', ''), '0'), '.');
    }
    if ($variant->measurement_unit) {
        $parts[] = (string)$variant->measurement_unit;
    }

    return implode(' ', $parts);
};
$productVariants = static function ($product): array {
    return array_values(array_filter((array)($product->catalog_product_variants ?? []), static fn ($variant): bool => !empty($variant->name)));
};
$formatPrice = static function ($product) use ($productVariants): string {
    $variants = $productVariants($product);
    if ($variants !== []) {
        $prices = array_values(array_filter(array_map(
            static fn ($variant) => $variant->price === null ? null : (float)$variant->price,
            $variants,
        ), static fn ($price): bool => $price !== null));
        if ($prices === []) {
            return 'Consultar';
        }

        return 'Desde $' . number_format(min($prices), 0, ',', '.');
    }
    if ($product->price === null) {
        return 'Consultar';
    }

    return trim((string)($product->price_prefix ? $product->price_prefix . ' ' : '') . '$' . number_format((float)$product->price, 0, ',', '.'));
};
$contactMessageFor = static function ($product) use ($siteNameForMessage): string {
    $item = trim((string)$product->name);

    return rawurlencode('Hola, quiero consultar por ' . $item . ' en ' . $siteNameForMessage . '.');
};
$renderProduct = static function ($product) use ($productFallback, $formatPrice, $formatMeasure, $productVariants, $availabilityLabels, $whatsapp, $contactMessageFor, $defaultContactMessage, $contactLabel): void {
    $availability = $product->availability ?? 'available';
    $variants = $productVariants($product);
    ?>
    <article class="product">
        <div class="product-image">
            <img src="<?= h($product->image_path ? '/' . $product->image_path : $productFallback) ?>" alt="<?= h($product->name) ?>" width="900" height="640" loading="lazy" decoding="async">
            <?php if ($product->featured): ?>
                <span class="featured">Destacado</span>
            <?php endif; ?>
            <?php if ($product->discount): ?>
                <span class="discount">Oferta $<?= number_format((float)$product->discount, 0, ',', '.') ?></span>
            <?php endif; ?>
            <?php if ($availability !== 'available'): ?>
                <span class="availability-badge"><?= h($availabilityLabels[$availability] ?? 'No disponible') ?></span>
            <?php endif; ?>
        </div>
        <div class="product-body">
            <div class="product-heading">
                <h3><?= h($product->name) ?></h3>
                <span class="price"><?= h($formatPrice($product)) ?></span>
            </div>
            <?php if ($variants): ?>
                <div class="product-variants" aria-label="Opciones de <?= h($product->name) ?>">
                    <?php foreach ($variants as $variant): ?>
                        <?php
                        $variantAvailability = $variant->availability ?? 'available';
                        $measure = $formatMeasure($variant);
                        ?>
                        <div class="variant-row">
                            <span><?= h(trim((string)$variant->name)) ?></span>
                            <?php if ($variantAvailability !== 'available'): ?>
                                <small><?= h($availabilityLabels[$variantAvailability] ?? 'No disponible') ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($product->description): ?>
                <p><?= h($product->description) ?></p>
            <?php endif; ?>
            <?php if ($product->duration): ?>
                <div class="duration"><?= h($product->duration) ?></div>
            <?php endif; ?>
            <?php if ($whatsapp && $availability === 'available'): ?>
                <a class="product-action" href="https://wa.me/<?= h($whatsapp) ?>?text=<?= $contactMessageFor($product) ?: $defaultContactMessage ?>" target="_blank" rel="noopener" aria-label="<?= h($contactLabel . ' ' . $product->name . ' por WhatsApp') ?>">
                    <span class="wa-icon" aria-hidden="true"></span>
                    <?= h($product->item_type === 'service' ? 'Cotizar' : $contactLabel) ?>
                </a>
            <?php endif; ?>
        </div>
    </article>
<?php
};
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($site->seo_title ?: $title) ?></title>
    <meta name="description" content="<?= h($site->seo_description ?: $slogan) ?>">
    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --background: <?= $pageBackground ?>;
            --foreground: <?= h($secondary) ?>;
            --primary: <?= h($actionColor) ?>;
            --primary-rgb: <?= h($primaryRgb) ?>;
            --secondary-rgb: <?= h($secondaryRgb) ?>;
            --muted: rgba(255, 255, 255, .62);
            --border: rgba(var(--secondary-rgb), .1);
            --shadow: 0 16px 34px rgba(var(--secondary-rgb), .09);
            --bg-normal: #f8f5ed;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background:
                radial-gradient(circle at 12% 4%, rgba(var(--primary-rgb), .08), transparent 26rem),
                var(--bg-normal);
            color: var(--foreground);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main {
            flex: 1;
        }

        a {
            color: inherit;
        }

        a:focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 3px;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
            letter-spacing: 0;
        }

        .skip-link {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 20;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--foreground);
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            transform: translateY(-160%);
        }

        .skip-link:focus {
            transform: translateY(0);
        }

        .container {
            width: min(1088px, calc(100vw - 32px));
            margin: 0 auto;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            border-bottom: 1px solid rgba(var(--secondary-rgb), .08);
            background: rgba(250, 247, 239, .86);
            backdrop-filter: blur(16px);
        }

        .nav {
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand-link {
            min-width: 0;
            display: inline-flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
        }

        .logo,
        .logo-mark {
            width: 50px;
            height: 50px;
            flex: 0 0 38px;
            border-radius: 12px;
            object-fit: cover;
        }

        .logo-mark {
            display: inline-grid;
            place-items: center;
            background: var(--foreground);
            color: #fff;
            font-weight: 900;
        }

        .brand-copy {
            min-width: 0;
            display: grid;
            gap: 1px;
        }

        .brand {
            overflow: hidden;
            color: var(--foreground);
            font-size: 16px;
            font-weight: 900;
            line-height: 1.1;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .brand-kicker {
            overflow: hidden;
            color: rgba(var(--secondary-rgb), .62);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 2.4px;
            line-height: 1.1;
            text-transform: uppercase;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .whatsapp,
        .hero-action,
        .product-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border-radius: 10px;
            font-weight: 900;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
        }

        .wa-icon {
            position: relative;
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            border: 2px solid currentColor;
            border-radius: 50%;
        }

        .wa-icon::after {
            content: "";
            position: absolute;
            right: 0;
            bottom: 0;
            width: 4px;
            height: 4px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(22deg);
        }

        .whatsapp {
            min-height: 42px;
            padding: 0 18px;
            /* background: var(--primary); */
            color: #000;
            box-shadow: 0 10px 22px rgba(var(--primary-rgb), .24);
        }

        .hero {
            position: relative;
            isolation: isolate;
            min-height: 570px;
            display: grid;
            align-items: center;
            overflow: hidden;
            background: <?= $heroHasImage ? 'url("' . h($heroImage) . '") center / cover no-repeat' : 'linear-gradient(135deg, rgba(var(--primary-rgb), .12), transparent 62%), var(--background)' ?>;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: <?= $heroHasImage ? 'linear-gradient(90deg, rgba(7, 23, 53, .86) 0%, rgba(7, 23, 53, .67) 42%, rgba(7, 23, 53, .28) 100%)' : 'transparent' ?>;
        }

        .hero-copy {
            width: min(640px, 100%);
            padding: 70px 0 78px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 20px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(var(--primary-rgb), .95);
            color: #fff;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .catalog-title {
            max-width: 12ch;
            color: <?= h($heroTitleColor) ?>;
            font-family: <?= h($titleFont) ?>;
            font-size: clamp(42px, 7vw, 76px);
            font-weight: 950;
            line-height: .98;
        }

        .lead {
            max-width: 620px;
            margin-top: 22px;
            color: <?= h($heroTextColor) ?>;
            font-family: <?= h($sloganFont) ?>;
            font-size: clamp(16px, 2.2vw, 19px);
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .hero-action {
            min-height: 46px;
            padding: 0 20px;
            border: 1px solid rgba(255, 255, 255, .28);
            color: #fff;
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(10px);
        }

        .hero-action.primary {
            border-color: transparent;
            background: var(--primary);
            box-shadow: 0 12px 26px rgba(var(--primary-rgb), .28);
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 28px;
            color: <?= h($heroTextColor) ?>;
            font-size: 14px;
        }

        .hero-meta span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .catalog-section {
            padding: 68px 0;
        }

        .section-heading {
            max-width: 720px;
            margin-bottom: 38px;
        }

        .section-kicker {
            margin-bottom: 10px;
            color: var(--primary);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .category-title,
        .section-heading h2 {
            color: var(--foreground);
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 950;
            line-height: 1.08;
        }

        .section-heading p {
            margin-top: 14px;
            color: rgba(var(--secondary-rgb), .68);
            font-size: 17px;
            line-height: 1.6;
        }

        .category {
            margin-bottom: 52px;
        }

        .category-title {
            margin-bottom: 20px;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 24px;
        }

        .product {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: rgba(255, 255, 255, .86);
            box-shadow: var(--shadow);
        }

        .product-image {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            /* background: rgba(var(--primary-rgb), .1); */
            /* padding: 10px; */
            /* border-right: 1px solid var(--border); */
        }

        .product-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            transition: transform .2s ease;
            /* border: 1px solid var(--border); */
            /* border-radius: 20px; */
        }

        .product:hover .product-image img {
            transform: scale(1.025);
        }

        .featured {
            position: absolute;
            top: 8px;
            left: 15px;
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .92);
            color: var(--foreground);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .availability-badge {
            position: absolute;
            top: 8px;
            right: 15px;
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(7, 23, 53, .86);
            color: #fff;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .product-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 5px;
            padding: 20px;
        }

        .product-heading {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: start;
        }

        .product h3 {
            color: var(--foreground);
            font-size: 19px;
            font-weight: 950;
            line-height: 1.2;
        }

        .product p {
            color: rgba(var(--secondary-rgb), .68);
            font-size: 14px;
            line-height: 1.55;
        }

        .price {
            color: var(--foreground);
            font-size: 18px;
            font-weight: 950;
            line-height: 1.2;
            text-align: right;
        }

        .discount,
        .duration {
            width: fit-content;
            color: var(--foreground);
            font-size: 13px;
            font-weight: 850;
        }

        .discount {
            background: rgba(var(--primary-rgb), .12);
            color: var(--primary);
            position: absolute;
            bottom: 8px;
            right: 15px;
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
        }

        .product-action {
            min-height: 30px;
            margin-top: auto;
            padding: 0 16px;
            background: var(--primary);
            color: #fff;
            box-shadow: 0 12px 22px rgba(var(--primary-rgb), .18);
        }

        .product-variants {
            display: flex;
            flex-wrap: wrap;
            gap: 5px 3px;
            color: rgba(var(--secondary-rgb), .76);
            font-size: 11px;
        }

        .variant-row {
            display: inline-flex;
            align-items: baseline;
        }

        .variant-row:not(:last-child)::after {
            content: ',';
        }

        .variant-row span {
            color: var(--foreground);
            font-weight: 500;
        }

        .variant-row small {
            color: var(--primary);
            font-weight: 800;
            font-size: 12px;
        }

        .empty {
            padding: 28px;
            border: 1px dashed rgba(var(--secondary-rgb), .2);
            border-radius: 8px;
            background: rgba(255, 255, 255, .7);
            color: rgba(var(--secondary-rgb), .68);
            text-align: center;
        }

        .contact-band {
            padding: 40px 0;
            border-top: 1px solid var(--border);
            background: rgba(255, 255, 255, .42);
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 20px;
            text-align: center;
        }

        .contact-item {
            display: grid;
            gap: 5px;
        }

        .contact-item b {
            color: var(--foreground);
            font-size: 14px;
        }

        .contact-item span,
        .contact-item a,
        footer {
            color: rgba(var(--secondary-rgb), .64);
        }

        .contact-item a {
            text-decoration: none;
        }

        footer {
            padding: 30px 0 calc(env(safe-area-inset-bottom) + 30px);
            border-top: 1px solid var(--border);
            background: rgba(255, 255, 255, .64);
            font-size: 14px;
        }

        .footer-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 820px) {
            .hero {
                min-height: 520px;
            }

            .products {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .container {
                width: min(100% - 32px, 1088px);
            }

            .nav {
                min-height: 70px;
            }

            .brand {
                max-width: 165px;
                font-size: 15px;
            }

            .brand-kicker {
                max-width: 165px;
                font-size: 9px;
            }

            .whatsapp {
                min-height: 40px;
                padding: 0 14px;
                font-size: 14px;
            }

            .hero {
                min-height: 416px;
                background-position: center;
            }

            .hero::before {
                background: <?= $heroHasImage ? 'linear-gradient(90deg, rgba(7, 23, 53, .84), rgba(7, 23, 53, .5))' : 'transparent' ?>;
            }

            .hero-copy {
                padding: 48px 0 54px;
            }

            .eyebrow {
                margin-bottom: 16px;
                font-size: 11px;
            }

            .catalog-title {
                max-width: 11ch;
                font-size: clamp(34px, 12vw, 48px);
            }

            .lead {
                margin-top: 16px;
                font-size: 16px;
            }

            .hero-actions {
                margin-top: 20px;
            }

            .hero-action {
                min-height: 44px;
                padding: 0 16px;
                font-size: 14px;
            }

            .hero-meta {
                gap: 10px;
                margin-top: 22px;
                font-size: 13px;
            }

            .catalog-section {
                padding: 42px 0;
            }

            .section-heading {
                margin-bottom: 28px;
            }

            .section-heading h2,
            .category-title {
                font-size: 28px;
            }

            .section-heading p {
                font-size: 16px;
            }

            .products {
                grid-template-columns: 1fr;
            }

            .product {
                display: inline-flex !important;
            }

            .product-body {
                padding: 20px;
            }

            .product-image {
                border-right: none !important;
            }

            .discount {
                font-size: 10px;
            }
        }

        }
    </style>
</head>

<body>
    <a class="skip-link" href="#contenido-principal">Saltar al contenido</a>
    <header class="topbar">
        <nav class="container nav" aria-label="Navegación principal">
            <a class="brand-link" href="#contenido-principal" aria-label="<?= h($site->name) ?>">
                <?php if ($site->logo_path): ?>
                    <img class="logo" src="/<?= h($site->logo_path) ?>" alt="<?= h($site->name) ?>">
                <?php else: ?>
                    <span class="logo-mark" aria-hidden="true"><?= h($brandInitial) ?></span>
                <?php endif; ?>
                <span class="brand-copy">
                    <span class="brand"><?= h($site->name) ?></span>
                    <span class="brand-kicker"><?= h($kindLabel) ?> digital</span>
                </span>
            </a>
            <!-- <?php if ($whatsapp): ?>
                <a class="whatsapp" href="<?= $ctaHref ?>" target="_blank" rel="noopener" aria-label="<?= h($ctaText) ?> por WhatsApp">
                    <?= h($ctaText) ?>
                </a>
            <?php endif; ?> -->
        </nav>
    </header>

    <main id="contenido-principal">
        <section class="hero" aria-label="<?= h($kindLabel . ' de ' . $site->name) ?>">
            <div class="container">
                <div class="hero-copy">
                    <span class="eyebrow"><?= h($kindLabel) ?> online</span>
                    <h1 class="catalog-title"><?= h($title) ?></h1>
                    <p class="lead"><?= h($slogan) ?></p>
                    <?php if ($intro): ?>
                        <p class="lead"><?= nl2br(h($intro)) ?></p>
                    <?php endif; ?>
                    <div class="hero-actions">
                        <a class="hero-action primary" href="#carta"><?= h($isCatalog ? 'Ver productos' : 'Ver la carta') ?></a>
                        <?php if ($whatsapp): ?>
                            <a class="hero-action" href="<?= $ctaHref ?>" target="_blank" rel="noopener">
                            <svg width="24px" height="24px" viewBox="0 0 48 48" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">

<title>Whatsapp-color</title>
<desc>Created with Sketch.</desc>
<defs>

</defs>
<g id="Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
    <g id="Color-" transform="translate(-700.000000, -360.000000)" fill="#67C15E">
        <path d="M723.993033,360 C710.762252,360 700,370.765287 700,383.999801 C700,389.248451 701.692661,394.116025 704.570026,398.066947 L701.579605,406.983798 L710.804449,404.035539 C714.598605,406.546975 719.126434,408 724.006967,408 C737.237748,408 748,397.234315 748,384.000199 C748,370.765685 737.237748,360.000398 724.006967,360.000398 L723.993033,360.000398 L723.993033,360 Z M717.29285,372.190836 C716.827488,371.07628 716.474784,371.034071 715.769774,371.005401 C715.529728,370.991464 715.262214,370.977527 714.96564,370.977527 C714.04845,370.977527 713.089462,371.245514 712.511043,371.838033 C711.806033,372.557577 710.056843,374.23638 710.056843,377.679202 C710.056843,381.122023 712.567571,384.451756 712.905944,384.917648 C713.258648,385.382743 717.800808,392.55031 724.853297,395.471492 C730.368379,397.757149 732.00491,397.545307 733.260074,397.27732 C735.093658,396.882308 737.393002,395.527239 737.971421,393.891043 C738.54984,392.25405 738.54984,390.857171 738.380255,390.560912 C738.211068,390.264652 737.745308,390.095816 737.040298,389.742615 C736.335288,389.389811 732.90737,387.696673 732.25849,387.470894 C731.623543,387.231179 731.017259,387.315995 730.537963,387.99333 C729.860819,388.938653 729.198006,389.89831 728.661785,390.476494 C728.238619,390.928051 727.547144,390.984595 726.969123,390.744481 C726.193254,390.420348 724.021298,389.657798 721.340985,387.273388 C719.267356,385.42535 717.856938,383.125756 717.448104,382.434484 C717.038871,381.729275 717.405907,381.319529 717.729948,380.938852 C718.082653,380.501232 718.421026,380.191036 718.77373,379.781688 C719.126434,379.372738 719.323884,379.160897 719.549599,378.681068 C719.789645,378.215575 719.62006,377.735746 719.450874,377.382942 C719.281687,377.030139 717.871269,373.587317 717.29285,372.190836 Z" id="Whatsapp">

        </path>
    </g>
</g>
</svg>
                                <?= h($ctaText . ' ahora') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hero-meta" aria-label="Información rápida">
                        <?php if ($businessHours): ?>
                            <span><?= h($businessHours) ?></span>
                        <?php endif; ?>
                        <?php if ($businessAddress): ?>
                            <span><?= h($businessAddress) ?></span>
                        <?php endif; ?>
                        <span><?= count($activeProducts) ?> <?= h($elementLabel) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="catalog-section" id="carta">
            <div class="container">
                <div class="section-heading">
                    <div class="section-kicker"><?= h($kindLabel) ?></div>
                    <!-- <h2><?= h($sectionTitle) ?></h2> -->
                    <!-- <p><?= h($isCatalog ? 'Revisa los productos disponibles y consulta por WhatsApp en un toque.' : 'Elige lo que quieres y envía tu pedido directo por WhatsApp.') ?></p> -->
                </div>

                <?php if ($usesCategories): ?>
                    <?php foreach ($site->catalog_categories ?? [] as $category): ?>
                        <?php
                        $categoryProducts = [];
                        foreach ($category->catalog_products ?? [] as $product) {
                            if ($product->active) {
                                $categoryProducts[] = $product;
                                $categorizedIds[] = $product->id;
                            }
                        }
                        ?>
                        <?php if ($categoryProducts): ?>
                            <div class="category">
                                <h2 class="category-title"><?= h($category->name) ?></h2>
                                <div class="products">
                                    <?php foreach ($categoryProducts as $product): ?>
                                        <?php $renderProduct($product); ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
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
                            <h2 class="category-title"><?= h($isCatalog ? 'Otros productos' : 'Otras preparaciones') ?></h2>
                            <div class="products">
                                <?php foreach ($uncategorized as $product): ?>
                                    <?php $renderProduct($product); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($activeProducts): ?>
                        <div class="category">
                            <div class="products">
                                <?php foreach ($activeProducts as $product): ?>
                                    <?php $renderProduct($product); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!$activeProducts): ?>
                    <div class="empty">Este <?= h(strtolower($kindLabel)) ?> aún no tiene productos publicados.</div>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($contactItems): ?>
            <section class="contact-band" aria-label="Datos de contacto">
                <div class="container contact-grid">
                    <?php foreach ($contactItems as $item): ?>
                        <div class="contact-item">
                            <b><?= h($item['label']) ?></b>
                            <?php if (!empty($item['href'])): ?>
                                <a href="<?= h($item['href']) ?>"><?= h($item['value']) ?></a>
                            <?php else: ?>
                                <span><?= h($item['value']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container footer-row">
            <span><?= h($site->name) ?></span>
            <span>Impulsado por CatOps · © <?= date('Y') ?></span>
        </div>
    </footer>

</body>

</html>
