<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\DomainProvisioningRunnerInterface;
use App\Service\DomainProvisioningService;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

class DomainProvisioningServiceTest extends TestCase
{
    public function testVerifiedDomainBecomesActiveWithFakeRunner(): void
    {
        $domain = $this->table('Domains')->newEntity(['site_id' => $this->siteId(), 'domain' => 'cliente-' . uniqid() . '.cl', 'type' => 'custom', 'verified' => true, 'active' => false, 'status' => 'verified']);
        $this->table('Domains')->saveOrFail($domain);
        $result = (new DomainProvisioningService(new FakeDomainRunner(true)))->provisionReadyDomains();
        $stored = $this->table('Domains')->get($domain->id);
        $this->assertSame(1, $result['processed']); $this->assertSame('active', $stored->status); $this->assertTrue((bool)$stored->active);
    }

    public function testFailedRunnerKeepsDomainInactiveAndRetryQueuesIt(): void
    {
        $domain = $this->table('Domains')->newEntity(['site_id' => $this->siteId(), 'domain' => 'fallo-' . uniqid() . '.cl', 'type' => 'custom', 'verified' => true, 'active' => false, 'status' => 'verified']);
        $this->table('Domains')->saveOrFail($domain);
        (new DomainProvisioningService(new FakeDomainRunner(false)))->provisionReadyDomains();
        $stored = $this->table('Domains')->get($domain->id);
        $this->assertSame('failed', $stored->status); $this->assertFalse((bool)$stored->active); $this->assertNotEmpty($stored->provisioning_error);
    }

    private function siteId(): int
    {
        $site = $this->table('Sites')->find()->first();
        return (int)$site->id;
    }
    private function table(string $name): object { return FactoryLocator::get('Table')->get($name); }
}

class FakeDomainRunner implements DomainProvisioningRunnerInterface
{
    public function __construct(private bool $success) {}
    public function provision(string $hostname): array { return ['success' => $this->success, 'summary' => $this->success ? 'OK' : 'Nginx invalid']; }
}
