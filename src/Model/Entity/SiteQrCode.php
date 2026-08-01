<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class SiteQrCode extends Entity
{
    protected array $_accessible = [
        'site_id' => true,
        'public_token' => true,
        'frame_style' => true,
        'generated_at' => true,
        'created' => true,
        'modified' => true,
        'site' => true,
    ];
}
