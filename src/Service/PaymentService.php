<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Table;
use RuntimeException;
use Throwable;
use function Cake\Core\env;

class PaymentService
{
    private const WEBPAY_BUY_ORDER_PREFIX = 'bo-';
    private const WEBPAY_BUY_ORDER_RANDOM_BYTES = 17;
    private const WEBPAY_COMMIT_LEASE_SECONDS = 120;
    public const STATUS_PENDING = 'pending';
    public const STATUS_AUTHORIZED = 'authorized';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_REVERSED = 'reversed';
    public const STATUS_FAILED = 'failed';

    public const CURRENCY = 'CLP';
    public const PROVIDER = 'webpay_plus';
    public const INTEGRATION_TEST_PLAN_SLUG = 'webpay-integration-test';
    public const PRODUCTION_VALIDATION_PLAN_SLUG = 'webpay-production-validation';
    private const SECRET_KEYS = [
        'card_number',
        'card',
        'cvv',
        'cvc',
        'pan',
        'token',
        'tbk_token',
        'secret',
        'api_key',
        'commerce_code',
        'provider_reference',
    ];

    public function __construct(
        private ?SubscriptionService $subscriptionService = null,
        private ?AuditLogService $auditLogService = null,
        private ?EmailService $emailService = null,
    ) {
        $this->subscriptionService ??= new SubscriptionService();
        $this->auditLogService ??= new AuditLogService();
        $this->emailService ??= new EmailService();
    }

    public function createPendingOrder(
        int|array|object $user,
        string|object $plan,
        string $billingCycle = 'monthly',
        bool $allowInternalTestPlan = false,
    ): object
    {
        $userId = $this->userId($user);
        $plan = is_object($plan) ? $plan : $this->planBySlug((string)$plan);
        if (!$userId) {
            throw new RuntimeException('Usuario inválido para crear la orden de pago.');
        }
        $isInternalTestPlan = $plan && $this->isInternalTestPlan($plan);
        if (!$plan || (!(bool)$plan->active && !($isInternalTestPlan && $allowInternalTestPlan))) {
            throw new RuntimeException('Plan inválido o inactivo.');
        }
        if ($isInternalTestPlan && !$allowInternalTestPlan) {
            throw new RuntimeException('El plan de prueba de Webpay solo puede iniciarse desde la herramienta interna.');
        }
        $billingCycle = strtolower(trim($billingCycle));
        if (!in_array($billingCycle, ['monthly', 'annual'], true)) {
            throw new RuntimeException('La modalidad de pago no es válida.');
        }
        $planService = new PlanService();
        if ($planService->isTrialPlan($plan)) {
            throw new RuntimeException('La prueba gratuita no requiere pago.');
        }
        $capabilities = $planService->capabilities($plan);
        if ($billingCycle === 'annual' && !$capabilities['annual_available']) {
            throw new RuntimeException('Este plan no está disponible en modalidad anual.');
        }

        $currentSubscription = $isInternalTestPlan ? null : $this->latestSubscription($userId);
        if (!$isInternalTestPlan) {
            $this->assertUpgradePolicy($currentSubscription, $plan);
        }

        $amount = $billingCycle === 'annual' ? $planService->annualPrice($plan) : (int)$plan->monthly_price;
        if (!$amount || $amount < 1) {
            throw new RuntimeException('No hay un precio válido para esta modalidad.');
        }
        $internalReference = $this->uniqueReference('pay');
        $buyOrder = $this->uniqueBuyOrder();
        $sessionId = $this->uniqueReference('sess');
        $now = DateTime::now();
        $payload = $this->sanitizePayload([
            'plan_slug' => (string)$plan->slug,
            'amount' => $amount,
            'currency' => self::CURRENCY,
            'billing_cycle' => $billingCycle,
            'buy_order' => $buyOrder,
            'session_id' => $sessionId,
        ]);

        $payment = $this->payments()->newEntity([
            'user_id' => $userId,
            'subscription_id' => $currentSubscription?->id,
            'plan_slug' => (string)$plan->slug,
            'billing_cycle' => $billingCycle,
            'status' => self::STATUS_PENDING,
            'amount' => $amount,
            'expected_amount' => $amount,
            'confirmed_amount' => null,
            'currency' => self::CURRENCY,
            'provider' => self::PROVIDER,
            'provider_reference' => null,
            'internal_reference' => $internalReference,
            'buy_order' => $buyOrder,
            'session_id' => $sessionId,
            'request_payload' => $this->jsonPayload($payload),
            'response_payload' => $this->jsonPayload([]),
        ]);
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log($userId, 'payment.created', 'payments', (int)$payment->id, [
            'plan_slug' => (string)$plan->slug,
            'amount' => $amount,
            'currency' => self::CURRENCY,
            'billing_cycle' => $billingCycle,
            'internal_reference' => $internalReference,
        ]);

        return $payment;
    }

