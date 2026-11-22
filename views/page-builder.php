<section class="container-fluid py-4" id="pageBuilder" aria-labelledby="builderTitle">
 <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
  <div><h1 id="builderTitle" class="h3 mb-1"><?= e(t('Page Builder')) ?></h1><p class="text-secondary mb-0"><?= e(t('Build your homepage with rows, columns and widgets.')) ?></p></div>
  <div class="d-flex flex-wrap gap-2"><button type="button" class="btn btn-outline-secondary" data-builder-action="preview"><?= e(t('Preview')) ?></button><button type="button" class="btn btn-primary" data-builder-action="save"><?= e(t('Publish homepage')) ?></button></div>
 </div>
 <div class="row g-3">
  <div class="col-lg-9">
   <div class="card"><div class="card-body">
    <label class="form-label" for="builderPageTitle"><?= e(t('Page title')) ?></label><input id="builderPageTitle" class="form-control mb-3" maxlength="160" value="<?= e(t('Homepage')) ?>">
    <div id="builderRows" class="vstack gap-3"></div>
    <button type="button" class="btn btn-outline-primary mt-3" data-builder-action="add-row"><?= e(t('+ Add row')) ?></button>
   </div></div>
  </div>
  <div class="col-lg-3"><div class="card"><div class="card-body">
   <h2 class="h6"><?= e(t('Widget library')) ?></h2><p class="small text-secondary"><?= e(t('Add a widget to any column. Drag cards to reorder within a column, or use the move buttons.')) ?></p>
   <p class="small mb-1"><?= e(t('Available: Heading, Text, Button')) ?></p>
   <p class="small mb-0"><?= e(t('Choose row width and column span (1–12) for Bootstrap layouts.')) ?></p>
  </div></div></div>
 </div>
 <p id="builderStatus" class="mt-3" role="status" aria-live="polite"></p>
 <div id="builderPreview" class="border rounded p-3 mt-3" hidden></div>
</section>
