<?php
/**
 * @var \App\View\AppView $this
 * @var object $payment
 * @var array{token: string, url: string} $transaction
 */
$this->assign('title', 'Redirigiendo a Webpay | CatOps');
?>

<section class="dashboard-section">
  <div class="section-heading">
    <h1>Redirigiendo a Webpay</h1>
    <p>Estamos abriendo el formulario seguro de pago.</p>
  </div>

  <form id="webpay-redirect-form" method="post" action="<?= h($transaction['url']) ?>">
    <input type="hidden" name="token_ws" value="<?= h($transaction['token']) ?>">
    <noscript><button class="button" type="submit">Continuar a Webpay</button></noscript>
  </form>
</section>

<script>
  document.getElementById('webpay-redirect-form').submit();
</script>
