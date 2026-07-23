<?php
/**
 * @var \App\View\AppView $this
 * @var object|null $payment
 * @var string $message
 */
$this->assign('title', 'Resultado del pago | CatOps');
?>

<section class="dashboard-section">
  <div class="section-heading">
    <h1>Resultado del pago</h1>
    <p><?= h($message) ?></p>
  </div>

  <div class="actions center">
    <?php if ($payment && $currentUser && (int)$payment->user_id === (int)$currentUser['id']): ?>
      <a class="button" href="/payments/result/<?= h($payment->internal_reference) ?>">Ver detalle</a>
    <?php else: ?>
      <a class="button" href="/login">Ir a mi cuenta</a>
    <?php endif; ?>
  </div>
</section>
