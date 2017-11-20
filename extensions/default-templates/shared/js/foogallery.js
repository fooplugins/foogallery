(function($, _){

	/**
	 * @summary A reference to the jQuery object the plugin is registered with.
	 * @memberof FooGallery
	 * @name $
	 * @type {jQuery}
	 * @description This is used internally for all jQuery operations to help work around issues where multiple jQuery libraries have been included in a single page.
	 * @example {@caption The following shows the issue when multiple jQuery's are included in a single page.}{@lang xml}
	 * <script src="jquery-1.12.4.js"></script>
	 * <script src="foogallery.js"></script>
	 * <script src="jquery-2.2.4.js"></script>
	 * <script>
	 * 	jQuery(function($){
	 * 		$(".selector").foogallery(); // => This would throw a TypeError: $(...).foogallery is not a function
	 * 	});
	 * </script>
	 * @example {@caption The reason the above throws an error is that the `$.fn.foogallery` function is registered to the first instance of jQuery in the page however the instance used to create the ready callback and actually try to execute `$(...).foogallery()` is the second. To resolve this issue ideally you would remove the second instance of jQuery however you can use the `FooGallery.$` member to ensure you are always working with the instance of jQuery the plugin was registered with.}{@lang xml}
	 * <script src="jquery-1.12.4.js"></script>
	 * <script src="foogallery.js"></script>
	 * <script src="jquery-2.2.4.js"></script>
	 * <script>
	 * 	FooGallery.$(function($){
	 * 		$(".selector").foogallery(); // => It works!
	 * 	});
	 * </script>
	 */
	_.$ = $;

	/**
	 * @summary The jQuery plugin namespace.
	 * @external "jQuery.fn"
	 * @see {@link http://learn.jquery.com/plugins/basic-plugin-creation/|How to Create a Basic Plugin | jQuery Learning Center}
	 */
})(
	// dependencies
	jQuery,
	/**
	 * @summary The core namespace for the plugin containing all its code.
	 * @namespace FooGallery
	 * @description This plugin houses all it's code within a single `FooGallery` global variable to prevent polluting the global namespace and to make accessing its various members simpler.
	 * @example {@caption As this namespace is registered as a global on the `window` object, it can be accessed using the `window.` prefix.}
	 * var fg = window.FooGallery;
	 * @example {@caption Or without it.}
	 * var fg = FooGallery;
	 * @example {@caption When using this namespace I would recommend aliasing it to a short variable name such as `fg` or as used internally `_`.}
	 * // alias the FooGallery namespace
	 * var _ = FooGallery;
	 * @example {@caption This is not required but lets us write less code and allows the alias to be minified by compressors like UglifyJS. How you choose to alias the namespace is up to you. You can use the simple `var` method as seen above or supply the namespace as a parameter when creating a new scope as seen below.}
	 * // create a new scope to work in passing the namespace as a parameter
	 * (function(_){
	 *
	 * 	// use `_.` to access members and methods
	 *
	 * })(FooGallery);
	 */
	window.FooGallery = window.FooGallery || {}
);
/*!
* FooGallery.utils - Contains common utility methods and classes used in our plugins.
* @version 0.0.5
* @link https://github.com/steveush/foo-utils#readme
* @copyright Steve Usher 2017
* @license Released under the GPL-3.0 license.
*/
/**
 * @file This creates the global FooGallery.utils namespace ensuring it only registers itself if the namespace doesn't already exist or if the current version is lower than this one.
 */