    /** Crea una orden interna de $1 exclusiva del ambiente de integración. */
    public function createIntegrationTestOrder(int|array|object $user): object
    {
        if (!$this->integrationTestOrderEnabled()) {
            throw new RuntimeException('La orden de prueba requiere WEBPAY_ENV=integration y WEBPAY_ENABLE_TEST_ORDER=true.');
        }

        return $this->createInternalTestOrder($user, self::INTEGRATION_TEST_PLAN_SLUG, 'payment.integration_test_created');
    }

    /**
     * Crea una orden interna de $50 para validar el comercio en producción.
     * Nunca renueva ni crea suscripciones.
     */
    public function createProductionValidationOrder(int|array|object $user): object
    {
        if (!$this->productionValidationOrderEnabled()) {
            throw new RuntimeException('La validación productiva requiere WEBPAY_ENV=production y WEBPAY_ENABLE_PRODUCTION_TEST_ORDER=true.');
        }

        return $this->createInternalTestOrder($user, self::PRODUCTION_VALIDATION_PLAN_SLUG, 'payment.production_validation_created');
    }

    /** @return array{environment:string, amount:int, enabled:bool, title:string, description:string} */
    public function testOrderConfiguration(): array
    {
        if ($this->webpayEnvironment() === 'production') {
            return [
                'environment' => 'producción',
                'amount' => 50,
                'enabled' => $this->productionValidationOrderEnabled(),
                'title' => 'Validación productiva Webpay Plus',
                'description' => 'Orden interna de $50 CLP para validar el comercio productivo. No crea ni renueva suscripciones.',
            ];
        }

        return [
            'environment' => 'integración',
            'amount' => 1,
            'enabled' => $this->integrationTestOrderEnabled(),
            'title' => 'Prueba de integración Webpay Plus',
            'description' => 'Orden interna de $1 CLP para comprobar el flujo real de Transbank. No crea ni renueva suscripciones.',
        ];
    }

    public function createConfiguredTestOrder(int|array|object $user): object
    {
        return $this->webpayEnvironment() === 'production'
            ? $this->createProductionValidationOrder($user)
            : $this->createIntegrationTestOrder($user);
    }

    private function createInternalTestOrder(int|array|object $user, string $planSlug, string $auditAction): object
    {
        $userId = $this->userId($user);
        $account = $this->users()->find()
            ->select(['id', 'role', 'active'])
            ->where(['id' => $userId])
            ->first();
        if (!$account || !(bool)$account->active || !in_array((string)$account->role, ['admin', 'superadmin'], true)) {
            throw new RuntimeException('Solo un administrador activo puede crear una orden de prueba Webpay.');
        }

        $plan = $this->plans()->find()
            ->where(['slug' => $planSlug])
            ->first();
        if (!$plan) {
            throw new RuntimeException('No se encontró el plan interno de prueba Webpay. Ejecuta las migraciones pendientes.');
        }

        $payment = $this->createPendingOrder($account, $plan, 'monthly', true);
        $this->auditLogService->log($userId, $auditAction, 'payments', (int)$payment->id, [
            'internal_reference' => (string)$payment->internal_reference,
            'amount' => (int)$payment->expected_amount,
            'currency' => self::CURRENCY,
        ]);

        return $payment;
    }

    public function integrationTestOrderEnabled(): bool
    {
        return $this->integrationTestEnabled();
    }

    public function productionValidationOrderEnabled(): bool
    {
        return $this->webpayEnvironment() === 'production'
            && (bool)Configure::read('Payments.webpay.productionValidationOrderEnabled', false);
    }

