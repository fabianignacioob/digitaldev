<?php
/** @var \App\View\AppView $this */
/** @var bool $enabled */
$this->assign('title', 'Prueba Webpay Plus | CatOps');
?>

<section class="dashboard-section">
  <div class="page-head">
    <div>
      <h1>Prueba de integración Webpay Plus</h1>
      <p>Orden interna de $1 CLP para comprobar el flujo real de Transbank.</p>
    </div>
  </div>

  <article class="card" style="max-width: 620px;">
    <span class="status status-active">Ambiente de integración</span>
    <h2 style="margin-top: 16px;">Plan de prueba</h2>
    <p>Usa una tarjeta de prueba de Transbank. Esta orden no crea ni renueva suscripciones.</p>
    <p class="plan-price">$1 <small>CLP</small></p>

    <?php if ($enabled): ?>
      <?= $this->Form->create(null, ['url' => '/test-plan']) ?>
        <?= $this->Form->button('Pagar $1 y abrir Webpay', ['class' => 'button']) ?>
      <?= $this->Form->end() ?>
    <?php else: ?>
      <p class="message">La prueba está deshabilitada. Requiere integración y la opción local de prueba activa.</p>
    <?php endif; ?>
  </article>
</section>
