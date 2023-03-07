/**
 * ws-scripts.helper.js
 *
 * This file overrides the webpack config used by the ws-scripts module to allow us to override any options we want.
 */

const defaults = require( '@wordpress/scripts/config/webpack.config' );
const sourceMapLoader = require.resolve( 'source-map-loader' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

/**
 * Generates a webpack config using the given entry points and output.
 * @param {object} entry - An object containing a map of entry point name to file.
 * @param {object} output - An object containing the `path` and `filename` to output.
 * @param {string} [mode="production"] - Either `development` or `production` depending on the desired output.
 * @param {boolean} [productionSourceMaps=false] - If set to `true` source maps will be included in the production output.
 * @param {boolean} [removePolyfill=false] - If set to `true` this prevents the default inclusion of `wp-polyfill` for even empty scripts.
 * @return {object} Returns the generated webpack config.
 */
module.exports = function configHelper(entry, output, mode, productionSourceMaps = false, removePolyfill = false){

    if ( productionSourceMaps ){
        defaults.devtool = "source-map";
        defaults.module = {
            ...defaults.module,
            rules: [
                {
                    test: /\.(j|t)sx?$/,
                    exclude: [ /node_modules/ ],
                    use: sourceMapLoader,
                    enforce: 'pre',
                },
                ...defaults.module.rules.filter( (rule) => rule.use !== sourceMapLoader )
            ]
        };
    }

    if ( removePolyfill ){
        defaults.plugins = [
            ...defaults.plugins.filter( ( plugin ) => plugin.constructor.name !== 'DependencyExtractionWebpackPlugin' ),
            new DependencyExtractionWebpackPlugin( { injectPolyfill: false } ),
        ];
    }

    return {
        ...defaults,
        entry,
        output,
        mode: mode ?? "production"
    }
};