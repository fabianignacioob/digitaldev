<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Mailer\Mailer;
use RuntimeException;
use Throwable;
use function Cake\Core\env;

class EmailService
{
    /** Sends the account verification code. */
    public function sendVerificationCode(object $user, string $code): void
    {
        $this->deliver(
            $user,
            'Código de verificación CatOps',
            ['kind' => 'verification_code', 'user' => $user, 'code' => $code],
        );
    }

    /** Sends the post-verification welcome message. */
    public function sendWelcome(object $user): void
    {
        $this->deliver(
            $user,
            'Bienvenido a CatOps',
            ['kind' => 'welcome', 'user' => $user],
        );
    }

    /** Sends a one-time password reset link. */
    public function sendPasswordReset(object $user, string $resetUrl): void
    {
        $this->deliver(
            $user,
            'Recupera tu contraseña de CatOps',
            ['kind' => 'password_reset', 'user' => $user, 'resetUrl' => $resetUrl],
        );
    }

    /** Sends a successful payment notification. */
    public function sendPaymentApproved(object $user, object $payment): void
    {
        $this->deliver(
            $user,
            'Pago confirmado en CatOps',
            ['kind' => 'payment_approved', 'user' => $user, 'payment' => $payment],
        );
    }

    /** Sends a rejected payment notification. */
    public function sendPaymentRejected(object $user, object $payment): void
    {
        $this->deliver(
            $user,
            'Pago no aprobado en CatOps',
            ['kind' => 'payment_rejected', 'user' => $user, 'payment' => $payment],
        );
    }

    /** Delivers a transactional message through the configured SMTP transport. */
    private function deliver(object $user, string $subject, array $viewVars): void
    {
        $password = trim((string)env('EMAIL_PASSWORD', ''));
        $from = trim((string)env('EMAIL_FROM', ''));
        if ($password === '' || $from === '') {
            throw new RuntimeException('El correo no está configurado. Define EMAIL_PASSWORD y EMAIL_FROM.');
        }

        $mailer = new Mailer('default');
        $mailer
            ->setTo((string)$user->email, (string)$user->name)
            ->setFrom([$from => (string)env('EMAIL_FROM_NAME', 'CatOps')])
            ->setSubject($subject)
            ->setEmailFormat('both')
            ->setViewVars($viewVars)
            ->viewBuilder()
            ->setTemplate('transactional');

        try {
            $mailer->deliver();
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo entregar el correo transaccional.', 0, $exception);
        }
    }
}
