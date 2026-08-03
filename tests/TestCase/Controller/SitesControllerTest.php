<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Double\FakeEmailService;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class SitesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    private ?string $previousBaseDomain = null;
    private ?string $previousPlatformDomain = null;
    private ?string $previousPublicBaseDomain = null;
    private ?string $previousPublicScheme = null;
    private int $userId;
    private int $templateId;
    private int $themeId;
    private FakeEmailService $emailService;
    private mixed $previousEmailService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousBaseDomain = getenv('APP_BASE_DOMAIN') !== false ? (string)getenv('APP_BASE_DOMAIN') : null;
        $this->previousPlatformDomain = getenv('APP_PLATFORM_DOMAIN') !== false ? (string)getenv('APP_PLATFORM_DOMAIN') : null;
        $this->previousPublicBaseDomain = getenv('APP_PUBLIC_BASE_DOMAIN') !== false ? (string)getenv('APP_PUBLIC_BASE_DOMAIN') : null;
        $this->previousPublicScheme = getenv('APP_PUBLIC_SCHEME') !== false ? (string)getenv('APP_PUBLIC_SCHEME') : null;
        $this->previousEmailService = Configure::read('EmailService');
        $this->emailService = new FakeEmailService();
        Configure::write('EmailService', $this->emailService);
        putenv('APP_BASE_DOMAIN');
        putenv('APP_PLATFORM_DOMAIN=catops.local');
        putenv('APP_PUBLIC_BASE_DOMAIN=vitrinahub.local');
        putenv('APP_PUBLIC_SCHEME=http');

        $this->ensurePlan();
        $this->templateId = $this->ensureTemplate();
        $this->themeId = $this->ensureTheme();
        $this->userId = $this->createUser('cliente-' . uniqid() . '@example.test');
        $this->createActiveSubscription($this->userId);
    }

    protected function tearDown(): void
    {
        $this->previousBaseDomain === null ? putenv('APP_BASE_DOMAIN') : putenv('APP_BASE_DOMAIN=' . $this->previousBaseDomain);
        $this->previousPlatformDomain === null ? putenv('APP_PLATFORM_DOMAIN') : putenv('APP_PLATFORM_DOMAIN=' . $this->previousPlatformDomain);
        $this->previousPublicBaseDomain === null ? putenv('APP_PUBLIC_BASE_DOMAIN') : putenv('APP_PUBLIC_BASE_DOMAIN=' . $this->previousPublicBaseDomain);
        $this->previousPublicScheme === null ? putenv('APP_PUBLIC_SCHEME') : putenv('APP_PUBLIC_SCHEME=' . $this->previousPublicScheme);
        $this->previousEmailService === null
            ? Configure::delete('EmailService')
            : Configure::write('EmailService', $this->previousEmailService);

        parent::tearDown();
    }

    public function testCreateSite(): void
    {
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/nuevo', [
            'name' => 'Café Alpha',
            'subdomain' => 'cafe-alpha',
            'template_id' => $this->templateId,
            'theme_id' => $this->themeId,
            'whatsapp_country_code' => '56',
            'whatsapp_number' => '912345678',
            'seo_title' => 'Café Alpha',
            'seo_description' => 'Carta simple para Café Alpha.',
        ]);

        $this->assertRedirectContains('/sitios/editar/');

        $site = $this->table('Sites')->find()
            ->where(['subdomain' => 'cafe-alpha'])
            ->first();
        $this->assertNotEmpty($site);
        $this->assertSame('draft', $site->status);
        $domain = $this->table('Domains')->find()->where(['site_id' => $site->id, 'type' => 'subdomain'])->first();
        $this->assertNotEmpty($domain);
        $this->assertSame('cafe-alpha.vitrinahub.local', $domain->domain);
    }

    public function testDuplicateSubdomainIsRejected(): void
    {
        $otherUserId = $this->createUser('dueno-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId);
        $this->createSite($otherUserId, 'duplicado');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/nuevo', [
            'name' => 'Duplicado',
            'subdomain' => 'duplicado',
            'template_id' => $this->templateId,
            'theme_id' => $this->themeId,
            'whatsapp_country_code' => '56',
            'whatsapp_number' => '912345678',
        ]);

        $this->assertResponseOk();
        $this->assertSame(1, $this->table('Sites')->find()->where(['subdomain' => 'duplicado'])->count());
    }

    public function testReservedSubdomainIsRejected(): void
    {
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/nuevo', [
            'name' => 'Admin',
            'subdomain' => 'admin',
            'template_id' => $this->templateId,
            'theme_id' => $this->themeId,
            'whatsapp_country_code' => '56',
            'whatsapp_number' => '912345678',
        ]);

        $this->assertResponseOk();
        $this->assertSame(0, $this->table('Sites')->find()->where(['subdomain' => 'admin'])->count());
    }

    public function testPublishRequiresPost(): void
    {
        $siteId = $this->createSite($this->userId, 'sitio-get');
        $this->loginAs($this->userId);

        $this->get('/sitios/publicar/' . $siteId);

        $this->assertResponseCode(405);
    }

    public function testPublishSiteWithPost(): void
    {
        $siteId = $this->createSite($this->userId, 'sitio-post');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/publicar/' . $siteId);

        $this->assertRedirect('/sitios/editar/' . $siteId);
        $site = $this->table('Sites')->get($siteId);
        $this->assertSame('published', $site->status);
        $this->assertNotEmpty($site->published_at);
        $this->assertCount(1, $this->emailService->messages);
        $this->assertSame('site_published', $this->emailService->messages[0]['kind']);
        $this->assertSame('http://sitio-post.vitrinahub.local', $this->emailService->messages[0]['publicUrl']);
    }

    public function testCannotPublishAnotherUsersSite(): void
    {
        $otherUserId = $this->createUser('otro-publicar-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId);
        $siteId = $this->createSite($otherUserId, 'publicar-ajeno');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/publicar/' . $siteId);

        $this->assertResponseCode(404);
        $this->assertSame('draft', $this->table('Sites')->get($siteId)->status);
    }

    public function testPublicationLimitByPlan(): void
    {
        $firstSiteId = $this->createSite($this->userId, 'limite-uno');
        $secondSiteId = $this->createSite($this->userId, 'limite-dos');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/publicar/' . $firstSiteId);
        $this->post('/sitios/publicar/' . $secondSiteId);

        $this->assertSame('draft', $this->table('Sites')->get($firstSiteId)->status);
        $this->assertSame('draft', $this->table('Sites')->get($secondSiteId)->status);
    }

    public function testUnknownHostIsRejected(): void
    {
        $this->configRequest(['headers' => ['Host' => 'intruso.test']]);

        $this->get('/');

        $this->assertResponseCode(404);
    }

    public function testPlatformHostDisplaysTheProductHomeAndActivePlans(): void
    {
        $plan = $this->table('Plans')->find()
            ->where(['active' => true])
            ->orderByAsc('sort_order')
            ->firstOrFail();

        $this->configRequest(['headers' => ['Host' => 'catops.local']]);
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Crea la');
        $this->assertResponseContains($plan->name);
        $this->assertResponseContains(number_format((int)$plan->monthly_price, 0, ',', '.'));
    }

    public function testPublishedVitrinaIsAccessibleByPublicHostAndLegacyUrlsRedirect(): void
    {
        $siteId = $this->createSite($this->userId, 'pizzeria', 'Pizzería Demo');
        $this->publishSiteDirectly($siteId);

        $this->configRequest(['headers' => ['Host' => 'pizzeria.vitrinahub.local']]);
        $this->get('/');
        $this->assertResponseOk();
        $this->assertResponseContains('Pizzería Demo');
        $this->assertResponseContains('<link rel="canonical" href="http://pizzeria.vitrinahub.local">');

        $this->configRequest(['headers' => ['Host' => 'catops.local']]);
        $this->get('/s/pizzeria');
        $this->assertResponseCode(301);
        $this->assertHeaderContains('Location', 'http://pizzeria.vitrinahub.local');

        $this->configRequest(['headers' => ['Host' => 'pizzeria.catops.local']]);
        $this->get('/');
        $this->assertResponseCode(301);
        $this->assertHeaderContains('Location', 'http://pizzeria.vitrinahub.local');
    }

    public function testEligibleUserCanDownloadPublishedSiteQr(): void
    {
        $this->enableQrFeature();
        $siteId = $this->createSite($this->userId, 'qr-demo');
        $this->publishSiteDirectly($siteId);
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/qr/generar');

        $this->assertRedirect('/sitios/editar/' . $siteId);
        $qrCode = $this->table('SiteQrCodes')->find()->where(['site_id' => $siteId])->firstOrFail();
        $this->assertMatchesRegularExpression('/^[a-z0-9]{32}$/', (string)$qrCode->public_token);

        $this->get('/sitios/' . $siteId . '/qr?format=svg&download=1');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'image/svg+xml');
        $this->assertHeaderContains('Content-Disposition', 'catops-qr-demo-qr.svg');
        $this->assertResponseContains('<svg');
    }

    public function testGeneratingQrAgainKeepsTheSamePermanentToken(): void
    {
        $this->enableQrFeature();
        $siteId = $this->createSite($this->userId, 'qr-permanente');
        $this->publishSiteDirectly($siteId);
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/qr/generar');
        $first = $this->table('SiteQrCodes')->find()->where(['site_id' => $siteId])->firstOrFail();

        $this->post('/sitios/' . $siteId . '/qr/generar');
        $second = $this->table('SiteQrCodes')->find()->where(['site_id' => $siteId])->firstOrFail();

        $this->assertSame((string)$first->public_token, (string)$second->public_token);
        $this->assertSame(1, $this->table('SiteQrCodes')->find()->where(['site_id' => $siteId])->count());
    }

    public function testQrStyleBelongsToTheOwnerAndCanBeUpdated(): void
    {
        $this->enableQrFeature();
        $siteId = $this->createSite($this->userId, 'qr-estilo');
        $this->publishSiteDirectly($siteId);
        $this->createQrCode($siteId);
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->put('/sitios/' . $siteId . '/qr/estilo', ['frame_style' => 'rounded']);

        $this->assertRedirect('/sitios/editar/' . $siteId);
        $this->assertSame('rounded', (string)$this->table('SiteQrCodes')->find()->where(['site_id' => $siteId])->firstOrFail()->frame_style);
    }

    public function testPublicQrRedirectsToTheCurrentSiteUrl(): void
    {
        $siteId = $this->createSite($this->userId, 'qr-publico');
        $this->publishSiteDirectly($siteId);
        $qrCode = $this->createQrCode($siteId);
        $this->configRequest(['headers' => ['Host' => 'catops.local']]);

        $this->get('/q/' . $qrCode->public_token);

        $this->assertResponseCode(302);
        $this->assertHeaderContains('Location', 'http://qr-publico.vitrinahub.local');
    }

    public function testQrRequiresPublishedSite(): void
    {
        $this->enableQrFeature();
        $siteId = $this->createSite($this->userId, 'qr-borrador');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/qr/generar');

        $this->assertResponseCode(400);
    }

    public function testLegacyPublicPathWorksWithProductionHostValidation(): void
    {
        $siteId = $this->createSite($this->userId, 'prueba', 'Sitio de prueba');
        $this->publishSiteDirectly($siteId);
        $previousDebug = Configure::read('debug');
        $previousFullBaseUrl = Configure::read('App.fullBaseUrl');
        $previousBaseDomain = getenv('APP_BASE_DOMAIN') !== false ? (string)getenv('APP_BASE_DOMAIN') : null;
        $previousPlatformDomain = getenv('APP_PLATFORM_DOMAIN') !== false ? (string)getenv('APP_PLATFORM_DOMAIN') : null;
        $previousPublicBaseDomain = getenv('APP_PUBLIC_BASE_DOMAIN') !== false ? (string)getenv('APP_PUBLIC_BASE_DOMAIN') : null;
        Configure::write('debug', false);
        Configure::write('App.fullBaseUrl', 'https://staging.catops.cl');
        putenv('APP_BASE_DOMAIN');
        putenv('APP_PLATFORM_DOMAIN=staging.catops.cl');
        putenv('APP_PUBLIC_BASE_DOMAIN=vitrinahub.local');

        try {
            $this->configRequest(['headers' => ['Host' => 'staging.catops.cl']]);
            $this->get('/s/prueba');
            $this->assertResponseCode(301);
            $this->assertHeaderContains('Location', 'http://prueba.vitrinahub.local');
        } finally {
            Configure::write('debug', $previousDebug);
            Configure::write('App.fullBaseUrl', $previousFullBaseUrl);
            $previousBaseDomain === null ? putenv('APP_BASE_DOMAIN') : putenv('APP_BASE_DOMAIN=' . $previousBaseDomain);
            $previousPlatformDomain === null ? putenv('APP_PLATFORM_DOMAIN') : putenv('APP_PLATFORM_DOMAIN=' . $previousPlatformDomain);
            $previousPublicBaseDomain === null ? putenv('APP_PUBLIC_BASE_DOMAIN') : putenv('APP_PUBLIC_BASE_DOMAIN=' . $previousPublicBaseDomain);
        }
    }

    public function testDraftSiteIsNotPublic(): void
    {
        $this->createSite($this->userId, 'borrador', 'Sitio Borrador');
        $this->configRequest(['headers' => ['Host' => 'borrador.vitrinahub.local']]);

        $this->get('/');

        $this->assertResponseCode(404);
        $this->assertResponseNotContains('Sitio Borrador');
    }

    public function testPausedSiteReturnsControlledMessage(): void
    {
        $siteId = $this->createSite($this->userId, 'pausado', 'Sitio Pausado');
        $site = $this->table('Sites')->get($siteId);
        $site->status = 'paused';
        $this->table('Sites')->saveOrFail($site);
        $this->configRequest(['headers' => ['Host' => 'pausado.vitrinahub.local']]);

        $this->get('/');

        $this->assertResponseCode(503);
        $this->assertResponseContains('pausada temporalmente');
    }

    public function testExpiredSubscriptionPublicAccessHasNoSideEffects(): void
    {
        $siteId = $this->createSite($this->userId, 'vencido', 'Sitio Vencido');
        $this->publishSiteDirectly($siteId);
        $subscription = $this->table('Subscriptions')->find()->where(['user_id' => $this->userId])->firstOrFail();
        $subscription->ends_at = DateTime::now()->subDays(1);
        $this->table('Subscriptions')->saveOrFail($subscription);
        $this->table('Payments')->updateAll([
            'period_end' => DateTime::now()->subDays(1),
        ], ['subscription_id' => $subscription->id]);
        $this->configRequest(['headers' => ['Host' => 'vencido.vitrinahub.local']]);

        $this->get('/');

        $this->assertResponseCode(503);
        $this->assertResponseContains('suscripción venció');
        $this->assertSame('published', $this->table('Sites')->get($siteId)->status);
    }

    public function testTenantIsolationByHost(): void
    {
        $otherUserId = $this->createUser('tenant-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId);
        $siteOneId = $this->createSite($this->userId, 'tenant-uno', 'Negocio Uno');
        $siteTwoId = $this->createSite($otherUserId, 'tenant-dos', 'Negocio Dos');
        $this->publishSiteDirectly($siteOneId);
        $this->publishSiteDirectly($siteTwoId);
        $this->configRequest(['headers' => ['Host' => 'tenant-uno.vitrinahub.local']]);

        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Negocio Uno');
        $this->assertResponseNotContains('Negocio Dos');
    }

    public function testCannotEditAnotherUsersSite(): void
    {
        $otherUserId = $this->createUser('otro-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId);
        $siteId = $this->createSite($otherUserId, 'sitio-ajeno');
        $this->loginAs($this->userId);

        $this->get('/sitios/editar/' . $siteId);

        $this->assertResponseCode(404);
    }

    public function testCustomerCanRequestCustomDomainForOwnSiteWhenPlanAllowsIt(): void
    {
        $plan = $this->table('Plans')->find()->where(['slug' => 'basica'])->firstOrFail();
        $capabilities = json_decode((string)$plan->capabilities, true) ?: [];
        $capabilities['custom_domain_enabled'] = true;
        $capabilities['custom_domains_limit'] = 1;
        $plan->capabilities = json_encode($capabilities);
        $this->table('Plans')->saveOrFail($plan);
        $siteId = $this->createSite($this->userId, 'dominio-cliente');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/dominios', ['domain' => 'mi-negocio.cl']);

        $this->assertRedirect('/sitios/editar/' . $siteId);
        $domain = $this->table('Domains')->find()
            ->where(['site_id' => $siteId, 'domain' => 'mi-negocio.cl', 'type' => 'custom'])
            ->first();
        $this->assertNotEmpty($domain);
        $this->assertFalse((bool)$domain->active);
        $this->assertFalse((bool)$domain->verified);
    }

    public function testCustomerCannotManageAnotherUsersCustomDomain(): void
    {
        $otherUserId = $this->createUser('dominio-ajeno-' . uniqid() . '@example.test');
        $this->createActiveSubscription($otherUserId);
        $siteId = $this->createSite($otherUserId, 'dominio-ajeno');
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/sitios/' . $siteId . '/dominios', ['domain' => 'ajeno.cl']);

        $this->assertResponseCode(404);
        $this->assertSame(0, $this->table('Domains')->find()->where(['domain' => 'ajeno.cl'])->count());
    }

    public function testVerifiedCustomDomainResolvesWithProductionHostValidation(): void
    {
        $siteId = $this->createSite($this->userId, 'dominio-publico', 'Sitio con dominio');
        $this->publishSiteDirectly($siteId);
        $this->table('Domains')->saveOrFail($this->table('Domains')->newEntity([
            'site_id' => $siteId,
            'domain' => 'cliente-ejemplo.cl',
            'type' => 'custom',
            'verified' => true,
            'active' => true,
            'status' => 'active',
        ]));

        $previousDebug = Configure::read('debug');
        $previousFullBaseUrl = Configure::read('App.fullBaseUrl');
        Configure::write('debug', false);
        Configure::write('App.fullBaseUrl', 'https://catops.local');
        try {
            $this->configRequest(['headers' => ['Host' => 'cliente-ejemplo.cl']]);
            $this->get('/');

            $this->assertResponseOk();
            $this->assertResponseContains('Sitio con dominio');
        } finally {
            Configure::write('debug', $previousDebug);
            Configure::write('App.fullBaseUrl', $previousFullBaseUrl);
        }
    }

    private function createUser(string $email): int
    {
        $users = $this->table('Users');
        $user = $users->newEntity([
            'name' => 'Cliente Test',
            'email' => $email,
            'password' => 'secret123',
            'role' => 'customer',
            'active' => true,
            'email_verified' => true,
        ]);
        $users->saveOrFail($user);

        return (int)$user->id;
    }

    private function ensurePlan(): void
    {
        $plans = $this->table('Plans');
        if ($plans->find()->where(['slug' => 'basica'])->count() > 0) {
            return;
        }

        $plans->saveOrFail($plans->newEntity([
            'name' => 'Básico',
            'slug' => 'basica',
            'monthly_price' => 6990,
            'max_sites' => 1,
            'max_published' => 1,
            'sort_order' => 1,
            'active' => true,
            'capabilities' => json_encode([
                'sites_configured_limit' => 1,
                'sites_published_limit' => 1,
                'items_limit' => 40,
                'categories_enabled' => false,
                'enabled_templates' => ['carta-simple', 'catalogo-simple'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]));
    }

    private function ensureTemplate(): int
    {
        $templates = $this->table('Templates');
        $template = $templates->find()->where(['slug' => 'carta-simple'])->first();
        if ($template) {
            $template->active = true;
            $templates->saveOrFail($template);

            return (int)$template->id;
        }

        $template = $templates->newEntity([
            'name' => 'Carta simple',
            'slug' => 'carta-simple',
            'description' => 'Carta de prueba',
            'active' => true,
        ]);
        $templates->saveOrFail($template);

        return (int)$template->id;
    }

    private function ensureTheme(): int
    {
        $themes = $this->table('Themes');
        $theme = $themes->find()->where(['slug' => 'catops-naranja'])->first();
        if ($theme) {
            $theme->active = true;
            $themes->saveOrFail($theme);

            return (int)$theme->id;
        }

        $theme = $themes->newEntity([
            'name' => 'CatOps naranja',
            'slug' => 'catops-naranja',
            'primary_color' => '#f36b16',
            'secondary_color' => '#0a2a66',
            'background_color' => '#fbfaf7',
            'font_family' => 'Inter, Arial, sans-serif',
            'active' => true,
        ]);
        $themes->saveOrFail($theme);

        return (int)$theme->id;
    }

    private function createActiveSubscription(int $userId): void
    {
        $now = DateTime::now();
        $end = DateTime::now()->addDays(30);
        $subscriptions = $this->table('Subscriptions');
        $subscription = $subscriptions->newEntity([
            'user_id' => $userId,
            'plan_slug' => 'basica',
            'status' => 'active',
            'starts_at' => $now,
            'ends_at' => $end,
            'notes' => 'Test subscription',
        ]);
        $subscriptions->saveOrFail($subscription);

        $payments = $this->table('Payments');
        $payments->saveOrFail($payments->newEntity([
            'user_id' => $userId,
            'subscription_id' => $subscription->id,
            'plan_slug' => 'basica',
            'status' => 'paid',
            'amount' => 6990,
            'currency' => 'CLP',
            'provider' => 'manual',
            'provider_reference' => 'test-' . $userId,
            'paid_at' => $now,
            'period_start' => $now,
            'period_end' => $end,
        ]));
    }

    private function createSite(int $userId, string $subdomain, string $name = 'Sitio Test'): int
    {
        $sites = $this->table('Sites');
        $site = $sites->newEntity([
            'user_id' => $userId,
            'template_id' => $this->templateId,
            'theme_id' => $this->themeId,
            'name' => $name,
            'slug' => $subdomain,
            'subdomain' => $subdomain,
            'status' => 'draft',
            'whatsapp_country_code' => '56',
            'whatsapp_number' => '912345678',
        ]);
        $sites->saveOrFail($site);

        return (int)$site->id;
    }

    private function publishSiteDirectly(int $siteId): void
    {
        $site = $this->table('Sites')->get($siteId);
        $site->status = 'published';
        $site->published_at = DateTime::now();
        $this->table('Sites')->saveOrFail($site);
    }

    private function enableQrFeature(): void
    {
        $plans = $this->table('Plans');
        $plan = $plans->find()->where(['slug' => 'basica'])->firstOrFail();
        $capabilities = json_decode((string)$plan->capabilities, true) ?: [];
        $capabilities['qr_enabled'] = true;
        $plan->capabilities = json_encode($capabilities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $plans->saveOrFail($plan);
    }

    private function createQrCode(int $siteId): object
    {
        $qrCodes = $this->table('SiteQrCodes');
        $qrCode = $qrCodes->newEntity([
            'site_id' => $siteId,
            'public_token' => bin2hex(random_bytes(16)),
            'frame_style' => 'square',
            'generated_at' => DateTime::now(),
        ]);
        $qrCodes->saveOrFail($qrCode);

        return $qrCode;
    }

    private function loginAs(int $userId): void
    {
        $this->session([
            'Auth.User' => [
                'id' => $userId,
                'name' => 'Cliente Test',
                'email' => 'cliente@example.test',
                'role' => 'customer',
            ],
        ]);
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
