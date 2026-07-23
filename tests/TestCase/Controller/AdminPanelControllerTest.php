<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Service\OperationalProcessRunService;
use App\Service\SystemStatusService;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class AdminPanelControllerTest extends TestCase
{
    use IntegrationTestTrait;

    private int $adminId;
    private int $superadminId;
    private int $userId;
    private int $planId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->planId = $this->ensurePlan();
        $this->adminId = $this->createUser('admin');
        $this->superadminId = $this->createUser('superadmin');
        $this->userId = $this->createUser('user');
    }

    public function testRegularUserCannotAccessAdminRoutes(): void
    {
        $this->loginAs($this->userId);
        $this->get('/admin');

        $this->assertResponseCode(403);
    }

    public function testAdminCanAccessDashboardAndUsers(): void
    {
        $this->loginAs($this->adminId);
        $this->get('/admin');
        $this->assertResponseOk();
        $this->assertResponseContains('Resumen de plataforma');

        $this->get('/admin/users');
        $this->assertResponseOk();
        $this->assertResponseContains('Usuarios');
    }

    public function testAdminCanOpenEveryReadOnlyModule(): void
    {
        $this->loginAs($this->adminId);
        foreach (['/admin/sites', '/admin/subscriptions', '/admin/payments', '/admin/domains', '/admin/plans', '/admin/audit-logs', '/admin/system-status'] as $url) {
            $this->get($url);
            $this->assertResponseOk($url);
        }
    }

    public function testRegularUserCannotAccessDomainsOrSystemStatus(): void
    {
        $this->loginAs($this->userId);
        $this->get('/admin/domains');
        $this->assertResponseCode(403);

        $this->get('/admin/system-status');
        $this->assertResponseCode(403);
    }

    public function testAdminCanListFilterAndDeactivateDomainWithAudit(): void
    {
        $site = $this->createSite();
        $domain = $this->createDomain($site->id, 'admin-filter-' . uniqid() . '.catops.local');
        $this->loginAs($this->adminId);
        $this->get('/admin/domains?q=' . urlencode($domain->domain) . '&active=1&type=subdomain&verified=1');
        $this->assertResponseOk();
        $this->assertResponseContains($domain->domain);

        $this->enableCsrfToken();
        $this->post('/admin/domains/' . $domain->id . '/deactivate', ['reason' => 'Prueba de operación']);
        $this->assertRedirect('/admin/domains/' . $domain->id);
        $this->assertFalse((bool)$this->table('Domains')->get($domain->id)->active);
        $this->assertSame(1, $this->table('AuditLogs')->find()->where([
            'user_id' => $this->adminId,
            'action' => 'admin.domain.deactivated',
            'entity' => 'domains',
            'entity_id' => $domain->id,
        ])->count());
    }

    public function testDomainHostnameIsNormalizedAndDuplicateIsRejected(): void
    {
        $site = $this->createSite();
        $hostname = 'duplicate-' . uniqid() . '.example.test';
        $domain = $this->createDomain($site->id, strtoupper($hostname));
        $this->assertSame($hostname, $this->table('Domains')->get($domain->id)->domain);

        $duplicate = $this->table('Domains')->newEntity([
            'site_id' => $site->id,
            'domain' => $hostname,
            'type' => 'custom',
            'verified' => true,
            'active' => true,
        ]);
        $this->assertFalse($this->table('Domains')->save($duplicate));
        $this->assertNotEmpty($duplicate->getError('domain'));
    }

    public function testAdminCanCorrectCustomDomainAssociationAndReactivateVerifiedDomain(): void
    {
        $source = $this->createSite();
        $target = $this->createSite();
        $domain = $this->createDomain($source->id, 'custom-' . uniqid() . '.example.test', 'custom', true, false);
        $this->loginAs($this->adminId);
        $this->enableCsrfToken();
        $this->post('/admin/domains/' . $domain->id . '/reassign', [
            'site_id' => $target->id,
            'reason' => 'Corrección de asociación',
        ]);
        $this->assertRedirect('/admin/domains/' . $domain->id);
        $this->assertSame((int)$target->id, (int)$this->table('Domains')->get($domain->id)->site_id);

        $this->enableCsrfToken();
        $this->post('/admin/domains/' . $domain->id . '/reactivate', ['reason' => 'DNS validado']);
        $this->assertRedirect('/admin/domains/' . $domain->id);
        $this->assertTrue((bool)$this->table('Domains')->get($domain->id)->active);
        $this->assertSame(1, $this->table('AuditLogs')->find()->where([
            'action' => 'admin.domain.reassigned',
            'entity' => 'domains',
            'entity_id' => $domain->id,
        ])->count());
    }

    public function testSystemStatusShowsOperationalMetricsWithoutCredentials(): void
    {
        $run = (new OperationalProcessRunService())->start('subscriptions.reminders', ['dry_run' => true]);
        (new OperationalProcessRunService())->finish($run, 'success', 4, 0, 0, 'Recordatorios detectados=4.');
        $this->createPendingPayment();
        putenv('WEBPAY_API_KEY=should-never-appear');
        putenv('DB_PASSWORD=should-never-appear');

        $snapshot = (new SystemStatusService())->snapshot();
        $this->assertTrue($snapshot['database']['connected']);
        $this->assertGreaterThanOrEqual(1, $snapshot['metrics']['payments_pending']);
        $this->assertNotNull($snapshot['processes']['subscriptions.reminders']);

        $this->loginAs($this->adminId);
        $this->get('/admin/system-status');
        $this->assertResponseOk();
        $this->assertResponseContains('Estado operativo');
        $this->assertResponseContains('subscriptions.reminders');
        $this->assertResponseNotContains('should-never-appear');
        putenv('WEBPAY_API_KEY');
        putenv('DB_PASSWORD');
    }

    public function testAdminCanBlockUserAndActionIsAudited(): void
    {
        $this->loginAs($this->adminId);
        $this->enableCsrfToken();
        $this->post('/admin/users/' . $this->userId . '/access', ['reason' => 'Prueba de seguridad']);

        $this->assertRedirect('/admin/users/' . $this->userId);
        $this->assertFalse((bool)$this->table('Users')->get($this->userId)->active);
        $this->assertSame(1, $this->table('AuditLogs')->find()->where([
            'user_id' => $this->adminId,
            'action' => 'admin.user.blocked',
            'entity' => 'users',
            'entity_id' => $this->userId,
        ])->count());
    }

    public function testOnlySuperadminCanChangeRoles(): void
    {
        $this->loginAs($this->adminId);
        $this->enableCsrfToken();
        $this->post('/admin/users/' . $this->userId . '/role', ['role' => 'admin', 'reason' => 'No permitido']);
        $this->assertResponseCode(403);
        $this->assertSame('user', $this->table('Users')->get($this->userId)->role);

        $this->loginAs($this->superadminId);
        $this->enableCsrfToken();
        $this->post('/admin/users/' . $this->userId . '/role', ['role' => 'admin', 'reason' => 'Promoción interna']);
        $this->assertRedirect('/admin/users/' . $this->userId);
        $this->assertSame('admin', $this->table('Users')->get($this->userId)->role);
    }

    public function testAdminCanPauseSiteWithReason(): void
    {
        $site = $this->createSite();
        $this->loginAs($this->adminId);
        $this->enableCsrfToken();
        $this->post('/admin/sites/' . $site->id . '/pause', ['reason' => 'Contenido en revisión']);

        $this->assertRedirect('/admin/sites/' . $site->id);
        $site = $this->table('Sites')->get($site->id);
        $this->assertSame('paused', $site->status);
        $this->assertSame('manual_admin', $site->paused_reason);
    }

    public function testAdminCanExtendSubscriptionThroughService(): void
    {
        $subscription = $this->createSubscription();
        $this->loginAs($this->adminId);
        $this->enableCsrfToken();
        $this->post('/admin/subscriptions/' . $subscription->id . '/extend', [
            'days' => 15,
            'reason' => 'Cortesía de pruebas',
        ]);

        $this->assertRedirect('/admin/subscriptions/' . $subscription->id);
        $subscription = $this->table('Subscriptions')->get($subscription->id);
        $this->assertSame('active', $subscription->status);
        $this->assertNotEmpty($subscription->ends_at);
        $this->assertSame(1, $this->table('Payments')->find()->where([
            'subscription_id' => $subscription->id,
            'provider' => 'manual',
            'status' => 'paid',
        ])->count());
    }

    public function testPlanEditingRequiresSuperadminAndAuditsChange(): void
    {
        $this->loginAs($this->adminId);
        $this->get('/admin/plans/' . $this->planId);
        $this->assertResponseCode(403);

        $this->loginAs($this->superadminId);
        $this->enableCsrfToken();
        $this->post('/admin/plans/' . $this->planId, [
            'name' => 'Básico revisado',
            'monthly_price' => 7990,
            'max_sites' => 1,
            'max_published' => 1,
            'sort_order' => 1,
            'active' => '1',
            'capabilities' => [
                'sites_configured_limit' => 1,
                'sites_published_limit' => 1,
                'items_limit' => 40,
                'enabled_templates' => [],
            ],
            'reason' => 'Ajuste de prueba',
        ]);

        $this->assertRedirect('/admin/plans/' . $this->planId);
        $this->assertSame(7990, (int)$this->table('Plans')->get($this->planId)->monthly_price);
        $this->assertSame(1, $this->table('AuditLogs')->find()->where([
            'user_id' => $this->superadminId,
            'action' => 'admin.plan.updated',
            'entity' => 'plans',
            'entity_id' => $this->planId,
        ])->count());
    }

    private function createUser(string $role): int
    {
        $user = $this->table('Users')->newEntity([
            'name' => ucfirst($role) . ' Admin test',
            'email' => $role . '-admin-' . uniqid() . '@example.test',
            'password' => 'secret123',
            'role' => $role,
            'active' => true,
            'email_verified' => true,
        ]);
        $this->table('Users')->saveOrFail($user);

        return (int)$user->id;
    }

    private function ensurePlan(): int
    {
        $plan = $this->table('Plans')->find()->where(['slug' => 'admin-test-' . date('Ymd')])->first();
        if (!$plan) {
            $plan = $this->table('Plans')->newEntity([
                'name' => 'Básico',
                'slug' => 'admin-test-' . date('Ymd'),
                'monthly_price' => 6990,
                'max_sites' => 1,
                'max_published' => 1,
                'sort_order' => 1,
                'active' => true,
                'capabilities' => '{}',
            ]);
            $this->table('Plans')->saveOrFail($plan);
        }

        return (int)$plan->id;
    }

    private function createSubscription(): object
    {
        $subscription = $this->table('Subscriptions')->newEntity([
            'user_id' => $this->userId,
            'plan_slug' => $this->table('Plans')->get($this->planId)->slug,
            'status' => 'expired',
            'starts_at' => DateTime::now()->subDays(30),
            'ends_at' => DateTime::now()->subDays(1),
        ]);
        $this->table('Subscriptions')->saveOrFail($subscription);

        return $subscription;
    }

    private function createSite(): object
    {
        $template = $this->table('Templates')->find()->first();
        if (!$template) {
            $template = $this->table('Templates')->newEntity(['name' => 'Carta', 'slug' => 'admin-template-' . uniqid(), 'active' => true]);
            $this->table('Templates')->saveOrFail($template);
        }
        $theme = $this->table('Themes')->find()->first();
        if (!$theme) {
            $theme = $this->table('Themes')->newEntity(['name' => 'Tema', 'slug' => 'admin-theme-' . uniqid(), 'active' => true]);
            $this->table('Themes')->saveOrFail($theme);
        }
        $site = $this->table('Sites')->newEntity([
            'user_id' => $this->userId,
            'template_id' => $template->id,
            'theme_id' => $theme->id,
            'name' => 'Sitio admin test',
            'slug' => 'admin-site-' . uniqid(),
            'subdomain' => 'admin-site-' . substr(uniqid(), -8),
            'status' => 'published',
            'whatsapp_country_code' => '56',
            'published_at' => DateTime::now(),
        ]);
        $this->table('Sites')->saveOrFail($site);

        return $site;
    }

    private function createDomain(int $siteId, string $domainName, string $type = 'subdomain', bool $verified = true, bool $active = true): object
    {
        $domain = $this->table('Domains')->newEntity([
            'site_id' => $siteId,
            'domain' => $domainName,
            'type' => $type,
            'verified' => $verified,
            'active' => $active,
        ]);
        $this->table('Domains')->saveOrFail($domain);

        return $domain;
    }

    private function createPendingPayment(): void
    {
        $payment = $this->table('Payments')->newEntity([
            'user_id' => $this->userId,
            'plan_slug' => 'admin-test-' . date('Ymd'),
            'status' => 'pending',
            'amount' => 6990,
            'expected_amount' => 6990,
            'currency' => 'CLP',
            'provider' => 'manual',
            'internal_reference' => 'admin-system-' . uniqid(),
        ]);
        $this->table('Payments')->saveOrFail($payment);
    }

    private function loginAs(int $userId): void
    {
        $user = $this->table('Users')->get($userId);
        $this->session(['Auth.User' => [
            'id' => $userId,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]]);
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
