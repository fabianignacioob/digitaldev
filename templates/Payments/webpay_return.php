<?php
/**
 * @var \App\View\AppView $this
 * @var object|null $payment
 * @var string $message
 */
$this->assign('title', 'Resultado del pago | CatOps');
$this->start('css');
?>
<link rel="stylesheet" href="<?= h($this->versionedAsset('/css/payment-result.min.css')) ?>">
<?php
$this->end();

$status = (string)($payment->status ?? 'unknown');
$states = [
    'paid' => [
        'class' => 'is-success',
        'eyebrow' => 'Transacción confirmada',
        'title' => 'Pago aprobado',
        'symbol' => '&#10003;',
    ],
    'rejected' => [
        'class' => 'is-danger',
        'eyebrow' => 'Transacción no aprobada',
        'title' => 'No se aprobó el pago',
        'symbol' => '&#215;',
    ],
    'canceled' => [
        'class' => 'is-warning',
        'eyebrow' => 'Proceso detenido',
        'title' => 'Pago cancelado',
        'symbol' => '&#8722;',
    ],
    'pending' => [
        'class' => 'is-pending',
        'eyebrow' => 'Confirmación pendiente',
        'title' => 'Estamos verificando el pago',
        'symbol' => '&#8230;',
    ],
    'authorized' => [
        'class' => 'is-pending',
        'eyebrow' => 'Confirmación pendiente',
        'title' => 'Estamos verificando el pago',
        'symbol' => '&#8230;',
    ],
];
$state = $states[$status] ?? [
    'class' => 'is-danger',
    'eyebrow' => 'No fue posible confirmar la transacción',
    'title' => 'Revisa el estado de tu pago',
    'symbol' => '!',
];
$isOwner = $payment && $currentUser && (int)$payment->user_id === (int)$currentUser['id'];
?>

<section class="dashboard-section payment-result">
  <article class="card payment-result-card <?= h($state['class']) ?>">
    <div class="payment-result-icon" aria-hidden="true"><?= $state['symbol'] ?></div>
    <div class="payment-result-content">
      <p class="payment-result-eyebrow"><?= h($state['eyebrow']) ?></p>
      <h1><?= h($state['title']) ?></h1>
      <p class="payment-result-message"><?= h($message) ?></p>

      <?php if ($payment): ?>
        <dl class="payment-result-summary">
          <div>
            <dt>Monto</dt>
            <dd>$<?= number_format((int)$payment->expected_amount, 0, ',', '.') ?> <?= h($payment->currency) ?></dd>
          </div>
          <div>
            <dt>Referencia</dt>
            <dd><?= h($payment->internal_reference) ?></dd>
          </div>
        </dl>
      <?php endif; ?>

      <div class="payment-result-actions">
        <?php if ($isOwner): ?>
          <a class="button" href="/payments/result/<?= h($payment->internal_reference) ?>">Ver detalle</a>
          <a class="button secondary" href="/panel">Ir a mis vitrinas</a>
        <?php else: ?>
          <a class="button" href="/login">Ir a mi cuenta</a>
        <?php endif; ?>
      </div>
    </div>
  </article>
</section>
