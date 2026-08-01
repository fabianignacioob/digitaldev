<?php $this->assign('title', 'Nueva vitrina | CatOps'); ?>

<section class="page-head">
  <div>
    <h1>Nueva vitrina</h1>
    <p>Parte desde una carta o catálogo simple. Después podrás editar diseño, productos y datos de contacto.</p>
  </div>
</section>

<article class="card">
  <?= $this->Form->create($site, ['type' => 'file']) ?>
  <div class="grid">
    <div>
      <?= $this->Form->control('name', ['label' => 'Nombre del negocio']) ?>
      <?= $this->Form->control('subdomain', [
          'label' => 'Subdominio gratuito',
          'placeholder' => 'market',
      ]) ?>
      <p class="meta">Este será el enlace público de tu vitrina: por ejemplo, market.<?= h($baseDomain ?? 'vitrinahub.cl') ?>.</p>
      <?= $this->Form->control('template_id', ['label' => 'Plantilla', 'options' => $templates]) ?>
      <?= $this->Form->control('theme_id', ['label' => 'Tema visual', 'options' => $themes]) ?>
    </div>
    <div>
      <?= $this->Form->control('logo_upload', ['label' => 'Logo', 'type' => 'file']) ?>
      <p class="meta">Formatos permitidos: JPG, PNG o WEBP. Máximo configurable desde el servidor.</p>
      <?= $this->Form->control('whatsapp_country_code', [
          'label' => 'Zona WhatsApp',
          'options' => ['56' => 'Chile +56', '54' => 'Argentina +54', '51' => 'Perú +51', '57' => 'Colombia +57', '52' => 'México +52'],
          'default' => '56',
      ]) ?>
      <?= $this->Form->control('whatsapp_number', [
          'label' => 'Número WhatsApp',
          'placeholder' => '912345678',
          'pattern' => '[0-9]+',
      ]) ?>
      <p class="meta">Ingresa solo los números faltantes, sin código de país.</p>
      <?= $this->Form->control('instagram_username', [
          'label' => 'Usuario Instagram',
          'placeholder' => 'tu_negocio',
      ]) ?>
      <p class="meta">Solo el usuario, sin https://instagram.com/.</p>
      <?= $this->Form->control('business_address', [
          'label' => 'Dirección del negocio',
          'placeholder' => 'Av. Italia 850, Providencia',
      ]) ?>
      <?= $this->Form->control('business_hours', [
          'label' => 'Horario de atención',
          'placeholder' => 'Mar a Dom · 18:30 a 23:00',
      ]) ?>
      <?= $this->Form->control('public_phone', [
          'label' => 'Teléfono público',
          'placeholder' => '+56 9 1234 5678',
      ]) ?>
      <?= $this->Form->control('public_email', [
          'label' => 'Correo público',
          'type' => 'email',
          'placeholder' => 'hola@tunegocio.cl',
      ]) ?>
      <?= $this->Form->control('seo_title', [
          'label' => 'Título SEO',
      ]) ?>
      <p class="meta">Texto que aparece como título en Google y pestañas del navegador.</p>
      <?= $this->Form->control('seo_description', [
          'label' => 'Descripción SEO',
      ]) ?>
      <p class="meta">Resumen breve para buscadores; ayuda a explicar qué ofrece el negocio.</p>
    </div>
  </div>
  <?= $this->Form->button('Crear vitrina') ?>
  <?= $this->Form->end() ?>
</article>
