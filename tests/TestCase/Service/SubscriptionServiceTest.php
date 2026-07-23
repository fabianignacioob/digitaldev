<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\SubscriptionService;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

class SubscriptionServiceTest extends TestCase
{
    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('SUBSCRIPTION_DURATION_DAYS=30');
        putenv('SUBSCRIPTION_GRACE_DAYS=3');
        $this->service = new SubscriptionService();
    }

    public function testActiveSubscription(): void
    {
        $subscription = $this->createSubscription($this->createUser(), 'active', DateTime::now()->addDays(10));

        $this->assertTrue($this->service->isActive($subscription));
    }

    public function testRenewBeforeExpirationExtendsFromCurrentEnd(): void
    {
        $userId = $this->createUser();
        $currentEnd = DateTime::now()->addDays(10);
        $subscription = $this->createSubscription($userId, 'active', $currentEnd);
        $payment = $this->createPayment($userId, (int)$subscription->id, 'renew-before');

        $this->service->renew($subscription, $payment, 30);

        $subscription = $this->table('Subscriptions')->get($subscription->id);
        $payment = $this->table('Payments')->get($payment->id);
        $this->assertSame('active', $subscription->status);
        $this->assertEquals($currentEnd->addDays(30)->format('Y-m-d'), $subscription->ends_at->format('Y-m-d'));
        $this->assertNotEmpty($payment->processed_at);
    }

    public function testRenewAfterExpirationStartsFromNow(): void
    {
        $userId = $this->createUser();
        $subscription = $this->createSubscription($userId, 'expired', DateTime::now()->subDays(10));
        $payment = $this->createPayment($userId, (int)$subscription->id, 'renew-after');

        $this->service->renew($subscription, $payment, 30);

        $subscription = $this->table('Subscriptions')->get($subscription->id);
        $this->assertSame('active', $subscription->status);
        $this->assertEquals(DateTime::now()->addDays(30)->format('Y-m-d'), $subscription->ends_at->format('Y-m-d'));
    }

    public function testDuplicatePaymentIsIgnored(): void
    {
        $userId = $this->createUser();
        $subscription = $this->createSubscription($userId, 'active', DateTime::now()->addDays(5));
        $payment = $this->createPayment($userId, (int)$subscription->id, 'duplicate-payment');

        $this->service->renew($subscription, $payment, 30);
        $firstEnd = $this->table('Subscriptions')->get($subscription->id)->ends_at;
        $this->service->renew($this->table('Subscriptions')->get($subscription->id), $this->table('Payments')->get($payment->id), 30);
        $secondEnd = $this->table('Subscriptions')->get($subscription->id)->ends_at;

        $this->assertEquals($firstEnd, $secondEnd);
    }

    public function testGraceExpirationPauseAndRenewalReactivation(): void
    {
        $userId = $this->createUser();
        $subscription = $this->createSubscription($userId, 'active', DateTime::now()->subDays(1));
        $siteId = $this->createSite($userId, 'published');

        $this->service->enterGracePeriod($subscription);
        $subscription = $this->table('Subscriptions')->get($subscription->id);
        $this->assertSame('grace_period', $subscription->status);
        $this->assertTrue($this->service->isActive($subscription));

        $subscription->grace_ends_at = DateTime::now()->subDays(1);
        $this->table('Subscriptions')->saveOrFail($subscription);
        $this->service->expire($subscription);

        $site = $this->table('Sites')->get($siteId);
        $this->assertSame('paused', $site->status);
        $this->assertSame(SubscriptionService::SITE_PAUSED_SUBSCRIPTION_EXPIRED, $site->paused_reason);

        $payment = $this->createPayment($userId, (int)$subscription->id, 'reactivate-payment');
        $this->service->renew($this->table('Subscriptions')->get($subscription->id), $payment, 30);
        $site = $this->table('Sites')->get($siteId);
        $this->assertSame('published', $site->status);
        $this->assertNull($site->paused_reason);
    }

    public function testManuallyPausedSiteIsNotReactivated(): void
    {
        $userId = $this->createUser();
        $subscription = $this->createSubscription($userId, 'expired', DateTime::now()->subDays(1));
        $siteId = $this->createSite($userId, 'paused', null);
        $payment = $this->createPayment($userId, (int)$subscription->id, 'manual-pause');

        $this->service->renew($subscription, $payment, 30);

        $site = $this->table('Sites')->get($siteId);
        $this->assertSame('paused', $site->status);
        $this->assertNull($site->paused_reason);
    }

    private function createUser(): int
    {
        $user = $this->table('Users')->newEntity([
            'name' => 'Cliente Servicio',
            'email' => 'servicio-' . uniqid() . '@example.test',
            'password' => 'secret123',
            'role' => 'customer',
            'active' => true,
            'email_verified' => true,
        ]);
        $this->table('Users')->saveOrFail($user);

        return (int)$user->id;
    }

    private function createSubscription(int $userId, string $status, DateTime $endsAt): object
    {
        $subscription = $this->table('Subscriptions')->newEntity([
            'user_id' => $userId,
            'plan_slug' => 'basica',
            'status' => $status,
            'starts_at' => DateTime::now()->subDays(30),
            'ends_at' => $endsAt,
        ]);
        $this->table('Subscriptions')->saveOrFail($subscription);

        return $subscription;
    }

    private function createPayment(int $userId, int $subscriptionId, string $reference): object
    {
        $now = DateTime::now();
        $payment = $this->table('Payments')->newEntity([
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
            'plan_slug' => 'basica',
            'status' => 'paid',
            'amount' => 6990,
            'currency' => 'CLP',
            'provider' => 'manual',
            'provider_reference' => $reference . '-' . uniqid(),
            'paid_at' => $now,
            'period_start' => $now,
            'period_end' => $now,
        ]);
        $this->table('Payments')->saveOrFail($payment);

        return $payment;
    }

    private function createSite(int $userId, string $status, ?string $pausedReason = null): int
    {
        $templateId = $this->ensureTemplate();
        $themeId = $this->ensureTheme();
        $site = $this->table('Sites')->newEntity([
            'user_id' => $userId,
            'template_id' => $templateId,
            'theme_id' => $themeId,
            'name' => 'Sitio Servicio',
            'slug' => 'servicio-' . uniqid(),
            'subdomain' => 'servicio-' . uniqid(),
            'status' => $status,
            'paused_reason' => $pausedReason,
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
        $theme = $this->table('Themes')->find()->where(['slug' => 'catops-test'])->first();
        if (!$theme) {
            $theme = $this->table('Themes')->newEntity([
                'name' => 'CatOps Test',
                'slug' => 'catops-test',
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
}
