<?php
declare(strict_types=1);

namespace App\Test\Double;

use App\Service\PaymentService;
use App\Service\WebpayPlusGatewayInterface;
use RuntimeException;

class FakeWebpayPlusGateway implements WebpayPlusGatewayInterface
{
    public array $createResponse = ['token' => 'token-webpay-test', 'url' => 'https://webpay.test/payment'];
    public array $commitResponse = [];
    public array $statusResponse = [];
    public bool $throwOnCreate = false;
    public bool $throwOnCommit = false;
    public bool $throwOnStatus = false;
    public int $createCalls = 0;
    public int $commitCalls = 0;
    public int $statusCalls = 0;

    public function createTransaction(string $buyOrder, string $sessionId, int $amount): array
    {
        $this->createCalls++;
        if ($this->throwOnCreate) {
            throw new RuntimeException('network error');
        }

        return $this->createResponse;
    }

    public function commit(string $token): array
    {
        $this->commitCalls++;
        if ($this->throwOnCommit) {
            throw new RuntimeException('timeout');
        }

        return $this->commitResponse;
    }

    public function status(string $token): array
    {
        $this->statusCalls++;
        if ($this->throwOnStatus) {
            throw new RuntimeException('timeout');
        }

        return $this->statusResponse;
    }

    public function isApproved(array $response): bool
    {
        return (int)($response['response_code'] ?? -1) === 0
            && strtoupper((string)($response['status'] ?? '')) === 'AUTHORIZED';
    }

    public function mapInternalStatus(array $response): string
    {
        if ($this->isApproved($response)) {
            return PaymentService::STATUS_PAID;
        }
        $status = strtoupper((string)($response['status'] ?? ''));
        if (in_array($status, ['REVERSED', 'NULLIFIED', 'PARTIALLY_NULLIFIED'], true)) {
            return PaymentService::STATUS_REVERSED;
        }

        return $status === 'INITIALIZED' || $status === ''
            ? PaymentService::STATUS_PENDING
            : PaymentService::STATUS_REJECTED;
    }

    public function configuration(): array
    {
        return [
            'environment' => 'integration',
            'return_url' => 'https://catops.test/payments/webpay/return',
            'timeout_seconds' => 20,
        ];
    }
}
