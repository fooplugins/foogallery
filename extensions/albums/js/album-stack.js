/* Modernizr 2.8.3 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-csstransitions-prefixed-testprop-testallprops-domprefixes
 */
if (!window.FooGalleryStackAlbumModernizr) {
	window.FooGalleryStackAlbumModernizr=function(a,b,c){function w(a){i.cssText=a}function x(a,b){return w(prefixes.join(a+";")+(b||""))}function y(a,b){return typeof a===b}function z(a,b){return!!~(""+a).indexOf(b)}function A(a,b){for(var d in a){var e=a[d];if(!z(e,"-")&&i[e]!==c)return b=="pfx"?e:!0}return!1}function B(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:y(f,"function")?f.bind(d||b):f}return!1}function C(a,b,c){var d=a.charAt(0).toUpperCase()+a.slice(1),e=(a+" "+m.join(d+" ")+d).split(" ");return y(b,"string")||y(b,"undefined")?A(e,b):(e=(a+" "+n.join(d+" ")+d).split(" "),B(e,b,c))}var d="2.8.3",e={},f=b.documentElement,g="modernizr",h=b.createElement(g),i=h.style,j,k={}.toString,l="Webkit Moz O ms",m=l.split(" "),n=l.toLowerCase().split(" "),o={},p={},q={},r=[],s=r.slice,t,u={}.hasOwnProperty,v;!y(u,"undefined")&&!y(u.call,"undefined")?v=function(a,b){return u.call(a,b)}:v=function(a,b){return b in a&&y(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=s.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(s.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(s.call(arguments)))};return e}),o.csstransitions=function(){return C("transition")};for(var D in o)v(o,D)&&(t=D.toLowerCase(),e[t]=o[D](),r.push((e[t]?"":"no-")+t));return e.addTest=function(a,b){if(typeof a=="object")for(var d in a)v(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof enableClasses!="undefined"&&enableClasses&&(f.className+=" "+(b?"":"no-")+a),e[a]=b}return e},w(""),h=j=null,e._version=d,e._domPrefixes=n,e._cssomPrefixes=m,e.testProp=function(a){return A([a])},e.testAllProps=C,e.prefixed=function(a,b,c){return b?C(a,b,c):C(a,"pfx")},e}(this,this.document);
}

/**
 * jquery.stapel.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2012, Codrops
 * http://www.codrops.com
 */
