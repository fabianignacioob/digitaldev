<?php
declare(strict_types=1);

namespace App\Service;

interface WebpayPlusGatewayInterface
{
    /** @return array{token: string, url: string} */
    public function createTransaction(string $buyOrder, string $sessionId, int $amount): array;

    /** @return array<string, mixed> */
    public function commit(string $token): array;

    /** @return array<string, mixed> */
    public function status(string $token): array;

    /** @param array<string, mixed> $response */
    public function isApproved(array $response): bool;

    /** @param array<string, mixed> $response */
    public function mapInternalStatus(array $response): string;

    /** @return array{environment: string, return_url: string, timeout_seconds: int} */
    public function configuration(): array;
}
