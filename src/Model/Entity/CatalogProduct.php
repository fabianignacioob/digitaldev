<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class CatalogProduct extends Entity
{
    protected array $_accessible = [
        'site_id' => true,
        'catalog_category_id' => true,
        'measurement_type_id' => true,
        'image_path' => true,
        'item_type' => true,
        'name' => true,
        'description' => true,
        'price' => true,
        'price_prefix' => true,
        'discount' => true,
        'duration' => true,
        'featured' => true,
        'active' => true,
        'availability' => true,
        'sort_order' => true,
        'created' => true,
        'modified' => true,
        'site' => true,
        'catalog_category' => true,
        'measurement_type' => true,
        'catalog_product_variants' => true,
    ];
}
