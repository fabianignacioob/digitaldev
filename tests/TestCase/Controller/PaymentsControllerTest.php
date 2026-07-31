<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Service\PaymentService;
use App\Test\Double\FakeWebpayPlusGateway;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class PaymentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    private int $userId;
    private bool $previousDebug;
    private ?string $previousAppEnv;
    private ?string $previousWebpayEnv;
    private ?string $previousIntegrationTestOrder;
    private FakeWebpayPlusGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousDebug = (bool)Configure::read('debug');
        $this->previousAppEnv = getenv('APP_ENV') !== false ? (string)getenv('APP_ENV') : null;
        $this->previousWebpayEnv = getenv('WEBPAY_ENV') !== false ? (string)getenv('WEBPAY_ENV') : null;
        $this->previousIntegrationTestOrder = getenv('WEBPAY_ENABLE_TEST_ORDER') !== false ? (string)getenv('WEBPAY_ENABLE_TEST_ORDER') : null;
        $this->gateway = new FakeWebpayPlusGateway();
        Configure::write('Payments.webpayGateway', $this->gateway);
        $this->ensurePlan();
        $this->userId = $this->createUser();
    }

    protected function tearDown(): void
    {
        Configure::write('debug', $this->previousDebug);
        $this->previousAppEnv === null ? putenv('APP_ENV') : putenv('APP_ENV=' . $this->previousAppEnv);
        $this->previousWebpayEnv === null ? putenv('WEBPAY_ENV') : putenv('WEBPAY_ENV=' . $this->previousWebpayEnv);
        $this->previousIntegrationTestOrder === null ? putenv('WEBPAY_ENABLE_TEST_ORDER') : putenv('WEBPAY_ENABLE_TEST_ORDER=' . $this->previousIntegrationTestOrder);
        Configure::delete('Payments.webpayGateway');
        parent::tearDown();
    }

    public function testCreateEndpointCreatesPendingPayment(): void
    {
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/payments/create', [
            'plan' => 'basica',
            'amount' => 1,
        ]);

        $payment = $this->table('Payments')->find()->where(['user_id' => $this->userId])->firstOrFail();
        $this->assertResponseOk();
        $this->assertSame('pending', $payment->status);
        $this->assertSame(6990, (int)$payment->expected_amount);
        $this->assertSame('token-webpay-test', $payment->gateway_token);
        $this->assertSame(1, $this->gateway->createCalls);
    }

    public function testIntegrationTestPlanIsRestrictedToAdministrators(): void
    {
        $this->loginAs($this->userId);

        $this->get('/test-plan');

        $this->assertResponseCode(403);
    }

    public function testIntegrationTestPlanCreatesOnePesoPaymentForAdministrator(): void
    {
        putenv('WEBPAY_ENV=integration');
        putenv('WEBPAY_ENABLE_TEST_ORDER=true');
        $this->ensureIntegrationTestPlan();
        $this->gateway->createResponse = [
            'token' => 'token-webpay-integration-' . uniqid(),
            'url' => 'https://webpay.test/payment',
        ];
        $adminId = $this->createUser('admin');
        $this->loginAs($adminId, 'admin');
        $this->enableCsrfToken();

        $this->post('/test-plan');

        $this->assertResponseOk();
        $payment = $this->table('Payments')->find()
            ->where(['user_id' => $adminId, 'plan_slug' => PaymentService::INTEGRATION_TEST_PLAN_SLUG])
            ->firstOrFail();
        $this->assertSame(1, (int)$payment->expected_amount);
        $this->assertSame('pending', $payment->status);
        $this->assertStringStartsWith('token-webpay-integration-', (string)$payment->gateway_token);
        $this->assertSame(1, $this->gateway->createCalls);
    }

    public function testAuthenticatedPaymentResultUsesTheDashboardLayout(): void
    {
        $payment = $this->createPendingPayment($this->userId);
        $this->loginAs($this->userId);

        $this->get('/payments/result/' . $payment->internal_reference);

        $this->assertResponseOk();
        $this->assertResponseContains('Resultado del pago');
        $this->assertResponseContains('Mis sitios');
    }

    public function testMockConfirmWorksInDebug(): void
    {
        Configure::write('debug', true);
        $payment = $this->createPendingPayment($this->userId);
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/payments/mock-confirm', [
            'reference' => $payment->internal_reference,
            'provider_reference' => 'mock-controller-ok',
        ]);

        $this->assertRedirectContains('/payments/result/' . $payment->internal_reference);
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('paid', $payment->status);
        $this->assertNotEmpty($payment->processed_at);
    }

    public function testMockConfirmBlockedOutsideDevelopmentAndTest(): void
    {
        Configure::write('debug', false);
        putenv('APP_ENV=production');
        $payment = $this->createPendingPayment($this->userId);
        $this->loginAs($this->userId);
        $this->enableCsrfToken();

        $this->post('/payments/mock-confirm', [
            'reference' => $payment->internal_reference,
        ]);

        $this->assertResponseCode(403);
        $this->assertSame('pending', $this->table('Payments')->get($payment->id)->status);
    }

    public function testPublicReturnCommitsAuthorizedPaymentOnlyOnce(): void
    {
        $payment = $this->gatewayPayment();
        $this->gateway->commitResponse = $this->approvedResponse($payment);

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));
        $this->assertResponseOk();
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('paid', $payment->status);
        $firstEnd = $this->table('Subscriptions')->get($payment->subscription_id)->ends_at;
        $this->assertNull($payment->provider_reference);
        $this->assertStringNotContainsString((string)$payment->gateway_token, (string)$payment->response_payload);
        $this->assertResponseNotContains((string)$payment->gateway_token);
        foreach ($this->table('AuditLogs')->find()->where(['entity' => 'payments', 'entity_id' => $payment->id]) as $audit) {
            $this->assertStringNotContainsString((string)$payment->gateway_token, (string)$audit->data);
        }

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));
        $this->assertResponseOk();
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertEquals($firstEnd, $this->table('Subscriptions')->get($payment->subscription_id)->ends_at);
        $this->assertSame(1, $this->gateway->commitCalls);
    }

    public function testPublicReturnRejectsInvalidProviderData(): void
    {
        $payment = $this->gatewayPayment();
        $this->gateway->commitResponse = $this->approvedResponse($payment, ['amount' => 1]);

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('failed', $payment->status);
        $this->assertSame('amount_mismatch', $payment->error_code);
        $this->assertSame(0, $this->table('Subscriptions')->find()->where(['user_id' => $this->userId])->count());
    }

    public function testPublicReturnRejectsProviderResponseCodeWithoutRenewing(): void
    {
        $payment = $this->gatewayPayment();
        $this->gateway->commitResponse = $this->approvedResponse($payment, [
            'status' => 'FAILED',
            'response_code' => -1,
        ]);

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('rejected', $payment->status);
        $this->assertSame(0, $this->table('Subscriptions')->find()->where(['user_id' => $this->userId])->count());
    }

    public function testPublicReturnRejectsUnknownTokenAndCanceledOrTimedOutPayments(): void
    {
        $this->get('/payments/webpay/return?token_ws=unknown-token');
        $this->assertResponseOk();

        $canceled = $this->gatewayPayment('token-canceled');
        $this->post('/payments/webpay/return', [
            'token_ws' => $canceled->gateway_token,
            'TBK_TOKEN' => $canceled->gateway_token,
            'TBK_ORDEN_COMPRA' => $canceled->buy_order,
            'TBK_ID_SESION' => $canceled->session_id,
        ]);
        $this->assertResponseOk();
        $this->assertSame('canceled', $this->table('Payments')->get($canceled->id)->status);
        $this->assertSame(0, $this->gateway->commitCalls);

        $timedOut = $this->gatewayPayment('token-timeout');
        $this->post('/payments/webpay/return', [
            'TBK_ORDEN_COMPRA' => $timedOut->buy_order,
            'TBK_ID_SESION' => $timedOut->session_id,
        ]);
        $this->assertSame('canceled', $this->table('Payments')->get($timedOut->id)->status);
        $this->assertSame(0, $this->gateway->commitCalls);
    }

    public function testPublicReturnKeepsPaymentPendingWhenGatewayTimesOut(): void
    {
        $payment = $this->gatewayPayment();
        $this->gateway->throwOnCommit = true;

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('gateway_commit_failed', $payment->error_code);
        $this->assertNull($payment->gateway_commit_started_at);

        $this->gateway->throwOnCommit = false;
        $this->gateway->commitResponse = $this->approvedResponse($payment);
        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('paid', $payment->status);
        $this->assertSame(2, $this->gateway->commitCalls);
    }

    public function testActiveCommitLeasePreventsConcurrentReturnFromCallingGatewayAgain(): void
    {
        $payment = $this->gatewayPayment();
        $claimed = (new PaymentService())->claimGatewayCommit($payment);
        $this->assertNotNull($claimed);

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $this->assertResponseOk();
        $this->assertSame(0, $this->gateway->commitCalls);
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('pending', $payment->status);
    }

    public function testRecoveredLeaseUsesApprovedStatusWithoutRepeatingCommit(): void
    {
        $payment = $this->expiredLeasePayment();
        $this->gateway->statusResponse = $this->approvedResponse($payment);

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('paid', $payment->status);
        $this->assertSame(1, $this->gateway->statusCalls);
        $this->assertSame(0, $this->gateway->commitCalls);
        $this->assertSame(1, $this->table('Subscriptions')->find()->where(['user_id' => $this->userId])->count());
        foreach ($this->table('AuditLogs')->find()->where(['entity' => 'payments', 'entity_id' => $payment->id]) as $audit) {
            $this->assertStringNotContainsString((string)$payment->gateway_token, (string)$audit->data);
        }
    }

    public function testRecoveredLeaseRetriesCommitOnlyWhenStatusIsInitialized(): void
    {
        $payment = $this->expiredLeasePayment();
        $this->gateway->statusResponse = ['status' => 'INITIALIZED', 'response_code' => null];
        $this->gateway->commitResponse = $this->approvedResponse($payment);

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('paid', $payment->status);
        $this->assertSame(1, $this->gateway->statusCalls);
        $this->assertSame(1, $this->gateway->commitCalls);
        $this->assertSame(1, $this->table('AuditLogs')->find()->where([
            'entity' => 'payments',
            'entity_id' => $payment->id,
            'action' => 'payment.gateway_commit_retried',
        ])->count());
    }

    public function testRecoveredLeaseRejectsKnownFailedStatusWithoutCallingCommit(): void
    {
        $payment = $this->expiredLeasePayment();
        $this->gateway->statusResponse = array_merge($this->approvedResponse($payment), [
            'status' => 'FAILED',
            'response_code' => -1,
        ]);

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('rejected', $payment->status);
        $this->assertSame(0, $this->gateway->commitCalls);
        $this->assertSame(0, $this->table('Subscriptions')->find()->where(['user_id' => $this->userId])->count());
    }

    public function testRecoveredLeaseKeepsPaymentPendingWhenStatusFailsOrIsInconclusive(): void
    {
        $payment = $this->expiredLeasePayment();
        $this->gateway->throwOnStatus = true;

        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('gateway_status_recovery_failed', $payment->error_code);
        $this->assertSame(0, $this->gateway->commitCalls);
        $claimAfterFailure = (new PaymentService())->claimGatewayCommit($payment);
        $this->assertNotNull($claimAfterFailure);
        (new PaymentService())->releaseGatewayCommit($claimAfterFailure['payment']);

        $this->table('Payments')->updateAll([
            'gateway_commit_started_at' => DateTime::now()->modify('-121 seconds'),
        ], ['id' => $payment->id]);
        $this->gateway->throwOnStatus = false;
        $this->gateway->statusResponse = ['status' => 'AUTHORIZED', 'response_code' => -1];
        $this->get('/payments/webpay/return?token_ws=' . urlencode((string)$payment->gateway_token));

        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('gateway_status_inconclusive', $payment->error_code);
        $this->assertSame(0, $this->gateway->commitCalls);
    }

    private function createPendingPayment(int $userId): object
    {
        $now = DateTime::now();
        $payment = $this->table('Payments')->newEntity([
            'user_id' => $userId,
            'plan_slug' => 'basica',
            'status' => 'pending',
            'amount' => 6990,
            'expected_amount' => 6990,
            'currency' => 'CLP',
            'provider' => PaymentService::PROVIDER,
            'internal_reference' => 'pay-' . uniqid(),
            'buy_order' => 'bo-' . uniqid(),
            'session_id' => 'sess-' . uniqid(),
            'request_payload' => '{}',
            'response_payload' => '{}',
            'created' => $now,
            'modified' => $now,
        ]);
        $this->table('Payments')->saveOrFail($payment);

        return $payment;
    }

    private function gatewayPayment(?string $token = null): object
    {
        $payment = $this->createPendingPayment($this->userId);
        $token ??= 'token-webpay-test-' . uniqid();
        $payment->gateway_token = $token;
        $payment->gateway_url = 'https://webpay.test/payment';
        $payment->gateway_created_at = DateTime::now();
        $payment->gateway_expires_at = DateTime::now()->addMinutes(60);
        $this->table('Payments')->saveOrFail($payment);
        $this->assertNotNull((new PaymentService())->paymentByGatewayToken($token));

        return $payment;
    }

    private function expiredLeasePayment(): object
    {
        $payment = $this->gatewayPayment();
        $this->table('Payments')->updateAll([
            'gateway_commit_started_at' => DateTime::now()->modify('-121 seconds'),
        ], ['id' => $payment->id]);

        return $this->table('Payments')->get($payment->id);
    }

    private function approvedResponse(object $payment, array $override = []): array
    {
        return array_merge([
            'amount' => (int)$payment->expected_amount,
            'currency' => 'CLP',
            'status' => 'AUTHORIZED',
            'response_code' => 0,
            'buy_order' => (string)$payment->buy_order,
            'session_id' => (string)$payment->session_id,
            'authorization_code' => 'AUTH-CONTROLLER',
        ], $override);
    }

    private function createUser(string $role = 'customer'): int
    {
        $user = $this->table('Users')->newEntity([
            'name' => 'Cliente Pago Controller',
            'email' => 'pago-controller-' . uniqid() . '@example.test',
            'password' => 'secret123',
            'role' => $role,
            'active' => true,
            'email_verified' => true,
        ]);
        $this->table('Users')->saveOrFail($user);

        return (int)$user->id;
    }

    private function ensurePlan(): void
    {
        $plan = $this->table('Plans')->find()->where(['slug' => 'basica'])->first();
        if (!$plan) {
            $plan = $this->table('Plans')->newEntity([
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
                    'enabled_templates' => ['carta-simple', 'catalogo-simple'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        } else {
            $plan->monthly_price = 6990;
            $plan->sort_order = 1;
            $plan->active = true;
            $plan->capabilities = json_encode([
                'sites_configured_limit' => 1,
                'sites_published_limit' => 1,
                'items_limit' => 40,
                'enabled_templates' => ['carta-simple', 'catalogo-simple'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $this->table('Plans')->saveOrFail($plan);
    }

    private function ensureIntegrationTestPlan(): void
    {
        $plan = $this->table('Plans')->find()
            ->where(['slug' => PaymentService::INTEGRATION_TEST_PLAN_SLUG])
            ->first();
        if (!$plan) {
            $plan = $this->table('Plans')->newEntity(['slug' => PaymentService::INTEGRATION_TEST_PLAN_SLUG]);
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

    private function loginAs(int $userId, string $role = 'customer'): void
    {
        $this->session([
            'Auth.User' => [
                'id' => $userId,
                'name' => 'Cliente Pago Controller',
                'email' => 'pago-controller@example.test',
                'role' => $role,
            ],
        ]);
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
