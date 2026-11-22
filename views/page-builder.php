<?php
$builderTranslations = [];
foreach (['Widget','Type','Content','Column','Span','+ Widget','Row','Width','Remove row','+ Column','Preview (unsaved)','Homepage published. View it at /','Page saved','Save failed','Unable to save homepage','Unable to load page','Unable to load saved layout','heading','text','button','contained','fluid','full','Drag to another column','Drop widgets here','Duplicate widget','Move column left','Move column right','Copy widget','Copy','Paste widget','Paste','Widget copied','Move to column','Move','Undo'] as $message) {
    $builderTranslations[$message] = t($message);
}
?>
<section class="container-fluid py-2" id="pageBuilder" data-page-id="<?= (int)($builderPage['id'] ?? 0) ?>" data-layout="<?= e($builderPage['layout_json'] ?? '[]') ?>" data-translations="<?= e(json_encode($builderTranslations, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) ?>" aria-labelledby="builderTitle">
 <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
  <div><h1 id="builderTitle" class="h3 mb-1"><?= e($builderPage ? t('Edit Page') : t('Add New Page')) ?></h1><p class="text-secondary mb-0"><?= e(t('Use the visual editor to arrange this page.')) ?></p></div>
  <div class="d-flex flex-wrap gap-2"><button type="button" class="btn btn-outline-secondary" id="builderUndo" data-builder-action="undo" disabled><?= e(t('Undo')) ?></button><button type="button" class="btn btn-outline-secondary" data-builder-action="preview"><?= e(t('Preview')) ?></button><button type="button" class="btn btn-primary" data-builder-action="save"><?= e(t('Save Page')) ?></button></div>
 </div>
 <div class="d-flex flex-wrap align-items-center gap-2 mb-3"><span id="builderDirty" class="small text-warning" hidden><?= e(t('Unsaved changes')) ?></span><a id="builderViewPage" class="btn btn-sm btn-outline-primary" href="/pages/<?= e($builderPage['slug'] ?? '') ?>" <?= ($builderPage['status'] ?? '') === 'published' ? '' : 'hidden' ?> target="_blank" rel="noopener"><?= e(t('View published page')) ?></a></div>
 <div class="row g-3">
  <div class="col-lg-8">
   <div class="card"><div class="card-body">
    <label class="form-label" for="builderPageTitle"><?= e(t('Page title')) ?></label><input id="builderPageTitle" class="form-control mb-3" maxlength="160" value="<?= e($builderPage['title'] ?? '') ?>">
    <div class="row g-3 mb-3">
 <div class="col-md-8"><label class="form-label" for="builderSlug"><?= e(t('Slug')) ?></label><input id="builderSlug" class="form-control" required maxlength="190" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= e($builderPage['slug'] ?? '') ?>"></div>
 <div class="col-md-4"><label class="form-label" for="builderPublishStatus"><?= e(t('Status')) ?></label><select id="builderPublishStatus" class="form-select"><option value="draft" <?= ($builderPage['status'] ?? '') === 'draft' ? 'selected' : '' ?>><?= e(t('Draft')) ?></option><option value="published" <?= ($builderPage['status'] ?? '') === 'published' ? 'selected' : '' ?>><?= e(t('Published')) ?></option></select></div>
 </div><div id="builderRows" class="vstack gap-3"></div>
    <button type="button" class="btn btn-outline-primary mt-3" data-builder-action="add-row"><?= e(t('+ Add row')) ?></button>
   </div></div>
  </div>
  <div class="col-lg-4"><div class="card mb-3"><div class="card-body"><h2 class="h6"><?= e(t('Publish settings')) ?></h2><p class="small text-secondary mb-2"><?= e(t('Draft pages are private. Published pages are visible to everyone.')) ?></p><p class="small mb-0"><?= e(t('Save as draft while editing, then select Published and save to go live.')) ?></p></div></div><div class="card"><div class="card-body">
   <h2 class="h6"><?= e(t('Widget library')) ?></h2><p class="small text-secondary"><?= e(t('On mobile, choose a target column and tap Move. Copy and paste widgets, or use Undo to restore a previous layout.')) ?></p>
   <p class="small mb-1"><?= e(t('Available: Heading, Text, Button')) ?></p>
   <p class="small mb-0"><?= e(t('Choose row width and column span (1–12) for Bootstrap layouts.')) ?></p>
  </div></div></div>
 </div>
 <p id="builderStatus" class="mt-3" role="status" aria-live="polite"></p>
 <div id="builderPreview" class="border rounded p-3 mt-3" hidden></div>
</section>
