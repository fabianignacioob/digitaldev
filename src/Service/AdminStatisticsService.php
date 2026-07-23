<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;

class AdminStatisticsService
{
    public function dashboard(): array
    {
        $now = DateTime::now();
        $sevenDays = (clone $now)->modify('-7 days');
        $thirtyDays = (clone $now)->modify('-30 days');
        $monthStart = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);

        $users = $this->table('Users');
        $sites = $this->table('Sites');
        $subscriptions = $this->table('Subscriptions');
        $payments = $this->table('Payments');
        $plans = $this->table('Plans');

        $siteStatuses = $this->countBy($sites, 'status');
        $subscriptionStatuses = $this->countBy($subscriptions, 'status');
        $paymentStatuses = $this->countBy($payments, 'status');
        $planClients = $this->countBy($subscriptions, 'plan_slug', ['status IN' => ['active', 'expiring', 'grace_period']]);
        $incomeByPlan = $this->sumBy($payments, 'plan_slug', [
            'status' => 'paid',
            'paid_at >=' => $monthStart,
        ]);

        return [
            'users' => [
                'total' => $users->find()->count(),
                'verified' => $users->find()->where(['email_verified' => true])->count(),
                'last7' => $users->find()->where(['created >=' => $sevenDays])->count(),
                'last30' => $users->find()->where(['created >=' => $thirtyDays])->count(),
            ],
            'sites' => $siteStatuses + [
                'configured' => $sites->find()->count(),
                'paused_expired' => $sites->find()->where(['status' => 'paused', 'paused_reason' => SubscriptionService::SITE_PAUSED_SUBSCRIPTION_EXPIRED])->count(),
            ],
            'subscriptions' => $subscriptionStatuses + [
                'expiring_soon' => $subscriptions->find()->where([
                    'status IN' => ['active', 'expiring'],
                    'ends_at >=' => $now,
                    'ends_at <=' => (clone $now)->modify('+7 days'),
                ])->count(),
            ],
            'payments' => $paymentStatuses + [
                'reconciliation' => $payments->find()->where([
                    'status IN' => ['pending', 'authorized'],
                    'gateway_token IS NOT' => null,
                ])->count(),
                'income_month' => (int)($payments->find()->select([
                    'amount' => $payments->find()->func()->sum('confirmed_amount'),
                ])->where(['status' => 'paid', 'paid_at >=' => $monthStart])->first()?->amount ?? 0),
                'by_plan' => $incomeByPlan,
            ],
            'plan_clients' => $planClients,
            'plans' => $plans->find()->where(['active' => true])->orderByAsc('sort_order')->all(),
            'recent_users' => $users->find()->orderByDesc('Users.created')->limit(6)->all(),
            'recent_payments' => $payments->find()->contain(['Users'])->orderByDesc('Payments.created')->limit(6)->all(),
            'recent_sites' => $sites->find()->contain(['Users'])->where(['Sites.status' => 'published'])->orderByDesc('Sites.published_at')->limit(6)->all(),
            'critical_audits' => $this->table('AuditLogs')->find()->contain(['Users'])
                ->where(['OR' => [
                    'action LIKE' => 'admin.%',
                    'action LIKE' => 'payment.%',
                    'action LIKE' => 'subscription.%',
                    'action LIKE' => 'site.paused%',
                ]])
                ->orderByDesc('AuditLogs.created')->limit(8)->all(),
        ];
    }

    private function countBy(object $table, string $field, array $conditions = []): array
    {
        $query = $table->find()
            ->select([$field, 'total' => $table->find()->func()->count('*')])
            ->where($conditions)
            ->groupBy([$field])
            ->enableHydration(false);
        $result = [];
        foreach ($query as $row) {
            $result[(string)$row[$field]] = (int)$row['total'];
        }

        return $result;
    }

    private function sumBy(object $table, string $field, array $conditions = []): array
    {
        $query = $table->find()
            ->select([$field, 'total' => $table->find()->func()->sum('confirmed_amount')])
            ->where($conditions)
            ->groupBy([$field])
            ->enableHydration(false);
        $result = [];
        foreach ($query as $row) {
            $result[(string)$row[$field]] = (int)$row['total'];
        }

        return $result;
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
