<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PublicSiteResolverService;
use Cake\Http\Exception\NotFoundException;

class PublicSitesController extends AppController
{
    public function view(?string $subdomain = null): ?\Cake\Http\Response
    {
        $resolver = new PublicSiteResolverService();
        $result = $subdomain
            ? $resolver->resolveBySubdomain($subdomain)
            : $resolver->resolveByHost((string)$this->request->host());

        if (($result['isBaseHost'] ?? false) === true) {
            $this->viewBuilder()->disableAutoLayout();
            $plans = $this->fetchTable('Plans')->find()
                ->where(['active' => true])
                ->orderByAsc('sort_order')
                ->all();
            $this->set(['page' => 'home', 'subpage' => null, 'plans' => $plans]);

            return $this->render('/Pages/home');
        }

        $site = $result['site'] ?? null;
        $reason = $result['reason'] ?? null;
        if (!$site || in_array($reason, [PublicSiteResolverService::REASON_NOT_FOUND, PublicSiteResolverService::REASON_DRAFT], true)) {
            throw new NotFoundException('Sitio no encontrado.');
        }

        if ($reason === PublicSiteResolverService::REASON_PAUSED) {
            return $this->response
                ->withStatus(503)
                ->withType('text')
                ->withStringBody('Este sitio está pausado temporalmente.');
        }

        if ($reason === PublicSiteResolverService::REASON_EXPIRED) {
            return $this->response
                ->withStatus(503)
                ->withType('text')
                ->withStringBody('Este sitio no está disponible porque la suscripción venció.');
        }

        $this->viewBuilder()->disableAutoLayout();
        $this->set(compact('site'));

        if (in_array($site->template->slug ?? '', ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'], true)) {
            return $this->render('/PublicSites/catalog');
        }

        return null;
    }
}
