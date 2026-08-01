<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;

class PublicUrlService
{
    public function baseDomain(): string
    {
        return $this->normalizeHost((string)env('APP_BASE_DOMAIN', 'catops.cl'));
    }

    public function scheme(): string
    {
        return (string)env('APP_PUBLIC_SCHEME', 'https');
    }

    public function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('/:\d+$/', '', $host) ?? $host;

        return trim($host, '.');
    }

    public function subdomainFromHost(string $host): string|false|null
    {
        $host = $this->normalizeHost($host);
        $baseDomain = $this->baseDomain();
        if ($host === $baseDomain || $host === 'localhost' || $host === '127.0.0.1') {
            return null;
        }

        $suffix = '.' . $baseDomain;
        if (!str_ends_with($host, $suffix)) {
            return false;
        }

        $subdomain = substr($host, 0, -strlen($suffix));
        if ($subdomain === '' || str_contains($subdomain, '.')) {
            return false;
        }

        return $subdomain;
    }

    public function hostForSubdomain(string $subdomain): string
    {
        return strtolower($subdomain) . '.' . $this->baseDomain();
    }

    public function publicUrl(object $site): string
    {
        return $this->scheme() . '://' . $this->preferredHostForSite($site);
    }

    public function qrUrl(string $token): string
    {
        if (!preg_match('/^[a-z0-9]{24,64}$/', $token)) {
            throw new \InvalidArgumentException('El identificador público del código QR no es válido.');
        }

        return $this->scheme() . '://' . $this->baseDomain() . '/q/' . $token;
    }

    public function preferredHostForSite(object $site): string
    {
        foreach ((array)($site->domains ?? []) as $domain) {
            if ((string)($domain->type ?? '') === 'custom' && (bool)($domain->verified ?? false) && (bool)($domain->active ?? false)) {
                return $this->normalizeHost((string)$domain->domain);
            }
        }

        $customDomain = FactoryLocator::get('Table')->get('Domains')->find()
            ->select(['domain'])
            ->where([
                'site_id' => (int)$site->id,
                'type' => 'custom',
                'verified' => true,
                'active' => true,
            ])
            ->orderByAsc('id')
            ->first();

        return $customDomain
            ? $this->normalizeHost((string)$customDomain->domain)
            : $this->hostForSubdomain((string)$site->subdomain);
    }
}
