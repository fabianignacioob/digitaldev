<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class NormalizeAdminRolesAndAddIndexes extends BaseMigration
{
    public function change(): void
    {
        $this->execute("UPDATE users SET role = 'user' WHERE role = 'customer'");

        $this->table('users')
            ->addIndex(['role', 'active'], ['name' => 'users_role_active_index'])
            ->update();

        $this->table('subscriptions')
            ->addIndex(['status', 'ends_at'], ['name' => 'subscriptions_status_ends_at_index'])
            ->update();

        $this->table('payments')
            ->addIndex(['status', 'created'], ['name' => 'payments_status_created_index'])
            ->update();

        $this->table('audit_logs')
            ->addIndex(['entity', 'entity_id'], ['name' => 'audit_logs_entity_index'])
            ->addIndex(['action', 'created'], ['name' => 'audit_logs_action_created_index'])
            ->update();
    }
}
