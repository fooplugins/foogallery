# FooGallery Premium E2E Testing Infrastructure

End-to-end testing infrastructure for FooGallery Premium using Docker, WordPress, and Playwright.

## Overview

This infrastructure provides:
- Reproducible WordPress + MySQL environment via Docker
- **Automatic lifecycle management** - fresh environment for each test run
- Automated WordPress setup with WP-CLI
- FooGallery Premium pre-activated (with optional Freemius bypass - see Developer Setup)
- Playwright E2E test harness with interactive HTML dashboard
- Screenshots at key test steps, video recordings, and trace debugging

## Prerequisites

- **Docker Desktop** (with Docker Compose v2)
- **Node.js** >= 18.0.0 (LTS recommended)
- **npm** (comes with Node.js)

## Quick Start

```bash
# 1. Navigate to the e2e directory
cd tests/e2e

# 2. Copy environment file
cp .env.example .env

# 3. Install dependencies and Playwright browsers
npm install && npx playwright install chromium

# 4. Run the tests (handles everything automatically!)
npm test
```

That's it! The `npm test` command automatically:
1. Cleans previous test artifacts
2. Starts fresh Docker containers
3. Sets up WordPress with FooGallery
4. Runs all tests
5. Stops containers and cleans up

After tests complete, view the report:
```bash
npm run test:report
```

## Developer Setup

### Freemius License Bypass (Required for Premium Feature Tests)

The E2E tests require FooGallery Premium features to be unlocked. For security reasons, the license bypass code is **not included in the repository**.

#### Setup Steps

1. **Create the secrets directory** (if not exists):
   ```bash
   mkdir -p tests/e2e/secrets
   ```

2. **Get the freemius-e2e-helper code**:
   - Contact your team lead or check internal documentation
   - The code should be saved to: `tests/e2e/secrets/freemius-e2e-helper.php`

3. **Verify setup**:
   ```bash
   ls tests/e2e/secrets/freemius-e2e-helper.php
   ```

#### What Happens Without It?

- Tests will run but premium features will be locked
- The setup script will display a warning message
- You'll need a valid FooGallery Premium license to test premium features

#### Security Note

- **NEVER** commit `freemius-e2e-helper.php` to version control
- The `secrets/` directory is gitignored
- Only the `.example` template file is tracked

## Test Lifecycle Management

The test infrastructure uses automatic lifecycle management to ensure reproducible, conflict-free test runs.

### How It Works

```
┌─────────────────────────────────────────────────────────────┐
│                    Test Lifecycle                            │
├─────────────────────────────────────────────────────────────┤
│  PRE-RUN (automatic)                                         │
│  ├─ Clean previous test artifacts (screenshots, reports)    │
│  ├─ Stop any existing containers                            │
│  ├─ Remove Docker volumes (fresh database)                  │
│  └─ Start containers + run WordPress setup                  │
├─────────────────────────────────────────────────────────────┤
│  TEST EXECUTION                                              │
│  ├─ Global setup (login, verify FooGallery)                 │
│  ├─ Run test specs with screenshots                         │
│  └─ Global teardown                                          │
├─────────────────────────────────────────────────────────────┤
│  POST-RUN (automatic)                                        │
│  ├─ Archive report (optional)                               │
│  └─ Stop containers + remove volumes                        │
└─────────────────────────────────────────────────────────────┘
```

### Usage Patterns

| Command | Use Case |
|---------|----------|
| `npm test` | **Standard run** - full lifecycle, fresh environment |
| `npm run test:keep-env` | **Debugging** - keeps containers running after tests |
| `npm run test:quick` | **Re-run** - uses existing environment (skip lifecycle) |
| `npm run test:headed` | **Visual** - see browser, environment preserved |

### Configuration

Control lifecycle behavior via `.env`:

```bash
# Stop containers after tests (default: true)
CLEANUP_AFTER_TESTS=true

# Archive reports with timestamp (default: false)
ARCHIVE_REPORTS=false
```

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Host Machine                              │
│  ┌─────────────────┐    ┌─────────────────────────────────┐ │
│  │   Playwright    │    │      Docker Network             │ │
│  │   Test Runner   │    │  ┌─────────┐  ┌──────────┐      │ │
│  │                 │◄───┼──│WordPress│◄─│  MySQL   │      │ │
│  │  (TypeScript)   │    │  │  :8080  │  │   :3306  │      │ │
│  └─────────────────┘    │  └─────────┘  └──────────┘      │ │
│                         └─────────────────────────────────┘ │
│                                    ▲                         │
│                                    │                         │
│  ┌─────────────────────────────────┴─────────────────────┐  │
│  │  foogallery-premium/ (mounted read-only)              │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Services

