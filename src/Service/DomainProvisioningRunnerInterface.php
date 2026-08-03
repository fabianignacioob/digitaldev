<?php
declare(strict_types=1);

namespace App\Service;

interface DomainProvisioningRunnerInterface
{
    /** @return array{success:bool,summary:string} */
    public function provision(string $hostname): array;
}
