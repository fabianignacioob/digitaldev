<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateCatalogFeature extends BaseMigration
{
    public function change(): void
    {
        $this->table('catalog_settings')
            ->addColumn('site_id', 'integer')
            ->addColumn('background_type', 'string', ['default' => 'color', 'limit' => 20])
            ->addColumn('background_color', 'string', ['default' => '#fbfaf7', 'limit' => 20])
            ->addColumn('background_image_path', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('title', 'string', ['default' => 'Nuestra carta', 'limit' => 160])
            ->addColumn('slogan', 'string', ['default' => 'Sabores simples, bien presentados.', 'limit' => 220])
            ->addColumn('intro_text', 'text', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['site_id'], ['unique' => true])
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('catalog_categories')
            ->addColumn('site_id', 'integer')
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addTimestamps('created', 'modified')
            ->addIndex(['site_id', 'name'], ['unique' => true])
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('catalog_products')
            ->addColumn('site_id', 'integer')
            ->addColumn('catalog_category_id', 'integer', ['null' => true])
            ->addColumn('image_path', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('name', 'string', ['limit' => 140])
            ->addColumn('description', 'string', ['limit' => 260, 'null' => true])
            ->addColumn('price', 'decimal', ['precision' => 12, 'scale' => 2])
            ->addColumn('discount', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addTimestamps('created', 'modified')
            ->addIndex(['site_id', 'catalog_category_id'])
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('catalog_category_id', 'catalog_categories', 'id', ['delete' => 'SET_NULL'])
            ->create();
    }
}