| Service | Image | Port | Purpose |
|---------|-------|------|---------|
| `wordpress` | Custom (WP 6.7 + PHP 8.2) | 8080 | WordPress with WP-CLI |
| `mysql` | MySQL 8.0 | 3306 (internal) | Database |

### Key Directories

| Path | Purpose |
|------|---------|
| `docker/` | Docker Compose and Dockerfile |
| `docker/scripts/` | WordPress setup scripts |
| `scripts/` | Test lifecycle scripts (pre-test, post-test) |
| `tests/` | Playwright configuration and tests |
| `tests/specs/` | Test specification files |
| `.auth/` | Stored authentication state (generated) |
| `playwright-report/` | HTML test reports (generated) |
| `test-results/` | Screenshots and test artifacts (generated) |

### Project Structure

```
tests/e2e/
├── docker/
│   ├── docker-compose.yml      # Docker services configuration
│   ├── Dockerfile.wordpress    # WordPress container build
│   └── scripts/
│       └── setup-wordpress.sh  # WordPress provisioning script
├── scripts/
│   ├── pre-test.sh             # Runs before tests (starts Docker)
│   └── post-test.sh            # Runs after tests (cleanup)
├── secrets/                    # GITIGNORED - local secrets
│   ├── .gitkeep                # Keeps directory in git
│   ├── freemius-e2e-helper.php.example  # Template (tracked)
│   └── freemius-e2e-helper.php     # Actual bypass (gitignored)
├── tests/
│   ├── playwright.config.ts    # Playwright configuration
│   ├── helpers/                # Test helper functions
│   └── specs/                  # Test specification files
├── .env.example                # Environment template
├── .env                        # Local environment (gitignored)
└── package.json                # NPM scripts and dependencies
```

### How FooGallery Plugin is Loaded

The FooGallery Premium plugin is mounted from the parent directory (since tests/e2e is inside the plugin) into Docker:

```yaml
# docker-compose.yml (from tests/e2e/docker/)
volumes:
  - ../../..:/var/www/html/wp-content/plugins/foogallery-premium:ro
```

**Path mapping:**
- Host: `foogallery-premium/` (parent of tests/e2e/)
- Container: `/var/www/html/wp-content/plugins/foogallery-premium`

The `:ro` flag makes it read-only to prevent tests from modifying the plugin source.

## NPM Scripts Reference

### Testing Commands

| Script | Description |
|--------|-------------|
| `npm test` | **Full lifecycle**: clean → fresh env → tests → cleanup |
| `npm run test:keep-env` | Full lifecycle but keeps containers running |
| `npm run test:quick` | Run tests on existing environment (skip lifecycle) |
| `npm run test:headed` | Run with visible browser (environment preserved) |
| `npm run test:debug` | Debug mode with Playwright inspector |
| `npm run test:ui` | Open Playwright UI mode |
| `npm run test:report` | View HTML report dashboard |
| `npm run test:trace` | Open trace viewer for debugging |

### Docker Management

| Script | Description |
|--------|-------------|
| `npm run docker:up` | Start containers in background |
| `npm run docker:down` | Stop containers |
| `npm run docker:clean` | Stop containers and remove volumes |
| `npm run docker:logs` | Follow container logs |
| `npm run docker:setup` | Run WordPress provisioning |
| `npm run docker:shell` | Open bash shell in WordPress container |
| `npm run docker:wp -- <command>` | Run WP-CLI commands |

### Cleanup Commands

| Script | Description |
|--------|-------------|
| `npm run clean` | Full cleanup (containers + artifacts) |
| `npm run clean:artifacts` | Clean only test artifacts |

## Environment Variables

All configuration is done via the `.env` file:

### WordPress & Docker

| Variable | Default | Description |
|----------|---------|-------------|
| `WORDPRESS_PORT` | `8080` | Host port for WordPress |
| `WP_BASE_URL` | `http://localhost:8080` | Base URL for tests |
| `MYSQL_ROOT_PASSWORD` | `rootpassword` | MySQL root password |
| `MYSQL_DATABASE` | `wordpress_test` | Database name |
| `MYSQL_USER` | `wordpress` | Database user |
| `MYSQL_PASSWORD` | `wordpress` | Database password |
| `WORDPRESS_ADMIN_USER` | `admin` | WP admin username |
| `WORDPRESS_ADMIN_PASSWORD` | `admin` | WP admin password |
| `WORDPRESS_ADMIN_EMAIL` | `admin@example.com` | WP admin email |

