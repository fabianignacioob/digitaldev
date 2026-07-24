<?php

use App\Service\CatalogTypography;

$this->assign('title', 'Contenido | CatOps');
$currentPreset = array_search((string)$catalogSetting->background_image_path, $backgroundPresets ?? [], true);
$fontOptions = CatalogTypography::options();
$titleFont = CatalogTypography::normalize($catalogSetting->title_font ?: $catalogSetting->heading_font);
$sloganFont = CatalogTypography::normalize($catalogSetting->slogan_font);
$kindLabel = $templateKind === 'catalogo' ? 'Catálogo' : 'Carta';
$modeLabel = $supportsCategories ? 'por categoría' : 'simple';
$defaultItemType = array_key_first($itemTypeOptions ?? ['product' => 'Producto']);
$elementLabel = $templateKind === 'catalogo' ? 'elementos' : 'productos';
$productFallback = '/img/placeholder.png';
$backgroundPresetLabels = [
    'parchment' => 'Carta clásica',
    'wood' => 'Mesa madera',
    'vintage-paper' => 'Papel envejecido',
    'natural-fiber' => 'Papel natural',
    'rustic-wood' => 'Madera rústica',
];
?>

<section class="page-head">
    <div>
        <h1><?= h($kindLabel) ?> <?= h($modeLabel) ?></h1>
        <p>Configura fondo, textos principales y <?= h($elementLabel) ?> visibles.</p>
    </div>
    <div class="toolbar">
        <a class="button secondary" href="/sitios/preview/<?= (int)$site->id ?>" target="_blank" rel="noopener">Vista previa</a>
        <a class="button secondary" href="/sitios/editar/<?= (int)$site->id ?>">Volver al sitio</a>
    </div>
</section>

<?= $this->Html->script('sortable.min') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const backgroundType = document.getElementById('background-type');
        const colorFields = document.querySelector('[data-background-color-fields]');
        const imageFields = document.querySelector('[data-background-image-fields]');
        const syncBackgroundFields = () => {
            if (!backgroundType || !colorFields || !imageFields) {
                return;
            }

            const usesImage = backgroundType.value === 'image';
            colorFields.hidden = usesImage;
            imageFields.hidden = !usesImage;
        };

        if (backgroundType) {
            backgroundType.addEventListener('change', syncBackgroundFields);
            syncBackgroundFields();
        }

        document.querySelectorAll('.font-preview-select').forEach((select) => {
            const applyFont = () => {
                select.style.fontFamily = select.value;
            };
            select.addEventListener('change', applyFont);
            applyFont();
        });

        const list = document.getElementById('catalog-products-sortable');
        const status = document.querySelector('[data-product-sort-status]');
        const csrfToken = document.querySelector('input[name="_csrfToken"]')?.value;
        if (!list || !window.Sortable || !csrfToken) {
            return;
        }

        new window.Sortable(list, {
            animation: 160,
            handle: '.product-drag-handle',
            draggable: '.product-editor-card',
            ghostClass: 'product-sort-ghost',
            chosenClass: 'product-sort-chosen',
            onEnd: async () => {
                const productIds = Array.from(list.querySelectorAll('[data-product-id]'))
                    .map((element) => element.dataset.productId)
                    .filter(Boolean);
                if (!productIds.length) {
                    return;
                }

                if (status) {
                    status.textContent = 'Guardando orden...';
                }
                const data = new URLSearchParams({
                    _csrfToken: csrfToken
                });
                productIds.forEach((id) => data.append('product_ids[]', id));

                try {
                    const response = await fetch(list.dataset.reorderUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: data.toString(),
                    });
                    if (!response.ok) {
                        throw new Error('No fue posible guardar el orden.');
                    }
                    if (status) {
                        status.textContent = 'Orden guardado.';
                    }
                } catch (error) {
                    if (status) {
                        status.textContent = 'No se pudo guardar el orden. Recargando...';
                    }
                    window.setTimeout(() => window.location.reload(), 900);
                }
            },
        });
    });
</script>

