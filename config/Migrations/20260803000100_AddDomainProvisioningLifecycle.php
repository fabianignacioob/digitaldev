<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddDomainProvisioningLifecycle extends BaseMigration
{
    public function up(): void
    {
        $this->table('domains')
            ->addColumn('status', 'string', ['limit' => 30, 'default' => 'pending_dns', 'after' => 'active'])
            ->addColumn('provisioning_started_at', 'datetime', ['null' => true, 'after' => 'last_dns_error'])
            ->addColumn('provisioning_last_attempt_at', 'datetime', ['null' => true, 'after' => 'provisioning_started_at'])
            ->addColumn('provisioning_attempts', 'integer', ['default' => 0, 'after' => 'provisioning_last_attempt_at'])
            ->addColumn('provisioned_at', 'datetime', ['null' => true, 'after' => 'provisioning_attempts'])
            ->addColumn('provisioning_summary', 'text', ['null' => true, 'after' => 'provisioned_at'])
            ->addColumn('provisioning_error', 'string', ['limit' => 500, 'null' => true, 'after' => 'provisioning_summary'])
            ->addIndex(['type', 'status'], ['name' => 'domains_provisioning_index'])
            ->update();

        $this->execute("UPDATE domains SET status = CASE WHEN type = 'subdomain' THEN 'active' WHEN verified = true AND active = true THEN 'active' WHEN verified = true THEN 'verified' ELSE 'pending_dns' END");
    }

    public function down(): void
    {
        $this->table('domains')
            ->removeIndexByName('domains_provisioning_index')
            ->removeColumn('status')->removeColumn('provisioning_started_at')->removeColumn('provisioning_last_attempt_at')
            ->removeColumn('provisioning_attempts')->removeColumn('provisioned_at')->removeColumn('provisioning_summary')->removeColumn('provisioning_error')
            ->update();
    }
}