### Test Configuration

| Variable | Default | Description |
|----------|---------|-------------|
| `CI` | `false` | Set to `true` in CI environment |
| `CLEANUP_AFTER_TESTS` | `true` | Stop containers after tests |
| `ARCHIVE_REPORTS` | `false` | Archive reports with timestamp |

## Viewing Test Results

### HTML Dashboard

After running tests, view the interactive dashboard:

```bash
npm run test:report
```

The dashboard shows:
- Test breakdown by pass/fail/skip status
- Screenshots at key test steps (embedded)
- Video recordings of test execution
- Filterable and searchable test list

### Trace Viewer

For detailed debugging of failures:

```bash
npm run test:trace
```

The trace viewer shows:
- Step-by-step test execution
- DOM snapshots at each action
- Network requests and responses
- Console logs
- Timeline of events

### Screenshots

Screenshots are captured automatically:
- At key steps in each test (always)
- On test completion (always)
- Located in `test-results/` directory

## Troubleshooting

### Docker Issues

**Containers won't start:**
```bash
# Check Docker is running
docker info

# Check for port conflicts
lsof -i :8080

# View container logs
npm run docker:logs
```

**MySQL not ready:**
```bash
# Check MySQL health
docker compose -f docker/docker-compose.yml exec mysql mysqladmin ping

# Wait and retry setup
sleep 30 && npm run docker:setup
```

### WordPress Issues

**Plugin not activated:**
```bash
# Check plugin status
npm run docker:wp -- plugin list

# Manually activate
npm run docker:wp -- plugin activate foogallery-premium
```

**Permalinks not working:**
```bash
# Flush rewrite rules
npm run docker:wp -- rewrite flush
```

### Playwright Issues

**Tests timing out:**
```bash
# Run in headed mode to see what's happening
npm run test:headed

# Check if WordPress is accessible
curl http://localhost:8080
```

**Auth state issues:**
```bash
# Clear auth state and re-run
rm -rf .auth
npm test
```

### Lifecycle Issues

**Want to keep environment after tests:**
```bash
npm run test:keep-env
```

**Want to re-run without full reset:**
```bash
npm run test:quick
```

## Writing New Tests

### Test File Location

Place new test files in `tests/specs/`:

```
tests/specs/
├── smoke.spec.ts           # Basic smoke tests
├── demo-galleries.spec.ts  # Demo gallery creation
├── gallery.spec.ts         # Gallery creation tests
├── templates.spec.ts       # Template tests
└── ...
```

### Test Template

```typescript
import { test, expect } from '@playwright/test';

test.describe('Feature Name', () => {
  test('should do something', async ({ page }) => {
    await page.goto('/wp-admin/');

    // ... test logic

    // Take screenshot at key step
    await page.screenshot({ path: 'test-results/feature-step-name.png' });

    await expect(page.locator('selector')).toBeVisible();
  });
});
```

### Adding Screenshots

Add screenshots at key steps for better visibility:

```typescript
// After important actions
await page.screenshot({ path: 'test-results/test-name-01-description.png' });
```

### Using Page Objects (Future)

For more complex tests, consider implementing Page Object Model:

```typescript
// tests/pages/GalleryPage.ts
export class GalleryPage {
  constructor(private page: Page) {}

  async createGallery(title: string) {
    // ...
  }
}
```

## Phase 2 Roadmap

- [ ] CI/CD integration (GitHub Actions)
- [ ] Test data factories
- [x] ~~Database reset between tests~~ (Implemented via lifecycle management)
- [ ] Page Object Model implementation
- [ ] Multi-browser testing (Firefox, WebKit)
- [ ] Visual regression testing
- [ ] Performance testing
- [ ] API testing for REST endpoints
- [ ] Gallery-specific test suites

## Assumptions Made

1. **WordPress Version:** Using 6.7 with PHP 8.2 (latest stable)
2. **Database:** MySQL 8.0 (most common production setup)
3. **Browser:** Chromium only for Phase 1 (fastest, most stable)
4. **Sequential Tests:** Tests run sequentially for Phase 1 reliability
5. **Plugin Location:** Plugin mounted from parent directory (tests/e2e is inside foogallery-premium/)
6. **Freemius Bypass:** Requires manual setup of `secrets/freemius-e2e-helper.php` (see Developer Setup)
7. **Fresh Environment:** Each `npm test` run starts with a clean WordPress installation

## License

GPL-2.0+ (same as FooGallery Premium)
