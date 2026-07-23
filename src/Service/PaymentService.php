<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use RuntimeException;
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
    ) {
        $this->subscriptionService ??= new SubscriptionService();
        $this->auditLogService ??= new AuditLogService();
    }

    public function createPendingOrder(int|array|object $user, string|object $plan): object
    {
        $userId = $this->userId($user);
        $plan = is_object($plan) ? $plan : $this->planBySlug((string)$plan);
        if (!$userId) {
            throw new RuntimeException('Usuario inválido para crear la orden de pago.');
        }
        if (!$plan || !(bool)$plan->active) {
            throw new RuntimeException('Plan inválido o inactivo.');
        }

        $currentSubscription = $this->latestSubscription($userId);
        $this->assertUpgradePolicy($currentSubscription, $plan);

        $amount = (int)$plan->monthly_price;
        $internalReference = $this->uniqueReference('pay');
        $buyOrder = $this->uniqueBuyOrder();
        $sessionId = $this->uniqueReference('sess');
        $now = DateTime::now();
        $payload = $this->sanitizePayload([
            'plan_slug' => (string)$plan->slug,
            'amount' => $amount,
            'currency' => self::CURRENCY,
            'buy_order' => $buyOrder,
            'session_id' => $sessionId,
        ]);

        $payment = $this->payments()->newEntity([
            'user_id' => $userId,
            'subscription_id' => $currentSubscription?->id,
            'plan_slug' => (string)$plan->slug,
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
            'internal_reference' => $internalReference,
        ]);

        return $payment;
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
            'environment' => strtolower((string)env('WEBPAY_ENV', 'integration')),
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

        return $connection->transactional(function () use ($payment, $providerResponse) {
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

            $subscription = $this->subscriptionForPayment($payment);
            $subscription->plan_slug = (string)$payment->plan_slug;
            $this->subscriptions()->saveOrFail($subscription);

            $payment->status = self::STATUS_PAID;
            $payment->paid_at = $now;
            $payment->confirmed_at = $now;
            $this->payments()->saveOrFail($payment);

            $this->subscriptionService->renew($subscription, $payment, $this->subscriptionService->renewalDays());
            $payment = $this->payments()->get($payment->id);
            $this->auditLogService->log((int)$payment->user_id, 'payment.paid', 'payments', (int)$payment->id);
            $this->auditLogService->log((int)$payment->user_id, 'payment.subscription_renewed', 'payments', (int)$payment->id, [
                'subscription_id' => (int)$subscription->id,
                'plan_slug' => (string)$payment->plan_slug,
            ]);

            return $payment;
        });
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
}
