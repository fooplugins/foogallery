(function(FOOGALLERYALBUM, $, undefined) {

	FOOGALLERYALBUM.bindElements = function() {
		$('.foogallery-album-gallery-list')
			.on('click', '.foogallery-gallery-select', function(e) {
				$(this).toggleClass('selected');
				FOOGALLERYALBUM.changeSelection();
			})
			.sortable({
				items: 'li',
				distance: 10,
				placeholder: 'attachment placeholder',
				stop : function() {
					FOOGALLERYALBUM.changeSelection();
				}
			});
	};

	FOOGALLERYALBUM.changeSelection = function() {
		var ids = '',
			none = true;
		$('.foogallery-gallery-select.selected').each(function() {
			ids += $(this).data('foogallery-id') + ',';
			none = false;
		});

		if (!none) {
			ids = ids.substring(0, ids.length - 1);
		}
		//build up the list of ids
		$('#foogallery_album_galleries').val(ids);
	};

	$(function() { //wait for ready
		FOOGALLERYALBUM.bindElements();
	});

}(window.FOOGALLERYALBUM = window.FOOGALLERYALBUM || {}, jQuery));