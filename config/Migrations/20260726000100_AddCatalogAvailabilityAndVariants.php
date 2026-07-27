<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCatalogAvailabilityAndVariants extends BaseMigration
{
    public function up(): void
    {
        $this->table('catalog_products')
            ->addColumn('availability', 'string', ['default' => 'available', 'limit' => 30, 'after' => 'active'])
            ->addIndex(['site_id', 'availability'])
            ->update();

        $this->table('measurement_types')
            ->addColumn('slug', 'string', ['limit' => 60])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('units', 'json', ['default' => '[]'])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $this->table('catalog_product_variants')
            ->addColumn('catalog_product_id', 'integer')
            ->addColumn('measurement_type_id', 'integer', ['null' => true])
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('measurement_value', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addColumn('measurement_unit', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('price', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true])
            ->addColumn('availability', 'string', ['default' => 'available', 'limit' => 30])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addTimestamps('created', 'modified')
            ->addIndex(['catalog_product_id', 'sort_order'])
            ->addIndex(['measurement_type_id'])
            ->addForeignKey('catalog_product_id', 'catalog_products', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('measurement_type_id', 'measurement_types', 'id', ['delete' => 'SET_NULL'])
            ->create();

        $now = date('Y-m-d H:i:s');
        $types = [
            ['slug' => 'size', 'name' => 'Tamaño', 'units' => ['cm', 'in']],
            ['slug' => 'length', 'name' => 'Longitud', 'units' => ['cm', 'm', 'in']],
            ['slug' => 'weight', 'name' => 'Peso', 'units' => ['g', 'kg']],
            ['slug' => 'volume', 'name' => 'Volumen', 'units' => ['ml', 'cc', 'L']],
            ['slug' => 'portion', 'name' => 'Porción', 'units' => ['porción', 'piezas', 'personas']],
            ['slug' => 'units', 'name' => 'Unidades', 'units' => ['unidad', 'pack', 'docena']],
            ['slug' => 'duration', 'name' => 'Duración', 'units' => ['min', 'hora', 'sesión']],
        ];
        foreach ($types as $position => $type) {
            $units = str_replace("'", "''", (string)json_encode($type['units'], JSON_UNESCAPED_UNICODE));
            $slug = str_replace("'", "''", $type['slug']);
            $name = str_replace("'", "''", $type['name']);
            $sortOrder = $position + 1;
            $this->execute(
                "INSERT INTO measurement_types (slug, name, units, sort_order, active, created, modified)\n"
                . "VALUES ('{$slug}', '{$name}', '{$units}', {$sortOrder}, true, '{$now}', '{$now}')\n"
                . "ON CONFLICT (slug) DO NOTHING"
            );
        }
    }
}
