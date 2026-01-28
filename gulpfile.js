const gulp = require("gulp");
const gulpWpPot = require("gulp-wp-pot");
const gulpZip = require("gulp-zip");
const gulpFreemius = require("gulp-freemius-deploy");
const del = require("del");
const pkg = require("./package.json");
const freemiusConfig = require("./fs-config.json");
const crypto = require("crypto");
const fs = require("fs");
const path = require("path");

// List of assets to fingerprint (full relative paths).
// Optionally use { source: 'path/to/file.ext', outputDir: 'existing/output/folder' }
// to write the fingerprinted file into a different folder when that folder exists.
const ASSETS_TO_FINGERPRINT = [
    { source: 'extensions/default-templates/shared/css/foogallery.css', outputDir: 'assets/css' },
	{ source: 'extensions/default-templates/shared/css/foogallery.min.css', outputDir: 'assets/css' },
    { source: 'extensions/default-templates/shared/js/foogallery.js', outputDir: 'assets/js' },
	{ source: 'extensions/default-templates/shared/js/foogallery.min.js', outputDir: 'assets/js' },
	{ source: 'extensions/default-templates/shared/js/foogallery.ready.js', outputDir: 'assets/js' },
	{ source: 'extensions/default-templates/shared/js/foogallery.ready.min.js', outputDir: 'assets/js' },
	{ source: 'extensions/default-templates/shared/js/foogallery.polyfills.js', outputDir: 'assets/js' },
	{ source: 'extensions/default-templates/shared/js/foogallery.polyfills.min.js', outputDir: 'assets/js' },

	{ source: 'pro/extensions/default-templates/shared/css/foogallery.css', outputDir: 'pro/assets/css' },
	{ source: 'pro/extensions/default-templates/shared/css/foogallery.min.css', outputDir: 'pro/assets/css' },
	{ source: 'pro/extensions/default-templates/shared/js/foogallery.js', outputDir: 'pro/assets/js' },
	{ source: 'pro/extensions/default-templates/shared/js/foogallery.min.js', outputDir: 'pro/assets/js' }
];

function normalizeAssetConfig(asset) {
    if (typeof asset === "string") {
        return { source: asset };
    }

    if (asset && typeof asset === "object" && asset.source) {
        return {
            source: asset.source,
            outputDir: asset.outputDir
        };
    }

    throw new Error("ASSETS_TO_FINGERPRINT entries must be a string path or an object with a 'source' property.");
}

function getAssetTargetDir(assetConfig) {
    const sourceDir = path.dirname(assetConfig.source);

    if (assetConfig.outputDir && fs.existsSync(assetConfig.outputDir) && fs.statSync(assetConfig.outputDir).isDirectory()) {
        return assetConfig.outputDir;
    }

    return sourceDir;
}

