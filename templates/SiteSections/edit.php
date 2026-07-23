<?php $this->assign('title', 'Editar secciones | CatOps'); ?>

<section class="page-head">
  <div>
    <h1>Secciones de <?= h($site->name) ?></h1>
    <p>Controla los textos visibles de la landing sin entrar a código.</p>
  </div>
  <a class="button secondary" href="/sitios/editar/<?= (int)$site->id ?>">Volver</a>
</section>

<?= $this->Form->create(null) ?>
<section class="grid">
  <?php foreach ($site->site_sections as $index => $section): ?>
    <article class="card">
      <h2><?= h(ucfirst($section->section_key)) ?></h2>
      <?= $this->Form->hidden("sections.$index.id", ['value' => $section->id]) ?>
      <?= $this->Form->control("sections.$index.title", ['label' => 'Título', 'value' => $section->title]) ?>
      <?= $this->Form->control("sections.$index.subtitle", ['label' => 'Subtítulo', 'value' => $section->subtitle]) ?>
      <?= $this->Form->control("sections.$index.content", [
          'label' => 'Contenido',
          'type' => 'textarea',
          'value' => $section->content,
      ]) ?>
      <?= $this->Form->control("sections.$index.sort_order", [
          'label' => 'Orden',
          'type' => 'number',
          'value' => $section->sort_order,
      ]) ?>
      <?= $this->Form->control("sections.$index.visible", [
          'label' => 'Mostrar sección',
          'type' => 'checkbox',
          'checked' => $section->visible,
      ]) ?>
    </article>
  <?php endforeach; ?>
</section>

<?= $this->Form->button('Guardar secciones') ?>
<?= $this->Form->end() ?>
