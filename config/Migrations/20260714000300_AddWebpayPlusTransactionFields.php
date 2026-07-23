<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddWebpayPlusTransactionFields extends BaseMigration
{
    public function change(): void
    {
        $this->table('payments')
            ->addColumn('gateway_token', 'string', ['limit' => 255, 'null' => true, 'after' => 'session_id'])
            ->addColumn('gateway_url', 'string', ['limit' => 500, 'null' => true, 'after' => 'gateway_token'])
            ->addColumn('gateway_created_at', 'datetime', ['null' => true, 'after' => 'gateway_url'])
            ->addColumn('gateway_expires_at', 'datetime', ['null' => true, 'after' => 'gateway_created_at'])
            ->addIndex(['gateway_token'], [
                'unique' => true,
                'name' => 'payments_gateway_token_unique',
            ])
            ->update();
    }
}
