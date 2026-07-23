<?php
declare(strict_types=1);

namespace App\Service;

use Cake\I18n\DateTime;
use RuntimeException;

class PaymentReconciliationService
{
    public function __construct(
        private WebpayPlusGatewayInterface $gateway,
        private ?PaymentService $paymentService = null,
    ) {
        $this->paymentService ??= new PaymentService();
    }

    /** @return array{action: string, payment: object} */
    public function reconcile(object $payment, bool $dryRun = false): array
    {
        if ((string)$payment->provider !== PaymentService::PROVIDER || !$payment->gateway_token) {
            throw new RuntimeException('Este pago no puede conciliarse con Webpay.');
        }
        if ($payment->processed_at || in_array((string)$payment->status, [PaymentService::STATUS_PAID, PaymentService::STATUS_CANCELED], true)) {
            return ['action' => 'omitido', 'payment' => $payment];
        }

        $response = $this->gateway->status((string)$payment->gateway_token);
        $response['provider_reference'] = (string)$payment->gateway_token;
        $action = $this->actionFor($payment, $this->gateway->mapInternalStatus($response));
        if ($dryRun || $action === 'omitido') {
            return ['action' => $action, 'payment' => $payment];
        }

        $updated = match ($action) {
            'confirmado' => $this->paymentService->confirm($payment, $response),
            'rechazado' => $this->paymentService->reject($payment, $response),
            'reversado' => $this->paymentService->markReversed($payment, $response),
            'expirado' => $this->paymentService->expirePendingPayment($payment),
            default => $payment,
        };

        return ['action' => $action, 'payment' => $updated];
    }

    private function actionFor(object $payment, string $mappedStatus): string
    {
        return match ($mappedStatus) {
            PaymentService::STATUS_PAID => 'confirmado',
            PaymentService::STATUS_REJECTED => 'rechazado',
            PaymentService::STATUS_REVERSED => 'reversado',
            default => $payment->gateway_expires_at && $payment->gateway_expires_at < DateTime::now() ? 'expirado' : 'omitido',
        };
    }
}
