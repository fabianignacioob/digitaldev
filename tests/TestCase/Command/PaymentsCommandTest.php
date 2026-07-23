<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Service\PaymentService;
use App\Test\Double\FakeWebpayPlusGateway;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

class PaymentsCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    private FakeWebpayPlusGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new FakeWebpayPlusGateway();
        Configure::write('Payments.webpayGateway', $this->gateway);
        $this->cleanTables();
        $this->ensurePlan();
    }

    protected function tearDown(): void
    {
        Configure::delete('Payments.webpayGateway');
        parent::tearDown();
    }

    public function testReconcileApprovesPendingGatewayPayment(): void
    {
        $payment = $this->gatewayPayment();
        $this->gateway->statusResponse = $this->approvedResponse($payment);

        $this->exec('payments reconcile');

        $this->assertExitSuccess();
        $this->assertOutputContains('confirmar');
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('paid', $payment->status);
        $this->assertNotEmpty($payment->processed_at);
        $this->assertSame(1, $this->table('Subscriptions')->find()->where(['user_id' => $payment->user_id])->count());
        $run = $this->table('OperationalProcessRuns')->find()
            ->where(['process_name' => 'payments.reconcile'])
            ->orderByDesc('id')
            ->firstOrFail();
        $this->assertSame('success', $run->status);
        $this->assertSame(1, (int)$run->processed_count);
    }

    public function testReconcileRecoversPendingPaymentAfterStatusRecoveryFailure(): void
    {
        $payment = $this->gatewayPayment();
        $payment->error_code = 'gateway_status_recovery_failed';
        $this->table('Payments')->saveOrFail($payment);
        $this->gateway->statusResponse = $this->approvedResponse($payment);

        $this->exec('payments reconcile');

        $this->assertExitSuccess();
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('paid', $payment->status);
        $this->assertNotEmpty($payment->processed_at);
    }

    public function testDryRunAndExpiredPendingPaymentDoNotRenew(): void
    {
        $payment = $this->gatewayPayment(DateTime::now()->subMinutes(1));
        $this->gateway->statusResponse = [
            'status' => 'INITIALIZED',
            'response_code' => null,
        ];

        $this->exec('payments reconcile --dry-run');
        $this->assertExitSuccess();
        $this->assertOutputContains('dry_run=sí');
        $this->assertSame('pending', $this->table('Payments')->get($payment->id)->status);

        $this->exec('payments reconcile');
        $this->assertExitSuccess();
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('expired', $payment->status);
        $this->assertSame(0, $this->table('Subscriptions')->find()->where(['user_id' => $payment->user_id])->count());
    }

    public function testReconcileNetworkFailureKeepsPaymentPending(): void
    {
        $payment = $this->gatewayPayment();
        $this->gateway->throwOnStatus = true;

        $this->exec('payments reconcile');

        $this->assertExitCode(1);
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('gateway_reconcile_failed', $payment->error_code);
    }

    private function gatewayPayment(?DateTime $expiresAt = null): object
    {
        $userId = $this->createUser();
        $service = new PaymentService();
        $payment = $service->createPendingOrder($userId, 'basica');
        $payment->gateway_token = 'token-command-' . uniqid();
        $payment->gateway_url = 'https://webpay.test/payment';
        $payment->gateway_created_at = DateTime::now();
        $payment->gateway_expires_at = $expiresAt ?: DateTime::now()->addMinutes(10);
        $this->table('Payments')->saveOrFail($payment);

        return $payment;
    }

    private function approvedResponse(object $payment): array
    {
        return [
            'amount' => (int)$payment->expected_amount,
            'currency' => 'CLP',
            'status' => 'AUTHORIZED',
            'response_code' => 0,
            'buy_order' => (string)$payment->buy_order,
            'session_id' => (string)$payment->session_id,
            'authorization_code' => 'AUTH-COMMAND',
        ];
    }

    private function createUser(): int
    {
        $user = $this->table('Users')->newEntity([
            'name' => 'Cliente Command Payment',
            'email' => 'command-payment-' . uniqid() . '@example.test',
            'password' => 'secret123',
            'role' => 'customer',
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
            $plan = $this->table('Plans')->newEntity(['slug' => 'basica']);
        }
        $plan->name = 'Básico';
        $plan->monthly_price = 6990;
        $plan->max_sites = 1;
        $plan->max_published = 1;
        $plan->sort_order = 1;
        $plan->active = true;
        $plan->capabilities = json_encode(['enabled_templates' => ['carta-simple', 'catalogo-simple']]);
        $this->table('Plans')->saveOrFail($plan);
    }

    private function cleanTables(): void
    {
        $this->table('OperationalProcessRuns')->deleteAll([]);
        $this->table('AuditLogs')->deleteAll([]);
        $this->table('Payments')->deleteAll([]);
        $this->table('Subscriptions')->deleteAll([]);
        $this->table('Users')->deleteAll(['email LIKE' => 'command-payment-%']);
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
