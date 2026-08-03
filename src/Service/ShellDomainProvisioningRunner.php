<?php
declare(strict_types=1);

namespace App\Service;

use RuntimeException;

/** Runs the single sudo-approved provisioner without invoking a shell. */
class ShellDomainProvisioningRunner implements DomainProvisioningRunnerInterface
{
    public function provision(string $hostname): array
    {
        if (!filter_var(env('DOMAIN_PROVISIONING_ENABLED', false), FILTER_VALIDATE_BOOL)) {
            throw new RuntimeException('El provisionamiento automático no está habilitado en este ambiente.');
        }

        $path = (string)env('DOMAIN_PROVISIONER_PATH', '/usr/local/sbin/provision-catops-domain');
        if ($path === '' || !str_starts_with($path, '/')) {
            throw new RuntimeException('La ruta del provisionador de dominios no es válida.');
        }

        $process = proc_open(['sudo', '-n', $path, '--domain', $hostname], [
            0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
        ], $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('No se pudo iniciar el provisionador de dominios.');
        }
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]) ?: '';
        $errors = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        $summary = $this->sanitize($output . "\n" . $errors);

        return ['success' => $exitCode === 0, 'summary' => $summary ?: ($exitCode === 0 ? 'Provisionamiento completado.' : 'El provisionador terminó con error.')];
    }

    private function sanitize(string $value): string
    {
        $value = preg_replace('/(?:token|secret|password|key)\s*[:=]\s*\S+/i', '[redactado]', $value) ?? '';

        return mb_substr(trim(preg_replace('/\s+/', ' ', $value) ?? ''), 0, 1500);
    }
}
