/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the W3C SOFTWARE AND DOCUMENT NOTICE AND LICENSE.
 *
 *  https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
 *
 */
(function() {
    'use strict';

// Exit early if we're not running in a browser.
    if (typeof window !== 'object') {
        return;
    }

// Exit early if all IntersectionObserver and IntersectionObserverEntry
// features are natively supported.
    if ('IntersectionObserver' in window &&
        'IntersectionObserverEntry' in window &&
        'intersectionRatio' in window.IntersectionObserverEntry.prototype) {

        // Minimal polyfill for Edge 15's lack of `isIntersecting`
        // See: https://github.com/w3c/IntersectionObserver/issues/211
        if (!('isIntersecting' in window.IntersectionObserverEntry.prototype)) {
            Object.defineProperty(window.IntersectionObserverEntry.prototype,
                'isIntersecting', {
                    get: function () {
                        return this.intersectionRatio > 0;
                    }
                });
        }
        return;
    }


    /**
     * A local reference to the document.
     */
    var document = window.document;


    /**
     * An IntersectionObserver registry. This registry exists to hold a strong
     * reference to IntersectionObserver instances currently observing a target
     * element. Without this registry, instances without another reference may be
     * garbage collected.
     */
    var registry = [];


    /**
     * Creates the global IntersectionObserverEntry constructor.
     * https://w3c.github.io/IntersectionObserver/#intersection-observer-entry
     * @param {Object} entry A dictionary of instance properties.
     * @constructor
     */
    function IntersectionObserverEntry(entry) {
        this.time = entry.time;
        this.target = entry.target;
        this.rootBounds = entry.rootBounds;
        this.boundingClientRect = entry.boundingClientRect;
        this.intersectionRect = entry.intersectionRect || getEmptyRect();
        this.isIntersecting = !!entry.intersectionRect;

        // Calculates the intersection ratio.
        var targetRect = this.boundingClientRect;
        var targetArea = targetRect.width * targetRect.height;
        var intersectionRect = this.intersectionRect;
        var intersectionArea = intersectionRect.width * intersectionRect.height;

        // Sets intersection ratio.
        if (targetArea) {
            // Round the intersection ratio to avoid floating point math issues:
            // https://github.com/w3c/IntersectionObserver/issues/324
            this.intersectionRatio = Number((intersectionArea / targetArea).toFixed(4));
        } else {
            // If area is zero and is intersecting, sets to 1, otherwise to 0
            this.intersectionRatio = this.isIntersecting ? 1 : 0;
        }
    }


    /**
     * Creates the global IntersectionObserver constructor.
     * https://w3c.github.io/IntersectionObserver/#intersection-observer-interface
     * @param {Function} callback The function to be invoked after intersection
     *     changes have queued. The function is not invoked if the queue has
     *     been emptied by calling the `takeRecords` method.
     * @param {Object=} opt_options Optional configuration options.
     * @constructor
     */
    function IntersectionObserver(callback, opt_options) {

        var options = opt_options || {};

        if (typeof callback != 'function') {
            throw new Error('callback must be a function');
        }

        if (options.root && options.root.nodeType != 1) {
            throw new Error('root must be an Element');
        }

        // Binds and throttles `this._checkForIntersections`.
        this._checkForIntersections = throttle(
            this._checkForIntersections.bind(this), this.THROTTLE_TIMEOUT);

        // Private properties.
        this._callback = callback;
        this._observationTargets = [];
        this._queuedEntries = [];
        this._rootMarginValues = this._parseRootMargin(options.rootMargin);

        // Public properties.
        this.thresholds = this._initThresholds(options.threshold);
        this.root = options.root || null;
        this.rootMargin = this._rootMarginValues.map(function(margin) {
            return margin.value + margin.unit;
        }).join(' ');
    }


    /**
     * The minimum interval within which the document will be checked for
     * intersection changes.
     */
    IntersectionObserver.prototype.THROTTLE_TIMEOUT = 100;


    /**
     * The frequency in which the polyfill polls for intersection changes.
     * this can be updated on a per instance basis and must be set prior to
     * calling `observe` on the first target.
     */
    IntersectionObserver.prototype.POLL_INTERVAL = null;

    /**
     * Use a mutation observer on the root element
     * to detect intersection changes.
     */
    IntersectionObserver.prototype.USE_MUTATION_OBSERVER = true;


    /**
     * Starts observing a target element for intersection changes based on
     * the thresholds values.
     * @param {Element} target The DOM element to observe.
     */
    IntersectionObserver.prototype.observe = function(target) {
        var isTargetAlreadyObserved = this._observationTargets.some(function(item) {
            return item.element == target;
        });

        if (isTargetAlreadyObserved) {
            return;
        }

        if (!(target && target.nodeType == 1)) {
            throw new Error('target must be an Element');
        }

        this._registerInstance();
        this._observationTargets.push({element: target, entry: null});
        this._monitorIntersections();
        this._checkForIntersections();
    };


    /**
     * Stops observing a target element for intersection changes.
     * @param {Element} target The DOM element to observe.
     */
    IntersectionObserver.prototype.unobserve = function(target) {
        this._observationTargets =
            this._observationTargets.filter(function(item) {

                return item.element != target;
            });
        if (!this._observationTargets.length) {
            this._unmonitorIntersections();
            this._unregisterInstance();
        }
    };


    /**
     * Stops observing all target elements for intersection changes.
     */
    IntersectionObserver.prototype.disconnect = function() {
        this._observationTargets = [];
        this._unmonitorIntersections();
        this._unregisterInstance();
    };


    /**
     * Returns any queue entries that have not yet been reported to the
     * callback and clears the queue. This can be used in conjunction with the
     * callback to obtain the absolute most up-to-date intersection information.
     * @return {Array} The currently queued entries.
     */
    IntersectionObserver.prototype.takeRecords = function() {
        var records = this._queuedEntries.slice();
        this._queuedEntries = [];
        return records;
    };


    /**
     * Accepts the threshold value from the user configuration object and
     * returns a sorted array of unique threshold values. If a value is not
     * between 0 and 1 and error is thrown.
     * @private
     * @param {Array|number=} opt_threshold An optional threshold value or
     *     a list of threshold values, defaulting to [0].
     * @return {Array} A sorted list of unique and valid threshold values.
     */
    IntersectionObserver.prototype._initThresholds = function(opt_threshold) {
        var threshold = opt_threshold || [0];
        if (!Array.isArray(threshold)) threshold = [threshold];

        return threshold.sort().filter(function(t, i, a) {
            if (typeof t != 'number' || isNaN(t) || t < 0 || t > 1) {
                throw new Error('threshold must be a number between 0 and 1 inclusively');
            }
            return t !== a[i - 1];
        });
    };


    /**
     * Accepts the rootMargin value from the user configuration object
     * and returns an array of the four margin values as an object containing
     * the value and unit properties. If any of the values are not properly
     * formatted or use a unit other than px or %, and error is thrown.
     * @private
     * @param {string=} opt_rootMargin An optional rootMargin value,
     *     defaulting to '0px'.
     * @return {Array<Object>} An array of margin objects with the keys
     *     value and unit.
     */
    IntersectionObserver.prototype._parseRootMargin = function(opt_rootMargin) {
        var marginString = opt_rootMargin || '0px';
        var margins = marginString.split(/\s+/).map(function(margin) {
            var parts = /^(-?\d*\.?\d+)(px|%)$/.exec(margin);
            if (!parts) {
                throw new Error('rootMargin must be specified in pixels or percent');
            }
            return {value: parseFloat(parts[1]), unit: parts[2]};
        });

        // Handles shorthand.
        margins[1] = margins[1] || margins[0];
        margins[2] = margins[2] || margins[0];
        margins[3] = margins[3] || margins[1];

        return margins;
    };


    /**
     * Starts polling for intersection changes if the polling is not already
     * happening, and if the page's visibility state is visible.
     * @private
     */
    IntersectionObserver.prototype._monitorIntersections = function() {
        if (!this._monitoringIntersections) {
            this._monitoringIntersections = true;

            // If a poll interval is set, use polling instead of listening to
            // resize and scroll events or DOM mutations.
            if (this.POLL_INTERVAL) {
                this._monitoringInterval = setInterval(
                    this._checkForIntersections, this.POLL_INTERVAL);
            }
            else {
                addEvent(window, 'resize', this._checkForIntersections, true);
                addEvent(document, 'scroll', this._checkForIntersections, true);

                if (this.USE_MUTATION_OBSERVER && 'MutationObserver' in window) {
                    this._domObserver = new MutationObserver(this._checkForIntersections);
                    this._domObserver.observe(document, {
                        attributes: true,
                        childList: true,
                        characterData: true,
                        subtree: true
                    });
                }
            }
        }
    };


    /**
     * Stops polling for intersection changes.
     * @private
     */
    IntersectionObserver.prototype._unmonitorIntersections = function() {
        if (this._monitoringIntersections) {
            this._monitoringIntersections = false;

            clearInterval(this._monitoringInterval);
            this._monitoringInterval = null;

            removeEvent(window, 'resize', this._checkForIntersections, true);
            removeEvent(document, 'scroll', this._checkForIntersections, true);

            if (this._domObserver) {
                this._domObserver.disconnect();
                this._domObserver = null;
            }
        }
    };


    /**
     * Scans each observation target for intersection changes and adds them
     * to the internal entries queue. If new entries are found, it
     * schedules the callback to be invoked.
     * @private
     */
    IntersectionObserver.prototype._checkForIntersections = function() {
        var rootIsInDom = this._rootIsInDom();
        var rootRect = rootIsInDom ? this._getRootRect() : getEmptyRect();

        this._observationTargets.forEach(function(item) {
            var target = item.element;
            var targetRect = getBoundingClientRect(target);
            var rootContainsTarget = this._rootContainsTarget(target);
            var oldEntry = item.entry;
            var intersectionRect = rootIsInDom && rootContainsTarget &&
                this._computeTargetAndRootIntersection(target, rootRect);

            var newEntry = item.entry = new IntersectionObserverEntry({
                time: now(),
                target: target,
                boundingClientRect: targetRect,
                rootBounds: rootRect,
                intersectionRect: intersectionRect
            });

            if (!oldEntry) {
                this._queuedEntries.push(newEntry);
            } else if (rootIsInDom && rootContainsTarget) {
                // If the new entry intersection ratio has crossed any of the
                // thresholds, add a new entry.
                if (this._hasCrossedThreshold(oldEntry, newEntry)) {
                    this._queuedEntries.push(newEntry);
                }
            } else {
                // If the root is not in the DOM or target is not contained within
                // root but the previous entry for this target had an intersection,
                // add a new record indicating removal.
                if (oldEntry && oldEntry.isIntersecting) {
                    this._queuedEntries.push(newEntry);
                }
            }
        }, this);

        if (this._queuedEntries.length) {
            this._callback(this.takeRecords(), this);
        }
    };


    /**
     * Accepts a target and root rect computes the intersection between then
     * following the algorithm in the spec.
     * TODO(philipwalton): at this time clip-path is not considered.
     * https://w3c.github.io/IntersectionObserver/#calculate-intersection-rect-algo
     * @param {Element} target The target DOM element
     * @param {Object} rootRect The bounding rect of the root after being
     *     expanded by the rootMargin value.
     * @return {?Object} The final intersection rect object or undefined if no
     *     intersection is found.
     * @private
     */
    IntersectionObserver.prototype._computeTargetAndRootIntersection =
        function(target, rootRect) {

            // If the element isn't displayed, an intersection can't happen.
            if (window.getComputedStyle(target).display == 'none') return;

            var targetRect = getBoundingClientRect(target);
            var intersectionRect = targetRect;
            var parent = getParentNode(target);
            var atRoot = false;

            while (!atRoot) {
                var parentRect = null;
                var parentComputedStyle = parent.nodeType == 1 ?
                    window.getComputedStyle(parent) : {};

                // If the parent isn't displayed, an intersection can't happen.
                if (parentComputedStyle.display == 'none') return;

                if (parent == this.root || parent == document) {
                    atRoot = true;
                    parentRect = rootRect;
                } else {
                    // If the element has a non-visible overflow, and it's not the <body>
                    // or <html> element, update the intersection rect.
                    // Note: <body> and <html> cannot be clipped to a rect that's not also
                    // the document rect, so no need to compute a new intersection.
                    if (parent != document.body &&
                        parent != document.documentElement &&
                        parentComputedStyle.overflow != 'visible') {
                        parentRect = getBoundingClientRect(parent);
                    }
                }

                // If either of the above conditionals set a new parentRect,
                // calculate new intersection data.
                if (parentRect) {
                    intersectionRect = computeRectIntersection(parentRect, intersectionRect);

                    if (!intersectionRect) break;
                }
                parent = getParentNode(parent);
            }
            return intersectionRect;
        };


    /**
     * Returns the root rect after being expanded by the rootMargin value.
     * @return {Object} The expanded root rect.
     * @private
     */
    IntersectionObserver.prototype._getRootRect = function() {
        var rootRect;
        if (this.root) {
            rootRect = getBoundingClientRect(this.root);
        } else {
            // Use <html>/<body> instead of window since scroll bars affect size.
            var html = document.documentElement;
            var body = document.body;
            rootRect = {
                top: 0,
                left: 0,
                right: html.clientWidth || body.clientWidth,
                width: html.clientWidth || body.clientWidth,
                bottom: html.clientHeight || body.clientHeight,
                height: html.clientHeight || body.clientHeight
            };
        }
        return this._expandRectByRootMargin(rootRect);
    };


    /**
     * Accepts a rect and expands it by the rootMargin value.
     * @param {Object} rect The rect object to expand.
     * @return {Object} The expanded rect.
     * @private
     */
    IntersectionObserver.prototype._expandRectByRootMargin = function(rect) {
        var margins = this._rootMarginValues.map(function(margin, i) {
            return margin.unit == 'px' ? margin.value :
                margin.value * (i % 2 ? rect.width : rect.height) / 100;
        });
        var newRect = {
            top: rect.top - margins[0],
            right: rect.right + margins[1],
            bottom: rect.bottom + margins[2],
            left: rect.left - margins[3]
        };
        newRect.width = newRect.right - newRect.left;
        newRect.height = newRect.bottom - newRect.top;

        return newRect;
    };


    /**
     * Accepts an old and new entry and returns true if at least one of the
     * threshold values has been crossed.
     * @param {?IntersectionObserverEntry} oldEntry The previous entry for a
     *    particular target element or null if no previous entry exists.
     * @param {IntersectionObserverEntry} newEntry The current entry for a
     *    particular target element.
     * @return {boolean} Returns true if a any threshold has been crossed.
     * @private
     */
    IntersectionObserver.prototype._hasCrossedThreshold =
        function(oldEntry, newEntry) {

            // To make comparing easier, an entry that has a ratio of 0
            // but does not actually intersect is given a value of -1
            var oldRatio = oldEntry && oldEntry.isIntersecting ?
                oldEntry.intersectionRatio || 0 : -1;
            var newRatio = newEntry.isIntersecting ?
                newEntry.intersectionRatio || 0 : -1;

            // Ignore unchanged ratios
            if (oldRatio === newRatio) return;

            for (var i = 0; i < this.thresholds.length; i++) {
                var threshold = this.thresholds[i];

                // Return true if an entry matches a threshold or if the new ratio
                // and the old ratio are on the opposite sides of a threshold.
                if (threshold == oldRatio || threshold == newRatio ||
                    threshold < oldRatio !== threshold < newRatio) {
                    return true;
                }
            }
        };


    /**
     * Returns whether or not the root element is an element and is in the DOM.
     * @return {boolean} True if the root element is an element and is in the DOM.
     * @private
     */
    IntersectionObserver.prototype._rootIsInDom = function() {
        return !this.root || containsDeep(document, this.root);
    };


    /**
     * Returns whether or not the target element is a child of root.
     * @param {Element} target The target element to check.
     * @return {boolean} True if the target element is a child of root.
     * @private
     */
    IntersectionObserver.prototype._rootContainsTarget = function(target) {
        return containsDeep(this.root || document, target);
    };


    /**
     * Adds the instance to the global IntersectionObserver registry if it isn't
     * already present.
     * @private
     */
    IntersectionObserver.prototype._registerInstance = function() {
        if (registry.indexOf(this) < 0) {
            registry.push(this);
        }
    };


    /**
     * Removes the instance from the global IntersectionObserver registry.
     * @private
     */
    IntersectionObserver.prototype._unregisterInstance = function() {
        var index = registry.indexOf(this);
        if (index != -1) registry.splice(index, 1);
    };


    /**
     * Returns the result of the performance.now() method or null in browsers
     * that don't support the API.
     * @return {number} The elapsed time since the page was requested.
     */
    function now() {
        return window.performance && performance.now && performance.now();
    }


    /**
     * Throttles a function and delays its execution, so it's only called at most
     * once within a given time period.
     * @param {Function} fn The function to throttle.
     * @param {number} timeout The amount of time that must pass before the
     *     function can be called again.
     * @return {Function} The throttled function.
     */
    function throttle(fn, timeout) {
        var timer = null;
        return function () {
            if (!timer) {
                timer = setTimeout(function() {
                    fn();
                    timer = null;
                }, timeout);
            }
        };
    }


    /**
     * Adds an event handler to a DOM node ensuring cross-browser compatibility.
     * @param {Node} node The DOM node to add the event handler to.
     * @param {string} event The event name.
     * @param {Function} fn The event handler to add.
     * @param {boolean} opt_useCapture Optionally adds the even to the capture
     *     phase. Note: this only works in modern browsers.
     */
    function addEvent(node, event, fn, opt_useCapture) {
        if (typeof node.addEventListener == 'function') {
            node.addEventListener(event, fn, opt_useCapture || false);
        }
        else if (typeof node.attachEvent == 'function') {
            node.attachEvent('on' + event, fn);
        }
    }


    /**
     * Removes a previously added event handler from a DOM node.
     * @param {Node} node The DOM node to remove the event handler from.
     * @param {string} event The event name.
     * @param {Function} fn The event handler to remove.
     * @param {boolean} opt_useCapture If the event handler was added with this
     *     flag set to true, it should be set to true here in order to remove it.
     */
    function removeEvent(node, event, fn, opt_useCapture) {
        if (typeof node.removeEventListener == 'function') {
            node.removeEventListener(event, fn, opt_useCapture || false);
        }
        else if (typeof node.detatchEvent == 'function') {
            node.detatchEvent('on' + event, fn);
        }
    }


    /**
     * Returns the intersection between two rect objects.
     * @param {Object} rect1 The first rect.
     * @param {Object} rect2 The second rect.
     * @return {?Object} The intersection rect or undefined if no intersection
     *     is found.
     */
    function computeRectIntersection(rect1, rect2) {
        var top = Math.max(rect1.top, rect2.top);
        var bottom = Math.min(rect1.bottom, rect2.bottom);
        var left = Math.max(rect1.left, rect2.left);
        var right = Math.min(rect1.right, rect2.right);
        var width = right - left;
        var height = bottom - top;

        return (width >= 0 && height >= 0) && {
            top: top,
            bottom: bottom,
            left: left,
            right: right,
            width: width,
            height: height
        };
    }


    /**
     * Shims the native getBoundingClientRect for compatibility with older IE.
     * @param {Element} el The element whose bounding rect to get.
     * @return {Object} The (possibly shimmed) rect of the element.
     */
    function getBoundingClientRect(el) {
        var rect;

        try {
            rect = el.getBoundingClientRect();
        } catch (err) {
            // Ignore Windows 7 IE11 "Unspecified error"
            // https://github.com/w3c/IntersectionObserver/pull/205
        }

        if (!rect) return getEmptyRect();

        // Older IE
        if (!(rect.width && rect.height)) {
            rect = {
                top: rect.top,
                right: rect.right,
                bottom: rect.bottom,
                left: rect.left,
                width: rect.right - rect.left,
                height: rect.bottom - rect.top
            };
        }
        return rect;
    }


    /**
     * Returns an empty rect object. An empty rect is returned when an element
     * is not in the DOM.
     * @return {Object} The empty rect.
     */
    function getEmptyRect() {
        return {
            top: 0,
            bottom: 0,
            left: 0,
            right: 0,
            width: 0,
            height: 0
        };
    }

    /**
     * Checks to see if a parent element contains a child element (including inside
     * shadow DOM).
     * @param {Node} parent The parent element.
     * @param {Node} child The child element.
     * @return {boolean} True if the parent node contains the child node.
     */
    function containsDeep(parent, child) {
        var node = child;
        while (node) {
            if (node == parent) return true;

            node = getParentNode(node);
        }
        return false;
    }


    /**
     * Gets the parent node of an element or its host element if the parent node
     * is a shadow root.
     * @param {Node} node The node whose parent to get.
     * @return {Node|null} The parent node or null if no parent exists.
     */
    function getParentNode(node) {
        var parent = node.parentNode;

        if (parent && parent.nodeType == 11 && parent.host) {
            // If the parent is a shadow root, return the host element.
            return parent.host;
        }

        if (parent && parent.assignedSlot) {
            // If the parent is distributed in a <slot>, return the parent of a slot.
            return parent.assignedSlot.parentNode;
        }

        return parent;
    }


// Exposes the constructors globally.
    window.IntersectionObserver = IntersectionObserver;
    window.IntersectionObserverEntry = IntersectionObserverEntry;

}());
// @see https://github.com/que-etc/resize-observer-polyfill
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
			typeof define === 'function' && define.amd ? define(factory) :
					(global.ResizeObserver = factory());
}(this, (function () { 'use strict';

	/**
	 * A collection of shims that provide minimal functionality of the ES6 collections.
	 *
	 * These implementations are not meant to be used outside of the ResizeObserver
	 * modules as they cover only a limited range of use cases.
	 */
	/* eslint-disable require-jsdoc, valid-jsdoc */
	var MapShim = (function () {
		if (typeof Map !== 'undefined') {
			return Map;
		}
		/**
		 * Returns index in provided array that matches the specified key.
		 *
		 * @param {Array<Array>} arr
		 * @param {*} key
		 * @returns {number}
		 */
		function getIndex(arr, key) {
			var result = -1;
			arr.some(function (entry, index) {
				if (entry[0] === key) {
					result = index;
					return true;
				}
				return false;
			});
			return result;
		}
		return /** @class */ (function () {
			function class_1() {
				this.__entries__ = [];
			}
			Object.defineProperty(class_1.prototype, "size", {
				/**
				 * @returns {boolean}
				 */
				get: function () {
					return this.__entries__.length;
				},
				enumerable: true,
				configurable: true
			});
			/**
			 * @param {*} key
			 * @returns {*}
			 */
			class_1.prototype.get = function (key) {
				var index = getIndex(this.__entries__, key);
				var entry = this.__entries__[index];
				return entry && entry[1];
			};
			/**
			 * @param {*} key
			 * @param {*} value
			 * @returns {void}
			 */
			class_1.prototype.set = function (key, value) {
				var index = getIndex(this.__entries__, key);
				if (~index) {
					this.__entries__[index][1] = value;
				}
				else {
					this.__entries__.push([key, value]);
				}
			};
			/**
			 * @param {*} key
			 * @returns {void}
			 */
			class_1.prototype.delete = function (key) {
				var entries = this.__entries__;
				var index = getIndex(entries, key);
				if (~index) {
					entries.splice(index, 1);
				}
			};
			/**
			 * @param {*} key
			 * @returns {void}
			 */
			class_1.prototype.has = function (key) {
				return !!~getIndex(this.__entries__, key);
			};
			/**
			 * @returns {void}
			 */
			class_1.prototype.clear = function () {
				this.__entries__.splice(0);
			};
			/**
			 * @param {Function} callback
			 * @param {*} [ctx=null]
			 * @returns {void}
			 */
			class_1.prototype.forEach = function (callback, ctx) {
				if (ctx === void 0) { ctx = null; }
				for (var _i = 0, _a = this.__entries__; _i < _a.length; _i++) {
					var entry = _a[_i];
					callback.call(ctx, entry[1], entry[0]);
				}
			};
			return class_1;
		}());
	})();

	/**
	 * Detects whether window and document objects are available in current environment.
	 */
	var isBrowser = typeof window !== 'undefined' && typeof document !== 'undefined' && window.document === document;

	// Returns global object of a current environment.
	var global$1 = (function () {
		if (typeof global !== 'undefined' && global.Math === Math) {
			return global;
		}
		if (typeof self !== 'undefined' && self.Math === Math) {
			return self;
		}
		if (typeof window !== 'undefined' && window.Math === Math) {
			return window;
		}
		// eslint-disable-next-line no-new-func
		return Function('return this')();
	})();

	/**
	 * A shim for the requestAnimationFrame which falls back to the setTimeout if
	 * first one is not supported.
	 *
	 * @returns {number} Requests' identifier.
	 */
	var requestAnimationFrame$1 = (function () {
		if (typeof requestAnimationFrame === 'function') {
			// It's required to use a bounded function because IE sometimes throws
			// an "Invalid calling object" error if rAF is invoked without the global
			// object on the left hand side.
			return requestAnimationFrame.bind(global$1);
		}
		return function (callback) { return setTimeout(function () { return callback(Date.now()); }, 1000 / 60); };
	})();

	// Defines minimum timeout before adding a trailing call.
	var trailingTimeout = 2;
	/**
	 * Creates a wrapper function which ensures that provided callback will be
	 * invoked only once during the specified delay period.
	 *
	 * @param {Function} callback - Function to be invoked after the delay period.
	 * @param {number} delay - Delay after which to invoke callback.
	 * @returns {Function}
	 */
	function throttle (callback, delay) {
		var leadingCall = false, trailingCall = false, lastCallTime = 0;
		/**
		 * Invokes the original callback function and schedules new invocation if
		 * the "proxy" was called during current request.
		 *
		 * @returns {void}
		 */
		function resolvePending() {
			if (leadingCall) {
				leadingCall = false;
				callback();
			}
			if (trailingCall) {
				proxy();
			}
		}
		/**
		 * Callback invoked after the specified delay. It will further postpone
		 * invocation of the original function delegating it to the
		 * requestAnimationFrame.
		 *
		 * @returns {void}
		 */
		function timeoutCallback() {
			requestAnimationFrame$1(resolvePending);
		}
		/**
		 * Schedules invocation of the original function.
		 *
		 * @returns {void}
		 */
		function proxy() {
			var timeStamp = Date.now();
			if (leadingCall) {
				// Reject immediately following calls.
				if (timeStamp - lastCallTime < trailingTimeout) {
					return;
				}
				// Schedule new call to be in invoked when the pending one is resolved.
				// This is important for "transitions" which never actually start
				// immediately so there is a chance that we might miss one if change
				// happens amids the pending invocation.
				trailingCall = true;
			}
			else {
				leadingCall = true;
				trailingCall = false;
				setTimeout(timeoutCallback, delay);
			}
			lastCallTime = timeStamp;
		}
		return proxy;
	}

	// Minimum delay before invoking the update of observers.
	var REFRESH_DELAY = 20;
	// A list of substrings of CSS properties used to find transition events that
	// might affect dimensions of observed elements.
	var transitionKeys = ['top', 'right', 'bottom', 'left', 'width', 'height', 'size', 'weight'];
	// Check if MutationObserver is available.
	var mutationObserverSupported = typeof MutationObserver !== 'undefined';
	/**
	 * Singleton controller class which handles updates of ResizeObserver instances.
	 */
	var ResizeObserverController = /** @class */ (function () {
		/**
		 * Creates a new instance of ResizeObserverController.
		 *
		 * @private
		 */
		function ResizeObserverController() {
			/**
			 * Indicates whether DOM listeners have been added.
			 *
			 * @private {boolean}
			 */
			this.connected_ = false;
			/**
			 * Tells that controller has subscribed for Mutation Events.
			 *
			 * @private {boolean}
			 */
			this.mutationEventsAdded_ = false;
			/**
			 * Keeps reference to the instance of MutationObserver.
			 *
			 * @private {MutationObserver}
			 */
			this.mutationsObserver_ = null;
			/**
			 * A list of connected observers.
			 *
			 * @private {Array<ResizeObserverSPI>}
			 */
			this.observers_ = [];
			this.onTransitionEnd_ = this.onTransitionEnd_.bind(this);
			this.refresh = throttle(this.refresh.bind(this), REFRESH_DELAY);
		}
		/**
		 * Adds observer to observers list.
		 *
		 * @param {ResizeObserverSPI} observer - Observer to be added.
		 * @returns {void}
		 */
		ResizeObserverController.prototype.addObserver = function (observer) {
			if (!~this.observers_.indexOf(observer)) {
				this.observers_.push(observer);
			}
			// Add listeners if they haven't been added yet.
			if (!this.connected_) {
				this.connect_();
			}
		};
		/**
		 * Removes observer from observers list.
		 *
		 * @param {ResizeObserverSPI} observer - Observer to be removed.
		 * @returns {void}
		 */
		ResizeObserverController.prototype.removeObserver = function (observer) {
			var observers = this.observers_;
			var index = observers.indexOf(observer);
			// Remove observer if it's present in registry.
			if (~index) {
				observers.splice(index, 1);
			}
			// Remove listeners if controller has no connected observers.
			if (!observers.length && this.connected_) {
				this.disconnect_();
			}
		};
		/**
		 * Invokes the update of observers. It will continue running updates insofar
		 * it detects changes.
		 *
		 * @returns {void}
		 */
		ResizeObserverController.prototype.refresh = function () {
			var changesDetected = this.updateObservers_();
			// Continue running updates if changes have been detected as there might
			// be future ones caused by CSS transitions.
			if (changesDetected) {
				this.refresh();
			}
		};
		/**
		 * Updates every observer from observers list and notifies them of queued
		 * entries.
		 *
		 * @private
		 * @returns {boolean} Returns "true" if any observer has detected changes in
		 *      dimensions of it's elements.
		 */
		ResizeObserverController.prototype.updateObservers_ = function () {
			// Collect observers that have active observations.
			var activeObservers = this.observers_.filter(function (observer) {
				return observer.gatherActive(), observer.hasActive();
			});
			// Deliver notifications in a separate cycle in order to avoid any
			// collisions between observers, e.g. when multiple instances of
			// ResizeObserver are tracking the same element and the callback of one
			// of them changes content dimensions of the observed target. Sometimes
			// this may result in notifications being blocked for the rest of observers.
			activeObservers.forEach(function (observer) { return observer.broadcastActive(); });
			return activeObservers.length > 0;
		};
		/**
		 * Initializes DOM listeners.
		 *
		 * @private
		 * @returns {void}
		 */
		ResizeObserverController.prototype.connect_ = function () {
			// Do nothing if running in a non-browser environment or if listeners
			// have been already added.
			if (!isBrowser || this.connected_) {
				return;
			}
			// Subscription to the "Transitionend" event is used as a workaround for
			// delayed transitions. This way it's possible to capture at least the
			// final state of an element.
			document.addEventListener('transitionend', this.onTransitionEnd_);
			window.addEventListener('resize', this.refresh);
			if (mutationObserverSupported) {
				this.mutationsObserver_ = new MutationObserver(this.refresh);
				this.mutationsObserver_.observe(document, {
					attributes: true,
					childList: true,
					characterData: true,
					subtree: true
				});
			}
			else {
				document.addEventListener('DOMSubtreeModified', this.refresh);
				this.mutationEventsAdded_ = true;
			}
			this.connected_ = true;
		};
		/**
		 * Removes DOM listeners.
		 *
		 * @private
		 * @returns {void}
		 */
		ResizeObserverController.prototype.disconnect_ = function () {
			// Do nothing if running in a non-browser environment or if listeners
			// have been already removed.
			if (!isBrowser || !this.connected_) {
				return;
			}
			document.removeEventListener('transitionend', this.onTransitionEnd_);
			window.removeEventListener('resize', this.refresh);
			if (this.mutationsObserver_) {
				this.mutationsObserver_.disconnect();
			}
			if (this.mutationEventsAdded_) {
				document.removeEventListener('DOMSubtreeModified', this.refresh);
			}
			this.mutationsObserver_ = null;
			this.mutationEventsAdded_ = false;
			this.connected_ = false;
		};
		/**
		 * "Transitionend" event handler.
		 *
		 * @private
		 * @param {TransitionEvent} event
		 * @returns {void}
		 */
		ResizeObserverController.prototype.onTransitionEnd_ = function (_a) {
			var _b = _a.propertyName, propertyName = _b === void 0 ? '' : _b;
			// Detect whether transition may affect dimensions of an element.
			var isReflowProperty = transitionKeys.some(function (key) {
				return !!~propertyName.indexOf(key);
			});
			if (isReflowProperty) {
				this.refresh();
			}
		};
		/**
		 * Returns instance of the ResizeObserverController.
		 *
		 * @returns {ResizeObserverController}
		 */
		ResizeObserverController.getInstance = function () {
			if (!this.instance_) {
				this.instance_ = new ResizeObserverController();
			}
			return this.instance_;
		};
		/**
		 * Holds reference to the controller's instance.
		 *
		 * @private {ResizeObserverController}
		 */
		ResizeObserverController.instance_ = null;
		return ResizeObserverController;
	}());

	/**
	 * Defines non-writable/enumerable properties of the provided target object.
	 *
	 * @param {Object} target - Object for which to define properties.
	 * @param {Object} props - Properties to be defined.
	 * @returns {Object} Target object.
	 */
	var defineConfigurable = (function (target, props) {
		for (var _i = 0, _a = Object.keys(props); _i < _a.length; _i++) {
			var key = _a[_i];
			Object.defineProperty(target, key, {
				value: props[key],
				enumerable: false,
				writable: false,
				configurable: true
			});
		}
		return target;
	});

	/**
	 * Returns the global object associated with provided element.
	 *
	 * @param {Object} target
	 * @returns {Object}
	 */
	var getWindowOf = (function (target) {
		// Assume that the element is an instance of Node, which means that it
		// has the "ownerDocument" property from which we can retrieve a
		// corresponding global object.
		var ownerGlobal = target && target.ownerDocument && target.ownerDocument.defaultView;
		// Return the local global object if it's not possible extract one from
		// provided element.
		return ownerGlobal || global$1;
	});

	// Placeholder of an empty content rectangle.
	var emptyRect = createRectInit(0, 0, 0, 0);
	/**
	 * Converts provided string to a number.
	 *
	 * @param {number|string} value
	 * @returns {number}
	 */
	function toFloat(value) {
		return parseFloat(value) || 0;
	}
	/**
	 * Extracts borders size from provided styles.
	 *
	 * @param {CSSStyleDeclaration} styles
	 * @param {...string} positions - Borders positions (top, right, ...)
	 * @returns {number}
	 */
	function getBordersSize(styles) {
		var positions = [];
		for (var _i = 1; _i < arguments.length; _i++) {
			positions[_i - 1] = arguments[_i];
		}
		return positions.reduce(function (size, position) {
			var value = styles['border-' + position + '-width'];
			return size + toFloat(value);
		}, 0);
	}
	/**
	 * Extracts paddings sizes from provided styles.
	 *
	 * @param {CSSStyleDeclaration} styles
	 * @returns {Object} Paddings box.
	 */
	function getPaddings(styles) {
		var positions = ['top', 'right', 'bottom', 'left'];
		var paddings = {};
		for (var _i = 0, positions_1 = positions; _i < positions_1.length; _i++) {
			var position = positions_1[_i];
			var value = styles['padding-' + position];
			paddings[position] = toFloat(value);
		}
		return paddings;
	}
	/**
	 * Calculates content rectangle of provided SVG element.
	 *
	 * @param {SVGGraphicsElement} target - Element content rectangle of which needs
	 *      to be calculated.
	 * @returns {DOMRectInit}
	 */
	function getSVGContentRect(target) {
		var bbox = target.getBBox();
		return createRectInit(0, 0, bbox.width, bbox.height);
	}
	/**
	 * Calculates content rectangle of provided HTMLElement.
	 *
	 * @param {HTMLElement} target - Element for which to calculate the content rectangle.
	 * @returns {DOMRectInit}
	 */
	function getHTMLElementContentRect(target) {
		// Client width & height properties can't be
		// used exclusively as they provide rounded values.
		var clientWidth = target.clientWidth, clientHeight = target.clientHeight;
		// By this condition we can catch all non-replaced inline, hidden and
		// detached elements. Though elements with width & height properties less
		// than 0.5 will be discarded as well.
		//
		// Without it we would need to implement separate methods for each of
		// those cases and it's not possible to perform a precise and performance
		// effective test for hidden elements. E.g. even jQuery's ':visible' filter
		// gives wrong results for elements with width & height less than 0.5.
		if (!clientWidth && !clientHeight) {
			return emptyRect;
		}
		var styles = getWindowOf(target).getComputedStyle(target);
		var paddings = getPaddings(styles);
		var horizPad = paddings.left + paddings.right;
		var vertPad = paddings.top + paddings.bottom;
		// Computed styles of width & height are being used because they are the
		// only dimensions available to JS that contain non-rounded values. It could
		// be possible to utilize the getBoundingClientRect if only it's data wasn't
		// affected by CSS transformations let alone paddings, borders and scroll bars.
		var width = toFloat(styles.width), height = toFloat(styles.height);
		// Width & height include paddings and borders when the 'border-box' box
		// model is applied (except for IE).
		if (styles.boxSizing === 'border-box') {
			// Following conditions are required to handle Internet Explorer which
			// doesn't include paddings and borders to computed CSS dimensions.
			//
			// We can say that if CSS dimensions + paddings are equal to the "client"
			// properties then it's either IE, and thus we don't need to subtract
			// anything, or an element merely doesn't have paddings/borders styles.
			if (Math.round(width + horizPad) !== clientWidth) {
				width -= getBordersSize(styles, 'left', 'right') + horizPad;
			}
			if (Math.round(height + vertPad) !== clientHeight) {
				height -= getBordersSize(styles, 'top', 'bottom') + vertPad;
			}
		}
		// Following steps can't be applied to the document's root element as its
		// client[Width/Height] properties represent viewport area of the window.
		// Besides, it's as well not necessary as the <html> itself neither has
		// rendered scroll bars nor it can be clipped.
		if (!isDocumentElement(target)) {
			// In some browsers (only in Firefox, actually) CSS width & height
			// include scroll bars size which can be removed at this step as scroll
			// bars are the only difference between rounded dimensions + paddings
			// and "client" properties, though that is not always true in Chrome.
			var vertScrollbar = Math.round(width + horizPad) - clientWidth;
			var horizScrollbar = Math.round(height + vertPad) - clientHeight;
			// Chrome has a rather weird rounding of "client" properties.
			// E.g. for an element with content width of 314.2px it sometimes gives
			// the client width of 315px and for the width of 314.7px it may give
			// 314px. And it doesn't happen all the time. So just ignore this delta
			// as a non-relevant.
			if (Math.abs(vertScrollbar) !== 1) {
				width -= vertScrollbar;
			}
			if (Math.abs(horizScrollbar) !== 1) {
				height -= horizScrollbar;
			}
		}
		return createRectInit(paddings.left, paddings.top, width, height);
	}
	/**
	 * Checks whether provided element is an instance of the SVGGraphicsElement.
	 *
	 * @param {Element} target - Element to be checked.
	 * @returns {boolean}
	 */
	var isSVGGraphicsElement = (function () {
		// Some browsers, namely IE and Edge, don't have the SVGGraphicsElement
		// interface.
		if (typeof SVGGraphicsElement !== 'undefined') {
			return function (target) { return target instanceof getWindowOf(target).SVGGraphicsElement; };
		}
		// If it's so, then check that element is at least an instance of the
		// SVGElement and that it has the "getBBox" method.
		// eslint-disable-next-line no-extra-parens
		return function (target) { return (target instanceof getWindowOf(target).SVGElement &&
		typeof target.getBBox === 'function'); };
	})();
	/**
	 * Checks whether provided element is a document element (<html>).
	 *
	 * @param {Element} target - Element to be checked.
	 * @returns {boolean}
	 */
	function isDocumentElement(target) {
		return target === getWindowOf(target).document.documentElement;
	}
	/**
	 * Calculates an appropriate content rectangle for provided html or svg element.
	 *
	 * @param {Element} target - Element content rectangle of which needs to be calculated.
	 * @returns {DOMRectInit}
	 */
	function getContentRect(target) {
		if (!isBrowser) {
			return emptyRect;
		}
		if (isSVGGraphicsElement(target)) {
			return getSVGContentRect(target);
		}
		return getHTMLElementContentRect(target);
	}
	/**
	 * Creates rectangle with an interface of the DOMRectReadOnly.
	 * Spec: https://drafts.fxtf.org/geometry/#domrectreadonly
	 *
	 * @param {DOMRectInit} rectInit - Object with rectangle's x/y coordinates and dimensions.
	 * @returns {DOMRectReadOnly}
	 */
	function createReadOnlyRect(_a) {
		var x = _a.x, y = _a.y, width = _a.width, height = _a.height;
		// If DOMRectReadOnly is available use it as a prototype for the rectangle.
		var Constr = typeof DOMRectReadOnly !== 'undefined' ? DOMRectReadOnly : Object;
		var rect = Object.create(Constr.prototype);
		// Rectangle's properties are not writable and non-enumerable.
		defineConfigurable(rect, {
			x: x, y: y, width: width, height: height,
			top: y,
			right: x + width,
			bottom: height + y,
			left: x
		});
		return rect;
	}
	/**
	 * Creates DOMRectInit object based on the provided dimensions and the x/y coordinates.
	 * Spec: https://drafts.fxtf.org/geometry/#dictdef-domrectinit
	 *
	 * @param {number} x - X coordinate.
	 * @param {number} y - Y coordinate.
	 * @param {number} width - Rectangle's width.
	 * @param {number} height - Rectangle's height.
	 * @returns {DOMRectInit}
	 */
	function createRectInit(x, y, width, height) {
		return { x: x, y: y, width: width, height: height };
	}

	/**
	 * Class that is responsible for computations of the content rectangle of
	 * provided DOM element and for keeping track of it's changes.
	 */
	var ResizeObservation = /** @class */ (function () {
		/**
		 * Creates an instance of ResizeObservation.
		 *
		 * @param {Element} target - Element to be observed.
		 */
		function ResizeObservation(target) {
			/**
			 * Broadcasted width of content rectangle.
			 *
			 * @type {number}
			 */
			this.broadcastWidth = 0;
			/**
			 * Broadcasted height of content rectangle.
			 *
			 * @type {number}
			 */
			this.broadcastHeight = 0;
			/**
			 * Reference to the last observed content rectangle.
			 *
			 * @private {DOMRectInit}
			 */
			this.contentRect_ = createRectInit(0, 0, 0, 0);
			this.target = target;
		}
		/**
		 * Updates content rectangle and tells whether it's width or height properties
		 * have changed since the last broadcast.
		 *
		 * @returns {boolean}
		 */
		ResizeObservation.prototype.isActive = function () {
			var rect = getContentRect(this.target);
			this.contentRect_ = rect;
			return (rect.width !== this.broadcastWidth ||
			rect.height !== this.broadcastHeight);
		};
		/**
		 * Updates 'broadcastWidth' and 'broadcastHeight' properties with a data
		 * from the corresponding properties of the last observed content rectangle.
		 *
		 * @returns {DOMRectInit} Last observed content rectangle.
		 */
		ResizeObservation.prototype.broadcastRect = function () {
			var rect = this.contentRect_;
			this.broadcastWidth = rect.width;
			this.broadcastHeight = rect.height;
			return rect;
		};
		return ResizeObservation;
	}());

	var ResizeObserverEntry = /** @class */ (function () {
		/**
		 * Creates an instance of ResizeObserverEntry.
		 *
		 * @param {Element} target - Element that is being observed.
		 * @param {DOMRectInit} rectInit - Data of the element's content rectangle.
		 */
		function ResizeObserverEntry(target, rectInit) {
			var contentRect = createReadOnlyRect(rectInit);
			// According to the specification following properties are not writable
			// and are also not enumerable in the native implementation.
			//
			// Property accessors are not being used as they'd require to define a
			// private WeakMap storage which may cause memory leaks in browsers that
			// don't support this type of collections.
			defineConfigurable(this, { target: target, contentRect: contentRect });
		}
		return ResizeObserverEntry;
	}());

	var ResizeObserverSPI = /** @class */ (function () {
		/**
		 * Creates a new instance of ResizeObserver.
		 *
		 * @param {ResizeObserverCallback} callback - Callback function that is invoked
		 *      when one of the observed elements changes it's content dimensions.
		 * @param {ResizeObserverController} controller - Controller instance which
		 *      is responsible for the updates of observer.
		 * @param {ResizeObserver} callbackCtx - Reference to the public
		 *      ResizeObserver instance which will be passed to callback function.
		 */
		function ResizeObserverSPI(callback, controller, callbackCtx) {
			/**
			 * Collection of resize observations that have detected changes in dimensions
			 * of elements.
			 *
			 * @private {Array<ResizeObservation>}
			 */
			this.activeObservations_ = [];
			/**
			 * Registry of the ResizeObservation instances.
			 *
			 * @private {Map<Element, ResizeObservation>}
			 */
			this.observations_ = new MapShim();
			if (typeof callback !== 'function') {
				throw new TypeError('The callback provided as parameter 1 is not a function.');
			}
			this.callback_ = callback;
			this.controller_ = controller;
			this.callbackCtx_ = callbackCtx;
		}
		/**
		 * Starts observing provided element.
		 *
		 * @param {Element} target - Element to be observed.
		 * @returns {void}
		 */
		ResizeObserverSPI.prototype.observe = function (target) {
			if (!arguments.length) {
				throw new TypeError('1 argument required, but only 0 present.');
			}
			// Do nothing if current environment doesn't have the Element interface.
			if (typeof Element === 'undefined' || !(Element instanceof Object)) {
				return;
			}
			if (!(target instanceof getWindowOf(target).Element)) {
				throw new TypeError('parameter 1 is not of type "Element".');
			}
			var observations = this.observations_;
			// Do nothing if element is already being observed.
			if (observations.has(target)) {
				return;
			}
			observations.set(target, new ResizeObservation(target));
			this.controller_.addObserver(this);
			// Force the update of observations.
			this.controller_.refresh();
		};
		/**
		 * Stops observing provided element.
		 *
		 * @param {Element} target - Element to stop observing.
		 * @returns {void}
		 */
		ResizeObserverSPI.prototype.unobserve = function (target) {
			if (!arguments.length) {
				throw new TypeError('1 argument required, but only 0 present.');
			}
			// Do nothing if current environment doesn't have the Element interface.
			if (typeof Element === 'undefined' || !(Element instanceof Object)) {
				return;
			}
			if (!(target instanceof getWindowOf(target).Element)) {
				throw new TypeError('parameter 1 is not of type "Element".');
			}
			var observations = this.observations_;
			// Do nothing if element is not being observed.
			if (!observations.has(target)) {
				return;
			}
			observations.delete(target);
			if (!observations.size) {
				this.controller_.removeObserver(this);
			}
		};
		/**
		 * Stops observing all elements.
		 *
		 * @returns {void}
		 */
		ResizeObserverSPI.prototype.disconnect = function () {
			this.clearActive();
			this.observations_.clear();
			this.controller_.removeObserver(this);
		};
		/**
		 * Collects observation instances the associated element of which has changed
		 * it's content rectangle.
		 *
		 * @returns {void}
		 */
		ResizeObserverSPI.prototype.gatherActive = function () {
			var _this = this;
			this.clearActive();
			this.observations_.forEach(function (observation) {
				if (observation.isActive()) {
					_this.activeObservations_.push(observation);
				}
			});
		};
		/**
		 * Invokes initial callback function with a list of ResizeObserverEntry
		 * instances collected from active resize observations.
		 *
		 * @returns {void}
		 */
		ResizeObserverSPI.prototype.broadcastActive = function () {
			// Do nothing if observer doesn't have active observations.
			if (!this.hasActive()) {
				return;
			}
			var ctx = this.callbackCtx_;
			// Create ResizeObserverEntry instance for every active observation.
			var entries = this.activeObservations_.map(function (observation) {
				return new ResizeObserverEntry(observation.target, observation.broadcastRect());
			});
			this.callback_.call(ctx, entries, ctx);
			this.clearActive();
		};
		/**
		 * Clears the collection of active observations.
		 *
		 * @returns {void}
		 */
		ResizeObserverSPI.prototype.clearActive = function () {
			this.activeObservations_.splice(0);
		};
		/**
		 * Tells whether observer has active observations.
		 *
		 * @returns {boolean}
		 */
		ResizeObserverSPI.prototype.hasActive = function () {
			return this.activeObservations_.length > 0;
		};
		return ResizeObserverSPI;
	}());

	// Registry of internal observers. If WeakMap is not available use current shim
	// for the Map collection as it has all required methods and because WeakMap
	// can't be fully polyfilled anyway.
	var observers = typeof WeakMap !== 'undefined' ? new WeakMap() : new MapShim();
	/**
	 * ResizeObserver API. Encapsulates the ResizeObserver SPI implementation
	 * exposing only those methods and properties that are defined in the spec.
	 */
	var ResizeObserver = /** @class */ (function () {
		/**
		 * Creates a new instance of ResizeObserver.
		 *
		 * @param {ResizeObserverCallback} callback - Callback that is invoked when
		 *      dimensions of the observed elements change.
		 */
		function ResizeObserver(callback) {
			if (!(this instanceof ResizeObserver)) {
				throw new TypeError('Cannot call a class as a function.');
			}
			if (!arguments.length) {
				throw new TypeError('1 argument required, but only 0 present.');
			}
			var controller = ResizeObserverController.getInstance();
			var observer = new ResizeObserverSPI(callback, controller, this);
			observers.set(this, observer);
		}
		return ResizeObserver;
	}());
	// Expose public methods of ResizeObserver.
	[
		'observe',
		'unobserve',
		'disconnect'
	].forEach(function (method) {
		ResizeObserver.prototype[method] = function () {
			var _a;
			return (_a = observers.get(this))[method].apply(_a, arguments);
		};
	});

	var index = (function () {
		// Export existing implementation if available.
		if (typeof global$1.ResizeObserver !== 'undefined') {
			return global$1.ResizeObserver;
		}
		return ResizeObserver;
	})();

	return index;

})));
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
		configure: function(name, options, classes, il8n){
			var self = this;
			if (self.contains(name)){
				var reg = self.registered;
				_obj.extend(reg[name].opt, options);
				_obj.extend(reg[name].cls, classes);
				_obj.extend(reg[name].il8n, il8n);
			}
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
			self._arr = [];
		},
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
			self._arr.splice(0, self._arr.length);
			$.each(self.ctrls.splice(0, self.ctrls.length), function (i, control) {
				control.destroy();
			});
			self._super();
		},
		build: function () {
			var self = this, items = self.tmpl.items.available();
			self.total = self.size > 0 && items.length > 0 ? Math.ceil(items.length / self.size) : 1;
			for (var i = 0; i < self.total; i++) {
				self._arr.push(items.splice(0, self.size));
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
			self._arr.splice(0, self._arr.length);
			$.each(self.ctrls.splice(0, self.ctrls.length), function (i, control) {
				control.destroy();
			});
			self.build();
		},
		all: function () {
			return this._arr.slice();
		},
		available: function () {
			return this.get(this.current);
		},
		items: function(){
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

			var pageIndex = pageNumber - 1, pageItems = self._arr[pageIndex], detach;
			if (isFilter){
				detach = self.tmpl.items.all();
			} else {
				detach = self._arr.reduce(function(detach, page, index){
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
				return self._arr[pageNumber - 1];
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
						if (self.scrollToTop && _is.boolean(scroll) ? scroll : false) {
							var page = self.get(self.current);
							if (page.length > 0) {
								page[0].scrollTo("top");
							}
						}
						self.tmpl.raise("after-page-change", [self.current, prev, isFilter]);
					};
					var e = self.tmpl.raise("before-page-change", [self.current, num, setPage, isFilter]);
					if (e.isDefaultPrevented()) return false;
					setPage();
					return true;
				}
			}
			return false;
		},
		find: function (item) {
			var self = this;
			for (var i = 0, l = self._arr.length; i < l; i++) {
				if (_utils.inArray(item, self._arr[i]) !== -1) {
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
			this.goto(this._arr.length);
		},
		prev: function () {
			this.goto(this.current - 1);
		},
		next: function () {
			this.goto(this.current + 1);
		},
		goto: function (pageNumber) {
			var self = this;
			if (self.set(pageNumber, true)) {
				self.tmpl.loadAvailable();
			}
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
			 * @type {?HTMLStyleElement}
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
		/**
		 * @summary Creates or gets the CSS stylesheet element for this template instance.
		 * @memberof FooGallery.MasonryTemplate#
		 * @function getStylesheet
		 * @returns {CSSStyleSheet}
		 */
		getStylesheet: function(){
			var self = this;
			if (self.style === null){
				self.style = document.createElement("style");
				self.style.appendChild(document.createTextNode(""));
				document.head.appendChild(self.style);
			}
			return self.style.sheet;
		},
		delayedLayout: function(){
			var self = this;
			if (self._delayedLayout) clearTimeout(self._delayedLayout);
			self._delayedLayout = setTimeout(function () {
				self._delayedLayout = null;
				self.masonry.layout();
			}, 20);
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
			// check if the layout is supplied as a CSS class
			var layouts = $.map(cls.layout, function(value, key){
				return {key: key, value: value};
			});
			for (var i =0, l = layouts.length; i < l; i++){
				if (self.$el.hasClass(layouts[i].value)){
					self.template.layout = layouts[i].key;
					break;
				}
			}
			// check if the supplied layout is supported
			if (!_is.string(cls.layout[self.template.layout])){
				// if not set the default
				self.template.layout = "col4";
			}
			// configure the base masonry options depending on the layout
			var fixed = self.template.layout === "fixed", sheet, rule;
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
			if (fixed && _is.number(self.template.columnWidth)){
				var $columnWidth = self.$el.find(sel.columnWidth).width(self.template.columnWidth);
				sheet = self.getStylesheet();
				rule = '#' + self.id + sel.container + ' ' + sel.item.elem + ' { width: ' + $columnWidth.outerWidth() + 'px; }';
				sheet.insertRule(rule , 0);
			}
			self.template.columnWidth = sel.columnWidth;

			// if this is a fixed layout and a number value is supplied as the gutter option then
			// make sure to vertically space the items using  a CSS class and the same value
			if (fixed && _is.number(self.template.gutter)){
				sheet = self.getStylesheet();
				rule = '#' + self.id + sel.container + ' ' + sel.item.elem + ' { margin-bottom: ' + self.template.gutter + 'px; }';
				sheet.insertRule(rule , 0);
			}
			self.masonry = new Masonry( self.$el.get(0), self.template );
		},
		onPostInit: function(event, self){
			self.masonry.layout();
		},
		onFirstLoad: function(event, self){
			self.masonry.layout();
		},
		onReady: function(event, self){
			self.delayedLayout();
		},
		onDestroy: function(event, self){
			if (self._delayedLayout) clearTimeout(self._delayedLayout);
			self.$el.find(self.sel.columnWidth).remove();
			self.$el.find(self.sel.gutterWidth).remove();
			if (self.style && self.style.parentNode){
				self.style.parentNode.removeChild(self.style);
			}
		},
		onDestroyed: function(event, self){
			if (self.masonry instanceof Masonry){
				self.masonry.destroy();
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
		fixLayout: true,
		template: {
			initLayout: false,
			isInitLayout: false,
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
		construct: function(template, options){
			this.tmpl = template;
			this.$el = template.$el;
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
		},
		destroy: function(){
			this.$el.removeAttr("style");
		},
		parse: function(){
			var self = this;
			return self._items = $.map(self.tmpl.getItems(), function(item, i){
				return {
					index: i,
					width: item.width,
					height: item.height,
					top: 0,
					left: 0,
					$item: item.$el
				};
			});
		},
		getMaxRowHeight: function() {
			var self = this;
			if (_is.string(self.options.maxRowHeight)){
				if (self.options.maxRowHeight.indexOf('%')){
					self.options.maxRowHeight = self.options.rowHeight * (parseInt(self.options.maxRowHeight) / 100);
				} else {
					self.options.maxRowHeight = parseInt(self.options.maxRowHeight);
				}
			}
			return _is.number(self.options.maxRowHeight) ? self.options.maxRowHeight : self.options.rowHeight;
		},
		getContainerWidth: function(){
			var self = this, visible = self.$el.is(':visible');
			if (!visible){
				return self.$el.parents(':visible:first').innerWidth();
			}
			return self.$el.width();
		},
		layout: function(){
			this.parse();

			var self = this,
				height = 0,
				maxWidth = self.getContainerWidth(),
				maxHeight = self.getMaxRowHeight(),
				rows = self.rows(maxWidth, maxHeight);

			$.each(rows, function(ri, row){
				if (row.visible){
					if (ri > 0) height += self.options.margins;
					height += row.height;
				}
				self.render(row);
			});
			self.$el.height(height);
		},
		render: function(row){
			for (var j = 0, jl = row.items.length, item; j < jl; j++){
				item = row.items[j];
				if (row.visible){
					item.$item.css({
						position: "absolute",
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
		justify: function(row, top, maxWidth, maxHeight){
			var self = this,
					margins = self.options.margins * (row.items.length - 1),
					max = maxWidth - margins;

			var w_ratio = max / row.width;
			row.width = row.width * w_ratio;
			row.height = row.height * w_ratio;
			row.top = top;

			if (row.height > maxHeight){
				row.height = maxHeight;
			}

			row.left = 0;
			if (row.width < max){
				// here I'm not sure if I should center, left or right align a row that cannot be displayed at 100% width
				row.left = (max - row.width) / 2;
			}
			row.width += margins;

			var left = row.left;
			for (var i = 0, l = row.items.length, item; i < l; i++){
				if (i > 0) left += self.options.margins;
				item = row.items[i];
				item.left = left;
				item.top = top;
				item.width = item.width * w_ratio;
				item.height = item.height * w_ratio;
				if (item.height > maxHeight){
					item.height = maxHeight;
				}
				left += item.width;
			}

			return row.height;
		},
		position: function(row, top, maxWidth, align){
			var self = this,
					margins = self.options.margins * (row.items.length - 1),
					max = maxWidth - margins;

			row.top = top;
			row.left = 0;
			if (row.width < max){
				switch (align){
					case "center":
						row.left = (max - row.width) / 2;
						break;
					case "right":
						row.left = max - row.width;
						break;
				}
			}
			row.width += margins;

			var left = row.left;
			for (var i = 0, l = row.items.length, item; i < l; i++){
				if (i > 0) left += self.options.margins;
				item = row.items[i];
				item.left = left;
				item.top = top;
				left += item.width;
			}

			return row.height;
		},
		lastRow: function(row, top, maxWidth, maxHeight){
			var self = this,
					margins = self.options.margins * (row.items.length - 1),
					max = maxWidth - margins,
					threshold = row.width / max > self.options.justifyThreshold;

			switch (self.options.lastRow){
				case "hide":
					if (threshold){
						self.justify(row, top, maxWidth, maxHeight);
					} else {
						row.visible = false;
					}
					break;
				case "justify":
					self.justify(row, top, maxWidth, maxHeight);
					break;
				case "nojustify":
					if (threshold){
						self.justify(row, top, maxWidth, maxHeight);
					} else {
						self.position(row, top, maxWidth, "left");
					}
					break;
				case "left":
				case "center":
				case "right":
					if (threshold){
						self.justify(row, top, maxWidth, maxHeight);
					} else {
						self.position(row, top, maxWidth, self.options.lastRow);
					}
					break;
			}
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
		rows: function(maxWidth, maxHeight){
			var self = this,
					items = self.items(),
					rows = [],
					index = -1;

			function create(){
				var row = {
					index: ++index,
					visible: true,
					width: 0,
					height: self.options.rowHeight,
					top: 0,
					left: 0,
					items: []
				};
				// push the row into the result collection now
				rows.push(row);
				return row;
			}

			var row = create(), top = 0, tmp = 0;
			for (var i = 0, il = items.length, item; i < il; i++){
				item = items[i];
				// first make all the items match the row height
				if (item.height !== self.options.rowHeight){
					var ratio = self.options.rowHeight / item.height;
					item.height = item.height * ratio;
					item.width = item.width * ratio;
				}

				if (tmp + item.width > maxWidth && i > 0){
					// adding this item to the row would exceed the max width
					if (rows.length > 1) top += self.options.margins;
					top += self.justify(row, top, maxWidth, maxHeight); // first justify the current row
					row = create(); // then make the new one
					tmp = 0;
				}

				if (row.items.length > 0) tmp += self.options.margins;
				tmp += item.width;
				row.width += item.width;
				row.items.push(item);
			}
			if (rows.length > 1) top += self.options.margins;
			self.lastRow(row, top, maxWidth, maxHeight);
			return rows;
		}
	});

	_.Justified.defaults = {
		itemSelector: ".fg-item",
		rowHeight: 150,
		maxRowHeight: "200%",
		margins: 0,
		lastRow: "center",
		justifyThreshold: 1,
		refreshInterval: 250
	};

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
);
(function($, _){

	_.JustifiedTemplate = _.Template.extend({
		onPreInit: function(event, self){
			self.justified = new _.Justified( self, self.template );
		},
		onInit: function(event, self){
			self.justified.init();
		},
		onFirstLoad: function(event, self){
			self.justified.layout();
		},
		onReady: function(event, self){
			self.justified.layout();
		},
		onDestroy: function(event, self){
			self.justified.destroy();
		},
		onLayout: function(event, self){
			self.justified.layout();
		},
		onAfterPageChange: function(event, self, current, prev, isFilter){
			if (!isFilter){
				self.justified.layout();
			}
		},
		onAfterFilterChange: function(event, self){
			self.justified.layout();
		}
	});

	_.template.register("justified", _.JustifiedTemplate, null, {
		container: "foogallery fg-justified"
	});

})(
	FooGallery.$,
	FooGallery
);
(function($, _){

	_.JustifiedCSSTemplate = _.Template.extend({});

	_.template.register("justified-css", _.JustifiedCSSTemplate, null, {
		container: "foogallery fg-justified-css"
	});

})(
	FooGallery.$,
	FooGallery
);
(function($, _, _utils, _is, _fn){

	// _.PortfolioTemplate = _.Template.extend({
	// 	construct: function(element, options){
	// 		this._super(element, options);
	// 		/**
	// 		 *
	// 		 * @type {?HTMLStyleElement}
	// 		 */
	// 		this.style = null;
	//
	// 		this.fullWidth = false;
	// 	},
	// 	/**
	// 	 * @summary Creates or gets the CSS stylesheet element for this template instance.
	// 	 * @memberof FooGallery.MasonryTemplate#
	// 	 * @function getStylesheet
	// 	 * @returns {StyleSheet}
	// 	 */
	// 	getStylesheet: function(){
	// 		var self = this;
	// 		if (self.style === null){
	// 			self.style = document.createElement("style");
	// 			self.style.appendChild(document.createTextNode(""));
	// 			document.head.appendChild(self.style);
	// 		}
	// 		return self.style.sheet;
	// 	},
	// 	onPreInit: function(event, self){
	// 		self.appendCSS();
	// 	},
	// 	onPostInit: function(event, self){
	// 		self.checkCSS();
	// 	},
	// 	onDestroy: function(event, self){
	// 		self.removeCSS();
	// 	},
	// 	onLayout: function(event, self){
	// 		self.checkCSS();
	// 	},
	// 	checkCSS: function(){
	// 		var self = this, maxWidth = self.getContainerWidth(), current = maxWidth < self.template.columnWidth;
	// 		if (current !== self.fullWidth){
	// 			self.appendCSS(maxWidth);
	// 		}
	// 	},
	// 	appendCSS: function(maxWidth){
	// 		var self = this;
	// 		maxWidth = _is.number(maxWidth) ? maxWidth : self.getContainerWidth();
	//
	// 		self.removeCSS();
	//
	// 		var sheet = self.getStylesheet(), rule,
	// 			container = '#' + self.id + self.sel.container,
	// 			item = container + ' ' + self.sel.item.elem,
	// 			width = self.template.columnWidth,
	// 			gutter = Math.ceil(self.template.gutter / 2);
	//
	// 		switch (self.template.align) {
	// 			case "center":
	// 				rule = container + ' { justify-content: center; }';
	// 				sheet.insertRule(rule , 0);
	// 				break;
	// 			case "left":
	// 				rule = container + ' { justify-content: flex-start; }';
	// 				sheet.insertRule(rule , 0);
	// 				break;
	// 			case "right":
	// 				rule = container + ' { justify-content: flex-end; }';
	// 				sheet.insertRule(rule , 0);
	// 				break;
	// 		}
	// 		self.fullWidth = maxWidth < width;
	// 		if (self.fullWidth){
	// 			rule = item + ' { max-width: 100%; min-width: 100%; margin: ' + gutter + 'px; }';
	// 			sheet.insertRule(rule , 0);
	// 		} else {
	// 			rule = item + ' { max-width: ' + width + 'px; min-width: ' + width + 'px; margin: ' + gutter + 'px; }';
	// 			sheet.insertRule(rule , 0);
	// 		}
	// 	},
	// 	removeCSS: function(){
	// 		var self = this;
	// 		if (self.style && self.style.parentNode){
	// 			self.style.parentNode.removeChild(self.style);
	// 			self.style = null;
	// 			self.fullWidth = false;
	// 		}
	// 	}
	// });

	_.PortfolioTemplate = _.Template.extend({});

	_.template.register("simple_portfolio", _.PortfolioTemplate, {
		template: {
			gutter: 40,
			align: "center",
			columnWidth: 250
		}
	}, {
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
			this.$inner = $();
			/**
			 * @summary The jQuery object that displays the current image count.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $current
			 * @type {jQuery}
			 */
			this.$current = $();
			/**
			 * @summary The jQuery object that displays the current image count.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $current
			 * @type {jQuery}
			 */
			this.$total = $();
			/**
			 * @summary The jQuery object for the previous button.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $prev
			 * @type {jQuery}
			 */
			this.$prev = $();
			/**
			 * @summary The jQuery object for the next button.
			 * @memberof FooGallery.ImageViewerTemplate#
			 * @name $next
			 * @type {jQuery}
			 */
			this.$next = $();
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
		onPreInit: function(event, self){
			self.$inner = self.$el.find(self.sel.innerContainer);
			self.$current = self.$el.find(self.sel.countCurrent);
			self.$total = self.$el.find(self.sel.countTotal);
			self.$prev = self.$el.find(self.sel.prev);
			self.$next = self.$el.find(self.sel.next);
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
			// item.fix();
			item.isAttached = true;
		},
		onAfterPageChange: function(event, self, current, prev, isFilter){
			if (!isFilter){
				self.update();
			}
		},
		onAfterFilterChange: function(event, self){
			self.update();
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
            self.robserver = new ResizeObserver(function () {
                if (!self.ignoreResize && self.$el.is(":visible")) self.layout(true);
            });
        },
        init: function(){
            var self = this;
            self.piles.forEach(function(pile){
                pile.init();
            });
            self.$back.on('click.foogallery', {self: self}, self.onBackClick);
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
        getLayoutInfo: function(){
            var self = this,
                space = self.opt.gutter + (self.opt.border*2);
            return {
                maxWidth: self.$el.width(),
                space: space,
                halfSpace: space/2,
                itemWidth: self.opt.itemWidth,
                itemHeight: self.opt.itemHeight,
                itemOuterWidth: self.opt.itemWidth + (self.opt.border*2),
                itemOuterHeight: self.opt.itemHeight + (self.opt.border*2),
                blockWidth: self.opt.itemWidth + space,
                blockHeight: self.opt.itemHeight + space,
                border: self.opt.border,
                doubleBorder: self.opt.border*2,
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
                self.$el.removeClass('fg-disable-transitions');
            }
        },
        layoutPiles: function(callback){
            var self = this,
                info = self.getLayoutInfo(),
                rowWidth = 0, rowCount = 1, width = 0;

            callback = _is.fn(callback) ? callback : function(){};

            self.piles.forEach(function(pile){
                var left = rowWidth;
                rowWidth += info.blockWidth;
                if (rowWidth > info.maxWidth){
                    left = 0;
                    rowWidth = info.blockWidth;
                    rowCount++;
                }
                var top = info.blockHeight * (rowCount - 1);
                callback(pile, top, left, info.blockWidth, info.blockHeight);
                pile.setPosition(top, left, info.blockWidth, info.blockHeight);
                // keep track of the max calculated width
                if (rowWidth > width) width = rowWidth;
            });
            return {
                width: width,
                height: info.blockHeight * rowCount
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
            self.$cover = $('<div/>', {'class': 'fg-pile-cover'}).append(
                $('<div/>', {'class': 'fg-pile-cover-content'}).append(
                    $('<span/>', {'class': 'fg-pile-cover-title', text: self.opt.title}),
                    $('<span/>', {'class': 'fg-pile-cover-count', text: self.items.length})
                )
            );
            self.top = 0;
            self.left = 0;
            self.isExpanded = false;
        },
        init: function(){
            var self = this,
                opt = self.album.opt,
                availableAngles = self.getAngles(opt.angleStep),
                currentAngle = opt.randomAngle ? self.randomAngle(availableAngles) : opt.angleStep;

            self.$cover.on('click.foogallery', {self: self}, self.onCoverClick);
            self.items.forEach(function(item, i){
                item.init();
                if (i > 3) return; // we only care about the first 4 items after init

                if (i === 0){
                    item.$el.addClass('fg-has-cover').append(self.$cover);
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
            var self = this,
                info = self.album.getLayoutInfo(),
                rowWidth = 0, rowCount = 1,
                isNew = false, width = 0;

            self.items.forEach(function(item){
                rowWidth += info.halfGutter;
                if (rowWidth > info.maxWidth){
                    rowWidth = info.halfGutter;
                    rowCount++;
                    isNew = true;
                }
                var left = rowWidth;
                rowWidth += info.itemOuterWidth + info.halfGutter;
                if (!isNew && rowWidth > info.maxWidth){
                    left = info.halfGutter;
                    rowWidth = info.blockWidth;
                    rowCount++;
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

        expand: function(){
            var self = this, size;
            self.$el.removeClass('fg-collapsed').addClass('fg-expanded');
            size = self.layout();
            self.setPosition(0, 0, size.width, size.height);
            self.isExpanded = true;
            return size;
        },
        collapse: function(){
            var self = this,
                info = self.album.getLayoutInfo();
            self.$el.removeClass('fg-expanded').addClass('fg-collapsed');
            self.items.forEach(function(item){
                item.setPosition(info.halfGutter, info.halfGutter, info.itemOuterWidth, info.itemOuterHeight);
            });
            var size = {
                width: info.blockWidth,
                height: info.blockHeight
            };
            self.setPosition(0, 0, size.width, size.height);
            self.isExpanded = false;
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
            self.$el = _is.jq(element) ? element : $(element);
            self.opt = _obj.extend({}, _.StackAlbum.Item.defaults, options, self.$el.data());
            self.$thumb = self.$el.find('.fg-pile-item-thumb');
            self.$image = self.$el.find('.fg-pile-item-image');
            self.isLoaded = false;
            self.isLoading = false;
            self._loading = null;
        },
        init: function(){

        },
        destroy: function(){

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
                self.$image.prop('src', self.$image.attr(self.opt.src));
            }).promise();
        }
    });

    _.StackAlbum.Item.defaults = {
        index: -1,
        src: 'data-src-fg'
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
(function ($, _, _utils, _obj, _is) {

	_.triggerPostLoad = function (e, tmpl, current, prev, isFilter) {
		if (e.type === "first-load" || (tmpl.initialized && ((e.type === "after-page-change" && !isFilter) || e.type === "after-filter-change"))) {
			try {
				// if the gallery is displayed within a FooBox do not trigger the post-load which would cause the lightbox to re-init
				if (tmpl.$el.parents(".fbx-item").length > 0) return;
				if (tmpl.$el.hasClass("fbx-instance") && !!window.FOOBOX && !!$.fn.foobox){
					tmpl.$el.foobox(window.FOOBOX.o);
				} else {
					$("body").trigger("post-load");
				}
			} catch(err) {
				console.error(err);
			}
		}
	};

	_.autoDefaults = {
		on: {
			"first-load after-page-change after-filter-change": _.triggerPostLoad
		}
	};

	_.autoEnabled = true;

	_.auto = function (options) {
		_.autoDefaults = _obj.merge(_.autoDefaults, options);
	};

	_.globalsMerged = false;

	_.mergeGlobals = function(){
		if (_.globalsMerged === true) return;
		if (window.FooGallery_il8n && _is.object(window.FooGallery_il8n)){
			var il8n = window.FooGallery_il8n;
			for (var factory in il8n){
				if (!il8n.hasOwnProperty(factory) || !(_[factory] instanceof _.Factory) || !_is.object(il8n[factory])) continue;
				for (var component in il8n[factory]){
					if (il8n[factory].hasOwnProperty(component)){
						_[factory].configure(component, null, null, il8n[factory][component]);
					}
				}
			}
			_.globalsMerged = true;
		}
	};

	_.load = _.reload = function(){
		// this automatically initializes all templates on page load
		$(function () {
			_.mergeGlobals();
			if (_.autoEnabled){
				$('[id^="foogallery-gallery-"]:not(.fg-ready)').foogallery(_.autoDefaults);
			}
		});

		_utils.ready(function () {
			_.mergeGlobals();
			if (_.autoEnabled){
				$('[id^="foogallery-gallery-"].fg-ready').foogallery(_.autoDefaults);
			}
		});
	};

	_.load();

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.obj,
	FooGallery.utils.is
);