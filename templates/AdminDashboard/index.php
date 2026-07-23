<?php $this->assign('title', 'Resumen | CatOps Admin'); ?>
<div class="d-flex justify-content-between gap-3 mb-4 page-heading">
  <div><h1 class="h2 mb-1">Resumen de plataforma</h1><p class="text-muted mb-0">Estado actual de usuarios, sitios, licencias y pagos.</p></div>
</div>
<div class="row g-3 mb-4">
  <?php foreach ([
    ['Usuarios', $stats['users']['total'], $stats['users']['verified'] . ' verificados'],
    ['Sitios', $stats['sites']['configured'], ($stats['sites']['published'] ?? 0) . ' publicados'],
    ['Suscripciones activas', $stats['subscriptions']['active'] ?? 0, ($stats['subscriptions']['expiring_soon'] ?? 0) . ' por vencer'],
    ['Pagos pendientes', $stats['payments']['pending'] ?? 0, ($stats['payments']['reconciliation'] ?? 0) . ' para conciliar'],
    ['Ingresos del mes', '$' . number_format((int)$stats['payments']['income_month'], 0, ',', '.'), 'solo pagos confirmados'],
  ] as [$label, $value, $detail]): ?>
    <div class="col-12 col-sm-6 col-xl"><article class="card stat-card h-100"><div class="card-body"><div class="text-muted small"><?= h($label) ?></div><div class="fs-3 fw-bold"><?= h((string)$value) ?></div><div class="small text-muted"><?= h($detail) ?></div></div></article></div>
  <?php endforeach; ?>
</div>
<div class="row g-4">
  <div class="col-lg-7"><section class="card admin-card h-100"><div class="card-body"><h2 class="h5">Clientes e ingresos por plan</h2><div class="table-wrap"><table class="table table-sm align-middle"><thead><tr><th>Plan</th><th>Clientes activos</th><th>Ingresos del mes</th></tr></thead><tbody><?php foreach ($stats['plans'] as $plan): ?><tr><td><?= h($plan->name) ?></td><td><?= (int)($stats['plan_clients'][$plan->slug] ?? 0) ?></td><td>$<?= number_format((int)($stats['payments']['by_plan'][$plan->slug] ?? 0), 0, ',', '.') ?></td></tr><?php endforeach; ?></tbody></table></div></div></section></div>
  <div class="col-lg-5"><section class="card admin-card h-100"><div class="card-body"><h2 class="h5">Estados a vigilar</h2><ul class="list-group list-group-flush"><li class="list-group-item d-flex justify-content-between">En gracia <strong><?= (int)($stats['subscriptions']['grace_period'] ?? 0) ?></strong></li><li class="list-group-item d-flex justify-content-between">Vencidas <strong><?= (int)($stats['subscriptions']['expired'] ?? 0) ?></strong></li><li class="list-group-item d-flex justify-content-between">Sitios pausados <strong><?= (int)($stats['sites']['paused'] ?? 0) ?></strong></li><li class="list-group-item d-flex justify-content-between">Pausados por vencimiento <strong><?= (int)$stats['sites']['paused_expired'] ?></strong></li><li class="list-group-item d-flex justify-content-between">Pagos rechazados <strong><?= (int)($stats['payments']['rejected'] ?? 0) ?></strong></li></ul></div></section></div>
  <div class="col-lg-6"><section class="card admin-card"><div class="card-body"><h2 class="h5">Últimos pagos</h2><?php foreach ($stats['recent_payments'] as $payment): ?><div class="border-bottom py-2 d-flex justify-content-between gap-2"><span><a href="/admin/payments/<?= (int)$payment->id ?>"><?= h($payment->internal_reference) ?></a><small class="d-block text-muted"><?= h($payment->user->email ?? '') ?></small></span><span class="text-end"><strong>$<?= number_format((int)$payment->amount, 0, ',', '.') ?></strong><small class="d-block text-muted"><?= h($payment->status) ?></small></span></div><?php endforeach; ?></div></section></div>
  <div class="col-lg-6"><section class="card admin-card"><div class="card-body"><h2 class="h5">Eventos recientes</h2><?php foreach ($stats['critical_audits'] as $audit): ?><div class="border-bottom py-2"><strong><?= h($audit->action) ?></strong><small class="d-block text-muted"><?= h($audit->created) ?> · <?= h($audit->user->email ?? 'sistema') ?></small></div><?php endforeach; ?></div></section></div>
</div>
