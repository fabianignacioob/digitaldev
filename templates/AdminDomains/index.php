<?php $this->assign('title', 'Dominios'); ?>
<div class="d-flex justify-content-between gap-3 mb-4 page-heading">
  <div>
    <h1 class="h2 mb-1">Dominios y subdominios</h1>
    <p class="text-muted mb-0">Revisa asociaciones, verificación y disponibilidad pública.</p>
  </div>
</div>

<section class="card admin-card mb-4">
  <div class="card-body">
    <form class="row g-2" method="get">
      <div class="col-12 col-md-4"><input class="form-control" name="q" value="<?= h($filters['q']) ?>" placeholder="Hostname, sitio o propietario"></div>
      <div class="col-6 col-md-2"><select class="form-select" name="type"><option value="">Tipo</option><option value="subdomain" <?= $filters['type'] === 'subdomain' ? 'selected' : '' ?>>Subdominio</option><option value="custom" <?= $filters['type'] === 'custom' ? 'selected' : '' ?>>Dominio propio</option></select></div>
      <div class="col-6 col-md-2"><select class="form-select" name="active"><option value="">Estado</option><option value="1" <?= $filters['active'] === '1' ? 'selected' : '' ?>>Activo</option><option value="0" <?= $filters['active'] === '0' ? 'selected' : '' ?>>Inactivo</option></select></div>
      <div class="col-6 col-md-2"><select class="form-select" name="verified"><option value="">Verificación</option><option value="1" <?= $filters['verified'] === '1' ? 'selected' : '' ?>>Verificado</option><option value="0" <?= $filters['verified'] === '0' ? 'selected' : '' ?>>Pendiente</option></select></div>
      <div class="col-6 col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
    </form>
  </div>
</section>

<section class="card admin-card">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead><tr><th>Hostname</th><th>Sitio</th><th>Propietario</th><th>Tipo</th><th>Estado</th><th>Verificación</th><th>Creado</th><th>Conflictos</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($pagination['items'] as $domain): ?>
        <tr>
          <td><strong><?= h($domain->domain) ?></strong><small class="d-block"><a href="<?= h($domain->admin_public_url) ?>" target="_blank" rel="noopener">URL pública</a></small></td>
          <td><?= h($domain->site->name ?? 'Sitio eliminado') ?></td>
          <td><?= h($domain->site->user->email ?? '—') ?></td>
          <td><?= h($domain->type) ?></td>
          <td><span class="badge badge-<?= $domain->active ? 'success' : 'secondary' ?> badge-status"><?= $domain->active ? 'activo' : 'inactivo' ?></span></td>
          <td><span class="badge badge-<?= $domain->verified ? 'success' : 'warning' ?> badge-status"><?= $domain->verified ? 'verificado' : 'pendiente' ?></span></td>
          <td><?= h($domain->created) ?></td>
          <td><small class="<?= $domain->admin_issues === [] ? 'text-muted' : 'text-danger' ?>"><?= h($domain->admin_issues === [] ? 'Sin conflictos' : implode(' · ', $domain->admin_issues)) ?></small></td>
          <td><a class="btn btn-sm btn-outline-primary" href="/admin/domains/<?= (int)$domain->id ?>">Ver</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-body"><?= $this->element('admin_pagination', compact('pagination')) ?></div>
</section>
