<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Domain extends Entity
{
    protected array $_accessible = [
        'site_id' => true,
        'domain' => true,
        'type' => true,
        'verified' => true,
        'active' => true,
        'status' => true,
        'verification_token' => true,
        'verification_method' => true,
        'verification_requested_at' => true,
        'verification_checked_at' => true,
        'verified_at' => true,
        'last_dns_error' => true,
        'provisioning_started_at' => true,
        'provisioning_last_attempt_at' => true,
        'provisioning_attempts' => true,
        'provisioned_at' => true,
        'provisioning_summary' => true,
        'provisioning_error' => true,
        'created' => true,
        'modified' => true,
        'site' => true,
    ];
}
