<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class MeasurementType extends Entity
{
    protected array $_accessible = [
        'slug' => true,
        'name' => true,
        'units' => true,
        'sort_order' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
        'catalog_products' => true,
    ];
}
