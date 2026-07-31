<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddWebpayIntegrationTestPlan extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $capabilities = json_encode([
            'annual_available' => false,
            'annual_price' => 0,
            'sites_configured_limit' => 0,
            'sites_published_limit' => 0,
            'items_limit' => 0,
            'categories_limit' => 0,
            'image_storage_limit_mb' => 0,
            'enabled_templates' => [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $capabilities = str_replace("'", "''", (string)$capabilities);

        $this->execute(
            "INSERT INTO plans (name, slug, monthly_price, annual_price, max_sites, max_published, sort_order, active, commercial_description, commercial_badge, annual_benefits, capabilities, created, modified)
             VALUES ('Prueba interna Webpay', 'webpay-integration-test', 1, NULL, 0, 0, 999, false, 'Orden interna de $1 para comprobar Webpay Plus en integración.', NULL, '[]', '{$capabilities}', '{$now}', '{$now}')
             ON CONFLICT (slug) DO UPDATE SET
                name = EXCLUDED.name,
                monthly_price = EXCLUDED.monthly_price,
                annual_price = EXCLUDED.annual_price,
                max_sites = EXCLUDED.max_sites,
                max_published = EXCLUDED.max_published,
                sort_order = EXCLUDED.sort_order,
                active = false,
                commercial_description = EXCLUDED.commercial_description,
                commercial_badge = NULL,
                annual_benefits = '[]',
                capabilities = EXCLUDED.capabilities,
                modified = EXCLUDED.modified"
        );
    }

    public function down(): void
    {
        $this->execute("DELETE FROM plans WHERE slug = 'webpay-integration-test'");
    }
}
