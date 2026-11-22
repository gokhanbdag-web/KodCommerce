<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= e($_SESSION['csrf']) ?>">
<title><?= e(t('KodCommerce Administration')) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="/assets/js/admin.js"></script>
</head>
<body>
<header class="navbar bg-body-tertiary border-bottom sticky-top">
 <div class="container-fluid">
  <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminNav" aria-controls="adminNav" aria-label="<?= e(t('Open navigation')) ?>">☰</button>
  <a class="navbar-brand" href="/admin/dashboard/overview">KodCommerce</a>
  <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#accountDrawer" aria-controls="accountDrawer"><?= e(t('Account')) ?></button>
 </div>
</header>
<div class="offcanvas offcanvas-start" tabindex="-1" id="adminNav" aria-labelledby="navTitle">
 <div class="offcanvas-header"><h2 class="offcanvas-title h5" id="navTitle"><?= e(t('Navigation')) ?></h2><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= e(t('Close')) ?>"></button></div>
 <div class="offcanvas-body">
  <div class="accordion" id="navGroups">
  <?php foreach (Navigation::groups() as $name => $labels): $key = Navigation::slug($name); ?>
   <div class="accordion-item">
    <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#nav-<?= e($key) ?>" aria-expanded="false"><?= e(t($name)) ?></button></h3>
    <div class="accordion-collapse collapse" id="nav-<?= e($key) ?>" data-bs-parent="#navGroups">
     <div class="accordion-body p-2">
      <?php foreach ($labels as $item): $route = $key . '/' . Navigation::slug($item); ?>
       <a class="d-block p-2 rounded text-decoration-none" data-admin-link href="/admin/<?= e($route) ?>"><?= e(t($item)) ?></a>
      <?php endforeach; ?>
     </div>
    </div>
   </div>
  <?php endforeach; ?>
  </div>
 </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="accountDrawer" aria-labelledby="accountTitle">
 <div class="offcanvas-header"><h2 class="offcanvas-title h5" id="accountTitle"><?= e(t('Account')) ?></h2><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= e(t('Close')) ?>"></button></div>
 <div class="offcanvas-body">
  <form id="loginForm" <?= Auth::user() ? 'hidden' : '' ?>>
   <div class="mb-3"><label class="form-label" for="email"><?= e(t('Email')) ?></label><input class="form-control" type="email" name="email" id="email" required autocomplete="username"></div>
   <div class="mb-3"><label class="form-label" for="password"><?= e(t('Password')) ?></label><input class="form-control" type="password" name="password" id="password" required autocomplete="current-password"></div>
   <p id="loginError" role="alert" class="text-danger" aria-live="polite"></p>
   <button class="btn btn-primary" type="submit"><?= e(t('Sign in')) ?></button>
  </form>
  <div id="accountLinks" <?= Auth::user() ? '' : 'hidden' ?>>
   <p class="small text-secondary" id="accountRole"><?= e(t('Administrator')) ?></p>
   <a data-admin-link class="d-block mb-3" href="/admin/dashboard/overview"><?= e(t('Dashboard')) ?></a>
   <a data-admin-link class="d-block mb-3" href="/admin/user-management/my-profile"><?= e(t('My Profile')) ?></a>
   <button class="btn btn-outline-danger" id="logoutButton" type="button"><?= e(t('Sign out')) ?></button>
  </div>
 </div>
</div>
<main id="adminContent" tabindex="-1">
 <?php if (Auth::allowed($selected)) { require __DIR__ . '/placeholder.php'; } else { ?>
 <section class="container py-5"><h1 class="h3"><?= e(t('Welcome to KodCommerce')) ?></h1><p><?= e(t('Sign in to access the administration panel.')) ?></p></section>
 <?php } ?>
</main>
</body>
</html>
