<?php $this->assign('title', 'Editar vitrina | CatOps'); ?>
<?php $templateSlug = $site->template->slug ?? ''; ?>

<section class="page-head">
  <div>
    <h1><?= h($site->name) ?></h1>
    <p>Actualiza identidad, contacto, SEO y estado de publicación de tu vitrina.</p>
  </div>
  <div class="toolbar">
    <a class="button secondary" href="/sitios/preview/<?= (int)$site->id ?>" target="_blank" rel="noopener">Vista previa de la vitrina</a>
    <?php if ($site->status === 'published'): ?>
      <span class="status" aria-label="Estado de la vitrina: publicada">Publicada</span>
    <?php else: ?>
      <?= $this->Form->postLink('Publicar vitrina', '/sitios/publicar/' . (int)$site->id, [
          'class' => 'button',
          'confirm' => '¿Publicar esta vitrina?',
      ]) ?>
    <?php endif; ?>
  </div>
</section>

<section class="grid site-edit-grid">
  <article class="card">
    <h2>Configuración general</h2>
    <?= $this->Form->create($site, ['type' => 'file']) ?>
    <?= $this->Form->control('name', ['label' => 'Nombre del negocio']) ?>
    <?= $this->Form->control('subdomain', ['label' => 'Subdominio de tu vitrina']) ?>
    <?php if (!empty($publicUrl)): ?>
      <p class="meta">Enlace de tu vitrina: <a href="<?= h($publicUrl) ?>" target="_blank" rel="noopener"><?= h($publicUrl) ?></a></p>
    <?php endif; ?>
    <?= $this->Form->control('template_id', ['label' => 'Plantilla', 'options' => $templates]) ?>
    <?= $this->Form->control('theme_id', ['label' => 'Paleta de colores', 'options' => $themes]) ?>
    <?php if (str_ends_with($templateSlug, '-categorias')): ?>
      <?php if ($categoryLayoutAvailable): ?>
        <?= $this->Form->control('category_layout', [
            'label' => 'Diseño de categorías',
            'options' => [
                'normal' => 'Normal: una categoría por fila',
                'blocks' => 'Por bloques: dos categorías por fila',
            ],
            'value' => $site->catalog_setting->category_layout ?? 'normal',
        ]) ?>
        <p class="meta">En teléfono las categorías se muestran una debajo de otra para conservar la lectura.</p>
      <?php else: ?>
        <p class="meta">El diseño por bloques está disponible en los planes Negocio y Full con plantillas por categorías.</p>
      <?php endif; ?>
    <?php endif; ?>
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
            'confirm' => '¿Eliminar el logo de esta vitrina?',
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
    <fieldset class="contact-visibility-options">
      <legend>Mostrar en la vitrina pública</legend>
      <?= $this->Form->control('show_whatsapp', [
          'label' => 'Mostrar acceso a WhatsApp',
          'type' => 'checkbox',
        ]) ?>
      <?= $this->Form->control('show_instagram', [
          'label' => 'Mostrar acceso a Instagram',
          'type' => 'checkbox',
        ]) ?>
    </fieldset>
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
  <h2>Código QR</h2>
  <?php if (!$qrEnabled): ?>
    <p>El código QR está disponible desde el plan Negocio.</p>
    <a class="button secondary" href="/planes">Ver planes</a>
  <?php elseif ($site->status !== 'published'): ?>
    <p>Publica la vitrina para generar un código QR que dirija a su enlace público.</p>
  <?php elseif (!$siteQrCode): ?>
    <p>Genera un código QR permanente para imprimir, descargar o compartir. Seguirá funcionando aunque después cambies el subdominio o conectes un dominio propio.</p>
    <?= $this->Form->postLink('Generar código QR', '/sitios/' . (int)$site->id . '/qr/generar', [
        'class' => 'button',
    ]) ?>
  <?php else: ?>
    <p>Tu código QR ya está listo. Su enlace es permanente y siempre dirige a la URL pública vigente de tu vitrina.</p>
    <div class="qr-management">
      <div class="qr-preview-frame qr-preview-frame--<?= h($siteQrCode->frame_style) ?>">
        <img src="/sitios/<?= (int)$site->id ?>/qr?format=svg" alt="Vista previa del código QR de <?= h($site->name) ?>">
      </div>
      <div class="qr-management-content">
        <?= $this->Form->create($siteQrCode, ['url' => '/sitios/' . (int)$site->id . '/qr/estilo']) ?>
        <?= $this->Form->control('frame_style', [
            'label' => 'Forma del marco',
            'options' => ['square' => 'Cuadrado', 'rounded' => 'Redondeado'],
            'value' => $siteQrCode->frame_style,
        ]) ?>
        <p class="meta">La forma cambia el marco visual; el patrón se mantiene cuadrado para que cualquier cámara lo lea bien.</p>
        <?= $this->Form->button('Guardar forma', ['class' => 'button secondary']) ?>
        <?= $this->Form->end() ?>
        <div class="qr-actions">
          <a class="button secondary" href="/sitios/<?= (int)$site->id ?>/qr?format=svg&amp;download=1">Descargar SVG</a>
          <a class="button secondary" href="/sitios/<?= (int)$site->id ?>/qr?format=png&amp;download=1">Descargar PNG</a>
          <button class="button secondary" type="button" data-qr-copy data-qr-url="<?= h($qrPublicUrl) ?>">Copiar enlace</button>
          <button class="button" type="button" data-qr-share data-qr-url="<?= h($qrPublicUrl) ?>" data-qr-title="<?= h($site->name) ?>">Compartir</button>
        </div>
        <p class="qr-share-status" data-qr-share-status aria-live="polite"></p>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="card site-followup-card">
  <h2>Vista actual</h2>
  <p>Plantilla seleccionada: <strong><?= h($site->template->name ?? 'Sin plantilla') ?></strong>. Si cambias la plantilla, guarda la configuración para ver las opciones correspondientes.</p>
  <a class="button secondary" href="/sitios/preview/<?= (int)$site->id ?>" target="_blank" rel="noopener">Abrir vista previa</a>
