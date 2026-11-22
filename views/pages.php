<?php
use KodCommerce\Support\Pages;
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$editing = $editId ? Pages::find($editId) : null;
$editorOpen = isset($_GET['new']) || $editId !== null;
?>
<section class="container-fluid py-4">
<?php if (!$editorOpen): ?>
 <div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3"><?= e(t('Pages')) ?></h1>
  <a data-admin-link class="btn btn-primary" href="/admin/content-management/pages?new=1"><?= e(t('Add New Page')) ?></a>
 </div>
 <div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
 <thead><tr><th><?= e(t('Title')) ?></th><th><?= e(t('Slug')) ?></th><th><?= e(t('Status')) ?></th><th><?= e(t('Last updated')) ?></th><th><?= e(t('Actions')) ?></th></tr></thead>
 <tbody>
 <?php foreach (Pages::all() as $item): ?>
 <tr><td><?= e($item['title']) ?></td><td><?= e($item['slug']) ?></td><td><?= e(t(ucfirst($item['status']))) ?></td>
 <td><?= e($item['updated_at']) ?></td><td><a data-admin-link href="/admin/content-management/pages?edit=<?= (int)$item['id'] ?>"><?= e(t('Edit')) ?></a><?php if ($item['status'] === 'published'): ?> · <a href="/pages/<?= e($item['slug']) ?>" target="_blank" rel="noopener"><?= e(t('View')) ?></a><?php endif; ?></td></tr>
 <?php endforeach; ?>
 </tbody></table></div></div>
<?php elseif ($editId !== null && !$editing): ?>
 <p role="alert"><?= e(t('Page not found')) ?></p><a data-admin-link href="/admin/content-management/pages"><?= e(t('Back to Pages')) ?></a>
<?php else: ?>
 <a data-admin-link class="d-inline-block mb-3" href="/admin/content-management/pages"><?= e(t('Back to Pages')) ?></a>
 <?php
 $builderPage = $editing;
 require __DIR__ . '/page-builder.php';
 ?>
<?php endif; ?>
</section>
