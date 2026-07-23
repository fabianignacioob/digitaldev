<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ReworkCatalogPlansAndContact extends BaseMigration
{
    public function change(): void
    {
        $this->table('sites')
            ->addColumn('whatsapp_country_code', 'string', ['default' => '56', 'limit' => 8])
            ->addColumn('whatsapp_number', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('instagram_username', 'string', ['limit' => 80, 'null' => true])
            ->update();

        $this->table('catalog_settings')
            ->addColumn('background_preset', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('title_color', 'string', ['default' => '#ffffff', 'limit' => 20])
            ->addColumn('title_font', 'string', [
                'default' => 'Inter, Arial, sans-serif',
                'limit' => 120,
            ])
            ->addColumn('slogan_color', 'string', ['default' => '#ffffff', 'limit' => 20])
            ->addColumn('slogan_font', 'string', [
                'default' => 'Inter, Arial, sans-serif',
                'limit' => 120,
            ])
            ->update();

        $this->table('payments')
            ->addColumn('user_id', 'integer')
            ->addColumn('subscription_id', 'integer', ['null' => true])
            ->addColumn('plan_slug', 'string', ['limit' => 40])
            ->addColumn('status', 'string', ['default' => 'paid', 'limit' => 30])
            ->addColumn('amount', 'decimal', ['precision' => 12, 'scale' => 2, 'default' => 0])
            ->addColumn('currency', 'string', ['default' => 'CLP', 'limit' => 10])
            ->addColumn('provider', 'string', ['default' => 'manual', 'limit' => 60])
            ->addColumn('provider_reference', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('paid_at', 'datetime')
            ->addColumn('period_start', 'datetime')
            ->addColumn('period_end', 'datetime')
            ->addTimestamps('created', 'modified')
            ->addIndex(['user_id', 'status'])
            ->addIndex(['plan_slug'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('subscription_id', 'subscriptions', 'id', ['delete' => 'SET_NULL'])
            ->create();

        $now = date('Y-m-d H:i:s');
        $periodEnd = date('Y-m-d H:i:s', strtotime('+30 days'));

        $this->execute("UPDATE templates SET active = false WHERE slug IN ('landing-simple', 'catalogo-ligero')");
        $this->execute("UPDATE subscriptions SET plan_slug = 'basica' WHERE plan_slug = 'basico'");
        $this->execute("UPDATE subscriptions SET plan_slug = 'full' WHERE plan_slug = 'completo'");
        // Never assign a plan to an arbitrary account during an environment migration.
        // Existing installations already ran this migration; fresh installations keep
        // their subscription data unchanged until an explicit plan action occurs.
        $this->execute("UPDATE subscriptions SET ends_at = '{$periodEnd}' WHERE status = 'active' AND ends_at IS NULL");

        $templates = [
            [
                'name' => 'Carta simple',
                'slug' => 'carta-simple',
                'description' => 'Carta visual sin categorías para mostrar productos con foto, descripción y valor.',
                'preview_image' => 'img/catalog-backgrounds/menu-parchment.png',
            ],
            [
                'name' => 'Carta por categoría',
                'slug' => 'carta-categorias',
                'description' => 'Carta visual con categorías para ordenar productos por familia o tipo.',
                'preview_image' => 'img/catalog-backgrounds/menu-wood.png',
            ],
            [
                'name' => 'Catálogo simple',
                'slug' => 'catalogo-simple',
                'description' => 'Catálogo sin categorías para mostrar productos o servicios de forma directa.',
                'preview_image' => 'img/idea-servicios-bg.png',
            ],
            [
                'name' => 'Catálogo por categoría',
                'slug' => 'catalogo-categorias',
                'description' => 'Catálogo con categorías para negocios con varias familias de productos o servicios.',
                'preview_image' => 'img/idea-corporex-bg.png',
            ],
        ];

        foreach ($templates as $template) {
            $name = addslashes($template['name']);
            $slug = addslashes($template['slug']);
            $description = addslashes($template['description']);
            $preview = addslashes($template['preview_image']);
            $this->execute(
                "INSERT INTO templates (name, slug, description, preview_image, active, created, modified)
                 VALUES ('{$name}', '{$slug}', '{$description}', '{$preview}', true, '{$now}', '{$now}')
                 ON CONFLICT (slug) DO UPDATE SET
                    name = EXCLUDED.name,
                    description = EXCLUDED.description,
                    preview_image = EXCLUDED.preview_image,
                    active = true,
                    modified = EXCLUDED.modified"
            );
        }

        $this->execute(
            "INSERT INTO payments (user_id, subscription_id, plan_slug, status, amount, currency, provider, provider_reference, paid_at, period_start, period_end, created, modified)
             SELECT s.user_id, s.id, s.plan_slug, 'paid', 0, 'CLP', 'manual', 'backfill-local', '{$now}', '{$now}', COALESCE(s.ends_at, '{$periodEnd}'), '{$now}', '{$now}'
             FROM subscriptions s
             WHERE s.status = 'active'
             AND NOT EXISTS (
                SELECT 1 FROM payments p
                WHERE p.subscription_id = s.id
                AND p.status = 'paid'
             )"
        );
    }
}
