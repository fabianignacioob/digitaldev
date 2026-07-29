<?php
declare(strict_types=1);

use App\Service\PlanService;

$this->assign('title', 'Planes mensuales | CatOps');
$this->assign('metaDescription', 'Compara los planes CatOps para publicar cartas, catálogos y páginas de servicios.');
$plans = $plans ?? [];
$planService = new PlanService();
$trialPlan = null;
foreach ($plans as $plan) {
    if ($planService->isTrialPlan($plan)) {
        $trialPlan = $plan;
        break;
    }
}
?>
<section class="page-hero">
  <div class="container hero-grid">
    <div><span class="eyebrow">Planes mensuales</span><h1>Una base clara para cada etapa de tu negocio</h1><p class="hero-copy">Parte con una prueba gratuita y elige el nivel de sitios, organización y personalización que necesitas.</p><div class="actions"><a class="button" href="#planes">Comparar planes</a><a class="button secondary" href="/servicio">Conocer el servicio</a></div></div>
    <div class="page-visual" aria-hidden="true"></div>
  </div>
</section>
<section id="planes">
  <div class="container">
    <div class="plans-header">
      <div class="section-head"><span class="section-kicker">Planes claros</span><h2>Elige un plan para partir simple y crecer con orden</h2><p>Renovación mensual o anual mediante pago seguro. Sin cobros automáticos.</p></div>
      <?php if ($trialPlan): ?>
        <div class="trial-box"><strong>Prueba gratuita</strong><span>7 días del plan Básico al publicar tu primer sitio.</span></div>
      <?php endif; ?>
    </div>
    <?= $this->element('marketing/plan_cards', compact('plans', 'currentUser')) ?>
    <div class="plan-notice"><p>Los beneficios señalados como Beta se habilitan por etapas. Tu plan y sus límites vigentes siempre se ven desde el panel.</p></div>
  </div>
</section>
<section class="section-soft">
  <div class="container section-head"><span class="section-kicker">Antes de elegir</span><h2>Conoce qué puedes publicar con CatOps</h2><p>Cartas digitales, catálogos de productos o catálogos de servicios, todos editables desde un panel privado.</p><div class="actions" style="justify-content:center"><a class="button" href="/servicio">Ver detalle del servicio</a><a class="button secondary" href="/registro">Crear mi cuenta</a></div></div>
</section>
