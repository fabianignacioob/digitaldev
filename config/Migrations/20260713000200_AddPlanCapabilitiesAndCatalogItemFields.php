<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPlanCapabilitiesAndCatalogItemFields extends BaseMigration
{
    public function change(): void
    {
        $this->table('plans')
            ->addColumn('capabilities', 'json', ['default' => '{}'])
            ->update();

        $this->table('catalog_products')
            ->addColumn('item_type', 'string', ['default' => 'product', 'limit' => 30])
            ->addColumn('price_prefix', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('duration', 'string', ['limit' => 80, 'null' => true])
            ->addColumn('featured', 'boolean', ['default' => false])
            ->changeColumn('price', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true])
            ->addIndex(['site_id', 'item_type'])
            ->update();

        $this->seedCapabilities();
    }

    private function seedCapabilities(): void
    {
        $plans = [
            'basica' => [
                'sites_configured_limit' => 1,
                'sites_published_limit' => 1,
                'items_limit' => 40,
                'categories_enabled' => false,
                'custom_domain' => false,
                'premium_themes' => false,
                'advanced_customization' => false,
                'seo_advanced' => false,
                'analytics' => false,
                'ai_item_descriptions' => false,
                'ai_landing_builder' => false,
                'enabled_templates' => ['carta-simple', 'catalogo-simple'],
            ],
            'basica-avanzada' => [
                'sites_configured_limit' => 5,
                'sites_published_limit' => 3,
                'items_limit' => 200,
                'categories_enabled' => true,
                'custom_domain' => false,
                'premium_themes' => false,
                'advanced_customization' => true,
                'seo_advanced' => false,
                'analytics' => false,
                'ai_item_descriptions' => false,
                'ai_landing_builder' => false,
                'enabled_templates' => ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'],
            ],
            'full' => [
                'sites_configured_limit' => 7,
                'sites_published_limit' => 5,
                'items_limit' => 500,
                'categories_enabled' => true,
                'custom_domain' => true,
                'premium_themes' => true,
                'advanced_customization' => true,
                'seo_advanced' => true,
                'analytics' => false,
                'ai_item_descriptions' => false,
                'ai_landing_builder' => false,
                'enabled_templates' => ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'],
            ],
        ];

        foreach ($plans as $slug => $capabilities) {
            $json = str_replace("'", "''", (string)json_encode($capabilities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $slug = str_replace("'", "''", $slug);
            $this->execute("UPDATE plans SET capabilities = '{$json}' WHERE slug = '{$slug}'");
        }
    }
}
