<?php
declare(strict_types=1);

use App\Service\PlanService;

$this->assign('title', 'Planes mensuales | CatOps');
$this->assign('metaDescription', 'Planes mensuales CatOps para crear cartas y catálogos digitales simples de actualizar.');
$plans = $plans ?? [];
$planService = new PlanService();
$trialPlan = null;
$commercialPlans = [];
foreach ($plans as $plan) {
    if ($planService->isTrialPlan($plan)) {
        $trialPlan = $plan;
        continue;
    }
    $commercialPlans[] = $plan;
}
?>

<style>
  .plans-commercial-grid { display: grid; gap: 18px; }
  .plan-commercial-card { position: relative; display: flex; flex-direction: column; }
  .plan-commercial-card.is-recommended { border: 2px solid #f36b16; box-shadow: 0 18px 40px rgba(243, 107, 22, .13); }
  .plan-recommended { position: absolute; top: -2px; right: 20px; padding: 5px 10px; border-radius: 0 0 7px 7px; background: #f36b16; color: #fff; font-size: 12px; font-weight: 800; }
  .plan-benefits { display: grid; gap: 9px; margin: 22px 0; padding: 0; list-style: none; }
  .plan-benefits li { color: var(--muted); line-height: 1.5; }
  .plan-benefits li::before { content: "•"; margin-right: 8px; color: var(--orange); font-weight: 900; }
  .plan-benefits .coming-soon { color: #826b58; }
  .plan-benefits .coming-soon::after { content: "Beta"; display: inline-block; margin-left: 7px; padding: 2px 6px; border-radius: 999px; background: #fff0e6; color: #9b4b17; font-size: 11px; font-weight: 800; }
  .plan-commercial-card .actions { margin-top: auto; }
  .trial-callout { display: flex; align-items: center; justify-content: space-between; gap: 22px; max-width: 880px; margin: 0 auto 30px; padding: 22px 26px; border: 1px solid rgba(243, 107, 22, .34); border-radius: 8px; background: #fff8f2; }
  .trial-callout h3 { margin: 0 0 5px; font-size: 21px; }
  .trial-callout p { margin: 0; font-size: 14px; }
  .plan-notice { max-width: 720px; margin: 26px auto 0; text-align: center; font-size: 14px; font-weight: 700; }
  .benefit-group { margin-top: 18px; }
  .benefit-group h3 { margin: 0; font-size: 13px; text-transform: uppercase; color: var(--ink); }
  .benefit-group .plan-benefits { margin: 9px 0 0; }
  .annual-price { margin: -10px 0 8px; font-size: 14px; color: var(--muted); font-weight: 700; }
  @media (min-width: 860px) { .plans-commercial-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
  @media (max-width: 640px) { .trial-callout { align-items: flex-start; flex-direction: column; } }
</style>

<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="kicker">Planes mensuales</span>
      <h1>Elige el nivel para publicar tus cartas y catálogos</h1>
      <p class="lead">Comienza con una prueba gratuita y escala cuando necesites más sitios, categorías o herramientas.</p>
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
      <p>Separamos lo que puedes usar hoy de las funciones que están disponibles en modalidad Beta.</p>
    </div>

    <?php if ($trialPlan): ?>
      <div class="trial-callout">
        <div>
          <h3>Solicita tu prueba gratuita del plan Básico por 7 días</h3>
          <p>Sin tarjeta. El período comienza cuando publiques tu primer sitio.</p>
        </div>
        <?php if ($currentUser): ?>
          <?= $this->Form->create(null, ['url' => '/planes/activar/' . rawurlencode((string)$trialPlan->slug)]) ?>
          <?= $this->Form->button('Solicitar prueba', ['class' => 'button']) ?>
          <?= $this->Form->end() ?>
        <?php else: ?>
          <a class="button" href="/registro?plan=<?= rawurlencode((string)$trialPlan->slug) ?>">Solicitar prueba</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="plans-commercial-grid">
      <?php foreach ($commercialPlans as $plan): ?>
        <?php
          $benefits = $planService->commercialBenefitRows($plan);
          $todayBenefits = array_filter($benefits, static fn(array $row): bool => $row['status'] === 'available');
          $futureBenefits = array_filter($benefits, static fn(array $row): bool => $row['status'] === 'coming_soon');
          $annualPrice = $planService->annualPrice($plan);
          $badge = trim((string)($plan->commercial_badge ?? ''));
        ?>
        <article class="card plan-commercial-card<?= $badge !== '' ? ' is-recommended' : '' ?>">
          <?php if ($badge !== ''): ?><span class="plan-recommended"><?= h($badge) ?></span><?php endif; ?>
          <span class="kicker"><?= h($plan->name) ?></span>
          <h2><?= h($plan->name) ?></h2>
          <div class="price">$<?= number_format((int)$plan->monthly_price, 0, ',', '.') ?>/mes</div>
          <?php if ($annualPrice): ?><p class="annual-price">Anual: $<?= number_format($annualPrice, 0, ',', '.') ?></p><?php endif; ?>
          <p><?= h((string)($plan->commercial_description ?: 'Para publicar y mantener tu información actualizada.')) ?></p>
          <div class="benefit-group"><h3>Incluye hoy</h3><ul class="plan-benefits"><?php foreach ($todayBenefits as $benefit): ?><li><strong><?= h($benefit['label']) ?>:</strong> <?= h($benefit['value']) ?></li><?php endforeach; ?></ul></div>
          <?php if ($futureBenefits): ?><ul class="plan-benefits"><?php foreach ($futureBenefits as $benefit): ?><li class="coming-soon"><strong><?= h($benefit['label']) ?>:</strong> <?= h($benefit['value']) ?></li><?php endforeach; ?></ul><?php endif; ?>
          <div class="actions">
            <?php if ($currentUser): ?>
              <?= $this->Form->create(null, ['url' => '/payments/create']) ?>
              <?= $this->Form->hidden('plan', ['value' => $plan->slug]) ?>
              <?= $this->Form->hidden('billing_cycle', ['value' => 'monthly']) ?>
              <?= $this->Form->button('Elegir ' . $plan->name, ['class' => 'button']) ?>
              <?= $this->Form->end() ?>
              <?php if ($annualPrice): ?>
                <?= $this->Form->create(null, ['url' => '/payments/create']) ?>
                <?= $this->Form->hidden('plan', ['value' => $plan->slug]) ?>
                <?= $this->Form->hidden('billing_cycle', ['value' => 'annual']) ?>
                <?= $this->Form->button('Pagar anual', ['class' => 'button secondary']) ?>
                <?= $this->Form->end() ?>
              <?php endif; ?>
            <?php else: ?>
              <a class="button" href="/registro?plan=<?= rawurlencode((string)$plan->slug) ?>">Elegir <?= h($plan->name) ?></a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="plan-notice">Renovación mensual o anual mediante pago seguro. Sin cobros automáticos.</p>
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
