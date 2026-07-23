<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddSubscriptionLifecycleFields extends BaseMigration
{
    public function change(): void
    {
        $this->table('subscriptions')
            ->addColumn('grace_ends_at', 'datetime', ['null' => true, 'after' => 'ends_at'])
            ->addColumn('last_processed_at', 'datetime', ['null' => true, 'after' => 'notes'])
            ->update();

        $this->table('payments')
            ->addColumn('processed_at', 'datetime', ['null' => true, 'after' => 'period_end'])
            ->addIndex(['provider', 'provider_reference'], [
                'unique' => true,
                'name' => 'payments_provider_reference_unique',
            ])
            ->update();

        $this->table('sites')
            ->addColumn('paused_reason', 'string', ['limit' => 80, 'null' => true, 'after' => 'status'])
            ->update();
    }
}
