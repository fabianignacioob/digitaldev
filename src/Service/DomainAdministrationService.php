<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\ORM\Table;
use InvalidArgumentException;

class DomainAdministrationService
{
    public function __construct(private ?PublicUrlService $urlService = null)
    {
        $this->urlService ??= new PublicUrlService();
    }

    public static function normalizeHostname(string $hostname): string
    {
        return strtolower(rtrim(trim($hostname), '.'));
    }

    public static function isValidHostname(string $hostname): bool
    {
        if ($hostname === '' || mb_strlen($hostname) > 180 || str_contains($hostname, '://') || str_contains($hostname, '/')) {
            return false;
        }

        $labels = explode('.', $hostname);
        if (count($labels) < 2) {
            return false;
        }

        foreach ($labels as $label) {
            if ($label === '' || strlen($label) > 63 || !preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $label)) {
                return false;
            }
        }

        return true;
    }

    public function publicUrl(object $domain): string
    {
        return $this->urlService->scheme() . '://' . self::normalizeHostname((string)$domain->domain);
    }

    /** @return list<string> */
    public function issues(object $domain): array
    {
        $issues = [];
        $hostname = self::normalizeHostname((string)$domain->domain);
        if (!self::isValidHostname($hostname)) {
            $issues[] = 'Hostname inválido';
        }

        $type = (string)$domain->type;
        if (!in_array($type, ['subdomain', 'custom'], true)) {
            $issues[] = 'Tipo de dominio no reconocido';
        }

        $site = $domain->site ?? $this->sites()->find()->where(['id' => $domain->site_id])->first();
        if (!$site) {
            $issues[] = 'Sitio asociado inexistente';
        } elseif ($type === 'subdomain') {
            $expected = $this->urlService->hostForSubdomain((string)$site->subdomain);
            if ($hostname !== $expected) {
                $issues[] = 'No coincide con el subdominio del sitio';
            }
            if ($this->isReservedHostname($hostname)) {
                $issues[] = 'Subdominio reservado';
            }
        }

        if ((int)$this->domains()->find()
            ->where(['domain' => $hostname, 'id !=' => (int)$domain->id])
            ->count() > 0) {
            $issues[] = 'Hostname duplicado';
        }

        if ((bool)$domain->active && !(bool)$domain->verified) {
            $issues[] = 'Un dominio activo debe estar verificado';
        }

        return $issues;
    }

    public function activate(object $domain): object
    {
        $domain = $this->domains()->get((int)$domain->id, contain: ['Sites']);
        $issues = $this->issues($domain);
        if ($issues !== []) {
            throw new InvalidArgumentException('No se puede activar el dominio: ' . implode(', ', $issues) . '.');
        }

        $domain->active = true;
        $this->domains()->saveOrFail($domain);

        return $domain;
    }

    public function deactivate(object $domain): object
    {
        $domain->active = false;
        $this->domains()->saveOrFail($domain);

        return $domain;
    }

    public function reassign(object $domain, int $siteId): object
    {
        $domain = $this->domains()->get((int)$domain->id);
        $site = $this->sites()->get($siteId);
        if ((int)$domain->site_id === $siteId) {
            throw new InvalidArgumentException('El dominio ya está asociado a este sitio.');
        }

        if ((string)$domain->type === 'subdomain') {
            $expected = $this->urlService->hostForSubdomain((string)$site->subdomain);
            if (self::normalizeHostname((string)$domain->domain) !== $expected) {
                throw new InvalidArgumentException('Un subdominio solo puede asociarse al sitio que coincide con su hostname.');
            }
        }

        $domain->site_id = $siteId;
        $this->domains()->saveOrFail($domain);

        return $domain;
    }

    private function isReservedHostname(string $hostname): bool
    {
        $suffix = '.' . $this->urlService->baseDomain();
        if (!str_ends_with($hostname, $suffix)) {
            return false;
        }

        $subdomain = substr($hostname, 0, -strlen($suffix));

        return $subdomain === '' || str_contains($subdomain, '.') || (new PlanService())->isReservedSubdomain($subdomain);
    }

    private function domains(): Table
    {
        return FactoryLocator::get('Table')->get('Domains');
    }

    private function sites(): Table
    {
        return FactoryLocator::get('Table')->get('Sites');
    }
}
