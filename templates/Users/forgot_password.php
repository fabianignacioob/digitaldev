<?php $this->assign('title', 'Recuperar contraseña | CatOps'); ?>

<h1>Recuperar contraseña</h1>
<p>Ingresa tu correo y te enviaremos un enlace para crear una contraseña nueva.</p>

<?= $this->Form->create() ?>
<?= $this->Form->control('email', ['label' => 'Correo', 'autocomplete' => 'email']) ?>
<?= $this->Form->button('Enviar enlace') ?>
<?= $this->Form->end() ?>

<p><a class="muted-link" href="/login">Volver al login</a></p>
