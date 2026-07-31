<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use InvalidArgumentException;

class DomainAdministrationService
{
    public function __construct(
        private ?PublicUrlService $urlService = null,
        private ?DnsTxtResolver $dnsTxtResolver = null,
        private ?AuditLogService $auditLogService = null,
    )
    {
        $this->urlService ??= new PublicUrlService();
        $this->dnsTxtResolver ??= new DnsTxtResolver();
        $this->auditLogService ??= new AuditLogService();
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

    public function verificationRecordName(object $domain): string
    {
        $prefix = trim((string)env('DOMAIN_VERIFICATION_PREFIX', '_catops-verify'), '.');

        return $prefix . '.' . self::normalizeHostname((string)$domain->domain);
    }

    public function routingCnameTarget(): string
    {
        $target = (string)env('APP_CUSTOM_DOMAIN_CNAME_TARGET', $this->urlService->baseDomain());

        return self::normalizeHostname($target);
    }

    public function routingIpv4(): ?string
    {
        $ip = trim((string)env('APP_CUSTOM_DOMAIN_IPV4', ''));

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ip : null;
    }

    public function canManageCustomDomains(int $userId): bool
    {
        $plans = new PlanService();

        return $plans->hasFeature($userId, 'custom_domain_enabled')
            && $plans->getLimit($userId, 'custom_domains_limit') > 0;
    }

    /** @return array{used:int,limit:int,remaining:int} */
    public function usageForUser(int $userId): array
    {
        $limit = (new PlanService())->getLimit($userId, 'custom_domains_limit');
        $used = $this->domains()->find()
            ->innerJoinWith('Sites')
            ->where(['Sites.user_id' => $userId, 'Domains.type' => 'custom'])
            ->count();

        return ['used' => $used, 'limit' => $limit, 'remaining' => max(0, $limit - $used)];
    }

    public function requestCustomDomain(object $site, int $userId, string $hostname): object
    {
        if ((int)$site->user_id !== $userId) {
            throw new InvalidArgumentException('No puedes configurar dominios para otro sitio.');
        }
        if (!$this->canManageCustomDomains($userId)) {
            throw new InvalidArgumentException('Tu plan actual no incluye dominios propios.');
        }

        $hostname = self::normalizeHostname($hostname);
        if (!self::isValidHostname($hostname)) {
            throw new InvalidArgumentException('Ingresa un dominio válido, sin https:// ni rutas.');
        }
        if ($this->isCatopsHostname($hostname)) {
            throw new InvalidArgumentException('Este hostname pertenece a CatOps. Usa el subdominio asignado al sitio.');
        }
        if ($this->domains()->find()->where(['domain' => $hostname])->count() > 0) {
            throw new InvalidArgumentException('Este dominio ya está asociado a otro sitio.');
        }

        $usage = $this->usageForUser($userId);
        if ($usage['used'] >= $usage['limit']) {
            throw new InvalidArgumentException('Tu plan llegó al límite de dominios propios.');
        }

        $domain = $this->domains()->newEntity([
            'site_id' => (int)$site->id,
            'domain' => $hostname,
            'type' => 'custom',
            'verified' => false,
            'active' => false,
            'verification_method' => 'dns_txt',
            'verification_token' => $this->newVerificationToken(),
            'verification_requested_at' => DateTime::now(),
            'last_dns_error' => null,
        ]);
        $this->domains()->saveOrFail($domain);
        $this->auditLogService->log($userId, 'domain.verification_requested', 'domains', (int)$domain->id, [
            'domain' => $hostname,
            'method' => 'dns_txt',
        ]);

        return $domain;
    }

    public function verifyCustomDomain(object $domain, int $userId): object
    {
        $domain = $this->domains()->get((int)$domain->id, contain: ['Sites']);
        if ((string)$domain->type !== 'custom' || (int)$domain->site->user_id !== $userId) {
            throw new InvalidArgumentException('No puedes verificar este dominio.');
        }

        $domain->verification_checked_at = DateTime::now();
        try {
            $records = $this->dnsTxtResolver->records($this->verificationRecordName($domain));
        } catch (\Throwable $exception) {
            $domain->last_dns_error = 'No se pudo consultar el registro TXT. Intenta nuevamente en unos minutos.';
            $this->domains()->saveOrFail($domain);
            throw new InvalidArgumentException($domain->last_dns_error);
        }

        if (!in_array((string)$domain->verification_token, $records, true)) {
            $domain->last_dns_error = 'Aún no encontramos el valor TXT indicado. Revisa el nombre y espera la propagación DNS.';
            $this->domains()->saveOrFail($domain);
            throw new InvalidArgumentException($domain->last_dns_error);
        }

        $domain->verified = true;
        $domain->active = true;
        $domain->verified_at = DateTime::now();
        $domain->last_dns_error = null;
        $this->domains()->saveOrFail($domain);
        $this->auditLogService->log($userId, 'domain.verified', 'domains', (int)$domain->id, [
            'domain' => (string)$domain->domain,
            'method' => 'dns_txt',
        ]);

        return $domain;
    }

    public function removeCustomDomain(object $domain, int $userId): void
    {
        $domain = $this->domains()->get((int)$domain->id, contain: ['Sites']);
        if ((string)$domain->type !== 'custom' || (int)$domain->site->user_id !== $userId) {
            throw new InvalidArgumentException('No puedes eliminar este dominio.');
        }

        $domainId = (int)$domain->id;
        $hostname = (string)$domain->domain;
        $this->domains()->deleteOrFail($domain);
        $this->auditLogService->log($userId, 'domain.removed', 'domains', $domainId, ['domain' => $hostname]);
    }

    public function isActiveVerifiedCustomHostname(string $hostname): bool
    {
        $hostname = self::normalizeHostname($hostname);
        if (!self::isValidHostname($hostname) || $this->isCatopsHostname($hostname)) {
            return false;
        }

        return $this->domains()->find()
            ->where([
                'domain' => $hostname,
                'type' => 'custom',
                'verified' => true,
                'active' => true,
            ])
            ->count() === 1;
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

    private function isCatopsHostname(string $hostname): bool
    {
        $baseDomain = $this->urlService->baseDomain();

        return $hostname === $baseDomain || str_ends_with($hostname, '.' . $baseDomain);
    }

    private function newVerificationToken(): string
    {
        return 'catops-verification=' . bin2hex(random_bytes(20));
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
