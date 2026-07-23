<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;

class AdminUsersController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string)$this->request->getQuery('q')),
            'verified' => (string)$this->request->getQuery('verified', ''),
            'role' => (string)$this->request->getQuery('role', ''),
            'subscription' => (string)$this->request->getQuery('subscription', ''),
        ];
        $query = $this->fetchTable('Users')->find()
            ->contain(['Subscriptions'])
            ->orderByDesc('Users.created');
        if ($filters['q'] !== '') {
            $query->where(['OR' => [
                'Users.name LIKE' => '%' . $filters['q'] . '%',
                'Users.email LIKE' => '%' . $filters['q'] . '%',
            ]]);
        }
        if (in_array($filters['verified'], ['0', '1'], true)) {
            $query->where(['Users.email_verified' => $filters['verified'] === '1']);
        }
        if (in_array($filters['role'], ['user', 'admin', 'superadmin'], true)) {
            $query->where(['Users.role' => $filters['role']]);
        }
        if ($filters['subscription'] !== '') {
            $query->matching('Subscriptions', function ($query) use ($filters) {
                return $query->where(['Subscriptions.status' => $filters['subscription']]);
            })->distinct(['Users.id']);
        }

        $pagination = $this->paginateAdmin($query);
        $this->set(compact('filters', 'pagination'));
    }

    public function view(int $id): void
    {
        $user = $this->fetchTable('Users')->get($id, [
            'contain' => ['Sites.Templates', 'Subscriptions', 'Payments'],
        ]);
        $audits = $this->fetchTable('AuditLogs')->find()
            ->where(['OR' => [
                ['entity' => 'users', 'entity_id' => $id],
                ['user_id' => $id],
            ]])
            ->orderByDesc('created')->limit(30)->all();
        $currentSubscription = $this->currentSubscriptionFor($id);
        $usage = [
            'configured' => count($user->sites ?? []),
            'published' => count(array_filter((array)($user->sites ?? []), fn ($site) => $site->status === 'published')),
        ];
        $this->set(compact('user', 'audits', 'currentSubscription', 'usage'));
    }

    public function verify(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $user = $this->fetchTable('Users')->get($id);
        $user->email_verified = true;
        $this->fetchTable('Users')->saveOrFail($user);
        $this->logAdminAction('admin.user.verified', 'users', $id, ['reason' => $this->adminReason()]);
        $this->Flash->success('Usuario verificado manualmente.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function toggleAccess(int $id): Response
    {
        $this->request->allowMethod(['post']);
        if ($id === (int)$this->currentUserId()) {
            throw new BadRequestException('No puedes bloquear tu propia sesión desde este panel.');
        }
        $user = $this->fetchTable('Users')->get($id);
        $user->active = !$user->active;
        $this->fetchTable('Users')->saveOrFail($user);
        $this->logAdminAction($user->active ? 'admin.user.unblocked' : 'admin.user.blocked', 'users', $id, [
            'reason' => $this->adminReason(),
        ]);
        $this->Flash->success($user->active ? 'Acceso habilitado.' : 'Acceso bloqueado.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function changeRole(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $this->requireSuperAdminAction();
        $role = (string)$this->request->getData('role');
        if (!in_array($role, ['user', 'admin', 'superadmin'], true)) {
            throw new BadRequestException('Rol inválido.');
        }
        $user = $this->fetchTable('Users')->get($id);
        $oldRole = (string)$user->role;
        if ($oldRole === 'superadmin' && $role !== 'superadmin' && $this->activeSuperadminCount() <= 1) {
            throw new BadRequestException('Debe existir al menos un superadministrador activo.');
        }
        $user->role = $role;
        $this->fetchTable('Users')->saveOrFail($user);
        $this->logAdminAction('admin.user.role_changed', 'users', $id, [
            'from' => $oldRole,
            'to' => $role,
            'reason' => $this->adminReason(),
        ]);
        $this->Flash->success('Rol actualizado.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function note(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $this->fetchTable('Users')->get($id);
        $this->logAdminAction('admin.user.note_added', 'users', $id, ['note' => $this->adminReason()]);
        $this->Flash->success('Nota administrativa registrada en la auditoría.');

        return $this->redirect(['action' => 'view', $id]);
    }

    private function currentSubscriptionFor(int $userId): ?object
    {
        return $this->fetchTable('Subscriptions')->find()
            ->where(['user_id' => $userId])
            ->orderByDesc('modified')->first();
    }

    private function activeSuperadminCount(): int
    {
        return $this->fetchTable('Users')->find()
            ->where(['role' => 'superadmin', 'active' => true])->count();
    }
}