    /**
     * Persists the transaction created by Webpay without exposing its token in
     * audit logs or response payloads.
     *
     * @param array{token: string, url: string} $transaction
     */
    public function recordGatewayTransaction(object $payment, array $transaction): object
    {
        if ($payment->processed_at || (string)$payment->status !== self::STATUS_PENDING) {
            throw new RuntimeException('La orden ya no está disponible para iniciar el pago.');
        }

        $token = trim((string)($transaction['token'] ?? ''));
        $url = trim((string)($transaction['url'] ?? ''));
        if ($token === '' || mb_strlen($token) > 255 || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Webpay no entregó una transacción válida.');
        }

        $payment = $this->payments()->get($payment->id);
        $now = DateTime::now();
        $payment->provider = self::PROVIDER;
        $payment->gateway_token = $token;
        $payment->gateway_url = $url;
        $payment->gateway_created_at = $now;
        $payment->gateway_expires_at = (clone $now)->modify('+' . $this->pendingExpirationMinutes() . ' minutes');
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log((int)$payment->user_id, 'payment.webpay_created', 'payments', (int)$payment->id, [
            'internal_reference' => (string)$payment->internal_reference,
            'environment' => $this->webpayEnvironment(),
        ]);

        return $payment;
    }

    public function confirm(object $payment, array $providerResponse): object
    {
        if ($payment->processed_at) {
            $this->auditLogService->log((int)$payment->user_id, 'payment.duplicate_ignored', 'payments', (int)$payment->id, [
                'internal_reference' => (string)$payment->internal_reference,
                'status' => (string)$payment->status,
            ]);

            return $payment;
        }

        $connection = ConnectionManager::get('default');

        $shouldNotify = false;
        $result = $connection->transactional(function () use ($payment, $providerResponse, &$shouldNotify) {
            $payment = $this->payments()->get($payment->id);
            if ($payment->processed_at) {
                $this->auditLogService->log((int)$payment->user_id, 'payment.duplicate_ignored', 'payments', (int)$payment->id);

                return $payment;
            }

            $response = $this->sanitizePayload($providerResponse);
            $confirmedAmount = $this->confirmedAmount($providerResponse);
            $currency = strtoupper((string)($providerResponse['currency'] ?? self::CURRENCY));
            $providerReference = trim((string)($providerResponse['provider_reference'] ?? $providerResponse['transaction_id'] ?? ''));

            if ($providerReference === '') {
                throw new RuntimeException('La confirmación no incluye referencia del proveedor.');
            }
            if ($currency !== self::CURRENCY) {
                $this->failPayment($payment, $response, 'currency_mismatch');
                return $this->payments()->get($payment->id);
            }
            if ((int)$confirmedAmount !== (int)$payment->expected_amount) {
                $this->failPayment($payment, $response, 'amount_mismatch', $confirmedAmount);
                return $this->payments()->get($payment->id);
            }
            if ((string)($providerResponse['buy_order'] ?? '') !== (string)$payment->buy_order) {
                $this->failPayment($payment, $response, 'buy_order_mismatch', $confirmedAmount);
                return $this->payments()->get($payment->id);
            }
            if ((string)($providerResponse['session_id'] ?? '') !== (string)$payment->session_id) {
                $this->failPayment($payment, $response, 'session_id_mismatch', $confirmedAmount);
                return $this->payments()->get($payment->id);
            }
            $this->assertProviderReferenceNotProcessed($providerReference, (int)$payment->id);

            $now = DateTime::now();
            $payment->status = self::STATUS_AUTHORIZED;
            $payment->confirmed_amount = $confirmedAmount;
            $payment->provider_reference = $this->providerReferenceForStorage($payment, $providerReference);
            $payment->response_payload = $this->jsonPayload($response);
            $payment->authorization_code = $this->cleanString($providerResponse['authorization_code'] ?? null, 80);
            $payment->authorized_at = $now;
            $this->payments()->saveOrFail($payment);
            $this->auditLogService->log((int)$payment->user_id, 'payment.authorized', 'payments', (int)$payment->id, [
                'internal_reference' => (string)$payment->internal_reference,
            ]);

            if ($this->isInternalTestPayment($payment)) {
                $payment->status = self::STATUS_PAID;
                $payment->paid_at = $now;
                $payment->confirmed_at = $now;
                $payment->processed_at = $now;
                $this->payments()->saveOrFail($payment);
                $this->auditLogService->log((int)$payment->user_id, 'payment.paid', 'payments', (int)$payment->id);
                $this->auditLogService->log((int)$payment->user_id, $this->internalTestCompletedAuditAction($payment), 'payments', (int)$payment->id, [
                    'internal_reference' => (string)$payment->internal_reference,
                ]);
                $shouldNotify = true;

                return $payment;
            }

            $subscription = $this->subscriptionForPayment($payment);
            $subscription->plan_slug = (string)$payment->plan_slug;
            $subscription->billing_cycle = (string)$payment->billing_cycle ?: 'monthly';
            $this->subscriptions()->saveOrFail($subscription);

            $payment->status = self::STATUS_PAID;
            $payment->paid_at = $now;
            $payment->confirmed_at = $now;
            $this->payments()->saveOrFail($payment);

            $durationDays = (string)$payment->billing_cycle === 'annual'
                ? $this->subscriptionService->annualRenewalDays()
                : $this->subscriptionService->renewalDays();
            $this->subscriptionService->renew($subscription, $payment, $durationDays);
            $payment = $this->payments()->get($payment->id);
            $this->auditLogService->log((int)$payment->user_id, 'payment.paid', 'payments', (int)$payment->id);
            $this->auditLogService->log((int)$payment->user_id, 'payment.subscription_renewed', 'payments', (int)$payment->id, [
                'subscription_id' => (int)$subscription->id,
                'plan_slug' => (string)$payment->plan_slug,
            ]);
            $shouldNotify = true;

            return $payment;
        });

        if ($shouldNotify) {
            $this->notifyPaymentApproved($result);
        }

        return $result;
    }

