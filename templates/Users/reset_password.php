<?php $this->assign('title', 'Nueva contraseña | CatOps'); ?>

<h1>Crear nueva contraseña</h1>
<p>Define una contraseña nueva para volver a acceder a tu cuenta.</p>

<?= $this->Form->create() ?>
<?= $this->Form->hidden('token', ['value' => $token]) ?>
<?= $this->Form->control('password', [
    'label' => 'Nueva contraseña',
    'autocomplete' => 'new-password',
]) ?>
<?= $this->Form->control('password_confirmation', [
    'type' => 'password',
    'label' => 'Repite la contraseña',
    'autocomplete' => 'new-password',
]) ?>
<?= $this->Form->button('Guardar contraseña') ?>
<?= $this->Form->end() ?>
