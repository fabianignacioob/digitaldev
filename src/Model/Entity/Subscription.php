<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Subscription extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'plan_slug' => true,
        'status' => true,
        'starts_at' => true,
        'ends_at' => true,
        'grace_ends_at' => true,
        'notes' => true,
        'last_processed_at' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'payments' => true,
    ];
}
