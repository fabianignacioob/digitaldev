<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PaymentService;
use App\Service\WebpayPlusGateway;
use App\Service\WebpayPlusGatewayInterface;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use function Cake\Core\env;

class PaymentsController extends AppController
{
    public function testPlan(): ?Response
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $this->viewBuilder()->setLayout('dashboard');
        $paymentService = $this->paymentService();
        $testOrder = $paymentService->testOrderConfiguration();
        $enabled = $testOrder['enabled'];

        if (!$this->request->is('post')) {
            $this->set(compact('enabled', 'testOrder'));

            return null;
        }

        $this->request->allowMethod(['post']);
        if (!$enabled) {
            throw new ForbiddenException('La prueba Webpay no está habilitada para este ambiente.');
        }

        $payment = null;
        try {
            $payment = $paymentService->createConfiguredTestOrder((int)$this->currentUserId());
            $transaction = $this->webpayGateway()->createTransaction(
                (string)$payment->buy_order,
                (string)$payment->session_id,
                (int)$payment->expected_amount,
            );
            $payment = $paymentService->recordGatewayTransaction($payment, $transaction);
            $this->set(compact('payment', 'transaction'));
            $this->viewBuilder()->setTemplate('redirect');
        } catch (\Throwable) {
            if ($payment) {
                $paymentService->markGatewaySetupFailed($payment);
            }
            $this->Flash->error('No pudimos iniciar la prueba de Webpay. Revisa la configuración del ambiente.');

            return $this->redirect('/test-plan');
        }

