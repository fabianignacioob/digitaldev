<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\DomainAdministrationService;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;

class AdminDomainsController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string)$this->request->getQuery('q')),
            'type' => (string)$this->request->getQuery('type', ''),
            'active' => (string)$this->request->getQuery('active', ''),
            'verified' => (string)$this->request->getQuery('verified', ''),
        ];
        $query = $this->fetchTable('Domains')->find()
            ->contain(['Sites.Users'])
            ->orderByDesc('Domains.modified');

        if ($filters['q'] !== '') {
            $query->leftJoinWith('Sites.Users')->where(['OR' => [
                'Domains.domain LIKE' => '%' . $filters['q'] . '%',
                'Sites.name LIKE' => '%' . $filters['q'] . '%',
                'Users.email LIKE' => '%' . $filters['q'] . '%',
            ]])->distinct(['Domains.id']);
        }
        if (in_array($filters['type'], ['subdomain', 'custom'], true)) {
            $query->where(['Domains.type' => $filters['type']]);
        }
        if (in_array($filters['active'], ['0', '1'], true)) {
            $query->where(['Domains.active' => $filters['active'] === '1']);
        }
        if (in_array($filters['verified'], ['0', '1'], true)) {
            $query->where(['Domains.verified' => $filters['verified'] === '1']);
        }

        $pagination = $this->paginateAdmin($query);
        $service = new DomainAdministrationService();
        foreach ($pagination['items'] as $domain) {
            $domain->set('admin_issues', $service->issues($domain), ['guard' => false]);
            $domain->set('admin_public_url', $service->publicUrl($domain), ['guard' => false]);
        }
        $this->set(compact('filters', 'pagination'));
    }

    public function view(int $id): void
    {
        $domain = $this->fetchTable('Domains')->get($id, ['contain' => ['Sites.Users']]);
        $service = new DomainAdministrationService();
        $issues = $service->issues($domain);
        $publicUrl = $service->publicUrl($domain);
        $sites = $this->fetchTable('Sites')->find('list')->orderByAsc('name')->all();
        $audits = $this->fetchTable('AuditLogs')->find()
            ->where(['entity' => 'domains', 'entity_id' => $id])
            ->orderByDesc('created')
            ->limit(30)
            ->all();
        $this->set(compact('domain', 'issues', 'publicUrl', 'sites', 'audits'));
    }

    public function deactivate(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $domain = $this->fetchTable('Domains')->get($id);
        $reason = $this->adminReason();
        (new DomainAdministrationService())->deactivate($domain);
        $this->logAdminAction('admin.domain.deactivated', 'domains', $id, ['reason' => $reason]);
        $this->Flash->success('Dominio desactivado.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function reactivate(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $domain = $this->fetchTable('Domains')->get($id);
        $reason = $this->adminReason();
        try {
            (new DomainAdministrationService())->activate($domain);
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestException($exception->getMessage());
        }
        $this->logAdminAction('admin.domain.reactivated', 'domains', $id, ['reason' => $reason]);
        $this->Flash->success('Dominio reactivado.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function reassign(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $targetSiteId = (int)$this->request->getData('site_id');
        if ($targetSiteId <= 0) {
            throw new BadRequestException('Selecciona un sitio válido.');
        }
        $reason = $this->adminReason();
        $domain = $this->fetchTable('Domains')->get($id);
        $previousSiteId = (int)$domain->site_id;
        try {
            (new DomainAdministrationService())->reassign($domain, $targetSiteId);
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestException($exception->getMessage());
        }
        $this->logAdminAction('admin.domain.reassigned', 'domains', $id, [
            'reason' => $reason,
            'from_site_id' => $previousSiteId,
            'to_site_id' => $targetSiteId,
        ]);
        $this->Flash->success('Asociación de dominio actualizada.');

        return $this->redirect(['action' => 'view', $id]);
    }
}
