<?php $this->assign('title', 'Verificar correo | CatOps'); ?>

<h1>Verifica tu correo</h1>
<p>Ingresa el código de 6 dígitos que enviamos a tu correo para activar tu cuenta.</p>

<?= $this->Form->create() ?>
<?= $this->Form->control('email', [
    'label' => 'Correo',
    'value' => $email ?? '',
]) ?>
<?= $this->Form->control('code', [
    'label' => 'Código',
    'placeholder' => '123456',
    'maxlength' => 6,
]) ?>
<?= $this->Form->button('Verificar y entrar') ?>
<?= $this->Form->end() ?>

<?= $this->Form->create(null, ['url' => '/reenviar-codigo']) ?>
<?= $this->Form->button('Reenviar código', ['class' => 'secondary']) ?>
<?= $this->Form->end() ?>

<p><a class="muted-link" href="/login">Volver al login</a></p>
