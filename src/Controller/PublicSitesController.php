<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PublicSiteResolverService;
use App\Service\PublicUrlService;
use Cake\Http\Exception\NotFoundException;

class PublicSitesController extends AppController
{
    public function view(?string $subdomain = null): ?\Cake\Http\Response
    {
        $resolver = new PublicSiteResolverService();
        $urlService = new PublicUrlService();
        $result = $subdomain
            ? $resolver->resolveBySubdomain($subdomain)
            : $resolver->resolveByHost((string)$this->request->host());

        if (($result['isBaseHost'] ?? false) === true) {
            if (($result['isPublicBaseHost'] ?? false) === true) {
                return $this->redirect($urlService->platformUrl(), 301);
            }

            $this->viewBuilder()->setLayout('marketing');
            $plans = $this->fetchTable('Plans')->find()
                ->where(['active' => true])
                ->orderByAsc('sort_order')
                ->all();
            $this->set([
                'page' => 'home',
                'subpage' => null,
                'plans' => $plans,
                'canonicalUrl' => $urlService->platformUrl(),
            ]);

            return $this->render('/Pages/home');
        }

        $site = $result['site'] ?? null;
        $reason = $result['reason'] ?? null;
        if (!$site || in_array($reason, [PublicSiteResolverService::REASON_NOT_FOUND, PublicSiteResolverService::REASON_DRAFT], true)) {
            throw new NotFoundException('Vitrina no encontrada.');
        }

        if ($reason === PublicSiteResolverService::REASON_PAUSED) {
            return $this->response
                ->withStatus(503)
                ->withType('text')
                ->withStringBody('Esta vitrina está pausada temporalmente.');
        }

        if ($reason === PublicSiteResolverService::REASON_EXPIRED) {
            return $this->response
                ->withStatus(503)
                ->withType('text')
                ->withStringBody('Esta vitrina no está disponible porque la suscripción venció.');
        }

        // Keep historical CatOps subdomains and /s/{subdomain} links working
        // without allowing them to create duplicate public content.
        if ($subdomain !== null || ($result['isLegacySubdomain'] ?? false) === true) {
            return $this->redirect($urlService->publicUrl($site), 301);
        }

        $this->viewBuilder()->disableAutoLayout();
        $productPresentation = $this->planService()->publicProductPresentation($site);
        $siteCapabilities = $this->planService()->getCapabilitiesForUser((int)$site->user_id);
        $canonicalUrl = $urlService->publicUrl($site);
        $this->set(compact('site', 'productPresentation', 'siteCapabilities', 'canonicalUrl'));

        if ($this->planService()->templateKind($site) !== 'landing') {
            return $this->render('/PublicSites/catalog');
        }

        return null;
    }
}
