<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\HostHeaderMiddleware;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HostHeaderMiddlewareTest extends TestCase
{
    private mixed $previousDebug;
    private ?string $previousBaseDomain;
    private ?string $previousPlatformDomain;
    private ?string $previousPublicBaseDomain;
    private ?string $previousEnvironment;
    private ?string $previousFullBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousDebug = Configure::read('debug');
        $this->previousFullBaseUrl = Configure::read('App.fullBaseUrl');
        $this->previousBaseDomain = getenv('APP_BASE_DOMAIN') !== false ? (string)getenv('APP_BASE_DOMAIN') : null;
        $this->previousPlatformDomain = getenv('APP_PLATFORM_DOMAIN') !== false ? (string)getenv('APP_PLATFORM_DOMAIN') : null;
        $this->previousPublicBaseDomain = getenv('APP_PUBLIC_BASE_DOMAIN') !== false ? (string)getenv('APP_PUBLIC_BASE_DOMAIN') : null;
        $this->previousEnvironment = getenv('APP_ENV') !== false ? (string)getenv('APP_ENV') : null;
        Configure::write('debug', false);
        Configure::write('App.fullBaseUrl', 'https://staging.catops.cl');
        putenv('APP_BASE_DOMAIN');
        putenv('APP_PLATFORM_DOMAIN=staging.catops.cl');
        putenv('APP_PUBLIC_BASE_DOMAIN=vitrinahub.cl');
        putenv('APP_ENV=staging');
    }

    protected function tearDown(): void
    {
        Configure::write('debug', $this->previousDebug);
        Configure::write('App.fullBaseUrl', $this->previousFullBaseUrl);
        $this->previousBaseDomain === null ? putenv('APP_BASE_DOMAIN') : putenv('APP_BASE_DOMAIN=' . $this->previousBaseDomain);
        $this->previousPlatformDomain === null ? putenv('APP_PLATFORM_DOMAIN') : putenv('APP_PLATFORM_DOMAIN=' . $this->previousPlatformDomain);
        $this->previousPublicBaseDomain === null ? putenv('APP_PUBLIC_BASE_DOMAIN') : putenv('APP_PUBLIC_BASE_DOMAIN=' . $this->previousPublicBaseDomain);
        $this->previousEnvironment === null ? putenv('APP_ENV') : putenv('APP_ENV=' . $this->previousEnvironment);

        parent::tearDown();
    }

    public function testProductionAllowsPlatformPublicBaseAndSingleLevelSubdomains(): void
    {
        $this->assertTrue($this->passes('staging.catops.cl'));
        $this->assertTrue($this->passes('www.staging.catops.cl'));
        $this->assertTrue($this->passes('TIENDA.vitrinahub.cl'));
        $this->assertTrue($this->passes('tienda.vitrinahub.cl:443'));
        // Temporary compatibility for historic public CatOps links.
        $this->assertTrue($this->passes('tienda.staging.catops.cl'));
    }

    public function testProductionRejectsInvalidAndLookalikeHosts(): void
    {
        foreach ([
            'staging.catops.cl.ejemplo.com',
            'fake-staging.catops.cl',
            'catops.cl.ejemplo.net',
            'a.b.vitrinahub.cl',
            'tienda.vitrinahub.cl:0',
            'tienda.vitrinahub.cl:65536',
            'tienda_vitrinahub.cl',
            '',
        ] as $host) {
            $this->assertFalse($this->passes($host), 'Expected rejected host: ' . $host);
        }
    }

    public function testLocalhostRequiresDebugOrLocalEnvironment(): void
    {
        $this->assertFalse($this->passes('localhost'));
        Configure::write('debug', true);
        $this->assertTrue($this->passes('localhost'));
    }

    private function passes(string $host): bool
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('Host')->willReturn($host);
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        try {
            (new HostHeaderMiddleware())->process($request, $handler);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
