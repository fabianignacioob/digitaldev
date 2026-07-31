<?php
/**
 * @var \App\View\AppView $this
 * @var object|null $payment
 * @var string $message
 */
$this->assign('title', 'Resultado del pago | CatOps');

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
          <a class="button secondary" href="/panel">Ir a mis sitios</a>
        <?php else: ?>
          <a class="button" href="/login">Ir a mi cuenta</a>
        <?php endif; ?>
      </div>
    </div>
  </article>
</section>

<style>
  .payment-result {
    display: grid;
    min-height: min(420px, calc(100vh - 190px));
    place-items: center;
  }

  .payment-result-card {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    width: min(720px, 100%);
    gap: 24px;
    padding: 32px;
    border-width: 1px;
  }

  .payment-result-icon {
    display: grid;
    width: 58px;
    height: 58px;
    place-items: center;
    border-radius: 50%;
    background: #e9f8ef;
    color: var(--catops-success);
    font-size: 30px;
    font-weight: 900;
    line-height: 1;
  }

  .payment-result-eyebrow {
    margin-bottom: 5px;
    color: var(--catops-success);
    font-size: 12px;
    font-weight: 850;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .payment-result h1 {
    margin-bottom: 8px;
    font-size: clamp(29px, 4vw, 40px);
    line-height: 1.08;
  }

  .payment-result-message {
    max-width: 540px;
    margin-bottom: 0;
    font-size: 16px;
  }

  .payment-result-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 28px;
    margin: 22px 0 0;
  }

  .payment-result-summary div {
    min-width: 140px;
  }

  .payment-result-summary dt {
    margin-bottom: 3px;
    color: var(--catops-muted);
    font-size: 12px;
    font-weight: 750;
  }

  .payment-result-summary dd {
    margin: 0;
    color: var(--catops-navy);
    font-size: 14px;
    font-weight: 850;
    overflow-wrap: anywhere;
  }

  .payment-result-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 25px;
  }

  .payment-result-card.is-danger {
    border-color: #efc7c0;
  }

  .payment-result-card.is-danger .payment-result-icon {
    background: #fff0ee;
    color: var(--catops-danger);
  }

  .payment-result-card.is-danger .payment-result-eyebrow {
    color: var(--catops-danger);
  }

  .payment-result-card.is-warning,
  .payment-result-card.is-pending {
    border-color: #f0d7b4;
  }

  .payment-result-card.is-warning .payment-result-icon,
  .payment-result-card.is-pending .payment-result-icon {
    background: #fff1df;
    color: var(--catops-warning);
  }

  .payment-result-card.is-warning .payment-result-eyebrow,
  .payment-result-card.is-pending .payment-result-eyebrow {
    color: var(--catops-warning);
  }

  @media (max-width: 560px) {
    .payment-result {
      min-height: 0;
      padding-top: 14px;
    }

    .payment-result-card {
      grid-template-columns: 1fr;
      gap: 16px;
      padding: 23px;
    }

    .payment-result-icon {
      width: 48px;
      height: 48px;
      font-size: 25px;
    }

    .payment-result-actions .button {
      flex: 1 1 150px;
    }
  }
</style>
