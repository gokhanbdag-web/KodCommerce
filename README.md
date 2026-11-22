# KodCommerce

Independent, modular PHP commerce and management platform.

## Architecture principles

- English (`en_US`) is the source language; gettext PO/MO catalogs are used for localization.
- Bootstrap 5 is the UI foundation; avoid duplicate styling and unnecessary CSS.
- Server-authorized, AJAX-powered administration with History API navigation.
- Modular controllers, services, repositories, templates and JavaScript modules.
- Stage 1: dashboard, all navigation destinations as placeholders, login drawer, localization infrastructure.

See `docs/architecture.md` and `docs/requirements.md` as the implementation progresses.
