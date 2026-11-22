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
<p>Configure your MySQL database and create the first administrator. Existing installations are preserved.</p>
<form id="setup">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<h2 class="h5 mt-4">MySQL connection</h2>
<div class="row g-3 mb-3">
<div class="col-sm-8"><label class="form-label" for="db_host">Database host</label><input class="form-control" id="db_host" name="db_host" value="localhost" required></div>
<div class="col-sm-4"><label class="form-label" for="db_port">Port</label><input class="form-control" id="db_port" name="db_port" type="number" min="1" max="65535" value="3306" required></div>
<div class="col-sm-6"><label class="form-label" for="db_name">Database name</label><input class="form-control" id="db_name" name="db_name" required></div>
<div class="col-sm-6"><label class="form-label" for="db_user">Database user</label><input class="form-control" id="db_user" name="db_user" required></div>
<div class="col-12"><label class="form-label" for="db_password">Database password</label><input class="form-control" id="db_password" name="db_password" type="password" autocomplete="off"></div>
</div>
<button class="btn btn-outline-secondary mb-3" id="testConnection" type="button">Test connection</button>
<h2 class="h5 mt-3">Administrator</h2>
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
document.querySelector('#testConnection').addEventListener('click', async event => {
  const button = event.currentTarget, message = document.querySelector('#message');
  const data = new FormData(document.querySelector('#setup'));
  data.set('action', 'test');
  button.disabled = true; message.textContent = '';
  try {
    const response = await fetch('/install', {method:'POST', body:data, credentials:'same-origin'});
    const result = await response.json();
    message.className = response.ok ? 'text-success' : 'text-danger';
    message.textContent = response.ok ? 'MySQL connection successful' : (result.error || 'Connection failed');
  } catch (_) { message.className = 'text-danger'; message.textContent = 'Connection failed'; }
  finally { button.disabled = false; }
});
</script>
</body></html>
