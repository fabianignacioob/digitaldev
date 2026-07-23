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
        'created' => true,
        'modified' => true,
        'site' => true,
    ];
}
