<?php
declare(strict_types=1);

namespace App\Service;

use RuntimeException;

/**
 * Small boundary around PHP DNS functions so domain verification remains
 * testable and can later move to a managed DNS verification provider.
 */
class DnsTxtResolver
{
    /** @return list<string> */
    public function records(string $hostname): array
    {
        if (!function_exists('dns_get_record')) {
            throw new RuntimeException('El servidor no tiene disponible la consulta DNS requerida para verificar el dominio.');
        }

        $records = @dns_get_record($hostname, DNS_TXT);
        if ($records === false) {
            throw new RuntimeException('No fue posible consultar el registro TXT del dominio.');
        }

        $values = [];
        foreach ($records as $record) {
            $value = $record['txt'] ?? null;
            if (is_string($value) && $value !== '') {
                $values[] = $value;
            }
        }

        return $values;
    }

    /** @return list<string> */
    public function cnameRecords(string $hostname): array
    {
        return $this->values($hostname, DNS_CNAME, 'target');
    }

    /** @return list<string> */
    public function aRecords(string $hostname): array
    {
        return $this->values($hostname, DNS_A, 'ip');
    }

    /** @return list<string> */
    private function values(string $hostname, int $type, string $key): array
    {
        if (!function_exists('dns_get_record')) {
            throw new RuntimeException('El servidor no tiene disponible la consulta DNS requerida para verificar el dominio.');
        }

        $records = @dns_get_record($hostname, $type);
        if ($records === false) {
            throw new RuntimeException('No fue posible consultar los registros DNS del dominio.');
        }

        $values = [];
        foreach ($records as $record) {
            $value = $record[$key] ?? null;
            if (is_string($value) && $value !== '') {
                $values[] = strtolower(rtrim($value, '.'));
            }
        }

        return $values;
    }
}
