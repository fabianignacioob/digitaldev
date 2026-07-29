<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPublicContactVisibilityControls extends BaseMigration
{
    public function change(): void
    {
        $this->table('sites')
            ->addColumn('show_whatsapp', 'boolean', ['default' => true])
            ->addColumn('show_instagram', 'boolean', ['default' => true])
            ->update();

        $this->table('catalog_settings')
            ->addColumn('show_product_action', 'boolean', ['default' => true])
            ->update();
    }
}
