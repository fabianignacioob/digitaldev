<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\CatalogTypography;
use App\Service\LocalImageStorageService;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use InvalidArgumentException;
use RuntimeException;

class CatalogsController extends AppController
{
    private const AVAILABILITY_OPTIONS = [
        'available' => 'Disponible',
        'unavailable' => 'Agotado o no disponible',
        'coming_soon' => 'Próximamente',
    ];

    private const BACKGROUND_PRESETS = [
        'parchment' => 'img/catalog-backgrounds/menu-parchment.png',
        'wood' => 'img/catalog-backgrounds/menu-wood.png',
        'vintage-paper' => 'img/catalog-backgrounds/menu-vintage-paper.jpg',
        'natural-fiber' => 'img/catalog-backgrounds/menu-natural-fiber.jpg',
        'rustic-wood' => 'img/catalog-backgrounds/menu-rustic-wood.jpg',
    ];

    public function edit(int $siteId): ?Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->viewBuilder()->setLayout('dashboard');
        $site = $this->getOwnedSite($siteId);
        $catalogSetting = $this->ensureCatalogSetting($siteId);
        $supportsCategories = $this->planService()->canUseCategories((int)$this->currentUserId(), $site);
        $featuredItemsEnabled = $this->planService()->hasFeature((int)$this->currentUserId(), 'featured_items_enabled');
        $featuredItemsLimit = $this->planService()->getLimit((int)$this->currentUserId(), 'featured_items_limit');
        $advancedProductSeoEnabled = $this->planService()->canUseAdvancedProductSeo((int)$this->currentUserId());
        $categoryLayoutAvailable = $this->planService()->canUseCategoryBlocks((int)$this->currentUserId(), $site);
        $templateKind = $this->planService()->templateKind($site);
        $itemTypeOptions = $this->itemTypeOptions($site);
        $catalogCategories = $this->fetchTable('CatalogCategories')->find()
            ->where(['site_id' => $siteId])
            ->orderByAsc('sort_order')
            ->all();
        $catalogProducts = $this->fetchTable('CatalogProducts')->find()
            ->contain(['CatalogCategories', 'MeasurementTypes', 'CatalogProductVariants'])
            ->where(['CatalogProducts.site_id' => $siteId])
            ->orderByAsc('CatalogProducts.sort_order')
            ->all();
        $categoryOptions = $this->fetchTable('CatalogCategories')->find('list')
            ->where(['site_id' => $siteId])
            ->orderByAsc('name')
            ->toArray();
        $backgroundPresets = self::BACKGROUND_PRESETS;
        $measurementTypes = $this->fetchTable('MeasurementTypes')->find()
            ->where(['active' => true])
            ->orderByAsc('sort_order')
            ->all();
        $availabilityOptions = self::AVAILABILITY_OPTIONS;

        $this->set(compact(
            'site',
            'catalogSetting',
            'catalogCategories',
            'catalogProducts',
            'categoryOptions',
            'backgroundPresets',
            'supportsCategories',
            'featuredItemsEnabled',
            'featuredItemsLimit',
            'advancedProductSeoEnabled',
            'categoryLayoutAvailable',
            'templateKind',
            'itemTypeOptions',
            'measurementTypes',
            'availabilityOptions',
        ));

