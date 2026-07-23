<?php
declare(strict_types=1);

namespace App\Controller;

class AdminAuditLogsController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'action' => trim((string)$this->request->getQuery('action')),
            'entity' => trim((string)$this->request->getQuery('entity')),
            'user_id' => trim((string)$this->request->getQuery('user_id')),
            'from' => trim((string)$this->request->getQuery('from')),
            'to' => trim((string)$this->request->getQuery('to')),
        ];
        $query = $this->fetchTable('AuditLogs')->find()->contain(['Users'])->orderByDesc('AuditLogs.created');
        if ($filters['action'] !== '') {
            $query->where(['AuditLogs.action LIKE' => '%' . $filters['action'] . '%']);
        }
        if ($filters['entity'] !== '') {
            $query->where(['AuditLogs.entity' => $filters['entity']]);
        }
        if (ctype_digit($filters['user_id'])) {
            $query->where(['AuditLogs.user_id' => (int)$filters['user_id']]);
        }
        if ($filters['from'] !== '') {
            $query->where(['AuditLogs.created >=' => $filters['from'] . ' 00:00:00']);
        }
        if ($filters['to'] !== '') {
            $query->where(['AuditLogs.created <=' => $filters['to'] . ' 23:59:59']);
        }
        $pagination = $this->paginateAdmin($query, 40);
        foreach ($pagination['items'] as $audit) {
            $audit->data = $this->safePayload($audit->data);
        }
        $entities = $this->fetchTable('AuditLogs')->find()->select(['entity'])->distinct(['entity'])->orderByAsc('entity')->all();
        $this->set(compact('filters', 'pagination', 'entities'));
    }
}
