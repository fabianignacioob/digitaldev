<?php $this->assign('title', 'Login | CatOps'); ?>

<h1>Iniciar sesión</h1>
<p>Accede para administrar tus sitios, textos, logos y secciones publicadas.</p>

<?= $this->Form->create() ?>
<?= $this->Form->control('email', ['label' => 'Correo', 'autocomplete' => 'email']) ?>
<?= $this->Form->control('password', ['label' => 'Contraseña', 'autocomplete' => 'current-password']) ?>
<?= $this->Form->button('Entrar') ?>
<?= $this->Form->end() ?>

<p><a class="muted-link" href="/recuperar-contrasena">¿Olvidaste tu contraseña?</a></p>
<p>¿Aún no tienes cuenta? <a class="muted-link" href="/registro">Crear cuenta</a></p>
