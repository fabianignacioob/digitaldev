<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class MediaAsset extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'site_id' => true,
        'type' => true,
        'path' => true,
        'original_name' => true,
        'mime_type' => true,
        'size' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'site' => true,
    ];
}