;( function( $, window, undefined ) {

	'use strict';

	/*
	 * debouncedresize: special jQuery event that happens once after a window resize
	 *
	 * latest version and complete README available on Github:
	 * https://github.com/louisremi/jquery-smartresize/blob/master/jquery.debouncedresize.js
	 *
	 * Copyright 2011 @louis_remi
	 * Licensed under the MIT license.
	 */
	var $event = $.event,
		$special,
		resizeTimeout;

	$special = $event.special.debouncedresize = {
		setup: function() {
			$( this ).on( "resize", $special.handler );
		},
		teardown: function() {
			$( this ).off( "resize", $special.handler );
		},
		handler: function( event, execAsap ) {
			// Save the context
			var context = this,
				args = arguments,
				dispatch = function() {
					// set correct event type
					event.type = "debouncedresize";
					$event.dispatch.apply( context, args );
				};

			if ( resizeTimeout ) {
				clearTimeout( resizeTimeout );
			}

			execAsap ?
				dispatch() :
				resizeTimeout = setTimeout( dispatch, $special.threshold );
		},
		threshold: 150
	};

	// ======================= imagesLoaded Plugin ===============================
	// https://github.com/desandro/imagesloaded

	// renamed from imagesLoaded to foogalleryImagesLoaded to avoid conflicts

	// $('#my-container').foogalleryImagesLoaded(myFunction)
	// execute a callback when all images have loaded.
	// needed because .load() doesn't work on cached images

	// callback function gets image collection as argument
	//  this is the container

	// original: mit license. paul irish. 2010.
	// contributors: Oren Solomianik, David DeSandro, Yiannis Chatzikonstantinou

	// blank image data-uri bypasses webkit log warning (thx doug jones)
	var BLANK = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

	$.fn.foogalleryImagesLoaded = function( callback ) {
		var $this = this,
			deferred = $.isFunction($.Deferred) ? $.Deferred() : 0,
			hasNotify = $.isFunction(deferred.notify),
			$images = $this.find('img').add( $this.filter('img') ),
			loaded = [],
			proper = [],
			broken = [];

		// Register deferred callbacks
		if ($.isPlainObject(callback)) {
			$.each(callback, function (key, value) {
				if (key === 'callback') {
					callback = value;
				} else if (deferred) {
					deferred[key](value);
				}
			});
		}

		function doneLoading() {
			var $proper = $(proper),
				$broken = $(broken);

			if ( deferred ) {
				if ( broken.length ) {
					deferred.reject( $images, $proper, $broken );
				} else {
					deferred.resolve( $images );
				}
			}

			if ( $.isFunction( callback ) ) {
				callback.call( $this, $images, $proper, $broken );
			}
		}

		function imgLoaded( img, isBroken ) {
			// don't proceed if BLANK image, or image is already loaded
			if ( img.src === BLANK || $.inArray( img, loaded ) !== -1 ) {
				return;
			}

			// store element in loaded images array
			loaded.push( img );

			// keep track of broken and properly loaded images
			if ( isBroken ) {
				broken.push( img );
			} else {
				proper.push( img );
			}

			// cache image and its state for future calls
			$.data( img, 'foogalleryImagesLoaded', { isBroken: isBroken, src: img.src } );

			// trigger deferred progress method if present
			if ( hasNotify ) {
				deferred.notifyWith( $(img), [ isBroken, $images, $(proper), $(broken) ] );
			}

			// call doneLoading and clean listeners if all images are loaded
			if ( $images.length === loaded.length ){
				setTimeout( doneLoading );
				$images.unbind( '.foogalleryImagesLoaded' );
			}
		}

		// if no images, trigger immediately
		if ( !$images.length ) {
			doneLoading();
		} else {
			$images.bind( 'load.foogalleryImagesLoaded error.foogalleryImagesLoaded', function( event ){
				// trigger imgLoaded
				imgLoaded( event.target, event.type === 'error' );
			}).each( function( i, el ) {
				var src = el.src;

				// find out if this image has been already checked for status
				// if it was, and src has not changed, call imgLoaded on it
				var cached = $.data( el, 'foogalleryImagesLoaded' );
				if ( cached && cached.src === src ) {
					imgLoaded( el, cached.isBroken );
					return;
				}

				// if complete is true and browser supports natural sizes, try
				// to check for image status manually
				if ( el.complete && el.naturalWidth !== undefined ) {
					imgLoaded( el, el.naturalWidth === 0 || el.naturalHeight === 0 );
					return;
				}

				// cached images don't fire load sometimes, so we reset src, but only when
				// dealing with IE, or image is complete (loaded) and failed manual check
				// webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
				if ( el.readyState || el.complete ) {
					el.src = BLANK;
					el.src = src;
				}
			});
		}

		return deferred ? deferred.promise( $this ) : $this;
	};

	// global
	var $window = $( window ),
		FooGalleryStackAlbumModernizr = window.FooGalleryStackAlbumModernizr;

	$.Stapel = function( options, element ) {

		this.el = $( element );
		this._init( options );

	};

	// the options
	$.Stapel.defaults = {
		// space between the items
		gutter : 40,
		// the rotations degree for the 2nd and 3rd item
		// (to give a more realistic pile effect)
		pileAngles : 2,
		// animation settings for the clicked pile's items
		pileAnimation : {
			openSpeed : 400,
			openEasing : 'ease-in-out', // try this :) 'cubic-bezier(.47,1.34,.9,1.03)',
			closeSpeed : 400,
			closeEasing : 'ease-in-out'
		},
		// animation settings for the other piles
		otherPileAnimation : {
			openSpeed : 400,
			openEasing : 'ease-in-out',
			closeSpeed : 350,
			closeEasing : 'ease-in-out'
		},
		// delay for each item of the pile
		delay : 0,
		// random rotation for the items once opened
		randomAngle : false,
		onLoad : function() { return false; },
		onBeforeOpen : function( pileName ) { return false; },
		onAfterOpen : function( pileName, totalItems ) { return false; },
		onBeforeClose : function( pileName ) { return false; },
		onAfterClose : function( pileName, totalItems ) { return false; }
	};

	$.Stapel.prototype = {

		_init : function( options ) {

			// options
			this.options = $.extend( true, {}, $.Stapel.defaults, options );

			// cache some elements
			this._config();

			// preload images
			var self = this;
			this.el.foogalleryImagesLoaded( function() {
				self.options.onLoad();
				self._layout();
				self._initEvents();
				if (typeof FOOBOX != 'undefined') {
					FOOBOX.init();
				}
			} );

		},
		_config : function() {

			// css transitions support
			this.support = FooGalleryStackAlbumModernizr.csstransitions;

			var transEndEventNames = {
					'WebkitTransition' : 'webkitTransitionEnd',
					'MozTransition' : 'transitionend',
					'OTransition' : 'oTransitionEnd',
					'msTransition' : 'MSTransitionEnd',
					'transition' : 'transitionend'
				},
				transformNames = {
					'WebkitTransform' : '-webkit-transform',
					'MozTransform' : '-moz-transform',
					'OTransform' : '-o-transform',
					'msTransform' : '-ms-transform',
					'transform' : 'transform'
				};

			if( this.support ) {

				this.transEndEventName = transEndEventNames[ FooGalleryStackAlbumModernizr.prefixed( 'transition' ) ] + '.cbpFWSlider';
				this.transformName = transformNames[ FooGalleryStackAlbumModernizr.prefixed( 'transform' ) ];

			}

			// true if one pile is opened
			this.spread = false;

			// the li's
			this.items = this.el.children( 'li' ).hide();

			// close pile
			this.close = $( '#tp-close' );

		},
		_getSize : function() {

			this.elWidth = this.el.outerWidth( true );

		},
		_initEvents : function() {

			var self = this;
			$window.on( 'debouncedresize.stapel', function() { self._resize(); } );
			this.items.on( 'click.stapel', function() {

				var $item = $( this );

				if( !self.spread && $item.data( 'isPile' ) ) {

					self.spread = true;
					self.pileName = $item.data( 'pileName' );
					self.options.onBeforeOpen( self.pileName );
					self._openPile();

					return false;

				}

			} );

		},
		_layout : function() {

			/*
			 piles() : save the items info in a object with the following structure:

			 this.piles = {

			 pileName : {

			 // elements of this pile (note that an element can be also in a different pile)
			 // for each element, the finalPosition is the position of the element when the pile is opened
			 elements : [
			 { el : HTMLELEMENT, finalPosition : { left : LEFT, top : TOP } },
			 {},
			 {},
			 ...
			 ],
			 // this is the position of the pile (all elements of the pile) when the pile is closed
			 position : { left : LEFT, top : TOP },
			 index : INDEX
			 },

			 // more piles
			 ...

			 }
			 */
			this._piles();

			// items width & height
			// assuming here that all items have the same width and height
			this.itemSize = { width : this.items.outerWidth( true ) , height : this.items.outerHeight( true ) };

			// remove original elements
			this.items.remove();

			// applies some initial style for the items
			this._setInitialStyle();

			this.el.css( 'min-width', this.itemSize.width + this.options.gutter );

			// gets the current ul size (needed for the calculation of each item's position)
			this._getSize();

			// calculate and set each Pile's elements position based on the current ul width
			// this function will also be called on window resize
			this._setItemsPosition();

			// new items
			this.items = this.el.children( 'li' ).show();
			// total items
			this.itemsCount	= this.items.length;

		},
		_piles : function() {

			var piles = {};
			this.pilesArr = [];
			var pile, self = this, idx = 0;
			this.items.each( function() {

				var $item = $( this ),
					itemPile = $item.attr( 'data-pile' ) || 'nopile-' + $item.index(),
					attr = itemPile.split( ',' );

				for( var i = 0, total = attr.length; i < total; ++i ) {

					var pileName = $.trim( attr[i] );
					pile = piles[ pileName ];

					if( !pile ) {

						pile = piles[ pileName ] = {
							name : pileName,
							elements : [],
							position : { left : 0, top : 0 },
							index : idx
						};

						self.pilesArr.push(pile);

						++idx;

					}

					var clone = $item.clone().get(0);
					pile.elements.push( { el : clone, finalPosition : { left : 0, top : 0 } } );
					var $clone = $(clone);
					$clone.appendTo(self.el);
				}

			} );

		},
		_setInitialStyle : function() {
			for (var j =0, pile_len = this.pilesArr.length; j < pile_len; j++){

				var p = this.pilesArr[j], featured = false, $el, styleCSS;
				for (var k = 0, kl = p.elements.length; k < kl; k++){
					if ( $( p.elements[k].el ).data('featured') ){
						featured = true;
						break;
					}
				}

				for( var i = 0, len = p.elements.length; i < len; ++i ) {

					$el = $( p.elements[i].el );
					styleCSS = { transform : 'rotate(0deg)' };

					this._applyInitialTransition( $el );

					if ( p.name.substr( 0, 6 ) !== 'nopile' && ( $el.data('featured') || ( !featured && i === 0 ) ) ){
						$el.css({ zIndex: 9999 }).data( 'front', true ).append( '<div class="tp-title-cover"><div class="tp-title"><span>' + p.name + '</span><span>' + len + '</span></div></div>' );
					}
					else if( i === len - 2 ) {
						styleCSS = { transform : 'rotate(' + this.options.pileAngles + 'deg)' };
					}
					else if( i === len - 3 ) {
						styleCSS = { transform : 'rotate(-' + this.options.pileAngles + 'deg)' };
					}
					else {
						var extraStyle = { visibility : 'hidden' };
						$el.css( extraStyle ).data( 'extraStyle', extraStyle );
					}

					$el.css( styleCSS ).data( {
						initialStyle : styleCSS,
						pileName : p.name,
						pileCount : len,
						shadow : $el.css( 'box-shadow' ),
						isPile : p.name.substr(0, 6) !== 'nopile'
					} );

				}

			}

		},
		_applyInitialTransition : function( $el ) {

			if( this.support ) {
				$el.css( 'transition', 'left 400ms ease-in-out, top 400ms ease-in-out' );
			}

		},
		_setItemsPosition : function() {

			var accumL = 0, accumT = 0,
				l, t, ml = 0,
				lastItemTop = 0;

			for (var j =0, pile_len = this.pilesArr.length; j < pile_len; j++){

				var p = this.pilesArr[j],

					//for( var pile in this.piles ) {
					//
					//	var p = this.piles[pile],
					stepW = this.itemSize.width + this.options.gutter,

					accumIL = 0, accumIT = 0, il, it;

				if( accumL + stepW <= this.elWidth ) {

					l = accumL;
					t = accumT;
					accumL += stepW;

				}
				else {

					if( ml === 0 ) {
						ml = Math.ceil( ( this.elWidth - accumL + this.options.gutter ) / 2 );
					}

					accumT += this.itemSize.height + this.options.gutter;
					l = 0;
					t = accumT;
					accumL = stepW;

				}

				p.position.left = l;
				p.position.top = t;

				for( var i = 0, len = p.elements.length; i < len; ++i ) {

					var elem = p.elements[i],
						fp = elem.finalPosition;

					if( accumIL + stepW <= this.elWidth ) {

						il = accumIL;
						it = accumIT;
						accumIL += stepW;

					}
					else {

						accumIT += this.itemSize.height + this.options.gutter;
						il = 0;
						it = accumIT;
						accumIL = stepW;

					}

					fp.left = il;
					fp.top = it;

					var $el = $( elem.el );

					if(p.name !== this.pileName ) {

						$el.css( { left : p.position.left, top : p.position.top } );

					}
					else {

						lastItemTop = elem.finalPosition.top;
						$el.css( { left : elem.finalPosition.left, top : lastItemTop } );

					}

				}

			}

			if( ml === 0 ) {
				ml = Math.ceil( ( this.elWidth - accumL + this.options.gutter ) / 2 );
			}

			// the position of the items will influence the final margin left value and height for the ul
			// center the ul
			lastItemTop = this.spread ? lastItemTop : accumT;
			this.marginLeft = ml;
			this.el.css( {
				marginLeft : this.marginLeft,
				height : lastItemTop + this.itemSize.height
			} );

		},
		_openPile : function() {

			if( !this.spread ) {
				return false;
			}

			// final style
			var fs, rowWidth = 0;

			for (var j =0, pile_len = this.pilesArr.length; j < pile_len; j++){

				var p = this.pilesArr[j],
					cnt = 0;

				for( var i = 0, len = p.elements.length; i < len; ++i ) {

					var elem = p.elements[i],
						$item = $( elem.el ),
						styleCSS = p.name === this.pileName ? {
							zIndex : 9999,
							visibility : 'visible',
							transition : this.support ? 'left ' + this.options.pileAnimation.openSpeed + 'ms ' + ( ( len - i - 1 ) * this.options.delay ) + 'ms ' + this.options.pileAnimation.openEasing + ', top ' + this.options.pileAnimation.openSpeed + 'ms ' + ( ( len - i - 1 ) * this.options.delay ) + 'ms ' + this.options.pileAnimation.openEasing + ', ' + this.transformName + ' ' + this.options.pileAnimation.openSpeed + 'ms ' + ( ( len - i - 1 ) * this.options.delay ) + 'ms ' + this.options.pileAnimation.openEasing : 'none'
						} : {
							zIndex : 1,
							transition : this.support ? 'opacity ' + this.options.otherPileAnimation.closeSpeed + 'ms ' + this.options.otherPileAnimation.closeEasing : 'none'
						};

					if( p.name === this.pileName ) {

						if (elem.finalPosition.top === 0){
							if (rowWidth > 0) rowWidth += this.options.gutter;
							rowWidth += this.itemSize.width;
						}

						if( $item.data( 'front' ) ) {
							$item.find( 'div.tp-title' ).hide();
						}

						fs = elem.finalPosition;
						fs.transform = this.options.randomAngle && i !== p.index ? 'rotate(' + Math.floor( Math.random() * ( 5 + 5 + 1 ) - 5 ) + 'deg)' : 'none';

						if( !this.support ) {
							$item.css( 'transform', 'none' );
						}

						// hack: remove box-shadow while animating to prevent the shadow stack effect
						if( i < len - 3 ) {
							$item.css( 'box-shadow', 'none' );
						}

					}

					$item.css( styleCSS );

					var self = this;

					p.name === this.pileName ?
						this._applyTransition( $item, fs, this.options.pileAnimation.openSpeed, function( evt ) {

							var target = this.target || this.nodeName;
							if( target !== 'LI' ) {
								return;
							}

							var $el = $( this );

							// hack: remove box-shadow while animating to prevent the shadow stack effect
							$el.css( 'box-shadow', $el.data( 'shadow' ) );

							if( self.support ) {
								$el.off( self.transEndEventName );
							}

							++cnt;

							if( cnt === $el.data( 'pileCount' ) ) {

								$( document ).one( 'mousemove.stapel', function() {
									self.el.addClass( 'tp-open' );
								} );
								self.options.onAfterOpen( self.pileName, cnt );

							}

						} ) :
						this._applyTransition( $item, { visibility: 'hidden', opacity : 0 }, this.options.otherPileAnimation.closeSpeed );

				}

			}

			//this.el.css( 'height', fs.top + this.itemSize.height );
			this.el.css({
				marginLeft: Math.ceil((this.elWidth - rowWidth) / 2),
				height: fs.top + this.itemSize.height
			});

		},
		_closePile : function() {

			var self = this;

			// close..
			if( this.spread ) {

				this.spread = false;

				this.options.onBeforeClose( this.pileName );

				this.el.removeClass( 'tp-open' );

				// final style
				var fs;
				for (var j =0, pile_len = this.pilesArr.length; j < pile_len; j++){

					var p = this.pilesArr[j],
						cnt = 0;

					for( var i = 0, len = p.elements.length; i < len; ++i ) {

						var $item = $( p.elements[i].el ),
							styleCSS = p.name === this.pileName ? {
								transition : this.support ? 'left ' + this.options.pileAnimation.closeSpeed + 'ms ' + this.options.pileAnimation.closeEasing + ', top ' + this.options.pileAnimation.closeSpeed + 'ms ' + this.options.pileAnimation.closeEasing + ', ' + this.transformName + ' ' + this.options.pileAnimation.closeSpeed + 'ms ' + this.options.pileAnimation.closeEasing : 'none'
							} : {
								transition : this.support ? 'opacity ' + this.options.otherPileAnimation.openSpeed + 'ms ' + this.options.otherPileAnimation.openEasing : 'none'
							};

						$item.css( styleCSS );

						fs = p.position;

						if(p.name === this.pileName ) {

							$.extend( fs, $item.data( 'initialStyle' ) );

							// hack: remove box-shadow while animating to prevent the shadow stack effect
							if( i < len - 3 ) {
								$item.css( 'box-shadow', 'none' );
							}

						}

						p.name === this.pileName ? this._applyTransition( $item, fs, this.options.pileAnimation.closeSpeed, function( evt ) {

							var target = this.target || this.nodeName;
							if( target !== 'LI' ) {
								return;
							}

							var $el = $( this ), extraStyle = $el.data( 'extraStyle' ), initialStyle = $el.data( 'initialStyle' );

							// hack: remove box-shadow while animating to prevent the shadow stack effect
							$el.css( 'box-shadow', $el.data( 'shadow' ) );

							if( self.support ) {
								$el.off( self.transEndEventName );
								self._applyInitialTransition( $el );
							}
							else {
								$el.css( initialStyle );
							}

							if( extraStyle ) {
								$el.css( extraStyle );
							}

							++cnt;

							if( $el.data( 'front' ) ) {
								$el.css({visibility: 'visible'}).find( 'div.tp-title' ).show();
							} else {
								$el.css({visibility: 'hidden', zIndex: 1});
							}

							if( cnt === $el.data( 'pileCount' ) ) {
								self.options.onAfterClose( $el.data( 'pileName' ), cnt );
							}

						} ) : this._applyTransition( $item, { opacity : 1 }, this.options.otherPileAnimation.openSpeed, function( evt ) {

							var target = this.target || this.nodeName;
							if( target !== 'LI' ) {
								return;
							}

							var $el = $( this );
							if( $el.data( 'front' ) ) {
								$el.css({visibility: 'visible'}).find( 'div.tp-title' ).show();
							} else {
								$el.css({visibility: 'hidden', zIndex: 1});
							}

							if( self.support ) {
								$el.off( self.transEndEventName );
								self._applyInitialTransition( $el );
							}

						} );

					}

				}

				// reset pile name
				this.pileName = '';

				// update ul height
				//this.el.css( 'height', fs.top + this.itemSize.height );
				this.el.css({
					marginLeft: this.marginLeft,
					height: fs.top + this.itemSize.height
				});

			}

			return false;

		},
		_resize : function() {

			// get ul size again
			this._getSize();
			// reset items positions
			this._setItemsPosition();

		},
		_applyTransition : function( el, styleCSS, speed, fncomplete ) {

			$.fn.applyStyle = this.support ? $.fn.css : $.fn.animate;

			if( fncomplete && this.support ) {

				el.on( this.transEndEventName, fncomplete );

			}

			fncomplete = fncomplete || function() { return false; };

			el.stop().applyStyle( styleCSS, $.extend( true, [], { duration : speed + 'ms', complete : fncomplete } ) );

		},
		closePile : function() {

			this._closePile();

		}

	};

	var logError = function( message ) {

		if ( window.console ) {

			window.console.error( message );

		}

	};

	$.fn.stapel = function( options ) {

		var instance = $.data( this, 'stapel' );

		if ( typeof options === 'string' ) {

			var args = Array.prototype.slice.call( arguments, 1 );

			this.each(function() {

				if ( !instance ) {

					logError( "cannot call methods on stapel prior to initialization; " +
						"attempted to call method '" + options + "'" );
					return;

				}

				if ( !$.isFunction( instance[options] ) || options.charAt(0) === "_" ) {

					logError( "no such method '" + options + "' for stapel instance" );
					return;

				}

				instance[ options ].apply( instance, args );

			});

		}
		else {

			this.each(function() {

				if ( instance ) {

					instance._init();

				}
				else {

					instance = $.data( this, 'stapel', new $.Stapel( options, this ) );

				}

			});

		}

		return instance;

	};

} )( jQuery, window );