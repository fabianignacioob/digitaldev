<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class CatalogSetting extends Entity
{
    protected array $_accessible = [
        'site_id' => true,
        'background_type' => true,
        'background_color' => true,
        'background_image_path' => true,
        'background_preset' => true,
        'title_color' => true,
        'heading_font' => true,
        'title_font' => true,
        'title' => true,
        'slogan_color' => true,
        'slogan_font' => true,
        'slogan' => true,
        'intro_text' => true,
        'created' => true,
        'modified' => true,
        'site' => true,
    ];
}
