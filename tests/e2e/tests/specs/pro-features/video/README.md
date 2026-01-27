# Video Gallery E2E Tests

End-to-end tests for FooGallery Pro Video support feature.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `video-settings.spec.ts` | 10 | Video settings configuration in gallery admin |
| `video-url-config.spec.ts` | 6 | Adding/removing video URLs on attachments |
| `video-frontend.spec.ts` | 5 | Video icon display and styling on frontend |
| `video-lightbox.spec.ts` | 4 | Video playback in lightbox |

**Total: 25 tests**

## Running Video Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all video tests
npm run test:video

# Run video tests in headed mode (see browser)
npm run test:video:headed

# Run specific test file
npx playwright test --config=tests/playwright.config.ts tests/specs/pro-features/video/video-settings.spec.ts
```

## Prerequisites

### 1. Test Videos (Self-Hosted)
Sample videos are located in `e2e/test-assets/videos/`:
- `sample-video-1.mp4` - Test video 1
- `sample-video-2.mp4` - Test video 2
- `sample-video-3.mp4` - Test video 3
- `sample-video-4.mp4` - Test video 4

These are automatically imported into WordPress during test setup.

### 2. API Keys (Optional)
For YouTube playlist and Vimeo channel import tests, add API keys to `.env`:

```bash
# YouTube Data API v3 key
YOUTUBE_API_KEY=your_api_key_here

# Vimeo Access Token
VIMEO_ACCESS_TOKEN=your_token_here
```

**Note:** Single video URL imports work without API keys.

## Test Coverage

### Video Settings (`video-settings.spec.ts`)
- Default video enabled state
- Disabling video feature
- Video hover icon options (6 styles)
- Video icon style application
- Sticky video icon enable/disable
- Video icon size configuration
- Lightbox video size selection
- Lightbox autoplay enable/disable

### Video URL Configuration (`video-url-config.spec.ts`)
- Add YouTube video URL to image
- Add Vimeo video URL to image
- Add self-hosted video URL to image
- Clear video URL from image
- Update video provider field
- Update video ID field

### Video Frontend Display (`video-frontend.spec.ts`)
- Video icon on thumbnail
- Video icon style class
- Sticky icon visibility
- Video disabled state
- Item count display

### Video Lightbox Playback (`video-lightbox.spec.ts`)
- Open video in lightbox
- Video size configuration
- Navigation between videos
- Close video lightbox

## Troubleshooting

### Tests failing to find Video tab
The Video tab in gallery settings may be named differently or require scrolling. Use Chrome DevTools Recorder to capture the exact selectors.

### API key not working
1. Verify the key is in `.env` (not `.env.example`)
2. Check that Docker containers have been restarted to pick up new env vars
3. Verify the key is valid by testing in WordPress admin directly

### Self-hosted videos not importing
1. Check that `e2e/test-assets/videos/` contains MP4 files
2. Verify Docker volume mount in `docker-compose.yml`
3. Check setup script logs: `npm run docker:logs`

## Related Files

- `tests/helpers/video-test-helper.ts` - Helper functions
- `docker/scripts/setup-wordpress.sh` - Video import during setup
- `docker/docker-compose.yml` - Test assets volume mount
- `.env.example` - API key configuration template
