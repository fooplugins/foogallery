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

// List of assets to fingerprint (full relative paths)
const ASSETS_TO_FINGERPRINT = [
    'extensions/default-templates/shared/css/foogallery.css',
    'extensions/default-templates/shared/js/foogallery.js'
];

// Delete old fingerprinted files
function deleteOldFingerprints() {
    ASSETS_TO_FINGERPRINT.forEach(originalPath => {
        const dir  = path.dirname(originalPath);
        const ext  = path.extname(originalPath);
        const base = path.basename(originalPath, ext);

        const files = fs.readdirSync(dir);

        files.forEach(file => {
            // match foogallery.fp-1a2b3c4d.css
            const regex = new RegExp(`^${base}\\.v-[a-f0-9]{8}\\${ext}$`);

            if (regex.test(file)) {
                fs.unlinkSync(path.join(dir, file));
                console.log("Deleted old fingerprint:", file);
            }
        });
    });
}

// Generate manifest.php mapping original → fingerprinted
function fingerprintAssets(cb) {
	deleteOldFingerprints();

    const manifest = {};

    ASSETS_TO_FINGERPRINT.forEach(originalPath => {
        if (!fs.existsSync(originalPath)) {
            console.warn("Missing asset:", originalPath);
            return;
        }

        const content = fs.readFileSync(originalPath);
        const hash = crypto.createHash("md5").update(content).digest("hex").slice(0, 8);

        const dir  = path.dirname(originalPath);
        const ext  = path.extname(originalPath);
        const base = path.basename(originalPath, ext);

        const fingerprintedName = `${base}.v-${hash}${ext}`;
        const fingerprintedPath = path.join(dir, fingerprintedName);

        // Copy file to fingerprinted filename
        fs.copyFileSync(originalPath, fingerprintedPath);

        // Add to manifest using full relative paths
        manifest[originalPath] = fingerprintedPath;
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
        `./dist/${pkg.name}.v${pkg.version}.zip`
    ]);
}

// extract a .pot file from all PHP files excluding those in the node_modules dir
function translate(){
    return gulp.src("./**/*.php")
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
        '!WORDPRESS.md'
    ])
        .pipe(gulpZip(`${pkg.name}.v${pkg.version}.zip`))
        .pipe(gulp.dest("./dist"));
}

exports.default = gulp.series(clean, translate, fingerprintAssets, zip);
