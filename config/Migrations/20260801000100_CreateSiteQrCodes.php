<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateSiteQrCodes extends BaseMigration
{
    public function change(): void
    {
        $this->table('site_qr_codes')
            ->addColumn('site_id', 'integer')
            ->addColumn('public_token', 'string', ['limit' => 64])
            ->addColumn('frame_style', 'string', ['default' => 'square', 'limit' => 20])
            ->addColumn('generated_at', 'datetime')
            ->addTimestamps('created', 'modified')
            ->addIndex(['site_id'], ['unique' => true])
            ->addIndex(['public_token'], ['unique' => true])
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
