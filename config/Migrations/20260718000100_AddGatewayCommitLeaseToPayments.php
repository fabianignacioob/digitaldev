<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddGatewayCommitLeaseToPayments extends BaseMigration
{
    public function change(): void
    {
        $this->table('payments')
            ->addColumn('gateway_commit_started_at', 'datetime', ['null' => true, 'after' => 'gateway_expires_at'])
            ->addIndex(['gateway_commit_started_at'], ['name' => 'payments_gateway_commit_lease'])
            ->update();
    }
}
