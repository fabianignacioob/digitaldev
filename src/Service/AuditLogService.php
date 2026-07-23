<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;

class AuditLogService
{
    public function log(?int $userId, string $action, ?string $entity = null, ?int $entityId = null, array $data = []): void
    {
        $auditLogs = FactoryLocator::get('Table')->get('AuditLogs');
        $auditLogs->saveOrFail($auditLogs->newEntity([
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'data' => json_encode($data ?: new \stdClass(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created' => DateTime::now(),
        ]));
    }
}
