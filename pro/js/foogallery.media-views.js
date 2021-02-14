// wp.foogallery - create the base namespace to house the FooGallery media code
(function(){

	/**
	 * Create and return a media frame.
	 *
	 * Handles the default media experience as well as FooGallery's customized frames.
	 *
	 * @alias wp.foogallery
	 * @memberOf wp
	 * @namespace
	 *
	 * @param  {object} attributes The properties passed to the main media controller.
	 * @return {wp.media.view.MediaFrame} A media workflow.
	 */
	wp.foogallery = function(attributes){
		attributes = _.defaults( attributes || {}, {
			frame: 'select'
		});

		var frame;
		if ( 'select' === attributes.frame && wp.foogallery.Select ) {
			frame = new wp.foogallery.Select( attributes );
			delete attributes.frame;
			wp.media.frame = frame;
			return frame;
		} else if ( 'select-child' === attributes.frame && wp.foogallery.media.Select ){
			frame = new wp.foogallery.media.Select( attributes );
			delete attributes.frame;
			wp.media.frame = frame;
			return frame;
		}

		return wp.media.apply(this, arguments);
	};

	wp.foogallery.media = {};
	wp.foogallery.frames = {};
	wp.foogallery.debug = false;

})();
// wp.foogallery.l10n - setup the various strings used by the media modal
(function(l10n){

	/**
	 * The various strings used by the FooGallery media modal.
	 *
	 * @memberof wp.foogallery
	 *
	 * @type {object}
	 * @property {object} tabs
	 * @property {string} tabs.upload
	 * @property {string} tabs.browse
	 * @property {string} tabs.import
	 * @property {object} toolbar
	 * @property {string} toolbar.cancel
	 * @property {string} toolbar.import
	 */
	wp.foogallery.l10n = {
		tabs: {
			upload: l10n && _.isString(l10n.uploadFilesTitle) ? l10n.uploadFilesTitle : "Upload Files",
			browse: l10n && _.isString(l10n.mediaLibraryTitle) ? l10n.mediaLibraryTitle : "Media Library",
			import: "Import Videos"
		},
		toolbar: {
			back: "Back",
			import: "Import",
			loadMore: "Load More Results"
		},
		errorTitle: "An Error Occurred",
		errors: {
			unknown: "An unknown error has occurred.",
			unexpectedResponse: "Unexpected response.",
			requestAborted: "Request aborted.",
			invalidAttachment: "The selected attachment does not have the required information available.",
			required: "Required.",
			pattern: "Regular expression failed.",
			importNoVideos: "No videos supplied for import."
		},
		compatibility: {
			supported: "Supported.",
			desktopEdgeWebM: "Progressive sources not supported.",
			mobileOperaWebM: "Requires codec.",
			desktopEdgeOgg: "Supported from 17 onward.",
			mobileFirefoxMP4: "Hardware acceleration not supported."
		},
		import: {
			cancel: "Cancel",
			cancelling: "Cancelling",
			cancelled: "Cancelled",
			complete: "Complete",
			fetching: "Fetching next page...",
			importing: "Importing {start} to {end} of {total} - {percent}%"
		}
	};

	/**
	 * Basic string format function to help with localization.
	 * @param {string} target - The format string containing any placeholders to replace.
	 * @param {string|number|Object|Array} arg1 - The first value to format the target with. If an object is supplied it's properties are used to match named placeholders. If an array, string or number is supplied it's values are used to match any index placeholders.
	 * @param {...(string|number)} [argN] - Any number of additional strings or numbers to format the target with.
	 * @returns {string} The string formatted with the supplied arguments.
	 */
	wp.foogallery.format_l10n = function (target, arg1, argN){
		var args = Array.prototype.slice.call(arguments);
		target = args.shift(); // remove the target from the args
		if (!_.isString(target) || args.length === 0) return target;
		if (args.length === 1 && (_.isArray(args[0]) || _.isObject(args[0]))){
			args = args[0];
		}
		for (var arg in args){
			if (!args.hasOwnProperty(arg)) continue;
			target = target.replace(new RegExp("\\{" + arg + "\\}", "gi"), args[arg]);
		}
		return target;
	};

})( wp.media.view.l10n );
(function( l10n ){

	/**
	 * The basic structure of a supported device.
	 *
	 * @typedef {Object} CompatDevice
	 * @property {string} name - A friendly name for the device.
	 * @property {Object} browsers - An object containing the devices' supported browsers and there friendly names.
	 * @example
	 * device_slug: {
	 * 	name: "Friendly Name",
	 * 	browsers: {
	 * 		browser_slug: "Friendly Browser Name"
	 * 	}
	 * }
	 */

	/**
	 * The basic structure of a supported mime-type.
	 *
	 * @typedef {object} CompatType
	 * @property {string} name - A friendly name for the type.
	 * @property {Array<string>} aliases - An array containing all aliases for the type.
	 * @property {Object} browsers - An object containing the types' supported devices and its' compatibility with those browsers.
	 * @example
	 * type_slug: {
	 * 	name: "Friendly Name",
	 * 	aliases: ["type_slug","custom/name"],
	 * 	browsers: {
	 * 		device_slug: {
	 * 			browser_slug: true (Supported) | false (Not Supported) | "string" (Partial Support)
	 * 		}
	 * 	}
	 * }
	 */

	/**
	 * The structure of a result object.
	 *
	 * @typedef {Object} CompatResult
	 * @property {Object} 					desktop
	 * @property {string} 					desktop.title										- The friendly name for the device.
	 * @property {Object}						desktop.browsers
	 * @property {Object} 					desktop.browsers.ie
	 * @property {string} 					desktop.browsers.ie.title				- The friendly name for desktop Internet Explorer.
	 * @property {number} 					desktop.browsers.ie.value				- Whether or not desktop Internet Explorer is supported.
	 * @property {Object} 					desktop.browsers.edge
	 * @property {string} 					desktop.browsers.edge.title			- The friendly name for desktop Edge.
	 * @property {number} 					desktop.browsers.edge.value			- Whether or not desktop Edge is supported.
	 * @property {Object} 					desktop.browsers.chrome
	 * @property {string} 					desktop.browsers.chrome.title		- The friendly name for desktop Chrome.
	 * @property {number} 					desktop.browsers.chrome.value		- Whether or not desktop Chrome is supported.
	 * @property {Object} 					desktop.browsers.firefox
	 * @property {string} 					desktop.browsers.firefox.title	- The friendly name for desktop Firefox.
	 * @property {number} 					desktop.browsers.firefox.value	- Whether or not desktop Firefox is supported.
	 * @property {Object} 					desktop.browsers.opera
	 * @property {string} 					desktop.browsers.opera.title		- The friendly name for desktop Opera.
	 * @property {number} 					desktop.browsers.opera.value		- Whether or not desktop Opera is supported.
	 * @property {Object} 					desktop.browsers.safari
	 * @property {string} 					desktop.browsers.safari.title		- The friendly name for desktop Safari.
	 * @property {number} 					desktop.browsers.safari.value		- Whether or not desktop Safari is supported.
	 * @property {Object} 					mobile
	 * @property {string} 					mobile.title										- The friendly name for the device.
	 * @property {Object}						mobile.browsers
	 * @property {Object} 					mobile.browsers.uc
	 * @property {string} 					mobile.browsers.uc.title				- The friendly name for mobile UC Browser.
	 * @property {number} 					mobile.browsers.uc.value				- Whether or not mobile UC Browser is supported.
	 * @property {Object} 					mobile.browsers.samsung
	 * @property {string} 					mobile.browsers.samsung.title		- The friendly name for mobile Samsung Internet.
	 * @property {number} 					mobile.browsers.samsung.value		- Whether or not mobile Samsung Internet is supported.
	 * @property {Object} 					mobile.browsers.chrome
	 * @property {string} 					mobile.browsers.chrome.title		- The friendly name for mobile Chrome.
	 * @property {number} 					mobile.browsers.chrome.value		- Whether or not mobile Chrome is supported.
	 * @property {Object} 					mobile.browsers.firefox
	 * @property {string} 					mobile.browsers.firefox.title		- The friendly name for mobile Firefox.
	 * @property {number} 					mobile.browsers.firefox.value		- Whether or not mobile Firefox is supported.
	 * @property {Object} 					mobile.browsers.opera
	 * @property {string} 					mobile.browsers.opera.title			- The friendly name for mobile Opera.
	 * @property {number} 					mobile.browsers.opera.value			- Whether or not mobile Opera is supported.
	 * @property {Object} 					mobile.browsers.safari
	 * @property {string} 					mobile.browsers.safari.title		- The friendly name for mobile Safari.
	 * @property {number} 					mobile.browsers.safari.value		- Whether or not mobile Safari is supported.
	 */

	/**
	 * The configuration object used by the compatibility method.
	 *
	 * @type {object}
	 * @property {object} 					devices 															- An object containing the configuration for the various supported devices.
	 * @property {CompatDevice} 		devices.desktop
	 * @property {string} 					devices.desktop.name									- The friendly name for the device.
	 * @property {object}						devices.desktop.browsers
	 * @property {string} 					devices.desktop.browsers.ie						- The friendly name for desktop Internet Explorer.
	 * @property {string} 					devices.desktop.browsers.edge					- The friendly name for desktop Edge.
	 * @property {string} 					devices.desktop.browsers.chrome				- The friendly name for desktop Chrome.
	 * @property {string} 					devices.desktop.browsers.firefox			- The friendly name for desktop Firefox.
	 * @property {string} 					devices.desktop.browsers.opera				- The friendly name for desktop Opera.
	 * @property {string} 					devices.desktop.browsers.safari				- The friendly name for desktop Safari.
	 * @property {CompatDevice} 		devices.mobile
	 * @property {string} 					devices.mobile.name										- The friendly name for the device.
	 * @property {object}						devices.mobile.browsers
	 * @property {string} 					devices.mobile.browsers.uc						- The friendly name for mobile UC Browser.
	 * @property {string} 					devices.mobile.browsers.samsung				- The friendly name for mobile Samsung Internet.
	 * @property {string} 					devices.mobile.browsers.chrome				- The friendly name for mobile Chrome.
	 * @property {string} 					devices.mobile.browsers.firefox				- The friendly name for mobile Firefox.
	 * @property {string} 					devices.mobile.browsers.opera					- The friendly name for mobile Opera.
	 * @property {string} 					devices.mobile.browsers.safari				- The friendly name for mobile Safari.
	 * @property {object} 					types 																- An object containing the configuration for the various supported types.
	 * @property {CompatType} 			types.webm
	 * @property {string} 					types.webm.name												- The friendly name for the WebM type.
	 * @property {Array<string>} 		types.webm.aliases										- All aliases for the WebM type.
	 * @property {object}						types.webm.browsers
	 * @property {object}						types.webm.browsers.desktop
	 * @property {string} 					types.webm.browsers.desktop.ie				- Webm compatibility with desktop Internet Explorer.
	 * @property {string} 					types.webm.browsers.desktop.edge			- Webm compatibility with desktop Edge.
	 * @property {string} 					types.webm.browsers.desktop.chrome		- Webm compatibility with desktop Chrome.
	 * @property {string} 					types.webm.browsers.desktop.firefox		- Webm compatibility with desktop Firefox.
	 * @property {string} 					types.webm.browsers.desktop.opera			- Webm compatibility with desktop Opera.
	 * @property {string} 					types.webm.browsers.desktop.safari		- Webm compatibility with desktop Safari.
	 * @property {object}						types.webm.browsers.mobile
	 * @property {string} 					types.webm.browsers.mobile.uc					- Webm compatibility with mobile UC Browser.
	 * @property {string} 					types.webm.browsers.mobile.samsung		- Webm compatibility with mobile Samsung Internet.
	 * @property {string} 					types.webm.browsers.mobile.chrome			- Webm compatibility with mobile Chrome.
	 * @property {string} 					types.webm.browsers.mobile.firefox		- Webm compatibility with mobile Firefox.
	 * @property {string} 					types.webm.browsers.mobile.opera			- Webm compatibility with mobile Opera.
	 * @property {string} 					types.webm.browsers.mobile.safari			- Webm compatibility with mobile Safari.
	 * @property {CompatType} 			types.ogg
	 * @property {string} 					types.ogg.name												- The friendly name for the Ogg type.
	 * @property {Array<string>} 		types.ogg.aliases											- All aliases for the Ogg type.
	 * @property {object}						types.ogg.browsers
	 * @property {object}						types.ogg.browsers.desktop
	 * @property {string} 					types.ogg.browsers.desktop.ie					- Ogg compatibility with desktop Internet Explorer.
	 * @property {string} 					types.ogg.browsers.desktop.edge				- Ogg compatibility with desktop Edge.
	 * @property {string} 					types.ogg.browsers.desktop.chrome			- Ogg compatibility with desktop Chrome.
	 * @property {string} 					types.ogg.browsers.desktop.firefox		- Ogg compatibility with desktop Firefox.
	 * @property {string} 					types.ogg.browsers.desktop.opera			- Ogg compatibility with desktop Opera.
	 * @property {string} 					types.ogg.browsers.desktop.safari			- Ogg compatibility with desktop Safari.
	 * @property {object}						types.ogg.browsers.mobile
	 * @property {string} 					types.ogg.browsers.mobile.uc					- Ogg compatibility with mobile UC Browser.
	 * @property {string} 					types.ogg.browsers.mobile.samsung			- Ogg compatibility with mobile Samsung Internet.
	 * @property {string} 					types.ogg.browsers.mobile.chrome			- Ogg compatibility with mobile Chrome.
	 * @property {string} 					types.ogg.browsers.mobile.firefox			- Ogg compatibility with mobile Firefox.
	 * @property {string} 					types.ogg.browsers.mobile.opera				- Ogg compatibility with mobile Opera.
	 * @property {string} 					types.ogg.browsers.mobile.safari			- Ogg compatibility with mobile Safari.
	 * @property {CompatType} 			types.mp4
	 * @property {string} 					types.mp4.name												- The friendly name for the MP4 type.
	 * @property {Array<string>} 		types.mp4.aliases											- All aliases for the MP4 type.
	 * @property {object}						types.mp4.browsers
	 * @property {object}						types.mp4.browsers.desktop
	 * @property {string} 					types.mp4.browsers.desktop.ie					- MP4 compatibility with desktop Internet Explorer.
	 * @property {string} 					types.mp4.browsers.desktop.edge				- MP4 compatibility with desktop Edge.
	 * @property {string} 					types.mp4.browsers.desktop.chrome			- MP4 compatibility with desktop Chrome.
	 * @property {string} 					types.mp4.browsers.desktop.firefox		- MP4 compatibility with desktop Firefox.
	 * @property {string} 					types.mp4.browsers.desktop.opera			- MP4 compatibility with desktop Opera.
	 * @property {string} 					types.mp4.browsers.desktop.safari			- MP4 compatibility with desktop Safari.
	 * @property {object}						types.mp4.browsers.mobile
	 * @property {string} 					types.mp4.browsers.mobile.uc					- MP4 compatibility with mobile UC Browser.
	 * @property {string} 					types.mp4.browsers.mobile.samsung			- MP4 compatibility with mobile Samsung Internet.
	 * @property {string} 					types.mp4.browsers.mobile.chrome			- MP4 compatibility with mobile Chrome.
	 * @property {string} 					types.mp4.browsers.mobile.firefox			- MP4 compatibility with mobile Firefox.
	 * @property {string} 					types.mp4.browsers.mobile.opera				- MP4 compatibility with mobile Opera.
	 * @property {string} 					types.mp4.browsers.mobile.safari			- MP4 compatibility with mobile Safari.
	 */
	var config = {
		devices: {
			desktop: {
				name: "Desktop",
				browsers: {
					ie: "Internet Explorer 9-11",
					edge: "Edge",
					chrome: "Chrome",
					firefox: "Firefox",
					opera: "Opera",
					safari: "Safari"
				}
			},
			mobile: {
				name: "Mobile",
				browsers: {
					chrome: "Chrome for Android",
					firefox: "Firefox for Android",
					opera: "Opera Mobile",
					safari: "iOS Safari",
					uc: "UC Browser for Android",
					samsung: "Samsung Internet"
				}
			}
		},
		types: {
			webm: {
				name: "WebM",
				aliases: ["webm","video/webm"],
				browsers: {
					desktop: {ie: false, edge: l10n.compatibility.desktopEdgeWebM, firefox: true, chrome: true, opera: true, safari: false},
					mobile: {firefox: true, chrome: true, opera: l10n.mobileOperaWebM, safari: false, uc: true, samsung: true}
				}
			},
			ogg: {
				name: "Ogg",
				aliases: ["ogg","video/ogg","ogv","video/ogv"],
				browsers: {
					desktop: {ie: false, edge: l10n.compatibility.desktopEdgeOgg, firefox: true, chrome: true, opera: true, safari: false},
					mobile: {firefox: true, chrome: true, opera: false, safari: false, uc: true, samsung: true}
				}
			},
			mp4: {
				name: "MP4",
				aliases: ["mp4","video/mp4"],
				browsers: {
					desktop: {ie: true, edge: true, firefox: true, chrome: true, opera: true, safari: true},
					mobile: {firefox: l10n.compatibility.mobileFirefoxMP4, chrome: true, opera: true, safari: true, uc: true, samsung: true}
				}
			}
		}
	};

	/**
	 * A map of all aliases to there actual type.
	 * @type {Object}
	 * @example
	 * typeMap = {
	 * 	"webm": "webm",
	 * 	"video/webm": "webm"
	 * }
	 */
	var typeMap = (function(types){
		var obj = {};
		_.each(types, function(options, type){
			_.each(options.aliases, function(name){
				obj[name] = type;
			});
		});
		return obj;
	})(config.types);

	/**
	 * Takes the supplied array of types and returns an array of there configuration objects.
	 * @param {Array<string>} types - The types to retrieve the configuration for.
	 * @returns {Array<CompatType>}
	 */
	function compatibility_types(types){
		if (!_.isArray(types)) return [];
		var arr = _.map(types, function(alias){
			var type = typeMap[alias];
			return config.types.hasOwnProperty(type) ? config.types[type] : null;
		});
		return _.compact(arr);
	}

	/**
	 * Generates a new result object from the configuration.
	 * @returns {CompatResult}
	 */
	function compatibility_result() {
		var obj = {};
		_.each(config.devices, function(config, device){
			obj[device] = {
				title: config.name,
				browsers: {}
			};
			_.each(config.browsers, function(title, browser){
				obj[device].browsers[browser] = {
					title: title,
					value: -1
				};
			});
		});
		return obj;
	}

	/**
	 * Checks the supplied video mime-types and returns the browser compatibility.
	 * @param {Array<string>} types - The types to check compatibility for.
	 * @returns {CompatResult}
	 */
	wp.foogallery.compatibility = function(types){
		var result = compatibility_result();

		_.each(compatibility_types(types), function(type){
			_.each(type.browsers, function(browsers, device){
				_.each(result[device].browsers, function(current, browser){
					var separator = current.title.indexOf("\n\n") === -1 ? "\n\n" : "\n",
							value = browsers[browser];

					if (_.isString(value)){
						value = [type.name, value].join(" - ");
						current.title = [current.title, value].join(separator);
						current.value = current.value === 1 ? 1 : 0;
					} else if (value === true){
						value = [type.name, l10n.compatibility.supported].join(" - ");
						current.title = [current.title, value].join(separator);
						current.value = 1;
					}
				});
			});
		});

		return result;
	};

})( wp.foogallery.l10n );
// wp.foogallery.formJSON - method to parse a forms' inputs into a JSON object
(function($){

	/**
	 * Used to get the value of a single part of a multipart name.
	 *
	 * A multipart name is defined by using square brackets to define the property name or an index in an array.
	 *
	 * @param {object} obj - The object to retrieve the value from.
	 * @param {string} name - The name of the property to retrieve.
	 * @param {(string|number)} [next] - The next property name of the multipart name if one exists.
	 *
	 * @returns {(array|object)}
	 */
	function formJSON_get(obj, name, next) {
		if (name === "" && _.isArray(obj) && next){
			// handle arrays defined without an index i.e.: name[]="value"
			var l = obj.length, j = l - 1, tmp = {};
			// if this array already has items grab the last one and if the property does not exist on it or is an array then use it
			if (l > 0 && obj[j] && (_.isUndefined(obj[j][next]) || _.isArray(obj[j][next]))) tmp = obj[j];
			// otherwise push a new object into the array
			else obj.push(tmp);
			return tmp;
		}
		return obj[name] = obj[name] || (!isNaN(next) || next === "" ? [] : {});
	}

	/**
	 * Used to set the value of a object.
	 *
	 * If the obj is an array and the name is empty the value is simply pushed into its' current collection.
	 * If the property already exists and is not an array it will be converted to one containing all supplied values.
	 *
	 * @param {object} obj - The object to set the value on.
	 * @param {string} name - The name of the property to set.
	 * @param {*} value - The value of the property.
	 */
	function formJSON_set(obj, name, value){
		if (name === ""){
			// handle arrays defined without an index i.e.: name[]="value"
			if (_.isArray(obj)){
				obj.push(value);
			}
		} else if (!_.isUndefined(obj[name])) {
			// if the property already exists but is not an array then convert it to one so the original value is not lost
			if (!_.isArray(obj[name])) {
				obj[name] = [obj[name]];
			}
			// push the supplied value into the array
			obj[name].push(value);
		} else {
			// otherwise just straight up set the property
			obj[name] = value;
		}
	}

	/**
	 * Returns a JSON object generated from the names and values of all inputs.
	 *
	 * @param {jQuery} $container - The jQuery object that contains all the inputs.
	 * @param {string} [inputs=":input:not(:button)"] - The selector used to find all inputs.
	 *
	 * @returns {object}
	 */
	wp.foogallery.formJSON = function($container, inputs){
		inputs = _.isString(inputs) ? inputs : ":input:not(:button)";
		var json = {}, serialized = $container.find(inputs).serializeArray();
		_.each(serialized, function(pair) {
			var found = pair.name.indexOf('[');
			if (found > -1) {
				var tmp = json, parts = pair.name.replace(/]/gi, '').split('[');
				for (var i = 0, len = parts.length; i < len; i++) {
					var name = parts[i], next = parts[i + 1];
					if (i == len - 1) {
						formJSON_set(tmp, name, pair.value);
					} else {
						tmp = formJSON_get(tmp, name, next);
					}
				}
			} else {
				formJSON_set(json, pair.name, pair.value);
			}
		});
		return json;
	};

})(jQuery);
// wp.foogallery.media.View - create our own view base class so we can create/modify base utility methods
(function(){

	/**
	 * wp.foogallery.media.View
	 *
	 * The base view class for FooGallery media.
	 *
	 * @memberOf wp.foogallery.media
	 *
	 * @class
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var View = wp.media.View.extend(/** @lends wp.foogallery.media.View.prototype */{

		// region Overrides

		/**
		 * Initializes the view class extending the options with default values and binding events.
		 */
		initialize: function(){
			this.mediaFrame = this.options.mediaFrame ? this.options.mediaFrame : false;
			_.defaults( this.options, {
				region: "",
				mode: "default",
				persist: true
			});
			/**
			 * Whether or not the view is currently in an activated state.
			 *
			 * Activated views are rendered and have any UI events bound.
			 *
			 * @type {boolean}
			 * @default false
			 */
			this.activated = false;
			// listen just once for ready, for some reason the default workflow within wp.media can trigger ready multiple times.
			this.once("ready", this._ready, this);
		},
		/**
		 * Removes the view.el from the DOM and unbinds all events unless data.persist is set to true.
		 * @param {boolean} [force=false] If true the data.persist option is ignored.
		 * @returns {wp.foogallery.media.View} Returns itself for method chaining.
		 */
		remove: function (force) {
			// always call our internal _deactivate method to set the activated variable and unbind UI events
			this._deactivate();
			// if we should persist this view and we are not being forced to remove it then exit early
			if (this.options.persist && !force){
				return this;
			}
			// call the base remove method which unbinds all events, removes all subviews and removes the element from the DOM
			return wp.media.View.prototype.remove.apply( this, arguments );
		},

		// endregion

		// region Listeners

		/**
		 * Our internal ready callback guaranteed to only be called once per view initialization.
		 * @private
		 */
		_ready: function(){
			// first call our internal _activate method to set the activated variable and bind UI events
			this._activate();
			// next bind to the controller region events for this view so we can properly manage our UI event binding.
			this.controller.off(this.options.region + ":activate:" + this.options.mode)
					.off(this.options.region + ":deactivate:" + this.options.mode)
					.on(this.options.region + ":activate:" + this.options.mode, this._activate, this)
					.on(this.options.region + ":deactivate:" + this.options.mode, this._deactivate, this);
		},
		/**
		 * Internal method for activating the view.
		 * @private
		 */
		_activate: function(){
			// only do anything if the view is currently deactivated
			if (!this.activated){
				this.activated = true;
				this.activate();
			}
		},
		/**
		 * Internal method for deactivating the view.
		 * @private
		 */
		_deactivate: function(){
			// only do anything if the view is currently activated
			if (this.activated){
				this.activated = false;
				this.deactivate();
			}
		},

		// endregion

		// region Public Methods

		/**
		 * Called whenever the view is activated and is where any UI events should be bound.
		 */
		activate: function(){},
		/**
		 * Called whenever the view is deactivated and is where any UI events should be unbound.
		 */
		deactivate: function(){}

		// endregion

	});

	wp.foogallery.media.View = View;

})();
// wp.foogallery.media.DataView - create a data view class to help with management of views that display data
(function(View){

	/**
	 * wp.foogallery.media.DataView
	 *
	 * The base view class for FooGallery media that contains data.
	 *
	 * @memberOf wp.foogallery.media
	 *
	 * @class
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var DataView = View.extend(/** @lends wp.foogallery.media.DataView.prototype */{

		// region Overrides

		/**
		 * Initializes the view class extending the options with default values and binding events.
		 */
		initialize: function() {
			// first call the base class method
			View.prototype.initialize.apply( this, arguments );
			// then extend the options with defaults
			_.defaults( this.options, { data: {} });

			this.data = _.defaults(this.options.data, {
				// all data views require a timestamp on the data so we can check if they should be recreated or appended
				timestamp: new Date().getTime()
			});
		},
		/**
		 * Override the original prepare method as data views make use of the this.data value when rendering.
		 * @returns {object}
		 */
		prepare: function(){
			return this.data;
		},

		// endregion

		// region Public Methods

		/**
		 * Compares the given data to the current data and returns a value indicating whether the supplied data is older (-1), the same (0) or newer (1) than the current data.
		 *
		 * @param {Object} data - The data to compare.
		 * @returns {number} Returns -1 if the supplied data is invalid or older than the current data.
		 * Returns 0 if the supplied data is equal to the current data.
		 * Returns 1 if the supplied data is newer than the current data.
		 */
		compare: function(data){
			if (!_.isObject(data) || !_.isNumber(data.timestamp) || data.timestamp < this.data.timestamp){
				return -1;
			}
			if (data.timestamp > this.data.timestamp){
				return 1;
			}
			return 0;
		}

		// endregion

	});

	wp.foogallery.media.DataView = DataView;

})( wp.foogallery.media.View );
// wp.foogallery.media.Region - create our own region so we can add additional arguments to the 'activate', 'deactivate', 'create' and 'render' events
(function(){

	/**
	 * wp.foogallery.media.Region
	 *
	 * @see wp.media.controller.Region
	 *
	 * @memberOf wp.foogallery.media
	 *
	 * @class
	 *
	 * @augments wp.media.controller.Region
	 *
	 * @param {object}        options          Options hash for the region.
	 * @param {string}        options.id       Unique identifier for the region.
	 * @param {Backbone.View} options.view     A parent view the region exists within.
	 * @param {string}        options.selector jQuery selector for the region within the parent view.
	 */
	var Region = wp.media.controller.Region.extend(/** @lends wp.foogallery.media.Region.prototype */{

		// region Overrides

		/**
		 * Activate a mode.
		 *
		 * @param {string} [mode] - If provided sets the mode triggering various events, otherwise the current mode is simply returned.
		 * @param {object} [data] - When setting a mode the object will be supplied as an additional argument to various events.
		 *
		 * @fires wp.foogallery.media.Region#activate
		 * @fires wp.foogallery.media.Region#deactivate
		 *
		 * @returns {wp.foogallery.media.Region} Returns itself to allow chaining.
		 */
		mode: function( mode, data ) {
			if ( ! mode ) {
				return this._mode;
			}
			// Bail if we're trying to change to the current mode.
			// if ( mode === this._mode ) {
			// 	return this;
			// }

			data = _.isObject(data) ? data : {};

			/**
			 * Region mode deactivation event.
			 *
			 * @event wp.foogallery.media.Region#deactivate
			 */
			this.trigger('deactivate', mode, data);

			this._mode = mode;
			this.render( mode, data );

			/**
			 * Region mode activation event.
			 *
			 * @event wp.foogallery.media.Region#activate
			 */
			this.trigger('activate', mode, data);
			return this;
		},
		/**
		 * Render a mode.
		 *
		 * @param {string} [mode] - If provided renders the mode by triggering various events, otherwise renders the current mode.
		 * @param {object} [data] - If provided this object will be supplied as an additional argument to various events.
		 *
		 * @fires wp.foogallery.media.Region#create
		 * @fires wp.foogallery.media.Region#render
		 *
		 * @returns {wp.foogallery.media.Region} Returns itself to allow chaining
		 */
		render: function( mode, data ) {
			// If the mode isn't active, activate it.
			if ( mode && mode !== this._mode ) {
				return this.mode( mode, data );
			}

			data = _.isObject(data) ? data : {};

			var set = { view: null },
					view;

			/**
			 * Create region view event.
			 *
			 * Region view creation takes place in an event callback on the frame.
			 *
			 * @event wp.foogallery.media.Region#create
			 * @type {object}
			 * @property {object} view
			 */
			this.trigger( 'create', set, mode, data );
			view = set.view;

			/**
			 * Render region view event.
			 *
			 * Region view creation takes place in an event callback on the frame.
			 *
			 * @event wp.foogallery.media.Region#render
			 * @type {object}
			 */
			this.trigger( 'render', view, mode, data );
			if ( view ) {
				this.set( view );
			}
			return this;
		}

		// endregion

	});

	wp.foogallery.media.Region = Region;

})();
// wp.foogallery.media.Frame - create our own frame so we can specify the region selectors
(function(View, DataView, Region){

	/**
	 * wp.foogallery.media.Frame
	 *
	 * A frame is a composite view consisting of one or more views and a content region.
	 *
	 * @memberOf wp.foogallery.media
	 *
	 * @see wp.foogallery.media.Region
	 *
	 * @class
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Frame = View.extend(/** @lends wp.foogallery.media.Frame.prototype */{
		/**
		 * Specifies the regions this frame manages. Regions are supplied as properties of the object
		 * while the value specifies the selector used to find the region.
		 *
		 * @example
		 *
		 * regions: {
		 * 	region_1: ".region-1",
		 * 	region_2: ".region-2"
		 * }
		 */
		regions: {},

		// region Overrides

		/**
		 * Initializes the frame extending the options with default values, binding events and creating regions.
		 */
		initialize: function(){
			if (!this.options.mediaFrame && this.controller instanceof wp.foogallery.Select){
				this.options.mediaFrame = this.controller;
			}

			View.prototype.initialize.apply( this, arguments );

			_.defaults(this.options, {
				defaultRegion: "content",
				defaultMode: "default"
			});

			/**
			 * Object used to internally store the activated mode for each region.
			 * @private
			 */
			this._regions = {};
			/**
			 * Object used to internally store views for each region.
			 * @private
			 */
			this._views = {};
			this._createRegions();
		},

		// endregion

		// region Private Methods

		/**
		 * Creates the regions for the frame from the this.regions property which should be populated by child classes.
		 * @private
		 */
		_createRegions: function(){
			_.each(_.keys(this.regions), function(region){
				// for every region supplied create a new class using it's specifics
				this[region] = new Region({
					mediaFrame: this.mediaFrame,
					view:     this,
					id:       region,
					selector: this.regions[region]
				});
				// set the initial starting state of the region using the following order:
				// 1. Use the mode recorded in this._regions[region]
				// 2. Use the mode supplied in this.options[region]
				// 3. Use "default"
				this._regions[region] = this._regions[region] || this.options[region] || "default";
			}, this);
		},

		// endregion

		// region Public Methods

		/**
		 * Called whenever the frame is activated this loops through all child regions and ensures they are set to the
		 * correct mode and properly activated.
		 */
		activate: function(){
			_.each(_.keys(this.regions), function(region){
				var mode = this[region].mode();
				if (mode != this._regions[region]){
					// if the current mode is not what it should be then call .mode( value ) to kick off the following chain of events:
					// 1. 'deactivate' is triggered for the current mode
					// 2. 'create', 'render' and 'activate' are triggered for the new mode in that order
					this[region].mode(this._regions[region]);
				} else {
					// if the current mode is the same then simply trigger 'activate' for it.
					this.trigger(region + ":activate:" + this._regions[region]);
				}
			}, this);
			if (this.controller instanceof wp.media.view.Frame){
				this.controller.activateMode(this.options.mode);
			}
		},
		/**
		 * Called whenever the frame is deactivated this loops through all child regions and records there current mode
		 * and ensures they are properly deactivated.
		 */
		deactivate: function(){
			_.each(_.keys(this.regions), function(region){
				// record the current mode for use in the 'activate' method
				this._regions[region] = this[region].mode();
				// trigger 'deactivate' for the current mode.
				this.trigger(region + ":deactivate:" + this._regions[region]);
			}, this);
			if (this.controller instanceof wp.media.view.Frame){
				this.controller.deactivateMode(this.options.mode);
			}
		},
		getMode: function(region){
			var mode = null;
			if (this[region] instanceof Region){
				mode = this[region].mode();
				this._regions[region] = mode;
			}
			return mode;
		},
		setMode: function(region, mode, data){
			if (this[region] instanceof Region){
				this[region].mode(mode, data);
				this._regions[region] = mode;
			}
		},
		regionMode: function(region, mode, data){
			if (_.isString(region) && this[region] instanceof Region){
				if (_.isString(mode)){
					this[region].mode(mode, data);
					this._regions[region] = mode;
				} else {
					return this._regions[region] = this[region].mode();
				}
			}
		},
		getView: function(options, klass){
			_.defaults(options, {
				mediaFrame: this.mediaFrame,
				controller: this,
				region: this.options.defaultRegion,
				mode: this.options.defaultMode,
				data: {}
			});
			var name = options.region + "_", view = null;
			if (klass){
				name += options.mode;
				view = this._views[name];
				var exists = view instanceof View,
						type_diff = !(view instanceof klass),
						old_data = view instanceof DataView && view.compare(options.data) != 0;
				if (type_diff || old_data){
					if (exists) view.remove(true);
					this._views[name] = view = new klass(options);
				}
			} else if (this[options.region] instanceof Region) {
				name += this[options.region].mode();
				view = this._views.hasOwnProperty(name) ? this._views[name] : null;
			}
			return view;
		}

		// endregion

	});

	wp.foogallery.media.Frame = Frame;

})( wp.foogallery.media.View, wp.foogallery.media.DataView, wp.foogallery.media.Region );
// wp.foogallery.Select - create a select frame that is parent aware
(function(MediaFrame, $){

	/**
	 * wp.foogallery.media.Select
	 *
	 * A frame for selecting an item or items from the media library.
	 *
	 * @memberOf wp.foogallery.media
	 *
	 * @class
	 * @augments wp.media.view.MediaFrame.Select
	 * @augments wp.media.view.MediaFrame
	 * @augments wp.media.view.Frame
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Select = MediaFrame.Select.extend(/** @lends wp.foogallery.media.Select.prototype */{

		// region Overrides

		/**
		 * Initializes the custom Select frame extending the options with default values and binding internal events.
		 */
		initialize: function(){
			// call the _.defaults before the base initialize to provide our own defaults for base options
			_.defaults(this.options, {
				frame: "select",
				multiple: false,
				resetOnOpen: true, // if set to true the library selection is reset whenever the 'open' event is triggered
				content: "browse", // the default mode to set the content region to
				persistState: true // if set to false the modal will not store its' state
			});
			// initialize the original frame
			MediaFrame.Select.prototype.initialize.apply(this, arguments);
			// we bind handlers to the three events below to implement our custom options and provide the 'selected' event.
			this.on("select", this._select, this);
			this.on("open", this._open, this);
			this.on("close", this._close, this);
		},
		/**
		 * Create the default states on the frame.
		 *
		 * We override this so we can set the 'contentUserSetting' and 'syncSelection' options on the library to avoid
		 * conflicts with the original wp.media.view.MediaFrame.Select's state.
		 *
		 * @see wp.media.view.MediaFrame.Select#createStates
		 */
		createStates: function() {
			var options = this.options;

			if ( options.states ) {
				return;
			}

			// Add the default states.
			this.states.add([
				// Main states.
				new wp.media.controller.Library({
					library:   wp.media.query( options.library ),
					multiple:  options.multiple,
					title:     options.title,
					priority:  20,
					// setting these two values to false prevents the state of our custom modal
					// interfering with the original select modal state even though we are using it's library
					contentUserSetting: options.persistState,
					syncSelection: options.persistState
				})
			]);
		},

		// endregion

		// region Listeners

		/**
		 * Listens for the 'select' event to be raised and triggers the 'selected' event passing either all selected
		 * attachments or just a single one depending on the options.multiple value.
		 * @private
		 */
		_select: function(){
			var attachments = this.state().get('selection').toJSON();
			this.trigger("selected", !!this.options.multiple ? attachments : _.first(attachments));
		},
		/**
		 * Listens for the 'open' event to be raised and implements the options.resetOnOpen and options.content logic.
		 * If this frame is a child of another i.e. it has a controller set, then hide the original modal while this
		 * one is opened.
		 * @private
		 */
		_open: function(){
			var selection;
			if (this.options.resetOnOpen && (selection = this.state().get('selection'))) {
				selection.reset();
			}
			if (this.content.mode() == "upload"){
				this.content.mode(this.options.content);
			}
			if (this.controller){
				this.controller.$el.parents(".media-modal:first").parent().hide();
			}
		},
		/**
		 * Listens for the 'open' event to be raised and if this frame is a child of another i.e. it has a controller
		 * set, then show the original modal when then one is closed.
		 * @private
		 */
		_close: function(){
			if (this.controller){
				$("body").addClass("modal-open");
				this.controller.$el.parents(".media-modal:first").parent().show();
			}
		}

		// endregion

	});

	wp.foogallery.media.Select = Select;

})( wp.media.view.MediaFrame, jQuery );
// wp.foogallery.Select - create our select frame that also contains the importer frame
(function(l10n, MediaSelect){

	/**
	 * wp.foogallery.Select
	 *
	 * A frame for selecting an item or items from the media library which also contains any FooGallery customizations.
	 *
	 * @memberOf wp.foogallery
	 *
	 * @class
	 * @augments wp.foogallery.media.Select
	 * @augments wp.media.view.MediaFrame.Select
	 * @augments wp.media.view.MediaFrame
	 * @augments wp.media.view.Frame
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Select = MediaSelect.extend(/** @lends wp.foogallery.Select.prototype */{
		initialize: function(){
			_.defaults(this.options, {
				frame: "select-import",
				persistState: false
			});
			MediaSelect.prototype.initialize.apply(this, arguments);

			if (wp.foogallery.debug){
				this.on("all", function(event){
					console.log("Select			" + event, Array.prototype.slice.call(arguments));
				});
			}
			this.importer = new wp.foogallery.frames.Importer({
				controller: this
			});
			this.on("content:render:import", this.renderImport, this);
		},
		createSelectToolbar: function( toolbar, options ) {
			options = options || this.options.button || {};
			options.controller = this;

			toolbar.view = new Select.Toolbar( options );
		},
		/**
		 * Render callback for the router region in the `browse` mode.
		 *
		 * We override this so we can add in our custom tabs.
		 *
		 * @see wp.media.view.MediaFrame.Select#browseRouter
		 *
		 * @param {wp.media.view.Router} routerView
		 */
		browseRouter: function (routerView) {
			routerView.set({
				upload: {
					text: l10n.tabs.upload,
					priority: 20
				},
				browse: {
					text: l10n.tabs.browse,
					priority: 40
				},
				import: {
					text: l10n.tabs.import,
					priority: 50
				}
			});
		},
		/**
		 * Whenever the content region wants to render the importer supply it with the view.
		 */
		renderImport: function(){
			this.content.set( this.importer );
		},
		toggleMode: function(mode, state){
			if (!_.isUndefined(state) ? state : !this.isModeActive(mode)){
				this.activateMode(mode);
			} else {
				this.deactivateMode(mode);
			}
		}
	});

	wp.foogallery.Select = Select;

})( wp.foogallery.l10n, wp.foogallery.media.Select );
// wp.foogallery.Select.Toolbar - create a toolbar to add our custom buttons
(function(l10n, Select){

	/**
	 * wp.foogallery.Select.Toolbar
	 *
	 * @memberOf wp.foogallery.Select
	 *
	 * @class
	 * @augments wp.media.view.Toolbar.Select
	 * @augments wp.media.view.Toolbar
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Toolbar = Select.extend(/** @lends wp.foogallery.Select.Toolbar.prototype */{
		/**
		 *
		 */
		initialize: function() {
			var options = this.options;

			_.bindAll( this, 'clickImport', 'clickBack' );

			options.items = _.defaults( options.items || {}, {
				import: {
					style:    'primary',
					text:     l10n.toolbar.import,
					priority: 10,
					click:    this.clickImport,
					requires: {
						mode: "importable"
					}
				},
				back: {
					style:    'secondary',
					text:     l10n.toolbar.back,
					priority: 20,
					click:    this.clickBack,
					requires: {
						mode: "backable"
					}
				}
			});
			// Call 'initialize' directly on the parent class.
			Select.prototype.initialize.apply( this, arguments );
			this.bindModes();
		},

		refresh: function() {
			Select.prototype.refresh.apply( this, arguments );
			var controller = this.controller;
			_.each( this._views, function( button ) {
				if ( ! button.model || ! button.options || ! button.options.requires ) {
					return;
				}

				var requires = button.options.requires;
				if ( requires.mode ) {
					button.model.set( 'disabled', !controller.isModeActive(requires.mode) );
				}
			});
		},

		bindModes: function(){
			var modes = _.map(this.options.items, function(button){
				return button.requires && button.requires.mode ? button.requires.mode : null;
			});
			modes = _.uniq(_.compact(modes));
			if (modes.length){
				var events = _.map(modes, function(mode){
					return [mode+":activate",mode+":deactivate"];
				});
				events = _.flatten(events);
				this.controller.on(events.join(" "), this.refresh, this);
			}
		},

		clickImport: function() {
			this.controller.state().trigger( "toolbar:button:import" );
		},
		clickBack: function(){
			this.controller.state().trigger( "toolbar:button:back" );
		}
	});

	wp.foogallery.Select.Toolbar = Toolbar;

})( wp.foogallery.l10n, wp.media.view.Toolbar.Select );
// wp.foogallery.frames.Importer - create the importer frame which controls its' own regions
(function(l10n, Frame, $){

	var QUERY_ACTION = "fgi_query",
			IMPORT_ACTION = "fgi_import",
			SAVE_ACTION = "fgi_save",
			IMPORTER_NONCE = "fgi_nonce";

	/**
	 * wp.foogallery.frames.Importer
	 *
	 * The importer is a frame containing the toolbar, help and content regions.
	 *
	 * @memberOf wp.foogallery.frames
	 *
	 * @class
	 * @augments wp.foogallery.media.Frame
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Importer = Frame.extend({
		tagName: 'div',
		className: 'fgi',
		template: wp.template("fg-importer"),
		regions: {
			query: ".fgi-region-query",
			help: ".fgi-region-help",
			content: ".fgi-region-content"
		},

		initialize: function(){

			_.defaults( this.options, {
				mediaFrame: this.controller,
				region: "content",
				mode: "import",
				query: "default",
				help: "default",
				content: "getting-started",
				mimeTypes: ["video/mp4","video/webm","video/ogv"]
			});

			Frame.prototype.initialize.apply(this, arguments);

			this._request = null;
			this._history = [];
			this._first = false;
			this._content = {
				"error": Importer.Error,
				"getting-started": Importer.GettingStarted,
				"self-hosted": Importer.Query.SelfHosted,
				"oembed": Importer.Query.oEmbed,
				"json-result": Importer.Query.JSONResult,
				"search": Importer.Query.Result,
				"single": Importer.Query.Result,
				"embed": Importer.Query.Result,
				"vimeo": Importer.Query.Vimeo,
				"youtube": Importer.Query.YouTube,
				"album": Importer.Query.Album,
				"channel": Importer.Query.Channel,
				"playlistItems": Importer.Query.Playlist,
				"user": Importer.Query.User,
				"import": Importer.Import,
				"import-result": Importer.Import.Result
			};
			if (wp.foogallery.debug){
				this.on("all", function(event){
					console.log("Importer		" + event, Array.prototype.slice.call(arguments));
				});
			}
			this.mediaFrame.on("toolbar:button:back", this.back, this);

			this.on("query:create:default", this.createQuery, this);
			this.on("help:create:default", this.createHelp, this);

			this.on("content:create", this.createContent, this);
			this.on("content:render", this.renderContent, this);

		},
		activate: function(){
			if (this._first){
				this._first = false;
				this.mediaFrame.activateMode("help");
			}
			Frame.prototype.activate.apply(this, arguments);
		},
		back: function(){
			this._history.pop(); // remove the current entry
			var prev = this._history.pop(); // then get the previous entry
			this.regionMode("content", prev.mode, prev.data);
			var query;
			if (_.isObject(prev.data) && (query = this.getView({region: "query"}))){
				query.set(_.isString(prev.data.query) ? prev.data.query : "");
			}
		},
		createQuery: function(region){
			region.view = this.getView({region: "query"}, Importer.Query);
		},
		createHelp: function(region){
			region.view = this.getView({region: "help"}, Importer.Help);
		},
		createContent: function(region, mode, data){
			if (this._content[mode]){
				region.view = this.getView({mode: mode, data: data}, this._content[mode]);
			} else {
				region.view = this.getView({mode: mode, data: data}, this._content["json-result"]);
			}
		},
		renderContent: function(view, mode, data){
			this._history.push({mode: mode, data: data});
			if (data && data.reset === true){
				var current = this._history.pop();
				this._history.splice(0, this._history.length);
				this._history.push(current);
				var query = this.getView({region: "query"});
				if (!!query){
					query.set(_.isString(data.query) ? data.query : "");
				}
			}
			this.mediaFrame.toggleMode("backable", this._history.length > 1);
			this.mediaFrame.deactivateMode("importable");

			this.content.set( view );
		},

		contentMode: function(mode, data){
			return this.regionMode("content", mode, data);
		},

		ajax: function(options){
			var self = this;
			return $.Deferred(function(def){
				options = _.defaults(options || {}, { action: QUERY_ACTION });
				options[IMPORTER_NONCE] = self.$("." + IMPORTER_NONCE).val();

				if (self._request){
					self._request.abort();
				}

				self.trigger("request:start", options);
				self._request = $.ajax({
					type: "POST",
					url: ajaxurl,
					data: options,
					success: function(response){
						self.trigger("request:success request:end");
						if (response && response.success && response.data && response.data.mode){
							if (response.data.mode === "error"){
								def.reject(response.data);
							} else {
								response.data.timestamp = new Date().getTime();
								def.resolve(response.data);
							}
						} else {
							def.reject({"mode": "error", "message": l10n.errors.unexpectedResponse});
						}
					},
					error: function(xhr, textStatus, errorThrown){
						self.trigger("request:error request:end");
						if (textStatus === "abort"){
							def.reject({"mode": "abort", "message": l10n.errors.requestAborted});
						} else {
							def.reject({"mode": "error", "message": errorThrown});
						}
					}
				});
			}).promise();
		},
		ajax_query: function(options){
			return this.ajax(_.defaults(options || {}, { action: QUERY_ACTION }));
		},
		ajax_import: function(options){
			return this.ajax(_.defaults(options || {}, { action: IMPORT_ACTION }));
		},
		ajax_save: function(options){
			return this.ajax(_.defaults(options || {}, { action: SAVE_ACTION }));
		}
	});

	wp.foogallery.frames.Importer = Importer;

})( wp.foogallery.l10n, wp.foogallery.media.Frame, jQuery );
// wp.foogallery.frames.Importer.Error - the importer error view
(function(l10n, DataView){

	/**
	 * wp.foogallery.frames.Importer.Error
	 *
	 * The view class for the importers Error content.
	 *
	 * @memberOf wp.foogallery.frames.Importer
	 *
	 * @class
	 * @augments wp.foogallery.media.DataView
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Error = DataView.extend({
		tagName: 'div',
		className: 'fgi-error',
		template: wp.template("fgi-error"),
		namespace: ".fgi-error",
		sel: {
			ok: ".fgi-ok"
		},
		initialize: function(){
			DataView.prototype.initialize.apply(this, arguments);
			_.defaults(this.data, {
				title: l10n.errorTitle,
				message: l10n.errors.unknown
			});
		},
		activate: function(){
			this.$(this.sel.ok).on("click" + this.namespace, _.bind(this.onOkClick, this));
		},
		deactivate: function(){
			this.$(this.sel.ok).off(this.namespace);
		},
		onOkClick: function(event){
			event.preventDefault();
			this.controller.contentMode("getting-started", {reset: true});
		}
	});

	wp.foogallery.frames.Importer.Error = Error;

})( wp.foogallery.l10n, wp.foogallery.media.DataView );
// wp.foogallery.frames.Importer.Help - the importer help view
(function(View, $){

	/**
	 * wp.foogallery.frames.Importer.Help
	 *
	 * The view class for the importers Help content.
	 *
	 * @memberOf wp.foogallery.frames.Importer
	 *
	 * @class
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Help = View.extend({
		tagName: 'div',
		className: 'fgi-help',
		template: wp.template("fgi-help"),
		namespace: ".fgi-help",
		sel: {
			title: ".fgi-provider-title",
			content: ".fgi-provider-content"
		},
		activate: function(){
			this.$(this.sel.title).on("click" + this.namespace, _.bind(this.onProviderTitleClick, this));
		},
		deactivate: function(){
			this.$(this.sel.title).off(this.namespace);
		},
		onProviderTitleClick: function(event){
			event.preventDefault();
			$(event.target).next(this.sel.content).addBack().toggleClass("mode-expanded");
		}
	});

	wp.foogallery.frames.Importer.Help = Help;

})( wp.foogallery.media.View, jQuery );
// wp.foogallery.frames.Importer.GettingStarted - the importer getting started view
(function(l10n, View, $){

	/**
	 * wp.foogallery.frames.Importer.GettingStarted
	 *
	 * The view class for the importers Getting Started content.
	 *
	 * @memberOf wp.foogallery.frames.Importer
	 *
	 * @class
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var GettingStarted = View.extend({
		tagName: 'div',
		className: 'fgi-getting-started',
		template: wp.template("fgi-getting-started"),
		namespace: ".fgi-getting-started",
		sel: {
			help: '[href="#toggle-help"]',
			select: ".fgi-select"
		},
		initialize: function(){
			View.prototype.initialize.apply(this, arguments);
			this._select = null;
		},
		activate: function(){
			this.$(this.sel.help).on("click" + this.namespace, _.bind(this.onHelpClick, this));
			this.$(this.sel.select).on("click" + this.namespace, _.bind(this.onSelectClick, this));
		},
		deactivate: function(){
			this.$(this.sel.help).off(this.namespace);
			this.$(this.sel.select).off(this.namespace);
		},
		onHelpClick: function(event){
			event.preventDefault();
			this.mediaFrame.toggleMode("help");
		},
		onSelectClick: function(event){
			event.preventDefault();
			if (this._select instanceof wp.media.view.MediaFrame.Select){
				this._select.open();
				return;
			}
			var options = $(event.target).data("options");
			this._select = wp.foogallery({
				controller: this,
				title: options.title,
				button: {
					text: options.button
				},
				library: {
					type: options.type
				}
			}).on("selected", function(attachment){
				var data = this.attachmentToSelfHosted(attachment);
				this.controller.contentMode(data.mode, data);
			}, this).open();
		},
		attachmentToSelfHosted: function(attachment){
			var index;
			if (_.isObject(attachment) && _.isString(attachment.filename) && (index = attachment.filename.lastIndexOf('.')) !== -1){
				var data = {
					"mode": "self-hosted",
					"id": attachment.filename.substr(0, index) || "",
					"title": attachment.title || "",
					"description": attachment.description || "",
					"thumbnail": "",
					"urls": {
						"mp4": "",
						"ogg": "",
						"webm": ""
					}
				};
				if (attachment.subtype && data.urls[attachment.subtype] === ""){
					data.urls[attachment.subtype] = attachment.url || data.urls[attachment.subtype];
					return data; // only return the data if we successfully set a url
				}
			}
			return {"mode": "error", "message": l10n.errors.invalidAttachment};
		}
	});

	wp.foogallery.frames.Importer.GettingStarted = GettingStarted;

})( wp.foogallery.l10n, wp.foogallery.media.View, jQuery );
// wp.foogallery.frames.Importer.Query - the query view for the importer
(function(View, $){

	/**
	 * wp.foogallery.frames.Importer.Query
	 *
	 * The view class for the importers query.
	 *
	 * @memberOf wp.foogallery.frames.Importer
	 *
	 * @class
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Query = View.extend(/** @lends wp.foogallery.frames.Importer.Query.prototype */{
		tagName: 'div',
		className: 'fgi-query',
		template: wp.template("fgi-query"),
		namespace: ".fgi-query",
		sel: {
			input: ".fgi-query-input",
			help: '[href="#toggle-help"]'
		},
		initialize: function(){
			View.prototype.initialize.apply( this, arguments );
			this.controller.on("request:start", this.requestStart, this);
			this.controller.on("request:end", this.requestEnd, this);
		},
		activate: function(){
			this.$(this.sel.input).on("keypress" + this.namespace + " paste" + this.namespace, _.debounce(_.bind(this.onInputChange, this), 500))
					.on("keydown" + this.namespace, _.debounce(_.bind(this.onInputKeydown, this), 500)).focus();
			this.$(this.sel.help).on("click" + this.namespace, _.bind(this.onHelpClick, this));
		},
		deactivate: function(){
			this.$(this.sel.input).off(this.namespace);
			this.$(this.sel.help).off(this.namespace);
		},
		set: function(value){
			this.$(this.sel.input).val(value);
		},
		get: function(){
			return this.$(this.sel.input).val();
		},
		query: function (value) {
			value = _.isString(value) ? value : this.get();
			var controller = this.controller;
			return controller.ajax_query({
				query: value,
				page: 1,
				offset: 0
			}).then(function(data){
				controller.contentMode(data.mode, data);
			}, function (err) {
				if (err && err.mode === "abort") return;
				controller.contentMode(err.mode, err);
			});
		},
		requestStart: function(){
			this.$el.addClass("mode-requesting");
		},
		requestEnd: function(){
			this.$el.removeClass("mode-requesting");
		},
		onInputChange: function(event){
			this.handleChange(event.target);
		},
		onInputKeydown: function(event){
			var special = [8]; // backspace
			if (_.contains(special, event.which)){
				this.handleChange(event.target);
			}
		},
		onHelpClick: function(event){
			event.preventDefault();
			this.mediaFrame.toggleMode("help");
		},
		handleChange: function(input){
			var $input = $(input), val = $input.val();
			if (val !== null && typeof val === "string" && val.length >= 3){
				this.query(val);
			}
		}
	});

	wp.foogallery.frames.Importer.Query = Query;

})( wp.foogallery.media.View, jQuery );
// the self-hosted view
(function(l10n, DataView, $){

	var SelfHosted = DataView.extend({
		tagName: "div",
		className: "fgi-self-hosted",
		template: wp.template("fgi-self-hosted"),
		template_compatibility: wp.template("fgi-compatibility"),
		namespace: ".fgi-self-hosted",
		sel: {
			form: ".fgi-form",
			row: ".fgi-row",
			col_input: ".fgi-col-input",
			browse: ".fgi-browse",
			browse_button: ".fgi-browse-col-button button",
			browse_input: ".fgi-browse-col-input input",
			compatibility: ".fgi-video-compatibility",
			validate: 'input[name="thumbnail"],input[name^="urls"]'
		},
		initialize: function(){
			DataView.prototype.initialize.apply( this, arguments );
			_.defaults(this.data, {
				mode: "",
				id: "",
				thumbnail: "",
				title: "",
				description: "",
				urls: {
					mp4: "",
					ogg: "",
					webm: ""
				},
				types: {
					mp4: { pattern: "mp4", text: ".mp4", mime: "video/mp4" },
					ogg: { pattern: "ogv|ogg", text: ".ogv or .ogg", mime: "video/ogv,video/ogg" },
					webm: { pattern: "webm", text: ".webm", mime: "video/webm" }
				}
			});
			this._select = {};
		},
		render: function(){
			var result = DataView.prototype.render.apply( this, arguments );
			this.compatibility();
			return result;
		},
		activate: function(){
			this.mediaFrame.on("toolbar:button:import", this.import, this);
			this.$(this.sel.browse_button).on("click" + this.namespace, _.bind(this.onBrowseClick, this));
			this.$(this.sel.validate)
					.on("keypress" + this.namespace + " paste" + this.namespace + " change" + this.namespace, _.debounce(_.bind(this.onValidateChange, this), 500))
					.on("keydown" + this.namespace, _.debounce(_.bind(this.onValidateKeydown, this), 500));
		},
		deactivate: function(){
			this.mediaFrame.off("toolbar:button:import", this.import, this);
			this.$(this.sel.browse_button).off(this.namespace);
			this.$(this.sel.validate).off(this.namespace);
		},
		compatibility: function(){
			if (this.template_compatibility){
				var types = this.$('input[name^="urls["]').map(function(){
					var $input = $(this), value = $input.val(), type = $input.data("type");
					return _.isString(value) && value != "" ? type : null;
				}).get();
				var compat = wp.foogallery.compatibility(types);
				this.$(this.sel.compatibility).html(this.template_compatibility(compat));
			}
		},
		onBrowseClick: function(event){
			event.preventDefault();
			var self = this,
					$button = $(event.target),
					options = $button.data("options");

			if (this._select[options.type] instanceof wp.media.view.MediaFrame.Select){
				this._select[options.type].open();
				return;
			}
			this._select[options.type] = wp.foogallery({
				frame: "select-child",
				mode: "browse",
				controller: this,
				title: options.title,
				button: {
					text: options.button
				},
				library: {
					type: options.type
				}
			}).on("selected", function(attachment){
				var $input = $button.parents(self.sel.browse).find(self.sel.browse_input).val(attachment.url);
				self.validateChange($input);
			}).open();
		},
		onValidateChange: function (event) {
			this.validateChange(event.target);
		},
		onValidateKeydown: function (event) {
			var special = [8]; // backspace
			if (_.contains(special, event.which)){
				this.validateChange(event.target);
			}
		},
		validateChange: function(input){
			var $input = $(input), valid = this.validateInput($input, true);
			if (valid && $input.is('[name^="urls["]')){
				this.compatibility();
			}
			if (valid){
				valid = this.validateForm();
			}
			this.mediaFrame.toggleMode("importable", valid);
			return valid;
		},
		validateForm: function(outputErrors){
			var $inputs = this.$(this.sel.validate), valid = true;
			for (var i = 0, l = $inputs.length; i < l; i++){
				if (!this.validateInput($inputs.eq(i), outputErrors)){
					valid = false;
				}
			}
			return valid;
		},
		validateInput: function(input, outputErrors){
			var $input = $(input), valid = true, errors;
			if ((errors = this._validate($input)).length > 0){
				valid = false;
				if (outputErrors == true){
					var $errors = $.map(errors, function(message){
						return $("<p/>", {"class": "fgi-input-error", text: message});
					});
					$input.parents(this.sel.row).addClass("mode-error");
					$input.parents([this.sel.browse,this.sel.col_input].join(",")).first()
							.find(".fgi-input-error").remove().end()
							.append($errors);
				}
			} else {
				$input.parents(this.sel.row).removeClass("mode-error");
				$input.parents([this.sel.browse,this.sel.col_input].join(",")).first()
						.find(".fgi-input-error").remove();
			}
			return valid;
		},
		_validate: function(input){
			var $input = $(input),
					value = $input.val(),
					required = $input.data("required"),
					populated = _.isString(value) && value !== "",
					pattern = this._regex($input.data("pattern")),
					messages = $input.data("messages") || {},
					errors = [];

			if (required && !populated){
				var $required;
				if (_.isString(required) && ($required = this.$(required).not($input)).length > 0){
					var valid = false;
					for (var i = 0, l = $required.length, val; i < l; i++){
						val = $required.eq(i).val();
						if (_.isString(val) && val !== ""){
							valid = true;
						}
					}
					if (!valid) errors.push(_.isString(messages.required) ? messages.required : l10n.errors.required);
				} else {
					errors.push(_.isString(messages.required) ? messages.required : l10n.errors.required);
				}
			}
			if (pattern && populated && !pattern.test(value)){
				errors.push(_.isString(messages.pattern) ? messages.pattern : l10n.errors.pattern);
			}
			return errors;
		},
		_regex: function(pattern){
			if (!_.isString(pattern) || pattern === "") return null;
			try {
				return new RegExp(pattern, "i");
			} catch(err) {
				return null;
			}
		},
		import: function(){
			if (this.validateForm(true)){
				var video = wp.foogallery.formJSON(this.$(this.sel.form));
				this.controller.contentMode("import", {"mode": "self-hosted", "total": 1, "videos": [video]});
			}
		}
	});

	wp.foogallery.frames.Importer.Query.SelfHosted = SelfHosted;

})( wp.foogallery.l10n, wp.foogallery.media.DataView, jQuery );
// wp.foogallery.frames.Importer.Query.JSONResult - a basic result view that simply dumps the data as JSON
(function(DataView, $){

	/**
	 * wp.foogallery.frames.Importer.Query.JSONResult
	 *
	 * The default view class for any successful server response with JSON data.
	 *
	 * @memberOf wp.foogallery.frames.Importer.Query
	 *
	 * @class
	 * @augments wp.foogallery.media.DataView
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var JSONResult = DataView.extend({
		tagName: "div",
		className: "fgi-json-result",
		/**
		 * Renders the current view using the options.data object and dumping its' JSON formatted value into a <pre/> element.
		 *
		 * Overriding this method is required as we do not supply a template for this base view and manually create the markup.
		 *
		 * If a template is supplied in a child class then the original wp.media.View#render method is called which handles templates.
		 *
		 * @returns {wp.foogallery.frames.Importer.Query.JSONResult} Returns itself for method chaining.
		 */
		render: function(){
			// if a template is supplied in a child class then simply call the original render which handles templates
			if ( this.template ){
				return DataView.prototype.render.apply(this, arguments);
			}
			// otherwise we simply dump the prepared data wrapped in a pre into the main element
			var data = this.prepare();
			this.views.detach();
			this.$el.html( $("<pre/>", {text: JSON.stringify(data, null, 2)}) );
			this.views.render();
			return this;
		}
	});

	wp.foogallery.frames.Importer.Query.JSONResult = JSONResult;

})( wp.foogallery.media.DataView, jQuery );
// wp.foogallery.frames.Importer.Query.Result - the base result view
(function(DataView, $){

	/**
	 * wp.foogallery.frames.Importer.Query.Result
	 *
	 * The view class for the importers Results content.
	 *
	 * @memberOf wp.foogallery.frames.Importer.Query
	 *
	 * @class
	 * @augments wp.foogallery.media.DataView
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Result = DataView.extend({
		tagName: 'div',
		className: 'fgi-query-result',
		template: wp.template("fgi-query-result"),
		template_notification: wp.template("fgi-query-result-notification"),
		template_items: wp.template("fgi-query-result-items"),
		template_status: wp.template("fgi-query-result-status"),
		namespace: ".fgi-query-result",
		sel: {
			notification: ".fgi-query-result-notification",
			list: ".fgi-query-result-list",
			status: ".fgi-query-result-status",
			check: ".fgi-query-result-video-check",
			thumbnail: ".fgi-query-result-video-thumbnail",
			paged: ".fgi-query-result-paged",
			loadMore: '[href="#load-more"]',
			tryAgain: '[href="#try-again"]',
			offset: ".fgi-query-result-offset",
			total: ".fgi-query-result-total"
		},
		initialize: function(){
			DataView.prototype.initialize.apply( this, arguments );
			_.defaults(this.data, {
				total: 1,
				page: 1,
				nextPage: 0,
				offset: 0,
				videos: []
			});
		},
		render: function(){
			var options;

			if ( this.prepare )
				options = this.prepare();

			this.views.detach();

			if ( this.template ) {
				options = options || {};
				this.trigger( 'prepare', options );
				this.$el.html( this.template( options ) ).toggleClass("mode-single", this.data.total <= 1);
				if (this.template_notification){
					this.$(this.sel.notification).html(this.template_notification(options));
				}
				if (this.template_items){
					this.$(this.sel.list).html(this.template_items(options));
				}
				if (this.template_status){
					this.$(this.sel.status).html(this.template_status(options));
				}
			}

			this.views.render();
			return this;
		},
		activate: function(){
			this.mediaFrame.toggleMode("importable", this.hasSelection());
			this.mediaFrame.on("toolbar:button:import", this.import, this);
			this.$(this.sel.list).on("click" + this.namespace, this.sel.check, _.bind(this.onCheckClick, this))
					.on("click" + this.namespace, this.sel.thumbnail, _.bind(this.onThumbnailClick, this));
			this.$el.on("click" + this.namespace, this.sel.loadMore, _.bind(this.onLoadMoreClick, this));
			this.$el.on("click" + this.namespace, this.sel.tryAgain, _.bind(this.onLoadMoreClick, this));
		},
		deactivate: function(){
			this.mediaFrame.off("toolbar:button:import", this.onImportClick, this);
			this.$(this.sel.list).off(this.namespace);
			this.$el.off(this.namespace);
		},
		onThumbnailClick: function(event){
			event.preventDefault();
			this.$(this.sel.list).children(".mode-current").removeClass("mode-current");
			$(event.target).parents("li:first").addClass("mode-selected mode-current");
			this.mediaFrame.toggleMode("importable", this.hasSelection());
		},
		onCheckClick: function(event){
			event.preventDefault();
			event.stopPropagation();
			$(event.target).parents("li:first").removeClass("mode-selected mode-current");
			this.mediaFrame.toggleMode("importable", this.hasSelection());
		},
		hasSelection: function(){
			return this.$(this.sel.list).children(".mode-selected").length > 0;
		},
		selection: function(){
			var self = this;
			return this.$(this.sel.list).children(".mode-selected").map(function(i, li){
				return _.find(self.data.videos, function(video){
					return video.id === $(li).attr("data-id");
				});
			}).get();
		},
		loadMore: function(){
			var data = this.data;
			var self = this, $status = self.$(self.sel.status).removeClass("mode-error").addClass("mode-loading");
			return this.controller.ajax_query({
				query: data.query,
				page: data.nextPage,
				offset: data.offset
			}).then(function(data){
				// first generate the new item HTML using just the returned videos
				var html = self.template_items(data);
				// then update the UI
				self.$(self.sel.list).append(html);

				// then concat the new and old videos
				data.videos = self.data.videos.concat(data.videos);
				self.data = self.options.data = data;

				html = self.template_status(data);
				$status.removeClass("mode-loading mode-error").html(html);
			}, function(){
				$status.removeClass("mode-loading").addClass("mode-error");
			});
		},
		onLoadMoreClick: function(event){
			event.preventDefault();
			this.loadMore();
		},
		import: function(){
			var selection = this.selection();
			this.controller.contentMode("import", {"mode": "selection", "total": selection.length, "videos": selection});
		}
	});

	wp.foogallery.frames.Importer.Query.Result = Result;

})( wp.foogallery.media.DataView, jQuery );
// wp.foogallery.frames.Importer.Query.Album - create the album result which has a custom notification allowing the user to import the entire album
(function(Result){

	/**
	 * wp.foogallery.frames.Importer.Query.Album
	 *
	 * The view class for the importers Album content.
	 *
	 * @memberOf wp.foogallery.frames.Importer.Query
	 *
	 * @class
	 * @augments wp.foogallery.frames.Importer.Query.Result
	 * @augments wp.foogallery.media.DataView
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Album = Result.extend({
		template_notification: wp.template("fgi-album-notification"),
		namespace: ".fgi-album",
		activate: function(){
			this.$('[href="#import-album"]').on("click" + this.namespace, _.bind(this.onImportAlbumClick, this));
			Result.prototype.activate.apply(this, arguments);
		},
		deactivate: function(){
			this.$('[href="#import-album"]').off(this.namespace);
			Result.prototype.deactivate.apply(this, arguments);
		},
		onImportAlbumClick: function(event){
			event.preventDefault();
			this.controller.contentMode("import", this.data);
		}
	});

	wp.foogallery.frames.Importer.Query.Album = Album;

})( wp.foogallery.frames.Importer.Query.Result );
// wp.foogallery.frames.Importer.Query.Channel - create the channel result which has a custom notification allowing the user to import the entire channel
(function(Result){

	/**
	 * wp.foogallery.frames.Importer.Query.Channel
	 *
	 * The view class for the importers Channel content.
	 *
	 * @memberOf wp.foogallery.frames.Importer.Query
	 *
	 * @class
	 * @augments wp.foogallery.frames.Importer.Query.Result
	 * @augments wp.foogallery.media.DataView
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Channel = Result.extend({
		template_notification: wp.template("fgi-channel-notification"),
		namespace: ".fgi-channel",
		activate: function(){
			this.$('[href="#import-channel"]').on("click" + this.namespace, _.bind(this.onImportChannelClick, this));
			Result.prototype.activate.apply(this, arguments);
		},
		deactivate: function(){
			this.$('[href="#import-channel"]').off(this.namespace);
			Result.prototype.deactivate.apply(this, arguments);
		},
		onImportChannelClick: function(event){
			event.preventDefault();
			this.controller.contentMode("import", this.data);
		}
	});

	wp.foogallery.frames.Importer.Query.Channel = Channel;

})( wp.foogallery.frames.Importer.Query.Result );
// wp.foogallery.frames.Importer.Query.Playlist - create the playlist result which has a custom notification allowing the user to import the entire playlist
(function(Result){

	/**
	 * wp.foogallery.frames.Importer.Query.Playlist
	 *
	 * The view class for the importers Playlist content.
	 *
	 * @memberOf wp.foogallery.frames.Importer.Query
	 *
	 * @class
	 * @augments wp.foogallery.frames.Importer.Query.Result
	 * @augments wp.foogallery.media.DataView
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var Playlist = Result.extend({
		template_notification: wp.template("fgi-playlist-notification"),
		namespace: ".fgi-playlist",
		activate: function(){
			this.$('[href="#import-playlist"]').on("click" + this.namespace, _.bind(this.onImportPlaylistClick, this));
			Result.prototype.activate.apply(this, arguments);
		},
		deactivate: function(){
			this.$('[href="#import-playlist"]').off(this.namespace);
			Result.prototype.deactivate.apply(this, arguments);
		},
		onImportPlaylistClick: function(event){
			event.preventDefault();
			this.controller.contentMode("import", this.data);
		}
	});

	wp.foogallery.frames.Importer.Query.Playlist = Playlist;

})( wp.foogallery.frames.Importer.Query.Result );
// wp.foogallery.frames.Importer.Query.User - create the user result which has a custom notification allowing all user videos to be imported
(function(Result){

	/**
	 * wp.foogallery.frames.Importer.Query.User
	 *
	 * The view class for the importers User content.
	 *
	 * @memberOf wp.foogallery.frames.Importer.Query
	 *
	 * @class
	 * @augments wp.foogallery.frames.Importer.Query.Result
	 * @augments wp.foogallery.media.DataView
	 * @augments wp.foogallery.media.View
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	var User = Result.extend({
		template_notification: wp.template("fgi-user-notification"),
		namespace: ".fgi-user",
		activate: function(){
			this.$('[href="#import-user"]').on("click" + this.namespace, _.bind(this.onImportUserClick, this));
			Result.prototype.activate.apply(this, arguments);
		},
		deactivate: function(){
			this.$('[href="#import-user"]').off(this.namespace);
			Result.prototype.deactivate.apply(this, arguments);
		},
		onImportUserClick: function(event){
			event.preventDefault();
			this.controller.contentMode("import", this.data);
		}
	});

	wp.foogallery.frames.Importer.Query.User = User;

})( wp.foogallery.frames.Importer.Query.Result );
// the oEmbed details view
(function(l10n, DataView, $){

	var oEmbed = DataView.extend({
		tagName: "div",
		className: "fgi-oembed",
		template: wp.template("fgi-oembed"),
		namespace: ".fgi-oembed",
		sel: {
			form: ".fgi-form",
			row: ".fgi-row",
			col_input: ".fgi-col-input",
			browse: ".fgi-browse",
			browse_button: ".fgi-browse-col-button button",
			browse_input: ".fgi-browse-col-input input",
			compatibility: ".fgi-video-compatibility",
			validate: 'input[name="thumbnail"]'
		},
		initialize: function(){
			DataView.prototype.initialize.apply( this, arguments );
			_.defaults(this.data, {
				mode: "",
				id: "",
				provider: "",
				thumbnail: "",
				title: "",
				description: ""
			});
			this._select = {};
		},
		activate: function(){
			this.mediaFrame.on("toolbar:button:import", this.import, this);
			this.$(this.sel.browse_button).on("click" + this.namespace, _.bind(this.onBrowseClick, this));
			this.$(this.sel.validate)
					.on("keypress" + this.namespace + " paste" + this.namespace + " change" + this.namespace, _.debounce(_.bind(this.onValidateChange, this), 500))
					.on("keydown" + this.namespace, _.debounce(_.bind(this.onValidateKeydown, this), 500));
		},
		deactivate: function(){
			this.mediaFrame.off("toolbar:button:import", this.import, this);
			this.$(this.sel.browse_button).off(this.namespace);
			this.$(this.sel.validate).off(this.namespace);
		},
		onBrowseClick: function(event){
			event.preventDefault();
			var self = this,
					$button = $(event.target),
					options = $button.data("options");

			if (this._select[options.type] instanceof wp.media.view.MediaFrame.Select){
				this._select[options.type].open();
				return;
			}
			this._select[options.type] = wp.foogallery({
				frame: "select-child",
				mode: "browse",
				controller: this,
				title: options.title,
				button: {
					text: options.button
				},
				library: {
					type: options.type
				}
			}).on("selected", function(attachment){
				var $input = $button.parents(self.sel.browse).find(self.sel.browse_input).val(attachment.url);
				self.validateChange($input);
			}).open();
		},
		onValidateChange: function (event) {
			this.validateChange(event.target);
		},
		onValidateKeydown: function (event) {
			var special = [8]; // backspace
			if (_.contains(special, event.which)){
				this.validateChange(event.target);
			}
		},
		validateChange: function(input){
			var $input = $(input), valid = this.validateInput($input, true);
			if (valid){
				valid = this.validateForm();
			}
			this.mediaFrame.toggleMode("importable", valid);
			return valid;
		},
		validateForm: function(outputErrors){
			var $inputs = this.$(this.sel.validate), valid = true;
			for (var i = 0, l = $inputs.length; i < l; i++){
				if (!this.validateInput($inputs.eq(i), outputErrors)){
					valid = false;
				}
			}
			return valid;
		},
		validateInput: function(input, outputErrors){
			var $input = $(input), valid = true, errors;
			if ((errors = this._validate($input)).length > 0){
				valid = false;
				if (outputErrors == true){
					var $errors = $.map(errors, function(message){
						return $("<p/>", {"class": "fgi-input-error", text: message});
					});
					$input.parents(this.sel.row).addClass("mode-error");
					$input.parents([this.sel.browse,this.sel.col_input].join(",")).first()
							.find(".fgi-input-error").remove().end()
							.append($errors);
				}
			} else {
				$input.parents(this.sel.row).removeClass("mode-error");
				$input.parents([this.sel.browse,this.sel.col_input].join(",")).first()
						.find(".fgi-input-error").remove();
			}
			return valid;
		},
		_validate: function(input){
			var $input = $(input),
					value = $input.val(),
					required = $input.data("required"),
					populated = _.isString(value) && value !== "",
					pattern = this._regex($input.data("pattern")),
					messages = $input.data("messages") || {},
					errors = [];

			if (required && !populated){
				var $required;
				if (_.isString(required) && ($required = this.$(required).not($input)).length > 0){
					var valid = false;
					for (var i = 0, l = $required.length, val; i < l; i++){
						val = $required.eq(i).val();
						if (_.isString(val) && val !== ""){
							valid = true;
						}
					}
					if (!valid) errors.push(_.isString(messages.required) ? messages.required : l10n.errors.required);
				} else {
					errors.push(_.isString(messages.required) ? messages.required : l10n.errors.required);
				}
			}
			if (pattern && populated && !pattern.test(value)){
				errors.push(_.isString(messages.pattern) ? messages.pattern : l10n.errors.pattern);
			}
			return errors;
		},
		_regex: function(pattern){
			if (!_.isString(pattern) || pattern === "") return null;
			try {
				return new RegExp(pattern, "i");
			} catch(err) {
				return null;
			}
		},
		import: function(){
			if (this.validateForm(true)){
				var video = wp.foogallery.formJSON(this.$(this.sel.form));
				this.controller.contentMode("import", {"mode": "embed", "total": 1, "videos": [video]});
			}
		}
	});

	wp.foogallery.frames.Importer.Query.oEmbed = oEmbed;

})( wp.foogallery.l10n, wp.foogallery.media.DataView, jQuery );
// the Vimeo access token view
(function(l10n, DataView, $){

	var Vimeo = DataView.extend({
		tagName: "div",
		className: "fgi-vimeo",
		template: wp.template("fgi-vimeo"),
		namespace: ".fgi-vimeo",
		sel: {
			access_token: "input[name='access_token']",
			save: ".fgi-save",
			help: '[href="#toggle-help"]',
			container: ".button-hero-container",
			error: '.fgi-vimeo-error-message'
		},
		cls: {
			error: "fgi-vimeo-error"
		},
		initialize: function(){
			DataView.prototype.initialize.apply( this, arguments );
			_.defaults(this.data, {
				mode: "",
				access_token: "",
				query: ""
			});
		},
		activate: function(){
			this.$(this.sel.help).on("click" + this.namespace, _.bind(this.onHelpClick, this));
			this.$(this.sel.save).on("click" + this.namespace, _.bind(this.onSaveClick, this));
			this.$(this.sel.access_token)
					.on("keypress" + this.namespace + " paste" + this.namespace + " change" + this.namespace, _.debounce(_.bind(this.onValidateChange, this), 500))
					.on("keydown" + this.namespace, _.debounce(_.bind(this.onValidateKeydown, this), 500));
		},
		deactivate: function(){
			this.$(this.sel.save).off(this.namespace);
			this.$(this.sel.access_token).off(this.namespace);
		},
		onValidateChange: function (event) {
			this.validateChange(event.target);
		},
		onValidateKeydown: function (event) {
			var special = [8]; // backspace
			if (_.contains(special, event.which)){
				this.validateChange(event.target);
			}
		},
		validateChange: function(input){
			var $input = $(input), value = $input.val(), valid = _.isString(value) && value.length >= 30;
			this.$(this.sel.save).attr('disabled', !valid);
			this.$(this.sel.container).removeClass(this.cls.error);
			this.$(this.sel.error).empty();
			return valid;
		},
		onHelpClick: function(event){
			event.preventDefault();
			this.mediaFrame.toggleMode("help");
		},
		onSaveClick: function(event){
			event.preventDefault();
			this.save();
		},
		save: function(){
			var value = this.$(this.sel.access_token).val();
			if (_.isString(value) && value.length >= 30){
				var self = this, controller = this.controller;
				self.$(self.sel.save).attr('disabled', true);
				return controller.ajax_save({
					access_token: value,
					action: "fgi_save_vimeo_access_token"
				}).then(function(){
					var q = controller.getView({region: "query"});
					if (q instanceof wp.foogallery.frames.Importer.Query){
						q.query(self.data.query);
					}
				}, function (err) {
					if (err && err.mode === "abort") return;
					self.$(self.sel.error).html(err.message);
					self.$(self.sel.container).addClass(self.cls.error);
				});
			}
		}
	});

	wp.foogallery.frames.Importer.Query.Vimeo = Vimeo;

})( wp.foogallery.l10n, wp.foogallery.media.DataView, jQuery );
// the YouTube api key view
(function(l10n, DataView, $){

	var YouTube = DataView.extend({
		tagName: "div",
		className: "fgi-youtube",
		template: wp.template("fgi-youtube"),
		namespace: ".fgi-youtube",
		sel: {
			api_key: "input[name='api_key']",
			save: ".fgi-save",
			help: '[href="#toggle-help"]',
			container: ".button-hero-container",
			error: '.fgi-youtube-error-message'
		},
		cls: {
			error: "fgi-youtube-error"
		},
		initialize: function(){
			DataView.prototype.initialize.apply( this, arguments );
			_.defaults(this.data, {
				mode: "",
				api_key: "",
				query: ""
			});
		},
		activate: function(){
			this.$(this.sel.help).on("click" + this.namespace, _.bind(this.onHelpClick, this));
			this.$(this.sel.save).on("click" + this.namespace, _.bind(this.onSaveClick, this));
			this.$(this.sel.api_key)
					.on("keypress" + this.namespace + " paste" + this.namespace + " change" + this.namespace, _.debounce(_.bind(this.onValidateChange, this), 500))
					.on("keydown" + this.namespace, _.debounce(_.bind(this.onValidateKeydown, this), 500));
		},
		deactivate: function(){
			this.$(this.sel.save).off(this.namespace);
			this.$(this.sel.api_key).off(this.namespace);
		},
		onValidateChange: function (event) {
			this.validateChange(event.target);
		},
		onValidateKeydown: function (event) {
			var special = [8]; // backspace
			if (_.contains(special, event.which)){
				this.validateChange(event.target);
			}
		},
		validateChange: function(input){
			var $input = $(input), value = $input.val(), valid = _.isString(value) && value.length >= 30;
			this.$(this.sel.save).attr('disabled', !valid);
			this.$(this.sel.container).removeClass(this.cls.error);
			this.$(this.sel.error).empty();
			return valid;
		},
		onHelpClick: function(event){
			event.preventDefault();
			this.mediaFrame.toggleMode("help");
		},
		onSaveClick: function(event){
			event.preventDefault();
			this.save();
		},
		save: function(){
			var value = this.$(this.sel.api_key).val();
			if (_.isString(value) && value.length >= 30){
				var self = this, controller = this.controller;
				self.$(self.sel.save).attr('disabled', true);
				return controller.ajax_save({
					api_key: value,
					action: "fgi_save_youtube_api_key"
				}).then(function(){
					var q = controller.getView({region: "query"});
					if (q instanceof wp.foogallery.frames.Importer.Query){
						q.query(self.data.query);
					}
				}, function (err) {
					if (err && err.mode === "abort") return;
					self.$(self.sel.error).html(err.message);
					self.$(self.sel.container).addClass(self.cls.error);
				});
			}
		}
	});

	wp.foogallery.frames.Importer.Query.YouTube = YouTube;

})( wp.foogallery.l10n, wp.foogallery.media.DataView, jQuery );
(function(l10n, DataView, $){

	var Import = DataView.extend({
		tagName: "div",
		className: "fgi-import",
		template: wp.template("fgi-import"),
		namespace: ".fgi-import",
		sel: {
			yes: ".fgi-import-yes",
			back: ".fgi-import-back",
			cancel: ".fgi-import-cancel",
			progress_value: ".fgi-import-progress-value",
			progress_text: ".fgi-import-progress-text"
		},
		initialize: function(){
			DataView.prototype.initialize.apply( this, arguments );
			_.defaults(this.data, {
				total: 0,
				videos: []
			});
			this.max = 10;
			this.cancelling = false;
			this._result = {imported: [], failed: [], errors: [], cancelled: []};
			this._start = -1;
			this._end = -1;
			this.once("ready", this.onceReady, this);
		},
		onceReady: function(){
			if (this.skipConfirm()){
				this.do_import().then(_.bind(this.checkResult, this), function(err){
					console.log(err);
				});
			}
		},
		remove: function(force){
			this.$el.removeClass("mode-importing");
			return DataView.prototype.remove.apply(this, arguments);
		},
		activate: function(){
			this.$(this.sel.yes).on("click" + this.namespace, _.bind(this.onYesClick, this));
			this.$(this.sel.back).on("click" + this.namespace, _.bind(this.onBackClick, this));
			this.$(this.sel.cancel).on("click" + this.namespace, _.bind(this.onCancelClick, this));
		},
		deactivate: function(){
			this.$(this.sel.yes).off(this.namespace);
			this.$(this.sel.back).off(this.namespace);
			this.$(this.sel.cancel).off(this.namespace);
		},
		onBackClick: function(event){
			event.preventDefault();
			this.controller.back();
		},
		onYesClick: function(event){
			event.preventDefault();
			this.do_import().then(_.bind(this.checkResult, this), function(err){
				console.log(err);
			});
		},
		onCancelClick: function(event){
			event.preventDefault();
			this.setCancelling(true);
		},
		setCancelling: function(value){
			this.cancelling = value;
			if (value){
				this.$(this.sel.cancel).attr("disabled", "disabled").text(l10n.import.cancelling);
			} else {
				this.$(this.sel.cancel).removeAttr("disabled").text(l10n.import.cancel);
			}
		},
		skipConfirm: function(){
			return _.isNumber(this.data.total) && this.data.total <= this.max;
		},
		hasNextPage: function(){
			return this.data.hasOwnProperty("nextPage") && this.data.nextPage !== 0;
		},
		do_import: function(next){
			var self = this, data = this.data, parts = [];
			if (_.isArray(data.videos) && data.videos.length > 0){
				self.$el.addClass("mode-importing");
				self.mediaFrame.deactivateMode("backable");
				var len = data.videos.length;
				if (next){
					self._start = self._end + 1;
					self._end += len;
				} else {
					self._start = 1;
					self._end = len;
				}
				if (data.videos.length > self.max){
					var part = [], count = 0;
					_.each(data.videos, function(video){
						if (++count > self.max){
							parts.push(part);
							count = 1;
							part = [];
						}
						part.push(video);
					});
					if (part.length > 0) parts.push(part);
				} else {
					parts = _.map(data.videos, function(video){ return [video]; });
				}

				var total = parts.length, imported = [], failed = [], errors = [], cancelled = [],
						def = $.Deferred(),
						promise = def.promise();

				_.each(parts, function(videos, i){

					promise = promise.then(function(){
						if (self.cancelling){
							Array.prototype.push.apply(cancelled, videos);
							return;
						}
						return self.controller.ajax_import({videos: videos}).then(function(data){
							Array.prototype.push.apply(imported, data.imported);
							Array.prototype.push.apply(failed, data.failed);
							Array.prototype.push.apply(errors, data.errors);
							self.progressed(i + 1, total);
						});
					});

				});

				promise = promise.then(function(){
					var state = failed.length > 0 ? "mixed" : "completed";
					if (imported.length === 0) state = "failed";
					if (self.cancelling) state = "cancelled";
					self.setCancelling(false);
					return {
						"state": state,
						"imported": imported,
						"failed": failed,
						"errors": errors,
						"cancelled": cancelled
					};
				});

				self.progressed(0, total);
				def.resolve();
				return promise;
			} else {
				return $.Deferred().reject({"mode": "error","message": l10n.errors.importNoVideos}).promise();
			}
		},
		checkResult: function(result){
			Array.prototype.push.apply(this._result.imported, result.imported);
			Array.prototype.push.apply(this._result.failed, result.failed);
			Array.prototype.push.apply(this._result.errors, result.errors);
			Array.prototype.push.apply(this._result.cancelled, result.cancelled);
			switch(result.state){
				case "cancelled":
					this.setProgress(0, l10n.import.cancelled);
					this.cancelled(this._result);
					this._result = {imported: [], failed: [], errors: [], cancelled: []};
					break;
				default:
					if (this.hasNextPage()){
						var self = this;
						this.setProgress(0, l10n.import.fetching);
						this.controller.ajax_query({
							query: this.data.query,
							page: this.data.nextPage,
							offset: this.data.offset
						}).then(function(data){
							self.data = data;
							self.do_import(true).then(_.bind(self.checkResult, self), function(err){
								console.log(err);
							});
						});
					} else {
						this.completed(this._result);
						this._result = {imported: [], failed: [], errors: [], cancelled: []};
					}
					break;
			}
		},
		setProgress: function(percent, text){
			if (!_.isString(text)) text = percent + "%";
			this.$(this.sel.progress_text).text(text);
			this.$(this.sel.progress_value).css("width", percent + "%");
		},
		progressed: function(current, total){
			if (wp.foogallery.debug){
				console.log("progressed", current, total);
			}
			var percent = Math.round(((current / total) * 100) * 100) / 100, data = this.data, text;
			if (percent === 100){
				text = l10n.import.complete;
			} else if (this.hasNextPage()){
				text = wp.foogallery.format_l10n(l10n.import.importing, {start: this._start, end: this._end, total: data.total, percent: percent});
			}
			this.setProgress(percent, text);
		},
		completed: function(result){
			_.defaults(result, {reset: true});
			if (wp.foogallery.debug){
				console.log("completed", result);
			}
			this.$el.removeClass("mode-importing");
			this.controller.contentMode("import-result", result);
			if (this.mediaFrame.content.mode() !== "import"){
				this.mediaFrame.content.mode("import");
			}
		},
		cancelled: function(result){
			_.defaults(result, {reset: true});
			if (wp.foogallery.debug){
				console.log("cancelled", result);
			}
			this.$el.removeClass("mode-importing");
			this.controller.contentMode("import-result", result);
			if (this.mediaFrame.content.mode() !== "import"){
				this.mediaFrame.content.mode("import");
			}
		}
	});

	wp.foogallery.frames.Importer.Import = Import;

})( wp.foogallery.l10n, wp.foogallery.media.DataView, jQuery );
(function(DataView){

	var Result = DataView.extend({
		tagName: "div",
		className: "fgi-import-result",
		template: wp.template("fgi-import-result"),
		namespace: ".fgi-import-result",
		sel: {
			toggle_failed: '[href="#toggle-failed"]',
			toggle_cancelled: '[href="#toggle-cancelled"]',
			try_again_failed: '[href="#try-again-failed"]',
			import_cancelled: '[href="#import-cancelled"]',
			failed_list: ".fgi-import-failed",
			cancelled_list: ".fgi-import-cancelled",
			more_videos: ".fgi-import-more-videos",
			add_videos: ".fgi-import-add-videos",
			media_library: '[href="#media-library"]'
		},
		initialize: function(){
			DataView.prototype.initialize.apply( this, arguments );
			_.defaults(this.data, {
				imported: [],
				failed: [],
				errors: [],
				cancelled: []
			});
			if (this.data.imported.length > 0){
				var state = this.mediaFrame.state(),
						library = state.get("library"),
						selection = state.get("selection");
				library.props.set({ignore: (+ new Date())});
				this.loadMore(library, selection);
			}
		},
		activate: function(){
			this.$(this.sel.toggle_failed).on("click" + this.namespace, _.bind(this.onToggleFailedClick, this));
			this.$(this.sel.toggle_cancelled).on("click" + this.namespace, _.bind(this.onToggleCancelledClick, this));
			this.$(this.sel.try_again_failed).on("click" + this.namespace, _.bind(this.onTryAgainFailedClick, this));
			this.$(this.sel.import_cancelled).on("click" + this.namespace, _.bind(this.onImportCancelledClick, this));
			this.$(this.sel.more_videos).on("click" + this.namespace, _.bind(this.onImportMoreVideosClick, this));
			this.$(this.sel.add_videos).on("click" + this.namespace, _.bind(this.onImportAddVideosClick, this));
			this.$(this.sel.media_library).on("click" + this.namespace, _.bind(this.onMediaLibraryClick, this));
		},
		deactivate: function(){
			this.$(this.sel.toggle_failed).off(this.namespace);
			this.$(this.sel.toggle_cancelled).off(this.namespace);
			this.$(this.sel.try_again_failed).off(this.namespace);
			this.$(this.sel.import_cancelled).off(this.namespace);
			this.$(this.sel.more_videos).off(this.namespace);
			this.$(this.sel.add_videos).off(this.namespace);
			this.$(this.sel.media_library).off(this.namespace);
		},
		loadMore: function(library, selection){
			var self = this;
			library.more().then(function(){
				if (library.models.length < self.data.imported.length){
					self.loadMore(library, selection);
				} else {
					var add = _.filter(library.models, function(model){
						var id = model.get("id");
						return _.indexOf(self.data.imported, id) !== -1;
					});
					if (add.length > 0) selection.add(add);
				}
			});
		},
		onToggleFailedClick: function(event){
			event.preventDefault();
			this.$(this.sel.failed_list).toggleClass("mode-expanded");
		},
		onToggleCancelledClick: function(event){
			event.preventDefault();
			this.$(this.sel.cancelled_list).toggleClass("mode-expanded");
		},
		onTryAgainFailedClick: function(event){
			event.preventDefault();
			this.controller.contentMode("import", {"mode": "retry", "total": this.data.failed.length, "videos": this.data.failed});
		},
		onImportCancelledClick: function(event){
			event.preventDefault();
			this.controller.contentMode("import", {"mode": "retry", "total": this.data.cancelled.length, "videos": this.data.cancelled});
		},
		onImportMoreVideosClick: function(event){
			event.preventDefault();
			this.controller.contentMode("getting-started", {reset: true});
		},
		onImportAddVideosClick: function(event){
			event.preventDefault();
			this.controller.contentMode("getting-started", {reset: true});
			this.mediaFrame.content.mode("browse");
			this.mediaFrame.$(".media-button-select").click();
		},
		onMediaLibraryClick: function(event){
			event.preventDefault();
			this.mediaFrame.content.mode("browse");
		}
	});

	wp.foogallery.frames.Importer.Import.Result = Result;

})( wp.foogallery.media.DataView );