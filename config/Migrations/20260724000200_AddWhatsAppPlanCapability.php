<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddWhatsAppPlanCapability extends BaseMigration
{
    public function up(): void
    {
        foreach ($this->fetchAll('SELECT id, capabilities FROM plans') as $plan) {
            $capabilities = json_decode((string)($plan['capabilities'] ?? '{}'), true);
            $capabilities = is_array($capabilities) ? $capabilities : [];
            $capabilities['whatsapp_enabled'] = true;
            $json = str_replace("'", "''", (string)json_encode($capabilities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->execute("UPDATE plans SET capabilities = '{$json}', modified = CURRENT_TIMESTAMP WHERE id = " . (int)$plan['id']);
        }
    }
}
