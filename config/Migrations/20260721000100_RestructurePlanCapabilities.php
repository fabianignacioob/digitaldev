<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RestructurePlanCapabilities extends BaseMigration
{
    public function up(): void
    {
        $plans = [
            'basica' => [
                'name' => 'Básico',
                'price' => 6990,
                'max_sites' => 1,
                'max_published' => 1,
                'capabilities' => [
                    'sites_configured_limit' => 1,
                    'sites_published_limit' => 1,
                    'items_limit' => 40,
                    'categories_limit' => 0,
                    'image_storage_limit_mb' => 100,
                    'categories_enabled' => false,
                    'featured_items_enabled' => false,
                    'customization_level' => 'basic',
                    'analytics_level' => 'none',
                    'seo_level' => 'basic',
                    'qr_enabled' => false,
                    'custom_domain_enabled' => false,
                    'premium_themes_enabled' => false,
                    'catops_branding_removable' => false,
                    'priority_support' => false,
                    'enabled_templates' => ['carta-simple', 'catalogo-simple'],
                ],
            ],
            // The stable slug is kept to avoid breaking existing subscriptions.
            'basica-avanzada' => [
                'name' => 'Negocio',
                'price' => 9990,
                'max_sites' => 3,
                'max_published' => 3,
                'capabilities' => [
                    'sites_configured_limit' => 3,
                    'sites_published_limit' => 3,
                    'items_limit' => 200,
                    'categories_limit' => 20,
                    'image_storage_limit_mb' => 500,
                    'categories_enabled' => true,
                    'featured_items_enabled' => true,
                    'customization_level' => 'extended',
                    'analytics_level' => 'basic',
                    'seo_level' => 'standard',
                    'qr_enabled' => true,
                    'custom_domain_enabled' => false,
                    'premium_themes_enabled' => false,
                    'catops_branding_removable' => false,
                    'priority_support' => false,
                    'enabled_templates' => ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'],
                ],
            ],
            'full' => [
                'name' => 'Full',
                'price' => 16990,
                'max_sites' => 5,
                'max_published' => 5,
                'capabilities' => [
                    'sites_configured_limit' => 5,
                    'sites_published_limit' => 5,
                    'items_limit' => 500,
                    'categories_limit' => 50,
                    'image_storage_limit_mb' => 2000,
                    'categories_enabled' => true,
                    'featured_items_enabled' => true,
                    'customization_level' => 'advanced',
                    'analytics_level' => 'advanced',
                    'seo_level' => 'advanced',
                    'qr_enabled' => true,
                    'custom_domain_enabled' => false,
                    'premium_themes_enabled' => true,
                    'catops_branding_removable' => true,
                    'priority_support' => true,
                    'enabled_templates' => ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'],
                ],
            ],
        ];

        foreach ($plans as $slug => $plan) {
            $json = str_replace("'", "''", (string)json_encode($plan['capabilities'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $name = str_replace("'", "''", $plan['name']);
            $slug = str_replace("'", "''", $slug);
            $this->execute(
                "UPDATE plans
                 SET name = '{$name}',
                     monthly_price = {$plan['price']},
                     max_sites = {$plan['max_sites']},
                     max_published = {$plan['max_published']},
                     capabilities = '{$json}',
                     modified = CURRENT_TIMESTAMP
                 WHERE slug = '{$slug}'"
            );
        }
    }
}
