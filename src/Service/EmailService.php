<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
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
    public function sendWelcome(object $user, ?object $plan = null): void
    {
        $this->deliver(
            $user,
            'Bienvenido a CatOps',
            [
                'kind' => 'welcome',
                'user' => $user,
                'planSummary' => $this->buildPlanSummary($plan),
            ],
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

    /** Sends a notification when a payment is cancelled before confirmation. */
    public function sendPaymentCanceled(object $user, object $payment): void
    {
        $this->deliver(
            $user,
            'Pago cancelado en CatOps',
            ['kind' => 'payment_canceled', 'user' => $user, 'payment' => $payment],
        );
    }

    /** Sends a notification when a pending payment expires. */
    public function sendPaymentExpired(object $user, object $payment): void
    {
        $this->deliver(
            $user,
            'Pago vencido en CatOps',
            ['kind' => 'payment_expired', 'user' => $user, 'payment' => $payment],
        );
    }

    /** Sends the public address after a site becomes available. */
    public function sendSitePublished(object $user, object $site, string $publicUrl): void
    {
        $this->deliver(
            $user,
            'Tu vitrina ya está publicada en CatOps',
            [
                'kind' => 'site_published',
                'user' => $user,
                'site' => $site,
                'publicUrl' => $publicUrl,
            ],
        );
    }

    /** Delivers a transactional message through the configured SMTP transport. */
    private function deliver(object $user, string $subject, array $viewVars): void
    {
        if (
            defined('PHPUNIT_COMPOSER_INSTALL')
            || in_array(strtolower((string)env('APP_ENV', '')), ['test', 'testing'], true)
        ) {
            return;
        }

        $transportConfig = (array)TransportFactory::getConfig('default');
        $mailerConfig = (array)Mailer::getConfig('default');
        $fromConfig = $mailerConfig['from'] ?? null;
        $fromAddress = is_array($fromConfig) ? (string)array_key_first($fromConfig) : (string)$fromConfig;
        $fromName = is_array($fromConfig) && $fromAddress !== ''
            ? (string)($fromConfig[$fromAddress] ?? 'CatOps')
            : (string)env('EMAIL_FROM_NAME', 'CatOps');
        if (trim((string)($transportConfig['password'] ?? '')) === '' || $fromAddress === '') {
            throw new RuntimeException('El correo no está configurado en app_local.php.');
        }

        $mailer = new Mailer('default');
        $mailer
            ->setTo((string)$user->email, (string)$user->name)
            ->setFrom([$fromAddress => $fromName])
            ->setSubject($subject)
            ->setEmailFormat('both')
            ->setViewVars($viewVars)
            ->viewBuilder()
            ->setTemplate('transactional');

        if (in_array($viewVars['kind'] ?? null, [
            'verification_code',
            'welcome',
            'password_reset',
            'payment_approved',
            'payment_rejected',
            'payment_canceled',
            'payment_expired',
            'site_published',
        ], true)) {
            $logoPath = ROOT . DS . 'webroot' . DS . 'img' . DS . 'catops-logo.png';
            if (is_file($logoPath)) {
                $mailer->setAttachments([
                    'catops-logo.png' => [
                        'file' => $logoPath,
                        'mimetype' => 'image/png',
                        'contentId' => 'catops-logo',
                        'contentDisposition' => false,
                    ],
                ]);
            }
        }

        try {
            $mailer->deliver();
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo entregar el correo transaccional.', 0, $exception);
        }
    }

    /** @return array{name:string, description:string, isTrial:bool, monthlyPrice:int, annualPrice:?int, trialDays:int, features:list<string>} */
    private function buildPlanSummary(?object $plan): array
    {
        if (!$plan) {
            return [
                'name' => 'CatOps',
                'description' => 'Tu espacio para crear y compartir una presencia digital para tu negocio.',
                'isTrial' => false,
                'monthlyPrice' => 0,
                'annualPrice' => null,
                'trialDays' => 0,
                'features' => [],
            ];
        }

        $capabilities = $plan->capabilities ?? [];
        if (is_string($capabilities)) {
            $capabilities = json_decode($capabilities, true);
        }
        $capabilities = is_array($capabilities) ? $capabilities : [];
        $isTrial = (bool)($capabilities['trial_enabled'] ?? false);
        $features = [];
        $sites = (int)($capabilities['sites_configured_limit'] ?? $plan->max_sites ?? 0);
        $published = (int)($capabilities['sites_published_limit'] ?? $plan->max_published ?? 0);
        $items = (int)($capabilities['items_limit'] ?? 0);
        $categories = (int)($capabilities['categories_limit'] ?? 0);
        $storage = (int)($capabilities['image_storage_limit_mb'] ?? 0);

        if ($sites > 0) {
            $features[] = $sites . ($sites === 1 ? ' vitrina configurable' : ' vitrinas configurables');
        }
        if ($published > 0) {
            $features[] = $published . ($published === 1 ? ' vitrina publicada' : ' vitrinas publicadas');
        }
        if ($items > 0) {
            $features[] = 'Hasta ' . $items . ' productos o servicios';
        }
        if ((bool)($capabilities['categories_enabled'] ?? false) && $categories > 0) {
            $features[] = 'Categorías para ordenar tu contenido';
        }
        if ((bool)($capabilities['whatsapp_enabled'] ?? false)) {
            $features[] = 'Botón de contacto por WhatsApp';
        }
        if ((bool)($capabilities['qr_enabled'] ?? false)) {
            $features[] = 'Código QR para compartir tu vitrina';
        }
        if ($storage > 0) {
            $features[] = $storage . ' MB para imágenes';
        }
        if ((bool)($capabilities['premium_themes_enabled'] ?? false)) {
            $features[] = 'Plantillas premium y personalización avanzada';
        }
        if ((bool)($capabilities['branding_removable'] ?? $capabilities['catops_branding_removable'] ?? false)) {
            $features[] = 'Sin marca CatOps en tu vitrina';
        }
        if ((bool)($capabilities['priority_support'] ?? false)) {
            $features[] = 'Soporte prioritario';
        }

        return [
            'name' => (string)($plan->name ?? 'CatOps'),
            'description' => (string)($plan->commercial_description
                ?? 'Tu plan CatOps para crear y compartir una presencia digital profesional.'),
            'isTrial' => $isTrial,
            'monthlyPrice' => (int)($plan->monthly_price ?? 0),
            'annualPrice' => isset($plan->annual_price) && $plan->annual_price !== null
                ? (int)$plan->annual_price
                : null,
            'trialDays' => (int)($capabilities['trial_duration_days'] ?? 0),
            'features' => $features,
        ];
    }
}