function escapeRegex(value) {
    return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function getFingerprintRegexes(assetConfig) {
    const ext  = path.extname(assetConfig.source);
    const base = path.basename(assetConfig.source, ext);
    const escapedExt = escapeRegex(ext);

    if (base.endsWith(".min")) {
        const baseNoMin = base.slice(0, -4);

        return [
            new RegExp(`^${escapeRegex(baseNoMin)}\\.[a-f0-9]{8}\\.min${escapedExt}$`),
            new RegExp(`^${escapeRegex(base)}\\.[a-f0-9]{8}${escapedExt}$`)
        ];
    }

    return [
        new RegExp(`^${escapeRegex(base)}\\.[a-f0-9]{8}${escapedExt}$`)
    ];
}

function getFingerprintedName(assetConfig, hash) {
    const ext  = path.extname(assetConfig.source);
    const base = path.basename(assetConfig.source, ext);

    if (base.endsWith(".min")) {
        const baseNoMin = base.slice(0, -4);
        return `${baseNoMin}.${hash}.min${ext}`;
    }

    return `${base}.${hash}${ext}`;
}

// Delete old fingerprinted files
function deleteOldFingerprints() {
    ASSETS_TO_FINGERPRINT.forEach(assetEntry => {
        const assetConfig = normalizeAssetConfig(assetEntry);
        const sourceDir = path.dirname(assetConfig.source);
        const possibleDirs = [sourceDir];

        if (assetConfig.outputDir && fs.existsSync(assetConfig.outputDir) && fs.statSync(assetConfig.outputDir).isDirectory()) {
            possibleDirs.push(assetConfig.outputDir);
        }

        const fingerprintRegexes = getFingerprintRegexes(assetConfig);

        possibleDirs.forEach(dir => {
            if (!fs.existsSync(dir)) {
                return;
            }

            const files = fs.readdirSync(dir);

            files.forEach(file => {
                if (fingerprintRegexes.some(regex => regex.test(file))) {
                    fs.unlinkSync(path.join(dir, file));
                    console.log("Deleted old fingerprint:", path.join(dir, file));
                }
            });
        });
    });
}

// Generate manifest.php mapping original → fingerprinted
function fingerprintAssets(cb) {
	deleteOldFingerprints();

    const manifest = {};

    ASSETS_TO_FINGERPRINT.forEach(assetEntry => {
        const assetConfig = normalizeAssetConfig(assetEntry);

        if (!fs.existsSync(assetConfig.source)) {
            console.warn("Missing asset:", assetConfig.source);
            return;
        }

        const content = fs.readFileSync(assetConfig.source);
        const hash = crypto.createHash("md5").update(content).digest("hex").slice(0, 8);

        const targetDir = getAssetTargetDir(assetConfig);
        const fingerprintedName = getFingerprintedName(assetConfig, hash);
        const fingerprintedPath = path.join(targetDir, fingerprintedName);

        // Copy file to fingerprinted filename
        fs.copyFileSync(assetConfig.source, fingerprintedPath);

        // Add to manifest using full relative paths
        manifest[assetConfig.source] = fingerprintedPath;
    });

    // Build PHP file contents
    let php = "<?php\n\nreturn [\n";
    Object.keys(manifest).forEach(key => {
        php += `    '${key}' => '${manifest[key]}',\n`;
    });
    php += "];\n";

    // Write manifest.php inside shared folder
    fs.writeFileSync(
        "includes/asset-manifest.php",
        php
    );

    cb();
}

// register the freemius-deploy task
gulpFreemius(gulp, {
    ...freemiusConfig,
    zip_name: `${pkg.name}.v${pkg.version}.zip`,
    zip_path: "./dist/",
    add_contributor: true
});

// clean up the files created by the tasks
function clean(){
    return del([
        `./languages/${pkg.name}.pot`,
        `./dist/${pkg.name}.v${pkg.version}.zip`,
        "**/.DS_Store"
    ], {
        dot: true,
        ignore: ["**/node_modules/**"]
    });
}

// extract a .pot file from all PHP files excluding those in the node_modules dir
function translate(){
    return gulp.src([
        "./*.php",
		"./extensions/**/*.php",
		"./gutenberg/**/*.php",
		"./pro/**/*.php",
        "./includes/**/*.php"
    ])
        .pipe(gulpWpPot({
            "domain": `${pkg.name}`,
            "package": `${pkg.title}`,
            "bugReport": `${pkg.bugs}`,
            "team": `${pkg.author}`,
            "lastTranslator": `${pkg.author}`
        }))
        .pipe(gulp.dest(`./languages/${pkg.name}.pot`));
}

// create a .zip containing just the production code for the plugin
function zip(){
    return gulp.src([
        "**/*",
        "!**/.DS_Store",
        "!package*.json",
        "!./{node_modules,node_modules/**/*}",
        "!./{dist,dist/**/*}",
        "!./{.nodeploy,.nodeploy/**/*}",
        '!./{vendor,vendor/**/*}',
        "!./{src,src/**/*}",
        '!./{gutenberg/src,gutenberg/src/**/*,gutenberg/config,gutenberg/config/**/*}',
        "!fs-config.json",
        "!composer.json",
        "!composer.lock",
        "!gulpfile*.js",
        "!webpack*.js",
        "!./{gulpfile.js,gulpfile.js/**/*}",
        '!README.md',
        '!Gruntfile.js',
        "!./{.nodeploy,.nodeploy/**/*}",
        '!AGENTS.md',
        '!WORDPRESS.md',
		"!./{tests,tests/**/*}",
		"!./{.docs,.docs/**/*}",
		'!phpunit.xml.dist'
    ])
        .pipe(gulpZip(`${pkg.name}.v${pkg.version}.zip`))
        .pipe(gulp.dest("./dist"));
}

exports.default = gulp.series(clean, translate, fingerprintAssets, zip);
