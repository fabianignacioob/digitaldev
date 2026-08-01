<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\PlanService;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

class PlanServiceTest extends TestCase
{
    private PlanService $service;
    private int $templateId;
    private int $themeId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PlanService();
        $this->templateId = $this->ensureTemplate();
        $this->themeId = $this->ensureTheme();
    }

    public function testConfiguredAndPublishedLimitsOneThreeAndFive(): void
    {
        foreach ([1, 3, 5] as $limit) {
            $slug = 'limit-' . $limit . '-' . uniqid();
            $this->savePlan($slug, $limit, $limit);
            $userId = $this->createUser('limit-' . $limit . '-' . uniqid() . '@example.test');
            $this->createSubscription($userId, $slug);
            for ($position = 0; $position < $limit - 1; $position++) {
                $this->createSite($userId, 'draft');
            }

            $this->assertTrue($this->service->canCreateSite($userId));
            $this->createSite($userId, 'draft');
            $this->assertFalse($this->service->canCreateSite($userId));

            $draftSites = $this->table('Sites')->find()->where(['user_id' => $userId])->all()->toList();
            foreach ($draftSites as $site) {
                $this->assertTrue($this->service->canPublishSite($userId, $site));
                $site->status = 'published';
                $this->table('Sites')->saveOrFail($site);
            }
            $extraDraft = $this->createSiteDirect($userId, 'draft');
            $this->assertFalse($this->service->canPublishSite($userId, $extraDraft));
        }
    }

    public function testInheritedOverLimitKeepsPublishedSitesButBlocksNewActions(): void
    {
        $slug = 'heredado-' . uniqid();
        $this->savePlan($slug, 1, 1);
        $userId = $this->createUser('heredado-' . uniqid() . '@example.test');
        $this->createSubscription($userId, $slug);
        $published = $this->createSiteDirect($userId, 'published');
        $draft = $this->createSiteDirect($userId, 'draft');

        $usage = $this->service->siteUsage($userId);
        $this->assertTrue($usage['over_limit']);
        $this->assertTrue($usage['configured_over_limit']);
        $this->assertFalse($this->service->canCreateSite($userId));
        $this->assertTrue($this->service->canPublishSite($userId, $published));
        $this->assertFalse($this->service->canPublishSite($userId, $draft));
        $this->assertSame('published', $this->table('Sites')->get($published->id)->status);
    }

    public function testMissingAndMalformedCapabilitiesUseSafeDefaults(): void
    {
        $plan = (object)[
            'max_sites' => 3,
            'max_published' => 3,
            'capabilities' => [
                'categories_enabled' => 'yes',
                'featured_items_enabled' => 'true',
                'analytics_level' => 'unsafe',
                'items_limit' => '500',
            ],
        ];

        $capabilities = $this->service->capabilities($plan);
        $this->assertSame(3, $capabilities['sites_configured_limit']);
        $this->assertSame(3, $capabilities['sites_published_limit']);
        $this->assertSame(0, $capabilities['items_limit']);
        $this->assertFalse($capabilities['categories_enabled']);
        $this->assertFalse($capabilities['featured_items_enabled']);
        $this->assertSame('none', $capabilities['analytics_level']);
        $this->assertFalse($this->service->hasFeature($this->createUser('sin-capacidad-' . uniqid() . '@example.test'), 'qr_enabled'));
    }

    public function testCapabilityInputValidationAndCommercialRowsMarkFutureModules(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->validateCapabilityInput([
            'sites_configured_limit' => '1.5',
            'sites_published_limit' => '1',
            'items_limit' => '40',
            'categories_limit' => '0',
            'image_storage_limit_mb' => '100',
            'categories_enabled' => false,
            'featured_items_enabled' => false,
            'qr_enabled' => false,
            'custom_domain_enabled' => false,
            'premium_themes_enabled' => false,
            'catops_branding_removable' => false,
            'priority_support' => false,
            'customization_level' => 'basic',
            'analytics_level' => 'none',
            'seo_level' => 'basic',
            'enabled_templates' => [],
        ]);
    }

    public function testCommercialRowsMatchTheFullPlanMarketingCopy(): void
    {
        $plan = (object)[
            'max_sites' => 5,
            'max_published' => 5,
            'capabilities' => [
                'sites_configured_limit' => 5,
                'sites_published_limit' => 5,
                'items_limit' => 500,
                'categories_limit' => 50,
                'image_storage_limit_mb' => 2000,
                'categories_enabled' => true,
                'featured_items_enabled' => true,
                'customization_level' => 'advanced',
                'analytics_level' => 'advanced',
                'seo_level' => 'advanced',
                'qr_enabled' => true,
                'custom_domain_enabled' => false,
                'premium_themes_enabled' => true,
                'catops_branding_removable' => true,
                'priority_support' => true,
                'enabled_templates' => ['carta-simple', 'catalogo-simple'],
            ],
        ];

        $rows = $this->service->commercialBenefitRows($plan);
        $byLabel = [];
        foreach ($rows as $row) {
            $byLabel[$row['label']] = $row;
        }
        $this->assertSame('Estadística avanzada + Analytics', $byLabel['Estadísticas']['copy']);
        $this->assertSame('available', $byLabel['Código QR']['status']);
        $this->assertSame('SEO avanzado', $byLabel['SEO']['copy']);
        $this->assertSame('Personalización avanzada por bloques', $byLabel['Personalización']['copy']);
        $this->assertSame('Conexión de dominios propios', $byLabel['Dominio propio']['copy']);
    }

    public function testCommercialRowsPresentEnabledCustomDomainsAsAvailable(): void
    {
        $plan = (object)[
            'capabilities' => [
                'sites_configured_limit' => 3,
                'sites_published_limit' => 3,
                'custom_domain_enabled' => true,
                'custom_domains_limit' => 1,
                'enabled_templates' => ['carta-simple'],
            ],
        ];

        $rows = $this->service->commercialBenefitRows($plan);
        $byLabel = [];
        foreach ($rows as $row) {
            $byLabel[$row['label']] = $row;
        }
        $this->assertSame('Conexión de dominio propio', $byLabel['Dominio propio']['copy']);
        $this->assertSame('available', $byLabel['Dominio propio']['status']);
    }

    public function testTrialAndAnnualCapabilitiesAreValidatedWithSafeDefaults(): void
    {
        $plan = (object)[
            'annual_price' => 119900,
            'annual_benefits' => ['domain_credit' => true],
            'capabilities' => [
                'trial_enabled' => true,
                'trial_duration_days' => 7,
                'trial_expire_after_registration_days' => 14,
                'custom_domains_limit' => 1,
                'domain_credit' => true,
                'annual_available' => true,
                'annual_price' => 119900,
                'branding_removable' => false,
            ],
        ];

        $capabilities = $this->service->capabilities($plan);
        $this->assertTrue($capabilities['trial_enabled']);
        $this->assertSame(7, $capabilities['trial_duration_days']);
        $this->assertSame(14, $capabilities['trial_expire_after_registration_days']);
        $this->assertSame(1, $capabilities['custom_domains_limit']);
        $this->assertSame(119900, $this->service->annualPrice($plan));
        $this->assertTrue($this->service->annualBenefits($plan)['domain_credit']);
        $this->assertTrue($this->service->isTrialPlan($plan));
    }

    private function savePlan(string $slug, int $configuredLimit, int $publishedLimit): void
    {
        $plan = $this->table('Plans')->newEntity([
            'name' => 'Plan ' . $slug,
            'slug' => $slug,
            'monthly_price' => 6990,
            'max_sites' => $configuredLimit,
            'max_published' => $publishedLimit,
            'sort_order' => 50,
            'active' => true,
            'capabilities' => json_encode([
                'sites_configured_limit' => $configuredLimit,
                'sites_published_limit' => $publishedLimit,
                'items_limit' => 40,
                'categories_limit' => 0,
                'image_storage_limit_mb' => 100,
                'categories_enabled' => false,
                'featured_items_enabled' => false,
                'customization_level' => 'basic',
                'analytics_level' => 'none',
                'seo_level' => 'basic',
                'qr_enabled' => false,
                'custom_domain_enabled' => false,
                'premium_themes_enabled' => false,
                'catops_branding_removable' => false,
                'priority_support' => false,
                'enabled_templates' => ['carta-simple', 'catalogo-simple'],
            ]),
        ]);
        $this->table('Plans')->saveOrFail($plan);
    }

    private function createUser(string $email): int
    {
        $user = $this->table('Users')->newEntity([
            'name' => 'Cliente Plan',
            'email' => $email,
            'password' => 'secret123',
            'role' => 'customer',
            'active' => true,
            'email_verified' => true,
        ]);
        $this->table('Users')->saveOrFail($user);

        return (int)$user->id;
    }

    private function createSubscription(int $userId, string $planSlug): void
    {
        $now = DateTime::now();
        $subscription = $this->table('Subscriptions')->newEntity([
            'user_id' => $userId,
            'plan_slug' => $planSlug,
            'status' => 'active',
            'starts_at' => $now,
            'ends_at' => $now->addDays(30),
        ]);
        $this->table('Subscriptions')->saveOrFail($subscription);
    }

    private function createSite(int $userId, string $status): void
    {
        $this->createSiteDirect($userId, $status);
    }

    private function createSiteDirect(int $userId, string $status): object
    {
        $suffix = uniqid();
        $site = $this->table('Sites')->newEntity([
            'user_id' => $userId,
            'template_id' => $this->templateId,
            'theme_id' => $this->themeId,
            'name' => 'Sitio ' . $suffix,
            'slug' => 'sitio-' . $suffix,
            'subdomain' => 'sitio-' . $suffix,
            'status' => $status,
            'whatsapp_country_code' => '56',
            'whatsapp_number' => '912345678',
        ]);
        $this->table('Sites')->saveOrFail($site);

        return $site;
    }

    private function ensureTemplate(): int
    {
        $table = $this->table('Templates');
        $template = $table->find()->where(['slug' => 'carta-simple'])->first();
        if (!$template) {
            $template = $table->newEntity([
                'name' => 'Carta simple',
                'slug' => 'carta-simple',
                'description' => 'Template de prueba',
                'active' => true,
            ]);
            $table->saveOrFail($template);
        }

        return (int)$template->id;
    }

    private function ensureTheme(): int
    {
        $table = $this->table('Themes');
        $theme = $table->find()->where(['slug' => 'plan-test-theme'])->first();
        if (!$theme) {
            $theme = $table->newEntity([
                'name' => 'Tema de planes',
                'slug' => 'plan-test-theme',
                'primary_color' => '#f36b16',
                'secondary_color' => '#0a2a66',
                'background_color' => '#fbfaf7',
                'font_family' => 'Arial, sans-serif',
                'active' => true,
            ]);
            $table->saveOrFail($theme);
        }

        return (int)$theme->id;
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
