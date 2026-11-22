// Keep authentication and navigation functional even when the editor module fails to load.
import('./page-builder.js').catch(error => console.error('Page editor module unavailable', error));
const content = document.querySelector('#adminContent');
const loginForm = document.querySelector('#loginForm');
const accountLinks = document.querySelector('#accountLinks');
const errorBox = document.querySelector('#loginError');
const csrfMeta = document.querySelector('meta[name="csrf-token"]');

function csrf() { return csrfMeta.content; }
function updateCsrf(value) { csrfMeta.content = value; }
function closeDrawer(element) {
  const drawer = element.closest('.offcanvas');
  if (drawer) bootstrap.Offcanvas.getInstance(drawer)?.hide();
}

async function navigate(url, push = true) {
  const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
  if (response.status === 401) {
    bootstrap.Offcanvas.getOrCreateInstance(document.querySelector('#accountDrawer')).show();
    return;
  }
  if (!response.ok) throw new Error('Navigation failed');
  const data = await response.json();
  content.innerHTML = data.html;
  document.title = data.title + ' — KodCommerce';
  if (push) history.pushState({}, '', url);
  content.focus();
}

document.addEventListener('click', async (event) => {
  const link = event.target.closest('a[data-admin-link]');
  if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
  event.preventDefault();
  try { await navigate(link.href); closeDrawer(link); }
  catch (error) { console.error(error); window.location.assign(link.href); }
});
window.addEventListener('popstate', () => navigate(location.pathname + location.search, false).catch(console.error));

loginForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  errorBox.textContent = '';
  const body = new FormData(loginForm);
  body.set('csrf', csrf());
  try {
    const response = await fetch('/api/login', { method: 'POST', body, credentials: 'same-origin' });
    const result = await response.json();
    if (!response.ok) { errorBox.textContent = result.error; return; }
    updateCsrf(result.csrf);
    loginForm.hidden = true;
    accountLinks.hidden = false;
    loginForm.reset();
    const roleLabel = document.querySelector('#accountRole');
    if (roleLabel) roleLabel.textContent = result.role || '';
    // Remain on the current page; account drawer is updated in place.
  } catch (error) { errorBox.textContent = 'Unable to sign in'; }
});
document.querySelector('#logoutButton').addEventListener('click', async () => {
  const body = new FormData();
  body.set('csrf', csrf());
  const response = await fetch('/api/logout', { method: 'POST', body, credentials: 'same-origin' });
  if (!response.ok) return;
  const result = await response.json();
  updateCsrf(result.csrf);
  loginForm.hidden = false;
  accountLinks.hidden = true;
  content.replaceChildren();
  history.replaceState({}, '', '/');
});
