<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

class PublicUrlService
{
    /**
     * Institutional domain for CatOps: authentication, panel, payments and QR redirects.
     */
    public function platformDomain(): string
    {
        $configured = trim((string)env('APP_PLATFORM_DOMAIN', ''));
        if ($configured !== '') {
            return $this->normalizeHost($configured);
        }

        $fullBaseUrl = (string)Configure::read('App.fullBaseUrl', '');
        $host = parse_url($fullBaseUrl, PHP_URL_HOST);
        if (is_string($host) && $host !== '') {
            return $this->normalizeHost($host);
        }

        $legacy = trim((string)env('APP_BASE_DOMAIN', ''));

        return $legacy !== '' ? $this->normalizeHost($legacy) : 'catops.cl';
    }

    /**
     * Public domain for customer vitrinas. APP_BASE_DOMAIN remains a backwards-compatible
     * fallback for existing local and staging environments during the transition.
     */
    public function publicBaseDomain(): string
    {
        $configured = trim((string)env('APP_PUBLIC_BASE_DOMAIN', ''));
        if ($configured !== '') {
            return $this->normalizeHost($configured);
        }

        $legacy = trim((string)env('APP_BASE_DOMAIN', ''));

        return $legacy !== '' ? $this->normalizeHost($legacy) : 'vitrinahub.cl';
    }

    /**
     * @deprecated Use publicBaseDomain(). Kept for existing services during the transition.
     */
    public function baseDomain(): string
    {
        return $this->publicBaseDomain();
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
        $baseDomain = $this->publicBaseDomain();
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

    public function legacySubdomainFromHost(string $host): string|false|null
    {
        return $this->subdomainForBaseDomain($host, $this->platformDomain());
    }

    public function isPlatformHost(string $host): bool
    {
        $host = $this->normalizeHost($host);
        $platform = $this->platformDomain();

        return $host === $platform || $host === 'www.' . $platform;
    }

    public function isPublicBaseHost(string $host): bool
    {
        return $this->normalizeHost($host) === $this->publicBaseDomain();
    }

    public function hostForSubdomain(string $subdomain): string
    {
        return strtolower($subdomain) . '.' . $this->publicBaseDomain();
    }

    public function legacyHostForSubdomain(string $subdomain): string
    {
        return strtolower($subdomain) . '.' . $this->platformDomain();
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

        return $this->platformUrl('/q/' . $token);
    }

    public function platformUrl(string $path = ''): string
    {
        return $this->scheme() . '://' . $this->platformDomain() . '/' . ltrim($path, '/');
    }

    public function preferredHostForSite(object $site): string
    {
        foreach ((array)($site->domains ?? []) as $domain) {
            if ((string)($domain->type ?? '') === 'custom' && (bool)($domain->verified ?? false) && (bool)($domain->active ?? false) && (string)($domain->status ?? '') === 'active') {
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
                'status' => 'active',
            ])
            ->orderByAsc('id')
            ->first();

        return $customDomain
            ? $this->normalizeHost((string)$customDomain->domain)
            : $this->hostForSubdomain((string)$site->subdomain);
    }

    private function subdomainForBaseDomain(string $host, string $baseDomain): string|false|null
    {
        $host = $this->normalizeHost($host);
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
}
