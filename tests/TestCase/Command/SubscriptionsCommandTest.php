<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Service\SubscriptionService;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

class SubscriptionsCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('SUBSCRIPTION_GRACE_DAYS=3');
        putenv('SUBSCRIPTION_EXPIRING_WINDOW_DAYS=7');
        $this->cleanLifecycleTables();
    }

    public function testDryRunDoesNotModifyData(): void
    {
        $userId = $this->createUser();
        $subscription = $this->createSubscription($userId, 'active', DateTime::now()->subDays(1));

        $this->exec('subscriptions process_expirations --dry-run');

        $this->assertExitSuccess();
        $this->assertOutputContains('dry_run=sí');
        $subscription = $this->table('Subscriptions')->get($subscription->id);
        $this->assertSame('active', $subscription->status);
        $run = $this->table('OperationalProcessRuns')->find()
            ->where(['process_name' => 'subscriptions.process_expirations'])
            ->orderByDesc('id')
            ->firstOrFail();
        $this->assertSame('success', $run->status);
        $metadata = is_array($run->metadata)
            ? $run->metadata
            : json_decode((string)$run->metadata, true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue((bool)($metadata['dry_run'] ?? false));
    }

    public function testProcessExpirationsIsIdempotentAndPausesSites(): void
    {
        $userId = $this->createUser();
        $subscription = $this->createSubscription($userId, 'grace_period', DateTime::now()->subDays(5), DateTime::now()->subDays(1));
        $siteId = $this->createSite($userId, 'published');

        $this->exec('subscriptions process_expirations');
        $this->assertExitSuccess();
        $this->assertOutputContains('procesados=1');

        $subscription = $this->table('Subscriptions')->get($subscription->id);
        $site = $this->table('Sites')->get($siteId);
        $this->assertSame('expired', $subscription->status);
        $this->assertSame('paused', $site->status);
        $this->assertSame(SubscriptionService::SITE_PAUSED_SUBSCRIPTION_EXPIRED, $site->paused_reason);

        $this->exec('subscriptions process_expirations');
        $this->assertExitSuccess();
        $this->assertOutputContains('procesados=0');
    }

    public function testExpiringAndGraceTransitions(): void
    {
        $expiringUserId = $this->createUser();
        $graceUserId = $this->createUser();
        $expiring = $this->createSubscription($expiringUserId, 'active', DateTime::now()->addDays(2));
        $grace = $this->createSubscription($graceUserId, 'active', DateTime::now()->subHours(1));

        $this->exec('subscriptions process_expirations');

        $this->assertExitSuccess();
        $this->assertSame('expiring', $this->table('Subscriptions')->get($expiring->id)->status);
        $this->assertSame('grace_period', $this->table('Subscriptions')->get($grace->id)->status);
    }

    public function testReminderCommandReportsBuckets(): void
    {
        $this->createSubscription($this->createUser(), 'active', DateTime::now()->addDays(7));
        $this->createSubscription($this->createUser(), 'active', DateTime::now()->addDays(3));
        $this->createSubscription($this->createUser(), 'active', DateTime::now()->addDays(1));

        $this->exec('subscriptions reminders');

        $this->assertExitSuccess();
        $this->assertOutputContains('vencen en 7 días');
        $this->assertOutputContains('vencen en 3 días');
        $this->assertOutputContains('vencen en 1 días');
        $this->assertOutputContains('vencen hoy');
    }

    private function createUser(): int
    {
        $user = $this->table('Users')->newEntity([
            'name' => 'Cliente Command',
            'email' => 'command-' . uniqid() . '@example.test',
            'password' => 'secret123',
            'role' => 'customer',
            'active' => true,
            'email_verified' => true,
        ]);
        $this->table('Users')->saveOrFail($user);

        return (int)$user->id;
    }

    private function createSubscription(int $userId, string $status, DateTime $endsAt, ?DateTime $graceEndsAt = null): object
    {
        $subscription = $this->table('Subscriptions')->newEntity([
            'user_id' => $userId,
            'plan_slug' => 'basica',
            'status' => $status,
            'starts_at' => DateTime::now()->subDays(30),
            'ends_at' => $endsAt,
            'grace_ends_at' => $graceEndsAt,
        ]);
        $this->table('Subscriptions')->saveOrFail($subscription);

        return $subscription;
    }

    private function createSite(int $userId, string $status): int
    {
        $site = $this->table('Sites')->newEntity([
            'user_id' => $userId,
            'template_id' => $this->ensureTemplate(),
            'theme_id' => $this->ensureTheme(),
            'name' => 'Sitio Command',
            'slug' => 'command-' . uniqid(),
            'subdomain' => 'command-' . uniqid(),
            'status' => $status,
            'whatsapp_country_code' => '56',
        ]);
        $this->table('Sites')->saveOrFail($site);

        return (int)$site->id;
    }

    private function ensureTemplate(): int
    {
        $template = $this->table('Templates')->find()->where(['slug' => 'carta-simple'])->first();
        if (!$template) {
            $template = $this->table('Templates')->newEntity([
                'name' => 'Carta simple',
                'slug' => 'carta-simple',
                'active' => true,
            ]);
            $this->table('Templates')->saveOrFail($template);
        }

        return (int)$template->id;
    }

    private function ensureTheme(): int
    {
        $theme = $this->table('Themes')->find()->where(['slug' => 'catops-command'])->first();
        if (!$theme) {
            $theme = $this->table('Themes')->newEntity([
                'name' => 'CatOps Command',
                'slug' => 'catops-command',
                'primary_color' => '#f36b16',
                'secondary_color' => '#0a2a66',
                'background_color' => '#fbfaf7',
                'font_family' => 'Inter, Arial, sans-serif',
                'active' => true,
            ]);
            $this->table('Themes')->saveOrFail($theme);
        }

        return (int)$theme->id;
    }

    private function table(string $name): object
    {
        return FactoryLocator::get('Table')->get($name);
    }

    private function cleanLifecycleTables(): void
    {
        $this->table('OperationalProcessRuns')->deleteAll([]);
        $this->table('AuditLogs')->deleteAll([]);
        $this->table('Payments')->deleteAll([]);
        $this->table('Sites')->deleteAll([]);
        $this->table('Subscriptions')->deleteAll([]);
        $this->table('Users')->deleteAll(['email LIKE' => 'command-%']);
    }
}
