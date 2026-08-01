<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\ORM\Query\SelectQuery;

class PublicSiteResolverService
{
    public const REASON_NOT_FOUND = 'not_found';
    public const REASON_DRAFT = 'draft';
    public const REASON_PAUSED = 'paused';
    public const REASON_EXPIRED = 'expired';

    public function __construct(
        private ?SubscriptionService $subscriptionService = null,
        private ?PublicUrlService $urlService = null,
    ) {
        $this->subscriptionService ??= new SubscriptionService();
        $this->urlService ??= new PublicUrlService();
    }

    public function resolveBySubdomain(string $subdomain): array
    {
        return $this->resolveQuery($this->baseSiteQuery()->where(['Sites.subdomain' => strtolower($subdomain)]));
    }

    public function resolveByHost(string $host): array
    {
        $host = $this->urlService->normalizeHost($host);
        if ($this->urlService->isPlatformHost($host)) {
            return ['site' => null, 'reason' => null, 'isBaseHost' => true, 'isPlatformHost' => true];
        }

        if ($this->urlService->isPublicBaseHost($host)) {
            return ['site' => null, 'reason' => null, 'isBaseHost' => true, 'isPublicBaseHost' => true];
        }

        $subdomain = $this->urlService->subdomainFromHost($host);
        if (is_string($subdomain)) {
            return $this->resolveBySubdomain($subdomain) + ['isBaseHost' => false, 'isLegacySubdomain' => false];
        }

        $legacySubdomain = $this->urlService->legacySubdomainFromHost($host);
        if (is_string($legacySubdomain)) {
            return $this->resolveBySubdomain($legacySubdomain) + ['isBaseHost' => false, 'isLegacySubdomain' => true];
        }

        if ($subdomain === false && $legacySubdomain === false) {
            $domain = FactoryLocator::get('Table')->get('Domains')->find()
                ->select(['site_id'])
                ->where([
                    'domain' => DomainAdministrationService::normalizeHostname($host),
                    'type' => 'custom',
                    'verified' => true,
                    'active' => true,
                ])
                ->first();

            if (!$domain) {
                return ['site' => null, 'reason' => self::REASON_NOT_FOUND, 'isBaseHost' => false];
            }

            return $this->resolveQuery($this->baseSiteQuery()->where(['Sites.id' => (int)$domain->site_id])) + ['isLegacySubdomain' => false];
        }

        return ['site' => null, 'reason' => self::REASON_NOT_FOUND, 'isBaseHost' => false];
    }

    private function resolveQuery(SelectQuery $query): array
    {
        $site = $query->first();
        if (!$site) {
            return ['site' => null, 'reason' => self::REASON_NOT_FOUND, 'isBaseHost' => false];
        }

        if ($site->status === 'draft') {
            return ['site' => $site, 'reason' => self::REASON_DRAFT, 'isBaseHost' => false];
        }

        if ($site->status === 'paused') {
            return ['site' => $site, 'reason' => self::REASON_PAUSED, 'isBaseHost' => false];
        }

        if (!$this->subscriptionService->getCurrentSubscription((int)$site->user_id)) {
            return ['site' => $site, 'reason' => self::REASON_EXPIRED, 'isBaseHost' => false];
        }

        if ($site->status !== 'published') {
            return ['site' => $site, 'reason' => self::REASON_NOT_FOUND, 'isBaseHost' => false];
        }

        return ['site' => $site, 'reason' => null, 'isBaseHost' => false];
    }

    private function baseSiteQuery(): SelectQuery
    {
        return FactoryLocator::get('Table')->get('Sites')->find()
            ->contain([
                'Templates',
                'Themes',
                'CatalogSettings',
                'CatalogCategories.CatalogProducts.MeasurementTypes',
                'CatalogCategories.CatalogProducts.CatalogProductVariants',
                'CatalogProducts.MeasurementTypes',
                'CatalogProducts.CatalogProductVariants',
                'SiteSections' => function ($q) {
                    return $q->where(['SiteSections.visible' => true])
                        ->orderByAsc('SiteSections.sort_order');
                },
            ]);
    }
}
