<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Lead extends Entity
{
    protected array $_accessible = [
        'site_id' => true,
        'name' => true,
        'email' => true,
        'phone' => true,
        'message' => true,
        'source' => true,
        'created' => true,
        'site' => true,
    ];
}
