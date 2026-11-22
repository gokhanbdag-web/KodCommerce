# Stage 1 acceptance checklist

- [x] Bootstrap responsive navigation with all planned placeholder destinations
- [x] Initial dashboard placeholder
- [x] AJAX navigation and browser back/forward support
- [x] Login drawer and session-backed administrator authentication
- [x] Gettext bootstrapping and English message catalog
- [ ] Compile and validate Turkish MO catalog in CI
- [ ] Add translation bridge for dynamic JavaScript messages
- [x] Database-backed initial administrator (MySQL for new installs; legacy SQLite preserved); granular roles and permissions remain pending
- [ ] Module-level functionality and editor lifecycle
- [ ] End-to-end browser and accessibility tests

## Setup

PHP 8.2+ with sessions, gettext extension recommended. Serve `public/`
as the document root with a router fallback to `public/index.php`.
Open `/install` on the first run to create the administrator; then use the existing AJAX account drawer to sign in. New installations require PDO MySQL, an existing MySQL database with CREATE/INSERT permissions, and a writable `storage/` directory. The setup screen tests the connection before installation. Existing SQLite installations remain accessible without automatic migration. Configure the web server document root to `public/` so the database cannot be downloaded.
Do not commit passwords or local environment files. The MySQL configuration is stored in `storage/database.php` outside the public document root and is ignored by Git.
