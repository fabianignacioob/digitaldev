<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\ORM\Table;

class DomainProvisioningService
{
    public function __construct(
        private ?DomainProvisioningRunnerInterface $runner = null,
        private ?AuditLogService $auditLogService = null,
    ) {
        $this->runner ??= new ShellDomainProvisioningRunner();
        $this->auditLogService ??= new AuditLogService();
    }

    /** @return array{processed:int,skipped:int,errors:int} */
    public function provisionReadyDomains(int $limit = 25, bool $dryRun = false): array
    {
        $result = ['processed' => 0, 'skipped' => 0, 'errors' => 0];
        $domains = $this->domains()->find()->where(['type' => 'custom', 'status IN' => ['verified', 'provisioning']])
            ->orderByAsc('verification_checked_at')->limit(max(1, min(100, $limit)))->all();
        foreach ($domains as $candidate) {
            if ($dryRun) { $result['processed']++; continue; }
            $domain = $this->claim((int)$candidate->id);
            if (!$domain) { $result['skipped']++; continue; }
            try {
                $run = $this->runner->provision((string)$domain->domain);
                $domain = $this->domains()->get((int)$domain->id);
                $domain->provisioning_summary = $run['summary'];
                if ($run['success']) {
                    $domain->status = 'active'; $domain->active = true; $domain->provisioned_at = DateTime::now(); $domain->provisioning_error = null;
                    $this->domains()->saveOrFail($domain);
                    $this->auditLogService->log(null, 'domain.provisioning_succeeded', 'domains', (int)$domain->id, ['domain' => $domain->domain]);
                    $result['processed']++;
                } else {
                    $domain->status = 'failed'; $domain->active = false; $domain->provisioning_error = $run['summary'];
                    $this->domains()->saveOrFail($domain);
                    $this->auditLogService->log(null, 'domain.provisioning_failed', 'domains', (int)$domain->id, ['domain' => $domain->domain]);
                    $result['errors']++;
                }
            } catch (\Throwable $exception) {
                $domain = $this->domains()->get((int)$domain->id);
                $domain->status = 'failed'; $domain->active = false; $domain->provisioning_error = 'No se pudo ejecutar el provisionador. Revisa el estado operativo.';
                $this->domains()->saveOrFail($domain);
                $this->auditLogService->log(null, 'domain.provisioning_failed', 'domains', (int)$domain->id, ['domain' => $domain->domain]);
                $result['errors']++;
            }
        }
        return $result;
    }

    private function claim(int $id): ?object
    {
        $connection = $this->domains()->getConnection();
        $connection->begin();
        try {
            $query = $this->domains()->find()->where(['id' => $id]);
            // PostgreSQL protects concurrent workers. SQLite is used only by the
            // test suite and does not support FOR UPDATE.
            if (!str_contains($connection->getDriver()::class, 'Sqlite')) {
                $query->epilog('FOR UPDATE');
            }
            $domain = $query->first();
            if (!$domain || !in_array((string)$domain->status, ['verified', 'provisioning'], true)) { $connection->commit(); return null; }
            $staleAfter = DateTime::now()->subMinutes((int)env('DOMAIN_PROVISIONING_LEASE_MINUTES', 15));
            if ($domain->status === 'provisioning' && $domain->provisioning_started_at && $domain->provisioning_started_at > $staleAfter) { $connection->commit(); return null; }
            $domain->status = 'provisioning'; $domain->active = false; $domain->provisioning_started_at = DateTime::now();
            $domain->provisioning_last_attempt_at = DateTime::now(); $domain->provisioning_attempts = (int)$domain->provisioning_attempts + 1;
            $this->domains()->saveOrFail($domain); $connection->commit();
            $this->auditLogService->log(null, 'domain.provisioning_started', 'domains', (int)$domain->id, ['domain' => $domain->domain]);
            return $domain;
        } catch (\Throwable $exception) { $connection->rollback(); throw $exception; }
    }

    private function domains(): Table { return FactoryLocator::get('Table')->get('Domains'); }
}
