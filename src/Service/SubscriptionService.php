<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use RuntimeException;
use function Cake\Core\env;

class SubscriptionService
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRING = 'expiring';
    public const STATUS_GRACE = 'grace_period';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';

    public const SITE_PAUSED_SUBSCRIPTION_EXPIRED = 'subscription_expired';

    private const ACTIVE_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRING,
        self::STATUS_GRACE,
    ];

    public function __construct(private ?AuditLogService $auditLogService = null)
    {
        $this->auditLogService ??= new AuditLogService();
    }

    public function isActive(?object $subscription): bool
    {
        if (!$subscription) {
            return false;
        }

        $status = (string)$subscription->status;
        if (!in_array($status, self::ACTIVE_STATUSES, true)) {
            return false;
        }

        $now = DateTime::now();
        if ($status === self::STATUS_GRACE) {
            return $subscription->grace_ends_at && $this->asDateTime($subscription->grace_ends_at) >= $now;
        }

        return !$subscription->ends_at || $this->asDateTime($subscription->ends_at) >= $now;
    }

    public function getCurrentSubscription(int $userId): ?object
    {
        if (!$userId) {
            return null;
        }

        $subscription = $this->subscriptions()->find()
            ->where([
                'user_id' => $userId,
                'status IN' => self::ACTIVE_STATUSES,
            ])
            ->orderByDesc('modified')
            ->first();

        return $this->isActive($subscription) ? $subscription : null;
    }

    public function renew(object $subscription, object $payment, int $durationDays): object
    {
        if ((string)$payment->status !== 'paid') {
            throw new RuntimeException('Solo se pueden procesar pagos marcados como pagados.');
        }
        if ($payment->processed_at) {
            return $subscription;
        }

        $now = DateTime::now();
        $previousStatus = (string)$subscription->status;
        $currentEnd = $subscription->ends_at ? $this->asDateTime($subscription->ends_at) : null;
        $periodStart = ($currentEnd && $currentEnd > $now) ? $currentEnd : $now;
        $periodEnd = (clone $periodStart)->modify('+' . $durationDays . ' days');

        $subscription->status = self::STATUS_ACTIVE;
        $subscription->starts_at = $subscription->starts_at ?: $now;
        $subscription->ends_at = $periodEnd;
        $subscription->grace_ends_at = null;
        $subscription->notes = 'Renovación registrada por orden ' . (string)$payment->internal_reference;
        $this->subscriptions()->saveOrFail($subscription);

        $payment->subscription_id = (int)$subscription->id;
        $payment->period_start = $periodStart;
        $payment->period_end = $periodEnd;
        $payment->processed_at = $now;
        $this->payments()->saveOrFail($payment);

        $this->reactivateSitesPausedByExpiration((int)$subscription->user_id);
        if ($previousStatus !== self::STATUS_ACTIVE) {
            $this->auditLogService->log((int)$subscription->user_id, 'subscription.activated', 'subscriptions', (int)$subscription->id, [
                'previous_status' => $previousStatus,
                'payment_id' => (int)$payment->id,
            ]);
        }
        $this->auditLogService->log((int)$subscription->user_id, 'subscription.renewed', 'subscriptions', (int)$subscription->id, [
            'payment_id' => (int)$payment->id,
            'period_start' => $periodStart->i18nFormat('yyyy-MM-dd HH:mm:ss'),
            'period_end' => $periodEnd->i18nFormat('yyyy-MM-dd HH:mm:ss'),
            'duration_days' => $durationDays,
        ]);

        return $subscription;
    }

    public function expire(object $subscription): object
    {
        if ((string)$subscription->status === self::STATUS_EXPIRED) {
            return $subscription;
        }

        $subscription->status = self::STATUS_EXPIRED;
        $subscription->last_processed_at = DateTime::now();
        $this->subscriptions()->saveOrFail($subscription);
        $this->pausePublishedSitesForExpiration((int)$subscription->user_id);
        $this->auditLogService->log((int)$subscription->user_id, 'subscription.expired', 'subscriptions', (int)$subscription->id);

        return $subscription;
    }

    public function enterGracePeriod(object $subscription): object
    {
        if ((string)$subscription->status === self::STATUS_GRACE) {
            return $subscription;
        }

        $base = $subscription->ends_at ? $this->asDateTime($subscription->ends_at) : DateTime::now();
        $subscription->status = self::STATUS_GRACE;
        $subscription->grace_ends_at = (clone $base)->modify('+' . $this->graceDays() . ' days');
        $subscription->last_processed_at = DateTime::now();
        $this->subscriptions()->saveOrFail($subscription);
        $this->auditLogService->log((int)$subscription->user_id, 'subscription.grace_period', 'subscriptions', (int)$subscription->id, [
            'grace_ends_at' => $subscription->grace_ends_at->i18nFormat('yyyy-MM-dd HH:mm:ss'),
        ]);

        return $subscription;
    }

    public function reactivate(object $subscription): object
    {
        if (!$this->isActive($subscription)) {
            throw new RuntimeException('La suscripción debe tener fechas vigentes para reactivarse.');
        }

        $subscription->status = self::STATUS_ACTIVE;
        $subscription->grace_ends_at = null;
        $this->subscriptions()->saveOrFail($subscription);
        $this->reactivateSitesPausedByExpiration((int)$subscription->user_id);
        $this->auditLogService->log((int)$subscription->user_id, 'subscription.reactivated', 'subscriptions', (int)$subscription->id);

        return $subscription;
    }

    public function suspend(object $subscription): object
    {
        if ((string)$subscription->status === self::STATUS_SUSPENDED) {
            return $subscription;
        }

        $subscription->status = self::STATUS_SUSPENDED;
        $subscription->last_processed_at = DateTime::now();
        $this->subscriptions()->saveOrFail($subscription);
        $this->auditLogService->log((int)$subscription->user_id, 'subscription.suspended', 'subscriptions', (int)$subscription->id);

        return $subscription;
    }

    public function cancel(object $subscription): object
    {
        if ((string)$subscription->status === self::STATUS_CANCELLED) {
            return $subscription;
        }

        $subscription->status = self::STATUS_CANCELLED;
        $subscription->last_processed_at = DateTime::now();
        $this->subscriptions()->saveOrFail($subscription);
        $this->auditLogService->log((int)$subscription->user_id, 'subscription.cancelled', 'subscriptions', (int)$subscription->id);

        return $subscription;
    }

    public function changePlan(object $subscription, string $planSlug): object
    {
        $planSlug = trim($planSlug);
        if ($planSlug === '') {
            throw new RuntimeException('Debes indicar un plan válido.');
        }

        $previousPlan = (string)$subscription->plan_slug;
        $subscription->plan_slug = $planSlug;
        $this->subscriptions()->saveOrFail($subscription);
        $this->auditLogService->log((int)$subscription->user_id, 'subscription.plan_changed', 'subscriptions', (int)$subscription->id, [
            'from' => $previousPlan,
            'to' => $planSlug,
        ]);

        return $subscription;
    }

    public function processExpiration(object $subscription): object
    {
        $now = DateTime::now();
        $status = (string)$subscription->status;
        $endsAt = $subscription->ends_at ? $this->asDateTime($subscription->ends_at) : null;
        $graceEndsAt = $subscription->grace_ends_at ? $this->asDateTime($subscription->grace_ends_at) : null;

        if ($status === self::STATUS_GRACE && $graceEndsAt && $graceEndsAt < $now) {
            return $this->expire($subscription);
        }
        if (in_array($status, [self::STATUS_ACTIVE, self::STATUS_EXPIRING], true) && $endsAt && $endsAt < $now) {
            $subscription = $this->enterGracePeriod($subscription);
            $graceEndsAt = $subscription->grace_ends_at ? $this->asDateTime($subscription->grace_ends_at) : null;

            return $graceEndsAt && $graceEndsAt < $now ? $this->expire($subscription) : $subscription;
        }

        return $subscription;
    }

    public function markExpiring(object $subscription): object
    {
        if ((string)$subscription->status !== self::STATUS_ACTIVE) {
            return $subscription;
        }

        $subscription->status = self::STATUS_EXPIRING;
        $subscription->last_processed_at = DateTime::now();
        $this->subscriptions()->saveOrFail($subscription);
        $this->auditLogService->log((int)$subscription->user_id, 'subscription.expiring', 'subscriptions', (int)$subscription->id);

        return $subscription;
    }

    public function pausePublishedSitesForExpiration(int $userId): int
    {
        $sites = $this->sites()->find()
            ->where([
                'user_id' => $userId,
                'status' => 'published',
            ]);
        $count = 0;
        foreach ($sites as $site) {
            $site->status = 'paused';
            $site->paused_reason = self::SITE_PAUSED_SUBSCRIPTION_EXPIRED;
            $this->sites()->saveOrFail($site);
            $this->auditLogService->log($userId, 'site.paused_subscription_expired', 'sites', (int)$site->id);
            $count++;
        }

        return $count;
    }

    public function reactivateSitesPausedByExpiration(int $userId): int
    {
        $sites = $this->sites()->find()
            ->where([
                'user_id' => $userId,
                'status' => 'paused',
                'paused_reason' => self::SITE_PAUSED_SUBSCRIPTION_EXPIRED,
            ]);
        $count = 0;
        foreach ($sites as $site) {
            $site->status = 'published';
            $site->paused_reason = null;
            $this->sites()->saveOrFail($site);
            $this->auditLogService->log($userId, 'site.reactivated_subscription_renewed', 'sites', (int)$site->id);
            $count++;
        }

        return $count;
    }

    public function createManualPayment(object $subscription, int $amount, string $reference, ?string $planSlug = null): object
    {
        $now = DateTime::now();
        $payment = $this->payments()->newEntity([
            'user_id' => (int)$subscription->user_id,
            'subscription_id' => (int)$subscription->id,
            'plan_slug' => $planSlug ?: (string)$subscription->plan_slug,
            'status' => 'paid',
            'amount' => $amount,
            'currency' => 'CLP',
            'provider' => 'manual',
            'provider_reference' => $reference,
            'internal_reference' => $reference,
            'buy_order' => $reference,
            'session_id' => $reference,
            'expected_amount' => $amount,
            'confirmed_amount' => $amount,
            'paid_at' => $now,
            'period_start' => $now,
            'period_end' => $now,
            'authorized_at' => $now,
            'confirmed_at' => $now,
            'request_payload' => json_encode(new \stdClass()),
            'response_payload' => json_encode(new \stdClass()),
        ]);
        $this->payments()->saveOrFail($payment);

        return $payment;
    }

    public function renewalDays(): int
    {
        return max(1, (int)env('SUBSCRIPTION_DURATION_DAYS', 30));
    }

    public function graceDays(): int
    {
        return max(0, (int)env('SUBSCRIPTION_GRACE_DAYS', 3));
    }

    public function expiringWindowDays(): int
    {
        return max(1, (int)env('SUBSCRIPTION_EXPIRING_WINDOW_DAYS', 7));
    }

    private function asDateTime(mixed $value): DateTime
    {
        return $value instanceof DateTime ? $value : new DateTime((string)$value);
    }

    private function subscriptions(): Table
    {
        return FactoryLocator::get('Table')->get('Subscriptions');
    }

    private function payments(): Table
    {
        return FactoryLocator::get('Table')->get('Payments');
    }

    private function sites(): Table
    {
        return FactoryLocator::get('Table')->get('Sites');
    }
}
