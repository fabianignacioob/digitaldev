<?php $this->assign('title', 'Editar sitio | CatOps'); ?>
<?php $templateSlug = $site->template->slug ?? ''; ?>

<section class="page-head">
  <div>
    <h1><?= h($site->name) ?></h1>
    <p>Actualiza identidad, contacto, SEO y estado de publicación.</p>
  </div>
  <div class="toolbar">
    <a class="button secondary" href="/sitios/preview/<?= (int)$site->id ?>" target="_blank" rel="noopener">Vista previa</a>
    <?php if ($site->status === 'published'): ?>
      <span class="status" aria-label="Estado del sitio: publicado">Publicado</span>
    <?php else: ?>
      <?= $this->Form->postLink('Publicar', '/sitios/publicar/' . (int)$site->id, [
          'class' => 'button',
          'confirm' => '¿Publicar este sitio?',
      ]) ?>
    <?php endif; ?>
  </div>
</section>

<section class="grid site-edit-grid">
  <article class="card">
    <h2>Configuración general</h2>
    <?= $this->Form->create($site, ['type' => 'file']) ?>
    <?= $this->Form->control('name', ['label' => 'Nombre del negocio']) ?>
    <?= $this->Form->control('subdomain', ['label' => 'Subdominio']) ?>
    <?php if (!empty($publicUrl)): ?>
      <p class="meta">URL pública: <a href="<?= h($publicUrl) ?>" target="_blank" rel="noopener"><?= h($publicUrl) ?></a></p>
    <?php endif; ?>
    <?= $this->Form->control('template_id', ['label' => 'Plantilla', 'options' => $templates]) ?>
    <?= $this->Form->control('theme_id', ['label' => 'Tema visual', 'options' => $themes]) ?>
    <?= $this->Form->control('status', [
        'label' => 'Estado',
        'options' => ['draft' => 'Borrador', 'published' => 'Publicado', 'paused' => 'Pausado'],
    ]) ?>
    <?= $this->Form->control('logo_upload', ['label' => 'Cambiar logo', 'type' => 'file']) ?>
    <p class="meta">Formatos permitidos: JPG, PNG o WEBP. Las imágenes grandes se optimizan automáticamente.</p>
    <?php if ($site->logo_path): ?>
      <div class="image-preview">
        <img src="/<?= h($site->logo_path) ?>" alt="Logo actual">
      </div>
    <?php endif; ?>
    <div class="form-actions form-actions-stacked">
      <?= $this->Form->button('Guardar configuración') ?>
    </div>
    <?= $this->Form->end() ?>
    <?php if ($site->logo_path): ?>
      <div class="form-actions form-actions-stacked">
        <?= $this->Form->postLink('Eliminar logo', ['controller' => 'Sites', 'action' => 'deleteLogo', $site->id], [
            'class' => 'button danger',
            'confirm' => '¿Eliminar el logo de este sitio?',
        ]) ?>
      </div>
    <?php endif; ?>
  </article>

  <article class="card">
    <h2>Contacto y SEO</h2>
    <?= $this->Form->create($site) ?>
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
    <?= $this->Form->button('Guardar contacto') ?>
    <?= $this->Form->end() ?>
  </article>
</section>

<section class="card site-followup-card">
  <h2><?= str_starts_with($templateSlug, 'catalogo-') ? 'Catálogo' : 'Carta' ?></h2>
  <p>Configura fondo, títulos, productos<?= str_ends_with($templateSlug, '-categorias') ? ' y categorías' : '' ?>.</p>
  <a class="button" href="/sitios/<?= (int)$site->id ?>/carta">Administrar contenido</a>
</section>

<section class="card site-followup-card">
  <h2>Vista actual</h2>
  <p>Plantilla seleccionada: <strong><?= h($site->template->name ?? 'Sin plantilla') ?></strong>. Si cambias la plantilla, guarda la configuración para ver las opciones correspondientes.</p>
  <a class="button secondary" href="/sitios/preview/<?= (int)$site->id ?>" target="_blank" rel="noopener">Abrir vista previa</a>
</section>
