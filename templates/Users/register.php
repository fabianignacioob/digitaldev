<?php
use App\Service\PlanService;

$this->assign('title', 'Crear cuenta | CatOps');
$selectedPlanName = $selectedPlan ? (new PlanService())->getPlanBySlug((string)$selectedPlan)?->name : null;
?>

<h1>Crear cuenta</h1>
<p>Regístrate para crear una carta o catálogo editable para tu negocio. La prueba gratuita comienza cuando publiques tu primer sitio.</p>

<?php if ($selectedPlanName): ?>
  <p class="message">Opción seleccionada: <strong><?= h((string)$selectedPlanName) ?></strong>. Si eliges la prueba, tendrás 7 días desde la primera publicación y no necesitas tarjeta.</p>
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