(function ($) {

	if (!$){
		console.warn('jQuery must be included in the page prior to the FooGallery.utils library.');
		return;
	}

	/**
	 * @summary This namespace contains common utility methods and code shared between our plugins.
	 * @namespace FooGallery.utils
	 * @description This namespace relies on jQuery being included in the page prior to it being loaded.
	 */
	var utils = {
		/**
		 * @summary A reference to the jQuery object the library is registered with.
		 * @memberof FooGallery.utils
		 * @name $
		 * @type {jQuery}
		 * @description This is used internally for all jQuery operations to help work around issues where multiple jQuery libraries have been included in a single page.
		 * @example {@caption The following shows the issue when multiple jQuery's are included in a single page.}{@lang html}
		 * <script src="jquery-1.12.4.js"></script>
		 * <script src="my-plugin.js"></script>
		 * <script src="jquery-2.2.4.js"></script>
		 * <script>
		 * 	jQuery(function($){
	 	 * 		$(".selector").myPlugin(); // => This would throw a TypeError: $(...).myPlugin is not a function
	 	 * 	});
		 * </script>
		 * @example {@caption The reason the above throws an error is that the `$.fn.myPlugin` function is registered to the first instance of jQuery in the page however the instance used to create the ready callback and actually try to execute `$(...).myPlugin()` is the second. To resolve this issue ideally you would remove the second instance of jQuery however you can use the `FooGallery.utils.$` member to ensure you are always working with the instance of jQuery the library was registered with.}{@lang html}
		 * <script src="jquery-1.12.4.js"></script>
		 * <script src="my-plugin.js"></script>
		 * <script src="jquery-2.2.4.js"></script>
		 * <script>
		 * 	FooGallery.utils.$(function($){
	 	 * 		$(".selector").myPlugin(); // => It works!
	 	 * 	});
		 * </script>
		 */
		$: $,
		/**
		 * @summary The version of this library.
		 * @memberof FooGallery.utils
		 * @name version
		 * @type {string}
		 */
		version: '0.0.5',
	};

	/**
	 * @summary Compares two version numbers.
	 * @memberof FooGallery.utils
	 * @function versionCompare
	 * @param {string} version1 - The first version to use in the comparison.
	 * @param {string} version2 - The second version to compare to the first.
	 * @returns {number} `0` if the version are equal.
	 * `-1` if `version1` is less than `version2`.
	 * `1` if `version1` is greater than `version2`.
	 * `NaN` if either of the supplied versions do not conform to MAJOR.MINOR.PATCH format.
	 * @description This method will compare two version numbers that conform to the basic MAJOR.MINOR.PATCH format returning the result as a simple number. This method will handle short version string comparisons e.g. `1.0` versus `1.0.1`.
	 * @example {@caption The following shows the results of comparing various version strings.}
	 * console.log( FooGallery.utils.versionCompare( "0", "0" ) ); // => 0
	 * console.log( FooGallery.utils.versionCompare( "0.0", "0" ) ); // => 0
	 * console.log( FooGallery.utils.versionCompare( "0.0", "0.0.0" ) ); // => 0
	 * console.log( FooGallery.utils.versionCompare( "0.1", "0.0.0" ) ); // => 1
	 * console.log( FooGallery.utils.versionCompare( "0.1", "0.0.1" ) ); // => 1
	 * console.log( FooGallery.utils.versionCompare( "1", "0.1" ) ); // => 1
	 * console.log( FooGallery.utils.versionCompare( "1.10", "1.9" ) ); // => 1
	 * console.log( FooGallery.utils.versionCompare( "1.9", "1.10" ) ); // => -1
	 * console.log( FooGallery.utils.versionCompare( "1", "1.1" ) ); // => -1
	 * console.log( FooGallery.utils.versionCompare( "1.0.9", "1.1" ) ); // => -1
	 * @example {@caption If either of the supplied version strings does not match the MAJOR.MINOR.PATCH format then `NaN` is returned.}
	 * console.log( FooGallery.utils.versionCompare( "not-a-version", "1.1" ) ); // => NaN
	 * console.log( FooGallery.utils.versionCompare( "1.1", "not-a-version" ) ); // => NaN
	 * console.log( FooGallery.utils.versionCompare( "not-a-version", "not-a-version" ) ); // => NaN
	 */
	utils.versionCompare = function(version1, version2){
		// if either of the versions do not match the expected format return NaN
		if (!(/[\d.]/.test(version1) && /[\d.]/.test(version2))) return NaN;

		/**
		 * @summary Splits and parses the given version string into a numeric array.
		 * @param {string} version - The version string to split and parse.
		 * @returns {Array.<number>}
		 * @ignore
		 */
		function split(version){
			var res = version.split('.');
			for(var i = 0, len = res.length; i < len; i++){
				res[i] = parseInt(res[i]);
				if (isNaN(res[i])) res[i] = 0;
			}
			return res;
		}

		// get the base numeric arrays for each version
		var v1parts = split(version1),
			v2parts = split(version2);

		// ensure both arrays are the same length by padding the shorter with 0
		while (v1parts.length < v2parts.length) v1parts.push(0);
		while (v2parts.length < v1parts.length) v2parts.push(0);

		// perform the actual comparison
		for (var i = 0; i < v1parts.length; ++i) {
			if (v2parts.length == i) return 1;
			if (v1parts[i] == v2parts[i]) continue;
			if (v1parts[i] > v2parts[i]) return 1;
			else return -1;
		}
		if (v1parts.length != v2parts.length) return -1;
		return 0;
	};

	function __exists(){
		try {
			return !!window.FooGallery.utils; // does the namespace already exist?
		} catch(err) {
			return false;
		}
	}

	if (__exists()){
		// if it already exists always log a warning as there may be version conflicts as the following code always ensures the latest version is loaded
		if (utils.versionCompare(utils.version, window.FooGallery.utils.version) > 0){
			// if it exists but it's an old version replace it
			console.warn("An older version of FooGallery.utils (" + window.FooGallery.utils.version + ") already exists in the page, version " + utils.version + " will override it.");
			window.FooGallery.utils = utils;
		} else {
			// otherwise its a newer version so do nothing
			console.warn("A newer version of FooGallery.utils (" + window.FooGallery.utils.version + ") already exists in the page, version " + utils.version + " will not register itself.");
		}
	} else {
		// if it doesn't exist register it
		window.FooGallery.utils = utils;
	}

	// at this point there will always be a FooGallery.utils namespace registered to the global scope.

})(jQuery);
(function ($, _){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	/**
	 * @summary Contains common type checking utility methods.
	 * @memberof FooGallery.utils
	 * @namespace is
	 */
	_.is = {};

	/**
	 * @summary Checks if the `value` is an array.
	 * @memberof FooGallery.utils.is
	 * @function array
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is an array.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.array( [] ) ); // => true
	 * console.log( _is.array( null ) ); // => false
	 * console.log( _is.array( 123 ) ); // => false
	 * console.log( _is.array( "" ) ); // => false
	 */
	_.is.array = function (value) {
		return '[object Array]' === Object.prototype.toString.call(value);
	};

	/**
	 * @summary Checks if the `value` is a boolean.
	 * @memberof FooGallery.utils.is
	 * @function boolean
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is a boolean.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.boolean( true ) ); // => true
	 * console.log( _is.boolean( false ) ); // => true
	 * console.log( _is.boolean( "true" ) ); // => false
	 * console.log( _is.boolean( "false" ) ); // => false
	 * console.log( _is.boolean( 1 ) ); // => false
	 * console.log( _is.boolean( 0 ) ); // => false
	 */
	_.is.boolean = function (value) {
		return '[object Boolean]' === Object.prototype.toString.call(value);
	};

	/**
	 * @summary Checks if the `value` is an element.
	 * @memberof FooGallery.utils.is
	 * @function element
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is an element.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is,
	 * 	// create an element to test
	 * 	el = document.createElement("span");
	 *
	 * console.log( _is.element( el ) ); // => true
	 * console.log( _is.element( $(el) ) ); // => false
	 * console.log( _is.element( null ) ); // => false
	 * console.log( _is.element( {} ) ); // => false
	 */
	_.is.element = function (value) {
		return typeof HTMLElement === 'object'
			? value instanceof HTMLElement
			: !!value && typeof value === 'object' && value !== null && value.nodeType === 1 && typeof value.nodeName === 'string';
	};

	/**
	 * @summary Checks if the `value` is empty.
	 * @memberof FooGallery.utils.is
	 * @function empty
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is empty.
	 * @description The following values are considered to be empty by this method:
	 *
	 * <ul><!--
	 * --><li>`""`			- An empty string</li><!--
	 * --><li>`0`			- 0 as an integer</li><!--
	 * --><li>`0.0`		- 0 as a float</li><!--
	 * --><li>`[]`			- An empty array</li><!--
	 * --><li>`{}`			- An empty object</li><!--
	 * --><li>`$()`		- An empty jQuery object</li><!--
	 * --><li>`false`</li><!--
	 * --><li>`null`</li><!--
	 * --><li>`undefined`</li><!--
	 * --></ul>
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.empty( undefined ) ); // => true
	 * console.log( _is.empty( null ) ); // => true
	 * console.log( _is.empty( 0 ) ); // => true
	 * console.log( _is.empty( 0.0 ) ); // => true
	 * console.log( _is.empty( "" ) ); // => true
	 * console.log( _is.empty( [] ) ); // => true
	 * console.log( _is.empty( {} ) ); // => true
	 * console.log( _is.empty( 1 ) ); // => false
	 * console.log( _is.empty( 0.1 ) ); // => false
	 * console.log( _is.empty( "one" ) ); // => false
	 * console.log( _is.empty( ["one"] ) ); // => false
	 * console.log( _is.empty( { "name": "My Object" } ) ); // => false
	 */
	_.is.empty = function(value){
		if (_.is.undef(value) || value === null) return true;
		if (_.is.number(value) && value == 0) return true;
		if (_.is.boolean(value) && value === false) return true;
		if (_.is.string(value) && value.length === 0) return true;
		if (_.is.array(value) && value.length === 0) return true;
		if (_.is.jq(value) && value.length === 0) return true;
		if (_.is.hash(value)){
			for(var prop in value) {
				if(value.hasOwnProperty(prop))
					return false;
			}
			return true;
		}
		return false;
	};

	/**
	 * @summary Checks if the `value` is an error.
	 * @memberof FooGallery.utils.is
	 * @function error
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is an error.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is,
	 * 	// create some errors to test
	 * 	err1 = new Error("err1"),
	 * 	err2 = new SyntaxError("err2");
	 *
	 * console.log( _is.error( err1 ) ); // => true
	 * console.log( _is.error( err2 ) ); // => true
	 * console.log( _is.error( null ) ); // => false
	 * console.log( _is.error( 123 ) ); // => false
	 * console.log( _is.error( "" ) ); // => false
	 * console.log( _is.error( {} ) ); // => false
	 * console.log( _is.error( [] ) ); // => false
	 */
	_.is.error = function (value) {
		return '[object Error]' === Object.prototype.toString.call(value);
	};

	/**
	 * @summary Checks if the `value` is a function.
	 * @memberof FooGallery.utils.is
	 * @function fn
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is a function.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is,
	 * 	// create a function to test
	 * 	func = function(){};
	 *
	 * console.log( _is.fn( func ) ); // => true
	 * console.log( _is.fn( null ) ); // => false
	 * console.log( _is.fn( 123 ) ); // => false
	 * console.log( _is.fn( "" ) ); // => false
	 */
	_.is.fn = function (value) {
		return value === window.alert || '[object Function]' === Object.prototype.toString.call(value);
	};

	/**
	 * @summary Checks if the `value` is a hash.
	 * @memberof FooGallery.utils.is
	 * @function hash
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is a hash.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.hash( {"some": "prop"} ) ); // => true
	 * console.log( _is.hash( {} ) ); // => true
	 * console.log( _is.hash( window ) ); // => false
	 * console.log( _is.hash( document ) ); // => false
	 * console.log( _is.hash( "" ) ); // => false
	 * console.log( _is.hash( 123 ) ); // => false
	 */
	_.is.hash = function (value) {
		return _.is.object(value) && value.constructor === Object && !value.nodeType && !value.setInterval;
	};

	/**
	 * @summary Checks if the `value` is a jQuery object.
	 * @memberof FooGallery.utils.is
	 * @function jq
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is a jQuery object.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is,
	 * 	// create an element to test
	 * 	el = document.createElement("span");
	 *
	 * console.log( _is.jq( $(el) ) ); // => true
	 * console.log( _is.jq( $() ) ); // => true
	 * console.log( _is.jq( el ) ); // => false
	 * console.log( _is.jq( {} ) ); // => false
	 * console.log( _is.jq( null ) ); // => false
	 * console.log( _is.jq( 123 ) ); // => false
	 * console.log( _is.jq( "" ) ); // => false
	 */
	_.is.jq = function(value){
		return !_.is.undef($) && value instanceof $;
	};

	/**
	 * @summary Checks if the `value` is a number.
	 * @memberof FooGallery.utils.is
	 * @function number
	 * @param {*} value - The value to check.
	 * @returns {boolean}
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.number( 123 ) ); // => true
	 * console.log( _is.number( undefined ) ); // => false
	 * console.log( _is.number( null ) ); // => false
	 * console.log( _is.number( "" ) ); // => false
	 */
	_.is.number = function (value) {
		return '[object Number]' === Object.prototype.toString.call(value) && !isNaN(value);
	};

	/**
	 * @summary Checks if the `value` is an object.
	 * @memberof FooGallery.utils.is
	 * @function object
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the supplied `value` is an object.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.object( {"some": "prop"} ) ); // => true
	 * console.log( _is.object( {} ) ); // => true
	 * console.log( _is.object( window ) ); // => true
	 * console.log( _is.object( document ) ); // => true
	 * console.log( _is.object( undefined ) ); // => false
	 * console.log( _is.object( null ) ); // => false
	 * console.log( _is.object( "" ) ); // => false
	 * console.log( _is.object( 123 ) ); // => false
	 */
	_.is.object = function (value) {
		return '[object Object]' === Object.prototype.toString.call(value) && !_.is.undef(value) && value !== null;
	};

	/**
	 * @summary Checks if the `value` is a promise.
	 * @memberof FooGallery.utils.is
	 * @function promise
	 * @param {*} value - The object to check.
	 * @returns {boolean} `true` if the supplied `value` is an object.
	 * @description This is a simple check to determine if an object is a jQuery promise object. It simply checks the object has a `then` and `promise` function defined.
	 *
	 * The promise object is created as an object literal inside of `jQuery.Deferred`, it has no prototype, nor any other truly unique properties that could be used to distinguish it.
	 *
	 * This method should be a little more accurate than the internal jQuery one that simply checks for a `promise` function.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.promise( $.Deferred() ) ); // => true
	 * console.log( _is.promise( {} ) ); // => false
	 * console.log( _is.promise( undefined ) ); // => false
	 * console.log( _is.promise( null ) ); // => false
	 * console.log( _is.promise( "" ) ); // => false
	 * console.log( _is.promise( 123 ) ); // => false
	 */
	_.is.promise = function(value){
		return _.is.object(value) && _.is.fn(value.then) && _.is.fn(value.promise);
	};

	/**
	 * @summary Checks if the `value` is a valid CSS length.
	 * @memberof FooGallery.utils.is
	 * @function size
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the `value` is a number or CSS length.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.size( 80 ) ); // => true
	 * console.log( _is.size( "80px" ) ); // => true
	 * console.log( _is.size( "80em" ) ); // => true
	 * console.log( _is.size( "80%" ) ); // => true
	 * console.log( _is.size( {} ) ); // => false
	 * console.log( _is.size( undefined ) ); // => false
	 * console.log( _is.size( null ) ); // => false
	 * console.log( _is.size( "" ) ); // => false
	 * @see {@link https://developer.mozilla.org/en-US/docs/Web/CSS/length|&lt;length&gt; - CSS | MDN} for more information on CSS length values.
	 */
	_.is.size = function(value){
		if (!(_.is.string(value) && !_.is.empty(value)) && !_.is.number(value)) return false;
		return /^(auto|none|(?:[\d\.]*)+?(?:%|px|mm|q|cm|in|pt|pc|em|ex|ch|rem|vh|vw|vmin|vmax)?)$/.test(value);
	};

	/**
	 * @summary Checks if the `value` is a string.
	 * @memberof FooGallery.utils.is
	 * @function string
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the `value` is a string.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.string( "" ) ); // => true
	 * console.log( _is.string( undefined ) ); // => false
	 * console.log( _is.string( null ) ); // => false
	 * console.log( _is.string( 123 ) ); // => false
	 */
	_.is.string = function (value) {
		return '[object String]' === Object.prototype.toString.call(value);
	};

	/**
	 * @summary Checks if the `value` is `undefined`.
	 * @memberof FooGallery.utils.is
	 * @function undef
	 * @param {*} value - The value to check is undefined.
	 * @returns {boolean} `true` if the supplied `value` is `undefined`.
	 * @example {@run true}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * console.log( _is.undef( undefined ) ); // => true
	 * console.log( _is.undef( null ) ); // => false
	 * console.log( _is.undef( 123 ) ); // => false
	 * console.log( _is.undef( "" ) ); // => false
	 */
	_.is.undef = function (value) {
		return typeof value === 'undefined';
	};

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils
);
(function($, _, _is){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	/**
	 * @memberof FooGallery.utils
	 * @namespace fn
	 * @summary Contains common function utility methods.
	 */
	_.fn = {};

	var fnStr = Function.prototype.toString;

	/**
	 * @summary The regular expression to test if a function uses the `this._super` method applied by the {@link FooGallery.utils.fn.add} method.
	 * @memberof FooGallery.utils.fn
	 * @name CONTAINS_SUPER
	 * @type {RegExp}
	 * @default /\b_super\b/
	 * @readonly
	 * @description When the script is first loaded into the page this performs a quick check to see if the browser supports function decompilation. If it does the regular expression is set to match the expected `_super`, however if  function decompilation is not supported, the regular expression is set to match anything effectively making the test always return `true`.
	 * @example {@run true}
	 * // alias the FooGallery.utils.fn namespace
	 * var _fn = FooGallery.utils.fn;
	 *
	 * // create some functions to test
	 * function testFn1(){}
	 * function testFn2(){
	 * 	this._super();
	 * }
	 *
	 * console.log( _fn.CONTAINS_SUPER.test( testFn1 ) ); // => false
	 * console.log( _fn.CONTAINS_SUPER.test( testFn2 ) ); // => true
	 *
	 * // NOTE: in browsers that don't support functional decompilation both tests will return `true`
	 */
	_.fn.CONTAINS_SUPER = /xyz/.test(fnStr.call(function(){
		//noinspection JSUnresolvedVariable,BadExpressionStatementJS
		xyz;
	})) ? /\b_super\b/ : /.*/;

	/**
	 * @summary Adds or overrides the given method `name` on the `proto` using the supplied `fn`.
	 * @memberof FooGallery.utils.fn
	 * @function addOrOverride
	 * @param {Object} proto - The prototype to add the method to.
	 * @param {string} name - The name of the method to add, if this already exists the original will be exposed within the scope of the supplied `fn` as `this._super`.
	 * @param {function} fn - The function to add to the prototype, if this is overriding an existing method you can use `this._super` to access the original within its' scope.
	 * @description If the new method overrides a pre-existing one, this function will expose the overridden method as `this._super` within the new methods scope.
	 *
	 * This replaces having to write out the following to override a method and call its original:
	 *
	 * ```javascript
	 * var original = MyClass.prototype.someMethod;
	 * MyClass.prototype.someMethod = function(arg1, arg2){
	 * 	// execute the original
	 * 	original.call(this, arg1, arg2);
	 * };
	 * ```
	 *
	 * With the following:
	 *
	 * ```javascript
	 * FooGallery.utils.fn.addOrOverride( MyClass.prototype, "someMethod", function(arg1, arg2){
	 * 	// execute the original
	 * 	this._super(arg1, arg2);
	 * });
	 * ```
	 *
	 * This method is used by the {@link FooGallery.utils.Class} to implement the inheritance of individual methods.
	 * @example {@run true}
	 * // alias the FooGallery.utils.fn namespace
	 * var _fn = FooGallery.utils.fn;
	 *
	 * var proto = {
	 * 	write: function( message ){
	 * 		console.log( "Original#write: " + message );
	 * 	}
	 * };
	 *
	 * proto.write( "My message" ); // => "Original#write: My message"
	 *
	 * _fn.addOrOverride( proto, "write", function( message ){
	 * 	message = "Override#write: " + message;
	 * 	this._super( message );
	 * } );
	 *
	 * proto.write( "My message" ); // => "Original#write: Override#write: My message"
	 */
	_.fn.addOrOverride = function(proto, name, fn){
		if (!_is.object(proto) || !_is.string(name) || _is.empty(name) || !_is.fn(fn)) return;
		var _super = proto[name],
			wrap = _is.fn(_super) && _.fn.CONTAINS_SUPER.test(fnStr.call(fn));
		// only wrap the function if it overrides a method and makes use of `_super` within it's body.
		proto[name] = wrap ?
			(function (_super, fn) {
				// create a new wrapped that exposes the original method as `_super`
				return function () {
					var tmp = this._super;
					this._super = _super;
					var ret = fn.apply(this, arguments);
					this._super = tmp;
					return ret;
				};
			})(_super, fn) : fn;
	};

	/**
	 * @summary Use the `Function.prototype.apply` method on a class constructor using the `new` keyword.
	 * @memberof FooGallery.utils.fn
	 * @function apply
	 * @param {Object} klass - The class to create.
	 * @param {Array} [args=[]] - The arguments to pass to the constructor.
	 * @returns {function} The new instance of the `klass` created with the supplied `args`.
	 * @description When using the default `Function.prototype.apply` you can't use it on class constructors requiring the `new` keyword, this method allows us to do that.
	 * @example {@run true}
	 * // alias the FooGallery.utils.fn namespace
	 * var _fn = FooGallery.utils.fn;
	 *
	 * // create a class to test with
	 * function Test( name, value ){
	 * 	if ( !( this instanceof Test )){
	 * 		console.log( "Test instantiated without the `new` keyword." );
	 * 		return;
	 * 	}
	 * 	console.log( "Test: name = " + name + ", value = " + value );
	 * }
	 *
	 * Test.apply( Test, ["My name", "My value"] ); // => "Test instantiated without the `new` keyword."
	 * _fn.apply( Test, ["My name", "My value"] ); // => "Test: name = My name, value = My value"
	 */
	_.fn.apply = function(klass, args){
		args = _is.array(args) ? args : [];
		function Class() {
			return klass.apply(this, args);
		}
		Class.prototype = klass.prototype;
		//noinspection JSValidateTypes
		return new Class();
	};

	/**
	 * @summary Converts the default `arguments` object into a proper array.
	 * @memberof FooGallery.utils.fn
	 * @function arg2arr
	 * @param {Arguments} args - The arguments object to create an array from.
	 * @returns {Array}
	 * @description This method is simply a replacement for calling `Array.prototype.slice.call()` to create an array from an `arguments` object.
	 * @example {@run true}
	 * // alias the FooGallery.utils.fn namespace
	 * var _fn = FooGallery.utils.fn;
	 *
	 * function callMe(){
	 * 	var args = _fn.arg2arr(arguments);
	 * 	console.log( arguments instanceof Array ); // => false
	 * 	console.log( args instanceof Array ); // => true
	 * 	console.log( args ); // => [ "arg1", "arg2" ]
	 * }
	 *
	 * callMe("arg1", "arg2");
	 */
	_.fn.arg2arr = function(args){
		return Array.prototype.slice.call(args);
	};

	/**
	 * @summary Checks the given `value` and ensures a function is returned.
	 * @memberof FooGallery.utils.fn
	 * @function check
	 * @param {?Object} thisArg=window - The `this` keyword within the returned function, if the supplied value is not an object this defaults to the `window`.
	 * @param {*} value - The value to check, if not a function or the name of one then the `def` value is automatically returned.
	 * @param {function} [def=jQuery.noop] - A default function to use if the `value` is not resolved to a function.
	 * @param {Object} [ctx=window] - If the `value` is a string this is supplied to the {@link FooGallery.utils.fn.fetch} method as the content to retrieve the function from.
	 * @returns {function} A function that ensures the correct context is applied when executed.
	 * @description This function is primarily used to check the value of a callback option that could be supplied as either a function or a string.
	 *
	 * When just the function name is supplied this method uses the {@link FooGallery.utils.fn.fetch} method to resolve and wrap it to ensure when it's called the correct context is applied.
	 *
	 * Being able to resolve a function from a name allows callbacks to be easily set even through data attributes as you can just supply the full function name as a string and then use this method to retrieve the actual function.
	 * @example {@run true}
	 * // alias the FooGallery.utils.fn namespace
	 * var _fn = FooGallery.utils.fn;
	 *
	 * // a simple `api` with a `sendMessage` function
	 * window.api = {
	 * 	sendMessage: function(){
	 * 		this.write( "window.api.sendMessage" );
	 * 	},
	 * 	child: {
	 * 		api: {
	 * 			sendMessage: function(){
	 * 				this.write( "window.api.child.api.sendMessage" );
	 * 			}
	 * 		}
	 * 	}
	 * };
	 *
	 * // a default function to use in case the check fails
	 * var def = function(){
	 * 	this.write( "default" );
	 * };
	 *
	 * // an object to use as the `this` object within the scope of the checked functions
	 * var thisArg = {
	 * 	write: function( message ){
	 * 		console.log( message );
	 * 	}
	 * };
	 *
	 * // check the value and return a wrapped function ensuring the correct context.
	 * var fn = _fn.check( thisArg, null, def );
	 * fn(); // => "default"
	 *
	 * fn = _fn.check( thisArg, "api.doesNotExist", def );
	 * fn(); // => "default"
	 *
	 * fn = _fn.check( thisArg, api.sendMessage, def );
	 * fn(); // => "window.api.sendMessage"
	 *
	 * fn = _fn.check( thisArg, "api.sendMessage", def );
	 * fn(); // => "window.api.sendMessage"
	 *
	 * fn = _fn.check( thisArg, "api.sendMessage", def, window.api.child );
	 * fn(); // => "window.api.child.api.sendMessage"
	 */
	_.fn.check = function(thisArg, value, def, ctx){
		def = _is.fn(def) ? def : $.noop;
		thisArg = _is.object(thisArg) ? thisArg : window;
		function wrap(fn){
			return function(){
				return fn.apply(thisArg, arguments);
			};
		}
		value = _is.string(value) ? _.fn.fetch(value, ctx) : value;
		return _is.fn(value) ? wrap(value) : wrap(def);
	};

	/**
	 * @summary Fetches a function given its `name`.
	 * @memberof FooGallery.utils.fn
	 * @function fetch
	 * @param {string} name - The name of the function to fetch. This can be a `.` notated name.
	 * @param {Object} [ctx=window] - The context to retrieve the function from, defaults to the `window` object.
	 * @returns {?function} `null` if a function with the given name is not found within the context.
	 * @example {@run true}
	 * // alias the FooGallery.utils.fn namespace
	 * var _fn = FooGallery.utils.fn;
	 *
	 * // create a dummy `api` with a `sendMessage` function to test
	 * window.api = {
	 * 	sendMessage: function( message ){
	 * 		console.log( "api.sendMessage: " + message );
	 * 	}
	 * };
	 *
	 * // the below shows 3 different ways to fetch the `sendMessage` function
	 * var send1 = _fn.fetch( "api.sendMessage" );
	 * var send2 = _fn.fetch( "api.sendMessage", window );
	 * var send3 = _fn.fetch( "sendMessage", window.api );
	 *
	 * // all the retrieved methods should be the same
	 * console.log( send1 === send2 && send2 === send3 ); // => true
	 *
	 * // check if the function was found
	 * if ( send1 != null ){
	 * 	send1( "My message" ); // => "api.sendMessage: My message"
	 * }
	 */
	_.fn.fetch = function(name, ctx){
		if (!_is.string(name) || _is.empty(name)) return null;
		ctx = _is.object(ctx) ? ctx : window;
		$.each(name.split('.'), function(i, part){
			if (ctx[part]) ctx = ctx[part];
			else return false;
		});
		return _is.fn(ctx) ? ctx : null;
	};

	/**
	 * @summary Enqueues methods using the given `name` from all supplied `objects` and executes each in order with the given arguments.
	 * @memberof FooGallery.utils.fn
	 * @function enqueue
	 * @param {Array.<Object>} objects - The objects to call the method on.
	 * @param {string} name - The name of the method to execute.
	 * @param {*} [arg1] - The first argument to call the method with.
	 * @param {...*} [argN] - Any additional arguments for the method.
	 * @returns {Promise} If `resolved` the first argument supplied to any success callbacks is an array of all returned value(s). These values are encapsulated within their own array as if the method returned a promise it could be resolved with more than one argument.
	 *
	 * If `rejected` any fail callbacks are supplied the arguments the promise was rejected with plus an additional one appended by this method, an array of all objects that have already had their methods run. This allows you to perform rollback operations if required after a failure. The last object in this array would contain the method that raised the error.
	 * @description This method allows an array of `objects` that implement a common set of methods to be executed in a supplied order. Each method in the queue is only executed after the successful completion of the previous. Success is evaluated as the method did not throw an error and if it returned a promise it was resolved.
	 *
	 * An example of this being used within the plugin is the loading and execution of methods on the various components. Using this method ensures components are loaded and have their methods executed in a static order regardless of when they were registered with the plugin or if the method is async. This way if `ComponentB`'s `preinit` relies on properties set in `ComponentA`'s `preinit` method you can register `ComponentB` with a lower priority than `ComponentA` and you can be assured `ComponentA`'s `preinit` completed successfully before `ComponentB`'s `preinit` is called event if it performs an async operation.
	 * @example {@caption Shows a basic example of how you can use this method.}{@run true}
	 * // alias the FooGallery.utils.fn namespace
	 * var _fn = FooGallery.utils.fn;
	 *
	 * // create some dummy objects that implement the same members or methods.
	 * var obj1 = {
	 * 	"name": "obj1",
	 * 	"appendName": function(str){
	 * 		console.log( "Executing obj1.appendName..." );
	 * 		return str + this.name;
	 * 	}
	 * };
	 *
	 * // this objects `appendName` method returns a promise
	 * var obj2 = {
	 * 	"name": "obj2",
	 * 	"appendName": function(str){
	 * 		console.log( "Executing obj2.appendName..." );
	 * 		var self = this;
	 * 		return $.Deferred(function(def){
	 *			// use a setTimeout to delay execution
	 *			setTimeout(function(){
	 *					def.resolve(str + self.name);
	 *			}, 300);
	 * 		});
	 * 	}
	 * };
	 *
	 * // this objects `appendName` method is only executed once obj2's promise is resolved
	 * var obj3 = {
	 * 	"name": "obj3",
	 * 	"appendName": function(str){
	 * 		console.log( "Executing obj3.appendName..." );
	 * 		return str + this.name;
	 * 	}
	 * };
	 *
	 * _fn.enqueue( [obj1, obj2, obj3], "appendName", "modified_by:" ).then(function(results){
	 * 	console.log( results ); // => [ [ "modified_by:obj1" ], [ "modified_by:obj2" ], [ "modified_by:obj3" ] ]
	 * });
	 * @example {@caption If an error is thrown by one of the called methods or it returns a promise that is rejected, execution is halted and any fail callbacks are executed. The last argument is an array of objects that have had their methods run, the last object within this array is the one that raised the error.}{@run true}
	 * // alias the FooGallery.utils.fn namespace
	 * var _fn = FooGallery.utils.fn;
	 *
	 * // create some dummy objects that implement the same members or methods.
	 * var obj1 = {
	 * 	"name": "obj1",
	 * 	"last": null,
	 * 	"appendName": function(str){
	 * 		console.log( "Executing obj1.appendName..." );
	 * 		return this.last = str + this.name;
	 * 	},
	 * 	"rollback": function(){
	 * 		console.log( "Executing obj1.rollback..." );
	 * 		this.last = null;
	 * 	}
	 * };
	 *
	 * // this objects `appendName` method throws an error
	 * var obj2 = {
	 * 	"name": "obj2",
	 * 	"last": null,
	 * 	"appendName": function(str){
	 * 		console.log( "Executing obj2.appendName..." );
	 * 		//throw new Error("Oops, something broke.");
	 * 		var self = this;
	 * 		return $.Deferred(function(def){
	 *			// use a setTimeout to delay execution
	 *			setTimeout(function(){
	 *					self.last = str + self.name;
	 *					def.reject(Error("Oops, something broke."));
	 *			}, 300);
	 * 		});
	 * 	},
	 * 	"rollback": function(){
	 * 		console.log( "Executing obj2.rollback..." );
	 * 		this.last = null;
	 * 	}
	 * };
	 *
	 * // this objects `appendName` and `rollback` methods are never executed
	 * var obj3 = {
	 * 	"name": "obj3",
	 * 	"last": null,
	 * 	"appendName": function(str){
	 * 		console.log( "Executing obj3.appendName..." );
	 * 		return this.last = str + this.name;
	 * 	},
	 * 	"rollback": function(){
	 * 		console.log( "Executing obj3.rollback..." );
	 * 		this.last = null;
	 * 	}
	 * };
	 *
	 * _fn.enqueue( [obj1, obj2, obj3], "appendName", "modified_by:" ).fail(function(err, run){
	 * 	console.log( err.message ); // => "Oops, something broke."
	 * 	console.log( run ); // => [ {"name":"obj1","last":"modified_by:obj1"}, {"name":"obj2","last":"modified_by:obj2"} ]
	 * 	var guilty = run[run.length - 1];
	 * 	console.log( "Error thrown by: " + guilty.name ); // => "obj2"
	 * 	run.reverse(); // reverse execution when rolling back to avoid dependency issues
	 * 	return _fn.enqueue( run, "rollback" ).then(function(){
	 * 		console.log( "Error handled and rollback performed." );
	 * 		console.log( run ); // => [ {"name":"obj1","last":null}, {"name":"obj2","last":null} ]
	 * 	});
	 * });
	 */
	_.fn.enqueue = function(objects, name, arg1, argN){
		var args = _.fn.arg2arr(arguments), // get an array of all supplied arguments
			def = $.Deferred(), // the main deferred object for the function
			queue = $.Deferred(), // the deferred object to use as an queue
			promise = queue.promise(), // used to register component methods for execution
			results = [], // stores the results of each method to be returned by the main deferred
			run = [], // stores each object once its' method has been run
			first = true; // whether or not this is the first resolve callback

		// take the objects and name parameters out of the args array
		objects = args.shift();
		name = args.shift();

		// safely execute a function, catch any errors and reject the deferred if required.
		function safe(obj, method){
			try {
				run.push(obj);
				return method.apply(obj, args);
			} catch(err) {
				def.reject(err, run);
				return def;
			}
		}

		// loop through all the supplied objects
		$.each(objects, function(i, obj){
			// if the obj has a function with the supplied name
			if (_is.fn(obj[name])){
				// then register the method in the callback queue
				promise = promise.then(function(){
					// only register the result if this is not the first resolve callback, the first is triggered by this function kicking off the queue
					if (!first){
						var resolveArgs = _.fn.arg2arr(arguments);
						results.push(resolveArgs);
					}
					first = false;
					// execute the method and return it's result, if the result is a promise
					// the next method will only be executed once it's resolved
					return safe(obj, obj[name]);
				});
			}
		});

		// add one last callback to catch the final result
		promise.then(function(){
			// only register the result if this is not the first resolve callback
			if (!first){
				var resolveArgs = _.fn.arg2arr(arguments);
				results.push(resolveArgs);
			}
			first = false;
			// resolve the main deferred with the array of all the method results
			def.resolve(results);
		});

		// hook into failures and ensure the run array is appended to the args
		promise.fail(function(){
			var rejectArgs = _.fn.arg2arr(arguments);
			rejectArgs.push(run);
			def.reject.apply(def, rejectArgs);
		});

		// kick off the queue
		queue.resolve();

		return def.promise();
	};

	/**
	 * @summary Waits for the outcome of all promises regardless of failure and resolves supplying the results of just those that succeeded.
	 * @memberof FooGallery.utils.fn
	 * @function when
	 * @param {Promise[]} promises - The array of promises to wait for.
	 * @returns {Promise}
	 */
	_.fn.when = function(promises){
		if (!_is.array(promises) || _is.empty(promises)) return $.when();
		var d = $.Deferred(), results = [], remaining = promises.length;
		for(var i = 0; i < promises.length; i++){
			promises[i].then(function(res){
				results.push(res); // on success, add to results
			}).always(function(){
				remaining--; // always mark as finished
				if(!remaining) d.resolve(results);
			})
		}
		return d.promise(); // return a promise on the remaining values
	};

	/**
	 * @summary Return a promise rejected using the supplied args.
	 * @memberof FooGallery.utils.fn
	 * @function rejectWith
	 * @param {*} [arg1] - The first argument to reject the promise with.
	 * @param {...*} [argN] - Any additional arguments to reject the promise with.
	 * @returns {Promise}
	 */
	_.fn.rejectWith = function(arg1, argN){
		var def = $.Deferred(), args = _.fn.arg2arr(arguments);
		return def.reject.apply(def, args).promise();
	};

	/**
	 * @summary Return a promise resolved using the supplied args.
	 * @memberof FooGallery.utils.fn
	 * @function resolveWith
	 * @param {*} [arg1] - The first argument to resolve the promise with.
	 * @param {...*} [argN] - Any additional arguments to resolve the promise with.
	 * @returns {Promise}
	 */
	_.fn.resolveWith = function(arg1, argN){
		var def = $.Deferred(), args = _.fn.arg2arr(arguments);
		return def.resolve.apply(def, args).promise();
	};

	/**
	 * @summary A resolved promise object.
	 * @memberof FooGallery.utils.fn
	 * @name resolved
	 * @type {Promise}
	 */
	_.fn.resolved = $.Deferred().resolve().promise();

	/**
	 * @summary A rejected promise object.
	 * @memberof FooGallery.utils.fn
	 * @name resolved
	 * @type {Promise}
	 */
	_.fn.rejected = $.Deferred().reject().promise();

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is
);
(function(_, _is){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	/**
	 * @summary Contains common url utility methods.
	 * @memberof FooGallery.utils
	 * @namespace url
	 */
	_.url = {};

	// used for parsing a url into it's parts.
	var _a = document.createElement('a');

	/**
	 * @summary Parses the supplied url into an object containing it's component parts.
	 * @memberof FooGallery.utils.url
	 * @function parts
	 * @param {string} url - The url to parse.
	 * @returns {FooGallery.utils.url~Parts}
	 * @example {@run true}
	 * // alias the FooGallery.utils.url namespace
	 * var _url = FooGallery.utils.url;
	 *
	 * console.log( _url.parts( "http://example.com/path/?param=true#something" ) ); // => {"hash":"#something", ...}
	 */
	_.url.parts = function(url){
		_a.href = url;
		return {
			hash: _a.hash, host: _a.host, hostname: _a.hostname, href: _a.href,
			origin: _a.origin, pathname: _a.pathname, port: _a.port,
			protocol: _a.protocol, search: _a.search
		};
	};

	/**
	 * @summary Given a <code>url</code> that could be relative or full this ensures a full url is returned.
	 * @memberof FooGallery.utils.url
	 * @function full
	 * @param {string} url - The url to ensure is full.
	 * @returns {?string} `null` if the given `path` is not a string or empty.
	 * @description Given a full url this will simply return it however if given a relative url this will create a full url using the current location to fill in the blanks.
	 * @example {@run true}
	 * // alias the FooGallery.utils.url namespace
	 * var _url = FooGallery.utils.url;
	 *
	 * console.log( _url.full( "http://example.com/path/" ) ); // => "http://example.com/path/"
	 * console.log( _url.full( "/path/" ) ); // => "{protocol}//{host}/path/"
	 * console.log( _url.full( "path/" ) ); // => "{protocol}//{host}/{pathname}/path/"
	 * console.log( _url.full( "../path/" ) ); // => "{protocol}//{host}/{calculated pathname}/path/"
	 * console.log( _url.full() ); // => null
	 * console.log( _url.full( 123 ) ); // => null
	 */
	_.url.full = function(url){
		if (!_is.string(url) || _is.empty(url)) return null;
		_a.href = url;
		return _a.href;
	};

	/**
	 * @summary Gets or sets a parameter in the given <code>search</code> string.
	 * @memberof FooGallery.utils.url
	 * @function param
	 * @param {string} search - The search string to use (usually `location.search`).
	 * @param {string} key - The key of the parameter.
	 * @param {string} [value] - The value to set for the parameter. If not provided the current value for the `key` is returned.
	 * @returns {?string} The value of the `key` in the given `search` string if no `value` is supplied or `null` if the `key` does not exist.
	 * @returns {string} A modified `search` string if a `value` is supplied.
	 * @example <caption>Shows how to retrieve a parameter value from a search string.</caption>{@run true}
	 * // alias the FooGallery.utils.url namespace
	 * var _url = FooGallery.utils.url,
	 * 	// create a search string to test
	 * 	search = "?wmode=opaque&autoplay=1";
	 *
	 * console.log( _url.param( search, "wmode" ) ); // => "opaque"
	 * console.log( _url.param( search, "autoplay" ) ); // => "1"
	 * console.log( _url.param( search, "nonexistent" ) ); // => null
	 * @example <caption>Shows how to set a parameter value in the given search string.</caption>{@run true}
	 * // alias the FooGallery.utils.url namespace
	 * var _url = FooGallery.utils.url,
	 * 	// create a search string to test
	 * 	search = "?wmode=opaque&autoplay=1";
	 *
	 * console.log( _url.param( search, "wmode", "window" ) ); // => "?wmode=window&autoplay=1"
	 * console.log( _url.param( search, "autoplay", "0" ) ); // => "?wmode=opaque&autoplay=0"
	 * console.log( _url.param( search, "v", "2" ) ); // => "?wmode=opaque&autoplay=1&v=2"
	 */
	_.url.param = function(search, key, value){
		if (!_is.string(search) || _is.empty(search) || !_is.string(key) || _is.empty(key)) return search;
		var regex, match, result, param;
		if (_is.undef(value)){
			regex = new RegExp('[?|&]' + key + '=([^&;]+?)(&|#|;|$)'); // regex to match the key and it's value but only capture the value
			match = regex.exec(search) || [,""]; // match the param otherwise return an empty string match
			result = match[1].replace(/\+/g, '%20'); // replace any + character's with spaces
			return _is.string(result) && !_is.empty(result) ? decodeURIComponent(result) : null; // decode the result otherwise return null
		}
		if (value === "" || value === null){
			regex = new RegExp('^([^#]*\?)(([^#]*)&)?' + key + '(\=[^&#]*)?(&|#|$)');
			result = search.replace(regex, '$1$3$5').replace(/^([^#]*)((\?)&|\?(#|$))/,'$1$3$4');
		} else {
			regex = new RegExp('([?&])' + key + '[^&]*'); // regex to match the key and it's current value but only capture the preceding ? or & char
			param = key + '=' + encodeURIComponent(value);
			result = search.replace(regex, '$1' + param); // replace any existing instance of the key with the new value
			// If nothing was replaced, then add the new param to the end
			if (result === search && !regex.test(result)) { // if no replacement occurred and the parameter is not currently in the result then add it
				result += '&' + param;
			}
		}
		return result;
	};

	//######################
	//## Type Definitions ##
	//######################

	/**
	 * @summary A plain JavaScript object returned by the {@link FooGallery.utils.url.parts} method.
	 * @typedef {Object} FooGallery.utils.url~Parts
	 * @property {string} hash - A string containing a `#` followed by the fragment identifier of the URL.
	 * @property {string} host - A string containing the host, that is the hostname, a `:`, and the port of the URL.
	 * @property {string} hostname - A string containing the domain of the URL.
	 * @property {string} href - A string containing the entire URL.
	 * @property {string} origin - A string containing the canonical form of the origin of the specific location.
	 * @property {string} pathname - A string containing an initial `/` followed by the path of the URL.
	 * @property {string} port - A string containing the port number of the URL.
	 * @property {string} protocol - A string containing the protocol scheme of the URL, including the final `:`.
	 * @property {string} search - A string containing a `?` followed by the parameters of the URL. Also known as "querystring".
	 * @see {@link FooGallery.utils.url.parts} for example usage.
	 */

})(
	// dependencies
	FooGallery.utils,
	FooGallery.utils.is
);
(function (_, _is, _fn) {
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	/**
	 * @summary Contains common string utility methods.
	 * @memberof FooGallery.utils
	 * @namespace str
	 */
	_.str = {};

	/**
	 * @summary Converts the given `target` to camel case.
	 * @memberof FooGallery.utils.str
	 * @function camel
	 * @param {string} target - The string to camel case.
	 * @returns {string}
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str;
	 *
	 * console.log( _str.camel( "max-width" ) ); // => "maxWidth"
	 * console.log( _str.camel( "max--width" ) ); // => "maxWidth"
	 * console.log( _str.camel( "max Width" ) ); // => "maxWidth"
	 * console.log( _str.camel( "Max_width" ) ); // => "maxWidth"
	 * console.log( _str.camel( "MaxWidth" ) ); // => "maxWidth"
	 * console.log( _str.camel( "Abbreviations like CSS are left intact" ) ); // => "abbreviationsLikeCSSAreLeftIntact"
	 */
	_.str.camel = function (target) {
		if (_is.empty(target)) return target;
		if (target.toUpperCase() === target) return target.toLowerCase();
		return target.replace(/^([A-Z])|[-\s_]+(\w)/g, function (match, p1, p2) {
			if (_is.string(p2)) return p2.toUpperCase();
			return p1.toLowerCase();
		});
	};

	/**
	 * @summary Checks if the `target` contains the given `substr`.
	 * @memberof FooGallery.utils.str
	 * @function contains
	 * @param {string} target - The string to check.
	 * @param {string} substr - The string to check for.
	 * @param {boolean} [ignoreCase=false] - Whether or not to ignore casing when performing the check.
	 * @returns {boolean} `true` if the `target` contains the given `substr`.
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str,
	 * 	// create a string to test
	 * 	target = "To be, or not to be, that is the question.";
	 *
	 * console.log( _str.contains( target, "To be" ) ); // => true
	 * console.log( _str.contains( target, "question" ) ); // => true
	 * console.log( _str.contains( target, "no" ) ); // => true
	 * console.log( _str.contains( target, "nonexistent" ) ); // => false
	 * console.log( _str.contains( target, "TO BE" ) ); // => false
	 * console.log( _str.contains( target, "TO BE", true ) ); // => true
	 */
	_.str.contains = function (target, substr, ignoreCase) {
		if (!_is.string(target) || _is.empty(target) || !_is.string(substr) || _is.empty(substr)) return false;
		return substr.length <= target.length
			&& (!!ignoreCase ? target.toUpperCase().indexOf(substr.toUpperCase()) : target.indexOf(substr)) !== -1;
	};

	/**
	 * @summary Checks if the `target` contains the given `word`.
	 * @memberof FooGallery.utils.str
	 * @function containsWord
	 * @param {string} target - The string to check.
	 * @param {string} word - The word to check for.
	 * @param {boolean} [ignoreCase=false] - Whether or not to ignore casing when performing the check.
	 * @returns {boolean} `true` if the `target` contains the given `word`.
	 * @description This method differs from {@link FooGallery.utils.str.contains} in that it searches for whole words by splitting the `target` string on word boundaries (`\b`) and then comparing the individual parts.
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str,
	 * 	// create a string to test
	 * 	target = "To be, or not to be, that is the question.";
	 *
	 * console.log( _str.containsWord( target, "question" ) ); // => true
	 * console.log( _str.containsWord( target, "no" ) ); // => false
	 * console.log( _str.containsWord( target, "NOT" ) ); // => false
	 * console.log( _str.containsWord( target, "NOT", true ) ); // => true
	 * console.log( _str.containsWord( target, "nonexistent" ) ); // => false
	 */
	_.str.containsWord = function(target, word, ignoreCase){
		if (!_is.string(target) || _is.empty(target) || !_is.string(word) || _is.empty(word) || target.length < word.length)
			return false;
		var parts = target.split(/\W/);
		for (var i = 0, len = parts.length; i < len; i++){
			if (ignoreCase ? parts[i].toUpperCase() == word.toUpperCase() : parts[i] == word) return true;
		}
		return false;
	};

	/**
	 * @summary Checks if the `target` ends with the given `substr`.
	 * @memberof FooGallery.utils.str
	 * @function endsWith
	 * @param {string} target - The string to check.
	 * @param {string} substr - The substr to check for.
	 * @returns {boolean} `true` if the `target` ends with the given `substr`.
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str;
	 *
	 * console.log( _str.endsWith( "something", "g" ) ); // => true
	 * console.log( _str.endsWith( "something", "ing" ) ); // => true
	 * console.log( _str.endsWith( "something", "no" ) ); // => false
	 */
	_.str.endsWith = function (target, substr) {
		if (!_is.string(target) || _is.empty(target) || !_is.string(substr) || _is.empty(substr)) return target == substr;
		return target.slice(target.length - substr.length) == substr;
	};

	/**
	 * @summary Escapes the `target` for use in a regular expression.
	 * @memberof FooGallery.utils.str
	 * @function escapeRegExp
	 * @param {string} target - The string to escape.
	 * @returns {string}
	 * @see {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions|Regular Expressions: Using Special Characters - JavaScript | MDN}
	 */
	_.str.escapeRegExp = function(target){
		if (_is.empty(target)) return target;
		return target.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
	};

	/**
	 * @summary Generates a 32 bit FNV-1a hash from the given `target`.
	 * @memberof FooGallery.utils.str
	 * @function fnv1a
	 * @param {string} target - The string to generate a hash from.
	 * @returns {?number} `null` if the `target` is not a string or empty otherwise a 32 bit FNV-1a hash.
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str;
	 *
	 * console.log( _str.fnv1a( "Some string to generate a hash for." ) ); // => 207568994
	 * console.log( _str.fnv1a( "Some string to generate a hash for" ) ); // => 1350435704
	 * @see {@link https://en.wikipedia.org/wiki/Fowler%E2%80%93Noll%E2%80%93Vo_hash_function|Fowler–Noll–Vo hash function}
	 */
	_.str.fnv1a = function(target){
		if (!_is.string(target) || _is.empty(target)) return null;
		var i, l, hval = 0x811c9dc5;
		for (i = 0, l = target.length; i < l; i++) {
			hval ^= target.charCodeAt(i);
			hval += (hval << 1) + (hval << 4) + (hval << 7) + (hval << 8) + (hval << 24);
		}
		return hval >>> 0;
	};

	/**
	 * @summary Returns the remainder of the `target` split on the first index of the given `substr`.
	 * @memberof FooGallery.utils.str
	 * @function from
	 * @param {string} target - The string to split.
	 * @param {string} substr - The substring to split on.
	 * @returns {?string} `null` if the given `substr` does not exist within the `target`.
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str,
	 * 	// create a string to test
	 * 	target = "To be, or not to be, that is the question.";
	 *
	 * console.log( _str.from( target, "no" ) ); // => "t to be, that is the question."
	 * console.log( _str.from( target, "that" ) ); // => " is the question."
	 * console.log( _str.from( target, "question" ) ); // => "."
	 * console.log( _str.from( target, "nonexistent" ) ); // => null
	 */
	_.str.from = function (target, substr) {
		if (!_is.string(target) || _is.empty(target) || !_is.string(substr) || _is.empty(substr)) return null;
		return _.str.contains(target, substr) ? target.substring(target.indexOf(substr) + substr.length) : null;
	};

	/**
	 * @summary Joins any number of strings using the given `separator`.
	 * @memberof FooGallery.utils.str
	 * @function join
	 * @param {string} separator - The separator to use to join the strings.
	 * @param {string} part - The first string to join.
	 * @param {...string} [partN] - Any number of additional strings to join.
	 * @returns {?string}
	 * @description This method differs from using the standard `Array.prototype.join` function to join strings in that it ignores empty parts and checks to see if each starts with the supplied `separator`. If the part starts with the `separator` it is removed before appending it to the final result.
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str;
	 *
	 * console.log( _str.join( "_", "all", "in", "one" ) ); // => "all_in_one"
	 * console.log( _str.join( "_", "all", "_in", "one" ) ); // => "all_in_one"
	 * console.log( _str.join( "/", "http://", "/example.com/", "/path/to/image.png" ) ); // => "http://example.com/path/to/image.png"
	 * console.log( _str.join( "/", "http://", "/example.com", "/path/to/image.png" ) ); // => "http://example.com/path/to/image.png"
	 * console.log( _str.join( "/", "http://", "example.com", "path/to/image.png" ) ); // => "http://example.com/path/to/image.png"
	 */
	_.str.join = function(separator, part, partN){
		if (!_is.string(separator) || !_is.string(part)) return null;
		var parts = _fn.arg2arr(arguments);
		separator = parts.shift();
		var i, l, result = parts.shift();
		for (i = 0, l = parts.length; i < l; i++){
			part = parts[i];
			if (_is.empty(part)) continue;
			if (_.str.endsWith(result, separator)){
				result = result.slice(0, result.length-separator.length);
			}
			if (_.str.startsWith(part, separator)){
				part = part.slice(separator.length);
			}
			result += separator + part;
		}
		return result;
	};

	/**
	 * @summary Checks if the `target` starts with the given `substr`.
	 * @memberof FooGallery.utils.str
	 * @function startsWith
	 * @param {string} target - The string to check.
	 * @param {string} substr - The substr to check for.
	 * @returns {boolean} `true` if the `target` starts with the given `substr`.
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str;
	 *
	 * console.log( _str.startsWith( "something", "s" ) ); // => true
	 * console.log( _str.startsWith( "something", "some" ) ); // => true
	 * console.log( _str.startsWith( "something", "no" ) ); // => false
	 */
	_.str.startsWith = function (target, substr) {
		if (_is.empty(target) || _is.empty(substr)) return false;
		return target.slice(0, substr.length) == substr;
	};

	/**
	 * @summary Returns the first part of the `target` split on the first index of the given `substr`.
	 * @memberof FooGallery.utils.str
	 * @function until
	 * @param {string} target - The string to split.
	 * @param {string} substr - The substring to split on.
	 * @returns {string} The `target` if the `substr` does not exist.
	 * @example {@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str,
	 * 	// create a string to test
	 * 	target = "To be, or not to be, that is the question.";
	 *
	 * console.log( _str.until( target, "no" ) ); // => "To be, or "
	 * console.log( _str.until( target, "that" ) ); // => "To be, or not to be, "
	 * console.log( _str.until( target, "question" ) ); // => "To be, or not to be, that is the "
	 * console.log( _str.until( target, "nonexistent" ) ); // => "To be, or not to be, that is the question."
	 */
	_.str.until = function (target, substr) {
		if (_is.empty(target) || _is.empty(substr)) return target;
		return _.str.contains(target, substr) ? target.substring(0, target.indexOf(substr)) : target;
	};

	/**
	 * @summary A basic string formatter that can use both index and name based placeholders but handles only string or number replacements.
	 * @memberof FooGallery.utils.str
	 * @function format
	 * @param {string} target - The format string containing any placeholders to replace.
	 * @param {string|number|Object|Array} arg1 - The first value to format the target with. If an object is supplied it's properties are used to match named placeholders. If an array, string or number is supplied it's values are used to match any index placeholders.
	 * @param {...(string|number)} [argN] - Any number of additional strings or numbers to format the target with.
	 * @returns {string} The string formatted with the supplied arguments.
	 * @description This method allows you to supply the replacements as an object when using named placeholders or as an array or additional arguments when using index placeholders.
	 *
	 * This does not perform a simultaneous replacement of placeholders, which is why it's referred to as a basic formatter. This means replacements that contain placeholders within there value could end up being replaced themselves as seen in the last example.
	 * @example {@caption The following shows how to use index placeholders.}{@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str,
	 * 	// create a format string using index placeholders
	 * 	format = "Hello, {0}, are you feeling {1}?";
	 *
	 * console.log( _str.format( format, "Steve", "OK" ) ); // => "Hello, Steve, are you feeling OK?"
	 * // or
	 * console.log( _str.format( format, [ "Steve", "OK" ] ) ); // => "Hello, Steve, are you feeling OK?"
	 * @example {@caption While the above works perfectly fine the downside is that the placeholders provide no clues as to what should be supplied as a replacement value, this is were supplying an object and using named placeholders steps in.}{@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str,
	 * 	// create a format string using named placeholders
	 * 	format = "Hello, {name}, are you feeling {adjective}?";
	 *
	 * console.log( _str.format( format, {name: "Steve", adjective: "OK"} ) ); // => "Hello, Steve, are you feeling OK?"
	 * @example {@caption The following demonstrates the issue with not performing a simultaneous replacement of placeholders.}{@run true}
	 * // alias the FooGallery.utils.str namespace
	 * var _str = FooGallery.utils.str;
	 *
	 * console.log( _str.format("{0}{1}", "{1}", "{0}") ); // => "{0}{0}"
	 *
	 * // If the replacement happened simultaneously the result would be "{1}{0}" but this method executes
	 * // replacements synchronously as seen below:
	 *
	 * // "{0}{1}".replace( "{0}", "{1}" )
	 * // => "{1}{1}".replace( "{1}", "{0}" )
	 * // => "{0}{0}"
	 */
	_.str.format = function (target, arg1, argN){
		var args = _fn.arg2arr(arguments);
		target = args.shift(); // remove the target from the args
		if (_is.empty(target) || _is.empty(args)) return target;
		if (args.length === 1 && (_is.array(args[0]) || _is.object(args[0]))){
			args = args[0];
		}
		for (var arg in args){
			target = target.replace(new RegExp("\\{" + arg + "\\}", "gi"), args[arg]);
		}
		return target;
	};

})(
	// dependencies
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn
);
(function($, _, _is, _fn, _str){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	/**
	 * @summary Contains common object utility methods.
	 * @memberof FooGallery.utils
	 * @namespace obj
	 */
	_.obj = {};

	// used by the obj.create method
	var Obj = function () {};
	/**
	 * @summary Creates a new object with the specified prototype.
	 * @memberof FooGallery.utils.obj
	 * @function create
	 * @param {object} proto - The object which should be the prototype of the newly-created object.
	 * @returns {object} A new object with the specified prototype.
	 * @description This is a basic implementation of the {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/create|Object.create} method.
	 */
	_.obj.create = function (proto) {
		if (!_is.object(proto))
			throw TypeError('Argument must be an object');
		Obj.prototype = proto;
		var result = new Obj();
		Obj.prototype = null;
		return result;
	};

	/**
	 * @summary Merge the contents of two or more objects together into the first `target` object.
	 * @memberof FooGallery.utils.obj
	 * @function extend
	 * @param {Object} target - The object to merge properties into.
	 * @param {Object} object - An object containing properties to merge.
	 * @param {...Object} [objectN] - Additional objects containing properties to merge.
	 * @returns {Object} The `target` merged with the contents from any additional objects.
	 * @description This does not merge arrays by index as jQuery does, it treats them as a single property and replaces the array with a shallow copy of the new one.
	 *
	 * This method makes use of the {@link FooGallery.utils.obj.merge} method internally.
	 * @example {@run true}
	 * // alias the FooGallery.utils.obj namespace
	 * var _obj = FooGallery.utils.obj,
	 * 	// create some objects to merge
	 * 	defaults = {"name": "My Object", "enabled": false, "arr": [1,2,3]},
	 * 	options = {"enabled": true, "something": 123, "arr": [4,5,6]};
	 *
	 * // merge the two objects into a new third one without modifying either of the originals
	 * var settings = _obj.extend( {}, defaults, options );
	 *
	 * console.log( settings ); // => {"name": "My Object", "enabled": true, "arr": [4,5,6], "something": 123}
	 * console.log( defaults ); // => {"name": "My Object", "enabled": true, "arr": [1,2,3]}
	 * console.log( options ); // => {"enabled": true, "arr": [4,5,6], "something": 123}
	 */
	_.obj.extend = function(target, object, objectN){
		target = _is.object(target) ? target : {};
		var objects = _fn.arg2arr(arguments);
		objects.shift();
		$.each(objects, function(i, object){
			_.obj.merge(target, object);
		});
		return target;
	};

	/**
	 * @summary Merge the contents of two objects together into the first `target` object.
	 * @memberof FooGallery.utils.obj
	 * @function merge
	 * @param {Object} target - The object to merge properties into.
	 * @param {Object} object - The object containing properties to merge.
	 * @returns {Object} The `target` merged with the contents from the `object`.
	 * @description This does not merge arrays by index as jQuery does, it treats them as a single property and replaces the array with a shallow copy of the new one.
	 *
	 * This method is used internally by the {@link FooGallery.utils.obj.extend} method.
	 * @example {@run true}
	 * // alias the FooGallery.utils.obj namespace
	 * var _obj = FooGallery.utils.obj,
	 * 	// create some objects to merge
	 * 	target = {"name": "My Object", "enabled": false, "arr": [1,2,3]},
	 * 	object = {"enabled": true, "something": 123, "arr": [4,5,6]};
	 *
	 * console.log( _obj.merge( target, object ) ); // => {"name": "My Object", "enabled": true, "arr": [4,5,6], "something": 123}
	 */
	_.obj.merge = function(target, object){
		target = _is.object(target) ? target : {};
		object = _is.object(object) ? object : {};
		for (var prop in object) {
			if (object.hasOwnProperty(prop)) {
				if (_is.object(object[prop])) {
					target[prop] = _is.object(target[prop]) ? target[prop] : {};
					_.obj.merge(target[prop], object[prop]);
				} else if (_is.array(object[prop])) {
					target[prop] = object[prop].slice();
				} else {
					target[prop] = object[prop];
				}
			}
		}
		return target;
	};

	/**
	 * @summary Merge the validated properties of the `object` into the `target` using the optional `mappings`.
	 * @memberof FooGallery.utils.obj
	 * @function mergeValid
	 * @param {Object} target - The object to merge properties into.
	 * @param {FooGallery.utils.obj~Validators} validators - An object containing validators for the `target` object properties.
	 * @param {Object} object - The object containing properties to merge.
	 * @param {FooGallery.utils.obj~Mappings} [mappings] - An object containing property name mappings.
	 * @returns {Object} The modified `target` object containing any valid properties from the supplied `object`.
	 * @example {@caption Shows the basic usage for this method and shows how invalid properties or those with no corresponding validator are ignored.}{@run true}
	 * // alias the FooGallery.utils.obj and FooGallery.utils.is namespaces
	 * var _obj = FooGallery.utils.obj,
	 * 	_is = FooGallery.utils.is;
	 *
	 * //create the target object and it's validators
	 * var target = {"name":"John","location":"unknown"},
	 * 	validators = {"name":_is.string,"location":_is.string};
	 *
	 * // create the object to merge into the target
	 * var object = {
	 * 	"name": 1234, // invalid
	 * 	"location": "Liverpool", // updated
	 * 	"notMerged": true // ignored
	 * };
	 *
	 * // merge the object into the target, invalid properties or those with no corresponding validator are ignored.
	 * console.log( _obj.mergeValid( target, validators, object ) ); // => { "name": "John", "location": "Liverpool" }
	 * @example {@caption Shows how to supply a mappings object for this method.}{@run true}
	 * // alias the FooGallery.utils.obj and FooGallery.utils.is namespaces
	 * var _obj = FooGallery.utils.obj,
	 * 	_is = FooGallery.utils.is;
	 *
	 * //create the target object and it's validators
	 * var target = {"name":"John","location":"unknown"},
	 * 	validators = {"name":_is.string,"location":_is.string};
	 *
	 * // create the object to merge into the target
	 * var object = {
	 * 	"name": { // ignored
	 * 		"proper": "Christopher", // mapped to name if short is invalid
	 * 		"short": "Chris" // map to name
	 * 	},
	 * 	"city": "London" // map to location
	 * };
	 *
	 * // create the mapping object
	 * var mappings = {
	 * 	"name": [ "name.short", "name.proper" ], // try use the short name and fallback to the proper
	 * 	"location": "city"
	 * };
	 *
	 * // merge the object into the target using the mappings, invalid properties or those with no corresponding validator are ignored.
	 * console.log( _obj.mergeValid( target, validators, object, mappings ) ); // => { "name": "Chris", "location": "London" }
	 */
	_.obj.mergeValid = function(target, validators, object, mappings){
		if (!_is.hash(object) || !_is.hash(validators)) return target;
		validators = _is.hash(validators) ? validators : {};
		mappings = _is.hash(mappings) ? mappings : {};
		var prop, maps, value;
		for (prop in validators){
			if (!validators.hasOwnProperty(prop) || !_is.fn(validators[prop])) continue;
			maps = _is.array(mappings[prop]) ? mappings[prop] : (_is.string(mappings[prop]) ? [mappings[prop]] : [prop]);
			$.each(maps, function(i, map){
				value = _.obj.prop(object, map);
				if (_is.undef(value)) return; // continue
				if (validators[prop](value)){
					_.obj.prop(target, prop, value);
					return false; // break
				}
			});
		}
		return target;
	};

	/**
	 * @summary Get or set a property value given its `name`.
	 * @memberof FooGallery.utils.obj
	 * @function prop
	 * @param {Object} object - The object to inspect for the property.
	 * @param {string} name - The name of the property to fetch. This can be a `.` notated name.
	 * @param {*} [value] - If supplied this is the value to set for the property.
	 * @returns {*} The value for the `name` property, if it does not exist then `undefined`.
	 * @returns {undefined} If a `value` is supplied this method returns nothing.
	 * @example {@caption Shows how to get a property value from an object.}{@run true}
	 * // alias the FooGallery.utils.obj namespace
	 * var _obj = FooGallery.utils.obj,
	 * 	// create an object to test
	 * 	object = {
	 * 		"name": "My Object",
	 * 		"some": {
	 * 			"thing": 123
	 * 		}
	 * 	};
	 *
	 * console.log( _obj.prop( object, "name" ) ); // => "My Object"
	 * console.log( _obj.prop( object, "some.thing" ) ); // => 123
	 * @example {@caption Shows how to set a property value for an object.}{@run true}
	 * // alias the FooGallery.utils.obj namespace
	 * var _obj = FooGallery.utils.obj,
	 * 	// create an object to test
	 * 	object = {
	 * 		"name": "My Object",
	 * 		"some": {
	 * 			"thing": 123
	 * 		}
	 * 	};
	 *
	 * _obj.prop( object, "name", "My Updated Object" );
	 * _obj.prop( object, "some.thing", 987 );
	 *
	 * console.log( object ); // => { "name": "My Updated Object", "some": { "thing": 987 } }
	 */
	_.obj.prop = function(object, name, value){
		if (!_is.object(object) || _is.empty(name)) return;
		var parts, last;
		if (_is.undef(value)){
			if (_str.contains(name, '.')){
				parts = name.split('.');
				last = parts.length - 1;
				$.each(parts, function(i, part){
					if (i === last){
						value = object[part];
					} else if (_is.hash(object[part])) {
						object = object[part];
					} else {
						// exit early
						return false;
					}
				});
			} else if (!_is.undef(object[name])){
				value = object[name];
			}
			return value;
		}
		if (_str.contains(name, '.')){
			parts = name.split('.');
			last = parts.length - 1;
			$.each(parts, function(i, part){
				if (i === last){
					object[part] = value;
				} else {
					object = _is.hash(object[part]) ? object[part] : (object[part] = {});
				}
			});
		} else if (!_is.undef(object[name])){
			object[name] = value;
		}
	};

	//######################
	//## Type Definitions ##
	//######################

	/**
	 * @summary An object used by the {@link FooGallery.utils.obj.mergeValid|mergeValid} method to map new values onto the `target` object.
	 * @typedef {Object.<string,string>|Object.<string,Array.<string>>} FooGallery.utils.obj~Mappings
	 * @description The mappings object is a single level object. If you want to map a property from/to a child object on either the source or target objects you must supply the name using `.` notation as seen in the below example with the `"name.first"` to `"Name.Short"` mapping.
	 * @example {@caption The basic structure of a mappings object is the below.}
	 * {
	 * 	"TargetName": "SourceName", // for top level properties
	 * 	"Child.TargetName": "Child.SourceName" // for child properties
	 * }
	 * @example {@caption Given the following target object.}
	 * var target = {
	 * 	"name": {
	 * 		"first": "",
	 * 		"last": null
	 * 	},
	 * 	"age": 0
	 * };
	 * @example {@caption And the following object to merge.}
	 * var object = {
	 * 	"Name": {
	 * 		"Full": "Christopher",
	 * 		"Short": "Chris"
	 * 	},
	 * 	"Age": 32
	 * };
	 * @example {@caption The mappings object would look like the below.}
	 * var mappings = {
	 * 	"name.first": "Name.Short",
	 * 	"age": "Age"
	 * };
	 * @example {@caption If you want the `"name.first"` property to try to use the `"Name.Short"` value but fallback to `"Name.Proper"` you can specify the mapping value as an array.}
	 * var mappings = {
	 * 	"name.first": [ "Name.Short", "Name.Proper" ],
	 * 	"age": "Age"
	 * };
	 */

	/**
	 * @summary An object used by the {@link FooGallery.utils.obj.mergeValid|mergeValid} method to validate properties.
	 * @typedef {Object.<string,function(*):boolean>} FooGallery.utils.obj~Validators
	 * @description The validators object is a single level object. If you want to validate a property of a child object you must supply the name using `.` notation as seen in the below example with the `"name.first"` and `"name.last"` properties.
	 *
	 * Any function that accepts a value to test as the first argument and returns a boolean can be used as a validator. This means the majority of the {@link FooGallery.utils.is} methods can be used directly. If the property supports multiple types just provide your own function as seen with `"name.last"` in the below example.
	 * @example {@caption The basic structure of a validators object is the below.}
	 * {
	 * 	"PropName": function(*):boolean, // for top level properties
	 * 	"Child.PropName": function(*):boolean // for child properties
	 * }
	 * @example {@caption Given the following target object.}
	 * var target = {
	 * 	"name": {
	 * 		"first": "", // must be a string
	 * 		"last": null // must be a string or null
	 * 	},
	 * 	"age": 0 // must be a number
	 * };
	 * @example {@caption The validators object could be created as seen below.}
	 * // alias the FooGallery.utils.is namespace
	 * var _is = FooGallery.utils.is;
	 *
	 * var validators = {
	 * 	"name.first": _is.string,
	 * 	"name.last": function(value){
	 * 		return _is.string(value) || value === null;
	 * 	},
	 * 	"age": _is.number
	 * };
	 */

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.str
);
(function($, _, _is){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	// any methods that have dependencies but don't fall into a specific subset or namespace can be added here

	/**
	 * @summary The callback for the {@link FooGallery.utils.ready} method.
	 * @callback FooGallery.utils~readyCallback
	 * @param {jQuery} $ - The instance of jQuery the plugin was registered with.
	 * @this window
	 * @see Take a look at the {@link FooGallery.utils.ready} method for example usage.
	 */

	/**
	 * @summary Waits for the DOM to be accessible and then executes the supplied callback.
	 * @memberof FooGallery.utils
	 * @function ready
	 * @param {FooGallery.utils~readyCallback} callback - The function to execute once the DOM is accessible.
	 * @example {@caption This method can be used as a replacement for the jQuery ready callback to avoid an error in another script stopping our scripts from running.}
	 * FooGallery.utils.ready(function($){
	 * 	// do something
	 * });
	 */
	_.ready = function (callback) {
		function onready(){
			try { callback.call(window, _.$); }
			catch(err) { console.error(err); }
		}
		if (Function('/*@cc_on return true@*/')() ? document.readyState === "complete" : document.readyState !== "loading") onready();
		else document.addEventListener('DOMContentLoaded', onready, false);
	};

	// A variable to hold the last number used to generate an ID in the current page.
	var uniqueId = 0;

	/**
	 * @summary Generate and apply a unique id for the given `$element`.
	 * @memberof FooGallery.utils
	 * @function uniqueId
	 * @param {jQuery} $element - The jQuery element object to retrieve an id from or generate an id for.
	 * @param {string} [prefix="uid-"] - A prefix to append to the start of any generated ids.
	 * @returns {string} Either the `$element`'s existing id or a generated one that has been applied to it.
	 * @example {@run true}
	 * // alias the FooGallery.utils namespace
	 * var _ = FooGallery.utils;
	 *
	 * // create some elements to test
	 * var $hasId = $("<span/>", {id: "exists"});
	 * var $generatedId = $("<span/>");
	 * var $generatedPrefixedId = $("<span/>");
	 *
	 * console.log( _.uniqueId( $hasId ) ); // => "exists"
	 * console.log( $hasId.attr( "id" ) ); // => "exists"
	 * console.log( _.uniqueId( $generatedId ) ); // => "uid-1"
	 * console.log( $generatedId.attr( "id" ) ); // => "uid-1"
	 * console.log( _.uniqueId( $generatedPrefixedId, "plugin-" ) ); // => "plugin-2"
	 * console.log( $generatedPrefixedId.attr( "id" ) ); // => "plugin-2"
	 */
	_.uniqueId = function($element, prefix){
		var id = $element.attr('id');
		if (_is.empty(id)){
			prefix = _is.string(prefix) && !_is.empty(prefix) ? prefix : "uid-";
			id = prefix + (++uniqueId);
			$element.attr('id', id).data('__uniqueId__', true);
		}
		return id;
	};

	/**
	 * @summary Remove the id from the given `$element` if it was set using the {@link FooGallery.utils.uniqueId|uniqueId} method.
	 * @memberof FooGallery.utils
	 * @function removeUniqueId
	 * @param {jQuery} $element - The jQuery element object to remove a generated id from.
	 * @example {@run true}
	 * // alias the FooGallery.utils namespace
	 * var _ = FooGallery.utils;
	 *
	 * // create some elements to test
	 * var $hasId = $("<span/>", {id: "exists"});
	 * var $generatedId = $("<span/>");
	 * var $generatedPrefixedId = $("<span/>");
	 *
	 * console.log( _.uniqueId( $hasId ) ); // => "exists"
	 * console.log( _.uniqueId( $generatedId ) ); // => "uid-1"
	 * console.log( _.uniqueId( $generatedPrefixedId, "plugin-" ) ); // => "plugin-2"
	 */
	_.removeUniqueId = function($element){
		if ($element.data('__uniqueId__')){
			$element.removeAttr('id').removeData('__uniqueId__');
		}
	};

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _is){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	/**
	 * @summary Contains common utility methods and members for the CSS transition property.
	 * @memberof FooGallery.utils
	 * @namespace transition
	 */
	_.transition = {};

	// create a test element to check for the existence of the various transition properties
	var testElement = document.createElement('div');

	/**
	 * @summary Whether or not transitions are supported by the current browser.
	 * @memberof FooGallery.utils.transition
	 * @name supported
	 * @type {boolean}
	 */
	_.transition.supported = (
		/**
		 * @ignore
		 * @summary Performs a one time test to see if transitions are supported
		 * @param {Element} el - An element to test with.
		 * @returns {boolean} `true` if transitions are supported.
		 */
		function(el){
			var style = el.style;
			return _is.string(style['transition'])
				|| _is.string(style['WebkitTransition'])
				|| _is.string(style['MozTransition'])
				|| _is.string(style['msTransition'])
				|| _is.string(style['OTransition']);
		}
	)(testElement);

	/**
	 * @summary The `transitionend` event name for the current browser.
	 * @memberof FooGallery.utils.transition
	 * @name end
	 * @type {string}
	 * @description Depending on the browser this returns one of the following values:
	 *
	 * <ul><!--
	 * --><li>`"transitionend"`</li><!--
	 * --><li>`"webkitTransitionEnd"`</li><!--
	 * --><li>`"msTransitionEnd"`</li><!--
	 * --><li>`"oTransitionEnd"`</li><!--
	 * --><li>`null` - If the browser doesn't support transitions</li><!--
	 * --></ul>
	 */
	_.transition.end = (
		/**
		 * @ignore
		 * @summary Performs a one time test to determine which `transitionend` event to use for the current browser.
		 * @param {Element} el - An element to test with.
		 * @returns {?string} The correct `transitionend` event for the current browser, `null` if the browser doesn't support transitions.
		 */
		function(el){
			var style = el.style;
			if (_is.string(style['transition'])) return 'transitionend';
			if (_is.string(style['WebkitTransition'])) return 'webkitTransitionEnd';
			if (_is.string(style['MozTransition'])) return 'transitionend';
			if (_is.string(style['msTransition'])) return 'msTransitionEnd';
			if (_is.string(style['OTransition'])) return 'oTransitionEnd';
			return null;
		}
	)(testElement);

	/**
	 * @summary Gets the `transition-duration` value for the supplied jQuery element.
	 * @memberof FooGallery.utils.transition
	 * @function duration
	 * @param {jQuery} $element - The jQuery element to retrieve the duration from.
	 * @param {number} [def=0] - The default value to return if no duration is set.
	 * @returns {number} The value of the `transition-duration` property converted to a millisecond value.
	 */
	_.transition.duration = function($element, def){
		def = _is.number(def) ? def : 0;
		if (!_is.jq($element)) return def;
		// we can use jQuery.css() method to retrieve the value cross browser
		var duration = $element.css('transition-duration');
		if (/^([\d\.]*)+?(ms|s)$/i.test(duration)){
			// if we have a valid time value
			var match = duration.match(/^([\d\.]*)+?(ms|s)$/i),
				value = parseFloat(match[1]),
				unit = match[2].toLowerCase();
			if (unit === 's'){
				// convert seconds to milliseconds
				value = value * 1000;
			}
			return value;
		}
		return def;
	};

	/**
	 * @summary Start a transition by toggling the supplied `className` on the `$element`.
	 * @memberof FooGallery.utils.transition
	 * @function start
	 * @param {jQuery} $element - The jQuery element to start the transition on.
	 * @param {string} className - One or more class names (separated by spaces) to be toggled that starts the transition.
	 * @param {boolean} [state] - A Boolean (not just truthy/falsy) value to determine whether the class should be added or removed.
	 * @param {number} [timeout] - The maximum time, in milliseconds, to wait for the `transitionend` event to be raised. If not provided this will be automatically set to the elements `transition-duration` property plus an extra 50 milliseconds.
	 * @returns {Promise}
	 * @description This method lets us use CSS transitions by toggling a class and using the `transitionend` event to perform additional actions once the transition has completed across all browsers. In browsers that do not support transitions this method would behave the same as if just calling jQuery's `.toggleClass` method.
	 *
	 * The last parameter `timeout` is used to create a timer that behaves as a safety net in case the `transitionend` event is never raised and ensures the deferred returned by this method is resolved or rejected within a specified time.
	 * @see {@link https://developer.mozilla.org/en/docs/Web/CSS/transition-duration|transition-duration - CSS | MDN} for more information on the `transition-duration` CSS property.
	 */
	_.transition.start = function($element, className, state, timeout){
		var deferred = $.Deferred();

		$element = $element.first();

		if (_.transition.supported){
			var safety = $element.data('transition_safety');
			if (_is.hash(safety) && _is.number(safety.timer)){
				clearTimeout(safety.timer);
				$element.removeData('transition_safety').off(_.transition.end + '.utils');
				safety.deferred.reject();
			}
			timeout = _is.number(timeout) ? timeout : _.transition.duration($element) + 50;
			safety = {
				deferred: deferred,
				timer: setTimeout(function(){
					// This is the safety net in case a transition fails for some reason and the transitionend event is never raised.
					// This will remove the bound event and resolve the deferred
					$element.removeData('transition_safety').off(_.transition.end + '.utils');
					deferred.resolve();
				}, timeout)
			};
			$element.data('transition_safety', safety);

			$element.on(_.transition.end + '.utils', function(e){
				if ($element.is(e.target)){
					clearTimeout(safety.timer);
					$element.removeData('transition_safety').off(_.transition.end + '.utils');
					deferred.resolve();
				}
			});
		}

		setTimeout(function(){
			// This is executed inside of a 20ms timeout to allow the binding of the event handler above to actually happen before the class is toggled
			$element.toggleClass(className, state);
			if (!_.transition.supported){
				// If the browser doesn't support transitions then just resolve the deferred
				deferred.resolve();
			}
		}, 20);

		return deferred.promise();
	};

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is
);
(function ($, _, _is, _obj, _fn) {
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	/**
	 * @summary A base class providing some helper methods for prototypal inheritance.
	 * @memberof FooGallery.utils
	 * @constructs Class
	 * @description This is a base class for making prototypal inheritance simpler to work with. It provides an easy way to inherit from another class and exposes a `_super` method within the scope of any overriding methods that allows a simple way to execute the overridden function.
	 *
	 * Have a look at the {@link FooGallery.utils.Class.extend|extend} and {@link FooGallery.utils.Class.override|override} method examples to see some basic usage.
	 * @example {@caption When using this base class the actual construction of a class is performed by the `construct` method.}
	 * var MyClass = FooGallery.utils.Class.extend({
	 * 	construct: function(arg1, arg2){
	 * 		// handle the construction logic here
	 * 	}
	 * });
	 *
	 * // use the class
	 * var myClass = new MyClass( "arg1:value", "arg2:value" );
	 */
	_.Class = function(){};

	/**
	 * @ignore
	 * @summary The original function when within the scope of an overriding method.
	 * @memberof FooGallery.utils.Class#
	 * @name _super
	 * @type {?function}
	 * @description This is only available within the scope of an overriding method if it was created using the {@link FooGallery.utils.Class.extend|extend}, {@link FooGallery.utils.Class.override|override} or {@link FooGallery.utils.fn.addOrOverride} methods.
	 * @see {@link FooGallery.utils.fn.addOrOverride} to see an example of how this property is used.
	 */

	/**
	 * @summary Creates a new class that inherits from this one which in turn allows itself to be extended.
	 * @memberof FooGallery.utils.Class
	 * @function extend
	 * @param {Object} [definition] - An object containing any methods to implement/override.
	 * @returns {function} A new class that inherits from the base class.
	 * @description Every class created using this method has both the {@link FooGallery.utils.Class.extend|extend} and {@link FooGallery.utils.Class.override|override} static methods added to it to allow it to be extended.
	 * @example {@caption The below shows an example of how to implement inheritance using this method.}{@run true}
	 * // create a base Person class
	 * var Person = FooGallery.utils.Class.extend({
	 * 	construct: function(isDancing){
	 * 		this.dancing = isDancing;
	 * 	},
	 * 	dance: function(){
	 * 		return this.dancing;
	 * 	}
	 * });
	 *
	 * var Ninja = Person.extend({
	 * 	construct: function(){
	 * 		// Call the inherited version of construct()
	 * 		this._super( false );
	 * 	},
	 * 	dance: function(){
	 * 		// Call the inherited version of dance()
	 * 		return this._super();
	 * 	},
	 * 	swingSword: function(){
	 * 		return true;
	 * 	}
	 * });
	 *
	 * var p = new Person(true);
	 * console.log( p.dance() ); // => true
	 *
	 * var n = new Ninja();
	 * console.log( n.dance() ); // => false
	 * console.log( n.swingSword() ); // => true
	 * console.log(
	 * 	p instanceof Person && p.constructor === Person && p instanceof FooGallery.utils.Class
	 * 	&& n instanceof Ninja && n.constructor === Ninja && n instanceof Person && n instanceof FooGallery.utils.Class
	 * ); // => true
	 */
	_.Class.extend = function(definition){
		definition = _is.hash(definition) ? definition : {};
		var proto = _obj.create(this.prototype); // create a new prototype to work with so we don't modify the original
		// iterate over all properties in the supplied definition and update the prototype
		for (var name in definition) {
			if (!definition.hasOwnProperty(name)) continue;
			_fn.addOrOverride(proto, name, definition[name]);
		}
		// if no construct method is defined add a default one that does nothing
		proto.construct = _is.fn(proto.construct) ? proto.construct : function(){};

		// create the new class using the prototype made above
		function Class() {
			if (!_is.fn(this.construct))
				throw new SyntaxError('FooGallery.utils.Class objects must be constructed with the "new" keyword.');
			this.construct.apply(this, arguments);
		}
		Class.prototype = proto;
		//noinspection JSUnresolvedVariable
		Class.prototype.constructor = _is.fn(proto.__ctor__) ? proto.__ctor__ : Class;
		Class.extend = _.Class.extend;
		Class.override = _.Class.override;
		return Class;
	};

	/**
	 * @summary Overrides a single method on this class.
	 * @memberof FooGallery.utils.Class
	 * @function override
	 * @param {string} name - The name of the function to override.
	 * @param {function} fn - The new function to override with, the `_super` method will be made available within this function.
	 * @description This is a helper method for overriding a single function of a {@link FooGallery.utils.Class} or one of its child classes. This uses the {@link FooGallery.utils.fn.addOrOverride} method internally and simply provides the correct prototype.
	 * @example {@caption The below example wraps the `Person.prototype.dance` method with a new one that inverts the result. Note the override applies even to instances of the class that are already created.}{@run true}
	 * var Person = FooGallery.utils.Class.extend({
	 *   construct: function(isDancing){
	 *     this.dancing = isDancing;
	 *   },
	 *   dance: function(){
	 *     return this.dancing;
	 *   }
	 * });
	 *
	 * var p = new Person(true);
	 * console.log( p.dance() ); // => true
	 *
	 * Person.override("dance", function(){
	 * 	// Call the original version of dance()
	 * 	return !this._super();
	 * });
	 *
	 * console.log( p.dance() ); // => false
	 */
	_.Class.override = function(name, fn){
		_fn.addOrOverride(this.prototype, name, fn);
	};

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.obj,
	FooGallery.utils.fn
);
(function($, _, _is){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	_.Bounds = _.Class.extend(/** @lends FooGallery.utils.Bounds */{
		/**
		 * @summary A simple bounding rectangle class.
		 * @memberof FooGallery.utils
		 * @constructs Bounds
		 * @augments FooGallery.utils.Class
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(){
			var self = this;
			self.top = 0;
			self.right = 0;
			self.bottom = 0;
			self.left = 0;
			self.width = 0;
			self.height = 0;
		},
		/**
		 * @summary Inflate the bounds by the specified amount.
		 * @memberof FooGallery.utils.Bounds#
		 * @function inflate
		 * @param {number} amount - A positive number will expand the bounds while a negative one will shrink it.
		 * @returns {FooGallery.utils.Bounds}
		 */
		inflate: function(amount){
			var self = this;
			if (_is.number(amount)){
				self.top -= amount;
				self.right += amount;
				self.bottom += amount;
				self.left -= amount;
				self.width += amount * 2;
				self.height += amount * 2;
			}
			return self;
		},
		/**
		 * @summary Checks if the supplied bounds object intersects with this one.
		 * @memberof FooGallery.utils.Bounds#
		 * @function intersects
		 * @param {FooGallery.utils.Bounds} bounds - The bounds to check.
		 * @returns {boolean}
		 */
		intersects: function(bounds){
			var self = this;
			return self.left <= bounds.right && bounds.left <= self.right && self.top <= bounds.bottom && bounds.top <= self.bottom;
		}
	});

	var __$window;
	/**
	 * @summary Gets the bounding rectangle of the current viewport.
	 * @memberof FooGallery.utils
	 * @function getViewportBounds
	 * @param {number} [inflate] - An amount to inflate the bounds by. A positive number will expand the bounds outside of the visible viewport while a negative one would shrink it.
	 * @returns {FooGallery.utils.Bounds}
	 */
	_.getViewportBounds = function(inflate){
		if (!__$window) __$window = $(window);
		var bounds = new _.Bounds();
		bounds.top = __$window.scrollTop();
		bounds.left = __$window.scrollLeft();
		bounds.width = __$window.width();
		bounds.height = __$window.height();
		bounds.right = bounds.left + bounds.width;
		bounds.bottom = bounds.top + bounds.height;
		bounds.inflate(inflate);
		return bounds;
	};

	/**
	 * @summary Get the bounding rectangle for the supplied element.
	 * @memberof FooGallery.utils
	 * @function getElementBounds
	 * @param {(jQuery|HTMLElement|string)} element - The jQuery wrapper around the element, the element itself, or a CSS selector to retrieve the element with.
	 * @returns {FooGallery.utils.Bounds}
	 */
	_.getElementBounds = function(element){
		if (!_is.jq(element)) element = $(element);
		var bounds = new _.Bounds();
		if (element.length !== 0){
			var offset = element.offset();
			bounds.top = offset.top;
			bounds.left = offset.left;
			bounds.width = element.width();
			bounds.height = element.height();
		}
		bounds.right = bounds.left + bounds.width;
		bounds.bottom = bounds.top + bounds.height;
		return bounds;
	};

})(
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _is, _fn){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	_.Factory = _.Class.extend(/** @lends FooGallery.utils.Factory */{
		/**
		 * @summary A factory for classes allowing them to be registered and created using a friendly name.
		 * @memberof FooGallery.utils
		 * @constructs Factory
		 * @description This class allows other classes to register themselves for use at a later time. Depending on how you intend to use the registered classes you can also specify a load and execution order through the `priority` parameter of the {@link FooGallery.utils.Factory#register|register} method.
		 * @augments FooGallery.utils.Class
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(){
			/**
			 * @summary An object containing all registered classes.
			 * @memberof FooGallery.utils.Factory#
			 * @name registered
			 * @type {Object.<string, Object>}
			 * @readonly
			 * @example {@caption The following shows the structure of this object. The `<name>` placeholders would be the name the class was registered with.}
			 * {
			 * 	"<name>": {
			 * 		"name": <string>,
			 * 		"klass": <function>,
			 * 		"priority": <number>
			 * 	},
			 * 	"<name>": {
			 * 		"name": <string>,
			 * 		"klass": <function>,
			 * 		"priority": <number>
			 * 	},
			 * 	...
			 * }
			 */
			this.registered = {};
		},
		/**
		 * @summary Checks if the factory contains a class registered using the supplied `name`.
		 * @memberof FooGallery.utils.Factory#
		 * @function contains
		 * @param {string} name - The name of the class to check.
		 * @returns {boolean}
		 * @example {@run true}
		 * // create a new instance of the factory, this is usually exposed by the class that will be using the factory.
		 * var factory = new FooGallery.utils.Factory();
		 *
		 * // create a class to register
		 * function Test(){}
		 *
		 * // register the class with the factory with the default priority
		 * factory.register( "test", Test );
		 *
		 * // test if the class was registered
		 * console.log( factory.contains( "test" ) ); // => true
		 */
		contains: function(name){
			return !_is.undef(this.registered[name]);
		},
		/**
		 * @summary Creates new instances of all registered classes using there registered priority and the supplied arguments.
		 * @memberof FooGallery.utils.Factory#
		 * @function load
		 * @param {Object.<string, function>} overrides - An object containing classes to override any matching registered classes with, if no overrides are required you can pass `false` or `null`.
		 * @param {*} arg1 - The first argument to supply when creating new instances of all registered classes.
		 * @param {...*} [argN] - Any number of additional arguments to supply when creating new instances of all registered classes.
		 * @returns {Array.<Object>} An array containing new instances of all registered classes.
		 * @description The class indexes within the result array are determined by the `priority` they were registered with, the higher the `priority` the lower the index.
		 *
		 * This method is designed to be used when all registered classes share a common interface or base type and constructor arguments.
		 * @example {@caption The following loads all registered classes into an array ordered by there priority.}{@run true}
		 * // create a new instance of the factory, this is usually exposed by the class that will be using the factory.
		 * var factory = new FooGallery.utils.Factory();
		 *
		 * // create a base Extension class
		 * var Extension = FooGallery.utils.Class.extend({
		 * 	construct: function( type, options ){
		 * 		this.type = type;
		 * 		this.options = options;
		 * 	},
		 * 	getType: function(){
		 * 		return this.type;
		 * 	}
		 * });
		 *
		 * // create various item, this would usually be in another file
		 * var MyExtension1 = Extension.extend({
		 * 	construct: function(options){
		 * 		this._super( "my-extension-1", options );
		 * 	}
		 * });
		 * factory.register( "my-extension-1", MyExtension1, 0 );
		 *
		 * // create various item, this would usually be in another file
		 * var MyExtension2 = Extension.extend({
		 * 	construct: function(options){
		 * 		this._super( "my-extension-2", options );
		 * 	}
		 * });
		 * factory.register( "my-extension-2", MyExtension2, 1 );
		 *
		 * // load all registered classes according to there priority passing the options to all constructors
		 * var loaded = factory.load( null, {"something": true} );
		 *
		 * // only two classes should be loaded
		 * console.log( loaded.length ); // => 2
		 *
		 * // the MyExtension2 class is loaded first due to it's priority being higher than the MyExtension1 class.
		 * console.log( loaded[0] instanceof MyExtension2 && loaded[0] instanceof Extension ); // => true
		 * console.log( loaded[1] instanceof MyExtension1 && loaded[1] instanceof Extension ); // => true
		 *
		 * // do something with the loaded classes
		 * @example {@caption The following loads all registered classes into an array ordered by there priority but uses the overrides parameter to swap out one of them for a custom implementation.}{@run true}
		 * // create a new instance of the factory, this is usually exposed by the class that will be using the factory.
		 * var factory = new FooGallery.utils.Factory();
		 *
		 * // create a base Extension class
		 * var Extension = FooGallery.utils.Class.extend({
		 * 	construct: function( type, options ){
		 * 		this.type = type;
		 * 		this.options = options;
		 * 	},
		 * 	getType: function(){
		 * 		return this.type;
		 * 	}
		 * });
		 *
		 * // create a new extension, this would usually be in another file
		 * var MyExtension1 = Extension.extend({
		 * 	construct: function(options){
		 * 		this._super( "my-extension-1", options );
		 * 	}
		 * });
		 * factory.register( "my-extension-1", MyExtension1, 0 );
		 *
		 * // create a new extension, this would usually be in another file
		 * var MyExtension2 = Extension.extend({
		 * 	construct: function(options){
		 * 		this._super( "my-extension-2", options );
		 * 	}
		 * });
		 * factory.register( "my-extension-2", MyExtension2, 1 );
		 *
		 * // create a custom extension that is not registered but overrides the default "my-extension-1"
		 * var UpdatedMyExtension1 = MyExtension1.extend({
		 * 	construct: function(options){
		 * 		this._super( options );
		 * 		// do something different to the original MyExtension1 class
		 * 	}
		 * });
		 *
		 * // load all registered classes but swaps out the registered "my-extension-1" for the supplied override.
		 * var loaded = factory.load( {"my-extension-1": UpdatedMyExtension1}, {"something": true} );
		 *
		 * // only two classes should be loaded
		 * console.log( loaded.length ); // => 2
		 *
		 * // the MyExtension2 class is loaded first due to it's priority being higher than the UpdatedMyExtension1 class which inherited a priority of 0.
		 * console.log( loaded[0] instanceof MyExtension2 && loaded[0] instanceof Extension ); // => true
		 * console.log( loaded[1] instanceof UpdatedMyExtension1 && loaded[1] instanceof MyExtension1 && loaded[1] instanceof Extension ); // => true
		 *
		 * // do something with the loaded classes
		 */
		load: function(overrides, arg1, argN){
			var self = this,
				args = _fn.arg2arr(arguments),
				reg = [],
				loaded = [],
				name, klass;

			overrides = args.shift() || {};
			for (name in self.registered){
				if (!self.registered.hasOwnProperty(name)) continue;
				var component = self.registered[name];
				if (overrides.hasOwnProperty(name)){
					klass = overrides[name];
					if (_is.string(klass)) klass = _fn.fetch(overrides[name]);
					if (_is.fn(klass)){
						component = {name: name, klass: klass, priority: self.registered[name].priority};
					}
				}
				reg.push(component);
			}

			for (name in overrides){
				if (!overrides.hasOwnProperty(name) || self.registered.hasOwnProperty(name)) continue;
				klass = overrides[name];
				if (_is.string(klass)) klass = _fn.fetch(overrides[name]);
				if (_is.fn(klass)){
					reg.push({name: name, klass: klass, priority: 0});
				}
			}

			reg.sort(function(a, b){ return b.priority - a.priority; });
			$.each(reg, function(i, r){
				if (_is.fn(r.klass)){
					loaded.push(_fn.apply(r.klass, args));
				}
			});
			return loaded;
		},
		/**
		 * @summary Create a new instance of a class registered with the supplied `name` and arguments.
		 * @memberof FooGallery.utils.Factory#
		 * @function make
		 * @param {string} name - The name of the class to create.
		 * @param {*} arg1 - The first argument to supply to the new instance.
		 * @param {...*} [argN] - Any number of additional arguments to supply to the new instance.
		 * @returns {Object}
		 * @example {@caption The following shows how to create a new instance of a registered class.}{@run true}
		 * // create a new instance of the factory, this is usually done by the class that will be using it.
		 * var factory = new FooGallery.utils.Factory();
		 *
		 * // create a Logger class to register, this would usually be in another file
		 * var Logger = FooGallery.utils.Class.extend({
		 * 	write: function( message ){
		 * 		console.log( "Logger#write: " + message );
		 * 	}
		 * });
		 *
		 * factory.register( "logger", Logger );
		 *
		 * // create a new instances of the class registered as "logger"
		 * var logger = factory.make( "logger" );
		 * logger.write( "My message" ); // => "Logger#write: My message"
		 */
		make: function(name, arg1, argN){
			var self = this, args = _fn.arg2arr(arguments), reg;
			name = args.shift();
			reg = self.registered[name];
			if (_is.hash(reg) && _is.fn(reg.klass)){
				return _fn.apply(reg.klass, args);
			}
			return null;
		},
		/**
		 * @summary Gets an array of all registered names.
		 * @memberof FooGallery.utils.Factory#
		 * @function names
		 * @param {boolean} [prioritize=false] - Whether or not to order the names by the priority they were registered with.
		 * @returns {Array.<string>}
		 * @example {@run true}
		 * // create a new instance of the factory, this is usually exposed by the class that will be using the factory.
		 * var factory = new FooGallery.utils.Factory();
		 *
		 * // create some classes to register
		 * function Test1(){}
		 * function Test2(){}
		 *
		 * // register the classes with the factory with the default priority
		 * factory.register( "test-1", Test1 );
		 * factory.register( "test-2", Test2, 1 );
		 *
		 * // log all registered names
		 * console.log( factory.names() ); // => ["test-1","test-2"]
		 * console.log( factory.names( true ) ); // => ["test-2","test-1"] ~ "test-2" appears before "test-1" as it was registered with a higher priority
		 */
		names: function( prioritize ){
			prioritize = _is.boolean(prioritize) ? prioritize : false;
			var names = [], name;
			if (prioritize){
				var reg = [];
				for (name in this.registered){
					if (!this.registered.hasOwnProperty(name)) continue;
					reg.push(this.registered[name]);
				}
				reg.sort(function(a, b){ return b.priority - a.priority; });
				$.each(reg, function(i, r){
					names.push(r.name);
				});
			} else {
				for (name in this.registered){
					if (!this.registered.hasOwnProperty(name)) continue;
					names.push(name);
				}
			}
			return names;
		},
		/**
		 * @summary Registers a `klass` constructor with the factory using the given `name`.
		 * @memberof FooGallery.utils.Factory#
		 * @function register
		 * @param {string} name - The friendly name of the class.
		 * @param {function} klass - The class constructor to register.
		 * @param {number} [priority=0] - This determines the index for the class when using either the {@link FooGallery.utils.Factory#load|load} or {@link FooGallery.utils.Factory#names|names} methods, a higher value equals a lower index.
		 * @returns {boolean} `true` if the `klass` was successfully registered.
		 * @description Once a class is registered you can use either the {@link FooGallery.utils.Factory#load|load} or {@link FooGallery.utils.Factory#make|make} methods to create new instances depending on your use case.
		 * @example {@run true}
		 * // create a new instance of the factory, this is usually exposed by the class that will be using the factory.
		 * var factory = new FooGallery.utils.Factory();
		 *
		 * // create a class to register
		 * function Test(){}
		 *
		 * // register the class with the factory with the default priority
		 * var succeeded = factory.register( "test", Test );
		 *
		 * console.log( succeeded ); // => true
		 * console.log( factory.registered.hasOwnProperty( "test" ) ); // => true
		 * console.log( factory.registered[ "test" ].name === "test" ); // => true
		 * console.log( factory.registered[ "test" ].klass === Test ); // => true
		 * console.log( factory.registered[ "test" ].priority === 0 ); // => true
		 */
		register: function(name, klass, priority){
			if (!_is.string(name) || _is.empty(name) || !_is.fn(klass)) return false;
			priority = _is.number(priority) ? priority : 0;
			var current = this.registered[name];
			this.registered[name] = {
				name: name,
				klass: klass,
				priority: !_is.undef(current) ? current.priority : priority
			};
			return true;
		}
	});

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn
);
(function(_, _fn, _str){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	_.Debugger = _.Class.extend(/** @lends FooGallery.utils.Debugger */{
		/**
		 * @summary A debug utility class that can be enabled across sessions using the given `key` by storing its state in `localStorage`.
		 * @memberof FooGallery.utils
		 * @constructs Debugger
		 * @param {string} key - The key to use to store the debug state in `localStorage`.
		 * @description This class allows you to write additional debug info to the console within your code which by default is not actually output. You can then enable the debugger and it will start to output the results to the console.
		 *
		 * This most useful feature of this is the ability to store the debug state across page sessions by using `localStorage`. This allows you enable the debugger and then refresh the page to view any debugger output that occurs on page load.
		 */
		construct: function(key){
			/**
			 * @summary The key used to store the debug state in `localStorage`.
			 * @memberof FooGallery.utils.Debugger#
			 * @name key
			 * @type {string}
			 */
			this.key = key;
			/**
			 * @summary Whether or not the debugger is currently enabled.
			 * @memberof FooGallery.utils.Debugger#
			 * @name enabled
			 * @type {boolean}
			 * @readonly
			 * @description The value for this property is synced with the current state stored in `localStorage` and should never set from outside of this class.
			 */
			this.enabled = !!localStorage.getItem(this.key);
		},
		/**
		 * @summary Enable the debugger causing additional info to be logged to the console.
		 * @memberof FooGallery.utils.Debugger#
		 * @function enable
		 * @example
		 * var d = new FooGallery.utils.Debugger( "FOO_DEBUG" );
		 * d.log( "Never logged" );
		 * d.enabled();
		 * d.log( "I am logged!" );
		 */
		enable: function(){
			this.enabled = true;
			localStorage.setItem(this.key, this.enabled);
		},
		/**
		 * @summary Disable the debugger stopping additional info being logged to the console.
		 * @memberof FooGallery.utils.Debugger#
		 * @function disable
		 * @example
		 * var d = new FooGallery.utils.Debugger( "FOO_DEBUG" );
		 * d.log( "Never logged" );
		 * d.enabled();
		 * d.log( "I am logged!" );
		 * d.disable();
		 * d.log( "Never logged" );
		 */
		disable: function(){
			this.enabled = false;
			localStorage.removeItem(this.key);
		},
		/**
		 * @summary Logs the supplied message and additional arguments to the console when enabled.
		 * @memberof FooGallery.utils.Debugger#
		 * @function log
		 * @param {string} message - The message to log to the console.
		 * @param {*} [argN] - Any number of additional arguments to supply after the message.
		 * @description This method basically wraps the `console.log` method and simply checks the enabled state of the debugger before passing along any supplied arguments.
		 */
		log: function(message, argN){
			if (!this.enabled) return;
			console.log.apply(console, _fn.arg2arr(arguments));
		},
		/**
		 * @summary Logs the formatted message and additional arguments to the console when enabled.
		 * @memberof FooGallery.utils.Debugger#
		 * @function logf
		 * @param {string} message - The message containing named `replacements` to log to the console.
		 * @param {Object.<string, *>} replacements - An object containing key value pairs used to perform a named format on the `message`.
		 * @param {*} [argN] - Any number of additional arguments to supply after the message.
		 * @see {@link FooGallery.utils.str.format} for more information on supplying the replacements object.
		 */
		logf: function(message, replacements, argN){
			if (!this.enabled) return;
			var args = _fn.arg2arr(arguments);
			message = args.shift();
			replacements = args.shift();
			args.unshift(_str.format(message, replacements));
			this.log.apply(this, args);
		}
	});

})(
	// dependencies
	FooGallery.utils,
	FooGallery.utils.fn,
	FooGallery.utils.str
);
(function($, _, _is){
	// only register methods if this version is the current version
	if (_.version !== '0.0.5') return;

	_.Throttle = _.Class.extend(/** @lends FooGallery.utils.Throttle */{
		/**
		 * @summary A timer to throttle the execution of code.
		 * @memberof FooGallery.utils
		 * @constructs
		 * @param {number} [idle=0] - The idle time, in milliseconds, that must pass before executing the callback supplied to the {@link FooGallery.utils.Throttle#limit|limit} method.
		 * @augments FooGallery.utils.Class
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 * @description This class is basically a wrapper around the {@link https://developer.mozilla.org/en-US/docs/Web/API/WindowTimers/setTimeout|window.setTimeout} and {@link https://developer.mozilla.org/en-US/docs/Web/API/WindowTimers/clearTimeout|window.clearTimeout} functions. It was created to help throttle the execution of code in event handlers that could be called multiple times per second such as the window resize event. It is meant to limit the execution of expensive code until the specified idle time has lapsed.
		 *
		 * Take a look at the examples for the {@link FooGallery.utils.Throttle#limit|limit} and {@link FooGallery.utils.Throttle#clear|clear} methods for basic usage.
		 * @example <caption>The below shows how you can use this class to prevent expensive code being executed with every call to your window resize handler. If you run this example resize your browser to see when the messages are logged.</caption>{@run true}
		 * var throttle = new FooGallery.utils.Throttle( 50 );
		 *
		 * $(window).on("resize", function(){
		 *
		 * 	throttle.limit(function(){
		 * 		console.log( "Only called when resizing has stopped for at least 50 milliseconds." );
		 * 	});
		 *
		 * });
		 * @see {@link https://developer.mozilla.org/en-US/docs/Web/API/WindowTimers/setTimeout|WindowTimers.setTimeout() - Web APIs | MDN}
		 * @see {@link https://developer.mozilla.org/en-US/docs/Web/API/WindowTimers/clearTimeout|WindowTimers.clearTimeout() - Web APIs | MDN}
		 */
		construct: function(idle){
			/**
			 * @summary The id from the last call to {@link https://developer.mozilla.org/en-US/docs/Web/API/WindowTimers/setTimeout|window.setTimeout}.
			 * @type {?number}
			 * @readonly
			 * @default null
			 */
			this.id = null;
			/**
			 * @summary Whether or not there is an active timer.
			 * @type {boolean}
			 * @readonly
			 * @default false
			 */
			this.active = false;
			/**
			 * @summary The idle time, in milliseconds, the timer should wait before executing the callback supplied to the {@link FooGallery.utils.Throttle#limit|limit} method.
			 * @type {number}
			 * @readonly
			 * @default 0
			 */
			this.idle = _is.number(idle) ? idle : 0;
		},
		/**
		 * @summary Starts a new timer clearing any previously set and executes the <code>callback</code> once it expires.
		 * @instance
		 * @param {function} callback - The function to call once the timer expires.
		 * @example <caption>In the below example the <code>callback</code> function will only be executed once despite the repeated calls to the {@link FooGallery.utils.Throttle#limit|limit} method as each call resets the idle timer.</caption>{@run true}
		 * // create a new throttle
		 * var throttle = new FooGallery.utils.Throttle( 50 );
		 *
		 * // this `for` loop represents something like the window resize event that could call your handler multiple times a second
		 * for (var i = 0, max = 5; i < max; i++){
		 *
		 * 	throttle.limit( function(){
		 * 		console.log( "Only called once, after the idle timer lapses" );
		 * 	} );
		 *
		 * }
		 */
		limit: function(callback){
			if (!_is.fn(callback)) return;
			this.clear();
			var self = this;
			this.active = true;
			this.id = setTimeout(function(){
				self.active = false;
				self.id = null;
				callback();
			}, this.idle);
		},
		/**
		 * @summary Clear any previously set timer and prevent the execution of its' callback.
		 * @instance
		 * @example <caption>The below shows how to cancel an active throttle and prevent the execution of it's callback.</caption>{@run true}
		 * // create a new throttle
		 * var throttle = new FooGallery.utils.Throttle( 50 );
		 *
		 * // this `for` loop represents something like the window resize event that could call your handler multiple times a second
		 * for (var i = 0, max = 5; i < max; i++){
		 *
		 * 	throttle.limit( function(){
		 * 		console.log( "I'm never called" );
		 * 	} );
		 *
		 * }
		 *
		 * // cancel the current throttle timer
		 * throttle.clear();
		 */
		clear: function(){
			if (_is.number(this.id)){
				clearTimeout(this.id);
				this.active = false;
				this.id = null;
			}
		}
	});

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _utils, _is, _fn){

	_.debug = new _utils.Debugger("__FooGallery__");

	/**
	 * @summary Simple utility method to convert space delimited strings of CSS class names into a CSS selector.
	 * @memberof FooGallery.utils
	 * @function selectify
	 * @param {(string|string[]|object)} classes - A single space delimited string of CSS class names to convert or an array of them with each item being included in the selector using the OR (`,`) syntax as a separator. If an object is supplied the result will be an object with the same property names but the values converted to selectors.
	 * @returns {(object|string)}
	 */
	_utils.selectify = function(classes){
		if (_is.hash(classes)){
			var result = {}, selector;
			for (var name in classes){
				if (!classes.hasOwnProperty(name)) continue;
				if (selector = _utils.selectify(classes[name])){
					result[name] = selector;
				}
			}
			return result;
		}
		if (_is.string(classes) || _is.array(classes)){
			if (_is.string(classes)) classes = [classes];
			return $.map(classes, function(str){
				return _is.string(str) ? "." + str.split(/\s/g).join(".") : null;
			}).join(",");
		}
		return null;
	};

	/**
	 * @summary The url of an empty 1x1 pixel image used as the default value for the `placeholder` and `error` {@link FooGallery.defaults|options}.
	 * @memberof FooGallery
	 * @name emptyImage
	 * @type {string}
	 * @default "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
	 */
	_.emptyImage = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";

	/**
	 * @summary The name to use when getting or setting an instance of a {@link FooGallery.Template|template} on an element using jQuery's `.data()` method.
	 * @memberof FooGallery
	 * @name dataTemplate
	 * @type {string}
	 * @default "__FooGallery__"
	 */
	_.dataTemplate = "__FooGallery__";

	/**
	 * @summary The name to use when getting or setting an instance of a {@link FooGallery.Item|item} on an element using jQuery's `.data()` method.
	 * @memberof FooGallery
	 * @name dataItem
	 * @type {string}
	 * @default "__FooGalleryItem__"
	 */
	_.dataItem = "__FooGalleryItem__";

	_.init = function(options, element){
		return _.template.make(options, element).initialize();
	};

	_.initAll = function(options){
		return _fn.when($(".foogallery").map(function(i, element){
			return _.init(options, element);
		}).get());
	};

	_.parseSrc = function(src, srcWidth, srcHeight, srcset, renderWidth, renderHeight){
		if (!_is.string(src)) return null;
		// if there is no srcset just return the src
		if (!_is.string(srcset)) return src;

		// parse the srcset into objects containing the url, width, height and pixel density for each supplied source
		var list = $.map(srcset.replace(/(\s[\d.]+[whx]),/g, '$1 @,@ ').split(' @,@ '), function (val) {
			return {
				url: /^\s*(\S*)/.exec(val)[1],
				w: parseFloat((/\S\s+(\d+)w/.exec(val) || [0, Infinity])[1]),
				h: parseFloat((/\S\s+(\d+)h/.exec(val) || [0, Infinity])[1]),
				x: parseFloat((/\S\s+([\d.]+)x/.exec(val) || [0, 1])[1])
			};
		});

		// if there is no items parsed from the srcset then just return the src
		if (!list.length) return src;

		// add the current src into the mix by inspecting the first parsed item to figure out how to handle it
		list.unshift({
			url: src,
			w: list[0].w !== Infinity && list[0].h === Infinity ? srcWidth : Infinity,
			h: list[0].h !== Infinity && list[0].w === Infinity ? srcHeight : Infinity,
			x: 1
		});

		// get the current viewport info and use it to determine the correct src to load
		var dpr = window.devicePixelRatio || 1,
			area = {w: renderWidth * dpr, h: renderHeight * dpr, x: dpr},
			property;

		// first check each of the viewport properties against the max values of the same properties in our src array
		// only src's with a property greater than the viewport or equal to the max are kept
		for (property in area) {
			if (!area.hasOwnProperty(property)) continue;
			list = $.grep(list, (function (prop, limit) {
				return function (item) {
					return item[prop] >= area[prop] || item[prop] === limit;
				};
			})(property, Math.max.apply(null, $.map(list, function (item) {
				return item[property];
			}))));
		}

		// next reduce our src array by comparing the viewport properties against the minimum values of the same properties of each src
		// only src's with a property equal to the minimum are kept
		for (property in area) {
			if (!area.hasOwnProperty(property)) continue;
			list = $.grep(list, (function (prop, limit) {
				return function (item) {
					return item[prop] === limit;
				};
			})(property, Math.min.apply(null, $.map(list, function (item) {
				return item[property];
			}))));
		}

		// return the first url as it is the best match for the current viewport
		return list[0].url;
	};

	/**
	 * @summary Expose FooGallery as a jQuery plugin.
	 * @memberof external:"jQuery.fn"#
	 * @function foogallery
	 * @param {(object|string)} [options] - The options to supply to FooGallery or one of the supported method names.
	 * @param {external:"jQuery.fn"~readyCallback} [ready] - A callback executed once each template initialized is ready.
	 * @returns {jQuery}
	 * @example {@caption The below shows using this method in its simplest form, initializing a template on pre-existing elements.}{@lang html}
	 * <!-- The container element for the template -->
	 * <div id="gallery-1" class="foogallery">
	 *   <!-- A single item -->
	 *   <div class="fg-item" data-id="[item.id]">
	 *     <div class="fg-item-inner">
	 *       <a class="fg-thumb" href="[item.href]">
	 *         <img class="fg-image" width="[item.width]" height="[item.height]"
	 *         	title="[item.title]" alt="[item.description]"
	 *         	data-src="[item.src]"
	 *         	data-srcset="[item.srcset]" />
	 *         <!-- Optional caption markup -->
	 *         <div class="fg-caption">
	 *         	<div class="fg-caption-inner">
	 *         	 <div class="fg-caption-title">[item.title]</div>
	 *         	 <div class="fg-caption-desc">[item.description]</div>
	 *         	</div>
	 *         </div>
	 *       </a>
	 *     </div>
	 *   </div>
	 *   <!-- Any number of additional items -->
	 * </div>
	 * <script>
	 * 	jQuery(function($){
	 * 		$("#gallery-1").foogallery();
	 * 	});
	 * </script>
	 * @example {@caption Options can be supplied directly to the `.foogallery()` method or by supplying them using the `data-foogallery` attribute. If supplied using the attribute the value must follow [valid JSON syntax](http://en.wikipedia.org/wiki/JSON#Data_types.2C_syntax_and_example) including quoted property names. If the same option is supplied in both locations as it is below, the value from the attribute overrides the value supplied to the method, in this case `lazy` would be `true`.}{@lang html}
	 * <!-- Supplying the options using the attribute -->
	 * <div id="gallery-1" class="foogallery fg-responsive" data-foogallery='{"lazy": true}'>
	 * 	<!-- Items -->
	 * </div>
	 * <script>
	 * 	jQuery(function($){
	 * 		// Supply the options directly to the method
	 * 		$("#gallery-1").foogallery({
	 * 			lazy: false
	 * 		});
	 * 	});
	 * </script>
	 */
	$.fn.foogallery = function(options, ready){
		return this.filter(".foogallery").each(function(i, element){
			if (_is.string(options)){
				var template = $.data(element, _.dataTemplate);
				if (template instanceof _.Template){
					switch (options){
						case "layout":
							template.layout();
							return;
						case "destroy":
							template.destroy();
							return;
					}
				}
			} else {
				_.template.make(options, element).initialize().then(function(template){
					if (_is.fn(ready)){
						ready(template);
					}
				});
			}
		});
	};

	/**
	 * @summary If supplied this method is executed after each template is initialized.
	 * @callback external:"jQuery.fn"~readyCallback
	 * @param {FooGallery.Template} template - The template that was initialized.
	 * @example {@caption The below shows an example of supplying this callback to the `.foogallery()` method.}
	 * jQuery(".foogallery").foogallery({
	 * 	// Options here
	 * }, function(template){
	 * 	// Called after each template is initialized on the matched elements
	 * });
	 */

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn
);
(function($, _, _utils, _is, _fn, _obj){

	_.TemplateFactory = _utils.Factory.extend(/** @lends FooGallery.TemplateFactory */{
		/**
		 * @summary A factory for galleries allowing them to be easily registered and created.
		 * @memberof FooGallery
		 * @constructs TemplateFactory
		 * @description The plugin makes use of an instance of this class exposed as {@link FooGallery.template}.
		 * @augments FooGallery.utils.Factory
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(){
			/**
			 * @summary An object containing all registered galleries.
			 * @memberof FooGallery.TemplateFactory#
			 * @name registered
			 * @type {Object.<string, Object>}
			 * @readonly
			 * @example {@caption The following shows the structure of this object. The `<name>` placeholders would be the name the class was registered with.}
			 * {
			 * 	"<name>": {
			 * 		"name": <string>,
			 * 		"klass": <function>,
			 * 		"test": <function>,
			 * 		"priority": <number>
			 * 	},
			 * 	"<name>": {
			 * 		"name": <string>,
			 * 		"klass": <function>,
			 * 		"test": <function>,
			 * 		"priority": <number>
			 * 	},
			 * 	...
			 * }
			 */
			this.registered = {};
		},
		/**
		 * @summary Registers a template constructor with the factory using the given `name` and `test` function.
		 * @memberof FooGallery.TemplateFactory#
		 * @function register
		 * @param {string} name - The friendly name of the class.
		 * @param {FooGallery.Template} template - The template constructor to register.
		 * @param {object} options - The default options for the template.
		 * @param {object} [classes={}] - The CSS classes for the template.
		 * @param {object} [il8n={}] - The il8n strings for the template.
		 * @param {number} [priority=0] - This determines the index for the class when using either the {@link FooGallery.TemplateFactory#load|load} or {@link FooGallery.TemplateFactory#names|names} methods, a higher value equals a lower index.
		 * @returns {boolean} `true` if the `klass` was successfully registered.
		 */
		register: function(name, template, options, classes, il8n, priority){
			var self = this, result = self._super(name, template, priority);
			if (result){
				var reg = self.registered;
				reg[name].opt = _is.hash(options) ? options : {};
				reg[name].cls = _is.hash(classes) ? classes : {};
				reg[name].il8n = _is.hash(il8n) ? il8n : {};
			}
			return result;
		},
		/**
		 * @summary Create a new instance of a registered template from the supplied `element` and `options`.
		 * @memberof FooGallery.TemplateFactory#
		 * @function make
		 * @param {(object|FooGallery~Options)} [options] - The options for the template. If not supplied this will fall back to using the {@link FooGallery.defaults|defaults}.
		 * @param {(jQuery|HTMLElement|string)} [element] - The jQuery object, HTMLElement or selector of the template element to create. If not supplied the {@link FooGallery~Options#type|type} options' value is used.
		 * @returns {FooGallery.Template}
		 */
		make: function(options, element){
			element = _is.jq(element) ? element : $(element);
			options = _obj.merge(options, element.data("foogallery"));
			var self = this, type = self.type(options, element);
			if (!self.contains(type)) return null;
			options = self.options(type, options);
			return self._super(type, options, element);
		},
		type: function(options, element){
			element = _is.jq(element) ? element : $(element);
			var self = this, type = _is.hash(options) && _is.hash(options) && _is.string(options.type) && self.contains(options.type) ? options.type : "core";
			if (type === "core" && element.length > 0){
				var reg = self.registered, names = self.names(true);
				for (var i = 0, l = names.length; i < l; i++) {
					if (!reg.hasOwnProperty(names[i])) continue;
					var name = names[i], cls = reg[name].cls;
					if (!_is.string(cls.container)) continue;
					var selector = _utils.selectify(cls.container);
					if (element.is(selector)) {
						type = names[i];
						break;
					}
				}
			}
			return type;
		},
		configure: function(name, options, classes, il8n){
			var self = this;
			if (self.contains(name)){
				var reg = self.registered;
				_obj.extend(reg[name].opt, options);
				_obj.extend(reg[name].cls, classes);
				_obj.extend(reg[name].il8n, il8n);
			}
		},
		options: function(name, options){
			options = _is.hash(options) ? options : {};
			var self = this, reg = self.registered,
				def = reg["core"].opt,
				cls = reg["core"].cls,
				il8n = reg["core"].il8n;

			options = _.paging.merge(options);
			if (name !== "core" && self.contains(name)){
				options = _obj.extend({}, def, reg[name].opt, options);
				options.cls = _obj.extend({}, cls, reg[name].cls, options.cls);
				options.il8n = _obj.extend({}, il8n, reg[name].il8n, options.il8n);
			} else {
				options = _obj.extend({}, def, options);
				options.cls = _obj.extend({}, cls, options.cls);
				options.il8n = _obj.extend({}, il8n, options.il8n);
			}
			return options;
		}
	});

	/**
	 * @summary The factory used to register and create the various template types of FooGallery.
	 * @memberof FooGallery
	 * @name template
	 * @type {FooGallery.TemplateFactory}
	 */
	_.template = new _.TemplateFactory();

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.obj
);
(function(_, _utils, _is, _fn, _obj){

	_.PagingFactory = _utils.Factory.extend(/** @lends FooGallery.PagingFactory */{
		/**
		 * @summary A factory for paging types allowing them to be easily registered and created.
		 * @memberof FooGallery
		 * @constructs PagingFactory
		 * @description The plugin makes use of an instance of this class exposed as {@link FooGallery.paging}.
		 * @augments FooGallery.Factory
		 * @borrows FooGallery.Factory.extend as extend
		 * @borrows FooGallery.Factory.override as override
		 */
		construct: function(){
			/**
			 * @summary An object containing all registered paging types.
			 * @memberof FooGallery.PagingFactory#
			 * @name registered
			 * @type {Object.<string, Object>}
			 * @readonly
			 * @example {@caption The following shows the structure of this object. The `<name>` placeholders would be the name the class was registered with.}
			 * {
			 * 	"<name>": {
			 * 		"name": <string>,
			 * 		"klass": <function>,
			 * 		"ctrl": <function>,
			 * 		"priority": <number>
			 * 	},
			 * 	"<name>": {
			 * 		"name": <string>,
			 * 		"klass": <function>,
			 * 		"ctrl": <function>,
			 * 		"priority": <number>
			 * 	},
			 * 	...
			 * }
			 */
			this.registered = {};
		},
		/**
		 * @summary Registers a paging `type` constructor with the factory using the given `name` and `test` function.
		 * @memberof FooGallery.PagingFactory#
		 * @function register
		 * @param {string} name - The friendly name of the class.
		 * @param {FooGallery.Paging} type - The paging type constructor to register.
		 * @param {FooGallery.PagingControl} [ctrl] - An optional control to register for the paging type.
		 * @param {object} [options={}] - The default options for the paging type.
		 * @param {object} [classes={}] - The CSS classes for the paging type.
		 * @param {object} [il8n={}] - The il8n strings for the paging type.
		 * @param {number} [priority=0] - This determines the index for the class when using either the {@link FooGallery.PagingFactory#load|load} or {@link FooGallery.PagingFactory#names|names} methods, a higher value equals a lower index.
		 * @returns {boolean} `true` if the `klass` was successfully registered.
		 */
		register: function(name, type, ctrl, options, classes, il8n, priority){
			var self = this, result = self._super(name, type, priority);
			if (result){
				var reg = self.registered;
				reg[name].ctrl = _is.fn(ctrl) ? ctrl : null;
				reg[name].opt = _is.hash(options) ? options : {};
				reg[name].cls = _is.hash(classes) ? classes : {};
				reg[name].il8n = _is.hash(il8n) ? il8n : {};
			}
			return result;
		},
		// /**
		//  * @summary Create a new instance of a registered paging type for the supplied `template`.
		//  * @memberof FooGallery.PagingFactory#
		//  * @function make
		//  * @param {FooGallery.Template} template - The template creating the new instance.
		//  * @returns {FooGallery.Paging}
		//  */
		// make: function(template){
		// 	var self = this, paging, type;
		// 	if (!(template instanceof _.Template) || !_is.hash(paging = template.opt.paging) || !self.contains(type = paging.type) || type === "default") return null;
		// 	var reg = self.registered;
		// 	template.opt.paging = _obj.extend({}, reg["default"].opt, reg[type].opt, template.opt.paging);
		// 	template.cls.paging = _obj.extend({}, reg["default"].cls, reg[type].cls, template.cls.paging);
		// 	template.il8n.paging = _obj.extend({}, reg["default"].il8n, reg[type].il8n, template.il8n.paging);
		// 	template.sel.paging = _utils.selectify(template.cls.paging);
		// 	return self._super(type, template);
		// },
		type: function(options){
			var self = this, opt;
			return _is.hash(options) && _is.hash(opt = options.paging) && _is.string(opt.type) && self.contains(opt.type) ? opt.type : null;
		},
		merge: function(options){
			options = _is.hash(options) ? options : {};
			var self = this, type = self.type(options),
				reg = self.registered,
				def = reg["default"].opt,
				def_cls = reg["default"].cls,
				def_il8n = reg["default"].il8n,
				opt = _is.hash(options.paging) ? options.paging : {},
				cls = _is.hash(options.cls) && _is.hash(options.cls.paging) ? options.cls.paging : {},
				il8n = _is.hash(options.il8n) && _is.hash(options.il8n.paging) ? options.il8n.paging : {};

			if (type !== "default" && self.contains(type)){
				options.paging = _obj.extend({}, def, reg[type].opt, opt, {type: type});
				options.cls = _obj.extend({}, {paging: def_cls}, {paging: reg[type].cls}, {paging: cls});
				options.il8n = _obj.extend({}, {paging: def_il8n}, {paging: reg[type].il8n}, {paging: il8n});
			} else {
				options.paging = _obj.extend({}, def, opt, {type: type});
				options.cls = _obj.extend({}, {paging: def_cls}, {paging: cls});
				options.il8n = _obj.extend({}, {paging: def_il8n}, {paging: il8n});
			}
			return options;
		},
		configure: function(name, options, classes, il8n){
			var self = this;
			if (self.contains(name)){
				var reg = self.registered;
				_obj.extend(reg[name].opt, options);
				_obj.extend(reg[name].cls, classes);
				_obj.extend(reg[name].il8n, il8n);
			}
		},
		options: function(name, options){
			options = _is.hash(options) ? options : {};
			var self = this,
				reg = self.registered,
				def = reg["default"].opt,
				def_cls = reg["default"].cls,
				def_il8n = reg["default"].il8n,
				opt = _is.hash(options.paging) ? options.paging : {},
				cls = _is.hash(options.cls) && _is.hash(options.cls.paging) ? options.cls.paging : {},
				il8n = _is.hash(options.il8n) && _is.hash(options.il8n.paging) ? options.il8n.paging : {};

			if (name !== "default" && self.contains(name)){
				options.paging = _obj.extend({}, def, reg[name].opt, opt, {type: name});
				options.cls = _obj.extend({}, {paging: def_cls}, {paging: reg[name].cls}, {paging: cls});
				options.il8n = _obj.extend({}, {paging: def_il8n}, {paging: reg[name].il8n}, {paging: il8n});
			} else {
				options.paging = _obj.extend({}, def, opt, {type: name});
				options.cls = _obj.extend({}, {paging: def_cls}, {paging: cls});
				options.il8n = _obj.extend({}, {paging: def_il8n}, {paging: il8n});
			}
			return options;
		},
		/**
		 * @summary Checks if the factory contains a control registered using the supplied `name`.
		 * @memberof FooGallery.PagingFactory#
		 * @function hasCtrl
		 * @param {string} name - The friendly name of the class.
		 * @returns {boolean}
		 */
		hasCtrl: function(name){
			var self = this, reg = self.registered[name];
			return _is.hash(reg) && _is.fn(reg.ctrl);
		},
		/**
		 * @summary Create a new instance of a control class registered with the supplied `name` and arguments.
		 * @memberof FooGallery.PagingFactory#
		 * @function makeCtrl
		 * @param {string} name - The friendly name of the class.
		 * @param {FooGallery.Template} template - The template creating the control.
		 * @param {FooGallery.Paging} parent - The parent paging class creating the control.
		 * @param {string} position - The position the control will be displayed at.
		 * @returns {?FooGallery.PagingControl}
		 */
		makeCtrl: function(name, template, parent, position){
			var self = this, reg = self.registered[name];
			if (_is.hash(reg) && _is.fn(reg.ctrl)){
				return new reg.ctrl(template, parent, position);
			}
			return null;
		}
	});

	/**
	 * @summary The factory used to register and create the various paging types of FooGallery.
	 * @memberof FooGallery
	 * @name paging
	 * @type {FooGallery.PagingFactory}
	 */
	_.paging = new _.PagingFactory();

})(
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.obj
);
(function($, _, _utils, _is, _fn, _str){

	_.Template = _utils.Class.extend(/** @lends FooGallery.Template */{
		/**
		 * @summary The primary class for FooGallery, this controls the flow of the plugin across all templates.
		 * @memberof FooGallery
		 * @constructs Template
		 * @param {FooGallery~Options} [options] - The options for the template.
		 * @param {jQuery} [element] - The jQuery object of the templates' container element. If not supplied one will be created within the `parent` element supplied to the {@link FooGallery.Template#initialize|initialize} method.
		 * @augments FooGallery.utils.Class
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(options, element){
			var self = this;
			/**
			 * @summary The jQuery object for the template container.
			 * @memberof FooGallery.Template#
			 * @name $el
			 * @type {jQuery}
			 */
			self.$el = _is.jq(element) ? element : $(element);
			/**
			 * @summary The options for the template.
			 * @memberof FooGallery.Template#
			 * @name opt
			 * @type {FooGallery~Options}
			 */
			self.opt = options;
			/**
			 * @summary Any custom options for the template.
			 * @memberof FooGallery.Template#
			 * @name template
			 * @type {object}
			 */
			self.template = options.template;
			/**
			 * @summary The ID for the template.
			 * @memberof FooGallery.Template#
			 * @name id
			 * @type {string}
			 */
			self.id = self.$el.prop("id") || options.id;
			/**
			 * @summary Whether or not the template created its' own container element.
			 * @memberof FooGallery.Template#
			 * @name createdSelf
			 * @type {boolean}
			 */
			self.createdSelf = false;
			/**
			 * @summary The CSS classes for the template.
			 * @memberof FooGallery.Template#
			 * @name cls
			 * @type {FooGallery~CSSClasses}
			 */
			self.cls = options.cls;
			/**
			 * @summary The il8n strings for the template.
			 * @memberof FooGallery.Template#
			 * @name il8n
			 * @type {FooGallery~il8n}
			 */
			self.il8n = options.il8n;
			/**
			 * @summary The CSS selectors for the template.
			 * @memberof FooGallery.Template#
			 * @name sel
			 * @type {FooGallery~CSSSelectors}
			 */
			self.sel = _utils.selectify(self.cls);
			/**
			 * @summary The item manager for the template.
			 * @memberof FooGallery.Template#
			 * @name items
			 * @type {FooGallery.Items}
			 */
			self.items = _.components.make("items", self);
			/**
			 * @summary The page manager for the template.
			 * @memberof FooGallery.Template#
			 * @name pages
			 * @type {?FooGallery.Paging}
			 */
			self.pages = _.paging.make(options.paging.type, self);
			/**
			 * @summary The state manager for the template.
			 * @memberof FooGallery.Template#
			 * @name state
			 * @type {FooGallery.State}
			 */
			self.state = _.components.make("state", self);
			/**
			 * @summary The promise object returned by the {@link FooGallery.Template#initialize|initialize} method.
			 * @memberof FooGallery.Template#
			 * @name _initialize
			 * @type {?Promise}
			 * @private
			 */
			self._initialize = null;
			self.initializing = false;
			self.initialized = false;
			self.destroying = false;
			self.destroyed = false;
		},

		// ################
		// ## Initialize ##
		// ################

		/**
		 * @summary Initialize the template.
		 * @memberof FooGallery.Template#
		 * @function initialize
		 * @param {(jQuery|HTMLElement|string)} [parent] - If no element was supplied to the constructor you must supply a parent element for the template to append itself to. This can be a jQuery object, HTMLElement or a CSS selector.
		 * @returns {Promise.<FooGallery.Template>}
		 * @description Once resolved all options, objects and elements required by the template have been parsed or created and the initial load is complete.
		 * @fires FooGallery.Template~"pre-init.foogallery"
		 * @fires FooGallery.Template~"init.foogallery"
		 * @fires FooGallery.Template~"post-init.foogallery"
		 * @fires FooGallery.Template~"ready.foogallery"
		 */
		initialize: function(parent){
			var self = this;
			if (_is.promise(self._initialize)) return self._initialize;
			parent = _is.jq(parent) ? parent : $(parent);
			return self._initialize = $.Deferred(function(def){
				self.initializing = true;
				if (parent.length === 0 && self.$el.parent().length === 0){
					def.reject("A parent element is required.");
					return;
				}
				if (self.$el.length === 0){
					self.$el = self.create();
					self.createdSelf = true;
				}
				if (parent.length > 0){
					self.$el.appendTo(parent);
				}
				var queue = $.Deferred(), promise = queue.promise(), existing;
				if (self.$el.length > 0 && (existing = self.$el.data(_.dataTemplate)) instanceof _.Template){
					promise = promise.then(function(){
						return existing.destroy().then(function(){
							self.$el.data(_.dataTemplate, self);
						});
					});
				} else {
					self.$el.data(_.dataTemplate, self);
				}
				promise.then(function(){
					if (self.destroying) return _fn.rejectWith("destroy in progress");
					// at this point we have our container element free of pre-existing instances so let's bind any event listeners supplied by the .on option
					if (!_is.empty(self.opt.on)){
						self.$el.on(self.opt.on);
					}

					/**
					 * @summary Raised before the template is fully initialized.
					 * @event FooGallery.Template~"pre-init.foogallery"
					 * @type {jQuery.Event}
					 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
					 * @param {FooGallery.Template} template - The template raising the event.
					 * @returns {Promise} Resolved once the pre-initialization work is complete, rejected if an error occurs or execution is prevented.
					 * @description At this point in the initialization chain the {@link FooGallery.Template#opt|opt} property has not undergone any additional parsing and is just the result of the {@link FooGallery.defaults|default options} being extended with any user supplied ones.
					 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
					 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"pre-init.foogallery": function(event, template){
					 * 			// do something
					 * 		}
					 * 	}
					 * });
					 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent the template being initialized.}
					 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"pre-init.foogallery": function(event, template){
					 * 			if ("some condition"){
					 * 				// stop the template being initialized
					 * 				event.preventDefault();
					 * 			}
					 * 		}
					 * 	}
					 * });
					 */
					var e = self.raise("pre-init");
					if (e.isDefaultPrevented()) return _fn.rejectWith("pre-init default prevented");
				}).then(function(){
					if (self.destroying) return _fn.rejectWith("destroy in progress");
					// checks the delay option and if it is greater than 0 waits for that amount of time before continuing
					if (self.opt.delay <= 0) return _fn.resolved;
					return $.Deferred(function(wait){
						self._delay = setTimeout(function () {
							self._delay = null;
							wait.resolve();
						}, self.opt.delay);
					}).promise();
				}).then(function(){
					if (self.destroying) return _fn.rejectWith("destroy in progress");
					/**
					 * @summary Raised before the template is initialized but after any pre-initialization work is complete.
					 * @event FooGallery.Template~"init.foogallery"
					 * @type {jQuery.Event}
					 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
					 * @param {FooGallery.Template} template - The template raising the event.
					 * @returns {Promise} Resolved once the initialization work is complete, rejected if an error occurs or execution is prevented.
					 * @description At this point in the initialization chain all additional option parsing has been completed but the base components such as the items or state are not yet initialized.
					 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
					 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"init.foogallery": function(event, template){
					 * 			// do something
					 * 		}
					 * 	}
					 * });
					 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent the template being initialized.}
					 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"init.foogallery": function(event, template){
					 * 			if ("some condition"){
					 * 				// stop the template being initialized
					 * 				event.preventDefault();
					 * 			}
					 * 		}
					 * 	}
					 * });
					 * @example {@caption You can also prevent the default logic and replace it with your own by calling the `preventDefault` method on the `event` object and returning a promise.}
					 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"init.foogallery": function(event, template){
					 * 			// stop the default logic
					 * 			event.preventDefault();
					 * 			// you can execute the default logic by calling the handler directly yourself
					 * 			// var promise = template.onInit();
					 * 			// replace the default logic with your own
					 * 			return Promise;
					 * 		}
					 * 	}
					 * });
					 */
					var e = self.raise("init");
					if (e.isDefaultPrevented()) return _fn.rejectWith("init default prevented");
					return self.items.fetch();
				}).then(function(){
					if (self.destroying) return _fn.rejectWith("destroy in progress");
					/**
					 * @summary Raised after the template is initialized but before any post-initialization work is complete.
					 * @event FooGallery.Template~"post-init.foogallery"
					 * @type {jQuery.Event}
					 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
					 * @param {FooGallery.Template} template - The template raising the event.
					 * @returns {Promise} Resolved once the post-initialization work is complete, rejected if an error occurs or execution is prevented.
					 * @description At this point in the initialization chain all options, objects and elements required by the template have been parsed or created however the initial state has not been set yet and no items have been loaded.
					 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
					 * $(".foogallery").foogallery({
						 * 	on: {
						 * 		"post-init.foogallery": function(event, template){
						 * 			// do something
						 * 		}
						 * 	}
						 * });
					 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent the template being initialized.}
					 * $(".foogallery").foogallery({
						 * 	on: {
						 * 		"post-init.foogallery": function(event, template){
						 * 			if ("some condition"){
						 * 				// stop the template being initialized
						 * 				event.preventDefault();
						 * 			}
						 * 		}
						 * 	}
						 * });
					 * @example {@caption You can also prevent the default logic and replace it with your own by calling the `preventDefault` method on the `event` object and returning a promise.}
					 * $(".foogallery").foogallery({
						 * 	on: {
						 * 		"post-init.foogallery": function(event, template){
						 * 			// stop the default logic
						 * 			event.preventDefault();
						 * 			// you can execute the default logic by calling the handler directly yourself
						 * 			// var promise = template.onPostInit();
						 * 			// replace the default logic with your own
						 * 			return Promise;
						 * 		}
						 * 	}
						 * });
					 */
					var e = self.raise("post-init");
					if (e.isDefaultPrevented()) return _fn.rejectWith("post-init default prevented");
					var state = self.state.parse();
					self.state.set(_is.empty(state) ? self.state.initial() : state);
					$(window).on("scroll.foogallery", {self: self}, self.throttle(self.onWindowScroll, self.opt.throttle))
							.on("popstate.foogallery", {self: self}, self.onWindowPopState);
				}).then(function(){
					if (self.destroying) return _fn.rejectWith("destroy in progress");
					/**
					 * @summary Raised after the template is fully initialized but before the first load occurs.
					 * @event FooGallery.Template~"first-load.foogallery"
					 * @type {jQuery.Event}
					 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
					 * @param {FooGallery.Template} template - The template raising the event.
					 * @description This event is raised after all post-initialization work such as setting the initial state is performed but before the first load of items takes place.
					 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
					 * $(".foogallery").foogallery({
						 * 	on: {
						 * 		"first-load.foogallery": function(event, template){
						 * 			// do something
						 * 		}
						 * 	}
						 * });
					 */
					self.raise("first-load");
					return self.loadAvailable();
				}).then(function(){
					if (self.destroying) return _fn.rejectWith("destroy in progress");
					self.initializing = false;
					self.initialized = true;

					// performed purely to re-check if any items need to be loaded after content has possibly shifted
					self._check(200);
					self._check(500);
					self._check(1000);
					self._check(2000);
					self._check(5000);

					/**
					 * @summary Raised after the template is fully initialized and is ready to be interacted with.
					 * @event FooGallery.Template~"ready.foogallery"
					 * @type {jQuery.Event}
					 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
					 * @param {FooGallery.Template} template - The template raising the event.
					 * @description This event is raised after all post-initialization work such as setting the initial state and performing the first load are completed.
					 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
					 * $(".foogallery").foogallery({
							 * 	on: {
							 * 		"ready.foogallery": function(event, template){
							 * 			// do something
							 * 		}
							 * 	}
							 * });
					 */
					self.raise("ready");
					def.resolve(self);
				}).fail(function(err){
					def.reject(err);
				});
				queue.resolve();
			}).promise().fail(function(err){
				console.log("initialize failed", self, err);
				self.destroy();
			});
		},
		/**
		 * @summary Create a new container element for the template returning the jQuery object.
		 * @memberof FooGallery.Template#
		 * @function create
		 * @returns {jQuery} A jQuery object to use as the container for the template.
		 * @description This method is called from within the {@link FooGallery.Template#initialize|initialize} method only if a container element is required.
		 * @example {@caption The below shows an example of what the returned jQuery objects' outer HTML would look like.}{@lang html}
		 * <div id="{options.id}" class="{options.cls.container} {options.classes}">
		 * </div>
		 */
		create: function(){
			var self = this;
			return $("<div/>", {"id": self.id, "class": self.cls.container}).addClass(self.opt.classes);
		},

		// #############
		// ## Destroy ##
		// #############

		/**
		 * @summary Destroy the template.
		 * @memberof FooGallery.Template#
		 * @function destroy
		 * @returns {Promise}
		 * @description Once this method is called it can not be stopped and the template will be destroyed.
		 * @fires FooGallery.Template~"destroy.foogallery"
		 */
		destroy: function(){
			var self = this;
			if (self.destroyed) return _fn.resolved;
			self.destroying = true;
			return $.Deferred(function(def){
				if (self.initializing && _is.promise(self._initialize)){
					self._initialize.always(function(){
						self.destroying = false;
						self._destroy();
						def.resolve();
					});
				} else {
					self.destroying = false;
					self._destroy();
					def.resolve();
				}
			}).promise();
		},
		/**
		 * @summary Destroy the template.
		 * @memberof FooGallery.Template#
		 * @function _destroy
		 * @private
		 */
		_destroy: function(){
			var self = this;
			if (self.destroyed) return;
			/**
			 * @summary Raised before the template is destroyed.
			 * @event FooGallery.Template~"destroy.foogallery"
			 * @type {jQuery.Event}
			 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
			 * @param {FooGallery.Template} template - The template raising the event.
			 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"destroy.foogallery": function(event, template){
			 * 			// do something
			 * 		}
			 * 	}
			 * });
			 */
			self.raise("destroy");
			$(window).off("popstate.foogallery", self.onWindowPopState)
					.off("scroll.foogallery");
			self.state.destroy();
			if (self.pages) self.pages.destroy();
			self.items.destroy();
			if (!_is.empty(self.opt.on)){
				self.$el.off(self.opt.on);
			}
			/**
			 * @summary Raised after the template has been destroyed.
			 * @event FooGallery.Template~"destroyed.foogallery"
			 * @type {jQuery.Event}
			 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
			 * @param {FooGallery.Template} template - The template raising the event.
			 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"destroyed.foogallery": function(event, template){
			 * 			// do something
			 * 		}
			 * 	}
			 * });
			 */
			self.raise("destroyed");
			self.$el.removeData(_.dataTemplate);
			if (self.createdSelf){
				self.$el.remove();
			}
			self.$el = self.state = self.items = self.pages = null;
			self.destroyed = true;
			self.initializing = false;
			self.initialized = false;
		},

		// ################
		// ## Load Items ##
		// ################

		/**
		 * @summary Check if any available items need to be loaded and loads them.
		 * @memberof FooGallery.Template#
		 * @function loadAvailable
		 * @returns {Promise<FooGallery.Item[]>} Resolves with an array of {@link FooGallery.Item|items} as the first argument. If no items are loaded this array is empty.
		 */
		loadAvailable: function(){
			var self = this, items;
			if (self.pages){
				items = self.pages.available();
			} else {
				items = self.items.available();
			}
			return self.items.load(items);
		},

		/**
		 * @summary Check if any available items need to be loaded and loads them.
		 * @memberof FooGallery.Template#
		 * @function _check
		 * @private
		 */
		_check: function(delay){
			delay = _is.number(delay) ? delay : 0;
			var self = this;
			setTimeout(function(){
				if (self.initialized && (!self.destroying || !self.destroyed)){
					self.loadAvailable();
				}
			}, delay);
		},

		// #############
		// ## Utility ##
		// #############

		/**
		 * @summary Raises the supplied `eventName` on the template {@link FooGallery.Template#$el|element}.
		 * @memberof FooGallery.Template#
		 * @function raise
		 * @param {string} eventName - The name of the event to raise.
		 * @param {Array} [args] - An additional arguments to supply to the listeners for the event.
		 * @returns {?jQuery.Event} The jQuery.Event object or null if no `eventName` was supplied.
		 * @description This method also executes any listeners set on the template object itself. These listeners are not bound to the element but are executed after the event is raised but before any default logic is executed. The names of these listeners use the following convention; prefix the `eventName` with `"on-"` and then camel-case the result. e.g. `"pre-init"` becomes `onPreInit`.
		 * @example {@caption The following displays a listener for the `"pre-init.foogallery"` event in a sub-classed template.}
		 * FooGallery.MyTemplate = FooGallery.Template.extend({
		 * 	onPreInit: function(event, template){
		 * 		// do something
		 * 	}
		 * });
		 */
		raise: function (eventName, args) {
			if (!_is.string(eventName) || _is.empty(eventName)) return null;
			args = _is.array(args) ? args : [];
			var self = this,
				name = eventName.split(".")[0],
				listener = _str.camel("on-" + name),
				event = $.Event(name + ".foogallery");
			args.unshift(self); // add self
			self.$el.trigger(event, args);
			_.debug.logf("{id}|{name}:", {id: self.id, name: name}, args);
			if (_is.fn(self[listener])){
				args.unshift(event); // add event
				self[listener].apply(self.$el.get(0), args);
			}
			return event;
		},

		layout: function(){
			var self = this;
			if (self._initialize === null) return;
			/**
			 * @summary Raised when the templates' {@link FooGallery.Template#layout|layout} method is called.
			 * @event FooGallery.Template~"layout.foogallery"
			 * @type {jQuery.Event}
			 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
			 * @param {FooGallery.Template} template - The template raising the event.
			 * @description This allows templates to perform layout if required for example when visibility changes.
			 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"layout.foogallery": function(event, template){
			 * 			// do something
			 * 		}
			 * 	}
			 * });
			 */
			self.raise("layout");
		},

		/**
		 * @summary Throttles the supplied function to only execute once every N milliseconds.
		 * @memberof FooGallery.Template#
		 * @function throttle
		 * @param {Function} fn - The function to throttle.
		 * @param {number} wait - The number of milliseconds to wait before allowing execution.
		 * @returns {Function}
		 */
		throttle: function(fn, wait){
			var time = Date.now();
			return function() {
				if ((time + wait - Date.now()) < 0) {
					var args = _fn.arg2arr(arguments);
					fn.apply(this, args);
					time = Date.now();
				}
			}
		},

		// ###############
		// ## Listeners ##
		// ###############

		/**
		 * @summary Listens for the windows popstate event and performs any actions required by the template.
		 * @memberof FooGallery.Template#
		 * @function onWindowPopState
		 * @param {jQuery.Event} e - The jQuery.Event object for the event.
		 * @private
		 */
		onWindowPopState: function(e){
			var self = e.data.self, state = e.originalEvent.state;
			if (!_is.empty(state) && state.id === self.id){
				self.state.set(state);
				self.loadAvailable();
			}
		},
		/**
		 * @summary Listens for the windows scroll event and performs any checks required by the template.
		 * @memberof FooGallery.Template#
		 * @function onWindowScroll
		 * @param {jQuery.Event} e - The jQuery.Event object for the event.
		 * @private
		 */
		onWindowScroll: function(e){
			var self = e.data.self;
			self.loadAvailable();
		}
	});

	_.template.register("core", _.Template, {
		id: null,
		type: "core",
		classes: "",
		on: {},
		lazy: true,
		viewport: 200,
		items: [],
		delay: 100,
		throttle: 50,
		timeout: 60000,
		srcset: "data-srcset",
		src: "data-src",
		template: {}
	}, {
		container: "foogallery"
	}, {

	}, -100);

	/**
	 * @summary An object containing all the core template options.
	 * @typedef {object} FooGallery.Template~Options
	 * @property {?string} [id=null] - The id for the template. This is only required if creating a template without a pre-existing container element that has an `id` attribute.
	 * @property {string} [type="core"] - The type of template to load. If no container element exists to parse the type from this must be supplied so the correct type of template is loaded.
	 * @property {string} [classes=""] - A space delimited string of any additional CSS classes to append to the container element of the template.
	 * @property {object} [on={}] - An object containing any template events to bind to.
	 * @property {boolean} [lazy=true] - Whether or not to enable lazy loading of images.
	 * @property {number} [viewport=200] - The number of pixels to inflate the viewport by when checking to lazy load items.
	 * @property {(FooGallery.Item~Options[]|FooGallery.Item[]| string)} [items=[]] - An array of items to load when required. A url can be provided and the items will be fetched using an ajax call, the response should be a properly formatted JSON array of {@link FooGallery.Item~Options|item} object.
	 * @property {number} [delay=100] - The number of milliseconds to delay the initialization of a template.
	 * @property {number} [throttle=50] - The number of milliseconds to wait once scrolling has stopped before performing any work.
	 * @property {number} [timeout=60000] - The number of milliseconds to wait before forcing a timeout when loading items.
	 * @property {string} [src="data-src"] - The name of the attribute to retrieve an images src url from.
	 * @property {string} [srcset="data-srcset"] - The name of the attribute to retrieve an images srcset url from.
	 * @property {object} [template={}] - An object containing any additional custom options for the template.
	 * @property {FooGallery.Template~CSSClasses} [cls] - An object containing all CSS classes for the template.
	 * @property {FooGallery.Template~CSSSelectors} [sel] - An object containing all CSS selectors for the template.
	 * @property {FooGallery.Template~il8n} [il8n] - An object containing all il8n strings for the template.
	 * @property {FooGallery.Item~Options} [item] - An object containing the default values for all items.
	 * @property {FooGallery.State~Options} [state] - An object containing the state options for the template.
	 * @property {FooGallery.Paging~Options} [paging] - An object containing the default paging options for the template.
	 */

	/**
	 * @summary An object containing all CSS classes for the core template.
	 * @typedef {object} FooGallery.Template~CSSClasses
	 * @property {string} [container="foogallery"] - The base CSS class names to apply to the container element.
	 * @property {FooGallery.Item~CSSClasses} [item] - A simple object containing the CSS classes used by an item.
	 */

	/**
	 * @summary An object containing all il8n strings for the core template.
	 * @typedef {object} FooGallery.Template~il8n
	 */

	/**
	 * @summary An object containing all CSS selectors for the core template.
	 * @typedef {object} FooGallery.Template~CSSSelectors
	 * @property {string} [container=".foogallery"] - The selector for the base CSS class names for the container element.
	 * @property {FooGallery.Item~CSSSelectors} [item] - An object containing the CSS selectors for an item.
	 * @description This object is automatically generated from a {@link FooGallery.Template~CSSClasses|classes} object and its properties mirror those except the space delimited string of class names is converted into a CSS selector.
	 */

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.str
);


(function(_, _utils){

	_.Component = _utils.Class.extend(/** @lend FooGallery.Component */{
		/**
		 * @summary The base class for all child components of a {@link FooGallery.Template|template}.
		 * @memberof FooGallery
		 * @constructs Component
		 * @param {FooGallery.Template} template - The template creating the component.
		 * @augments FooGallery.utils.Class
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(template){
			/**
			 * @summary The template that created this component.
			 * @memberof FooGallery.Component#
			 * @name tmpl
			 * @type {FooGallery.Template}
			 */
			this.tmpl = template;
		},
		/**
		 * @summary Destroy the component making it ready for garbage collection.
		 * @memberof FooGallery.Component#
		 * @function destroy
		 */
		destroy: function(){
			this.tmpl = null;
		}
	});

	/**
	 * @summary A factory for registering and creating basic gallery components.
	 * @memberof FooGallery
	 * @name components
	 * @type {FooGallery.utils.Factory}
	 */
	_.components = new _utils.Factory();

})(
	FooGallery,
	FooGallery.utils
);
(function($, _, _is, _str){

	_.State = _.Component.extend(/** @lends FooGallery.State */{
		/**
		 * @summary This class manages all the getting and setting of its' parent templates' state.
		 * @memberof FooGallery
		 * @constructs State
		 * @param {FooGallery.Template} template - The template to manage the state for.
		 * @augments FooGallery.Component
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(template){
			var self = this;
			/**
			 * @ignore
			 * @memberof FooGallery.State#
			 * @function _super
			 */
			self._super(template);
			/**
			 * @summary Whether or not the history API is enabled in the current browser.
			 * @memberof FooGallery.State#
			 * @name apiEnabled
			 * @type {boolean}
			 * @readonly
			 */
			self.apiEnabled = !!window.history && !!history.replaceState;
			/**
			 * @summary The state specific options for the template.
			 * @memberof FooGallery.State#
			 * @name opt
			 * @type {FooGallery.State~Options}
			 */
			self.opt = self.tmpl.opt.state;
			/**
			 * @summary Whether or not the component is enabled.
			 * @memberof FooGallery.State#
			 * @name enabled
			 * @type {boolean}
			 */
			self.enabled = self.opt.enabled;
			/**
			 * @summary Which method of the history API to use by default when updating the state.
			 * @memberof FooGallery.State#
			 * @name pushOrReplace
			 * @type {string}
			 * @default "replace"
			 */
			self.pushOrReplace = self.isPushOrReplace(self.opt.pushOrReplace) ? self.opt.pushOrReplace : "replace";

			var id = _str.escapeRegExp(self.tmpl.id),
				values = _str.escapeRegExp(self.opt.values),
				pair = _str.escapeRegExp(self.opt.pair);
			/**
			 * @summary An object containing regular expressions used to test and parse a hash value into a state object.
			 * @memberof FooGallery.State#
			 * @name regex
			 * @type {{exists: RegExp, values: RegExp}}
			 * @readonly
			 * @description The regular expressions contained within this object are specific to this template and are created using the template {@link FooGallery.Template#id|id} and the delimiters from the {@link FooGallery.State#opt|options}.
			 */
			self.regex = {
				exists: new RegExp("^#"+id+"\\"+values+".+?"),
				values: new RegExp("(\\w+)"+pair+"([^"+values+"]+)", "g")
			};
		},
		/**
		 * @summary Destroy the component clearing any current state from the url and preparing it for garbage collection.
		 * @memberof FooGallery.State#
		 * @function destroy
		 */
		destroy: function(){
			var self = this;
			self.clear();
			self.opt = self.regex = {};
			self._super();
		},
		/**
		 * @summary Check if the supplied value is `"push"` or `"replace"`.
		 * @memberof FooGallery.State#
		 * @function isPushOrReplace
		 * @param {*} value - The value to check.
		 * @returns {boolean}
		 */
		isPushOrReplace: function(value){
			return $.inArray(value, ["push","replace"]) !== -1;
		},
		/**
		 * @summary Check if the current url contains state for this template.
		 * @memberof FooGallery.State#
		 * @function exists
		 * @returns {boolean}
		 */
		exists: function(){
			return this.regex.exists.test(location.hash) && this.regex.values.test(location.hash);
		},
		/**
		 * @summary Parse the current url returning an object containing all values for the template.
		 * @memberof FooGallery.State#
		 * @function parse
		 * @returns {object}
		 * @description This method always returns an object, if successful the object contains properties otherwise it is just a plain empty object. For this method to be successful the current template {@link FooGallery.Template#id|id} must match the one from the url.
		 */
		parse: function(){
			var self = this, state = {};
			if (self.exists()){
				if (self.enabled){
					state.id = self.tmpl.id;
					var pairs = location.hash.match(self.regex.values);
					$.each(pairs, function(i, pair){
						var parts = pair.split(self.opt.pair);
						if (parts.length === 2){
							state[parts[0]] = parts[1].indexOf(self.opt.array) === -1
								? decodeURIComponent(parts[1])
								: $.map(parts[1].split(self.opt.array), function(part){ return decodeURIComponent(part); });
							if (_is.string(state[parts[0]]) && !isNaN(state[parts[0]])){
								state[parts[0]] = parseInt(state[parts[0]]);
							}
						}
					});
				} else {
					// if we're here it means there is a hash on the url but the option is disabled so remove it
					if (self.apiEnabled){
						history.replaceState(null, "", location.pathname + location.search);
					} else {
						location.hash = "#";
					}
				}
			}
			return state;
		},
		/**
		 * @summary Converts the supplied state object into a string value to use as a hash.
		 * @memberof FooGallery.State#
		 * @function hashify
		 * @param {object} state - The object to hashify.
		 * @returns {string}
		 */
		hashify: function(state){
			var self = this;
			if (_is.hash(state)){
				var hash = [];
				$.each(state, function(name, value){
					if (!_is.empty(value) && name !== "id"){
						if (_is.array(value)){
							value = $.map(value, function(part){ return encodeURIComponent(part); }).join("+");
						} else {
							value = encodeURIComponent(value);
						}
						hash.push(name + self.opt.pair + value);
					}
				});
				if (hash.length > 0){
					hash.unshift("#"+self.tmpl.id);
				}
				return hash.join(self.opt.values);
			}
			return "";
		},
		/**
		 * @summary Replace the current state with the one supplied.
		 * @memberof FooGallery.State#
		 * @function replace
		 * @param {object} state - The state to replace the current with.
		 */
		replace: function(state){
			var self = this;
			if (self.enabled && self.apiEnabled){
				state.id = self.tmpl.id;
				var hash = self.hashify(state), empty = _is.empty(hash);
				history.replaceState(empty ? null : state, "", empty ? location.pathname + location.search : hash);
			}
		},
		/**
		 * @summary Push the supplied `state` into the browser history.
		 * @memberof FooGallery.State#
		 * @function push
		 * @param {object} state - The state to push.
		 */
		push: function(state){
			var self = this;
			if (self.enabled && self.apiEnabled){
				state.id = self.tmpl.id;
				var hash = self.hashify(state), empty = _is.empty(hash);
				history.pushState(empty ? null : state, "", empty ? location.pathname + location.search : hash);
			}
		},
		/**
		 * @summary Update the browser history using the supplied `state`.
		 * @memberof FooGallery.State#
		 * @function update
		 * @param {object} state - The state to update.
		 * @param {string} [pushOrReplace] - The method to use to update the state. If not supplied this falls back to the value of the {@link FooGallery.State#pushOrReplace|pushOrReplace} property.
		 */
		update: function(state, pushOrReplace){
			var self = this;
			if (self.enabled && self.apiEnabled){
				pushOrReplace = self.isPushOrReplace(pushOrReplace) ? pushOrReplace : self.pushOrReplace;
				self[pushOrReplace](state);
			}
		},
		/**
		 * @summary Clear the template state from the current browser history if it exists.
		 * @memberof FooGallery.State#
		 * @function clear
		 */
		clear: function(){
			if (this.exists()) this.replace({});
		},
		/**
		 * @summary Get the initial start up state of the template.
		 * @memberof FooGallery.State#
		 * @function initial
		 * @returns {FooGallery~State}
		 * @description This method returns an initial start up state from the template options.
		 */
		initial: function(){
			var self = this, tmpl = self.tmpl, state = {};
			if (tmpl.pages && tmpl.pages.current > 1) state.p = tmpl.pages.current;
			return state;
		},
		/**
		 * @summary Get the current state of the template.
		 * @memberof FooGallery.State#
		 * @function get
		 * @param {FooGallery.Item} [item] - If supplied the items' {@link FooGallery.Item#id|id} is included in the resulting state object.
		 * @returns {FooGallery~State}
		 * @description This method does not parse the history or url it returns the current state of the template itself. To parse the current url use the {@link FooGallery.State#parse|parse} method instead.
		 */
		get: function(item){
			var self = this, tmpl = self.tmpl, state = {};
			if (item instanceof _.Item) state.i = item.id;
			if (tmpl.pages && tmpl.pages.isValid(tmpl.pages.current)){
				state.p = tmpl.pages.current;
			}
			return state;
		},
		/**
		 * @summary Set the current state of the template.
		 * @memberof FooGallery.State#
		 * @function set
		 * @param {FooGallery~State} state - The state to set the template to.
		 * @description This method does not set the history or url it sets the current state of the template itself. To update the url use the {@link FooGallery.State#update|update} method instead.
		 */
		set: function(state){
			var self = this, tmpl = self.tmpl;
			if (_is.hash(state)){
				tmpl.items.reset();
				var item = tmpl.items.get(state.i);
				if (tmpl.pages){
					tmpl.pages.rebuild();
					var page = tmpl.pages.number(state.p);
					if (item && !tmpl.pages.contains(page, item)){
						page = tmpl.pages.find(item);
						page = page !== 0 ? page : 1;
					}
					tmpl.pages.set(page, !_is.empty(state), false);
					if (item && tmpl.pages.contains(page, item)){
						item.scrollTo();
					}
				} else {
					tmpl.items.detach(tmpl.items.all());
					tmpl.items.create(tmpl.items.available(), true);
					if (item){
						item.scrollTo();
					}
				}
				if (!_is.empty(state.i)){
					state.i = null;
					self.replace(state);
				}
			}
		},
	});

	_.template.configure("core", {
		state: {
			enabled: false,
			pushOrReplace: "replace",
			values: "/",
			pair: ":",
			array: "+"
		}
	});

	// register the component
	_.components.register("state", _.State);

	/**
	 * @summary An object containing the state options for the template.
	 * @typedef {object} FooGallery.State~Options
	 * @property {boolean} [enabled=false] - Whether or not state is enabled for the template.
	 * @property {string} [pushOrReplace="replace"] - Which method of the history API to use by default when updating the state.
	 * @property {string} [values="/"] - The delimiter used between key value pairs in the hash.
	 * @property {string} [pair=":"] - The delimiter used between a key and a value in the hash.
	 * @property {string} [array="+"] - The delimiter used for array values in the hash.
	 */

	/**
	 * @summary An object used to store the state of a template.
	 * @typedef {object} FooGallery~State
	 * @property {number} [p] - The current page number.
	 * @property {?string} [i] - The currently selected item.
	 */

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils.is,
	FooGallery.utils.str
);
(function($, _, _utils, _is, _fn, _obj){

	_.Item = _.Component.extend(/** @lends FooGallery.Item */{
		/**
		 * @summary The base class for an item.
		 * @memberof FooGallery
		 * @constructs Item
		 * @param {FooGallery.Template} template - The template this item belongs to.
		 * @param {FooGallery.Item~Options} [options] - The options to initialize the item with.
		 * @augments FooGallery.Component
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(template, options){
			var self = this;
			/**
			 * @ignore
			 * @memberof FooGallery.Item#
			 * @function _super
			 */
			self._super(template);
			self.cls = template.cls.item;
			self.il8n = template.il8n.item;
			self.sel = template.sel.item;
			/**
			 * @summary Whether or not the items' elements are appended to the template.
			 * @memberof FooGallery.Item#
			 * @name isAttached
			 * @type {boolean}
			 * @readonly
			 */
			self.isAttached = false;
			/**
			 * @summary Whether or not the items' elements are created and can be used.
			 * @memberof FooGallery.Item#
			 * @name isCreated
			 * @type {boolean}
			 * @readonly
			 */
			self.isCreated = false;
			/**
			 * @summary Whether or not the items' image is currently loading.
			 * @memberof FooGallery.Item#
			 * @name isLoading
			 * @type {boolean}
			 * @readonly
			 */
			self.isLoading = false;
			/**
			 * @summary Whether or not the items' image has been loaded.
			 * @memberof FooGallery.Item#
			 * @name isLoaded
			 * @type {boolean}
			 * @readonly
			 */
			self.isLoaded = false;
			/**
			 * @summary Whether or not the items' image threw an error while loading.
			 * @memberof FooGallery.Item#
			 * @name isError
			 * @type {boolean}
			 * @readonly
			 */
			self.isError = false;
			/**
			 * @summary Whether or not this item was parsed from an existing DOM element.
			 * @memberof FooGallery.Item#
			 * @name isParsed
			 * @type {boolean}
			 * @readonly
			 */
			self.isParsed = false;
			/**
			 * @memberof FooGallery.Item#
			 * @name $el
			 * @type {?jQuery}
			 */
			self.$el = null;
			/**
			 * @memberof FooGallery.Item#
			 * @name $inner
			 * @type {?jQuery}
			 */
			self.$inner = null;
			/**
			 * @memberof FooGallery.Item#
			 * @name $anchor
			 * @type {?jQuery}
			 */
			self.$anchor = null;
			/**
			 * @memberof FooGallery.Item#
			 * @name $image
			 * @type {?jQuery}
			 */
			self.$image = null;
			/**
			 * @memberof FooGallery.Item#
			 * @name $caption
			 * @type {?jQuery}
			 */
			self.$caption = null;

			options = _obj.extend({}, template.opt.item, options);

			/**
			 * @memberof FooGallery.Item#
			 * @name id
			 * @type {string}
			 */
			self.id = options.id;
			/**
			 * @memberof FooGallery.Item#
			 * @name href
			 * @type {string}
			 */
			self.href = options.href;
			/**
			 * @memberof FooGallery.Item#
			 * @name src
			 * @type {string}
			 */
			self.src = options.src;
			/**
			 * @memberof FooGallery.Item#
			 * @name srcset
			 * @type {string}
			 */
			self.srcset = options.srcset;
			/**
			 * @memberof FooGallery.Item#
			 * @name width
			 * @type {number}
			 */
			self.width = options.width;
			/**
			 * @memberof FooGallery.Item#
			 * @name height
			 * @type {number}
			 */
			self.height = options.height;
			/**
			 * @memberof FooGallery.Item#
			 * @name title
			 * @type {string}
			 */
			self.title = options.title;
			/**
			 * @memberof FooGallery.Item#
			 * @name alt
			 * @type {string}
			 */
			self.alt = options.alt;
			/**
			 * @memberof FooGallery.Item#
			 * @name caption
			 * @type {string}
			 */
			self.caption = _is.empty(options.caption) ? self.title : options.caption;
			/**
			 * @memberof FooGallery.Item#
			 * @name description
			 * @type {string}
			 */
			self.description = _is.empty(options.description) ? self.alt : options.description;
			/**
			 * @memberof FooGallery.Item#
			 * @name attrItem
			 * @type {FooGallery.Item~Attributes}
			 */
			self.attr = options.attr;
			/**
			 * @memberof FooGallery.Item#
			 * @name tags
			 * @type {string[]}
			 */
			self.tags = options.tags;
			/**
			 * @memberof FooGallery.Item#
			 * @name maxWidth
			 * @type {?FooGallery.Item~maxWidthCallback}
			 */
			self.maxWidth = options.maxWidth;
			/**
			 * @memberof FooGallery.Item#
			 * @name maxCaptionLength
			 * @type {number}
			 */
			self.maxCaptionLength = options.maxCaptionLength;
			/**
			 * @memberof FooGallery.Item#
			 * @name maxDescriptionLength
			 * @type {number}
			 */
			self.maxDescriptionLength = options.maxDescriptionLength;
			/**
			 * @memberof FooGallery.Item#
			 * @name showCaptionTitle
			 * @type {boolean}
			 */
			self.showCaptionTitle = options.showCaptionTitle;
			/**
			 * @memberof FooGallery.Item#
			 * @name showCaptionDescription
			 * @type {boolean}
			 */
			self.showCaptionDescription = options.showCaptionDescription;
			/**
			 * @summary The cached result of the last call to the {@link FooGallery.Item#getThumbUrl|getThumbUrl} method.
			 * @memberof FooGallery.Item#
			 * @name _thumbUrl
			 * @type {string}
			 * @private
			 */
			self._thumbUrl = null;
			/**
			 * @summary This property is used to store the promise created when loading an item for the first time.
			 * @memberof FooGallery.Item#
			 * @name _load
			 * @type {?Promise}
			 * @private
			 */
			self._load = null;
		},
		/**
		 * @summary Destroy the item preparing it for garbage collection.
		 * @memberof FooGallery.Item#
		 * @function destroy
		 */
		destroy: function(){
			var self = this;
			/**
			 * @summary Raised when a template destroys an item.
			 * @event FooGallery.Template~"destroy-item.foogallery"
			 * @type {jQuery.Event}
			 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
			 * @param {FooGallery.Template} template - The template raising the event.
			 * @param {FooGallery.Item} item - The item to destroy.
			 * @returns {boolean} `true` if the {@link FooGallery.Item|`item`} has been successfully destroyed.
			 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"destroy-item.foogallery": function(event, template, item){
			 * 			// do something
			 * 		}
			 * 	}
			 * });
			 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent the `item` being destroyed.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"destroy-item.foogallery": function(event, template, item){
			 * 			if ("some condition"){
			 * 				// stop the item being destroyed
			 * 				event.preventDefault();
			 * 			}
			 * 		}
			 * 	}
			 * });
			 * @example {@caption You can also prevent the default logic and replace it with your own by calling the `preventDefault` method on the `event` object.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"destroy-item.foogallery": function(event, template, item){
			 * 			// stop the default logic
			 * 			event.preventDefault();
			 * 			// replacing it with your own destroying the item yourself
			 * 			item.$el.off(".foogallery").remove();
			 * 			item.$el = null;
			 * 			...
			 * 			// once all destroy work is complete you must set tmpl to null
			 * 			item.tmpl = null;
			 * 		}
			 * 	}
			 * });
			 */
			var e = self.tmpl.raise("destroy-item");
			if (!e.isDefaultPrevented()){
				if (self.isParsed && !self.isAttached){
					self.append();
				} else if (!self.isParsed && self.isAttached) {
					self.detach();
				}
				self._super();
			}
			return self.tmpl === null;
		},
		/**
		 * @summary Parse the supplied element updating the current items' properties.
		 * @memberof FooGallery.Item#
		 * @function parse
		 * @param {(jQuery|HTMLElement|string)} element - The element to parse.
		 * @returns {boolean}
		 * @fires FooGallery.Template~"parse-item.foogallery"
		 * @fires FooGallery.Template~"parsed-item.foogallery"
		 */
		parse: function(element){
			var self = this, o = self.tmpl.opt, cls = self.cls, sel = self.sel, $el = $(element);
			/**
			 * @summary Raised when an item needs to parse properties from an element.
			 * @event FooGallery.Template~"parse-item.foogallery"
			 * @type {jQuery.Event}
			 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
			 * @param {FooGallery.Template} template - The template raising the event.
			 * @param {FooGallery.Item} item - The item to populate.
			 * @param {jQuery} $element - The jQuery object of the element to parse.
			 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"parse-item.foogallery": function(event, template, item, $element){
			 * 			// do something
			 * 		}
			 * 	}
			 * });
			 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent the `item` properties being parsed from the `element`.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"parse-item.foogallery": function(event, template, item, $element){
			 * 			if ("some condition"){
			 * 				// stop the item being parsed
			 * 				event.preventDefault();
			 * 			}
			 * 		}
			 * 	}
			 * });
			 * @example {@caption You can also prevent the default logic and replace it with your own by calling the `preventDefault` method on the `event` object and then populating the `item` properties from the `element`.}
			 * $(".foogallery").foogallery({
			 * 	on: {
			 * 		"parse-item.foogallery": function(event, template, item, $element){
			 * 			// stop the default logic
			 * 			event.preventDefault();
			 * 			// replacing it with your own setting each property of the item yourself
			 * 			item.$el = $element;
			 * 			...
			 * 			// once all properties are set you must set isParsed to true
			 * 			item.isParsed = true;
			 * 		}
			 * 	}
			 * });
			 */
			var e = self.tmpl.raise("parse-item", [self, $el]);
			if (!e.isDefaultPrevented() && (self.isCreated = $el.is(sel.elem))){
				self.$el = $el.data(_.dataItem, self);
				self.$inner = self.$el.find(sel.inner);
				self.$anchor = self.$el.find(sel.anchor).on("click.foogallery", {self: self}, self.onAnchorClick);
				self.$image = self.$anchor.find(sel.image);
				self.$caption = self.$el.find(sel.caption.elem).on("click.foogallery", {self: self}, self.onCaptionClick);
				self.isAttached = self.$el.parent().length > 0;
				self.isLoading = self.$el.is(sel.loading);
				self.isLoaded = self.$el.is(sel.loaded);
				self.isError = self.$el.is(sel.error);
				self.id = self.$anchor.data("id");
				self.tags = self.$anchor.data("tags");
				self.href = self.$anchor.attr("href");
				self.src = self.$image.attr(o.src);
				self.srcset = self.$image.attr(o.srcset);
				self.width = parseInt(self.$image.attr("width"));
				self.height = parseInt(self.$image.attr("height"));
				self.title = self.$image.attr("title");
				self.alt = self.$image.attr("alt");
				self.caption = self.$anchor.data("title") || self.$anchor.data("captionTitle");
				self.description = self.$anchor.data("description") || self.$anchor.data("captionDesc");
				// if the caption or description are not provided set there values to the title and alt respectively
				if (_is.empty(self.caption)) self.caption = self.title;
				if (_is.empty(self.description)) self.description = self.alt;
				// enforce the max lengths for the caption and description
				if (_is.number(self.maxCaptionLength) && self.maxCaptionLength > 0 && !_is.empty(self.caption) && _is.string(self.caption) && self.caption.length > self.maxCaptionLength){
					self.$caption.find(sel.caption.title).html(self.caption.substr(0, self.maxCaptionLength) + "&hellip;");
				}
				if (_is.number(self.maxDescriptionLength) && self.maxDescriptionLength > 0 && !_is.empty(self.description) && _is.string(self.description) && self.description.length > self.maxDescriptionLength){
					self.$caption.find(sel.caption.description).html(self.description.substr(0, self.maxDescriptionLength) + "&hellip;");
				}
				// check if the item has a loader
				if (self.$el.find(sel.loader).length === 0){
					self.$el.append($("<div/>", {"class": cls.loader}));
				}
				// if the image has no src url then set the placeholder
				if (_is.empty(self.$image.prop("src"))){
					self.$image.prop("src", self.tmpl.items.placeholder(self.width, self.height));
				}
				if (self.isCreated && self.isAttached && !self.isLoading && !self.isLoaded && !self.isError){
					self.$el.addClass(cls.idle);
				}
				self.fix();
				self.isParsed = true;
				// We don't load the attributes when parsing as they are only ever used to create an item and if you're parsing it's already created.
			}
			if (self.isParsed){
				/**
				 * @summary Raised after an item has been parsed from an element.
				 * @event FooGallery.Template~"parsed-item.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item} item - The item that was parsed.
				 * @param {jQuery} $element - The jQuery object of the element that was parsed.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"parsed-item.foogallery": function(event, template, item, $element){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 */
				self.tmpl.raise("parsed-item", [self]);
			}
			return self.isParsed;
		},
		/**
		 * @summary Create the items' DOM elements and populate the corresponding properties.
		 * @memberof FooGallery.Item#
		 * @function create
		 * @returns {boolean}
		 * @fires FooGallery.Template~"create-item.foogallery"
		 * @fires FooGallery.Template~"created-item.foogallery"
		 */
		create: function(){
			var self = this;
			if (!self.isCreated && _is.string(self.href) && _is.string(self.src) && _is.number(self.width) && _is.number(self.height)){
				/**
				 * @summary Raised when an item needs to create its' elements.
				 * @event FooGallery.Template~"create-item.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item} item - The item to create the elements for.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"create-item.foogallery": function(event, template, item){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent the `item` being created.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"create-item.foogallery": function(event, template, item){
				 * 			if ("some condition"){
				 * 				// stop the item being created
				 * 				event.preventDefault();
				 * 			}
				 * 		}
				 * 	}
				 * });
				 * @example {@caption You can also prevent the default logic and replace it with your own by calling the `preventDefault` method on the `event` object.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"create-item.foogallery": function(event, template, item){
				 * 			// stop the default logic
				 * 			event.preventDefault();
				 * 			// replacing it with your own creating each element property of the item yourself
				 * 			item.$el = $("<div/>");
				 * 			...
				 * 			// once all elements are created you must set isCreated to true
				 * 			item.isCreated = true;
				 * 		}
				 * 	}
				 * });
				 */
				var e = self.tmpl.raise("create-item", [self]);
				if (!e.isDefaultPrevented()){
					var o = self.tmpl.opt, cls = self.cls, attr = self.attr;
					attr.elem["class"] = cls.elem + " " + cls.idle;

					attr.inner["class"] = cls.inner;

					attr.anchor["class"] = cls.anchor;
					attr.anchor["href"] = self.href;
					attr.anchor["data-id"] = self.id;
					attr.anchor["data-title"] = self.caption;
					attr.anchor["data-description"] = self.description;
					if (!_is.empty(self.tags)){
						attr.anchor["data-tags"] = JSON.stringify(self.tags);
					}

					attr.image["class"] = cls.image;
					attr.image["src"] = self.tmpl.items.placeholder(self.width, self.height);
					attr.image[o.src] = self.src;
					attr.image[o.srcset] = self.srcset;
					attr.image["width"] = self.width;
					attr.image["height"] = self.height;
					attr.image["title"] = self.title;
					attr.image["alt"] = self.alt;

					self.$el = $("<div/>").attr(attr.elem).data(_.dataItem, self);
					self.$inner = $("<figure/>").attr(attr.inner).appendTo(self.$el);
					self.$anchor = $("<a/>").attr(attr.anchor).appendTo(self.$inner).on("click.foogallery", {self: self}, self.onAnchorClick);
					self.$image = $("<img/>").attr(attr.image).appendTo(self.$anchor);

					cls = self.cls.caption;
					attr = self.attr.caption;
					attr.elem["class"] = cls.elem;
					self.$caption = $("<figcaption/>").attr(attr.elem).on("click.foogallery", {self: self}, self.onCaptionClick);
					var hasTitle = !_is.empty(self.caption), hasDesc = !_is.empty(self.description);
					if (hasTitle || hasDesc){
						attr.inner["class"] = cls.inner;
						attr.title["class"] = cls.title;
						attr.description["class"] = cls.description;
						var $inner = $("<div/>").attr(attr.inner).appendTo(self.$caption);
						if (hasTitle){
							var $title;
							// enforce the max length for the caption
							if (_is.number(self.maxCaptionLength) && self.maxCaptionLength  > 0 && _is.string(self.caption) && self.caption.length > self.maxCaptionLength){
								$title = $("<div/>").attr(attr.title).html(self.caption.substr(0, self.maxCaptionLength) + "&hellip;");
							} else {
								$title = $("<div/>").attr(attr.title).html(self.caption);
							}
							$inner.append($title);
						}
						if (hasDesc){
							var $desc;
							// enforce the max length for the description
							if (_is.number(self.maxDescriptionLength) && self.maxDescriptionLength  > 0 && _is.string(self.description) && self.description.length > self.maxDescriptionLength){
								$desc = $("<div/>").attr(attr.description).html(self.description.substr(0, self.maxDescriptionLength) + "&hellip;");
							} else {
								$desc = $("<div/>").attr(attr.description).html(self.description);
							}
							$inner.append($desc);
						}
					}
					self.$caption.appendTo(self.$inner);
					// check if the item has a loader
					if (self.$el.find(self.sel.loader).length === 0){
						self.$el.append($("<div/>", {"class": self.cls.loader}));
					}
					self.isCreated = true;
				}
				if (self.isCreated){
					/**
					 * @summary Raised after an items' elements have been created.
					 * @event FooGallery.Template~"created-item.foogallery"
					 * @type {jQuery.Event}
					 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
					 * @param {FooGallery.Template} template - The template raising the event.
					 * @param {FooGallery.Item} item - The item that was created.
					 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
					 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"created-item.foogallery": function(event, template, item){
					 * 			// do something
					 * 		}
					 * 	}
					 * });
					 */
					self.tmpl.raise("created-item", [self]);
				}
			}
			return self.isCreated;
		},
		/**
		 * @summary Append the item to the current template.
		 * @memberof FooGallery.Item#
		 * @function append
		 * @returns {boolean}
		 * @fires FooGallery.Template~"append-item.foogallery"
		 * @fires FooGallery.Template~"appended-item.foogallery"
		 */
		append: function(){
			var self = this;
			if (self.isCreated && !self.isAttached){
				/**
				 * @summary Raised when an item needs to append its elements to the template.
				 * @event FooGallery.Template~"append-item.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item} item - The item to append to the template.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"append-item.foogallery": function(event, template, item){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent the `item` being appended.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"append-item.foogallery": function(event, template, item){
				 * 			if ("some condition"){
				 * 				// stop the item being appended
				 * 				event.preventDefault();
				 * 			}
				 * 		}
				 * 	}
				 * });
				 * @example {@caption You can also prevent the default logic and replace it with your own by calling the `preventDefault` method on the `event` object.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"append-item.foogallery": function(event, template, item){
				 * 			// stop the default logic
				 * 			event.preventDefault();
				 * 			// replacing it with your own appending the item to the template
				 * 			item.$el.appendTo(template.$el);
				 * 			...
				 * 			// once the item is appended you must set isAttached to true
				 * 			item.isAttached = true;
				 * 		}
				 * 	}
				 * });
				 */
				var e = self.tmpl.raise("append-item", [self]);
				if (!e.isDefaultPrevented()){
					self.tmpl.$el.append(self.$el);
					self.fix();
					self.isAttached = true;
				}
				if (self.isAttached){
					/**
					 * @summary Raised after an item has appended its' elements to the template.
					 * @event FooGallery.Template~"appended-item.foogallery"
					 * @type {jQuery.Event}
					 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
					 * @param {FooGallery.Template} template - The template raising the event.
					 * @param {FooGallery.Item} item - The item that was appended.
					 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
					 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"appended-item.foogallery": function(event, template, item){
					 * 			// do something
					 * 		}
					 * 	}
					 * });
					 */
					self.tmpl.raise("appended-item", [self]);
				}
			}
			return self.isAttached;
		},
		/**
		 * @summary Detach the item from the current template preserving its' data and events.
		 * @memberof FooGallery.Item#
		 * @function detach
		 * @returns {boolean}
		 */
		detach: function(){
			var self = this;
			if (self.isCreated && self.isAttached){
				/**
				 * @summary Raised when an item needs to detach its' elements from the template.
				 * @event FooGallery.Template~"detach-item.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item} item - The item to detach from the template.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"detach-item.foogallery": function(event, template, item){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent the `item` being detached.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"detach-item.foogallery": function(event, template, item){
				 * 			if ("some condition"){
				 * 				// stop the item being detached
				 * 				event.preventDefault();
				 * 			}
				 * 		}
				 * 	}
				 * });
				 * @example {@caption You can also prevent the default logic and replace it with your own by calling the `preventDefault` method on the `event` object.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"detach-item.foogallery": function(event, template, item){
				 * 			// stop the default logic
				 * 			event.preventDefault();
				 * 			// replacing it with your own detaching the item from the template
				 * 			item.$el.detach();
				 * 			...
				 * 			// once the item is detached you must set isAttached to false
				 * 			item.isAttached = false;
				 * 		}
				 * 	}
				 * });
				 */
				var e = self.tmpl.raise("detach-item", [self]);
				if (!e.isDefaultPrevented()){
					self.$el.detach();
					self.unfix();
					self.isAttached = false;
				}
				if (!self.isAttached){
					/**
					 * @summary Raised after an item has detached its' elements from the template.
					 * @event FooGallery.Template~"detached-item.foogallery"
					 * @type {jQuery.Event}
					 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
					 * @param {FooGallery.Template} template - The template raising the event.
					 * @param {FooGallery.Item} item - The item that was detached.
					 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
					 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"detached-item.foogallery": function(event, template, item){
					 * 			// do something
					 * 		}
					 * 	}
					 * });
					 */
					self.tmpl.raise("detached-item", [self]);
				}
			}
			return !self.isAttached;
		},
		/**
		 * @summary Load the items' {@link FooGallery.Item#$image|$image}.
		 * @memberof FooGallery.Item#
		 * @function load
		 * @returns {Promise.<FooGallery.Item>}
		 */
		load: function(){
			var self = this;
			if (_is.promise(self._load)) return self._load;
			if (!self.isCreated || !self.isAttached) return _fn.rejectWith("not created or attached");
			var e = self.tmpl.raise("load-item", [self]);
			if (e.isDefaultPrevented()) return _fn.rejectWith("default prevented");
			var cls = self.cls, img = self.$image.get(0), placeholder = img.src;
			self.isLoading = true;
			self.$el.removeClass(cls.idle).removeClass(cls.loaded).removeClass(cls.error).addClass(cls.loading);
			return self._load = $.Deferred(function (def) {
				// if Firefox reset to empty src or else the onload and onerror callbacks are executed immediately
				if (!_is.undef(window.InstallTrigger)) img.src = "";
				img.onload = function () {
					img.onload = img.onerror = null;
					self.isLoading = false;
					self.isLoaded = true;
					self.$el.removeClass(cls.loading).addClass(cls.loaded);
					self.unfix();
					self.tmpl.raise("loaded-item", [self]);
					def.resolve(self);
				};
				img.onerror = function () {
					img.onload = img.onerror = null;
					self.isLoading = false;
					self.isError = true;
					self.$el.removeClass(cls.loading).addClass(cls.error);
					if (_is.string(placeholder)) {
						self.$image.prop("src", placeholder);
					}
					self.tmpl.raise("error-item", [self]);
					def.reject(self);
				};
				// set everything in motion by setting the src
				img.src = self.getThumbUrl();
			}).promise();
		},
		/**
		 * @summary Attempts to set a inline width and height on the {@link FooGallery.Item#$image|$image} to prevent layout jumps.
		 * @memberof FooGallery.Item#
		 * @function fix
		 * @returns {FooGallery.Item}
		 */
		fix: function(){
			var self = this;
			if (self.isCreated && !self.isLoading && !self.isLoaded && !self.isError){
				var w = self.width, h = self.height;
				// if we have a base width and height to work with
				if (!isNaN(w) && !isNaN(h)){
					// figure out the max image width and calculate the height the image should be displayed as
					var width = _is.fn(self.maxWidth) ? self.maxWidth(self) : self.$image.width();
					if (width <= 0) width = w;
					var ratio = width / w, height = h * ratio;
					// actually set the inline css on the image
					self.$image.css({width: width,height: height});
				}
			}
			return self;
		},
		/**
		 * @summary Removes any inline width and height values set on the {@link FooGallery.Item#$image|$image}.
		 * @memberof FooGallery.Item#
		 * @function unfix
		 * @returns {FooGallery.Item}
		 */
		unfix: function(){
			var self = this;
			if (self.isCreated) self.$image.css({width: '',height: ''});
			return self;
		},
		/**
		 * @summary Inspect the `src` and `srcset` properties to determine which url to load for the thumb.
		 * @memberof FooGallery.Item#
		 * @function getThumbUrl
		 * @param {boolean} [refresh=false] - Whether or not to force refreshing of the cached value.
		 * @returns {string}
		 */
		getThumbUrl: function(refresh){
			refresh = _is.boolean(refresh) ? refresh : false;
			var self = this;
			if (!refresh && _is.string(self._thumbUrl)) return self._thumbUrl;
			return self._thumbUrl = _.parseSrc(self.src, self.width, self.height, self.srcset, self.$anchor.innerWidth(), self.$anchor.innerHeight());
		},
		/**
		 * @summary Scroll the item into the center of the viewport.
		 * @memberof FooGallery.Item#
		 * @function scrollTo
		 */
		scrollTo: function(align){
			var self = this;
			if (self.isAttached){
				var ib = self.bounds(), vb = _utils.getViewportBounds();
				switch(align){
					case "top": // attempts to center the item horizontally but aligns the top with the middle of the viewport
						ib.left += (ib.width / 2) - (vb.width / 2);
						ib.top -= (vb.height / 5);
						break;
					default: // attempts to center the item in the viewport
						ib.left += (ib.width / 2) - (vb.width / 2);
						ib.top += (ib.height / 2) - (vb.height / 2);
						break;
				}
				window.scrollTo(ib.left, ib.top);
			}
			return self;
		},
		/**
		 * @summary Get the bounds for the item.
		 * @memberof FooGallery.Item#
		 * @function bounds
		 * @returns {?FooGallery.utils.Bounds}
		 */
		bounds: function(){
			return this.isAttached ? _utils.getElementBounds(this.$el) : null;
		},
		/**
		 * @summary Checks if the item bounds intersects the supplied bounds.
		 * @memberof FooGallery.Item#
		 * @function intersects
		 * @param {FooGallery.utils.Bounds} bounds - The bounds to check.
		 * @returns {boolean}
		 */
		intersects: function(bounds){
			return this.isAttached ? this.bounds().intersects(bounds) : false;
		},
		/**
		 * @summary Listens for the click event on the {@link FooGallery.Item#$anchor|$anchor} element and updates the state if enabled.
		 * @memberof FooGallery.Item#
		 * @function onAnchorClick
		 * @param {jQuery.Event} e - The jQuery.Event object for the click event.
		 * @private
		 */
		onAnchorClick: function(e){
			var self = e.data.self,
				state = self.tmpl.state.get(self);
			self.tmpl.state.update(state);
		},
		/**
		 * @summary Listens for the click event on the {@link FooGallery.Item#$caption|$caption} element and redirects it to the anchor if required.
		 * @memberof FooGallery.Item#
		 * @function onCaptionClick
		 * @param {jQuery.Event} e - The jQuery.Event object for the click event.
		 * @private
		 */
		onCaptionClick: function(e){
			var self = e.data.self;
			if ($(e.target).is(self.sel.caption.all) && self.$anchor.length > 0){
				self.$anchor.get(0).click();
			}
		}
	});

	/**
	 * @summary Called when setting an items' image size to prevent layout jumps.
	 * @callback FooGallery.Item~maxWidthCallback
	 * @param {FooGallery.Item} item - The item to determine the maxWidth for.
	 * @returns {number} Returns the maximum width allowed for the {@link FooGallery.Item#$image|$image} element.
	 * @example {@caption An example of the default behavior this callback replaces would look like the below.}
	 * {
	 * 	"maxWidth": function(item){
	 * 		return item.$image.outerWidth();
	 * 	}
	 * }
	 */

	/**
	 * @summary A simple object containing an items' default values.
	 * @typedef {object} FooGallery.Item~Options
	 * @property {?string} [id=null] - The `data-id` attribute for the outer element.
	 * @property {?string} [href=null] - The `href` attribute for the anchor element.
	 * @property {?string} [src=null] - The `src` attribute for the image element.
	 * @property {?string} [srcset=null] - The `srcset` attribute for the image element.
	 * @property {number} [width=0] - The width of the image.
	 * @property {number} [height=0] - The height of the image.
	 * @property {?string} [title=null] - The title for the image. This should be plain text.
	 * @property {?string} [alt=null] - The alt for the image. This should be plain text.
	 * @property {?string} [caption=null] - The caption for the image. This can contain HTML content.
	 * @property {?string} [description=null] - The description for the image. This can contain HTML content.
	 * @property {string[]} [tags=[]] - The `data-tags` attribute for the outer element.
	 * @property {?FooGallery.Item~maxWidthCallback} [maxWidth=null] - Called when setting an items' image size. If not supplied the images outer width is used.
	 * @property {number} [maxCaptionLength=0] - The max length of the title for the caption.
	 * @property {number} [maxDescriptionLength=0] - The max length of the description for the caption.
	 * @property {boolean} [showCaptionTitle=true] - Whether or not the caption title should be displayed.
	 * @property {boolean} [showCaptionDescription=true] - Whether or not the caption description should be displayed.
	 * @property {FooGallery.Item~Attributes} [attr] - Additional attributes to apply to the items' elements.
	 */
	_.template.configure("core", {
		item: {
			id: "",
			href: "",
			src: "",
			srcset: "",
			width: 0,
			height: 0,
			title: "",
			alt: "",
			caption: "",
			description: "",
			tags: [],
			maxWidth: null,
			maxCaptionLength: 0,
			maxDescriptionLength: 0,
			showCaptionTitle: true,
			showCaptionDescription: true,
			attr: {
				elem: {},
				inner: {},
				anchor: {},
				image: {},
				caption: {
					elem: {},
					inner: {},
					title: {},
					description: {}
				}
			}
		}
	}, {
		item: {
			elem: "fg-item",
			inner: "fg-item-inner",
			anchor: "fg-thumb",
			image: "fg-image",
			loader: "fg-loader",
			idle: "fg-idle",
			loading: "fg-loading",
			loaded: "fg-loaded",
			error: "fg-error",
			caption: {
				elem: "fg-caption",
				inner: "fg-caption-inner",
				title: "fg-caption-title",
				description: "fg-caption-desc"
			}
		}
	});

	_.components.register("item", _.Item);

	// ######################
	// ## Type Definitions ##
	// ######################

	/**
	 * @summary A simple object containing the CSS classes used by an item.
	 * @typedef {object} FooGallery.Item~CSSClasses
	 * @property {string} [elem="fg-item"] - The CSS class for the outer containing `div` element of an item.
	 * @property {string} [inner="fg-item-inner"] - The CSS class for the inner containing `div` element of an item.
	 * @property {string} [anchor="fg-thumb"] - The CSS class for the `a` element of an item.
	 * @property {string} [image="fg-image"] - The CSS class for the `img` element of an item.
	 * @property {string} [loading="fg-idle"] - The CSS class applied to an item that is waiting to be loaded.
	 * @property {string} [loading="fg-loading"] - The CSS class applied to an item while it is loading.
	 * @property {string} [loaded="fg-loaded"] - The CSS class applied to an item once it is loaded.
	 * @property {string} [error="fg-error"] - The CSS class applied to an item if it throws an error while loading.
	 * @property {object} [caption] - A simple object containing the CSS classes used by an items' caption.
	 * @property {string} [caption.elem="fg-caption"] - The CSS class for the outer containing `div` element of a caption.
	 * @property {string} [caption.inner="fg-caption-inner"] - The CSS class for the inner containing `div` element of a caption.
	 * @property {string} [caption.title="fg-caption-title"] - The CSS class for the title `div` element of a caption.
	 * @property {string} [caption.description="fg-caption-desc"] - The CSS class for the description `div` element of a caption.
	 */
	/**
	 * @summary A simple object used to store any additional attributes to apply to an items' elements.
	 * @typedef {object} FooGallery.Item~Attributes
	 * @property {object} [elem={}] - The attributes to apply to the items' outer `<div/>` element.
	 * @property {object} [inner={}] - The attributes to apply to the items' inner element.
	 * @property {object} [anchor={}] - The attributes to apply to the items' anchor element.
	 * @property {object} [image={}] - The attributes to apply to the items' image element.
	 * @property {object} [caption] - A simple object used to store any additional attributes to apply to an items' caption elements.
	 * @property {object} [caption.elem={}] - The attributes to apply to the captions' outer `<div/>` element.
	 * @property {object} [caption.inner={}] - The attributes to apply to the captions' inner element.
	 * @property {object} [caption.title={}] - The attributes to apply to the captions' title element.
	 * @property {object} [caption.description={}] - The attributes to apply to the captions' description element.
	 */

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.obj
);
(function($, _, _utils, _is, _fn){

	_.Items = _.Component.extend(/** @lends FooGallery.Items */{
		/**
		 * @summary This class controls everything related to items and serves as the base class for the various paging types.
		 * @memberof FooGallery
		 * @constructs Items
		 * @param {FooGallery.Template} template - The template for this component.
		 * @augments FooGallery.Component
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(template){
			var self = this;
			/**
			 * @ignore
			 * @memberof FooGallery.Items#
			 * @function _super
			 */
			self._super(template);
			self.idMap = {};
			self._fetched = null;
			self._arr = [];
			self._available = [];
			self._canvas = document.createElement("canvas");
			// add the .all caption selector
			var cls = self.tmpl.cls.item.caption;
			self.tmpl.sel.item.caption.all = _utils.selectify([cls.elem, cls.inner, cls.title, cls.description]);
		},
		destroy: function(){
			var self = this, items = self.all(), destroyed = [];
			if (items.length > 0) {
				/**
				 * @summary Raised before the template destroys its' items.
				 * @event FooGallery.Template~"destroy-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items about to be destroyed.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"destroy-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 */
				self.tmpl.raise("destroy-items", [items]);
				destroyed = $.map(items, function(item){
					return item.destroy() ? item : null;
				});
				/**
				 * @summary Raised after the template has destroyed items.
				 * @event FooGallery.Template~"destroyed-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items destroyed.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"destroyed-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 */
				if (destroyed.length > 0) self.tmpl.raise("destroyed-items", [destroyed]);
				// should we handle a case where the destroyed.length != items.length??
			}
			self.idMap = {};
			self._canvas = self._fetched = null;
			self._arr = [];
			self._available = [];
			self._super();
		},
		fetch: function(refresh){
			var self = this;
			if (!refresh && _is.promise(self._fetched)) return self._fetched;
			var fg = self.tmpl, selectors = fg.sel,
				option = fg.opt.items,
				def = $.Deferred();

			var items = self.make(fg.$el.find(selectors.item.elem));

			if (!_is.empty(option)){
				if (_is.array(option)){
					items.push.apply(items, self.make(option));
					def.resolve(items);
				} else if (_is.string(option)){
					$.get(option).then(function(response){
						items.push.apply(items, self.make(response));
						def.resolve(items);
					}, function( jqXHR, textStatus, errorThrown ){
						console.log("FooGallery: GET items error.", option, jqXHR, textStatus, errorThrown);
						def.resolve(items);
					});
				} else {
					def.resolve(items);
				}
			} else {
				items.push.apply(items, self.make(window[fg.id + "-items"]));
				def.resolve(items);
			}
			def.then(function(items){
				self._arr = items;
				self.idMap = self.createIdMap(items);
				self.setAvailable(self.all());
			});
			return self._fetched = def.promise();
		},
		all: function(){
			return this._arr.slice();
		},
		available: function(){
			return this._available.slice();
		},
		get: function(id){
			return !_is.empty(id) && !!this.idMap[id] ? this.idMap[id] : null;
		},
		setAvailable: function(items){
			this._available = _is.array(items) ? items : [];
		},
		reset: function(){
			this.setAvailable(this.all());
		},
		placeholder: function(width, height){
			if (this._canvas && this._canvas.toDataURL && _is.number(width) && _is.number(height)){
				this._canvas.width = width;
				this._canvas.height = height;
				return this._canvas.toDataURL();
			}
			return _.emptyImage;
		},
		/**
		 * @summary Filter the supplied `items` and return only those that can be loaded.
		 * @memberof FooGallery.Items#
		 * @function loadable
		 * @param {FooGallery.Item[]} items - The items to filter.
		 * @returns {FooGallery.Item[]}
		 */
		loadable: function(items){
			var self = this, opt = self.tmpl.opt, viewport;
			if (opt.lazy){
				viewport = _utils.getViewportBounds(opt.viewport);
			}
			return _is.array(items) ? $.map(items, function(item){
				return item.isCreated && item.isAttached && !item.isLoading && !item.isLoaded && !item.isError && (!opt.lazy || (opt.lazy && item.intersects(viewport))) ? item : null;
			}) : [];
		},
		/**
		 * @summary Filter the supplied `items` and return only those that can be created.
		 * @memberof FooGallery.Items#
		 * @function creatable
		 * @param {FooGallery.Item[]} items - The items to filter.
		 * @returns {FooGallery.Item[]}
		 */
		creatable: function(items){
			return _is.array(items) ? $.map(items, function(item){
				return item instanceof _.Item && !item.isCreated ? item : null;
			}) : [];
		},
		/**
		 * @summary Filter the supplied `items` and return only those that can be appended.
		 * @memberof FooGallery.Items#
		 * @function appendable
		 * @param {FooGallery.Item[]} items - The items to filter.
		 * @returns {FooGallery.Item[]}
		 */
		appendable: function(items){
			return _is.array(items) ? $.map(items, function(item){
					return item instanceof _.Item && item.isCreated && !item.isAttached ? item : null;
				}) : [];
		},
		/**
		 * @summary Filter the supplied `items` and return only those that can be detached.
		 * @memberof FooGallery.Items#
		 * @function detachable
		 * @param {FooGallery.Item[]} items - The items to filter.
		 * @returns {FooGallery.Item[]}
		 */
		detachable: function(items){
			return _is.array(items) ? $.map(items, function(item){
				return item instanceof _.Item && item.isCreated && item.isAttached ? item : null;
			}) : [];
		},
		/**
		 * @summary Get a single jQuery object containing all the supplied items' elements.
		 * @memberof FooGallery.Items#
		 * @function jquerify
		 * @param {FooGallery.Item[]} items - The items to get a jQuery object for.
		 * @returns {jQuery}
		 */
		jquerify: function(items){
			return $($.map(items, function (item) {
				return item.$el.get();
			}));
		},
		/**
		 * @summary Makes a jQuery object, NodeList or an array of elements or item options, into an array of {@link FooGallery.Item|item} objects.
		 * @memberof FooGallery.Items#
		 * @function make
		 * @param {(jQuery|NodeList|Node[]|FooGallery.Item~Options[])} items - The value to convert into an array of items.
		 * @returns {FooGallery.Item[]} The array of items successfully made.
		 * @fires FooGallery.Template~"make-items.foogallery"
		 * @fires FooGallery.Template~"made-items.foogallery"
		 * @fires FooGallery.Template~"parsed-items.foogallery"
		 */
		make: function(items){
			var self = this, made = [];
			if (_is.jq(items) || _is.array(items)){
				var parsed = [], arr = $.makeArray(items);
				if (arr.length === 0) return made;
				/**
				 * @summary Raised before the template makes an array of elements or item options into an array of {@link FooGallery.Item|item} objects.
				 * @event FooGallery.Template~"make-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {(HTMLElement[]|FooGallery.Item~Options[])} items - The array of Nodes or item options.
				 * @returns {(HTMLElement[]|FooGallery.Item~Options[])} A filtered list of items to make.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"make-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent any items being made.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"make-items.foogallery": function(event, template, items){
				 * 			if ("some condition"){
				 * 				// stop any items being made
				 * 				event.preventDefault();
				 * 			}
				 * 		}
				 * 	}
				 * });
				 */
				var e = self.tmpl.raise("make-items", [arr]);
				if (!e.isDefaultPrevented()){
					made = $.map(arr, function(obj){
						var item = _.components.make("item", self.tmpl, _is.hash(obj) ? obj : {});
						if (_is.element(obj)){
							if (item.parse(obj)){
								parsed.push(item);
								return item;
							}
							return null;
						}
						return item;
					});
				}

				/**
				 * @summary Raised after the template has made an array of {@link FooGallery.Item|item} objects.
				 * @event FooGallery.Template~"made-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items made, this includes parsed items.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"made-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 */
				if (made.length > 0) self.tmpl.raise("made-items", [made]);

				/**
				 * @summary Raised after the template has parsed any elements into an array of {@link FooGallery.Item|item} objects.
				 * @event FooGallery.Template~"parsed-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items parsed.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"parsed-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 */
				if (parsed.length > 0) self.tmpl.raise("parsed-items", [parsed]);
			}
			return made;
		},
		/**
		 * @summary Create each of the supplied {@link FooGallery.Item|`items`} elements.
		 * @memberof FooGallery.Items#
		 * @function create
		 * @param {FooGallery.Item[]} items - The array of items to create.
		 * @param {boolean} [append=false] - Whether or not to automatically append the item after it is created.
		 * @returns {FooGallery.Item[]} The array of items that were created or if `append` is `true` the array of items that were appended.
		 * @description This will only create and/or append items that are not already created and/or appended so it is safe to call without worrying about the items' pre-existing state.
		 * @fires FooGallery.Template~"create-items.foogallery"
		 * @fires FooGallery.Template~"created-items.foogallery"
		 * @fires FooGallery.Template~"append-items.foogallery"
		 * @fires FooGallery.Template~"appended-items.foogallery"
		 */
		create: function(items, append){
			var self = this, created = [], creatable = self.creatable(items);
			if (creatable.length > 0) {
				/**
				 * @summary Raised before the template creates the `items` elements.
				 * @event FooGallery.Template~"create-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items to create.
				 * @param {boolean} [append=false] - Whether or not to automatically append the item after it is created.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"create-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent any items being created.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"create-items.foogallery": function(event, template, items){
				 * 			if ("some condition"){
				 * 				// stop any items being created
				 * 				event.preventDefault();
				 * 			}
				 * 		}
				 * 	}
				 * });
				 */
				var e = self.tmpl.raise("create-items", [creatable]);
				if (!e.isDefaultPrevented()){
					created = $.map(creatable, function(item){
						return item.create() ? item : null;
					});
				}
				/**
				 * @summary Raised after the template has created the `items` elements.
				 * @event FooGallery.Template~"created-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items created.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"created-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 */
				if (created.length > 0) self.tmpl.raise("created-items", [created]);
			}
			if (_is.boolean(append) ? append : false) return self.append(items);
			return created;
		},
		/**
		 * @summary Append each of the supplied {@link FooGallery.Item|`items`} to the template.
		 * @memberof FooGallery.Items#
		 * @function append
		 * @param {FooGallery.Item[]} items - The array of items to append.
		 * @returns {FooGallery.Item[]} The array of items that were appended.
		 * @fires FooGallery.Template~"append-items.foogallery"
		 * @fires FooGallery.Template~"appended-items.foogallery"
		 */
		append: function(items){
			var self = this, appended = [], appendable = self.appendable(items);
			if (appendable.length > 0) {
				/**
				 * @summary Raised before the template appends any items to itself.
				 * @event FooGallery.Template~"append-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items to append.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"append-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent any items being appended.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"append-items.foogallery": function(event, template, items){
				 * 			if ("some condition"){
				 * 				// stop any items being appended
				 * 				event.preventDefault();
				 * 			}
				 * 		}
				 * 	}
				 * });
				 */
				var e = self.tmpl.raise("append-items", [appendable]);
				if (!e.isDefaultPrevented()){
					appended = $.map(appendable, function(item){
						return item.append() ? item : null;
					});
				}
				/**
				 * @summary Raised after the template has appended items to itself.
				 * @event FooGallery.Template~"appended-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items appended.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"appended-items.foogallery": function(event, template, items){
					 * 			// do something
					 * 		}
					 * 	}
					 * });
				 */
				if (appended.length > 0) self.tmpl.raise("appended-items", [appended]);
			}
			return appended;
		},
		/**
		 * @summary Detach each of the supplied {@link FooGallery.Item|`items`} from the template.
		 * @memberof FooGallery.Items#
		 * @function detach
		 * @param {FooGallery.Item[]} items - The array of items to detach.
		 * @returns {FooGallery.Item[]} The array of items that were detached.
		 * @fires FooGallery.Template~"detach-items.foogallery"
		 * @fires FooGallery.Template~"detached-items.foogallery"
		 */
		detach: function(items){
			var self = this, detached = [], detachable = self.detachable(items);
			if (detachable.length > 0) {
				/**
				 * @summary Raised before the template detaches any items from itself.
				 * @event FooGallery.Template~"detach-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items to detach.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"detach-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent any items being detached.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"detach-items.foogallery": function(event, template, items){
				 * 			if ("some condition"){
				 * 				// stop any items being detached
				 * 				event.preventDefault();
				 * 			}
				 * 		}
				 * 	}
				 * });
				 */
				var e = self.tmpl.raise("detach-items", [detachable]);
				if (!e.isDefaultPrevented()){
					detached = $.map(detachable, function(item){
						return item.detach() ? item : null;
					});
				}
				/**
				 * @summary Raised after the template has detached items from itself.
				 * @event FooGallery.Template~"detached-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items detached.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"detached-items.foogallery": function(event, template, items){
					 * 			// do something
					 * 		}
					 * 	}
					 * });
				 */
				if (detached.length > 0) self.tmpl.raise("detached-items", [detached]);
			}
			return detached;
		},
		/**
		 * @summary Load each of the supplied `items` images.
		 * @memberof FooGallery.Items#
		 * @function load
		 * @param {FooGallery.Item[]} items - The array of items to load.
		 * @returns {Promise<FooGallery.Item[]>} Resolved with an array of {@link FooGallery.Item|items} as the first argument. If no items are loaded this array is empty.
		 * @fires FooGallery.Template~"load-items.foogallery"
		 * @fires FooGallery.Template~"loaded-items.foogallery"
		 */
		load: function(items){
			var self = this;
			items = self.loadable(items);
			if (items.length > 0){
				/**
				 * @summary Raised before the template loads any items.
				 * @event FooGallery.Template~"load-items.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item[]} items - The array of items to load.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"load-items.foogallery": function(event, template, items){
				 * 			// do something
				 * 		}
				 * 	}
				 * });
				 * @example {@caption Calling the `preventDefault` method on the `event` object will prevent any `items` being loaded.}
				 * $(".foogallery").foogallery({
				 * 	on: {
				 * 		"load-items.foogallery": function(event, template, items){
				 * 			if ("some condition"){
				 * 				// stop any items being loaded
				 * 				event.preventDefault();
				 * 			}
				 * 		}
				 * 	}
				 * });
				 */
				var e = self.tmpl.raise("load-items", [items]);
				if (!e.isDefaultPrevented()){
					var loading = $.map(items, function(item){
						return item.load();
					});
					return _fn.when(loading).done(function(loaded) {
						/**
						 * @summary Raised after the template has loaded items.
						 * @event FooGallery.Template~"loaded-items.foogallery"
						 * @type {jQuery.Event}
						 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
						 * @param {FooGallery.Template} template - The template raising the event.
						 * @param {FooGallery.Item[]} items - The array of items that were loaded.
						 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
						 * $(".foogallery").foogallery({
						 * 	on: {
						 * 		"loaded-items.foogallery": function(event, template, items){
						 * 			// do something
						 * 		}
						 * 	}
						 * });
						 */
						self.tmpl.raise("loaded-items", [loaded]);
					});
				}
			}
			return _fn.resolveWith([]);
		},
		createIdMap: function(items){
			var map = {};
			$.each(items, function(i, item){
				if (_is.empty(item.id)) item.id = "" + (i + 1);
				map[item.id] = item;
			});
			return map;
		}
	});

	_.components.register("items", _.Items);

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.str
);
(function($, _, _utils, _is){

	_.Paging = _.Component.extend({
		construct: function(template){
			var self = this;
			/**
			 * @ignore
			 * @memberof FooGallery.Paging#
			 * @function _super
			 */
			self._super(template);
			self.opt = self.tmpl.opt.paging;
			self.cls = self.tmpl.cls.paging;
			self.il8n = self.tmpl.il8n.paging;
			self.sel = self.tmpl.sel.paging;
			self.pushOrReplace = self.opt.pushOrReplace;
			self.type = self.opt.type;
			self.theme = self.opt.theme;
			self.size = self.opt.size;
			self.position = self.opt.position;
			self.scrollToTop = self.opt.scrollToTop;
			self.current = 0;
			self.total = 0;
			self.ctrls = [];
			self._arr = [];
		},
		destroy: function(){
			var self = this;
			self._arr.splice(0, self._arr.length);
			$.each(self.ctrls.splice(0, self.ctrls.length), function(i, control){
				control.destroy();
			});
			self._super();
		},
		build: function(){
			var self = this, items = self.tmpl.items.available();
			self.total = self.size > 0 && items.length > 0 ? Math.ceil(items.length / self.size) : 1;
			if (self.total <= 1){
				self._arr.push(items);
				self.tmpl.items.detach(self._arr[0]);
			} else {
				for (var i = 0; i < self.total; i++){
					self._arr.push(items.splice(0, self.size));
					self.tmpl.items.detach(self._arr[i]);
				}
			}
			if (self.total > 1 && _.paging.hasCtrl(self.type)){
				var pos = self.position, top, bottom;
				if (pos === "both" || pos === "top"){
					top = _.paging.makeCtrl(self.type, self.tmpl, self, "top");
					if (top.create()){
						top.append();
						self.ctrls.push(top);
					}
				}
				if (pos === "both" || pos === "bottom"){
					bottom = _.paging.makeCtrl(self.type, self.tmpl, self, "bottom");
					if (bottom.create()){
						bottom.append();
						self.ctrls.push(bottom);
					}
				}
			}
		},
		rebuild: function(){
			var self = this;
			self.current = 0;
			self.total = 0;
			self._arr.splice(0, self._arr.length);
			$.each(self.ctrls.splice(0, self.ctrls.length), function(i, control){
				control.destroy();
			});
			self.build();
		},
		all: function(){
			return this._arr.slice();
		},
		available: function(){
			return this.get(this.current);
		},
		controls: function(pageNumber){
			var self = this;
			if (self.isValid(pageNumber)){
				$.each(self.ctrls, function(i, control){
					control.update(pageNumber);
				});
			}
		},
		isValid: function(pageNumber){
			return _is.number(pageNumber) && pageNumber > 0 && pageNumber <= this.total;
		},
		number: function(value){
			return this.isValid(value) ? value : (this.current === 0 ? 1 : this.current);
		},
		create: function(pageNumber){
			var self = this;
			pageNumber = self.number(pageNumber);
			var index = pageNumber - 1;
			self.tmpl.items.create(self._arr[index], true);
			for (var i = 0, l = self._arr.length; i < l; i++) {
				if (i === index) continue;
				self.tmpl.items.detach(self._arr[i]);
			}
			self.current = pageNumber;
		},
		get: function(pageNumber){
			var self = this;
			if (self.isValid(pageNumber)){
				pageNumber = self.number(pageNumber);
				return self._arr[pageNumber - 1];
			}
			return [];
		},
		set: function(pageNumber, scroll, updateState){
			var self = this;
			if (self.isValid(pageNumber)){
				self.controls(pageNumber);
				var num = self.number(pageNumber), state;
				if (num !== self.current) {
					var prev = self.current, setPage = function(){
						updateState = _is.boolean(updateState) ? updateState : true;
						if (updateState && self.current === 1 && !self.tmpl.state.exists()){
							state = self.tmpl.state.get();
							self.tmpl.state.update(state, self.pushOrReplace);
						}
						self.create(num);
						if (updateState){
							state = self.tmpl.state.get();
							self.tmpl.state.update(state, self.pushOrReplace);
						}
						if (self.scrollToTop && _is.boolean(scroll) ? scroll : false) {
							var page = self.get(self.current);
							if (page.length > 0){
								page[0].scrollTo("top");
							}
						}
						self.tmpl.raise("after-page-change", [self.current, prev]);
					};
					var e = self.tmpl.raise("before-page-change", [self.current, num, setPage]);
					if (e.isDefaultPrevented()) return false;
					setPage();
					return true;
				}
			}
			return false;
		},
		find: function(item){
			var self = this;
			for (var i = 0, l = self._arr.length; i < l; i++) {
				if ($.inArray(item, self._arr[i]) !== -1) {
					return i + 1;
				}
			}
			return 0;
		},
		contains: function(pageNumber, item){
			var items = this.get(pageNumber);
			return $.inArray(item, items) !== -1;
		},
		first: function(){
			this.goto(1);
		},
		last: function(){
			this.goto(this._arr.length);
		},
		prev: function(){
			this.goto(this.current - 1);
		},
		next: function(){
			this.goto(this.current + 1);
		},
		goto: function(pageNumber){
			var self = this;
			if (self.set(pageNumber, true)){
				self.tmpl.loadAvailable();
			}
		}
	});

	_.PagingControl = _.Component.extend({
		construct: function(template, parent, position){
			var self = this;
			self._super(template);
			self.pages = parent;
			self.position = position;
			self.$container = null;
		},
		create: function(){
			var self = this;
			self.$container = $("<nav/>", {"class": self.pages.cls.container}).addClass(self.pages.theme);
			return true;
		},
		destroy: function(){
			var self = this;
			self.$container.remove();
			self.$container = null;
		},
		append: function(){
			var self = this;
			if (self.position === "top"){
				self.$container.insertBefore(self.tmpl.$el);
			} else {
				self.$container.insertAfter(self.tmpl.$el);
			}
		},
		update: function(pageNumber){}
	});

	_.paging.register("default", _.Paging, null, {
		type: "none",
		theme: "fg-light",
		size: 30,
		pushOrReplace: "push",
		position: "none",
		scrollToTop: true
	}, {
		container: "fg-paging-container"
	}, null, -100);

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _utils, _is){

	_.Dots = _.Paging.extend({});

	_.DotsControl = _.PagingControl.extend({
		construct: function(template, parent, position){
			this._super(template, parent, position);
			this.$container = $();
			this.$list = $();
			this.$items = $();
		},
		create: function(){
			var self = this, cls = self.pages.cls, il8n = self.pages.il8n,
				items = [], $list = $("<ul/>", {"class": cls.list});

			for (var i = 0, l = self.pages.total, $item; i < l; i++){
				items.push($item = self.createItem(i + 1, il8n.page));
				$list.append($item);
			}
			self.$list = $list;
			self.$container = $("<nav/>", {"class": cls.container}).addClass(self.pages.theme).append($list);
			self.$items = $($.map(items, function($item){ return $item.get(); }));
			return true;
		},
		append: function(){
			var self = this;
			if (self.position === "top"){
				self.$container.insertBefore(self.tmpl.$el);
			} else {
				self.$container.insertAfter(self.tmpl.$el);
			}
		},
		destroy: function(){
			var self = this, sel = self.pages.sel;
			self.$list.find(sel.link).off("click.foogallery", self.onLinkClick);
			self.$container.remove();
			self.$container = $();
			self.$list = $();
			self.$items = $();
		},
		update: function(pageNumber){
			this.setSelected(pageNumber - 1);
		},
		setSelected: function(index){
			var self = this, cls = self.pages.cls, il8n = self.pages.il8n, sel = self.pages.sel;
			// first find any previous selected items and deselect them
			self.$items.filter(sel.selected).removeClass(cls.selected).each(function (i, el) {
				// we need to revert the original items screen-reader text if it existed as being selected sets it to the value of the labels.current option
				var $item = $(el), label = $item.data("label"), $sr = $item.find(sel.reader);
				// if we have an original value and a screen-reader element then update it
				if (_is.string(label) && $sr.length !== 0) {
					$sr.html(label);
				}
			});
			// next find the newly selected item and set it as selected
			self.$items.eq(index).addClass(cls.selected).each(function (i, el) {
				// we need to update the items screen-reader text to appropriately show it as selected using the value of the labels.current option
				var $item = $(el), $sr = $item.find(sel.reader), label = $sr.html();
				// if we have a current label to backup and a screen-reader element then update it
				if (_is.string(label) && $sr.length !== 0) {
					// store the original screen-reader text so we can revert it later
					$item.data("label", label);
					$sr.html(il8n.current);
				}
			});
		},
		/**
		 * @summary Create and return a jQuery object containing a single `li` and its' link.
		 * @memberof FooGallery.DotsControl#
		 * @function createItem
		 * @param {(number|string)} pageNumber - The page number for the item.
		 * @param {string} [label=""] - The label that is displayed when hovering over an item.
		 * @param {string} [text=""] - The text to display for the item, if not supplied this defaults to the `pageNumber` value.
		 * @param {string} [classNames=""] - A space separated list of CSS class names to apply to the item.
		 * @param {string} [sr=""] - The text to use for screen readers, if not supplied this defaults to the `label` value.
		 * @returns {jQuery}
		 */
		createItem: function(pageNumber, label, text, classNames, sr){
			text = _is.string(text) ? text : pageNumber;
			label = _is.string(label) ? label : "";
			var self = this, opt = self.pages.opt, cls = self.pages.cls;
			var $link = $("<a/>", {"class": cls.link, "href": "#page-" + pageNumber}).html(text).on("click.foogallery", {self: self, page: pageNumber}, self.onLinkClick);
			if (!_is.empty(label)){
				$link.attr("title", label.replace(/\{PAGE}/g, pageNumber).replace(/\{LIMIT}/g, opt.limit + ""));
			}
			sr = _is.string(sr) ? sr : label;
			if (!_is.empty(sr)){
				$link.prepend($("<span/>", {"class":cls.reader, text: sr.replace(/\{PAGE}/g, "").replace(/\{LIMIT}/g, opt.limit + "")}));
			}
			var $item = $("<li/>", {"class": cls.item}).append($link);
			classNames = _is.string(classNames) ? classNames : "";
			if (!_is.empty(classNames)){
				$item.addClass(classNames);
			}
			return $item;
		},
		/**
		 * @summary Handles the click event of the dots links.
		 * @memberof FooGallery.DotsControl#
		 * @function onLinkClick
		 * @param {jQuery.Event} e - The jQuery.Event object for the click event.
		 * @private
		 */
		onLinkClick: function(e){
			e.preventDefault();
			var self = e.data.self, page = e.data.page, sel = self.pages.sel;
			// this check should not be required as we use the CSS pointer-events: none; property on disabled links but just in case test for the class here
			if (!$(this).closest(sel.item).is(sel.disabled)){
				self.pages.set(page, true);
				self.tmpl.loadAvailable();
			}
		}
	});

	_.paging.register("dots", _.Dots, _.DotsControl, {
		type: "dots",
		position: "both",
		pushOrReplace: "push"
	}, {
		list: "fg-dots",
		item: "fg-dot-item",
		link: "fg-dot-link",
		disabled: "fg-disabled",
		selected: "fg-selected",
		visible: "fg-visible",
		reader: "fg-sr-only"
	}, {
		current: "Current page",
		page: "Page {PAGE}"
	});

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _utils){

	_.DefaultTemplate = _.Template.extend({});

	_.template.register("default", _.DefaultTemplate, null, {
		container: "foogallery fg-default"
	});

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils
);
(function($, _, _utils, _is){

	/**
	 * @summary The Masonry template for FooGallery.
	 * @memberof FooGallery
	 * @constructs MasonryTemplate
	 * @param {FooGallery.MasonryTemplate~Options} [options] - The options for the template.
	 * @param {(jQuery|HTMLElement)} [element] - The jQuery object or HTMLElement of the template. If not supplied one will be created within the `parent` element supplied to the {@link FooGallery.Template#initialize|initialize} method.
	 * @augments FooGallery.Template
	 * @borrows FooGallery.utils.Class.extend as extend
	 * @borrows FooGallery.utils.Class.override as override
	 * @description This template makes use of the popular [Masonry library](http://masonry.desandro.com/) to perform its layout. It supports two basic layout types, fixed and column based.
	 * @example {@caption The below shows the simplest way to create a Masonry gallery using this template, by simply initializing it on pre-existing elements.}{@lang html}
	 * <!-- The container element for the template -->
	 * <div id="gallery-1" class="foogallery fg-masonry">
	 *   <!-- Used by the masonry to handle responsive sizing -->
	 *   <div class="fg-column-width"></div>
	 *   <div class="fg-gutter-width"></div>
	 *   <!-- A single item -->
	 *   <div class="fg-item" data-id="[item.id]">
	 *     <div class="fg-item-inner">
	 *       <a class="fg-thumb" href="[item.href]">
	 *         <img class="fg-image" width="[item.width]" height="[item.height]"
	 *         	title="[item.title]" alt="[item.description]"
	 *         	data-src="[item.src]"
	 *         	data-srcset="[item.srcset]" />
	 *         <!-- Optional caption markup -->
	 *         <div class="fg-caption">
	 *         	<div class="fg-caption-inner">
	 *         	 <div class="fg-caption-title">[item.title]</div>
	 *         	 <div class="fg-caption-desc">[item.description]</div>
	 *         	</div>
	 *         </div>
	 *       </a>
	 *     </div>
	 *   </div>
	 *   <!-- Any number of additional items -->
	 * </div>
	 * <script>
	 * 	jQuery(function($){
	 * 		$("#gallery-1").foogallery();
	 * 	});
	 * </script>
	 * @example {@caption Options can be supplied directly to the `.foogallery()` method or by supplying them using the `data-foogallery` attribute. If supplied using the attribute the value must follow [valid JSON syntax](http://en.wikipedia.org/wiki/JSON#Data_types.2C_syntax_and_example) including quoted property names.}{@lang html}
	 * <!-- Supplying the options using the attribute -->
	 * <div id="gallery-1" class="foogallery fg-masonry" data-foogallery='{"lazy": true, "template": {"layout": "col4"}}'>
	 * 	<!-- Snip -->
	 * </div>
	 * <script>
	 * 	jQuery(function($){
	 * 		// Supply the options directly to the method
	 * 		$("#gallery-1").foogallery({
	 * 			lazy: true,
	 * 			template: {
	 * 				layout: "col4"
	 * 			}
	 * 		});
	 * 	});
	 * </script>
	 * @example {@caption If required the templates container element can be created from just options however a parent element must be supplied to the `initialize` method. The created gallery container is appended to the supplied parent. When creating galleries this way all items must be supplied using the `items` option.}{@lang html}
	 * <div id="gallery-parent"></div>
	 * <script>
	 * 	jQuery(function($){
	 * 		// Create the template using just options
	 * 		var tmpl = FooGallery.template.make({
	 * 			type: "masonry", // required when creating from options
	 * 			lazy: true,
	 * 			template: {
	 * 				layout: "col4"
	 * 			},
	 * 			items: [{
	 * 				id: "item-1",
	 * 				href: "https://url-to-your/full-image.jpg",
	 * 				src: "https://url-to-your/thumb-image.jpg",
	 * 				width: 250,
	 * 				height: 300,
	 * 				srcset: "https://url-to-your/thumb-image@2x.jpg 500w,https://url-to-your/thumb-image@3x.jpg 750w",
	 * 				title: "Short Item Title",
	 * 				description: "Longer item description but still fairly brief."
	 * 			},{
	 * 				// Any number of additional items
	 * 			}]
	 * 		});
	 * 		// Supply the parent element to the initialize method
	 * 		tmpl.initialize("#gallery-parent");
	 * 	});
	 * </script>
	 */
	_.MasonryTemplate = _.Template.extend(/** @lends FooGallery.MasonryTemplate */{
		construct: function(options, element){
			this._super(options, element);
			/**
			 * @summary The current Masonry instance for the template.
			 * @memberof FooGallery.MasonryTemplate#
			 * @name masonry
			 * @type {?Masonry}
			 * @description This value is `null` until after the {@link FooGallery.Template~event:"pre-init.foogallery"|`pre-init.foogallery`} event has been raised.
			 */
			this.masonry = null;
			/**
			 *
			 * @type {HTMLStyleElement}
			 */
			this.style = null;
			this.$columnWidth = null;
			/**
			 * @summary The CSS classes for the Masonry template.
			 * @memberof FooGallery.MasonryTemplate#
			 * @name cls
			 * @type {FooGallery.MasonryTemplate~CSSClasses}
			 */
			/**
			 * @summary The CSS selectors for the Masonry template.
			 * @memberof FooGallery.MasonryTemplate#
			 * @name sel
			 * @type {FooGallery.MasonryTemplate~CSSSelectors}
			 */
		},
		createStylesheet: function(){
			var self = this;
			self.style = document.createElement("style");
			self.style.appendChild(document.createTextNode(""));
			document.head.appendChild(self.style);
			return self.style.sheet;
		},
		/**
		 * @summary Listens for the {@link FooGallery.Template~event:"pre-init.foogallery"|`pre-init.foogallery`} event.
		 * @memberof FooGallery.MasonryTemplate#
		 * @function onPreInit
		 * @param {jQuery.Event} event - The jQuery.Event object for the event.
		 * @param {FooGallery.MasonryTemplate} self - The current instance of the template.
		 * @this {HTMLElement} The templates container element that the event was raised on.
		 * @description Performs all pre-initialization work required by the Masonry template, specifically handling the `layout` option and building up the required Masonry options.
		 * @protected
		 */
		onPreInit: function(event, self){
			var sel = self.sel, cls = self.cls;
			// first update the templates classes to include one property containing all layouts
			cls.layouts = $.map(cls.layout, function(value){
				return value;
			}).join(" ");
			// check if the supplied layout is supported
			if (!_is.string(cls.layout[self.template.layout])){
				// if not set the default
				self.template.layout = "col4";
			}
			// configure the base masonry options depending on the layout
			var fixed = self.template.layout === "fixed";
			self.template.isFitWidth = fixed;
			self.template.percentPosition = !fixed;
			self.template.transitionDuration = 0;
			self.template.itemSelector = sel.item.elem;
			// remove any layout classes and then apply only the current to the container
			self.$el.removeClass(cls.layouts).addClass(cls.layout[self.template.layout]);

			if (!fixed){
				// if the gutterWidth element does not exist create it
				if (self.$el.find(sel.gutterWidth).length === 0){
					self.$el.prepend($("<div/>").addClass(cls.gutterWidth));
				}
				self.template.gutter = sel.gutterWidth;
			}

			// if the columnWidth element does not exist create it
			if (self.$el.find(sel.columnWidth).length === 0){
				self.$el.prepend($("<div/>").addClass(cls.columnWidth));
			}
			if (_is.number(self.template.columnWidth)){
				self.$el.find(sel.columnWidth).width(self.template.columnWidth);
			}
			self.template.columnWidth = sel.columnWidth;

			// if this is a fixed layout and a number value is supplied as the gutter option then
			// make sure to vertically space the items using  a CSS class and the same value
			if (fixed && _is.number(self.template.gutter)){
				var sheet = self.createStylesheet(),
						rule = '#' + self.id + sel.container + ' ' + sel.item.elem + ' { margin-bottom: ' + self.template.gutter + 'px; }';
				sheet.insertRule(rule , 0);
			}

			self.masonry = new Masonry( self.$el.get(0), self.template );
		},
		onInit: function(event, self){
			self.masonry.layout();
		},
		onPostInit: function(event, self){
			self.masonry.layout();
		},
		onReady: function(event, self){
			self.masonry.layout();
		},
		onDestroy: function(event, self){
			self.$el.find(self.sel.columnWidth).remove();
			self.$el.find(self.sel.gutterWidth).remove();
			if (self.masonry instanceof Masonry){
				self.masonry.destroy();
			}
			if (self.style && self.style.parentNode){
				self.style.parentNode.removeChild(self.style);
			}
		},
		onLayout: function(event, self){
			self.masonry.layout();
		},
		/**
		 * @summary Listens for the {@link FooGallery.Template~event:"parsed-items.foogallery"|`parsed-items.foogallery`} event.
		 * @memberof FooGallery.MasonryTemplate#
		 * @function onParsedItems
		 * @param {jQuery.Event} event - The jQuery.Event object for the event.
		 * @param {FooGallery.MasonryTemplate} self - The current instance of the template.
		 * @param {FooGallery.Item[]} items - The array of items that were parsed.
		 * @this {HTMLElement} The templates container element that the event was raised on.
		 * @description Instructs Masonry to perform a layout operation whenever items are parsed.
		 * @protected
		 */
		onParsedItems: function(event, self, items){
			self.masonry.layout();
		},
		/**
		 * @summary Listens for the {@link FooGallery.Template~event:"appended-items.foogallery"|`appended-items.foogallery`} event.
		 * @memberof FooGallery.MasonryTemplate#
		 * @function onAppendedItems
		 * @param {jQuery.Event} event - The jQuery.Event object for the event.
		 * @param {FooGallery.MasonryTemplate} self - The current instance of the template.
		 * @param {FooGallery.Item[]} items - The array of items that were appended.
		 * @this {HTMLElement} The templates container element that the event was raised on.
		 * @description Instructs Masonry to perform a layout operation whenever items are appended.
		 * @protected
		 */
		onAppendedItems: function(event, self, items){
			items = self.items.jquerify(items);
			items = self.masonry.addItems(items);
			// add and layout the new items with no transitions
			self.masonry.layoutItems(items, true);
		},
		/**
		 * @summary Listens for the {@link FooGallery.Template~event:"detach-item.foogallery"|`detach-item.foogallery`} event.
		 * @memberof FooGallery.MasonryTemplate#
		 * @function onDetachItem
		 * @param {jQuery.Event} event - The jQuery.Event object for the event.
		 * @param {FooGallery.MasonryTemplate} self - The current instance of the template.
		 * @param {FooGallery.Item} item - The item to detach.
		 * @this {HTMLElement} The templates container element that the event was raised on.
		 * @description If not already overridden this method will override the default logic to detach an item and replace it with Masonry specific logic.
		 * @protected
		 */
		onDetachItem: function(event, self, item){
			if (!event.isDefaultPrevented()){
				event.preventDefault();
				self.masonry.remove(item.$el);
				item.isAttached = false;
				item.unfix();
			}
		},
		/**
		 * @summary Listens for the {@link FooGallery.Template~event:"detached-items.foogallery"|`detached-items.foogallery`} event.
		 * @memberof FooGallery.MasonryTemplate#
		 * @function onDetachedItems
		 * @param {jQuery.Event} event - The jQuery.Event object for the event.
		 * @param {FooGallery.MasonryTemplate} self - The current instance of the template.
		 * @param {FooGallery.Item[]} items - The array of items that were detached.
		 * @this {HTMLElement} The templates container element that the event was raised on.
		 * @description Instructs Masonry to perform a layout operation whenever items are detached.
		 * @protected
		 */
		onDetachedItems: function(event, self, items){
			self.masonry.layout();
		},
		onLoadedItems: function(event, self, items){
			self.masonry.layout();
		}
	});

	_.template.register("masonry", _.MasonryTemplate, {
		template: {
			layout: "col4"
		}
	}, {
		container: "foogallery fg-masonry",
		columnWidth: "fg-column-width",
		gutterWidth: "fg-gutter-width",
		layout: {
			fixed: "fg-masonry-fixed",
			col2: "fg-masonry-2col",
			col3: "fg-masonry-3col",
			col4: "fg-masonry-4col",
			col5: "fg-masonry-5col"
		}
	});

	/**
	 * @summary An object containing the default options for the Masonry template.
	 * @typedef {FooGallery.Template~Options} FooGallery.MasonryTemplate~Options
	 * @property {object} [template] - An object containing the custom options for the Masonry template.
	 * @property {string} [template.layout="col4"] - The layout to use for the template; "fixed", "col2", "col3", "col4" or "col5".
	 * @property {FooGallery.MasonryTemplate~CSSClasses} [cls] - An object containing all CSS classes for the Masonry template.
	 * @description Apart from the `layout` option the template object is identical to the standard {@link https://masonry.desandro.com/options.html|Masonry options}.
	 * Note that the template overrides and sets its' own values for the following options based primarily on the `layout` value; `itemSelector`, `columnWidth`, `gutter`, `isFitWidth`, `percentPosition` and `transitionDuration`.
	 * The `layout` value can be classed into two categories, fixed width and column type layouts. You can see in the examples below the options the template sets for each of these types of layouts.
	 * @example {@caption For both fixed and column layouts the template sets the below option values.}
	 * {
	 * 	"itemSelector": ".fg-item", // this selector is generated from the classes.item.elem value.
	 * 	"columnWidth": ".fg-column-width", // this selector is generated from the classes.masonry.columnWidth value.
	 * 	"gutter": ".fg-gutter-width", // this selector is generated from the classes.masonry.gutterWidth value.
	 * 	"transitionDuration": 0 // disables masonry's inline transitions to prevent them overriding our CSS class transitions
	 * }
	 * @example {@caption For fixed layouts (`"fixed"`) the template sets the below options. If a number was supplied for the `columnWidth` or `gutter` options it is applied to the relevant elements before they are replaced by the selector seen above.}
	 * {
	 * 	"isFitWidth": true,
	 * 	"percentPosition": false
	 * }
	 * @example {@caption For column layouts (`"col2","col3","col4","col5"`) the template sets the below options.}
	 * {
	 * 	"isFitWidth": false,
	 * 	"percentPosition": true
	 * }
	 */

	/**
	 * @summary An object containing the default CSS classes for the Masonry template.
	 * @typedef {FooGallery.Template~CSSClasses} FooGallery.MasonryTemplate~CSSClasses
	 * @property {string} [container="foogallery fg-masonry"] - The base CSS class names to apply to the container element.
	 * @property {string} [columnWidth="fg-column-width"] - The CSS class name to apply to the Masonry column sizer element.
	 * @property {string} [gutterWidth="fg-gutter-width"] - The CSS class name to apply to the Masonry gutter sizer element.
	 * @property {object} [layout] - An object containing all layout classes.
	 * @property {string} [layout.fixed="fg-masonry-fixed"] - The CSS class name for a fixed width layout.
	 * @property {string} [layout.col2="fg-masonry-2col"] - The CSS class name for a two column layout.
	 * @property {string} [layout.col3="fg-masonry-3col"] - The CSS class name for a three column layout.
	 * @property {string} [layout.col4="fg-masonry-4col"] - The CSS class name for a four column layout.
	 * @property {string} [layout.col5="fg-masonry-5col"] - The CSS class name for a five column layout.
	 * @property {string} [layouts="fg-masonry-fixed fg-masonry-2col fg-masonry-3col fg-masonry-4col fg-masonry-5col"] - A space delimited string of all CSS class names from the `layout` object.
	 */

	/**
	 * @summary An object containing all CSS selectors for the Masonry template.
	 * @typedef {FooGallery.Template~CSSSelectors} FooGallery.MasonryTemplate~CSSSelectors
	 * @property {string} [container=".foogallery.fg-masonry"] - The CSS selector for the container element.
	 * @property {string} [columnWidth=".fg-column-width"] - The CSS selector for the Masonry column sizer element.
	 * @property {string} [gutterWidth=".fg-gutter-width"] - The CSS selector for the Masonry gutter sizer element.
	 * @property {object} [layout] - An object containing all layout CSS selectors.
	 * @property {string} [layout.fixed=".fg-masonry-fixed"] - The CSS selector for a fixed width layout.
	 * @property {string} [layout.col2=".fg-masonry-2col"] - The CSS selector for a two column layout.
	 * @property {string} [layout.col3=".fg-masonry-3col"] - The CSS selector for a three column layout.
	 * @property {string} [layout.col4=".fg-masonry-4col"] - The CSS selector for a four column layout.
	 * @property {string} [layout.col5=".fg-masonry-5col"] - The CSS selector for a five column layout.
	 * @description This object is automatically generated from a {@link FooGallery.MasonryTemplate~CSSClasses|classes} object and its properties mirror those except the class name values are converted into CSS selectors.
	 */

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _utils, _is){

	_.Justified = _utils.Class.extend({
		construct: function(element, options){
			this.$el = $(element);
			this.options = $.extend(true, {}, _.Justified.defaults, options);
			this._items = [];
		},
		init: function(){
			var self = this;
			if (_is.string(self.options.maxRowHeight)){
				if (self.options.maxRowHeight.indexOf('%')){
					self.options.maxRowHeight = self.options.rowHeight * (parseInt(self.options.maxRowHeight) / 100);
				} else {
					self.options.maxRowHeight = parseInt(self.options.maxRowHeight);
				}
			}
			self.layout(true);
			$(window).on("resize.justified", {self: self}, self.onWindowResize);
		},
		destroy: function(){
			$(window).off("resize.justified");
			$.each(this._items, function(i, item){
				item.$item.removeAttr("style").removeClass("fg-positioned");
			});
			this.$el.removeAttr("style");
		},
		parse: function(){
			var self = this, visible = self.$el.is(':visible'),
					$test = $('<div/>', {'class': self.$el.attr('class')}).css({
						position: 'absolute',
						top: 0,
						left: -9999,
						visibility: 'hidden'
					}).appendTo('body');
			self._items = self.$el.find(self.options.itemSelector).removeAttr("style").removeClass("fg-positioned").map(function(i, el){
				var $item = $(el), width = 0, height = 0, ratio;
				if (!visible){
					var $clone = $item.clone();
					$clone.appendTo($test);
					width = $clone.outerWidth();
					height = $clone.outerHeight();
				} else {
					width = $item.outerWidth();
					height = $item.outerHeight();
				}
				ratio = self.options.rowHeight / height;

				return {
					index: i,
					width: width * ratio,
					height: self.options.rowHeight,
					top: 0,
					left: 0,
					$item: $item
				};
			}).get();
			$test.remove();
			return self._items;
		},
		round: function(value){
			return Math.round(value);
			//return Math.round(value*2) / 2;
		},
		getContainerWidth: function(){
			var self = this, visible = self.$el.is(':visible');
			if (!visible){
				return self.$el.parents(':visible:first').width();
			}
			return self.$el.width();
		},
		layout: function(refresh, autoCorrect){
			refresh = _is.boolean(refresh) ? refresh : false;
			autoCorrect = _is.boolean(autoCorrect) ? autoCorrect : true;

			if (refresh || this._items.length === 0){
				this.parse();
			}

			var self = this,
				containerWidth = self.getContainerWidth(),
				rows = self.rows(containerWidth),
				offsetTop = 0;

			for (var i = 0, l = rows.length, row; i < l; i++){
				row = rows[i];
				if (i === l - 1){
					offsetTop = self.lastRow(row, containerWidth, offsetTop);
				} else {
					offsetTop = self.justify(row, containerWidth, offsetTop);
				}
				self.render(row);
			}
			self.$el.height(offsetTop);
			// if our layout caused the container width to get smaller
			// i.e. makes a scrollbar appear then layout again to account for it
			if (autoCorrect && self.getContainerWidth() < containerWidth){
				self.layout(false, false);
			}
		},
		render: function(row){
			for (var j = 0, jl = row.items.length, item; j < jl; j++){
				item = row.items[j];
				if (row.visible){
					item.$item.css({
						width: item.width,
						height: item.height,
						top: item.top,
						left: item.left,
						display: "",
						maxHeight: this.options.maxRowHeight > 0 ? this.options.maxRowHeight : ""
					}).addClass("fg-positioned");
				} else {
					item.$item.css("display", "none");
				}
			}
		},
		lastRow: function(row, containerWidth, offsetTop){
			var self = this;
			switch(self.options.lastRow){
				case "hide":
					row.visible = false;
					break;
				case "justify":
					offsetTop = self.justify(row, containerWidth, offsetTop);
					break;
				case "nojustify":
					if (row.width / containerWidth > self.options.justifyThreshold){
						offsetTop = self.justify(row, containerWidth, offsetTop);
					} else {
						offsetTop = self.position(row, containerWidth, offsetTop, "left");
					}
					break;
				case "right":
				case "center":
				case "left":
					offsetTop = self.position(row, containerWidth, offsetTop, self.options.lastRow);
					break;
				default:
					offsetTop = self.position(row, containerWidth, offsetTop, "left");
					break;
			}
			return offsetTop;
		},
		justify: function(row, containerWidth, offsetTop){
			var self = this,
				left = 0,
				margins = self.options.margins * (row.items.length - 1),
				ratio = (containerWidth - margins) / row.width;

			if (row.index > 0) offsetTop += self.options.margins;
			row.top = offsetTop;
			row.width = self.round(row.width * ratio);
			row.height = self.round(row.height * ratio);

			for (var j = 0, jl = row.items.length, item; j < jl; j++){
				item = row.items[j];
				item.width = self.round(item.width * ratio);
				item.height = self.round(item.height * ratio);
				item.top = offsetTop;
				if (j > 0) left += self.options.margins;
				item.left = left;
				left += item.width;
			}
			return offsetTop + (row.height > self.options.maxRowHeight ? self.options.maxRowHeight : row.height);
		},
		position: function(row, containerWidth, offsetTop, alignment){
			var self = this, lastItem = row.items[row.items.length - 1], diff = containerWidth - (lastItem.left + lastItem.width);
			if (row.index > 0) offsetTop += self.options.margins;
			row.top = offsetTop;
			for (var i = 0, l = row.items.length, item; i < l; i++){
				item = row.items[i];
				item.top = offsetTop;
				if (alignment === "center"){
					item.left += diff / 2;
				} else if (alignment === "right"){
					item.left += diff;
				}
			}
			return offsetTop + row.height;
		},
		items: function(){
			return $.map(this._items, function(item){
				return {
					index: item.index,
					width: item.width,
					height: item.height,
					$item: item.$item,
					top: item.top,
					left: item.left,
				};
			});
		},
		rows: function(containerWidth){
			var self = this,
				items = self.items(),
				rows = [],
				process = items.length > 0,
				index = -1, offsetTop = 0;

			while (process){
				index += 1;
				if (index > 0) offsetTop += self.options.margins;
				var row = {
					index: index,
					visible: true,
					top: offsetTop,
					width: 0,
					height: self.options.rowHeight,
					items: []
				}, remove = [], left = 0, tmp;

				for (var i = 0, il = items.length, item, ratio; i < il; i++){
					item = items[i];
					tmp = row.width + item.width;
					if (tmp > containerWidth && i > 0){
						break;
					} else if (tmp > containerWidth && i == 0){
						tmp = containerWidth;
						ratio = containerWidth / item.width;
						item.width = self.round(item.width * ratio);
						item.height = self.round(item.height * ratio);
						row.height = item.height;
					}
					item.top = row.top;
					if (i > 0) left += self.options.margins;
					item.left = left;
					left += item.width;
					row.width = tmp;
					row.items.push(item);
					remove.push(i);
				}
				// make sure we don't get stuck in a loop, there should always be items to be removed
				if (remove.length === 0){
					process = false;
					break;
				}
				remove.sort(function(a, b){ return b - a; });
				for (var j = 0, jl = remove.length; j < jl; j++){
					items.splice(remove[j], 1);
				}
				rows.push(row);
				process = items.length > 0;
			}
			return rows;
		},
		onWindowResize: function(e){
			e.data.self.layout();
		}
	});

	_.Justified.defaults = {
		itemSelector: ".fg-item",
		rowHeight: 150,
		maxRowHeight: "200%",
		margins: 0,
		lastRow: "center",
		justifyThreshold: 0.5
	};

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _utils){

	_.JustifiedTemplate = _.Template.extend({
		onPreInit: function(event, self){
			self.justified = new _.Justified( self.$el.get(0), self.template );
		},
		onInit: function(event, self){
			self.justified.init();
		},
		onPostInit: function(event, self){
			self.justified.layout( true );
		},
		onReady: function(event, self){
			self.justified.layout( true );
		},
		onDestroy: function(event, self){
			self.justified.destroy();
		},
		onLayout: function(event, self){
			self.justified.layout( true );
		},
		onParsedItems: function(event, self, items){
			self.justified.layout( true );
		},
		onAppendedItems: function(event, self, items){
			self.justified.layout( true );
		},
		onDetachedItems: function(event, self, items){
			self.justified.layout( true );
		}
	});

	_.template.register("justified", _.JustifiedTemplate, null, {
		container: "foogallery fg-justified"
	});

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils
);
(function($, _, _utils, _is){

	_.Portfolio = _utils.Class.extend({
		construct: function(element, options){
			this.$el = $(element);
			this.options = $.extend(true, {}, _.Portfolio.defaults, options);
			this._items = [];
		},
		init: function(){
			var self = this;
			$(window).on("resize.portfolio", {self: self}, self.onWindowResize);
			self.layout(true);
		},
		destroy: function(){
			$(window).off("resize.portfolio");
			$.each(this._items, function(i, item){
				item.$item.removeAttr("style").removeClass("fg-positioned");
			});
			this.$el.removeAttr("style");
		},
		parse: function(){
			var self = this, visible = self.$el.is(':visible'),
					$test = $('<div/>', {'class': self.$el.attr('class')}).css({
						position: 'absolute',
						top: 0,
						left: -9999,
						visibility: 'hidden'
					}).appendTo('body');
			self._items = self.$el.find(".fg-item").removeAttr("style").removeClass("fg-positioned").map(function(i, el){
				var $item = $(el),
						$thumb = $item.find(".fg-thumb"),
						$img = $item.find(".fg-image"),
						width = 0, height = 0;
				$item.find(".fg-caption").css("max-width", parseFloat($img.attr("width")));
				$img.css({ width: $img.attr("width"), height: $img.attr("height") });
				if (!visible){
					var $clone = $item.clone();
					$clone.appendTo($test);
					width = $clone.outerWidth();
					height = $clone.outerHeight();
				} else {
					width = $item.outerWidth();
					height = $item.outerHeight();
				}
				$img.css({ width: '', height: '' });
				return {
					index: i,
					width: width,
					height: height,
					top: 0,
					left: 0,
					$item: $item,
					$thumb: $thumb
				};
			}).get();
			$test.remove();
			return self._items;
		},
		round: function(value){
			return Math.round(value*2) / 2;
		},
		getContainerWidth: function(){
			var self = this, visible = self.$el.is(':visible');
			if (!visible){
				return self.$el.parents(':visible:first').width();
			}
			return self.$el.width();
		},
		layout: function(refresh, autoCorrect){
			refresh = _is.boolean(refresh) ? refresh : false;
			autoCorrect = _is.boolean(autoCorrect) ? autoCorrect : true;

			if (refresh || this._items.length === 0){
				this.parse();
			}

			var self = this,
				containerWidth = self.getContainerWidth(),
				rows = self.rows(containerWidth),
				offsetTop = 0;

			for (var i = 0, l = rows.length, row; i < l; i++){
				row = rows[i];
				offsetTop = self.position(row, containerWidth, offsetTop, self.options.align);
				self.render(row);
			}
			self.$el.height(offsetTop);
			// if our layout caused the container width to get smaller
			// i.e. makes a scrollbar appear then layout again to account for it
			if (autoCorrect && self.getContainerWidth() < containerWidth){
				self.layout(false, false);
			}
		},
		render: function(row){
			for (var j = 0, jl = row.items.length, item; j < jl; j++){
				item = row.items[j];
				if (row.visible){
					item.$item.css({
						width: item.width,
						height: row.height,
						top: item.top,
						left: item.left,
						display: ""
					}).addClass("fg-positioned");
				} else {
					item.$item.css("display", "none");
				}
			}
		},
		position: function(row, containerWidth, offsetTop, alignment){
			var self = this, lastItem = row.items[row.items.length - 1], diff = containerWidth - (lastItem.left + lastItem.width);
			if (row.index > 0) offsetTop += self.options.gutter;
			row.top = offsetTop;
			for (var i = 0, l = row.items.length, item; i < l; i++){
				item = row.items[i];
				item.top = offsetTop;
				if (alignment === "center"){
					item.left += diff / 2;
				} else if (alignment === "right"){
					item.left += diff;
				}
			}
			return offsetTop + row.height;
		},
		items: function(){
			return $.map(this._items, function(item){
				return {
					index: item.index,
					width: item.width,
					height: item.height,
					$item: item.$item,
					$thumb: item.$thumb,
					top: item.top,
					left: item.left,
				};
			});
		},
		rows: function(containerWidth){
			var self = this,
				items = self.items(),
				rows = [],
				process = items.length > 0,
				index = -1, offsetTop = 0;

			while (process){
				index += 1;
				if (index > 0) offsetTop += self.options.gutter;
				var row = {
					index: index,
					visible: true,
					top: offsetTop,
					width: 0,
					height: 0,
					items: []
				}, remove = [], left = 0, tmp;

				for (var i = 0, il = items.length, item, ratio; i < il; i++){
					item = items[i];
					tmp = row.width + item.width;
					if (tmp > containerWidth && i > 0){
						break;
					} else if (tmp > containerWidth && i == 0){
						tmp = containerWidth;
						ratio = containerWidth / item.width;
						item.width = self.round(item.width * ratio);
						item.height = self.round(item.height * ratio);
						row.height = item.height;
					}
					item.top = row.top;
					if (i > 0){
						left += self.options.gutter;
					}
					if (i !== il - 1){
						tmp += self.options.gutter;
					}
					item.left = left;
					left += item.width;
					if (item.height > row.height) row.height = item.height;
					row.width = tmp;
					row.items.push(item);
					remove.push(i);
				}
				// make sure we don't get stuck in a loop, there should always be items to be removed
				if (remove.length === 0){
					process = false;
					break;
				}
				remove.sort(function(a, b){ return b - a; });
				for (var j = 0, jl = remove.length; j < jl; j++){
					items.splice(remove[j], 1);
				}
				rows.push(row);
				process = items.length > 0;
			}
			return rows;
		},
		onWindowResize: function(e){
			e.data.self.layout();
		}
	});

	_.Portfolio.defaults = {
		gutter: 40,
		align: "center"
	};

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _utils){

	_.PortfolioTemplate = _.Template.extend({
		construct: function(element, options){
			this._super(element, options);

			this.portfolio = null;
		},
		onPreInit: function(event, self){
			self.portfolio = new _.Portfolio( self.$el.get(0), self.template );
		},
		onInit: function(event, self){
			self.portfolio.init();
		},
		onPostInit: function(event, self){
			self.portfolio.layout( true );
		},
		onReady: function(event, self){
			self.portfolio.layout( true );
		},
		onDestroy: function(event, self){
			self.portfolio.destroy();
		},
		onLayout: function(event, self){
			self.portfolio.layout( true );
		},
		onParsedItems: function(event, self, items){
			self.portfolio.layout( true );
		},
		onAppendedItems: function(event, self, items){
			self.portfolio.layout( true );
		},
		onDetachedItems: function(event, self, items){
			self.portfolio.layout( true );
		}
	});

	_.template.register("simple_portfolio", _.PortfolioTemplate, {
		gutter: 40
	}, {
		container: "foogallery fg-simple_portfolio"
	});

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils
);
// (function(_){
//
// 	// This file contains the initialization code for the Image Viewer gallery. It makes use of the FooGallery.Loader
// 	// allowing for optimized loading of images within the gallery.
//
// 	// Use FooGallery.ready to wait for the DOM to be ready
// 	_.ready(function($){
//
// 		// Find each Image Viewer gallery in the current page
// 		$(".fg-image-viewer").each(function(){
// 			var $gallery = $(this),
// 				// Get the options for the plugin
// 				options = $gallery.data("loader-options"),
// 				// Get the options for the loader
// 				loader = $.extend(true, $gallery.data("loader-options"), {
// 					oninit: function(){
// 						// the first time the gallery is initialized it triggers a window resize event
// 						$(window).trigger("resize");
// 					},
// 					onloaded: function(image){
// 						// once the actual image is loaded we no longer need the inline css used to prevent layout jumps so remove it
// 						$(image).fgRemoveSize();
// 					}
// 				});
//
// 			// Find all images that have a width and height attribute set and calculate the size to set as a temporary inline style.
// 			// This calculated size is used to prevent layout jumps.
// 			// Once that is done initialize the plugin and the loader.
// 			$gallery.fgAddSize(true).fgImageViewer( options ).fgLoader( loader );
// 		});
//
// 	});
//
// })(
// 	FooGallery
// );
(function ($, _, _utils, _obj) {

	_.ImageViewerTemplate = _.Template.extend({
		construct: function (options, element) {
			this._super(_obj.extend({}, options, {
				paging: {
					pushOrReplace: "replace",
					theme: "fg-light",
					type: "default",
					size: 1,
					position: "none",
					scrollToTop: false
				}
			}), element);
			/**
			 * @summary The jQuery object containing the inner element that wraps all items.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $inner
			 * @type {jQuery}
			 */
			this.$inner = this.$el.find('.fiv-inner-container');
			/**
			 * @summary The jQuery object that displays the current image count.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $current
			 * @type {jQuery}
			 */
			this.$current = this.$el.find('.fiv-count-current');
			/**
			 * @summary The jQuery object that displays the current image count.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $current
			 * @type {jQuery}
			 */
			this.$total = this.$el.find('.fiv-count-total');
			/**
			 * @summary The jQuery object for the previous button.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $prev
			 * @type {jQuery}
			 */
			this.$prev = this.$el.find('.fiv-prev');
			/**
			 * @summary The jQuery object for the next button.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $next
			 * @type {jQuery}
			 */
			this.$next = this.$el.find('.fiv-next');
			/**
			 * @summary The CSS classes for the Image Viewer template.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name cls
			 * @type {FooGallery.ImageViewerTemplate~CSSClasses}
			 */
			/**
			 * @summary The CSS selectors for the Image Viewer template.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name sel
			 * @type {FooGallery.ImageViewerTemplate~CSSSelectors}
			 */
		},
		onInit: function (event, self) {
			if (self.template.attachFooBox) {
				self.$el.on('foobox.previous', {self: self}, self.onFooBoxPrev)
						.on('foobox.next', {self: self}, self.onFooBoxNext);
			}
			self.$prev.on('click', {self: self}, self.onPrevClick);
			self.$next.on('click', {self: self}, self.onNextClick);
		},
		onFirstLoad: function(event, self){
			self.update();
		},
		/**
		 * @summary Destroy the plugin cleaning up any bound events.
		 * @memberof FooGallery.ImageViewerTemplate#
		 * @function onDestroy
		 */
		onDestroy: function (event, self) {
			if (self.template.attachFooBox) {
				self.$el.off({
					'foobox.previous': self.onFooBoxPrev,
					'foobox.next': self.onFooBoxNext
				});
			}
			self.$prev.off('click', self.onPrevClick);
			self.$next.off('click', self.onNextClick);
		},
		onAppendItem: function (event, self, item) {
			event.preventDefault();
			self.$inner.append(item.$el);
			item.fix();
			item.isAttached = true;
		},
		update: function(){
			if (this.pages){
				this.$current.text(this.pages.current);
				this.$total.text(this.pages.total);
			}
		},
		/**
		 * @summary Navigate to the previous item in the collection.
		 * @memberof FooGallery.ImageViewerTemplate#
		 * @function prev
		 * @description If there is a previous item in the collection calling this method will navigate to it displaying its' image and updating the current image count.
		 */
		prev: function () {
			if (this.pages){
				this.pages.prev();
				this.update();
			}
		},
		/**
		 * @summary Navigate to the next item in the collection.
		 * @memberof FooGallery.ImageViewerTemplate#
		 * @function next
		 * @description If there is a next item in the collection calling this method will navigate to it displaying its' image and updating the current image count.
		 */
		next: function () {
			if (this.pages){
				this.pages.next();
				this.update();
			}
		},
		/**
		 * @summary Handles the `"foobox.previous"` event allowing the plugin to remain in sync with what is displayed in the lightbox.
		 * @memberof FooGallery.ImageViewerTemplate#
		 * @function onFooBoxPrev
		 * @param {jQuery.Event} e - The jQuery.Event object for the event.
		 */
		onFooBoxPrev: function (e) {
			e.data.self.prev();
		},
		/**
		 * @summary Handles the `"foobox.next"` event allowing the plugin to remain in sync with what is displayed in the lightbox.
		 * @memberof FooGallery.ImageViewerTemplate#
		 * @function onFooBoxNext
		 * @param {jQuery.Event} e - The jQuery.Event object for the event.
		 */
		onFooBoxNext: function (e) {
			e.data.self.next();
		},
		/**
		 * @summary Handles the `"click"` event of the previous button.
		 * @memberof FooGallery.ImageViewerTemplate#
		 * @function onPrevClick
		 * @param {jQuery.Event} e - The jQuery.Event object for the event.
		 */
		onPrevClick: function (e) {
			e.preventDefault();
			e.stopPropagation();
			e.data.self.prev();
		},
		/**
		 * @summary Handles the `"click"` event of the next button.
		 * @memberof FooGallery.ImageViewerTemplate#
		 * @function onNextClick
		 * @param {jQuery.Event} e - The jQuery.Event object for the event.
		 */
		onNextClick: function (e) {
			e.preventDefault();
			e.stopPropagation();
			e.data.self.next();
		}
	});

	_.template.register("image-viewer", _.ImageViewerTemplate, {
		template: {
			attachFooBox: false
		}
	}, {
		container: "foogallery fg-image-viewer"
	});

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.obj
);
(function($, _, _obj){

	_.ThumbnailTemplate = _.Template.extend({
		construct: function (options, element) {
			this._super(_obj.extend({}, options, {
				paging: {
					type: "none"
				}
			}), element);
		}
	});

	_.template.register("thumbnail", _.ThumbnailTemplate, null, {
		container: "foogallery fg-thumbnail"
	});

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils.obj
);
(function($, _utils){

	// this automatically initializes all templates on page load
	$(function () {
		$('[id^="foogallery-"]:not(.fg-ready)').foogallery();
	});

	_utils.ready(function(){
		$('[id^="foogallery-"].fg-ready').foogallery();
	});

})(
		FooGallery.$,
		FooGallery.utils
);