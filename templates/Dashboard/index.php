<?php
$configuredLimit = (int)($planCapabilities['sites_configured_limit'] ?? $plan?->max_sites ?? 0);
$publishedLimit = (int)($planCapabilities['sites_published_limit'] ?? $plan?->max_published ?? 0);
$usagePercent = static function (int $used, int $limit): int {
    if ($limit <= 0) {
        return 0;
    }

    return min(100, (int)round(($used / $limit) * 100));
};
$configuredPercent = $usagePercent((int)$siteUsage['configured'], $configuredLimit);
$publishedPercent = $usagePercent((int)$siteUsage['published'], $publishedLimit);
$overLimit = !empty($siteUsage['over_limit']);
$subscriptionIsActive = $hasActivePlan && $subscription && $plan;
$subscriptionStatusLabel = $subscriptionIsActive ? 'Suscripción activa' : 'Suscripción sin vigencia';
$subscriptionStatusClass = $subscriptionIsActive ? 'status-active' : 'status-expired';
$daysLabel = $daysRemaining === null ? 'Sin vigencia' : ((int)$daysRemaining . ' día' . ((int)$daysRemaining === 1 ? '' : 's'));
$statusMeta = [
    'draft' => ['Borrador', 'status-draft'],
    'published' => ['Publicado', 'status-published'],
    'paused' => ['Pausado', 'status-paused'],
];
?>
<?php $this->assign('title', 'Mis sitios | CatOps'); ?>

<section class="page-head">
  <div>
    <h1>Mis sitios</h1>
    <p>Administra tus cartas, catálogos, estados de publicación y accesos rápidos.</p>
  </div>
  <a class="button" href="<?= $hasActivePlan ? '/sitios/nuevo' : '/planes' ?>"><?= $hasActivePlan ? 'Crear sitio' : 'Activar plan' ?></a>
</section>

<?php if ($subscription && $plan): ?>
  <?php if ($overLimit): ?>
    <article class="message" role="status">
      Mantendremos tus sitios actuales sin cambios. Tu uso supera el límite actual del plan, por lo que no podrás crear ni publicar sitios nuevos hasta reducirlo o subir de plan.
    </article>
  <?php endif; ?>
  <section class="dashboard-kpis" aria-label="Resumen de uso">
    <article class="card metric-card">
      <div class="metric-label"><span class="metric-icon" aria-hidden="true">◈</span>Sitios configurados</div>
      <div class="metric-value"><?= (int)$siteUsage['configured'] ?></div>
      <p class="metric-support">de <?= $configuredLimit ?> disponible<?= $configuredLimit === 1 ? '' : 's' ?></p>
    </article>
    <article class="card metric-card">
      <div class="metric-label"><span class="metric-icon" aria-hidden="true">↗</span>Sitios publicados</div>
      <div class="metric-value"><?= (int)$siteUsage['published'] ?></div>
      <p class="metric-support">listos para el público</p>
    </article>
    <article class="card metric-card">
      <div class="metric-label"><span class="metric-icon" aria-hidden="true">◷</span>Días restantes</div>
      <div class="metric-value"><?= $daysRemaining === null ? '—' : (int)$daysRemaining ?></div>
      <p class="metric-support"><?= $daysRemaining === null ? 'sin plan vigente' : 'de tu plan actual' ?></p>
    </article>
  </section>

  <section class="card subscription-overview">
    <div class="subscription-main">
      <div class="subscription-title">
        <h2>Plan <?= h($plan->name) ?></h2>
        <span class="status <?= $subscriptionStatusClass ?>"><?= h($subscriptionStatusLabel) ?></span>
      </div>
      <div class="plan-price">$<?= number_format((int)$plan->monthly_price, 0, ',', '.') ?> <small>/ mes</small></div>
      <div class="usage-row"><span>Vigencia del plan</span><span><?= h($daysLabel) ?> restante<?= $daysRemaining === 1 ? '' : 's' ?></span></div>
      <div class="usage-track" aria-label="Días restantes del plan"><span class="usage-bar navy" style="width: <?= $daysRemaining === null ? 0 : min(100, max(0, (int)round(((int)$daysRemaining / 30) * 100))) ?>%"></span></div>
      <div class="subscription-actions">
        <?= $this->Form->create(null, ['url' => '/payments/create']) ?>
        <?= $this->Form->hidden('plan', ['value' => $plan->slug]) ?>
        <?= $this->Form->button('Extender 30 días', ['class' => 'button']) ?>
        <?= $this->Form->end() ?>
        <a class="subtle-link" href="/planes">Ver detalles del plan</a>
      </div>
    </div>
    <div class="subscription-usage">
      <p class="usage-label">Uso actual</p>
      <div class="usage-row"><span>Sitios configurados</span><span><?= (int)$siteUsage['configured'] ?> / <?= $configuredLimit ?></span></div>
      <div class="usage-track"><span class="usage-bar" style="width: <?= $configuredPercent ?>%"></span></div>
      <div class="usage-row"><span>Sitios publicados</span><span><?= (int)$siteUsage['published'] ?> / <?= $publishedLimit ?></span></div>
      <div class="usage-track"><span class="usage-bar navy" style="width: <?= $publishedPercent ?>%"></span></div>
    </div>
  </section>

  <section class="plan-change-card">
    <div class="plan-change-header">
      <h2>Cambiar de plan</h2>
      <p>Escala cuando necesites más sitios y publicaciones.</p>
    </div>
    <?php if ($upgradePlans): ?>
      <div class="upgrade-grid">
        <?php foreach ($upgradePlans as $index => $upgradePlan): ?>
          <?php $upgradeCapabilities = $planServiceCapabilities[$upgradePlan->slug] ?? []; ?>
          <article class="upgrade-option<?= $index === 0 ? ' is-recommended' : '' ?>">
            <?php if ($index === 0): ?><span class="plan-badge">Siguiente nivel</span><?php endif; ?>
            <strong>Plan <?= h($upgradePlan->name) ?></strong>
            <span class="upgrade-price">$<?= number_format((int)$upgradePlan->monthly_price, 0, ',', '.') ?> <small>/ mes</small></span>
            <small>Hasta <?= (int)($upgradeCapabilities['sites_configured_limit'] ?? $upgradePlan->max_sites) ?> sitios configurados.</small>
            <small>Hasta <?= (int)($upgradeCapabilities['sites_published_limit'] ?? $upgradePlan->max_published) ?> sitios publicados.</small>
            <?= $this->Form->create(null, ['url' => '/payments/create']) ?>
            <?= $this->Form->hidden('plan', ['value' => $upgradePlan->slug]) ?>
            <?= $this->Form->button('Subir a ' . $upgradePlan->name, ['class' => $index === 0 ? 'button' : 'button dark']) ?>
            <?= $this->Form->end() ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <article class="card"><p class="meta">Ya estás en el plan más alto disponible.</p></article>
    <?php endif; ?>
  </section>
