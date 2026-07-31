<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\PaymentService;
use App\Service\SubscriptionService;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use RuntimeException;

class PaymentServiceTest extends TestCase
{
    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('SUBSCRIPTION_DURATION_DAYS=30');
        putenv('WEBPAY_ENV');
        putenv('WEBPAY_ENABLE_TEST_ORDER');
        $this->service = new PaymentService();
        $this->ensurePlans();
    }

    protected function tearDown(): void
    {
        putenv('WEBPAY_ENV');
        putenv('WEBPAY_ENABLE_TEST_ORDER');
        parent::tearDown();
    }

    public function testCreatePendingOrderUsesPlanPriceCurrencyAndUniqueReferences(): void
    {
        $userId = $this->createUser();

        $first = $this->service->createPendingOrder($userId, 'basica');
        $second = $this->service->createPendingOrder($userId, 'basica');

        $this->assertSame('pending', $first->status);
        $this->assertSame(6990, (int)$first->expected_amount);
        $this->assertSame(6990, (int)$first->amount);
        $this->assertSame('CLP', $first->currency);
        $this->assertNotSame($first->internal_reference, $second->internal_reference);
        $this->assertNotSame($first->buy_order, $second->buy_order);
        $this->assertStringStartsWith('bo-', $first->buy_order);
        $this->assertLessThanOrEqual(26, mb_strlen($first->buy_order));
        $this->assertSame(26, mb_strlen($first->buy_order));
    }

    public function testBuyOrderHasDatabaseUniquenessProtection(): void
    {
        $userId = $this->createUser();
        $payment = $this->service->createPendingOrder($userId, 'basica');

        $duplicate = $this->table('Payments')->newEntity([
            'user_id' => $userId,
            'plan_slug' => 'basica',
            'status' => 'pending',
            'amount' => 6990,
            'expected_amount' => 6990,
            'currency' => 'CLP',
            'provider' => PaymentService::PROVIDER,
            'internal_reference' => 'pay-collision-' . uniqid(),
            'buy_order' => $payment->buy_order,
            'session_id' => 'sess-collision-' . uniqid(),
            'request_payload' => '{}',
            'response_payload' => '{}',
        ]);

        $this->assertFalse($this->table('Payments')->save($duplicate));
        $this->assertArrayHasKey('buy_order', $duplicate->getErrors());
    }

    public function testGatewayCommitLeaseIsExclusiveAndCanBeReleased(): void
    {
        $payment = $this->service->createPendingOrder($this->createUser(), 'basica');

        $firstClaim = $this->service->claimGatewayCommit($payment);
        $secondClaim = $this->service->claimGatewayCommit($payment);

        $this->assertNotNull($firstClaim);
        $this->assertFalse($firstClaim['recovered']);
        $this->assertNull($secondClaim);

        $released = $this->service->releaseGatewayCommit($firstClaim['payment']);
        $this->assertNotNull($this->service->claimGatewayCommit($released));
    }

    public function testExpiredGatewayCommitLeaseCanBeRecovered(): void
    {
        $payment = $this->service->createPendingOrder($this->createUser(), 'basica');
        $this->table('Payments')->updateAll([
            'gateway_commit_started_at' => DateTime::now()->modify('-121 seconds'),
        ], ['id' => $payment->id]);

        $claim = $this->service->claimGatewayCommit($payment);
        $this->assertNotNull($claim);
        $this->assertTrue($claim['recovered']);
    }

    public function testGatewayTransactionExpiresAfterTenMinutesByDefault(): void
    {
        putenv('WEBPAY_PENDING_EXPIRATION_MINUTES');
        $payment = $this->service->createPendingOrder($this->createUser(), 'basica');
        $before = DateTime::now()->getTimestamp();

        $payment = $this->service->recordGatewayTransaction($payment, [
            'token' => 'token-expiration-' . uniqid(),
            'url' => 'https://webpay.test/payment',
        ]);

        $this->assertGreaterThanOrEqual($before + 599, $payment->gateway_expires_at->getTimestamp());
        $this->assertLessThanOrEqual($before + 601, $payment->gateway_expires_at->getTimestamp());
    }

    public function testFrontendAmountIsIgnoredBecausePriceComesFromDatabase(): void
    {
        $userId = $this->createUser();

        $payment = $this->service->createPendingOrder($userId, 'full');

        $this->assertSame(16990, (int)$payment->expected_amount);
    }

    public function testAnnualOrderUsesAnnualPriceAndRenewsForAnnualDuration(): void
    {
        $plan = $this->table('Plans')->find()->where(['slug' => 'basica'])->firstOrFail();
        $plan->annual_price = 76900;
        $plan->capabilities = json_encode([
            'annual_available' => true,
            'annual_price' => 76900,
            'sites_configured_limit' => 1,
            'sites_published_limit' => 1,
            'items_limit' => 80,
            'enabled_templates' => ['carta-simple'],
        ]);
        $this->table('Plans')->saveOrFail($plan);
        $userId = $this->createUser();

        $payment = $this->service->createPendingOrder($userId, 'basica', 'annual');
        $this->assertSame('annual', $payment->billing_cycle);
        $this->assertSame(76900, (int)$payment->expected_amount);

        $payment = $this->service->confirm($payment, [
            'amount' => 76900,
            'currency' => 'CLP',
            'buy_order' => $payment->buy_order,
            'session_id' => $payment->session_id,
            'provider_reference' => 'tx-annual-' . uniqid(),
        ]);
        $subscription = $this->table('Subscriptions')->get($payment->subscription_id);
        $this->assertSame('annual', $subscription->billing_cycle);
        $this->assertGreaterThanOrEqual(DateTime::now()->addDays(364)->getTimestamp(), $subscription->ends_at->getTimestamp());
    }

    public function testConfirmRejectsWrongAmountAndCurrency(): void
    {
        $userId = $this->createUser();
        $payment = $this->service->createPendingOrder($userId, 'basica');

        try {
            $this->service->confirm($payment, [
                'amount' => 1,
                'currency' => 'CLP',
                'buy_order' => $payment->buy_order,
                'session_id' => $payment->session_id,
                'provider_reference' => 'tx-wrong-amount',
            ]);
        } catch (RuntimeException) {
        }

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('failed', $payment->status);
    }

    public function testConfirmRejectsWrongCurrency(): void
    {
        $userId = $this->createUser();
        $payment = $this->service->createPendingOrder($userId, 'basica');

        try {
            $this->service->confirm($payment, [
                'amount' => 6990,
                'currency' => 'USD',
                'buy_order' => $payment->buy_order,
                'session_id' => $payment->session_id,
                'provider_reference' => 'tx-wrong-currency',
            ]);
        } catch (RuntimeException) {
        }

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('failed', $payment->status);
        $this->assertSame('currency_mismatch', $payment->error_code);
    }

    public function testApprovedPaymentRenewsSubscription(): void
    {
        $userId = $this->createUser();
        $payment = $this->service->createPendingOrder($userId, 'basica');

        $payment = $this->service->confirm($payment, [
            'amount' => 6990,
            'currency' => 'CLP',
            'buy_order' => $payment->buy_order,
            'session_id' => $payment->session_id,
            'provider_reference' => 'tx-approved',
            'authorization_code' => 'AUTH123',
            'card_number' => '4111111111111111',
        ]);

        $this->assertSame('paid', $payment->status);
        $this->assertNotEmpty($payment->processed_at);
        $this->assertSame('AUTH123', $payment->authorization_code);
        $responsePayload = is_string($payment->response_payload)
            ? json_decode($payment->response_payload, true)
            : $payment->response_payload;
        $this->assertSame('[redacted]', $responsePayload['card_number']);

        $subscription = $this->table('Subscriptions')->get($payment->subscription_id);
        $this->assertSame('active', $subscription->status);
        $this->assertSame('basica', $subscription->plan_slug);
        $this->assertGreaterThan(DateTime::now(), $subscription->ends_at);
    }

    public function testIntegrationTestOrderUsesOnePesoAndDoesNotChangeSubscription(): void
    {
        putenv('WEBPAY_ENV=integration');
        putenv('WEBPAY_ENABLE_TEST_ORDER=true');
        $this->ensureIntegrationTestPlan();
        $userId = $this->createUser('admin');

        $payment = $this->service->createIntegrationTestOrder($userId);

        $this->assertSame(PaymentService::INTEGRATION_TEST_PLAN_SLUG, $payment->plan_slug);
        $this->assertSame(1, (int)$payment->expected_amount);
        $this->assertSame('CLP', $payment->currency);
        $this->assertNull($payment->subscription_id);

        $payment = $this->service->confirm($payment, [
            'amount' => 1,
            'currency' => 'CLP',
            'buy_order' => $payment->buy_order,
            'session_id' => $payment->session_id,
            'provider_reference' => 'tx-integration-test',
            'authorization_code' => 'AUTH01',
        ]);

        $this->assertSame('paid', $payment->status);
        $this->assertNotEmpty($payment->processed_at);
        $this->assertSame(0, $this->table('Subscriptions')->find()->where(['user_id' => $userId])->count());
    }

    public function testIntegrationTestOrderRequiresEnabledEnvironmentAndSuperAdmin(): void
    {
        $this->ensureIntegrationTestPlan();
        $userId = $this->createUser();

        $this->expectException(RuntimeException::class);
        $this->service->createIntegrationTestOrder($userId);
    }

    public function testRejectedPaymentDoesNotRenewSubscription(): void
    {
        $userId = $this->createUser();
        $payment = $this->service->createPendingOrder($userId, 'basica');

        $payment = $this->service->reject($payment, ['error_code' => 'REJECTED']);

        $this->assertSame('rejected', $payment->status);
        $this->assertSame(0, $this->table('Subscriptions')->find()->where(['user_id' => $userId])->count());
    }

    public function testDuplicateConfirmationDoesNotRenewTwice(): void
    {
        $userId = $this->createUser();
        $payment = $this->service->createPendingOrder($userId, 'basica');
        $payment = $this->service->confirm($payment, [
            'amount' => 6990,
            'currency' => 'CLP',
            'buy_order' => $payment->buy_order,
            'session_id' => $payment->session_id,
            'provider_reference' => 'tx-duplicate',
        ]);
        $firstEnd = $this->table('Subscriptions')->get($payment->subscription_id)->ends_at;

        $payment = $this->service->confirm($this->table('Payments')->get($payment->id), [
            'amount' => 6990,
            'currency' => 'CLP',
            'buy_order' => $payment->buy_order,
            'session_id' => $payment->session_id,
            'provider_reference' => 'tx-duplicate',
        ]);
        $secondEnd = $this->table('Subscriptions')->get($payment->subscription_id)->ends_at;

        $this->assertEquals($firstEnd, $secondEnd);
    }

    public function testRollbackIfSubscriptionRenewFails(): void
    {
        $userId = $this->createUser();
        $payment = $this->service->createPendingOrder($userId, 'basica');
        $service = new PaymentService(new class extends SubscriptionService {
            public function renew(object $subscription, object $payment, int $durationDays): object
            {
                throw new RuntimeException('renew failed');
            }
        });

        try {
            $service->confirm($payment, [
                'amount' => 6990,
                'currency' => 'CLP',
                'buy_order' => $payment->buy_order,
                'session_id' => $payment->session_id,
                'provider_reference' => 'tx-rollback',
            ]);
        } catch (RuntimeException) {
        }

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('pending', $payment->status);
        $this->assertNull($payment->processed_at);
        $this->assertSame(0, $this->table('Subscriptions')->find()->where(['user_id' => $userId])->count());
    }

    public function testUnknownReferenceReturnsNull(): void
    {
        $this->assertNull($this->service->paymentByReference('pay-no-existe'));
    }

    public function testUpgradeIsAllowedAndDowngradeIsRejected(): void
    {
        $userId = $this->createUser();
        $this->createSubscription($userId, 'basica');
        $upgrade = $this->service->createPendingOrder($userId, 'full');
        $upgrade = $this->service->confirm($upgrade, [
            'amount' => 16990,
            'currency' => 'CLP',
            'buy_order' => $upgrade->buy_order,
            'session_id' => $upgrade->session_id,
            'provider_reference' => 'tx-upgrade',
        ]);
        $this->assertSame('full', $this->table('Subscriptions')->get($upgrade->subscription_id)->plan_slug);

        $this->expectException(RuntimeException::class);
        $this->service->createPendingOrder($userId, 'basica');
    }

    public function testConfirmRejectsDifferentBuyOrderAndSessionId(): void
    {
        $userId = $this->createUser();
        $payment = $this->service->createPendingOrder($userId, 'basica');
        $payment = $this->service->confirm($payment, [
            'amount' => 6990,
            'currency' => 'CLP',
            'buy_order' => 'other-order',
            'session_id' => $payment->session_id,
            'provider_reference' => 'tx-other-order',
        ]);
        $this->assertSame('failed', $payment->status);
        $this->assertSame('buy_order_mismatch', $payment->error_code);

        $payment = $this->service->createPendingOrder($userId, 'basica');
        $payment = $this->service->confirm($payment, [
            'amount' => 6990,
            'currency' => 'CLP',
            'buy_order' => $payment->buy_order,
            'session_id' => 'other-session',
            'provider_reference' => 'tx-other-session',
        ]);
        $this->assertSame('failed', $payment->status);
        $this->assertSame('session_id_mismatch', $payment->error_code);
    }

    private function createUser(string $role = 'customer'): int
    {
        $user = $this->table('Users')->newEntity([
            'name' => 'Cliente Pago',
            'email' => 'pago-' . uniqid() . '@example.test',
            'password' => 'secret123',
            'role' => $role,
            'active' => true,
            'email_verified' => true,
        ]);
        $this->table('Users')->saveOrFail($user);

        return (int)$user->id;
    }

    private function createSubscription(int $userId, string $planSlug): object
    {
        $subscription = $this->table('Subscriptions')->newEntity([
            'user_id' => $userId,
            'plan_slug' => $planSlug,
            'status' => 'active',
            'starts_at' => DateTime::now()->subDays(10),
            'ends_at' => DateTime::now()->addDays(20),
        ]);
        $this->table('Subscriptions')->saveOrFail($subscription);

        return $subscription;
    }

    private function ensurePlans(): void
    {
        foreach ([
            ['Básico', 'basica', 6990, 1],
            ['Medio', 'basica-avanzada', 9990, 2],
            ['Full', 'full', 16990, 3],
        ] as [$name, $slug, $price, $sort]) {
            $capabilities = json_encode([
                'sites_configured_limit' => $slug === 'basica' ? 1 : 5,
                'sites_published_limit' => $slug === 'basica' ? 1 : 5,
                'items_limit' => 100,
                'categories_enabled' => $slug !== 'basica',
                'enabled_templates' => ['carta-simple', 'catalogo-simple', 'carta-categorias', 'catalogo-categorias'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $plan = $this->table('Plans')->find()->where(['slug' => $slug])->first();
            if (!$plan) {
                $plan = $this->table('Plans')->newEntity([
                    'name' => $name,
                    'slug' => $slug,
                    'monthly_price' => $price,
                    'max_sites' => 5,
                    'max_published' => 5,
                    'sort_order' => $sort,
                    'active' => true,
                    'capabilities' => $capabilities,
                ]);
            } else {
                $plan->monthly_price = $price;
                $plan->sort_order = $sort;
                $plan->active = true;
                $plan->capabilities = $capabilities;
            }
            $this->table('Plans')->saveOrFail($plan);
        }
    }

    private function ensureIntegrationTestPlan(): void
    {
        $plan = $this->table('Plans')->find()
            ->where(['slug' => PaymentService::INTEGRATION_TEST_PLAN_SLUG])
            ->first();
        if (!$plan) {
            $plan = $this->table('Plans')->newEntity([
                'slug' => PaymentService::INTEGRATION_TEST_PLAN_SLUG,
            ]);
        }
        $plan->name = 'Prueba interna Webpay';
        $plan->monthly_price = 1;
        $plan->max_sites = 0;
        $plan->max_published = 0;
        $plan->sort_order = 999;
        $plan->active = false;
        $plan->capabilities = json_encode(['enabled_templates' => []]);
        $this->table('Plans')->saveOrFail($plan);
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
