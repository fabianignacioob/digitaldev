<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AlignPlanBenefitsAndCatalogPersonalization extends BaseMigration
{
    public function up(): void
    {
        $this->table('catalog_settings')
            ->addColumn('category_layout', 'string', ['default' => 'normal', 'limit' => 20, 'after' => 'show_product_action'])
            ->update();

        $this->table('catalog_products')
            ->addColumn('seo_description', 'string', ['limit' => 180, 'null' => true, 'after' => 'description'])
            ->addColumn('seo_keywords', 'string', ['limit' => 255, 'null' => true, 'after' => 'seo_description'])
            ->update();

        $plans = [
            'trial' => [
                'name' => 'Prueba gratuita',
                'max_sites' => 1,
                'max_published' => 1,
                'description' => 'Prueba las herramientas esenciales de CatOps durante 7 días.',
                'badge' => null,
                'capabilities' => $this->capabilities(1, 1, false, 3, 'basic', 'none', 'basic', false, false, 0, 20, 0, 100, true),
            ],
            'basica' => [
                'name' => 'Básico',
                'max_sites' => 1,
                'max_published' => 1,
                'description' => 'Para comenzar a mostrar tu negocio online.',
                'badge' => null,
                'capabilities' => $this->capabilities(1, 1, false, 3, 'basic', 'none', 'basic', false, false, 0, 80, 0, 250),
            ],
            'basica-avanzada' => [
                'name' => 'Negocio',
                'max_sites' => 3,
                'max_published' => 2,
                'description' => 'Para negocios que necesitan más organización y presencia digital.',
                'badge' => 'Recomendado',
                'capabilities' => $this->capabilities(3, 2, true, 10, 'extended', 'basic', 'standard', true, true, 1, 250, 25, 750),
            ],
            'full' => [
                'name' => 'Full',
                'max_sites' => 5,
                'max_published' => 5,
                'description' => 'Para negocios con varias marcas, sucursales o líneas de servicio.',
                'badge' => null,
                'capabilities' => $this->capabilities(5, 5, true, 0, 'advanced', 'advanced', 'advanced', true, true, 5, 500, 50, 2000),
            ],
        ];

        foreach ($plans as $slug => $plan) {
            $json = str_replace("'", "''", (string)json_encode($plan['capabilities'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $name = str_replace("'", "''", $plan['name']);
            $description = str_replace("'", "''", $plan['description']);
            $badge = $plan['badge'] === null ? 'NULL' : "'" . str_replace("'", "''", $plan['badge']) . "'";
            $safeSlug = str_replace("'", "''", $slug);
            $this->execute(
                "UPDATE plans
                 SET name = '{$name}',
                     max_sites = {$plan['max_sites']},
                     max_published = {$plan['max_published']},
                     commercial_description = '{$description}',
                     commercial_badge = {$badge},
                     capabilities = '{$json}',
                     modified = CURRENT_TIMESTAMP
                 WHERE slug = '{$safeSlug}'"
            );
        }
    }

    /** @return array<string, mixed> */
    private function capabilities(
        int $configured,
        int $published,
        bool $categories,
        int $featuredLimit,
        string $customization,
        string $analytics,
        string $seo,
        bool $qr,
        bool $customDomain,
        int $customDomainLimit,
        int $items,
        int $categoryLimit,
        int $storage,
        bool $trial = false,
    ): array {
        return [
            'sites_configured_limit' => $configured,
            'sites_published_limit' => $published,
            'items_limit' => $items,
            'categories_limit' => $categoryLimit,
            'featured_items_limit' => $featuredLimit,
            'image_storage_limit_mb' => $storage,
            'categories_enabled' => $categories,
            'whatsapp_enabled' => true,
            'featured_items_enabled' => true,
            'customization_level' => $customization,
            'analytics_level' => $analytics,
            'seo_level' => $seo,
            'qr_enabled' => $qr,
            'custom_domain_enabled' => $customDomain,
            'custom_domains_limit' => $customDomainLimit,
            'premium_themes_enabled' => $customization === 'advanced',
            'catops_branding_removable' => $customization === 'advanced',
            'priority_support' => $customization === 'advanced',
            'trial_enabled' => $trial,
            'trial_duration_days' => $trial ? 7 : 0,
            'trial_expire_after_registration_days' => $trial ? 14 : 0,
            'annual_available' => !$trial,
            'enabled_templates' => $categories
                ? ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias']
                : ['carta-simple', 'catalogo-simple'],
        ];
    }
}
