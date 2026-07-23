<?php $this->assign('title', h($domain->domain) . ' | Dominios'); ?>
<div class="d-flex justify-content-between gap-3 mb-4 page-heading">
  <div>
    <a class="small" href="/admin/domains">← Dominios</a>
    <h1 class="h2 mb-1"><?= h($domain->domain) ?></h1>
    <p class="text-muted mb-0"><?= h($domain->site->name ?? 'Sitio eliminado') ?> · <?= h($domain->site->user->email ?? '—') ?></p>
  </div>
  <span class="badge badge-<?= $domain->active ? 'success' : 'secondary' ?> align-self-center"><?= $domain->active ? 'activo' : 'inactivo' ?></span>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <section class="card admin-card mb-4"><div class="card-body">
      <h2 class="h5">Estado del hostname</h2>
      <dl class="row mb-0">
        <dt class="col-sm-4">Tipo</dt><dd class="col-sm-8"><?= h($domain->type) ?></dd>
        <dt class="col-sm-4">Verificación</dt><dd class="col-sm-8"><?= $domain->verified ? 'Verificado' : 'Pendiente de verificación' ?></dd>
        <dt class="col-sm-4">URL pública</dt><dd class="col-sm-8"><a href="<?= h($publicUrl) ?>" target="_blank" rel="noopener"><?= h($publicUrl) ?></a></dd>
        <dt class="col-sm-4">Creado</dt><dd class="col-sm-8"><?= h($domain->created) ?></dd>
      </dl>
    </div></section>
    <section class="card admin-card mb-4"><div class="card-body">
      <h2 class="h5">Consistencia</h2>
      <?php if ($issues === []): ?><p class="text-success mb-0">No se detectaron conflictos de hostname, asociación ni estado.</p>
      <?php else: ?><ul class="mb-0 text-danger"><?php foreach ($issues as $issue): ?><li><?= h($issue) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div></section>
    <section class="card admin-card"><div class="card-body">
      <h2 class="h5">Auditoría</h2>
      <?php if ($audits->isEmpty()): ?><p class="text-muted mb-0">Sin acciones administrativas registradas.</p><?php endif; ?>
      <?php foreach ($audits as $audit): ?><div class="border-bottom py-2"><strong class="small"><?= h($audit->action) ?></strong><small class="d-block text-muted"><?= h($audit->created) ?></small></div><?php endforeach; ?>
    </div></section>
  </div>
  <aside class="col-lg-4">
    <section class="card admin-card mb-4"><div class="card-body">
      <h2 class="h5">Estado administrativo</h2>
      <?php if ($domain->active): ?>
        <?= $this->Form->create(null, ['url' => '/admin/domains/' . $domain->id . '/deactivate', 'data-admin-confirm' => '¿Desactivar este dominio? El sitio y su contenido no se eliminarán.']) ?>
        <textarea class="form-control form-control-sm mb-2" name="reason" required maxlength="500" placeholder="Motivo de desactivación"></textarea><button class="btn btn-outline-danger w-100">Desactivar dominio</button><?= $this->Form->end() ?>
      <?php else: ?>
        <?= $this->Form->create(null, ['url' => '/admin/domains/' . $domain->id . '/reactivate', 'data-admin-confirm' => '¿Reactivar este dominio?']) ?>
        <textarea class="form-control form-control-sm mb-2" name="reason" required maxlength="500" placeholder="Motivo de reactivación"></textarea><button class="btn btn-success w-100">Reactivar dominio</button><?= $this->Form->end() ?>
      <?php endif; ?>
    </div></section>
    <section class="card admin-card"><div class="card-body">
      <h2 class="h5">Corregir asociación</h2>
      <p class="small text-muted">Los subdominios solo pueden asociarse al sitio cuyo hostname coincide.</p>
      <?= $this->Form->create(null, ['url' => '/admin/domains/' . $domain->id . '/reassign', 'data-admin-confirm' => '¿Actualizar la asociación de este dominio?']) ?>
      <select class="form-select form-select-sm mb-2" name="site_id" required><?php foreach ($sites as $siteId => $siteName): ?><option value="<?= (int)$siteId ?>" <?= (int)$siteId === (int)$domain->site_id ? 'selected' : '' ?>><?= h($siteName) ?></option><?php endforeach; ?></select>
      <textarea class="form-control form-control-sm mb-2" name="reason" required maxlength="500" placeholder="Motivo de corrección"></textarea><button class="btn btn-outline-primary w-100">Guardar asociación</button><?= $this->Form->end() ?>
    </div></section>
  </aside>
</div>
