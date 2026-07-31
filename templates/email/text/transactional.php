<?php
/** @var \Cake\View\View $this */

$name = (string)($user->name ?? '');
$kind = (string)($kind ?? '');

echo 'Hola ' . $name . ",\n\n";

switch ($kind) {
    case 'verification_code':
        echo "Tu código de verificación para CatOps es:\n\n";
        echo (string)$code . "\n\n";
        echo "Este código vence en 15 minutos. Si no solicitaste esta cuenta, puedes ignorar este mensaje.\n";
        break;
    case 'welcome':
        echo "Tu correo fue verificado y tu cuenta ya está activa.\n\n";
        echo "Ya puedes ingresar a CatOps y crear tu carta o catálogo digital.\n";
        break;
    case 'password_reset':
        echo "Recibimos una solicitud para cambiar tu contraseña.\n\n";
        echo "Abre este enlace para continuar:\n" . (string)$resetUrl . "\n\n";
        echo "El enlace vence en 30 minutos. Si no solicitaste este cambio, puedes ignorar este mensaje.\n";
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
}

echo "\nSaludos,\nEl equipo de CatOps\n";
