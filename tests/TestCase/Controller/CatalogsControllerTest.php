<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\UploadedFile;

class CatalogsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    private int $userId;
    private int $themeId;
    private ?string $previousPlatformDomain = null;
    private ?string $previousPublicBaseDomain = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousPlatformDomain = getenv('APP_PLATFORM_DOMAIN') !== false ? (string)getenv('APP_PLATFORM_DOMAIN') : null;
        $this->previousPublicBaseDomain = getenv('APP_PUBLIC_BASE_DOMAIN') !== false ? (string)getenv('APP_PUBLIC_BASE_DOMAIN') : null;
        putenv('APP_PLATFORM_DOMAIN=catops.local');
        putenv('APP_PUBLIC_BASE_DOMAIN=vitrinahub.local');

        $this->ensurePlans();
        $this->ensureTemplates();
        $this->themeId = $this->ensureTheme();
        $this->userId = $this->createUser('catalogo-' . uniqid() . '@example.test');
        $this->createActiveSubscription($this->userId, 'full');
    }

    protected function tearDown(): void
    {
        $this->previousPlatformDomain === null ? putenv('APP_PLATFORM_DOMAIN') : putenv('APP_PLATFORM_DOMAIN=' . $this->previousPlatformDomain);
        $this->previousPublicBaseDomain === null ? putenv('APP_PUBLIC_BASE_DOMAIN') : putenv('APP_PUBLIC_BASE_DOMAIN=' . $this->previousPublicBaseDomain);

        parent::tearDown();
    }

    public function testCategoryCrudAndOwnership(): void
    {
        $siteId = $this->createSite($this->userId, 'carta-categorias', 'carta-cat-' . uniqid());
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/carta/categorias', [
            'name' => 'Entradas',
            'sort_order' => 2,
        ]);
        $this->assertRedirect('/sitios/' . $siteId . '/carta');
        $category = $this->table('CatalogCategories')->find()->where(['site_id' => $siteId])->firstOrFail();
        $this->assertSame('Entradas', $category->name);

        $this->post('/carta/categorias/editar/' . $category->id, [
            'name' => 'Platos principales',
            'sort_order' => 1,
        ]);
        $category = $this->table('CatalogCategories')->get($category->id);
        $this->assertSame('Platos principales', $category->name);
        $this->assertSame(1, (int)$category->sort_order);

        $otherUserId = $this->createUser('ajeno-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId, 'full');
        $this->loginAs($otherUserId);
        $this->enableCsrfToken();
        $this->post('/carta/categorias/editar/' . $category->id, [
            'name' => 'No autorizado',
            'sort_order' => 9,
        ]);
        $this->assertResponseCode(404);

        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/carta/categorias/eliminar/' . $category->id);
        $this->assertSame(0, $this->table('CatalogCategories')->find()->where(['id' => $category->id])->count());
    }

    public function testCategoryRejectedOnSimpleTemplate(): void
    {
        $siteId = $this->createSite($this->userId, 'carta-simple', 'carta-simple-' . uniqid());
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/carta/categorias', [
            'name' => 'No debe crear',
            'sort_order' => 1,
        ]);

        $this->assertRedirect('/sitios/' . $siteId . '/carta');
        $this->assertSame(0, $this->table('CatalogCategories')->find()->where(['site_id' => $siteId])->count());
    }

    public function testProductsServicesPriceVariantsAndWhatsapp(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'servicios-demo-' . uniqid(), 'Servicios Demo');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/carta/productos', [
            'item_type' => 'service',
            'name' => 'Servicio Express',
            'description' => 'Atención rápida',
            'price' => '15000',
            'price_prefix_enabled' => '1',
            'discount' => '',
            'duration' => '45 min',
            'featured' => '1',
            'sort_order' => 1,
        ]);
        $product = $this->table('CatalogProducts')->find()->where(['site_id' => $siteId])->firstOrFail();
        $this->assertSame('service', $product->item_type);
        $this->assertSame('Desde', $product->price_prefix);
        $this->assertSame('45 min', $product->duration);
        $this->assertTrue((bool)$product->featured);

        $this->post('/carta/productos/editar/' . $product->id, [
            'item_type' => 'service',
            'name' => 'Servicio Express Plus',
            'description' => 'Atención prioritaria',
            'price' => '',
            'discount' => '',
            'duration' => '60 min',
            'featured' => '0',
            'active' => '1',
            'sort_order' => 3,
        ]);
        $product = $this->table('CatalogProducts')->get($product->id);
        $this->assertSame('Servicio Express Plus', $product->name);
        $this->assertNull($product->price);
        $this->assertNull($product->price_prefix);
        $this->assertSame('60 min', $product->duration);
        $this->assertFalse((bool)$product->featured);
        $this->assertSame(1, (int)$product->sort_order);

        $this->post('/sitios/publicar/' . $siteId);
        $this->getPublicVitrina((string)$this->table('Sites')->get($siteId)->subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('Consultar');
        $this->assertResponseContains('60 min');
        $this->assertResponseContains('Cotizar');
        $this->assertResponseContains('https://wa.me/56912345678?text=Hola%2C%20quiero%20consultar%20por%20Servicio%20Express%20Plus%20en%20Servicios%20Demo.');

        $this->post('/carta/productos/editar/' . $product->id, [
            'item_type' => 'service',
            'name' => 'Servicio oculto',
            'description' => '',
            'price' => '',
            'discount' => '',
            'duration' => '',
            'active' => '0',
            'sort_order' => 4,
        ]);
        $this->getPublicVitrina((string)$this->table('Sites')->get($siteId)->subdomain);
        $this->assertResponseOk();
        $this->assertResponseNotContains('Servicio oculto');
    }

    public function testFeaturedItemsRespectThePlanLimit(): void
    {
        $plan = $this->table('Plans')->find()->where(['slug' => 'limitado'])->firstOrFail();
        $capabilities = json_decode((string)$plan->capabilities, true);
        $capabilities['featured_items_enabled'] = true;
        $capabilities['featured_items_limit'] = 1;
        $capabilities['items_limit'] = 10;
        $plan->capabilities = json_encode($capabilities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->table('Plans')->saveOrFail($plan);
        $subscription = $this->table('Subscriptions')->find()->where(['user_id' => $this->userId])->firstOrFail();
        $subscription->plan_slug = 'limitado';
        $this->table('Subscriptions')->saveOrFail($subscription);

        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'destacados-' . uniqid());
        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/sitios/' . $siteId . '/carta/productos', [
            'item_type' => 'product',
            'name' => 'Primer destacado',
            'featured' => '1',
        ]);
        $this->assertRedirect('/sitios/' . $siteId . '/carta');

        $this->post('/sitios/' . $siteId . '/carta/productos', [
            'item_type' => 'product',
            'name' => 'Segundo destacado',
            'featured' => '1',
        ]);
        $this->assertResponseCode(400);
        $this->assertSame(1, $this->table('CatalogProducts')->find()->where(['site_id' => $siteId, 'featured' => true])->count());
    }

    public function testFullPlanAppliesCategoryBlocksAndProductSeoPublicly(): void
    {
        $plan = $this->table('Plans')->find()->where(['slug' => 'full'])->firstOrFail();
        $capabilities = json_decode((string)$plan->capabilities, true);
        $capabilities['seo_level'] = 'advanced';
        $capabilities['customization_level'] = 'advanced';
        $plan->capabilities = json_encode($capabilities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->table('Plans')->saveOrFail($plan);

        $siteId = $this->createSite($this->userId, 'catalogo-categorias', 'bloques-' . uniqid(), 'Tienda SEO');
        $category = $this->table('CatalogCategories')->newEntity([
            'site_id' => $siteId,
            'name' => 'Destacados',
            'sort_order' => 1,
        ]);
        $this->table('CatalogCategories')->saveOrFail($category);
        $this->table('CatalogProducts')->saveOrFail($this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'catalog_category_id' => $category->id,
            'item_type' => 'product',
            'name' => 'Producto SEO',
            'description' => 'Descripción visible',
            'seo_description' => 'Descripción preparada para buscadores.',
            'seo_keywords' => 'producto seo, tienda demo',
            'active' => true,
            'sort_order' => 1,
        ]));
        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/sitios/' . $siteId . '/carta/configuracion', [
            'background_type' => 'color',
            'background_color' => '#fbfaf7',
            'title' => 'Tienda SEO',
            'title_color' => '#17202a',
            'title_font' => 'Inter, Arial, sans-serif',
            'slogan' => 'Productos organizados',
            'slogan_color' => '#17202a',
            'slogan_font' => 'Inter, Arial, sans-serif',
            'category_layout' => 'blocks',
            'show_product_action' => '1',
        ]);
        $this->assertRedirect('/sitios/' . $siteId . '/carta');
        $siteToPublish = $this->table('Sites')->get($siteId);
        $siteToPublish->status = 'published';
        $siteToPublish->published_at = DateTime::now();
        $this->table('Sites')->saveOrFail($siteToPublish);
        $site = $this->table('Sites')->get($siteId);

        $this->getPublicVitrina((string)$site->subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('class="category-blocks"');
        $this->assertResponseContains('Descripción preparada para buscadores.');
        $this->assertResponseContains('producto seo, tienda demo');
    }

    public function testPublicProductPresentationMatchesSelectedTemplateAndPreview(): void
    {
        $menuSiteId = $this->createSite($this->userId, 'carta-categorias', 'menu-' . uniqid(), 'Bistró Demo');
        $category = $this->table('CatalogCategories')->newEntity([
            'site_id' => $menuSiteId,
            'name' => 'Entradas',
            'sort_order' => 1,
        ]);
        $this->table('CatalogCategories')->saveOrFail($category);
        $this->table('CatalogCategories')->saveOrFail($this->table('CatalogCategories')->newEntity([
            'site_id' => $menuSiteId,
            'name' => 'Categoría vacía',
            'sort_order' => 2,
        ]));
        $this->table('CatalogProducts')->saveOrFail($this->table('CatalogProducts')->newEntity([
            'site_id' => $menuSiteId,
            'catalog_category_id' => $category->id,
            'item_type' => 'menu_item',
            'name' => 'Empanada de queso',
            'description' => 'Masa artesanal y queso fundido.',
            'price' => 4500,
            'featured' => true,
            'active' => true,
            'sort_order' => 1,
        ]));
        $this->table('CatalogProducts')->saveOrFail($this->table('CatalogProducts')->newEntity([
            'site_id' => $menuSiteId,
            'item_type' => 'menu_item',
            'name' => 'Sopa del día',
            'active' => true,
            'sort_order' => 2,
        ]));
        $menuSite = $this->table('Sites')->get($menuSiteId);
        $menuSite->status = 'published';
        $this->table('Sites')->saveOrFail($menuSite);

        $this->getPublicVitrina((string)$menuSite->subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('class="menu-list"');
        $this->assertResponseContains('Empanada de queso');
        $this->assertResponseContains('Otras preparaciones');
        $this->assertResponseNotContains('Categoría vacía');
        $this->assertResponseNotContains('class="product-image"');

        $this->loginAs($this->userId);
        $this->get('/sitios/preview/' . $menuSiteId);
        $this->assertResponseOk();
        $this->assertResponseContains('class="menu-list"');

        $catalogSiteId = $this->createSite($this->userId, 'catalogo-simple', 'catalogo-' . uniqid(), 'Tienda Demo');
        $this->table('CatalogProducts')->saveOrFail($this->table('CatalogProducts')->newEntity([
            'site_id' => $catalogSiteId,
            'item_type' => 'product',
            'name' => 'Producto con imagen',
            'active' => true,
            'sort_order' => 1,
        ]));
        $catalogSite = $this->table('Sites')->get($catalogSiteId);
        $catalogSite->status = 'published';
        $this->table('Sites')->saveOrFail($catalogSite);

        $this->getPublicVitrina((string)$catalogSite->subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('class="products"');
        $this->assertResponseContains('class="product-image"');
    }

    public function testUnknownTemplateFallsBackToCatalogPresentation(): void
    {
        $templates = $this->table('Templates');
        $template = $templates->newEntity([
            'name' => 'Plantilla histórica',
            'slug' => 'legacy-' . uniqid(),
            'active' => true,
        ]);
        $templates->saveOrFail($template);
        $subdomain = 'legacy-' . uniqid();
        $site = $this->table('Sites')->newEntity([
            'user_id' => $this->userId,
            'template_id' => $template->id,
            'theme_id' => $this->themeId,
            'name' => 'Sitio histórico',
            'slug' => $subdomain,
            'subdomain' => $subdomain,
            'status' => 'published',
            'whatsapp_country_code' => '56',
            'whatsapp_number' => '912345678',
        ]);
        $this->table('Sites')->saveOrFail($site);
        $this->table('CatalogSettings')->saveOrFail($this->table('CatalogSettings')->newEntity([
            'site_id' => $site->id,
            'background_type' => 'color',
            'background_color' => '#fbfaf7',
            'title' => 'Sitio histórico',
            'slogan' => 'Catálogo de prueba',
        ]));
        $this->table('CatalogProducts')->saveOrFail($this->table('CatalogProducts')->newEntity([
            'site_id' => $site->id,
            'item_type' => 'product',
            'name' => 'Producto histórico',
            'active' => true,
            'sort_order' => 1,
        ]));

        $this->getPublicVitrina((string)$subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('class="products"');
        $this->assertResponseContains('Producto histórico');
    }

    public function testPublicContactVisibilityAndProductActionAreConfigurable(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'contacto-' . uniqid(), 'Tienda Contacto');
        $site = $this->table('Sites')->get($siteId);
        $site->status = 'published';
        $site->show_whatsapp = false;
        $site->show_instagram = true;
        $site->instagram = 'https://instagram.com/tienda_contacto';
        $this->table('Sites')->saveOrFail($site);
        $settings = $this->table('CatalogSettings')->find()->where(['site_id' => $siteId])->firstOrFail();
        $settings->show_product_action = false;
        $this->table('CatalogSettings')->saveOrFail($settings);
        $this->table('CatalogProducts')->saveOrFail($this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'item_type' => 'product',
            'name' => 'Producto de contacto',
            'active' => true,
            'sort_order' => 1,
        ]));

        $this->getPublicVitrina((string)$site->subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('https://instagram.com/tienda_contacto');
        $this->assertResponseNotContains('https://wa.me/56912345678');
        $this->assertResponseNotContains('class="product-action"');

        $settings->show_product_action = true;
        $this->table('CatalogSettings')->saveOrFail($settings);
        $this->getPublicVitrina((string)$site->subdomain);
        $this->assertResponseContains('class="product-action"');
        $this->assertResponseContains('https://wa.me/56912345678');

        $site->show_instagram = false;
        $this->table('Sites')->saveOrFail($site);
        $this->getPublicVitrina((string)$site->subdomain);
        $this->assertResponseNotContains('https://instagram.com/tienda_contacto');
    }

    public function testItemLimitByPlan(): void
    {
        $limitedUserId = $this->createUser('limite-' . uniqid() . '@example.test');
        $this->createActiveSubscription($limitedUserId, 'limitado');
        $siteId = $this->createSite($limitedUserId, 'catalogo-simple', 'limitado-' . uniqid());
        $this->loginAs($limitedUserId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/carta/productos', [
            'item_type' => 'product',
            'name' => 'Primer producto',
            'price' => '1000',
            'sort_order' => 1,
        ]);
        $this->post('/sitios/' . $siteId . '/carta/productos', [
            'item_type' => 'product',
            'name' => 'Segundo producto',
            'price' => '2000',
            'sort_order' => 2,
        ]);

        $this->assertSame(1, $this->table('CatalogProducts')->find()->where(['site_id' => $siteId])->count());
    }

    public function testProductAvailabilityAndVariantsAreStoredAndShownPublicly(): void
    {
        $siteId = $this->createSite($this->userId, 'carta-simple', 'variantes-' . uniqid(), 'Pizzería Demo');
        $measurementTypes = $this->table('MeasurementTypes');
        $sizeType = $measurementTypes->find()->where(['slug' => 'size'])->first();
        if (!$sizeType) {
            $measurementTypes->getConnection()->insert('measurement_types', [
                'slug' => 'size',
                'name' => 'Tamaño',
                'units' => json_encode(['cm', 'in'], JSON_UNESCAPED_UNICODE),
                'sort_order' => 1,
                'active' => true,
                'created' => DateTime::now(),
                'modified' => DateTime::now(),
            ]);
            $sizeType = $measurementTypes->find()->where(['slug' => 'size'])->firstOrFail();
        }
        $product = $this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'item_type' => 'menu_item',
            'name' => 'Pizza napolitana',
            'description' => 'Tomate, mozzarella y albahaca.',
            'measurement_type_id' => $sizeType->id,
            'price' => null,
            'availability' => 'available',
            'active' => true,
            'sort_order' => 1,
        ]);
        $this->table('CatalogProducts')->saveOrFail($product);

        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/carta/productos/' . $product->id . '/variantes', [
            'name' => 'Familiar',
            'measurement_value' => '40',
            'measurement_unit' => 'cm',
            'price' => '14990',
            'availability' => 'available',
        ]);
        $this->assertRedirect('/sitios/' . $siteId . '/carta');

        $variant = $this->table('CatalogProductVariants')->find()
            ->where(['catalog_product_id' => $product->id])
            ->firstOrFail();
        $this->assertSame('Familiar', $variant->name);
        $this->assertSame(40.0, (float)$variant->measurement_value);
        $this->assertSame('cm', $variant->measurement_unit);
        $this->assertSame('available', $variant->availability);

        $this->get('/sitios/preview/' . $siteId);
        $this->assertResponseOk();
        $this->assertResponseContains('Familiar');
        $this->assertResponseContains('40 cm');

        $this->post('/sitios/publicar/' . $siteId);
        $this->getPublicVitrina((string)$this->table('Sites')->get($siteId)->subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('Desde $14.990');
        $this->assertResponseContains('Familiar');
        $this->assertResponseContains('40 cm');
        $this->assertResponseContains('Pizza%20napolitana%20en');
        $this->assertResponseNotContains('Pizza%20napolitana%20%28Familiar%2040%20cm%29');

        $this->post('/carta/productos/editar/' . $product->id, [
            'item_type' => 'menu_item',
            'name' => 'Pizza napolitana',
            'description' => '',
            'price' => '',
            'discount' => '',
            'duration' => '',
            'availability' => 'unavailable',
            'active' => '1',
        ]);
        $product = $this->table('CatalogProducts')->get($product->id);
        $this->assertSame('unavailable', $product->availability);

        $this->getPublicVitrina((string)$this->table('Sites')->get($siteId)->subdomain);
        $this->assertResponseContains('Agotado');
        $this->assertResponseNotContains('Pizza%20napolitana%20en');
    }

    public function testCannotModifyAnotherUsersVariant(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'variante-propia-' . uniqid());
        $product = $this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'item_type' => 'product',
            'name' => 'Tornillo Phillips',
            'availability' => 'available',
            'active' => true,
            'sort_order' => 1,
        ]);
        $this->table('CatalogProducts')->saveOrFail($product);
        $variant = $this->table('CatalogProductVariants')->newEntity([
            'catalog_product_id' => $product->id,
            'name' => '2 pulgadas',
            'price' => 190,
            'availability' => 'available',
            'sort_order' => 1,
        ]);
        $this->table('CatalogProductVariants')->saveOrFail($variant);

        $otherUserId = $this->createUser('otra-variante-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId, 'full');
        $this->loginAs($otherUserId);
        $this->enableCsrfToken();
        $this->post('/carta/variantes/editar/' . $variant->id, [
            'name' => 'No autorizado',
            'price' => '1',
            'availability' => 'available',
        ]);

        $this->assertResponseCode(404);
        $variant = $this->table('CatalogProductVariants')->get($variant->id);
        $this->assertSame('2 pulgadas', $variant->name);
    }

    public function testPublicAccessAndSuspensionByExpiredLicense(): void
    {
        $siteId = $this->createSite($this->userId, 'carta-simple', 'publico-' . uniqid());
        $this->table('CatalogProducts')->saveOrFail($this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'item_type' => 'menu_item',
            'name' => 'Producto público',
            'price' => 5000,
            'active' => true,
            'sort_order' => 1,
        ]));
        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/sitios/publicar/' . $siteId);

        $subdomain = $this->table('Sites')->get($siteId)->subdomain;
        $this->getPublicVitrina((string)$subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('Producto público');

        $subscription = $this->table('Subscriptions')->find()->where(['user_id' => $this->userId])->firstOrFail();
        $subscription->ends_at = DateTime::now()->subDays(1);
        $this->table('Subscriptions')->saveOrFail($subscription);
        $this->table('Payments')->updateAll([
            'period_end' => DateTime::now()->subDays(1),
        ], ['subscription_id' => $subscription->id]);

        $this->getPublicVitrina((string)$subdomain);
        $this->assertResponseCode(503);
        $this->assertResponseContains('suscripción venció');
    }

    public function testCannotModifyAnotherUsersProduct(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'owner-' . uniqid());
        $product = $this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'item_type' => 'product',
            'name' => 'Producto dueño',
            'price' => 1000,
            'active' => true,
            'sort_order' => 1,
        ]);
        $this->table('CatalogProducts')->saveOrFail($product);

        $otherUserId = $this->createUser('otro-producto-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId, 'full');
        $this->loginAs($otherUserId);
        $this->enableCsrfToken();
        $this->post('/carta/productos/editar/' . $product->id, [
            'item_type' => 'product',
            'name' => 'Hack',
            'price' => '1',
            'active' => '1',
        ]);

        $this->assertResponseCode(404);
        $product = $this->table('CatalogProducts')->get($product->id);
        $this->assertSame('Producto dueño', $product->name);
    }

    public function testProductOrderIsSavedFromDragAndDrop(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'orden-' . uniqid());
        $products = $this->table('CatalogProducts');
        $productIds = [];
        foreach (['Primero', 'Segundo', 'Tercero'] as $position => $name) {
            $product = $products->newEntity([
                'site_id' => $siteId,
                'item_type' => 'product',
                'name' => $name,
                'active' => true,
                'sort_order' => $position + 1,
            ]);
            $products->saveOrFail($product);
            $productIds[] = (int)$product->id;
        }

        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/sitios/' . $siteId . '/carta/productos/orden', [
            'product_ids' => [$productIds[2], $productIds[0], $productIds[1]],
        ]);

        $this->assertResponseCode(204);
        $orderedIds = $products->find()
            ->select(['id'])
            ->where(['site_id' => $siteId])
            ->orderByAsc('sort_order')
            ->all()
            ->extract('id')
            ->toList();
        $this->assertSame([$productIds[2], $productIds[0], $productIds[1]], array_map('intval', $orderedIds));
    }

    public function testProductOrderRejectsProductsFromAnotherSite(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'orden-propio-' . uniqid());
        $otherSiteId = $this->createSite($this->userId, 'catalogo-simple', 'orden-ajeno-' . uniqid());
        $products = $this->table('CatalogProducts');
        $ownProduct = $products->newEntity([
            'site_id' => $siteId,
            'item_type' => 'product',
            'name' => 'Producto propio',
            'active' => true,
            'sort_order' => 1,
        ]);
        $otherProduct = $products->newEntity([
            'site_id' => $otherSiteId,
            'item_type' => 'product',
            'name' => 'Producto ajeno',
            'active' => true,
            'sort_order' => 1,
        ]);
        $products->saveOrFail($ownProduct);
        $products->saveOrFail($otherProduct);

        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/sitios/' . $siteId . '/carta/productos/orden', [
            'product_ids' => [(int)$otherProduct->id],
        ]);

        $this->assertResponseCode(400);
    }

    public function testColorBackgroundClearsImageAndUsesSelectedTypographyPublicly(): void
    {
        $siteId = $this->createSite($this->userId, 'carta-simple', 'estilo-' . uniqid(), 'Café Prueba');
        $settings = $this->table('CatalogSettings')->find()->where(['site_id' => $siteId])->firstOrFail();
        $settings->background_type = 'image';
        $settings->background_image_path = 'img/catalog-backgrounds/menu-wood.png';
        $settings->background_preset = 'wood';
        $this->table('CatalogSettings')->saveOrFail($settings);

        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/sitios/' . $siteId . '/carta/configuracion', [
            'background_type' => 'color',
            'background_color' => '#ddeeff',
            'background_preset' => 'wood',
            'title' => 'Carta de prueba',
            'title_color' => '#112233',
            'title_font' => 'Georgia, serif',
            'slogan' => 'Preparado con cariño',
            'slogan_color' => '#223344',
            'slogan_font' => 'Verdana, Arial, sans-serif',
        ]);

        $this->assertRedirect('/sitios/' . $siteId . '/carta');
        $settings = $this->table('CatalogSettings')->get($settings->id);
        $this->assertSame('color', $settings->background_type);
        $this->assertNull($settings->background_image_path);
        $this->assertNull($settings->background_preset);
        $this->assertSame('Georgia, serif', $settings->title_font);
        $this->assertSame('Verdana, Arial, sans-serif', $settings->slogan_font);

        $this->post('/sitios/publicar/' . $siteId);
        $this->getPublicVitrina((string)$this->table('Sites')->get($siteId)->subdomain);
        $this->assertResponseOk();
        $this->assertResponseContains('--catalog-title-font: Georgia, serif');
        $this->assertResponseContains('--catalog-slogan-font: Verdana, Arial, sans-serif');
        $this->assertResponseNotContains('menu-wood.png');
    }

    public function testNewSuggestedBackgroundPresetCanBeSaved(): void
    {
        $siteId = $this->createSite($this->userId, 'carta-simple', 'fondo-sugerido-' . uniqid());
        $settings = $this->table('CatalogSettings')->find()->where(['site_id' => $siteId])->firstOrFail();

        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/sitios/' . $siteId . '/carta/configuracion', [
            'background_type' => 'image',
            'background_color' => '#fbfaf7',
            'background_preset' => 'natural-fiber',
            'title' => 'Carta de prueba',
            'title_color' => '#17202a',
            'title_font' => 'Inter, Arial, sans-serif',
            'slogan' => 'Textura sugerida',
            'slogan_color' => '#17202a',
            'slogan_font' => 'Inter, Arial, sans-serif',
        ]);

        $this->assertRedirect('/sitios/' . $siteId . '/carta');
        $settings = $this->table('CatalogSettings')->get($settings->id);
        $this->assertSame('image', $settings->background_type);
        $this->assertSame('natural-fiber', $settings->background_preset);
        $this->assertSame('img/catalog-backgrounds/menu-natural-fiber.jpg', $settings->background_image_path);
    }

    public function testRejectsInvalidProductImageFromPanelFlow(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'imagen-invalida-' . uniqid());
        $invalidPath = $this->writeTempFile('fake-image.png', 'archivo inválido');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/carta/productos', [
            'product_image' => $this->uploadedFile($invalidPath, 'image/png', 'fake-image.png'),
            'item_type' => 'product',
            'name' => 'No debe guardarse',
            'price' => '1000',
            'sort_order' => 1,
        ]);

        $this->assertRedirect('/sitios/' . $siteId . '/carta');
        $this->assertSame(0, $this->table('CatalogProducts')->find()->where(['site_id' => $siteId])->count());
    }

    public function testProductWithoutImageUsesFallbackOnPublicSite(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'fallback-' . uniqid());
        $this->table('CatalogProducts')->saveOrFail($this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'item_type' => 'product',
            'name' => 'Producto sin foto',
            'price' => null,
            'active' => true,
            'sort_order' => 1,
        ]));
        $this->loginAs($this->userId);
        $this->enableCsrfToken();
        $this->post('/sitios/publicar/' . $siteId);

        $this->getPublicVitrina((string)$this->table('Sites')->get($siteId)->subdomain);

        $this->assertResponseOk();
        $this->assertResponseContains('/img/placeholder.png');
        $this->assertResponseContains('Producto sin foto');
        $this->assertResponseContains('Consultar');
    }

    public function testDeleteOwnProductImage(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'borra-imagen-' . uniqid());
        $imagePath = $this->createStoredImage('uploads/sites/' . $siteId . '/catalog/products/own.png');
        $product = $this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'item_type' => 'product',
            'name' => 'Producto con foto',
            'image_path' => $imagePath,
            'active' => true,
            'sort_order' => 1,
        ]);
        $this->table('CatalogProducts')->saveOrFail($product);
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/carta/productos/imagen/eliminar/' . $product->id);

        $this->assertRedirect('/sitios/' . $siteId . '/carta');
        $product = $this->table('CatalogProducts')->get($product->id);
        $this->assertNull($product->image_path);
        $this->assertFileDoesNotExist(WWW_ROOT . $imagePath);
    }

    public function testCannotDeleteAnotherUsersProductImage(): void
    {
        $siteId = $this->createSite($this->userId, 'catalogo-simple', 'imagen-ajena-' . uniqid());
        $imagePath = $this->createStoredImage('uploads/sites/' . $siteId . '/catalog/products/foreign.png');
        $product = $this->table('CatalogProducts')->newEntity([
            'site_id' => $siteId,
            'item_type' => 'product',
            'name' => 'Producto dueño',
            'image_path' => $imagePath,
            'active' => true,
            'sort_order' => 1,
        ]);
        $this->table('CatalogProducts')->saveOrFail($product);

        $otherUserId = $this->createUser('otro-borrado-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId, 'full');
        $this->loginAs($otherUserId);
        $this->enableCsrfToken();

        $this->post('/carta/productos/imagen/eliminar/' . $product->id);

        $this->assertResponseCode(404);
        $product = $this->table('CatalogProducts')->get($product->id);
        $this->assertSame($imagePath, $product->image_path);
        $this->assertFileExists(WWW_ROOT . $imagePath);
        unlink(WWW_ROOT . $imagePath);
    }

    private function ensurePlans(): void
    {
        $this->ensurePlan('full', 16990, 7, 5, 500, ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias']);
        $this->ensurePlan('limitado', 1000, 1, 1, 1, ['catalogo-simple']);
    }

    private function ensurePlan(string $slug, int $price, int $maxSites, int $maxPublished, int $itemsLimit, array $templates): void
    {
        $plans = $this->table('Plans');
        $plan = $plans->find()->where(['slug' => $slug])->first();
        $data = [
            'name' => ucfirst($slug),
            'slug' => $slug,
            'monthly_price' => $price,
            'max_sites' => $maxSites,
            'max_published' => $maxPublished,
            'sort_order' => $slug === 'limitado' ? 0 : 3,
            'active' => true,
            'capabilities' => json_encode([
                'sites_configured_limit' => $maxSites,
                'sites_published_limit' => $maxPublished,
                'items_limit' => $itemsLimit,
                'categories_enabled' => true,
                'featured_items_enabled' => true,
                'categories_limit' => 50,
                'image_storage_limit_mb' => 2000,
                'customization_level' => 'advanced',
                'analytics_level' => 'none',
                'seo_level' => 'basic',
                'qr_enabled' => false,
                'custom_domain_enabled' => false,
                'premium_themes_enabled' => false,
                'catops_branding_removable' => false,
                'priority_support' => false,
                'enabled_templates' => $templates,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
        $plan = $plan ? $plans->patchEntity($plan, $data) : $plans->newEntity($data);
        $plans->saveOrFail($plan);
    }

    private function ensureTemplates(): void
    {
        foreach (['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'] as $slug) {
            $templates = $this->table('Templates');
            $template = $templates->find()->where(['slug' => $slug])->first();
            $data = [
                'name' => ucwords(str_replace('-', ' ', $slug)),
                'slug' => $slug,
                'description' => 'Template de prueba',
                'active' => true,
            ];
            $template = $template ? $templates->patchEntity($template, $data) : $templates->newEntity($data);
            $templates->saveOrFail($template);
        }
    }

    private function ensureTheme(): int
    {
        $themes = $this->table('Themes');
        $theme = $themes->find()->where(['slug' => 'catops-naranja'])->first();
        $data = [
            'name' => 'CatOps naranja',
            'slug' => 'catops-naranja',
            'primary_color' => '#f36b16',
            'secondary_color' => '#0a2a66',
            'background_color' => '#fbfaf7',
            'font_family' => 'Inter, Arial, sans-serif',
            'active' => true,
        ];
        $theme = $theme ? $themes->patchEntity($theme, $data) : $themes->newEntity($data);
        $themes->saveOrFail($theme);

        return (int)$theme->id;
    }

    private function createUser(string $email): int
    {
        $users = $this->table('Users');
        $user = $users->newEntity([
            'name' => 'Cliente Catálogo',
            'email' => $email,
            'password' => 'secret123',
            'role' => 'customer',
            'active' => true,
            'email_verified' => true,
        ]);
        $users->saveOrFail($user);

        return (int)$user->id;
    }

    private function createActiveSubscription(int $userId, string $planSlug): void
    {
        $now = DateTime::now();
        $end = DateTime::now()->addDays(30);
        $subscriptions = $this->table('Subscriptions');
        $subscription = $subscriptions->newEntity([
            'user_id' => $userId,
            'plan_slug' => $planSlug,
            'status' => 'active',
            'starts_at' => $now,
            'ends_at' => $end,
            'notes' => 'Test subscription',
        ]);
        $subscriptions->saveOrFail($subscription);

        $this->table('Payments')->saveOrFail($this->table('Payments')->newEntity([
            'user_id' => $userId,
            'subscription_id' => $subscription->id,
            'plan_slug' => $planSlug,
            'status' => 'paid',
            'amount' => 16990,
            'currency' => 'CLP',
            'provider' => 'manual',
            'provider_reference' => 'test-' . $planSlug . '-' . $userId,
            'paid_at' => $now,
            'period_start' => $now,
            'period_end' => $end,
        ]));
    }

    private function createSite(int $userId, string $templateSlug, string $subdomain, string $name = 'Negocio Demo'): int
    {
        $templateId = (int)$this->table('Templates')->find()->where(['slug' => $templateSlug])->firstOrFail()->id;
        $site = $this->table('Sites')->newEntity([
            'user_id' => $userId,
            'template_id' => $templateId,
            'theme_id' => $this->themeId,
            'name' => $name,
            'slug' => $subdomain,
            'subdomain' => $subdomain,
            'status' => 'draft',
            'whatsapp_country_code' => '56',
            'whatsapp_number' => '912345678',
        ]);
        $this->table('Sites')->saveOrFail($site);
        $this->table('CatalogSettings')->saveOrFail($this->table('CatalogSettings')->newEntity([
            'site_id' => $site->id,
            'background_type' => 'color',
            'background_color' => '#fbfaf7',
            'title_color' => '#17202a',
            'heading_font' => 'Inter, Arial, sans-serif',
            'title_font' => 'Inter, Arial, sans-serif',
            'slogan_color' => '#17202a',
            'slogan_font' => 'Inter, Arial, sans-serif',
            'title' => $name,
            'slogan' => 'Catálogo de prueba',
        ]));

        return (int)$site->id;
    }

    private function loginAs(int $userId): void
    {
        $this->session([
            'Auth.User' => [
                'id' => $userId,
                'name' => 'Cliente Catálogo',
                'email' => 'catalogo@example.test',
                'role' => 'customer',
            ],
        ]);
    }

    private function getPublicVitrina(string $subdomain): void
    {
        $this->configRequest(['headers' => ['Host' => $subdomain . '.vitrinahub.local']]);
        $this->get('/');
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }

    private function uploadedFile(string $path, string $mime, string $clientFilename): UploadedFile
    {
        return new UploadedFile($path, filesize($path), UPLOAD_ERR_OK, $clientFilename, $mime);
    }

    private function writeTempFile(string $name, string $content): string
    {
        $path = TMP . uniqid('catops-upload-', true) . '-' . $name;
        file_put_contents($path, $content);

        return $path;
    }

    private function createStoredImage(string $relativePath): string
    {
        $path = WWW_ROOT . $relativePath;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $image = imagecreatetruecolor(8, 8);
        imagefilledrectangle($image, 0, 0, 8, 8, imagecolorallocate($image, 243, 107, 22));
        imagepng($image, $path);
        imagedestroy($image);

        return $relativePath;
    }
}
