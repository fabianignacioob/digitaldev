<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPublishedAtToSites extends BaseMigration
{
    public function change(): void
    {
        $this->table('sites')
            ->addColumn('published_at', 'datetime', ['null' => true, 'after' => 'status'])
            ->update();
    }
}
