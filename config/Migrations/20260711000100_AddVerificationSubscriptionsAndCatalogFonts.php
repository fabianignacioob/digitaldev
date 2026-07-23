<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddVerificationSubscriptionsAndCatalogFonts extends BaseMigration
{
    public function change(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('users')
            ->addColumn('email_verified', 'boolean', ['default' => true])
            ->addColumn('verification_code_hash', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('verification_expires', 'datetime', ['null' => true])
            ->addColumn('verification_sent_at', 'datetime', ['null' => true])
            ->update();

        $this->table('subscriptions')
            ->addColumn('user_id', 'integer')
            ->addColumn('plan_slug', 'string', ['limit' => 40])
            ->addColumn('status', 'string', ['default' => 'active', 'limit' => 30])
            ->addColumn('starts_at', 'datetime')
            ->addColumn('ends_at', 'datetime', ['null' => true])
            ->addColumn('notes', 'text', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['user_id', 'plan_slug'])
            ->addIndex(['user_id', 'status'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('catalog_settings')
            ->addColumn('heading_font', 'string', [
                'default' => 'Inter, Arial, sans-serif',
                'limit' => 120,
            ])
            ->update();

        $this->execute(
            "INSERT INTO subscriptions (user_id, plan_slug, status, starts_at, notes, created, modified)
             SELECT id, 'basico', 'active', '{$now}', 'Backfill local para cuentas existentes.', '{$now}', '{$now}'
             FROM users
             WHERE active = true
             AND NOT EXISTS (
                 SELECT 1 FROM subscriptions
                 WHERE subscriptions.user_id = users.id
                 AND subscriptions.status = 'active'
             )"
        );
    }
}
