<?php
/** @var \Cake\View\View $this */

$name = (string)($user->name ?? '');
$kind = (string)($kind ?? '');

echo 'Hola ' . $name . ",\n\n";

switch ($kind) {
    case 'verification_code':
        echo "Estamos activando tu cuenta CatOps. Ingresa este código en la pantalla de verificación:\n\n";
        echo (string)$code . "\n\n";
        echo "Este código vence en 15 minutos. No lo compartas; CatOps nunca te lo solicitará por teléfono, WhatsApp o correo.\n\n";
        echo "Si no creaste una cuenta en CatOps, puedes ignorar este mensaje.\n\n";
        echo "Mensaje automático: no respondas a este correo; esta bandeja no es monitoreada.\n";
        break;
    case 'welcome':
        $plan = (array)($planSummary ?? []);
        echo "Tu correo fue verificado correctamente y tu cuenta ya está activa.\n\n";
        echo "PLAN: " . (string)($plan['name'] ?? 'CatOps') . "\n";
        echo (bool)($plan['isTrial'] ?? false)
            ? "Prueba gratuita por " . (int)($plan['trialDays'] ?? 0) . " días. No necesitas tarjeta. Los días comienzan cuando publiques tu primera vitrina.\n"
            : "Plan seleccionado, pendiente de activación. Ingresa a CatOps y completa el pago seguro. No realizamos cobros automáticos.\n";
        if (!empty($plan['description'])) {
            echo "\n" . (string)$plan['description'] . "\n";
        }
        if (!empty($plan['monthlyPrice'])) {
            echo "Precio mensual: $" . number_format((int)$plan['monthlyPrice'], 0, ',', '.') . " CLP\n";
        }
        if (isset($plan['annualPrice']) && $plan['annualPrice'] !== null && (int)$plan['annualPrice'] > 0) {
            echo "Precio anual: $" . number_format((int)$plan['annualPrice'], 0, ',', '.') . " CLP\n";
        }
        if (!empty($plan['features'])) {
            echo "\nIncluye:\n";
            foreach ((array)$plan['features'] as $feature) {
                echo "- " . (string)$feature . "\n";
            }
        }
        echo "\nSiguiente paso: ingresa a CatOps, crea tu vitrina, personaliza tu contenido y compártela con tus clientes.\n";
        echo "\nMensaje automático: no respondas a este correo; esta bandeja no es monitoreada.\n";
        break;
    case 'password_reset':
        echo "Recibimos una solicitud para cambiar tu contraseña.\n\n";
        echo "Abre este enlace para crear una contraseña nueva:\n" . (string)$resetUrl . "\n\n";
        echo "El enlace vence en 30 minutos y solo puede utilizarse una vez. Si no solicitaste este cambio, no hagas nada y tu contraseña actual seguirá vigente.\n\n";
        echo "Mensaje automático: no respondas a este correo; esta bandeja no es monitoreada.\n";
        break;
    case 'payment_approved':
        echo "Tu pago fue confirmado correctamente.\n\n";
        echo "Plan: " . (string)$payment->plan_slug . "\n";
        echo "Monto: $" . number_format((int)$payment->amount, 0, ',', '.') . " CLP\n";
        echo "Referencia: " . (string)$payment->internal_reference . "\n";
        break;
    case 'payment_rejected':
        echo "No pudimos aprobar tu pago. Tu suscripción no fue modificada.\n\n";
        echo "Plan: " . (string)$payment->plan_slug . "\n";
        echo "Referencia: " . (string)$payment->internal_reference . "\n\n";
        echo "Puedes intentarlo nuevamente desde CatOps.\n";
        break;
    case 'payment_canceled':
        echo "El proceso de pago fue cancelado antes de completarse. Tu suscripción no fue modificada.\n\n";
        echo "Plan: " . (string)$payment->plan_slug . "\n";
        echo "Referencia: " . (string)$payment->internal_reference . "\n\n";
        echo "Puedes iniciar un nuevo pago desde CatOps cuando estés listo.\n";
        break;
    case 'payment_expired':
        echo "El tiempo para completar este pago finalizó. Tu suscripción no fue modificada.\n\n";
        echo "Plan: " . (string)$payment->plan_slug . "\n";
        echo "Referencia: " . (string)$payment->internal_reference . "\n\n";
        echo "Puedes iniciar un nuevo pago desde CatOps cuando estés listo.\n";
        break;
    case 'site_published':
        echo "Tu vitrina ya está disponible para que la compartas con tus clientes.\n\n";
        echo "Vitrina: " . (string)$site->name . "\n";
        echo "Enlace: " . (string)$publicUrl . "\n";
        break;
}

echo "\nSaludos,\nEl equipo de CatOps\n";
