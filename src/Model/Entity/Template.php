<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Template extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
        'description' => true,
        'preview_image' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
        'sites' => true,
    ];
}