    public function reject(object $payment, array $providerResponse): object
    {
        if ($payment->processed_at || in_array((string)$payment->status, [self::STATUS_PAID, self::STATUS_REJECTED], true)) {
            $this->auditLogService->log((int)$payment->user_id, 'payment.duplicate_ignored', 'payments', (int)$payment->id);

            return $payment;
        }

        $payment->status = self::STATUS_REJECTED;
        $payment->response_payload = $this->jsonPayload($this->sanitizePayload($providerResponse));
        $payment->error_code = $this->cleanString($providerResponse['error_code'] ?? $providerResponse['response_code'] ?? 'rejected', 80);
        $payment->provider_reference = $this->providerReferenceForStorage(
            $payment,
            $this->cleanString($providerResponse['provider_reference'] ?? null, 160),
        );
        $payment->confirmed_amount = $this->confirmedAmount($providerResponse) ?: null;
        $payment->confirmed_at = DateTime::now();
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log((int)$payment->user_id, 'payment.rejected', 'payments', (int)$payment->id, [
            'error_code' => (string)$payment->error_code,
        ]);
        $this->notifyPaymentRejected($payment);

        return $payment;
    }

    public function cancel(object $payment, array $providerResponse = []): object
    {
        if ($payment->processed_at || in_array((string)$payment->status, [self::STATUS_PAID, self::STATUS_CANCELED], true)) {
            $this->auditLogService->log((int)$payment->user_id, 'payment.duplicate_ignored', 'payments', (int)$payment->id);

            return $payment;
        }

        $payment->status = self::STATUS_CANCELED;
        $payment->provider_reference = $this->providerReferenceForStorage(
            $payment,
            $this->cleanString($providerResponse['provider_reference'] ?? null, 160),
        );
        $payment->response_payload = $this->jsonPayload($this->sanitizePayload($providerResponse));
        $payment->error_code = $this->cleanString($providerResponse['error_code'] ?? 'canceled', 80);
        $payment->canceled_at = DateTime::now();
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log((int)$payment->user_id, 'payment.canceled', 'payments', (int)$payment->id);
        $this->notifyPaymentCanceled($payment);

        return $payment;
    }

    public function paymentByReference(string $reference): ?object
    {
        return $this->payments()->find()
            ->where(['internal_reference' => $reference])
            ->first();
    }

    public function paymentByGatewayToken(string $token): ?object
    {
        $token = trim($token);
        if ($token === '' || mb_strlen($token) > 255) {
            return null;
        }

        return $this->payments()->find()
            ->where(['gateway_token' => $token, 'provider' => self::PROVIDER])
            ->first();
    }

    public function paymentByOrderAndSession(string $buyOrder, string $sessionId): ?object
    {
        if (trim($buyOrder) === '' || trim($sessionId) === '') {
            return null;
        }

        return $this->payments()->find()
            ->where([
                'buy_order' => trim($buyOrder),
                'session_id' => trim($sessionId),
                'provider' => self::PROVIDER,
            ])
            ->first();
    }

