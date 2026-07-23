<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AllowPendingPaymentDates extends BaseMigration
{
    public function change(): void
    {
        $this->table('payments')
            ->changeColumn('paid_at', 'datetime', ['null' => true])
            ->changeColumn('period_start', 'datetime', ['null' => true])
            ->changeColumn('period_end', 'datetime', ['null' => true])
            ->update();
    }
}
