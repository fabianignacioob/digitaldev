<?php
declare(strict_types=1);

use App\Service\PlanService;

$this->assign('title', 'Planes mensuales | CatOps');
$this->assign('metaDescription', 'Planes mensuales CatOps para crear cartas y catálogos digitales simples de actualizar.');
$plans = $plans ?? [];
$planService = new PlanService();
$planIntro = [
    'basica' => 'Para comenzar a mostrar tu negocio de forma simple y profesional.',
    'basica-avanzada' => 'Para negocios que necesitan más contenido, categorías y personalización.',
    'full' => 'Para administrar varias marcas, propuestas o negocios con herramientas avanzadas.',
];
?>

<style>
  .plans-commercial-grid { display: grid; gap: 18px; }
  .plan-commercial-card { position: relative; display: flex; flex-direction: column; }
  .plan-commercial-card.is-recommended { border-color: rgba(243, 107, 22, .6); box-shadow: 0 18px 40px rgba(243, 107, 22, .13); }
  .plan-recommended { position: absolute; top: -12px; left: 20px; padding: 5px 10px; border-radius: 999px; background: var(--orange); color: #fff; font-size: 12px; font-weight: 800; }
  .plan-benefits { display: grid; gap: 9px; margin: 22px 0; padding: 0; list-style: none; }
  .plan-benefits li { color: var(--muted); line-height: 1.5; }
  .plan-benefits li::before { content: "•"; margin-right: 8px; color: var(--orange); font-weight: 900; }
  .plan-benefits .coming-soon { color: #826b58; }
  .plan-benefits .coming-soon::after { content: "Próximamente"; display: inline-block; margin-left: 7px; padding: 2px 6px; border-radius: 999px; background: #fff0e6; color: #9b4b17; font-size: 11px; font-weight: 800; }
  .plan-commercial-card .actions { margin-top: auto; }
  .plan-notice { max-width: 720px; margin: 26px auto 0; text-align: center; font-size: 14px; font-weight: 700; }
  @media (min-width: 860px) { .plans-commercial-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
</style>

<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="kicker">Planes mensuales</span>
      <h1>Elige el nivel para publicar tus cartas y catálogos</h1>
      <p class="lead">Parte con una presencia clara para tu negocio y escala cuando necesites más sitios, categorías o herramientas.</p>
      <div class="actions">
        <a class="button" href="#planes">Comparar planes</a>
        <a class="button secondary" href="/servicio">Ver servicio</a>
      </div>
    </div>
    <div class="visual" aria-hidden="true"></div>
  </div>
</section>

<section id="planes">
  <div class="container">
    <div class="section-head">
      <h2>Planes disponibles</h2>
      <p>Todos incluyen una carta o catálogo editable. Los beneficios marcados como Próximamente están reservados en el plan, pero aún no se habilitan en el panel.</p>
    </div>

    <div class="plans-commercial-grid">
      <?php foreach ($plans as $plan): ?>
        <?php $benefits = $planService->commercialBenefitRows($plan); ?>
        <article class="card plan-commercial-card<?= $plan->slug === 'basica-avanzada' ? ' is-recommended' : '' ?>">
          <?php if ($plan->slug === 'basica-avanzada'): ?><span class="plan-recommended">Recomendado</span><?php endif; ?>
          <span class="kicker"><?= h($plan->name) ?></span>
          <h2><?= h($plan->name) ?></h2>
          <div class="price">$<?= number_format((int)$plan->monthly_price, 0, ',', '.') ?>/mes</div>
          <p><?= h($planIntro[$plan->slug] ?? 'Para publicar y mantener tu información actualizada.') ?></p>
          <ul class="plan-benefits">
            <?php foreach ($benefits as $benefit): ?>
              <li class="<?= $benefit['status'] === 'coming_soon' ? 'coming-soon' : '' ?>"><strong><?= h($benefit['label']) ?>:</strong> <?= h($benefit['value']) ?></li>
            <?php endforeach; ?>
          </ul>
          <div class="actions">
            <?php if ($currentUser): ?>
              <?= $this->Form->create(null, ['url' => '/payments/create']) ?>
              <?= $this->Form->hidden('plan', ['value' => $plan->slug]) ?>
              <?= $this->Form->button('Elegir ' . $plan->name, ['class' => 'button']) ?>
              <?= $this->Form->end() ?>
            <?php else: ?>
              <a class="button" href="/registro?plan=<?= rawurlencode((string)$plan->slug) ?>">Elegir <?= h($plan->name) ?></a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="plan-notice">Renovación mensual mediante pago seguro. Sin cobros automáticos.</p>
  </div>
</section>

<section class="cta container">
  <h2>¿Quieres revisar primero el servicio?</h2>
  <p>Conoce cómo funciona la plataforma antes de elegir el plan que mejor acompaña a tu negocio.</p>
  <div class="actions center">
    <a class="button" href="/servicio">Ver detalle del servicio</a>
    <a class="button secondary" href="/login">Entrar al sistema</a>
  </div>
</section>
