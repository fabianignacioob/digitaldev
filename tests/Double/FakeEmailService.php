<?php
declare(strict_types=1);

namespace App\Test\Double;

use App\Service\EmailService;

class FakeEmailService extends EmailService
{
    /** @var list<array{kind:string, user:object, subject:object, publicUrl:?string}> */
    public array $messages = [];

    public function sendPaymentApproved(object $user, object $payment): void
    {
        $this->record('payment_approved', $user, $payment);
    }

    public function sendPaymentRejected(object $user, object $payment): void
    {
        $this->record('payment_rejected', $user, $payment);
    }

    public function sendPaymentCanceled(object $user, object $payment): void
    {
        $this->record('payment_canceled', $user, $payment);
    }

    public function sendPaymentExpired(object $user, object $payment): void
    {
        $this->record('payment_expired', $user, $payment);
    }

    public function sendSitePublished(object $user, object $site, string $publicUrl): void
    {
        $this->record('site_published', $user, $site, $publicUrl);
    }

    private function record(string $kind, object $user, object $subject, ?string $publicUrl = null): void
    {
        $this->messages[] = compact('kind', 'user', 'subject', 'publicUrl');
    }
}
