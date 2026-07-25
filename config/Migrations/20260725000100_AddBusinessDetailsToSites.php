<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddBusinessDetailsToSites extends BaseMigration
{
    public function change(): void
    {
        $this->table('sites')
            ->addColumn('business_address', 'string', ['limit' => 220, 'null' => true])
            ->addColumn('business_hours', 'string', ['limit' => 220, 'null' => true])
            ->addColumn('public_phone', 'string', ['limit' => 60, 'null' => true])
            ->addColumn('public_email', 'string', ['limit' => 180, 'null' => true])
            ->update();
    }
}
