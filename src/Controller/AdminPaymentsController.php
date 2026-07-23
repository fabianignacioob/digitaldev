<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PaymentReconciliationService;
use App\Service\PaymentService;
use App\Service\WebpayPlusGateway;
use App\Service\WebpayPlusGatewayInterface;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;

class AdminPaymentsController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string)$this->request->getQuery('q')),
            'status' => (string)$this->request->getQuery('status', ''),
            'provider' => (string)$this->request->getQuery('provider', ''),
            'plan' => (string)$this->request->getQuery('plan', ''),
        ];
        $query = $this->fetchTable('Payments')->find()->contain(['Users', 'Subscriptions'])->orderByDesc('Payments.created');
        if ($filters['q'] !== '') {
            $query->leftJoinWith('Users')->where(['OR' => [
                'Payments.internal_reference LIKE' => '%' . $filters['q'] . '%',
                'Payments.buy_order LIKE' => '%' . $filters['q'] . '%',
                'Users.email LIKE' => '%' . $filters['q'] . '%',
            ]]);
        }
        if (in_array($filters['status'], ['pending', 'authorized', 'paid', 'rejected', 'canceled', 'expired', 'refunded', 'reversed', 'failed'], true)) {
            $query->where(['Payments.status' => $filters['status']]);
        }
        if ($filters['provider'] !== '') {
            $query->where(['Payments.provider' => $filters['provider']]);
        }
        if ($filters['plan'] !== '') {
            $query->where(['Payments.plan_slug' => $filters['plan']]);
        }
        $pagination = $this->paginateAdmin($query);
        $plans = $this->fetchTable('Plans')->find('list')->orderByAsc('sort_order')->all();
        $this->set(compact('filters', 'pagination', 'plans'));
    }

    public function view(int $id): void
    {
        $payment = $this->fetchTable('Payments')->get($id, ['contain' => ['Users', 'Subscriptions']]);
        $audits = $this->fetchTable('AuditLogs')->find()
            ->where(['entity' => 'payments', 'entity_id' => $id])->orderByDesc('created')->limit(30)->all();
        $requestPayload = $this->safePayload($payment->request_payload);
        $responsePayload = $this->safePayload($payment->response_payload);
        $this->set(compact('payment', 'audits', 'requestPayload', 'responsePayload'));
    }

    public function reconcile(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $payment = $this->fetchTable('Payments')->get($id);
        $reason = $this->adminReason();
        try {
            $result = (new PaymentReconciliationService($this->gateway()))->reconcile($payment);
            $this->logAdminAction('admin.payment.reconciled', 'payments', $id, [
                'reason' => $reason,
                'result' => $result['action'],
            ]);
            $this->Flash->success('Conciliación ejecutada: ' . $result['action'] . '.');
        } catch (\Throwable $exception) {
            $this->logAdminAction('admin.payment.reconciliation_failed', 'payments', $id, ['reason' => $reason]);
            $this->Flash->error('No se pudo conciliar el pago. Revisa su auditoría y vuelve a intentarlo.');
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    public function cancel(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $payment = $this->fetchTable('Payments')->get($id);
        if (!in_array((string)$payment->status, ['pending', 'authorized'], true)) {
            throw new BadRequestException('Solo se pueden cancelar órdenes pendientes o autorizadas sin procesar.');
        }
        $reason = $this->adminReason();
        (new PaymentService())->cancel($payment, ['error_code' => 'admin_canceled']);
        $this->logAdminAction('admin.payment.cancelled', 'payments', $id, ['reason' => $reason]);
        $this->Flash->success('Orden interna cancelada. La suscripción no fue modificada.');

        return $this->redirect(['action' => 'view', $id]);
    }

    public function markForReview(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $this->fetchTable('Payments')->get($id);
        $this->logAdminAction('admin.payment.marked_for_review', 'payments', $id, ['reason' => $this->adminReason()]);
        $this->Flash->success('Pago marcado para revisión en su auditoría.');

        return $this->redirect(['action' => 'view', $id]);
    }

    private function gateway(): WebpayPlusGatewayInterface
    {
        $gateway = Configure::read('Payments.webpayGateway');

        return $gateway instanceof WebpayPlusGatewayInterface ? $gateway : new WebpayPlusGateway();
    }
}
