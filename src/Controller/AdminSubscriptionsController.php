<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\SubscriptionService;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;

class AdminSubscriptionsController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string)$this->request->getQuery('q')),
            'status' => (string)$this->request->getQuery('status', ''),
            'plan' => (string)$this->request->getQuery('plan', ''),
        ];
        $query = $this->fetchTable('Subscriptions')->find()->contain(['Users'])->orderByDesc('Subscriptions.modified');
        if ($filters['q'] !== '') {
            $query->leftJoinWith('Users')->where(['OR' => [
                'Users.name LIKE' => '%' . $filters['q'] . '%',
                'Users.email LIKE' => '%' . $filters['q'] . '%',
            ]]);
        }
        if (in_array($filters['status'], ['active', 'expiring', 'grace_period', 'expired', 'suspended', 'cancelled'], true)) {
            $query->where(['Subscriptions.status' => $filters['status']]);
        }
        if ($filters['plan'] !== '') {
            $query->where(['Subscriptions.plan_slug' => $filters['plan']]);
        }
        $pagination = $this->paginateAdmin($query);
        $plans = $this->fetchTable('Plans')->find('list')->orderByAsc('sort_order')->all();
        $this->set(compact('filters', 'pagination', 'plans'));
    }

    public function view(int $id): void
    {
        $subscription = $this->fetchTable('Subscriptions')->get($id, ['contain' => ['Users', 'Payments']]);
        $sites = $this->fetchTable('Sites')->find()->where(['user_id' => $subscription->user_id])->orderByDesc('modified')->all();
        $audits = $this->fetchTable('AuditLogs')->find()
            ->where(['entity' => 'subscriptions', 'entity_id' => $id])->orderByDesc('created')->limit(30)->all();
        $plans = $this->fetchTable('Plans')->find('list')->where(['active' => true])->orderByAsc('sort_order')->all();
        $this->set(compact('subscription', 'sites', 'audits', 'plans'));
    }

    public function extend(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $days = (int)$this->request->getData('days');
        if ($days < 1 || $days > 365) {
            throw new BadRequestException('Indica entre 1 y 365 días.');
        }
        $subscription = $this->fetchTable('Subscriptions')->get($id);
        $reason = $this->adminReason();
        $reference = 'admin-' . $id . '-' . bin2hex(random_bytes(8));
        $service = new SubscriptionService();
        $payment = $service->createManualPayment($subscription, 0, $reference);
        $service->renew($subscription, $payment, $days);
        $this->logAdminAction('admin.subscription.extended', 'subscriptions', $id, [
            'reason' => $reason,
            'days' => $days,
            'payment_id' => (int)$payment->id,
        ]);
        $this->Flash->success('Suscripción extendida y activada.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function suspend(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $subscription = $this->fetchTable('Subscriptions')->get($id);
        (new SubscriptionService())->suspend($subscription);
        $this->logAdminAction('admin.subscription.suspended', 'subscriptions', $id, ['reason' => $this->adminReason()]);
        $this->Flash->success('Suscripción suspendida.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function reactivate(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $subscription = $this->fetchTable('Subscriptions')->get($id);
        (new SubscriptionService())->reactivate($subscription);
        $this->logAdminAction('admin.subscription.reactivated', 'subscriptions', $id, ['reason' => $this->adminReason()]);
        $this->Flash->success('Suscripción reactivada.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function cancel(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $subscription = $this->fetchTable('Subscriptions')->get($id);
        (new SubscriptionService())->cancel($subscription);
        $this->logAdminAction('admin.subscription.cancelled', 'subscriptions', $id, ['reason' => $this->adminReason()]);
        $this->Flash->success('Suscripción cancelada. El contenido se conserva.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function changePlan(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $subscription = $this->fetchTable('Subscriptions')->get($id);
        $planSlug = (string)$this->request->getData('plan_slug');
        if (!$this->fetchTable('Plans')->find()->where(['slug' => $planSlug, 'active' => true])->first()) {
            throw new BadRequestException('Plan inválido o inactivo.');
        }
        (new SubscriptionService())->changePlan($subscription, $planSlug);
        $this->logAdminAction('admin.subscription.plan_changed', 'subscriptions', $id, [
            'reason' => $this->adminReason(),
            'plan_slug' => $planSlug,
        ]);
        $this->Flash->success('Plan actualizado.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function processExpiration(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $subscription = $this->fetchTable('Subscriptions')->get($id);
        $updated = (new SubscriptionService())->processExpiration($subscription);
        $this->logAdminAction('admin.subscription.expiration_processed', 'subscriptions', $id, [
            'reason' => $this->adminReason(),
            'status' => (string)$updated->status,
        ]);
        $this->Flash->success('Vencimiento procesado. Estado actual: ' . $updated->status . '.');

        return $this->redirect(['action' => 'view', $id]);
    }
}