    public function expirePendingPayment(object $payment): object
    {
        if ($payment->processed_at || !in_array((string)$payment->status, [self::STATUS_PENDING, self::STATUS_AUTHORIZED], true)) {
            return $payment;
        }

        $payment->status = self::STATUS_EXPIRED;
        $payment->error_code = 'gateway_token_expired';
        $payment->confirmed_at = DateTime::now();
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log((int)$payment->user_id, 'payment.expired', 'payments', (int)$payment->id);
        $this->notifyPaymentExpired($payment);

        return $payment;
    }

    /**
     * Claims a short-lived lease before calling Webpay. The compare-and-set is
     * intentionally a single update, so no database transaction is held while
     * waiting on the external gateway.
     */
    /**
     * @return array{payment: object, recovered: bool}|null
     */
    public function claimGatewayCommit(object $payment): ?array
    {
        $now = DateTime::now();
        $expiredLease = (clone $now)->modify('-' . self::WEBPAY_COMMIT_LEASE_SECONDS . ' seconds');
        $connection = ConnectionManager::get('default');

        return $connection->transactional(function () use ($payment, $now, $expiredLease, $connection) {
            $lock = $connection->getDriver() instanceof \Cake\Database\Driver\Postgres ? ' FOR UPDATE' : '';
            $current = $connection->execute(
                'SELECT status, processed_at, gateway_commit_started_at FROM payments WHERE id = :id' . $lock,
                ['id' => (int)$payment->id],
            )->fetch('assoc');
            if (!$current || $current['processed_at'] !== null || $current['status'] !== self::STATUS_PENDING) {
                return null;
            }

            $recovered = $current['gateway_commit_started_at'] !== null
                && new DateTime((string)$current['gateway_commit_started_at']) < $expiredLease;
            if ($current['gateway_commit_started_at'] !== null && !$recovered) {
                return null;
            }

            $this->payments()->updateAll(
                ['gateway_commit_started_at' => $now],
                ['id' => (int)$payment->id],
            );

            return [
                'payment' => $this->payments()->get($payment->id),
                'recovered' => $recovered,
            ];
        });
    }

    public function releaseGatewayCommit(object $payment): object
    {
        $this->payments()->updateAll(
            ['gateway_commit_started_at' => null],
            ['id' => (int)$payment->id],
        );

        return $this->payments()->get($payment->id);
    }

    public function recordGatewayFailure(object $payment, string $errorCode): object
    {
        if ($payment->processed_at || !in_array((string)$payment->status, [self::STATUS_PENDING, self::STATUS_AUTHORIZED], true)) {
            return $payment;
        }

        $payment->error_code = $this->cleanString($errorCode, 80) ?: 'gateway_error';
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log((int)$payment->user_id, 'payment.gateway_error', 'payments', (int)$payment->id, [
            'error_code' => (string)$payment->error_code,
        ]);

        return $payment;
    }

    public function recordGatewayStatusRecoveryEvent(object $payment, string $action): void
    {
        $this->auditLogService->log((int)$payment->user_id, $action, 'payments', (int)$payment->id, [
            'internal_reference' => (string)$payment->internal_reference,
        ]);
    }

    public function recordGatewayStatusInconclusive(object $payment, string $errorCode): object
    {
        if ($payment->processed_at || !in_array((string)$payment->status, [self::STATUS_PENDING, self::STATUS_AUTHORIZED], true)) {
            return $payment;
        }

        $payment->error_code = $this->cleanString($errorCode, 80) ?: 'gateway_status_inconclusive';
        $this->payments()->saveOrFail($payment);
        $this->recordGatewayStatusRecoveryEvent($payment, 'payment.gateway_status_inconclusive');

        return $payment;
    }

    public function markGatewaySetupFailed(object $payment): object
    {
        if ($payment->processed_at || (string)$payment->status !== self::STATUS_PENDING) {
            return $payment;
        }

        $payment->status = self::STATUS_FAILED;
        $payment->error_code = 'gateway_create_failed';
        $payment->confirmed_at = DateTime::now();
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log((int)$payment->user_id, 'payment.gateway_create_failed', 'payments', (int)$payment->id);

        return $payment;
    }

