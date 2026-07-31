<?php
/** @var \App\View\AppView $this */
/** @var bool $enabled */
/** @var array{environment:string, amount:int, enabled:bool, title:string, description:string} $testOrder */
$this->assign('title', 'Prueba Webpay Plus | CatOps');
?>

<section class="dashboard-section">
  <div class="page-head">
    <div>
      <h1><?= h($testOrder['title']) ?></h1>
      <p><?= h($testOrder['description']) ?></p>
    </div>
  </div>

  <article class="card" style="max-width: 620px;">
    <span class="status status-active">Ambiente de <?= h($testOrder['environment']) ?></span>
    <h2 style="margin-top: 16px;">Plan de prueba</h2>
    <p>Esta orden no crea ni renueva suscripciones.</p>
    <p class="plan-price">$<?= number_format((int)$testOrder['amount'], 0, ',', '.') ?> <small>CLP</small></p>

    <?php if ($enabled): ?>
      <?= $this->Form->create(null, ['url' => '/test-plan']) ?>
        <?= $this->Form->button('Pagar $' . number_format((int)$testOrder['amount'], 0, ',', '.') . ' y abrir Webpay', ['class' => 'button']) ?>
      <?= $this->Form->end() ?>
    <?php else: ?>
      <p class="message">La prueba está deshabilitada. Activa únicamente la opción correspondiente al ambiente controlado.</p>
    <?php endif; ?>
  </article>
</section>
