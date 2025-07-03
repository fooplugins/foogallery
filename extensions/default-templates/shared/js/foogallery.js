(function($, _){

	/**
	 * @summary A reference to the jQuery object the plugin is registered with.
	 * @memberof FooGallery
	 * @function $
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
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/*!
* FooGallery.utils - Contains common utility methods and classes used in our plugins.
* @version 1.0.0
* @link https://github.com/steveush/foo-utils#readme
* @copyright Steve Usher 2021
* @license Released under the GPL-3.0 license.
*/

/**
 * @file This creates the global FooGallery.utils namespace
 */
(function ($) {
  if (!$) {
    console.warn('jQuery must be included in the page prior to the FooGallery.utils library.');
    return;
  }

  function __exists() {
    try {
      return !!window.FooGallery.utils; // does the namespace already exist?
    } catch (err) {
      return false;
    }
  }

  if (!__exists()) {
    /**
     * @summary This namespace contains common utility methods and code shared between our plugins.
     * @global
     * @namespace FooGallery.utils
     * @description This namespace relies on jQuery being included in the page prior to it being loaded.
     */
    window.FooGallery.utils = {
      /**
       * @summary A reference to the jQuery object the library is registered with.
       * @memberof FooGallery.utils.
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
       * @memberof FooGallery.utils.
       * @name version
       * @type {string}
       */
      version: '1.0.0'
    };
  } // at this point there will always be a FooGallery.utils namespace registered to the global scope.

})(jQuery);

(function ($, _) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return;
  /**
   * @summary Contains common type checking utility methods.
   * @memberof FooGallery.utils.
   * @namespace is
   */

  _.is = {};
  /**
   * @summary Checks if the `value` is an array.
   * @memberof FooGallery.utils.is.
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
   * @memberof FooGallery.utils.is.
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
   * @memberof FooGallery.utils.is.
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
    return (typeof HTMLElement === "undefined" ? "undefined" : _typeof(HTMLElement)) === 'object' ? value instanceof HTMLElement : !!value && _typeof(value) === 'object' && value.nodeType === 1 && typeof value.nodeName === 'string';
  };
  /**
   * @summary Checks if the `value` is empty.
   * @memberof FooGallery.utils.is.
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


  _.is.empty = function (value) {
    if (_.is.undef(value) || value === null) return true;
    if (_.is.number(value) && value === 0) return true;
    if (_.is.boolean(value) && value === false) return true;
    if (_.is.string(value) && value.length === 0) return true;
    if (_.is.array(value) && value.length === 0) return true;
    if (_.is.jq(value) && value.length === 0) return true;

    if (_.is.hash(value)) {
      for (var prop in value) {
        if (value.hasOwnProperty(prop)) return false;
      }

      return true;
    }

    return false;
  };
  /**
   * @summary Checks if the `value` is an error.
   * @memberof FooGallery.utils.is.
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
   * @memberof FooGallery.utils.is.
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
   * @memberof FooGallery.utils.is.
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
   * @memberof FooGallery.utils.is.
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


  _.is.jq = function (value) {
    return !_.is.undef($) && value instanceof $;
  };
  /**
   * @summary Checks if the `value` is a number.
   * @memberof FooGallery.utils.is.
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
   * @memberof FooGallery.utils.is.
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
   * @memberof FooGallery.utils.is.
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


  _.is.promise = function (value) {
    return _.is.object(value) && _.is.fn(value.then) && _.is.fn(value.promise);
  };
  /**
   * @summary Checks if the `value` is a valid CSS length.
   * @memberof FooGallery.utils.is.
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


  _.is.size = function (value) {
    if (!(_.is.string(value) && !_.is.empty(value)) && !_.is.number(value)) return false;
    return /^(auto|none|(?:[\d.]*)+?(?:%|px|mm|q|cm|in|pt|pc|em|ex|ch|rem|vh|vw|vmin|vmax)?)$/.test(value);
  };
  /**
   * @summary Checks if the `value` is a string.
   * @memberof FooGallery.utils.is.
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
   * @memberof FooGallery.utils.is.
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
})( // dependencies
FooGallery.utils.$, FooGallery.utils);

(function ($, _, _is) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return;
  /**
   * @memberof FooGallery.utils.
   * @namespace fn
   * @summary Contains common function utility methods.
   */

  _.fn = {};
  var fnStr = Function.prototype.toString;
  /**
   * @summary The regular expression to test if a function uses the `this._super` method applied by the {@link FooGallery.utils.fn.add} method.
   * @memberof FooGallery.utils.fn.
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

  _.fn.CONTAINS_SUPER = /xyz/.test(fnStr.call(function () {
    //noinspection JSUnresolvedVariable,BadExpressionStatementJS
    xyz;
  })) ? /\b_super\b/ : /.*/;
  /**
   * @summary An empty function that does nothing. Useful for setting a default value and checking if it has changed.
   * @memberof FooGallery.utils.fn.
   * @function noop
   */

  _.fn.noop = function () {};
  /**
   * @summary Adds or overrides the given method `name` on the `proto` using the supplied `fn`.
   * @memberof FooGallery.utils.fn.
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


  _.fn.addOrOverride = function (proto, name, fn) {
    if (!_is.object(proto) || !_is.string(name) || _is.empty(name) || !_is.fn(fn)) return;

    var _super = proto[name],
        wrap = _is.fn(_super) && _.fn.CONTAINS_SUPER.test(fnStr.call(fn)); // only wrap the function if it overrides a method and makes use of `_super` within it's body.


    proto[name] = wrap ? function (_super, fn) {
      // create a new wrapped that exposes the original method as `_super`
      return function () {
        var tmp = this._super;
        this._super = _super;
        var ret = fn.apply(this, arguments);
        this._super = tmp;
        return ret;
      };
    }(_super, fn) : fn;
  };
  /**
   * @summary Exposes the `methods` from the `source` on the `target`.
   * @memberof FooGallery.utils.fn.
   * @function expose
   * @param {Object} source - The object to expose methods from.
   * @param {Object} target - The object to expose methods on.
   * @param {String[]} methods - An array of method names to expose.
   * @param {*} [thisArg] - The value of `this` within the exposed `methods`. Defaults to the `source` object.
   */


  _.fn.expose = function (source, target, methods, thisArg) {
    if (_is.object(source) && _is.object(target) && _is.array(methods)) {
      thisArg = _is.undef(thisArg) ? source : thisArg;
      methods.forEach(function (method) {
        if (_is.string(method) && _is.fn(source[method])) {
          target[method] = source[method].bind(thisArg);
        }
      });
    }
  };
  /**
   * @summary Use the `Function.prototype.apply` method on a class constructor using the `new` keyword.
   * @memberof FooGallery.utils.fn.
   * @function apply
   * @param {Object} klass - The class to create.
   * @param {Array} [args=[]] - The arguments to pass to the constructor.
   * @returns {Object} The new instance of the `klass` created with the supplied `args`.
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


  _.fn.apply = function (klass, args) {
    args.unshift(klass);
    return new (Function.prototype.bind.apply(klass, args))();
  };
  /**
   * @summary Converts the default `arguments` object into a proper array.
   * @memberof FooGallery.utils.fn.
   * @function arg2arr
   * @param {IArguments} args - The arguments object to create an array from.
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


  _.fn.arg2arr = function (args) {
    return Array.prototype.slice.call(args);
  };
  /**
   * @summary Debounce the `fn` by the supplied `time`.
   * @memberof FooGallery.utils.fn.
   * @function debounce
   * @param {function} fn - The function to debounce.
   * @param {number} time - The time in milliseconds to delay execution.
   * @returns {function}
   * @description This returns a wrapped version of the `fn` which delays its' execution by the supplied `time`. Additional calls to the function will extend the delay until the `time` expires.
   */


  _.fn.debounce = function (fn, time) {
    var timeout;
    return function () {
      var ctx = this,
          args = _.fn.arg2arr(arguments);

      clearTimeout(timeout);
      timeout = setTimeout(function () {
        fn.apply(ctx, args);
      }, time);
    };
  };
  /**
   * @summary Throttles the `fn` by the supplied `time`.
   * @memberof FooGallery.utils.fn.
   * @function throttle
   * @param {function} fn - The function to throttle.
   * @param {number} time - The time in milliseconds to delay execution.
   * @returns {function}
   * @description This returns a wrapped version of the `fn` which ensures it's executed only once every `time` milliseconds. The first call to the function will be executed, after that only the last of any additional calls will be executed once the `time` expires.
   */


  _.fn.throttle = function (fn, time) {
    var last, timeout;
    return function () {
      var ctx = this,
          args = _.fn.arg2arr(arguments);

      if (!last) {
        fn.apply(ctx, args);
        last = Date.now();
      } else {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
          if (Date.now() - last >= time) {
            fn.apply(ctx, args);
            last = Date.now();
          }
        }, time - (Date.now() - last));
      }
    };
  };
  /**
   * @summary A resolved promise object.
   * @memberof FooGallery.utils.fn.
   * @name resolved
   * @type {Promise}
   */


  _.fn.resolved = $.Deferred().resolve().promise();
  /**
   * @summary A rejected promise object.
   * @memberof FooGallery.utils.fn.
   * @name rejected
   * @type {Promise}
   */

  _.fn.rejected = $.Deferred().reject().promise();
  /**
   * @summary Return a promise rejected using the supplied args.
   * @memberof FooGallery.utils.fn.
   * @function reject
   * @param {*} [arg1] - The first argument to reject the promise with.
   * @param {...*} [argN] - Any additional arguments to reject the promise with.
   * @returns {Promise}
   */

  _.fn.reject = function (arg1, argN) {
    var def = $.Deferred(),
        args = _.fn.arg2arr(arguments);

    return def.reject.apply(def, args).promise();
  };
  /**
   * @summary Return a promise resolved using the supplied args.
   * @memberof FooGallery.utils.fn.
   * @function resolve
   * @param {*} [arg1] - The first argument to resolve the promise with.
   * @param {...*} [argN] - Any additional arguments to resolve the promise with.
   * @returns {Promise}
   */


  _.fn.resolve = function (arg1, argN) {
    var def = $.Deferred(),
        args = _.fn.arg2arr(arguments);

    return def.resolve.apply(def, args).promise();
  };
  /**
   * @summary Return a promise rejected using the supplied args.
   * @memberof FooGallery.utils.fn.
   * @function rejectWith
   * @param {*} thisArg - The value of `this` within the promises callbacks.
   * @param {*} [arg1] - The first argument to reject the promise with.
   * @param {...*} [argN] - Any additional arguments to reject the promise with.
   * @returns {Promise}
   */


  _.fn.rejectWith = function (thisArg, arg1, argN) {
    var def = $.Deferred(),
        args = _.fn.arg2arr(arguments);

    args.shift(); // remove the thisArg

    return def.rejectWith(thisArg, args).promise();
  };
  /**
   * @summary Return a promise resolved using the supplied args.
   * @memberof FooGallery.utils.fn.
   * @function resolveWith
   * @param {*} thisArg - The value of `this` within the promises callbacks.
   * @param {*} [arg1] - The first argument to resolve the promise with.
   * @param {...*} [argN] - Any additional arguments to resolve the promise with.
   * @returns {Promise}
   */


  _.fn.resolveWith = function (thisArg, arg1, argN) {
    var def = $.Deferred(),
        args = _.fn.arg2arr(arguments);

    args.shift(); // remove the thisArg

    return def.resolveWith(thisArg, args).promise();
  };
  /**
   * @summary Waits for all promises to complete before resolving with an array containing the return value of each. This method will reject immediately with the first rejection message or error.
   * @memberof FooGallery.utils.fn.
   * @function all
   * @param {Promise[]} promises - The array of promises to wait for.
   * @returns {Promise}
   */


  _.fn.all = function (promises) {
    var d = $.Deferred(),
        results = [];

    if (_is.array(promises) && promises.length > 0) {
      (function () {
        /**
         * Pushes the arguments into the results array at the supplied index.
         * @ignore
         * @param {number} index
         * @param {Array} args
         */
        var pushResult = function pushResult(index, args) {
          if (rejected) return;
          results[index] = args.length === 0 ? undefined : args.length === 1 ? args[0] : args;
          remaining--;
          if (!remaining) d.resolve(results);
        };

        var remaining = promises.length,
            rejected = false;
        var i = 0,
            l = promises.length;

        var _loop = function _loop() {
          if (rejected) return "break";
          var j = i; // hold a scoped reference that can be used in the async callbacks

          if (_is.promise(promises[j])) {
            promises[j].then(function () {
              pushResult(j, _.fn.arg2arr(arguments));
            }, function () {
              if (rejected) return;
              rejected = true;
              d.reject.apply(d, _.fn.arg2arr(arguments));
            });
          } else {
            // if we were supplied something that was not a promise then just add it as a fulfilled result
            pushResult(j, [promises[j]]);
          }
        };

        for (; i < l; i++) {
          var _ret = _loop();

          if (_ret === "break") break;
        }
      })();
    } else {
      d.resolve(results);
    }

    return d.promise();
  };
  /**
   * @summary Waits for all promises to complete before resolving with an array containing the outcome of each.
   * @memberof FooGallery.utils.fn.
   * @function allSettled
   * @param {Promise[]} promises - The array of promises to wait for.
   * @returns {Promise}
   */


  _.fn.allSettled = function (promises) {
    var d = $.Deferred(),
        results = [];

    if (_is.array(promises) && promises.length > 0) {
      (function () {
        /**
         * Sets the value in the results array using the status and args.
         * @ignore
         * @param {number} index
         * @param {string} status
         * @param {Array} args
         */
        var setResult = function setResult(index, status, args) {
          results[index] = {
            status: status
          };

          if (args.length > 0) {
            var prop = status === "rejected" ? "reason" : "value";
            results[index][prop] = args.length === 1 ? args[0] : args;
          }

          remaining--;
          if (!remaining) d.resolve(results);
        };

        var remaining = promises.length;
        var i = 0,
            l = promises.length;

        var _loop2 = function _loop2() {
          var j = i; // hold a scoped reference that can be used in the async callbacks

          if (_is.promise(promises[j])) {
            promises[j].then(function () {
              setResult(j, "fulfilled", _.fn.arg2arr(arguments));
            }, function () {
              setResult(j, "rejected", _.fn.arg2arr(arguments));
            });
          } else {
            // if we were supplied something that was not a promise then just add it as a fulfilled result
            setResult(j, "fulfilled", [promises[j]]);
          }
        };

        for (; i < l; i++) {
          _loop2();
        }
      })();
    } else {
      d.resolve(results);
    }

    return d.promise();
  };
})( // dependencies
FooGallery.utils.$, FooGallery.utils, FooGallery.utils.is);

(function (_, _is) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return;
  /**
   * @summary Contains common url utility methods.
   * @memberof FooGallery.utils.
   * @namespace url
   */

  _.url = {}; // used for parsing a url into it's parts.

  var _a = document.createElement('a');
  /**
   * @summary Parses the supplied url into an object containing it's component parts.
   * @memberof FooGallery.utils.url.
   * @function parts
   * @param {string} url - The url to parse.
   * @returns {FooGallery.utils.url~Parts}
   * @example {@run true}
   * // alias the FooGallery.utils.url namespace
   * var _url = FooGallery.utils.url;
   *
   * console.log( _url.parts( "http://example.com/path/?param=true#something" ) ); // => {"hash":"#something", ...}
   */


  _.url.parts = function (url) {
    _a.href = url;
    var port = _a.port ? _a.port : ["http:", "https:"].indexOf(_a.protocol) !== -1 ? _a.protocol === "https:" ? "443" : "80" : "",
        host = _a.hostname + (port ? ":" + port : ""),
        origin = _a.origin ? _a.origin : _a.protocol + "//" + host,
        pathname = _a.pathname.slice(0, 1) === "/" ? _a.pathname : "/" + _a.pathname;
    return {
      hash: _a.hash,
      host: host,
      hostname: _a.hostname,
      href: _a.href,
      origin: origin,
      pathname: pathname,
      port: port,
      protocol: _a.protocol,
      search: _a.search
    };
  };
  /**
   * @summary Given a <code>url</code> that could be relative or full this ensures a full url is returned.
   * @memberof FooGallery.utils.url.
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


  _.url.full = function (url) {
    if (!_is.string(url) || _is.empty(url)) return null;
    _a.href = url;
    return _a.href;
  };
  /**
   * @summary Gets or sets a parameter in the given <code>search</code> string.
   * @memberof FooGallery.utils.url.
   * @function param
   * @param {string} search - The search string to use (usually `location.search`).
   * @param {string} key - The key of the parameter.
   * @param {?string} [value] - The value to set for the parameter. If not provided the current value for the `key` is returned.
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


  _.url.param = function (search, key, value) {
    if (!_is.string(search) || !_is.string(key) || _is.empty(key)) return search;
    var regex, match, result, param;

    if (_is.undef(value)) {
      regex = new RegExp('[?|&]' + key + '=([^&;]+?)(&|#|;|$)'); // regex to match the key and it's value but only capture the value

      match = regex.exec(search) || ["", ""]; // match the param otherwise return an empty string match

      result = match[1].replace(/\+/g, '%20'); // replace any + character's with spaces

      return _is.string(result) && !_is.empty(result) ? decodeURIComponent(result) : null; // decode the result otherwise return null
    }

    if (_is.empty(value)) {
      regex = new RegExp('^([^#]*\?)(([^#]*)&)?' + key + '(\=[^&#]*)?(&|#|$)');
      result = search.replace(regex, '$1$3$5').replace(/^([^#]*)((\?)&|\?(#|$))/, '$1$3$4');
    } else {
      regex = new RegExp('([?&])' + key + '[^&]*'); // regex to match the key and it's current value but only capture the preceding ? or & char

      param = key + '=' + encodeURIComponent(value);
      result = search.replace(regex, '$1' + param); // replace any existing instance of the key with the new value
      // If nothing was replaced, then add the new param to the end

      if (result === search && !regex.test(result)) {
        // if no replacement occurred and the parameter is not currently in the result then add it
        result += (result.indexOf("?") !== -1 ? '&' : '?') + param;
      }
    }

    return result;
  }; //######################
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

})( // dependencies
FooGallery.utils, FooGallery.utils.is);

(function (_, _is, _fn) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return;
  /**
   * @summary Contains common string utility methods.
   * @memberof FooGallery.utils.
   * @namespace str
   */

  _.str = {};
  /**
   * @summary Removes whitespace from both ends of the target string.
   * @memberof FooGallery.utils.str.
   * @function trim
   * @param {string} target - The string to trim.
   * @returns {string|null} Returns `null` if the supplied target is not a string.
   */

  _.str.trim = function (target) {
    return _is.string(target) ? target.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '') : null;
  };
  /**
   * @summary Converts the given `target` to camel case.
   * @memberof FooGallery.utils.str.
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
    if (!_is.string(target)) return target;
    if (target.toUpperCase() === target) return target.toLowerCase();
    return target.replace(/^([A-Z])|[-\s_]+(\w)/g, function (match, p1, p2) {
      if (_is.string(p2)) return p2.toUpperCase();
      return p1.toLowerCase();
    });
  };
  /**
   * @summary Converts the given `target` to kebab case. Non-alphanumeric characters are converted to `-`.
   * @memberof FooGallery.utils.str.
   * @function kebab
   * @param {string} target - The string to kebab case.
   * @returns {string}
   * @example {@run true}
   * // alias the FooGallery.utils.str namespace
   * var _str = FooGallery.utils.str;
   *
   * console.log( _str.kebab( "max-width" ) ); // => "max-width"
   * console.log( _str.kebab( "max--width" ) ); // => "max-width"
   * console.log( _str.kebab( "max Width" ) ); // => "max-width"
   * console.log( _str.kebab( "Max_width" ) ); // => "max-width"
   * console.log( _str.kebab( "MaxWidth" ) ); // => "max-width"
   * console.log( _str.kebab( "Non-alphanumeric ch@racters are converted to dashes!" ) ); // => "non-alphanumeric-ch-racters-are-converted-to-dashes"
   */


  _.str.kebab = function (target) {
    if (!_is.string(target)) return target;
    return target.match(/[A-Z]{2,}(?=[A-Z][a-z0-9]*|\b)|[A-Z]?[a-z0-9]*|[A-Z]|[0-9]+/g).filter(Boolean).map(function (x) {
      return x.toLowerCase();
    }).join('-');
  };
  /**
   * @summary Checks if the `target` contains the given `substr`.
   * @memberof FooGallery.utils.str.
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
    return substr.length <= target.length && (!!ignoreCase ? target.toUpperCase().indexOf(substr.toUpperCase()) : target.indexOf(substr)) !== -1;
  };
  /**
   * @summary Checks if the `target` contains the given `word`.
   * @memberof FooGallery.utils.str.
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


  _.str.containsWord = function (target, word, ignoreCase) {
    if (!_is.string(target) || _is.empty(target) || !_is.string(word) || _is.empty(word) || target.length < word.length) return false;
    var parts = target.split(/\W/);
    var i = 0,
        len = parts.length;

    for (; i < len; i++) {
      if (ignoreCase ? parts[i].toUpperCase() === word.toUpperCase() : parts[i] === word) return true;
    }

    return false;
  };
  /**
   * @summary Checks if the `target` ends with the given `substr`.
   * @memberof FooGallery.utils.str.
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
    if (!_is.string(target) || !_is.string(substr) || substr.length > target.length) return false;
    return target.slice(target.length - substr.length) === substr;
  };
  /**
   * @summary Checks if the `target` starts with the given `substr`.
   * @memberof FooGallery.utils.str.
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
    return target.slice(0, substr.length) === substr;
  };
  /**
   * @summary Escapes the `target` for use in a regular expression.
   * @memberof FooGallery.utils.str.
   * @function escapeRegExp
   * @param {string} target - The string to escape.
   * @returns {string}
   * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions
   */


  _.str.escapeRegExp = function (target) {
    if (!_is.string(target)) return target;
    return target.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  };
  /**
   * @summary Generates a 32 bit FNV-1a hash from the given `target`.
   * @memberof FooGallery.utils.str.
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


  _.str.fnv1a = function (target) {
    if (!_is.string(target) || _is.empty(target)) return null;
    var i,
        l,
        hval = 0x811c9dc5;

    for (i = 0, l = target.length; i < l; i++) {
      hval ^= target.charCodeAt(i);
      hval += (hval << 1) + (hval << 4) + (hval << 7) + (hval << 8) + (hval << 24);
    }

    return hval >>> 0;
  };
  /**
   * @summary Returns the remainder of the `target` split on the first index of the given `substr`.
   * @memberof FooGallery.utils.str.
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
    return _.str.contains(target, substr) ? target.substring(target.indexOf(substr) + substr.length) : null;
  };
  /**
   * @summary Joins any number of strings using the given `separator`.
   * @memberof FooGallery.utils.str.
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


  _.str.join = function (separator, part, partN) {
    if (!_is.string(separator) || !_is.string(part)) return null;

    var parts = _fn.arg2arr(arguments);

    separator = parts.shift();
    var i,
        l,
        result = parts.shift();

    for (i = 0, l = parts.length; i < l; i++) {
      part = parts[i];
      if (_is.empty(part)) continue;

      if (_.str.endsWith(result, separator)) {
        result = result.slice(0, result.length - separator.length);
      }

      if (_.str.startsWith(part, separator)) {
        part = part.slice(separator.length);
      }

      result += separator + part;
    }

    return result;
  };
  /**
   * @summary Returns the first part of the `target` split on the first index of the given `substr`.
   * @memberof FooGallery.utils.str.
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
    return _.str.contains(target, substr) ? target.substring(0, target.indexOf(substr)) : target;
  };
  /**
   * @summary A basic string formatter that can use both index and name based placeholders but handles only string or number replacements.
   * @memberof FooGallery.utils.str.
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


  _.str.format = function (target, arg1, argN) {
    var args = _fn.arg2arr(arguments);

    target = args.shift(); // remove the target from the args

    if (_is.string(target) && args.length > 0) {
      if (args.length === 1 && (_is.array(args[0]) || _is.object(args[0]))) {
        args = args[0];
      }

      _.each(args, function (value, placeholder) {
        target = target.replace(new RegExp("\\{" + placeholder + "\\}", "gi"), value + "");
      });
    }

    return target;
  };
})( // dependencies
FooGallery.utils, FooGallery.utils.is, FooGallery.utils.fn);

(function ($, _, _is, _fn, _str) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return;
  /**
   * @summary Contains common object utility methods.
   * @memberof FooGallery.utils.
   * @namespace obj
   */

  _.obj = {};
  /**
   * @summary Creates a new object with the specified prototype.
   * @memberof FooGallery.utils.obj.
   * @function create
   * @param {Object} proto - The object which should be the prototype of the newly-created object.
   * @returns {Object} A new object with the specified prototype.
   * @description This is a basic implementation of the {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/create|Object.create} method.
   */

  _.obj.create = function (proto) {
    if (!_is.object(proto)) throw TypeError('Argument must be an object');

    function Obj() {}

    Obj.prototype = proto;
    return new Obj();
  };
  /**
   * @summary Merge the contents of two or more objects together into the first `target` object.
   * @memberof FooGallery.utils.obj.
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


  _.obj.extend = function (target, object, objectN) {
    target = _is.object(target) ? target : {};

    var objects = _fn.arg2arr(arguments);

    objects.shift();

    _.each(objects, function (object) {
      _.obj.merge(target, object);
    });

    return target;
  };
  /**
   * @summary Merge the contents of two objects together into the first `target` object.
   * @memberof FooGallery.utils.obj.
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


  _.obj.merge = function (target, object) {
    target = _is.hash(target) ? target : {};
    object = _is.hash(object) ? object : {};

    for (var prop in object) {
      if (object.hasOwnProperty(prop)) {
        if (_is.hash(object[prop])) {
          target[prop] = _is.hash(target[prop]) ? target[prop] : {};

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
   * @memberof FooGallery.utils.obj.
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


  _.obj.mergeValid = function (target, validators, object, mappings) {
    if (!_is.hash(object) || !_is.hash(validators)) return target;
    validators = _is.hash(validators) ? validators : {};
    mappings = _is.hash(mappings) ? mappings : {};
    var prop, maps, value;

    for (prop in validators) {
      if (!validators.hasOwnProperty(prop) || !_is.fn(validators[prop])) continue;
      maps = _is.array(mappings[prop]) ? mappings[prop] : _is.string(mappings[prop]) ? [mappings[prop]] : [prop];

      _.each(maps, function (map) {
        value = _.obj.prop(object, map);
        if (_is.undef(value)) return; // continue

        if (validators[prop](value)) {
          _.obj.prop(target, prop, value);

          return false; // break
        }
      });
    }

    return target;
  };
  /**
   * @summary Get or set a property value given its `name`.
   * @memberof FooGallery.utils.obj.
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


  _.obj.prop = function (object, name, value) {
    if (!_is.object(object) || _is.empty(name)) return;
    var parts, last;

    if (_is.undef(value)) {
      if (_str.contains(name, '.')) {
        parts = name.split('.');
        last = parts.length - 1;

        _.each(parts, function (part, i) {
          if (i === last) {
            value = object[part];
          } else if (_is.hash(object[part])) {
            object = object[part];
          } else {
            // exit early
            return false;
          }
        });
      } else if (!_is.undef(object[name])) {
        value = object[name];
      }

      return value;
    }

    if (_str.contains(name, '.')) {
      parts = name.split('.');
      last = parts.length - 1;

      _.each(parts, function (part, i) {
        if (i === last) {
          object[part] = value;
        } else {
          object = _is.hash(object[part]) ? object[part] : object[part] = {};
        }
      });
    } else if (!_is.undef(object[name])) {
      object[name] = value;
    }
  }; //######################
  //## Type Definitions ##
  //######################

  /**
   * @summary An object used by the {@link FooGallery.utils.obj.mergeValid|mergeValid} method to map new values onto the `target` object.
   * @typedef {Object.<string,(string|Array.<string>)>} FooGallery.utils.obj~Mappings
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

})( // dependencies
FooGallery.utils.$, FooGallery.utils, FooGallery.utils.is, FooGallery.utils.fn, FooGallery.utils.str);

(function ($, _, _is) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return; // any methods that have dependencies but don't fall into a specific subset or namespace can be added here

  /**
   * @summary The callback for the {@link FooGallery.utils.ready} method.
   * @callback FooGallery.utils~readyCallback
   * @param {jQuery} $ - The instance of jQuery the plugin was registered with.
   * @this window
   * @see Take a look at the {@link FooGallery.utils.ready} method for example usage.
   */

  /**
   * @summary Waits for the DOM to be accessible and then executes the supplied callback.
   * @memberof FooGallery.utils.
   * @function ready
   * @param {FooGallery.utils~readyCallback} callback - The function to execute once the DOM is accessible.
   * @example {@caption This method can be used as a replacement for the jQuery ready callback to avoid an error in another script stopping our scripts from running.}
   * FooGallery.utils.ready(function($){
   * 	// do something
   * });
   */

  _.ready = function (callback) {
    function onready() {
      try {
        callback.call(window, _.$);
      } catch (err) {
        console.error(err);
      }
    }

    if (Function('/*@cc_on return true@*/')() ? document.readyState === "complete" : document.readyState !== "loading") onready();else document.addEventListener('DOMContentLoaded', onready, false);
  };
  /**
   * @summary Executed once for each array index or object property until it returns a truthy value.
   * @callback FooGallery.utils~findCallback
   * @param {*} value - The current value being iterated over. This could be either an element in an array or the value of an object property.
   * @param {(number|string)} [key] - The array index or property name of the `value`.
   * @param {(Object|Array)} [object] - The array or object currently being searched.
   * @returns {boolean} A truthy value.
   */

  /**
   * @summary Returns the value of the first element or property in the provided target that satisfies the provided test function.
   * @memberof FooGallery.utils.
   * @function find
   * @param {(Object|Array)} target - The object or array to search.
   * @param {FooGallery.utils~findCallback} callback - A function to execute for each value in the target.
   * @param {*} [thisArg] - The `this` value within the `callback`.
   * @returns {*} The value of the first element or property in the provided target that satisfies the provided test function. Otherwise, `undefined` is returned.
   */


  _.find = function (target, callback, thisArg) {
    if (!_is.fn(callback)) return;
    thisArg = _is.undef(thisArg) ? callback : thisArg;
    var i, l;

    if (_is.array(target)) {
      for (i = 0, l = target.length; i < l; i++) {
        if (callback.call(thisArg, target[i], i, target)) {
          return target[i];
        }
      }
    } else if (_is.object(target)) {
      var keys = Object.keys(target);

      for (i = 0, l = keys.length; i < l; i++) {
        if (callback.call(thisArg, target[keys[i]], keys[i], target)) {
          return target[keys[i]];
        }
      }
    }
  };
  /**
   * @summary Executed once for each array index or object property.
   * @callback FooGallery.utils~eachCallback
   * @param {*} value - The current value being iterated over. This could be either an element in an array or the value of an object property.
   * @param {(number|string)} [key] - The array index or property name of the `value`.
   * @param {(Object|Array)} [object] - The array or object currently being searched.
   * @returns {(boolean|void)} Return `false` to break out of the loop, all other values are ignored.
   */

  /**
   * @summary Iterate over all indexes or properties of the provided target executing the provided callback once per value.
   * @memberof FooGallery.utils.
   * @function each
   * @param {(Object|Array)} object - The object or array to search.
   * @param {FooGallery.utils~eachCallback} callback - A function to execute for each value in the target.
   * @param {*} [thisArg] - The `this` value within the `callback`.
   */


  _.each = function (object, callback, thisArg) {
    if (!_is.fn(callback)) return;
    thisArg = _is.undef(thisArg) ? callback : thisArg;
    var i, l, result;

    if (_is.array(object)) {
      for (i = 0, l = object.length; i < l; i++) {
        result = callback.call(thisArg, object[i], i, object);
        if (result === false) break;
      }
    } else if (_is.object(object)) {
      var keys = Object.keys(object);

      for (i = 0, l = keys.length; i < l; i++) {
        result = callback.call(thisArg, object[keys[i]], keys[i], object);
        if (result === false) break;
      }
    }
  };
  /**
   * @summary Checks if a value exists within an array.
   * @memberof FooGallery.utils.
   * @function inArray
   * @param {*} needle - The value to search for.
   * @param {Array} haystack - The array to search within.
   * @returns {number} Returns the index of the value if found otherwise -1.
   */


  _.inArray = function (needle, haystack) {
    if (_is.array(haystack)) {
      return haystack.indexOf(needle);
    }

    return -1;
  };
  /**
   * @summary Convert CSS class names into CSS selectors.
   * @memberof FooGallery.utils.
   * @function selectify
   * @param {(string|string[]|object)} classes - A space delimited string of CSS class names or an array of them with each item being included in the selector using the OR (`,`) syntax as a separator. If an object is supplied the result will be an object with the same property names but the values converted to selectors.
   * @returns {(object|string)}
   * @example {@caption Shows how the method can be used.}
   * // alias the FooGallery.utils namespace
   * var _ = FooGallery.utils;
   *
   * console.log( _.selectify("my-class") ); // => ".my-class"
   * console.log( _.selectify("my-class my-other-class") ); // => ".my-class.my-other-class"
   * console.log( _.selectify(["my-class", "my-other-class"]) ); // => ".my-class,.my-other-class"
   * console.log( _.selectify({
   * 	class1: "my-class",
   * 	class2: "my-class my-other-class",
   * 	class3: ["my-class", "my-other-class"]
   * }) ); // => { class1: ".my-class", class2: ".my-class.my-other-class", class3: ".my-class,.my-other-class" }
   */


  _.selectify = function (classes) {
    if (_is.empty(classes)) return null;

    if (_is.hash(classes)) {
      var result = {},
          selector;

      for (var name in classes) {
        if (!classes.hasOwnProperty(name)) continue;
        selector = _.selectify(classes[name]);

        if (selector) {
          result[name] = selector;
        }
      }

      return result;
    }

    if (_is.string(classes) || _is.array(classes)) {
      if (_is.string(classes)) classes = [classes];
      return classes.map(function (str) {
        return _is.string(str) ? "." + str.split(/\s/g).join(".") : null;
      }).join(",");
    }

    return null;
  };
  /**
   * @ignore
   * @summary Internal replacement for the `requestAnimationFrame` method if the browser doesn't support any form of the method.
   * @param {function} callback - The function to call when it's time to update your animation for the next repaint.
   * @return {number} - The request id that uniquely identifies the entry in the callback list.
   */


  function raf(callback) {
    return setTimeout(callback, 1000 / 60);
  }
  /**
   * @ignore
   * @summary Internal replacement for the `cancelAnimationFrame` method if the browser doesn't support any form of the method.
   * @param {number} requestID - The ID value returned by the call to {@link FooGallery.utils.requestFrame|requestFrame} that requested the callback.
   */


  function caf(requestID) {
    clearTimeout(requestID);
  }
  /**
   * @summary A cross browser wrapper for the `requestAnimationFrame` method.
   * @memberof FooGallery.utils.
   * @function requestFrame
   * @param {function} callback - The function to call when it's time to update your animation for the next repaint.
   * @return {number} - The request id that uniquely identifies the entry in the callback list.
   */


  _.requestFrame = (window.requestAnimationFrame || window.webkitRequestAnimationFrame || raf).bind(window);
  /**
   * @summary A cross browser wrapper for the `cancelAnimationFrame` method.
   * @memberof FooGallery.utils.
   * @function cancelFrame
   * @param {number} requestID - The ID value returned by the call to {@link FooGallery.utils.requestFrame|requestFrame} that requested the callback.
   */

  _.cancelFrame = (window.cancelAnimationFrame || window.webkitCancelAnimationFrame || caf).bind(window);
  /**
   * @summary Registers a callback with the next available animation frame.
   * @memberof FooGallery.utils.
   * @function nextFrame
   * @param {function} callback - The callback to execute for the next frame.
   * @param {*} [thisArg] - The value of `this` within the callback. Defaults to the callback itself.
   * @returns {Promise} Returns a promise object that is resolved using the return value of the callback.
   */

  _.nextFrame = function (callback, thisArg) {
    return $.Deferred(function (def) {
      if (!_is.fn(callback)) {
        def.reject(new Error('Provided callback is not a function.'));
      } else {
        thisArg = _is.undef(thisArg) ? callback : thisArg;

        _.requestFrame(function () {
          try {
            def.resolve(callback.call(thisArg));
          } catch (err) {
            def.reject(err);
          }
        });
      }
    }).promise();
  };
})( // dependencies
FooGallery.utils.$, FooGallery.utils, FooGallery.utils.is);

(function ($, _, _is, _obj, _fn) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return;
  /**
   * @summary A base class providing some helper methods for prototypal inheritance.
   * @memberof FooGallery.utils.
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

  _.Class = function () {};
  /**
   * @ignore
   * @summary The original function when within the scope of an overriding method.
   * @memberof FooGallery.utils.Class#
   * @function _super
   * @param {...*} [argN] - The same arguments as the base method.
   * @returns {*} The result of the base method.
   * @description This is only available within the scope of an overriding method if it was created using the {@link FooGallery.utils.Class.extend|extend}, {@link FooGallery.utils.Class.override|override} or {@link FooGallery.utils.fn.addOrOverride} methods.
   * @see {@link FooGallery.utils.fn.addOrOverride} to see an example of how this property is used.
   */

  /**
   * @summary Creates a new class that inherits from this one which in turn allows itself to be extended.
   * @memberof FooGallery.utils.Class.
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


  _.Class.extend = function (definition) {
    definition = _is.hash(definition) ? definition : {};

    var proto = _obj.create(this.prototype); // create a new prototype to work with so we don't modify the original
    // iterate over all properties in the supplied definition and update the prototype


    for (var name in definition) {
      if (!definition.hasOwnProperty(name)) continue;

      _fn.addOrOverride(proto, name, definition[name]);
    } // if no construct method is defined add a default one that does nothing


    proto.construct = _is.fn(proto.construct) ? proto.construct : function () {}; // create the new class using the prototype made above

    function Class() {
      if (!_is.fn(this.construct)) throw new SyntaxError('FooGallery.utils.Class objects must be constructed with the "new" keyword.');
      this.construct.apply(this, arguments);
    }

    Class.prototype = proto; //noinspection JSUnresolvedVariable

    Class.prototype.constructor = _is.fn(proto.__ctor__) ? proto.__ctor__ : Class;
    Class.extend = _.Class.extend;
    Class.override = _.Class.override;
    Class.getBaseClasses = _.Class.getBaseClasses;
    Class.__baseClass__ = this;
    return Class;
  };
  /**
   * @summary Overrides a single method on this class.
   * @memberof FooGallery.utils.Class.
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


  _.Class.override = function (name, fn) {
    _fn.addOrOverride(this.prototype, name, fn);
  };
  /**
   * @summary The base class for this class.
   * @memberof FooGallery.utils.Class.
   * @name __baseClass__
   * @type {?FooGallery.utils.Class}
   * @private
   */


  _.Class.__baseClass__ = null;

  function getBaseClasses(klass, result) {
    if (!_is.array(result)) result = [];

    if (_is.fn(klass) && klass.__baseClass__ !== null) {
      result.unshift(klass.__baseClass__);
      return getBaseClasses(klass.__baseClass__, result);
    }

    return result;
  }
  /**
   * @summary Get an array of all base classes for this class.
   * @memberof FooGallery.utils.Class.
   * @function getBaseClasses
   * @returns {FooGallery.utils.Class[]}
   */


  _.Class.getBaseClasses = function () {
    return getBaseClasses(this, []);
  };
})( // dependencies
FooGallery.utils.$, FooGallery.utils, FooGallery.utils.is, FooGallery.utils.obj, FooGallery.utils.fn);

(function ($, _, _is, _fn, _obj) {
  /**
   * @summary A registry class allowing classes to be easily registered and created.
   * @memberof FooGallery.utils.
   * @class ClassRegistry
   * @param {FooGallery.utils.ClassRegistry~Options} [options] - The options for the registry.
   * @augments FooGallery.utils.Class
   * @borrows FooGallery.utils.Class.extend as extend
   * @borrows FooGallery.utils.Class.override as override
   * @borrows FooGallery.utils.Class.getBaseClasses as getBaseClasses
   */
  _.ClassRegistry = _.Class.extend(
  /** @lends FooGallery.utils.ClassRegistry.prototype */
  {
    /**
     * @ignore
     * @constructs
     * @param {FooGallery.utils.ClassRegistry~Options} [options] - The options for the registry.
     */
    construct: function construct(options) {
      var self = this;
      /**
       * @summary A callback allowing the arguments supplied to the constructor of a new class to be modified.
       * @callback FooGallery.utils.ClassRegistry~beforeCreate
       * @param {FooGallery.utils.ClassRegistry~RegisteredClass} registered - The registered object containing all the information for the class being created.
       * @param {Array} args - An array of all arguments to be supplied to the constructor of the new class.
       * @returns {Array} Returns an array of all arguments to be supplied to the constructor of the new class.
       * @this FooGallery.utils.ClassRegistry
       */

      /**
       * @summary The options for the registry.
       * @typedef {?Object} FooGallery.utils.ClassRegistry~Options
       * @property {boolean} [allowBase] - Whether or not to allow base classes to be created. Base classes are registered with a priority below 0.
       * @property {?FooGallery.utils.ClassRegistry~beforeCreate} [beforeCreate] - A callback executed just prior to creating an instance of a registered class. This must return an array of arguments to supply to the constructor of the new class.
       */

      /**
       * @summary The options for this instance.
       * @memberof FooGallery.utils.ClassRegistry#
       * @name opt
       * @type {FooGallery.utils.ClassRegistry~Options}
       */

      self.opt = _obj.extend({
        allowBase: true,
        beforeCreate: null
      }, options);
      /**
       * @summary An object detailing a registered class.
       * @typedef {?Object} FooGallery.utils.ClassRegistry~RegisteredClass
       * @property {string} name - The name of the class.
       * @property {FooGallery.utils.Class} ctor - The class constructor.
       * @property {string} selector - The CSS selector for the class.
       * @property {Object} config - The configuration object for the class providing default values that can be overridden at runtime.
       * @property {number} priority - This determines the index for the class when using the {@link FooGallery.utils.ClassRegistry#find|find} method, a higher value equals a lower index.
       */

      /**
       * @summary An object containing all registered classes.
       * @memberof FooGallery.utils.ClassRegistry#
       * @name registered
       * @type {Object.<string, FooGallery.utils.ClassRegistry~RegisteredClass>}
       * @readonly
       * @example {@caption The following shows the structure of this object. The `<name>` placeholders would be the name the class was registered with.}
       * {
       * 	"<name>": {
       * 		"name": <string>,
       * 		"ctor": <function>,
       * 		"selector": <string>,
       * 		"config": <object>,
       * 		"priority": <number>
       * 	},
       * 	"<name>": {
       * 		"name": <string>,
       * 		"ctor": <function>,
       * 		"selector": <string>,
       * 		"config": <object>,
       * 		"priority": <number>
       * 	},
       * 	...
       * }
       */

      self.registered = {};
    },

    /**
     * @summary Register a class constructor with the provided `name`.
     * @memberof FooGallery.utils.ClassRegistry#
     * @function register
     * @param {string} name - The name of the class.
     * @param {FooGallery.utils.Class} klass - The class constructor to register.
     * @param {Object} [config] - The configuration object for the class providing default values that can be overridden at runtime.
     * @param {number} [priority=0] - This determines the index for the class when using the {@link FooGallery.utils.ClassRegistry#find|find} method, a higher value equals a lower index.
     * @returns {boolean} Returns `true` if the class was successfully registered.
     */
    register: function register(name, klass, config, priority) {
      var self = this;

      if (_is.string(name) && !_is.empty(name) && _is.fn(klass)) {
        priority = _is.number(priority) ? priority : 0;
        var current = self.registered[name];
        self.registered[name] = {
          name: name,
          ctor: klass,
          config: _is.hash(config) ? config : {},
          priority: !_is.undef(current) ? current.priority : priority
        };
        return true;
      }

      return false;
    },

    /**
     * @summary The callback function for the {@link FooGallery.utils.ClassRegistry#each|each} method.
     * @callback FooGallery.utils.ClassRegistry~eachCallback
     * @param {FooGallery.utils.ClassRegistry~RegisteredClass} registered - The current registered class being iterated over.
     * @param {number} index - The array index of the `registered` object.
     * @returns {(boolean|undefined)} Return `false` to break out of the loop, all other values are ignored.
     */

    /**
     * @summary Iterates over all registered classes executing the provided callback once per class.
     * @param {FooGallery.utils.ClassRegistry~eachCallback} callback - The callback to execute for each registered class.
     * @param {boolean} [prioritize=false] - Whether or not the registered classes should be prioritized before iteration.
     * @param {*} [thisArg] - The value of `this` within the callback.
     */
    each: function each(callback, prioritize, thisArg) {
      prioritize = _is.boolean(prioritize) ? prioritize : false;
      thisArg = _is.undef(thisArg) ? callback : thisArg;
      var self = this,
          names = Object.keys(self.registered),
          registered = names.map(function (name) {
        return self.registered[name];
      });

      if (prioritize) {
        registered.sort(function (a, b) {
          return b.priority - a.priority;
        });
      }

      var i = 0,
          l = registered.length;

      for (; i < l; i++) {
        var result = callback.call(thisArg, registered[i], i);
        if (result === false) break;
      }
    },

    /**
     * @summary The callback function for the {@link FooGallery.utils.ClassRegistry#find|find} method.
     * @callback FooGallery.utils.ClassRegistry~findCallback
     * @param {FooGallery.utils.ClassRegistry~RegisteredClass} registered - The current registered class being iterated over.
     * @param {number} index - The array index of the `registered` object.
     * @returns {boolean} `true` to return the current registered class.
     */

    /**
     * @summary Iterates through all registered classes until the supplied `callback` returns a truthy value.
     * @param {FooGallery.utils.ClassRegistry~findCallback} callback - The callback to execute for each registered class.
     * @param {boolean} [prioritize=false] - Whether or not the registered classes should be prioritized before iteration.
     * @param {*} [thisArg] - The value of `this` within the callback.
     * @returns {?FooGallery.utils.ClassRegistry~RegisteredClass} `null` if no registered class satisfied the `callback`.
     */
    find: function find(callback, prioritize, thisArg) {
      prioritize = _is.boolean(prioritize) ? prioritize : false;
      thisArg = _is.undef(thisArg) ? callback : thisArg;
      var self = this,
          names = Object.keys(self.registered),
          registered = names.map(function (name) {
        return self.registered[name];
      });

      if (prioritize) {
        registered.sort(function (a, b) {
          return b.priority - a.priority;
        });
      }

      var i = 0,
          l = registered.length;

      for (; i < l; i++) {
        if (callback.call(thisArg, registered[i], i)) {
          return registered[i];
        }
      }

      return null;
    },

    /**
     * @summary Create a new instance of a registered class by `name`.
     * @memberof FooGallery.utils.ClassRegistry#
     * @function create
     * @param {string} name - The name of the class to create.
     * @param {Object} [config] - Any custom configuration to supply to the class.
     * @param {...*} [argN] - Any number of additional arguments to pass to the class constructor.
     * @returns {?FooGallery.utils.Class} Returns `null` if no registered class can handle the supplied `element`.
     */
    create: function create(name, config, argN) {
      var self = this,
          args = _fn.arg2arr(arguments);

      name = args.shift();

      if (_is.string(name) && self.registered.hasOwnProperty(name)) {
        var registered = self.registered[name];
        var allowed = true;
        if (registered.priority < 0 && !self.opt.allowBase) allowed = false;

        if (allowed && _is.fn(registered.ctor)) {
          config = args.shift();
          config = self.mergeConfigurations(registered.name, config);
          args.unshift.apply(args, [registered.name, config]);
          return _fn.apply(registered.ctor, self.onBeforeCreate(registered, args));
        }
      }

      return null;
    },

    /**
     * @summary Executes the beforeCreate callback if supplied and gives sub-classes an easy way to modify the arguments supplied to newly created classes.
     * @memberof FooGallery.utils.ClassRegistry#
     * @function onBeforeCreate
     * @param {FooGallery.utils.ClassRegistry~RegisteredClass} registered - The registered class about to be created.
     * @param {Array} args - The array of arguments to be supplied to the registered class constructor.
     * @returns {Array}
     */
    onBeforeCreate: function onBeforeCreate(registered, args) {
      var self = this;

      if (self.opt.beforeCreate !== null && _is.fn(self.opt.beforeCreate)) {
        return self.opt.beforeCreate.call(self, registered, args);
      }

      return args;
    },

    /**
     * @summary Get the merged configuration for a class.
     * @memberof FooGallery.utils.ClassRegistry#
     * @function mergeConfigurations
     * @param {string} name - The name of the class to get the config for.
     * @param {Object} [config] - The user supplied defaults to override.
     * @returns {Object}
     */
    mergeConfigurations: function mergeConfigurations(name, config) {
      var self = this;

      if (_is.string(name) && self.registered.hasOwnProperty(name)) {
        // check params
        config = _is.hash(config) ? config : {};
        var baseClasses = self.getBaseClasses(name),
            eArgs = [{}];
        baseClasses.push(self.registered[name]);
        baseClasses.forEach(function (reg) {
          eArgs.push(reg.config);
        });
        eArgs.push(config);
        return _obj.extend.apply(_obj, eArgs);
      }

      return {};
    },

    /**
     * @summary Gets the registered base class for this instance.
     * @memberof FooGallery.utils.ClassRegistry#
     * @function getBaseClass
     * @returns {?FooGallery.utils.ClassRegistry~RegisteredClass}
     */
    getBaseClass: function getBaseClass() {
      return this.find(function (registered) {
        return registered.priority < 0;
      }, true);
    },

    /**
     * @summary Get all registered base classes for the supplied `name`.
     * @memberof FooGallery.utils.ClassRegistry#
     * @function getBaseClasses
     * @param {string} name - The name of the class to get the base classes for.
     * @returns {FooGallery.utils.ClassRegistry~RegisteredClass[]}
     */
    getBaseClasses: function getBaseClasses(name) {
      var self = this,
          reg = self.registered[name],
          result = [];

      if (!_is.undef(reg)) {
        reg.ctor.getBaseClasses().forEach(function (base) {
          var found = self.fromType(base);

          if (_is.hash(found)) {
            result.push(found);
          }
        });
      }

      return result;
    },

    /**
     * @summary Attempts to find a registered class given the type/constructor.
     * @memberof FooGallery.utils.ClassRegistry#
     * @function fromType
     * @param {FooGallery.utils.Class} type - The type/constructor of the registered class to find.
     * @returns {(FooGallery.utils.ClassRegistry~RegisteredClass|undefined)} Returns the registered class if found. Otherwise, `undefined` is returned.
     */
    fromType: function fromType(type) {
      if (!_is.fn(type)) return;
      return this.find(function (registered) {
        return registered.ctor === type;
      });
    }
  });
})(FooGallery.utils.$, FooGallery.utils, FooGallery.utils.is, FooGallery.utils.fn, FooGallery.utils.obj);

(function (_, _is, _str) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return; // noinspection JSUnusedGlobalSymbols

  /**
   * @summary A base event class providing just a type and defaultPrevented properties.
   * @memberof FooGallery.utils.
   * @class Event
   * @param {string} type - The type for this event.
   * @augments FooGallery.utils.Class
   * @borrows FooGallery.utils.Class.extend as extend
   * @borrows FooGallery.utils.Class.override as override
   * @description This is a very basic event class that is used internally by the {@link FooGallery.utils.EventClass#trigger} method when the first parameter supplied is simply the event name.
   *
   * To trigger your own custom event you will need to inherit from this class and then supply the instantiated event object as the first parameter to the {@link FooGallery.utils.EventClass#trigger} method.
   * @example {@caption The following shows how to use this class to create a custom event.}
   * var MyEvent = FooGallery.utils.Event.extend({
   * 	construct: function(type, customProp){
   * 	    this._super(type);
   * 	    this.myCustomProp = customProp;
   * 	}
   * });
   *
   * // to use the class you would then instantiate it and pass it as the first argument to a FooGallery.utils.EventClass's trigger method
   * var eventClass = ...; // any class inheriting from FooGallery.utils.EventClass
   * var event = new MyEvent( "my-event-type", true );
   * eventClass.trigger(event);
   */

  _.Event = _.Class.extend(
  /** @lends FooGallery.utils.Event.prototype */
  {
    /**
     * @ignore
     * @constructs
     * @param {string} type
     **/
    construct: function construct(type) {
      if (_is.empty(type)) throw new SyntaxError('FooGallery.utils.Event objects must be supplied a `type`.');

      var self = this,
          parsed = _.Event.parse(type);
      /**
       * @summary The type of event.
       * @memberof FooGallery.utils.Event#
       * @name type
       * @type {string}
       * @readonly
       */


      self.type = parsed.type;
      /**
       * @summary The namespace of the event.
       * @memberof FooGallery.utils.Event#
       * @name namespace
       * @type {string}
       * @readonly
       */

      self.namespace = parsed.namespace;
      /**
       * @summary Whether the default action should be taken or not.
       * @memberof FooGallery.utils.Event#
       * @name defaultPrevented
       * @type {boolean}
       * @readonly
       */

      self.defaultPrevented = false;
      /**
       * @summary The original {@link FooGallery.utils.EventClass} that triggered this event.
       * @memberof FooGallery.utils.Event#
       * @name target
       * @type {FooGallery.utils.EventClass}
       */

      self.target = null;
    },

    /**
     * @summary Informs the class that raised this event that its default action should not be taken.
     * @memberof FooGallery.utils.Event#
     * @function preventDefault
     */
    preventDefault: function preventDefault() {
      this.defaultPrevented = true;
    },

    /**
     * @summary Gets whether the default action should be taken or not.
     * @memberof FooGallery.utils.Event#
     * @function isDefaultPrevented
     * @returns {boolean}
     */
    isDefaultPrevented: function isDefaultPrevented() {
      return this.defaultPrevented;
    }
  });
  /**
   * @summary Parse the provided event string into a type and namespace.
   * @memberof FooGallery.utils.Event.
   * @function parse
   * @param {string} event - The event to parse.
   * @returns {{namespaced: boolean, type: string, namespace: string}} Returns an object containing the type and namespace for the event.
   */

  _.Event.parse = function (event) {
    event = _is.string(event) && !_is.empty(event) ? event : null;

    var namespaced = _str.contains(event, ".");

    return {
      namespaced: namespaced,
      type: namespaced ? _str.startsWith(event, ".") ? null : _str.until(event, ".") : event,
      namespace: namespaced ? _str.from(event, ".") : null
    };
  }; // noinspection JSUnusedGlobalSymbols

  /**
   * @summary A base class that implements a basic events interface.
   * @memberof FooGallery.utils.
   * @class EventClass
   * @augments FooGallery.utils.Class
   * @borrows FooGallery.utils.Class.extend as extend
   * @borrows FooGallery.utils.Class.override as override
   * @description This is a very basic events implementation that provides just enough to cover most needs.
   */


  _.EventClass = _.Class.extend(
  /** @lends FooGallery.utils.EventClass.prototype */
  {
    /**
     * @ignore
     * @constructs
     **/
    construct: function construct() {
      /**
       * @summary An object containing all the required info to execute a listener.
       * @typedef {Object} FooGallery.utils.EventClass~RegisteredListener
       * @property {string} namespace - The namespace for the listener.
       * @property {function} fn - The callback function for the listener.
       * @property {*} thisArg - The `this` value to execute the callback with.
       */

      /**
       * @summary An object containing a mapping of events to listeners.
       * @typedef {Object.<string, Array<FooGallery.utils.EventClass~RegisteredListener>>} FooGallery.utils.EventClass~RegisteredEvents
       */

      /**
       * @summary The object used to register event handlers.
       * @memberof FooGallery.utils.EventClass#
       * @name events
       * @type {FooGallery.utils.EventClass~RegisteredEvents}
       */
      this.events = {};
    },

    /**
     * @summary Destroy the current instance releasing used resources.
     * @memberof FooGallery.utils.EventClass#
     * @function destroy
     */
    destroy: function destroy() {
      this.events = {};
    },

    /**
     * @summary Attach multiple event listeners to the class.
     * @memberof FooGallery.utils.EventClass#
     * @function on
     * @param {Object.<string, function>} events - An object containing event types to listener mappings.
     * @param {*} [thisArg] - The value of `this` within the listeners. Defaults to the class raising the event.
     * @returns {this}
     */

    /**
    * @summary Attach an event listener for one or more events to the class.
    * @memberof FooGallery.utils.EventClass#
    * @function on
    * @param {string} events - One or more space-separated event types.
    * @param {function} listener - A function to execute when the event is triggered.
    * @param {*} [thisArg] - The value of `this` within the `listener`. Defaults to the class raising the event.
    * @returns {this}
    */
    on: function on(events, listener, thisArg) {
      var self = this;

      if (_is.object(events)) {
        thisArg = listener;
        Object.keys(events).forEach(function (key) {
          if (_is.fn(events[key])) {
            key.split(" ").forEach(function (type) {
              self.addListener(type, events[key], thisArg);
            });
          }
        });
      } else if (_is.string(events) && _is.fn(listener)) {
        events.split(" ").forEach(function (type) {
          self.addListener(type, listener, thisArg);
        });
      }

      return self;
    },

    /**
     * @summary Adds a single event listener to the current class.
     * @memberof FooGallery.utils.EventClass#
     * @function addListener
     * @param {string} event - The event type, this can not contain any whitespace.
     * @param {function} listener - A function to execute when the event is triggered.
     * @param {*} [thisArg] - The value of `this` within the `listener`. Defaults to the class raising the event.
     * @returns {boolean} Returns `true` if added.
     */
    addListener: function addListener(event, listener, thisArg) {
      if (!_is.string(event) || /\s/.test(event) || !_is.fn(listener)) return false;

      var self = this,
          parsed = _.Event.parse(event);

      thisArg = _is.undef(thisArg) ? self : thisArg;

      if (!_is.array(self.events[parsed.type])) {
        self.events[parsed.type] = [];
      }

      var exists = self.events[parsed.type].some(function (h) {
        return h.namespace === parsed.namespace && h.fn === listener && h.thisArg === thisArg;
      });

      if (!exists) {
        self.events[parsed.type].push({
          namespace: parsed.namespace,
          fn: listener,
          thisArg: thisArg
        });
        return true;
      }

      return false;
    },

    /**
     * @summary Remove multiple event listeners from the class.
     * @memberof FooGallery.utils.EventClass#
     * @function off
     * @param {Object.<string, function>} events - An object containing event types to listener mappings.
     * @param {*} [thisArg] - The value of `this` within the `listener` function. Defaults to the class raising the event.
     * @returns {this}
     */

    /**
    * @summary Remove an event listener from the class.
    * @memberof FooGallery.utils.EventClass#
    * @function off
    * @param {string} events - One or more space-separated event types.
    * @param {function} listener - A function to execute when the event is triggered.
    * @param {*} [thisArg] - The value of `this` within the `listener`. Defaults to the class raising the event.
    * @returns {this}
    */
    off: function off(events, listener, thisArg) {
      var self = this;

      if (_is.object(events)) {
        thisArg = listener;
        Object.keys(events).forEach(function (key) {
          key.split(" ").forEach(function (type) {
            self.removeListener(type, events[key], thisArg);
          });
        });
      } else if (_is.string(events)) {
        events.split(" ").forEach(function (type) {
          self.removeListener(type, listener, thisArg);
        });
      }

      return self;
    },

    /**
     * @summary Removes a single event listener from the current class.
     * @memberof FooGallery.utils.EventClass#
     * @function removeListener
     * @param {string} event - The event type, this can not contain any whitespace.
     * @param {function} [listener] - The listener registered to the event type.
     * @param {*} [thisArg] - The value of `this` registered for the `listener`. Defaults to the class raising the event.
     * @returns {boolean} Returns `true` if removed.
     */
    removeListener: function removeListener(event, listener, thisArg) {
      if (!_is.string(event) || /\s/.test(event)) return false;

      var self = this,
          parsed = _.Event.parse(event),
          types = [];

      thisArg = _is.undef(thisArg) ? self : thisArg;

      if (!_is.empty(parsed.type)) {
        types.push(parsed.type);
      } else if (!_is.empty(parsed.namespace)) {
        types.push.apply(types, Object.keys(self.events));
      }

      types.forEach(function (type) {
        if (!_is.array(self.events[type])) return;
        self.events[type] = self.events[type].filter(function (h) {
          if (listener != null) {
            return !(h.namespace === parsed.namespace && h.fn === listener && h.thisArg === thisArg);
          }

          if (parsed.namespace != null) {
            return h.namespace !== parsed.namespace;
          }

          return false;
        });

        if (self.events[type].length === 0) {
          delete self.events[type];
        }
      });
      return true;
    },

    /**
     * @summary Trigger an event on the current class.
     * @memberof FooGallery.utils.EventClass#
     * @function trigger
     * @param {(string|FooGallery.utils.Event)} event - Either a space-separated string of event types or a custom event object to raise.
     * @param {Array} [args] - An array of additional arguments to supply to the listeners after the event object.
     * @returns {(FooGallery.utils.Event|FooGallery.utils.Event[]|null)} Returns the {@link FooGallery.utils.Event|event object} of the triggered event. If more than one event was triggered an array of {@link FooGallery.utils.Event|event objects} is returned. If no `event` was supplied or triggered `null` is returned.
     */
    trigger: function trigger(event, args) {
      args = _is.array(args) ? args : [];
      var self = this,
          result = [];

      if (event instanceof _.Event) {
        result.push(event);
        self.emit(event, args);
      } else if (_is.string(event)) {
        event.split(" ").forEach(function (type) {
          var e = new _.Event(type);
          result.push(e);
          self.emit(e, args);
        });
      }

      return _is.empty(result) ? null : result.length === 1 ? result[0] : result;
    },

    /**
     * @summary Emits the supplied event on the current class.
     * @memberof FooGallery.utils.EventClass#
     * @function emit
     * @param {FooGallery.utils.Event} event - The event object to emit.
     * @param {Array} [args] - An array of additional arguments to supply to the listener after the event object.
     */
    emit: function emit(event, args) {
      if (!(event instanceof FooGallery.utils.Event)) return;
      var self = this;
      args = _is.array(args) ? args : [];
      if (event.target === null) event.target = self;

      if (_is.array(self.events[event.type])) {
        self.events[event.type].forEach(function (h) {
          if (event.namespace != null && h.namespace !== event.namespace) return;
          h.fn.apply(h.thisArg, [event].concat(args));
        });
      }

      if (_is.array(self.events["__all__"])) {
        self.events["__all__"].forEach(function (h) {
          h.fn.apply(h.thisArg, [event].concat(args));
        });
      }
    }
  });
})( // dependencies
FooGallery.utils, FooGallery.utils.is, FooGallery.utils.str);

(function ($, _, _is, _fn, _obj) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return;
  /**
   * @summary A simple timer that triggers events.
   * @memberof FooGallery.utils.
   * @class Timer
   * @param {number} [interval=1000] - The internal tick interval of the timer.
   * @augments FooGallery.utils.EventClass
   */

  _.Timer = _.EventClass.extend(
  /** @lends FooGallery.utils.Timer.prototype */
  {
    /**
     * @ignore
     * @constructs
     * @param {number} [interval=1000]
     */
    construct: function construct(interval) {
      var self = this;

      self._super();
      /**
       * @summary The internal tick interval of the timer in milliseconds.
       * @memberof FooGallery.utils.Timer#
       * @name interval
       * @type {number}
       * @default 1000
       * @readonly
       */


      self.interval = _is.number(interval) ? interval : 1000;
      /**
       * @summary Whether the timer is currently running or not.
       * @memberof FooGallery.utils.Timer#
       * @name isRunning
       * @type {boolean}
       * @default false
       * @readonly
       */

      self.isRunning = false;
      /**
       * @summary Whether the timer is currently paused or not.
       * @memberof FooGallery.utils.Timer#
       * @name isPaused
       * @type {boolean}
       * @default false
       * @readonly
       */

      self.isPaused = false;
      /**
       * @summary Whether the timer can resume from a previous state or not.
       * @memberof FooGallery.utils.Timer#
       * @name canResume
       * @type {boolean}
       * @default false
       * @readonly
       */

      self.canResume = false;
      /**
       * @summary Whether the timer can restart or not.
       * @memberof FooGallery.utils.Timer#
       * @name canRestart
       * @type {boolean}
       * @default false
       * @readonly
       */

      self.canRestart = false;
      /**
       * @summary The internal tick timeout ID.
       * @memberof FooGallery.utils.Timer#
       * @name __timeout
       * @type {?number}
       * @default null
       * @private
       */

      self.__timeout = null;
      /**
       * @summary Whether the timer is incrementing or decrementing.
       * @memberof FooGallery.utils.Timer#
       * @name __decrement
       * @type {boolean}
       * @default false
       * @private
       */

      self.__decrement = false;
      /**
       * @summary The total time for the timer.
       * @memberof FooGallery.utils.Timer#
       * @name __time
       * @type {number}
       * @default 0
       * @private
       */

      self.__time = 0;
      /**
       * @summary The remaining time for the timer.
       * @memberof FooGallery.utils.Timer#
       * @name __remaining
       * @type {number}
       * @default 0
       * @private
       */

      self.__remaining = 0;
      /**
       * @summary The current time for the timer.
       * @memberof FooGallery.utils.Timer#
       * @name __current
       * @type {number}
       * @default 0
       * @private
       */

      self.__current = 0;
      /**
       * @summary The final time for the timer.
       * @memberof FooGallery.utils.Timer#
       * @name __finish
       * @type {number}
       * @default 0
       * @private
       */

      self.__finish = 0;
      /**
       * @summary The last arguments supplied to the {@link FooGallery.utils.Timer#start|start} method.
       * @memberof FooGallery.utils.Timer#
       * @name __restart
       * @type {Array}
       * @default []
       * @private
       */

      self.__restart = [];
    },

    /**
     * @summary Resets the timer back to a fresh starting state.
     * @memberof FooGallery.utils.Timer#
     * @function __reset
     * @private
     */
    __reset: function __reset() {
      var self = this;
      clearTimeout(self.__timeout);
      self.__timeout = null;
      self.__decrement = false;
      self.__time = 0;
      self.__remaining = 0;
      self.__current = 0;
      self.__finish = 0;
      self.isRunning = false;
      self.isPaused = false;
      self.canResume = false;
    },

    /**
     * @summary Generates event args to be passed to listeners of the timer events.
     * @memberof FooGallery.utils.Timer#
     * @function __eventArgs
     * @param {...*} [args] - Any number of additional arguments to pass to an event listener.
     * @return {Array} - The first 3 values of the result will always be the current time, the total time and boolean indicating if the timer is decremental.
     * @private
     */
    __eventArgs: function __eventArgs(args) {
      var self = this;
      return [self.__current, self.__time, self.__decrement].concat(_fn.arg2arr(arguments));
    },

    /**
     * @summary Performs the tick for the timer checking and modifying the various internal states.
     * @memberof FooGallery.utils.Timer#
     * @function __tick
     * @private
     */
    __tick: function __tick() {
      var self = this;
      self.trigger("tick", self.__eventArgs());

      if (self.__current === self.__finish) {
        self.trigger("complete", self.__eventArgs());

        self.__reset();
      } else {
        if (self.__decrement) {
          self.__current--;
        } else {
          self.__current++;
        }

        self.__remaining--;
        self.canResume = self.__remaining > 0;
        self.__timeout = setTimeout(function () {
          self.__tick();
        }, self.interval);
      }
    },

    /**
     * @summary Starts the timer using the supplied `time` and whether or not to increment or decrement from the value.
     * @memberof FooGallery.utils.Timer#
     * @function start
     * @param {number} time - The total time in seconds for the timer.
     * @param {boolean} [decrement=false] - Whether the timer should increment or decrement from or to the supplied time.
     */
    start: function start(time, decrement) {
      var self = this;
      if (self.isRunning) return;
      decrement = _is.boolean(decrement) ? decrement : false;
      self.__restart = [time, decrement];
      self.__decrement = decrement;
      self.__time = time;
      self.__remaining = time;
      self.__current = decrement ? time : 0;
      self.__finish = decrement ? 0 : time;
      self.canRestart = true;
      self.isRunning = true;
      self.isPaused = false;
      self.trigger("start", self.__eventArgs());

      self.__tick();
    },

    /**
     * @summary Starts the timer counting down to `0` from the supplied `time`.
     * @memberof FooGallery.utils.Timer#
     * @function countdown
     * @param {number} time - The total time in seconds for the timer.
     */
    countdown: function countdown(time) {
      this.start(time, true);
    },

    /**
     * @summary Starts the timer counting up from `0` to the supplied `time`.
     * @memberof FooGallery.utils.Timer#
     * @function countup
     * @param {number} time - The total time in seconds for the timer.
     */
    countup: function countup(time) {
      this.start(time, false);
    },

    /**
     * @summary Stops and then restarts the timer using the last arguments supplied to the {@link FooGallery.utils.Timer#start|start} method.
     * @memberof FooGallery.utils.Timer#
     * @function restart
     */
    restart: function restart() {
      var self = this;
      self.stop();

      if (self.canRestart) {
        self.start.apply(self, self.__restart);
      }
    },

    /**
     * @summary Stops the timer.
     * @memberof FooGallery.utils.Timer#
     * @function stop
     */
    stop: function stop() {
      var self = this;

      if (self.isRunning || self.isPaused) {
        self.__reset();

        self.trigger("stop", self.__eventArgs());
      }
    },

    /**
     * @summary Pauses the timer and returns the remaining seconds.
     * @memberof FooGallery.utils.Timer#
     * @function pause
     * @return {number} - The number of seconds remaining for the timer.
     */
    pause: function pause() {
      var self = this;

      if (self.__timeout != null) {
        clearTimeout(self.__timeout);
        self.__timeout = null;
      }

      if (self.isRunning) {
        self.isRunning = false;
        self.isPaused = true;
        self.canResume = self.__remaining > 0;
        self.trigger("pause", self.__eventArgs());
      }

      return self.__remaining;
    },

    /**
     * @summary Resumes the timer from a previously paused state.
     * @memberof FooGallery.utils.Timer#
     * @function resume
     */
    resume: function resume() {
      var self = this;

      if (self.canResume) {
        self.isRunning = true;
        self.isPaused = false;
        self.trigger("resume", self.__eventArgs());

        self.__tick();
      }
    },

    /**
     * @summary Resets the timer back to a fresh starting state.
     * @memberof FooGallery.utils.Timer#
     * @function reset
     */
    reset: function reset() {
      var self = this;

      self.__reset();

      self.trigger("reset", this.__eventArgs());
    }
  });
})(FooGallery.utils.$, FooGallery.utils, FooGallery.utils.is, FooGallery.utils.fn, FooGallery.utils.obj);

(function ($, _, _fn) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return; // noinspection JSUnusedGlobalSymbols

  /**
   * @summary A wrapper around the fullscreen API to ensure cross browser compatibility.
   * @memberof FooGallery.utils.
   * @class FullscreenAPI
   * @augments FooGallery.utils.EventClass
   * @borrows FooGallery.utils.EventClass.extend as extend
   * @borrows FooGallery.utils.EventClass.override as override
   */

  _.FullscreenAPI = _.EventClass.extend(
  /** @lends FooGallery.utils.FullscreenAPI */
  {
    /**
     * @ignore
     * @constructs
     */
    construct: function construct() {
      this._super();
      /**
       * @summary An object containing a single browsers various methods and events needed for this wrapper.
       * @typedef {?Object} FooGallery.utils.FullscreenAPI~BrowserAPI
       * @property {string} enabled
       * @property {string} element
       * @property {string} request
       * @property {string} exit
       * @property {Object} events
       * @property {string} events.change
       * @property {string} events.error
       */

      /**
       * @summary An object containing the supported fullscreen browser API's.
       * @typedef {Object.<string, FooGallery.utils.FullscreenAPI~BrowserAPI>} FooGallery.utils.FullscreenAPI~SupportedBrowsers
       */

      /**
       * @summary Contains the various browser specific method and event names.
       * @memberof FooGallery.utils.FullscreenAPI#
       * @name apis
       * @type {FooGallery.utils.FullscreenAPI~SupportedBrowsers}
       */


      this.apis = {
        w3: {
          enabled: "fullscreenEnabled",
          element: "fullscreenElement",
          request: "requestFullscreen",
          exit: "exitFullscreen",
          events: {
            change: "fullscreenchange",
            error: "fullscreenerror"
          }
        },
        webkit: {
          enabled: "webkitFullscreenEnabled",
          element: "webkitCurrentFullScreenElement",
          request: "webkitRequestFullscreen",
          exit: "webkitExitFullscreen",
          events: {
            change: "webkitfullscreenchange",
            error: "webkitfullscreenerror"
          }
        },
        moz: {
          enabled: "mozFullScreenEnabled",
          element: "mozFullScreenElement",
          request: "mozRequestFullScreen",
          exit: "mozCancelFullScreen",
          events: {
            change: "mozfullscreenchange",
            error: "mozfullscreenerror"
          }
        },
        ms: {
          enabled: "msFullscreenEnabled",
          element: "msFullscreenElement",
          request: "msRequestFullscreen",
          exit: "msExitFullscreen",
          events: {
            change: "MSFullscreenChange",
            error: "MSFullscreenError"
          }
        }
      };
      /**
       * @summary The current browsers specific method and event names.
       * @memberof FooGallery.utils.FullscreenAPI#
       * @name api
       * @type {FooGallery.utils.FullscreenAPI~BrowserAPI}
       */

      this.api = this.getAPI();
      /**
       * @summary Whether or not the fullscreen API is supported in the current browser.
       * @memberof FooGallery.utils.FullscreenAPI#
       * @name supported
       * @type {boolean}
       */

      this.supported = this.api != null;

      this.__listen();
    },

    /**
     * @summary Destroys the current wrapper unbinding events and freeing up resources.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function destroy
     * @returns {boolean}
     */
    destroy: function destroy() {
      this.__stopListening();

      return this._super();
    },

    /**
     * @summary Fetches the correct API for the current browser.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function getAPI
     * @return {?FooGallery.utils.FullscreenAPI~BrowserAPI} Returns `null` if the fullscreen API is not supported.
     */
    getAPI: function getAPI() {
      for (var vendor in this.apis) {
        if (!this.apis.hasOwnProperty(vendor)) continue; // Check if document has the "enabled" property

        if (this.apis[vendor].enabled in document) {
          // It seems this browser supports the fullscreen API
          return this.apis[vendor];
        }
      }

      return null;
    },

    /**
     * @summary Gets the current fullscreen element or null.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function element
     * @returns {?Element}
     */
    element: function element() {
      return this.supported ? document[this.api.element] : null;
    },

    /**
     * @summary Requests the browser to place the specified element into fullscreen mode.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function request
     * @param {Element} element - The element to place into fullscreen mode.
     * @returns {Promise} A Promise which is resolved once the element is placed into fullscreen mode.
     */
    request: function request(element) {
      if (this.supported && !!element[this.api.request]) {
        var result = element[this.api.request]();
        return !result ? $.Deferred(this.__resolver(this.api.request)).promise() : result;
      }

      return _fn.rejected;
    },

    /**
     * @summary Requests that the browser switch from fullscreen mode back to windowed mode.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function exit
     * @returns {Promise} A Promise which is resolved once fullscreen mode is exited.
     */
    exit: function exit() {
      if (this.supported && !!this.element()) {
        var result = document[this.api.exit]();
        return !result ? $.Deferred(this.__resolver(this.api.exit)).promise() : result;
      }

      return _fn.rejected;
    },

    /**
     * @summary Toggles the supplied element between fullscreen and windowed modes.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function toggle
     * @param {Element} element - The element to switch between modes.
     * @returns {Promise} A Promise that is resolved once fullscreen mode is either entered or exited.
     */
    toggle: function toggle(element) {
      return !!this.element() ? this.exit() : this.request(element);
    },

    /**
     * @summary Starts listening to the document level fullscreen events and triggers an abbreviated version on this class.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function __listen
     * @private
     */
    __listen: function __listen() {
      var self = this;
      if (!self.supported) return;
      $(document).on(self.api.events.change + ".utils", function () {
        self.trigger("change");
      }).on(self.api.events.error + ".utils", function () {
        self.trigger("error");
      });
    },

    /**
     * @summary Stops listening to the document level fullscreen events.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function __stopListening
     * @private
     */
    __stopListening: function __stopListening() {
      var self = this;
      if (!self.supported) return;
      $(document).off(self.api.events.change + ".utils").off(self.api.events.error + ".utils");
    },

    /**
     * @summary Creates a resolver function to patch browsers which do not return a Promise from there request and exit methods.
     * @memberof FooGallery.utils.FullscreenAPI#
     * @function __resolver
     * @param {string} method - The request or exit method the resolver is being created for.
     * @returns {FooGallery.utils.FullscreenAPI~resolver}
     * @private
     */
    __resolver: function __resolver(method) {
      var self = this;
      /**
       * @summary Binds to the fullscreen change and error events and resolves or rejects the supplied deferred accordingly.
       * @callback FooGallery.utils.FullscreenAPI~resolver
       * @param {jQuery.Deferred} def - The jQuery.Deferred object to resolve.
       */

      return function resolver(def) {
        // Reject the promise if asked to exitFullscreen and there is no element currently in fullscreen
        if (method === self.api.exit && !!self.element()) {
          setTimeout(function () {
            // noinspection JSUnresolvedFunction
            def.reject(new TypeError());
          }, 1);
          return;
        } // When receiving an internal fullscreenchange event, fulfill the promise


        function change() {
          // noinspection JSUnresolvedFunction
          def.resolve();
          $(document).off(self.api.events.change, change).off(self.api.events.error, error);
        } // When receiving an internal fullscreenerror event, reject the promise


        function error() {
          // noinspection JSUnresolvedFunction
          def.reject(new TypeError());
          $(document).off(self.api.events.change, change).off(self.api.events.error, error);
        }

        $(document).on(self.api.events.change, change).on(self.api.events.error, error);
      };
    }
  });
})(FooGallery.utils.$, FooGallery.utils, FooGallery.utils.fn);

(function ($, _, _is, _fn) {
  // only register methods if this version is the current version
  if (_.version !== '1.0.0') return;
  /**
   * @summary Contains common utility methods and members for the CSS transition property.
   * @memberof FooGallery.utils.
   * @namespace transition
   */

  _.transition = {};
  /**
   * @summary The data name used by transitions to ensure promises are resolved.
   * @memberof FooGallery.utils.transition.
   * @name dataName
   * @type {string}
   * @default "__foo-transition__"
   */

  _.transition.dataName = '__foo-transition__';
  /**
   * @summary The CSS className used to disable transitions when using the {@link FooGallery.utils.transition.disable|disable} method instead of inline styles.
   * @memberof FooGallery.utils.transition.
   * @name disableClassName
   * @type {?string}
   * @default null
   */

  _.transition.disableClassName = null;
  /**
   * @summary The global timeout used as a safety measure when using the {@link FooGallery.utils.transition.start|start} method. This can be overridden using the `timeout` parameter of the {@link FooGallery.utils.transition.start|start} method.
   * @memberof FooGallery.utils.transition.
   * @name timeout
   * @type {number}
   * @default 3000
   */

  _.transition.timeout = 3000;
  /**
   * @summary Disable transitions temporarily on the provided element so changes can be made immediately within the callback.
   * @memberof FooGallery.utils.transition.
   * @function disable
   * @param {(jQuery|HTMLElement)} element - The element to disable transitions on.
   * @param {FooGallery.utils.transition~modifyFn} modifyFn - A function to execute while the elements transitions are disabled.
   */

  _.transition.disable = function (element, modifyFn) {
    var $el = _is.jq(element) ? element : $(element);

    if ($el.length > 0 && _is.fn(modifyFn)) {
      var el = $el.get(0),
          hasClass = _is.string(_.transition.disableClassName);

      var restore = null;
      if (hasClass) $el.addClass(_.transition.disableClassName);else {
        restore = {
          value: el.style.getPropertyValue('transition'),
          priority: el.style.getPropertyPriority('transition')
        };
        el.style.setProperty('transition', 'none', 'important');
      }
      modifyFn.call(modifyFn, $el);
      $el.prop("offsetWidth");
      if (hasClass) $el.removeClass(_.transition.disableClassName);else {
        el.style.removeProperty('transition');

        if (_is.string(restore.value) && restore.value.length > 0) {
          el.style.setProperty('transition', restore.value, restore.priority);
        }
      }
    }
  };
  /**
   * @summary Stop a transition started using the {@link FooGallery.utils.transition.start|start} method.
   * @memberof FooGallery.utils.transition.
   * @function stop
   * @param {(jQuery|HTMLElement)} element - The element to stop the transition on.
   * @returns {Promise}
   */


  _.transition.stop = function (element) {
    var d = $.Deferred(),
        $el = _is.jq(element) ? element : $(element);

    if ($el.length > 0) {
      var current = $el.data(_.transition.dataName);

      if (_is.promise(current)) {
        current.always(function () {
          // request the next frame to give the previous event unbinds time to settle
          _.requestFrame(function () {
            d.resolve($el);
          });
        }).reject(new Error("Transition cancelled."));
      } else {
        d.resolve($el);
      }
    } else {
      d.reject(new Error("Unable to stop transition. Make sure the element exists."));
    }

    return d.promise();
  };
  /**
   * @summary Creates a new transition event listener ensuring the element and optionally the propertyName matches before executing the callback.
   * @memberof FooGallery.utils.transition.
   * @function createListener
   * @param {HTMLElement} element - The element being listened to.
   * @param {function(*): void} callback - The callback to execute once the element and optional propertyName are matched.
   * @param {?string} [propertyName=null] - The propertyName to match on the TransitionEvent object.
   * @returns {function(*): void}
   */


  _.transition.createListener = function (element, callback, propertyName) {
    var el = element,
        fn = callback,
        prop = propertyName,
        hasProp = _is.string(propertyName);

    return function (event) {
      var evt = event.originalEvent instanceof TransitionEvent ? event.originalEvent : event;
      var matches = false;

      if (evt.target === el) {
        matches = hasProp ? evt.propertyName === prop : true;
      }

      if (matches) fn.apply(fn, _fn.arg2arr(arguments));
    };
  };
  /**
   * @summary Start a transition on an element returning a promise that is resolved once the transition ends.
   * @memberof FooGallery.utils.transition.
   * @function start
   * @param {(jQuery|HTMLElement)} element - The element to perform the transition on.
   * @param {FooGallery.utils.transition~modifyFn} triggerFn - The callback that triggers the transition on the element.
   * @param {?string} [propertyName] - A specific property name to wait for before resolving. If not supplied the first instance of the transitionend event will resolve the promise.
   * @param {number} [timeout] - A safety timeout to ensure the returned promise is finalized. If not supplied the value of the {@link FooGallery.utils.transition.timeout} property is used.
   * @returns {Promise}
   */


  _.transition.start = function (element, triggerFn, propertyName, timeout) {
    var d = $.Deferred(),
        $el = _is.jq(element) ? element : $(element);

    if ($el.length > 0 && _is.fn(triggerFn)) {
      var el = $el.get(0); // first stop any active transitions

      _.transition.stop($el).always(function () {
        // then setup the data object and event listeners for the new transition
        var listener = _.transition.createListener(el, function () {
          d.resolve($el);
        }, propertyName);

        $el.data(_.transition.dataName, d).on("transitionend.foo-utils", listener).prop("offsetWidth"); // force layout to ensure transitions on newly appended elements occur
        // request the next frame to give the event bindings time to settle

        _.requestFrame(function () {
          // just in case a transition is cancelled by some other means and the transitionend event is never fired this
          // timeout ensures the returned promise is always finalized.
          var safety = setTimeout(function () {
            d.reject(new Error("Transition safety timeout triggered."));
          }, _is.number(timeout) ? timeout : _.transition.timeout); // we always want to cleanup after ourselves so clear the safety, remove the data object and unbind the events

          d.always(function () {
            clearTimeout(safety);
            $el.removeData(_.transition.dataName).off("transitionend.foo-utils", listener);
          }); // now that everything is setup kick off the transition by calling the triggerFn

          triggerFn.call(triggerFn, $el);
        });
      });
    } else {
      d.reject(new Error("Unable to perform transition. Make sure the element exists and a trigger function is supplied."));
    }

    return d.promise();
  };
  /**
   * @summary Used to modify an element which has transitions optionally allowing the transition to occur or not.
   * @memberof FooGallery.utils.transition.
   * @function modify
   * @param {(jQuery|HTMLElement)} element - The element to perform the modifications to.
   * @param {FooGallery.utils.transition~modifyFn} modifyFn - The callback used to perform the modifications.
   * @param {boolean} [immediate=false] - Whether or not transitions should be allowed to execute and waited on. The default value of `false` means transitions are allowed and the promise will only resolve once there transitionend event has fired.
   * @param {?string} [propertyName=null] - A specific property name to wait for before resolving. If not supplied the first instance of the transitionend event will resolve the promise.
   * @returns {Promise} Returns a promise that is resolved once the modifications to the element have ended.
   */


  _.transition.modify = function (element, modifyFn, immediate, propertyName) {
    var $el = _is.jq(element) ? element : $(element);

    if ($el.length > 0 && _is.fn(modifyFn)) {
      if (immediate) {
        _.transition.disable($el, modifyFn);

        return _fn.resolve();
      }

      return _.transition.start($el, modifyFn, propertyName);
    }

    return _fn.reject(new Error("Unable to perform modification. Make sure the element exists and a modify function is supplied."));
  };
  /**
   * @summary Perform one or more modifications to the element such as setting inline styles or toggling classNames.
   * @callback FooGallery.utils.transition~modifyFn
   * @param {jQuery} $element - The jQuery object for the element to modify.
   */

})( // dependencies
FooGallery.utils.$, FooGallery.utils, FooGallery.utils.is, FooGallery.utils.fn);

(function ($, _, _utils, _is, _fn, _str) {

	/**
	 * @summary The name to use when getting or setting an instance of a {@link FooGallery.Template|template} on an element using jQuery's `.data()` method.
	 * @memberof FooGallery
	 * @name DATA_TEMPLATE
	 * @type {string}
	 * @default "__FooGallery__"
	 */
	_.DATA_TEMPLATE = "__FooGallery__";

	/**
	 * @summary The name to use when getting or setting an instance of a {@link FooGallery.Item|item} on an element using jQuery's `.data()` method.
	 * @memberof FooGallery
	 * @name DATA_ITEM
	 * @type {string}
	 * @default "__FooGalleryItem__"
	 */
	_.DATA_ITEM = "__FooGalleryItem__";

	_.get = function(selector){
		return $(selector).data(_.DATA_TEMPLATE);
	};

	_.init = function (options, element) {
		element = _is.jq(element) ? element : $(element);
		if (element.length > 0){
			var current = element.data(_.DATA_TEMPLATE);
			if (current instanceof _.Template) {
				return current.destroy(true).then(function(){
					var tmpl = _.template.make(options, element);
					return tmpl instanceof _.Template ? tmpl.initialize() : _fn.rejected;
				});
			}
		}
		var tmpl = _.template.make(options, element);
		return tmpl instanceof _.Template ? tmpl.initialize() : _fn.rejected;
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
	 *          title="[item.title]" alt="[item.description]"
	 *          data-src="[item.src]"
	 *          data-srcset="[item.srcset]" />
	 *         <!-- Optional caption markup -->
	 *         <div class="fg-caption">
	 *          <div class="fg-caption-inner">
	 *           <div class="fg-caption-title">[item.title]</div>
	 *           <div class="fg-caption-desc">[item.description]</div>
	 *          </div>
	 *         </div>
	 *       </a>
	 *     </div>
	 *   </div>
	 *   <!-- Any number of additional items -->
	 * </div>
	 * <script>
	 *  jQuery(function($){
	 * 		$("#gallery-1").foogallery();
	 * 	});
	 * </script>
	 * @example {@caption Options can be supplied directly to the `.foogallery()` method or by supplying them using the `data-foogallery` attribute. If supplied using the attribute the value must follow [valid JSON syntax](http://en.wikipedia.org/wiki/JSON#Data_types.2C_syntax_and_example) including quoted property names. If the same option is supplied in both locations as it is below, the value from the attribute overrides the value supplied to the method, in this case `lazy` would be `true`.}{@lang html}
	 * <!-- Supplying the options using the attribute -->
	 * <div id="gallery-1" class="foogallery fg-responsive" data-foogallery='{"lazy": true}'>
	 *  <!-- Items -->
	 * </div>
	 * <script>
	 *  jQuery(function($){
	 * 		// Supply the options directly to the method
	 * 		$("#gallery-1").foogallery({
	 * 			lazy: false
	 * 		});
	 * 	});
	 * </script>
	 */
	$.fn.foogallery = function (options, ready) {
		ready = _is.fn(ready) ? ready : $.noop;
		return this.each(function (i, element) {
			if (_is.string(options)) {
				var template = $.data(element, _.DATA_TEMPLATE);
				if (template instanceof _.Template) {
					switch (options) {
						case "layout":
							template.layout();
							return;
						case "destroy":
							template.destroy();
							return;
					}
				}
			} else {
				_.init( options, element ).then( ready );
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

	/**
	 * @summary Checks if the supplied image src is cached by the browser.
	 * @param {string} src - The image src to check.
	 * @returns {boolean}
	 */
	_.isCached = function(src){
		var img = new Image();
		img.src = src;
		var complete = img.complete;
		img.src = "";
		img = null;
		return complete;
	};

	/**
	 * @summary A string array of supported EXIF properties.
	 * @memberof FooGallery
	 * @name supportedExifProperties
	 * @type {string[]}
	 */
	_.supportedExifProperties = ["camera","aperture","created_timestamp","shutter_speed","focal_length","iso","orientation"];

	/**
	 * @memberof FooGallery.utils.is
	 * @function exif
	 * @param {*} value - The value to check.
	 * @returns {boolean} `true` if the `value` contains any supported and valid EXIF properties.
	 */
	_is.exif = function(value){
		if (_is.object(value)){
			var keys = Object.keys(value);
			return keys.length > 0 && keys.some(function(key){
				return _.supportedExifProperties.indexOf(key) !== -1 && !_is.empty(value[key]);
			});
		}
		return false;
	};

	/**
	 * @summary Trims the value if it exceeds the specified length and appends the suffix.
	 * @memberof FooGallery.utils.str.
	 * @function trimTo
	 * @param {string} value - The value to trim if required.
	 * @param {number} length - The length to trim the string to.
	 * @param {string} [suffix="&hellip;"] - The suffix to append to a trimmed value.
	 * @returns {string|null}
	 */
	_str.trimTo = function(value, length, suffix){
		if (_is.string(value) && _is.number(length) && length > 0 && value.length > length) {
			return value.substr(0, length) + (_is.string(suffix) ? suffix : "&hellip;");
		}
		return value;
	};

	/**
	 * @typedef {Object} ResizeObserverSize
	 * @property {number} inlineSize
	 * @property {number} blockSize
	 * @property {number} width
	 * @property {number} height
	 */
	/**
	 * @typedef {Object} ResizeObserverEntry
	 * @property {ResizeObserverSize|Array<ResizeObserverSize>|undefined} contentBoxSize
	 * @property {DOMRect} contentRect
	 */
	/**
	 * @summary Gets the width and height from the ResizeObserverEntry
	 * @memberof FooGallery.utils.
	 * @function getResizeObserverSize
	 * @param {ResizeObserverEntry} entry - The entry to retrieve the size from.
	 * @returns {{width: Number,height: Number}}
	 */
	_utils.getResizeObserverSize = function(entry){
		var width, height;
		if(entry.contentBoxSize) {
			// Checking for chrome as using a non-standard array
			if (entry.contentBoxSize[0]) {
				width = entry.contentBoxSize[0].inlineSize;
				height = entry.contentBoxSize[0].blockSize;
			} else {
				width = entry.contentBoxSize.inlineSize;
				height = entry.contentBoxSize.blockSize;
			}
		} else {
			width = entry.contentRect.width;
			height = entry.contentRect.height;
		}
		return {
			width: width,
			height: height
		};
	};

	/**
	 * @summary Whether or not the current browser supports "webp" images.
	 * @memberof FooGallery.
	 * @name supportsWebP
	 * @type {boolean}
	 * @default false
	 */
	_.supportsWebP = false;

	var webp = new Image();
	webp.onload = function(){
		_.supportsWebP = 0 < webp.width && 0 < webp.height;
	};
	webp.onerror=function(){
		_.supportsWebP = false;
	};
	webp.src = 'data:image/webp;base64,UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==';

	/**
	 * @summary Simple test to see if the browser supports the <picture> element.
	 * @memberof FooGallery.
	 * @name supportsPicture
	 * @type {boolean}
	 */
	_.supportsPicture = !!window.HTMLPictureElement;

	/**
	 * Utility class to make working with anonymous event listeners a bit simpler.
	 * @memberof FooGallery.utils.
	 * @class DOMEventListeners
	 * @augments FooGallery.utils.Class
	 * @borrows FooGallery.utils.Class.extend as extend
	 * @borrows FooGallery.utils.Class.override as override
	 */
	_utils.DOMEventListeners = _utils.Class.extend( /** @lends FooGallery.utils.DOMEventListeners.prototype */ {
		/**
		 * @ignore
		 * @constructs
		 **/
		construct: function(){
			/**
			 * A simple object containing the event listener and options.
			 * @typedef {Object} EventEntry
			 * @property {EventListener} listener
			 * @property {EventListenerOptions|boolean} [options]
			 */
			/**
			 * The map object containing all listeners.
			 * @type {Map<EventTarget, Map<string, EventEntry>>}
			 */
			this.eventTargets = new Map();
		},
		/**
		 * Add an event listener to the eventTarget.
		 * @param {EventTarget} eventTarget
		 * @param {string} type
		 * @param {EventListener} listener
		 * @param {AddEventListenerOptions|boolean} [options]
		 * @returns {boolean} False if a listener already exists for the element.
		 */
		add: function( eventTarget, type, listener, options ){
			eventTarget.addEventListener( type, listener, options );
			let listeners = this.eventTargets.get( eventTarget );
			if ( !listeners ){
				listeners = new Map();
				this.eventTargets.set( eventTarget, listeners );
			}
			let entry = listeners.get( type );
			if ( !entry ){
				listeners.set( type, { listener: listener, options: options } );
				return true;
			}
			return false;
		},
		/**
		 * Remove an event listener from the eventTarget.
		 * @param {EventTarget} eventTarget
		 * @param {string} type
		 */
		remove: function( eventTarget, type ){
			let listeners = this.eventTargets.get( eventTarget );
			if ( !listeners ) return;
			let entry = listeners.get( type );
			if ( !entry ) return;
			eventTarget.removeEventListener( type, entry.listener, entry.options );
			listeners.delete( type );
			if ( listeners.size === 0 ) this.eventTargets.delete( eventTarget );
		},
		/**
		 * Removes all event listeners from all eventTargets.
		 */
		clear: function(){
			this.eventTargets.forEach( function( listeners, eventTarget ){
				listeners.forEach( function( entry, type ){
					eventTarget.removeEventListener( type, entry.listener, entry.options );
				} );
			} );
			this.eventTargets.clear();
		}
	} );

	/**
	 * Utility class to help with managing timeouts.
	 * @memberof FooGallery.utils.
	 * @class Timeouts
	 * @augments FooGallery.utils.Class
	 * @borrows FooGallery.utils.Class.extend as extend
	 * @borrows FooGallery.utils.Class.override as override
	 */
	_utils.Timeouts = _utils.Class.extend( /** @lends FooGallery.utils.Timeouts.prototype */ {
		/**
		 * @ignore
		 * @constructs
		 */
		construct: function(){
			const self = this;
			/**
			 * @typedef {Object} Timeout
			 * @property {number} id
			 * @property {number} delay
			 * @property {function} fn
			 */
			/**
			 * @type {Map<string, Timeout>}
			 * @private
			 */
			self.instances = new Map();
		},
		/**
		 * Returns a boolean indicating whether a timeout with the specified key exists or not.
		 * @param {string} key
		 * @returns {boolean}
		 */
		has: function( key ){
			return this.instances.has( key );
		},
		/**
		 * Returns the specified timeout if it exists.
		 * @param {string} key
		 * @returns {Timeout}
		 */
		get: function( key ){
			return this.instances.get( key );
		},
		/**
		 * Adds or updates a specified timeout.
		 * @param {string} key
		 * @param {function} callback
		 * @param {number} delay
		 * @returns {FooGallery.utils.Timeouts}
		 */
		set: function( key, callback, delay ){
			const self = this;
			self.delete( key );
			const timeout = {
				id: setTimeout( function(){
					self.instances.delete( key );
					callback.call( self );
				}, delay ),
				delay: delay,
				fn: callback
			};
			this.instances.set( key, timeout );
			return self;
		},
		/**
		 * Removes the specified timeout if it exists.
		 * @param {string} key
		 * @returns {boolean}
		 */
		delete: function( key ){
			const self = this;
			if ( self.instances.has( key ) ){
				const timeout = self.instances.get( key );
				clearTimeout( timeout.id );
				return self.instances.delete( key );
			}
			return false;
		},
		/**
		 * Removes all timeouts.
		 */
		clear: function(){
			const self = this;
			self.instances.forEach( function( timeout ){
				clearTimeout( timeout.id );
			} );
			self.instances.clear();
		}
	} );

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.str
);
( function( _ ) {
    const GlobalAttributes = new globalThis.Set( [ "accesskey", "autocapitalize", "autofocus", "class", "contenteditable", "data-", "dir", "draggable", "enterkeyhint", "exportparts", "hidden", "id", "inert", "inputmode", "is", "itemid", "itemprop", "itemref", "itemscope", "itemtype", "lang", "nonce", "part", "popover", "role", "slot", "spellcheck", "style", "tabindex", "title", "translate" ] );
    const ElementsAttributesMap = new globalThis.Map( [ [ "html", [ "xmlns" ] ], [ "base", [ "href", "target" ] ], [ "head", [] ], [ "link", [ "as", "crossorigin", "disabled", "fetchpriority", "href", "hreflang", "imagesizes", "imagesrcset", "integrity", "media", "referrerpolicy", "rel", "sizes", "type" ] ], [ "meta", [ "charset", "content", "http-equiv", "name" ] ], [ "style", [ "media" ] ], [ "title", [] ], [ "body", [ "onafterprint", "onbeforeprint", "onbeforeunload", "onblur", "onerror", "onfocus", "onhashchange", "onlanguagechange", "onload", "onmessage", "onoffline", "ononline", "onpopstate", "onresize", "onstorage", "onunload" ] ], [ "address", [] ], [ "article", [] ], [ "aside", [] ], [ "footer", [] ], [ "header", [] ], [ "h1", [] ], [ "h2", [] ], [ "h3", [] ], [ "h4", [] ], [ "h5", [] ], [ "h6", [] ], [ "hgroup", [] ], [ "main", [] ], [ "nav", [] ], [ "section", [] ], [ "search", [] ], [ "blockquote", [ "cite" ] ], [ "dd", [] ], [ "div", [] ], [ "dl", [] ], [ "dt", [] ], [ "figcaption", [] ], [ "figure", [] ], [ "hr", [] ], [ "li", [ "value" ] ], [ "menu", [] ], [ "ol", [ "reversed", "start", "type" ] ], [ "p", [] ], [ "pre", [] ], [ "ul", [] ], [ "a", [ "download", "href", "hreflang", "ping", "referrerpolicy", "rel", "target", "type" ] ], [ "abbr", [] ], [ "b", [] ], [ "bdi", [] ], [ "bdo", [] ], [ "br", [] ], [ "cite", [] ], [ "code", [] ], [ "data", [ "value" ] ], [ "dfn", [] ], [ "em", [] ], [ "i", [] ], [ "kbd", [] ], [ "mark", [] ], [ "q", [ "cite" ] ], [ "rp", [] ], [ "rt", [] ], [ "ruby", [] ], [ "s", [] ], [ "samp", [] ], [ "small", [] ], [ "span", [] ], [ "strong", [] ], [ "sub", [] ], [ "sup", [] ], [ "time", [ "datetime" ] ], [ "u", [] ], [ "var", [] ], [ "wbr", [] ], [ "area", [ "alt", "coords", "download", "href", "ping", "referrerpolicy", "rel", "shape", "target" ] ], [ "audio", [ "autoplay", "controls", "controlslist", "crossorigin", "disableremoteplayback", "loop", "muted", "preload", "src" ] ], [ "img", [ "alt", "crossorigin", "decoding", "elementtiming", "fetchpriority", "height", "ismap", "loading", "referrerpolicy", "sizes", "src", "srcset", "usemap", "width" ] ], [ "map", [ "name" ] ], [ "track", [ "default", "kind", "label", "src", "srclang" ] ], [ "video", [ "autoplay", "controls", "controlslist", "crossorigin", "disablepictureinpicture", "disableremoteplayback", "height", "loop", "muted", "playsinline", "poster", "preload", "src", "width" ] ], [ "embed", [ "height", "src", "type", "width" ] ], [ "iframe", [ "allow", "allowfullscreen", "height", "loading", "name", "referrerpolicy", "sandbox", "src", "srcdoc", "width" ] ], [ "object", [ "data", "form", "height", "name", "type", "width" ] ], [ "picture", [] ], [ "portal", [ "referrerpolicy", "src" ] ], [ "source", [ "height", "media", "sizes", "src", "srcset", "type", "width" ] ], [ "svg", [ "height", "preserveaspectratio", "viewbox", "width", "x", "y" ] ], [ "canvas", [ "height", "width" ] ], [ "noscript", [] ], [ "script", [ "async", "crossorigin", "defer", "fetchpriority", "integrity", "nomodule", "referrerpolicy", "src", "type" ] ], [ "del", [ "cite", "datetime" ] ], [ "ins", [ "cite", "datetime" ] ], [ "caption", [] ], [ "col", [ "span" ] ], [ "colgroup", [ "span" ] ], [ "table", [] ], [ "tbody", [] ], [ "td", [ "colspan", "headers", "rowspan" ] ], [ "tfoot", [] ], [ "th", [ "abbr", "colspan", "headers", "rowspan", "scope" ] ], [ "thead", [] ], [ "tr", [] ], [ "button", [ "disabled", "form", "formaction", "formenctype", "formmethod", "formnovalidate", "formtarget", "name", "popovertarget", "popovertargetaction", "type", "value" ] ], [ "datalist", [] ], [ "fieldset", [ "disabled", "form", "name" ] ], [ "form", [ "accept-charset", "autocomplete", "name", "rel" ] ], [ "input", [] ], [ "label", [ "for" ] ], [ "legend", [] ], [ "meter", [ "form", "high", "low", "max", "min", "optimum", "value" ] ], [ "optgroup", [ "disabled", "label" ] ], [ "option", [ "disabled", "label", "selected", "value" ] ], [ "output", [ "for", "form", "name" ] ], [ "progress", [ "max", "value" ] ], [ "select", [ "autocomplete", "disabled", "form", "multiple", "name", "required", "size" ] ], [ "textarea", [ "autocomplete", "cols", "dirname", "disabled", "form", "maxlength", "minlength", "name", "placeholder", "readonly", "required", "rows", "wrap" ] ], [ "details", [ "name", "open" ] ], [ "dialog", [ "open" ] ], [ "summary", [] ], [ "slot", [ "name" ] ], [ "template", [ "shadowrootclonable", "shadowrootdelegatesfocus", "shadowrootmode" ] ] ] );
    const UrlAttributes = new globalThis.Set( [ 'code', 'codebase', 'src', 'href', 'formaction', 'ping', 'cite', 'action', 'background', 'poster', 'profile', 'manifest', 'data' ] );

    // always remove these elements
    [ 'script', 'embed', 'object' ].forEach( x => ElementsAttributesMap.delete( x ) );

    const getNodeName = node => ( node instanceof globalThis.Node ? node.nodeName : typeof node === 'string' ? node : '' ).toLowerCase();

    const isElementAttribute = attr => {
        if ( attr instanceof globalThis.Attr ) {
            const ownerElement = getNodeName( attr.ownerElement );
            if ( ElementsAttributesMap.has( ownerElement ) ) {
                return ElementsAttributesMap.get( ownerElement ).includes( attr.name );
            }
        }
        return false;
    }

    const isSafeUrlRegex = /^(?!javascript|vbscript|livescript|mocha)(?:[a-z0-9+.-]+:[^<>]*$|[^&:\/?#]*(?:[\/?#]|$))/i;
    const isSafeUrlAttribute = string => isSafeUrlRegex.test( string );

    const dangerousStyleRegex = /@import|expression|behaviou?r|binding|(?:javascript|vbscript|livescript|mocha):|[\x00-\x08\x0E-\x1F\x7F-\uFFFF]|\/\*.*?\*\/|<--.*?-->/i;
    const isSafeStyleAttribute = value => !dangerousStyleRegex.test( value.replace( /\s+/g, ' ' ) )


    const isPossibleAttributeRegex = /^[a-z](?:[\x2D.0-9_a-z\xB7\xC0-\xD6\xD8-\xF6\xF8-\u037D\u037F-\u1FFF\u200C\u200D\u203F\u2040\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD]|[\uD800-\uDB7F][\uDC00-\uDFFF])*$/;
    const isPossibleAttribute = attr => isPossibleAttributeRegex.test( getNodeName( attr ) );

    // https://html.spec.whatwg.org/multipage/scripting.html#valid-custom-element-name
    const isPossibleCustomElementRegex = /^[a-z](?:[.0-9_a-z\xB7\xC0-\xD6\xD8-\xF6\xF8-\u037D\u037F-\u1FFF\u200C\u200D\u203F\u2040\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD]|[\uD800-\uDB7F][\uDC00-\uDFFF])*-(?:[\x2D.0-9_a-z\xB7\xC0-\xD6\xD8-\xF6\xF8-\u037D\u037F-\u1FFF\u200C\u200D\u203F\u2040\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD]|[\uD800-\uDB7F][\uDC00-\uDFFF])*$/;
    const isPossibleCustomElement = element => isPossibleCustomElementRegex.test( getNodeName( element ) );

    const isPossibleCustomAttribute = attr => isPossibleCustomElement( attr?.ownerElement ) && isPossibleAttribute( attr );

    const isSafeAttribute = attr => {
        const name = getNodeName( attr );
        if ( !name.startsWith( 'on' ) ) {
            if ( name.startsWith( 'aria-' ) || name.startsWith( 'data-' ) ) {
                return true;
            }
            if ( GlobalAttributes.has( name ) || isElementAttribute( attr ) ) {
                if ( UrlAttributes.has( name ) ) {
                    return isSafeUrlAttribute( attr.value );
                }
                if ( name === 'style' ) {
                    return isSafeStyleAttribute( attr.value );
                }
                return true;
            }
            return isPossibleCustomAttribute( attr );
        }
        return false;
    };

    const isSafeElement = element => {
        if ( element instanceof globalThis.Element ) {
            const name = getNodeName( element );
            if ( ElementsAttributesMap.has( name ) || isPossibleCustomElement( element ) ) {
                for ( const attr of element.attributes ) {
                    if ( !isSafeAttribute( attr ) ) {
                        return false;
                    }
                }
                if ( name === 'style' ) {
                    return isSafeStyleAttribute( element.textContent );
                }
                return true;
            }
        }
        return false;
    };

    const cloneNode = ( node, deep = false ) => {
        if ( node instanceof globalThis.Element ) {
            if ( isSafeElement( node ) ) {
                const element = node.cloneNode( false );
                if ( deep && node.hasChildNodes() ) {
                    element.append( ...cloneNodes( node.childNodes, deep ) );
                }
                return element;
            }
            throw new TypeError( 'UNSAFE_NODE' );
        }
        if ( node instanceof globalThis.Text ) {
            return node.cloneNode();
        }
        return null;
    };

    const cloneNodes = ( nodes, deep = false ) => {
        const result = [];
        for ( const node of nodes ) {
            const cloned = cloneNode( node, deep );
            if ( cloned instanceof globalThis.Node ) {
                result.push( cloned );
            }
        }
        return result;
    };

    let parserInstance;
    const parser = () => {
        if ( parserInstance instanceof globalThis.DOMParser ) return parserInstance;
        return parserInstance = new globalThis.DOMParser();
    };

    const toHTML = nodes => nodes.map( node => {
        if ( node.nodeType === 1 ) return node.outerHTML;
        if ( node.nodeType === 3 ) return node.nodeValue;
        return '';
    } ).join( '' );

    _.safeParse = html => {
        if ( typeof html === 'string' ) {
            try {
                const doc = parser().parseFromString( html, 'text/html' );
                if ( doc.body.hasChildNodes() ) {
                    const nodes = cloneNodes( doc.body.childNodes, true );
                    return toHTML( nodes );
                }
            } catch ( e ) {
                if ( e.message !== 'UNSAFE_NODE' ) {
                    console.error( 'FooGallery.safeParse: Unexpected Error', e );
                }
            }
        }
        return '';
    };
} )( window.FooGallery );
(function($, _, _utils, _is, _obj){

    _.Icons = _utils.Class.extend({
        construct: function(){
            this.className = "fg-icon";
            this.registered = {
                "default": {
                    "close": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M13.957 3.457l-1.414-1.414-4.543 4.543-4.543-4.543-1.414 1.414 4.543 4.543-4.543 4.543 1.414 1.414 4.543-4.543 4.543 4.543 1.414-1.414-4.543-4.543z"></path></svg>',
                    "arrow-left": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M10.5 16l1.5-1.5-6.5-6.5 6.5-6.5-1.5-1.5-8 8 8 8z"></path></svg>',
                    "arrow-right": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M5.5 0l-1.5 1.5 6.5 6.5-6.5 6.5 1.5 1.5 8-8-8-8z"></path></svg>',
                    "maximize": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M2 2v4h-2v-5c0-0.552 0.448-1 1-1h14c0.552 0 1 0.448 1 1v14c0 0.552-0.448 1-1 1h-14c-0.552 0-1-0.448-1-1v-9h9c0.552 0 1 0.448 1 1v7h4v-12h-12z"/></svg>',
                    "expand": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M2 5h-2v-4c0-0.552 0.448-1 1-1h4v2h-3v3z"></path><path d="M16 5h-2v-3h-3v-2h4c0.552 0 1 0.448 1 1v4z"></path><path d="M15 16h-4v-2h3v-3h2v4c0 0.552-0.448 1-1 1z"></path><path d="M5 16h-4c-0.552 0-1-0.448-1-1v-4h2v3h3v2z"></path></svg>',
                    "shrink": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M3 0h2v4c0 0.552-0.448 1-1 1h-4v-2h3v-3z"></path><path d="M11 0h2v3h3v2h-4c-0.552 0-1-0.448-1-1v-4z"></path><path d="M12 11h4v2h-3v3h-2v-4c0-0.552 0.448-1 1-1z"></path><path d="M0 11h4c0.552 0 1 0.448 1 1v4h-2v-3h-3v-2z"></path></svg>',
                    "info": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M7 4.75c0-0.412 0.338-0.75 0.75-0.75h0.5c0.412 0 0.75 0.338 0.75 0.75v0.5c0 0.412-0.338 0.75-0.75 0.75h-0.5c-0.412 0-0.75-0.338-0.75-0.75v-0.5z"></path><path d="M10 12h-4v-1h1v-3h-1v-1h3v4h1z"></path><path d="M8 0c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zM8 14.5c-3.59 0-6.5-2.91-6.5-6.5s2.91-6.5 6.5-6.5 6.5 2.91 6.5 6.5-2.91 6.5-6.5 6.5z"></path></svg>',
                    "comment": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M3 4h10v1h-10zM3 6h8v1h-8zM3 8h4v1h-4zM14.5 1h-13c-0.825 0-1.5 0.675-1.5 1.5v8c0 0.825 0.675 1.5 1.5 1.5h2.5v4l4.8-4h5.7c0.825 0 1.5-0.675 1.5-1.5v-8c0-0.825-0.675-1.5-1.5-1.5zM14 10h-5.924l-3.076 2.73v-2.73h-3v-7h12v7z"></path></svg>',
                    "thumbs": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M3 3v10h-2v-11c0-0.552 0.448-1 1-1h12c0.552 0 1 0.448 1 1v12c0 0.552-0.448 1-1 1h-12c-0.552 0-1-0.448-1-1v-1h4v-2h-2v-2h2v-2h-2v-2h2v-2h2v10h6v-10h-10z"></path></svg>',
                    "cart": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M13.238 9c0.55 0 1.124-0.433 1.275-0.962l1.451-5.077c0.151-0.529-0.175-0.962-0.725-0.962h-10.238c0-1.105-0.895-2-2-2h-3v2h3v8.5c0 0.828 0.672 1.5 1.5 1.5h9.5c0.552 0 1-0.448 1-1s-0.448-1-1-1h-9v-1h8.238zM5 4h9.044l-0.857 3h-8.187v-3z"></path><path d="M6 14.5c0 0.828-0.672 1.5-1.5 1.5s-1.5-0.672-1.5-1.5c0-0.828 0.672-1.5 1.5-1.5s1.5 0.672 1.5 1.5z"></path><path d="M15 14.5c0 0.828-0.672 1.5-1.5 1.5s-1.5-0.672-1.5-1.5c0-0.828 0.672-1.5 1.5-1.5s1.5 0.672 1.5 1.5z"></path></svg>',
                    "circle-close": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M8 0c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zM8 14.5c-3.59 0-6.5-2.91-6.5-6.5s2.91-6.5 6.5-6.5 6.5 2.91 6.5 6.5-2.91 6.5-6.5 6.5z"></path><path d="M10.5 4l-2.5 2.5-2.5-2.5-1.5 1.5 2.5 2.5-2.5 2.5 1.5 1.5 2.5-2.5 2.5 2.5 1.5-1.5-2.5-2.5 2.5-2.5z"></path></svg>',
                    "auto-progress": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path class="[ICON_CLASS]-idle" d="M11.39 8c2.152-1.365 3.61-3.988 3.61-7 0-0.339-0.019-0.672-0.054-1h-13.891c-0.036 0.328-0.054 0.661-0.054 1 0 3.012 1.457 5.635 3.609 7-2.152 1.365-3.609 3.988-3.609 7 0 0.339 0.019 0.672 0.054 1h13.891c0.036-0.328 0.054-0.661 0.054-1 0-3.012-1.457-5.635-3.609-7zM2.5 15c0-2.921 1.253-5.397 3.5-6.214v-1.572c-2.247-0.817-3.5-3.294-3.5-6.214v0h11c0 2.921-1.253 5.397-3.5 6.214v1.572c2.247 0.817 3.5 3.294 3.5 6.214h-11zM9.462 10.462c-1.12-0.635-1.181-1.459-1.182-1.959v-1.004c0-0.5 0.059-1.327 1.184-1.963 0.602-0.349 1.122-0.88 1.516-1.537h-6.4c0.395 0.657 0.916 1.188 1.518 1.538 1.12 0.635 1.181 1.459 1.182 1.959v1.004c0 0.5-0.059 1.327-1.184 1.963-1.135 0.659-1.98 1.964-2.236 3.537h7.839c-0.256-1.574-1.102-2.879-2.238-3.538z"/><circle class="[ICON_CLASS]-circle" r="4" cx="8" cy="8"/><path class="[ICON_CLASS]-play" d="M3 2l10 6-10 6z"/><path class="[ICON_CLASS]-pause" d="M2 2h5v12h-5zM9 2h5v12h-5z"/></svg>',
                    "search": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M16 13.5l-4.695-4.695c0.444-0.837 0.695-1.792 0.695-2.805 0-3.314-2.686-6-6-6s-6 2.686-6 6 2.686 6 6 6c1.013 0 1.968-0.252 2.805-0.695l4.695 4.695 2.5-2.5zM2 6c0-2.209 1.791-4 4-4s4 1.791 4 4-1.791 4-4 4-4-1.791-4-4z"></path></svg>',
                    "exif-aperture": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M 8,0 C 3.5876443,0 0,3.5876443 0,8 c 0,4.412356 3.5876443,8 8,8 4.412356,0 8,-3.587644 8,-8 C 16,3.5876443 12.412356,0 8,0 Z m 0,1 c 3.871916,0 7,3.1280844 7,7 0,3.871916 -3.128084,7 -7,7 C 4.1280844,15 1,11.871916 1,8 1,4.1280844 4.1280844,1 8,1 Z M 7.53125,2.0214844 A 6,6 0 0 0 3.1835938,4.4335938 H 8.9257812 Z M 8.6132812,2.03125 C 9.5587451,3.6702105 10.504247,5.3091484 11.451172,6.9472656 L 12.863281,4.5 A 6,6 0 0 0 8.6132812,2.03125 Z M 2.5957031,5.4101562 A 6,6 0 0 0 2,8 6,6 0 0 0 2.5273438,10.439453 L 5.4296875,5.4101562 Z m 10.8261719,0.033203 -2.855469,4.9433598 h 2.935547 A 6,6 0 0 0 14,8 6,6 0 0 0 13.421875,5.4433592 Z M 4.5722656,8.8945312 3.0996094,11.449219 a 6,6 0 0 0 4.40625,2.527343 z m 2.5820313,2.4707028 1.4960937,2.591797 a 6,6 0 0 0 4.3144534,-2.591797 z"></path></svg>',
                    "exif-camera": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="m 8.0000002,5 a 4,4 0 0 0 -4,4 4,4 0 0 0 4,4 A 4,4 0 0 0 12,9 4,4 0 0 0 8.0000002,5 Z m 0.019531,1.015625 a 3,2.9814477 0 0 1 2.9804688,3 l -1,-0.00586 a 2,2 0 0 0 0,-0.00976 2,2 0 0 0 -1.9863279,-2 z M 5.125,1 C 4.5,1 4,1.5 4,2.125 V 3.0000004 L 1.125,3 C 0.5,2.9999999 0,3.5 0,4.125 v 9.75 C 0,14.5 0.5,15 1.125,15 h 13.75 C 15.5,15 16,14.5 16,13.875 V 4.125 C 16,3.5 15.5,3 14.875,3 H 12 V 2.125 C 12,1.5 11.5,1 10.875,1 Z M 5.25,2.0000004 h 5.5 c 0.125,0 0.25,0.1249996 0.25,0.25 v 1.75 h 3.75 c 0.125,0 0.25,0.1249996 0.25,0.25 V 13.75 C 15,13.875 14.875,14 14.75,14 H 1.25 C 1.125,14 1,13.875 1,13.75 V 4.25 C 1,4.125 1.125,4 1.25,4 l 3.75,4e-7 v -1.75 c 0,-0.1250004 0.125,-0.25 0.25,-0.25 z"></path></svg>',
                    "exif-created-timestamp": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M 3,1 V 2 H 1 V 15 H 16 V 2 H 14 V 1 H 13 V 2 H 4 V 1 Z M 2,4 H 15 V 14 H 2 Z M 6,5 V 7 H 8 V 5 Z m 3,0 v 2 h 2 V 5 Z m 3,0 v 2 h 2 V 5 Z M 3,8 v 2 H 5 V 8 Z m 3,0 v 2 H 8 V 8 Z m 3,0 v 2 h 2 V 8 Z m 3,0 v 2 h 2 V 8 Z m -9,3 v 2 h 2 v -2 z m 3,0 v 2 h 2 v -2 z m 3,0 v 2 h 2 v -2 z"></path></svg>',
                    "exif-shutter-speed": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M 8,0 C 3.5876443,0 -2.9415707e-8,3.5876443 0,8 c 2.3532563e-7,4.412356 3.5876445,8 8,8 4.412356,0 8,-3.587644 8,-8 C 16,3.5876443 12.412356,0 8,0 Z m 0,1 c 3.871916,0 7,3.1280844 7,7 0,3.871915 -3.128085,7 -7,7 -3.8719154,0 -6.9999998,-3.128085 -7,-7 -3e-8,-3.8719156 3.1280844,-7 7,-7 z M 11.646484,3.6464844 8.6445312,6.6484375 A 1.5,1.5 0 0 0 8,6.5 1.5,1.5 0 0 0 6.5,8 1.5,1.5 0 0 0 8,9.5 1.5,1.5 0 0 0 9.5,8 1.5,1.5 0 0 0 9.3515625,7.3554688 L 12.353516,4.3535156 Z M 2,7.5 v 1 h 2 v -1 z M 7.5,12 v 2 h 1 V 12 Z M 12,7.5 v 1 h 2 v -1 z M 7.5,2 v 2 h 1 V 2 Z"></path></svg>',
                    "exif-focal-length": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="m 1,4.125 -1,0.25 v 7.25 l 1,0.25 z M 5,1 2,4 v 8 l 3,3 h 6.875 C 12.500003,15 13,14.5 13,13.875 V 2.125 C 13,1.4999986 12.5,1 11.875,1 9.576807,0.99914375 7.1414067,0.96597644 5,1 Z M 5.5,2 H 6 V 14 H 5.5 L 3,11.5 v -7 z M 7,2 h 4.75 C 11.875,2 12,2.1249997 12,2.25 v 11.5 c 0,0.125 -0.125,0.250622 -0.25,0.25 H 7 Z m 7,0 c 1,2.2 1.5,4.35 1.5,6 0,1.65 -0.5,3.8 -1.5,6"></path></svg>',
                    "exif-iso": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M 7.5,0 V 1.6015625 C 6.0969201,1.7146076 4.8392502,2.256185 3.828125,3.1210938 L 2.6035156,1.8964844 1.8964844,2.6035156 3.1210938,3.828125 C 2.256185,4.8392502 1.7146076,6.0969201 1.6015625,7.5 H 0 v 1 h 1.6015625 c 0.1130451,1.4030799 0.6546225,2.66075 1.5195313,3.671875 l -1.2246094,1.224609 0.7070312,0.707032 1.2246094,-1.22461 C 4.8392502,13.743815 6.0969201,14.285392 7.5,14.398438 V 16 h 1 v -1.601562 c 1.4030799,-0.113046 2.66075,-0.654623 3.671875,-1.519532 l 1.224609,1.22461 0.707032,-0.707032 -1.22461,-1.224609 C 13.743815,11.16075 14.285392,9.9030799 14.398438,8.5 H 16 v -1 H 14.398438 C 14.285392,6.0969201 13.743815,4.8392502 12.878906,3.828125 L 14.103516,2.6035156 13.396484,1.8964844 12.171875,3.1210938 C 11.16075,2.256185 9.9030799,1.7146076 8.5,1.6015625 V 0 Z M 8,2.5 c 3.043488,0 5.5,2.4565116 5.5,5.5 0,3.043488 -2.456512,5.5 -5.5,5.5 C 4.9565116,13.5 2.5,11.043488 2.5,8 2.5,4.9565116 4.9565116,2.5 8,2.5 Z"></path></svg>',
                    "exif-orientation": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M 1.25,0 C 0.625,0 0,0.625 0,1.25 V 5 H 1 V 3 h 8 v 2 h 1 V 1.25 C 10,0.625 9.375,0 8.75,0 Z m 0,1 h 7.5 C 8.875,1 9,1.125 9,1.25 V 2 H 1 V 1.25 C 1,1.125 1.125,1 1.25,1 Z m 0,5 C 0.625,6 0,6.625 0,7.25 v 7.5 C 0,15.375 0.625,16 1.25,16 h 13.5 C 15.375,16 16,15.375 16,14.75 V 7.25 C 16,6.625 15.375,6 14.75,6 Z m 0,1 H 2 v 3 H 1 V 7.25 C 1,7.125 1.125,7 1.25,7 Z M 3,7 h 10 v 8 H 3 Z m 11,0 h 0.75 C 14.875,7 15,7.125 15,7.25 v 7.5 C 15,14.875 14.875,15 14.75,15 H 14 Z M 1,12 h 1 v 3 H 1.25 C 1.125,15 1,14.875 1,14.75 Z"></path></svg>'
                }
            };
        },
        register: function(setName, icons){
            if (_is.empty(setName) || _is.empty(icons) || !_is.string(setName) || !_is.hash(icons)) return false;
            this.registered[setName] = _obj.extend({}, this.registered.default, icons);
            return true;
        },
        get: function(name, setNameOrObject){
            var self = this, setName = "default",
                icons = _obj.extend({}, self.registered.default);

            if (_is.string(setNameOrObject) && setNameOrObject !== "default"){
                setName = setNameOrObject;
                icons = _obj.extend(icons, self.registered[setNameOrObject]);
            } else if (_is.hash(setNameOrObject)){
                setName = "custom";
                icons = _obj.extend(icons, setNameOrObject);
            }

            var icon = _is.string(name) && icons.hasOwnProperty(name) ? icons[name].replace(/\[ICON_CLASS]/g, self.className + "-" + name) : null,
                classNames = [false, name, setName].map(function(val){
                    return val === false ? self.className : self.className + "-" + val;
                }).join(" ");

            return $(icon).addClass(classNames);
        },
        element: function(name, setNameOrObject){
            const self = this;

            let setName = "default",
                icons = _obj.extend({}, self.registered.default);

            if (_is.string(setNameOrObject) && setNameOrObject !== "default"){
                setName = setNameOrObject;
                icons = _obj.extend(icons, self.registered[setNameOrObject]);
            } else if (_is.hash(setNameOrObject)){
                setName = "custom";
                icons = _obj.extend(icons, setNameOrObject);
            }

            const iconString = _is.string(name) && icons.hasOwnProperty(name) ? icons[name].replace(/\[ICON_CLASS]/g, self.className + "-" + name) : null;
            if ( iconString !== null ){
                const fragment = document.createRange().createContextualFragment(iconString);
                const svg = fragment.querySelector("svg");
                if ( svg ){
                    ["", "-" + name, "-" + setName].forEach(function(suffix){
                        svg.classList.add(self.className + suffix);
                    });
                    return svg;
                }
            }
            return null;
        }
    });

    /**
     * @summary Icon manager for FooGallery.
     * @memberof FooGallery
     * @name icons
     * @type {FooGallery.Icons}
     */
    _.icons = new _.Icons();

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.obj
);
(function($, _, _utils, _is, _obj) {

	var DATA_NAME = "__FooGallerySwipe__",
			TOUCH = "ontouchstart" in window,
			POINTER_IE10 = window.navigator.msPointerEnabled && !window.navigator.pointerEnabled && !TOUCH,
			POINTER = (window.navigator.pointerEnabled || window.navigator.msPointerEnabled) && !TOUCH,
			USE_TOUCH = TOUCH || POINTER;

	_.Swipe = _utils.Class.extend(/** @lend FooGallery.Swipe */{
		/**
		 * @summary A utility class for handling swipe gestures on touch devices.
		 * @memberof FooGallery
		 * @constructs Swipe
		 * @param {Element} element - The element being bound to.
		 * @param {Object} options - Any options for the current instance of the class.
		 * @augments FooGallery.utils.Class
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(element, options){
			var self = this, ns = ".fgswipe";
			/**
			 * @summary The jQuery element this instance of the class is bound to.
			 * @memberof FooGallery.Swipe
			 * @name $el
			 * @type {jQuery}
			 */
			self.$el = $(element);
			/**
			 * @summary The options for this instance of the class.
			 * @memberof FooGallery.Swipe
			 * @name opt
			 * @type {FooGallery.Swipe~Options}
			 */
			self.opt = _obj.extend({
				threshold: 20,
				allowPageScroll: false,
				swipe: $.noop,
				data: {}
			}, options);
			/**
			 * @summary Whether or not a swipe is in progress.
			 * @memberof FooGallery.Swipe
			 * @name active
			 * @type {boolean}
			 */
			self.active = false;
			/**
			 * @summary The start point for the last swipe.
			 * @memberof FooGallery.Swipe
			 * @name startPoint
			 * @type {?FooGallery.Swipe~Point}
			 */
			self.startPoint = null;
			/**
			 * @summary The end point for the last swipe.
			 * @memberof FooGallery.Swipe
			 * @name startPoint
			 * @type {?FooGallery.Swipe~Point}
			 */
			self.endPoint = null;
			/**
			 * @summary The event names used by this instance of the plugin.
			 * @memberof FooGallery.Swipe
			 * @name events
			 * @type {{start: string, move: string, end: string, leave: string}}
			 */
			self.events = {
				start: (USE_TOUCH ? (POINTER ? (POINTER_IE10 ? 'MSPointerDown' : 'pointerdown') : 'touchstart') : 'mousedown') + ns,
				move: (USE_TOUCH ? (POINTER ? (POINTER_IE10 ? 'MSPointerMove' : 'pointermove') : 'touchmove') : 'mousemove') + ns,
				end: (USE_TOUCH ? (POINTER ? (POINTER_IE10 ? 'MSPointerUp' : 'pointerup') : 'touchend') : 'mouseup') + ns,
				leave: (USE_TOUCH ? (POINTER ? 'mouseleave' : null) : 'mouseleave') + ns
			};
		},
		/**
		 * @summary Initializes this instance of the class.
		 * @memberof FooGallery.Swipe
		 * @function init
		 */
		init: function(){
			var self = this;
			self.$el.on(self.events.start, {self: self}, self.onStart);
			self.$el.on(self.events.move, {self: self}, self.onMove);
			self.$el.on(self.events.end, {self: self}, self.onEnd);
			if (_is.string(self.events.leave)) self.$el.on(self.events.leave, {self: self}, self.onEnd);
			self.$el.data(DATA_NAME, self);
		},
		/**
		 * @summary Destroys this instance of the class.
		 * @memberof FooGallery.Swipe
		 * @function destroy
		 */
		destroy: function(){
			var self = this;
			self.$el.off(self.events.start, self.onStart);
			self.$el.off(self.events.move, self.onMove);
			self.$el.off(self.events.end, self.onEnd);
			if (_is.string(self.events.leave)) self.$el.off(self.events.leave, self.onEnd);
			self.$el.removeData(DATA_NAME);
		},
		/**
		 * @summary Gets the angle between two points.
		 * @memberof FooGallery.Swipe
		 * @function getAngle
		 * @param {FooGallery.Swipe~Point} pt1 - The first point.
		 * @param {FooGallery.Swipe~Point} pt2 - The second point.
		 * @returns {number}
		 */
		getAngle: function(pt1, pt2){
			var radians = Math.atan2(pt1.x - pt2.x, pt1.y - pt2.y),
					degrees = Math.round(radians * 180 / Math.PI);
			return 360 - (degrees < 0 ? 360 - Math.abs(degrees) : degrees);
		},
		/**
		 * @summary Gets the distance between two points.
		 * @memberof FooGallery.Swipe
		 * @function getDistance
		 * @param {FooGallery.Swipe~Point} pt1 - The first point.
		 * @param {FooGallery.Swipe~Point} pt2 - The second point.
		 * @returns {number}
		 */
		getDistance: function(pt1, pt2){
			var xs = pt2.x - pt1.x,
					ys = pt2.y - pt1.y;

			xs *= xs;
			ys *= ys;

			return Math.sqrt( xs + ys );
		},
		/**
		 * @summary Gets the general direction between two points and returns the result as a compass heading: N, NE, E, SE, S, SW, W, NW or NONE if the points are the same.
		 * @memberof FooGallery.Swipe
		 * @function getDirection
		 * @param {FooGallery.Swipe~Point} pt1 - The first point.
		 * @param {FooGallery.Swipe~Point} pt2 - The second point.
		 * @returns {string}
		 */
		getDirection: function(pt1, pt2){
			var self = this, angle = self.getAngle(pt1, pt2);
			if (angle > 337.5 || angle <= 22.5) return "N";
			else if (angle > 22.5 && angle <= 67.5) return "NE";
			else if (angle > 67.5 && angle <= 112.5) return "E";
			else if (angle > 112.5 && angle <= 157.5) return "SE";
			else if (angle > 157.5 && angle <= 202.5) return "S";
			else if (angle > 202.5 && angle <= 247.5) return "SW";
			else if (angle > 247.5 && angle <= 292.5) return "W";
			else if (angle > 292.5 && angle <= 337.5) return "NW";
			return "NONE";
		},
		/**
		 * @summary Gets the pageX and pageY point from the supplied event whether it is for a touch or mouse event.
		 * @memberof FooGallery.Swipe
		 * @function getPoint
		 * @param {jQuery.Event} event - The event to parse the point from.
		 * @returns {FooGallery.Swipe~Point}
		 */
		getPoint: function(event){
			var touches;
			if (USE_TOUCH && !_is.empty(touches = event.originalEvent.touches || event.touches)){
				return {x: touches[0].pageX, y: touches[0].pageY};
			}
			if (_is.number(event.pageX) && _is.number(event.pageY)){
				return {x: event.pageX, y: event.pageY};
			}
			return null;
		},
		/**
		 * @summary Gets the offset from the supplied point.
		 * @memberof FooGallery.Swipe
		 * @function getOffset
		 * @param {FooGallery.Swipe~Point} pt - The point to use to calculate the offset.
		 * @returns {FooGallery.Swipe~Offset}
		 */
		getOffset: function(pt){
			var self = this, offset = self.$el.offset();
			return {
				left: pt.x - offset.left,
				top: pt.y - offset.top
			};
		},
		/**
		 * @summary Handles the {@link FooGallery.Swipe#events.start|start} event.
		 * @memberof FooGallery.Swipe
		 * @function onStart
		 * @param {jQuery.Event} event - The event object for the current event.
		 */
		onStart: function(event){
			var self = event.data.self, pt = self.getPoint(event);
			if (!_is.empty(pt)){
				self.active = true;
				self.startPoint = self.endPoint = pt;
			}
		},
		/**
		 * @summary Handles the {@link FooGallery.Swipe#events.move|move} event.
		 * @memberof FooGallery.Swipe
		 * @function onMove
		 * @param {jQuery.Event} event - The event object for the current event.
		 */
		onMove: function(event){
			var self = event.data.self, pt = self.getPoint(event);
			if (self.active && !_is.empty(pt)){
				self.endPoint = pt;
				if (!self.opt.allowPageScroll){
					event.preventDefault();
				} else if (_is.hash(self.opt.allowPageScroll)){
					var dir = self.getDirection(self.startPoint, self.endPoint);
					if (!self.opt.allowPageScroll.x && _utils.inArray(dir, ['NE','E','SE','NW','W','SW']) !== -1){
						event.preventDefault();
					}
					if (!self.opt.allowPageScroll.y && _utils.inArray(dir, ['NW','N','NE','SW','S','SE']) !== -1){
						event.preventDefault();
					}
				}
			}
		},
		/**
		 * @summary Handles the {@link FooGallery.Swipe#events.end|end} and {@link FooGallery.Swipe#events.leave|leave} events.
		 * @memberof FooGallery.Swipe
		 * @function onEnd
		 * @param {jQuery.Event} event - The event object for the current event.
		 */
		onEnd: function(event){
			var self = event.data.self;
			if (self.active){
				self.active = false;
				var info = {
					startPoint: self.startPoint,
					endPoint: self.endPoint,
					startOffset: self.getOffset(self.startPoint),
					endOffset: self.getOffset(self.endPoint),
					angle: self.getAngle(self.startPoint, self.endPoint),
					distance: self.getDistance(self.startPoint, self.endPoint),
					direction: self.getDirection(self.startPoint, self.endPoint)
				};

				if (self.opt.threshold > 0 && info.distance < self.opt.threshold) return;

				self.opt.swipe.apply(this, [info, self.opt.data]);
				self.startPoint = null;
				self.endPoint = null;
			}
		}
	});

	/**
	 * @summary Expose FooGallery.Swipe as a jQuery plugin.
	 * @memberof external:"jQuery.fn"#
	 * @function fgswipe
	 * @param {(FooGallery.Swipe~Options|string)} [options] - The options to supply to FooGallery.Swipe or one of the supported method names.
	 * @returns {jQuery}
	 */
	$.fn.fgswipe = function(options){
		return this.each(function(){
			var $this = $(this), swipe = $this.data(DATA_NAME), exists = swipe instanceof _.Swipe;
			if (exists){
				if (_is.string(options) && _is.fn(swipe[options])){
					swipe[options]();
					return;
				} else {
					swipe.destroy();
				}
			}
			if (_is.hash(options)){
				swipe = new _.Swipe(this, options);
				swipe.init();
			}
		});
	};

	/**
	 * @summary A simple point object containing X and Y coordinates.
	 * @typedef {Object} FooGallery.Swipe~Point
	 * @property {number} x - The X coordinate.
	 * @property {number} y - The Y coordinate.
	 */

	/**
	 * @summary A simple offset object containing top and left values.
	 * @typedef {Object} FooGallery.Swipe~Offset
	 * @property {number} left - The left value.
	 * @property {number} top - The top value.
	 */

	/**
	 * @summary The information object supplied as the first parameter to the {@link FooGallery.Swipe~swipeCallback} function.
	 * @typedef {Object} FooGallery.Swipe~Info
	 * @property {FooGallery.Swipe~Point} startPoint - The page X and Y coordinates where the swipe began.
	 * @property {FooGallery.Swipe~Point} endPoint - The page X and Y coordinates where the swipe ended.
	 * @property {FooGallery.Swipe~Offset} startOffset - The top and left values where the swipe began.
	 * @property {FooGallery.Swipe~Offset} endOffset - The top and left values where the swipe ended.
	 * @property {number} angle - The angle traveled from the start to the end of the swipe.
	 * @property {number} distance - The distance traveled from the start to the end of the swipe.
	 * @property {string} direction - The general direction traveled from the start to the end of the swipe: N, NE, E, SE, S, SW, W, NW or NONE if the points are the same.
	 */

	/**
	 * @summary The callback function to execute whenever a swipe occurs.
	 * @callback FooGallery.Swipe~swipeCallback
	 * @param {FooGallery.Swipe~Info} info - The swipe info.
	 * @param {Object} data - Any additional data supplied when the swipe was bound.
	 */

	/**
	 * @summary The options available for the swipe utility class.
	 * @typedef {Object} FooGallery.Swipe~Options
	 * @property {number} [threshold=20] - The minimum distance to travel before being registered as a swipe.
	 * @property {FooGallery.Swipe~swipeCallback} swipe - The callback function to execute whenever a swipe occurs.
	 * @property {Object} [data={}] - Any additional data to supply to the swipe callback.
	 */

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is,
		FooGallery.utils.obj
);
(function($, _, _utils, _is, _fn, _obj){

    /**
     * @summary A factory for classes allowing them to be registered and created using a friendly name.
     * @memberof FooGallery.
     * @class Factory
     * @description This class allows other classes to register themselves for use at a later time. Depending on how you intend to use the registered classes you can also specify a load and execution order through the `priority` parameter of the {@link FooGallery.utils.Factory#register|register} method.
     * @augments FooGallery.utils.Class
     * @borrows FooGallery.utils.Class.extend as extend
     * @borrows FooGallery.utils.Class.override as override
     */
    _.Factory = _utils.Class.extend(/** @lends FooGallery.Factory.prototype */{
        /**
         * @ignore
         * @constructs
         **/
        construct: function(){
            /**
             * @summary An object containing all the required info to create a new instance of a registered class.
             * @typedef {Object} FooGallery.Factory~RegisteredClass
             * @property {string} name - The friendly name of the registered class.
             * @property {function} klass - The constructor for the registered class.
             * @property {number} priority - The priority for the registered class.
             */

            /**
             * @summary An object containing all registered classes.
             * @memberof FooGallery.Factory#
             * @name registered
             * @type {Object.<string, FooGallery.Factory~RegisteredClass>}
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
         * @memberof FooGallery.Factory#
         * @function contains
         * @param {string} name - The name of the class to check.
         * @returns {boolean}
         * @example {@run true}
         * // create a new instance of the factory, this is usually exposed by the class that will be using the factory.
         * var factory = new FooGallery.Factory();
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
         * @summary Create a new instance of a class registered with the supplied `name` and arguments.
         * @memberof FooGallery.Factory#
         * @function make
         * @param {string} name - The name of the class to create.
         * @param {*} [arg1] - The first argument to supply to the new instance.
         * @param {...*} [argN] - Any number of additional arguments to supply to the new instance.
         * @returns {Object}
         * @example {@caption The following shows how to create a new instance of a registered class.}{@run true}
         * // create a new instance of the factory, this is usually done by the class that will be using it.
         * var factory = new FooGallery.Factory();
         *
         * // create a Logger class to register, this would usually be in another file
         * var Logger = FooGallery.Class.extend({
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
         * @memberof FooGallery.Factory#
         * @function names
         * @param {boolean} [prioritize=false] - Whether or not to order the names by the priority they were registered with.
         * @returns {Array.<string>}
         * @example {@run true}
         * // create a new instance of the factory, this is usually exposed by the class that will be using the factory.
         * var factory = new FooGallery.Factory();
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
                reg.forEach(function(registered){
                    names.push(registered.name);
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
         * @memberof FooGallery.Factory#
         * @function register
         * @param {string} name - The friendly name of the class.
         * @param {function} klass - The class constructor to register.
         * @param {number} [priority=0] - This determines the index for the class when using the {@link FooGallery.Factory#names|names} method, a higher value equals a lower index.
         * @returns {boolean} `true` if the `klass` was successfully registered.
         * @description Once a class is registered you can use either the {@link FooGallery.Factory#make|make} method to create new instances.
         * @example {@run true}
         * // create a new instance of the factory, this is usually exposed by the class that will be using the factory.
         * var factory = new FooGallery.Factory();
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
        },
        load: function(arg1, argN){
            var self = this, args = _fn.arg2arr(arguments), result = [], reg = [], name;
            for (name in self.registered){
                if (!self.registered.hasOwnProperty(name)) continue;
                reg.push(self.registered[name]);
            }
            reg.sort(function(a, b){ return b.priority - a.priority; });
            reg.forEach(function(registered){
                var makeArgs = args.slice();
                makeArgs.unshift(registered.name);
                result.push(self.make.apply(self, makeArgs));
            });
            return result;
        },
        /**
         * @memberof FooGallery.Factory#
         * @function configure
         * @param {string} name
         * @param {object} options
         * @param {object} classes
         * @param {object} il8n
         */
        configure: function(name, options, classes, il8n){
            var self = this;
            if (self.contains(name)) {
                var reg = self.registered;
                _obj.extend(reg[name].opt, options);
                _obj.extend(reg[name].cls, classes);
                _obj.extend(reg[name].il8n, il8n);
            }
        }
    });

})(
    // dependencies
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.obj
);
(function ($, _, _utils, _is, _fn, _obj) {

	_.TemplateFactory = _.Factory.extend(/** @lends FooGallery.TemplateFactory */{
		/**
		 * @summary A factory for galleries allowing them to be easily registered and created.
		 * @memberof FooGallery
		 * @constructs TemplateFactory
		 * @description The plugin makes use of an instance of this class exposed as {@link FooGallery.template}.
		 * @augments FooGallery.Factory
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function () {
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
		register: function (name, template, options, classes, il8n, priority) {
			var self = this, result = self._super(name, template, priority);
			if (result) {
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
		make: function (options, element) {
			element = _is.jq(element) ? element : $(element);
			options = _obj.extend({}, options, element.data("foogallery"));
			var self = this, type = self.type(options, element);
			if (!self.contains(type)) return null;
			options = self.options(type, options);
			return self._super(type, options, element);
		},
		type: function (options, element) {
			element = _is.jq(element) ? element : $(element);
			var self = this, type = _is.hash(options) && _is.hash(options) && _is.string(options.type) && self.contains(options.type) ? options.type : null;
			if (type === null && element.length > 0) {
				var reg = self.registered, names = self.names(true);
				for (var i = 0, l = names.length; i < l; i++) {
					if (!reg.hasOwnProperty(names[i]) || names[i] === "core") continue;
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
		options: function (name, options) {
			options = _obj.extend({type: name}, options);
			var self = this, reg = self.registered,
					def = reg["core"].opt,
					cls = reg["core"].cls,
					il8n = reg["core"].il8n;

			if (!_is.hash(options.cls)) options.cls = {};
			if (!_is.hash(options.il8n)) options.il8n = {};
			if (!_is.undef(_.filtering)) options = _.filtering.merge(options);
			if (!_is.undef(_.paging)) options = _.paging.merge(options);

			if (name !== "core" && self.contains(name)) {
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
(function ($, _, _utils, _is, _fn, _str) {

	var instance = 0;

	_.Template = _utils.EventClass.extend(/** @lends FooGallery.Template */{
		/**
		 * @summary The primary class for FooGallery, this controls the flow of the plugin across all templates.
		 * @memberof FooGallery
		 * @constructs Template
		 * @param {FooGallery.Template~Options} [options] - The options for the template.
		 * @param {jQuery} [element] - The jQuery object of the templates' container element. If not supplied one will be created within the `parent` element supplied to the {@link FooGallery.Template#initialize|initialize} method.
		 * @augments FooGallery.utils.Class
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function (options, element) {
			var self = this;
			self._super();
			/**
			 * @summary An instance specific namespace to use when binding events to global objects that could be shared across multiple galleries.
			 * @memberof FooGallery.Template#
			 * @name namespace
			 * @type {string}
			 */
			self.namespace = ".foogallery-" + (++instance);
			/**
			 * @summary The jQuery object for the template container.
			 * @memberof FooGallery.Template#
			 * @name $el
			 * @type {jQuery}
			 */
			self.$el = _is.jq(element) ? element : $(element);
			/**
			 * @summary The element for the template container.
			 * @memberof FooGallery.Template#
			 * @name el
			 * @type {?HTMLElement}
			 */
			self.el = self.$el.get(0) || null;
			/**
			 * @summary The jQuery object for the template containers scroll parent.
			 * @memberof FooGallery.Template#
			 * @name $scrollParent
			 * @type {?jQuery}
			 */
			self.$scrollParent = null;
			/**
			 * @summary The options for the template.
			 * @memberof FooGallery.Template#
			 * @name opt
			 * @type {FooGallery.Template~Options}
			 */
			self.opt = options;
			/**
			 * @summary Any custom options for the template.
			 * @memberof FooGallery.Template#
			 * @name template
			 * @type {Object}
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
			 * @summary The CSS classes for the template.
			 * @memberof FooGallery.Template#
			 * @name cls
			 * @type {FooGallery.Template~CSSClasses}
			 */
			self.cls = options.cls;
			/**
			 * @summary The il8n strings for the template.
			 * @memberof FooGallery.Template#
			 * @name il8n
			 * @type {FooGallery.Template~il8n}
			 */
			self.il8n = options.il8n;
			/**
			 * @summary The CSS selectors for the template.
			 * @memberof FooGallery.Template#
			 * @name sel
			 * @type {FooGallery.Template~CSSSelectors}
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
			self.pages = !_is.undef(_.paging) ? _.paging.make(options.paging.type, self) : null;
			/**
			 * @summary The page manager for the template.
			 * @memberof FooGallery.Template#
			 * @name filter
			 * @type {?FooGallery.Filtering}
			 */
			self.filter = !_is.undef(_.filtering) ? _.filtering.make(options.filtering.type, self) : null;
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

			self._layoutTimeout = null;
			/**
			 * @memberof FooGallery.Template#
			 * @name _layoutWidths
			 * @type {Number[]}
			 * @private
			 */
			self._layoutWidths = [];
			/**
			 * @memberof FooGallery.Template#
			 * @name lastWidth
			 * @type {Number}
			 */
			self.lastWidth = 0;
			self.initializing = false;
			self.initialized = false;
            self.destroying = false;
			self.destroyed = false;
			self._undo = {
				classes: "",
				style: "",
				create: false,
				children: false
			};
			self.robserver = new ResizeObserver(_fn.throttle(function(entries) {
				if (!self.destroying && !self.destroyed && entries.length === 1 && entries[0].target === self.el){
					var size = _utils.getResizeObserverSize(entries[0]);
					self.layout(size.width);
				}
			}, 50));
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
		initialize: function (parent) {
			var self = this;
			if (_is.promise(self._initialize)) return self._initialize;
			return self._initialize = $.Deferred(function (def) {
				if (self.preInit(parent)){
					self.init().then(function(){
						if (self.postInit()){
							self.ready();
							def.resolve(self);
						} else {
							def.reject("post-init failed");
						}
					}).fail(def.reject);
				} else {
					def.reject("pre-init failed");
				}
			}).fail(function (err) {
				console.log("initialize failed", self, err);
				return self.destroy();
			}).promise();
		},
		/**
		 * @summary Occurs before the template is initialized.
		 * @memberof FooGallery.Template#
		 * @function preInit
		 * @param {(jQuery|HTMLElement|string)} [parent] - If no element was supplied to the constructor you must supply a parent element for the template to append itself to. This can be a jQuery object, HTMLElement or a CSS selector.
		 * @returns {boolean}
		 * @fires FooGallery.Template~"pre-init.foogallery"
		 */
		preInit: function (parent) {
			var self = this;
            if (self.destroying) return false;
			parent = _is.jq(parent) ? parent : $(parent);
			self.initializing = true;

			if (parent.length === 0 && self.$el.parent().length === 0) {
				return false;
			}
			if (self.$el.length === 0) {
				self.$el = self.create();
				self.el = self.$el.get(0);
				self._undo.create = true;
			}
			if (parent.length > 0) {
				self.$el.appendTo(parent);
			}

			var $sp;
			if (!_is.empty(self.opt.scrollParent) && ($sp = $(self.opt.scrollParent)).length !== 0){
				self.$scrollParent = $sp.is("html") ? $(document) : $sp;
			} else {
				self.$scrollParent = $(document);
			}
			self.$el.data(_.DATA_TEMPLATE, self);

			// at this point we have our container element free of pre-existing instances so let's bind any event listeners supplied by the .on option
			if (!_is.empty(self.opt.on)) {
				self.on(self.opt.on);
			}
			self._undo.classes = self.$el.attr("class");
			self._undo.style = self.$el.attr("style");

			// ensure the container has it's required CSS classes
			if (!self.$el.is(self.sel.container)) {
				self.$el.addClass(self.cls.container);
			}
			var selector = _utils.selectify(self.opt.classes);
			if (selector != null && !self.$el.is(selector)) {
				self.$el.addClass(self.opt.classes);
			}

			// if the container currently has no children make them
			if (self.$el.children().not(self.sel.item.elem).length === 0) {
				self.$el.append(self.createChildren());
				self._undo.children = true;
			}

			if (self.opt.protected){
				self.el.oncontextmenu = function(e){
					e.preventDefault();
					return false;
				};
			}

			/**
			 * @summary Raised before the template is fully initialized.
			 * @event FooGallery.Template~"pre-init.foogallery"
			 * @type {jQuery.Event}
			 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
			 * @param {FooGallery.Template} template - The template raising the event.
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
			return !self.trigger("pre-init").isDefaultPrevented();
		},
		/**
		 * @summary Occurs as the template is initialized.
		 * @memberof FooGallery.Template#
		 * @function init
		 * @returns {Promise}
		 * @fires FooGallery.Template~"init.foogallery"
		 */
		init: function(){
			var self = this;
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
			var e = self.trigger("init");
			if (e.isDefaultPrevented()) return _fn.reject("init default prevented");
			return self.items.fetch();
		},
		/**
		 * @summary Occurs after the template is initialized.
		 * @memberof FooGallery.Template#
		 * @function postInit
		 * @returns {boolean}
		 * @fires FooGallery.Template~"post-init.foogallery"
		 */
		postInit: function () {
			var self = this;
			if (self.destroying) return false;
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
			var e = self.trigger("post-init");
			if (e.isDefaultPrevented()) return false;
			self.state.init();
			if (self.pages) self.pages.init();
			$(window).on("popstate" + self.namespace, {self: self}, self.onWindowPopState);
			self.robserver.observe(self.el);
			return true;
		},
		/**
		 * @summary Occurs once the template is ready.
		 * @memberof FooGallery.Template#
		 * @function ready
		 * @returns {boolean}
		 * @fires FooGallery.Template~"ready.foogallery"
		 */
		ready: function(){
			var self = this;
            if (self.destroying) return false;
			self.initializing = false;
			self.initialized = true;
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
			self.trigger("ready");
			return true;
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
		create: function () {
			var self = this;
			return $("<div/>", {"id": self.id, "class": self.cls.container}).addClass(self.opt.classes);
		},
		/**
		 * @summary Create any container child elements for the template returning the jQuery object.
		 * @memberof FooGallery.Template#
		 * @function createChildren
		 * @returns {(jQuery|jQuery[]|HTMLElement|HTMLElement[])} A jQuery object to use as the container for the template.
		 * @description This method is called just prior to the {@link FooGallery.Template~"preinit.foogallery"|preinit} event if the container element has no children to allow templates to add any markup required.
		 */
		createChildren: function () {
			return $();
		},

		// #############
		// ## Destroy ##
		// #############

		/**
		 * @summary Destroy the template.
		 * @memberof FooGallery.Template#
		 * @function destroy
		 * @param {boolean} [preserveState=false] - If set to true any existing state is left intact on the URL.
		 * @returns {Promise}
		 * @description Once this method is called it can not be stopped and the template will be destroyed.
		 * @fires FooGallery.Template~"destroy.foogallery"
		 */
		destroy: function (preserveState) {
			var self = this, _super = self._super.bind(self);
            if (self.destroyed) return _fn.resolved;
            self.destroying = true;
            return $.Deferred(function (def) {
                if (self.initializing && _is.promise(self._initialize)) {
                    self._initialize.always(function () {
                        self.destroying = false;
                        self.doDestroy(preserveState);
                        def.resolve();
                    });
                } else {
                    self.destroying = false;
                    self.doDestroy(preserveState);
                    def.resolve();
                }
            }).then(function(){
            	_super();
			}).promise();
		},
        doDestroy: function(preserveState){
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
            self.trigger("destroy");
			self.robserver.disconnect();
            $(window).off(self.namespace);
            self.state.destroy(preserveState);
            if (self.filter) self.filter.destroy();
            if (self.pages) self.pages.destroy();
            self.items.destroy();
            if (!_is.empty(self.opt.on)) {
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
            self.trigger("destroyed");
            self.$el.removeData(_.DATA_TEMPLATE);

            if (_is.empty(self._undo.classes)) self.$el.removeAttr("class");
            else self.$el.attr("class", self._undo.classes);

            if (_is.empty(self._undo.style)) self.$el.removeAttr("style");
            else self.$el.attr("style", self._undo.style);

            if (self._undo.children) {
                self.destroyChildren();
            }
            if (self._undo.create) {
                self.$el.remove();
            }
            self.$el = self.state = self.items = self.pages = null;
            self.destroyed = true;
            self.initializing = false;
            self.initialized = false;
        },
		/**
		 * @summary If the {@link FooGallery.Template#createChildren|createChildren} method is used to generate custom elements for a template this method should also be overridden and used to destroy them.
		 * @memberof FooGallery.Template#
		 * @function destroyChildren
		 * @description This method is called just after the {@link FooGallery.Template~"destroyed.foogallery"|destroyed} event to allow templates to remove any markup created in the {@link FooGallery.Template#createChildren|createChildren} method.
		 */
		destroyChildren: function(){
			// does nothing for the base template
		},

		// ################
		// ## Load Items ##
		// ################

		/**
		 * @summary Gets all available items.
		 * @description This takes into account if paging is enabled and will return only the current pages' items.
		 * @memberof FooGallery.Template#
		 * @function getAvailable
		 * @returns {FooGallery.Item[]} An array of {@link FooGallery.Item|items}.
		 */
		getAvailable: function () {
			return this.pages ? this.pages.available() : this.items.available();
		},

		// #############
		// ## Utility ##
		// #############

		/**
		 * @memberof FooGallery.Template#
		 * @function layout
		 */
		layout: function (width) {
			var self = this;
			if (self._initialize === null) return;
			if (!_is.number(width)){
				var rect = self.el.getBoundingClientRect();
				width = rect.width;
			}
			if (width === 0 || self._checkWidth(width)) return;

			self.lastWidth = width;

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
			self.trigger("layout", [width]);
		},
		/**
		 * @summary This method was added to prevent an infinite loop in the ResizeObserver.
		 * @memberof FooGallery.Template#
		 * @function _checkWidth
		 * @description When the viewport has no scrollbar by default and is then resized down until the gallery layout requires a scrollbar
		 * to show. There could be an infinite loop as follows:
		 * 1. No scrollbar shown, layout occurs, scrollbar is then required.
		 * 2. Scrollbar is shown, triggers ResizeObserver, layout occurs, scrollbar is no longer required, loops back round to #1.
		 * To work around this the Template now has a 100ms two width memory and if the same size appears the layout is aborted exiting the loop.
		 * @param {Number} width - The current width of the gallery.
		 * @returns {boolean} True if the layout should be allowed to proceed.
		 * @private
		 */
		_checkWidth: function(width){
			var self = this, exists;
			if (!(exists = (self._layoutWidths.indexOf(width) !== -1))){
				self._layoutWidths.unshift(width);
				self._layoutWidths.splice(2, self._layoutWidths.length - 2);
				if (self._layoutTimeout != null) clearTimeout(self._layoutTimeout);
				self._layoutTimeout = setTimeout(function(){
					self._layoutWidths.splice(0, self._layoutWidths.length);
				}, 100);
			}
			return exists;
		},

		/**
		 * @summary Gets a specific type of CSS class from the template.
		 * @memberof FooGallery.Template#
		 * @function
		 * @name getCSSClass
		 * @param {string} type - The specific type of CSS class to retrieve.
		 * @param {string} [def=""] - The default value to return if no CSS class is found.
		 * @returns {string}
		 */
		getCSSClass: function(type, def){
			def = _is.empty(def) ? "" : def;
			var regex = type instanceof RegExp ? type : (_is.string(type) && this.opt.regex.hasOwnProperty(type) ? this.opt.regex[type] : null),
				className = (this.$el.prop("className") || ''),
				match = regex != null ? className.match(regex) : null;
			return match != null && match.length >= 2 ? match[1] : def;
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
		onWindowPopState: function (e) {
			var self = e.data.self, state = e.originalEvent.state;
			if (!_is.empty(state) && state.id === self.id) {
				self.state.set(state);
			}
		}
	});

	_.template.register("core", _.Template, {
		id: null,
		type: "core",
		classes: "",
		on: {},
		lazy: true,
		items: [],
		scrollParent: null,
		delay: 0,
		throttle: 50,
		shortpixel: false,
		srcset: "data-srcset-fg",
		src: "data-src-fg",
		protected: false,
		template: {},
		regex: {
			theme: /(?:\s|^)(fg-(?:light|dark|custom))(?:\s|$)/,
			loadingIcon: /(?:\s|^)(fg-loading-(?:default|bars|dots|partial|pulse|trail))(?:\s|$)/,
			hoverIcon: /(?:\s|^)(fg-hover-(?:zoom|zoom2|zoom3|plus|circle-plus|eye|external|tint))(?:\s|$)/,
			videoIcon: /(?:\s|^)(fg-video-(?:default|1|2|3|4))(?:\s|$)/,
			border: /(?:\s|^)(fg-border-(?:thin|medium|thick))(?:\s|$)/,
			hoverColor: /(?:\s|^)(fg-hover-(?:colorize|grayscale))(?:\s|$)/,
			hoverScale: /(?:\s|^)(fg-hover-scale)(?:\s|$)/,
			stickyVideoIcon: /(?:\s|^)(fg-video-sticky)(?:\s|$)/,
			insetShadow: /(?:\s|^)(fg-shadow-inset-(?:small|medium|large))(?:\s|$)/,
			filter: /(?:\s|^)(fg-filter-(?:1977|amaro|brannan|clarendon|earlybird|lofi|poprocket|reyes|toaster|walden|xpro2|xtreme))(?:\s|$)/
		}
	}, {
		container: "foogallery"
	}, {}, -100);

	/**
	 * @summary An object containing all the core template options.
	 * @typedef {object} FooGallery.Template~Options
	 * @property {?string} [id=null] - The id for the template. This is only required if creating a template without a pre-existing container element that has an `id` attribute.
	 * @property {string} [type="core"] - The type of template to load. If no container element exists to parse the type from this must be supplied so the correct type of template is loaded.
	 * @property {string} [classes=""] - A space delimited string of any additional CSS classes to append to the container element of the template.
	 * @property {object} [on={}] - An object containing any template events to bind to.
	 * @property {boolean} [lazy=true] - Whether or not to enable lazy loading of images.
	 * @property {boolean} [protected=false] - Whether or not to enable basic image protection.
	 * @property {(FooGallery.Item~Options[]|FooGallery.Item[]| string)} [items=[]] - An array of items to load when required. A url can be provided and the items will be fetched using an ajax call, the response should be a properly formatted JSON array of {@link FooGallery.Item~Options|item} object.
	 * @property {string} [scrollParent=null] - The selector used to bind to the scroll parent for the gallery. If not supplied the template will attempt to find the element itself.
	 * @property {number} [delay=0] - The number of milliseconds to delay the initialization of a template.
	 * @property {number} [throttle=50] - The number of milliseconds to wait once scrolling has stopped before performing any work.
	 * @property {string} [src="data-src-fg"] - The name of the attribute to retrieve an images src url from.
	 * @property {string} [srcset="data-srcset-fg"] - The name of the attribute to retrieve an images srcset url from.
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
(function(_, _utils, _is){

	_.Component = _utils.EventClass.extend(/** @lend FooGallery.Component */{
		/**
		 * @summary The base class for all child components of a {@link FooGallery.Template|template}.
		 * @constructs
		 * @param {FooGallery.Template} template - The template creating the component.
		 * @augments FooGallery.utils.EventClass
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(template){
			this._super();
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
			this._super();
		}
	});

	/**
	 * @summary A factory for registering and creating basic gallery components.
	 * @memberof FooGallery
	 * @name components
	 * @type {FooGallery.Factory}
	 */
	_.components = new _.Factory();

})(
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _utils, _is, _str, _obj){

	_.State = _.Component.extend(/** @lends FooGallery.State.prototype */{
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
			 * @summary The current state of the template.
			 * @memberof FooGallery.State#
			 * @name current
			 * @type {{item: null, page: number, filter: []}}
			 */
			self.current = {
				filter: [],
				page: 0,
				item: null
			};
			/**
			 * @summary Which method of the history API to use by default when updating the state.
			 * @memberof FooGallery.State#
			 * @name pushOrReplace
			 * @type {string}
			 * @default "replace"
			 */
			self.pushOrReplace = self.isPushOrReplace(self.opt.pushOrReplace) ? self.opt.pushOrReplace : "replace";

			self.defaultMask = "foogallery-gallery-{id}";

			var id = _str.escapeRegExp(self.tmpl.id),
				masked = _str.escapeRegExp(self.getMasked()),
				values = _str.escapeRegExp(self.opt.values),
				pair = _str.escapeRegExp(self.opt.pair);
			/**
			 * @summary An object containing regular expressions used to test and parse a hash value into a state object.
			 * @memberof FooGallery.State#
			 * @name regex
			 * @type {{exists: RegExp, masked: RegExp, values: RegExp}}
			 * @readonly
			 * @description The regular expressions contained within this object are specific to this template and are created using the template {@link FooGallery.Template#id|id} and the delimiters from the {@link FooGallery.State#opt|options}.
			 */
			self.regex = {
				exists: new RegExp("^#"+id+"\\"+values+".+?"),
				masked: new RegExp("^#"+masked+"\\"+values+".+?"),
				values: new RegExp("(\\w+)"+pair+"([^"+values+"]+)", "g")
			};
			/**
			 * @summary Whether or not the component listens to the popstate event.
			 * @memberof FooGallery.State#
			 * @name usePopState
			 * @type {boolean}
			 */
			self.usePopState = self.opt.usePopState;
			// force context
			self.onPopState = self.onPopState.bind(self);
		},
		init: function(){
			var self = this;
			self.set(self.initial());
			if (self.enabled && self.apiEnabled && self.usePopState) window.addEventListener( 'popstate', self.onPopState );
		},
		/**
		 * @summary Destroy the component clearing any current state from the url and preparing it for garbage collection.
		 * @memberof FooGallery.State#
		 * @function destroy
		 * @param {boolean} [preserve=false] - If set to true any existing state is left intact on the URL.
		 */
		destroy: function(preserve){
			var self = this;
			if (self.enabled && self.apiEnabled && self.usePopState) window.removeEventListener( 'popstate', self.onPopState );
			if (!preserve) self.clear();
			self.opt = self.regex = {};
			self._super();
		},
		getIdNumber: function(){
			return this.tmpl.id.match(/\d+/g)[0];
		},
		getMasked: function(){
			var self = this, mask = _str.contains(self.opt.mask, "{id}") ? self.opt.mask : self.defaultMask;
			return _str.format(mask, {id: self.getIdNumber()});
		},
		/**
		 * @summary Check if the supplied value is `"push"` or `"replace"`.
		 * @memberof FooGallery.State#
		 * @function isPushOrReplace
		 * @param {*} value - The value to check.
		 * @returns {boolean}
		 */
		isPushOrReplace: function(value){
			return _utils.inArray(value, ["push","replace"]) !== -1;
		},
		/**
		 * @summary Check if the current url contains state for this template.
		 * @memberof FooGallery.State#
		 * @function exists
		 * @returns {boolean}
		 */
		exists: function(){
			this.regex.values.lastIndex = 0; // reset the index as we use the g flag
			return (this.regex.exists.test(location.hash) || this.regex.masked.test(location.hash)) && this.regex.values.test(location.hash);
		},
		/**
		 * @summary Parse the current url returning an object containing all values for the template.
		 * @memberof FooGallery.State#
		 * @function parse
		 * @returns {object}
		 * @description This method always returns an object, if successful the object contains properties otherwise it is just a plain empty object. For this method to be successful the current template {@link FooGallery.Template#id|id} must match the one from the url.
		 */
		parse: function(){
			var self = this, tmpl = self.tmpl, state = {};
			if (self.exists()){
				if (self.enabled){
					state.id = self.tmpl.id;
					self.regex.values.lastIndex = 0;
					var pairs = location.hash.match(self.regex.values);
					$.each(pairs, function(i, pair){
						var parts = pair.split(self.opt.pair), val;
						if (parts.length === 2){
							switch(parts[0]){
								case self.opt.itemKey:
									val = tmpl.items.fromHash(parts[1]);
									if (val !== null) state.item = val;
									break;
								case self.opt.pageKey:
									if (tmpl.pages){
										val = tmpl.pages.fromHash(parts[1]);
										if (val !== null) state.page = val;
									}
									break;
								case self.opt.filterKey:
									if (tmpl.filter){
										val = tmpl.filter.fromHash(parts[1]);
										if (val !== null) state.filter = val;
									}
									break;
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
			var self = this, tmpl = self.tmpl;
			if (_is.hash(state)){
				var hash = [], val = tmpl.items.toHash(state.item);
				if (val !== null) hash.push(self.opt.itemKey + self.opt.pair + val);

				if (!!tmpl.filter){
					val = tmpl.filter.toHash(state.filter);
					if (val !== null) hash.push(self.opt.filterKey + self.opt.pair + val);
				}
				if (!!tmpl.pages){
					val = tmpl.pages.toHash(state.page);
					if (val !== null) hash.push(self.opt.pageKey + self.opt.pair + val);
				}
				if (hash.length > 0){
					hash.unshift("#"+self.getMasked());
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
				var hash = self.hashify(state), empty = _is.empty(hash), hs = _obj.extend({}, state, {item: state.item instanceof _.Item ? state.item.id : state.item});
				history.replaceState(empty ? null : hs, "", empty ? location.pathname + location.search : hash);
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
				var hash = self.hashify(state), empty = _is.empty(hash), hs = _obj.extend({}, state, {item: state.item instanceof _.Item ? state.item.id : state.item});
				history.pushState(empty ? null : hs, "", empty ? location.pathname + location.search : hash);
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
			var self = this, state = self.parse();
			if (_is.empty(state)){
				return self.get();
			}
			return _obj.extend({ filter: [], page: 1, item: null }, state);
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
			var self = this, tmpl = self.tmpl, state = {}, val;
			if (item instanceof _.Item) state.item = item;
			if (!!tmpl.filter){
				val = tmpl.filter.getState();
				if (val !== null) state.filter = val;
			}
			if (!!tmpl.pages){
				val = tmpl.pages.getState();
				if (val !== null) state.page = val;
			}
			return _obj.extend({ filter: [], page: 1, item: null }, state);
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
				var obj = _obj.extend({ filter: [], page: 1, item: null }, state);
				tmpl.items.reset();
				var e = tmpl.trigger("before-state", [obj]);
				if (!e.isDefaultPrevented()){
					if (!!tmpl.filter){
						tmpl.filter.setState(obj);
					}
					if (!!tmpl.pages){
						tmpl.pages.setState(obj);
					} else {
						var available = tmpl.items.available();
						if (!tmpl.items.isAll(available)){
							var notAvailable = tmpl.items.not(available);
							tmpl.items.detach(notAvailable);
						}
						tmpl.items.create(available, true);
					}
					if (obj.item instanceof _.Item){
						if (self.opt.scrollTo) {
							obj.item.scrollTo();
						}
						if (!_is.empty(state.item)){
							state.item = null;
							self.replace(state);
						}
					}
					self.current = obj;
					tmpl.trigger("after-state", [obj]);
				}
			}
		},
		onPopState: function(e){
			var self = this, parsed = self.parse();
			if ( Object.keys( parsed ).length ){
				self.set( parsed );
			}
		}
	});

	_.template.configure("core", {
		state: {
			enabled: false,
			scrollTo: true,
			pushOrReplace: "replace",
			mask: "foogallery-gallery-{id}",
			usePopState: true,
			values: "/",
			pair: ":",
			array: "+",
			arraySeparator: ",",
			itemKey: "i",
			filterKey: "f",
			pageKey: "p"
		}
	});

	// register the component
	_.components.register("state", _.State);

	/**
	 * @summary An object containing the state options for the template.
	 * @typedef {object} FooGallery.State~Options
	 * @property {boolean} [enabled=false] - Whether state is enabled for the template.
	 * @property {boolean} [scrollTo=true] - Whether the page is scrolled to the current state item.
	 * @property {string} [pushOrReplace="replace"] - Which method of the history API to use by default when updating the state.
	 * @property {string} [mask="foogallery-gallery-{id}"] - The mask used to generate the full ID from just the ID number.
	 * @property {boolean} [usePopState=true] - Whether state listens to the 'popstate' event and updates the gallery.
	 * @property {string} [values="/"] - The delimiter used between key value pairs in the hash.
	 * @property {string} [pair=":"] - The delimiter used between a key and a value in the hash.
	 * @property {string} [array="+"] - The delimiter used for array values in the hash.
	 * @property {string} [arraySeparator=","] - The delimiter used to separate multiple array values in the hash.
	 */

	/**
	 * @summary An object used to store the state of a template.
	 * @typedef {object} FooGallery~State
	 * @property {number} [page] - The current page number.
	 * @property {string[]} [filter] - The current filter array.
	 * @property {?FooGallery.Item} [item] - The currently selected item.
	 */

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.str,
	FooGallery.utils.obj
);
(function ($, _, _utils, _is, _fn, _obj) {

	_.Items = _.Component.extend(/** @lends FooGallery.Items.prototype */{
		/**
		 * @summary This class controls everything related to items and serves as the base class for the various paging types.
		 * @memberof FooGallery
		 * @constructs Items
		 * @param {FooGallery.Template} template - The template for this component.
		 * @augments FooGallery.Component
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function (template) {
			var self = this;
			self.ALLOW_CREATE = true;
			self.ALLOW_APPEND = true;
			self.LAYOUT_AFTER_LOAD = true;
			/**
			 * @ignore
			 * @memberof FooGallery.Items#
			 * @function _super
			 */
			self._super(template);
			self._typeRegex = /(?:^|\s)?fg-type-(.*?)(?:$|\s)/;
			self._fetched = null;
			self._all = [];
			self._available = [];
			self._unavailable = [];
			self._observed = new Map();

			// add the .all caption selector
			var cls = self.tmpl.cls.item.caption;
			self.tmpl.sel.item.caption.all = _utils.selectify([cls.elem, cls.inner, cls.title, cls.description]);

			self._wait = [];
			self._layoutTimeout = null;
			self.iobserver = new IntersectionObserver(function(entries){
				if (!self.tmpl.destroying && !self.tmpl.destroyed){
					if ( self.LAYOUT_AFTER_LOAD ) clearTimeout(self._layoutTimeout);
					entries.forEach(function(entry){
						if (entry.isIntersecting){
							var item = self._observed.get(entry.target);
							if (item instanceof _.Item){
								self._wait.push(item.load());
							}
						}
					});
					if ( self.LAYOUT_AFTER_LOAD ){
						self._layoutTimeout = setTimeout(function(){
							if (self._wait.length > 0){
								_fn.allSettled(self._wait.splice(0)).then(function(){
									self.tmpl.layout();
								});
							}
						}, 100);
					}
				}
			});
		},
		fromHash: function(hash){
			return this.find(this._all, function(item){ return item.id === hash; });
		},
		toHash: function(value){
			return value instanceof _.Item ? value.id : null;
		},
		destroy: function () {
			var self = this, items = self.all(), destroyed = [];
			self.iobserver.disconnect();
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
				self.tmpl.trigger("destroy-items", [items]);
				destroyed = $.map(items, function (item) {
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
				if (destroyed.length > 0) self.tmpl.trigger("destroyed-items", [destroyed]);
				// should we handle a case where the destroyed.length != items.length??
			}
			self._fetched = null;
			self._all = [];
			self._available = [];
			self._unavailable = [];
			self._observed.clear();
			self._super();
		},
		fetch: function (refresh) {
			var self = this;
			if (!refresh && _is.promise(self._fetched)) return self._fetched;
			var itemsId = self.tmpl.id + "_items",
				selectors = self.tmpl.sel,
				option = self.tmpl.opt.items,
				def = $.Deferred();

			var items = self.make(self.tmpl.$el.find(selectors.item.elem));

			if (!_is.empty(option)) {
				if (_is.array(option)) {
					items.push.apply(items, self.make(option));
					def.resolve(items);
				} else if (_is.string(option)) {
					$.get(option).then(function (response) {
						items.push.apply(items, self.make(response));
						def.resolve(items);
					}, function (jqXHR, textStatus, errorThrown) {
						console.log("FooGallery: GET items error.", option, jqXHR, textStatus, errorThrown);
						def.resolve(items);
					});
				} else {
					def.resolve(items);
				}
			} else {
				if (_is.array(window[itemsId])) {
					items.push.apply(items, self.make(window[itemsId]));
				}
				def.resolve(items);
			}
			def.then(function (items) {
				self.setAll(items);
			});
			return self._fetched = def.promise();
		},
		toJSON: function(all){
			var items = all ? this.all() : this.available();
			return items.map(function(item){
				return item.toJSON();
			});
		},
		all: function () {
			return this._all.slice();
		},
		count: function (all) {
			return all ? this.all().length : this.available().length;
		},
		available: function (where) {
			if (_is.fn(where)){
				return this._available.filter(where, this);
			}
			return this._available.slice();
		},
		unavailable: function (where) {
			if (_is.fn(where)){
				return this._unavailable.filter(where, this);
			}
			return this._unavailable.slice();
		},
		setAll: function (items) {
			this._all = _is.array(items) ? items : [];
			this._all.forEach(function(item, i){
				item.index = i;
				if (_is.empty(item.id)) item.id = (i + 1) + "";
			});
			this._available = this.all();
			this._unavailable = [];
		},
		setAvailable: function (items) {
			var self = this;
			self._available = _is.array(items) ? items : [];
			if (self._all.length !== self._available.length){
				self._unavailable = self._all.filter(function(item){
					return self._available.indexOf(item) === -1;
				});
			} else {
				self._unavailable = [];
			}
		},
		reset: function () {
			this.setAvailable(this.all());
		},
		find: function(items, where){
			where = _is.fn(where) ? where : function(){ return true; };
			if (_is.array(items)){
				for (var i = 0, l = items.length; i < l; i++){
					if (where.call(this, items[i]) === true){
						return items[i];
					}
				}
			}
			return null;
		},
		not: function(items){
			var all = this.all();
			if (_is.array(items)){
				return all.filter(function(item){
					return items.indexOf(item) === -1;
				});
			}
			return all;
		},
		isAll: function(items){
			if (_is.array(items)){
				return this._all.length === items.length;
			}
			return false;
		},
		first: function(where){
			return this.find(this._available, where);
		},
		last: function(where){
			return this.find(this._available.slice().reverse(), where);
		},
		next: function(item, where, loop){
			if (!(item instanceof _.Item)) return null;
			loop = _is.boolean(loop) ? loop : false;
			var items = this._available.slice(),
				index = items.indexOf(item);
			if (index !== -1){
				var remainder = items.slice(0, index);
				items = items.slice(index + 1);
				if (loop){
					items = items.concat(remainder);
				}
				return this.find(items, where);
			}
			return null;
		},
		prev: function(item, where, loop){
			if (!(item instanceof _.Item)) return null;
			loop = _is.boolean(loop) ? loop : false;
			var items = this._available.slice().reverse(),
				index = items.indexOf(item);
			if (index !== -1){
				var remainder = items.slice(0, index);
				items = items.slice(index + 1);
				if (loop){
					items = items.concat(remainder);
				}
				return this.find(items, where);
			}
			return null;
		},
		get: function( indexOrElement, all ){
			const self = this;
			const items = all ? self._all : self._available;
			if ( _is.number( indexOrElement ) ){
				if ( indexOrElement >= 0 && indexOrElement < self._all.length ){
					return items[ indexOrElement ];
				}
				return null;
			}
			return self.find( items, function( item ){
				return item.el === indexOrElement;
			} );
		},
		indexOf: function( item, all ){
			return ( all ? this._all : this._available ).indexOf( item );
		},
		/**
		 * @summary Filter the supplied `items` and return only those that can be created.
		 * @memberof FooGallery.Items#
		 * @function creatable
		 * @param {FooGallery.Item[]} items - The items to filter.
		 * @returns {FooGallery.Item[]}
		 */
		creatable: function (items) {
			return this.ALLOW_CREATE && _is.array(items) ? $.map(items, function (item) {
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
		appendable: function (items) {
			return this.ALLOW_APPEND && _is.array(items) ? $.map(items, function (item) {
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
		detachable: function (items) {
			return _is.array(items) ? $.map(items, function (item) {
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
		jquerify: function (items) {
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
		make: function (items) {
			var self = this, made = [];
			if (_is.jq(items) || _is.array(items)) {
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
				var e = self.tmpl.trigger("make-items", [arr]);
				if (!e.isDefaultPrevented()) {
					made = $.map(arr, function (obj) {
						var type = self.type(obj), opt = _obj.extend(_is.hash(obj) ? obj : {}, {type: type});
						var item = _.components.make(type, self.tmpl, opt);
						if (_is.element(obj)) {
							if (item.parse(obj)) {
								parsed.push(item);
								if (!self.ALLOW_APPEND) item.detach();
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
				if (made.length > 0) self.tmpl.trigger("made-items", [made]);

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
				if (parsed.length > 0) self.tmpl.trigger("parsed-items", [parsed]);
			}
			return made;
		},
		type: function (objOrElement) {
			var self = this, type;
			if (_is.hash(objOrElement)) {
				type = objOrElement.type;
			} else if (_is.element(objOrElement)) {
				var match = objOrElement.className.match(self._typeRegex);
				if (match !== null && match.length === 2){
					type = match[1];
				}
				// var $el = $(objOrElement), item = this.tmpl.sel.item;
				// type = $el.find(item.anchor).data("type");
			}
			return _is.string(type) && _.components.contains(type) ? type : "image";
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
		create: function (items, append) {
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
				var e = self.tmpl.trigger("create-items", [creatable]);
				if (!e.isDefaultPrevented()) {
					created = $.map(creatable, function (item) {
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
				if (created.length > 0) self.tmpl.trigger("created-items", [created]);
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
		append: function (items) {
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
				var e = self.tmpl.trigger("append-items", [appendable]);
				if (!e.isDefaultPrevented()) {
					appended = $.map(appendable, function (item) {
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
				if (appended.length > 0) self.tmpl.trigger("appended-items", [appended]);
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
		detach: function (items) {
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
				var e = self.tmpl.trigger("detach-items", [detachable]);
				if (!e.isDefaultPrevented()) {
					detached = $.map(detachable, function (item) {
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
				if (detached.length > 0) self.tmpl.trigger("detached-items", [detached]);
			}
			return detached;
		},
		observe: function(item){
			var self = this;
			if (self.iobserver && item.isCreated && item.isAttached && (!item.isLoading || !item.isLoaded)){
				self.iobserver.observe(item.el);
				self._observed.set(item.el, item);
			}
		},
		unobserve: function(item){
			var self = this;
			if (self.iobserver) {
				self.iobserver.unobserve(item.el);
				self._observed.delete(item.el);
			}
		}
	});

	_.components.register("items", _.Items);

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is,
		FooGallery.utils.fn,
		FooGallery.utils.obj
);
(function ($, _, _utils, _is, _fn, _obj, _str) {

	_.Item = _.Component.extend(/** @lends FooGallery.Item.prototype */{
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
		construct: function (template, options) {
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
			self.opt = _obj.extend({}, template.opt.item, options);

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
			 * @summary Whether or not the item has been destroyed and can not be used.
			 * @memberof FooGallery.Item#
			 * @name isDestroyed
			 * @type {boolean}
			 * @readonly
			 */
			self.isDestroyed = false;
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
			 * @summary Whether or not this items image is a <picture> element.
			 * @memberof FooGallery.Item#
			 * @name isPicture
			 * @type {boolean}
			 * @readonly
			 */
			self.isPicture = false;
			/**
			 * @memberof FooGallery.Item#
			 * @name $el
			 * @type {?jQuery}
			 */
			self.$el = null;
			/**
			 * @memberof FooGallery.Item#
			 * @name el
			 * @type {?Element}
			 */
			self.el = null;
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
			 * @name $overlay
			 * @type {?jQuery}
			 */
			self.$overlay = null;
			/**
			 * @memberof FooGallery.Item#
			 * @name $wrap
			 * @type {?jQuery}
			 */
			self.$wrap = null;
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
			/**
			 * @memberof FooGallery.Item#
			 * @name $loader
			 * @type {?jQuery}
			 */
			self.$loader = null;

			/**
			 * @memberof FooGallery.Item#
			 * @name index
			 * @type {number}
			 * @default -1
			 */
			self.index = -1;
			/**
			 * @memberof FooGallery.Item#
			 * @name type
			 * @type {string}
			 */
			self.type = self.opt.type;
			/**
			 * @memberof FooGallery.Item#
			 * @name id
			 * @type {string}
			 */
			self.id = self.opt.id;
			/**
			 * @memberof FooGallery.Item#
			 * @name productId
			 * @type {string}
			 */
			self.productId = self.opt.productId;
			/**
			 * @memberof FooGallery.Item#
			 * @name href
			 * @type {string}
			 */
			self.href = self.opt.href;
			/**
			 * @memberof FooGallery.Item#
			 * @name placeholder
			 * @type {string}
			 */
			self.placeholder = self.opt.placeholder;
			/**
			 * @memberof FooGallery.Item#
			 * @name src
			 * @type {string}
			 */
			self.src = self.opt.src;
			/**
			 * @memberof FooGallery.Item#
			 * @name srcset
			 * @type {string}
			 */
			self.srcset = self.opt.srcset;
			/**
			 * @memberof FooGallery.Item#
			 * @name sources
			 * @type {Object[]}
			 */
			self.sources = self.opt.sources;
			/**
			 * @memberof FooGallery.Item#
			 * @name width
			 * @type {number}
			 */
			self.width = self.opt.width;
			/**
			 * @memberof FooGallery.Item#
			 * @name height
			 * @type {number}
			 */
			self.height = self.opt.height;
			/**
			 * @memberof FooGallery.Item#
			 * @name title
			 * @type {string}
			 */
			self.title = self.opt.title;
			/**
			 * @memberof FooGallery.Item#
			 * @name alt
			 * @type {string}
			 */
			self.alt = self.opt.alt;
			/**
			 * @memberof FooGallery.Item#
			 * @name caption
			 * @type {string}
			 */
			self.caption = self.opt.caption;
			/**
			 * @memberof FooGallery.Item#
			 * @name description
			 * @type {string}
			 */
			self.description = self.opt.description;
			/**
			 * @memberof FooGallery.Item#
			 * @name attrItem
			 * @type {FooGallery.Item~Attributes}
			 */
			self.attr = self.opt.attr;
			/**
			 * @memberof FooGallery.Item#
			 * @name tags
			 * @type {string[]}
			 */
			self.tags = self.opt.tags;
			/**
			 * @memberof FooGallery.Item#
			 * @name maxCaptionLength
			 * @type {number}
			 */
			self.maxCaptionLength = self.opt.maxCaptionLength;
			/**
			 * @memberof FooGallery.Item#
			 * @name maxDescriptionLength
			 * @type {number}
			 */
			self.maxDescriptionLength = self.opt.maxDescriptionLength;
			/**
			 * @memberof FooGallery.Item#
			 * @name showCaptionTitle
			 * @type {boolean}
			 */
			self.showCaptionTitle = self.opt.showCaptionTitle;
			/**
			 * @memberof FooGallery.Item#
			 * @name showCaptionDescription
			 * @type {boolean}
			 */
			self.showCaptionDescription = self.opt.showCaptionDescription;
			/**
			 * @memberof FooGallery.Item#
			 * @name noLightbox
			 * @type {boolean}
			 */
			self.noLightbox = self.opt.noLightbox;
			/**
			 * @memberof FooGallery.Item#
			 * @name panelHide
			 * @type {boolean}
			 */
			self.panelHide = self.opt.panelHide;
			/**
			 * @memberof FooGallery.Item#
			 * @name exif
			 * @type {Object}
			 */
			self.exif = self.opt.exif;
			/**
			 * @memberof FooGallery.Item#
			 * @name hasExif
			 * @type {boolean}
			 */
			self.hasExif = _is.exif(self.exif);
			/**
			 * @memberof FooGallery.Item#
			 * @name ribbon
			 * @type {FooGallery.Item~Ribbon}
			 */
			self.ribbon = self.opt.ribbon;
			/**
			 * @memberof FooGallery.Item#
			 * @name hasRibbon
			 * @type {boolean}
			 */
			self.hasRibbon = _is.hash(self.ribbon) && _is.string(self.ribbon.text) && _is.string(self.ribbon.type);
			/**
			 * @memberof FooGallery.Item#
			 * @name buttons
			 * @type {FooGallery.Item~Button[]}
			 */
			self.buttons = self.opt.buttons;
			/**
			 * @summary This property is used to store the promise created when loading an item for the first time.
			 * @memberof FooGallery.Item#
			 * @name _load
			 * @type {?Promise}
			 * @private
			 */
			self._load = null;
			/**
			 * @summary This property is used to store the init state of an item the first time it is parsed and is used to reset state during destroy.
			 * @memberof FooGallery.Item#
			 * @name _undo
			 * @type {Object}
			 * @private
			 */
			self._undo = {
				classes: "",
				style: "",
				placeholder: false
			};
		},
		/**
		 * @summary Destroy the item preparing it for garbage collection.
		 * @memberof FooGallery.Item#
		 * @function destroy
		 */
		destroy: function () {
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
			 * 			// once all destroy work is complete you must set isDestroyed to true
			 * 			item.isDestroyed = true;
			 * 		}
			 * 	}
			 * });
			 */
			var e = self.tmpl.trigger("destroy-item", [self]);
			if (!e.isDefaultPrevented()) {
				self.isDestroyed = self.doDestroyItem();
			}
			if (self.isDestroyed) {
				/**
				 * @summary Raised after an item has been destroyed.
				 * @event FooGallery.Template~"destroyed-item.foogallery"
				 * @type {jQuery.Event}
				 * @param {jQuery.Event} event - The jQuery.Event object for the current event.
				 * @param {FooGallery.Template} template - The template raising the event.
				 * @param {FooGallery.Item} item - The item that was destroyed.
				 * @example {@caption To listen for this event and perform some action when it occurs you would bind to it as follows.}
				 * $(".foogallery").foogallery({
					 * 	on: {
					 * 		"destroyed-item.foogallery": function(event, template, item){
					 * 			// do something
					 * 		}
					 * 	}
					 * });
				 */
				self.tmpl.trigger("destroyed-item", [self]);
				// call the original method that simply nulls the tmpl property
				self._super();
			}
			return self.isDestroyed;
		},
		/**
		 * @summary Performs the actual destroy logic for the item.
		 * @memberof FooGallery.Item#
		 * @function doDestroyItem
		 * @returns {boolean}
		 */
		doDestroyItem: function () {
			var self = this;
			if (self.isParsed) {
				self.$anchor.add(self.$caption).off("click.foogallery");
				self.append();

				self.tmpl.items.unobserve(self);

				if (_is.empty(self._undo.classes)) self.$el.removeAttr("class");
				else self.$el.attr("class", self._undo.classes);

				if (_is.empty(self._undo.style)) self.$el.removeAttr("style");
				else self.$el.attr("style", self._undo.style);

				if (self._undo.placeholder && self.$image.prop("src") === self.placeholder) {
					self.$image.removeAttr("src");
				}
			} else if (self.isCreated) {
				self.detach();
				self.$el.remove();
			}
			return true;
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
		parse: function (element) {
			var self = this, $el = $(element);
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
			var e = self.tmpl.trigger("parse-item", [self, $el]);
			if (!e.isDefaultPrevented() && (self.isCreated = $el.is(self.sel.elem))) {
				self.isParsed = self.doParseItem($el);
				// We don't load the attributes when parsing as they are only ever used to create an item and if you're parsing it's already created.
			}
			if (self.isParsed) {
				if (!self.isLoaded) self.tmpl.items.observe(self);
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
				self.tmpl.trigger("parsed-item", [self]);
			}
			return self.isParsed;
		},
		/**
		 * @summary Performs the actual parse logic for the item.
		 * @memberof FooGallery.Item#
		 * @function doParseItem
		 * @param {jQuery} $el - The jQuery element to parse.
		 * @returns {boolean}
		 */
		doParseItem: function ($el) {
			var self = this,
				cls = self.cls,
				sel = self.sel,
				el = $el.get(0);

			self._undo.classes = $el.attr("class") || "";
			self._undo.style = $el.attr("style") || "";

			self.$el = $el.data(_.DATA_ITEM, self);
			self.el = el;
			self.$inner = $(el.querySelector(sel.inner));
			self.$anchor = $(el.querySelector(sel.anchor)).on("click.foogallery", {self: self}, self.onAnchorClick);
			self.$image = $(el.querySelector(sel.image));
			self.$caption = $(el.querySelector(sel.caption.elem)).on("click.foogallery", {self: self}, self.onCaptionClick);
			self.$overlay = $(el.querySelector(sel.overlay));
			self.$wrap = $(el.querySelector(sel.wrap));
			self.$loader = $(el.querySelector(sel.loader));

			if ( !self.$el.length || !self.$inner.length || !self.$anchor.length || !self.$image.length ){
				console.error("FooGallery Error: Invalid HTML markup. Check the item markup for additional elements or malformed HTML in the title or description.", self);
				self.isError = true;
				self.tmpl.trigger("error-item", [self]);
				if (self.$el.length !== 0){
					self.$el.remove();
				}
				return false;
			}

			self.isAttached = el.parentNode !== null;
			self.isLoading = self.$el.hasClass(cls.loading);
			self.isLoaded = self.$el.hasClass(cls.loaded);
			self.isError = self.$el.hasClass(cls.error);

			var data = self.$anchor.data();
			self.id = data.id || self.id;
			self.productId = data.productId || self.productId;
			self.tags = data.tags || self.tags;
			self.href = data.href || self.$anchor.attr('href') || self.href;

			var $img;
			if (self.$image.is("picture")){
				self.isPicture = true;
				self.sources = self.$image.find("source").map(function(i, source){
					return {
						srcset: source.getAttribute(self.tmpl.opt.srcset),
						type: source.getAttribute("type"),
						media: source.getAttribute("media"),
						sizes: source.getAttribute("sizes")
					};
				}).get();
				$img = self.$image.find("img");
			} else {
				$img = self.$image;
			}

			self.src = $img.attr(self.tmpl.opt.src) || self.src;
			self.srcset = $img.attr(self.tmpl.opt.srcset) || self.srcset;
			self.width = parseInt($img.attr("width")) || self.width;
			self.height = parseInt($img.attr("height")) || self.height;
			self.title = _.safeParse( $img.attr("title") || self.title );
			self.alt = _.safeParse( $img.attr("alt") || self.alt );

			self.caption = _.safeParse( data.title || data.captionTitle || self.caption );
			self.description = _.safeParse( data.description || data.captionDesc || self.description );
			self.noLightbox = self.$anchor.hasClass(cls.noLightbox);
			self.panelHide = self.$anchor.hasClass(cls.panelHide);
			if (_is.exif(data.exif)){
				self.exif = _obj.extend(self.exif, data.exif);
				self.hasExif = true;
			}
			// enforce the max lengths for the caption and description
			if (self.maxCaptionLength > 0){
				var title = _str.trimTo(self.caption, self.maxCaptionLength);
				if (title !== self.caption) {
					self.$caption.find(sel.caption.title).html(title);
				}
			}
			if (self.maxDescriptionLength){
				var desc = _str.trimTo(self.description, self.maxDescriptionLength);
				if (desc !== self.description) {
					self.$caption.find(sel.caption.description).html(desc);
				}
			}

			// if the image has no src url then set the placeholder
			var img = $img.get(0);
			if (!_is.string(img.src) || img.src.length === 0) {
				if (!_is.string(self.placeholder) || self.placeholder.length === 0){
					self.placeholder = self.createPlaceholder(self.width, self.height);
				}
				if (self.placeholder.length > 0){
					img.src = self.placeholder;
					self._undo.placeholder = true;
				}
			}

			var typeClass = self.getTypeClass();
			if (!self.$el.hasClass(typeClass)){
				self.$el.addClass(typeClass);
			}
			if (self.hasExif && !self.$el.hasClass(cls.exif)){
				self.$el.addClass(cls.exif);
			}
			if (self.isCreated && self.isAttached && !self.isLoading && !self.isLoaded && !self.isError && !self.$el.hasClass(cls.idle)) {
				self.$el.addClass(cls.idle);
			}

			self.doShortPixel();

			return true;
		},
		/**
		 * @summary Create the items' DOM elements and populate the corresponding properties.
		 * @memberof FooGallery.Item#
		 * @function create
		 * @returns {boolean}
		 * @fires FooGallery.Template~"create-item.foogallery"
		 * @fires FooGallery.Template~"created-item.foogallery"
		 */
		create: function () {
			var self = this;
			if (!self.isCreated && _is.string(self.href) && _is.string(self.src) && _is.number(self.width) && _is.number(self.height)) {
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
				var e = self.tmpl.trigger("create-item", [self]);
				if (!e.isDefaultPrevented()) {
					self.isCreated = self.doCreateItem();
				}
				if (self.isCreated) {
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
					self.tmpl.trigger("created-item", [self]);
				}
			}
			return self.isCreated;
		},
		/**
		 * @memberof FooGallery.Item#
		 * @function _setAttributes
		 * @param element
		 * @param attributes
		 * @private
		 */
		_setAttributes: function(element, attributes){
			Object.entries( attributes ).forEach( ( [ key, value ] ) => {
				if ( !_is.empty( value ) ) {
					if ( key === "class" ) {
						const classes = ( _is.array( value ) ? value : [ value ] )
							.flatMap( className => _is.string( className ) ? className.split( " " ) : [] )
							.map( p => p.trim() )
							.filter( Boolean );

						element.classList.add( ...classes );
					} else {
						element.setAttribute( key, _is.string( value ) ? value : JSON.stringify( value ) );
					}
				}
			} );
		},
		/**
		 * @summary Performs some checks for ShortPixel integration and WebP support.
		 * @memberof FooGallery.Item#
		 * @function doShortPixel
		 */
		doShortPixel: function(){
			var self = this;
			if (self.tmpl.opt.shortpixel && !_.supportsWebP){
				var regex = /([\/,+])to_webp([\/,+])/i;
				function spReplacer(match, $1, $2){
					return $1 === "/" || $2 === "/" ? "/" : $1;
				}
				self.href = self.href.replace(regex, spReplacer);
				self.src = self.src.replace(regex, spReplacer);
				self.srcset = self.srcset.replace(regex, spReplacer);
			}
		},
		/**
		 * @summary Performs the actual create logic for the item.
		 * @memberof FooGallery.Item#
		 * @function doCreateItem
		 * @returns {boolean}
		 */
		doCreateItem: function () {
			var self = this,
				cls = self.cls,
				attr = self.attr,
				exif = self.hasExif ? cls.exif : "";

			self.isLoaded = !self.tmpl.opt.lazy;
			self.isPicture = self.sources.length > 0;

			self.doShortPixel();

			var elem = document.createElement("div");
			self._setAttributes(elem, attr.elem);
			self._setAttributes(elem, {
				"class": [cls.elem, self.getTypeClass(), exif, self.isLoaded ? cls.loaded : cls.idle]
			});

			var inner = document.createElement("figure");
			self._setAttributes(inner, attr.inner);
			self._setAttributes(inner, {
				"class": cls.inner
			});

			var anchorClasses = [cls.anchor];
			if (self.noLightbox){
				anchorClasses.push(cls.noLightbox);
			}
			if (self.panelHide){
				anchorClasses.push(cls.panelHide);
			}

			var anchor = document.createElement("a");
			self._setAttributes(anchor, attr.anchor);
			self._setAttributes(anchor, {
				"class": anchorClasses.join(" "),
				"href": self.href,
				"data-id": self.id,
				"data-type": self.type,
				"data-title": self.caption,
				"data-description": self.description,
				"data-tags": self.tags,
				"data-exif": self.exif,
				"data-product-id": self.productId
			});

			if (!_is.string(self.placeholder) || self.placeholder.length === 0){
				self.placeholder = self.createPlaceholder(self.width, self.height);
			}

			var image = document.createElement("img");
			self._setAttributes(image, attr.image);
			var imageAttrs = {
				"class": cls.image,
				"src": self.placeholder,
				"width": self.width + "",
				"height": self.height + "",
				"title": self.title,
				"alt": self.alt
			};
			if (self.isLoaded){
				imageAttrs["src"] = self.src;
				imageAttrs["srcset"] = self.srcset;
			} else {
				imageAttrs[self.tmpl.opt.src] = self.src;
				imageAttrs[self.tmpl.opt.srcset] = self.srcset;
			}
			self._setAttributes(image, imageAttrs);

			var picture;
			if (self.isPicture){
				picture = document.createElement("picture");
				self._setAttributes(picture, attr.picture);
				self.sources.forEach(function(opt){
					var source = document.createElement("source"),
						sourceAttrs = {
							media: opt.media,
							sizes: opt.sizes,
							type: opt.type
						};
					if (self.isLoaded){
						sourceAttrs["srcset"] = opt.srcset;
					} else {
						sourceAttrs[self.tmpl.opt.srcset] = opt.srcset;
					}

					self._setAttributes(source, sourceAttrs);
					picture.appendChild(source);
				});
				picture.appendChild(image);
			}

			var ribbon;
			if (self.hasRibbon){
				ribbon = document.createElement("div");
				ribbon.className = self.ribbon.type;
				var ribbonText = document.createElement("span");
				ribbonText.innerHTML = self.ribbon.text;
				ribbon.appendChild(ribbonText);
			}

			var overlay = document.createElement("span");
			overlay.className = cls.overlay;

			var wrap = document.createElement("span");
			wrap.className = cls.wrap;

			var loader = document.createElement("div");
			loader.className = cls.loader;

			var caption = document.createElement("figcaption");
			self._setAttributes(caption, attr.caption.elem);
			self._setAttributes(caption, {
				"class": cls.caption.elem
			});

			var captionInner = document.createElement("div");
			self._setAttributes(captionInner, attr.caption.inner);
			self._setAttributes(captionInner, {
				"class": cls.caption.inner
			});

			var captionTitle = null, hasTitle = self.showCaptionTitle && _is.string(self.caption) && self.caption.length > 0;
			if (hasTitle) {
				captionTitle = document.createElement("div");
				self._setAttributes(captionTitle, attr.caption.title);
				captionTitle.className = cls.caption.title;
				captionTitle.innerHTML = self.maxCaptionLength > 0 ? _str.trimTo(self.caption, self.maxCaptionLength) : self.caption;
				captionInner.appendChild(captionTitle);
			}
			var captionDesc = null, hasDescription = self.showCaptionDescription && _is.string(self.description) && self.description.length > 0;
			if (hasDescription) {
				captionDesc = document.createElement("div");
				self._setAttributes(captionDesc, attr.caption.description);
				captionDesc.className = cls.caption.description;
				captionDesc.innerHTML = self.maxDescriptionLength > 0 ? _str.trimTo(self.description, self.maxDescriptionLength) : self.description;
				captionInner.appendChild(captionDesc);
			}
			var captionButtons = null, hasButtons = _is.array(self.buttons) && self.buttons.length > 0;
			if (hasButtons){
				captionButtons = document.createElement("div");
				captionButtons.className = cls.caption.buttons;
				_utils.each(self.buttons, function(button){
					if (_is.hash(button) && _is.string(button.text)){
						var captionButton = document.createElement("a");
						captionButton.innerHTML = button.text;
						if (_is.string(button.url) && button.url.length > 0){
							captionButton.href = button.url;
						}
						if (_is.string(button.rel) && button.rel.length > 0){
							captionButton.rel = button.rel;
						}
						if (_is.string(button.target) && button.target.length > 0){
							captionButton.target = button.target;
						}
						if (_is.string(button.classes) && button.classes.length > 0){
							captionButton.className = button.classes;
						}
						if (_is.hash(button.attr)){
							self._setAttributes(captionButton, button.attr);
						}
						captionButtons.appendChild(captionButton);
					}
				});
				captionInner.appendChild(captionButtons);
			}
			caption.appendChild(captionInner);

			if (self.isPicture){
				wrap.appendChild(picture);
			} else {
				wrap.appendChild(image);
			}
			anchor.appendChild(overlay);
			anchor.appendChild(wrap);
			inner.appendChild(anchor);
			if (hasTitle || hasDescription || hasButtons){
				inner.appendChild(caption);
			}
			if (self.hasRibbon){
				elem.appendChild(ribbon);
			}
			elem.appendChild(inner);
			elem.appendChild(loader);

			self.$el = $(elem).data(_.DATA_ITEM, self);
			self.el = elem;
			self.$inner = $(inner);
			self.$anchor = $(anchor).on("click.foogallery", {self: self}, self.onAnchorClick);
			self.$overlay = $(overlay);
			self.$wrap = $(wrap);
			if (self.isPicture) {
				self.$image = $(picture);
			} else {
				self.$image = $(image);
			}
			self.$caption = $(caption).on("click.foogallery", {self: self}, self.onCaptionClick);
			self.$loader = $(loader);

			return true;
		},
		/**
		 * @summary Append the item to the current template.
		 * @memberof FooGallery.Item#
		 * @function append
		 * @returns {boolean}
		 * @fires FooGallery.Template~"append-item.foogallery"
		 * @fires FooGallery.Template~"appended-item.foogallery"
		 */
		append: function () {
			var self = this;
			if (self.isCreated && !self.isAttached) {
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
				var e = self.tmpl.trigger("append-item", [self]);
				if (!e.isDefaultPrevented()) {
					self.tmpl.$el.append(self.$el.removeClass(self.cls.hidden));
					self.isAttached = true;
				}
				if (self.isAttached) {
					if (!self.isLoaded) self.tmpl.items.observe(self);
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
					self.tmpl.trigger("appended-item", [self]);
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
		detach: function () {
			var self = this;
			if (self.isCreated && self.isAttached) {
				self.tmpl.items.unobserve(self);
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
				var e = self.tmpl.trigger("detach-item", [self]);
				if (!e.isDefaultPrevented()) {
					self.$el.detach().removeClass(self.cls.hidden);
					self.isAttached = false;
				}
				if (!self.isAttached) {
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
					self.tmpl.trigger("detached-item", [self]);
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
		load: function () {
			var self = this;
			if (_is.promise(self._load)) return self._load;
			return self._load = $.Deferred(function (def) {
				if (self.isLoaded){
					return def.resolve(self);
				}
				if (!self.isCreated || !self.isAttached) {
					return def.reject("not created or attached");
				}
				var e = self.tmpl.trigger("load-item", [self]);
				if (e.isDefaultPrevented()){
					return def.reject("default prevented");
				}

				self.isLoading = true;
				self.tmpl.items.unobserve(self);
				self.$el.removeClass(self.cls.idle)
					.removeClass(self.cls.hidden)
					.removeClass(self.cls.loaded)
					.removeClass(self.cls.error)
					.addClass(self.cls.loading);

				self.loadIMG().then(function(){
					self.isLoading = false;
					self.isLoaded = true;
					self.$el.removeClass(self.cls.loading).addClass(self.cls.loaded);
					self.tmpl.trigger("loaded-item", [self]);
					def.resolve(self);
				}, function(reason){
					self.isLoading = false;
					self.isError = true;
					self.$el.removeClass(self.cls.loading).addClass(self.cls.error);
					self.tmpl.trigger("error-item", [self]);
					def.reject(reason);
				});

			}).promise();
		},
		/**
		 * @summary Load the items' {@link FooGallery.Item#$image|$image} if it is an actual <img> element.
		 * @memberof FooGallery.Item#
		 * @function loadIMG
		 * @returns {Promise.<FooGallery.Item>}
		 */
		loadIMG: function(){
			var self = this;
			return new $.Deferred(function(def){
				var img = self.isPicture ? self.$image.find("img").get(0) : self.$image.get(0);
				if (!img){
					return def.reject("Unable to find img element.");
				}
				var ph_src = img.src, ph_srcset = img.srcset;
				img.onload = function () {
					img.onload = img.onerror = null;
					img.style.removeProperty("width");
					img.style.removeProperty("height");
					def.resolve(img);
				};
				img.onerror = function () {
					img.onload = img.onerror = null;
					if (!_is.empty(ph_src)) {
						img.src = ph_src;
					} else {
						img.removeAttribute("src");
					}
					if (!_is.empty(ph_srcset)) {
						img.srcset = ph_srcset;
					} else {
						img.removeAttribute("srcset");
					}
					def.reject(img);
				};
				// set everything in motion by setting the src & srcset
				if (self.isPicture){
					self.$image.find("source").each(function(i, source){
						var srcset = source.getAttribute(self.tmpl.opt.srcset);
						if (!_is.empty(srcset)){
							source.srcset = srcset;
						}
					});
				}
				var size = img.getBoundingClientRect();
				img.style.width = size.width;
				img.style.height = size.height;

				img.src = self.src;
				if (!_is.empty(self.srcset)){
					img.srcset = self.srcset;
				}
				if (img.complete){
					img.onload();
				}
			}).promise();
		},
		/**
		 * @summary Create an empty placeholder image using the supplied dimensions.
		 * @memberof FooGallery.Item#
		 * @function createPlaceholder
		 * @param {number} width - The width of the placeholder.
		 * @param {number} height - The height of the placeholder.
		 * @returns {string}
		 */
		createPlaceholder: function(width, height){
			if (_is.number(width) && _is.number(height)){
				return "data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20width=%22" + width + "%22%20height=%22" + height + "%22%20viewBox=%220%200%20" + width + "%20" + height + "%22%3E%3C/svg%3E";
			}
			return "";
		},
		/**
		 * @summary Gets the type specific CSS class for the item.
		 * @memberof FooGallery.Item#
		 * @function getTypeClass
		 * @returns {string}
		 */
		getTypeClass: function(){
			return this.cls.types[this.type] || "";
		},
		/**
		 * @summary Scroll the item into the center of the viewport.
		 * @memberof FooGallery.Item#
		 * @function scrollTo
		 */
		scrollTo: function (align) {
			var self = this;
			if (self.isAttached) {
				var el = self.$el.get(0);
				if (!!el.scrollIntoViewIfNeeded){
					el.scrollIntoViewIfNeeded();
				} else {
					el.scrollIntoView(align === "top");
				}
			}
			return self;
		},
		/**
		 * @summary Updates the current state to this item.
		 * @memberof FooGallery.Item#
		 * @function updateState
		 */
		updateState: function(){
			this.tmpl.state.update(this.tmpl.state.get(this));
		},
		/**
		 * @summary Converts the item to a JSON object.
		 * @memberof FooGallery.Item#
		 * @function toJSON
		 * @returns {object}
		 */
		toJSON: function(){
			return {
				"type": this.type,
				"id": this.id,
				"productId": this.productId,
				"href": this.href,
				"src": this.src,
				"srcset": this.srcset,
				"width": this.width,
				"height": this.height,
				"alt": this.alt,
				"title": this.title,
				"caption": this.caption,
				"description": this.description,
				"tags": this.tags.slice(),
				"maxCaptionLength": this.maxCaptionLength,
				"maxDescriptionLength": this.maxDescriptionLength,
				"showCaptionTitle": this.showCaptionTitle,
				"showCaptionDescription": this.showCaptionDescription,
				"noLightbox": this.noLightbox,
				"panelHide": this.panelHide,
				"attr": _obj.extend({}, this.attr)
			};
		},
		/**
		 * @summary Listens for the click event on the {@link FooGallery.Item#$anchor|$anchor} element and updates the state if enabled.
		 * @memberof FooGallery.Item#
		 * @function onAnchorClick
		 * @param {jQuery.Event} e - The jQuery.Event object for the click event.
		 * @private
		 */
		onAnchorClick: function (e) {
			var self = e.data.self, evt = self.tmpl.trigger("anchor-click-item", [self]);
			if (evt.isDefaultPrevented()) {
				e.preventDefault();
			} else {
				self.updateState();
			}
		},
		/**
		 * @summary Listens for the click event on the {@link FooGallery.Item#$caption|$caption} element and redirects it to the anchor if required.
		 * @memberof FooGallery.Item#
		 * @function onCaptionClick
		 * @param {jQuery.Event} e - The jQuery.Event object for the click event.
		 * @private
		 */
		onCaptionClick: function (e) {
			var self = e.data.self, evt = self.tmpl.trigger("caption-click-item", [self]);
			if (!evt.isDefaultPrevented() && self.$anchor.length > 0 && !$(e.target).is("a[href],:input")) {
				self.$anchor.get(0).click();
			}
		}
	});

	/**
	 * @summary A simple object containing an items' default values.
	 * @typedef {object} FooGallery.Item~Options
	 * @property {?string} [type="item"] - The `data-type` attribute for the anchor element.
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
	 * @property {number} [maxCaptionLength=0] - The max length of the title for the caption.
	 * @property {number} [maxDescriptionLength=0] - The max length of the description for the caption.
	 * @property {boolean} [showCaptionTitle=true] - Whether or not the caption title should be displayed.
	 * @property {boolean} [showCaptionDescription=true] - Whether or not the caption description should be displayed.
	 * @property {FooGallery.Item~Attributes} [attr] - Additional attributes to apply to the items' elements.
	 * @property {FooGallery.Item~Button[]} [buttons=[]] - An array of buttons to append to the caption.
	 * @property {FooGallery.Item~Ribbon} [ribbon] - The ribbon type and text to display for the item.
	 */
	_.template.configure("core", {
		item: {
			type: "item",
			id: "",
			href: "",
			placeholder: "",
			src: "",
			srcset: "",
			sources: [],
			width: 0,
			height: 0,
			title: "",
			alt: "",
			caption: "",
			description: "",
			tags: [],
			maxCaptionLength: 0,
			maxDescriptionLength: 0,
			showCaptionTitle: true,
			showCaptionDescription: true,
			noLightbox: false,
			panelHide: false,
			buttons: [],
			ribbon: {
				type: null,
				text: null
			},
			exif: {
				aperture: null,
				camera: null,
				created_timestamp: null,
				shutter_speed: null,
				focal_length: null,
				iso: null,
				orientation: null
			},
			attr: {
				elem: {},
				inner: {},
				anchor: {},
				image: {},
				picture: {},
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
			exif: "fg-item-exif",
			anchor: "fg-thumb",
			overlay: "fg-image-overlay",
			wrap: "fg-image-wrap",
			image: "fg-image",
			loader: "fg-loader",
			idle: "fg-idle",
			loading: "fg-loading",
			loaded: "fg-loaded",
			error: "fg-error",
			hidden: "fg-hidden",
			noLightbox: "fg-no-lightbox",
			panelHide: "fg-panel-hide",
			types: {
				item: "fg-type-unknown"
			},
			caption: {
				elem: "fg-caption",
				inner: "fg-caption-inner",
				title: "fg-caption-title",
				description: "fg-caption-desc",
				buttons: "fg-caption-buttons",
				button: "fg-caption-button"
			}
		}
	}, {
		item: {
			exif: {
				aperture: "Aperture",
				camera: "Camera",
				created_timestamp: "Date",
				shutter_speed: "Exposure",
				focal_length: "Focal Length",
				iso: "ISO",
				orientation: "Orientation"
			}
		}
	});

	_.components.register("item", _.Item);

	// ######################
	// ## Type Definitions ##
	// ######################

	/**
	 * @summary An object containing properties for a button to add to the item caption.
	 * @typedef {object} FooGallery.Item~Button
	 * @property {string} url - The url the button opens.
	 * @property {string} text - The text displayed within the button.
	 * @property {string} [rel=""] - The rel attribute for the button.
	 * @property {string} [target="_blank"] - The target attribute for the button.
	 * @property {string} [classes=""] - Additional CSS class names to apply to the button.
	 */

	/**
	 * @summary An object containing the ribbon information.
	 * @typedef {object} FooGallery.Item~Ribbon
	 * @property {string} type - The type of ribbon to display.
	 * @property {string} text - The text displayed within the ribbon.
	 */

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
	FooGallery.utils.obj,
	FooGallery.utils.str
);
(function($, _, _utils, _is){

    _.Image = _.Item.extend({});

    _.template.configure("core", null,{
        item: {
            types: {
                image: "fg-type-image"
            }
        }
    });

    _.components.register("image", _.Image);

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is
);
(function(_, _is, _fn, _obj){

	_.PagingFactory = _.Factory.extend(/** @lends FooGallery.PagingFactory */{
		/**
		 * @summary A factory for paging types allowing them to be easily registered and created.
		 * @memberof FooGallery
		 * @constructs PagingFactory
		 * @description The plugin makes use of an instance of this class exposed as {@link FooGallery.paging}.
		 * @augments FooGallery.Factory
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
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
		type: function(options){
			var self = this, opt;
			return _is.hash(options) && _is.hash(opt = options.paging) && _is.string(opt.type) && self.contains(opt.type) ? opt.type : null;
		},
		merge: function(options){
			options = _obj.extend({}, options);
			var self = this, type = self.type(options),
					reg = self.registered,
					def = reg["default"].opt,
					def_cls = reg["default"].cls,
					def_il8n = reg["default"].il8n,
					opt = _is.hash(options.paging) ? options.paging : {},
					cls = _is.hash(options.cls) && _is.hash(options.cls.paging) ? _obj.extend({}, options.cls.paging) : {},
					il8n = _is.hash(options.il8n) && _is.hash(options.il8n.paging) ? _obj.extend({}, options.il8n.paging) : {};

			if (!_is.hash(options.cls)) options.cls = {};
			if (!_is.hash(options.il8n)) options.il8n = {};
			if (type !== "default" && self.contains(type)){
				options.paging = _obj.extend({}, def, reg[type].opt, opt, {type: type});
				options.cls = _obj.extend(options.cls, {paging: def_cls}, {paging: reg[type].cls}, {paging: cls});
				options.il8n = _obj.extend(options.il8n, {paging: def_il8n}, {paging: reg[type].il8n}, {paging: il8n});
			} else {
				options.paging = _obj.extend({}, def, opt, {type: type});
				options.cls = _obj.extend(options.cls, {paging: def_cls}, {paging: cls});
				options.il8n = _obj.extend(options.il8n, {paging: def_il8n}, {paging: il8n});
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
		FooGallery.utils.is,
		FooGallery.utils.fn,
		FooGallery.utils.obj
);
(function ($, _, _utils, _is) {

	_.Paging = _.Component.extend({
		construct: function (template) {
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
			self._pages = [];
		},
		init: function(){},
		fromHash: function(hash){
			var parsed = parseInt(hash);
			return isNaN(parsed) ? null : parsed;
		},
		toHash: function(value){
			return _is.number(value) && value > 0 ? value.toString() : null;
		},
		getState: function(){
			return this.isValid(this.current) ? this.current : null;
		},
		setState: function(state){
			this.rebuild();
			var shouldScroll = false;
			if (!!state.item && !this.contains(state.page, state.item)){
				state.page = this.find(state.item);
				state.page = state.page !== 0 ? state.page : 1;
				shouldScroll = true;
			}
			this.set(state.page, shouldScroll, false, false);
		},
		destroy: function () {
			var self = this;
			self._pages.splice(0, self._pages.length);
			$.each(self.ctrls.splice(0, self.ctrls.length), function (i, control) {
				control.destroy();
			});
			self._super();
		},
		build: function () {
			var self = this, items = self.tmpl.items.available();
			self.total = self.size > 0 && items.length > 0 ? Math.ceil(items.length / self.size) : 1;
			for (var i = 0; i < self.total; i++) {
				self._pages.push(items.splice(0, self.size));
			}
			if (self.total > 1 && _.paging.hasCtrl(self.type)) {
				var pos = self.position, top, bottom;
				if (pos === "both" || pos === "top") {
					top = _.paging.makeCtrl(self.type, self.tmpl, self, "top");
					if (top.create()) {
						top.append();
						self.ctrls.push(top);
					}
				}
				if (pos === "both" || pos === "bottom") {
					bottom = _.paging.makeCtrl(self.type, self.tmpl, self, "bottom");
					if (bottom.create()) {
						bottom.append();
						self.ctrls.push(bottom);
					}
				}
			}
		},
		rebuild: function () {
			var self = this;
			self.current = 0;
			self.total = 0;
			self._pages.splice(0, self._pages.length);
			$.each(self.ctrls.splice(0, self.ctrls.length), function (i, control) {
				control.destroy();
			});
			self.build();
		},
		all: function () {
			return this._pages.slice();
		},
		available: function () {
			return this.get(this.current);
		},
		controls: function (pageNumber) {
			var self = this;
			if (self.isValid(pageNumber)) {
				$.each(self.ctrls, function (i, control) {
					control.update(pageNumber);
				});
			}
		},
		isValid: function (pageNumber) {
			return _is.number(pageNumber) && pageNumber > 0 && pageNumber <= this.total;
		},
		number: function (value) {
			return this.isValid(value) ? value : (this.current === 0 ? 1 : this.current);
		},
		create: function (pageNumber, isFilter) {
			var self = this;
			pageNumber = self.number(pageNumber);

			var pageIndex = pageNumber - 1, pageItems = self._pages[pageIndex], detach;
			if (isFilter){
				detach = self.tmpl.items.all();
			} else {
				detach = self._pages.reduce(function(detach, page, index){
					return index === pageIndex ? detach : detach.concat(page);
				}, self.tmpl.items.unavailable());
			}

			self.current = pageNumber;
			self.tmpl.items.detach(detach);
			self.tmpl.items.create(pageItems, true);
		},
		get: function (pageNumber) {
			var self = this;
			if (self.isValid(pageNumber)) {
				pageNumber = self.number(pageNumber);
				return self._pages[pageNumber - 1];
			}
			return [];
		},
		set: function (pageNumber, scroll, updateState, isFilter) {
			var self = this;
			if (self.isValid(pageNumber)) {
				self.controls(pageNumber);
				var num = self.number(pageNumber), state;
				if (num !== self.current) {
					var prev = self.current, setPage = function () {
						updateState = _is.boolean(updateState) ? updateState : true;
						isFilter = _is.boolean(isFilter) ? isFilter : false;
						if (updateState && self.current === 1 && !self.tmpl.state.exists()) {
							state = self.tmpl.state.get();
							self.tmpl.state.update(state, self.pushOrReplace);
						}
						self.create(num, isFilter);
						if (updateState) {
							state = self.tmpl.state.get();
							self.tmpl.state.update(state, self.pushOrReplace);
						}
						self.tmpl.trigger("page-change", [self.current, prev, isFilter]);
						if (self.scrollToTop && _is.boolean(scroll) ? scroll : false) {
							var page = self.get(self.current);
							if (page.length > 0) {
								page[0].scrollTo("top");
							}
						}
						self.tmpl.trigger("after-page-change", [self.current, prev, isFilter]);
					};
					var e = self.tmpl.trigger("before-page-change", [self.current, num, setPage, isFilter]);
					if (e.isDefaultPrevented()) return false;
					setPage();
					return true;
				}
			}
			return false;
		},
		find: function (item) {
			var self = this;
			for (var i = 0, l = self._pages.length; i < l; i++) {
				if (_utils.inArray(item, self._pages[i]) !== -1) {
					return i + 1;
				}
			}
			return 0;
		},
		contains: function (pageNumber, item) {
			var items = this.get(pageNumber);
			return _utils.inArray(item, items) !== -1;
		},
		first: function () {
			this.goto(1);
		},
		last: function () {
			this.goto(this._pages.length);
		},
		prev: function () {
			this.goto(this.current - 1);
		},
		next: function () {
			this.goto(this.current + 1);
		},
		goto: function (pageNumber) {
			this.set(pageNumber, true);
		}
	});

	_.PagingControl = _.Component.extend({
		construct: function (template, parent, position) {
			var self = this;
			self._super(template);
			self.pages = parent;
			self.position = position;
			self.$container = null;
			self._containerExisted = false;
			self._placeholderClasses = [];
		},
		create: function () {
			var self = this;
			self.$container = $("#" + self.tmpl.id + "_paging-" + self.position);
			if (self.$container.length > 0){
				self._containerExisted = true;
				self.$container.removeClass(function(i, classNames){
					self._placeholderClasses = classNames.match(/(^|\s)fg-ph-\S+/g) || [];
					return self._placeholderClasses.join(' ');
				}).addClass([self.pages.cls.container, self.pages.theme].join(' '));
			} else {
				self.$container = $("<nav/>", {"class": [self.pages.cls.container, self.pages.theme].join(' ')});
			}
			return true;
		},
		destroy: function () {
			var self = this;
			if (self._containerExisted){
				self.$container.empty()
					.removeClass()
					.addClass(self._placeholderClasses.join(' '));
			} else {
				self.$container.remove();
			}
			self.$container = null;
		},
		append: function () {
			var self = this;
			if (self._containerExisted) return;
			if (self.position === "top") {
				self.$container.insertBefore(self.tmpl.$el);
			} else {
				self.$container.insertAfter(self.tmpl.$el);
			}
		},
		update: function (pageNumber) {
		}
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
			this.$list = null;
			this.$items = null;
		},
		create: function(){
			var self = this;
			if (self._super()){
				var cls = self.pages.cls, il8n = self.pages.il8n,
					items = [], $list = $("<ul/>", {"class": cls.list});

				for (var i = 0, l = self.pages.total, $item; i < l; i++){
					items.push($item = self.createItem(i + 1, il8n.page));
					$list.append($item);
				}
				self.$list = $list;
				self.$items = $($.map(items, function($item){ return $item.get(); }));
				self.$container.append($list);
				return true;
			}
			return false;
		},
		destroy: function(){
			var self = this, sel = self.pages.sel;
			self.$list.find(sel.link).off("click.foogallery", self.onLinkClick);
			self.$list = $();
			self.$items = $();
			self._super();
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
(function($, _, _utils, _is, _obj, _fn, _t){

    var instance_id = 0;

    /**
     * @memberof FooGallery.
     * @class Panel
     * @param template
     * @param options
     * @param classes
     * @param il8n
     * @augments FooGallery.Component
     */
    _.Panel = _.Component.extend(/** @lends FooGallery.Panel */{
        /**
         * @constructs
         * @param template
         * @param options
         * @param classes
         * @param il8n
         */
        construct: function(template, options, classes, il8n){
            var self = this;
            self.instanceId = ++instance_id;
            self._super(template);

            self.opt = _obj.extend({}, self.tmpl.opt.panel, options);

            self.cls = _obj.extend({}, self.tmpl.cls.panel, classes);

            self.il8n = _obj.extend({}, self.tmpl.il8n.panel, il8n);

            var states = self.cls.states;
            self.cls.states.all = Object.keys(states).map(function (key) {
                return states[key];
            }).join(" ");
            self.cls.states.allLoading = [states.idle, states.loading, states.loaded, states.error].join(" ");
            self.cls.states.allProgress = [states.idle, states.started, states.stopped, states.paused].join(" ");

            self.sel = _utils.selectify(self.cls);

            self.videoSources = !_is.undef(_.Panel.Video) ? _.Panel.Video.sources.load(self) : [];

            self.buttons = new _.Panel.Buttons(self);

            self.content = new _.Panel.Content(self);
            self.info = new _.Panel.Info(self);
            self.thumbs = new _.Panel.Thumbs(self);

            self.areas = [self.content, self.info, self.thumbs];

            if ( _.Panel.Cart ){
                self.cart = new _.Panel.Cart(self);
                self.areas.push( self.cart );
            }

            self.$el = null;

            self.el = null;

            self.isCreated = false;

            self.isDestroyed = false;

            self.isDestroying = false;

            self.isAttached = false;

            self.isLoading = false;

            self.isLoaded = false;

            self.isError = false;

            self.isInline = false;

            self.isMaximized = false;

            self.isFullscreen = false;

            self.hasTransition = !_is.empty(self.cls.transition[self.opt.transition]);

            self.currentItem = null;

            self.prevItem = null;

            self.nextItem = null;

            self.lastBreakpoint = null;

            self.isSmallScreen = false;
            self.isMediumScreen = false;
            self.isLargeScreen = false;

            self.breakpointClassNames = self.opt.breakpoints.map(function(bp){
                return "fg-" + bp.name + " fg-" + bp.name + "-width" + " fg-" + bp.name + "-height";
            }).concat(["fg-landscape","fg-portrait"]).join(" ");

            self.robserver = new ResizeObserver(_fn.throttle(function (entries) {
                if (!self.destroying && !self.destroyed){
                    entries.forEach(function (entry) {
                        if (entry.target === self.el){
                            var size = _utils.getResizeObserverSize(entry);
                            self.onResize(size.width, size.height);
                        }
                    });
                }
            }, 50));

            self.__media = {};

            self.__loading = null;

            if (!(self.tmpl.destroying || self.tmpl.destroyed)){
                self.tmpl.on({
                    "after-filter-change": self.onItemsChanged
                }, self);
            }
        },
        isVisible: function(item){
            return item instanceof _.Item && !item.noLightbox && !item.panelHide;
        },
        onItemsChanged: function(e){
            var self = this;
            if (self.thumbs.isCreated && self.tmpl.initialized){
                self.thumbs.doCreateThumbs(self.tmpl.items.available(self.isVisible));
                if (self.isAttached) self.load(self.tmpl.items.first(self.isVisible));
            }
        },
        create: function(){
            var self = this;
            if (!self.isCreated) {
                var e = self.trigger("create");
                if (!e.isDefaultPrevented()) {
                    self.isCreated = self.doCreate();
                }
                if (self.isCreated) {
                    self.trigger("created");
                }
            }
            return self.isCreated;
        },
        doCreate: function(){
            var self = this;
            self.$el = self.createElem();
            self.el = self.$el.get(0);
            if (self.tmpl.opt.protected){
                self.el.oncontextmenu = function(e){
                    e.preventDefault();
                    return false;
                };
            }
            if (self.opt.keyboard){
                self.$el.attr("tabindex", -1).on("keydown.foogallery", {self: self}, self.onKeyDown);
            }
            self.areas.forEach(function(area){
                area.appendTo( self.$el );
            });
            self.buttons.appendTo( self.content.$el );
            return true;
        },
        createElem: function(){
            var self = this, transition = self.cls.transition[self.opt.transition] || "";
            self.hasTransition = !_is.empty(transition);
            var classes = [
                self.cls.elem,
                transition,
                self.cls.buttons.portrait[self.opt.buttonsPortrait] || "",
                self.cls.buttons.landscape[self.opt.buttonsLandscape] || "",
                _is.string(self.opt.theme) ? self.opt.theme : self.tmpl.getCSSClass("theme", "fg-dark"),
                _is.string(self.opt.loadingIcon) ? self.opt.loadingIcon : self.tmpl.getCSSClass("loadingIcon"),
                _is.string(self.opt.hoverIcon) ? self.opt.hoverIcon : self.tmpl.getCSSClass("hoverIcon"),
                _is.string(self.opt.videoIcon) ? self.opt.videoIcon : self.tmpl.getCSSClass("videoIcon"),
                _is.boolean(self.opt.stickyVideoIcon) && self.opt.stickyVideoIcon ? self.cls.stickyVideoIcon : self.tmpl.getCSSClass("stickyVideoIcon"),
                _is.string(self.opt.insetShadow) ? self.opt.insetShadow : self.tmpl.getCSSClass("insetShadow"),
                _is.string(self.opt.filter) ? self.opt.filter : self.tmpl.getCSSClass("filter"),
                _is.string(self.opt.hoverColor) ? self.opt.hoverColor : self.tmpl.getCSSClass("hoverColor"),
                _is.boolean(self.opt.hoverScale) && self.opt.hoverScale ? self.cls.hoverScale : self.tmpl.getCSSClass("hoverScale"),
                _is.string(self.opt.button) ? self.opt.button : "",
                _is.string(self.opt.highlight) ? self.opt.highlight : "",
                self.opt.stackSideAreas ? self.cls.stackSideAreas : "",
                self.opt.preserveButtonSpace ? self.cls.preserveButtonSpace : "",
                self.opt.fitMedia ? self.cls.fitMedia : "",
                self.opt.noMobile ? self.cls.noMobile : "",
                self.opt.hoverButtons ? self.cls.hoverButtons : "",
                self.opt.classNames
            ];
            return $('<div/>').addClass(classes.join(" "));
        },
        destroy: function () {
            var self = this, _super = self._super.bind(self);
            if (self.isDestroyed) return _fn.resolved;
            self.isDestroying = true;
            return $.Deferred(function (def) {
                if (self.isLoading && _is.promise(self.__loading)) {
                    self.__loading.always(function () {
                        var e = self.trigger("destroy");
                        self.isDestroying = false;
                        if (!e.isDefaultPrevented()) {
                            self.isDestroyed = self.doDestroy();
                        }
                        if (self.isDestroyed) {
                            self.trigger("destroyed");
                        }
                        def.resolve();
                    });
                } else {
                    var e = self.trigger("destroy");
                    self.isDestroying = false;
                    if (!e.isDefaultPrevented()) {
                        self.isDestroyed = self.doDestroy();
                    }
                    if (self.isDestroyed) {
                        self.trigger("destroyed");
                    }
                    def.resolve();
                }
            }).then(function(){
                _super();
            }).promise();
        },
        doDestroy: function(){
            var self = this;
            self.buttons.destroy();
            self.areas.reverse();
            self.areas.forEach(function (area) {
                area.destroy();
            });
            self.detach();
            if (self.isCreated){
                self.$el.remove();
            }
            return true;
        },
        appendTo: function( parent ){
            var self = this;
            if ((self.isCreated || self.create()) && !self.isAttached){
                var e = self.trigger("append", [parent]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = self.doAppendTo( parent );
                }
                if (self.isAttached) {
                    self.trigger("appended", [parent]);
                }
            }
            return self.isAttached;
        },
        doAppendTo: function( parent ){
            var self = this, $parent = $( parent ), maximize = self.buttons.get("maximize");
            self.isInline = !$parent.is("body");
            self.$el.appendTo( $parent );

            maximize.set(!self.isInline, self.isInline && maximize.isEnabled());

            self.robserver.observe(self.el);

            self.areas.forEach(function (area) {
                area.listen();
            });
            return self.el.parentNode !== null;
        },
        detach: function(){
            var self = this;
            if (self.isCreated && self.isAttached) {
                var e = self.trigger("detach");
                if (!e.isDefaultPrevented()) {
                    self.isAttached = !self.doDetach();
                }
                if (!self.isAttached) {
                    self.trigger("detached");
                }
            }
            return !self.isAttached;
        },
        doDetach: function(){
            var self = this;
            self.robserver.unobserve(self.el);
            self.areas.forEach(function (area) {
                area.stopListening();
            });
            self.$el.detach();
            return true;
        },
        resize: function(){
            var self = this;
            self.$el.removeClass(self.breakpointClassNames).addClass(self.lastBreakpoint);
            self.isMediumScreen = self.$el.hasClass("fg-medium");
            self.isLargeScreen = self.$el.hasClass("fg-large");
            self.isXLargeScreen = self.$el.hasClass("fg-x-large");
            self.isSmallScreen = !self.isMediumScreen && !self.isLargeScreen && !self.isXLargeScreen;
            self.areas.forEach(function (area) {
                area.resize();
            });
            self.buttons.resize();
        },
        onResize: function(width, height){
            var self = this, bp = self.getBreakpoint(width, height);
            if (self.lastBreakpoint !== bp){
                self.lastBreakpoint = bp;
                self.resize();
            }
        },
        getBreakpoint: function(width, height){
            var self = this,
                result = [];

            self.opt.breakpoints.forEach(function(bp){
                var w = bp.width <= width, h = bp.height <= height;
                if (w && h) result.push("fg-" + bp.name);
                if (w) result.push("fg-" + bp.name + "-width");
                if (h) result.push("fg-" + bp.name + "-height");
            });

            result.push(width > height ? "fg-landscape" : "fg-portrait");

            return result.length > 0 ? result.join(" ") : null;
        },
        getMedia: function(item){
            if (!(item instanceof _.Item)) return null;
            if (this.__media.hasOwnProperty(item.id)) return this.__media[item.id];
            return this.__media[item.id] = _.Panel.media.make(item.type, this, item);
        },
        getItem: function(item){
            var self = this, result = item;
            if (!(result instanceof _.Item)) result = self.currentItem;
            if (!(result instanceof _.Item)) result = self.tmpl.items.first(self.isVisible);
            if (item instanceof _.Item && !self.isVisible(item)){
                result = self.tmpl.items.next(item, self.isVisible, self.opt.loop);
                if (!(result instanceof _.Item)){
                    result = self.tmpl.items.prev(item, self.isVisible, self.opt.loop);
                }
            }
            return result;
        },
        load: function( item ){
            var self = this;

            item = self.getItem(item);

            if (!(item instanceof _.Item)) return _fn.reject("no item to load");
            if (item === self.currentItem) return _fn.reject("item is currently loaded");

            self.isLoading = true;
            self.isLoaded = false;
            self.isError = false;

            return self.__loading = $.Deferred(function(def){
                if (!self.isCreated || !self.isAttached){
                    def.rejectWith("not created or attached");
                    return;
                }
                var media = self.getMedia(item);
                if (!(media instanceof _.Panel.Media)){
                    def.rejectWith("no media to load");
                    return;
                }
                var e = self.trigger("load", [media, item]);
                if (e.isDefaultPrevented()){
                    def.rejectWith("default prevented");
                    return;
                }
                self.currentItem = item;
                self.prevItem = self.tmpl.items.prev(item, self.isVisible, self.opt.loop);
                self.nextItem = self.tmpl.items.next(item, self.isVisible, self.opt.loop);
                self.doLoad(media).then(def.resolve).fail(def.reject);
            }).always(function(){
                self.isLoading = false;
            }).then(function(){
                self.isLoaded = true;
                self.trigger("loaded", [item]);
                item.updateState();
            }).fail(function(){
                self.isError = true;
                self.trigger("error", [item]);
            }).promise();
        },
        doLoad: function( media ){
            var self = this, wait = [];
            self.buttons.beforeLoad(media);
            self.areas.forEach(function (area) {
                wait.push(area.load(media));
            });
            return $.when.apply($, wait).then(function(){
                self.buttons.afterLoad(media);
            }).promise();
        },
        open: function( item, parent ){
            var self = this;
            item = self.getItem(item);
            var e = self.trigger("open", [item, parent]);
            if (e.isDefaultPrevented()) return _fn.reject("default prevented");
            return self.doOpen(item, parent).then(function(){
                self.trigger("opened", [item, parent]);
            });
        },
        doOpen: function( item, parent ){
            var self = this;
            return $.Deferred(function(def){
                if (!(item instanceof _.Item)){
                    def.rejectWith("item not instanceof FooGallery.Item");
                    return;
                }
                parent = !_is.empty(parent) ? parent : "body";
                if (!self.isAttached){
                    self.appendTo( parent );
                }
                if (self.isAttached){
                    self.load( item ).then(def.resolve).fail(def.reject);
                } else {
                    def.rejectWith("not attached");
                }
            }).promise();
        },
        next: function(){
            var self = this, current = self.currentItem, next = self.nextItem;
            if (!(next instanceof _.Item)) return _fn.reject("no next item");
            var e = self.trigger("next", [current, next]);
            if (e.isDefaultPrevented()) return _fn.reject("default prevented");
            return self.doNext(next).then(function(){
                self.trigger("after-next", [current, next]);
            });
        },
        doNext: function(item){
            return this.load( item );
        },
        prev: function(){
            var self = this, current = self.currentItem, prev = self.prevItem;
            if (!(prev instanceof _.Item)) return _fn.reject("no prev item");
            var e = self.trigger("prev", [current, prev]);
            if (e.isDefaultPrevented()) return _fn.reject("default prevented");
            return self.doPrev(prev).then(function(){
                self.trigger("after-prev", [current, prev]);
            });
        },
        doPrev: function(item){
            return this.load( item );
        },
        close: function(immediate){
            var self = this;
            if ( self.isClosing ) return $.Deferred().reject();
            var e = self.trigger("close", [self.currentItem]);
            if (e.isDefaultPrevented()) return _fn.reject("default prevented");
            return self.doClose(immediate).then(function(){
                self.trigger("closed");
            });
        },
        doClose: function(immediate, detach){
            detach = _is.boolean(detach) ? detach : true;
            var self = this;
            self.isClosing = true;
            return $.Deferred(function(def){
                self.content.close(immediate).then(function(){
                    var wait = [];
                    self.areas.forEach(function(area){
                        if (area !== self.content){
                            wait.push(area.close(immediate));
                        }
                    });
                    $.when.apply($, wait).then(def.resolve).fail(def.reject);
                });
            }).always(function(){
                self.isClosing = false;
                self.currentItem = null;
                self.buttons.close();
                if (detach) self.detach();
                self.tmpl.state.clear();
            }).promise();
        },
        trapFocus: function(){
            if (!this.isCreated) return;
            this.$el.on('keydown', {self: this}, this.onTrapFocusKeydown);
        },
        releaseFocus: function(){
            if (!this.isCreated) return;
            this.$el.off('keydown', this.onTrapFocusKeydown);
        },
        onTrapFocusKeydown: function(e){
            // If TAB key pressed
            if (e.keyCode === 9) {
                var self = e.data.self, $target = $(e.target), $dialog = $target.parents('[role=dialog]');
                // If inside a Modal dialog (determined by attribute role="dialog")
                if ($dialog.length) {
                    // Find first or last input element in the dialog parent (depending on whether Shift was pressed).
                    var $focusable = $dialog.find(self.opt.focusable.include).not(self.opt.focusable.exclude),
                        $first = $focusable.first(), $last = $focusable.last(),
                        $boundary = e.shiftKey ? $first : $last,
                        $new = e.shiftKey ? $last : $first;

                    if ($boundary.length && $target.is($boundary)) {
                        e.preventDefault();
                        $new.trigger('focus');
                    }
                }
            }
        },
        onKeyDown: function(e){
            var self = e.data.self;
            switch (e.which){
                case 39: case 40: self.next(); break;
                case 37: case 38: self.prev(); break;
                case 27:
                    var button;
                    if (self.isFullscreen){
                        button = self.buttons.get("fullscreen");
                        button.exit();
                    } else if (self.isMaximized && self.isInline){
                        button = self.buttons.get("maximize");
                        button.exit();
                    } else if (self.opt.buttons.close) {
                        self.close();
                    }
                    break;
            }
        }
    });


    _.template.configure("core", {
        panel: {
            classNames: "",
            noMobile: false,
            hoverButtons: false,
            icons: "default",
            transition: "none", // none | fade | horizontal | vertical

            // the below are CSS class names to use, if not supplied the lightbox will inherit the value from the gallery
            theme: null,
            button: null,
            highlight: null,
            loadingIcon: null,
            hoverIcon: null,
            videoIcon: null,
            stickyVideoIcon: null,
            hoverColor: null,
            hoverScale: null,
            insetShadow: null,
            filter: null,

            loop: true,
            autoProgress: 0,
            autoProgressStart: true,
            fitMedia: false,
            keyboard: true,
            noScrollbars: true,
            swipe: true,
            stackSideAreas: true,
            preserveButtonSpace: true,
            buttonsPortrait: "top", // top | bottom
            buttonsLandscape: "right", // left | right
            admin: false,

            info: "bottom", // none | top | bottom | left | right
            infoVisible: false,
            infoOverlay: true,
            infoAutoHide: true,
            infoAlign: "default", // default | left | center | right | justified
            exif: "none", // none | full | partial | minimal

            cart: "none", // none | top | bottom | left | right
            cartAutoHide: true,
            cartVisible: false,
            cartOriginal: false,
            cartOverlay: true,
            cartAjax: null,
            cartNonce: null,
            cartTimeout: null,

            thumbs: "none", // none | top | bottom | left | right
            thumbsVisible: true,
            thumbsCaptions: true,
            thumbsCaptionsAlign: "default", // default | left | center | right | justified
            thumbsSmall: false,
            thumbsBestFit: true,

            focusable: {
                include: 'a[href], area[href], input, select, textarea, button, iframe, object, embed, [tabindex], [contenteditable]',
                exclude: '[tabindex=-1], [disabled], :hidden'
            },

            buttons: {
                prev: true,
                next: true,
                close: true,
                maximize: true,
                fullscreen: true,
                autoProgress: true,
                info: true,
                thumbs: false,
                cart: true
            },
            breakpoints: [{
                name: "medium",
                width: 800,
                height: 800
            },{
                name: "large",
                width: 1024,
                height: 1024
            },{
                name: "x-large",
                width: 1280,
                height: 1280
            }]
        }
    },{
        panel: {
            elem: "fg-panel",
            maximized: "fg-panel-maximized",
            fullscreen: "fg-panel-fullscreen",

            fitMedia: "fg-panel-fit-media",
            noScrollbars: "fg-panel-no-scroll",
            stackSideAreas: "fg-panel-area-stack",
            preserveButtonSpace: "fg-panel-preserve-button-space",
            hoverButtons: "fg-panel-hover-buttons",
            stickyVideoIcon: "fg-video-sticky",
            hoverScale: "fg-hover-scale",
            noMobile: "fg-panel-no-mobile",

            loader: "fg-loader",

            states: {
                idle: "fg-idle",
                loading: "fg-loading",
                loaded: "fg-loaded",
                error: "fg-error",
                visible: "fg-visible",
                reverse: "fg-reverse",
                toggled: "fg-toggled",
                selected: "fg-selected",
                disabled: "fg-disabled",
                hidden: "fg-hidden",
                started: "fg-started",
                stopped: "fg-stopped",
                paused: "fg-paused",
                noTransitions: "fg-no-transitions"
            },

            buttons: {
                portrait: {
                    top: "fg-panel-buttons-top",
                    bottom: "fg-panel-buttons-bottom"
                },
                landscape: {
                    right: "fg-panel-buttons-right",
                    left: "fg-panel-buttons-left"
                },
                container: "fg-panel-buttons",
                prev: "fg-panel-button fg-panel-button-prev",
                next: "fg-panel-button fg-panel-button-next",
                autoProgress: "fg-panel-button fg-panel-button-progress",
                close: "fg-panel-button fg-panel-button-close",
                fullscreen: "fg-panel-button fg-panel-button-fullscreen",
                maximize: "fg-panel-button fg-panel-button-maximize",
                info: "fg-panel-button fg-panel-button-info",
                thumbs: "fg-panel-button fg-panel-button-thumbs",
                cart: "fg-panel-button fg-panel-button-cart"
            },

            transition: {
                fade: "fg-panel-fade",
                horizontal: "fg-panel-horizontal",
                vertical: "fg-panel-vertical"
            },

            area: {
                elem: "fg-panel-area",
                inner: "fg-panel-area-inner"
            },

            content: {},

            sideArea: {
                toggle: "fg-panel-area-toggle",
                button: "fg-panel-area-button",
                visible: "fg-panel-area-visible",
                position: {
                    top: "fg-panel-area-top",
                    right: "fg-panel-area-right",
                    bottom: "fg-panel-area-bottom",
                    left: "fg-panel-area-left"
                }
            },

            info: {
                overlay: "fg-panel-info-overlay",
                align: {
                    left: "fg-panel-media-caption-left",
                    center: "fg-panel-media-caption-center",
                    right: "fg-panel-media-caption-right",
                    justified: "fg-panel-media-caption-justified"
                }
            },

            cart: {
                original: "fg-panel-cart-original"
            },

            thumbs: {
                prev: "fg-panel-thumbs-button fg-panel-thumbs-prev",
                next: "fg-panel-thumbs-button fg-panel-thumbs-next",
                viewport: "fg-panel-thumbs-viewport",
                stage: "fg-panel-thumbs-stage",
                noCaptions: "fg-panel-thumbs-no-captions",
                small: "fg-panel-thumbs-small",
                spacer: "fg-panel-thumb-spacer",
                thumb: {
                    elem: "fg-panel-thumb",
                    media: "fg-panel-thumb-media",
                    overlay: "fg-panel-thumb-overlay",
                    wrap: "fg-panel-thumb-wrap",
                    image: "fg-panel-thumb-image",
                    caption: "fg-panel-thumb-caption",
                    title: "fg-panel-thumb-title",
                    description: "fg-panel-thumb-description"
                },
                align: {
                    left: "fg-panel-thumb-caption-left",
                    center: "fg-panel-thumb-caption-center",
                    right: "fg-panel-thumb-caption-right",
                    justified: "fg-panel-thumb-caption-justified"
                }
            }
        }
    },{
        panel: {
            buttons: {
                prev: "Previous Media",
                next: "Next Media",
                close: "Close Modal",
                maximize: "Toggle Maximize",
                fullscreen: "Toggle Fullscreen",
                autoProgress: "Auto Progress",
                info: "Toggle Information",
                thumbs: "Toggle Thumbnails",
                cart: "Toggle Cart"
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.obj,
    FooGallery.utils.fn,
    FooGallery.utils.transition
);
(function($, _, _icons, _utils, _is){

    _.Panel.Buttons = _utils.Class.extend({
        construct: function(panel){
            this.panel = panel;

            this.opt = panel.opt.buttons;

            this.cls = panel.cls.buttons;

            this.sel = panel.sel.buttons;

            this.il8n = panel.il8n.buttons;

            this.$el = null;

            this.isCreated = false;

            this.isAttached = false;

            this.__registered = [];

            this.registerCore();
        },

        registerCore: function(){
            this.register(new _.Panel.Button(this.panel, "prev", {
                icon: "arrow-left",
                label: this.il8n.prev,
                click: this.panel.prev.bind(this.panel),
                beforeLoad: function (media) {
                    this.disable(this.panel.prevItem == null);
                }
            }), 10);
            this.register(new _.Panel.Button(this.panel, "next", {
                icon: "arrow-right",
                label: this.il8n.next,
                click: this.panel.next.bind(this.panel),
                beforeLoad: function (media) {
                    this.disable(this.panel.nextItem == null);
                }
            }), 20);
            this.register(new _.Panel.AutoProgress(this.panel), 30);

            // area buttons are inserted by default with priority 99

            this.register(new _.Panel.Maximize(this.panel), 180);
            this.register(new _.Panel.Fullscreen(this.panel), 190);
            this.register(new _.Panel.Button(this.panel, "close", {
                icon: "close",
                label: this.il8n.close,
                click: this.panel.close.bind(this.panel)
            }), 200);
        },

        register: function( button, priority ){
            if (button instanceof _.Panel.Button){
                return this.__registered.push({
                    name: button.name,
                    button: button,
                    priority: _is.number(priority) ? priority : 99
                }) - 1;
            }
            return -1;
        },

        get: function( name ){
            var button = null;
            for (var i = 0, l = this.__registered.length; i < l; i++){
                if (this.__registered[i].name !== name) continue;
                button = this.__registered[i].button;
                break;
            }
            return button;
        },

        each: function(callback, prioritize){
            var self = this;
            if (prioritize){
                self.__registered.sort(function(a, b){
                    return a.priority - b.priority;
                });
            }
            self.__registered.forEach(function(registered){
                callback.call(self, registered.button);
            });
        },

        toggle: function( name, visible ){
            var button = this.get(name);
            if (button == null) return;
            button.toggle(visible);
        },

        disable: function( name, disable ){
            var button = this.get(name);
            if (button == null) return;
            button.disable(disable);
        },

        press: function( name, pressed ){
            var self = this, button = self.get(name);
            if ( self.panel.isSmallScreen && pressed && _is.string(button.groupName) ){
                self.each(function(btn){
                    if ( button !== btn && btn instanceof _.Panel.SideAreaButton && button.groupName === btn.groupName ){
                        btn.area.toggle( false );
                    }
                });
            }
            button.press( pressed );
        },

        destroy: function(){
            var self = this;
            var e = self.panel.trigger("buttons-destroy", [self]);
            if (!e.isDefaultPrevented()) {
                self.isCreated = !self.doDestroy();
            }
            if (!self.isCreated) {
                self.panel.trigger("buttons-destroyed", [self]);
            }
            return !self.isCreated;
        },
        doDestroy: function(){
            var self = this;
            self.each(function(button){
                button.destroy();
            });
            if (self.isCreated){
                self.detach();
                self.$el.remove();
            }
            return true;
        },
        create: function(){
            var self = this;
            if (!self.isCreated) {
                var e = self.panel.trigger("buttons-create", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isCreated = self.doCreate();
                }
                if (self.isCreated) {
                    self.panel.trigger("buttons-created", [self]);
                }
            }
            return self.isCreated;
        },
        doCreate: function(){
            var self = this;
            self.$el = $('<div/>').addClass(self.cls.container);

            self.each(function(button){
                button.appendTo(self.$el);
            }, true);

            return true;
        },
        appendTo: function( parent ){
            var self = this;
            if (!self.isCreated){
                self.create();
            }
            if (self.isCreated && !self.isAttached){
                var e = self.panel.trigger("buttons-append", [self, parent]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = self.doAppendTo( parent );
                }
                if (self.isAttached) {
                    self.panel.trigger("buttons-appended", [self, parent]);
                }
            }
            return self.isAttached;
        },
        doAppendTo: function( parent ){
            this.$el.appendTo( parent );
            return this.$el.parent().length > 0;
        },
        detach: function(){
            var self = this;
            if (self.isCreated && self.isAttached) {
                var e = self.panel.trigger("buttons-detach", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = !self.doDetach();
                }
                if (!self.isAttached) {
                    self.panel.trigger("buttons-detached", [self]);
                }
            }
            return !self.isAttached;
        },
        doDetach: function(){
            this.$el.detach();
            return true;
        },

        beforeLoad: function(media){
            this.each(function(button){
                button.beforeLoad(media);
            });
        },

        afterLoad: function(media){
            this.each(function(button){
                button.afterLoad(media);
            });
        },

        close: function(){
            this.each(function(button){
                button.close();
            });
        },

        resize: function(){
            this.each(function(button){
                button.resize();
            });
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.icons,
    FooGallery.utils,
    FooGallery.utils.is
);
(function($, _, _icons, _utils, _is, _obj){

    _.Panel.Button = _utils.Class.extend({
        construct: function(panel, name, options){
            this.panel = panel;
            this.name = name;
            this.opt = _obj.extend({
                icon: null,
                label: null,
                visible: !!panel.opt.buttons[name],
                disabled: false,
                toggle: false,
                pressed: false,
                group: null,
                click: $.noop,
                beforeLoad: $.noop,
                afterLoad: $.noop,
                close: $.noop,
                resize: $.noop
            }, options);
            this.cls = {
                elem: panel.cls.buttons[name],
                states: panel.cls.states
            };
            this.$el = null;
            this.groupName = this.opt.group;
            this.isVisible = this.opt.visible;
            this.isDisabled = this.opt.disabled;
            this.isToggle = this.opt.toggle;
            this.isPressed = this.opt.pressed;
            this.isCreated = false;
            this.isAttached = false;
        },
        isEnabled: function(){
            return this.panel.opt.buttons.hasOwnProperty(this.name) && this.panel.opt.buttons[this.name];
        },
        create: function(){
            var self = this;
            if (!self.isCreated){
                self.$el = $('<button/>', {
                    type: 'button',
                    "aria-label": self.opt.label,
                    "aria-disabled": self.isDisabled,
                    "aria-hidden": !self.isVisible
                }).addClass(self.cls.elem).on("click.foogallery", {self: self}, self.onButtonClick);
                if (_is.string(self.opt.icon)){
                    self.$el.append(_icons.get(self.opt.icon, self.panel.opt.icons));
                } else if (_is.array(self.opt.icon)){
                    self.opt.icon.forEach(function(icon){
                        self.$el.append(_icons.get(icon, self.panel.opt.icons));
                    });
                } else if (_is.fn(self.opt.icon)){
                    self.$el.append(self.opt.icon.call(this));
                }
                self.isCreated = true;
                var enabled = self.isEnabled();
                self.toggle(enabled);
                self.disable(!enabled);
                if (self.isToggle) self.press( self.opt.pressed );
            }
            return self.isCreated;
        },
        destroy: function(){
            if (this.isCreated){
                this.$el.off("click.foogallery").remove();
                this.isCreated = false;
            }
            return !this.isCreated;
        },
        appendTo: function(parent){
            if ((this.isCreated || this.create()) && !this.isAttached){
                this.$el.appendTo(parent);
            }
            return this.isAttached;
        },
        detach: function(){
            if (this.isCreated && this.isAttached){
                this.$el.detach();
            }
            return !this.isAttached;
        },
        toggle: function(visible){
            if (!this.isCreated) return;
            this.isVisible = _is.boolean(visible) ? visible : !this.isVisible;
            this.$el.toggleClass(this.cls.states.hidden, !this.isVisible).attr("aria-hidden", !this.isVisible);
        },
        disable: function(disabled){
            if (!this.isCreated) return;
            this.isDisabled = _is.boolean(disabled) ? disabled : !this.isDisabled;
            this.$el.toggleClass(this.cls.states.disabled, this.isDisabled).attr({
                "aria-disabled": this.isDisabled,
                "disabled": this.isDisabled
            });
        },
        press: function(pressed){
            if ( !this.isCreated ) return;
            this.isPressed = pressed;
            this.$el.attr("aria-pressed", this.isPressed);
        },
        beforeLoad: function(media){
            this.opt.beforeLoad.call(this, media);
        },
        afterLoad: function(media){
            this.opt.afterLoad.call(this, media);
        },
        close: function(){
            this.opt.close.call(this);
        },
        click: function(){
            this.opt.click.call(this);
        },
        resize: function(){
            this.opt.resize.call(this);
        },
        onButtonClick: function (e) {
            e.preventDefault();
            e.data.self.click();
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.icons,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.obj
);
(function($, _, _is){

    _.Panel.SideAreaButton = _.Panel.Button.extend({
        construct: function(area){
            this._super(area.panel, area.name, {
                icon: area.opt.icon,
                label: area.opt.label,
                autoHideArea: area.opt.autoHide,
                click: area.toggle.bind(area),
                toggle: true,
                pressed: area.opt.visible,
                group: area.opt.group
            });
            this.area = area;
            this.__isVisible = null;
            this.__autoHide = null;
        },
        beforeLoad: function(media){
            var enabled = this.area.isEnabled(), supported = enabled && this.area.canLoad(media);
            if (!supported && this.__isVisible == null){
                this.__isVisible = this.area.isVisible;
                this.area.toggle(false);
            } else if (supported && _is.boolean(this.__isVisible)) {
                this.area.toggle(this.__isVisible);
                this.__isVisible = null;
            }
            if (enabled) this.disable(!supported);
            else this.toggle(supported);

            this.checkAutoHide(enabled, supported);

            this.opt.beforeLoad.call(this, media);
        },
        checkAutoHide: function(enabled, supported){
            if (enabled && supported && this.opt.autoHideArea === true){
                if (this.__autoHide == null && this.panel.isSmallScreen) {
                    this.__autoHide = this.area.isVisible;
                    this.area.toggle(false);
                    this.area.button.toggle(true);
                } else if (_is.boolean(this.__autoHide) && !this.panel.isSmallScreen) {
                    this.area.button.toggle(this.area.button.isEnabled() && this.area.opt.toggle);
                    this.area.toggle(this.__autoHide);
                    this.__autoHide = null;
                }
            }
        },
        resize: function(){
            var enabled = this.area.isEnabled(), supported = enabled && this.area.canLoad(this.area.currentMedia);
            this.checkAutoHide(enabled, supported);
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils.is
);
(function($, _, _utils){

    _.Panel.AutoProgress = _.Panel.Button.extend({
        construct: function(panel){
            var self = this;
            self.__stopped = !panel.opt.autoProgressStart;
            self.__timer = new _utils.Timer();
            self._super(panel, "autoProgress", {
                icon: "auto-progress",
                label: panel.il8n.buttons.autoProgress
            });
            self.$icon = null;
            self.$circle = null;
            self.circumference = 0;
        },
        isEnabled: function(){
            return this._super() && this.panel.opt.autoProgress > 0;
        },
        create: function () {
            if (this._super()){
                this.$icon = this.$el.find("svg");
                this.$circle = this.$icon.find("circle");
                var radius = parseFloat(this.$circle.attr("r"));
                this.circumference = (radius * 2) * Math.PI;
                this.$circle.css({
                    "stroke-dasharray": this.circumference + ' ' + this.circumference,
                    "stroke-dashoffset": this.circumference + ''
                });
                this.__timer.on({
                    "start resume": this.onStartOrResume,
                    "pause": this.onPause,
                    "stop": this.onStop,
                    "tick": this.onTick,
                    "complete reset": this.onCompleteOrReset,
                    "complete": this.onComplete
                }, this);
            }
            return this.isCreated;
        },
        close: function(){
            this.__timer.pause();
            this._super();
        },
        destroy: function(){
            this.__timer.destroy();
            return this._super();
        },
        beforeLoad: function(media){
            if (this.isEnabled()) {
                this.__timer.reset();
            }
            this._super(media);
        },
        afterLoad: function(media){
            if (this.isEnabled()) {
                this.__timer.countdown(this.panel.opt.autoProgress);
                if (this.__stopped) this.__timer.pause();
            }
            this._super(media);
        },
        click: function(){
            if (this.__timer.isRunning){
                this.__stopped = true;
                this.__timer.pause();
            } else if (this.__timer.canResume) {
                this.__stopped = false;
                this.__timer.resume();
            } else {
                this.__stopped = false;
                this.__timer.restart();
            }
            this._super();
        },
        onStartOrResume: function(){
            this.$icon.removeClass(this.cls.states.allProgress).addClass(this.cls.states.started);
        },
        onPause: function(){
            this.$icon.removeClass(this.cls.states.allProgress).addClass(this.cls.states.paused);
        },
        onStop: function(){
            this.$icon.removeClass(this.cls.states.allProgress).addClass(this.cls.states.stopped);
        },
        onTick: function(e, current, time){
            var percent = current / time * 100;
            this.$circle.css("stroke-dashoffset", this.circumference - percent / 100 * this.circumference);
        },
        onCompleteOrReset: function(){
            this.$icon.removeClass(this.cls.states.allProgress);
        },
        onComplete: function(){
            this.panel.next();
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils
);
(function($, _, _utils){

    _.fullscreen = new _utils.FullscreenAPI();

    _.Panel.Fullscreen = _.Panel.Button.extend({
        construct: function(panel){
            var self = this;
            self._super(panel, "fullscreen", {
                icon: ["expand", "shrink"],
                label: panel.il8n.buttons.fullscreen,
                toggle: true
            });
        },
        isEnabled: function(){
            return this._super() && _.fullscreen.supported;
        },
        click: function(){
            var self = this, pnl = self.panel.el;
            _.fullscreen.toggle(pnl).then(function(){
                if (_.fullscreen.element() === pnl){
                    _.fullscreen.on("change error", self.onFullscreenChange, self);
                    self.enter();
                } else {
                    _.fullscreen.off("change error", self.onFullscreenChange, self);
                    self.exit();
                }
            }, function(err){
                console.debug('Error toggling fullscreen on element.', err, pnl);
            });
            self._super();
        },
        onFullscreenChange: function(){
            if (_.fullscreen.element() !== this.panel.el){
                this.exit();
            }
        },
        enter: function(){
            if (this.panel.isFullscreen) return;
            this.panel.isFullscreen = true;
            this.panel.$el.addClass(this.panel.cls.fullscreen);
            if (!this.panel.isMaximized){
                this.panel.$el.attr({
                    'role': 'dialog',
                    'aria-modal': true
                }).trigger('focus');
                this.panel.trapFocus();
            }
            this.panel.buttons.press('fullscreen', true);
            this.panel.buttons.toggle('maximize', false);
        },
        exit: function(){
            if (!this.panel.isFullscreen) return;
            this.panel.$el.removeClass(this.panel.cls.fullscreen);
            if (!this.panel.isMaximized){
                this.panel.$el.attr({
                    'role': null,
                    'aria-modal': null
                }).trigger('focus');
                this.panel.releaseFocus();
            }
            this.panel.buttons.press('fullscreen', false);
            this.panel.buttons.toggle('maximize', this.panel.isInline && this.panel.buttons.opt.maximize);
            this.panel.isFullscreen = false;
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils
);
(function($, _, _is){

    _.Panel.Maximize = _.Panel.Button.extend({
        construct: function(panel){
            this._super(panel, "maximize", {
                icon: "maximize",
                label: panel.il8n.buttons.maximize,
                toggle: true
            });
            this.scrollPosition = [];
            this.$placeholder = $("<span/>");
        },
        click: function(){
            this.set(!this.panel.isMaximized);
            this._super();
        },
        close: function(){
            this.exit();
            this._super();
        },
        set: function(maximized, visible){
            if (maximized) this.enter();
            else this.exit();
            visible = _is.boolean(visible) ? visible : this.isVisible;
            this.toggle(visible);
        },
        enter: function(){
            if (this.panel.isMaximized) return;
            this.panel.isMaximized = true;
            this.$placeholder.insertAfter(this.panel.$el);
            this.panel.$el.appendTo("body").addClass(this.panel.cls.maximized).attr({
                'role': 'dialog',
                'aria-modal': true
            }).trigger('focus');
            this.panel.buttons.press('maximize', true);
            this.panel.trapFocus();
            if (this.panel.opt.noScrollbars){
                this.scrollPosition = [window.scrollX, window.scrollY];
                $("html").addClass(this.panel.cls.noScrollbars);
            }
        },
        exit: function(){
            if (!this.panel.isMaximized) return;
            this.panel.isMaximized = false;
            this.panel.$el.removeClass(this.panel.cls.maximized).attr({
                'role': null,
                'aria-modal': null
            }).insertBefore(this.$placeholder);
            if (this.panel.isInline) this.panel.$el.trigger('focus');
            this.$placeholder.detach();
            this.panel.buttons.press('maximize', false);
            this.panel.releaseFocus();
            if (this.panel.opt.noScrollbars){
                $("html").removeClass(this.panel.cls.noScrollbars)
                    .prop("clientWidth"); // query the clientWidth to force the class to be removed prior to setting the scroll position
                if (_is.array(this.scrollPosition) && this.scrollPosition.length === 2){
                    window.scrollTo(this.scrollPosition[0], this.scrollPosition[1]);
                }
                this.scrollPosition = [];
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils.is
);
(function($, _, _utils, _is, _fn, _obj, _str){

    /**
     * @memberof FooGallery.Panel.
     * @class Area
     * @augments FooGallery.utils.Class
     */
    _.Panel.Area = _utils.Class.extend(/** @lends FooGallery.Panel.Area */{
        /**
         * @ignore
         * @constructs
         * @param panel
         * @param name
         * @param options
         * @param classes
         */
        construct: function(panel, name, options, classes){
            this.panel = panel;
            this.name = name;
            this.opt = _obj.extend({
                waitForUnload: true
            }, options);
            this.cls = _obj.extend({
                elem: this.__cls(panel.cls.area.elem, name, true),
                inner: this.__cls(panel.cls.area.inner, name, true)
            }, classes);
            this.sel = _utils.selectify(this.cls);
            this.currentMedia = null;
            this.$el = null;
            this.$inner = null;
            this.isCreated = false;
            this.isAttached = false;
        },
        __cls: function(cls, replacement, andOriginal){
            var formatted = cls.replace(/-area($|-)/, "-" + replacement + "$1");
            return andOriginal ? [ cls, formatted ].join(" ") : formatted;
        },
        create: function(){
            var self = this;
            if (!self.isCreated) {
                var e = self.panel.trigger("area-create", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isCreated = self.doCreate();
                }
                if (self.isCreated) {
                    self.panel.trigger("area-created", [self]);
                }
            }
            return self.isCreated;
        },
        doCreate: function(){
            this.$el = $("<div/>").addClass(this.cls.elem);
            this.$inner = $("<div/>").addClass(this.cls.inner).appendTo(this.$el);
            return true;
        },
        destroy: function(){
            var self = this;
            if (self.isCreated){
                var e = self.panel.trigger("area-destroy", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isCreated = !self.doDestroy();
                }
                if (!self.isCreated) {
                    self.panel.trigger("area-destroyed", [self]);
                }
            }
            return !self.isCreated;
        },
        doDestroy: function(){
            if (this.currentMedia instanceof _.Panel.Media){
                this.currentMedia.detach();
            }
            this.$el.remove();
            return true;
        },
        appendTo: function( parent ){
            var self = this;
            if (!self.isCreated){
                self.create();
            }
            if (self.isCreated && !self.isAttached){
                var e = self.panel.trigger("area-append", [self, parent]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = self.doAppendTo( parent );
                }
                if (self.isAttached) {
                    self.panel.trigger("area-appended", [self, parent]);
                }
            }
            return self.isAttached;
        },
        doAppendTo: function( parent ){
            this.$el.appendTo( parent );
            return this.$el.parent().length > 0;
        },
        detach: function(){
            var self = this;
            if (self.isCreated && self.isAttached) {
                var e = self.panel.trigger("area-detach", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = !self.doDetach();
                }
                if (!self.isAttached) {
                    self.panel.trigger("area-detached", [self]);
                }
            }
            return !self.isAttached;
        },
        doDetach: function(){
            this.$el.detach();
            return true;
        },
        load: function(media){
            var self = this;
            if (!(media instanceof _.Panel.Media)) return _fn.reject("unable to load media");
            return $.Deferred(function(def){
                var reverseTransition = self.shouldReverseTransition(self.currentMedia, media);
                var e = self.panel.trigger("area-load", [self, media, reverseTransition]);
                if (e.isDefaultPrevented()){
                    def.rejectWith("default prevented");
                    return;
                }
                var hasMedia = self.currentMedia instanceof _.Panel.Media, prev = self.currentMedia;
                if (self.opt.waitForUnload && hasMedia){
                    self.panel.trigger("area-unload", [self, prev]);
                    self.doUnload(prev, reverseTransition).then(function(){
                        self.panel.trigger("area-unloaded", [self, prev]);
                        self.currentMedia = media;
                        self.panel.trigger("area-load", [self, media]);
                        self.doLoad(media, reverseTransition).then(def.resolve).fail(def.reject);
                    }).fail(def.reject);
                } else {
                    if (hasMedia){
                        self.panel.trigger("area-unload", [self, prev]);
                        self.doUnload(prev, reverseTransition).then(function(){
                            self.panel.trigger("area-unloaded", [self, prev]);
                        });
                    }
                    self.currentMedia = media;
                    self.panel.trigger("area-load", [self, media]);
                    self.doLoad(media, reverseTransition).then(def.resolve).fail(def.reject);
                }
            }).then(function(){
                self.panel.trigger("area-loaded", [self, media]);
            }).fail(function(){
                self.panel.trigger("area-error", [self, media]);
            }).promise();
        },
        doLoad: function(media, reverseTransition){
            return _fn.resolved;
        },
        doUnload: function(media, reverseTransition){
            return _fn.resolved;
        },
        close: function(immediate){
            var self = this;
            if (self.currentMedia instanceof _.Panel.Media){
                var current = self.currentMedia;
                if (!immediate){
                    self.panel.trigger("area-unload", [self, current]);
                    return self.doUnload(current, false).then(function() {
                        self.panel.trigger("area-unloaded", [self, current]);
                        self.currentMedia = null;
                    });
                }
                self.panel.trigger("area-unload", [self, current]);
                self.doUnload(current, false).then(function(){
                    self.panel.trigger("area-unloaded", [self, current]);
                });
                self.currentMedia = null;
            }
            return _fn.resolved;
        },
        shouldReverseTransition: function( oldMedia, newMedia ){
            if (!(oldMedia instanceof _.Panel.Media) || !(newMedia instanceof _.Panel.Media)) return true;
            var result = oldMedia.item.index < newMedia.item.index,
                last = this.panel.tmpl.items.last(this.panel.isVisible);
            if (last instanceof _.Item && ((newMedia.item.index === 0 && oldMedia.item.index === last.index) || (newMedia.item.index === last.index && oldMedia.item.index === 0))){
                result = !result;
            }
            return result;
        },
        listen: function(){},
        stopListening: function(){},
        resize: function(){}
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.obj,
    FooGallery.utils.str
);
(function($, _, _fn, _t){

    /**
     * @memberof FooGallery.Panel.
     * @class Content
     * @augments FooGallery.Panel.Area
     */
    _.Panel.Content = _.Panel.Area.extend(/** @lends FooGallery.Panel.Content */{
        /**
         * @ignore
         * @constructs
         * @param panel
         */
        construct: function(panel){
            this._super(panel, "content", {
                waitForUnload: false
            }, panel.cls.content);
            this.robserver = null;
        },
        doCreate: function(){
            var self = this;
            if (self._super()){
                if (self.panel.opt.swipe){
                    self.$inner.fgswipe({data: {self: self}, swipe: self.onSwipe, allowPageScroll: true});
                }
                self.robserver = new ResizeObserver(_fn.throttle(function () {
                    if (self.panel instanceof _.Panel && !self.panel.destroying && !self.panel.destroyed) {
                        // only the inner is being observed so if a change occurs we can safely just call resize
                        self.resize();
                    }
                }, 50));
                self.robserver.observe(self.$inner.get(0));
                return true;
            }
            return false;
        },
        doDestroy: function(){
            if (this.robserver instanceof ResizeObserver){
                this.robserver.disconnect();
            }
            this.$inner.fgswipe("destroy");
            return this._super();
        },
        doLoad: function(media, reverseTransition){
            var self = this, states = self.panel.cls.states;
            return $.Deferred(function (def) {
                if (!media.isCreated) media.create();
                media.$el.toggleClass(states.reverse, reverseTransition);
                media.appendTo(self.$inner);
                var wait = [];
                if (self.panel.hasTransition){
                    wait.push(_t.start(media.$el, function($el){
                        $el.addClass(states.visible);
                    }, null, 350));
                } else {
                    media.$el.addClass(states.visible);
                }
                wait.push(media.load());
                $.when.apply($, wait).then(def.resolve).fail(def.reject);
            }).promise();
        },
        doUnload: function(media, reverseTransition){
            var self = this, states = self.panel.cls.states;
            return $.Deferred(function (def) {
                var wait = [];
                if (media.isCreated){
                    media.$el.toggleClass(states.reverse, !reverseTransition);
                    if (self.panel.hasTransition){
                        wait.push(_t.start(media.$el, function($el){
                            $el.removeClass(states.visible);
                        }, null, 350));
                    } else {
                        media.$el.removeClass(states.visible);
                    }
                }
                wait.push(media.unload());
                $.when.apply($, wait).then(def.resolve).fail(def.reject);
            }).always(function(){
                if (media.isCreated){
                    media.$el.removeClass(states.reverse);
                }
                media.detach();
            }).promise();
        },
        onSwipe: function(info, data){
            var self = data.self;
            if (info.direction === "E"){
                self.panel.prev();
            }
            if (info.direction === "W"){
                self.panel.next();
            }
        },
        resize: function(){
            if (this.currentMedia instanceof _.Panel.Media){
                this.currentMedia.resize();
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils.fn,
    FooGallery.utils.transition
);
(function($, _, _icons, _utils, _is, _fn, _obj){

    /**
     * @memberof FooGallery.Panel.
     * @class Area
     * @augments FooGallery.Panel.SideArea
     */
    _.Panel.SideArea = _.Panel.Area.extend(/** @lends FooGallery.Panel.SideArea */{
        /**
         * @ignore
         * @constructs
         * @param panel
         * @param name
         * @param options
         * @param classes
         */
        construct: function(panel, name, options, classes){
            var self = this, cls = panel.cls.sideArea;
            self._super(panel, name, _obj.extend({
                icon: null,
                label: null,
                position: null,
                visible: true,
                autoHide: false,
                toggle: !!panel.opt.buttons[name]
            }, options), _obj.extend({
                toggle: this.__cls(cls.toggle, name, true),
                visible: this.__cls(cls.visible, name),
                position: {
                    top: this.__cls(cls.position.top, name),
                    right: this.__cls(cls.position.right, name),
                    bottom: this.__cls(cls.position.bottom, name),
                    left: this.__cls(cls.position.left, name),
                }
            }, classes));
            self.isVisible = self.opt.visible;
            self.allPositionClasses = Object.keys(self.cls.position).map(function (key) {
                return self.cls.position[key];
            }).join(" ");
            self.button = self.registerButton();
        },
        registerButton: function(){
            var btn = new _.Panel.SideAreaButton(this);
            this.panel.buttons.register(btn);
            return btn;
        },
        doCreate: function(){
            if (this._super()){
                if (this.opt.toggle){
                    $('<button/>', {type: 'button'}).addClass(this.cls.toggle)
                        .append(_icons.get("circle-close", this.panel.opt.icons))
                        .on("click.foogallery", {self: this}, this.onToggleClick)
                        .appendTo(this.$inner);
                }
                if (this.isEnabled()){
                    this.panel.$el.toggleClass(this.cls.visible, this.isVisible);
                    this.setPosition( this.opt.position );
                }
                return true;
            }
            return false;
        },
        isEnabled: function(){
            return this.cls.position.hasOwnProperty(this.opt.position);
        },
        canLoad: function(media){
            return media instanceof _.Panel.Media;
        },
        getPosition: function(){
            if (this.isEnabled()){
                return this.cls.position[this.opt.position];
            }
            return null;
        },
        setPosition: function( position ){
            this.opt.position = this.cls.position.hasOwnProperty(position) ? position : null;
            if (_is.jq(this.panel.$el)){
                this.panel.$el.removeClass(this.allPositionClasses).addClass(this.getPosition());
            }
        },
        toggle: function( visible ){
            this.isVisible = _is.boolean(visible) ? visible : !this.isVisible;
            if (_is.jq(this.panel.$el)) {
                this.panel.$el.toggleClass(this.cls.visible, this.isVisible);
            }
            this.panel.buttons.press( this.name, this.isVisible );
        },
        onToggleClick: function(e){
            e.preventDefault();
            e.data.self.toggle();
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.icons,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.obj
);
(function($, _, _is, _fn){

    /**
     * @memberof FooGallery.Panel.
     * @class Info
     * @augments FooGallery.Panel.SideArea
     */
    _.Panel.Info = _.Panel.SideArea.extend(/** @lends FooGallery.Panel.Info */{
        /**
         * @ignore
         * @constructs
         * @param panel
         */
        construct: function(panel){
            this._super(panel, "info", {
                icon: "info",
                label: panel.il8n.buttons.info,
                position: panel.opt.info,
                overlay: panel.opt.infoOverlay,
                visible: panel.opt.infoVisible,
                autoHide: panel.opt.infoAutoHide,
                align: panel.opt.infoAlign,
                waitForUnload: false,
                group: "overlay"
            }, panel.cls.info);
            this.allPositionClasses += " " + this.cls.overlay;
        },
        doCreate: function(){
            var self = this;
            if (self.isEnabled() && self._super()) {
                if (_is.string(self.opt.align) && self.cls.align.hasOwnProperty(self.opt.align)){
                    self.panel.$el.addClass(self.cls.align[self.opt.align]);
                }
                return true;
            }
            return false;
        },
        getPosition: function(){
            var result = this._super();
            return result != null && this.opt.overlay ? result + " " + this.cls.overlay : result;
        },
        setPosition: function( position, overlay ){
            if (_is.boolean(overlay)) this.opt.overlay = overlay;
            this._super( position );
        },
        canLoad: function(media){
            return this._super(media) && media.caption.canLoad();
        },
        doLoad: function(media, reverseTransition){
            if (this.canLoad(media)){
                media.caption.appendTo(this.$inner);
                media.caption.load();
            }
            return _fn.resolved;
        },
        doUnload: function(media, reverseTransition){
            media.caption.unload();
            media.caption.detach();
            return _fn.resolved;
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils.is,
    FooGallery.utils.fn
);
(function($, _, _icons, _utils, _is, _fn, _t){

    /**
     * @memberof FooGallery.Panel.
     * @class Thumbs
     * @augments FooGallery.Panel.SideArea
     */
    _.Panel.Thumbs = _.Panel.SideArea.extend(/** @lends FooGallery.Panel.Thumbs */{
        /**
         * @ignore
         * @constructs
         * @param panel
         */
        construct: function(panel){
            this._super(panel, "thumbs", {
                icon: "thumbs",
                label: panel.il8n.buttons.thumbs,
                position: panel.opt.thumbs,
                captions: panel.opt.thumbsCaptions,
                align: panel.opt.thumbsCaptionsAlign,
                small: panel.opt.thumbsSmall,
                bestFit: panel.opt.thumbsBestFit,
                toggle: false,
                waitForUnload: false
            }, panel.cls.thumbs);

            this.iobserver = null;
            this.robserver = null;
            this.$prev = null;
            this.$next = null;
            this.$viewport = null;
            this.$stage = null;
            this.$dummy = null;

            this.__items = [];
            this.__animationFrameId = null;

            this.info = this.getInfo();
            this.selectedIndex = 0;
            this.scrollIndex = 0;
            this.lastIndex = 0;
        },
        isHorizontal: function(){
            return ["top","bottom"].indexOf(this.opt.position) !== -1;
        },
        doCreate: function(){
            var self = this;
            if (self.isEnabled() && self._super()){
                if (!self.opt.captions) self.panel.$el.addClass(self.cls.noCaptions);
                if (self.opt.small) self.panel.$el.addClass(self.cls.small);
                if (_is.string(self.opt.align) && self.cls.align.hasOwnProperty(self.opt.align)) self.panel.$el.addClass(self.cls.align[self.opt.align]);

                self.$prev = $('<button/>', {type: 'button'}).addClass(self.cls.prev)
                    .append(_icons.get("arrow-left", self.panel.opt.icons))
                    .on("click.foogallery", {self: self}, self.onPrevClick)
                    .prependTo(self.$inner);
                self.$viewport = $('<div/>').addClass(self.cls.viewport).appendTo(self.$inner);
                self.$next = $('<button/>', {type: 'button'}).addClass(self.cls.next)
                    .append(_icons.get("arrow-right", self.panel.opt.icons))
                    .on("click.foogallery", {self: self}, self.onNextClick)
                    .appendTo(self.$inner);
                self.$stage = $('<div/>').addClass(self.cls.stage).appendTo(self.$viewport);
                self.$dummy = $('<div/>').addClass(self.cls.thumb.elem).appendTo(self.$viewport);

                self.iobserver = new IntersectionObserver(function(entries){
                    entries.forEach(function(entry){
                        if (entry.isIntersecting){
                            self.iobserver.unobserve(entry.target);
                            self.loadThumbElement(entry.target);
                        }
                    });
                }, { root: self.$inner.get(0), rootMargin: "82px 300px" });

                self.robserver = new ResizeObserver(_fn.throttle(function (entries) {
                    if (entries.length > 0 && self.panel instanceof _.Panel && !self.panel.destroying && !self.panel.destroyed) {
                        // only the viewport is being observed so if a change occurs we can safely grab just the first entry
                        var size = _utils.getResizeObserverSize(entries[0]), viewport = self.info.viewport;
                        var diffX = Math.floor(Math.abs(size.width - viewport.width)),
                            diffY = Math.floor(Math.abs(size.height - viewport.height));
                        if (self.isVisible && (diffX > 1 || diffY > 1)) {
                            self.resize();
                        }
                    }
                }, 50));

                self.doCreateThumbs(self.panel.tmpl.items.available(self.panel.isVisible));

                return true;
            }
            return false;
        },
        doCreateThumbs: function(items){
            if (_is.empty(items)) return;
            var self = this;
            if (self.iobserver instanceof IntersectionObserver){
                self.iobserver.takeRecords().forEach(function(entry){
                    self.iobserver.unobserve(entry.target);
                });
            }
            self.__items = items;
            self.selectedIndex = 0;
            self.scrollIndex = 0;
            self.lastIndex = self.__items.length - 1;
            self.$stage.empty();
            items.forEach(function(item){
                var $thumb = self.doCreateThumb(item).appendTo(self.$stage);
                self.iobserver.observe($thumb.get(0));
            });
            self.$stage.append($("<div/>").addClass(self.cls.spacer));
        },
        doCreateThumb: function(item){
            var self = this, cls = self.cls.thumb;
            return $("<figure/>").addClass(cls.elem).addClass(item.getTypeClass()).addClass(self.panel.cls.states.idle).append(
                $("<div/>").addClass(cls.media).append(
                    $("<div/>").addClass(cls.overlay),
                    $("<div/>").addClass(cls.wrap).append(
                        $("<img/>", {title: item.title, alt: item.alt}).attr({draggable: false}).addClass(cls.image)
                    ),
                    $("<div/>").addClass(self.panel.cls.loader)
                ),
                $("<div/>").addClass(cls.caption).append(
                    $("<div/>").addClass(cls.title).html(item.caption),
                    $("<div/>").addClass(cls.description).html(item.description)
                )
            ).data("item", item).on("click", {self: self, item: item}, self.onThumbClick);
        },
        doDestroy: function(){
            this.stopListening();
            if (this.iobserver instanceof IntersectionObserver){
                this.iobserver.disconnect();
            }
            if (this.robserver instanceof ResizeObserver){
                this.robserver.disconnect();
            }
            return this._super();
        },
        doLoad: function(media, reverseTransition){
            if (this.isCreated){
                var index = this.__items.indexOf(media.item);
                if (index !== -1) {
                    this.makeVisible(index);
                    this.$stage.find(this.sel.thumb.elem)
                        .removeClass(this.panel.cls.states.selected)
                        .eq(index).addClass(this.panel.cls.states.selected);
                    this.selectedIndex = index;
                }
            }
            return _fn.resolved;
        },
        makeVisible: function(index, disableTransition){
            if (index <= this.scrollIndex) {
                this.goto(index, disableTransition);
            } else if (index >= this.scrollIndex + this.info.count) {
                this.goto(index, disableTransition);
            }
        },
        listen: function(){
            var self = this;
            self.stopListening();
            if (self.isCreated){
                self.resize();
                self.robserver.observe(self.$viewport.get(0));
                self.$inner.fgswipe({data: {self: self}, swipe: self.onSwipe, allowPageScroll: true})
                    .on("DOMMouseScroll.foogallery-panel-thumbs mousewheel.foogallery-panel-thumbs", {self: self}, self.onMouseWheel);
            }
        },
        stopListening: function(){
            if (this.isCreated){
                this.$inner.fgswipe("destroy").off(".foogallery-panel-thumbs");
                this.$stage.find(this.sel.thumb).css({width: "", minWidth: "", height: "", minHeight: ""});
                this.robserver.unobserve(this.$viewport.get(0));
            }
        },
        loadThumbElement: function(element){
            var self = this,
                $thumb = $(element),
                item = $thumb.data("item"),
                img = $thumb.find(self.sel.thumb.image).get(0),
                states = self.panel.cls.states;

            $thumb.removeClass(states.allLoading).addClass(states.loading);
            img.onload = function(){
                img.onload = img.onerror = null;
                $thumb.removeClass(states.allLoading).addClass(states.loaded);
            };
            img.onerror = function(){
                img.onload = img.onerror = null;
                $thumb.removeClass(states.allLoading).addClass(states.error);
            };
            img.src = item.src;
            img.srcset = item.srcset;
            if (img.complete){
                img.onload();
            }
        },
        goto: function(index, disableTransition){
            var self = this;
            if (!self.isCreated) return _fn.reject("thumbs not created");

            index = index < 0 ? 0 : (index > self.lastIndex ? self.lastIndex : index);

            var states = self.panel.cls.states,
                rightOrBottom = index >= self.scrollIndex + self.info.count, // position the thumb to the right or bottom of the viewport depending on orientation
                scrollIndex = rightOrBottom ? index - (self.info.count - 1) : index, // if rightOrBottom we subtract the count - 1 so the thumb appears to the right or bottom of the viewport
                maxIndex = self.lastIndex - (self.info.count - 1); // the scrollIndex of the last item

            // fix any calculated value overflows
            if (scrollIndex < 0) scrollIndex = 0;
            if (maxIndex < 0) maxIndex = 0;
            if (scrollIndex > maxIndex) scrollIndex = maxIndex;

            return $.Deferred(function(def){
                // find the thumb
                var $thumb = self.$stage.find(self.sel.thumb.elem).eq(scrollIndex);
                if ($thumb.length > 0){
                    // align the right or bottom edge of the thumb with the viewport
                    var alignRightOrBottom = scrollIndex > self.scrollIndex, hasFullStage = self.__items.length >= self.info.count, offset, translate;
                    if (self.info.isHorizontal) {
                        offset = -($thumb.prop("offsetLeft"));
                        if (alignRightOrBottom) offset += self.info.remaining.width;
                        if (hasFullStage && self.info.stage.width - Math.abs(offset) < self.info.viewport.width) {
                            offset = self.info.viewport.width - self.info.stage.width;
                        }
                        translate = "translateX(" + (offset - 1) + "px)";
                    } else {
                        offset = -($thumb.prop("offsetTop"));
                        if (alignRightOrBottom) offset += self.info.remaining.height;
                        if (hasFullStage && self.info.stage.height - Math.abs(offset) < self.info.viewport.height) {
                            offset = self.info.viewport.height - self.info.stage.height;
                        }
                        translate = "translateY(" + (offset - 1) + "px)";
                    }
                    if (self.panel.hasTransition && !disableTransition) {
                        _t.start(self.$stage, function ($el) {
                            $el.css("transform", translate);
                        }, null, 350).then(function () {
                            def.resolve();
                        }).fail(def.reject);
                    } else {
                        self.$stage.addClass(states.noTransitions).css("transform", translate);
                        self.$stage.prop("offsetHeight");
                        self.$stage.removeClass(states.noTransitions);
                        def.resolve();
                    }
                } else {
                    def.resolve();
                }
            }).always(function(){
                self.scrollIndex = scrollIndex;
                self.$prev.toggleClass(states.disabled, scrollIndex <= 0);
                self.$next.toggleClass(states.disabled, scrollIndex >= maxIndex);
            }).promise();
        },
        getInfo: function(){
            var isHorizontal = this.isHorizontal(),
                viewport = { width: 0, height: 0 },
                stage = { width: 0, height: 0 },
                original = { width: 0, height: 0 },
                counts = { horizontal: 0, vertical: 0 },
                adjusted = { width: 0, height: 0 },
                remaining = { width: 0, height: 0 },
                width = 0, height = 0, itemCount = this.__items.length;
            if (this.isCreated){
                viewport = { width: this.$viewport.innerWidth() + 1, height: this.$viewport.innerHeight() + 1 };
                original = { width: this.$dummy.outerWidth(), height: this.$dummy.outerHeight() };
                counts = { horizontal: Math.floor(viewport.width / original.width), vertical: Math.floor(viewport.height / original.height) };
                adjusted = { width: viewport.width / Math.min(itemCount, counts.horizontal), height: viewport.height / Math.min(itemCount, counts.vertical) };
                width = this.opt.bestFit ? adjusted.width : original.width;
                height = this.opt.bestFit ? adjusted.height : original.height;
                stage = { width: isHorizontal ? this.__items.length * width : width, height: !isHorizontal ? this.__items.length * height : height };
                remaining = { width: Math.floor(viewport.width - (counts.horizontal * width)), height: Math.floor(viewport.height - (counts.vertical * height)) };
            }
            return {
                isHorizontal: isHorizontal,
                viewport: viewport,
                stage: stage,
                original: original,
                adjusted: adjusted,
                remaining: remaining,
                counts: counts,
                count: isHorizontal ? counts.horizontal : counts.vertical,
                width: width,
                height: height
            };
        },
        resize: function(){
            if (this.isCreated){
                this.info = this.getInfo();
                if (this.opt.bestFit){
                    if (this.info.isHorizontal){
                        this.$stage.find(this.sel.thumb.elem).css({width: this.info.width, minWidth: this.info.width, height: "", minHeight: ""});
                    } else {
                        this.$stage.find(this.sel.thumb.elem).css({height: this.info.height, minHeight: this.info.height, width: "", minWidth: ""});
                    }
                }
                this.goto(this.scrollIndex, true);
            }
        },
        onThumbClick: function(e){
            e.preventDefault();
            e.data.self.panel.load(e.data.item);
        },
        onPrevClick: function(e){
            e.preventDefault();
            var self = e.data.self;
            self.goto(self.scrollIndex - (self.info.count - 1 || 1));
        },
        onNextClick: function(e){
            e.preventDefault();
            var self = e.data.self;
            self.goto(self.scrollIndex + (self.info.count - 1 || 1));
        },
        onSwipe: function(info, data){
            var self = data.self, amount = 1;
            if (self.info.isHorizontal){
                amount = Math.ceil(info.distance / self.info.width);
                if (info.direction === "E"){
                    self.goto(self.scrollIndex - amount);
                }
                if (info.direction === "W"){
                    self.goto(self.scrollIndex + amount);
                }
            } else {
                amount = Math.ceil(info.distance / self.info.height);
                if (info.direction === "S"){
                    self.goto(self.scrollIndex - amount);
                }
                if (info.direction === "N"){
                    self.goto(self.scrollIndex + amount);
                }
            }
        },
        onMouseWheel: function(e){
            var self = e.data.self,
                delta = Math.max(-1, Math.min(1, (e.originalEvent.wheelDelta || -e.originalEvent.detail)));
            if (delta > 0){
                self.goto(self.scrollIndex - 1);
                e.preventDefault();
            } else if (delta < 0){
                self.goto(self.scrollIndex + 1);
                e.preventDefault();
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.icons,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.transition
);
(function($, _, _utils, _is, _fn, _obj, _str, _t){

    _.Panel.Media = _utils.Class.extend({
        construct: function(panel, item){
            var self = this;

            self.panel = panel;

            self.item = item;

            self.opt = _obj.extend({}, panel.opt.media);

            self.cls = _obj.extend({}, panel.cls.media);

            self.sel = _obj.extend({}, panel.sel.media);

            self.il8n = _obj.extend({}, panel.il8n.media);

            self.caption = new _.Panel.Media.Caption(panel, self);

            if ( _.Panel.Media.Product ){
                self.product = new _.Panel.Media.Product(panel, self);
            }

            self.$el = null;

            self.$content = null;

            self.isCreated = false;

            self.isAttached = false;

            self.isLoading = false;

            self.isLoaded = false;

            self.isError = false;
        },
        getSize: function(attrWidth, attrHeight, defWidth, defHeight){
            var self = this, size = {};
            if (!_is.string(attrWidth) || !_is.string(attrHeight)) return size;

            size[attrWidth] = _is.size(defWidth) ? defWidth : null;
            size[attrHeight] = _is.size(defHeight) ? defHeight : null;


            if (!self.item.isCreated) return size;

            size[attrWidth] = self.item.$anchor.data(attrWidth) || size[attrWidth];
            size[attrHeight] = self.item.$anchor.data(attrHeight) || size[attrHeight];
            return size;
        },
        getSizes: function(){
            var self = this,
                size = self.getSize("width", "height", self.opt.width, self.opt.height),
                max = self.getSize("maxWidth", "maxHeight", self.opt.maxWidth, self.opt.maxHeight),
                min = self.getSize("minWidth", "minHeight", self.opt.minWidth, self.opt.minHeight);
            return _obj.extend(size, max, min);
        },
        destroy: function(){
            var self = this;
            var e = self.panel.trigger("media-destroy", [self]);
            if (!e.isDefaultPrevented()) {
                self.isCreated = !self.doDestroy();
            }
            if (!self.isCreated) {
                self.panel.trigger("media-destroyed", [self]);
            }
            return !self.isCreated;
        },
        doDestroy: function(){
            var self = this;
            if (self.isCreated){
                self.caption.destroy();
                self.detach();
                self.$el.remove();
            }
            return true;
        },
        create: function(){
            var self = this;
            if (!self.isCreated && _is.string(self.item.href)) {
                var e = self.panel.trigger("media-create", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isCreated = self.doCreate();
                }
                if (self.isCreated) {
                    self.panel.trigger("media-created", [self]);
                }
            }
            return self.isCreated;
        },
        doCreate: function(){
            var self = this;
            self.$el = $('<div/>').addClass([self.cls.elem, self.cls.type].join(" ")).append(
                $('<div/>').addClass(self.panel.cls.loader)
            );
            self.$content = self.doCreateContent().addClass(self.cls.content).css(self.getSizes()).appendTo(self.$el);
            return true;
        },
        doCreateContent: function(){
            return $();
        },
        appendTo: function( parent ){
            var self = this;
            if (!self.isCreated){
                self.create();
            }
            if (self.isCreated && !self.isAttached){
                var e = self.panel.trigger("media-append", [self, parent]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = self.doAppendTo( parent );
                }
                if (self.isAttached) {
                    self.panel.trigger("media-appended", [self, parent]);
                }
            }
            return self.isAttached;
        },
        doAppendTo: function( parent ){
            this.$el.appendTo( parent );
            return this.$el.parent().length > 0;
        },
        detach: function(){
            var self = this;
            if (self.isCreated && self.isAttached) {
                var e = self.panel.trigger("media-detach", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = !self.doDetach();
                }
                if (!self.isAttached) {
                    self.panel.trigger("media-detached", [self]);
                }
            }
            return !self.isAttached;
        },
        doDetach: function(){
            this.$el.detach();
            return true;
        },
        load: function(){
            var self = this, states = self.panel.cls.states;
            return $.Deferred(function(def){
                var e = self.panel.trigger("media-load", [self]);
                if (e.isDefaultPrevented()){
                    def.rejectWith("default prevented");
                    return;
                }
                self.$el.removeClass(states.allLoading).addClass(states.loading);
                self.doLoad().then(def.resolve).fail(def.reject);
            }).always(function(){
                self.$el.removeClass(states.loading);
            }).then(function(){
                self.$el.addClass(states.loaded);
                self.panel.trigger("media-loaded", [self]);
            }).fail(function(){
                self.$el.addClass(states.loaded);
                self.panel.trigger("media-error", [self]);
            }).promise();
        },
        doLoad: function(){
            return _fn.resolved;
        },
        unload: function(){
            var self = this;
            return $.Deferred(function(def){
                if (!self.isCreated || !self.isAttached){
                    def.rejectWith("not created or attached");
                    return;
                }
                var e = self.panel.trigger("media-unload", [self]);
                if (e.isDefaultPrevented()){
                    def.rejectWith("default prevented");
                    return;
                }
                self.doUnload().then(def.resolve).fail(def.reject);
            }).then(function(){
                self.panel.trigger("media-unloaded", [self]);
            }).promise();
        },
        doUnload: function(){
            return _fn.resolved;
        },
        resize: function(){}
    });

    _.template.configure("core", {
        panel: {
            media: {
                width: null,
                height: null,
                minWidth: null,
                minHeight: null,
                maxWidth: null,
                maxHeight: null,
                attrs: {}
            }
        }
    },{
        panel: {
            media: {
                elem: "fg-media",
                type: "fg-media-unknown",
                content: "fg-media-content",
                caption: {
                    elem: "fg-media-caption",
                    title: "fg-media-caption-title",
                    description: "fg-media-caption-description",
                    exif: {
                        elem: "fg-media-caption-exif",
                        auto: "fg-media-caption-exif-auto",
                        full: "fg-media-caption-exif-full",
                        partial: "fg-media-caption-exif-partial",
                        minimal: "fg-media-caption-exif-minimal",
                        prop: "fg-media-caption-exif-prop",
                        icon: "fg-media-caption-exif-icon",
                        content: "fg-media-caption-exif-content",
                        label: "fg-media-caption-exif-label",
                        value: "fg-media-caption-exif-value",
                        tooltip: "fg-media-caption-exif-tooltip",
                        tooltipPointer: "fg-media-caption-exif-tooltip-pointer",
                        showTooltip: "fg-media-caption-exif-show-tooltip"
                    }
                },
                product: {
                    elem: "fg-media-product",
                    inner: "fg-media-product-inner",
                    header: "fg-media-product-header",
                    body: "fg-media-product-body",
                    footer: "fg-media-product-footer",
                    button: "fg-panel-button",
                    hidden: "fg-hidden",
                    disabled: "fg-disabled",
                    loading: "fg-loading"
                }
            }
        }
    }, {
        panel: {
            media: {
                product: {
                    title: "Product Information",
                    addToCart: "Add to Cart",
                    viewProduct: "View Product",
                    success: "Successfully added to cart.",
                    error: "Something went wrong adding to cart."
                }
            }
        }
    });

    _.Panel.media = new _.Factory();

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.obj,
    FooGallery.utils.str,
    FooGallery.utils.transition
);
(function ($, _, _icons, _utils, _is, _fn, _obj, _str, _t) {

    var canHover = !!window.matchMedia && window.matchMedia("(hover: hover)").matches;

    _.Panel.Media.Caption = _utils.Class.extend({
        construct: function (panel, media) {
            var self = this;
            self.panel = panel;
            self.media = media;
            self.opt = panel.opt;
            self.cls = media.cls.caption;
            self.sel = media.sel.caption;
            self.$el = null;
            self.title = null;
            self.description = null;
            self.isCreated = false;
            self.isAttached = false;
            self.hasTitle = false;
            self.hasDescription = false;
            self.hasExif = false;
            self.init(media.item);
        },
        canLoad: function(){
            return this.hasTitle || this.hasDescription || this.hasExif;
        },
        init: function(item){
            if (!(item instanceof _.Item)) return;
            var self = this, title, desc, supplied = false;
            if (item.isCreated){
                var data = item.$anchor.data() || {};
                title = _is.string(data.lightboxTitle);
                desc = _is.string(data.lightboxDescription);
                if (title || desc){
                    supplied = true;
                    self.title = _.safeParse( title ? data.lightboxTitle : "" );
                    self.description = _.safeParse( desc ? data.lightboxDescription : "" );
                }
            } else {
                var attr = item.attr.anchor;
                title = _is.string(attr["data-lightbox-title"]);
                desc = _is.string(attr["data-lightbox-description"]);
                if (title || desc){
                    supplied = true;
                    self.title = _.safeParse( title ? attr["data-lightbox-title"] : "" );
                    self.description = _.safeParse( desc ? attr["data-lightbox-description"] : "" );
                }
            }
            if (!supplied){
                self.title = item.caption;
                self.description = item.description;
            }
            self.hasTitle = !_is.empty(self.title);
            self.hasDescription = !_is.empty(self.description);
            self.hasExif = item.hasExif && _utils.inArray(self.opt.exif, ["auto","full","partial","minimal"]) !== -1;
        },
        create: function(){
            if (!this.isCreated){
                var e = this.panel.trigger("caption-create", [this]);
                if (!e.isDefaultPrevented()){
                    this.isCreated = this.doCreate();
                    if (this.isCreated){
                        this.panel.trigger("caption-created", [this]);
                    }
                }
            }
            return this.isCreated;
        },
        doCreate: function(){
            var self = this;
            self.$el = $("<div/>").addClass(self.cls.elem);
            if (self.hasTitle){
                self.$el.append($("<div/>").addClass(self.cls.title).html(self.title));
            }
            if (self.hasDescription){
                self.$el.append($("<div/>").addClass(self.cls.description).html(self.description));
            }
            if (self.hasExif){
                var exif = self.media.item.exif, $exif = $("<div/>", {"class": self.cls.exif.elem}).addClass(self.cls.exif[self.opt.exif]);
                _.supportedExifProperties.forEach(function(prop){
                    if (!_is.empty(exif[prop])){
                        var icon = "exif-" + _str.kebab(prop), text = self.media.item.il8n.exif[prop], value = exif[prop];
                        var $exifProp = $("<div/>", {"class": self.cls.exif.prop}).append(
                            $("<div/>", {"class": self.cls.exif.icon}).append(_icons.get(icon, self.opt.icons)),
                            $("<div/>", {"class": self.cls.exif.content}).append(
                                $("<div/>", {"class": self.cls.exif.label}).text(text),
                                $("<div/>", {"class": self.cls.exif.value}).text(value)
                            ),
                            $("<span/>", {"class": self.cls.exif.tooltip}).text(text + ": " + value).append(
                                $("<span/>", {"class": self.cls.exif.tooltipPointer})
                            )
                        );
                        if (!canHover){
                            $exifProp.on("click", {self: self}, self.onExifClick);
                        }
                        $exif.append($exifProp);
                    }
                });
                self.$el.append($exif);
            }
            return true;
        },
        onExifClick: function(e){
            e.preventDefault();
            var self = e.data.self, $this = $(this),
                $tooltip = $this.find(self.sel.exif.tooltip),
                $current = $(self.sel.exif.showTooltip);

            $(self.sel.exif.prop).removeClass(self.cls.exif.showTooltip)
                .find(self.sel.exif.tooltip).css("left", "")
                .find(self.sel.exif.tooltipPointer).css("left", "");
            if (!$current.is($this)){
                $tooltip.css("display", "inline-block");

                var left = $tooltip.offset().left,
                    right = left + $tooltip.outerWidth(),
                    diff = Math.ceil(right - window.innerWidth);

                if (diff > 0){
                    $tooltip.css("left", "calc(50% - " + diff + "px)")
                        .find(self.sel.exif.tooltipPointer).css("left", "calc(50% + " + diff + "px)");
                }
                if (left < 0){
                    left = Math.abs(left);
                    $tooltip.css("left", "calc(50% + " + left + "px)")
                        .find(self.sel.exif.tooltipPointer).css("left", "calc(50% - " + left + "px)");
                }

                $tooltip.css("display", "");
                $this.addClass(self.cls.exif.showTooltip);
            }
        },
        destroy: function(){
            if (this.isCreated){
                var e = this.panel.trigger("caption-destroy", [this]);
                if (!e.isDefaultPrevented()){
                    this.isCreated = !this.doDestroy();
                    if (!this.isCreated){
                        this.panel.trigger("caption-destroyed", [this]);
                    }
                }
            }
            return !this.isCreated;
        },
        doDestroy: function(){
            this.$el.remove();
            return true;
        },
        appendTo: function( parent ){
            var self = this;
            if (!self.isCreated){
                self.create();
            }
            if (self.isCreated && !self.isAttached){
                var e = self.panel.trigger("caption-append", [self, parent]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = self.doAppendTo( parent );
                }
                if (self.isAttached) {
                    self.panel.trigger("caption-appended", [self, parent]);
                }
            }
            return self.isAttached;
        },
        doAppendTo: function( parent ){
            this.$el.appendTo( parent );
            return this.$el.parent().length > 0;
        },
        detach: function(){
            var self = this;
            if (self.isCreated && self.isAttached) {
                var e = self.panel.trigger("caption-detach", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = !self.doDetach();
                }
                if (!self.isAttached) {
                    self.panel.trigger("caption-detached", [self]);
                }
            }
            return !self.isAttached;
        },
        doDetach: function(){
            this.$el.detach();
            return true;
        },
        load: function(){
            var self = this, states = self.panel.cls.states;
            return $.Deferred(function(def){
                var e = self.panel.trigger("caption-load", [self]);
                if (e.isDefaultPrevented()){
                    def.rejectWith("default prevented");
                    return;
                }
                self.$el.removeClass(states.allLoading).addClass(states.loading);
                self.doLoad().then(def.resolve).fail(def.reject);
            }).always(function(){
                self.$el.removeClass(states.loading);
            }).then(function(){
                self.$el.addClass(states.loaded);
                self.panel.trigger("caption-loaded", [self]);
            }).fail(function(){
                self.$el.addClass(states.loaded);
                self.panel.trigger("caption-error", [self]);
            }).promise();
        },
        doLoad: function(){
            return _fn.resolved;
        },
        unload: function(){
            var self = this;
            return $.Deferred(function(def){
                if (!self.isCreated || !self.isAttached){
                    def.rejectWith("not created or attached");
                    return;
                }
                var e = self.panel.trigger("caption-unload", [self]);
                if (e.isDefaultPrevented()){
                    def.rejectWith("default prevented");
                    return;
                }
                self.doUnload().then(def.resolve).fail(def.reject);
            }).then(function(){
                self.panel.trigger("caption-unloaded", [self]);
            }).promise();
        },
        doUnload: function(){
            return _fn.resolved;
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.icons,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.obj,
    FooGallery.utils.str,
    FooGallery.utils.transition
);
(function($, _, _utils, _obj){

    _.Panel.Image = _.Panel.Media.extend({
        construct: function(panel, item){
            this._super(panel, item);
            _obj.extend(this.opt, panel.opt.image);
            _obj.extend(this.cls, panel.cls.image);
            _obj.extend(this.sel, panel.sel.image);
            this.allFullClasses = [this.cls.fullWidth, this.cls.fullHeight].join(" ");
        },
        doCreateContent: function(){
            return $('<img/>').attr(this.opt.attrs);
        },
        resize: function(){
            var self = this;
            if (self.isCreated && self.panel.opt.fitMedia){
                var img = self.$content.get(0);
                if (img.naturalWidth && img.naturalHeight){
                    var landscape = img.naturalWidth >= img.naturalHeight,
                        fullWidth = landscape,
                        targetWidth = self.$el.innerWidth(),
                        targetHeight = self.$el.innerHeight(),
                        ratio;

                    if (landscape){
                        ratio = targetWidth / img.naturalWidth;
                        if (img.naturalHeight * ratio < targetHeight){
                            fullWidth = false;
                        }
                    } else {
                        ratio = targetHeight / img.naturalHeight;
                        if (img.naturalWidth * ratio < targetWidth){
                            fullWidth = true;
                        }
                    }
                    _utils.requestFrame(function(){
                        self.$content.removeClass(self.allFullClasses).addClass(fullWidth ? self.cls.fullWidth : self.cls.fullHeight);
                    });
                }
            }
        },
        doLoad: function(){
            var self = this;
            return $.Deferred(function(def){
                var img = self.$content.get(0);
                img.onload = function () {
                    img.onload = img.onerror = null;
                    def.resolve(self);
                };
                img.onerror = function () {
                    img.onload = img.onerror = null;
                    def.rejectWith("error loading image");
                };
                // set everything in motion by setting the src
                img.src = self.item.href;
                if (img.complete){
                    img.onload();
                }
            }).then(function(){
                self.resize();
            }).promise();
        }
    });

    _.Panel.media.register("image", _.Panel.Image);

    _.template.configure("core", {
        panel: {
            image: {
                attrs: {
                    draggable: false
                }
            }
        }
    },{
        panel: {
            image: {
                type: "fg-media-image",
                fullWidth: "fg-media-full-width",
                fullHeight: "fg-media-full-height"
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.obj
);
(function($, _, _utils, _obj){

    _.Panel.Iframe = _.Panel.Media.extend({
        construct: function(panel, item){
            this._super(panel, item);
            _obj.extend(this.opt, panel.opt.iframe);
            _obj.extend(this.cls, panel.cls.iframe);
            _obj.extend(this.sel, panel.sel.iframe);
        },
        doCreateContent: function(){
            return $('<iframe/>').attr(this.opt.attrs);
        },
        doLoad: function(){
            var self = this;
            return $.Deferred(function(def){
                self.$content.off("load error").on({
                    'load': function(){
                        self.$content.off("load error");
                        def.resolve(self);
                    },
                    'error': function(){
                        self.$content.off("load error");
                        def.reject(self);
                    }
                });
                self.$content.attr("src", self.item.href);
            }).promise();
        }
    });

    _.Panel.media.register("iframe", _.Panel.Iframe);

    _.template.configure("core", {
        panel: {
            iframe: {
                attrs: {
                    src: '',
                    frameborder: 'no',
                    allow: "autoplay; fullscreen",
                    allowfullscreen: true
                }
            }
        }
    },{
        panel: {
            iframe: {
                type: "fg-media-iframe"
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.obj
);
(function($, _, _utils, _obj, _str){

    _.Panel.Html = _.Panel.Media.extend({
        construct: function(panel, item){
            this._super(panel, item);
            _obj.extend(this.opt, panel.opt.html);
            _obj.extend(this.cls, panel.cls.html);
            _obj.extend(this.sel, panel.sel.html);
            this.$target = null;
        },
        doCreate: function(){
            if (this._super()){
                if (!_str.startsWith(this.item.href, '#') || (this.$target = $(this.item.href)).length === 0){
                    this.$target = null;
                    return false;
                }
                return true;
            }
            return false;
        },
        doCreateContent: function(){
            return $('<div/>').attr(this.opt.attrs);
        },
        doAppendTo: function( parent ){
            if (this._super( parent )){
                this.$content.append(this.$target.contents());
                return true;
            }
            return false;
        },
        doDetach: function(){
            this.$target.append(this.$content.contents());
            return this._super();
        }
    });

    _.Panel.media.register("html", _.Panel.Html);

    _.template.configure("core", {
        panel: {
            html: {}
        }
    },{
        panel: {
            html: {
                type: "fg-media-html"
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.obj,
    FooGallery.utils.str
);
(function($, _, _is, _obj){

    _.Lightbox = _.Panel.extend({
        construct: function (template, options) {
            var self = this;
            self._super(template, options);
            if (self.opt.enabled && (self.tmpl instanceof _.Template) && !(self.tmpl.destroying || self.tmpl.destroyed)) {
                self.tmpl.on({
                    "after-state": self.onAfterState,
                    "anchor-click-item": self.onAnchorClickItem,
                    "destroyed": self.onDestroyedTemplate
                }, self);
            }
        },
        onAnchorClickItem: function(e, item){
            if (!item.noLightbox){
                e.preventDefault();
                try {
                    this.open(item);
                } catch( err ) {
                    console.error( err );
                }
            }
        },
        onDestroyedTemplate: function(){
            this.destroy();
        },
        onAfterState: function(e, state){
            if (state.item instanceof _.Item && !state.item.noLightbox){
                try {
                    this.open(state.item);
                } catch( err ) {
                    console.error( err );
                }
            }
        }
    });

    _.template.configure("core", {
        lightbox: {
            enabled: false
        }
    }, {});

    _.Template.override("construct", function(options, element){
        this._super(options, element);
        var data = this.$el.data("foogalleryLightbox"),
            enabled = this.opt.lightbox.enabled || _is.hash(data) || (this.$el.length > 0 && this.el.hasAttribute("data-foogallery-lightbox"));

        this.opt.lightbox = _obj.extend({}, this.opt.panel, this.opt.lightbox, { enabled: enabled }, data);
        this.lightbox = enabled ? new _.Lightbox(this, this.opt.lightbox) : null;
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils.is,
    FooGallery.utils.obj
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
	 * @memberof FooGallery.
	 * @constructs MasonryTemplate
	 * @param {Object} [options] - The options for the template.
	 * @param {(jQuery|HTMLElement)} [element] - The jQuery object or HTMLElement of the template. If not supplied one will be created within the `parent` element supplied to the {@link FooGallery.Template#initialize|initialize} method.
	 * @augments FooGallery.Template
	 * @borrows FooGallery.utils.Class.extend as extend
	 * @borrows FooGallery.utils.Class.override as override
	 * @description This template makes use of the popular [Masonry library](http://masonry.desandro.com/) to perform its layout. It supports two basic layout types, fixed and column based.
	 */
	_.MasonryTemplate = _.Template.extend(/** @lends FooGallery.MasonryTemplate */{
		construct: function(options, element){
			var self = this;
			self._super(options, element);
			self.masonry = null;
			self.on({
				"pre-init": self.onPreInit,
				"destroyed": self.onDestroyed,
				"appended-items": self.onAppendedItems,
				"detach-item": self.onDetachItem,
				"layout after-filter-change": self.onLayoutRequired,
				"page-change": self.onPageChange
			}, self);
		},
		onPreInit: function(){
			var self = this, sel = self.sel,
				fixed = self.$el.hasClass("fg-fixed");

			self.template.isFitWidth = fixed;
			self.template.percentPosition = !fixed;
			self.template.transitionDuration = 0;
			self.template.itemSelector = sel.item.elem;
			if (!fixed){
				self.template.gutter = sel.gutterWidth;
				self.template.columnWidth = sel.columnWidth;
			}
			self.masonry = new Masonry( self.el, self.template );
		},
		onDestroyed: function(){
			var self = this;
			if (self.masonry instanceof Masonry){
				self.masonry.destroy();
			}
		},
		onLayoutRequired: function(){
			this.masonry.layout();
		},
		onPageChange: function(event, current, prev, isFilter){
			if (!isFilter){
				this.masonry.layout();
			}
		},
		onAppendedItems: function(event, items){
			var self = this,
				elements = items.map(function(item){
					return item.el;
				}),
				mItems = self.masonry.addItems(elements);
			// add and layout the new items with no transitions
			self.masonry.layoutItems(mItems, true);
		},
		onDetachItem: function(event, item){
			if (!event.isDefaultPrevented()){
				event.preventDefault();
				this.masonry.remove(item.el);
				item.$el.removeClass(this.cls.hidden);
				item.isAttached = false;
			}
		}
	});

	_.template.register("masonry", _.MasonryTemplate, {
		template: {}
	}, {
		container: "foogallery fg-masonry",
		columnWidth: "fg-column-width",
		gutterWidth: "fg-gutter-width"
	});

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
);
(function($, _, _utils, _is){

	_.Justified = _utils.Class.extend({
		construct: function(template, options){
			var self = this;
			self.tmpl = template;
			self.$el = template.$el;
			self.options = $.extend(true, {}, _.Justified.defaults, options);
			self._items = [];
			self.maxRowHeight = 0;
			self.borderSize = 0;
			self.align = ["left","center","right"].indexOf(self.options.align) !== -1 ? self.options.align : "center";
		},
		init: function(){
			var self = this;
			self.maxRowHeight = self.getMaxRowHeight(self.options.maxRowHeight, self.options.rowHeight);
			self.borderSize = self.getBorderSize();
		},
		destroy: function(){
			this.$el.removeAttr("style");
		},
		getBorderSize: function(){
			var border = this.tmpl.getCSSClass("border", "");
			switch (border){
				case "fg-border-thin":
					return 4;
				case "fg-border-medium":
					return 10;
				case "fg-border-thick":
					return 16;
				default:
					return 0;
			}
		},
		getMaxRowHeight: function(value, def) {
			if (_is.string(value)){
				var parsed = parseInt(value);
				if (isNaN(parsed)) return def;
				if (parsed <= 0) return Infinity;
				return value.indexOf('%') !== -1 ? def * (parsed / 100) : parsed;
			}
			if (_is.number(value)){
				if (value <= 0) return Infinity;
				return value;
			}
			return def;
		},
		layout: function(width){
			var self = this;
			if (!_is.number(width)){
				width = self.$el.width();
			}
			if (width > 0){
				var result = self.createRows(width);
				if (result.height !== 0 && result.rows.length > 0){
					self.$el.height(result.height);
					result.rows.forEach(function(row, i){
						self.render(row, i === result.rows.length - 1);
					});
				}
			}
		},
		render: function(row, isLast){
			var self = this, applyMaxHeight = !isLast && self.options.lastRow !== "justify";
			row.items.forEach(function(item){
				if (item.elem){
					if (row.visible){
						item.elem.style.setProperty("position", "absolute");
						item.elem.style.setProperty("width", item.width + "px");
						item.elem.style.setProperty("height", item.height + "px");
						item.elem.style.setProperty("top", item.top + "px");
						item.elem.style.setProperty("left", item.left + "px");
						item.elem.style.setProperty("margin", "0");
						item.elem.style.removeProperty("display");
						if (self.maxRowHeight > 0 && applyMaxHeight){
							item.elem.style.setProperty("max-height", (self.maxRowHeight + (self.borderSize * 2)) + "px");
						} else {
							item.elem.style.removeProperty("max-height");
						}
						if (!item.elem.classList.contains("fg-positioned")){
							item.elem.classList.add("fg-positioned");
						}
					} else {
						item.elem.style.setProperty("display", "none");
					}
				}
			});
		},
		justify: function(row, top, maxWidth, maxHeight){
			var self = this,
				margin = self.options.margins,
				margins = margin * (row.items.length - 1),
				max = maxWidth - margins,
				rowWidth = row.width - margins;

			var w_ratio = max / rowWidth;
			row.width = rowWidth * w_ratio;
			row.height = row.height * w_ratio;

			if (row.height > (maxHeight + (self.borderSize * 2))){
				var h_ratio = (maxHeight + (self.borderSize * 2)) / row.height;
				row.width = row.width * h_ratio;
				row.height = row.height * h_ratio;
			}

			row.top = top;
			// default is left 0 because a full row starts at 0 and it matches default layouts
			row.left = 0;
			// if we don't have a full row and align !== left
			if (self.align !== "left" && row.width < max){
				if (self.align === "right"){
					row.left = max - row.width;
				} else {
					row.left = (max - row.width) / 2;
				}
			}

			row.width += margins;

			var left = row.left;
			row.items.forEach(function(item, i){
				if (i > 0) left += margin;
				item.left = left;
				item.top = top;
				var i_ratio = row.height / item.height;
				item.width = item.width * i_ratio;
				item.height = item.height * i_ratio;
				left += item.width;
			});

			return row.height;
		},
		createRows: function(maxWidth){
			var self = this,
				margin = self.options.margins,
				items = self.tmpl.getAvailable(),
				rows = [],
				index = -1;

			function newRow(){
				return {
					index: ++index,
					visible: true,
					width: 0,
					height: self.options.rowHeight + (self.borderSize * 2),
					top: 0,
					left: 0,
					items: []
				};
			}

			function newItem(item, rowHeight){
				var width = item.width, height = item.height;
				// make the item match the row height
				if (height !== rowHeight){
					var ratio = rowHeight / height;
					height = height * ratio;
					width = width * ratio;
				}
				var maxRatio = self.maxRowHeight / rowHeight,
					maxWidth = width * maxRatio,
					maxHeight = height * maxRatio;
				return {
					__item: item,
					elem: item.el,
					width: width,
					height: height,
					maxWidth: maxWidth,
					maxHeight: maxHeight,
					top: 0,
					left: 0
				};
			}

			var row = newRow(), top = 0, max = 0;
			items.forEach(function(fgItem){
				var item = newItem(fgItem, row.height);
				// adding this item to the row would exceed the max width
				if (row.width + item.width > maxWidth && row.items.length > 0){
					if (rows.length > 0) top += margin;
					var height = self.justify(row, top, maxWidth, self.maxRowHeight); // first justify the current row
					if (height > max) max = height;
					top += height;
					rows.push(row);
					row = newRow(); // then make the new one
				}

				if (row.items.length > 0) row.width += margin;
				row.width += item.width;
				row.items.push(item);
			});

			if (row.items.length > 0){
				var height, top_start = top + margin;
				switch (self.options.lastRow){
					case "smart":
						height = self.justify(row, top_start, maxWidth, self.maxRowHeight);
						if (max !== 0 && height > max){
							var h_ratio = max / height,
								w_ratio = (row.width * h_ratio) / maxWidth;

							if (h_ratio < 0.9 || w_ratio < 0.9){
								height = self.justify(row, top_start, maxWidth, max - (self.borderSize * 2));
							}
						}
						break;
					case "justify":
						height = self.justify(row, top_start, maxWidth, 99999);
						break;
					case "hide":
						height = self.justify(row, top_start, maxWidth, self.maxRowHeight);
						if (row.width < maxWidth && rows.length > 1){
							row.visible = false;
						}
						break;
				}
				if (row.visible){
					top += height + margin;
				}
				rows.push(row);
			}

			return {
				height: top,
				rows: rows
			};
		}
	});

	_.Justified.defaults = {
		rowHeight: 150,
		maxRowHeight: "200%",
		margins: 0,
		align: "center",
		lastRow: "smart" // "smart","justify","hide"
	};

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
);
(function($, _){

	_.JustifiedTemplate = _.Template.extend({
		construct: function(options, element){
			var self = this;
			self._super(options, element);
			self.justified = null;
			self.on({
				"pre-init": self.onPreInit,
				"init": self.onInit,
				"destroyed": self.onDestroyed,
				"layout after-filter-change": self.onLayoutRequired,
				"page-change": self.onPageChange
			}, self);
		},
		onPreInit: function(){
			var self = this;
			self.justified = new _.Justified( self, self.template );
		},
		onInit: function(){
			this.justified.init();
		},
		onDestroyed: function(){
			var self = this;
			if (self.justified instanceof _.Justified){
				self.justified.destroy();
			}
		},
		onLayoutRequired: function(){
			this.justified.layout(this.lastWidth);
		},
		onPageChange: function(event, current, prev, isFilter){
			if (!isFilter){
				this.justified.layout(this.lastWidth);
			}
		}
	});

	_.template.register("justified", _.JustifiedTemplate, null, {
		container: "foogallery fg-justified"
	});

})(
	FooGallery.$,
	FooGallery
);
(function($, _, _utils, _is, _fn){

	_.PortfolioTemplate = _.Template.extend({});

	_.template.register("simple_portfolio", _.PortfolioTemplate, {}, {
		container: "foogallery fg-simple_portfolio"
	});

})(
		FooGallery.$,
		FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn
);
(function ($, _, _utils, _obj) {

	_.ImageViewerTemplate = _.Template.extend({
		construct: function (options, element) {
			var self = this;
			self._super(_obj.extend({}, options, {
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
			self.$inner = $();
			/**
			 * @summary The jQuery object that displays the current image count.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $current
			 * @type {jQuery}
			 */
			self.$current = $();
			/**
			 * @summary The jQuery object that displays the current image count.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $current
			 * @type {jQuery}
			 */
			self.$total = $();
			/**
			 * @summary The jQuery object for the previous button.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $prev
			 * @type {jQuery}
			 */
			self.$prev = $();
			/**
			 * @summary The jQuery object for the next button.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $next
			 * @type {jQuery}
			 */
			self.$next = $();
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
			self.on({
				"pre-init": self.onPreInit,
				"init": self.onInit,
				"destroy": self.onDestroy,
				"append-item": self.onAppendItem,
				"after-page-change": self.onAfterPageChange,
				"after-filter-change": self.onAfterFilterChange
			}, self);
		},
		createChildren: function(){
			var self = this;
			return $("<div/>", {"class": self.cls.inner}).append(
					$("<div/>", {"class": self.cls.innerContainer}),
					$("<div/>", {"class": self.cls.controls}).append(
							$("<div/>", {"class": self.cls.prev})
									.append($("<span/>", {text: self.il8n.prev})),
							$("<label/>", {"class": self.cls.count, text: self.il8n.count})
									.prepend($("<span/>", {"class": self.cls.countCurrent, text: "0"}))
									.append($("<span/>", {"class": self.cls.countTotal, text: "0"})),
							$("<div/>", {"class": self.cls.next})
									.append($("<span/>", {text: self.il8n.next}))
					)
			);
		},
		destroyChildren: function(){
			var self = this;
			self.$el.find(self.sel.inner).remove();
		},

		onPreInit: function(event){
			var self = this;
			self.$inner = self.$el.find(self.sel.innerContainer);
			self.$current = self.$el.find(self.sel.countCurrent);
			self.$total = self.$el.find(self.sel.countTotal);
			self.$prev = self.$el.find(self.sel.prev);
			self.$next = self.$el.find(self.sel.next);
		},
		onInit: function (event) {
			var self = this;
			if (self.template.attachFooBox) {
				self.$el.on('foobox.previous', {self: self}, self.onFooBoxPrev)
						.on('foobox.next', {self: self}, self.onFooBoxNext);
			}
			self.$prev.on('click', {self: self}, self.onPrevClick);
			self.$next.on('click', {self: self}, self.onNextClick);
		},
		/**
		 * @summary Destroy the plugin cleaning up any bound events.
		 * @memberof FooGallery.ImageViewerTemplate#
		 * @function onDestroy
		 */
		onDestroy: function (event) {
			var self = this;
			if (self.template.attachFooBox) {
				self.$el.off({
					'foobox.previous': self.onFooBoxPrev,
					'foobox.next': self.onFooBoxNext
				});
			}
			self.$prev.off('click', self.onPrevClick);
			self.$next.off('click', self.onNextClick);
		},
		onAppendItem: function (event, item) {
			event.preventDefault();
			this.$inner.append(item.$el);
			item.isAttached = true;
		},
		onAfterPageChange: function(event, current, prev, isFilter){
			if (!isFilter){
				this.update();
			}
		},
		onAfterFilterChange: function(event){
			this.update();
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
				if (this.template.loop && this.pages.current === 1){
					this.pages.last();
				} else {
					this.pages.prev();
				}
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
				if (this.template.loop && this.pages.current === this.pages.total){
					this.pages.first();
				} else {
					this.pages.next();
				}
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
			attachFooBox: false,
			loop: false
		}
	}, {
		container: "foogallery fg-image-viewer",
		inner: "fiv-inner",
		innerContainer: "fiv-inner-container",
		controls: "fiv-ctrls",
		prev: "fiv-prev",
		next: "fiv-next",
		count: "fiv-count",
		countCurrent: "fiv-count-current",
		countTotal: "fiv-count-total"
	}, {
		prev: "Prev",
		next: "Next",
		count: "of"
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
				filtering: {
					type: "none"
				},
				paging: {
					pushOrReplace: "replace",
					theme: "fg-light",
					type: "default",
					size: 1,
					position: "none",
					scrollToTop: false
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
(function($, _, _utils, _is, _obj, _t){

    _.StackAlbum = _utils.Class.extend({
        construct: function(element, options){
            var self = this;
            self.$el = _is.jq(element) ? element : $(element);
            self.el = self.$el.get(0);
            self.opt = _obj.extend({}, _.StackAlbum.defaults, options, self.$el.data('foogallery'));
            self.$back = self.$el.find('.fg-header-back');
            self.$active = self.$el.find('.fg-header-active');
            self.$piles = self.$el.find('.fg-piles');
            self.piles = self.$piles.find('.fg-pile').map(function(i, el){
                return new _.StackAlbum.Pile(self, el, { index: i });
            }).get();
            self.ignoreResize = false;
            self.info = self.getLayoutInfo();
            self.robserver = new ResizeObserver(function (e) {
                if (!self.ignoreResize && self.$el.is(":visible")){
                    const width = self.$el.width();
                    if ( self.info.maxWidth !== width ) {
                        self.info = self.getLayoutInfo();
                        self.layout(true);
                    }
                }
            });
        },
        init: function(){
            var self = this;
            self.info = self.getLayoutInfo();
            self.piles.forEach(function(pile){
                pile.init();
            });
            self.$back.on('click.foogallery', {self: self}, self.onBackClick);
            self.layout(true);
            self.robserver.observe(self.el);
        },
        destroy: function(){
            var self = this;
            self.robserver.disconnect();
            self.$back.off('.foogallery');
            self.piles.forEach(function(pile){
                pile.destroy();
            });
        },
        // getLayoutInfo: function(){
        //     var self = this,
        //         space = self.opt.gutter + (self.opt.border*2);
        //     return {
        //         maxWidth: self.$el.width(),
        //         space: space,
        //         halfSpace: space/2,
        //         itemWidth: self.opt.itemWidth,
        //         itemHeight: self.opt.itemHeight,
        //         itemOuterWidth: self.opt.itemWidth + (self.opt.border*2),
        //         itemOuterHeight: self.opt.itemHeight + (self.opt.border*2),
        //         blockWidth: self.opt.itemWidth + space,
        //         blockHeight: self.opt.itemHeight + space,
        //         border: self.opt.border,
        //         doubleBorder: self.opt.border*2,
        //         gutter: self.opt.gutter,
        //         halfGutter: self.opt.gutter/2
        //     };
        // },
        getLayoutInfo: function(){
            const self = this,
                maxWidth = self.$el.width(),
                doubleBorder = self.opt.border * 2,
                space = self.opt.gutter + doubleBorder,
                maxWidthInner = maxWidth - space;

            let ratio = 1;
            if ( self.opt.itemWidth > maxWidthInner ) {
                ratio = maxWidthInner / self.opt.itemWidth;
            }

            const itemWidth = self.opt.itemWidth * ratio,
                itemHeight = self.opt.itemHeight * ratio;

            return {
                maxWidth: maxWidth,
                space: space,
                halfSpace: space/2,
                itemWidth: itemWidth,
                itemHeight: itemHeight,
                itemOuterWidth: itemWidth + doubleBorder,
                itemOuterHeight: itemHeight + doubleBorder,
                blockWidth: itemWidth + space,
                blockHeight: itemHeight + space,
                border: self.opt.border,
                doubleBorder: doubleBorder,
                gutter: self.opt.gutter,
                halfGutter: self.opt.gutter/2
            };
        },
        layout: function(immediate){
            var self = this, size;
            if (immediate){
                self.$el.addClass('fg-disable-transitions');
                self.$el.prop('offsetWidth');
            }
            if (self.hasActive){
                size = self.activePile.layout();
                self.activePile.setPosition(0, 0, size.width, size.height);
                self.$piles.css({width: size.width + 'px', height: size.height + 'px'});
            } else {
                size = self.layoutPiles();
                self.$piles.css({width: size.width + 'px', height: size.height + 'px'});
            }
            if (immediate){
                setTimeout(function(){
                    self.$el.removeClass('fg-disable-transitions');
                }, 0);
            }
        },
        layoutPiles: function(callback){
            var self = this,
                rowWidth = 0, rowCount = 1, width = 0;

            callback = _is.fn(callback) ? callback : function(){};

            self.piles.forEach(function(pile){
                var left = rowWidth;
                rowWidth += self.info.blockWidth;
                if (rowWidth > self.info.maxWidth && left > 0){
                    left = 0;
                    rowWidth = self.info.blockWidth;
                    rowCount++;
                }
                var top = self.info.blockHeight * (rowCount - 1);
                callback(pile, self.info);
                pile.layoutCollapsed();
                pile.setPosition(top, left, self.info.blockWidth, self.info.blockHeight);
                // keep track of the max calculated width
                if (rowWidth > width) width = rowWidth;
            });
            return {
                width: width,
                height: self.info.blockHeight * rowCount
            };
        },
        setActive: function(pile){
            var self = this,
                previous = self.activePile,
                hadActive = previous instanceof _.StackAlbum.Pile,
                size;

            pile = pile instanceof _.StackAlbum.Pile ? pile : null;

            self.activePile = pile;
            self.hasActive = pile !== null;

            if (hadActive){
                previous.collapse();
            }

            self.ignoreResize = true;
            if (self.hasActive){
                self.piles.forEach(function(p){
                    if (p === pile) return;
                    p.hide(self.activePile);
                });
                size = self.activePile.expand();
                self.$active.text(pile.title);
                self.$el.addClass('fg-has-active');
            } else {
                size = self.layoutPiles(function(p){
                    p.show();
                });
                self.$el.removeClass('fg-has-active');
            }
            _t.start(self.$piles, function($el){
                $el.css({width: size.width + 'px', height: size.height + 'px'});
            }, null, 350).then(function(){
                self.ignoreResize = false;
            });
        },
        onBackClick: function(e){
            e.preventDefault();
            e.stopPropagation();
            e.data.self.setActive(null);
        }
    });

    _.StackAlbum.defaults = {
        gutter: 50,
        itemWidth: 150,
        itemHeight: 150,
        border: 10,
        angleStep: 1,
        randomAngle: false
    };

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.obj,
    FooGallery.utils.transition
);
(function($, _, _utils, _is, _obj){

    _.StackAlbum.Pile = _utils.Class.extend({
        construct: function(album, element, options){
            var self = this;
            self.album = album;
            self.$el = _is.jq(element) ? element : $(element);
            self.opt = _obj.extend({}, _.StackAlbum.Pile.defaults, options, self.$el.data());
            self.title = self.opt.title;
            self.items = self.$el.find('.fg-pile-item').map(function(i, el){
                return new _.StackAlbum.Item(self, el, { index: i });
            }).get();
            self.$cover = self.$el.find('.fg-pile-cover').first();
            self.top = 0;
            self.left = 0;
            self.isExpanded = false;
        },
        init: function(){
            var self = this,
                opt = self.album.opt,
                availableAngles = self.getAngles(opt.angleStep),
                currentAngle = opt.randomAngle ? self.randomAngle(availableAngles) : opt.angleStep;

            if ( self.$cover.length === 0 && self.items.length > 0 ) {
                self.$cover = $('<div/>', {'class': 'fg-pile-cover'}).append(
                    $('<div/>', {'class': 'fg-pile-cover-content'}).append(
                        $('<span/>', {'class': 'fg-pile-cover-title', text: self.opt.title}),
                        $('<span/>', {'class': 'fg-pile-cover-count', text: self.items.length})
                    )
                );
                self.items[0].$el.addClass('fg-has-cover').append(self.$cover);
            }
            self.$cover.on('click.foogallery', {self: self}, self.onCoverClick);
            self.items.forEach(function(item, i){
                item.init();
                if (i > 3) return; // we only care about the first 4 items after init
                if (i === 0){
                    item.load();
                } else {
                    if (i % 2 === 0){
                        item.setAngle(-currentAngle);
                    } else {
                        item.setAngle(currentAngle);
                    }
                    if (opt.randomAngle){
                        currentAngle = self.randomAngle(availableAngles);
                    } else {
                        currentAngle += opt.angleStep;
                    }
                }
            });
        },
        destroy: function(){
            var self = this;
            self.$cover.remove();
            self.items.forEach(function(item, i){
                if (i === 0) item.$el.removeClass('fg-has-cover');
                item.destroy();
            });
        },
        getAngles: function(step){
            var result = [], i = 1;
            for (; i <= 3; i++){
                result.push(i * step);
            }
            return result;
        },
        randomAngle: function(available){
            var min = 0, max = available.length,
                index = Math.floor(Math.random() * (max - min) + min),
                angle = available.splice(index, 1);
            return angle.length === 1 ? angle[0] : 0;
        },
        setPosition: function(top, left, itemWidth, itemHeight){
            var self = this;
            self.top = top;
            self.left = left;
            if (_is.number(itemWidth) && _is.number(itemHeight)){
                self.$el.css({top: top + 'px', left: left + 'px', width: itemWidth + 'px', height: itemHeight + 'px'});
            } else {
                self.$el.css({top: top + 'px', left: left + 'px'});
            }
        },
        layout: function(){
            const self = this,
                info = self.album.info;

            if ( !self.isExpanded ) {
                return self.layoutCollapsed();
            }

            let rowWidth = 0, rowCount = 1,
                isNew = false, width = 0;

            self.items.forEach(function(item){
                rowWidth += info.halfGutter;
                if (rowWidth > info.maxWidth){
                    rowWidth = info.halfGutter;
                    rowCount++;
                    isNew = true;
                    console.log("A");
                }
                var left = rowWidth;
                rowWidth += info.itemOuterWidth + info.halfGutter;
                if (!isNew && rowWidth > info.maxWidth){
                    left = info.halfGutter;
                    rowWidth = info.blockWidth;
                    rowCount++;
                    console.log("B");
                }
                var top = (info.blockHeight * (rowCount - 1)) + info.halfGutter;
                isNew = false;
                item.setPosition(top, left, info.itemOuterWidth, info.itemOuterHeight);
                if (!item.isLoaded) item.load();
                // keep track of the max calculated width
                if (rowWidth > width) width = rowWidth;
            });
            return {
                width: width,
                height: info.blockHeight * rowCount
            };
        },

        layoutCollapsed: function(){
            const self = this,
                info = self.album.info;
            self.items.forEach(function(item){
                item.setPosition(info.halfGutter, info.halfGutter, info.itemOuterWidth, info.itemOuterHeight);
            });
            return {
                width: info.blockWidth,
                height: info.blockHeight
            };
        },

        expand: function(){
            var self = this, size;
            self.$el.removeClass('fg-collapsed').addClass('fg-expanded');
            self.isExpanded = true;
            size = self.layout();
            self.setPosition(0, 0, size.width, size.height);
            return size;
        },
        collapse: function(){
            var self = this, size;
            self.$el.removeClass('fg-expanded').addClass('fg-collapsed');
            self.isExpanded = false;
            size = self.layout();
            self.setPosition(0, 0, size.width, size.height);
            return size;
        },
        show: function(){
            var self = this;
            self.$el.removeClass('fg-hidden fg-expanded fg-collapsed');
        },
        hide: function(behind){
            var self = this;
            if (behind instanceof _.StackAlbum.Pile){
                self.setPosition(behind.top, behind.left);
            }
            self.$el.addClass('fg-hidden');
        },

        onCoverClick: function(e){
            e.preventDefault();
            e.stopPropagation();
            var self = e.data.self;
            self.album.setActive(self);
        }
    });

    _.StackAlbum.Pile.defaults = {
        index: -1,
        title: null
    };

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.obj
);
(function($, _, _utils, _is, _obj){

    _.StackAlbum.Item = _utils.Class.extend({
        construct: function(pile, element, options){
            var self = this;
            self.pile = pile;
            self.$el = _is.jq(element) ? element : $(element);
            self.opt = _obj.extend({}, _.StackAlbum.Item.defaults, options, self.$el.data());
            self.$thumb = self.$el.find('.fg-pile-item-thumb');
            self.$image = self.$el.find('.fg-pile-item-image');
            self.isLoaded = false;
            self.isLoading = false;
            self._loading = null;
        },
        init: function(){
            const self = this,
                info = self.pile.album.info;
            self.$el.css({width: info.itemOuterWidth + 'px', height: info.itemOuterHeight + 'px'});
        },
        destroy: function(){
            const self = this;
            self.$el.css({top: '', left: '', width: '', height: '', transform: ''});
        },
        setAngle: function(angle){
            var self = this;
            self.$el.css({transform: 'rotate(' + angle + 'deg)'});
        },
        setPosition: function(top, left, itemWidth, itemHeight){
            var self = this;
            self.$el.css({top: top + 'px', left: left + 'px', width: itemWidth + 'px', height: itemHeight + 'px'});
        },
        load: function(){
            var self = this;
            if (_is.promise(self._loading)) return self._loading;
            return self._loading = $.Deferred(function(def){
                self.$el.addClass('fg-loading');
                self.isLoading = true;
                self.$image.on({
                    'load.foogallery': function(){
                        self.$image.off('.foogallery');
                        self.$el.removeClass('fg-loading');
                        self.isLoading = false;
                        self.isLoaded = true;
                        def.resolve();
                    },
                    'error.foogallery': function(){
                        self.$image.off('.foogallery');
                        self.$el.removeClass('fg-loading');
                        self.isLoading = false;
                        self.isLoaded = true;
                        def.reject();
                    }
                });
                self.$image.prop('src', self.$image.attr(self.opt.src))
                    .prop('srcset', self.$image.attr(self.opt.srcset));
            }).promise();
        }
    });

    _.StackAlbum.Item.defaults = {
        index: -1,
        src: 'data-src-fg',
        srcset: 'data-srcset-fg'
    };

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.obj
);
(function ($, _, _utils) {

    $.fn.foogalleryStackAlbum = function(options){
        return this.each(function(i, el){
            var $el = $(el), inst = $el.data('__FooGalleryAlbum__');
            if (inst instanceof _.StackAlbum) inst.destroy();
            inst = new _.StackAlbum($el);
            inst.init();
            $el.data('__FooGalleryAlbum__', inst);
        });
    };

    _.loadStackAlbums = _.reloadStackAlbums = function(){
        // this automatically initializes all templates on page load
        $(function () {
            $('.foogallery-stack-album:not(.fg-ready)').foogalleryStackAlbum();
        });

        _utils.ready(function () {
            $('.foogallery-stack-album.fg-ready').foogalleryStackAlbum();
        });
    };

    _.loadStackAlbums();

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils
);
(function($, _, _icons, _utils, _is){

    _utils.Progress = _utils.EventClass.extend( {
        construct: function( tickRate ){
            const self = this;
            self._super();
            self.percent = 0;
            self.tickRate = _is.number( tickRate ) ? tickRate : 100;
            self.isPaused = false;
            self.isActive = false;
            self._intervalId = null;
            self._total = 0;
            self._target = null;
            self.onTick = self.onTick.bind( self );
        },
        destroy: function(){
            const self = this;
            self._reset();
            self._super();
        },
        _reset: function(){
            const self = this;
            if ( self._intervalId !== null ) clearInterval( self._intervalId );
            self.percent = 0;
            self._total = 0;
            self._intervalId = null;
            self._target = null;
            self.isActive = false;
            self.isPaused = false;
        },
        stop: function(){
            const self = this;
            if ( self.isActive ){
                self._reset();
                self.trigger( "stop" );
            }
        },
        start: function( seconds ){
            const self = this;
            self.stop();
            if ( !self.isActive && _is.number( seconds ) && seconds > 0 ){
                self._total = seconds * 1000;
                self._target = Date.now() + self._total;
                self._intervalId = setInterval( self.onTick, self.tickRate );
                self.isActive = true;
                self.trigger( "start" );
            }
        },
        pause: function(){
            const self = this;
            if ( self.isActive && !self.isPaused && self.percent < 100 ){
                if ( self._intervalId !== null ) clearInterval( self._intervalId );
                self._intervalId = null;
                self._target = null;
                self.isPaused = true;
                self.trigger( "pause" );
            }
        },
        resume: function(){
            const self = this;
            if ( self.isActive && self.isPaused ){
                const remaining = self._total - ( ( self._total * self.percent ) / 100 );
                self._target = Date.now() + remaining;
                self._intervalId = setInterval( self.onTick, self.tickRate );
                self.isPaused = false;
                self.trigger( "resume" );
            } else if ( self._total > 0 ){
                self.start( self._total / 1000 );
            }
        },
        onTick: function(){
            const self = this;
            const diff = self._total - Math.max( self._target - Date.now(), 0 );
            self.percent = Math.min( ( diff / self._total ) * 100, 100 );
            self.trigger( "tick", [ self.percent ] );
            if ( self.percent >= 100 ){
                if ( self._intervalId !== null ) clearInterval( self._intervalId );
                self._intervalId = null;
                self._target = null;
                self.isActive = false;
                self.trigger( "complete" );
            }
        }
    } );

    /**
     * @memberof FooGallery.
     * @class Carousel
     * @extends FooGallery.utils.Class
     */
    _.Carousel = _utils.Class.extend( /** @lends FooGallery.Carousel.prototype */ {
        /**
         * @ignore
         * @constructs
         * @param {FooGallery.Template} template
         * @param {object} opt
         * @param {object} cls
         * @param {object} sel
         */
        construct: function(template, opt, cls, sel){
            const self = this;
            self.tmpl = template;
            self.el = template.el;
            self.opt = opt;
            self.cls = cls;
            self.sel = sel;
            self.elem = {
                inner: null,
                center: null,
                bottom: null,
                prev: null,
                next: null,
                progress: null
            };
            self.activeItem = null;
            self._itemWidth = 0;
            self._leftExclude = [ self.sel.activeItem, self.sel.nextItem ].join( "," );
            self._rightExclude = [ self.sel.activeItem, self.sel.prevItem ].join( "," );
            /**
             *
             * @type {FooGallery.utils.DOMEventListeners}
             * @private
             */
            self._centerListeners = new _utils.DOMEventListeners();
            /**
             *
             * @type {FooGallery.utils.DOMEventListeners}
             * @private
             */
            self._listeners = new _utils.DOMEventListeners();
            /**
             *
             * @type {FooGallery.utils.Progress}
             * @private
             */
            self._progress = new _utils.Progress();
            self._canHover = window.matchMedia("(hover: hover)").matches;
            self.cache = new Map();
            self.timeouts = new _utils.Timeouts();
            self.interacted = false;
            self.isRTL = false;
            self._firstLayout = true;
        },

        //#region Transform Utils

        /**
         * @summary Calculate the screen X co-ord given a 3D points' X, Z co-ords and the perspective.
         * @memberof
         * @param {number} vectorX - The 3D point X co-ord.
         * @param {number} vectorZ - The 3D point Z co-ord.
         * @param {number} perspective - The visual perspective.
         * @returns {number} The screen X co-ord.
         */
        getScreenX: function( vectorX, vectorZ, perspective ){
            return ( perspective / ( perspective + vectorZ ) ) * vectorX;
        },
        /**
         * @summary Calculate a 3D points' X co-ord given the screens' X co-ord, the 3D points' Z co-ord and the perspective.
         * @param {number} screenX - The screen X co-ord.
         * @param {number} vectorZ - The 3D point Z co-ord.
         * @param {number} perspective - The visual perspective.
         * @returns {number} The 3D point X co-ord.
         */
        getVectorX: function( screenX, vectorZ, perspective ){
            return ( ( perspective + vectorZ ) / perspective ) * screenX;
        },
        /**
         * @summary Calculate the incremental Z co-ord for a 3D point that is part of a scaled sequence.
         * @param {number} index - The index within the sequence for this iteration.
         * @param {number} scale - The scale to adjust each iteration by.
         * @param {number} perspective - The visual perspective.
         * @returns {number} The 3D point Z co-ord
         */
        getSequentialZFromScale: function( index, scale, perspective ){
            return perspective * ( scale * ( index + 1 ) );
        },
        /**
         * @summary Scales the value to the given 3D points' Z co-ord and perspective.
         * @param {number} value - The value to be scaled.
         * @param {number} vectorZ - The 3D point Z co-ord.
         * @param {number} perspective - The visual perspective.
         * @returns {number} The scaled value.
         */
        scaleToZ: function( value, vectorZ, perspective ){
            return value * ( 1 - vectorZ / ( perspective + vectorZ ) );
        },
        /**
         * @summary Returns the absolute difference between 2 numbers.
         * @param {number} num1
         * @param {number} num2
         * @returns {number}
         */
        getDiff: function( num1, num2 ){
            return num1 > num2 ? num1 - num2 : num2 - num1;
        },

        //#endregion

        //#region Autoplay

        pause: function(){
            this._progress.pause();
        },
        resume: function(){
            this._progress.resume();
        },
        start: function(){
            if ( this.opt.autoplay.interaction === "disable" && this.interacted ) return;
            this._progress.start( this.opt.autoplay.time );
        },
        stop: function(){
            this._progress.stop();
        },

        //#endregion

        init: function(){
            const self = this;
            self.isRTL = window.getComputedStyle(self.el).direction === "rtl";

            self.elem = {
                inner: self.el.querySelector( self.sel.inner ),
                center: self.el.querySelector( self.sel.center ),
                bottom: self.el.querySelector( self.sel.bottom ),
                prev: self.el.querySelector( self.sel.prev ),
                next: self.el.querySelector( self.sel.next ),
                progress: self.el.querySelector( self.sel.progress )
            };

            if ( self.opt.perspective !== 150 ){
                self.el.style.setProperty( "--fg-carousel-perspective", self.opt.perspective + "px" );
            }
        },
        postInit: function(){
            const self = this;
            self.activeItem = self.tmpl.items.first();
            self.initNavigation();
            self.initPagination();
            self.initSwipe();
            self.initAutoplay();
        },
        initNavigation: function(){
            const self = this;
            self._listeners.add( self.elem.prev, "click", function( event ){
                event.preventDefault();
                self.interacted = true;
                self.previous();
            } );
            if ( self.elem.prev.type !== "button" ) self.elem.prev.type = "button";
            self.elem.prev.appendChild( _icons.element( "arrow-left" ) );

            self._listeners.add( self.elem.next, "click", function( event ){
                event.preventDefault();
                self.interacted = true;
                self.next();
            } );
            if ( self.elem.next.type !== "button" ) self.elem.next.type = "button";
            self.elem.next.appendChild( _icons.element( "arrow-right" ) );
        },
        initPagination: function(){
            const self = this;
            const count = self.tmpl.items.count();
            for (let i = 0; i < count; i++){
                const bullet = document.createElement( "button" );
                bullet.type = "button";
                bullet.classList.add( self.cls.bullet );
                if ( i === 0 ) bullet.classList.add( self.cls.activeBullet );
                self._listeners.add( bullet, "click", function( event ){
                    event.preventDefault();
                    self.interacted = true;
                    self.goto( self.tmpl.items.get( i ) );
                } );
                self.elem.bottom.appendChild( bullet );
            }
        },
        initSwipe: function(){
            const self = this;
            let startX = 0, endX = 0, min = 25 * (window.devicePixelRatio || 1);
            self._listeners.add( self.elem.inner, "touchstart", function( event ){
                self.interacted = true;
                startX = event.changedTouches[0].screenX;
            }, { passive: true } );

            self._listeners.add( self.elem.inner, "touchend", function( event ){
                endX = event.changedTouches[0].screenX;
                const diff = self.getDiff( startX, endX );
                if ( diff > min ){
                    if ( endX < startX ){ // swipe left
                        self.next();
                    } else { // swipe right
                        self.previous();
                    }
                }
                endX = 0;
                startX = 0;
            }, { passive: true } );
        },
        initAutoplay: function(){
            const self = this, opt = self.opt.autoplay;
            if ( opt.time <= 0 || !( self.elem.progress instanceof HTMLElement ) ) return;

            const progressElement = self.elem.progress.style;

            let frame = null;
            function update( percent, immediate ){
                _utils.cancelFrame( frame );
                _utils.requestFrame( function(){
                    progressElement.setProperty( "width", percent + "%" );
                    if ( immediate ){
                        progressElement.setProperty( "transition-duration", "0s" );
                    } else {
                        progressElement.setProperty( "transition-duration", self._progress.tickRate + "ms" );
                    }
                } );
            }

            self._progress.on( {
                "start stop": function(){
                    update( 0, true );
                },
                "pause resume": function(){
                    update( self._progress.percent, true );
                },
                "tick": function() {
                    update( self._progress.percent, false );
                },
                "complete": function() {
                    self.next( function(){
                        self._progress.start( opt.time );
                    } );
                }
            } );

            if ( opt.interaction === "pause" ){
                if ( self._canHover ) {
                    self._listeners.add( self.el, "mouseenter", function( event ) {
                        self._progress.pause();
                    }, { passive: true } );
                    self._listeners.add( self.el, "mouseleave", function( event ) {
                        self._progress.resume();
                    }, { passive: true } );
                } else {
                    // handle touch only
                    self._listeners.add( self.el, "touchstart", function( event ) {
                        self.timeouts.delete( "autoplay" );
                        self._progress.pause();
                    }, { passive: true } );

                    self._listeners.add( self.el, "touchend", function( event ) {
                        self.timeouts.set( "autoplay", function(){
                            self._progress.resume();
                        }, opt.time * 1000 );
                    }, { passive: true } );
                }
            }
            self._progress.start( opt.time );
        },
        getFirst: function(){
            return this.tmpl.items.first();
        },
        getNext: function( item ){
            return this.tmpl.items.next( !( item instanceof _.Item ) ? this.activeItem : item, null, true );
        },
        getPrev: function( item ){
            return this.tmpl.items.prev( !( item instanceof _.Item ) ? this.activeItem : item, null, true );
        },
        goto: function( item, callback ){
            const self = this;
            if ( !( item instanceof _.Item ) ){
                return;
            }
            const autoplay = self.opt.autoplay;
            const restart = !self._canHover && self.timeouts.has( "autoplay" );
            self.timeouts.delete( "autoplay" );
            self.timeouts.delete( "navigation" );

            const pause = self._progress.isPaused;
            if ( self._progress.isActive ){
                self._progress.stop();
            }

            self.activeItem = item;
            self.layout();

            self.timeouts.set( "navigation", function(){
                if ( autoplay.time > 0 && ( autoplay.interaction === "pause" || ( autoplay.interaction === "disable" && !self.interacted ) ) ){
                    self._progress.start( autoplay.time );
                    if ( pause ){
                        self._progress.pause();
                    }
                    if ( restart ){
                        self.timeouts.set( "autoplay", function(){
                            self._progress.resume();
                        }, autoplay.time * 1000 );
                    }
                }
                if ( _is.fn( callback ) ) callback.call( self );
            }, self.opt.speed );
        },
        next: function( callback ){
            this.goto( this.getNext(), callback );
        },
        previous: function( callback ){
            this.goto( this.getPrev(), callback );
        },
        destroy: function(){
            const self = this;
            self.cache.clear();
            self.timeouts.clear();
            self._listeners.clear();
            self._centerListeners.clear();
            if ( self.opt.perspective !== 150 ){
                self.el.style.removeProperty( "--fg-carousel-perspective" );
            }
        },
        getSize: function( element, inner ){
            const rect = element.getBoundingClientRect();
            const size = { width: rect.width, height: rect.height };
            if ( inner ){
                const style = getComputedStyle( element );
                size.width -= parseFloat( style.paddingLeft ) + parseFloat( style.paddingRight ) + parseFloat( style.borderLeftWidth ) + parseFloat( style.borderRightWidth );
                size.height -= parseFloat( style.paddingTop ) + parseFloat( style.paddingBottom ) + parseFloat( style.borderTopWidth ) + parseFloat( style.borderBottomWidth );
            }
            return size;
        },
        layout: function( width ){
            const self = this;
            if ( self.activeItem === null ){
                self.activeItem = self.tmpl.items.first();
            }
            if ( !_is.number( width ) && self.cache.has( "width" ) ){
                width = self.cache.get( "width" );
            }
            if ( !_is.number( width ) ) return;

            const layout = self.getLayout( width );
            if ( self._layoutFrame !== null ) _utils.cancelFrame( self._layoutFrame );
            self._layoutFrame = _utils.requestFrame( function(){
                self._layoutFrame = null;
                if ( self.renderActive( layout ) ){
                    self.renderSide( "left", self.sel.prevItem, self._leftExclude, self.cls.prevItem, layout );
                    self.renderSide( "right", self.sel.nextItem, self._rightExclude, self.cls.nextItem, layout );
                    self._firstLayout = false;
                }
            } );
        },
        getLayout: function( width ){
            const self = this;

            if ( self.cache.has("layout") && self.cache.get("width") === width ){
                return self.cache.get("layout");
            }
            const itemWidth = self.getSize( self.elem.center ).width;
            const maxOffset = ( self.getSize( self.elem.inner, true ).width / 2 ) + ( itemWidth / 2 );
            const layout = self.calculate( itemWidth, maxOffset );
            self.cache.set( "width", width );
            self.cache.set( "layout", layout );
            return layout;
        },
        round: function( value, precision ){
            let multiplier = Math.pow(10, precision || 0);
            return Math.round(value * multiplier) / multiplier;
        },
        getShowPerSide: function(){
            const self = this, count = self.tmpl.items.count();
            let perSide = self.opt.maxItems;
            if ( perSide === "auto" || perSide <= 0 ) perSide = ( count % 2 === 0 ? count - 1 : count );
            if ( perSide % 2 === 0 ) perSide -= 1;
            if ( perSide < 1 ) perSide = 1;
            if ( perSide > count ) perSide = ( count % 2 === 0 ? count - 1 : count );
            return ( perSide - 1 ) / 2;
        },
        calculate: function( itemWidth, maxOffset, gutter, showPerSide ){
            const self = this;
            if ( !_is.number( gutter ) ){
                gutter = self.opt.gutter.max;
            }
            if ( !_is.number( showPerSide ) ){
                showPerSide = self.getShowPerSide();
            }

            const result = {
                zIndex: showPerSide + 10,
                gutter: gutter,
                perSide: showPerSide,
                side: []
            };

            let offset = itemWidth, zIndex = result.zIndex - 1;
            for (let i = 0; i < showPerSide; i++, zIndex--){
                const z = self.getSequentialZFromScale( i, self.opt.scale, self.opt.perspective );
                const width = self.scaleToZ( itemWidth, z, self.opt.perspective );
                const diff = ( itemWidth - width ) / 2;

                offset -= diff;

                const gutterStep = 1;
                const gutterValue = self.opt.gutter.unit === "%" ? itemWidth * Math.abs( gutter / 100 ) : Math.abs( gutter );
                const gutterOffset = self.opt.gutter.unit === "%" ? self.scaleToZ( gutterValue, z, self.opt.perspective ) : gutterValue;
                if (gutter > 0){
                    offset += gutterOffset;
                } else {
                    offset -= gutterOffset;
                }
                if ( offset + width + diff > maxOffset ){
                    if ( gutter - gutterStep < self.opt.gutter.min ){
                        return self.calculate( itemWidth, maxOffset, self.opt.gutter.max, showPerSide - 1);
                    }
                    return self.calculate( itemWidth, maxOffset, gutter - gutterStep, showPerSide);
                }

                const x = self.getVectorX( offset, z, self.opt.perspective );

                offset += width + diff;

                result.side.push({x: x, z: z, zIndex: zIndex });
            }
            return result;
        },
        cleanup: function( selector, className, exclude ){
            const self = this;
            const hasExclude = _is.string( exclude );
            Array.from( self.el.querySelectorAll( selector ) ).forEach( function( node ){

                node.classList.remove( className );
                if ( self.opt.centerOnClick ){
                    self._centerListeners.remove( node, "click" );
                }

                if ( hasExclude && node.matches( exclude ) ) return;

                node.style.removeProperty( "transition-duration" );
                node.style.removeProperty( "transform" );
                node.style.removeProperty( "z-index" );
            } );
        },
        renderActive: function( layout ){
            const self = this;
            if ( ! ( self.activeItem instanceof _.Item ) ) return false;
            const el = self.activeItem.el;

            self.cleanup( self.sel.activeItem, self.cls.activeItem );

            el.classList.add( self.cls.activeItem );
            el.style.setProperty("transition-duration", self.opt.speed + "ms" );
            el.style.setProperty( "z-index", layout.zIndex );
            el.style.removeProperty( "transform" );

            const ai = self.tmpl.items.indexOf( self.activeItem );
            Array.from( self.el.querySelectorAll( self.sel.bullet ) ).forEach( function( node, i ){
                node.classList.remove( self.cls.activeBullet );
                if ( i === ai ) node.classList.add( self.cls.activeBullet );
            } );
            return true;
        },
        renderSide: function( side, selector, exclude, cls, layout ) {
            const self = this;
            if ( [ "left","right" ].indexOf( side ) === -1 ) return;

            self.cleanup( selector, cls, exclude );

            let place = self.activeItem;
            for (let i = 0; i < layout.side.length; i++ ){
                const values = layout.side[i];
                const item = side === "left" ? self.getPrev( place ) : self.getNext( place );
                if ( item instanceof _.Item ){
                    let transform = "translate3d(" + ( ( side === "left" && !self.isRTL ) || ( side === "right" && self.isRTL ) ? "-" : "" ) + values.x + "px, 0,-" + values.z + "px)";
                    item.el.classList.add( cls );
                    if ( !item.isLoaded ){
                        item.el.style.setProperty("transition-duration", "0ms" );
                        item.el.style.setProperty( "transform", "translate3d(0,0,-" + self.opt.perspective + "px)" );
                        item.el.offsetHeight;
                    }
                    item.el.style.setProperty("transition-duration", ( self._firstLayout ? 0 : self.opt.speed ) + "ms" );
                    item.el.style.setProperty( "transform", transform );
                    item.el.style.setProperty( "z-index", values.zIndex );

                    if ( self.opt.centerOnClick ){
                        self._centerListeners.add( item.el, "click", function( event ){
                            event.preventDefault();
                            event.stopPropagation();
                            self.interacted = true;
                            self.goto( item );
                        }, true );
                    }

                    place = item;
                } else {
                    // exit early if no item was found
                    break;
                }
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.icons,
    FooGallery.utils,
    FooGallery.utils.is
);
(function($, _, _obj){

    _.CarouselTemplate = _.Template.extend({
        construct: function(options, element){
            const self = this;
            self._super(_obj.extend({}, options, {
                paging: {
                    type: "none"
                }
            }), element);
            self.items.LAYOUT_AFTER_LOAD = false;
            self.carousel = null;
            self.on({
                "pre-init": self.onPreInit,
                "init": self.onInit,
                "post-init": self.onPostInit,
                "destroyed": self.onDestroyed,
                "append-item": self.onAppendItem,
                "after-filter-change": self.onAfterFilterChange,
                "layout": self.onLayout
            }, self);
            if ( !!_.Panel && self.lightbox instanceof _.Panel ){
                self.lightbox.on({
                    "open": self.onLightboxOpen,
                    "closed": self.onLightboxClosed,
                    "next": self.onLightboxNext,
                    "prev": self.onLightboxPrev
                }, self);
            }
        },
        onPreInit: function(){
            const self = this;
            self.carousel = new _.Carousel( self, self.template, self.cls.carousel, self.sel.carousel );
        },
        onInit: function(){
            this.carousel.init();
        },
        onPostInit: function(){
            this.carousel.postInit();
        },
        onDestroyed: function(){
            const self = this;
            if (self.carousel instanceof _.Carousel){
                self.carousel.destroy();
            }
        },
        onAppendItem: function (event, item) {
            event.preventDefault();
            this.carousel.elem.inner.appendChild(item.el);
            item.isAttached = true;
        },
        onAfterFilterChange: function(){
            this.carousel.cache.delete( "layout" );
            this.carousel.interacted = true;
            this.carousel.goto(this.carousel.getFirst());
        },
        onLayout: function(){
            this.carousel.layout(this.lastWidth);
        },
        onLightboxOpen: function(){
            this.carousel.interacted = true;
            this.carousel.stop();
        },
        onLightboxClosed: function(){
            this.carousel.start();
        },
        onLightboxNext: function(){
            this.carousel.next();
        },
        onLightboxPrev: function(){
            this.carousel.previous();
        }
    });

    _.template.register("carousel", _.CarouselTemplate, {
        template: {
            maxItems: 0, // "auto" or 0 will be calculated on the fly.
            perspective: 150,
            scale: 0.12,
            speed: 300,
            centerOnClick: true,
            gutter: {
                min: -40,
                max: -20,
                unit: "%"
            },
            autoplay: {
                time: 0,
                interaction: "pause" // "pause" or "disable"
            }
        }
    }, {
        container: "foogallery fg-carousel",
        carousel: {
            inner: "fg-carousel-inner",
            center: "fg-carousel-center",
            bottom: "fg-carousel-bottom",
            prev: "fg-carousel-prev",
            next: "fg-carousel-next",
            bullet: "fg-carousel-bullet",
            activeBullet: "fg-bullet-active",
            activeItem: "fg-item-active",
            prevItem: "fg-item-prev",
            nextItem: "fg-item-next",
            progress: "fg-carousel-progress"
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils.obj
);
(function ($, _, _utils, _obj, _is) {

	/**
	 * Contains any FooBox specific integration code.
	 *
	 * @namespace FooGallery.__foobox__
	 * @private
	 */
	_.__foobox__ = {
		/**
		 * Check if the element is displayed within FooBox.
		 *
		 * @param {jQuery} $element
		 * @return {boolean}
		 */
		owns: function( $element ){
			return $element.parents(".fbx-item").length > 0;
		},
		/**
		 * Check if the template can be handled by FooBox.
		 *
		 * @param {FooGallery.Template} template
		 * @return {boolean}
		 */
		handles: function( template ){
			return template.$el.hasClass("fbx-instance") && _is.object( window[ 'FOOBOX' ] ) && !!$.fn[ 'foobox' ];
		},
		/**
		 * Updates the template's FooBox.
		 *
		 * @param {FooGallery.Template} template
		 */
		update: function( template ){
			const opts = [{}];
			if ( _is.object( window[ 'FOOBOX' ][ 'o' ] ) ) {
				opts.push( window[ 'FOOBOX' ][ 'o' ] );
			}
			if ( template.opt.protected ) {
				opts.push( { images: { noRightClick: true } } );
			}
			if ( _is.fn( template.$el[ 'foobox' ] ) ) {
				template.$el[ 'foobox' ]( _obj.extend.apply( null, opts ) );
			}
		}
	};

	/**
	 * Handles the ready, after-page-change and after-filter-change events and conditional raises a post-load event
	 * on the document body to notify other plugins that content has changed.
	 * @param e
	 * @param current
	 * @param prev
	 * @param isFilter
	 */
	_.triggerPostLoad = function (e, current, prev, isFilter) {
		const tmpl = e.target;
		if (tmpl instanceof _.Template){
			if (tmpl.initialized && (e.type === "ready" || (e.type === "after-page-change" && !isFilter) || e.type === "after-filter-change")) {
				try {
					if ( _.__foobox__.owns( tmpl.$el ) ) return;

					if ( _.__foobox__.handles( tmpl ) ){
						_.__foobox__.update( tmpl );
					} else {
						$("body").trigger("post-load");
					}
				} catch(err) {
					console.error(err);
				}
			}
		}
	};

	/**
	 * The options applied to all galleries initialized using the auto mechanism.
	 *
	 * @memberof FooGallery.
	 * @name autoDefaults
	 * @type {object}
	 */
	_.autoDefaults = {
		on: {
			"ready after-page-change after-filter-change": _.triggerPostLoad
		}
	};

	/**
	 * If set to FALSE then FooGallery will not automatically initialize itself on all valid elements
	 * with an ID starting with 'foogallery-gallery-'.
	 *
	 * @memberof FooGallery.
	 * @name autoEnabled
	 * @type {boolean}
	 * @default true
	 */
	_.autoEnabled = true;

	/**
	 * Allows you to merge options into the FooGallery.autoDefaults object.
	 *
	 * @memberof FooGallery.
	 * @function auto
	 * @param {object} options
	 * @returns {object} The result of the merged options.
	 */
	_.auto = function (options) {
		return _.autoDefaults = _obj.merge(_.autoDefaults, options);
	};

	/**
	 * Indicates if any globally supplied variables such as the FooGallery_il8n object have been merged.
	 *
	 * @memberof FooGallery.
	 * @name globalsMerged
	 * @type {boolean}
	 * @default false
	 * @readonly
	 */
	_.globalsMerged = false;

	/**
	 * Merges any globally supplied variables such as the FooGallery_il8n object into the various component configurations for the plugin.
	 */
	_.mergeGlobals = function(){
		// if this has already been done, don't do it again
		if ( _.globalsMerged === true ) return;

		if ( _is.object( window[ 'FooGallery_il8n' ] ) ){
			_.merge_il8n( window[ 'FooGallery_il8n' ] );
			_.globalsMerged = true;
		}
	};

	/**
	 * Merges an "il8n" configuration object into the various component configurations for the plugin.
	 * @param configuration
	 */
	_.merge_il8n = function( configuration ){
		if ( !_is.object( configuration ) ) return;
		Object.keys( configuration ).forEach( ( factoryName ) => {
			if ( _is.object( configuration[ factoryName ] ) && _[ factoryName ] instanceof _.Factory ) {
				const factory = /** @type FooGallery.Factory */ _[ factoryName ],
					componentConfiguration = configuration[ factoryName ];

				Object.keys( componentConfiguration ).forEach( ( componentName ) => {
					if ( _is.object( componentConfiguration[ componentName ] ) ) {
						factory.configure( componentName, null, null, componentConfiguration[ componentName ] );
					}
				} );
			}
		} );
	};

	_.load = _.reload = function(){
		let jqReady = false, customReady = false;
		// this automatically initializes all templates on page load
		$(function () {
			_.mergeGlobals();
			if (_.autoEnabled){
				$('[id^="foogallery-gallery-"]:not(.fg-ready)').foogallery(_.autoDefaults);
			}
			jqReady = true;
			if ( jqReady && customReady ){
				document.dispatchEvent( new CustomEvent( 'foogallery-loaded', { detail: _ } ) );
			}
		});

		_utils.ready(function () {
			_.mergeGlobals();
			if (_.autoEnabled){
				$('[id^="foogallery-gallery-"].fg-ready').foogallery(_.autoDefaults);
			}
			customReady = true;
			if ( jqReady && customReady ){
				document.dispatchEvent( new CustomEvent( 'foogallery-loaded', { detail: _ } ) );
			}
		});
	};

	document.dispatchEvent( new CustomEvent( 'foogallery-ready', { detail: _ } ) );

	_.load();

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.obj,
	FooGallery.utils.is
);