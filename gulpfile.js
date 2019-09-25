var gulp = require('gulp'), notify = require("gulp-notify"), zip = require('gulp-zip'),
    fs_config = require('./fs-config.json'),
    packageJSON = require('./package.json'), fileName = packageJSON.name, fileVersion = packageJSON.version;

require( 'gulp-freemius-deploy' )( gulp, {
    developer_id: fs_config.developer_id,
    plugin_id: fs_config.plugin_id,
    public_key: fs_config.public_key,
    secret_key: fs_config.secret_key,
    zip_name: fileName + '.v' + fileVersion + '.zip',
    zip_path: 'dist/',
    add_contributor: true
} );

var buildInclude = [ '**/*', '!package*.json', '!./{node_modules,node_modules/**/*}', '!./{dist,dist/**/*}' ];

gulp.task('zip', function () {
    return gulp.src( buildInclude, {base: './'})
        .pipe(zip(fileName + '.v' + fileVersion + '.zip'))
        .pipe(gulp.dest('dist/'))
        .pipe(notify({message: 'Zip task complete', onLast: true}));
});