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
/*!
* FooGallery.utils - Contains common utility methods and classes used in our plugins.
* @version 0.1.7
* @link https://github.com/steveush/foo-utils#readme
* @copyright Steve Usher 2019
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
		version: '0.1.7'
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
			var parts = version.split('.'), result = [];
			for(var i = 0, len = parts.length; i < len; i++){
				result[i] = parseInt(parts[i]);
				if (isNaN(result[i])) result[i] = 0;
			}
			return result;
		}

		// get the base numeric arrays for each version
		var v1parts = split(version1),
			v2parts = split(version2);

		// ensure both arrays are the same length by padding the shorter with 0
		while (v1parts.length < v2parts.length) v1parts.push(0);
		while (v2parts.length < v1parts.length) v2parts.push(0);

		// perform the actual comparison
		for (var i = 0; i < v1parts.length; ++i) {
			if (v2parts.length === i) return 1;
			if (v1parts[i] === v2parts[i]) continue;
			if (v1parts[i] > v2parts[i]) return 1;
			else return -1;
		}
		if (v1parts.length !== v2parts.length) return -1;
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
	if (_.version !== '0.1.7') return;

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
			: !!value && typeof value === 'object' && value.nodeType === 1 && typeof value.nodeName === 'string';
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
		if (_.is.number(value) && value === 0) return true;
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
		return /^(auto|none|(?:[\d.]*)+?(?:%|px|mm|q|cm|in|pt|pc|em|ex|ch|rem|vh|vw|vmin|vmax)?)$/.test(value);
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
	if (_.version !== '0.1.7') return;

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
	 * @summary Debounces the `fn` by the supplied `time`.
	 * @memberof FooGallery.utils.fn
	 * @function debounce
	 * @param {function} fn - The function to debounce.
	 * @param {number} time - The time in milliseconds to delay execution.
	 * @returns {function}
	 * @description This returns a wrapped version of the `fn` which delays its' execution by the supplied `time`. Additional calls to the function will extend the delay until the `time` expires.
	 */
	_.fn.debounce = function (fn, time) {
		var timeout;
		return function () {
			var ctx = this, args = _.fn.arg2arr(arguments);
			clearTimeout(timeout);
			timeout = setTimeout(function () {
				fn.apply(ctx, args);
			}, time);
		};
	};

	/**
	 * @summary Throttles the `fn` by the supplied `time`.
	 * @memberof FooGallery.utils.fn
	 * @function throttle
	 * @param {function} fn - The function to throttle.
	 * @param {number} time - The time in milliseconds to delay execution.
	 * @returns {function}
	 * @description This returns a wrapped version of the `fn` which ensures it's executed only once every `time` milliseconds. The first call to the function will be executed, after that only the last of any additional calls will be executed once the `time` expires.
	 */
	_.fn.throttle = function (fn, time) {
		var last, timeout;
		return function () {
			var ctx = this, args = _.fn.arg2arr(arguments);
			if (!last){
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
		}
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
	if (_.version !== '0.1.7') return;

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
		var port = _a.port ? _a.port : (["http:","https:"].indexOf(_a.protocol) !== -1 ? (_a.protocol === "https:" ? "443" : "80") : ""),
			host = _a.hostname + (port ? ":" + port : ""),
			origin = _a.origin ? _a.origin : _a.protocol + "//" + host,
			pathname = _a.pathname.slice(0, 1) === "/" ? _a.pathname : "/" + _a.pathname;
		return {
			hash: _a.hash, host: host, hostname: _a.hostname, href: _a.href,
			origin: origin, pathname: pathname, port: port,
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
	_.url.param = function(search, key, value){
		if (!_is.string(search) || !_is.string(key) || _is.empty(key)) return search;
		var regex, match, result, param;
		if (_is.undef(value)){
			regex = new RegExp('[?|&]' + key + '=([^&;]+?)(&|#|;|$)'); // regex to match the key and it's value but only capture the value
			match = regex.exec(search) || ["",""]; // match the param otherwise return an empty string match
			result = match[1].replace(/\+/g, '%20'); // replace any + character's with spaces
			return _is.string(result) && !_is.empty(result) ? decodeURIComponent(result) : null; // decode the result otherwise return null
		}
		if (_is.empty(value)){
			regex = new RegExp('^([^#]*\?)(([^#]*)&)?' + key + '(\=[^&#]*)?(&|#|$)');
			result = search.replace(regex, '$1$3$5').replace(/^([^#]*)((\?)&|\?(#|$))/,'$1$3$4');
		} else {
			regex = new RegExp('([?&])' + key + '[^&]*'); // regex to match the key and it's current value but only capture the preceding ? or & char
			param = key + '=' + encodeURIComponent(value);
			result = search.replace(regex, '$1' + param); // replace any existing instance of the key with the new value
			// If nothing was replaced, then add the new param to the end
			if (result === search && !regex.test(result)) { // if no replacement occurred and the parameter is not currently in the result then add it
				result += (result.indexOf("?") !== -1 ? '&' : '?') + param;
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
	if (_.version !== '0.1.7') return;

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
			if (ignoreCase ? parts[i].toUpperCase() === word.toUpperCase() : parts[i] === word) return true;
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
		if (!_is.string(target) || _is.empty(target) || !_is.string(substr) || _is.empty(substr)) return target === substr;
		return target.slice(target.length - substr.length) === substr;
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
		return target.slice(0, substr.length) === substr;
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
	if (_.version !== '0.1.7') return;

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
	if (_.version !== '0.1.7') return;

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

	/**
	 * @summary Convert CSS class names into CSS selectors.
	 * @memberof FooGallery.utils
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
			var result = {}, selector;
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
			return classes.map(function(str){
				return _is.string(str) ? "." + str.split(/\s/g).join(".") : null;
			}).join(",");
		}
		return null;
	};

	/**
	 * @summary Parses the supplied `src` and `srcset` values and returns the best matching URL for the supplied render size.
	 * @memberof FooGallery.utils
	 * @function src
	 * @param {string} src - The default src for the image.
	 * @param {string} srcset - The srcset containing additional image sizes.
	 * @param {number} srcWidth - The width of the `src` image.
	 * @param {number} srcHeight - The height of the `src` image.
	 * @param {number} renderWidth - The rendered width of the image element.
	 * @param {number} renderHeight - The rendered height of the image element.
	 * @param {number} [devicePixelRatio] - The device pixel ratio to use while parsing. Defaults to the current device pixel ratio.
	 * @returns {(string|null)} Returns the parsed responsive src or null if no src is provided.
	 * @description This can be used to parse the correct src to use when loading an image through JavaScript.
	 * @example {@caption The following shows using the method with the srcset w-descriptor.}{@run true}
	 * var src = "test-240x120.jpg",
	 * 	width = 240, // the naturalWidth of the 'src' image
	 * 	height = 120, // the naturalHeight of the 'src' image
	 * 	srcset = "test-480x240.jpg 480w, test-720x360.jpg 720w, test-960x480.jpg 960w";
	 *
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 240, 120, 1 ) ); // => "test-240x120.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 240, 120, 2 ) ); // => "test-480x240.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 480, 240, 1 ) ); // => "test-480x240.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 480, 240, 2 ) ); // => "test-960x480.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 720, 360, 1 ) ); // => "test-720x360.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 960, 480, 1 ) ); // => "test-960x480.jpg"
	 * @example {@caption The following shows using the method with the srcset h-descriptor.}{@run true}
	 * var src = "test-240x120.jpg",
	 * 	width = 240, // the naturalWidth of the 'src' image
	 * 	height = 120, // the naturalHeight of the 'src' image
	 * 	srcset = "test-480x240.jpg 240h, test-720x360.jpg 360h, test-960x480.jpg 480h";
	 *
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 240, 120, 1 ) ); // => "test-240x120.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 240, 120, 2 ) ); // => "test-480x240.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 480, 240, 1 ) ); // => "test-480x240.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 480, 240, 2 ) ); // => "test-960x480.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 720, 360, 1 ) ); // => "test-720x360.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 960, 480, 1 ) ); // => "test-960x480.jpg"
	 * @example {@caption The following shows using the method with the srcset x-descriptor.}{@run true}
	 * var src = "test-240x120.jpg",
	 * 	width = 240, // the naturalWidth of the 'src' image
	 * 	height = 120, // the naturalHeight of the 'src' image
	 * 	srcset = "test-480x240.jpg 2x, test-720x360.jpg 3x, test-960x480.jpg 4x";
	 *
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 240, 120, 1 ) ); // => "test-240x120.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 240, 120, 2 ) ); // => "test-480x240.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 480, 240, 1 ) ); // => "test-240x120.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 480, 240, 2 ) ); // => "test-480x240.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 720, 360, 1 ) ); // => "test-240x120.jpg"
	 * console.log( FooGallery.utils.src( src, srcset, width, height, 960, 480, 1 ) ); // => "test-240x120.jpg"
	 */
	_.src = function(src, srcset, srcWidth, srcHeight, renderWidth, renderHeight, devicePixelRatio){
		if (!_is.string(src)) return null;
		// if there is no srcset just return the src
		if (!_is.string(srcset)) return src;

		// first split the srcset into its individual sources
		var sources = srcset.replace(/(\s[\d.]+[whx]),/g, '$1 @,@ ').split(' @,@ ');
		// then parse those sources into objects containing the url, width, height and pixel density
		var list = sources.map(function (val) {
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
		var dpr = _is.number(devicePixelRatio) ? devicePixelRatio : (window.devicePixelRatio || 1),
			area = {w: renderWidth * dpr, h: renderHeight * dpr, x: dpr},
			props = ['w','h','x'];

		// first check each of the viewport properties against the max values of the same properties in our src array
		// only src's with a property greater than the viewport or equal to the max are kept
		props.forEach(function (prop) {
			var max = Math.max.apply(null, list.map(function (item) {
				return item[prop];
			}));
			list = list.filter(function (item) {
				return item[prop] >= area[prop] || item[prop] === max;
			});
		});

		// next reduce our src array by comparing the viewport properties against the minimum values of the same properties of each src
		// only src's with a property equal to the minimum are kept
		props.forEach(function (prop) {
			var min = Math.min.apply(null, list.map(function (item) {
				return item[prop];
			}));
			list = list.filter(function (item) {
				return item[prop] === min;
			});
		});

		// return the first url as it is the best match for the current viewport
		return list[0].url;
	};

	/**
	 * @summary Get the scroll parent for the supplied element optionally filtering by axis.
	 * @memberof FooGallery.utils
	 * @function scrollParent
	 * @param {(string|Element|jQuery)} element - The selector, element or jQuery element to find the scroll parent of.
	 * @param {string} [axis="xy"] - The axis to check. By default this method will check both the X and Y axis.
	 * @param {jQuery} [def] - The default jQuery element to return if no result was found. Defaults to the supplied elements document.
	 * @returns {jQuery}
	 */
	_.scrollParent = function(element, axis, def){
		element = _is.jq(element) ? element : $(element);
		axis = _is.string(axis) && /^(x|y|xy|yx)$/i.test(axis) ? axis : "xy";
		var $doc = $(!!element.length && element[0].ownerDocument || document);
		def = _is.jq(def) ? def : $doc;

		if (!element.length) return def;

		var position = element.css("position"),
			excludeStaticParent = position === "absolute",
			scroll = /(auto|scroll)/i, axisX = /x/i, axisY = /y/i,
			$parent = element.parentsUntil(def).filter(function(i, el){
				var $el = $(this);
				if (excludeStaticParent && $el.css("position") === "static") return false;
				var scrollY = axisY.test(axis) && el.scrollHeight > el.clientHeight && scroll.test($el.css("overflow-y")),
					scrollX = axisX.test(axis) && el.scrollWidth > el.clientWidth && scroll.test($el.css("overflow-x"));
				return scrollY || scrollX;
			}).eq(0);

		if ($parent.is("html")) $parent = $doc;
		return position === "fixed" || !$parent.length ? def : $parent;
	};

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _is){
	// only register methods if this version is the current version
	if (_.version !== '0.1.7') return;

	/**
	 * @summary Contains common utility methods and members for the CSS animation property.
	 * @memberof FooGallery.utils
	 * @namespace animation
	 */
	_.animation = {};

	function raf(callback){
		return setTimeout(callback, 1);
	}

	function caf(requestID){
		clearTimeout(requestID);
	}

	/**
	 * @summary A cross browser wrapper for the `requestAnimationFrame` method.
	 * @memberof FooGallery.utils.animation
	 * @function requestFrame
	 * @param {function} callback - The function to call when it's time to update your animation for the next repaint.
	 * @return {number} - The request id that uniquely identifies the entry in the callback list.
	 */
	_.animation.requestFrame = (window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame || raf).bind(window);

	/**
	 * @summary A cross browser wrapper for the `cancelAnimationFrame` method.
	 * @memberof FooGallery.utils.animation
	 * @function cancelFrame
	 * @param {number} requestID - The ID value returned by the call to {@link FooGallery.utils.animation.requestFrame|requestFrame} that requested the callback.
	 */
	_.animation.cancelFrame = (window.cancelAnimationFrame || window.mozCancelAnimationFrame || window.webkitCancelAnimationFrame || window.msCancelAnimationFrame || caf).bind(window);

	// create a test element to check for the existence of the various animation properties
	var testElement = document.createElement('div');

	/**
	 * @summary Whether or not animations are supported by the current browser.
	 * @memberof FooGallery.utils.animation
	 * @name supported
	 * @type {boolean}
	 */
	_.animation.supported = (
		/**
		 * @ignore
		 * @summary Performs a one time test to see if animations are supported
		 * @param {HTMLElement} el - An element to test with.
		 * @returns {boolean} `true` if animations are supported.
		 */
		function(el){
			var style = el.style;
			return _is.string(style['animation'])
				|| _is.string(style['WebkitAnimation'])
				|| _is.string(style['MozAnimation'])
				|| _is.string(style['msAnimation'])
				|| _is.string(style['OAnimation']);
		}
	)(testElement);

	/**
	 * @summary The `animationend` event name for the current browser.
	 * @memberof FooGallery.utils.animation
	 * @name end
	 * @type {string}
	 * @description Depending on the browser this returns one of the following values:
	 *
	 * <ul><!--
	 * --><li>`"animationend"`</li><!--
	 * --><li>`"webkitAnimationEnd"`</li><!--
	 * --><li>`"msAnimationEnd"`</li><!--
	 * --><li>`"oAnimationEnd"`</li><!--
	 * --><li>`null` - If the browser doesn't support animations</li><!--
	 * --></ul>
	 */
	_.animation.end = (
		/**
		 * @ignore
		 * @summary Performs a one time test to determine which `animationend` event to use for the current browser.
		 * @param {HTMLElement} el - An element to test with.
		 * @returns {?string} The correct `animationend` event for the current browser, `null` if the browser doesn't support animations.
		 */
		function(el){
			var style = el.style;
			if (_is.string(style['animation'])) return 'animationend';
			if (_is.string(style['WebkitAnimation'])) return 'webkitAnimationEnd';
			if (_is.string(style['MozAnimation'])) return 'animationend';
			if (_is.string(style['msAnimation'])) return 'msAnimationEnd';
			if (_is.string(style['OAnimation'])) return 'oAnimationEnd';
			return null;
		}
	)(testElement);

	/**
	 * @summary Gets the `animation-duration` value for the supplied jQuery element.
	 * @memberof FooGallery.utils.animation
	 * @function duration
	 * @param {jQuery} $element - The jQuery element to retrieve the duration from.
	 * @param {number} [def=0] - The default value to return if no duration is set.
	 * @returns {number} The value of the `animation-duration` property converted to a millisecond value.
	 */
	_.animation.duration = function($element, def){
		def = _is.number(def) ? def : 0;
		if (!_is.jq($element)) return def;
		// we can use jQuery.css() method to retrieve the value cross browser
		var duration = $element.css('animation-duration');
		if (/^([\d.]*)+?(ms|s)$/i.test(duration)){
			// if we have a valid time value
			var match = duration.match(/^([\d.]*)+?(ms|s)$/i),
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
	 * @summary Gets the `animation-iteration-count` value for the supplied jQuery element.
	 * @memberof FooGallery.utils.animation
	 * @function iterations
	 * @param {jQuery} $element - The jQuery element to retrieve the duration from.
	 * @param {number} [def=1] - The default value to return if no iteration count is set.
	 * @returns {number} The value of the `animation-iteration-count` property.
	 */
	_.animation.iterations = function($element, def){
		def = _is.number(def) ? def : 1;
		if (!_is.jq($element)) return def;
		// we can use jQuery.css() method to retrieve the value cross browser
		var iterations = $element.css('animation-iteration-count');
		if (/^(\d+|infinite)$/i.test(iterations)){
			return iterations === "infinite" ? Infinity : parseInt(iterations);
		}
		return def;
	};

	/**
	 * @summary The callback function to execute when starting a animation.
	 * @callback FooGallery.utils.animation~startCallback
	 * @param {jQuery} $element - The element to start the animation on.
	 * @this Element
	 */

	/**
	 * @summary Start a animation by toggling the supplied `className` on the `$element`.
	 * @memberof FooGallery.utils.animation
	 * @function start
	 * @param {jQuery} $element - The jQuery element to start the animation on.
	 * @param {(string|FooGallery.utils.animation~startCallback)} classNameOrFunc - One or more class names (separated by spaces) to be toggled or a function that performs the required actions to start the animation.
	 * @param {boolean} [state] - A Boolean (not just truthy/falsy) value to determine whether the class should be added or removed.
	 * @param {number} [timeout] - The maximum time, in milliseconds, to wait for the `animationend` event to be raised. If not provided this will be automatically set to the elements `animation-duration` multiplied by the `animation-iteration-count` property plus an extra 50 milliseconds.
	 * @returns {Promise}
	 * @description This method lets us use CSS animations by toggling a class and using the `animationend` event to perform additional actions once the animation has completed across all browsers. In browsers that do not support animations this method would behave the same as if just calling jQuery's `.toggleClass` method.
	 *
	 * The last parameter `timeout` is used to create a timer that behaves as a safety net in case the `animationend` event is never raised and ensures the deferred returned by this method is resolved or rejected within a specified time.
	 *
	 * If no `timeout` is supplied the `animation-duration` and `animation-iterations-count` must be set on the `$element` before this method is called so one can be generated.
	 * @see {@link https://developer.mozilla.org/en/docs/Web/CSS/animation-duration|animation-duration - CSS | MDN} for more information on the `animation-duration` CSS property.
	 */
	_.animation.start = function($element, classNameOrFunc, state, timeout){
		var deferred = $.Deferred(), promise = deferred.promise();

		$element = $element.first();

		if (_.animation.supported){
			$element.prop('offsetTop');
			var safety = $element.data('animation_safety');
			if (_is.hash(safety) && _is.number(safety.timer)){
				clearTimeout(safety.timer);
				$element.removeData('animation_safety').off(_.animation.end + '.utils');
				safety.deferred.reject();
			}
			if (!_is.number(timeout)){
				var iterations = _.animation.iterations($element);
				if (iterations === Infinity){
					deferred.reject("No timeout supplied with an infinite animation.");
					return promise;
				}
				timeout = (_.animation.duration($element) * iterations) + 50;
			}
			safety = {
				deferred: deferred,
				timer: setTimeout(function(){
					// This is the safety net in case a animation fails for some reason and the animationend event is never raised.
					// This will remove the bound event and resolve the deferred
					$element.removeData('animation_safety').off(_.animation.end + '.utils');
					deferred.resolve();
				}, timeout)
			};
			$element.data('animation_safety', safety);

			$element.on(_.animation.end + '.utils', function(e){
				if ($element.is(e.target)){
					clearTimeout(safety.timer);
					$element.removeData('animation_safety').off(_.animation.end + '.utils');
					deferred.resolve();
				}
			});
		}

		_.animation.requestFrame(function(){
			if (_is.fn(classNameOrFunc)){
				classNameOrFunc.apply($element.get(0), [$element]);
			} else {
				$element.toggleClass(classNameOrFunc, state);
			}
			if (!_.animation.supported){
				// If the browser doesn't support animations then just resolve the deferred
				deferred.resolve();
			}
		});

		return promise;
	};

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _is, _animation){
	// only register methods if this version is the current version
	if (_.version !== '0.1.7') return;

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
		 * @param {HTMLElement} el - An element to test with.
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
		 * @param {HTMLElement} el - An element to test with.
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
		if (/^([\d.]*)+?(ms|s)$/i.test(duration)){
			// if we have a valid time value
			var match = duration.match(/^([\d.]*)+?(ms|s)$/i),
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
	 * @summary The callback function to execute when starting a transition.
	 * @callback FooGallery.utils.transition~startCallback
	 * @param {jQuery} $element - The element to start the transition on.
	 * @this Element
	 */

	/**
	 * @summary Start a transition by toggling the supplied `className` on the `$element`.
	 * @memberof FooGallery.utils.transition
	 * @function start
	 * @param {jQuery} $element - The jQuery element to start the transition on.
	 * @param {(string|FooGallery.utils.transition~startCallback)} classNameOrFunc - One or more class names (separated by spaces) to be toggled or a function that performs the required actions to start the transition.
	 * @param {boolean} [state] - A Boolean (not just truthy/falsy) value to determine whether the class should be added or removed.
	 * @param {number} [timeout] - The maximum time, in milliseconds, to wait for the `transitionend` event to be raised. If not provided this will be automatically set to the elements `transition-duration` property plus an extra 50 milliseconds.
	 * @returns {Promise}
	 * @description This method lets us use CSS transitions by toggling a class and using the `transitionend` event to perform additional actions once the transition has completed across all browsers. In browsers that do not support transitions this method would behave the same as if just calling jQuery's `.toggleClass` method.
	 *
	 * The last parameter `timeout` is used to create a timer that behaves as a safety net in case the `transitionend` event is never raised and ensures the deferred returned by this method is resolved or rejected within a specified time.
	 * @see {@link https://developer.mozilla.org/en/docs/Web/CSS/transition-duration|transition-duration - CSS | MDN} for more information on the `transition-duration` CSS property.
	 */
	_.transition.start = function($element, classNameOrFunc, state, timeout){
		var deferred = $.Deferred(), promise = deferred.promise();

		$element = $element.first();

		if (_.transition.supported){
			$element.prop('offsetTop');
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

		_animation.requestFrame(function() {
			if (_is.fn(classNameOrFunc)){
				classNameOrFunc.apply($element.get(0), [$element]);
			} else {
				$element.toggleClass(classNameOrFunc, state);
			}
			if (!_.transition.supported){
				// If the browser doesn't support transitions then just resolve the deferred
				deferred.resolve();
			}
		});

		return promise;
	};

})(
	// dependencies
	FooGallery.utils.$,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.animation
);
(function ($, _, _is, _obj, _fn) {
	// only register methods if this version is the current version
	if (_.version !== '0.1.7') return;

	/**
	 * @summary A base class providing some helper methods for prototypal inheritance.
	 * @constructs FooGallery.utils.Class
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
(function (_, _is, _str) {
    // only register methods if this version is the current version
    if (_.version !== '0.1.7') return;

    _.Event = _.Class.extend(/** @lends FooGallery.utils.Event */{
        /**
         * @summary A base event class providing just a type and defaultPrevented properties.
         * @constructs
         * @param {string} type - The type for this event.
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
        construct: function(type){
            if (_is.empty(type))
                throw new SyntaxError('FooGallery.utils.Event objects must be supplied a `type`.');

            var namespaced = _str.contains(type, ".");
            /**
             * @summary The type of event.
             * @memberof FooGallery.utils.Event#
             * @name type
             * @type {string}
             * @readonly
             */
            this.type = namespaced ? _str.until(type, ".") : type;
            /**
             * @summary The namespace of the event.
             * @memberof FooGallery.utils.Event#
             * @name namespace
             * @type {string}
             * @readonly
             */
            this.namespace = namespaced ? _str.from(type, ".") : null;
            /**
             * @summary Whether the default action should be taken or not.
             * @memberof FooGallery.utils.Event#
             * @name defaultPrevented
             * @type {boolean}
             * @readonly
             */
            this.defaultPrevented = false;
            /**
             * @summary The {@link FooGallery.utils.EventClass} that triggered this event.
             * @memberof FooGallery.utils.Event#
             * @name target
             * @type {FooGallery.utils.EventClass}
             * @readonly
             */
            this.target = null;
        },
        /**
         * @summary Informs the class that raised this event that its default action should not be taken.
         * @memberof FooGallery.utils.Event#
         * @function preventDefault
         */
        preventDefault: function(){
            this.defaultPrevented = true;
        },
        /**
         * @summary Gets whether the default action should be taken or not.
         * @memberof FooGallery.utils.Event#
         * @function isDefaultPrevented
         * @returns {boolean}
         */
        isDefaultPrevented: function(){
            return this.defaultPrevented;
        }
    });

    _.EventClass = _.Class.extend(/** @lends FooGallery.utils.EventClass */{
        /**
         * @summary A base class that implements a basic events interface.
         * @constructs
         * @description This is a very basic events implementation that provides just enough to cover most needs.
         */
        construct: function(){
            /**
             * @summary The object used internally to register event handlers.
             * @memberof FooGallery.utils.EventClass#
             * @name __handlers
             * @type {Object}
             * @private
             */
            this.__handlers = {};
        },
        /**
         * @summary Destroy the current instance releasing used resources.
         * @memberof FooGallery.utils.EventClass#
         * @function destroy
         */
        destroy: function(){
            this.__handlers = {};
        },
        /**
         * @summary Attach multiple event handler functions for one or more events to the class.
         * @memberof FooGallery.utils.EventClass#
         * @function on
         * @param {object} events - An object containing an event name to handler mapping.
         * @param {*} [thisArg] - The value of `this` within the `handler` function. Defaults to the `EventClass` raising the event.
         * @returns {this}
         *//**
         * @summary Attach an event handler function for one or more events to the class.
         * @memberof FooGallery.utils.EventClass#
         * @function on
         * @param {string} events - One or more space-separated event types.
         * @param {function} handler - A function to execute when the event is triggered.
         * @param {*} [thisArg] - The value of `this` within the `handler` function. Defaults to the `EventClass` raising the event.
         * @returns {this}
         */
        on: function(events, handler, thisArg){
            var self = this;
            if (_is.object(events)){
                thisArg = _is.undef(handler) ? this : handler;
                Object.keys(events).forEach(function(key){
                    key.split(" ").forEach(function(type){
                        self.__on(type, events[key], thisArg);
                    });
                });
            } else if (_is.string(events) && _is.fn(handler)) {
                thisArg = _is.undef(thisArg) ? this : thisArg;
                events.split(" ").forEach(function(type){
                    self.__on(type, handler, thisArg);
                });
            }

            return self;
        },
        __on: function(event, handler, thisArg){
            var self = this,
                namespaced = _str.contains(event, "."),
                type = namespaced ? _str.until(event, ".") : event,
                namespace = namespaced ? _str.from(event, ".") : null;

            if (!_is.array(self.__handlers[type])){
                self.__handlers[type] = [];
            }
            var exists = self.__handlers[type].some(function(h){
                return h.namespace === namespace && h.fn === handler && h.thisArg === thisArg;
            });
            if (!exists){
                self.__handlers[type].push({
                    namespace: namespace,
                    fn: handler,
                    thisArg: thisArg
                });
            }
        },
        /**
         * @summary Remove multiple event handler functions for one or more events from the class.
         * @memberof FooGallery.utils.EventClass#
         * @function off
         * @param {object} events - An object containing an event name to handler mapping.
         * @param {*} [thisArg] - The value of `this` within the `handler` function. Defaults to the `EventClass` raising the event.
         * @returns {this}
         *//**
         * @summary Remove an event handler function for one or more events from the class.
         * @memberof FooGallery.utils.EventClass#
         * @function off
         * @param {string} events - One or more space-separated event types.
         * @param {function} handler - The handler to remove.
         * @param {*} [thisArg] - The value of `this` within the `handler` function.
         * @returns {this}
         */
        off: function(events, handler, thisArg){
            var self = this;
            if (_is.object(events)){
                thisArg = _is.undef(handler) ? this : handler;
                Object.keys(events).forEach(function(key){
                    key.split(" ").forEach(function(type){
                        self.__off(type, _is.fn(events[key]) ? events[key] : null, thisArg);
                    });
                });
            } else if (_is.string(events)) {
                handler = _is.fn(handler) ? handler : null;
                thisArg = _is.undef(thisArg) ? this : thisArg;
                events.split(" ").forEach(function(type){
                    self.__off(type, handler, thisArg);
                });
            }

            return self;
        },
        __off: function(event, handler, thisArg){
            var self = this,
                type = _str.until(event, ".") || null,
                namespace = _str.from(event, ".") || null,
                types = [];

            if (!_is.empty(type)){
                types.push(type);
            } else if (!_is.empty(namespace)){
                types.push.apply(types, Object.keys(self.__handlers));
            }

            types.forEach(function(type){
                if (!_is.array(self.__handlers[type])) return;
                self.__handlers[type] = self.__handlers[type].filter(function (h) {
                    if (handler != null){
                        return !(h.namespace === namespace && h.fn === handler && h.thisArg === thisArg);
                    }
                    if (namespace != null){
                        return h.namespace !== namespace;
                    }
                    return false;
                });
                if (self.__handlers[type].length === 0){
                    delete self.__handlers[type];
                }
            });
        },
        /**
         * @summary Trigger an event on the current class.
         * @memberof FooGallery.utils.EventClass#
         * @function trigger
         * @param {(string|FooGallery.utils.Event)} event - Either a space-separated string of event types or a custom event object to raise.
         * @param {Array} [args] - An array of additional arguments to supply to the handlers after the event object.
         * @returns {(FooGallery.utils.Event|FooGallery.utils.Event[]|null)} Returns the {@link FooGallery.utils.Event|event object} of the triggered event. If more than one event was triggered an array of {@link FooGallery.utils.Event|event objects} is returned. If no `event` was supplied or triggered `null` is returned.
         */
        trigger: function(event, args){
            args = _is.array(args) ? args : [];
            var self = this, result = [];
            if (event instanceof _.Event){
                result.push(event);
                self.__trigger(event, args);
            } else if (_is.string(event)) {
                event.split(" ").forEach(function(type){
                    var index = result.push(new _.Event(type)) - 1;
                    self.__trigger(result[index], args);
                });
            }
            return _is.empty(result) ? null : (result.length === 1 ? result[0] : result);
        },
        __trigger: function(event, args){
            var self = this;
            event.target = self;
            if (!_is.array(self.__handlers[event.type])) return;
            self.__handlers[event.type].forEach(function (h) {
                if (event.namespace != null && h.namespace !== event.namespace) return;
                h.fn.apply(h.thisArg, [event].concat(args));
            });
        }
    });

})(
    // dependencies
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.str
);
(function($, _, _is){
	// only register methods if this version is the current version
	if (_.version !== '0.1.7') return;

	_.Bounds = _.Class.extend(/** @lends FooGallery.utils.Bounds */{
		/**
		 * @summary A simple bounding rectangle class.
		 * @constructs
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
(function($, _, _is, _fn, _obj){
    // only register methods if this version is the current version
    if (_.version !== '0.1.7') return;

    _.Timer = _.EventClass.extend(/** @lends FooGallery.utils.Timer */{
        /**
         * @summary A simple timer that triggers events.
         * @constructs
         * @param {number} [interval=1000] - The internal tick interval of the timer.
         */
        construct: function(interval){
            this._super();
            /**
             * @summary The internal tick interval of the timer in milliseconds.
             * @memberof FooGallery.utils.Timer#
             * @name interval
             * @type {number}
             * @default 1000
             * @readonly
             */
            this.interval = _is.number(interval) ? interval : 1000;
            /**
             * @summary Whether the timer is currently running or not.
             * @memberof FooGallery.utils.Timer#
             * @name isRunning
             * @type {boolean}
             * @default false
             * @readonly
             */
            this.isRunning = false;
            /**
             * @summary Whether the timer is currently paused or not.
             * @memberof FooGallery.utils.Timer#
             * @name isPaused
             * @type {boolean}
             * @default false
             * @readonly
             */
            this.isPaused = false;
            /**
             * @summary Whether the timer can resume from a previous state or not.
             * @memberof FooGallery.utils.Timer#
             * @name canResume
             * @type {boolean}
             * @default false
             * @readonly
             */
            this.canResume = false;
            /**
             * @summary Whether the timer can restart or not.
             * @memberof FooGallery.utils.Timer#
             * @name canRestart
             * @type {boolean}
             * @default false
             * @readonly
             */
            this.canRestart = false;
            /**
             * @summary The internal tick timeout ID.
             * @memberof FooGallery.utils.Timer#
             * @name __timeout
             * @type {?number}
             * @default null
             * @private
             */
            this.__timeout = null;
            /**
             * @summary Whether the timer is incrementing or decrementing.
             * @memberof FooGallery.utils.Timer#
             * @name __decrement
             * @type {boolean}
             * @default false
             * @private
             */
            this.__decrement = false;
            /**
             * @summary The total time for the timer.
             * @memberof FooGallery.utils.Timer#
             * @name __time
             * @type {number}
             * @default 0
             * @private
             */
            this.__time = 0;
            /**
             * @summary The remaining time for the timer.
             * @memberof FooGallery.utils.Timer#
             * @name __remaining
             * @type {number}
             * @default 0
             * @private
             */
            this.__remaining = 0;
            /**
             * @summary The current time for the timer.
             * @memberof FooGallery.utils.Timer#
             * @name __current
             * @type {number}
             * @default 0
             * @private
             */
            this.__current = 0;
            /**
             * @summary The final time for the timer.
             * @memberof FooGallery.utils.Timer#
             * @name __finish
             * @type {number}
             * @default 0
             * @private
             */
            this.__finish = 0;
            /**
             * @summary The last arguments supplied to the {@link FooGallery.utils.Timer#start|start} method.
             * @memberof FooGallery.utils.Timer#
             * @name __restart
             * @type {Array}
             * @default []
             * @private
             */
            this.__restart = [];
        },
        /**
         * @summary Resets the timer back to a fresh starting state.
         * @memberof FooGallery.utils.Timer#
         * @function __reset
         * @private
         */
        __reset: function(){
            clearTimeout(this.__timeout);
            this.__timeout = null;
            this.__decrement = false;
            this.__time = 0;
            this.__remaining = 0;
            this.__current = 0;
            this.__finish = 0;
            this.isRunning = false;
            this.isPaused = false;
            this.canResume = false;
        },
        /**
         * @summary Generates event args to be passed to listeners of the timer events.
         * @memberof FooGallery.utils.Timer#
         * @function __eventArgs
         * @param {...*} [args] - Any number of additional arguments to pass to an event listener.
         * @return {Array} - The first 3 values of the result will always be the current time, the total time and boolean indicating if the timer is decremental.
         * @private
         */
        __eventArgs: function(args){
            return [
                this.__current,
                this.__time,
                this.__decrement
            ].concat(_fn.arg2arr(arguments));
        },
        /**
         * @summary Performs the tick for the timer checking and modifying the various internal states.
         * @memberof FooGallery.utils.Timer#
         * @function __tick
         * @private
         */
        __tick: function(){
            var self = this;
            self.trigger("tick", self.__eventArgs());
            if (self.__current === self.__finish){
                self.trigger("complete", self.__eventArgs());
                self.__reset();
            } else {
                if (self.__decrement){
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
        start: function(time, decrement){
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
        countdown: function(time){
            this.start(time, true);
        },
        /**
         * @summary Starts the timer counting up from `0` to the supplied `time`.
         * @memberof FooGallery.utils.Timer#
         * @function countup
         * @param {number} time - The total time in seconds for the timer.
         */
        countup: function(time){
            this.start(time, false);
        },
        /**
         * @summary Stops and then restarts the timer using the last arguments supplied to the {@link FooGallery.utils.Timer#start|start} method.
         * @memberof FooGallery.utils.Timer#
         * @function restart
         */
        restart: function(){
            this.stop();
            if (this.canRestart){
                this.start.apply(this, this.__restart);
            }
        },
        /**
         * @summary Stops the timer.
         * @memberof FooGallery.utils.Timer#
         * @function stop
         */
        stop: function(){
            if (this.isRunning || this.isPaused){
                this.__reset();
                this.trigger("stop", this.__eventArgs());
            }
        },
        /**
         * @summary Pauses the timer and returns the remaining seconds.
         * @memberof FooGallery.utils.Timer#
         * @function pause
         * @return {number} - The number of seconds remaining for the timer.
         */
        pause: function(){
            var self = this;
            if (self.__timeout != null){
                clearTimeout(self.__timeout);
                self.__timeout = null;
            }
            if (self.isRunning){
                self.isRunning = false;
                self.isPaused = true;
                self.trigger("pause", self.__eventArgs());
            }
            return self.__remaining;
        },
        /**
         * @summary Resumes the timer from a previously paused state.
         * @memberof FooGallery.utils.Timer#
         * @function resume
         */
        resume: function(){
            var self = this;
            if (self.canResume){
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
        reset: function(){
            this.__reset();
            this.trigger("reset", this.__eventArgs());
        }
    });

})(
    FooGallery.utils.$,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.obj
);

(function($, _, _is, _fn){
	// only register methods if this version is the current version
	if (_.version !== '0.1.7') return;

	_.Factory = _.Class.extend(/** @lends FooGallery.utils.Factory */{
		/**
		 * @summary A factory for classes allowing them to be registered and created using a friendly name.
		 * @constructs
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
	if (_.version !== '0.1.7') return;

	// this is done to handle Content Security in Chrome and other browsers blocking access to the localStorage object under certain configurations.
	// see: https://www.chromium.org/for-testers/bug-reporting-guidelines/uncaught-securityerror-failed-to-read-the-localstorage-property-from-window-access-is-denied-for-this-document
	var localAvailable = false;
	try { localAvailable = !!window.localStorage; }
	catch (err){ localAvailable = false; }

	_.Debugger = _.Class.extend(/** @lends FooGallery.utils.Debugger */{
		/**
		 * @summary A debug utility class that can be enabled across sessions using the given `key` by storing its state in `localStorage`.
		 * @constructs
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
			this.enabled = localAvailable ? !!localStorage.getItem(this.key) : false;
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
			if (!localAvailable) return;
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
			if (!localAvailable) return;
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
(function($, _, _fn){
    // only register methods if this version is the current version
    if (_.version !== '0.1.7') return;

    _.FullscreenAPI = _.EventClass.extend(/** @lends FooGallery.utils.FullscreenAPI */{
        /**
         * @summary A wrapper around the fullscreen API to ensure cross browser compatibility.
         * @constructs
         */
        construct: function(){
            this._super();
            /**
             * @summary An object containing a single browsers various methods and events needed for this wrapper.
             * @typedef {Object} FooGallery.utils.FullscreenAPI~BrowserAPI
             * @property {string} enabled
             * @property {string} element
             * @property {string} request
             * @property {string} exit
             * @property {Object} events
             * @property {string} events.change
             * @property {string} events.error
             */

            /**
             * @summary Contains the various browser specific method and event names.
             * @memberof FooGallery.utils.FullscreenAPI#
             * @name apis
             * @type {{w3: BrowserAPI, ms: BrowserAPI, moz: BrowserAPI, webkit: BrowserAPI}}
             */
            this.apis = {
                w3: {
                    enabled: "fullscreenEnabled",
                    element: "fullscreenElement",
                    request: "requestFullscreen",
                    exit:    "exitFullscreen",
                    events: {
                        change: "fullscreenchange",
                        error:  "fullscreenerror"
                    }
                },
                webkit: {
                    enabled: "webkitFullscreenEnabled",
                    element: "webkitCurrentFullScreenElement",
                    request: "webkitRequestFullscreen",
                    exit:    "webkitExitFullscreen",
                    events: {
                        change: "webkitfullscreenchange",
                        error:  "webkitfullscreenerror"
                    }
                },
                moz: {
                    enabled: "mozFullScreenEnabled",
                    element: "mozFullScreenElement",
                    request: "mozRequestFullScreen",
                    exit:    "mozCancelFullScreen",
                    events: {
                        change: "mozfullscreenchange",
                        error:  "mozfullscreenerror"
                    }
                },
                ms: {
                    enabled: "msFullscreenEnabled",
                    element: "msFullscreenElement",
                    request: "msRequestFullscreen",
                    exit:    "msExitFullscreen",
                    events: {
                        change: "MSFullscreenChange",
                        error:  "MSFullscreenError"
                    }
                }
            };
            /**
             * @summary The current browsers specific method and event names.
             * @memberof FooGallery.utils.FullscreenAPI#
             * @name api
             * @type {?BrowserAPI}
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
        destroy: function(){
            this.__stopListening();
            return this._super();
        },
        /**
         * @summary Fetches the correct API for the current browser.
         * @memberof FooGallery.utils.FullscreenAPI#
         * @function getAPI
         * @return {?BrowserAPI} If the fullscreen API is not supported `null` is returned.
         */
        getAPI: function(){
            for (var vendor in this.apis) {
                if (!this.apis.hasOwnProperty(vendor)) continue;
                // Check if document has the "enabled" property
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
        element: function(){
            return this.supported ? document[this.api.element] : null;
        },
        /**
         * @summary Requests the browser to place the specified element into fullscreen mode.
         * @memberof FooGallery.utils.FullscreenAPI#
         * @function request
         * @param {Element} element - The element to place into fullscreen mode.
         * @returns {Promise} A Promise which is resolved once the element is placed into fullscreen mode.
         */
        request: function(element){
            if (this.supported && !!element[this.api.request]){
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
        exit: function(){
            if (this.supported && !!this.element()){
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
        toggle: function(element){
            return !!this.element() ? this.exit() : this.request(element);
        },
        /**
         * @summary Starts listening to the document level fullscreen events and triggers an abbreviated version on this class.
         * @memberof FooGallery.utils.FullscreenAPI#
         * @function __listen
         * @private
         */
        __listen: function(){
            var self = this;
            if (!self.supported) return;
            $(document).on(self.api.events.change + ".utils", function() {
                self.trigger("change");
            }).on(self.api.events.error + ".utils", function() {
                self.trigger("error");
            });
        },
        /**
         * @summary Stops listening to the document level fullscreen events.
         * @memberof FooGallery.utils.FullscreenAPI#
         * @function __stopListening
         * @private
         */
        __stopListening: function(){
            var self = this;
            if (!self.supported) return;
            $(document).off(self.api.events.change + ".utils")
                .off(self.api.events.error + ".utils");
        },
        /**
         * @summary Creates a resolver function to patch browsers which do not return a Promise from there request and exit methods.
         * @memberof FooGallery.utils.FullscreenAPI#
         * @function __resolver
         * @param {string} method - The request or exit method the resolver is being created for.
         * @returns {resolver}
         * @private
         */
        __resolver: function(method){
            var self = this;
            /**
             * @summary Binds to the fullscreen change and error events and resolves or rejects the supplied deferred accordingly.
             * @callback FooGallery.utils.FullscreenAPI~resolver
             * @param {jQuery.Deferred} def - The jQuery.Deferred object to resolve.
             */
            return function resolver(def) {
                // Reject the promise if asked to exitFullscreen and there is no element currently in fullscreen
                if (method === self.api.exit && !!self.element()) {
                    setTimeout(function() {
                        def.reject(new TypeError());
                    }, 1);
                    return;
                }

                // When receiving an internal fullscreenchange event, fulfill the promise
                function change() {
                    def.resolve();
                    $(document).off(self.api.events.change, change)
                        .off(self.api.events.error, error);
                }

                // When receiving an internal fullscreenerror event, reject the promise
                function error() {
                    def.reject(new TypeError());
                    $(document).off(self.api.events.change, change)
                        .off(self.api.events.error, error);
                }

                $(document).on(self.api.events.change, change)
                    .on(self.api.events.error, error);
            };
        }
    });

    /**
     * @summary A cross browser wrapper for the fullscreen API.
     * @memberof FooGallery.utils
     * @name fullscreen
     * @type {FooGallery.utils.FullscreenAPI}
     */
    _.fullscreen = new _.FullscreenAPI();

})(
    FooGallery.utils.$,
    FooGallery.utils,
    FooGallery.utils.fn
);
(function ($, _, _utils, _is, _fn) {

	_.debug = new _utils.Debugger("__FooGallery__");

	/**
	 * @summary The url of an empty 1x1 pixel image used as the default value for the `placeholder` and `error` {@link FooGallery.defaults|options}.
	 * @memberof FooGallery
	 * @name EMPTY_IMAGE
	 * @type {string}
	 * @default "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
	 */
	_.EMPTY_IMAGE = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";

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
					return _.template.make(options, element).initialize();
				});
			}
		}
		return _.template.make(options, element).initialize();
	};

	_.initAll = function (options) {
		return _fn.when($(".foogallery").map(function (i, element) {
			return _.init(options, element);
		}).get());
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

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is,
		FooGallery.utils.fn
);
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
                    "auto-progress": '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path class="[ICON_CLASS]-idle" d="M11.39 8c2.152-1.365 3.61-3.988 3.61-7 0-0.339-0.019-0.672-0.054-1h-13.891c-0.036 0.328-0.054 0.661-0.054 1 0 3.012 1.457 5.635 3.609 7-2.152 1.365-3.609 3.988-3.609 7 0 0.339 0.019 0.672 0.054 1h13.891c0.036-0.328 0.054-0.661 0.054-1 0-3.012-1.457-5.635-3.609-7zM2.5 15c0-2.921 1.253-5.397 3.5-6.214v-1.572c-2.247-0.817-3.5-3.294-3.5-6.214v0h11c0 2.921-1.253 5.397-3.5 6.214v1.572c2.247 0.817 3.5 3.294 3.5 6.214h-11zM9.462 10.462c-1.12-0.635-1.181-1.459-1.182-1.959v-1.004c0-0.5 0.059-1.327 1.184-1.963 0.602-0.349 1.122-0.88 1.516-1.537h-6.4c0.395 0.657 0.916 1.188 1.518 1.538 1.12 0.635 1.181 1.459 1.182 1.959v1.004c0 0.5-0.059 1.327-1.184 1.963-1.135 0.659-1.98 1.964-2.236 3.537h7.839c-0.256-1.574-1.102-2.879-2.238-3.538z"/><circle class="[ICON_CLASS]-circle" r="4" cx="8" cy="8"/><path class="[ICON_CLASS]-play" d="M3 2l10 6-10 6z"/><path class="[ICON_CLASS]-pause" d="M2 2h5v12h-5zM9 2h5v12h-5z"/></svg>'
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
					if (!self.opt.allowPageScroll.x && $.inArray(dir, ['NE','E','SE','NW','W','SW']) !== -1){
						event.preventDefault();
					}
					if (!self.opt.allowPageScroll.y && $.inArray(dir, ['NW','N','NE','SW','S','SE']) !== -1){
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
(function ($, _, _utils, _is, _fn, _obj) {

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
			var self = this, type = _is.hash(options) && _is.hash(options) && _is.string(options.type) && self.contains(options.type) ? options.type : "core";
			if (type === "core" && element.length > 0) {
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
		configure: function (name, options, classes, il8n) {
			var self = this;
			if (self.contains(name)) {
				var reg = self.registered;
				_obj.extend(reg[name].opt, options);
				_obj.extend(reg[name].cls, classes);
				_obj.extend(reg[name].il8n, il8n);
			}
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
		FooGallery.utils,
		FooGallery.utils.is,
		FooGallery.utils.fn,
		FooGallery.utils.obj
);
(function(_, _utils, _is, _fn, _obj){

	_.FilteringFactory = _utils.Factory.extend(/** @lends FooGallery.FilteringFactory */{
		/**
		 * @summary A factory for filtering types allowing them to be easily registered and created.
		 * @memberof FooGallery
		 * @constructs FilteringFactory
		 * @description The plugin makes use of an instance of this class exposed as {@link FooGallery.filtering}.
		 * @augments FooGallery.Factory
		 * @borrows FooGallery.Factory.extend as extend
		 * @borrows FooGallery.Factory.override as override
		 */
		construct: function(){
			/**
			 * @summary An object containing all registered filtering types.
			 * @memberof FooGallery.FilteringFactory#
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
		 * @summary Registers a filtering `type` constructor with the factory using the given `name` and `test` function.
		 * @memberof FooGallery.FilteringFactory#
		 * @function register
		 * @param {string} name - The friendly name of the class.
		 * @param {FooGallery.Filtering} type - The filtering type constructor to register.
		 * @param {FooGallery.FilteringControl} [ctrl] - An optional control to register for the filtering type.
		 * @param {object} [options={}] - The default options for the filtering type.
		 * @param {object} [classes={}] - The CSS classes for the filtering type.
		 * @param {object} [il8n={}] - The il8n strings for the filtering type.
		 * @param {number} [priority=0] - This determines the index for the class when using either the {@link FooGallery.FilteringFactory#load|load} or {@link FooGallery.FilteringFactory#names|names} methods, a higher value equals a lower index.
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
			return _is.hash(options) && _is.hash(opt = options.filtering) && _is.string(opt.type) && self.contains(opt.type) ? opt.type : null;
		},
		merge: function(options){
			options = _obj.extend({}, options);
			var self = this, type = self.type(options),
				reg = self.registered,
				def = reg["default"].opt,
				def_cls = reg["default"].cls,
				def_il8n = reg["default"].il8n,
				opt = _is.hash(options.filtering) ? options.filtering : {},
				cls = _is.hash(options.cls) && _is.hash(options.cls.filtering) ? _obj.extend({}, options.cls.filtering) : {},
				il8n = _is.hash(options.il8n) && _is.hash(options.il8n.filtering) ? _obj.extend({}, options.il8n.filtering) : {};

			if (!_is.hash(options.cls)) options.cls = {};
			if (!_is.hash(options.il8n)) options.il8n = {};
			if (type !== "default" && self.contains(type)){
				options.filtering = _obj.extend({}, def, reg[type].opt, opt, {type: type});
				options.cls = _obj.extend(options.cls, {filtering: def_cls}, {filtering: reg[type].cls}, {filtering: cls});
				options.il8n = _obj.extend(options.il8n, {filtering: def_il8n}, {filtering: reg[type].il8n}, {filtering: il8n});
			} else {
				options.filtering = _obj.extend({}, def, opt, {type: type});
				options.cls = _obj.extend(options.cls, {filtering: def_cls}, {filtering: cls});
				options.il8n = _obj.extend(options.il8n, {filtering: def_il8n}, {filtering: il8n});
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
		 * @memberof FooGallery.FilteringFactory#
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
		 * @memberof FooGallery.FilteringFactory#
		 * @function makeCtrl
		 * @param {string} name - The friendly name of the class.
		 * @param {FooGallery.Template} template - The template creating the control.
		 * @param {FooGallery.Filtering} parent - The parent filtering class creating the control.
		 * @param {string} position - The position the control will be displayed at.
		 * @returns {?FooGallery.FilteringControl}
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
	 * @summary The factory used to register and create the various filtering types of FooGallery.
	 * @memberof FooGallery
	 * @name filtering
	 * @type {FooGallery.FilteringFactory}
	 */
	_.filtering = new _.FilteringFactory();

})(
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.obj
);
(function($, _, _utils, _is, _obj, _fn){

    var instance = 0;

    _.Breakpoints = _utils.Class.extend({
        construct: function(options){
            var self = this;

            self.namespace = ".foogallery-breakpoints-" + (++instance);

            self.opt = _obj.extend({}, _.Breakpoints.defaults, options);

            self.registered = [];

            self.robserver = new ResizeObserver(_fn.throttle(function (entries) {
                entries.forEach(function (entry) {
                    self.checkEntry(entry);
                });
            }, 50));

            // $(window).on("resize" + self.namespace, _fn.debounce(function(){
            //     self.check();
            // }, 50));
        },

        destroy: function(){
            // $(window).off(this.namespace);
            this.robserver.disconnect();
            this.registered = [];
        },

        register: function( $el, breakpoints, callback, thisArg ){
            if (!_is.jq($el) || !_is.hash(breakpoints)) return -1;
            var self = this,
                parsed = self.parse( breakpoints ),
                classNames = parsed.reduce(function(acc, bp){
                    return acc.concat([bp.className, bp.className + self.opt.suffixWidth, bp.className + self.opt.suffixHeight]);
                }, [self.opt.prefix + "portrait", self.opt.prefix + "landscape"]).join(" ");

            self.robserver.observe($el.get(0));
            return self.registered.push({
                $element: $el,
                simple: parsed.every(function(bp){
                    return bp.width > 0 && bp.height === 0;
                }),
                current: "",
                orientation: null,
                breakpoints: parsed,
                classNames: classNames,
                callback: _is.fn(callback) ? callback : $.noop,
                thisArg: !_is.undef(thisArg) ? thisArg : self
            }) - 1;
        },

        remove: function( $el ){
            if (!_is.jq($el)) return;
            var self = this;
            self.robserver.unobserve($el.get(0));
            self.registered = self.registered.filter(function(x){
                return x.$element.get(0) !== $el.get(0);
            });
        },

        find: function( el ){
            var self = this;
            for (var i = 0, l = self.registered.length, r; i < l; i++){
                r = self.registered[i];
                if (r.$element.get(0) !== el) continue;
                return r;
            }
            return null;
        },

        current: function( $el ){
            if (!_is.jq($el)) return "";
            var self = this, registered = self.find( $el.get(0) );
            return _is.hash(registered) ? registered.current : "";
        },

        parse: function( breakpoints ){
            var self = this, result = [];
            for (var name in breakpoints){
                if (!breakpoints.hasOwnProperty(name)) continue;
                var width, height;
                if (_is.number(breakpoints[name])){
                    width = breakpoints[name];
                    height = 0;
                } else if (_is.hash(breakpoints[name])){
                    width = breakpoints[name].width || 0;
                    height = breakpoints[name].height || 0;
                }
                result.push({
                    name: name,
                    width: width,
                    height: height,
                    className: self.opt.prefix + name
                });
            }
            result.sort(function (a, b) {
                if (a.width < b.width) return -1;
                if (a.width > b.width) return 1;
                return 0;
            });
            return result;
        },

        check: function( $el ){
            var self = this;
            if (_is.jq($el)){
                var registered = self.find( $el.get(0) );
                if (_is.hash(registered)){
                    self.checkRegistered(registered, self.getSize(registered));
                }
            } else {
                self.registered.forEach(function (registered) {
                    self.checkRegistered(registered, self.getSize(registered));
                }, self);
            }
        },

        checkEntry: function( entry ){
            var self = this, registered = self.find( entry.target ), rect = !!entry ? entry.contentRect : null;
            if (_is.hash(registered) && !!rect){
                self.checkRegistered(registered, { width: entry.contentRect.width || 0, height: entry.contentRect.height || 0, isValid: true });
            }
        },

        checkRegistered: function( registered, size ){
            var prevOrientation = registered.orientation,
                nextOrientation = this.opt.prefix + (size.height > size.width ? "portrait" : "landscape"),
                prevBreakpoint = registered.current,
                nextBreakpoint = this.getCurrent( registered, size );
            if (nextBreakpoint !== prevBreakpoint || nextOrientation !== prevOrientation){
                registered.current = nextBreakpoint;
                registered.$element.removeClass(registered.classNames).addClass([nextBreakpoint, nextOrientation].join(" "));
                registered.callback.call(registered.thisArg, registered, nextBreakpoint, nextOrientation, prevBreakpoint, prevOrientation);
            }
        },

        getSize: function( registered ){
            var width, height;
            if (!registered.$element.is(':visible')){
                var $el = registered.$element.parents(':visible:first');
                width = $el.innerWidth();
                height = $el.innerHeight();
            } else {
                width = registered.$element.width();
                height = registered.$element.height();
            }
            var hasWidth = _is.number(width), hasHeight = _is.number(height);
            return {
                width: hasWidth ? width : 0,
                height: hasHeight ? height : 0,
                isValid: hasWidth && hasHeight
            };
        },

        getCurrent: function( registered, size ){
            if (!_is.hash(size) || !size.isValid) return "";
            var self = this, result = [], hasWidth = false, hasHeight = false;
            for (var i = 0, l = registered.breakpoints.length, bp, validWidth, validHeight, matchWidth, matchHeight, match; i < l; i++){
                bp = registered.breakpoints[i];
                validWidth = bp.width > 0 && (self.opt.mobileFirst ? size.width >= bp.width : size.width < bp.width);
                validHeight = bp.height > 0 && (self.opt.mobileFirst ? size.height >= bp.height : size.height < bp.height);
                if (validWidth || validHeight) {
                    if (registered.simple){
                        result.push(bp.className);
                    } else {
                        matchWidth = validWidth && (self.opt.mobileFirst || !hasWidth);
                        matchHeight = validHeight && (self.opt.mobileFirst || !hasHeight);
                        match = self.opt.mobileFirst ? matchWidth && matchHeight : matchWidth || matchHeight;
                        if (match){
                            result.push(bp.className);
                        }
                        if (matchWidth){
                            result.push(self.opt.prefix + bp.name + self.opt.suffixWidth);
                            hasWidth = true;
                        }
                        if (matchHeight){
                            result.push(self.opt.prefix + bp.name + self.opt.suffixHeight);
                            hasHeight = true;
                        }
                        if (!self.opt.mobileFirst && hasWidth && hasHeight){
                            break;
                        }
                    }
                }
            }
            return result.join(" ");
        }

    });

    _.Breakpoints.defaults = {
        prefix: "fg-",
        suffixWidth: "-width",
        suffixHeight: "-height",
        mobileFirst: true
    };

    _.Breakpoints.NONE = {
        name: "none",
        width: Infinity,
        height: Infinity,
        className: ""
    };

    _.breakpoints = new _.Breakpoints();

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.obj,
    FooGallery.utils.fn
);
(function ($, _, _utils, _is, _fn, _str) {

	var instance = 0;

	_.Template = _utils.EventClass.extend(/** @lends FooGallery.Template */{
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
			self._checkTimeout = null;
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
		 * @fires FooGallery.Template~"first-load.foogallery"
		 * @fires FooGallery.Template~"ready.foogallery"
		 */
		initialize: function (parent) {
			var self = this;
			if (_is.promise(self._initialize)) return self._initialize;
			return self._initialize = $.Deferred(function (def) {
				if (self.preInit(parent)){
					self.init().then(function(){
						if (self.postInit()){
							self.firstLoad().then(function(){
								self.ready();
								def.resolve(self);
							}).fail(def.reject);
						} else {
							def.reject("post-init failed");
						}
					}).fail(def.reject);
				} else {
					def.reject("pre-init failed");
				}
			}).fail(function (err) {
				console.log("initialize failed", self, err);
				self.destroy();
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
				self.$el.on(self.opt.on);
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
			return !self.raise("pre-init").isDefaultPrevented();
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
			var e = self.raise("init");
			if (e.isDefaultPrevented()) return _fn.rejectWith("init default prevented");
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
			var e = self.raise("post-init");
			if (e.isDefaultPrevented()) return false;
			self.state.init();
			self.$scrollParent.on("scroll" + self.namespace, {self: self}, _fn.throttle(function () {
				self.loadAvailable();
			}, 50));
			$(window).on("popstate" + self.namespace, {self: self}, self.onWindowPopState);
			return true;
		},
		/**
		 * @summary Occurs after all template initialization work is completed.
		 * @memberof FooGallery.Template#
		 * @function firstLoad
		 * @returns {Promise}
		 * @fires FooGallery.Template~"first-load.foogallery"
		 */
		firstLoad: function(){
			var self = this;
            if (self.destroying) return _fn.rejected;
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
			// performed purely to re-check if any items need to be loaded after content has possibly shifted
			self._check(1000);
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
            self.raise("destroy");
			if (self._checkTimeout) clearTimeout(self._checkTimeout);
            self.$scrollParent.off(self.namespace);
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
            self.raise("destroyed");
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

		/**
		 * @summary Check if any available items need to be loaded and loads them.
		 * @memberof FooGallery.Template#
		 * @function loadAvailable
		 * @returns {Promise<FooGallery.Item[]>} Resolves with an array of {@link FooGallery.Item|items} as the first argument. If no items are loaded this array is empty.
		 */
		loadAvailable: function () {
			return this.items.load(this.getAvailable());
		},

		getItems: function(){
			return this.pages ? this.pages.items() : this.items.available();
		},

		/**
		 * @summary Check if any available items need to be loaded and loads them.
		 * @memberof FooGallery.Template#
		 * @function _check
		 * @private
		 */
		_check: function (delay) {
			delay = _is.number(delay) ? delay : 0;
			var self = this;
			if (self._checkTimeout) clearTimeout(self._checkTimeout);
			return self._checkTimeout = setTimeout(function () {
				self._checkTimeout = null;
				if (self.initialized && (!self.destroying || !self.destroyed)) {
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
			if (this.destroying || this.destroyed || !_is.string(eventName) || _is.empty(eventName)) return null;
			args = _is.array(args) ? args : [];
			var self = this,
					name = eventName.split(".")[0],
					listener = _str.camel("on-" + name),
					event = $.Event(name + ".foogallery");
			args.unshift(self); // add self
			var e = self.trigger(name, args);
			if (e.defaultPrevented) event.preventDefault();
			self.$el.trigger(event, args);
			_.debug.logf("{id}|{name}:", {id: self.id, name: name}, args);
			if (_is.fn(self[listener])) {
				args.unshift(event); // add event
				self[listener].apply(self.$el.get(0), args);
			}
			return event;
		},

		layout: function () {
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
		 * @summary Gets the width of the FooGallery container.
		 * @memberof FooGallery.Template#
		 * @function
		 * @name getContainerWidth
		 * @returns {number}
		 */
		getContainerWidth: function(){
			var self = this, visible = self.$el.is(':visible');
			if (!visible){
				return self.$el.parents(':visible:first').innerWidth();
			}
			return self.$el.width();
		},

		/**
		 * @summary Gets a specific type of CSS class from the template.
		 * @memberof FooGallery.Template#
		 * @function
		 * @name getCSSClass
		 * @param {string} type - The specific type of CSS class to retrieve.
		 * @returns {string}
		 */
		getCSSClass: function(type){
			var regex = type instanceof RegExp ? type : (_is.string(type) && this.opt.regex.hasOwnProperty(type) ? this.opt.regex[type] : null),
				className = (this.$el.prop("className") || ''),
				match = regex != null ? className.match(regex) : null;
			return match != null && match.length >= 2 ? match[1] : "";
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
				self.loadAvailable();
			}
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
		fixLayout: true,
		scrollParent: null,
		delay: 0,
		throttle: 50,
		timeout: 60000,
		srcset: "data-srcset-fg",
		src: "data-src-fg",
		template: {},
		regex: {
			theme: /(?:\s|^)(fg-(?:light|dark|custom))(?:\s|$)/,
			loadingIcon: /(?:\s|^)(fg-loading-(?:default|bars|dots|partial|pulse|trail))(?:\s|$)/,
			hoverIcon: /(?:\s|^)(fg-hover-(?:zoom|zoom2|zoom3|plus|circle-plus|eye|external|tint))(?:\s|$)/,
			videoIcon: /(?:\s|^)(fg-video-(?:default|1|2|3|4))(?:\s|$)/,
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
	 * @property {number} [viewport=200] - The number of pixels to inflate the viewport by when checking to lazy load items.
	 * @property {(FooGallery.Item~Options[]|FooGallery.Item[]| string)} [items=[]] - An array of items to load when required. A url can be provided and the items will be fetched using an ajax call, the response should be a properly formatted JSON array of {@link FooGallery.Item~Options|item} object.
	 * @property {boolean} [fixLayout=true] - Whether or not the items' size should be set with CSS until the image is loaded.
	 * @property {string} [scrollParent=null] - The selector used to bind to the scroll parent for the gallery. If not supplied the template will attempt to find the element itself.
	 * @property {number} [delay=0] - The number of milliseconds to delay the initialization of a template.
	 * @property {number} [throttle=50] - The number of milliseconds to wait once scrolling has stopped before performing any work.
	 * @property {number} [timeout=60000] - The number of milliseconds to wait before forcing a timeout when loading items.
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

	_.EventComponent = _utils.EventClass.extend(/** @lend FooGallery.EventComponent */{
		/**
		 * @summary The base class for all child components of a {@link FooGallery.Template|template} that raise there own events.
		 * @constructs
		 * @param {FooGallery.Template} template - The template creating the component.
		 * @param {string} prefix - A prefix to prepend to any events bubbled up to the template.
		 * @augments FooGallery.utils.EventClass
		 * @borrows FooGallery.utils.Class.extend as extend
		 * @borrows FooGallery.utils.Class.override as override
		 */
		construct: function(template, prefix){
			this._super(template);
			/**
			 * @summary The template that created this component.
			 * @memberof FooGallery.EventComponent#
			 * @name tmpl
			 * @type {FooGallery.Template}
			 */
			this.tmpl = template;
			/**
			 * @summary A prefix to prepend to any events bubbled up to the template.
			 * @memberof FooGallery.EventComponent#
			 * @name tmplEventPrefix
			 * @type {string}
			 */
			this.tmplEventPrefix = prefix;
		},
		/**
		 * @summary Destroy the component making it ready for garbage collection.
		 * @memberof FooGallery.EventComponent#
		 * @function destroy
		 */
		destroy: function(){
			this._super();
			this.tmpl = null;
		},
		/**
		 * @summary Trigger an event on the current component.
		 * @memberof FooGallery.EventComponent#
		 * @function trigger
		 * @param {(string|FooGallery.utils.Event)} event - Either a space-separated string of event types or a custom event object to raise.
		 * @param {Array} [args] - An array of additional arguments to supply to the handlers after the event object.
		 * @returns {(FooGallery.utils.Event|FooGallery.utils.Event[]|null)} Returns the {@link FooGallery.utils.Event|event object} of the triggered event. If more than one event was triggered an array of {@link FooGallery.utils.Event|event objects} is returned. If no `event` was supplied or triggered `null` is returned.
		 */
		trigger: function(event, args){
			var self = this, result = self._super(event, args), name, e;
			if (self.tmpl != null){
				if (result instanceof _utils.Event && !result.isDefaultPrevented()){
					name = result.namespace != null ? [result.type, result.namespace].join(".") : result.type;
					e = self.tmpl.raise(self.tmplEventPrefix + name, args);
					if (!!e && e.isDefaultPrevented()) result.preventDefault();
				} else if (_is.array(result)){
					result.forEach(function (evt) {
						if (!evt.isDefaultPrevented()){
							name = evt.namespace != null ? [evt.type, evt.namespace].join(".") : evt.type;
							e = self.tmpl.raise(self.tmplEventPrefix + name, args);
							if (!!e && e.isDefaultPrevented()) evt.preventDefault();
						}
					});
				}
			}
			return _is.empty(result) ? null : (result.length === 1 ? result[0] : result);
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
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _is, _str, _obj){

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
		},
		/**
		 * @summary Destroy the component clearing any current state from the url and preparing it for garbage collection.
		 * @memberof FooGallery.State#
		 * @function destroy
		 * @param {boolean} [preserve=false] - If set to true any existing state is left intact on the URL.
		 */
		destroy: function(preserve){
			var self = this;
			if (!preserve) self.clear();
			self.opt = self.regex = {};
			self._super();
		},
		init: function(){
			this.set(this.initial());
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
			return $.inArray(value, ["push","replace"]) !== -1;
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
				var e = tmpl.raise("before-state", [obj]);
				if (!e.isDefaultPrevented()){
					if (!!tmpl.filter){
						tmpl.filter.setState(obj);
					}
					if (!!tmpl.pages){
						tmpl.pages.setState(obj);
					} else {
						tmpl.items.detach(tmpl.items.all());
						tmpl.items.create(tmpl.items.available(), true);
					}
					if (obj.item){
						if (self.opt.scrollTo) {
							obj.item.scrollTo();
						}
						if (!_is.empty(state.item)){
							state.item = null;
							self.replace(state);
						}
					}
					self.current = obj;
					tmpl.raise("after-state", [obj]);
				}
			}
		},
	});

	_.template.configure("core", {
		state: {
			enabled: false,
			scrollTo: true,
			pushOrReplace: "replace",
			mask: "foogallery-gallery-{id}",
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
	 * @property {boolean} [enabled=false] - Whether or not state is enabled for the template.
	 * @property {string} [pushOrReplace="replace"] - Which method of the history API to use by default when updating the state.
	 * @property {string} [values="/"] - The delimiter used between key value pairs in the hash.
	 * @property {string} [pair=":"] - The delimiter used between a key and a value in the hash.
	 * @property {string} [array="+"] - The delimiter used for array values in the hash.
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
	FooGallery.utils.is,
	FooGallery.utils.str,
	FooGallery.utils.obj
);
(function ($, _, _utils, _is, _fn, _obj) {

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
		construct: function (template) {
			var self = this;
			self.ALLOW_CREATE = true;
			self.ALLOW_APPEND = true;
			self.ALLOW_LOAD = true;
			/**
			 * @ignore
			 * @memberof FooGallery.Items#
			 * @function _super
			 */
			self._super(template);
			self.maps = {};
			self._fetched = null;
			self._arr = [];
			self._available = [];
			// add the .all caption selector
			var cls = self.tmpl.cls.item.caption;
			self.tmpl.sel.item.caption.all = _utils.selectify([cls.elem, cls.inner, cls.title, cls.description]);
		},
		fromHash: function(hash){
			return this.get(hash);
		},
		toHash: function(value){
			return value instanceof _.Item ? value.id : null;
		},
		destroy: function () {
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
				if (destroyed.length > 0) self.tmpl.raise("destroyed-items", [destroyed]);
				// should we handle a case where the destroyed.length != items.length??
			}
			self.maps = {};
			self._fetched = null;
			self._arr = [];
			self._available = [];
			self._super();
		},
		fetch: function (refresh) {
			var self = this;
			if (!refresh && _is.promise(self._fetched)) return self._fetched;
			var fg = self.tmpl, selectors = fg.sel,
					option = fg.opt.items,
					def = $.Deferred();

			var items = self.make(fg.$el.find(selectors.item.elem));

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
				items.push.apply(items, self.make(window[fg.id + "-items"]));
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
			return this._arr.slice();
		},
		count: function (all) {
			return all ? this.all().length : this.available().length;
		},
		available: function () {
			return this._available.slice();
		},
		get: function (idOrIndex) {
			var map = _is.number(idOrIndex) ? 'index' : 'id';
			return !!this.maps[map][idOrIndex] ? this.maps[map][idOrIndex] : null;
		},
		setAll: function (items) {
			this._arr = _is.array(items) ? items : [];
			this.maps = this.createMaps(this._arr);
			this._available = this.all();
		},
		setAvailable: function (items) {
			this.maps = this.createMaps(this._arr);
			this._available = _is.array(items) ? items : [];
		},
		reset: function () {
			this.setAvailable(this.all());
		},
		first: function(){
			return this._available.length > 0 ? this._available[0] : null;
		},
		last: function(){
			return this._available.length > 0 ? this._available[this._available.length - 1] : null;
		},
		next: function(item, loop){
			if (!(item instanceof _.Item)) return null;
			loop = _is.boolean(loop) ? loop : false;
			var index = this._available.indexOf(item);
			if (index !== -1){
				index++;
				if (index >= this._available.length){
					if (!loop) return null;
					index = 0;
				}
				return this._available[index];
			}
			return null;
		},
		prev: function(item, loop){
			if (!(item instanceof _.Item)) return null;
			loop = _is.boolean(loop) ? loop : false;
			var index = this._available.indexOf(item);
			if (index !== -1){
				index--;
				if (index < 0){
					if (!loop) return null;
					index = this._available.length - 1;
				}
				return this._available[index];
			}
			return null;
		},
		createMaps: function(items){
			items = _is.array(items) ? items : [];
			var maps = {
				id: {},
				index: {}
			};
			$.each(items, function (i, item) {
				if (_is.empty(item.id)) item.id = "" + (i + 1);
				item.index = i;
				maps.id[item.id] = item;
				maps.index[item.index] = item;
			});
			return maps;
		},
		/**
		 * @summary Filter the supplied `items` and return only those that can be loaded.
		 * @memberof FooGallery.Items#
		 * @function loadable
		 * @param {FooGallery.Item[]} items - The items to filter.
		 * @returns {FooGallery.Item[]}
		 */
		loadable: function (items) {
			var self = this, opt = self.tmpl.opt, viewport;
			if (opt.lazy) {
				viewport = _utils.getViewportBounds(opt.viewport);
			}
			return self.ALLOW_LOAD && _is.array(items) ? $.map(items, function (item) {
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
				var e = self.tmpl.raise("make-items", [arr]);
				if (!e.isDefaultPrevented()) {
					made = $.map(arr, function (obj) {
						var type = self.type(obj), opt = _obj.extend(_is.hash(obj) ? obj : {}, {type: type});
						var item = _.components.make(type, self.tmpl, opt);
						if (_is.element(obj)) {
							if (item.parse(obj)) {
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
		type: function (objOrElement) {
			var type;
			if (_is.hash(objOrElement)) {
				type = objOrElement.type;
			} else if (_is.element(objOrElement)) {
				var $el = $(objOrElement), item = this.tmpl.sel.item;
				type = $el.find(item.anchor).data("type");
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
				var e = self.tmpl.raise("create-items", [creatable]);
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
				var e = self.tmpl.raise("append-items", [appendable]);
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
				var e = self.tmpl.raise("detach-items", [detachable]);
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
		load: function (items) {
			var self = this;
			items = self.loadable(items);
			if (items.length > 0) {
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
				if (!e.isDefaultPrevented()) {
					var loading = $.map(items, function (item) {
						return item.load();
					});
					return _fn.when(loading).done(function (loaded) {
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
			 * @name fixLayout
			 * @type {boolean}
			 */
			self.fixLayout = self.tmpl.opt.fixLayout;

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
			self.caption = _is.empty(self.opt.caption) ? self.title : self.opt.caption;
			/**
			 * @memberof FooGallery.Item#
			 * @name description
			 * @type {string}
			 */
			self.description = _is.empty(self.opt.description) ? self.alt : self.opt.description;
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
			 * @name maxWidth
			 * @type {?FooGallery.Item~maxWidthCallback}
			 */
			self.maxWidth = self.opt.maxWidth;
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
			 * @summary The cached result of the last call to the {@link FooGallery.Item#getThumbUrl|getThumbUrl} method.
			 * @memberof FooGallery.Item#
			 * @name _thumbUrl
			 * @type {?string}
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
				loader: false,
				wrap: false,
				overlay: false,
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
			var e = self.tmpl.raise("destroy-item", [self]);
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
				self.tmpl.raise("destroyed-item", [self]);
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
				if (_is.empty(self._undo.classes)) self.$el.removeAttr("class");
				else self.$el.attr("class", self._undo.classes);

				if (_is.empty(self._undo.style)) self.$el.removeAttr("style");
				else self.$el.attr("style", self._undo.style);

				if (self._undo.overlay) {
					self.$overlay.remove();
				}
				if (self._undo.wrap) {
					self.$anchor.append(self.$image);
					self.$wrap.remove();
				}
				if (self._undo.loader) {
					self.$el.find(self.sel.loader).remove();
				}
				if (self._undo.placeholder && self.$image.prop("src") === _.EMPTY_IMAGE) {
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
			var e = self.tmpl.raise("parse-item", [self, $el]);
			if (!e.isDefaultPrevented() && (self.isCreated = $el.is(self.sel.elem))) {
				self.isParsed = self.doParseItem($el);
				if (self.fixLayout) self.fix();
				// We don't load the attributes when parsing as they are only ever used to create an item and if you're parsing it's already created.
			}
			if (self.isParsed) {
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
		 * @summary Performs the actual parse logic for the item.
		 * @memberof FooGallery.Item#
		 * @function doParseItem
		 * @param {jQuery} $el - The jQuery element to parse.
		 * @returns {boolean}
		 */
		doParseItem: function ($el) {
			var self = this, o = self.tmpl.opt, cls = self.cls, sel = self.sel;

			self._undo.classes = $el.attr("class") || "";
			self._undo.style = $el.attr("style") || "";

			self.$el = $el.data(_.DATA_ITEM, self);
			self.$inner = self.$el.children(sel.inner);
			self.$anchor = self.$inner.children(sel.anchor).on("click.foogallery", {self: self}, self.onAnchorClick);
			self.$image = self.$anchor.find(sel.image);
			self.$caption = self.$inner.children(sel.caption.elem).on("click.foogallery", {self: self}, self.onCaptionClick);

			if ( !self.$el.length || !self.$inner.length || !self.$anchor.length || !self.$image.length ){
				console.error("FooGallery Error: Invalid HTML markup. Check the item markup for additional elements or malformed HTML in the title or description.", self);
				self.isError = true;
				self.tmpl.raise("error-item", [self]);
				if (self.$el.length !== 0){
					self.$el.remove();
				}
				return false;
			}

			self.isAttached = self.$el.parent().length > 0;
			self.isLoading = self.$el.is(sel.loading);
			self.isLoaded = self.$el.is(sel.loaded);
			self.isError = self.$el.is(sel.error);

			var data = self.$anchor.attr("data-type", self.type).data();
			self.id = data.id || self.id;
			self.productId = data.productId || self.productId;
			self.tags = data.tags || self.tags;
			self.href = data.href || self.$anchor.attr('href') || self.href;
			self.src = self.$image.attr(o.src) || self.src;
			self.srcset = self.$image.attr(o.srcset) || self.srcset;
			self.width = parseInt(self.$image.attr("width")) || self.width;
			self.height = parseInt(self.$image.attr("height")) || self.height;
			self.title = self.$image.attr("title") || self.title;
			self.alt = self.$image.attr("alt") || self.alt;
			self.caption = data.title || data.captionTitle || self.caption || self.title;
			self.description = data.description || data.captionDesc || self.description || self.alt;
			// if the caption or description are not set yet try fetching it from the html
			if (_is.empty(self.caption)) self.caption = $.trim(self.$caption.find(sel.caption.title).html());
			if (_is.empty(self.description)) self.description = $.trim(self.$caption.find(sel.caption.description).html());
			// enforce the max lengths for the caption and description
			if (_is.number(self.maxCaptionLength) && self.maxCaptionLength > 0 && !_is.empty(self.caption) && _is.string(self.caption) && self.caption.length > self.maxCaptionLength) {
				self.$caption.find(sel.caption.title).html(self.caption.substr(0, self.maxCaptionLength) + "&hellip;");
			}
			if (_is.number(self.maxDescriptionLength) && self.maxDescriptionLength > 0 && !_is.empty(self.description) && _is.string(self.description) && self.description.length > self.maxDescriptionLength) {
				self.$caption.find(sel.caption.description).html(self.description.substr(0, self.maxDescriptionLength) + "&hellip;");
			}
			// check if the item has an overlay
			self.$overlay = self.$anchor.children(sel.overlay);
			if (self.$overlay.length === 0) {
				self.$overlay = $("<span/>", {"class": cls.overlay});
				self.$anchor.append(self.$overlay);
				self._undo.overlay = true;
			}
			// check if the item has a wrap
			self.$wrap = self.$anchor.children(sel.wrap);
			if (self.$wrap.length === 0) {
				self.$wrap = $("<span/>", {"class": cls.wrap});
				self.$anchor.append(self.$wrap.append(self.$image));
				self._undo.wrap = true;
			}
			// check if the item has a loader
			if (self.$el.children(sel.loader).length === 0) {
				self.$el.append($("<div/>", {"class": cls.loader}));
				self._undo.loader = true;
			}
			// if the image has no src url then set the placeholder
			var img = self.$image.get(0);
			if (_is.empty(img.src)) {
				img.src = _.EMPTY_IMAGE;
				self._undo.placeholder = true;
			}
			self.$el.addClass(self.getTypeClass());
			if (self.isCreated && self.isAttached && !self.isLoading && !self.isLoaded && !self.isError) {
				self.$el.addClass(cls.idle);
			}
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
				var e = self.tmpl.raise("create-item", [self]);
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
					self.tmpl.raise("created-item", [self]);
				}
			}
			return self.isCreated;
		},
		/**
		 * @summary Performs the actual create logic for the item.
		 * @memberof FooGallery.Item#
		 * @function doCreateItem
		 * @returns {boolean}
		 */
		doCreateItem: function () {
			var self = this, o = self.tmpl.opt, cls = self.cls, attr = self.attr, type = self.getTypeClass();
			attr.elem["class"] = [cls.elem, type, cls.idle].join(" ");

			attr.inner["class"] = cls.inner;

			attr.anchor["class"] = cls.anchor;
			attr.anchor["href"] = self.href;
			attr.anchor["data-type"] = self.type;
			attr.anchor["data-id"] = self.id;
			attr.anchor["data-title"] = self.caption;
			attr.anchor["data-description"] = self.description;
			if (!_is.empty(self.tags)) {
				attr.anchor["data-tags"] = JSON.stringify(self.tags);
			}
			if (!_is.empty(self.productId)) {
				attr.anchor["data-product-id"] = self.productId;
			}

			attr.image["class"] = cls.image;
			attr.image[o.src] = self.src;
			attr.image[o.srcset] = self.srcset;
			attr.image["width"] = self.width;
			attr.image["height"] = self.height;
			attr.image["title"] = self.title;
			attr.image["alt"] = self.alt;

			self.$el = $("<div/>").attr(attr.elem).data(_.DATA_ITEM, self);
			self.$inner = $("<figure/>").attr(attr.inner).appendTo(self.$el);
			self.$anchor = $("<a/>").attr(attr.anchor).appendTo(self.$inner).on("click.foogallery", {self: self}, self.onAnchorClick);
			self.$overlay = $("<span/>", {"class": cls.overlay}).appendTo(self.$anchor);
			self.$wrap = $("<span/>", {"class": cls.wrap}).appendTo(self.$anchor);
			self.$image = $("<img/>").attr(attr.image).appendTo(self.$wrap);

			cls = self.cls.caption;
			attr = self.attr.caption;
			attr.elem["class"] = cls.elem;
			self.$caption = $("<figcaption/>").attr(attr.elem).on("click.foogallery", {self: self}, self.onCaptionClick);
			attr.inner["class"] = cls.inner;
			var $inner = $("<div/>").attr(attr.inner).appendTo(self.$caption);
			var hasTitle = self.showCaptionTitle && !_is.empty(self.caption), hasDesc = self.showCaptionDescription && !_is.empty(self.description);
			if (hasTitle || hasDesc) {
				attr.title["class"] = cls.title;
				attr.description["class"] = cls.description;
				if (hasTitle) {
					var $title = $("<div/>").attr(attr.title), titleHtml = self.caption;
					// enforce the max length for the caption
					if (_is.number(self.maxCaptionLength) && self.maxCaptionLength > 0 && _is.string(self.caption) && self.caption.length > self.maxCaptionLength) {
						titleHtml = self.caption.substr(0, self.maxCaptionLength) + "&hellip;";
					}
					$title.get(0).innerHTML = titleHtml;
					$inner.append($title);
				}
				if (hasDesc) {
					var $desc = $("<div/>").attr(attr.description), descHtml = self.description;
					// enforce the max length for the description
					if (_is.number(self.maxDescriptionLength) && self.maxDescriptionLength > 0 && _is.string(self.description) && self.description.length > self.maxDescriptionLength) {
						descHtml = self.description.substr(0, self.maxDescriptionLength) + "&hellip;";
					}
					$desc.get(0).innerHTML = descHtml;
					$inner.append($desc);
				}
			}
			self.$caption.appendTo(self.$inner);
			// check if the item has a loader
			if (self.$el.find(self.sel.loader).length === 0) {
				self.$el.append($("<div/>", {"class": self.cls.loader}));
			}
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
				var e = self.tmpl.raise("append-item", [self]);
				if (!e.isDefaultPrevented()) {
					self.tmpl.$el.append(self.$el);
					if (self.fixLayout || !self.isParsed) self.fix();
					self.isAttached = true;
				}
				if (self.isAttached) {
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
		detach: function () {
			var self = this;
			if (self.isCreated && self.isAttached) {
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
				if (!e.isDefaultPrevented()) {
					self.$el.detach();
					if (self.fixLayout || !self.isParsed) self.unfix();
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
		load: function () {
			var self = this;
			if (_is.promise(self._load)) return self._load;
			if (!self.isCreated || !self.isAttached) return _fn.rejectWith("not created or attached");
			var e = self.tmpl.raise("load-item", [self]);
			if (e.isDefaultPrevented()) return _fn.rejectWith("default prevented");
			var cls = self.cls, img = self.$image.get(0), placeholder = img.src;
			self.isLoading = true;
			self.$el.removeClass(cls.idle).removeClass(cls.loaded).removeClass(cls.error).addClass(cls.loading);
			return self._load = $.Deferred(function (def) {
				img.onload = function () {
					img.onload = img.onerror = null;
					self.isLoading = false;
					self.isLoaded = true;
					self.$el.removeClass(cls.loading).addClass(cls.loaded);
					if (self.fixLayout || !self.isParsed) self.unfix();
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
				if (img.complete){
					img.onload();
				}
			}).promise();
		},
		/**
		 * @summary Attempts to set a inline width and height on the {@link FooGallery.Item#$image|$image} to prevent layout jumps.
		 * @memberof FooGallery.Item#
		 * @function fix
		 * @returns {FooGallery.Item}
		 */
		fix: function () {
			var self = this;
			if (self.tmpl == null) return self;
			if (self.isCreated && !self.isLoading && !self.isLoaded && !self.isError) {
				var w = self.width, h = self.height, img = self.$image.get(0);
				// if we have a base width and height to work with
				if (!isNaN(w) && !isNaN(h) && !!img) {
					// figure out the max image width and calculate the height the image should be displayed as
					var width = _is.fn(self.maxWidth) ? self.maxWidth(self) : self.$image.width();
					if (width <= 0) width = w;
					var ratio = width / w, height = h * ratio;
					// actually set the inline css on the image
					self.$image.css({width: width, height: height});
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
		unfix: function () {
			var self = this;
			if (self.tmpl == null) return self;
			if (self.isCreated) self.$image.css({width: '', height: ''});
			return self;
		},
		/**
		 * @summary Inspect the `src` and `srcset` properties to determine which url to load for the thumb.
		 * @memberof FooGallery.Item#
		 * @function getThumbSrc
		 * @param {number} renderWidth - The rendered width of the image to fetch the url for.
		 * @param {number} renderHeight - The rendered height of the image to fetch the url for.
		 * @returns {string}
		 */
		getThumbSrc: function(renderWidth, renderHeight){
			return _utils.src(this.src, this.srcset, this.width, this.height, renderWidth, renderHeight);
		},
		/**
		 * @summary Inspect the `src` and `srcset` properties to determine which url to load for the thumb.
		 * @memberof FooGallery.Item#
		 * @function getThumbUrl
		 * @param {boolean} [refresh=false] - Whether or not to force refreshing of the cached value.
		 * @returns {string}
		 */
		getThumbUrl: function (refresh) {
			refresh = _is.boolean(refresh) ? refresh : false;
			var self = this;
			if (!refresh && _is.string(self._thumbUrl)) return self._thumbUrl;
			return self._thumbUrl = self.getThumbSrc(self.$anchor.innerWidth(), self.$anchor.innerHeight());
		},
		/**
		 * @summary Gets the type specific CSS class for the item.
		 * @memberof FooGallery.Item#
		 * @function getTypeClass
		 * @returns {string}
		 */
		getTypeClass: function(){
			return this.cls.types.hasOwnProperty(this.type) ? this.cls.types[this.type] : "";
		},
		/**
		 * @summary Scroll the item into the center of the viewport.
		 * @memberof FooGallery.Item#
		 * @function scrollTo
		 */
		scrollTo: function (align) {
			var self = this;
			if (self.isAttached) {
				var ib = self.bounds(), vb = _utils.getViewportBounds();
				switch (align) {
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
		bounds: function () {
			return this.isAttached ? _utils.getElementBounds(this.$el) : null;
		},
		/**
		 * @summary Checks if the item bounds intersects the supplied bounds.
		 * @memberof FooGallery.Item#
		 * @function intersects
		 * @param {FooGallery.utils.Bounds} bounds - The bounds to check.
		 * @returns {boolean}
		 */
		intersects: function (bounds) {
			return this.isAttached ? this.bounds().intersects(bounds) : false;
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
			var self = e.data.self, evt = self.tmpl.raise("anchor-click-item", [self]);
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
			var self = e.data.self, evt = self.tmpl.raise("caption-click-item", [self]);
			if (!evt.isDefaultPrevented() && self.$anchor.length > 0 && !$(e.target).is("a,:input")) {
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
	 * @property {?FooGallery.Item~maxWidthCallback} [maxWidth=null] - Called when setting an items' image size. If not supplied the images outer width is used.
	 * @property {number} [maxCaptionLength=0] - The max length of the title for the caption.
	 * @property {number} [maxDescriptionLength=0] - The max length of the description for the caption.
	 * @property {boolean} [showCaptionTitle=true] - Whether or not the caption title should be displayed.
	 * @property {boolean} [showCaptionDescription=true] - Whether or not the caption description should be displayed.
	 * @property {FooGallery.Item~Attributes} [attr] - Additional attributes to apply to the items' elements.
	 */
	_.template.configure("core", {
		item: {
			type: "item",
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
			overlay: "fg-image-overlay",
			wrap: "fg-image-wrap",
			image: "fg-image",
			loader: "fg-loader",
			idle: "fg-idle",
			loading: "fg-loading",
			loaded: "fg-loaded",
			error: "fg-error",
			types: {
				item: "fg-type-unknown"
			},
			caption: {
				elem: "fg-caption",
				inner: "fg-caption-inner",
				title: "fg-caption-title",
				description: "fg-caption-desc"
			}
		}
	}, {
		item: {}
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
(function($, _, _utils, _is){

	_.Video = _.Item.extend({
		construct: function(template, options){
			var self = this;
			self._super(template, options);
			self.cover = self.opt.cover;
		},
		doParseItem: function($element){
			var self = this;
			if (self._super($element)){
				self.cover = self.$anchor.data("cover") || self.cover;
				return true;
			}
			return false;
		},
		doCreateItem: function(){
			var self = this;
			if (self._super()){
				self.$anchor.attr("data-cover", self.cover);
				return true;
			}
			return false;
		},
		toJSON: function(){
			var json = this._super();
			json.cover = this.cover;
			return json;
		}
	});

	_.template.configure("core", {
		item: {
			cover: ""
		}
	},{
		item: {
			types: {
				video: "fg-type-video"
			}
		}
	});

	_.components.register("video", _.Video);

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
);
(function($, _, _utils, _is){

	_.Iframe = _.Item.extend({});

	_.template.configure("core", null,{
		item: {
			types: {
				iframe: "fg-type-iframe"
			}
		}
	});

	_.components.register("iframe", _.Iframe);

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
);
(function($, _, _utils, _is){

	_.Html = _.Item.extend({});

	_.template.configure("core", null,{
		item: {
			types: {
				html: "fg-type-html"
			}
		}
	});

	_.components.register("html", _.Html);

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
);
(function($, _, _utils, _is){

	_.Embed = _.Video.extend({});

	_.template.configure("core", null,{
		item: {
			types: {
				embed: "fg-type-embed fg-type-video"
			}
		}
	});

	_.components.register("embed", _.Embed);

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
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
			if (!!state.item && !this.contains(state.page, state.item)){
				state.page = this.find(state.item);
				state.page = state.page !== 0 ? state.page : 1;
			}
			this.set(state.page, false, false, true);
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
			var index = pageNumber - 1;
			self.tmpl.items.detach(self.tmpl.items.all());
			self.current = pageNumber;
			self.tmpl.items.create(self._arr[index], true);
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
				var num = self.number(pageNumber), state;
				if (num !== self.current) {
					var prev = self.current, setPage = function () {
						updateState = _is.boolean(updateState) ? updateState : true;
						isFilter = _is.boolean(isFilter) ? isFilter : false;
						if (updateState && self.current === 1 && !self.tmpl.state.exists()) {
							state = self.tmpl.state.get();
							self.tmpl.state.update(state, self.pushOrReplace);
						}
						self.controls(pageNumber);
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
				if ($.inArray(item, self._arr[i]) !== -1) {
					return i + 1;
				}
			}
			return 0;
		},
		contains: function (pageNumber, item) {
			var items = this.get(pageNumber);
			return $.inArray(item, items) !== -1;
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
		},
		create: function () {
			var self = this;
			self.$container = $("<nav/>", {"class": self.pages.cls.container}).addClass(self.pages.theme);
			return true;
		},
		destroy: function () {
			var self = this;
			self.$container.remove();
			self.$container = null;
		},
		append: function () {
			var self = this;
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

	_.Infinite = _.Paging.extend({
		construct: function(template){
			var self = this;
			self._super(template);
			self.distance = self.opt.distance;
			self._created = [];
		},
		build: function(){
			this._super();
			this._created = [];
		},
		available: function(){
			var self = this, items = [], page = self.get(self.current), viewport = _utils.getViewportBounds(), last, first;
			if (!self.tmpl.initializing && !_is.empty(page) && self._created.length < self.total){
				last = page[page.length - 1].bounds();
				if (last.top - viewport.bottom < self.distance){
					self.set(self.current + 1, false);
					return self.available();
				}
			}
			for (var i = 0, l = self._created.length, num; i < l; i++){
				num = i + 1;
				page = self.get(num);
				if (!_is.empty(page)){
					first = page[0].bounds();
					last = page[page.length - 1].bounds();
					if (first.top - viewport.bottom < self.distance || last.bottom - viewport.top < self.distance){
						items.push.apply(items, page);
					}
				}
			}
			return items;
		},
		items: function(){
			var self = this, items = [];
			for (var i = 0, l = self._created.length, num, page; i < l; i++){
				num = i + 1;
				page = self.get(num);
				if (!_is.empty(page)){
					items.push.apply(items, page);
				}
			}
			return items;
		},
		create: function(pageNumber, isFilter){
			var self = this;
			pageNumber = self.number(pageNumber);
			if (isFilter) self.tmpl.items.detach(self.tmpl.items.all());
			for (var i = 0; i < pageNumber; i++){
				var exists = $.inArray(i, self._created);
				if (exists === -1){
					var items = self.tmpl.items.create(self._arr[i], true);
					if (items.length){
						self._created.push(i);
					}
				}
			}
			self.current = pageNumber;
		}
	});

	_.paging.register("infinite", _.Infinite, null, {
		type: "infinite",
		pushOrReplace: "replace",
		distance: 200
	});


})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is
);
(function($, _, _utils, _is){

	_.LoadMore = _.Infinite.extend({
		construct: function(template){
			this._super(template);
			this.amount = this.opt.amount;
			this._count = this.opt.amount;
		},
		build: function(){
			this._super();
			this._count = this.amount;
		},
		available: function(){
			var self = this, items = [], page = self.get(self.current), viewport = _utils.getViewportBounds(), last, first;
			if (!_is.empty(page) && self._created.length !== self.total){
				last = page[page.length - 1].bounds();
				if (last.top - viewport.bottom < self.distance){
					var pageNumber = self.current + 1;
					if (self.isValid(pageNumber) && self._count < self.amount){
						self._count++;
						self.set(pageNumber, false);
						return self.available();
					}
				}
			}
			if (self._created.length === self.total){
				if (!_is.empty(self.ctrls)){
					$.each(self.ctrls.splice(0, self.ctrls.length), function(i, control){
						control.destroy();
					});
				}
			}
			for (var i = 0, l = self._created.length, num; i < l; i++){
				num = i + 1;
				page = self.get(num);
				if (!_is.empty(page)){
					first = page[0].bounds();
					last = page[page.length - 1].bounds();
					if (first.top - viewport.bottom < self.distance || last.bottom - viewport.top < self.distance){
						items.push.apply(items, page);
					}
				}
			}
			return items;
		},
		loadMore: function(){
			var self = this;
			self._count = 0;
			self.tmpl.loadAvailable();
		}
	});

	_.LoadMoreControl = _.PagingControl.extend({
		construct: function(template, parent, position){
			this._super(template, parent, position);
			this.$container = $();
			this.$button = $();
		},
		create: function(){
			var self = this;
			self.$container = $("<nav/>", {"class": self.pages.cls.container}).addClass(self.pages.theme);
			self.$button = $("<button/>", {"class": self.pages.cls.button, "type": "button"}).html(self.pages.il8n.button)
				.on("click.foogallery", {self: self}, self.onButtonClick)
				.appendTo(self.$container);
			return true;
		},
		destroy: function(){
			var self = this;
			self.$button.off("click.foogallery", self.onButtonClick);
			self.$container.remove();
			self.$container = $();
			self.$button = $();
		},
		append: function(){
			var self = this;
			if (self.position === "top"){
				self.$container.insertBefore(self.tmpl.$el);
			} else {
				self.$container.insertAfter(self.tmpl.$el);
			}
		},
		onButtonClick: function(e){
			e.preventDefault();
			e.data.self.pages.loadMore();
		}
	});

	_.paging.register("loadMore", _.LoadMore, _.LoadMoreControl, {
		type: "loadMore",
		position: "bottom",
		pushOrReplace: "replace",
		amount: 1,
		distance: 200
	}, {
		button: "fg-load-more"
	}, {
		button: "Load More"
	});


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
(function($, _, _utils, _is){

	_.Pagination = _.Dots.extend({
		construct: function(template){
			this._super(template);
			this.limit = this.opt.limit;
			this.showFirstLast = this.opt.showFirstLast;
			this.showPrevNext = this.opt.showPrevNext;
			this.showPrevNextMore = this.opt.limit === 0 ? false : this.opt.showPrevNextMore;
			this.pageKeywords = ["first","prev","prevMore","nextMore","next","last"];
			this.sel.firstPrev = [this.sel.first, this.sel.prev].join(",");
			this.sel.nextLast = [this.sel.next, this.sel.last].join(",");
			this.range = {
				index: -1,
				start: -1,
				end: -1,
				changed: false,
				selected: false
			};
		},
		build: function(){
			this._super();
			this.range = {
				index: -1,
				start: -1,
				end: -1,
				changed: false,
				selected: false
			};
		},
		controls: function(pageNumber){
			var self = this;
			if (self.isValid(pageNumber)){
				self.range = self.getControlRange(pageNumber);
				$.each(self.ctrls, function(i, control){
					control.update(self.range);
				});
			}
		},
		isValid: function(pageNumber){
			return this._super(pageNumber) || this.isKeyword(pageNumber);
		},
		isKeyword: function(pageNumber){
			return _is.string(pageNumber) && $.inArray(pageNumber, this.pageKeywords) !== -1;
		},
		number: function(value){
			var self = this;
			if (value === "first") value = 1;
			if (value === "prev") value = self.current - 1;
			if (value === "next") value = self.current + 1;
			if (value === "last") value = self.total;
			if (value === "prevMore" || value === "nextMore") value = self.current;
			return self._super(value);
		},
		getControlRange: function(pageNumber){
			var self = this;
			switch(pageNumber){
				case "prevMore":
					return self._range(self.range.start - 1, false, false);
				case "nextMore":
					return self._range(self.range.end + 1, true, false);
				default:
					pageNumber = self.number(pageNumber);
					return self._range(pageNumber - 1, pageNumber <= self.current)
			}
		},
		_range: function(index, leftMost, selected){
			var self = this, range = {
				index: index,
				start: self.range.start,
				end: self.range.end,
				changed: false,
				selected: _is.boolean(selected) ? selected : true
			};
			// if we have less pages than the limit or there is no limit
			if (self.total <= self.limit || self.limit === 0){
				// then set the range so that all page links are displayed
				range.start = 0;
				range.end = self.total - 1;
			}
			// else if the goto index falls outside the current range
			else if (index < range.start || index > range.end) {
				// then calculate the correct range to display
				var max = index + (self.limit - 1),
					min = index - (self.limit - 1);

				// if the goto index is to be displayed as the left most page link
				if (leftMost) {
					// then check that the right most item falls within the actual number of pages
					range.start = index;
					range.end = max;
					while (range.end > self.total) {
						// adjust the visible range so that the right most item is not greater than maximum page
						range.start -= 1;
						range.end -= 1;
					}
				}
				// else if the goto index is to be displayed as the right most page link
				else {
					// then check that the left most item falls within the actual number of pages
					range.start = min;
					range.end = index;
					while (range.start < 0) {
						// adjust the visible range so that the left most item is not less than the minimum page
						range.start += 1;
						range.end += 1;
					}
				}
			}
			// if the current visible range of links has changed
			if (range.changed = range.start !== self.range.start || range.end !== self.range.end){
				// then cache the range for the next time this method is called
				self.range = range;
			}
			return range;
		}
	});

	_.PaginationControl = _.DotsControl.extend({
		construct: function(template, parent, position){
			this._super(template, parent, position);
			this.$buttons = $();
		},
		create: function(){
			var self = this;
			if (self._super()){
				var displayAll = self.pages.total <= self.pages.limit || self.pages.limit === 0,
					buttons = [], $button;

				if (!displayAll && self.pages.showPrevNextMore){
					buttons.push($button = self.createButton("prevMore"));
					self.$list.prepend($button);
				}
				if (self.pages.showPrevNext){
					buttons.push($button = self.createButton("prev"));
					self.$list.prepend($button);
				}
				if (self.pages.showFirstLast){
					buttons.push($button = self.createButton("first"));
					self.$list.prepend($button);
				}
				if (!displayAll && self.pages.showPrevNextMore){
					buttons.push($button = self.createButton("nextMore"));
					self.$list.append($button);
				}
				if (self.pages.showPrevNext){
					buttons.push($button = self.createButton("next"));
					self.$list.append($button);
				}
				if (self.pages.showFirstLast){
					buttons.push($button = self.createButton("last"));
					self.$list.append($button);
				}
				self.$buttons = $($.map(buttons, function($button){ return $button.get(); }));

				return true;
			}
			return false;
		},
		destroy: function(){
			this._super();
			this.$buttons = $();
		},
		update: function(range){
			var self = this, sel = self.pages.sel;
			// if the range changed update the visible links
			if (range.changed) {
				self.setVisible(range.start, range.end);
			}
			// if the range index is selected
			if (range.selected) {
				// then update the items as required
				self.setSelected(range.index);

				// if this is the first page then we need to disable the first and prev buttons
				self.toggleDisabled(self.$buttons.filter(sel.firstPrev), range.index <= 0);
				// if this is the last page we need to disable the next and last buttons
				self.toggleDisabled(self.$buttons.filter(sel.nextLast), range.index >= self.pages.total - 1);
			}
			// if the visible range starts with the first page then we need to disable the prev more button
			self.toggleDisabled(self.$buttons.filter(sel.prevMore), range.start <= 0);
			// if the visible range ends with the last page then we need to disable the next more button
			self.toggleDisabled(self.$buttons.filter(sel.nextMore), range.end >= self.pages.total - 1);
		},
		setVisible: function(start, end){
			var self = this, cls = self.pages.cls;
			// when we slice we add + 1 to the upper limit of the range as $.slice does not include the end index in the result
			self.$items.removeClass(cls.visible).slice(start, end + 1).addClass(cls.visible);
		},
		toggleDisabled: function($buttons, state){
			var self = this, cls = self.pages.cls, sel = self.pages.sel;
			if (state) {
				$buttons.addClass(cls.disabled).find(sel.link).attr("tabindex", -1);
			} else {
				$buttons.removeClass(cls.disabled).find(sel.link).removeAttr("tabindex");
			}
		},
		/**
		 * @summary Create and return a jQuery object containing a single `li` and its' button.
		 * @memberof FooGallery.PaginationControl#
		 * @function createButton
		 * @param {string} keyword - One of the page keywords; `"first"`, `"prev"`, `"prevMore"`, `"nextMore"`, `"next"` or `"last"`.
		 * @returns {jQuery}
		 */
		createButton: function(keyword){
			var self = this, cls = self.pages.cls, il8n = self.pages.il8n;
			return self.createItem(keyword, il8n.labels[keyword], il8n.buttons[keyword], cls.button + " " + cls[keyword]);
		}
	});

	_.paging.register("pagination", _.Pagination, _.PaginationControl, {
		type: "pagination",
		position: "both",
		pushOrReplace: "push",
		limit: 5,
		showPrevNext: true,
		showFirstLast: true,
		showPrevNextMore: true
	}, {
		list: "fg-pages",
		item: "fg-page-item",
		button: "fg-page-button",
		link: "fg-page-link",
		first: "fg-page-first",
		prev: "fg-page-prev",
		prevMore: "fg-page-prev-more",
		nextMore: "fg-page-next-more",
		next: "fg-page-next",
		last: "fg-page-last",
		disabled: "fg-disabled",
		selected: "fg-selected",
		visible: "fg-visible",
		reader: "fg-sr-only"
	}, {
		buttons: {
			first: "&laquo;",
			prev: "&lsaquo;",
			next: "&rsaquo;",
			last: "&raquo;",
			prevMore: "&hellip;",
			nextMore: "&hellip;"
		},
		labels: {
			current: "Current page",
			page: "Page {PAGE}",
			first: "First page",
			prev: "Previous page",
			next: "Next page",
			last: "Last page",
			prevMore: "Select from previous {LIMIT} pages",
			nextMore: "Select from next {LIMIT} pages"
		}
	});

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils,
	FooGallery.utils.is
);
(function ($, _, _utils, _is) {

	_.Filtering = _.Component.extend({
		construct: function (template) {
			var self = this;
			/**
			 * @ignore
			 * @memberof FooGallery.Filtering#
			 * @function _super
			 */
			self._super(template);
			self.opt = self.tmpl.opt.filtering;
			self.cls = self.tmpl.cls.filtering;
			self.il8n = self.tmpl.il8n.filtering;
			self.sel = self.tmpl.sel.filtering;
			self.pushOrReplace = self.opt.pushOrReplace;
			self.type = self.opt.type;
			self.theme = self.opt.theme;
			self.position = self.opt.position;

			self.mode = self.opt.mode;
			self.sortBy = self.opt.sortBy;
			self.sortInvert = self.opt.sortInvert;

			self.min = self.opt.min;
			self.limit = self.opt.limit;
			self.showCount = self.opt.showCount;

			self.adjustSize = self.opt.adjustSize;
			self.smallest = self.opt.smallest;
			self.largest = self.opt.largest;

			self.adjustOpacity = self.opt.adjustOpacity;
			self.lightest = self.opt.lightest;
			self.darkest = self.opt.darkest;

			self.current = [];
			self.ctrls = [];
			self.tags = [];
			self.isMultiLevel = false;
		},
		fromHash: function(hash){
			var self = this, opt = self.tmpl.state.opt;
			return hash.indexOf(opt.arraySeparator) === -1
				? [hash.split(opt.array).map(function(part){ return decodeURIComponent(part.replace(/\+/g, '%20')); })]
				: hash.split(opt.arraySeparator).map(function(arr){
					return _is.empty(arr) ? [] : arr.split(opt.array).map(function(part){
						return decodeURIComponent(part.replace(/\+/g, '%20'));
					})
				});
		},
		toHash: function(value){
			var self = this, opt = self.tmpl.state.opt, hash = null;
			if (_is.array(value)){
				if (_is.array(value[0])){
					hash = $.map(value, function(tags){
						return $.map(tags, function(tag){
							return encodeURIComponent(tag);
						}).join(opt.array);
					}).join(opt.arraySeparator);
				} else {
					hash = $.map(value, function(tag){
						return encodeURIComponent(tag);
					}).join(opt.array);
				}
			}
			return _is.empty(hash) ? null : hash;
		},
		getState: function(){
			return _is.array(this.current) && !this.current.every(function (tags) {
				return tags.length === 0;
			}) ? this.current.slice() : null;
		},
		setState: function(state){
			this.rebuild();
			this.set(state.filter, false);
		},
		destroy: function () {
			var self = this;
			self.tags.splice(0, self.tags.length);
			$.each(self.ctrls.splice(0, self.ctrls.length), function (i, control) {
				control.destroy();
			});
			self._super();
		},
		count: function (items, tags) {
			items = _is.array(items) ? items : [];
			tags = _is.array(tags) ? tags : [];
			var result = { __ALL__: 0 }, generate = tags.length === 0;
			for (var i = 0, l = items.length, t; i < l; i++) {
				if (!_is.empty(t = items[i].tags)) {
					result.__ALL__++;
					for (var j = 0, jl = t.length, tag; j < jl; j++) {
						if (!_is.empty(tag = t[j]) && (generate || (!generate && $.inArray(tag, tags) !== -1))) {
							if (_is.number(result[tag])) {
								result[tag]++;
							} else {
								result[tag] = 1;
							}
						}
					}
				}
			}
			for (var k = 0, kl = tags.length; k < kl; k++) {
				if (!result.hasOwnProperty(tags[k])) result[tags[k]] = 0;
			}
			return result;
		},
		createTagObjects: function(items, tags, levelIndex, levelText){
			var self = this, result = [];
			// first get a count of the tags
			var counts = self.count(items, tags), min = Infinity, max = 0, index = -1;
			for (var prop in counts) {
				if (counts.hasOwnProperty(prop)) {
					var count = counts[prop], isAll = prop === "__ALL__";
					if (self.min <= 0 || count >= self.min) {
						if (tags.length > 0){
							index = $.inArray(prop, tags);
						} else {
							index++;
						}
						result.push({
							level: levelIndex,
							index: index,
							value: isAll ? "" : prop,
							text: isAll ? levelText : prop,
							count: count,
							percent: 1,
							size: self.largest,
							opacity: self.darkest
						});
						if (count < min) min = count;
						if (count > max) max = count;
					}
				}
			}

			// if there's a limit set, remove other tags
			if (self.limit > 0 && result.length > self.limit) {
				result.sort(function (a, b) {
					return b.count - a.count;
				});
				result = result.slice(0, self.limit);
			}

			// if adjustSize or adjustOpacity is enabled, calculate a percentage value used to calculate the appropriate font size and opacity
			if (!self.isMultiLevel && (self.adjustSize === true || self.adjustOpacity === true)) {
				var fontRange = self.largest - self.smallest;
				var opacityRange = self.darkest - self.lightest;
				for (var i = 0, l = result.length, tag; i < l; i++) {
					tag = result[i];
					tag.percent = (tag.count - min) / (max - min);
					tag.size = self.adjustSize ? Math.round((fontRange * tag.percent) + self.smallest) : self.largest;
					tag.opacity = self.adjustOpacity ? (opacityRange * tag.percent) + self.lightest : self.darkest;
				}
			}

			// finally sort the tags using the sort options
			switch (self.sortBy){
				case "none":
					self.sort(result, "index", false);
					break;
				default:
					self.sort(result, self.sortBy, self.sortInvert);
					break;
			}

			return result;
		},
		showControl: function(){
			return !this.tags.every(function (tags) {
				return tags.length === 0;
			});
		},
		build: function () {
			var self = this, items = self.tmpl.items.all();
			self.isMultiLevel = self.opt.tags.length > 0 && _is.object(self.opt.tags[0]);
			if (items.length > 0) {
				if (self.isMultiLevel){
					$.each(self.opt.tags, function(i, level){
						self.tags.push(self.createTagObjects(items, level.tags, i, level.all || self.il8n.all));
					});
				} else {
					self.tags.push(self.createTagObjects(items, self.opt.tags, 0, self.il8n.all));
				}
			}

			if (self.showControl() && _.filtering.hasCtrl(self.type)) {
				var pos = self.position, top, bottom;
				if (pos === "both" || pos === "top") {
					top = _.filtering.makeCtrl(self.type, self.tmpl, self, "top");
					if (top.create()) {
						top.append();
						self.ctrls.push(top);
					}
				}
				if (pos === "both" || pos === "bottom") {
					bottom = _.filtering.makeCtrl(self.type, self.tmpl, self, "bottom");
					if (bottom.create()) {
						bottom.append();
						self.ctrls.push(bottom);
					}
				}
			}
		},
		rebuild: function () {
			var self = this;
			self.tags.splice(0, self.tags.length);
			$.each(self.ctrls.splice(0, self.ctrls.length), function (i, control) {
				control.destroy();
			});
			self.build();
		},
		controls: function (tags) {
			var self = this;
			$.each(self.ctrls, function (i, control) {
				control.update(tags);
			});
		},
		hasAll: function(item, tags){
			return tags.every(function(arr){
				return arr.length === 0 || (_is.array(item.tags) && arr.every(function (tag) {
					return item.tags.indexOf(tag) !== -1;
				}));
			});
		},
		hasSome: function(item, tags){
			return tags.every(function(arr){
				return arr.length === 0 || (_is.array(item.tags) && arr.some(function (tag) {
					return item.tags.indexOf(tag) !== -1;
				}));
			});
		},
		set: function (tags, updateState) {
			if (_is.string(tags)) tags = [[tags]];
			if (!_is.array(tags)) tags = [];
			var self = this, state;
			if (!self.arraysEqual(self.current, tags)) {
				var prev = self.current.slice(), setFilter = function () {
					updateState = _is.boolean(updateState) ? updateState : true;
					if (updateState && !self.tmpl.state.exists()) {
						state = self.tmpl.state.get();
						self.tmpl.state.update(state, self.pushOrReplace);
					}

					if (_is.empty(tags)) {
						self.tmpl.items.reset();
					} else {
						var items = self.tmpl.items.all();
						if (self.mode === 'intersect') {
							items = $.map(items, function (item) {
								return self.hasAll(item, tags) ? item : null;
							});
						} else {
							items = $.map(items, function (item) {
								return self.hasSome(item, tags) ? item : null;
							});
						}
						self.tmpl.items.setAvailable(items);
					}
					self.current = tags.slice();
					self.controls(tags);
					if (self.tmpl.pages) {
						self.tmpl.pages.rebuild();
						self.tmpl.pages.set(1, null, null, true);
					} else {
						self.tmpl.items.detach(self.tmpl.items.all());
						self.tmpl.items.create(self.tmpl.getAvailable(), true);
					}

					if (updateState) {
						state = self.tmpl.state.get();
						self.tmpl.state.update(state, self.pushOrReplace);
					}

					self.tmpl.raise("after-filter-change", [self.current, prev]);
				};
				var e = self.tmpl.raise("before-filter-change", [self.current, tags, setFilter]);
				if (e.isDefaultPrevented()) return false;
				setFilter();
				return true;
			}
			return false;
		},
		arraysEqual: function (arr1, arr2) {
			if (arr1.length !== arr2.length)
				return false;

			arr1 = arr1.slice();
			arr2 = arr2.slice();

			arr1.sort();
			arr2.sort();
			for (var i = arr1.length; i--;) {
				if (arr1[i] !== arr2[i])
					return false;
			}
			return true;
		},
		sort: function(tags, prop, invert){
			tags.sort(function(a, b){

				if (a.hasOwnProperty(prop) && b.hasOwnProperty(prop)){
					if (_is.string(a[prop]) && _is.string(b[prop])){
						var s1 = a[prop].toUpperCase(), s2 = b[prop].toUpperCase();
						if (invert){
							if (s2 < s1) return -1;
							if (s2 > s1) return 1;
						} else {
							if (s1 < s2) return -1;
							if (s1 > s2) return 1;
						}
					} else {
						if (invert){
							return b[prop] - a[prop];
						}
						return a[prop] - b[prop];
					}
				}
				return 0;

			});
		},
		apply: function (tags) {
			var self = this;
			if (self.set(tags, !self.tmpl.pages)) {
				self.tmpl.loadAvailable();
			}
		}
	});

	_.FilteringControl = _.Component.extend({
		construct: function (template, parent, position) {
			var self = this;
			self._super(template);
			self.filter = parent;
			self.position = position;
			self.$container = null;
		},
		create: function () {
			var self = this;
			self.$container = $("<nav/>", {"class": self.filter.cls.container}).addClass(self.filter.theme);
			return true;
		},
		destroy: function () {
			var self = this;
			self.$container.remove();
			self.$container = null;
		},
		append: function () {
			var self = this;
			if (self.position === "top") {
				self.$container.insertBefore(self.tmpl.$el);
			} else {
				self.$container.insertAfter(self.tmpl.$el);
			}
		},
		update: function (tags) {
		}
	});

	_.filtering.register("default", _.Filtering, null, {
		type: "none",
		theme: "fg-light",
		pushOrReplace: "push",
		position: "none",
		mode: "single",
		sortBy: "value", // "value", "count", "index", "none"
		sortInvert: false, // the direction of the sorting
		tags: [],
		min: 0,
		limit: 0,
		showCount: false,
		adjustSize: false,
		adjustOpacity: false,
		smallest: 12,
		largest: 16,
		lightest: 0.5,
		darkest: 1
	}, {
		container: "fg-filtering-container"
	}, null, -100);

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
);
(function($, _, _utils, _is){

	_.Tags = _.Filtering.extend({});

	_.TagsControl = _.FilteringControl.extend({
		construct: function(template, parent, position){
			this._super(template, parent, position);
			this.$container = $();
			this.lists = [];
		},
		create: function(){
			var self = this, cls = self.filter.cls;
			self.$container = $("<nav/>", {"class": cls.container}).addClass(self.filter.theme);
			for (var i = 0, l = self.filter.tags.length; i < l; i++){
				self.lists.push(self.createList(self.filter.tags[i]).appendTo(self.$container));
			}
			if (!self.filter.isMultiLevel && self.filter.showCount === true){
				self.$container.addClass(cls.showCount);
			}
			return true;
		},
		createList: function(tags){
			var self = this, cls = self.filter.cls,
				$list = $("<ul/>", {"class": cls.list});

			for (var i = 0, l = tags.length; i < l; i++){
				$list.append(self.createItem(tags[i]).toggleClass(cls.selected, i === 0));
			}
			return $list;
		},
		destroy: function(){
			var self = this, sel = self.filter.sel;
			self.lists.forEach(function($list, i){
				$list.find(sel.link).off("click.foogallery", self.onLinkClick);
			});
			self.$container.remove();
			self.$container = $();
			self.lists = [];
		},
		append: function(){
			var self = this;
			if (self.position === "top"){
				self.$container.insertBefore(self.tmpl.$el);
			} else {
				self.$container.insertAfter(self.tmpl.$el);
			}
		},
		update: function(tags){
			var self = this, cls = self.filter.cls, sel = self.filter.sel;
			self.lists.forEach(function($list, i){
				$list.find(sel.item).removeClass(cls.selected).each(function(){
					var $item = $(this), tag = $item.data("tag"), empty = _is.empty(tag);
					$item.toggleClass(cls.selected, (empty && _is.empty(tags[i])) || (!empty && $.inArray(tag, tags[i]) !== -1));
				});
			});
		},
		createItem: function(tag){
			var self = this, cls = self.filter.cls,
					$li = $("<li/>", {"class": cls.item}).attr("data-tag", tag.value),
					$link = $("<a/>", {"href": "#tag-" + tag.value, "class": cls.link})
							.on("click.foogallery", {self: self, tag: tag}, self.onLinkClick)
							.css("font-size", tag.size)
							.css("opacity", tag.opacity)
							.append($("<span/>", {"text": _is.string(tag.text) ? tag.text : tag.value, "class": cls.text}))
							.appendTo($li);

			if (!self.filter.isMultiLevel && self.filter.showCount === true){
				$link.append($("<span/>", {"text": tag.count, "class": cls.count}));
			}
			return $li;
		},
		onLinkClick: function(e){
			e.preventDefault();
			var self = e.data.self, tag = e.data.tag, tags = self.filter.current.map(function(obj){
				if (_is.array(obj)) return obj.slice();
				return obj;
			}), i;
			if (!_is.empty(tag.value)){
				switch (self.filter.mode){
					case "union":
					case "intersect":
						if (!_is.array(tags[tag.level])){
							tags[tag.level] = [];
						}
						i = $.inArray(tag.value, tags[tag.level]);
						if (i === -1){
							tags[tag.level].push(tag.value);
						} else {
							tags[tag.level].splice(i, 1);
						}
						break;
					case "single":
					default:
						tags[tag.level] = [tag.value];
						break;
				}
			} else {
				tags[tag.level] = [];
			}
			if (tags.every(_is.empty)){
				tags = [];
			}
			self.filter.apply(tags);
		}
	});

	_.filtering.register("tags", _.Tags, _.TagsControl, {
		type: "tags",
		position: "top",
		pushOrReplace: "push"
	}, {
		showCount: "fg-show-count",
		list: "fg-tag-list",
		item: "fg-tag-item",
		link: "fg-tag-link",
		text: "fg-tag-text",
		count: "fg-tag-count",
		selected: "fg-selected"
	}, {
		all: "All",
		none: "No items found."
	}, -100);

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils,
		FooGallery.utils.is
);
(function($, _, _utils, _is, _obj, _fn, _t){

    var instance_id = 0;

    _.Panel = _.EventComponent.extend({
        construct: function(template, options, classes){
            var self = this;
            self.instanceId = ++instance_id;
            self._super(template, "panel-");

            self.opt = _obj.extend({}, self.tmpl.opt.panel, options);

            self.cls = _obj.extend({}, self.tmpl.cls.panel, classes);

            var states = self.cls.states;
            self.cls.states.all = Object.keys(states).map(function (key) {
                return states[key];
            }).join(" ");
            self.cls.states.allLoading = [states.idle, states.loading, states.loaded, states.error].join(" ");
            self.cls.states.allProgress = [states.idle, states.started, states.stopped, states.paused].join(" ");

            self.sel = _utils.selectify(self.cls);

            self.videoSources = _.Panel.Video.sources.load();

            self.buttons = new _.Panel.Buttons(self);

            self.content = new _.Panel.Content(self);
            self.info = new _.Panel.Info(self);
            self.thumbs = new _.Panel.Thumbs(self);
            self.cart = new _.Panel.Cart(self);

            self.areas = [self.content, self.info, self.thumbs, self.cart];

            self.$el = null;

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

            self.__media = {};

            self.__loading = null;

            if (!(self.tmpl.destroying || self.tmpl.destroyed)){
                self.tmpl.on({
                    "after-filter-change": self.onItemsChanged
                }, self);
            }
        },
        onItemsChanged: function(e, tmpl){
            if (this.thumbs.isCreated && tmpl.initialized){
                this.thumbs.doCreateThumbs(tmpl.items.available());
                if (this.isAttached) this.load(tmpl.items.first());
            }
        },
        create: function(){
            var self = this;
            if (!self.isCreated) {
                var e = self.trigger("create", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isCreated = self.doCreate();
                }
                if (self.isCreated) {
                    self.trigger("created", [self]);
                }
            }
            return self.isCreated;
        },
        doCreate: function(){
            var self = this;
            self.$el = self.createElem();
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
                _is.string(self.opt.theme) ? self.opt.theme : self.tmpl.getCSSClass("theme"),
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
                        var e = self.trigger("destroy", [self]);
                        self.isDestroying = false;
                        if (!e.isDefaultPrevented()) {
                            self.isDestroyed = self.doDestroy();
                        }
                        if (self.isDestroyed) {
                            self.trigger("destroyed", [self]);
                        }
                        def.resolve();
                    });
                } else {
                    var e = self.trigger("destroy", [self]);
                    self.isDestroying = false;
                    if (!e.isDefaultPrevented()) {
                        self.isDestroyed = self.doDestroy();
                    }
                    if (self.isDestroyed) {
                        self.trigger("destroyed", [self]);
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
                var e = self.trigger("append", [self, parent]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = self.doAppendTo( parent );
                }
                if (self.isAttached) {
                    self.trigger("appended", [self, parent]);
                }
            }
            return self.isAttached;
        },
        doAppendTo: function( parent ){
            var self = this, $parent = $( parent ), maximize = self.buttons.get("maximize");
            self.isInline = !$parent.is("body");
            maximize.set(!self.isInline, self.isInline);
            _.breakpoints.register(self.$el, self.opt.breakpoints, function(){
                self.areas.forEach(function (area) {
                    area.resize();
                });
                self.buttons.resize();
            });

            self.$el.appendTo( $parent );
            self.areas.forEach(function (area) {
                area.listen();
            });
            return self.$el.parent().length > 0;
        },
        detach: function(){
            var self = this;
            if (self.isCreated && self.isAttached) {
                var e = self.trigger("detach", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = !self.doDetach();
                }
                if (!self.isAttached) {
                    self.trigger("detached", [self]);
                }
            }
            return !self.isAttached;
        },
        doDetach: function(){
            var self = this;
            self.areas.forEach(function (area) {
                area.stopListening();
            });
            _.breakpoints.remove(self.$el);
            self.$el.detach();
            return true;
        },
        resize: function(){
            _.breakpoints.check(this.$el);
        },
        getMedia: function(item){
            if (!(item instanceof _.Item)) return null;
            if (this.__media.hasOwnProperty(item.id)) return this.__media[item.id];
            return this.__media[item.id] = _.Panel.media.make(item.type, this, item);
        },
        load: function( item ){
            var self = this;

            item = item instanceof _.Item ? item : self.currentItem;
            item = item instanceof _.Item ? item : self.tmpl.items.first();

            if (!(item instanceof _.Item)) return _fn.rejectWith("no items to load");
            if (item === self.currentItem) return _fn.rejectWith("item is currently loaded");

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
                var e = self.trigger("load", [self, media, item]);
                if (e.isDefaultPrevented()){
                    def.rejectWith("default prevented");
                    return;
                }
                self.currentItem = item;
                self.prevItem = self.tmpl.items.prev(item, self.opt.loop);
                self.nextItem = self.tmpl.items.next(item, self.opt.loop);
                self.doLoad(media).then(def.resolve).fail(def.reject);
            }).always(function(){
                self.isLoading = false;
                self.$el.focus();
            }).then(function(){
                self.isLoaded = true;
                self.trigger("loaded", [self, item]);
                item.updateState();
            }).fail(function(){
                self.isError = true;
                self.trigger("error", [self, item]);
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
            var self = this,
                e = self.trigger("open", [self, item, parent]);
            if (e.isDefaultPrevented()) return _fn.rejectWith("default prevented");
            return self.doOpen(item, parent).then(function(){
                self.trigger("opened", [self, item, parent]);
            });
        },
        doOpen: function( item, parent ){
            var self = this;
            return $.Deferred(function(def){
                item = item instanceof _.Item ? item : self.tmpl.items.first();
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
            if (!(next instanceof _.Item)) return _fn.rejectWith("no next item");
            var e = self.trigger("next", [self, current, next]);
            if (e.isDefaultPrevented()) return _fn.rejectWith("default prevented");
            return self.doNext(next).then(function(){
                self.trigger("after-next", [self, current, next]);
            });
        },
        doNext: function(item){
            return this.load( item );
        },
        prev: function(){
            var self = this, current = self.currentItem, prev = self.prevItem;
            if (!(prev instanceof _.Item)) return _fn.rejectWith("no prev item");
            var e = self.trigger("prev", [self, current, prev]);
            if (e.isDefaultPrevented()) return _fn.rejectWith("default prevented");
            return self.doPrev(prev).then(function(){
                self.trigger("after-prev", [self, current, prev]);
            });
        },
        doPrev: function(item){
            return this.load( item );
        },
        close: function(immediate){
            var self = this, e = self.trigger("close", [self, self.currentItem]);
            if (e.isDefaultPrevented()) return _fn.rejectWith("default prevented");
            return self.doClose(immediate).then(function(){
                self.trigger("closed", [self]);
            });
        },
        doClose: function(immediate, detach){
            detach = _is.boolean(detach) ? detach : true;
            var self = this;
            return $.Deferred(function(def){
                self.content.close(immediate).then(function(){
                    var wait = [];
                    wait.push(self.cart.close(immediate));
                    wait.push(self.thumbs.close(immediate));
                    wait.push(self.info.close(immediate));
                    $.when.apply($, wait).then(def.resolve).fail(def.reject);
                });
            }).always(function(){
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
                        $new.focus();
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
            noMobile: false,
            hoverButtons: false,
            icons: "default",
            transition: "none", // none | fade | horizontal | vertical

            loop: true,
            autoProgress: 0,
            fitMedia: false,
            keyboard: true,
            noScrollbars: true,
            swipe: true,
            stackSideAreas: true,
            preserveButtonSpace: true,

            info: "bottom", // none | top | bottom | left | right
            infoVisible: false,
            infoOverlay: true,

            cart: "none", // none | top | bottom | left | right
            cartVisible: false,

            thumbs: "none", // none | top | bottom | left | right
            thumbsVisible: true,
            thumbsCaptions: true,
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
            breakpoints: {
                medium: {
                    width: 480,
                    height: 480
                },
                large: {
                    width: 768,
                    height: 640
                },
                "x-large": {
                    width: 1024,
                    height: 768
                }
            }
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
                selected: "fg-selected",
                disabled: "fg-disabled",
                hidden: "fg-hidden",
                started: "fg-started",
                stopped: "fg-stopped",
                paused: "fg-paused",
                noTransitions: "fg-no-transitions"
            },

            buttons: {
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
                overlay: "fg-panel-info-overlay"
            },

            cart: {},

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
                }
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

            this.$el = null;

            this.isCreated = false;

            this.isAttached = false;

            this.__registered = [];

            this.registerCore();
        },

        registerCore: function(){
            this.register(new _.Panel.Button(this.panel, "prev", {
                icon: "arrow-left",
                label: "Previous Media",
                onclick: this.panel.prev.bind(this.panel),
                beforeLoad: function (media) {
                    this.disable(this.panel.prevItem == null);
                }
            }), 10);
            this.register(new _.Panel.Button(this.panel, "next", {
                icon: "arrow-right",
                label: "Next Media",
                onclick: this.panel.next.bind(this.panel),
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
                label: "Close Modal",
                onclick: this.panel.close.bind(this.panel)
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

        resize: function(){}
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
                visible: true,
                disabled: false,
                onclick: $.noop,
                beforeLoad: $.noop,
                afterLoad: $.noop,
                close: $.noop
            }, options);
            this.cls = {
                elem: panel.cls.buttons[name],
                states: panel.cls.states
            };
            this.$el = null;
            this.isVisible = this.opt.visible;
            this.isDisabled = this.opt.disabled;
            this.isCreated = false;
            this.isAttached = false;
        },
        isEnabled: function(){
            return this.panel.opt.buttons.hasOwnProperty(this.name) && this.panel.opt.buttons[this.name];
        },
        create: function(){
            var self = this;
            if (!self.isCreated && self.isEnabled()){
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
            this.opt.onclick.call(this);
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
(function($, _, _utils){

    _.Panel.AutoProgress = _.Panel.Button.extend({
        construct: function(panel){
            var self = this;
            self.__stopped = false;
            self.__timer = new _utils.Timer();
            self._super(panel, "autoProgress", {
                icon: "auto-progress",
                label: "Auto Progress"
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
                    "stroke-dashoffset": this.circumference
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
(function($, _, _fs){

    _.Panel.Fullscreen = _.Panel.Button.extend({
        construct: function(panel){
            var self = this;
            self._super(panel, "fullscreen", {
                icon: ["expand", "shrink"],
                label: "Fullscreen"
            });
        },
        create: function(){
            if (this._super()){
                this.$el.attr("aria-pressed", false);
                return true;
            }
            return false;
        },
        click: function(){
            var self = this, pnl = self.panel.$el.get(0);
            _fs.toggle(pnl).then(function(){
                if (_fs.element() === pnl){
                    _fs.on("change error", self.onFullscreenChange, self);
                    self.enter();
                } else {
                    _fs.off("change error", self.onFullscreenChange, self);
                    self.exit();
                }
            });
            self._super();
        },
        onFullscreenChange: function(){
            if (_fs.element() !== this.panel.$el.get(0)){
                this.exit();
            }
        },
        enter: function(){
            this.panel.$el.addClass(this.panel.cls.fullscreen);
            if (!this.panel.isMaximized){
                this.panel.$el.attr({
                    'role': 'dialog',
                    'aria-modal': true
                });
                this.panel.trapFocus();
            }
            this.$el.attr("aria-pressed", true);
            this.panel.buttons.toggle('maximize', false);
            this.panel.isFullscreen = true;
        },
        exit: function(){
            this.panel.$el.removeClass(this.panel.cls.fullscreen);
            if (!this.panel.isMaximized){
                this.panel.$el.attr({
                    'role': null,
                    'aria-modal': null
                });
                this.panel.releaseFocus();
            }
            this.$el.attr("aria-pressed", false);
            this.panel.buttons.toggle('maximize', this.panel.isInline && this.panel.buttons.opt.maximize);
            this.panel.isFullscreen = false;
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils.fullscreen
);
(function($, _, _is, _fs){

    _.Panel.Maximize = _.Panel.Button.extend({
        construct: function(panel){
            this._super(panel, "maximize", {
                icon: "maximize",
                label: "Maximize"
            });
            this.$placeholder = $("<span/>");
        },
        create: function(){
            if (this._super()){
                this.$el.attr("aria-pressed", false);
                return true;
            }
            return false;
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
            this.panel.isMaximized = true;
            this.$placeholder.insertAfter(this.panel.$el);
            this.panel.$el.appendTo("body").addClass(this.panel.cls.maximized).attr({
                'role': 'dialog',
                'aria-modal': true
            });
            if (this.isCreated) this.$el.attr("aria-pressed", true);
            this.panel.trapFocus();
            if (this.panel.opt.noScrollbars){
                $("html").addClass(this.panel.cls.noScrollbars);
            }
        },
        exit: function(){
            this.panel.isMaximized = false;
            this.panel.$el.removeClass(this.panel.cls.maximized).attr({
                'role': null,
                'aria-modal': null
            }).insertBefore(this.$placeholder);
            this.$placeholder.detach();
            if (this.isCreated) this.$el.attr("aria-pressed", false);
            this.panel.releaseFocus();
            if (this.panel.opt.noScrollbars){
                $("html").removeClass(this.panel.cls.noScrollbars);
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils.is,
    FooGallery.utils.fullscreen
);
(function($, _, _utils, _is, _fn, _obj, _str){

    _.Panel.Area = _utils.Class.extend({
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
            if (!(media instanceof _.Panel.Media)) return _fn.rejectWith("unable to load media");
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
                last = this.panel.tmpl.items.last();
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

    _.Panel.Content = _.Panel.Area.extend({
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
                    // only the inner is being observed so if a change occurs we can safely just call resize
                    self.resize();
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
                    wait.push(_t.start(media.$el, states.visible, true, 350));
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
                        wait.push(_t.start(media.$el, states.visible, false, 350));
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

    _.Panel.SideArea = _.Panel.Area.extend({
        construct: function(panel, name, options, classes){
            var self = this, cls = panel.cls.sideArea;
            self._super(panel, name, _obj.extend({
                icon: null,
                label: null,
                position: null,
                visible: true,
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
            self.__isVisible = null;
            self.allPositionClasses = Object.keys(self.cls.position).map(function (key) {
                return self.cls.position[key];
            }).join(" ");
            self.panel.buttons.register(new _.Panel.Button(panel, name, {
                icon: self.opt.icon,
                label: self.opt.label,
                onclick: self.toggle.bind(self),
                beforeLoad: function(media){
                    var enabled = self.isEnabled(), supported = enabled && self.canLoad(media);
                    if (!supported && self.__isVisible == null){
                        self.__isVisible = self.isVisible;
                        self.toggle(false);
                    } else if (self.__isVisible != null) {
                        self.toggle(self.__isVisible);
                        self.__isVisible = null;
                    }
                    if (enabled) this.disable(!supported);
                    else this.toggle(supported);
                }
            }));
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

    _.Panel.Info = _.Panel.SideArea.extend({
        construct: function(panel){
            this._super(panel, "info", {
                icon: "info",
                label: "Information",
                position: panel.opt.info,
                overlay: panel.opt.infoOverlay,
                visible: panel.opt.infoVisible,
                waitForUnload: false
            }, panel.cls.info);
            this.allPositionClasses += " " + this.cls.overlay;
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

    _.Panel.Thumbs = _.Panel.SideArea.extend({
        construct: function(panel){
            this._super(panel, "thumbs", {
                icon: "thumbs",
                label: "Thumbnails",
                position: panel.opt.thumbs,
                captions: panel.opt.thumbsCaptions,
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
                    // only the viewport is being observed so if a change occurs we can safely grab just the first entry
                    var rect = entries[0].contentRect, viewport = self.info.viewport;
                    var diffX = Math.floor(Math.abs(rect.width - viewport.width)),
                        diffY = Math.floor(Math.abs(rect.height - viewport.height));
                    if (self.isVisible && (diffX > 1 || diffY > 1)){
                        self.resize();
                    }
                }, 50));

                self.doCreateThumbs(self.panel.tmpl.items.available());

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
                $media = $thumb.find(self.sel.thumb.media),
                $img = $thumb.find(self.sel.thumb.image),
                img = $img.get(0),
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
            img.src = item.getThumbSrc($media.width(), $media.height());
            if (img.complete){
                img.onload();
            }
        },
        goto: function(index, disableTransition){
            var self = this;
            if (!self.isCreated) return _fn.rejectWith("thumbs not created");

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
                width = 0, height = 0;
            if (this.isCreated){
                viewport = { width: this.$viewport.innerWidth() + 1, height: this.$viewport.innerHeight() + 1 };
                original = { width: this.$dummy.outerWidth(), height: this.$dummy.outerHeight() };
                counts = { horizontal: Math.floor(viewport.width / original.width), vertical: Math.floor(viewport.height / original.height) };
                adjusted = { width: viewport.width / counts.horizontal, height: viewport.height / counts.vertical };
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
                var visible = this.selectedIndex >= this.scrollIndex && this.selectedIndex < this.scrollIndex + this.info.count;
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
(function($, _, _fn, _t){

    _.Panel.Cart = _.Panel.SideArea.extend({
        construct: function(panel){
            this._super(panel, "cart", {
                icon: "cart",
                label: "Cart",
                position: panel.opt.cart,
                visible: panel.opt.cartVisible,
                waitForUnload: false
            }, panel.cls.cart);
        },
        canLoad: function(media){
            return this._super(media) && media.product.canLoad();
        },
        doLoad: function(media, reverseTransition){
            if (this.canLoad(media)){
                media.product.appendTo(this.$inner);
                media.product.load();
            }
            return _fn.resolved;
        },
        doUnload: function(media, reverseTransition){
            media.product.unload();
            media.product.detach();
            return _fn.resolved;
        }
    });

})(
    FooGallery.$,
    FooGallery,
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

            self.caption = new _.Panel.Media.Caption(panel, self);

            self.product = new _.Panel.Media.Product(panel, self);

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
                    description: "fg-media-caption-description"
                },
                product: {
                    elem: "fg-media-product",
                    inner: "fg-media-product-inner",
                    header: "fg-media-product-header",
                    body: "fg-media-product-body",
                    footer: "fg-media-product-footer"
                }
            }
        }
    });

    _.Panel.media = new _utils.Factory();

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
(function ($, _, _utils, _is, _fn, _obj, _t) {

    _.Panel.Media.Caption = _utils.Class.extend({
        construct: function (panel, media) {
            var self = this;
            self.panel = panel;
            self.media = media;
            self.opt = panel.opt;
            self.cls = media.cls.caption;
            self.sel = media.sel.caption;
            self.$el = null;
            self.isCreated = false;
            self.isAttached = false;
        },
        canLoad: function(){
            return !_is.empty(this.media.item.caption) && !_is.empty(this.media.item.description);
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
            this.$el = $("<div/>").addClass(this.cls.elem).append(
                $("<div/>").addClass(this.cls.title).html(this.media.item.caption),
                $("<div/>").addClass(this.cls.description).html(this.media.item.description)
            );
            return true;
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
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.obj,
    FooGallery.utils.transition
);
(function ($, _, _utils, _is, _fn, _obj, _t) {

    _.Panel.Media.Product = _utils.Class.extend({
        construct: function (panel, media) {
            var self = this;
            self.panel = panel;
            self.media = media;
            self.opt = panel.opt;
            self.cls = media.cls.product;
            self.sel = media.sel.product;
            self.$el = null;
            self.$inner = null;
            self.$header = null;
            self.$body = null;
            self.$footer = null;
            self.isCreated = false;
            self.isAttached = false;
            self.__loaded = null;
            self.__requestId = null;
        },
        canLoad: function(){
            return !_is.empty(this.media.item.productId);
        },
        create: function(){
            if (!this.isCreated){
                var e = this.panel.trigger("product-create", [this]);
                if (!e.isDefaultPrevented()){
                    this.isCreated = this.doCreate();
                    if (this.isCreated){
                        this.panel.trigger("product-created", [this]);
                    }
                }
            }
            return this.isCreated;
        },
        doCreate: function(){
            this.$el = $("<div/>").addClass(this.cls.elem).append(
                $("<div/>").addClass(this.panel.cls.loader)
            );
            this.$inner = $("<div/>").addClass(this.cls.inner).appendTo(this.$el);
            this.$header = $("<div/>").addClass(this.cls.header).text("Add To Cart").appendTo(this.$inner);
            this.$body = $("<div/>").addClass(this.cls.body).appendTo(this.$inner);
            this.$footer = $("<div/>").addClass(this.cls.footer).append(
                $("<div/>").addClass("fg-panel-button fg-product-button").text("Add to Cart"),
                $("<div/>").addClass("fg-panel-button fg-product-button").text("View Cart")
            ).appendTo(this.$inner);
            return true;
        },
        destroy: function(){
            if (this.isCreated){
                var e = this.panel.trigger("product-destroy", [this]);
                if (!e.isDefaultPrevented()){
                    this.isCreated = !this.doDestroy();
                    if (!this.isCreated){
                        this.panel.trigger("product-destroyed", [this]);
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
                var e = self.panel.trigger("product-append", [self, parent]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = self.doAppendTo( parent );
                }
                if (self.isAttached) {
                    self.panel.trigger("product-appended", [self, parent]);
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
                var e = self.panel.trigger("product-detach", [self]);
                if (!e.isDefaultPrevented()) {
                    self.isAttached = !self.doDetach();
                }
                if (!self.isAttached) {
                    self.panel.trigger("product-detached", [self]);
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
                var e = self.panel.trigger("product-load", [self]);
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
                self.panel.trigger("product-loaded", [self]);
            }).fail(function(){
                self.$el.addClass(states.loaded);
                self.panel.trigger("product-error", [self]);
            }).promise();
        },
        doLoad: function(){
            var self = this;
            if (self.__loaded != null) return self.__loaded;
            return self.__loaded = $.Deferred(function(def){
                self.__requestId = setTimeout(function(){
                    self.$body.append("loaded!");
                    def.resolve();
                }, 3000);
            }).promise();
        },
        unload: function(){
            var self = this;
            return $.Deferred(function(def){
                if (!self.isCreated || !self.isAttached){
                    def.rejectWith("not created or attached");
                    return;
                }
                var e = self.panel.trigger("product-unload", [self]);
                if (e.isDefaultPrevented()){
                    def.rejectWith("default prevented");
                    return;
                }
                self.doUnload().then(def.resolve).fail(def.reject);
            }).then(function(){
                self.panel.trigger("product-unloaded", [self]);
            }).promise();
        },
        doUnload: function(){

            return _fn.resolved;
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.fn,
    FooGallery.utils.obj,
    FooGallery.utils.transition
);
(function($, _, _utils, _obj, _animation){

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
                    _animation.requestFrame(function(){
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
    FooGallery.utils.obj,
    FooGallery.utils.animation
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
(function($, _, _utils, _obj, _str){

    _.Panel.Embed = _.Panel.Html.extend({
        construct: function(panel, item){
            this._super(panel, item);
            _obj.extend(this.opt, panel.opt.embed);
            _obj.extend(this.cls, panel.cls.embed);
            _obj.extend(this.sel, panel.sel.embed);
        }
    });

    _.Panel.media.register("embed", _.Panel.Embed);

    _.template.configure("core", {
        panel: {
            embed: {}
        }
    },{
        panel: {
            embed: {
                type: "fg-media-embed"
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
(function($, _, _utils, _is, _obj, _url){

    _.Panel.Video = _.Panel.Media.extend({
        construct: function(panel, item){
            this._super(panel, item);
            _obj.extend(this.opt, panel.opt.video);
            _obj.extend(this.cls, panel.cls.video);
            _obj.extend(this.sel, panel.sel.video);
            this.urls = [];
            this.isSelfHosted = false;
        },
        parseHref: function(){
            var self = this, urls = self.item.href.split(','), result = [];
            for (var i = 0, il = urls.length, url, source; i < il; i++){
                if (_is.empty(urls[i])) continue;
                url = _url.parts(urls[i]);
                source = null;
                for (var j = 0, jl = self.panel.videoSources.length; j < jl; j++){
                    if (self.panel.videoSources[j].canPlay(url)){
                        source = self.panel.videoSources[j];
                        result.push({
                            parts: url,
                            source: source,
                            embed: source.getEmbedUrl(url, self.opt.autoPlay)
                        });
                        break;
                    }
                }
            }
            return result;
        },
        doCreateContent: function(){
            this.urls = this.parseHref();
            this.isSelfHosted = $.map(this.urls, function(url){ return url.source.selfHosted ? true : null; }).length > 0;
            return this.isSelfHosted ? $('<video/>', this.opt.attrs.video) : $('<iframe/>', this.opt.attrs.iframe);
        },
        doLoad: function(){
            var self = this;
            return $.Deferred(function(def){
                if (self.urls.length === 0){
                    def.rejectWith("no urls available");
                    return;
                }
                var promise = self.isSelfHosted ? self.loadSelfHosted() : self.loadIframe();
                promise.then(def.resolve).fail(def.reject);
            }).promise();
        },
        loadSelfHosted: function(){
            var self = this;
            return $.Deferred(function(def){
                self.$content.off("loadeddata error");
                self.$content.find("source").remove();
                if (!_is.empty(self.item.cover)){
                    self.$content.attr("poster", self.item.cover);
                }
                self.$content.on({
                    'loadeddata': function(){
                        self.$content.off("loadeddata error");
                        this.volume = self.opt.volume;
                        if (self.opt.autoPlay){
                            var p = this.play();
                            if (typeof p !== 'undefined'){
                                p.catch(function(){
                                    console.log("Unable to autoplay video due to policy changes: https://developers.google.com/web/updates/2017/09/autoplay-policy-changes");
                                });
                            }
                        }
                        def.resolve(self);
                    },
                    'error': function(){
                        self.$content.off("loadeddata error");
                        def.reject(self);
                    }
                });
                var sources = $.map(self.urls, function(url){
                    return $("<source/>", {src: url.embed, mimeType: url.source.mimeType});
                });
                self.$content.append(sources);
                if (self.$content.prop("readyState") > 0){
                    self.$content.get(0).load();
                }
            }).promise();
        },
        loadIframe: function(){
            var self = this;
            return $.Deferred(function(def){
                if (!_is.empty(self.item.cover)){
                    self.$content.css("background-image", "url('" + self.item.cover + "')");
                }
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
                self.$content.attr("src", self.urls[0].embed);
            }).promise();
        }
    });

    _.Panel.media.register("video", _.Panel.Video);

    _.template.configure("core", {
        panel: {
            video: {
                autoPlay: false,
                volume: 0.2,
                attrs: {
                    iframe: {
                        src: '',
                        frameborder: 'no',
                        allow: "autoplay; fullscreen",
                        allowfullscreen: true
                    },
                    video: {
                        controls: true,
                        preload: false,
                        controlsList: "nodownload"
                    }
                }
            }
        }
    },{
        panel: {
            video: {
                type: "fg-media-video"
            }
        }
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.obj,
    FooGallery.utils.url
);
(function($, _, _utils, _is, _url, _str){

    var videoEl = document.createElement("video");

    _.Panel.Video.Source = _utils.Class.extend({
        construct: function(mimeType, regex, selfHosted, embedParams, autoPlayParam){
            this.mimeType = mimeType;
            this.regex = regex;
            this.selfHosted = _is.boolean(selfHosted) ? selfHosted : false;
            this.embedParams = _is.array(embedParams) ? embedParams : [];
            this.autoPlayParam = _is.hash(autoPlayParam) ? autoPlayParam : {};
            this.canPlayType = this.selfHosted && _is.fn(videoEl.canPlayType) ? $.inArray(videoEl.canPlayType(this.mimeType), ['probably','maybe']) !== -1 : true;
        },
        canPlay: function(urlParts){
            return this.canPlayType && this.regex.test(urlParts.href);
        },
        mergeParams: function(urlParts, autoPlay){
            var self = this;
            for (var i = 0, il = self.embedParams.length, ip; i < il; i++){
                ip = self.embedParams[i];
                urlParts.search = _url.param(urlParts.search, ip.key, ip.value);
            }
            if (!_is.empty(self.autoPlayParam)){
                urlParts.search = _url.param(urlParts.search, self.autoPlayParam.key, autoPlay ? self.autoPlayParam.value : '');
            }
            return urlParts.search;
        },
        getId: function(urlParts){
            var match = urlParts.href.match(/.*\/(.*?)($|\?|#)/);
            return match && match.length >= 2 ? match[1] : null;
        },
        getEmbedUrl: function(urlParts, autoPlay){
            urlParts.search = this.mergeParams(urlParts, autoPlay);
            return _str.join('/', location.protocol, '//', urlParts.hostname, urlParts.pathname) + urlParts.search + urlParts.hash;
        }
    });

    _.Panel.Video.sources = new _utils.Factory();

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.is,
    FooGallery.utils.url,
    FooGallery.utils.str
);
(function(_){

    _.Panel.Video.Dailymotion = _.Panel.Video.Source.extend({
        construct: function(){
            this._super(
                'video/daily',
                /(www.)?dailymotion\.com|dai\.ly/i,
                false,
                [
                    {key: 'wmode', value: 'opaque'},
                    {key: 'info', value: '0'},
                    {key: 'logo', value: '0'},
                    {key: 'related', value: '0'}
                ],
                {key: 'autoplay', value: '1'}
            );
        },
        getId: function(urlParts){
            return /\/video\//i.test(urlParts.href)
                ? urlParts.href.split(/\/video\//i)[1].split(/[?&]/)[0].split(/[_]/)[0]
                : urlParts.href.split(/dai\.ly/i)[1].split(/[?&]/)[0];
        },
        getEmbedUrl: function(urlParts, autoPlay){
            var id = this.getId(urlParts);
            urlParts.search = this.mergeParams(urlParts, autoPlay);
            return location.protocol + '//www.dailymotion.com/embed/video/' + id + urlParts.search + urlParts.hash;
        }
    });

    _.Panel.Video.sources.register('video/daily', _.Panel.Video.Dailymotion);

})(
    FooGallery
);
(function(_){

    _.Panel.Video.Mp4 = _.Panel.Video.Source.extend({
        construct: function(){
            this._super('video/mp4', /\.mp4/i, true);
        }
    });
    _.Panel.Video.sources.register('video/mp4', _.Panel.Video.Mp4);

    _.Panel.Video.Webm = _.Panel.Video.Source.extend({
        construct: function(){
            this._super('video/webm', /\.webm/i, true);
        }
    });
    _.Panel.Video.sources.register('video/webm', _.Panel.Video.Webm);

    _.Panel.Video.Wmv = _.Panel.Video.Source.extend({
        construct: function(){
            this._super('video/wmv', /\.wmv/i, true);
        }
    });
    _.Panel.Video.sources.register('video/wmv', _.Panel.Video.Wmv);

    _.Panel.Video.Ogv = _.Panel.Video.Source.extend({
        construct: function(){
            this._super('video/ogg', /\.ogv|\.ogg/i, true);
        }
    });
    _.Panel.Video.sources.register('video/ogg', _.Panel.Video.Ogv);

})(
    FooGallery
);
(function(_){

    _.Panel.Video.Vimeo = _.Panel.Video.Source.extend({
        construct: function(){
            this._super(
                'video/vimeo',
                /(player.)?vimeo\.com/i,
                false,
                [
                    {key: 'badge', value: '0'},
                    {key: 'portrait', value: '0'}
                ],
                {key: 'autoplay', value: '1'}
            );
        },
        getEmbedUrl: function(urlParts, autoPlay){
            var id = this.getId(urlParts);
            urlParts.search = this.mergeParams(urlParts, autoPlay);
            return location.protocol + '//player.vimeo.com/video/' + id + urlParts.search + urlParts.hash;
        }
    });

    _.Panel.Video.sources.register('video/vimeo', _.Panel.Video.Vimeo);

})(
    FooGallery
);
(function(_, _is, _url){

    _.Panel.Video.Wistia = _.Panel.Video.Source.extend({
        construct: function(){
            this._super(
                'video/wistia',
                /(.+)?(wistia\.(com|net)|wi\.st)\/.*/i,
                false,
                [],
                {
                    iframe: {key: 'autoPlay', value: '1'},
                    playlists: {key: 'media_0_0[autoPlay]', value: '1'}
                }
            );
        },
        getType: function(href){
            return /playlists\//i.test(href) ? 'playlists' : 'iframe';
        },
        mergeParams: function(urlParts, autoPlay){
            var self = this;
            for (var i = 0, il = self.embedParams.length, ip; i < il; i++){
                ip = self.embedParams[i];
                urlParts.search = _url.param(urlParts.search, ip.key, ip.value);
            }
            if (!_is.empty(self.autoPlayParam)){
                var param = self.autoPlayParam[self.getType(urlParts.href)];
                urlParts.search = _url.param(urlParts.search, param.key, autoPlay ? param.value : '');
            }
            return urlParts.search;
        },
        getId: function(urlParts){
            return /embed\//i.test(urlParts.href)
                ? urlParts.href.split(/embed\/.*?\//i)[1].split(/[?&]/)[0]
                : urlParts.href.split(/medias\//)[1].split(/[?&]/)[0];
        },
        getEmbedUrl: function(urlParts, autoPlay){
            var id = this.getId(urlParts);
            urlParts.search = this.mergeParams(urlParts, autoPlay);
            return location.protocol + '//fast.wistia.net/embed/'+this.getType(urlParts.href)+'/' + id + urlParts.search + urlParts.hash;
        }
    });

    _.Panel.Video.sources.register('video/wistia', _.Panel.Video.Wistia);

})(
    FooGallery,
    FooGallery.utils.is,
    FooGallery.utils.url
);
(function(_){

    _.Panel.Video.YouTube = _.Panel.Video.Source.extend({
        construct: function(){
            this._super(
                'video/youtube',
                /(www.)?youtube|youtu\.be/i,
                false,
                [
                    {key: 'modestbranding', value: '1'},
                    {key: 'rel', value: '0'},
                    {key: 'wmode', value: 'transparent'},
                    {key: 'showinfo', value: '0'}
                ],
                {key: 'autoplay', value: '1'}
            );
        },
        getId: function(urlParts){
            return /embed\//i.test(urlParts.href)
                ? urlParts.href.split(/embed\//i)[1].split(/[?&]/)[0]
                : urlParts.href.split(/v\/|v=|youtu\.be\//i)[1].split(/[?&]/)[0];
        },
        getEmbedUrl: function(urlParts, autoPlay){
            var id = this.getId(urlParts);
            urlParts.search = this.mergeParams(urlParts, autoPlay);
            return 'https://www.youtube-nocookie.com/embed/' + id + urlParts.search + urlParts.hash;
        }
    });

    _.Panel.Video.sources.register('video/youtube', _.Panel.Video.YouTube);

})(
    FooGallery
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
        onAnchorClickItem: function(e, tmpl, item){
            e.preventDefault();
            this.open(item);
        },
        onDestroyedTemplate: function(e, tmpl){
            this.destroy();
        },
        onAfterState: function(e, tmpl, state){
            if (state.item instanceof _.Item){
                this.open(state.item);
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
            enabled = this.opt.lightbox.enabled || _is.hash(data) || (this.$el.length > 0 && this.$el.get(0).hasAttribute("data-foogallery-lightbox"));

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
			self.masonry.layout();
		},
		onDestroy: function(event, self){
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
			this._lastRefresh = 0;
			this._refresh = null;
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
			$(window).on("resize.justified", {self: self}, self.onWindowResize);
			this._refresh = setInterval(function(){
				self.refresh();
			}, self.options.refreshInterval);
		},
		destroy: function(){
			if (this._refresh) clearInterval(this._refresh);
			$(window).off("resize.justified");
			this.$el.removeAttr("style");
		},
		refresh: function(){
			var maxWidth = this.getContainerWidth();
			if (maxWidth != this._lastRefresh){
				this.layout();
				this._lastRefresh = maxWidth;
			}
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
		layout: function(refresh, autoCorrect){
			refresh = _is.boolean(refresh) ? refresh : false;
			autoCorrect = _is.boolean(autoCorrect) ? autoCorrect : true;

			if (refresh || this._items.length === 0){
				this.parse();
			}

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
			// if our layout caused the container width to get smaller
			// i.e. makes a scrollbar appear then layout again to account for it
			if (autoCorrect && self.getContainerWidth() < maxWidth){
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
				if (item.height != self.options.rowHeight){
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
		},
		onWindowResize: function(e){
			e.data.self.layout( true );
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
(function($, _, _is){

	_.JustifiedTemplate = _.Template.extend({
		onPreInit: function(event, self){
			self.justified = new _.Justified( self, self.template );
		},
		onInit: function(event, self){
			self.justified.init();
		},
		onFirstLoad: function(event, self){
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
		onAfterPageChange: function(event, self, current, prev, isFilter){
			if (!isFilter){
				self.justified.layout( true );
			}
		},
		onAfterFilterChange: function(event, self){
			self.justified.layout( true );
		}
	});

	_.template.register("justified", _.JustifiedTemplate, null, {
		container: "foogallery fg-justified"
	});

})(
		FooGallery.$,
		FooGallery,
		FooGallery.utils.is
);
(function($, _, _utils, _is, _fn){

	_.PortfolioTemplate = _.Template.extend({
		construct: function(element, options){
			this._super(element, options);
			/**
			 *
			 * @type {?HTMLStyleElement}
			 */
			this.style = null;

			this.fullWidth = false;
		},
		/**
		 * @summary Creates or gets the CSS stylesheet element for this template instance.
		 * @memberof FooGallery.MasonryTemplate#
		 * @function getStylesheet
		 * @returns {StyleSheet}
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
		onPreInit: function(event, self){
			self.appendCSS();
		},
		onPostInit: function(event, self){
			self.checkCSS();
			$(window).on("resize" + self.namespace, {self: self}, _fn.debounce(function () {
				self.checkCSS();
			}, 50));
		},
		onDestroy: function(event, self){
			self.removeCSS();
			$(window).off("resize" + self.namespace);
		},
		checkCSS: function(){
			var self = this, maxWidth = self.getContainerWidth(), current = maxWidth < self.template.columnWidth;
			if (current !== self.fullWidth){
				self.appendCSS(maxWidth);
			}
		},
		appendCSS: function(maxWidth){
			var self = this;
			maxWidth = _is.number(maxWidth) ? maxWidth : self.getContainerWidth();

			self.removeCSS();

			var sheet = self.getStylesheet(), rule,
				container = '#' + self.id + self.sel.container,
				item = container + ' ' + self.sel.item.elem,
				width = self.template.columnWidth,
				gutter = Math.ceil(self.template.gutter / 2);

			switch (self.template.align) {
				case "center":
					rule = container + ' { justify-content: center; }';
					sheet.insertRule(rule , 0);
					break;
				case "left":
					rule = container + ' { justify-content: flex-start; }';
					sheet.insertRule(rule , 0);
					break;
				case "right":
					rule = container + ' { justify-content: flex-end; }';
					sheet.insertRule(rule , 0);
					break;
			}
			self.fullWidth = maxWidth < width;
			if (self.fullWidth){
				rule = item + ' { max-width: 100%; margin: ' + gutter + 'px; }';
				sheet.insertRule(rule , 0);
			} else {
				rule = item + ' { max-width: ' + width + 'px; min-width: ' + width + 'px; margin: ' + gutter + 'px; }';
				sheet.insertRule(rule , 0);
			}
		},
		removeCSS: function(){
			var self = this;
			if (self.style && self.style.parentNode){
				self.style.parentNode.removeChild(self.style);
				self.style = null;
				self.fullWidth = false;
			}
		}
	});

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
			item.fix();
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
(function($, _, _is, _fn, _obj, _t){

	_.FooGridTemplate = _.Template.extend({
		construct: function(options, element){
			var self = this;
			self._super(options, element);
			self.$section = null;
			self.panel = new _.Panel( self, self.template );
			self.isFirst = false;
		},
		onPreInit: function(event, self){
			self.$section = $('<section/>', {'class': 'foogrid-content'});
			if (self.panel.opt.transition === "none"){
				if (self.$el.hasClass("foogrid-transition-horizontal")){
					self.panel.opt.transition = "horizontal";
				}
				if (self.$el.hasClass("foogrid-transition-vertical")){
					self.panel.opt.transition = "vertical";
				}
				if (self.$el.hasClass("foogrid-transition-fade")){
					self.panel.opt.transition = "fade";
				}
			}
			if (self.panel.opt.info === "none"){
				if (self.$el.hasClass("foogrid-caption-below")){
					self.panel.opt.info = "bottom";
				}
				if (self.$el.hasClass("foogrid-caption-right")){
					self.panel.opt.info = "right";
				}
			}
			if (self.panel.opt.theme === null){
				self.panel.opt.theme = self.getCSSClass("theme");
			}
			if (self.panel.opt.theme === "fg-light" && self.panel.opt.button === null){
				self.panel.opt.button = "fg-button-blue";
			}
			if (self.panel.opt.theme === "fg-dark" && self.panel.opt.button === null){
				self.panel.opt.button = "fg-button-dark";
			}
		},
		ready: function(){
			var self = this;
			if (self._super()){
				_.breakpoints.register(self.$el, self.template.outerBreakpoints);
				return true;
			}
			return false;
		},
		destroy: function(preserveState){
			var self = this, _super = self._super.bind(self);
			return self.panel.destroy().then(function(){
				_.breakpoints.remove(self.$el);
				self.$section.remove();
				return _super(preserveState);
			});
		},
		onPanelNext: function(event, self, panel, currentItem, nextItem){
			event.preventDefault();
			self.open(nextItem);
		},
		onPanelPrev: function(event, self, panel, currentItem, prevItem){
			event.preventDefault();
			self.open(prevItem);
		},
		onPanelClose: function(event, self, panel){
			event.preventDefault();
			self.close(false, true);
		},
		onPanelAreaLoad: function(event, self, area, media){
			if (area.name === "content"){
				media.item.$el.addClass(self.cls.visible);
			}
		},
		onPanelAreaUnload: function(event, self, area, media){
			if (area.name === "content"){
				media.item.$el.removeClass(self.cls.visible);
			}
		},
		onParsedItem: function(event, self, item){
			if (item.isError) return;
			item.$anchor.on("click.gg", {self: self, item: item}, self.onAnchorClick);
			item.$el.append($("<span/>").addClass([self.cls.currentPointer, self.panel.opt.theme].join(' ')));
		},
		onCreatedItem: function(event, self, item){
			if (item.isError) return;
			item.$anchor.on("click.gg", {self: self, item: item}, self.onAnchorClick);
			item.$el.append($("<span/>").addClass([self.cls.currentPointer, self.panel.opt.theme].join(' ')));
		},
		onDestroyItem: function(event, self, item){
			if (item.isError) return;
			item.$anchor.off("click.gg", self.onAnchorClick);
			item.$el.find(self.sel.currentPointer).remove();
		},
		onAfterState: function(event, self, state){
			if (!(state.item instanceof _.Item)) return;
			self.open(state.item);
		},
		onBeforePageChange: function(event, self, current, next, setPage, isFilter){
			if (isFilter) return;
			if (!self.panel.isMaximized) self.close(true, self.panel.isAttached);
		},
		onBeforeFilterChange: function(event, self, current, next, setFilter){
			if (!self.panel.isMaximized) self.close(true, self.panel.isAttached);
		},
		onAnchorClick: function(e){
			e.preventDefault();
			e.data.self.toggle(e.data.item);
		},


		transitionsEnabled: function(){
			return _t.supported && !this.disableTransitions && this.panel.hasTransition;
		},
		isNewRow: function( item ){
			var self = this,
				oldTop = self.getOffsetTop(self.panel.currentItem),
				newTop = self.getOffsetTop(item);
			return oldTop !== newTop;
		},
		getOffsetTop: function(item){
			return item instanceof _.Item && item.isCreated ? item.$el.offset().top : 0;
		},
		scrollTo: function(scrollTop, when, duration){
			var self = this;

			scrollTop = (_is.number(scrollTop) ? scrollTop : 0) - (+self.template.scrollOffset);
			when = _is.boolean(when) ? when : true;
			duration = _is.number(duration) ? duration : 300;

			var $wp = $('#wpadminbar'), $page = $('html, body');
			if ($wp.length === 1){
				scrollTop -= $wp.height();
			}

			return $.Deferred(function(d){
				if (!self.template.scroll || !when){
					d.resolve();
				} else if (self.template.scrollSmooth && !self.panel.isMaximized){
					$page.animate({ scrollTop: scrollTop }, duration, function(){
						d.resolve();
					});
				} else {
					$page.scrollTop(scrollTop);
					d.resolve();
				}
			});
		},

		open: function(item){
			var self = this;
			if (item.index !== -1){
				var newRow = self.isNewRow(item);
				if (self.panel.currentItem instanceof _.Item && newRow && !self.panel.isMaximized){
					return self.doClose(newRow).then(function(){
						if (!!self.pages && !self.pages.contains(self.pages.current, item)){
							self.pages.goto(self.pages.find(item));
						}
						return self.doOpen(item, newRow);
					});
				}
				if (!!self.pages && !self.pages.contains(self.pages.current, item)){
					self.pages.goto(self.pages.find(item));
				}
				return self.doOpen(item, newRow);
			}
			return $.when();
		},
		doOpen: function(item, newRow){
			var self = this;
			return $.Deferred(function(def){

				self.scrollTo(self.getOffsetTop(item), newRow || self.isFirst).then(function(){

					self.panel.appendTo(self.$section);
					if (newRow) item.$el.after(self.$section);
					if (self.transitionOpen(newRow)){
						self.isFirst = false;
						_t.start(self.$section, self.cls.visible, true, 350).then(function(){
							def.resolve();
						});
					} else {
						self.$section.addClass(self.cls.visible);
						def.resolve();
					}

				});

			}).then(function(){
				return self.scrollTo(self.getOffsetTop(item), true);
			}).then(function(){
				return self.panel.load(item);
			}).then(function(){
				self.$section.focus();
				self.isBusy = false;
			}).promise();
		},
		transitionOpen: function(newRow){
			return this.transitionsEnabled() && !this.panel.isMaximized && ((this.template.transitionOpen && this.isFirst) || (this.template.transitionRow && newRow));
		},
		close: function(immediate, newRow){
			immediate = _is.boolean(immediate) ? immediate : false;
			var self = this, previous = self.disableTransitions;
			self.disableTransitions = immediate;
			return self.doClose(newRow).then(function(){
				self.disableTransitions = previous;
			});
		},
		doClose: function(newRow){
			var self = this;
			return $.Deferred(function(def){
				if (self.panel.currentItem instanceof _.Item){
					if (newRow) self.panel.currentItem.$el.removeClass(self.cls.visible);
					if (self.transitionClose(newRow)){
						_t.start(self.$section, self.cls.visible, false, 350).then(function(){
							self.panel.doClose(true, true).then(function(){
								def.resolve();
							});
						});
					} else {
						self.$section.removeClass(self.cls.visible);
						self.panel.doClose(true, true).then(function(){
							def.resolve();
						});
					}
				} else {
					def.resolve();
				}
			}).always(function(){
				self.$section.detach();
				self.isFirst = true;
			}).promise();
		},
		transitionClose: function(newRow){
			return this.transitionsEnabled() && !this.panel.isMaximized && ((this.template.transitionRow && newRow) || (this.template.transitionOpen && !newRow));
		},
		toggle: function(item){
			var self = this;
			if (item instanceof _.Item){
				if (self.panel.currentItem === item){
					return self.close();
				} else {
					return self.open(item);
				}
			}
			return _fn.reject();
		}
	});

	_.template.register("foogrid", _.FooGridTemplate, {
		template: {
			classNames: "foogrid-panel",
			scroll: true,
			scrollOffset: 0,
			scrollSmooth: false,
			loop: true,
			external: '_blank',
			externalText: null,
			keyboard: true,
			transitionRow: true,
			transitionOpen: true,
			info: "bottom",
            infoVisible: true,
            infoOverlay: false,
			buttons: {
				fullscreen: false,
			},
			outerBreakpoints: {
				"x-small": 480,
				small: 768,
				medium: 1024,
				large: 1280,
				"x-large": 1600
			}
		}
	}, {
		container: "foogallery foogrid",
		currentPointer: "fg-current-pointer",
		visible: "foogrid-visible"
	});

})(
	FooGallery.$,
	FooGallery,
	FooGallery.utils.is,
	FooGallery.utils.fn,
	FooGallery.utils.obj,
	FooGallery.utils.transition
);
(function($, _, _utils, _obj){

    _.SliderTemplate = _.Template.extend({
        construct: function(options, element){
            var self = this;
            self._super(_obj.extend({}, options, {
                paging: {
                    type: "none"
                }
            }), element);
            self.items.ALLOW_CREATE = false;
            self.items.ALLOW_APPEND = false;
            self.items.ALLOW_LOAD = false;
            self.panel = new _.Panel(self, self.template);
        },
        preInit: function(){
            if (this._super()){
                this.$el.toggleClass(this.cls.fitContainer, this.template.fitContainer);
                this.template.horizontal = this.$el.hasClass("fgs-horizontal") || this.template.horizontal;
                if (this.panel.opt.thumbs === null){
                    this.panel.thumbs.opt.position = this.template.horizontal ? "bottom" : "right";
                }
                if (this.$el.hasClass("fgs-no-captions")){
                    this.template.noCaptions = true;
                    this.panel.thumbs.opt.captions = !this.template.noCaptions;
                }
                if (this.$el.hasClass("fgs-content-nav")){
                    this.template.contentNav = true;
                    this.panel.opt.buttons.prev = this.panel.opt.buttons.next = this.template.contentNav;
                }
                if (this.panel.opt.button === null){
                    this.panel.opt.button = this.getPanelButtonClass();
                }
                return true;
            }
            return false;
        },
        ready: function(){
            var self = this;
            if (self._super()){
                _.breakpoints.register(self.$el, self.template.outerBreakpoints, function () {
                    self.panel.resize();
                });
                self.panel.appendTo(self.$el);
                self.panel.load(self.state.current.item);
                return true;
            }
            return false;
        },
        destroy: function(preserveState){
            var self = this, _super = self._super.bind(self);
            return self.panel.destroy().then(function(){
                _.breakpoints.remove(self.$el);
                return _super(preserveState);
            });
        },
        getPanelButtonClass: function(){
            var className = this.$el.prop("className"),
                match = /(?:^|\s)fgs-(purple|red|green|blue|orange)(?:$|\s)/.exec(className);

            return match != null && match.length >= 2 ? "fg-button-" + match[1] : null;
        },
    });

    _.template.register("slider", _.SliderTemplate, {
        template: {
            horizontal: false,
            noCaptions: false,
            contentNav: false,

            fitContainer: false,
            fitMedia: true,
            transition: "horizontal",
            hoverButtons: true,
            preserveButtonSpace: false,
            noMobile: true,
            thumbs: null,
            thumbsSmall: true,
            info: "top",
            infoVisible: true,
            buttons: {
                close: false,
                info: false,
                maximize: false,
                fullscreen: false
            },
            outerBreakpoints: {
                "x-small": 480,
                small: 768,
                medium: 1024,
                large: 1280,
                "x-large": 1600
            }
        }
    }, {
        container: "foogallery fg-slider",
        fitContainer: "fg-fit-container"
    });

})(
    FooGallery.$,
    FooGallery,
    FooGallery.utils,
    FooGallery.utils.obj
);
(function ($, _, _utils, _obj) {

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
			"first-load.foogallery after-page-change.foogallery after-filter-change.foogallery": _.triggerPostLoad
		}
	};

	_.autoEnabled = true;

	_.auto = function (options) {
		_.autoDefaults = _obj.merge(_.autoDefaults, options);
	};

	_.load = _.reload = function(){
		// this automatically initializes all templates on page load
		$(function () {
			if (_.autoEnabled){
				$('[id^="foogallery-gallery-"]:not(.fg-ready)').foogallery(_.autoDefaults);
			}
		});

		_utils.ready(function () {
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
		FooGallery.utils.obj
);