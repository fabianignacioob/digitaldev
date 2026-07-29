<?php
declare(strict_types=1);

use App\Service\PlanService;

$plans = $plans ?? [];
$planService = new PlanService();
$commercialPlans = [];
foreach ($plans as $plan) {
    if (!$planService->isTrialPlan($plan)) {
        $commercialPlans[] = $plan;
    }
}
?>
<div class="grid three pricing-grid">
  <?php foreach ($commercialPlans as $plan): ?>
    <?php
      $isRecommended = trim((string)($plan->commercial_badge ?? '')) !== '';
      $annualPrice = $planService->annualPrice($plan);
      $benefits = array_slice($planService->commercialBenefitRows($plan), 0, 8);
    ?>
    <article class="card plan-card<?= $isRecommended ? ' featured' : '' ?>">
      <?php if ($isRecommended): ?><span class="badge"><?= h((string)$plan->commercial_badge) ?></span><?php endif; ?>
      <h3><?= h((string)$plan->name) ?></h3>
      <p><?= h((string)($plan->commercial_description ?: 'Para publicar y mantener la información de tu negocio actualizada.')) ?></p>
      <div class="price">$<?= number_format((int)$plan->monthly_price, 0, ',', '.') ?><small>/ mes</small></div>
      <?php if ($annualPrice): ?><div class="plan-note">Anual: $<?= number_format($annualPrice, 0, ',', '.') ?></div><?php endif; ?>
      <ul class="plan-list">
        <?php foreach ($benefits as $benefit): ?>
          <li><strong><?= h($benefit['label']) ?>:</strong> <?= h($benefit['value']) ?><?= $benefit['status'] === 'coming_soon' ? ' (Beta)' : '' ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if (!empty($currentUser)): ?>
        <?= $this->Form->create(null, ['url' => '/payments/create']) ?>
        <?= $this->Form->hidden('plan', ['value' => $plan->slug]) ?>
        <?= $this->Form->button('Elegir ' . $plan->name, ['class' => 'button']) ?>
        <?= $this->Form->end() ?>
      <?php else: ?>
        <a class="button" href="/registro?plan=<?= rawurlencode((string)$plan->slug) ?>">Elegir <?= h((string)$plan->name) ?></a>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
