<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuditLogService;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use Cake\ORM\Query\SelectQuery;

abstract class AdminController extends AppController
{
    public function beforeFilter(EventInterface $event): ?Response
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        $this->viewBuilder()->setLayout('admin');

        return parent::beforeFilter($event);
    }

    /** @return array{items: iterable, page: int, pages: int, total: int, limit: int} */
    protected function paginateAdmin(SelectQuery $query, int $limit = 25): array
    {
        $limit = max(10, min(100, $limit));
        $page = max(1, (int)$this->request->getQuery('page', 1));
        $total = (clone $query)->count();
        $pages = max(1, (int)ceil($total / $limit));
        $page = min($page, $pages);

        return [
            'items' => $query->limit($limit)->offset(($page - 1) * $limit)->all(),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit,
        ];
    }

    protected function adminReason(): string
    {
        $reason = trim((string)$this->request->getData('reason'));
        if ($reason === '' || mb_strlen($reason) > 500) {
            throw new BadRequestException('Debes indicar un motivo de hasta 500 caracteres.');
        }

        return $reason;
    }

    protected function logAdminAction(string $action, string $entity, int $entityId, array $data = []): void
    {
        (new AuditLogService())->log((int)$this->currentUserId(), $action, $entity, $entityId, $data);
    }

    protected function requireSuperAdminAction(): void
    {
        $this->requireSuperAdmin();
    }

    protected function safePayload(mixed $payload): mixed
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = json_last_error() === JSON_ERROR_NONE ? $decoded : $payload;
        }
        if (!is_array($payload)) {
            return $payload;
        }

        $safe = [];
        foreach ($payload as $key => $value) {
            if (in_array(strtolower((string)$key), ['token', 'token_ws', 'gateway_token', 'api_key', 'secret', 'card', 'card_number', 'cvv', 'cvc'], true)) {
                $safe[$key] = '[redacted]';
                continue;
            }
            $safe[$key] = is_array($value) ? $this->safePayload($value) : $value;
        }

        return $safe;
    }
}
