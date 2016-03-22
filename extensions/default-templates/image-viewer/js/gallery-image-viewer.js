(function($){

	$.ImageViewer = function(element, options){
		if (!(this instanceof $.ImageViewer)) return new $.ImageViewer(element, options);
		this.options = $.extend(true, {}, $.ImageViewer.defaults, options);
		this.$el = $(element);
		this.$items = this.$el.find('.fiv-inner-container > a');
		this.$current = this.$el.find('.fiv-count-current');
		this.$prev = this.$el.find('.fiv-prev');
		this.$next = this.$el.find('.fiv-next');
		this._init();
	};

	$.ImageViewer.defaults = {

	};

	$.ImageViewer.prototype._init = function(){
		this.$el.on('foobox.previous', {self: this}, this.onFooBoxPrev)
			.on('foobox.next', {self: this}, this.onFooBoxNext);
		this.$prev.on('click', {self: this}, this.onPrevClick);
		this.$next.on('click', {self: this}, this.onNextClick);
		this.$items.removeClass('fiv-active').first().addClass('fiv-active');
	};

	$.ImageViewer.prototype.reinit = function(options){
		this.destroy();
		this.options = $.extend(true, {}, $.ImageViewer.defaults, options);
		this._init();
	};

	$.ImageViewer.prototype.destroy = function(){
		this.$el.off('foobox.previous', this.onFooBoxPrev).off('foobox.next', this.onFooBoxNext);
		this.$prev.off('click', this.onPrevClick);
		this.$next.off('click', this.onNextClick);
	};

	$.ImageViewer.prototype.prev = function(){
		var $current = this.$items.filter('.fiv-active').removeClass('fiv-active'),
			$prev = $current.prev();

		if ($prev.length == 0) $prev = this.$items.last();
		$prev.addClass('fiv-active');
		this.$current.text($prev.index() + 1);
	};

	$.ImageViewer.prototype.next = function(){
		var $current = this.$items.filter('.fiv-active').removeClass('fiv-active'),
			$next = $current.next();

		if ($next.length == 0) $next = this.$items.first();
		$next.addClass('fiv-active');
		this.$current.text($next.index() + 1);
	};

	$.ImageViewer.prototype.onFooBoxPrev = function(e){
		e.data.self.prev();
	};

	$.ImageViewer.prototype.onFooBoxNext = function(e){
		e.data.self.next();
	};

	$.ImageViewer.prototype.onPrevClick = function(e){
		e.preventDefault();
		e.stopPropagation();
		e.data.self.prev();
	};

	$.ImageViewer.prototype.onNextClick = function(e){
		e.preventDefault();
		e.stopPropagation();
		e.data.self.next();
	};

	$.fn.imageViewer = function(options){
		return this.each(function(i, el){
			var $el = $(el), fiv;
			if (fiv = $el.data('__imageViewer__')){
				fiv.reinit(options);
			} else {
				fiv = new $.ImageViewer(el, options);
				$el.data('__imageViewer__', fiv);
			}
		});
	};
})(jQuery);

/**
 * Small ready function to circumvent external errors blocking jQuery's ready.
 * @param {Function} callback - The function to call when the document is ready.
 */
function FooGallery_ImageViewer_Ready(callback) {
	if (Function('/*@cc_on return true@*/')() ? document.readyState === "complete" : document.readyState !== "loading") callback($);
	else setTimeout(function () { FooGallery_ImageViewer_Ready(callback); }, 1);
}

FooGallery_ImageViewer_Ready(function() {
	jQuery('.foogallery-image-viewer').imageViewer();
});