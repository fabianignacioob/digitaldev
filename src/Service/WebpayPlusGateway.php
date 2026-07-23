<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use RuntimeException;
use Transbank\Webpay\WebpayPlus\Transaction;

class WebpayPlusGateway implements WebpayPlusGatewayInterface
{
    private array $config;

    /** @var callable|null */
    private $transactionFactory;

    public function __construct(?array $config = null, ?callable $transactionFactory = null)
    {
        $configured = (array)Configure::read('Payments.webpay', []);
        $this->config = array_merge([
            'environment' => 'integration',
            'commerceCode' => '',
            'apiKey' => '',
            'returnUrl' => '',
            'timeoutSeconds' => 20,
        ], $configured, $config ?? []);
        $this->transactionFactory = $transactionFactory;
    }

    public function createTransaction(string $buyOrder, string $sessionId, int $amount): array
    {
        if ($amount <= 0) {
            throw new RuntimeException('El monto de Webpay debe ser mayor que cero.');
        }

        $response = $this->transaction()->create($buyOrder, $sessionId, $amount, $this->returnUrl());
        $token = trim((string)$response->getToken());
        $url = trim((string)$response->getUrl());
        if ($token === '' || $url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Webpay no entregó una transacción válida.');
        }

        return ['token' => $token, 'url' => $url];
    }

    public function commit(string $token): array
    {
        return $this->mapTransactionResponse($this->transaction()->commit($this->token($token)));
    }

    public function status(string $token): array
    {
        return $this->mapTransactionResponse($this->transaction()->status($this->token($token)));
    }

    public function isApproved(array $response): bool
    {
        return (int)($response['response_code'] ?? -1) === 0
            && strtoupper((string)($response['status'] ?? '')) === 'AUTHORIZED';
    }

    public function mapInternalStatus(array $response): string
    {
        $status = strtoupper((string)($response['status'] ?? ''));
        if ($this->isApproved($response)) {
            return PaymentService::STATUS_PAID;
        }
        if (in_array($status, ['REVERSED', 'NULLIFIED', 'PARTIALLY_NULLIFIED'], true)) {
            return PaymentService::STATUS_REVERSED;
        }
        if ($status === 'INITIALIZED' || $status === '') {
            return PaymentService::STATUS_PENDING;
        }

        return PaymentService::STATUS_REJECTED;
    }

    public function configuration(): array
    {
        return [
            'environment' => $this->environment(),
            'return_url' => $this->returnUrl(),
            'timeout_seconds' => $this->timeoutSeconds(),
        ];
    }

    private function transaction(): object
    {
        $this->assertConfigured();
        if ($this->transactionFactory) {
            return ($this->transactionFactory)($this->configuration(), $this->config);
        }

        $transaction = $this->environment() === 'production'
            ? Transaction::buildForProduction((string)$this->config['apiKey'], (string)$this->config['commerceCode'])
            : Transaction::buildForIntegration((string)$this->config['apiKey'], (string)$this->config['commerceCode']);
        $transaction->getOptions()->setTimeout($this->timeoutSeconds());

        return $transaction;
    }

    /** @return array<string, mixed> */
    private function mapTransactionResponse(object $response): array
    {
        return [
            'amount' => (int)$response->getAmount(),
            'currency' => 'CLP',
            'status' => strtoupper((string)$response->getStatus()),
            'response_code' => $response->getResponseCode(),
            'buy_order' => (string)$response->getBuyOrder(),
            'session_id' => (string)$response->getSessionId(),
            'authorization_code' => (string)$response->getAuthorizationCode(),
            'transaction_date' => (string)$response->getTransactionDate(),
            'payment_type_code' => (string)$response->getPaymentTypeCode(),
            'installments_number' => $response->getInstallmentsNumber(),
        ];
    }

    private function assertConfigured(): void
    {
        if (!in_array($this->environment(), ['integration', 'production'], true)) {
            throw new RuntimeException('WEBPAY_ENV debe ser integration o production.');
        }
        if (trim((string)$this->config['commerceCode']) === '' || trim((string)$this->config['apiKey']) === '') {
            throw new RuntimeException('Faltan credenciales de Webpay. Configura WEBPAY_COMMERCE_CODE y WEBPAY_API_KEY.');
        }
        if ($this->environment() === 'production' && !str_starts_with($this->returnUrl(), 'https://')) {
            throw new RuntimeException('WEBPAY_RETURN_URL debe usar HTTPS en producción.');
        }
    }

    private function environment(): string
    {
        return strtolower(trim((string)$this->config['environment']));
    }

    private function returnUrl(): string
    {
        $url = trim((string)$this->config['returnUrl']);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('WEBPAY_RETURN_URL no es una URL válida.');
        }

        return $url;
    }

    private function timeoutSeconds(): int
    {
        return max(1, min(120, (int)$this->config['timeoutSeconds']));
    }

    private function token(string $token): string
    {
        $token = trim($token);
        if ($token === '' || mb_strlen($token) > 255) {
            throw new RuntimeException('Token de Webpay inválido.');
        }

        return $token;
    }
}
