<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreatePlansCatalog extends BaseMigration
{
    public function change(): void
    {
        $this->table('plans')
            ->addColumn('name', 'string', ['limit' => 80])
            ->addColumn('slug', 'string', ['limit' => 40])
            ->addColumn('monthly_price', 'integer')
            ->addColumn('max_sites', 'integer')
            ->addColumn('max_published', 'integer')
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $now = date('Y-m-d H:i:s');
        $plans = [
            ['Básico', 'basica', 6990, 1, 1, 1],
            ['Medio', 'basica-avanzada', 9990, 5, 3, 2],
            ['Full', 'full', 16990, 7, 5, 3],
        ];

        foreach ($plans as [$name, $slug, $price, $maxSites, $maxPublished, $sort]) {
            $name = addslashes($name);
            $this->execute(
                "INSERT INTO plans (name, slug, monthly_price, max_sites, max_published, sort_order, active, created, modified)
                 VALUES ('{$name}', '{$slug}', {$price}, {$maxSites}, {$maxPublished}, {$sort}, true, '{$now}', '{$now}')
                 ON CONFLICT (slug) DO UPDATE SET
                    name = EXCLUDED.name,
                    monthly_price = EXCLUDED.monthly_price,
                    max_sites = EXCLUDED.max_sites,
                    max_published = EXCLUDED.max_published,
                    sort_order = EXCLUDED.sort_order,
                    active = true,
                    modified = EXCLUDED.modified"
            );
        }
    }
}
