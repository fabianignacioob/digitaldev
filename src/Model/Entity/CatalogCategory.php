<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class CatalogCategory extends Entity
{
    protected array $_accessible = [
        'site_id' => true,
        'name' => true,
        'sort_order' => true,
        'created' => true,
        'modified' => true,
        'site' => true,
        'catalog_products' => true,
    ];
}
