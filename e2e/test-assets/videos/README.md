# Test Video Assets

Sample video files for FooGallery E2E video testing.

## Files

| File | Size | Description |
|------|------|-------------|
| `sample-video-1.mp4` | ~699KB | Test video 1 |
| `sample-video-2.mp4` | ~707KB | Test video 2 |
| `sample-video-3.mp4` | ~894KB | Test video 3 |
| `sample-video-4.mp4` | ~1.1MB | Test video 4 |

**Total size:** ~3.4MB

## Usage in Tests

These videos are automatically imported into WordPress during test setup:
1. Files are mounted into Docker container at `/var/www/html/wp-content/e2e-assets/videos/`
2. Setup script imports them via WP-CLI: `wp media import`
3. Tests use them for self-hosted video functionality

## Adding Your Own Videos

To use your own test videos:
1. Add MP4 files to this directory (keep them small, under 2MB each)
2. Use royalty-free / CC0 / CC-BY licensed content
3. Update this README with source and license info
4. Ensure videos play in browser (use H.264 codec for best compatibility)

## Recommended Sources for Test Videos

- [Pexels](https://www.pexels.com/videos/) - CC0, no attribution required
- [Pixabay](https://pixabay.com/videos/) - CC0, no attribution required
- [Coverr](https://coverr.co/) - Free for commercial use
- [Blender Open Movies](https://studio.blender.org/films/) - CC-BY

## Note

Ensure any videos committed to the repository are properly licensed for redistribution.
iStock preview videos should only be used for local testing, not committed to public repos.
