<?php $this->assign('title', 'Crear cuenta | CatOps'); ?>

<h1>Crear cuenta</h1>
<p>Regístrate para crear una landing simple, elegir plantilla y editar los textos principales de tu negocio.</p>

<?php if (!empty($selectedPlan)): ?>
  <p class="message">Plan seleccionado: <strong><?= h(ucfirst((string)$selectedPlan)) ?></strong>. El pago mensual se coordinará cuando esté integrada la etapa comercial.</p>
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
