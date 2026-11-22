# KodCommerce architecture decisions

- Independent greenfield PHP application, separate from KodVall/KDALL.
- Bootstrap 5 grid, components and offcanvas; custom CSS only when essential.
- English is the source language. GNU gettext `.po` and compiled `.mo` catalogs supply translations.
- UI strings are not hard-coded in Turkish; PHP uses `t()`; JS localization is a follow-up requirement.
- AJAX/Fetch admin navigation with History API; direct URL loads must also work.
- A single centralized menu registry generates all placeholder routes.
- Login is a same-page drawer. The server owns sessions, CSRF and permissions.
- Each future feature must update translations and tests in the same change.
- Admin and storefront share media records; uploads are grouped by year/month.
- Bootstrap colors are configurable without duplicate header/footer CSS.
- Page builder rows support container, full-width and full-width background with contained content.
- CMS widget styles are scoped, with absent settings producing no unnecessary CSS.
- Frontend page rendering uses a shared page template, not one PHP file per page.
- Future modules must separate controller, service, repository, validation and view responsibilities.

## Stage 1 limits

The initial shell intentionally contains placeholders rather than fabricated functionality.
Only a bootstrap administrator can sign in. Staff/customer accounts, full RBAC,
page builder, storefront and database persistence are **not yet implemented**.
