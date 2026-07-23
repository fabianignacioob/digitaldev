<?php
declare(strict_types=1);

namespace App\Test\TestCase\Migration;

use Cake\TestSuite\TestCase;

class MigrationSafetyTest extends TestCase
{
    public function testCatalogReworkMigrationDoesNotTargetAnArbitraryUser(): void
    {
        $migration = file_get_contents(CONFIG . 'Migrations/20260712000100_ReworkCatalogPlansAndContact.php');

        $this->assertIsString($migration);
        $this->assertStringNotContainsString("UPDATE subscriptions SET plan_slug = 'basica' WHERE user_id = 1", $migration);
    }
}