<section class="split">
    <article class="card">
        <h2>Diseño</h2>
        <p>Elige color plano, una imagen sugerida o una imagen propia para el fondo.</p>
        <?= $this->Form->create($catalogSetting, [
            'type' => 'file',
            'url' => ['controller' => 'Catalogs', 'action' => 'updateSettings', $site->id],
        ]) ?>
        <?= $this->Form->control('background_type', [
            'id' => 'background-type',
            'label' => 'Tipo de fondo',
            'options' => ['color' => 'Color', 'image' => 'Imagen'],
        ]) ?>
        <div data-background-color-fields>
            <?= $this->Form->control('background_color', [
                'label' => 'Color de fondo',
                'type' => 'color',
            ]) ?>
        </div>
        <div data-background-image-fields>
            <label>Fondos sugeridos</label>
            <div class="preset-grid">
                <?php foreach ($backgroundPresets as $key => $path): ?>
                    <label class="preset-option <?= $currentPreset === $key ? 'selected' : '' ?>">
                        <input
                            type="radio"
                            name="background_preset"
                            value="<?= h($key) ?>"
                            <?= $currentPreset === $key ? 'checked' : '' ?>>
                        <span style="background-image:url('/<?= h($path) ?>')"></span>
                        <strong><?= h($backgroundPresetLabels[$key] ?? $key) ?></strong>
                    </label>
                <?php endforeach; ?>
            </div>
            <?= $this->Form->control('background_upload', [
                'label' => 'Subir otra imagen de fondo',
                'type' => 'file',
            ]) ?>
            <p class="meta">Formatos permitidos: JPG, PNG o WEBP. Las imágenes grandes se redimensionan y optimizan.</p>
        </div>
        <?php if ($catalogSetting->background_image_path): ?>
            <p class="meta">Imagen actual: <?= h($catalogSetting->background_image_path) ?></p>
        <?php endif; ?>
        <!-- <?php if ($catalogSetting->background_image_path): ?>
            <div class="form-actions form-actions-stacked">
                <?= $this->Form->postLink('Eliminar fondo', ['controller' => 'Catalogs', 'action' => 'deleteBackground', $site->id], [
                    'class' => 'button danger',
                    'confirm' => '¿Eliminar la imagen de fondo actual?',
                ]) ?>
            </div>
        <?php endif; ?> -->
        <div class="catalog-typography-group">
            <div class="catalog-text-row">
                <?= $this->Form->control('title', ['label' => 'Título principal']) ?>
                <?= $this->Form->control('title_color', [
                    'label' => 'Color del título',
                    'type' => 'color',
                    'value' => $catalogSetting->title_color ?: '#17202a',
                ]) ?>
            </div>
            <label for="title-font">Fuente del título</label>
            <select class="font-preview-select" name="title_font" id="title-font">
                <?php foreach ($fontOptions as $fontValue => $fontLabel): ?>
                    <option value="<?= h($fontValue) ?>" style="font-family: <?= h($fontValue) ?>" <?= $titleFont === $fontValue ? 'selected' : '' ?>><?= h($fontLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="catalog-typography-group">
            <div class="catalog-text-row">
                <?= $this->Form->control('slogan', ['label' => 'Slogan']) ?>
                <?= $this->Form->control('slogan_color', [
                    'label' => 'Color del slogan',
                    'type' => 'color',
                    'value' => $catalogSetting->slogan_color ?: '#17202a',
                ]) ?>
            </div>
            <label for="slogan-font">Fuente del slogan</label>
            <select class="font-preview-select" name="slogan_font" id="slogan-font">
                <?php foreach ($fontOptions as $fontValue => $fontLabel): ?>
                    <option value="<?= h($fontValue) ?>" style="font-family: <?= h($fontValue) ?>" <?= $sloganFont === $fontValue ? 'selected' : '' ?>><?= h($fontLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?= $this->Form->control('intro_text', [
            'label' => 'Texto adicional',
            'type' => 'textarea',
        ]) ?>
        <?= $this->Form->end() ?>
        <div class="form-actions form-actions-stacked">
            <?= $this->Form->button('Guardar diseño') ?>
        </div>
    </article>

    <?php if ($supportsCategories): ?>
        <article class="card">
            <h2>Categorías</h2>
            <p>Crea grupos simples para ordenar <?= h($elementLabel) ?>.</p>
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Catalogs', 'action' => 'addCategory', $site->id],
            ]) ?>
            <?= $this->Form->control('name', ['id' => 'category-create-name', 'label' => 'Nombre de categoría', 'placeholder' => 'Ej: Platos principales']) ?>
            <?= $this->Form->control('sort_order', ['id' => 'category-create-order', 'label' => 'Orden', 'type' => 'number', 'value' => 0]) ?>
            <?= $this->Form->button('Crear categoría') ?>
            <?= $this->Form->end() ?>

            <div class="list">
                <?php foreach ($catalogCategories as $category): ?>
                    <div class="list-item no-thumb">
                        <?= $this->Form->create($category, [
                            'url' => ['controller' => 'Catalogs', 'action' => 'updateCategory', $category->id],
                            'class' => 'inline-edit-form',
                        ]) ?>
                        <div>
                            <?= $this->Form->control('name', ['id' => 'category-' . (int)$category->id . '-name', 'label' => 'Categoría', 'value' => $category->name]) ?>
                            <?= $this->Form->control('sort_order', ['id' => 'category-' . (int)$category->id . '-order', 'label' => 'Orden', 'type' => 'number', 'value' => $category->sort_order]) ?>
                        </div>
                        <div class="row-actions">
                            <?= $this->Form->button('Guardar', ['class' => 'button secondary']) ?>
                        </div>
                        <?= $this->Form->end() ?>
                        <?= $this->Form->postLink('Eliminar', ['controller' => 'Catalogs', 'action' => 'deleteCategory', $category->id], [
                            'class' => 'button danger',
                            'confirm' => '¿Eliminar esta categoría?',
                        ]) ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($catalogCategories->isEmpty()): ?>
                    <p class="meta">Aún no hay categorías.</p>
                <?php endif; ?>
            </div>
        </article>
    <?php else: ?>
        <article class="card">
            <h2>Modo simple</h2>
            <p>Esta plantilla muestra todos los productos en una sola lista, sin familias ni categorías.</p>
        </article>
    <?php endif; ?>
