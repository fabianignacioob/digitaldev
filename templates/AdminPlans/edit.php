<?php $this->assign('title', 'Editar plan | CatOps Admin'); ?>
<div class="mb-4">
  <a class="small" href="/admin/plans">← Planes</a>
  <h1 class="h2 mb-1">Editar <?= h($plan->name) ?></h1>
  <p class="text-muted mb-0">El slug <code><?= h($plan->slug) ?></code> se mantiene estable porque tiene <?= (int)$affectedSubscriptions ?> suscripciones asociadas.</p>
</div>

<?= $this->Form->create($plan, ['data-admin-confirm' => '¿Guardar los cambios de este plan?']) ?>
<div class="row g-4">
  <div class="col-lg-7">
    <section class="card admin-card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-7">
            <label class="form-label" for="plan-name">Nombre</label>
            <input class="form-control" id="plan-name" name="name" value="<?= h($plan->name) ?>" required maxlength="80">
          </div>
          <div class="col-md-5">
            <label class="form-label" for="plan-price">Precio mensual CLP</label>
            <input class="form-control" id="plan-price" type="number" min="0" name="monthly_price" value="<?= (int)$plan->monthly_price ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="plan-order">Orden comercial</label>
            <input class="form-control" id="plan-order" type="number" min="0" name="sort_order" value="<?= (int)$plan->sort_order ?>">
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="active" value="1" id="active" <?= $plan->active ? 'checked' : '' ?>>
              <label class="form-check-label" for="active">Plan activo</label>
            </div>
          </div>
        </div>
        <p class="small text-muted mb-0 mt-3">Los límites se guardan desde Capacidades para mantener una única fuente de verdad.</p>
      </div>
    </section>
  </div>

  <div class="col-lg-5">
    <section class="card admin-card">
      <div class="card-body">
        <h2 class="h5">Capacidades y límites</h2>
        <?php foreach ([
            'sites_configured_limit' => 'Sitios configurados',
            'sites_published_limit' => 'Sitios publicados',
            'items_limit' => 'Ítems por sitio',
            'categories_limit' => 'Categorías por sitio',
            'image_storage_limit_mb' => 'Almacenamiento de imágenes (MB)',
        ] as $key => $label): ?>
          <label class="form-label small" for="capability-<?= h($key) ?>"><?= h($label) ?></label>
          <input class="form-control form-control-sm mb-2" id="capability-<?= h($key) ?>" type="number" min="0" name="capabilities[<?= h($key) ?>]" value="<?= (int)($capabilities[$key] ?? 0) ?>">
        <?php endforeach; ?>

        <div class="row g-2 mt-1">
          <?php foreach ([
              'customization_level' => ['Personalización', ['none' => 'No incluida', 'basic' => 'Básica', 'extended' => 'Extendida', 'advanced' => 'Avanzada']],
              'analytics_level' => ['Estadísticas', ['none' => 'No incluidas', 'basic' => 'Básicas', 'advanced' => 'Avanzadas']],
              'seo_level' => ['SEO', ['none' => 'No incluido', 'basic' => 'Básico', 'standard' => 'Estándar', 'advanced' => 'Avanzado']],
          ] as $key => [$label, $options]): ?>
            <div class="col-12">
              <label class="form-label small" for="capability-<?= h($key) ?>"><?= h($label) ?></label>
              <select class="form-select form-select-sm" id="capability-<?= h($key) ?>" name="capabilities[<?= h($key) ?>]">
                <?php foreach ($options as $value => $optionLabel): ?>
                  <option value="<?= h($value) ?>" <?= ($capabilities[$key] ?? '') === $value ? 'selected' : '' ?>><?= h($optionLabel) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endforeach; ?>
        </div>

        <hr>
        <?php foreach ([
            'categories_enabled' => 'Categorías',
            'featured_items_enabled' => 'Productos destacados',
            'qr_enabled' => 'Código QR',
            'custom_domain_enabled' => 'Dominio propio',
            'premium_themes_enabled' => 'Temas premium',
            'catops_branding_removable' => 'Quitar marca CatOps',
            'priority_support' => 'Soporte prioritario',
        ] as $key => $label): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="capabilities[<?= h($key) ?>]" value="1" id="<?= h($key) ?>" <?= !empty($capabilities[$key]) ? 'checked' : '' ?>>
            <label class="form-check-label" for="<?= h($key) ?>"><?= h($label) ?></label>
          </div>
        <?php endforeach; ?>

        <hr>
        <h3 class="h6">Plantillas habilitadas</h3>
        <?php foreach ($templates as $slug => $name): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="capabilities[enabled_templates][]" value="<?= h($slug) ?>" id="template-<?= h($slug) ?>" <?= in_array($slug, (array)($capabilities['enabled_templates'] ?? []), true) ? 'checked' : '' ?>>
            <label class="form-check-label" for="template-<?= h($slug) ?>"><?= h($name) ?></label>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>

  <div class="col-12">
    <section class="card admin-card">
      <div class="card-body">
        <label class="form-label" for="plan-reason">Motivo administrativo</label>
        <textarea class="form-control mb-3" id="plan-reason" name="reason" required maxlength="500" placeholder="Explica el cambio y su impacto."></textarea>
        <button class="btn btn-primary">Guardar plan</button>
      </div>
    </section>
  </div>
</div>
<?= $this->Form->end() ?>
