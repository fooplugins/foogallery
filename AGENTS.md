# Agent Instructions for WordPress Plugin Development

When working on this repository, please follow these guidelines:

## Guidelines

Before starting any WordPress plugin development work:

1. **Read the README.md** - Contains info about the plugin.
2. **Read WORDPRESS.md** - Contains comprehensive WordPress.org compliance requirements including:
   - Security best practices (input sanitization, output escaping, nonces)
   - Proper namespacing and unique prefixes (minimum 4 characters)
   - WordPress coding standards
   - Common review failures to avoid
   - Proper enqueueing of scripts and styles
   - Translation and internationalization requirements

## Key Development Principles

### Security First
- ALWAYS sanitize all inputs
- ALWAYS escape all outputs at the last possible moment
- ALWAYS use nonces for forms and AJAX requests
- ALWAYS check user capabilities

### WordPress Standards
- Use WordPress functions instead of PHP natives (e.g., `wp_remote_get()` not `curl`)
- Enqueue scripts/styles properly - never include directly
- Use WordPress bundled libraries (jQuery, etc.) - don't download your own
- Follow WordPress naming conventions and coding standards

### Unique Prefixes
- All global functions, constants, and classes must have unique prefixes of at least 4 characters
- Example: Use `MYAWESOMEPLUGIN_` not `MY_` for constants
- Namespaces should also be unique: `MyAwesomePlugin\` not `MyPlugin\`

### No Trademark Violations
- Never use "WordPress" in plugin names (use "WP" instead)
- Avoid using trademarked names unless creating official integrations

### Clean Code
- Use Composer autoloading with classmap
- Organize code into logical directories (admin/, public/, includes/)
- Always include proper PHPDoc comments
- Add translator comments for translatable strings with placeholders

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

Remember: The goal is to create secure, efficient plugins that will pass WordPress.org review on first submission.