</section>

<section class="card catalog-products-panel">
    <div class="row">
        <div class="col-lg-6">
            <h2><?= h($templateKind === 'catalogo' ? 'Elementos' : 'Productos') ?></h2>
            <p>Agrega elementos con imagen, descripción, valor opcional y visibilidad. Después ordénalos arrastrando las tarjetas.</p>
            <?= $this->Form->create(null, [
                'type' => 'file',
                'url' => ['controller' => 'Catalogs', 'action' => 'addProduct', $site->id],
            ]) ?>
            <?= $this->Form->control('product_image', ['id' => 'product-create-image', 'label' => 'Imagen', 'type' => 'file']) ?>
            <p class="meta">JPG, PNG o WEBP. Si no subes imagen, se mostrará una imagen de respaldo.</p>
            <?= $this->Form->control('name', ['id' => 'product-create-name', 'label' => 'Nombre']) ?>
            <?php if (count($itemTypeOptions) > 1): ?>
                <?= $this->Form->control('item_type', [
                    'id' => 'product-create-type',
                    'label' => 'Tipo de elemento',
                    'options' => $itemTypeOptions,
                    'default' => $defaultItemType,
                ]) ?>
            <?php else: ?>
                <?= $this->Form->hidden('item_type', ['value' => $defaultItemType]) ?>
            <?php endif; ?>
            <?php if ($supportsCategories): ?>
                <?= $this->Form->control('catalog_category_id', [
                    'id' => 'product-create-category',
                    'label' => 'Categoría',
                    'empty' => 'Sin categoría',
                    'options' => $categoryOptions,
                ]) ?>
            <?php endif; ?>

            <?= $this->Form->control('description', [
                'id' => 'product-create-description',
                'label' => 'Descripción corta',
                'placeholder' => 'Ej: Pan artesanal, queso fundido y salsa de la casa.',
            ]) ?>
            <?= $this->Form->control('price', ['id' => 'product-create-price', 'label' => 'Valor', 'type' => 'number', 'step' => '0.01']) ?>
            <?= $this->Form->control('price_prefix_enabled', [
                'id' => 'product-create-price-prefix',
                'label' => 'Mostrar texto "Desde" antes del valor',
                'type' => 'checkbox',
            ]) ?>
            <?= $this->Form->control('discount', [
                'id' => 'product-create-discount',
                'label' => 'Descuento opcional',
                'type' => 'number',
                'step' => '0.01',
                'required' => false,
            ]) ?>
            <?= $this->Form->control('duration', [
                'id' => 'product-create-duration',
                'label' => 'Duración opcional',
                'placeholder' => 'Ej: 45 min, 1 sesión, mensual',
            ]) ?>
            <?php if ($featuredItemsEnabled): ?>
                <?= $this->Form->control('featured', [
                    'id' => 'product-create-featured',
                    'label' => 'Destacado',
                    'type' => 'checkbox',
                ]) ?>
            <?php endif; ?>
            <div class="toolbar">
                <?= $this->Form->button('Agregar elemento') ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
        <div class="col-lg-6">
            <p class="product-sort-help">Usa el control ↕ para cambiar el orden. Los cambios se guardan al soltar cada tarjeta.</p>
            <p class="product-sort-status" data-product-sort-status role="status" aria-live="polite"></p>
            <div class="product-editor-list" id="catalog-products-sortable" data-reorder-url="/sitios/<?= (int)$site->id ?>/carta/productos/orden">
                <?php foreach ($catalogProducts as $product): ?>
                    <?php $collapseId = 'product-edit-' . (int)$product->id; ?>
                    <article class="product-editor-card" data-product-id="<?= (int)$product->id ?>">
                        <aside class="product-media-panel">
                            <div class="product-status-stack">
                                <span class="status"><?= $product->active ? 'Visible' : 'Oculto' ?></span>
                                <?php if ($product->featured): ?>
                                    <span class="status">Destacado</span>
                                <?php endif; ?>
                                <?php if ($product->price === null): ?>
                                    <span class="status">Consultar</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-media">
                                <?php if ($product->image_path): ?>
                                    <img src="/<?= h($product->image_path) ?>" alt="<?= h($product->name) ?>">
                                <?php else: ?>
                                    <img src="<?= h($productFallback) ?>" alt="">
                                <?php endif; ?>
                            </div>
                        </aside>
                        <div>
                            <div class="product-card-head">
                                <div>
                                    <strong class="d-block"><?= h($product->name) ?></strong>
                                    <p class="meta"><?= $product->duration ? h($product->duration) : 'Sin duración definida' ?></p>
                                </div>
                                <div class="product-card-tools">
                                    <button class="product-drag-handle" type="button" aria-label="Mover <?= h($product->name) ?>" title="Arrastra para reordenar">↕</button>
                                    <?php if ($product->price !== null): ?>
                                        <span class="price-line"><?= h($product->price_prefix ? $product->price_prefix . ' ' : '') ?>$<?= number_format((float)$product->price, 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($product->description): ?>
                                <p class="meta product-summary"><?= h($product->description) ?></p>
                            <?php endif; ?>
                            <div class="product-actions product-summary-actions">
                                <button class="button secondary" type="button" data-toggle="collapse" data-target="#<?= h($collapseId) ?>" aria-expanded="false" aria-controls="<?= h($collapseId) ?>">Editar</button>
                                <?php if ($product->image_path): ?>
                                    <?= $this->Form->postLink('Quitar imagen', ['controller' => 'Catalogs', 'action' => 'deleteProductImage', $product->id], [
                                        'class' => 'button secondary',
                                        'confirm' => '¿Quitar la imagen de este elemento?',
                                    ]) ?>
                                <?php endif; ?>
                                <?= $this->Form->postLink('Eliminar', ['controller' => 'Catalogs', 'action' => 'deleteProduct', $product->id], [
                                    'class' => 'button danger',
                                    'confirm' => '¿Eliminar este elemento?',
                                ]) ?>
                            </div>
                            <div class="collapse product-edit-collapse" id="<?= h($collapseId) ?>">
                                <?= $this->Form->create($product, [
                                    'type' => 'file',
                                    'url' => ['controller' => 'Catalogs', 'action' => 'updateProduct', $product->id],
                                    'class' => 'product-editor-form',
                                ]) ?>
                                <div class="product-editor-grid">
                                    <div>
                                        <?= $this->Form->control('name', ['id' => 'product-' . (int)$product->id . '-name', 'label' => 'Nombre', 'value' => $product->name]) ?>
                                    </div>
                                    <?php if (count($itemTypeOptions) > 1): ?>
                                        <div>
                                            <?= $this->Form->control('item_type', [
                                                'id' => 'product-' . (int)$product->id . '-type',
                                                'label' => 'Tipo',
                                                'options' => $itemTypeOptions,
                                                'value' => $product->item_type ?: $defaultItemType,
                                            ]) ?>
                                        </div>
                                    <?php else: ?>
                                        <?= $this->Form->hidden('item_type', ['value' => $product->item_type ?: $defaultItemType]) ?>
                                    <?php endif; ?>
                                    <?php if ($supportsCategories): ?>
                                        <div>
                                            <?= $this->Form->control('catalog_category_id', [
                                                'id' => 'product-' . (int)$product->id . '-category',
                                                'label' => 'Categoría',
                                                'empty' => 'Sin categoría',
                                                'options' => $categoryOptions,
                                                'value' => $product->catalog_category_id,
                                            ]) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="field-wide">
                                        <?= $this->Form->control('description', ['id' => 'product-' . (int)$product->id . '-description', 'label' => 'Descripción corta', 'value' => $product->description]) ?>
                                    </div>
                                    <div>
                                        <?= $this->Form->control('price', ['id' => 'product-' . (int)$product->id . '-price', 'label' => 'Valor', 'type' => 'number', 'step' => '0.01', 'value' => $product->price]) ?>
                                    </div>
                                    <div>
                                        <?= $this->Form->control('discount', ['id' => 'product-' . (int)$product->id . '-discount', 'label' => 'Descuento opcional', 'type' => 'number', 'step' => '0.01', 'value' => $product->discount]) ?>
                                    </div>
                                    <div>
                                        <?= $this->Form->control('duration', ['id' => 'product-' . (int)$product->id . '-duration', 'label' => 'Duración opcional', 'value' => $product->duration]) ?>
                                    </div>
                                    <div class="field-full">
                                        <?= $this->Form->control('product_image', ['id' => 'product-' . (int)$product->id . '-image', 'label' => 'Cambiar imagen', 'type' => 'file']) ?>
                                        <p class="meta">La imagen nueva reemplaza a la anterior de forma segura.</p>
                                    </div>
                                    <div class="field-full product-checks">
                                        <?= $this->Form->control('price_prefix_enabled', [
                                            'id' => 'product-' . (int)$product->id . '-price-prefix',
                                            'label' => 'Mostrar "Desde"',
                                            'type' => 'checkbox',
                                            'checked' => $product->price_prefix === 'Desde',
                                        ]) ?>
                                        <?php if ($featuredItemsEnabled): ?>
                                            <?= $this->Form->control('featured', ['id' => 'product-' . (int)$product->id . '-featured', 'label' => 'Destacado', 'type' => 'checkbox', 'checked' => (bool)$product->featured]) ?>
                                        <?php endif; ?>
                                        <?= $this->Form->control('active', ['id' => 'product-' . (int)$product->id . '-active', 'label' => 'Visible', 'type' => 'checkbox', 'checked' => (bool)$product->active]) ?>
                                    </div>
                                </div>
                                <div class="product-actions">
                                    <?= $this->Form->button('Guardar cambios', ['class' => 'button secondary']) ?>
                                </div>
                                <?= $this->Form->end() ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($catalogProducts->isEmpty()): ?>
                    <p class="meta">Aún no hay productos cargados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>


</section>