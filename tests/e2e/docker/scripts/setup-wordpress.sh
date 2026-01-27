#!/bin/bash
# File: docker/scripts/setup-wordpress.sh
# Idempotent WordPress setup script for FooGallery E2E testing

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Configuration from environment (with defaults)
WP_URL="${WP_URL:-http://localhost:8080}"
WP_TITLE="${WP_TITLE:-FooGallery E2E Test Site}"
WP_ADMIN_USER="${WORDPRESS_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WORDPRESS_ADMIN_PASSWORD:-admin}"
WP_ADMIN_EMAIL="${WORDPRESS_ADMIN_EMAIL:-admin@example.com}"
DB_HOST="${WORDPRESS_DB_HOST:-mysql}"
DB_NAME="${WORDPRESS_DB_NAME:-wordpress_test}"
DB_USER="${WORDPRESS_DB_USER:-wordpress}"
DB_PASSWORD="${WORDPRESS_DB_PASSWORD:-wordpress}"

cd /var/www/html

log_info "Starting WordPress setup..."

# Step 1: Wait for MySQL to be ready
log_info "Waiting for MySQL..."
COUNTER=0
MAX_TRIES=60
until wp db check --allow-root 2>/dev/null; do
    COUNTER=$((COUNTER + 1))
    if [ $COUNTER -ge $MAX_TRIES ]; then
        log_error "MySQL not available after ${MAX_TRIES} attempts"
        exit 1
    fi
    sleep 2
done
log_info "MySQL is ready!"

# Step 2: Check if WordPress is installed
if wp core is-installed --allow-root 2>/dev/null; then
    log_info "WordPress is already installed"
else
    log_info "Installing WordPress..."
    wp core install \
        --url="$WP_URL" \
        --title="$WP_TITLE" \
        --admin_user="$WP_ADMIN_USER" \
        --admin_password="$WP_ADMIN_PASSWORD" \
        --admin_email="$WP_ADMIN_EMAIL" \
        --skip-email \
        --allow-root
    log_info "WordPress installed successfully!"
fi

# Step 3: Set up mu-plugins directory and freemius-killer
log_info "Setting up mu-plugins..."
mkdir -p /var/www/html/wp-content/mu-plugins

# Check for freemius-killer.php in mounted secrets directory
FREEMIUS_KILLER_SOURCE="/var/www/html/wp-content/e2e-secrets/freemius-killer.php"
FREEMIUS_KILLER_DEST="/var/www/html/wp-content/mu-plugins/freemius-killer.php"

if [ -f "$FREEMIUS_KILLER_SOURCE" ]; then
    log_info "Installing freemius-killer from secrets..."
    cp "$FREEMIUS_KILLER_SOURCE" "$FREEMIUS_KILLER_DEST"
    chown www-data:www-data "$FREEMIUS_KILLER_DEST"
    log_info "Freemius killer installed!"
else
    log_warn "Freemius killer not found at $FREEMIUS_KILLER_SOURCE"
    log_warn "Premium features will require a valid license."
    log_warn "See e2e/README.md for setup instructions."
fi

# Step 4: Activate FooGallery Premium
log_info "Activating FooGallery Premium..."
if wp plugin is-active foogallery-premium --allow-root 2>/dev/null; then
    log_info "FooGallery Premium is already active"
else
    wp plugin activate foogallery-premium --allow-root || log_warn "Could not activate FooGallery Premium (may not be installed)"
fi

# Step 4b: Activate FooGallery Albums Extension
log_info "Activating FooGallery Albums extension..."
wp eval 'update_option("foogallery_extensions_activated", array_merge(get_option("foogallery_extensions_activated", array()), array("foogallery-albums" => "FooGallery_Albums_Extension"))); echo "Albums extension activated!";' --allow-root 2>/dev/null || log_warn "Could not activate Albums extension"

# Step 5: Configure permalinks
log_info "Setting permalinks to /%postname%/..."
wp rewrite structure '/%postname%/' --allow-root
wp rewrite flush --allow-root

# Step 6: Enable debug mode
log_info "Configuring WordPress debug settings..."
wp config set WP_DEBUG true --raw --allow-root 2>/dev/null || true
wp config set WP_DEBUG_LOG true --raw --allow-root 2>/dev/null || true
wp config set WP_DEBUG_DISPLAY false --raw --allow-root 2>/dev/null || true
wp config set SCRIPT_DEBUG true --raw --allow-root 2>/dev/null || true

# Step 7: Ensure uploads directory has correct permissions for FooGallery thumbnails
log_info "Setting up uploads directory permissions..."
mkdir -p /var/www/html/wp-content/uploads/cache
chown -R www-data:www-data /var/www/html/wp-content/uploads
chmod -R 755 /var/www/html/wp-content/uploads
log_info "Uploads directory ready!"

