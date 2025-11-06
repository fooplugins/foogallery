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

        if ( _is.object( window[ 'FooGallery_auto' ] ) ) {
            _.auto( window[ 'FooGallery_auto' ] );
        }
		if ( _is.object( window[ 'FooGallery_il8n' ] ) ){
			_.merge_il8n( window[ 'FooGallery_il8n' ] );
		}
        _.globalsMerged = true;
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