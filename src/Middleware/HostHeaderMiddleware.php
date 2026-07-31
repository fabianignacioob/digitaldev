<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Service\DomainAdministrationService;
use App\Service\PublicUrlService;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to validate Host header and prevent Host Header Injection attacks.
 *
 * In production, this middleware validates the configured application host
 * plus the single-level site subdomains supported by PublicUrlService.
 *
 * @see https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/17-Testing_for_Host_Header_Injection
 */
class HostHeaderMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and validate the Host header.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Configure::read('debug')) {
            return $handler->handle($request);
        }

        $fullBaseUrl = Configure::read('App.fullBaseUrl');
        if (!$fullBaseUrl) {
            throw new InternalErrorException(
                'SECURITY: App.fullBaseUrl is not configured. ' .
                'This is required in production to prevent Host Header Injection attacks. ' .
                'Set APP_FULL_BASE_URL environment variable or configure App.fullBaseUrl in config/app.php',
            );
        }

        $configuredHost = $this->configuredHost((string)$fullBaseUrl);
        $requestHost = $this->requestHost($request);
        if ($requestHost === null || !$this->isAllowedHost($requestHost, $configuredHost)) {
            throw new BadRequestException(
                'Invalid Host header.',
            );
        }

        return $handler->handle($request);
    }

    private function configuredHost(string $fullBaseUrl): string
    {
        $host = parse_url($fullBaseUrl, PHP_URL_HOST);
        if (!is_string($host) || !self::isValidHostname($host)) {
            throw new InternalErrorException('App.fullBaseUrl debe incluir un hostname válido.');
        }

        return self::normalizeHostname($host);
    }

    private function requestHost(ServerRequestInterface $request): ?string
    {
        // Do not trust X-Forwarded-Host. The proxy must pass the validated Host header.
        $rawHost = trim($request->getHeaderLine('Host'));
        if ($rawHost === '' || !preg_match('/^(?<host>[A-Za-z0-9.-]+)(?::(?<port>[0-9]{1,5}))?$/D', $rawHost, $matches)) {
            return null;
        }

        if (isset($matches['port']) && ((int)$matches['port'] < 1 || (int)$matches['port'] > 65535)) {
            return null;
        }

        $host = self::normalizeHostname($matches['host']);

        return self::isValidHostname($host) ? $host : null;
    }

    private function isAllowedHost(string $requestHost, string $configuredHost): bool
    {
        $baseDomain = (new PublicUrlService())->baseDomain();
        if ($requestHost === $configuredHost || $requestHost === $baseDomain) {
            return true;
        }

        if ($requestHost === 'localhost') {
            return Configure::read('debug') || in_array(strtolower((string)env('APP_ENV', '')), ['local', 'development', 'test', 'testing'], true);
        }

        $suffix = '.' . $baseDomain;
        if (str_ends_with($requestHost, $suffix)) {
            // The public resolver accepts one subdomain label only; do not allow
            // hosts that it cannot resolve or wildcard suffix bypasses.
            $subdomain = substr($requestHost, 0, -strlen($suffix));

            return self::isValidLabel($subdomain);
        }

        // Domains outside the CatOps zone are never accepted merely because they
        // look valid. They must have completed DNS ownership verification.
        return (new DomainAdministrationService())->isActiveVerifiedCustomHostname($requestHost);
    }

    private static function normalizeHostname(string $host): string
    {
        return strtolower(rtrim(trim($host), '.'));
    }

    private static function isValidHostname(string $host): bool
    {
        if ($host === '' || strlen($host) > 253) {
            return false;
        }

        foreach (explode('.', $host) as $label) {
            if (!self::isValidLabel($label)) {
                return false;
            }
        }

        return true;
    }

    private static function isValidLabel(string $label): bool
    {
        return (bool)preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/D', $label);
    }
}
