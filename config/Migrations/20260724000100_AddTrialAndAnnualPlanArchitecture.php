<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddTrialAndAnnualPlanArchitecture extends BaseMigration
{
    public function up(): void
    {
        $this->table('plans')
            ->addColumn('annual_price', 'integer', ['null' => true, 'after' => 'monthly_price'])
            ->addColumn('commercial_description', 'string', ['limit' => 255, 'null' => true, 'after' => 'active'])
            ->addColumn('commercial_badge', 'string', ['limit' => 60, 'null' => true, 'after' => 'commercial_description'])
            ->addColumn('annual_benefits', 'json', ['null' => true, 'after' => 'commercial_badge'])
            ->update();

        $this->table('users')
            ->addColumn('trial_used_at', 'datetime', ['null' => true, 'after' => 'verification_sent_at'])
            ->update();

        $this->table('subscriptions')
            ->addColumn('billing_cycle', 'string', ['limit' => 20, 'default' => 'monthly', 'after' => 'plan_slug'])
            ->addColumn('trial_started_at', 'datetime', ['null' => true, 'after' => 'ends_at'])
            ->addColumn('trial_registration_expires_at', 'datetime', ['null' => true, 'after' => 'trial_started_at'])
            ->addIndex(['status', 'trial_registration_expires_at'], ['name' => 'subscriptions_trial_expiry_index'])
            ->update();

        $this->table('payments')
            ->addColumn('billing_cycle', 'string', ['limit' => 20, 'default' => 'monthly', 'after' => 'plan_slug'])
            ->update();

        $now = date('Y-m-d H:i:s');
        $plans = [
            [
                'name' => 'Prueba gratuita',
                'slug' => 'trial',
                'monthly_price' => 0,
                'annual_price' => null,
                'max_sites' => 1,
                'max_published' => 1,
                'sort_order' => 0,
                'description' => 'Prueba CatOps durante 7 días al publicar tu primer sitio.',
                'badge' => null,
                'annual_benefits' => [],
                'capabilities' => [
                    'trial_enabled' => true,
                    'trial_duration_days' => 7,
                    'trial_expire_after_registration_days' => 14,
                    'sites_configured_limit' => 1,
                    'sites_published_limit' => 1,
                    'items_limit' => 20,
                    'categories_limit' => 3,
                    'image_storage_limit_mb' => 100,
                    'categories_enabled' => true,
                    'whatsapp_enabled' => true,
                    'featured_items_enabled' => false,
                    'customization_level' => 'basic',
                    'analytics_level' => 'none',
                    'seo_level' => 'basic',
                    'qr_enabled' => true,
                    'custom_domains_limit' => 0,
                    'domain_credit' => false,
                    'annual_available' => false,
                    'annual_price' => 0,
                    'branding_removable' => false,
                    'catops_branding_removable' => false,
                    'priority_support' => false,
                    'custom_domain_enabled' => false,
                    'premium_themes_enabled' => false,
                    'enabled_templates' => ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'],
                ],
            ],
            [
                'name' => 'Básico', 'slug' => 'basica', 'monthly_price' => 6990, 'annual_price' => 76900,
                'max_sites' => 1, 'max_published' => 1, 'sort_order' => 1,
                'description' => 'Para comenzar a mostrar tu negocio de forma simple y profesional.', 'badge' => null, 'annual_benefits' => [],
                'capabilities' => [
                    'trial_enabled' => false, 'trial_duration_days' => 0, 'trial_expire_after_registration_days' => 0,
                    'sites_configured_limit' => 1, 'sites_published_limit' => 1, 'items_limit' => 80, 'categories_limit' => 10, 'image_storage_limit_mb' => 250,
                    'categories_enabled' => true, 'whatsapp_enabled' => true, 'featured_items_enabled' => true, 'customization_level' => 'basic', 'analytics_level' => 'none', 'seo_level' => 'basic', 'qr_enabled' => true,
                    'custom_domains_limit' => 0, 'domain_credit' => false, 'annual_available' => true, 'annual_price' => 76900,
                    'branding_removable' => false, 'catops_branding_removable' => false, 'priority_support' => false,
                    'custom_domain_enabled' => false, 'premium_themes_enabled' => false,
                    'enabled_templates' => ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'],
                ],
            ],
            [
                'name' => 'Negocio', 'slug' => 'basica-avanzada', 'monthly_price' => 9990, 'annual_price' => 119900,
                'max_sites' => 3, 'max_published' => 3, 'sort_order' => 2,
                'description' => 'Para negocios que necesitan más contenido, categorías y personalización.', 'badge' => 'Recomendado', 'annual_benefits' => ['domain_credit' => true],
                'capabilities' => [
                    'trial_enabled' => false, 'trial_duration_days' => 0, 'trial_expire_after_registration_days' => 0,
                    'sites_configured_limit' => 3, 'sites_published_limit' => 3, 'items_limit' => 250, 'categories_limit' => 25, 'image_storage_limit_mb' => 750,
                    'categories_enabled' => true, 'whatsapp_enabled' => true, 'featured_items_enabled' => true, 'customization_level' => 'extended', 'analytics_level' => 'basic', 'seo_level' => 'standard', 'qr_enabled' => true,
                    'custom_domains_limit' => 1, 'domain_credit' => true, 'annual_available' => true, 'annual_price' => 119900,
                    'branding_removable' => false, 'catops_branding_removable' => false, 'priority_support' => false,
                    'custom_domain_enabled' => false, 'premium_themes_enabled' => false,
                    'enabled_templates' => ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'],
                ],
            ],
            [
                'name' => 'Full', 'slug' => 'full', 'monthly_price' => 16990, 'annual_price' => 189900,
                'max_sites' => 5, 'max_published' => 5, 'sort_order' => 3,
                'description' => 'Para administrar varias marcas, propuestas o negocios con herramientas avanzadas.', 'badge' => null, 'annual_benefits' => ['domain_credit' => true],
                'capabilities' => [
                    'trial_enabled' => false, 'trial_duration_days' => 0, 'trial_expire_after_registration_days' => 0,
                    'sites_configured_limit' => 5, 'sites_published_limit' => 5, 'items_limit' => 500, 'categories_limit' => 50, 'image_storage_limit_mb' => 2000,
                    'categories_enabled' => true, 'whatsapp_enabled' => true, 'featured_items_enabled' => true, 'customization_level' => 'advanced', 'analytics_level' => 'advanced', 'seo_level' => 'advanced', 'qr_enabled' => true,
                    'custom_domains_limit' => 5, 'domain_credit' => true, 'annual_available' => true, 'annual_price' => 189900,
                    'branding_removable' => true, 'catops_branding_removable' => true, 'priority_support' => true,
                    'custom_domain_enabled' => false, 'premium_themes_enabled' => true,
                    'enabled_templates' => ['carta-simple', 'carta-categorias', 'catalogo-simple', 'catalogo-categorias'],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            $name = str_replace("'", "''", $plan['name']);
            $slug = str_replace("'", "''", $plan['slug']);
            $description = str_replace("'", "''", $plan['description']);
            $badge = $plan['badge'] === null ? 'NULL' : "'" . str_replace("'", "''", $plan['badge']) . "'";
            $annualPrice = $plan['annual_price'] === null ? 'NULL' : (string)$plan['annual_price'];
            $annualBenefits = str_replace("'", "''", (string)json_encode($plan['annual_benefits'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $capabilities = str_replace("'", "''", (string)json_encode($plan['capabilities'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $this->execute(
                "INSERT INTO plans (name, slug, monthly_price, annual_price, max_sites, max_published, sort_order, active, commercial_description, commercial_badge, annual_benefits, capabilities, created, modified)
                 VALUES ('{$name}', '{$slug}', {$plan['monthly_price']}, {$annualPrice}, {$plan['max_sites']}, {$plan['max_published']}, {$plan['sort_order']}, true, '{$description}', {$badge}, '{$annualBenefits}', '{$capabilities}', '{$now}', '{$now}')
                 ON CONFLICT (slug) DO UPDATE SET
                    name = EXCLUDED.name,
                    monthly_price = EXCLUDED.monthly_price,
                    annual_price = EXCLUDED.annual_price,
                    max_sites = EXCLUDED.max_sites,
                    max_published = EXCLUDED.max_published,
                    sort_order = EXCLUDED.sort_order,
                    active = true,
                    commercial_description = EXCLUDED.commercial_description,
                    commercial_badge = EXCLUDED.commercial_badge,
                    annual_benefits = EXCLUDED.annual_benefits,
                    capabilities = EXCLUDED.capabilities,
                    modified = EXCLUDED.modified"
            );
        }
    }
}