# Step 8: Import sample images for testing (30 images for pagination tests)
log_info "Importing sample images for E2E testing..."
IMAGES_IMPORTED=$(wp media list --format=count --allow-root 2>/dev/null || echo "0")
TEST_IMAGES_DIR="/var/www/html/wp-content/e2e-assets/images"
MIN_IMAGES=30

if [ "$IMAGES_IMPORTED" -lt "$MIN_IMAGES" ]; then
    log_info "Need at least $MIN_IMAGES images for pagination tests..."
    ATTACHMENT_IDS=""

    # First, try to import from local test-assets/images directory
    if [ -d "$TEST_IMAGES_DIR" ] && [ "$(ls -A $TEST_IMAGES_DIR/*.jpg 2>/dev/null | wc -l)" -gt 0 ]; then
        log_info "Importing images from local test assets..."
        NUM=1
        for img in "$TEST_IMAGES_DIR"/*.jpg; do
            if [ -f "$img" ]; then
                BASENAME=$(basename "$img" .jpg)
                log_info "Importing image $NUM: $BASENAME"
                ATTACHMENT_ID=$(wp media import "$img" \
                    --title="Sample image $NUM for E2E testing" \
                    --alt="Sample image $NUM for E2E testing" \
                    --porcelain \
                    --allow-root 2>/dev/null)
                if [ -n "$ATTACHMENT_ID" ]; then
                    log_info "Image $NUM imported as attachment ID: $ATTACHMENT_ID"
                    ATTACHMENT_IDS="$ATTACHMENT_IDS $ATTACHMENT_ID"
                else
                    log_warn "Could not import $BASENAME"
                fi
                NUM=$((NUM + 1))
            fi
        done
    else
        # Fallback: download from picsum.photos if no local images
        log_info "No local images found, downloading from picsum.photos..."
        for i in $(seq 1 $MIN_IMAGES); do
            IMAGE_ID=$((i * 10))
            log_info "Downloading image $i (picsum ID: $IMAGE_ID)..."
            ATTACHMENT_ID=$(wp media import "https://picsum.photos/id/$IMAGE_ID/800/600.jpg" \
                --title="Sample image $i for E2E testing" \
                --alt="Sample image $i for E2E testing" \
                --porcelain \
                --allow-root 2>/dev/null)
            if [ -n "$ATTACHMENT_ID" ]; then
                log_info "Image $i imported as attachment ID: $ATTACHMENT_ID"
                ATTACHMENT_IDS="$ATTACHMENT_IDS $ATTACHMENT_ID"
            else
                log_warn "Could not import image $i"
            fi
        done
    fi

    # Regenerate thumbnails for all imported images
    if [ -n "$ATTACHMENT_IDS" ]; then
        log_info "Regenerating thumbnails for imported images..."
        wp media regenerate $ATTACHMENT_IDS --yes --allow-root 2>/dev/null || log_warn "Thumbnail regeneration had issues"
        log_info "Thumbnail regeneration complete!"
    fi

    # Assign media tags to images for filtering tests
    log_info "Assigning media tags to images for filtering..."
    # Create the tags first
    wp term create foogallery_attachment_tag "Nature" --allow-root 2>/dev/null || true
    wp term create foogallery_attachment_tag "Animals" --allow-root 2>/dev/null || true
    wp term create foogallery_attachment_tag "Objects" --allow-root 2>/dev/null || true
    wp term create foogallery_attachment_tag "Workspace" --allow-root 2>/dev/null || true

    # Assign tags to images (rotating through tags for variety)
    IDS_ARRAY=($ATTACHMENT_IDS)
    TAGS=("Nature" "Animals" "Objects" "Workspace")
    for i in "${!IDS_ARRAY[@]}"; do
        TAG_INDEX=$((i % 4))
        TAG="${TAGS[$TAG_INDEX]}"
        wp post term set ${IDS_ARRAY[$i]} foogallery_attachment_tag "$TAG" --allow-root 2>/dev/null && \
            log_info "Tagged image $((i+1)) with $TAG" || true
    done
    log_info "Media tags assigned to images!"

    # Fix ownership of imported images (wp-cli runs as root)
    chown -R www-data:www-data /var/www/html/wp-content/uploads
    log_info "Sample images import complete! ($((${#IDS_ARRAY[@]})) images imported)"
else
    log_info "Sample images already exist ($IMAGES_IMPORTED images found, need $MIN_IMAGES)"
fi

# Step 9: Create E2E test page
log_info "Creating E2E test page..."
if ! wp post list --post_type=page --name=e2e-test-page --format=ids --allow-root | grep -q .; then
    wp post create \
        --post_type=page \
        --post_title="E2E Test Page" \
        --post_name="e2e-test-page" \
        --post_status=publish \
        --post_content='<!-- wp:paragraph --><p>This page is used for E2E testing.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[foogallery id="0"]<!-- /wp:shortcode -->' \
        --allow-root
    log_info "E2E test page created!"
else
    log_info "E2E test page already exists"
fi

# Step 10: Import test videos for video gallery testing
log_info "Checking for test videos to import..."
TEST_VIDEOS_DIR="/var/www/html/wp-content/e2e-assets/videos"
if [ -d "$TEST_VIDEOS_DIR" ]; then
    VIDEOS_IMPORTED=$(wp media list --mime_type=video --format=count --allow-root 2>/dev/null || echo "0")
    if [ "$VIDEOS_IMPORTED" -lt 2 ]; then
        log_info "Importing test videos from $TEST_VIDEOS_DIR..."
        for video in "$TEST_VIDEOS_DIR"/*.mp4; do
            if [ -f "$video" ]; then
                BASENAME=$(basename "$video" .mp4)
                log_info "Importing video: $BASENAME"
                wp media import "$video" \
                    --title="$BASENAME" \
                    --allow-root 2>/dev/null || log_warn "Could not import $video"
            fi
        done
        chown -R www-data:www-data /var/www/html/wp-content/uploads
        log_info "Test videos import complete!"
    else
        log_info "Test videos already exist ($VIDEOS_IMPORTED videos found)"
    fi
else
    log_info "No test videos directory found at $TEST_VIDEOS_DIR (skipping video import)"
fi

# Step 11: Import EXIF test images for EXIF metadata testing
log_info "Checking for EXIF test images to import..."
EXIF_IMAGES_DIR="/var/www/html/wp-content/e2e-assets/exif"
if [ -d "$EXIF_IMAGES_DIR" ]; then
    log_info "Importing EXIF test images from $EXIF_IMAGES_DIR..."
    for img in "$EXIF_IMAGES_DIR"/*.jpg; do
        if [ -f "$img" ]; then
            BASENAME=$(basename "$img" .jpg)
            # Check if image already exists by searching for title
            EXISTING=$(wp post list --post_type=attachment --title="EXIF - $BASENAME" --format=count --allow-root 2>/dev/null || echo "0")
            if [ "$EXISTING" -eq 0 ]; then
                log_info "Importing EXIF image: $BASENAME"
                ATTACHMENT_ID=$(wp media import "$img" \
                    --title="EXIF - $BASENAME" \
                    --alt="EXIF test image: $BASENAME" \
                    --porcelain \
                    --allow-root 2>/dev/null)
                if [ -n "$ATTACHMENT_ID" ]; then
                    log_info "EXIF image imported as attachment ID: $ATTACHMENT_ID"
                    # Tag it with EXIF for easy filtering
                    wp term create foogallery_attachment_tag "EXIF" --allow-root 2>/dev/null || true
                    wp post term set $ATTACHMENT_ID foogallery_attachment_tag "EXIF" --allow-root 2>/dev/null || true
                else
                    log_warn "Could not import EXIF image: $BASENAME"
                fi
            else
                log_info "EXIF image already exists: $BASENAME"
            fi
        fi
    done
    chown -R www-data:www-data /var/www/html/wp-content/uploads
    log_info "EXIF test images import complete!"
else
    log_info "No EXIF test images directory found at $EXIF_IMAGES_DIR (skipping EXIF import)"
fi

# Step 12: Configure video API keys (if provided)
log_info "Configuring video API keys..."
YOUTUBE_API_KEY="${YOUTUBE_API_KEY:-}"
VIMEO_ACCESS_TOKEN="${VIMEO_ACCESS_TOKEN:-}"

if [ -n "$YOUTUBE_API_KEY" ]; then
    log_info "Setting YouTube API key..."
    # FooGallery stores this in options with foogallery prefix
    wp option update foogallery_youtube_api_key "$YOUTUBE_API_KEY" --allow-root 2>/dev/null || true
    log_info "YouTube API key configured!"
else
    log_info "No YouTube API key provided (YOUTUBE_API_KEY env var not set)"
fi

if [ -n "$VIMEO_ACCESS_TOKEN" ]; then
    log_info "Setting Vimeo access token..."
    # FooGallery stores this in options with foogallery prefix
    wp option update foogallery_vimeo_access_token "$VIMEO_ACCESS_TOKEN" --allow-root 2>/dev/null || true
    log_info "Vimeo access token configured!"
else
    log_info "No Vimeo access token provided (VIMEO_ACCESS_TOKEN env var not set)"
fi

# Step 12: Set timezone
wp option update timezone_string "UTC" --allow-root

# Step 13: Update site URLs (in case they differ)
wp option update siteurl "$WP_URL" --allow-root
wp option update home "$WP_URL" --allow-root

# Print summary
echo ""
echo "========================================"
echo -e "${GREEN}WordPress Setup Complete!${NC}"
echo "========================================"
echo ""
echo "Site URL:     $WP_URL"
echo "Admin URL:    $WP_URL/wp-admin/"
echo "Admin User:   $WP_ADMIN_USER"
echo "Admin Pass:   $WP_ADMIN_PASSWORD"
echo ""
echo "FooGallery:   $(wp plugin status foogallery-premium --allow-root 2>/dev/null | grep Status || echo 'Check manually')"
echo ""
echo "========================================"
