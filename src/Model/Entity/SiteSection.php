<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class SiteSection extends Entity
{
    protected array $_accessible = [
        'site_id' => true,
        'section_key' => true,
        'title' => true,
        'subtitle' => true,
        'content' => true,
        'image_path' => true,
        'sort_order' => true,
        'visible' => true,
        'settings' => true,
        'created' => true,
        'modified' => true,
        'site' => true,
    ];
}
