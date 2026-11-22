<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= e($_SESSION['csrf']) ?>">
<title><?= e($publicPage['title'] ?? 'KodCommerce') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="/assets/js/storefront.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
<header class="border-bottom bg-white sticky-top">
 <div class="container d-flex align-items-center gap-3 py-3">
  <a href="/" class="navbar-brand fw-bold me-auto">KodCommerce</a>
  <nav aria-label="Main navigation"><a href="/" class="text-decoration-none">Home</a></nav>
  <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#accountDrawer" aria-controls="accountDrawer" aria-label="Open account">Account</button>
 </div>
</header>
<main class="flex-grow-1">
<?php $homePage = $publicPage ?? \KodCommerce\Support\Pages::home(); ?>
<?php if ($homePage): ?>
<?= \KodCommerce\Support\Pages::render($homePage) ?>
<?php else: ?>
<section class="container py-5"><h1 class="h3">KodCommerce</h1><p class="text-secondary">Homepage content can be published from the administration panel.</p></section>
<?php endif; ?>
</main>
<footer class="border-top py-4"><div class="container d-flex flex-wrap justify-content-between gap-2">
 <span>© <?= date('Y') ?> KodCommerce</span><span>Powered by KodCommerce</span>
</div></footer>
<div class="offcanvas offcanvas-end" tabindex="-1" id="accountDrawer" aria-labelledby="accountTitle">
 <div class="offcanvas-header"><h2 class="offcanvas-title h5" id="accountTitle">Account</h2><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
 <div class="offcanvas-body">
  <form id="loginForm" <?= \KodCommerce\Support\Auth::user() ? 'hidden' : '' ?>>
   <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" autocomplete="username" required></div>
   <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div>
   <p id="loginError" class="text-danger" role="alert" aria-live="polite"></p>
   <button class="btn btn-primary" type="submit">Sign in</button>
  </form>
  <div id="accountLinks" <?= \KodCommerce\Support\Auth::user() ? '' : 'hidden' ?>>
   <p>Signed in as administrator</p>
   <a class="d-block mb-3" href="/admin/dashboard/overview">Open administration</a>
   <button class="btn btn-outline-danger" id="logoutButton" type="button">Sign out</button>
  </div>
 </div>
</div>
</body></html>
