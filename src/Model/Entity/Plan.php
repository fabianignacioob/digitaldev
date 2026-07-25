<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Plan extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
        'monthly_price' => true,
        'annual_price' => true,
        'max_sites' => true,
        'max_published' => true,
        'capabilities' => true,
        'annual_benefits' => true,
        'commercial_description' => true,
        'commercial_badge' => true,
        'sort_order' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
    ];
}