    public function markReversed(object $payment, array $providerResponse): object
    {
        if ($payment->processed_at || in_array((string)$payment->status, [self::STATUS_PAID, self::STATUS_REVERSED], true)) {
            return $payment;
        }

        $payment->status = self::STATUS_REVERSED;
        $payment->provider_reference = $this->providerReferenceForStorage(
            $payment,
            $this->cleanString($providerResponse['provider_reference'] ?? null, 160),
        );
        $payment->response_payload = $this->jsonPayload($this->sanitizePayload($providerResponse));
        $payment->confirmed_at = DateTime::now();
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log((int)$payment->user_id, 'payment.reversed', 'payments', (int)$payment->id);

        return $payment;
    }

    private function failPayment(object $payment, array $response, string $errorCode, ?int $confirmedAmount = null): void
    {
        $payment->status = self::STATUS_FAILED;
        $payment->response_payload = $this->jsonPayload($response);
        $payment->error_code = $errorCode;
        $payment->confirmed_amount = $confirmedAmount;
        $payment->confirmed_at = DateTime::now();
        $this->payments()->saveOrFail($payment);
        $this->auditLogService->log((int)$payment->user_id, 'payment.failed', 'payments', (int)$payment->id, [
            'error_code' => $errorCode,
        ]);
    }

    private function notifyPaymentApproved(object $payment): void
    {
        try {
            $this->emailService->sendPaymentApproved($this->users()->get($payment->user_id), $payment);
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar la confirmación de pago: ' . $exception->getMessage());
        }
    }

    private function notifyPaymentRejected(object $payment): void
    {
        try {
            $this->emailService->sendPaymentRejected($this->users()->get($payment->user_id), $payment);
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar el rechazo de pago: ' . $exception->getMessage());
        }
    }

    private function notifyPaymentCanceled(object $payment): void
    {
        try {
            $this->emailService->sendPaymentCanceled($this->users()->get($payment->user_id), $payment);
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar la cancelación de pago: ' . $exception->getMessage());
        }
    }

    private function notifyPaymentExpired(object $payment): void
    {
        try {
            $this->emailService->sendPaymentExpired($this->users()->get($payment->user_id), $payment);
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar el vencimiento de pago: ' . $exception->getMessage());
        }
    }

    private function pendingExpirationMinutes(): int
    {
        return max(5, min(1440, (int)env('WEBPAY_PENDING_EXPIRATION_MINUTES', 10)));
    }

    private function subscriptionForPayment(object $payment): object
    {
        if ($payment->subscription_id) {
            return $this->subscriptions()->get($payment->subscription_id);
        }

        $subscription = $this->latestSubscription((int)$payment->user_id);
        if ($subscription) {
            $payment->subscription_id = (int)$subscription->id;
            $this->payments()->saveOrFail($payment);

            return $subscription;
        }

        $now = DateTime::now();
        $subscription = $this->subscriptions()->newEntity([
            'user_id' => (int)$payment->user_id,
            'plan_slug' => (string)$payment->plan_slug,
            'billing_cycle' => (string)$payment->billing_cycle,
            'status' => SubscriptionService::STATUS_EXPIRED,
            'starts_at' => $now,
            'ends_at' => $now,
            'notes' => 'Suscripción creada al confirmar pago interno.',
        ]);
        $this->subscriptions()->saveOrFail($subscription);
        $payment->subscription_id = (int)$subscription->id;
        $this->payments()->saveOrFail($payment);

        return $subscription;
    }

    private function assertUpgradePolicy(?object $subscription, object $targetPlan): void
    {
        if (!$subscription) {
            return;
        }

        $currentPlan = $this->planBySlug((string)$subscription->plan_slug);
        if (!$currentPlan) {
            return;
        }

        if ((int)$targetPlan->sort_order < (int)$currentPlan->sort_order) {
            throw new RuntimeException('El downgrade no está implementado todavía; se debe programar para un periodo futuro.');
        }
    }

    private function assertProviderReferenceNotProcessed(string $providerReference, int $paymentId): void
    {
        $existing = $this->payments()->find()
            ->where([
                'provider' => self::PROVIDER,
                'provider_reference' => $providerReference,
                'id !=' => $paymentId,
                'processed_at IS NOT' => null,
            ])
            ->first();
        if ($existing) {
            throw new RuntimeException('La referencia del proveedor ya fue procesada.');
        }
    }

