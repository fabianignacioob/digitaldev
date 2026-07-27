<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class CatalogProductVariant extends Entity
{
    protected array $_accessible = [
        'catalog_product_id' => true,
        'name' => true,
        'measurement_value' => true,
        'measurement_unit' => true,
        'price' => true,
        'availability' => true,
        'sort_order' => true,
        'created' => true,
        'modified' => true,
        'catalog_product' => true,
    ];
}