<?php endif; ?>

<?php if (!$hasActivePlan): ?>
  <article class="card" style="margin-bottom:28px">
    <h2>Activa un plan para crear una web</h2>
    <p>Para crear y publicar sitios necesitas una suscripción activa con un pago vigente de 30 días.</p>
    <a class="button" href="/planes">Ver planes</a>
  </article>
<?php endif; ?>

<section>
  <div class="sites-section-header">
    <div>
      <h2>Tus sitios</h2>
      <p>Edita, previsualiza o publica tus cartas y catálogos.</p>
    </div>
  </div>

  <?php if ($sites->isEmpty()): ?>
    <article class="card">
      <h3>Aún no tienes sitios</h3>
      <p>Crea el primero, elige carta o catálogo y empieza a cargar textos, logo y productos.</p>
      <a class="button" href="<?= $hasActivePlan ? '/sitios/nuevo' : '/planes' ?>"><?= $hasActivePlan ? 'Crear mi primer sitio' : 'Elegir plan' ?></a>
    </article>
  <?php else: ?>
    <div class="site-list">
      <?php foreach ($sites as $site): ?>
        <?php [$statusLabel, $statusClass] = $statusMeta[$site->status] ?? [ucfirst((string)$site->status), 'status-draft']; ?>
        <article class="card site-card">
          <div>
            <div class="site-card-heading"><h3><?= h($site->name) ?></h3><span class="status <?= h($statusClass) ?>"><?= h($statusLabel) ?></span></div>
            <span class="site-url"><?= h($site->subdomain) ?>.<?= h($baseDomain ?? 'catops.cl') ?></span>
            <div class="site-meta">
              <span>Plantilla: <strong><?= h($site->template->name ?? 'Sin plantilla') ?></strong></span>
              <span>Actualizado <?= $site->modified ? h($site->modified->i18nFormat('d MMM yyyy')) : 'recientemente' ?></span>
              <span><?= $site->status === 'published' ? 'Visible para clientes' : 'Sin publicar' ?></span>
            </div>
            <div class="site-actions">
              <a class="button" href="/sitios/editar/<?= (int)$site->id ?>">Editar</a>
              <a class="button secondary" href="/sitios/preview/<?= (int)$site->id ?>" target="_blank" rel="noopener">Vista previa</a>
              <?php if ($site->status === 'published' && !empty($publicUrlService)): ?>
                <a class="button secondary" href="<?= h($publicUrlService->publicUrl($site)) ?>" target="_blank" rel="noopener">Abrir sitio</a>
              <?php endif; ?>
            </div>
          </div>
          <div class="site-preview-art" aria-hidden="true">
            <div class="site-preview-dots"><span></span><span></span><span></span></div>
            <div class="site-preview-page"><span class="site-preview-title"></span><span class="site-preview-copy"></span><div class="site-preview-grid"><span></span><span></span><span></span><span></span></div></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
