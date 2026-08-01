<?php
use App\Service\PlanService;

$this->assign('title', 'Crear cuenta | CatOps');
$planService = new PlanService();
$selectedPlanEntity = $selectedPlan ? $planService->getPlanBySlug((string)$selectedPlan) : null;
$selectedPlanName = $selectedPlanEntity?->name;
?>

<h1>Crear cuenta</h1>
<p>Regístrate para crear una vitrina con carta, catálogo o servicios para tu negocio. La prueba gratuita comienza cuando publiques tu primera vitrina.</p>

<?php if ($selectedPlanName): ?>
  <p class="message">
    Opción seleccionada: <strong><?= h((string)$selectedPlanName) ?></strong>.
    <?php if ($planService->isTrialPlan($selectedPlanEntity)): ?>
      Tendrás 7 días desde la primera publicación y no necesitas tarjeta.
    <?php else: ?>
      Verifica tu correo para continuar con el pago seguro de este plan.
    <?php endif; ?>
  </p>
<?php endif; ?>

<?= $this->Form->create($user) ?>
<?= $this->Form->control('name', ['label' => 'Nombre', 'autocomplete' => 'name']) ?>
<?= $this->Form->control('email', ['label' => 'Correo', 'autocomplete' => 'email']) ?>
<?= $this->Form->control('password', [
    'label' => 'Contraseña',
    'autocomplete' => 'new-password',
]) ?>
<?= $this->Form->button('Crear cuenta y verificar correo') ?>
<?= $this->Form->end() ?>

<p>¿Ya tienes cuenta? <a class="muted-link" href="/login">Inicia sesión</a></p>
