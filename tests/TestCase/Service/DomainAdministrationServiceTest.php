<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\DnsTxtResolver;
use App\Service\DomainAdministrationService;
use App\Service\PublicSiteResolverService;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

class DomainAdministrationServiceTest extends TestCase
{
    private ?string $previousBaseDomain = null;
    private int $templateId;
    private int $themeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousBaseDomain = getenv('APP_BASE_DOMAIN') !== false ? (string)getenv('APP_BASE_DOMAIN') : null;
        putenv('APP_BASE_DOMAIN=catops.local');
        $this->templateId = $this->ensureTemplate();
        $this->themeId = $this->ensureTheme();
    }

    protected function tearDown(): void
    {
        $this->previousBaseDomain === null ? putenv('APP_BASE_DOMAIN') : putenv('APP_BASE_DOMAIN=' . $this->previousBaseDomain);
        parent::tearDown();
    }

    public function testRequestVerifyResolveAndRemoveCustomDomain(): void
    {
        $userId = $this->createUser('domain-owner-' . uniqid() . '@example.test');
        $planSlug = $this->createPlan('domains-' . uniqid(), true, 1);
        $this->createSubscription($userId, $planSlug);
        $site = $this->createSite($userId, 'dominio-' . uniqid(), 'Dominio propio');

        $resolver = new TestDnsTxtResolver();
        $service = new DomainAdministrationService(dnsTxtResolver: $resolver);
        $domain = $service->requestCustomDomain($site, $userId, 'WWW.Mi-Negocio.cl.');

        $this->assertSame('www.mi-negocio.cl', $domain->domain);
        $this->assertFalse((bool)$domain->verified);
        $this->assertFalse((bool)$domain->active);
        $this->assertMatchesRegularExpression('/^catops-verification=[a-f0-9]{40}$/', (string)$domain->verification_token);
        $this->assertSame('_catops-verify.www.mi-negocio.cl', $service->verificationRecordName($domain));

        $resolver->recordsByHostname[$service->verificationRecordName($domain)] = [(string)$domain->verification_token];
        $domain = $service->verifyCustomDomain($domain, $userId);
        $this->assertTrue((bool)$domain->verified);
        $this->assertTrue((bool)$domain->active);
        $this->assertNull($domain->last_dns_error);

        $site->status = 'published';
        $site->published_at = DateTime::now();
        $this->table('Sites')->saveOrFail($site);
        $result = (new PublicSiteResolverService())->resolveByHost('www.mi-negocio.cl');
        $this->assertSame((int)$site->id, (int)$result['site']->id);
        $this->assertNull($result['reason']);

        $service->removeCustomDomain($domain, $userId);
        $this->assertSame(0, $this->table('Domains')->find()->where(['id' => $domain->id])->count());
        $this->assertSame(PublicSiteResolverService::REASON_NOT_FOUND, (new PublicSiteResolverService())->resolveByHost('www.mi-negocio.cl')['reason']);
    }

    public function testCustomDomainRespectsPlanLimitAndOwnership(): void
    {
        $ownerId = $this->createUser('domain-limit-' . uniqid() . '@example.test');
        $otherUserId = $this->createUser('domain-other-' . uniqid() . '@example.test');
        $planSlug = $this->createPlan('one-domain-' . uniqid(), true, 1);
        $this->createSubscription($ownerId, $planSlug);
        $this->createSubscription($otherUserId, $planSlug);
        $ownerSite = $this->createSite($ownerId, 'owner-' . uniqid());
        $otherSite = $this->createSite($otherUserId, 'other-' . uniqid());
        $service = new DomainAdministrationService(dnsTxtResolver: new TestDnsTxtResolver());

        try {
            $service->requestCustomDomain($otherSite, $ownerId, 'ajeno-ejemplo.cl');
            $this->fail('La asignación a un sitio ajeno debe rechazarse.');
        } catch (InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        $service->requestCustomDomain($ownerSite, $ownerId, 'uno-ejemplo.cl');

        $this->expectException(InvalidArgumentException::class);
        $service->requestCustomDomain($ownerSite, $ownerId, 'dos-ejemplo.cl');
    }

    public function testUnverifiedOrIncorrectTxtNeverActivatesCustomDomain(): void
    {
        $userId = $this->createUser('domain-pending-' . uniqid() . '@example.test');
        $planSlug = $this->createPlan('pending-domain-' . uniqid(), true, 1);
        $this->createSubscription($userId, $planSlug);
        $site = $this->createSite($userId, 'pending-' . uniqid());
        $resolver = new TestDnsTxtResolver();
        $service = new DomainAdministrationService(dnsTxtResolver: $resolver);
        $domain = $service->requestCustomDomain($site, $userId, 'pendiente-ejemplo.cl');
        $resolver->recordsByHostname[$service->verificationRecordName($domain)] = ['catops-verification=incorrecto'];

        try {
            $service->verifyCustomDomain($domain, $userId);
            $this->fail('Un TXT que no coincide no debe activar el dominio.');
        } catch (InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        $stored = $this->table('Domains')->get($domain->id);
        $this->assertFalse((bool)$stored->verified);
        $this->assertFalse((bool)$stored->active);
        $this->assertNotEmpty($stored->last_dns_error);
        $this->assertFalse($service->isActiveVerifiedCustomHostname('pendiente-ejemplo.cl'));
    }

    private function createPlan(string $slug, bool $customDomainsEnabled, int $limit): string
    {
        $plan = $this->table('Plans')->newEntity([
            'name' => 'Plan dominios',
            'slug' => $slug,
            'monthly_price' => 9990,
            'max_sites' => 3,
            'max_published' => 3,
            'sort_order' => 90,
            'active' => true,
            'capabilities' => json_encode([
                'sites_configured_limit' => 3,
                'sites_published_limit' => 3,
                'items_limit' => 20,
                'categories_limit' => 5,
                'image_storage_limit_mb' => 100,
                'custom_domain_enabled' => $customDomainsEnabled,
                'custom_domains_limit' => $limit,
                'enabled_templates' => ['carta-simple'],
            ]),
        ]);
        $this->table('Plans')->saveOrFail($plan);

        return $slug;
    }

    private function createUser(string $email): int
    {
        $user = $this->table('Users')->newEntity([
            'name' => 'Cliente dominio',
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

    private function createSite(int $userId, string $subdomain, string $name = 'Sitio dominio'): object
    {
        $site = $this->table('Sites')->newEntity([
            'user_id' => $userId,
            'template_id' => $this->templateId,
            'theme_id' => $this->themeId,
            'name' => $name,
            'slug' => $subdomain,
            'subdomain' => $subdomain,
            'status' => 'draft',
        ]);
        $this->table('Sites')->saveOrFail($site);

        return $site;
    }

    private function ensureTemplate(): int
    {
        $template = $this->table('Templates')->find()->where(['slug' => 'carta-simple'])->first();
        if (!$template) {
            $template = $this->table('Templates')->newEntity(['name' => 'Carta', 'slug' => 'carta-simple', 'active' => true]);
            $this->table('Templates')->saveOrFail($template);
        }

        return (int)$template->id;
    }

    private function ensureTheme(): int
    {
        $theme = $this->table('Themes')->find()->where(['slug' => 'catops-naranja'])->first();
        if (!$theme) {
            $theme = $this->table('Themes')->newEntity([
                'name' => 'CatOps naranja',
                'slug' => 'catops-naranja',
                'primary_color' => '#f36b16',
                'secondary_color' => '#0a2a66',
                'background_color' => '#fbfaf7',
                'font_family' => 'Arial, sans-serif',
                'active' => true,
            ]);
            $this->table('Themes')->saveOrFail($theme);
        }

        return (int)$theme->id;
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}

class TestDnsTxtResolver extends DnsTxtResolver
{
    /** @var array<string, list<string>> */
    public array $recordsByHostname = [];

    public function records(string $hostname): array
    {
        return $this->recordsByHostname[$hostname] ?? [];
    }
}
