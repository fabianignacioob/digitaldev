<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\DomainAdministrationService;
use Cake\Http\Response;

/**
 * Internal allow-list endpoint for Caddy on-demand TLS. It never exposes
 * domain data and accepts only a shared deployment secret.
 */
class DomainTlsController extends AppController
{
    public function allow(): Response
    {
        $this->request->allowMethod(['get']);
        $configuredToken = (string)env('DOMAIN_TLS_ASK_TOKEN', '');
        $providedToken = (string)$this->request->getQuery('token', '');
        $hostname = (string)$this->request->getQuery('domain', '');

        if ($configuredToken === '' || !hash_equals($configuredToken, $providedToken)) {
            return $this->response->withStatus(403);
        }

        $allowed = (new DomainAdministrationService())->isActiveVerifiedCustomHostname($hostname);

        return $this->response->withStatus($allowed ? 200 : 403);
    }
}
