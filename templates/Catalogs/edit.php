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
$measurementTypeOptions = [];
$measurementUnits = [];
foreach ($measurementTypes ?? [] as $measurementType) {
    $measurementTypeOptions[(int)$measurementType->id] = $measurementType->name;
    $units = $measurementType->units;
    if (is_string($units)) {
        $units = json_decode($units, true) ?: [];
    }
    $measurementUnits[(int)$measurementType->id] = array_values(array_filter((array)$units, 'is_string'));
}
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
        const measurementUnits = <?= json_encode($measurementUnits, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const syncMeasurementUnit = (typeSelect) => {
            const unitSelect = document.getElementById(typeSelect.dataset.unitTarget || '');
            if (!unitSelect) {
                return;
            }

            const selected = unitSelect.dataset.selected || unitSelect.value;
            const units = measurementUnits[typeSelect.value] || [];
            unitSelect.replaceChildren(new Option('Sin unidad', ''));
            units.forEach((unit) => unitSelect.add(new Option(unit, unit, false, unit === selected)));
            unitSelect.disabled = !typeSelect.value || units.length === 0;
        };
        document.querySelectorAll('[data-measurement-type]').forEach((typeSelect) => {
            typeSelect.addEventListener('change', () => {
                const unitSelect = document.getElementById(typeSelect.dataset.unitTarget || '');
                if (unitSelect) {
                    unitSelect.dataset.selected = '';
                }
                syncMeasurementUnit(typeSelect);
            });
            syncMeasurementUnit(typeSelect);
        });

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

        const csrfToken = document.querySelector('input[name="_csrfToken"]')?.value;
        if (!window.Sortable || !csrfToken) {
            return;
        }

        const initSortable = ({ list, status, itemSelector, idAttribute, requestKey, handle, savingText, savedText, errorText }) => {
            if (!list) {
                return;
            }

            new window.Sortable(list, {
                animation: 160,
                handle,
                draggable: itemSelector,
                ghostClass: 'product-sort-ghost',
                chosenClass: 'product-sort-chosen',
                onEnd: async () => {
                    const itemIds = Array.from(list.querySelectorAll(itemSelector))
                        .map((element) => element.dataset[idAttribute])
                        .filter(Boolean);
                    if (!itemIds.length) {
                        return;
                    }

                    if (status) {
                        status.textContent = savingText;
                    }
                    const data = new URLSearchParams({
                        _csrfToken: csrfToken
                    });
                    itemIds.forEach((id) => data.append(requestKey, id));

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
                            status.textContent = savedText;
                        }
                    } catch (error) {
                        if (status) {
                            status.textContent = errorText;
                        }
                        window.setTimeout(() => window.location.reload(), 900);
                    }
                },
            });
        };

        initSortable({
            list: document.getElementById('catalog-categories-sortable'),
            status: document.querySelector('[data-category-sort-status]'),
            itemSelector: '.category-editor-card',
            idAttribute: 'categoryId',
            requestKey: 'category_ids[]',
            handle: '.category-drag-handle',
            savingText: 'Guardando orden...',
            savedText: 'Orden de categorías guardado.',
            errorText: 'No se pudo guardar el orden de categorías. Recargando...',
        });

        initSortable({
            list: document.getElementById('catalog-products-sortable'),
            status: document.querySelector('[data-product-sort-status]'),
            itemSelector: '.product-editor-card',
            idAttribute: 'productId',
            requestKey: 'product_ids[]',
            handle: '.product-drag-handle',
            savingText: 'Guardando orden...',
            savedText: 'Orden guardado.',
            errorText: 'No se pudo guardar el orden. Recargando...',
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
            'label' => 'Tipo de Banner',
            'options' => ['color' => 'Color', 'image' => 'Imagen'],
        ]) ?>
        <div data-background-color-fields>
            <?= $this->Form->control('background_color', [
                'label' => 'Color de fondo',
                'type' => 'color',
            ]) ?>
        </div>
        <div data-background-image-fields>
            <label>Fondo banner sugerido</label>
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
        <?= $this->Form->control('show_product_action', [
            'label' => 'Mostrar botón de pedido o consulta en cada producto',
            'type' => 'checkbox',
        ]) ?>
        <p class="meta">Esta opción se aplica a todos los productos o preparaciones del sitio.</p>
        <?php if ($supportsCategories && $categoryLayoutAvailable): ?>
            <?= $this->Form->control('category_layout', [
                'label' => 'Diseño de categorías',
                'options' => [
                    'normal' => 'Normal: una categoría por fila',
                    'blocks' => 'Por bloques: dos categorías por fila',
                ],
                'value' => $catalogSetting->category_layout ?? 'normal',
            ]) ?>
            <p class="meta">Por bloques usa dos categorías por fila en escritorio y tablet; en teléfono mantiene una por fila.</p>
        <?php endif; ?>
        <div class="form-actions form-actions-stacked">
            <?= $this->Form->button('Guardar diseño') ?>
        </div>
        <?= $this->Form->end() ?>
    </article>

    <?php if ($supportsCategories): ?>
        <article class="card">
            <h2>Categorías</h2>
            <p>Crea grupos simples para ordenar <?= h($elementLabel) ?>.</p>
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Catalogs', 'action' => 'addCategory', $site->id],
                'class' => 'category-create-form',
            ]) ?>
            <?= $this->Form->control('name', ['id' => 'category-create-name', 'label' => 'Nombre de categoría', 'placeholder' => 'Ej: Platos principales']) ?>
            <!-- <?= $this->Form->control('sort_order', ['id' => 'category-create-order', 'label' => 'Orden', 'type' => 'number', 'value' => 0]) ?> -->
            <?= $this->Form->button('Crear categoría', ['class' => 'mt-2']) ?>
            <?= $this->Form->end() ?>

            <p class="product-sort-help">Arrastra las categorías para cambiar su orden.</p>
            <div class="product-sort-status" data-category-sort-status aria-live="polite"></div>
            <div class="list" id="catalog-categories-sortable" data-reorder-url="/sitios/<?= (int)$site->id ?>/carta/categorias/orden">
                <?php foreach ($catalogCategories as $category): ?>
                    <div class="list-item no-thumb category-editor-card" data-category-id="<?= (int)$category->id ?>">
                        <?= $this->Form->create($category, [
                            'url' => ['controller' => 'Catalogs', 'action' => 'updateCategory', $category->id],
                            'class' => 'inline-edit-form',
                        ]) ?>
                        <div>
                            <?= $this->Form->control('name', ['id' => 'category-' . (int)$category->id . '-name', 'label' => 'Categoría', 'value' => $category->name]) ?>
                            <!-- <?= $this->Form->control('sort_order', ['id' => 'category-' . (int)$category->id . '-order', 'label' => 'Orden', 'type' => 'number', 'value' => $category->sort_order]) ?> -->
                        </div>
                        <div class="row-actions">
                            <button class="product-drag-handle category-drag-handle" type="button" aria-label="Mover <?= h($category->name) ?>" title="Arrastra para reordenar">↕</button>
                            <?= $this->Form->button('Guardar', ['class' => 'button secondary']) ?>
                            <?= $this->Form->postLink('Eliminar', ['controller' => 'Catalogs', 'action' => 'deleteCategory', $category->id], [
                                'class' => 'button danger',
                                'confirm' => '¿Eliminar esta categoría?',
                            ]) ?>
                        </div>
                        <?= $this->Form->end() ?>
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
        <div class="col-lg-4">
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
            <?= $this->Form->control('measurement_type_id', [
                'id' => 'product-create-measurement-type',
                'label' => 'Tipo de medida para sus opciones',
                'empty' => 'Sin variantes de medida',
                'options' => $measurementTypeOptions,
                'required' => false,
            ]) ?>

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
            <?= $this->Form->control('availability', [
                'id' => 'product-create-availability',
                'label' => 'Disponibilidad',
                'options' => $availabilityOptions,
                'default' => 'available',
            ]) ?>
            <?php if ($featuredItemsEnabled): ?>
                <?= $this->Form->control('featured', [
                    'id' => 'product-create-featured',
                    'label' => 'Destacado',
                    'type' => 'checkbox',
                ]) ?>
                <p class="meta"><?= $featuredItemsLimit === 0 ? 'Productos destacados ilimitados.' : 'Máximo ' . (int)$featuredItemsLimit . ' producto' . ($featuredItemsLimit === 1 ? '' : 's') . ' destacado' . ($featuredItemsLimit === 1 ? '' : 's') . ' por sitio.' ?></p>
            <?php endif; ?>
            <?php if ($advancedProductSeoEnabled): ?>
                <?= $this->Form->control('seo_description', [
                    'id' => 'product-create-seo-description',
                    'label' => 'Descripción SEO del producto',
                    'maxlength' => 180,
                    'required' => false,
                ]) ?>
                <?= $this->Form->control('seo_keywords', [
                    'id' => 'product-create-seo-keywords',
                    'label' => 'Palabras clave SEO',
                    'placeholder' => 'Ej: pizza napolitana, pizza artesanal',
                    'maxlength' => 255,
                    'required' => false,
                ]) ?>
            <?php endif; ?>
            <div class="toolbar">
                <?= $this->Form->button('Agregar elemento') ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
        <div class="col-lg-8">
            <p class="product-sort-help">Usa el control ↕ para cambiar el orden. Los cambios se guardan al soltar cada tarjeta.</p>
            <p class="product-sort-status" data-product-sort-status role="status" aria-live="polite"></p>
            <div class="product-editor-list" id="catalog-products-sortable" data-reorder-url="/sitios/<?= (int)$site->id ?>/carta/productos/orden">
                <?php foreach ($catalogProducts as $product): ?>
                    <?php $collapseId = 'product-edit-' . (int)$product->id; ?>
                    <article class="product-editor-card" data-product-id="<?= (int)$product->id ?>">
                        <aside class="product-media-panel">
                            <div class="product-status-stack">
                                <span class="status"><?= $product->active ? 'Visible' : 'Oculto' ?></span>
                                <span class="status"><?= h($availabilityOptions[$product->availability ?? 'available'] ?? 'Disponible') ?></span>
                                <?php if ($product->featured): ?>
                                    <span class="status">Destacado</span>
                                <?php endif; ?>
                                <?php if ($product->price === null): ?>
                                    <span class="status">Consultar</span>
                                <?php endif; ?>
                                <?php if (!empty($product->catalog_product_variants)): ?>
                                    <span class="status"><?= count($product->catalog_product_variants) ?> opciones</span>
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
                                    <div>
                                        <?php if (!empty($product->catalog_product_variants)): ?>
                                            <?= $this->Form->hidden('measurement_type_id', ['value' => $product->measurement_type_id]) ?>
                                            <label>Tipo de medida</label>
                                            <p class="meta product-measurement-type"><?= h($product->measurement_type->name ?? 'Sin medida') ?></p>
                                            <p class="meta">Para cambiarlo, elimina primero sus opciones actuales.</p>
                                        <?php else: ?>
                                            <?= $this->Form->control('measurement_type_id', [
                                                'id' => 'product-' . (int)$product->id . '-measurement-type',
                                                'label' => 'Tipo de medida para sus opciones',
                                                'empty' => 'Sin variantes de medida',
                                                'options' => $measurementTypeOptions,
                                                'value' => $product->measurement_type_id,
                                                'required' => false,
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="field-wide">
                                        <?= $this->Form->control('description', ['id' => 'product-' . (int)$product->id . '-description', 'label' => 'Descripción corta', 'value' => $product->description]) ?>
                                    </div>
                                    <?php if ($advancedProductSeoEnabled): ?>
                                        <div class="field-wide">
                                            <?= $this->Form->control('seo_description', [
                                                'id' => 'product-' . (int)$product->id . '-seo-description',
                                                'label' => 'Descripción SEO del producto',
                                                'value' => $product->seo_description,
                                                'maxlength' => 180,
                                                'required' => false,
                                            ]) ?>
                                        </div>
                                        <div class="field-wide">
                                            <?= $this->Form->control('seo_keywords', [
                                                'id' => 'product-' . (int)$product->id . '-seo-keywords',
                                                'label' => 'Palabras clave SEO',
                                                'value' => $product->seo_keywords,
                                                'maxlength' => 255,
                                                'required' => false,
                                            ]) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <?= $this->Form->control('price', ['id' => 'product-' . (int)$product->id . '-price', 'label' => 'Valor', 'type' => 'number', 'step' => '0.01', 'value' => $product->price]) ?>
                                    </div>
                                    <div>
                                        <?= $this->Form->control('discount', ['id' => 'product-' . (int)$product->id . '-discount', 'label' => 'Descuento opcional', 'type' => 'number', 'step' => '0.01', 'value' => $product->discount]) ?>
                                    </div>
                                    <div>
                                        <?= $this->Form->control('duration', ['id' => 'product-' . (int)$product->id . '-duration', 'label' => 'Duración opcional', 'value' => $product->duration]) ?>
                                    </div>
                                    <div>
                                        <?= $this->Form->control('availability', [
                                            'id' => 'product-' . (int)$product->id . '-availability',
                                            'label' => 'Disponibilidad',
                                            'options' => $availabilityOptions,
                                            'value' => $product->availability ?: 'available',
                                        ]) ?>
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
                                    <?= $this->Form->button('Guardar cambios', ['class' => 'button primary']) ?>
                                </div>
                                <?= $this->Form->end() ?>
                                <section class="variant-section" aria-labelledby="product-<?= (int)$product->id ?>-variants-title">
                                    <div class="variant-section-heading">
                                        <div>
                                            <h3 id="product-<?= (int)$product->id ?>-variants-title">Variantes<?= $product->measurement_type ? ' de ' . h(strtolower((string)$product->measurement_type->name)) : '' ?></h3>
                                            <p class="meta">Agrega las opciones del producto, por ejemplo: Individual, Mediana y Familiar.</p>
                                        </div>
                                    </div>
                                    <?php if (!$product->measurement_type_id): ?>
                                        <p class="meta">Elige y guarda primero un tipo de medida para agregar sus variantes.</p>
                                    <?php else: ?>
                                    <?= $this->Form->create(null, [
                                        'url' => ['controller' => 'Catalogs', 'action' => 'addVariant', $product->id],
                                        'class' => 'variant-form',
                                    ]) ?>
                                    <div class="variant-form-grid">
                                        <?= $this->Form->control('name', [
                                            'id' => 'variant-create-' . (int)$product->id . '-name',
                                            'label' => 'Nombre de la opción',
                                            'placeholder' => 'Ej: Mediana, Familiar, Pack 100',
                                        ]) ?>
                                        <?= $this->Form->control('measurement_value', [
                                            'id' => 'variant-create-' . (int)$product->id . '-value',
                                            'label' => 'Medida opcional',
                                            'type' => 'number',
                                            'step' => '0.01',
                                            'min' => '0',
                                            'required' => false,
                                            'placeholder' => 'Ej: 30',
                                        ]) ?>
                                        <?= $this->Form->control('measurement_unit', [
                                            'id' => 'variant-create-' . (int)$product->id . '-unit',
                                            'label' => 'Unidad',
                                            'type' => 'select',
                                            'options' => $measurementUnits[(int)$product->measurement_type_id] ?? [],
                                            'empty' => 'Sin unidad',
                                            'disabled' => !$product->measurement_type_id,
                                        ]) ?>
                                        <?= $this->Form->control('price', [
                                            'id' => 'variant-create-' . (int)$product->id . '-price',
                                            'label' => 'Valor',
                                            'type' => 'number',
                                            'step' => '0.01',
                                            'min' => '0',
                                            'required' => false,
                                        ]) ?>
                                        <?= $this->Form->control('availability', [
                                            'id' => 'variant-create-' . (int)$product->id . '-availability',
                                            'label' => 'Disponibilidad',
                                            'options' => $availabilityOptions,
                                            'default' => 'available',
                                        ]) ?>
                                    </div>
                                    <div class="product-actions variant-actions">
                                        <?= $this->Form->button('Agregar opción', ['class' => 'button secondary']) ?>
                                    </div>
                                    <?= $this->Form->end() ?>

                                    <?php if (!empty($product->catalog_product_variants)): ?>
                                        <div class="variant-list">
                                            <?php foreach ($product->catalog_product_variants as $variant): ?>
                                                <?php $variantUnitOptions = $measurementUnits[(int)$product->measurement_type_id] ?? []; ?>
                                                <?= $this->Form->create($variant, [
                                                    'url' => ['controller' => 'Catalogs', 'action' => 'updateVariant', $variant->id],
                                                    'class' => 'variant-editor-row',
                                                ]) ?>
                                                <div class="variant-form-grid">
                                                    <?= $this->Form->control('name', ['id' => 'variant-' . (int)$variant->id . '-name', 'label' => 'Opción', 'value' => $variant->name]) ?>
                                                    <?= $this->Form->control('measurement_value', ['id' => 'variant-' . (int)$variant->id . '-value', 'label' => 'Medida opcional', 'type' => 'number', 'step' => '0.01', 'min' => '0', 'required' => false, 'value' => $variant->measurement_value]) ?>
                                                    <?= $this->Form->control('measurement_unit', [
                                                        'id' => 'variant-' . (int)$variant->id . '-unit',
                                                        'label' => 'Unidad',
                                                        'type' => 'select',
                                                        'options' => $variantUnitOptions,
                                                        'empty' => 'Sin unidad',
                                                        'value' => $variant->measurement_unit,
                                                        'disabled' => !$product->measurement_type_id,
                                                    ]) ?>
                                                    <?= $this->Form->control('price', ['id' => 'variant-' . (int)$variant->id . '-price', 'label' => 'Valor', 'type' => 'number', 'step' => '0.01', 'min' => '0', 'required' => false, 'value' => $variant->price]) ?>
                                                    <?= $this->Form->control('availability', ['id' => 'variant-' . (int)$variant->id . '-availability', 'label' => 'Disponibilidad', 'options' => $availabilityOptions, 'value' => $variant->availability ?: 'available']) ?>
                                                </div>
                                                <div class="product-actions variant-actions">
                                                    <?= $this->Form->button('Guardar opción', ['class' => 'button secondary']) ?>
                                                    <?= $this->Form->postLink('Eliminar opción', ['controller' => 'Catalogs', 'action' => 'deleteVariant', $variant->id], [
                                                        'class' => 'button danger',
                                                        'confirm' => '¿Eliminar esta opción?',
                                                    ]) ?>
                                                </div>
                                                <?= $this->Form->end() ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </section>
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
