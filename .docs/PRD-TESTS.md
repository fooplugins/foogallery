# PRD-TESTS

## Overview
This document defines the required unit/integration tests that should run before every FooGallery release. Each test area is documented in a separate file under `.docs/tests`.

## Required Test Files
- `.docs/tests/TEST-core-galleries.md` — core gallery CRUD and rendering checks.
- `.docs/tests/TEST-rest-api.md` — REST API permissions and multisite scope.
- `.docs/tests/TEST-security-multisite.md` — cross-site access protections.
- `.docs/tests/TEST-templates-rendering.md` — template rendering and escaping.
- `.docs/tests/TEST-settings-migrations.md` — settings defaults and upgrades.
- `.docs/tests/TEST-integrations.md` — Elementor/WooCommerce/Freemius integrations.
- `.docs/tests/TEST-performance-cache.md` — caching and thumbnails.
- `.docs/tests/TEST-functions.md` — core helper functions and defaults.

## Execution Notes
- Single-site tests run via `npm run test:php`.
- Multisite tests should run using a `tests/phpunit/multisite.xml` config (to be added) and can be grouped under `multisite`.
- AJAX tests require `--group ajax` to be enabled; plan a CI job that includes this group.

## Release Gate
- All tests in the above documents must pass before release.
- Failures in `ajax`, `rest`, or `multisite` groups block release.
