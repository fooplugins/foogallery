const gulp = require("gulp");
const gulpWpPot = require("gulp-wp-pot");
const gulpZip = require("gulp-zip");
const gulpFreemius = require("gulp-freemius-deploy");
const del = require("del");
const pkg = require("./package.json");
const freemiusConfig = require("./fs-config.json");

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
        '!Gruntfile.js'
    ])
        .pipe(gulpZip(`${pkg.name}.v${pkg.version}.zip`))
        .pipe(gulp.dest("./dist"));
}

exports.default = gulp.series(clean, translate, zip);