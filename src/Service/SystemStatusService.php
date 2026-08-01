<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Table;

class SystemStatusService
{
    public function snapshot(): array
    {
        $connection = ConnectionManager::get('default');
        $database = ['connected' => false, 'schema' => (string)($connection->config()['schema'] ?? 'public')];
        try {
            $connection->execute('SELECT 1')->fetchAll('assoc');
            $database['connected'] = true;
        } catch (\Throwable) {
            $database['connected'] = false;
        }

        $uploads = WWW_ROOT . 'uploads';
        $latestRuns = (new OperationalProcessRunService())->latestByProcess([
            'subscriptions.process_expirations',
            'subscriptions.reminders',
            'payments.reconcile',
        ]);

        $urlService = new PublicUrlService();

        return [
            'application' => [
                'environment' => (string)env('APP_ENV', Configure::read('debug') ? 'development' : 'production'),
                'debug' => (bool)Configure::read('debug'),
                'version' => (string)env('APP_VERSION', 'local'),
                'php_version' => PHP_VERSION,
                'cakephp_version' => Configure::version(),
                'platform_domain' => $urlService->platformDomain(),
                'public_base_domain' => $urlService->publicBaseDomain(),
                'scheme' => $urlService->scheme(),
                'webpay_environment' => (string)env('WEBPAY_ENV', 'integration'),
            ],
            'database' => $database,
            'processes' => $latestRuns,
            'metrics' => [
                'payments_pending' => $this->payments()->find()->where(['status IN' => ['pending', 'authorized']])->count(),
                'payments_reconcile_failed' => $this->payments()->find()->where(['error_code LIKE' => 'gateway_reconcile%'])->count(),
                'subscriptions_unprocessed' => $this->subscriptions()->find()->where([
                    'OR' => [
                        ['status IN' => ['active', 'expiring'], 'ends_at <' => date('Y-m-d H:i:s')],
                        ['status' => 'grace_period', 'grace_ends_at <' => date('Y-m-d H:i:s')],
                    ],
                ])->count(),
                'sites_paused_expired' => $this->sites()->find()
                    ->where(['status' => 'paused', 'paused_reason' => SubscriptionService::SITE_PAUSED_SUBSCRIPTION_EXPIRED])
                    ->count(),
            ],
            'storage' => [
                'driver' => 'local',
                'path' => 'webroot/uploads',
                'uploads_exists' => is_dir($uploads),
                'uploads_writable' => is_writable(is_dir($uploads) ? $uploads : dirname($uploads)),
                'webroot_writable' => is_writable(WWW_ROOT),
                'logs_writable' => is_writable(LOGS),
                'tmp_writable' => is_writable(TMP),
                'free_bytes' => disk_free_space(WWW_ROOT) ?: null,
            ],
        ];
    }

    private function payments(): Table
    {
        return FactoryLocator::get('Table')->get('Payments');
    }

    private function subscriptions(): Table
    {
        return FactoryLocator::get('Table')->get('Subscriptions');
    }

    private function sites(): Table
    {
        return FactoryLocator::get('Table')->get('Sites');
    }
}
