<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class MoveMeasurementTypeToCatalogProducts extends BaseMigration
{
    public function up(): void
    {
        $this->table('catalog_products')
            ->addColumn('measurement_type_id', 'integer', ['null' => true, 'after' => 'catalog_category_id'])
            ->addIndex(['measurement_type_id'])
            ->addForeignKey('measurement_type_id', 'measurement_types', 'id', ['delete' => 'SET_NULL'])
            ->update();

        // Conserva el tipo de la primera variante para no perder los datos ya cargados.
        $this->execute(
            'UPDATE catalog_products SET measurement_type_id = ('
            . 'SELECT measurement_type_id FROM catalog_product_variants '
            . 'WHERE catalog_product_variants.catalog_product_id = catalog_products.id '
            . 'AND measurement_type_id IS NOT NULL ORDER BY sort_order ASC LIMIT 1'
            . ') WHERE EXISTS ('
            . 'SELECT 1 FROM catalog_product_variants '
            . 'WHERE catalog_product_variants.catalog_product_id = catalog_products.id '
            . 'AND measurement_type_id IS NOT NULL'
            . ')'
        );

        $this->table('catalog_product_variants')
            ->dropForeignKey('measurement_type_id')
            ->removeIndex(['measurement_type_id'])
            ->removeColumn('measurement_type_id')
            ->update();
    }
}
