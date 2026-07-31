<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCustomDomainVerification extends BaseMigration
{
    public function up(): void
    {
        $this->table('domains')
            ->addColumn('verification_token', 'string', ['limit' => 120, 'null' => true, 'after' => 'active'])
            ->addColumn('verification_method', 'string', ['limit' => 30, 'null' => true, 'after' => 'verification_token'])
            ->addColumn('verification_requested_at', 'datetime', ['null' => true, 'after' => 'verification_method'])
            ->addColumn('verification_checked_at', 'datetime', ['null' => true, 'after' => 'verification_requested_at'])
            ->addColumn('verified_at', 'datetime', ['null' => true, 'after' => 'verification_checked_at'])
            ->addColumn('last_dns_error', 'string', ['limit' => 500, 'null' => true, 'after' => 'verified_at'])
            ->addIndex(['type', 'verified', 'active'], ['name' => 'domains_resolution_index'])
            ->update();

        $plans = $this->fetchAll('SELECT id, slug, capabilities FROM plans WHERE slug IN (\'basica-avanzada\', \'full\')');
        foreach ($plans as $plan) {
            $capabilities = json_decode((string)$plan['capabilities'], true) ?: [];
            $capabilities['custom_domain_enabled'] = true;
            $this->execute(sprintf(
                "UPDATE plans SET capabilities = '%s' WHERE id = %d",
                str_replace("'", "''", json_encode($capabilities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                (int)$plan['id'],
            ));
        }
    }

    public function down(): void
    {
        $this->table('domains')
            ->removeIndexByName('domains_resolution_index')
            ->removeColumn('verification_token')
            ->removeColumn('verification_method')
            ->removeColumn('verification_requested_at')
            ->removeColumn('verification_checked_at')
            ->removeColumn('verified_at')
            ->removeColumn('last_dns_error')
            ->update();
    }
}
