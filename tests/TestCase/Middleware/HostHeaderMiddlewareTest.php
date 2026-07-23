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
    private ?string $previousEnvironment;
    private ?string $previousFullBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousDebug = Configure::read('debug');
        $this->previousFullBaseUrl = Configure::read('App.fullBaseUrl');
        $this->previousBaseDomain = getenv('APP_BASE_DOMAIN') !== false ? (string)getenv('APP_BASE_DOMAIN') : null;
        $this->previousEnvironment = getenv('APP_ENV') !== false ? (string)getenv('APP_ENV') : null;
        Configure::write('debug', false);
        Configure::write('App.fullBaseUrl', 'https://staging.catops.cl');
        putenv('APP_BASE_DOMAIN=staging.catops.cl');
        putenv('APP_ENV=staging');
    }

    protected function tearDown(): void
    {
        Configure::write('debug', $this->previousDebug);
        Configure::write('App.fullBaseUrl', $this->previousFullBaseUrl);
        $this->previousBaseDomain === null ? putenv('APP_BASE_DOMAIN') : putenv('APP_BASE_DOMAIN=' . $this->previousBaseDomain);
        $this->previousEnvironment === null ? putenv('APP_ENV') : putenv('APP_ENV=' . $this->previousEnvironment);

        parent::tearDown();
    }

    public function testProductionAllowsBaseHostAndSingleLevelSubdomain(): void
    {
        $this->assertTrue($this->passes('staging.catops.cl'));
        $this->assertTrue($this->passes('TIENDA.staging.catops.cl'));
        $this->assertTrue($this->passes('tienda.staging.catops.cl:443'));
    }

    public function testProductionRejectsInvalidAndLookalikeHosts(): void
    {
        foreach ([
            'staging.catops.cl.ejemplo.com',
            'fake-staging.catops.cl',
            'catops.cl.ejemplo.net',
            'a.b.staging.catops.cl',
            'tienda.staging.catops.cl:0',
            'tienda.staging.catops.cl:65536',
            'tienda_staging.catops.cl',
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
