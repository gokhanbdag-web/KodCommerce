<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>KodCommerce Setup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<main class="container py-5" style="max-width:540px">
<section class="card shadow-sm"><div class="card-body p-4">
<h1 class="h3 mb-3">KodCommerce — Initial setup</h1>
<p>Create your first administrator. This setup is available only before an administrator exists.</p>
<form id="setup">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<div class="mb-3"><label class="form-label" for="email">Administrator email</label><input class="form-control" id="email" name="email" type="email" required autocomplete="username"></div>
<div class="mb-3"><label class="form-label" for="password">Administrator password (12 characters minimum)</label><input class="form-control" id="password" name="password" type="password" minlength="12" required autocomplete="new-password"></div>
<p id="message" role="alert" aria-live="polite" class="text-danger"></p>
<button class="btn btn-primary" type="submit">Install KodCommerce</button>
</form>
</div></section>
</main>
<script>
document.querySelector('#setup').addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget, button = form.querySelector('button'), message = document.querySelector('#message');
  button.disabled = true; message.textContent = '';
  try {
    const response = await fetch('/install', {method:'POST', body:new FormData(form), credentials:'same-origin'});
    const result = await response.json();
    if (!response.ok) { message.textContent = result.error || 'Installation failed'; return; }
    location.assign('/admin/dashboard/overview');
  } catch (_) { message.textContent = 'Unable to complete installation'; }
  finally { button.disabled = false; }
});
</script>
</body></html>
