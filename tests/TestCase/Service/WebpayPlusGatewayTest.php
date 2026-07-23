<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\PaymentService;
use App\Service\WebpayPlusGateway;
use Cake\TestSuite\TestCase;

class WebpayPlusGatewayTest extends TestCase
{
    public function testCreatesAndMapsIntegrationTransaction(): void
    {
        $gateway = new WebpayPlusGateway([
            'environment' => 'integration',
            'commerceCode' => '597055555532',
            'apiKey' => 'integration-key',
            'returnUrl' => 'https://catops.test/payments/webpay/return',
            'timeoutSeconds' => 15,
        ], static fn () => new class {
            public function create(string $buyOrder, string $sessionId, int $amount, string $returnUrl): object
            {
                return new class {
                    public function getToken(): string { return 'token-sdk'; }
                    public function getUrl(): string { return 'https://webpay.test/form'; }
                };
            }

            public function commit(string $token): object
            {
                return new class {
                    public function getAmount(): int { return 6990; }
                    public function getStatus(): string { return 'AUTHORIZED'; }
                    public function getResponseCode(): int { return 0; }
                    public function getBuyOrder(): string { return 'bo-1'; }
                    public function getSessionId(): string { return 'sess-1'; }
                    public function getAuthorizationCode(): string { return 'AUTH1'; }
                    public function getTransactionDate(): string { return '2026-07-14T12:00:00Z'; }
                    public function getPaymentTypeCode(): string { return 'VN'; }
                    public function getInstallmentsNumber(): int { return 0; }
                };
            }

            public function status(string $token): object
            {
                return $this->commit($token);
            }
        });

        $created = $gateway->createTransaction('bo-1', 'sess-1', 6990);
        $response = $gateway->commit('token-sdk');

        $this->assertSame('token-sdk', $created['token']);
        $this->assertTrue($gateway->isApproved($response));
        $this->assertSame(PaymentService::STATUS_PAID, $gateway->mapInternalStatus($response));
    }

    public function testProductionConfigurationIsExplicit(): void
    {
        $captured = null;
        $gateway = new WebpayPlusGateway([
            'environment' => 'production',
            'commerceCode' => 'commerce-production',
            'apiKey' => 'production-key',
            'returnUrl' => 'https://catops.cl/payments/webpay/return',
            'timeoutSeconds' => 10,
        ], static function (array $configuration, array $rawConfig) use (&$captured): object {
            $captured = [$configuration, $rawConfig];

            return new class {
                public function create(): object
                {
                    return new class {
                        public function getToken(): string { return 'token-production'; }
                        public function getUrl(): string { return 'https://webpay.test/form'; }
                    };
                }
            };
        });

        $gateway->createTransaction('bo-prod', 'sess-prod', 6990);

        $this->assertSame('production', $captured[0]['environment']);
        $this->assertSame('commerce-production', $captured[1]['commerceCode']);
    }
}