    private function confirmedAmount(array $providerResponse): int
    {
        return (int)($providerResponse['amount'] ?? $providerResponse['confirmed_amount'] ?? 0);
    }

    private function planBySlug(string $slug): ?object
    {
        return $this->plans()->find()
            ->where(['slug' => $slug, 'active' => true])
            ->first();
    }

    private function isInternalTestPlan(object $plan): bool
    {
        return in_array((string)($plan->slug ?? ''), [
            self::INTEGRATION_TEST_PLAN_SLUG,
            self::PRODUCTION_VALIDATION_PLAN_SLUG,
        ], true);
    }

    private function isInternalTestPayment(object $payment): bool
    {
        return in_array((string)($payment->plan_slug ?? ''), [
            self::INTEGRATION_TEST_PLAN_SLUG,
            self::PRODUCTION_VALIDATION_PLAN_SLUG,
        ], true);
    }

    private function integrationTestEnabled(): bool
    {
        return $this->webpayEnvironment() === 'integration'
            && (bool)Configure::read('Payments.webpay.integrationTestOrderEnabled', false);
    }

    private function webpayEnvironment(): string
    {
        return strtolower(trim((string)Configure::read('Payments.webpay.environment', env('WEBPAY_ENV', 'integration'))));
    }

    private function internalTestCompletedAuditAction(object $payment): string
    {
        return (string)$payment->plan_slug === self::PRODUCTION_VALIDATION_PLAN_SLUG
            ? 'payment.production_validation_completed'
            : 'payment.integration_test_completed';
    }

    private function latestSubscription(int $userId): ?object
    {
        return $this->subscriptions()->find()
            ->where(['user_id' => $userId])
            ->orderByDesc('modified')
            ->first();
    }

    private function uniqueReference(string $prefix): string
    {
        do {
            $reference = $prefix . '-' . bin2hex(random_bytes(12));
            $exists = $this->payments()->find()
                ->where([
                    'OR' => [
                        'internal_reference' => $reference,
                        'buy_order' => $reference,
                        'session_id' => $reference,
                    ],
                ])
                ->count() > 0;
        } while ($exists);

        return $reference;
    }

    private function uniqueBuyOrder(): string
    {
        do {
            $buyOrder = self::WEBPAY_BUY_ORDER_PREFIX . rtrim(
                strtr(base64_encode(random_bytes(self::WEBPAY_BUY_ORDER_RANDOM_BYTES)), '+/', '-_'),
                '=',
            );
            $exists = $this->payments()->find()
                ->where(['buy_order' => $buyOrder])
                ->count() > 0;
        } while ($exists);

        return $buyOrder;
    }

    private function providerReferenceForStorage(object $payment, ?string $providerReference): ?string
    {
        $providerReference = $providerReference ?: null;
        if ($providerReference !== null && $providerReference === (string)$payment->gateway_token) {
            return null;
        }

        return $providerReference ?: $payment->provider_reference;
    }

    private function sanitizePayload(array $payload): array
    {
        $clean = [];
        foreach ($payload as $key => $value) {
            $key = (string)$key;
            if (in_array(strtolower($key), self::SECRET_KEYS, true)) {
                $clean[$key] = '[redacted]';
                continue;
            }
            if (is_array($value)) {
                $clean[$key] = $this->sanitizePayload($value);
                continue;
            }
            if (is_scalar($value) || $value === null) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    private function jsonPayload(array $payload): string
    {
        return json_encode($payload ?: new \stdClass(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function cleanString(mixed $value, int $limit): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_substr(preg_replace('/[^\w\-.:]/u', '', (string)$value) ?: '', 0, $limit);
    }

    private function userId(int|array|object $user): int
    {
        if (is_int($user)) {
            return $user;
        }
        if (is_array($user)) {
            return (int)($user['id'] ?? 0);
        }

        return (int)($user->id ?? 0);
    }

    private function payments(): Table
    {
        return FactoryLocator::get('Table')->get('Payments');
    }

    private function subscriptions(): Table
    {
        return FactoryLocator::get('Table')->get('Subscriptions');
    }

    private function plans(): Table
    {
        return FactoryLocator::get('Table')->get('Plans');
    }

    private function users(): Table
    {
        return FactoryLocator::get('Table')->get('Users');
    }
}
