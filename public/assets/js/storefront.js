const loginForm = document.querySelector('#loginForm');
const accountLinks = document.querySelector('#accountLinks');
const errorBox = document.querySelector('#loginError');
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
loginForm.addEventListener('submit', async event => {
  event.preventDefault();
  errorBox.textContent = '';
  const button = loginForm.querySelector('button[type="submit"]');
  button.disabled = true;
  const body = new FormData(loginForm);
  body.set('csrf', csrfMeta.content);
  try {
    const response = await fetch('/api/login', {method:'POST', body, credentials:'same-origin'});
    const data = await response.json();
    if (!response.ok) { errorBox.textContent = data.error || 'Sign in failed'; return; }
    csrfMeta.content = data.csrf;
    loginForm.hidden = true;
    accountLinks.hidden = false;
    loginForm.reset();
    bootstrap.Offcanvas.getInstance(document.querySelector('#accountDrawer'))?.hide();
  } catch (_) { errorBox.textContent = 'Unable to sign in'; }
  finally { button.disabled = false; }
});
document.querySelector('#logoutButton').addEventListener('click', async () => {
  const body = new FormData();
  body.set('csrf', csrfMeta.content);
  try {
    const response = await fetch('/api/logout', {method:'POST', body, credentials:'same-origin'});
    if (!response.ok) return;
    const data = await response.json();
    csrfMeta.content = data.csrf;
    loginForm.hidden = false;
    accountLinks.hidden = true;
  } catch (_) { /* Preserve current UI on network failure. */ }
});

if (new URLSearchParams(location.search).get('login') === '1' && !loginForm.hidden) {
  bootstrap.Offcanvas.getOrCreateInstance(document.querySelector('#accountDrawer')).show();
}
