<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PublicUrlService;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use Cake\I18n\DateTime;

class AdminSitesController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string)$this->request->getQuery('q')),
            'status' => (string)$this->request->getQuery('status', ''),
            'template' => (string)$this->request->getQuery('template', ''),
        ];
        $query = $this->fetchTable('Sites')->find()
            ->contain(['Users', 'Templates', 'Domains'])
            ->orderByDesc('Sites.modified');
        if ($filters['q'] !== '') {
            $query->leftJoinWith('Users');
            $query->where(['OR' => [
                'Sites.name LIKE' => '%' . $filters['q'] . '%',
                'Sites.subdomain LIKE' => '%' . $filters['q'] . '%',
                'Users.email LIKE' => '%' . $filters['q'] . '%',
            ]])->distinct(['Sites.id']);
        }
        if (in_array($filters['status'], ['draft', 'published', 'paused'], true)) {
            $query->where(['Sites.status' => $filters['status']]);
        }
        if (ctype_digit($filters['template'])) {
            $query->where(['Sites.template_id' => (int)$filters['template']]);
        }
        $pagination = $this->paginateAdmin($query);
        $templates = $this->fetchTable('Templates')->find('list')->orderByAsc('name')->all();
        $this->set(compact('filters', 'pagination', 'templates'));
    }

    public function view(int $id): void
    {
        $site = $this->fetchTable('Sites')->get($id, [
            'contain' => ['Users', 'Templates', 'Themes', 'Domains', 'CatalogCategories', 'CatalogProducts', 'CatalogSettings'],
        ]);
        $subscription = $this->fetchTable('Subscriptions')->find()
            ->where(['user_id' => $site->user_id])->orderByDesc('modified')->first();
        $audits = $this->fetchTable('AuditLogs')->find()
            ->where(['entity' => 'sites', 'entity_id' => $id])->orderByDesc('created')->limit(30)->all();
        $publicUrl = (new PublicUrlService())->publicUrl($site);
        $this->set(compact('site', 'subscription', 'audits', 'publicUrl'));
    }

    public function pause(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $site = $this->fetchTable('Sites')->get($id);
        $reason = $this->adminReason();
        $site->status = 'paused';
        $site->paused_reason = 'manual_admin';
        $this->fetchTable('Sites')->saveOrFail($site);
        $this->logAdminAction('admin.site.paused', 'sites', $id, ['reason' => $reason]);
        $this->Flash->success('Sitio pausado manualmente.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function reactivate(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $site = $this->fetchTable('Sites')->get($id);
        if ($site->status !== 'paused' || $site->paused_reason !== 'manual_admin') {
            throw new BadRequestException('Solo se pueden reactivar sitios pausados manualmente.');
        }
        $reason = $this->adminReason();
        $site->status = 'published';
        $site->paused_reason = null;
        $site->published_at ??= DateTime::now();
        $this->fetchTable('Sites')->saveOrFail($site);
        $this->logAdminAction('admin.site.reactivated', 'sites', $id, ['reason' => $reason]);
        $this->Flash->success('Sitio reactivado.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function unpublish(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $site = $this->fetchTable('Sites')->get($id);
        $reason = $this->adminReason();
        $site->status = 'draft';
        $site->paused_reason = null;
        $this->fetchTable('Sites')->saveOrFail($site);
        $this->logAdminAction('admin.site.unpublished', 'sites', $id, ['reason' => $reason]);
        $this->Flash->success('Sitio despublicado.');

        return $this->redirect(['action' => 'view', $id]);
    }
}
