<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Service\PlanService;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    private ?PlanService $planService = null;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $this->set('currentUser', $this->currentUser());
    }

    protected function currentUser(): ?array
    {
        return $this->request->getSession()->read('Auth.User');
    }

    protected function currentUserId(): ?int
    {
        $user = $this->currentUser();

        return $user ? (int)$user['id'] : null;
    }

    protected function requireLogin(): ?Response
    {
        $sessionUser = $this->currentUser();
        if (!$sessionUser) {
            $this->Flash->error('Inicia sesión para continuar.');

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $user = $this->fetchTable('Users')->find()
            ->select(['id', 'name', 'email', 'role', 'active'])
            ->where(['id' => (int)$sessionUser['id'], 'active' => true])
            ->first();
        if (!$user) {
            $this->request->getSession()->delete('Auth.User');
            $this->Flash->error('Tu sesión ya no está activa. Inicia sesión nuevamente.');

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $this->request->getSession()->write('Auth.User', [
            'id' => (int)$user->id,
            'name' => (string)$user->name,
            'email' => (string)$user->email,
            'role' => (string)$user->role,
        ]);

        return null;
    }

    protected function requireAdmin(): ?Response
    {
        return $this->requireRole(['admin', 'superadmin']);
    }

    protected function requireSuperAdmin(): ?Response
    {
        return $this->requireRole(['superadmin']);
    }

    /** @param list<string> $roles */
    private function requireRole(array $roles): ?Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $role = (string)($this->currentUser()['role'] ?? 'user');
        // Accounts created before the role normalization remain regular users.
        if ($role === 'customer') {
            $role = 'user';
        }
        if (!in_array($role, $roles, true)) {
            throw new ForbiddenException('No tienes permisos para acceder a esta sección.');
        }

        return null;
    }

    protected function hasActivePlan(?int $userId = null): bool
    {
        return $this->currentSubscription($userId) !== null;
    }

    protected function currentSubscription(?int $userId = null): ?object
    {
        $userId = $userId ?? $this->currentUserId();
        if (!$userId) {
            return null;
        }

        return $this->planService()->getCurrentSubscription($userId);
    }

    protected function currentPlanRules(?int $userId = null): ?array
    {
        $subscription = $this->currentSubscription($userId);
        if (!$subscription) {
            return null;
        }

        $plan = $this->planService()->getPlanBySlug((string)$subscription->plan_slug);

        return $plan ? $this->planService()->capabilities($plan) : null;
    }

    protected function planRules(): array
    {
        return [];
    }

    protected function allowedTemplateSlugs(?int $userId = null): array
    {
        $userId = $userId ?? $this->currentUserId();

        return $userId ? $this->planService()->allowedTemplateSlugs($userId) : [];
    }

    protected function planService(): PlanService
    {
        if (!$this->planService) {
            $this->planService = new PlanService();
        }

        return $this->planService;
    }
}
