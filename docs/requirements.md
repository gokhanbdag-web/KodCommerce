# Stage 1 acceptance checklist

- [x] Bootstrap responsive navigation with all planned placeholder destinations
- [x] Initial dashboard placeholder
- [x] AJAX navigation and browser back/forward support
- [x] Login drawer and session-backed administrator authentication
- [x] Gettext bootstrapping and English message catalog
- [ ] Compile and validate Turkish MO catalog in CI
- [ ] Add translation bridge for dynamic JavaScript messages
- [ ] Database-backed users, roles and permissions
- [ ] Module-level functionality and editor lifecycle
- [ ] End-to-end browser and accessibility tests

## Setup

PHP 8.2+ with sessions, gettext extension recommended. Serve `public/`
as the document root with a router fallback to `public/index.php`.
Set `KODCOMMERCE_ADMIN_EMAIL` and
`KODCOMMERCE_ADMIN_PASSWORD_HASH` (from PHP `password_hash`).
Do not commit passwords or local environment files.
