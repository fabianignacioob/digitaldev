<?php
/**
 * @var \App\View\AppView $this
 * @var object $payment
 */
?>

<section class="dashboard-section">
  <div class="section-heading">
    <h1>Resultado del pago</h1>
    <p>Estado actual de la orden interna preparada para Webpay Plus.</p>
  </div>

  <article class="card">
    <p><strong>Referencia:</strong> <?= h($payment->internal_reference) ?></p>
    <p><strong>Plan:</strong> <?= h($payment->plan_slug) ?></p>
    <p><strong>Estado:</strong> <?= h($payment->status) ?></p>
    <p><strong>Monto:</strong> $<?= number_format((int)$payment->expected_amount, 0, ',', '.') ?> <?= h($payment->currency) ?></p>
    <?php if ($payment->provider_reference && $payment->provider_reference !== $payment->gateway_token): ?>
      <p><strong>Referencia proveedor:</strong> <?= h($payment->provider_reference) ?></p>
    <?php endif; ?>
  </article>
</section>
