<?php
$query = $this->getRequest()->getQueryParams();
$urlFor = static function (int $page) use ($query): string {
    $query['page'] = $page;
    return '?' . http_build_query($query);
};
?>
<?php if ($pagination['pages'] > 1): ?>
  <nav class="mt-3" aria-label="Paginación"><ul class="pagination mb-0">
    <li class="page-item<?= $pagination['page'] <= 1 ? ' disabled' : '' ?>"><a class="page-link" href="<?= h($urlFor(max(1, $pagination['page'] - 1))) ?>">Anterior</a></li>
    <li class="page-item disabled"><span class="page-link">Página <?= (int)$pagination['page'] ?> de <?= (int)$pagination['pages'] ?></span></li>
    <li class="page-item<?= $pagination['page'] >= $pagination['pages'] ? ' disabled' : '' ?>"><a class="page-link" href="<?= h($urlFor(min($pagination['pages'], $pagination['page'] + 1))) ?>">Siguiente</a></li>
  </ul></nav>
<?php endif; ?>
