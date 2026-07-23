<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class DashboardControllerTest extends TestCase
{
    use IntegrationTestTrait;

    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureBasicPlan();
        $this->userId = $this->createUser();
        $this->createActiveSubscription($this->userId);
    }

    public function testPanelDisplaysTheCurrentPlanUsageAndRenewalAction(): void
    {
        $this->session([
            'Auth.User' => [
                'id' => $this->userId,
                'name' => 'Cliente Panel',
                'email' => 'cliente-panel@example.test',
                'role' => 'customer',
            ],
        ]);

        $this->get('/panel');

        $this->assertResponseOk();
        $this->assertResponseContains('Mis sitios');
        $this->assertResponseContains('Sitios configurados');
        $this->assertResponseContains('Plan Básico');
        $this->assertResponseContains('Extender 30 días');
        $this->assertResponseContains('Tus sitios');
    }

    private function ensureBasicPlan(): void
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
            ]),
        ]));
    }

    private function createUser(): int
    {
        $users = $this->table('Users');
        $user = $users->newEntity([
            'name' => 'Cliente Panel',
            'email' => 'cliente-panel-' . uniqid() . '@example.test',
            'password' => 'secret123',
            'role' => 'customer',
            'active' => true,
            'email_verified' => true,
        ]);
        $users->saveOrFail($user);

        return (int)$user->id;
    }

    private function createActiveSubscription(int $userId): void
    {
        $subscriptions = $this->table('Subscriptions');
        $now = DateTime::now();
        $subscription = $subscriptions->newEntity([
            'user_id' => $userId,
            'plan_slug' => 'basica',
            'status' => 'active',
            'starts_at' => $now,
            'ends_at' => $now->addDays(30),
            'notes' => 'Dashboard test subscription',
        ]);
        $subscriptions->saveOrFail($subscription);
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