        return null;
    }

    public function updateSettings(int $siteId): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'put', 'patch']);
        $site = $this->getOwnedSite($siteId);

        $settingsTable = $this->fetchTable('CatalogSettings');
        $catalogSetting = $this->ensureCatalogSetting($siteId);
        $data = $this->request->getData();
        $oldBackgroundPath = $catalogSetting->background_image_path;
        $preset = (string)($data['background_preset'] ?? '');
        $backgroundType = (string)($data['background_type'] ?? 'color');
        $data['background_type'] = $backgroundType === 'image' ? 'image' : 'color';

        if ($data['background_type'] === 'image' && $preset && isset(self::BACKGROUND_PRESETS[$preset])) {
            $data['background_image_path'] = self::BACKGROUND_PRESETS[$preset];
            $data['background_preset'] = $preset;
        }

        if ($data['background_type'] === 'image') {
            try {
                $backgroundPath = $this->saveCatalogUpload('background_upload', $siteId, 'backgrounds');
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $this->Flash->error($exception->getMessage());

                return $this->redirect(['action' => 'edit', $siteId]);
            }
            if ($backgroundPath) {
                $data['background_image_path'] = $backgroundPath;
                $data['background_preset'] = null;
            }
        }
        if ($data['background_type'] === 'color') {
            $data['background_image_path'] = null;
            $data['background_preset'] = null;
        }
        $data['title_font'] = CatalogTypography::normalize($data['title_font'] ?? null);
        $data['heading_font'] = $data['title_font'];
        $data['slogan_font'] = CatalogTypography::normalize($data['slogan_font'] ?? null);
        $data['category_layout'] = $this->planService()->canUseCategoryBlocks((int)$this->currentUserId(), $site)
            && ($data['category_layout'] ?? 'normal') === 'blocks'
            ? 'blocks'
            : 'normal';

        $catalogSetting = $settingsTable->patchEntity($catalogSetting, $data);
        if ($settingsTable->save($catalogSetting)) {
            if ($oldBackgroundPath && $oldBackgroundPath !== $catalogSetting->background_image_path) {
                $this->imageStorage()->delete((string)$oldBackgroundPath);
            }
            $this->Flash->success('Configuración de la carta guardada.');
        } else {
            $this->Flash->error('No pudimos guardar la configuración.');
        }

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function deleteBackground(int $siteId): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'delete']);
        $this->getOwnedSite($siteId);
        $settings = $this->fetchTable('CatalogSettings');
        $catalogSetting = $this->ensureCatalogSetting($siteId);
        $oldBackgroundPath = $catalogSetting->background_image_path;
        $catalogSetting = $settings->patchEntity($catalogSetting, [
            'background_type' => 'color',
            'background_image_path' => null,
            'background_preset' => null,
        ]);
        $settings->saveOrFail($catalogSetting);
        $this->imageStorage()->delete((string)$oldBackgroundPath);
        $this->Flash->success('Imagen de fondo eliminada.');

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function addCategory(int $siteId): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post']);
        $site = $this->getOwnedSite($siteId);
        if (!$this->planService()->canUseCategories((int)$this->currentUserId(), $site)) {
            $this->Flash->error('Esta plantilla no usa categorías.');

            return $this->redirect(['action' => 'edit', $siteId]);
        }
        if (!$this->planService()->canCreateCategory((int)$this->currentUserId(), $site)) {
            $this->Flash->error('Tu plan llegó al máximo de categorías permitidas para este sitio.');

            return $this->redirect(['action' => 'edit', $siteId]);
        }

        $categories = $this->fetchTable('CatalogCategories');
        $category = $categories->newEntity([
            'site_id' => $siteId,
            'name' => $this->request->getData('name'),
            'sort_order' => $this->nextCategorySortOrder($siteId),
        ]);

        if ($categories->save($category)) {
            $this->Flash->success('Categoría creada.');
        } else {
            $this->Flash->error('No pudimos crear la categoría. Revisa que no esté repetida.');
        }

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function deleteCategory(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'delete']);
        $category = $this->fetchTable('CatalogCategories')->find()
            ->contain(['Sites'])
            ->where(['CatalogCategories.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
        $siteId = (int)$category->site_id;

        $this->fetchTable('CatalogCategories')->delete($category);
        $this->Flash->success('Categoría eliminada. Los productos asociados quedaron sin categoría.');

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function updateCategory(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'patch', 'put']);
        $category = $this->fetchTable('CatalogCategories')->find()
            ->contain(['Sites.Templates'])
            ->where(['CatalogCategories.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
        $siteId = (int)$category->site_id;
        if (!$this->planService()->canUseCategories((int)$this->currentUserId(), $category->site)) {
            $this->Flash->error('Esta plantilla no usa categorías.');

            return $this->redirect(['action' => 'edit', $siteId]);
        }

        $categoryData = [
            'name' => $this->request->getData('name'),
        ];
        if ($this->request->getData('sort_order') !== null) {
            $categoryData['sort_order'] = (int)$this->request->getData('sort_order');
        }

        $category = $this->fetchTable('CatalogCategories')->patchEntity($category, $categoryData);

        if ($this->fetchTable('CatalogCategories')->save($category)) {
            $this->Flash->success('Categoría actualizada.');
        } else {
            $this->Flash->error('No pudimos actualizar la categoría.');
        }

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function reorderCategories(int $siteId): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post']);
        $site = $this->getOwnedSite($siteId);
        if (!$this->planService()->canUseCategories((int)$this->currentUserId(), $site)) {
            throw new BadRequestException('Esta plantilla no usa categorías.');
        }

        $categoryIds = array_values(array_filter(
            array_map('intval', (array)$this->request->getData('category_ids', [])),
            static fn (int $id): bool => $id > 0,
        ));
        if ($categoryIds === [] || count($categoryIds) !== count(array_unique($categoryIds))) {
            throw new BadRequestException('El orden de categorías no es válido.');
        }

        $categories = $this->fetchTable('CatalogCategories');
        $ownedCategories = $categories->find()
            ->where(['site_id' => $siteId])
            ->all()
            ->toList();
        $ownedIds = array_map(static fn (object $category): int => (int)$category->id, $ownedCategories);
        sort($ownedIds);
        $submittedIds = $categoryIds;
        sort($submittedIds);
        if ($ownedIds !== $submittedIds) {
            throw new BadRequestException('El orden debe incluir solo las categorías de este sitio.');
        }

        $categoriesById = [];
        foreach ($ownedCategories as $category) {
            $categoriesById[(int)$category->id] = $category;
        }
        $categories->getConnection()->transactional(function () use ($categories, $categoriesById, $categoryIds): void {
            foreach ($categoryIds as $position => $categoryId) {
                $category = $categoriesById[$categoryId];
                $category->sort_order = $position + 1;
                $categories->saveOrFail($category);
            }
        });

        return $this->response->withStatus(204);
    }

    public function addProduct(int $siteId): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post']);
        $site = $this->getOwnedSite($siteId);
        if (!$this->planService()->canCreateCatalogItem((int)$this->currentUserId(), $site)) {
            $this->Flash->error('Tu plan llegó al máximo de elementos permitidos para este sitio.');

            return $this->redirect(['action' => 'edit', $siteId]);
        }

        $data = $this->normalizeProductData((array)$this->request->getData(), $site, true);
        $data['site_id'] = $siteId;
        $data['sort_order'] = $this->nextProductSortOrder($siteId);
        try {
            $imagePath = $this->saveCatalogUpload('product_image', $siteId, 'products');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $this->Flash->error($exception->getMessage());

            return $this->redirect(['action' => 'edit', $siteId]);
        }
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $products = $this->fetchTable('CatalogProducts');
        $product = $products->newEntity($data);

        if ($products->save($product)) {
            $this->Flash->success('Elemento agregado.');
        } else {
            $this->Flash->error('No pudimos agregar el elemento. Revisa nombre, valor y categoría.');
        }

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function updateProduct(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'patch', 'put']);
        $product = $this->fetchTable('CatalogProducts')->find()
            ->contain(['Sites.Templates', 'MeasurementTypes', 'CatalogProductVariants'])
            ->where(['CatalogProducts.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
        $site = $product->site;
        $siteId = (int)$product->site_id;
        $data = $this->normalizeProductData((array)$this->request->getData(), $site, false, $product);
        $oldImagePath = $product->image_path;
        try {
            $imagePath = $this->saveCatalogUpload('product_image', $siteId, 'products');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $this->Flash->error($exception->getMessage());

            return $this->redirect(['action' => 'edit', $siteId]);
        }
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $product = $this->fetchTable('CatalogProducts')->patchEntity($product, $data);

        if ($this->fetchTable('CatalogProducts')->save($product)) {
            if ($imagePath && $oldImagePath) {
                $this->imageStorage()->delete((string)$oldImagePath);
            }
            $this->Flash->success('Elemento actualizado.');
        } else {
            $this->Flash->error('No pudimos actualizar el elemento.');
        }

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function addVariant(int $productId): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post']);
        $product = $this->getOwnedProduct($productId);
        if ($product->measurement_type_id === null) {
            $this->Flash->error('Define primero el tipo de medida del producto antes de agregar variantes.');

            return $this->redirect(['action' => 'edit', (int)$product->site_id]);
        }
        $variants = $this->fetchTable('CatalogProductVariants');
        $data = $this->normalizeVariantData((array)$this->request->getData(), $product);
        $data['catalog_product_id'] = $productId;
        $data['sort_order'] = $this->nextVariantSortOrder($productId);

        $variant = $variants->newEntity($data);
        if ($variants->save($variant)) {
            $this->Flash->success('Opción agregada.');
        } else {
            $this->Flash->error('No pudimos agregar la opción. Revisa su nombre, medida y valor.');
        }

        return $this->redirect(['action' => 'edit', (int)$product->site_id]);
    }

    public function updateVariant(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'patch', 'put']);
        $variant = $this->getOwnedVariant($id);
        $data = $this->normalizeVariantData((array)$this->request->getData(), $variant->catalog_product);
        $variant = $this->fetchTable('CatalogProductVariants')->patchEntity($variant, $data);
        if ($this->fetchTable('CatalogProductVariants')->save($variant)) {
            $this->Flash->success('Opción actualizada.');
        } else {
            $this->Flash->error('No pudimos actualizar la opción.');
        }

        return $this->redirect(['action' => 'edit', (int)$variant->catalog_product->site_id]);
    }

    public function deleteVariant(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'delete']);
        $variant = $this->getOwnedVariant($id);
        $siteId = (int)$variant->catalog_product->site_id;
        $this->fetchTable('CatalogProductVariants')->deleteOrFail($variant);
        $this->Flash->success('Opción eliminada.');

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function deleteProduct(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'delete']);
        $product = $this->fetchTable('CatalogProducts')->find()
            ->contain(['Sites'])
            ->where(['CatalogProducts.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
        $siteId = (int)$product->site_id;
        $oldImagePath = $product->image_path;

        if ($this->fetchTable('CatalogProducts')->delete($product)) {
            $this->imageStorage()->delete((string)$oldImagePath);
        }
        $this->Flash->success('Producto eliminado.');

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function deleteProductImage(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post', 'delete']);
        $products = $this->fetchTable('CatalogProducts');
        $product = $products->find()
            ->contain(['Sites'])
            ->where(['CatalogProducts.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
        $siteId = (int)$product->site_id;
        $oldImagePath = $product->image_path;
        $product->image_path = null;
        $products->saveOrFail($product);
        $this->imageStorage()->delete((string)$oldImagePath);
        $this->Flash->success('Imagen eliminada.');

        return $this->redirect(['action' => 'edit', $siteId]);
    }

    public function reorderProducts(int $siteId): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['post']);
        $this->getOwnedSite($siteId);
        $productIds = array_values(array_filter(
            array_map('intval', (array)$this->request->getData('product_ids', [])),
            static fn (int $id): bool => $id > 0,
        ));
        if ($productIds === [] || count($productIds) !== count(array_unique($productIds))) {
            throw new BadRequestException('El orden de productos no es válido.');
        }

        $products = $this->fetchTable('CatalogProducts');
        $ownedProducts = $products->find()
            ->where(['site_id' => $siteId])
            ->all()
            ->toList();
        $ownedIds = array_map(static fn (object $product): int => (int)$product->id, $ownedProducts);
        sort($ownedIds);
        $submittedIds = $productIds;
        sort($submittedIds);
        if ($ownedIds !== $submittedIds) {
            throw new BadRequestException('El orden debe incluir solo los productos de este sitio.');
        }

        $productsById = [];
        foreach ($ownedProducts as $product) {
            $productsById[(int)$product->id] = $product;
        }
        $products->getConnection()->transactional(function () use ($products, $productsById, $productIds): void {
            foreach ($productIds as $position => $productId) {
                $product = $productsById[$productId];
                $product->sort_order = $position + 1;
                $products->saveOrFail($product);
            }
        });

        return $this->response->withStatus(204);
    }

    private function getOwnedSite(int $siteId): \App\Model\Entity\Site
    {
        return $this->fetchTable('Sites')->find()
            ->contain(['Templates'])
            ->where(['Sites.id' => $siteId, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
    }

    private function getOwnedProduct(int $productId): \App\Model\Entity\CatalogProduct
    {
        return $this->fetchTable('CatalogProducts')->find()
            ->contain(['Sites.Templates', 'MeasurementTypes', 'CatalogProductVariants'])
            ->where(['CatalogProducts.id' => $productId, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
    }

    private function getOwnedVariant(int $variantId): \App\Model\Entity\CatalogProductVariant
    {
        return $this->fetchTable('CatalogProductVariants')->find()
            ->contain(['CatalogProducts.Sites.Templates', 'CatalogProducts.MeasurementTypes'])
            ->where(['CatalogProductVariants.id' => $variantId, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
    }

    private function ensureCatalogSetting(int $siteId): \App\Model\Entity\CatalogSetting
    {
        $settings = $this->fetchTable('CatalogSettings');
        $catalogSetting = $settings->find()->where(['site_id' => $siteId])->first();
        if ($catalogSetting) {
            return $catalogSetting;
        }

        $catalogSetting = $settings->newEntity([
            'site_id' => $siteId,
            'background_type' => 'color',
            'background_color' => '#fbfaf7',
            'background_preset' => null,
            'title_color' => '#17202a',
            'heading_font' => 'Inter, Arial, sans-serif',
            'title_font' => 'Inter, Arial, sans-serif',
            'slogan_color' => '#17202a',
            'slogan_font' => 'Inter, Arial, sans-serif',
            'title' => 'Nuestra carta',
            'slogan' => 'Sabores simples, bien presentados.',
            'intro_text' => 'Revisa nuestras opciones y consulta disponibilidad por WhatsApp.',
            'category_layout' => 'normal',
        ]);
        $settings->saveOrFail($catalogSetting);

        return $catalogSetting;
    }

    private function saveCatalogUpload(string $field, int $siteId, string $folder): ?string
    {
        return $this->imageStorage()->storeOptional(
            $this->request->getData($field),
            'uploads/sites/' . $siteId . '/catalog/' . $folder,
        );
    }

    private function imageStorage(): LocalImageStorageService
    {
        return new LocalImageStorageService();
    }

    private function itemTypeOptions(\App\Model\Entity\Site $site): array
    {
        $labels = [
            'menu_item' => 'Ítem de carta',
            'product' => 'Producto',
            'service' => 'Servicio',
        ];
        $options = [];
        foreach ($this->planService()->validItemTypesForTemplate($site) as $type) {
            $options[$type] = $labels[$type] ?? ucfirst($type);
        }

        return $options;
    }

    private function normalizeProductData(
        array $data,
        \App\Model\Entity\Site $site,
        bool $creating,
        ?\App\Model\Entity\CatalogProduct $existingProduct = null,
    ): array
    {
        $validItemTypes = $this->planService()->validItemTypesForTemplate($site);
        if (!in_array((string)($data['item_type'] ?? ''), $validItemTypes, true)) {
            $data['item_type'] = $this->planService()->defaultItemTypeForTemplate($site);
        }
        if (!$this->planService()->canUseCategories((int)$this->currentUserId(), $site) || empty($data['catalog_category_id'])) {
            $data['catalog_category_id'] = null;
        }
        if (($data['price'] ?? '') === '') {
            $data['price'] = null;
        }
        $data['price_prefix'] = !empty($data['price_prefix_enabled']) ? 'Desde' : null;
        if (($data['discount'] ?? '') === '') {
            $data['discount'] = null;
        }
        if (($data['duration'] ?? '') === '') {
            $data['duration'] = null;
        }
        $data['measurement_type_id'] = !empty($data['measurement_type_id']) ? (int)$data['measurement_type_id'] : null;
        if ($existingProduct && !empty($existingProduct->catalog_product_variants)) {
            $data['measurement_type_id'] = $existingProduct->measurement_type_id;
        }
        if ($data['measurement_type_id'] !== null && !$this->fetchTable('MeasurementTypes')->exists([
            'id' => $data['measurement_type_id'],
            'active' => true,
        ])) {
            throw new BadRequestException('El tipo de medida seleccionado no está disponible.');
        }
        $requestedFeatured = !empty($data['featured']);
        if ($requestedFeatured && !$this->planService()->canFeatureCatalogItem((int)$this->currentUserId(), $site, $existingProduct)) {
            throw new BadRequestException('Tu plan llegó al máximo de productos destacados para este sitio.');
        }
        $data['featured'] = $requestedFeatured;
        if (!$this->planService()->canUseAdvancedProductSeo((int)$this->currentUserId())) {
            unset($data['seo_description'], $data['seo_keywords']);
        }
        $data['active'] = $creating ? true : !empty($data['active']);
        $data['availability'] = array_key_exists((string)($data['availability'] ?? ''), self::AVAILABILITY_OPTIONS)
            ? (string)$data['availability']
            : 'available';
        unset($data['sort_order']);

        return $data;
    }

    private function normalizeVariantData(array $data, \App\Model\Entity\CatalogProduct $product): array
    {
        $data['measurement_value'] = ($data['measurement_value'] ?? '') === '' ? null : $data['measurement_value'];
        $data['measurement_unit'] = trim((string)($data['measurement_unit'] ?? '')) ?: null;
        $data['price'] = ($data['price'] ?? '') === '' ? null : $data['price'];
        $data['availability'] = array_key_exists((string)($data['availability'] ?? ''), self::AVAILABILITY_OPTIONS)
            ? (string)$data['availability']
            : 'available';

        if ($product->measurement_type_id === null) {
            $data['measurement_value'] = null;
            $data['measurement_unit'] = null;

            return $data;
        }

        $type = $this->fetchTable('MeasurementTypes')->find()
            ->where(['id' => $product->measurement_type_id, 'active' => true])
            ->first();
        if (!$type) {
            throw new BadRequestException('El tipo de medida seleccionado no está disponible.');
        }
        $units = $type->units;
        if (is_string($units)) {
            $units = json_decode($units, true) ?: [];
        }
        $allowedUnits = array_values(array_filter((array)$units, 'is_string'));
        if ($data['measurement_unit'] !== null && !in_array($data['measurement_unit'], $allowedUnits, true)) {
            throw new BadRequestException('La unidad seleccionada no corresponde al tipo de medida.');
        }

        return $data;
    }

    private function nextCategorySortOrder(int $siteId): int
    {
        $lastCategory = $this->fetchTable('CatalogCategories')->find()
            ->select(['sort_order'])
            ->where(['site_id' => $siteId])
            ->orderByDesc('sort_order')
            ->first();

        return ((int)($lastCategory->sort_order ?? 0)) + 1;
    }

    private function nextProductSortOrder(int $siteId): int
    {
        $lastProduct = $this->fetchTable('CatalogProducts')->find()
            ->select(['sort_order'])
            ->where(['site_id' => $siteId])
            ->orderByDesc('sort_order')
            ->first();

        return ((int)($lastProduct->sort_order ?? 0)) + 1;
    }

    private function nextVariantSortOrder(int $productId): int
    {
        $lastVariant = $this->fetchTable('CatalogProductVariants')->find()
            ->select(['sort_order'])
            ->where(['catalog_product_id' => $productId])
            ->orderByDesc('sort_order')
            ->first();

        return ((int)($lastVariant->sort_order ?? 0)) + 1;
    }
}
