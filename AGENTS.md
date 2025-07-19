# AGENTS.md

## Build Commands

- `npm run build` - Build Gutenberg blocks
- `npm run develop` - Development build with watch
- `npm run gulp` - Run gulp tasks (translation, zip)
- `npm run package` - Full build + gulp
- `composer install` - Install PHP dependencies

## Lint Commands

- `npx wp-scripts lint-js` - JavaScript linting
- `npx wp-scripts lint-style` - CSS linting
- `vendor/bin/phpcs --standard=WordPress .` - PHP linting

## Code Style

- WordPress coding standards for PHP
- WordPress JavaScript standards
- Use `foogallery_` prefix for functions
- Follow WordPress i18n: `__()`, `_e()`, etc.
- Class names: `FooGallery_` prefix
- File naming: lowercase with hyphens
- Error handling: use `WP_Error` objects
