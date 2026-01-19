# TEST-settings-migrations

## Scope
Settings defaults, migrations, and backward compatibility.

## Required Tests
- Default settings are applied on fresh install.
- Upgrade routines preserve existing settings.
- Invalid settings values fall back to defaults.

## Test Types
- `WP_UnitTestCase`
- Group: `settings`
