<?php
/** @var \Cake\View\View $this */

$name = h((string)($user->name ?? ''));
$kind = (string)($kind ?? '');
?>
<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#102033;line-height:1.6">
  <h1 style="color:#041f55;font-size:24px">Hola <?= $name ?></h1>
  <?php if ($kind === 'verification_code'): ?>
    <p>Tu código de verificación para CatOps es:</p>
    <p style="font-size:32px;font-weight:700;letter-spacing:8px;color:#f46a12"><?= h((string)$code) ?></p>
    <p>Este código vence en 15 minutos. Si no solicitaste esta cuenta, puedes ignorar este mensaje.</p>
  <?php elseif ($kind === 'welcome'): ?>
    <p>Tu correo fue verificado y tu cuenta ya está activa.</p>
    <p>Ya puedes ingresar a CatOps y crear tu carta o catálogo digital.</p>
  <?php elseif ($kind === 'password_reset'): ?>
    <p>Recibimos una solicitud para cambiar tu contraseña.</p>
    <p><a href="<?= h((string)$resetUrl) ?>" style="display:inline-block;padding:12px 20px;border-radius:24px;background:#f46a12;color:#fff;text-decoration:none;font-weight:700">Cambiar contraseña</a></p>
    <p>El enlace vence en 30 minutos. Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
  <?php elseif ($kind === 'payment_approved'): ?>
    <p>Tu pago fue confirmado correctamente.</p>
    <p><strong>Plan:</strong> <?= h((string)$payment->plan_slug) ?><br>
    <strong>Monto:</strong> $<?= number_format((int)$payment->amount, 0, ',', '.') ?> CLP<br>
    <strong>Referencia:</strong> <?= h((string)$payment->internal_reference) ?></p>
  <?php elseif ($kind === 'payment_rejected'): ?>
    <p>No pudimos aprobar tu pago. Tu suscripción no fue modificada.</p>
    <p><strong>Plan:</strong> <?= h((string)$payment->plan_slug) ?><br>
    <strong>Referencia:</strong> <?= h((string)$payment->internal_reference) ?></p>
    <p>Puedes intentarlo nuevamente desde CatOps.</p>
  <?php endif; ?>
  <p style="margin-top:32px">Saludos,<br>El equipo de CatOps</p>
</div>
