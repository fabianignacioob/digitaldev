<?php
/** @var \Cake\View\View $this */

$name = h((string)($user->name ?? ''));
$kind = (string)($kind ?? '');
$planSummary = (array)($planSummary ?? []);
$planName = h((string)($planSummary['name'] ?? 'CatOps'));
$planDescription = h((string)($planSummary['description'] ?? ''));
$isTrial = (bool)($planSummary['isTrial'] ?? false);
$trialDays = (int)($planSummary['trialDays'] ?? 0);
$monthlyPrice = (int)($planSummary['monthlyPrice'] ?? 0);
$annualPrice = $planSummary['annualPrice'] ?? null;
$features = (array)($planSummary['features'] ?? []);
?>
<?php if ($kind === 'verification_code'): ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:0;padding:0;background:#f3f6fa;font-family:Arial,Helvetica,sans-serif;color:#102033">
  <tr>
    <td align="center" style="padding:28px 14px">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden">
        <tr>
          <td style="height:6px;background:#f46a12;font-size:0;line-height:0">&nbsp;</td>
        </tr>
        <tr>
          <td align="center" style="padding:30px 32px 20px;background:#ffffff">
            <img src="cid:catops-logo" width="92" height="92" alt="CatOps" style="display:block;width:92px;height:92px;object-fit:contain;border:0;margin:0 auto 14px">
            <p style="margin:0;color:#041f55;font-size:13px;letter-spacing:1.8px;text-transform:uppercase;font-weight:bold">CatOps</p>
          </td>
        </tr>
        <tr>
          <td style="padding:6px 42px 40px">
            <h1 style="margin:0 0 12px;color:#041f55;font-size:26px;line-height:1.2;text-align:center">Verifica tu correo</h1>
            <p style="margin:0 0 24px;color:#526174;font-size:16px;line-height:1.6;text-align:center">Hola <?= $name ?>, estamos activando tu cuenta CatOps.</p>
            <p style="margin:0 0 10px;color:#526174;font-size:14px;line-height:1.5;text-align:center">Ingresa este código en la pantalla de verificación:</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px">
              <tr>
                <td align="center" style="padding:20px 12px;background:#fff3eb;border:1px solid #ffd5bd;border-radius:12px">
                  <p style="margin:0;color:#8a3b0f;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:bold">Código de verificación</p>
                  <p style="margin:10px 0 0;color:#f46a12;font-size:38px;line-height:1;font-weight:bold;letter-spacing:10px"><?= h((string)$code) ?></p>
                </td>
              </tr>
            </table>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px">
              <tr>
                <td style="padding:14px 16px;background:#f7f9fc;border-left:4px solid #0a2a66;border-radius:4px;color:#526174;font-size:13px;line-height:1.55">
                  Este código vence en <strong style="color:#102033">15 minutos</strong>. No compartas este código con nadie; CatOps nunca te lo solicitará por teléfono, WhatsApp o correo.
                </td>
              </tr>
            </table>
            <p style="margin:0;color:#697789;font-size:13px;line-height:1.6;text-align:center">Si no creaste una cuenta en CatOps, puedes ignorar este mensaje.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:20px 30px;background:#041f55;color:#dbe7f5;text-align:center;font-size:12px;line-height:1.6">
            <strong style="color:#ffffff">Mensaje automático</strong><br>
            No respondas a este correo; esta bandeja no es monitoreada.<br>
            © <?= date('Y') ?> CatOps · catops.cl
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php elseif ($kind === 'welcome'): ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:0;padding:0;background:#f3f6fa;font-family:Arial,Helvetica,sans-serif;color:#102033">
  <tr>
    <td align="center" style="padding:28px 14px">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden">
        <tr><td style="height:6px;background:#f46a12;font-size:0;line-height:0">&nbsp;</td></tr>
        <tr>
          <td align="center" style="padding:30px 32px 20px;background:#ffffff">
            <img src="cid:catops-logo" width="92" height="92" alt="CatOps" style="display:block;width:92px;height:92px;object-fit:contain;border:0;margin:0 auto 14px">
            <p style="margin:0;color:#041f55;font-size:13px;letter-spacing:1.8px;text-transform:uppercase;font-weight:bold">CatOps</p>
          </td>
        </tr>
        <tr>
          <td style="padding:6px 42px 38px">
            <h1 style="margin:0 0 12px;color:#041f55;font-size:26px;line-height:1.2;text-align:center">¡Tu cuenta está lista!</h1>
            <p style="margin:0 0 24px;color:#526174;font-size:16px;line-height:1.6;text-align:center">Hola <?= $name ?>, tu correo fue verificado correctamente.</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px">
              <tr>
                <td style="padding:20px 22px;background:#041f55;border-radius:12px;color:#ffffff">
                  <p style="margin:0 0 7px;color:#a9c6e8;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:bold">Tu plan en CatOps</p>
                  <p style="margin:0 0 7px;font-size:23px;line-height:1.25;font-weight:bold"><?= $planName ?></p>
                  <?php if ($isTrial): ?>
                    <p style="margin:0;color:#ffd9bf;font-size:14px;font-weight:bold">Prueba gratuita<?= $trialDays > 0 ? ' por ' . $trialDays . ' días' : '' ?></p>
                  <?php else: ?>
                    <p style="margin:0;color:#ffd9bf;font-size:14px;font-weight:bold">Plan seleccionado · pendiente de activación</p>
                  <?php endif; ?>
                </td>
              </tr>
            </table>
            <p style="margin:0 0 14px;color:#102033;font-size:15px;line-height:1.6"><?= $planDescription ?></p>
            <?php if ($isTrial): ?>
              <p style="margin:0 0 20px;padding:13px 15px;background:#ecfdf3;border-left:4px solid #14804a;border-radius:4px;color:#14532d;font-size:13px;line-height:1.55"><strong>Tu prueba no requiere tarjeta.</strong> Los 7 días comienzan cuando publiques tu primer sitio.</p>
            <?php else: ?>
              <p style="margin:0 0 20px;padding:13px 15px;background:#fff3eb;border-left:4px solid #f46a12;border-radius:4px;color:#8a3b0f;font-size:13px;line-height:1.55"><strong>Para activar este plan, ingresa a CatOps y completa el pago seguro.</strong> No realizamos cobros automáticos.</p>
            <?php endif; ?>
            <?php if ($monthlyPrice > 0 || $annualPrice !== null): ?>
              <p style="margin:0 0 14px;color:#526174;font-size:13px;font-weight:bold">Valores de referencia</p>
              <p style="margin:0 0 20px;color:#526174;font-size:13px;line-height:1.6">
                <?php if ($monthlyPrice > 0): ?>Desde <strong style="color:#102033">$<?= number_format($monthlyPrice, 0, ',', '.') ?> CLP al mes</strong><?php endif; ?>
                <?php if ($annualPrice !== null && (int)$annualPrice > 0): ?> · Anual: <strong style="color:#102033">$<?= number_format((int)$annualPrice, 0, ',', '.') ?> CLP</strong><?php endif; ?>
              </p>
            <?php endif; ?>
            <?php if ($features): ?>
              <p style="margin:0 0 8px;color:#526174;font-size:13px;font-weight:bold">Lo que puedes hacer con tu plan</p>
              <ul style="margin:0 0 22px;padding-left:20px;color:#526174;font-size:13px;line-height:1.8">
                <?php foreach ($features as $feature): ?>
                  <li><?= h((string)$feature) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <p style="margin:0;padding:15px 16px;background:#f7f9fc;border-radius:8px;color:#526174;font-size:13px;line-height:1.6"><strong style="color:#102033">Siguiente paso:</strong> ingresa a CatOps, crea tu sitio, personaliza tu contenido y compártelo con tus clientes.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:20px 30px;background:#041f55;color:#dbe7f5;text-align:center;font-size:12px;line-height:1.6">
            <strong style="color:#ffffff">Mensaje automático</strong><br>
            No respondas a este correo; esta bandeja no es monitoreada.<br>
            © <?= date('Y') ?> CatOps · catops.cl
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php elseif ($kind === 'password_reset'): ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:0;padding:0;background:#f3f6fa;font-family:Arial,Helvetica,sans-serif;color:#102033">
  <tr>
    <td align="center" style="padding:28px 14px">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden">
        <tr><td style="height:6px;background:#f46a12;font-size:0;line-height:0">&nbsp;</td></tr>
        <tr>
          <td align="center" style="padding:30px 32px 20px;background:#ffffff">
            <img src="cid:catops-logo" width="92" height="92" alt="CatOps" style="display:block;width:92px;height:92px;object-fit:contain;border:0;margin:0 auto 14px">
            <p style="margin:0;color:#041f55;font-size:13px;letter-spacing:1.8px;text-transform:uppercase;font-weight:bold">CatOps</p>
          </td>
        </tr>
        <tr>
          <td style="padding:6px 42px 38px">
            <h1 style="margin:0 0 12px;color:#041f55;font-size:26px;line-height:1.2;text-align:center">Recupera tu contraseña</h1>
            <p style="margin:0 0 24px;color:#526174;font-size:16px;line-height:1.6;text-align:center">Hola <?= $name ?>, recibimos una solicitud para cambiar tu contraseña.</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px">
              <tr>
                <td align="center" style="padding:22px 16px;background:#fff3eb;border:1px solid #ffd5bd;border-radius:12px">
                  <p style="margin:0 0 14px;color:#8a3b0f;font-size:13px;line-height:1.5">Usa el botón para crear una contraseña nueva y recuperar el acceso a tu cuenta.</p>
                  <a href="<?= h((string)$resetUrl) ?>" style="display:inline-block;padding:14px 26px;border-radius:24px;background:#f46a12;color:#ffffff;text-decoration:none;font-size:15px;font-weight:bold">Crear nueva contraseña</a>
                </td>
              </tr>
            </table>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px">
              <tr>
                <td style="padding:14px 16px;background:#f7f9fc;border-left:4px solid #0a2a66;border-radius:4px;color:#526174;font-size:13px;line-height:1.55">
                  Este enlace vence en <strong style="color:#102033">30 minutos</strong> y solo puede utilizarse una vez. Si no solicitaste este cambio, no hagas nada y tu contraseña actual seguirá vigente.
                </td>
              </tr>
            </table>
            <p style="margin:0 0 8px;color:#697789;font-size:12px;line-height:1.5">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
            <p style="margin:0;color:#526174;font-size:12px;line-height:1.5;word-break:break-all"><?= h((string)$resetUrl) ?></p>
          </td>
        </tr>
        <tr>
          <td style="padding:20px 30px;background:#041f55;color:#dbe7f5;text-align:center;font-size:12px;line-height:1.6">
            <strong style="color:#ffffff">Mensaje automático</strong><br>
            No respondas a este correo; esta bandeja no es monitoreada.<br>
            © <?= date('Y') ?> CatOps · catops.cl
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php else: ?>
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
<?php endif; ?>
