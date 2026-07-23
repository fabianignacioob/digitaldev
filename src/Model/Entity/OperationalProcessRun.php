<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class OperationalProcessRun extends Entity
{
    protected array $_accessible = [
        'process_name' => true,
        'started_at' => true,
        'finished_at' => true,
        'status' => true,
        'processed_count' => true,
        'skipped_count' => true,
        'error_count' => true,
        'message' => true,
        'metadata' => true,
        'created' => true,
        'modified' => true,
    ];
}