        return null;
    }

    public function create(): ?Response
    {
        $this->request->allowMethod(['post']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $plan = (string)$this->request->getData('plan');
        $billingCycle = (string)$this->request->getData('billing_cycle', 'monthly');
        $payment = null;
        try {
            $payment = $this->paymentService()->createPendingOrder((int)$this->currentUserId(), $plan, $billingCycle);
            $transaction = $this->webpayGateway()->createTransaction(
                (string)$payment->buy_order,
                (string)$payment->session_id,
                (int)$payment->expected_amount,
            );
            $payment = $this->paymentService()->recordGatewayTransaction($payment, $transaction);
            $this->set(compact('payment', 'transaction'));
            $this->viewBuilder()->setTemplate('redirect');

        } catch (\Throwable $exception) {
            if ($payment) {
                $this->paymentService()->markGatewaySetupFailed($payment);
            }
            $this->Flash->error('No pudimos iniciar el pago. Inténtalo nuevamente en unos minutos.');

            return $this->redirect('/planes');
        }

        return null;
    }

    public function result(string $reference): ?Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->viewBuilder()->setLayout('dashboard');

        $payment = $this->paymentService()->paymentByReference($reference);
        if (!$payment || (int)$payment->user_id !== (int)$this->currentUserId()) {
            throw new NotFoundException('Pago no encontrado.');
        }

        $this->set(compact('payment'));

        return null;
    }

    public function mockConfirm(): Response
    {
        $this->request->allowMethod(['post']);
        if (!$this->mockConfirmAllowed()) {
            throw new ForbiddenException('La confirmación mock solo está disponible en desarrollo o test.');
        }
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $reference = (string)$this->request->getData('reference');
        $payment = $this->paymentService()->paymentByReference($reference);
        if (!$payment || (int)$payment->user_id !== (int)$this->currentUserId()) {
            throw new NotFoundException('Pago no encontrado.');
        }

        try {
            if ((string)$this->request->getData('status', 'approved') === 'rejected') {
                $payment = $this->paymentService()->reject($payment, [
                    'error_code' => (string)$this->request->getData('error_code', 'mock_rejected'),
                    'provider_reference' => (string)$this->request->getData('provider_reference', 'mock-' . $reference),
                ]);
            } else {
                $payment = $this->paymentService()->confirm($payment, [
                    'amount' => (int)$payment->expected_amount,
                    'currency' => 'CLP',
                    'buy_order' => (string)$payment->buy_order,
                    'session_id' => (string)$payment->session_id,
                    'provider_reference' => (string)$this->request->getData('provider_reference', 'mock-' . $reference),
                    'authorization_code' => (string)$this->request->getData('authorization_code', 'MOCK123'),
                ]);
            }
            $this->Flash->success('Resultado mock procesado.');

            return $this->redirect(['controller' => 'Payments', 'action' => 'result', $payment->internal_reference]);
        } catch (\Throwable $exception) {
            $this->Flash->error($exception->getMessage());

            return $this->redirect(['controller' => 'Payments', 'action' => 'result', $reference]);
        }
    }

    public function webpayReturn(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('dashboard');
        $this->viewBuilder()->setTemplate('webpay_return');
        $data = array_merge($this->request->getQueryParams(), (array)$this->request->getData());
        $token = trim((string)($data['token_ws'] ?? ''));
        $cancelToken = trim((string)($data['TBK_TOKEN'] ?? ''));
        $buyOrder = trim((string)($data['TBK_ORDEN_COMPRA'] ?? ''));
        $sessionId = trim((string)($data['TBK_ID_SESION'] ?? ''));
        $payment = null;
        $message = 'No pudimos verificar el pago. Inténtalo nuevamente o contacta soporte.';

        if ($cancelToken !== '') {
            $payment = $this->paymentService()->paymentByGatewayToken($cancelToken);
            if ($payment && $this->returnIdentifiersMatch($payment, $buyOrder, $sessionId)) {
                $payment = $this->paymentService()->cancel($payment, [
                    'provider_reference' => $cancelToken,
                    'error_code' => 'webpay_canceled',
                ]);
                $message = 'El pago fue cancelado. Tu suscripción no fue modificada.';
            }
            $this->set(compact('payment', 'message'));

            return null;
        }

        if ($token === '') {
            $payment = $this->paymentService()->paymentByOrderAndSession($buyOrder, $sessionId);
            if ($payment) {
                $payment = $this->paymentService()->cancel($payment, ['error_code' => 'webpay_timeout']);
                $message = 'El tiempo para pagar terminó. Tu suscripción no fue modificada.';
            }
            $this->set(compact('payment', 'message'));

            return null;
        }

        $payment = $this->paymentService()->paymentByGatewayToken($token);
        if (!$payment) {
            $this->set(compact('payment', 'message'));

            return null;
        }
        if ($payment->processed_at) {
            $message = $this->resultMessage((string)$payment->status, $payment);
            $this->set(compact('payment', 'message'));

            return null;
        }

        $paymentService = $this->paymentService();
        $claim = $paymentService->claimGatewayCommit($payment);
        if (!$claim) {
            $payment = $this->paymentService()->paymentByGatewayToken($token) ?? $payment;
            $message = $this->resultMessage((string)$payment->status, $payment);
            $this->set(compact('payment', 'message'));

            return null;
        }
        $payment = $claim['payment'];

        try {
            if ($claim['recovered']) {
                $paymentService->recordGatewayStatusRecoveryEvent($payment, 'payment.gateway_status_recovery_started');
                $response = $this->webpayGateway()->status($token);
                $response['provider_reference'] = $token;
                $recoveryAction = $this->gatewayStatusRecoveryAction($response);

                if ($recoveryAction === 'confirm') {
                    $payment = $paymentService->confirm($payment, $response);
                    $paymentService->recordGatewayStatusRecoveryEvent($payment, 'payment.gateway_status_recovered');
                    $message = $this->resultMessage((string)$payment->status, $payment);
                    $this->set(compact('payment', 'message'));

                    return null;
                }
                if ($recoveryAction === 'reject') {
                    $payment = $paymentService->reject($payment, $response);
                    $paymentService->recordGatewayStatusRecoveryEvent($payment, 'payment.gateway_status_recovered');
                    $message = $this->resultMessage((string)$payment->status, $payment);
                    $this->set(compact('payment', 'message'));

                    return null;
                }
                if ($recoveryAction === 'cancel') {
                    $payment = $paymentService->cancel($payment, $response);
                    $paymentService->recordGatewayStatusRecoveryEvent($payment, 'payment.gateway_status_recovered');
                    $message = $this->resultMessage((string)$payment->status, $payment);
                    $this->set(compact('payment', 'message'));

                    return null;
                }
                if ($recoveryAction === 'reverse') {
                    $payment = $paymentService->markReversed($payment, $response);
                    $paymentService->recordGatewayStatusRecoveryEvent($payment, 'payment.gateway_status_recovered');
                    $message = $this->resultMessage((string)$payment->status, $payment);
                    $this->set(compact('payment', 'message'));

                    return null;
                }
                if ($recoveryAction !== 'commit') {
                    $payment = $paymentService->recordGatewayStatusInconclusive($payment, 'gateway_status_inconclusive');
                    $message = $this->resultMessage((string)$payment->status, $payment);
                    $this->set(compact('payment', 'message'));

                    return null;
                }

                $paymentService->recordGatewayStatusRecoveryEvent($payment, 'payment.gateway_commit_retried');
            }

            $response = $this->webpayGateway()->commit($token);
            $response['provider_reference'] = $token;
            if ($this->webpayGateway()->isApproved($response)) {
                $payment = $paymentService->confirm($payment, $response);
            } else {
                $payment = $paymentService->reject($payment, $response);
            }
            $message = $this->resultMessage((string)$payment->status, $payment);
        } catch (\Throwable) {
            $payment = $claim['recovered']
                ? $paymentService->recordGatewayStatusInconclusive($payment, 'gateway_status_recovery_failed')
                : $paymentService->recordGatewayFailure($payment, 'gateway_commit_failed');
        } finally {
            $payment = $paymentService->releaseGatewayCommit($payment);
        }

        $this->set(compact('payment', 'message'));
        return null;
    }

    private function mockConfirmAllowed(): bool
    {
        $env = strtolower((string)env('APP_ENV', ''));

        return Configure::read('debug') === true || $env === 'test';
    }

    private function paymentService(): PaymentService
    {
        return new PaymentService();
    }

    private function webpayGateway(): WebpayPlusGatewayInterface
    {
        $gateway = Configure::read('Payments.webpayGateway');
        if ($gateway instanceof WebpayPlusGatewayInterface) {
            return $gateway;
        }

        return new WebpayPlusGateway();
    }

    private function returnIdentifiersMatch(object $payment, string $buyOrder, string $sessionId): bool
    {
        return $buyOrder !== ''
            && $sessionId !== ''
            && hash_equals((string)$payment->buy_order, $buyOrder)
            && hash_equals((string)$payment->session_id, $sessionId);
    }

    private function gatewayStatusRecoveryAction(array $response): string
    {
        $status = strtoupper(trim((string)($response['status'] ?? '')));
        if ($this->webpayGateway()->isApproved($response)) {
            return 'confirm';
        }

        return match ($status) {
            'INITIALIZED' => 'commit',
            'FAILED' => 'reject',
            'CANCELED' => 'cancel',
            'REVERSED', 'NULLIFIED', 'PARTIALLY_NULLIFIED' => 'reverse',
            default => 'inconclusive',
        };
    }

    private function resultMessage(string $status, ?object $payment = null): string
    {
        if (
            $status === PaymentService::STATUS_PAID
            && (string)($payment->plan_slug ?? '') === PaymentService::INTEGRATION_TEST_PLAN_SLUG
        ) {
            return 'Pago de prueba aprobado. No se modificó ninguna suscripción.';
        }

        return match ($status) {
            PaymentService::STATUS_PAID => 'Pago aprobado. Tu suscripción fue actualizada.',
            PaymentService::STATUS_REJECTED => 'El pago fue rechazado. Tu suscripción no fue modificada.',
            PaymentService::STATUS_CANCELED => 'El pago fue cancelado. Tu suscripción no fue modificada.',
            PaymentService::STATUS_PENDING, PaymentService::STATUS_AUTHORIZED => 'El pago sigue pendiente de confirmación.',
            default => 'No pudimos verificar el pago. Inténtalo nuevamente o contacta soporte.',
        };
    }
}
