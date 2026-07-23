<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Theme extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
        'primary_color' => true,
        'secondary_color' => true,
        'background_color' => true,
        'font_family' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
        'sites' => true,
    ];
}