</section>

<?php if ($siteQrCode): ?>
<script>
    (() => {
        const copyButton = document.querySelector('[data-qr-copy]');
        const shareButton = document.querySelector('[data-qr-share]');
        const status = document.querySelector('[data-qr-share-status]');
        const setStatus = (message) => {
            if (status) status.textContent = message;
        };
        const copyUrl = async (url) => {
            try {
                await navigator.clipboard.writeText(url);
                setStatus('Enlace copiado.');
            } catch (error) {
                setStatus('No pudimos copiar el enlace. Puedes copiarlo desde la barra del navegador.');
            }
        };

        copyButton?.addEventListener('click', () => copyUrl(copyButton.dataset.qrUrl));
        shareButton?.addEventListener('click', async () => {
            const url = shareButton.dataset.qrUrl;
            if (navigator.share) {
                try {
                    await navigator.share({ title: shareButton.dataset.qrTitle, url });
                    setStatus('Enlace compartido.');
                } catch (error) {
                    if (error.name !== 'AbortError') setStatus('No pudimos abrir las opciones para compartir.');
                }
                return;
            }
            await copyUrl(url);
        });
    })();
</script>
<?php endif; ?>

<section class="card site-followup-card">
  <h2>Dominio propio</h2>
  <?php if (!$customDomainAvailable): ?>
    <p>Tu plan actual no incluye dominios propios. Puedes seguir usando <strong><?= h($site->subdomain . '.' . $baseDomain) ?></strong> o subir de plan para conectar un dominio registrado.</p>
    <a class="button secondary" href="/planes">Ver planes</a>
  <?php else: ?>
    <p>Conecta un dominio registrado a esta vitrina. Uso actual: <strong><?= (int)$customDomainUsage['used'] ?> de <?= (int)$customDomainUsage['limit'] ?></strong>.</p>
    <?php foreach ($customDomains as $domain): ?>
      <article class="domain-setup">
        <div class="domain-setup-heading">
          <strong><?= h($domain->domain) ?></strong>
          <span class="status <?= $domain->verified && $domain->active ? '' : 'draft' ?>"><?= $domain->verified && $domain->active ? 'Verificado' : 'Pendiente' ?></span>
        </div>
        <?php if ($domain->verified && $domain->active): ?>
          <p class="meta">Activo en <a href="<?= h($domainService->publicUrl($domain)) ?>" target="_blank" rel="noopener"><?= h($domainService->publicUrl($domain)) ?></a>.</p>
        <?php else: ?>
          <p class="meta">En el proveedor DNS de tu dominio, agrega este registro TXT para demostrar que eres su propietario:</p>
          <dl class="domain-dns-record">
            <dt>Tipo</dt><dd>TXT</dd>
            <dt>Nombre</dt><dd><code><?= h($domainService->verificationRecordName($domain)) ?></code></dd>
            <dt>Valor</dt><dd><code><?= h($domain->verification_token) ?></code></dd>
          </dl>
          <p class="meta">Luego dirige el tráfico: para <strong>www</strong> usa un CNAME a <code><?= h($domainService->routingCnameTarget()) ?></code>. Para el dominio raíz usa un registro A a <code><?= h($domainService->routingIpv4() ?? 'la IP pública configurada por CatOps') ?></code>.</p>
          <?php if ($domain->last_dns_error): ?><p class="form-error"><?= h($domain->last_dns_error) ?></p><?php endif; ?>
          <div class="form-actions">
            <?= $this->Form->postLink('Verificar DNS', '/sitios/' . (int)$site->id . '/dominios/' . (int)$domain->id . '/verificar', ['class' => 'button']) ?>
          </div>
        <?php endif; ?>
        <div class="form-actions form-actions-stacked">
          <?= $this->Form->postLink('Eliminar dominio', '/sitios/' . (int)$site->id . '/dominios/' . (int)$domain->id . '/eliminar', [
              'class' => 'button danger',
              'confirm' => '¿Eliminar este dominio? La vitrina y su contenido seguirán disponibles con su enlace de VitrinaHub.',
          ]) ?>
        </div>
      </article>
    <?php endforeach; ?>
    <?php if ($customDomainUsage['remaining'] > 0): ?>
      <?= $this->Form->create(null, ['url' => '/sitios/' . (int)$site->id . '/dominios']) ?>
      <?= $this->Form->control('domain', [
          'label' => 'Agregar dominio',
          'placeholder' => 'tunegocio.cl o www.tunegocio.cl',
          'maxlength' => 180,
          'required' => true,
      ]) ?>
      <p class="meta">No incluyas https:// ni rutas. En NIC.cl configura los DNS desde el proveedor que administre la zona del dominio.</p>
      <?= $this->Form->button('Agregar dominio') ?>
      <?= $this->Form->end() ?>
    <?php endif; ?>
  <?php endif; ?>
</section>
