/*!
 * Vid - A simple utility method for retrieving video information.
 * @version 0.0.2
 * @link https://bitbucket.org/fooplugins/vid
 * @copyright FooPlugins 2016
 * @license Released under the GPLv3 license.
 */
!function (a) {
	a.vid = function (b, c) {
		return (b instanceof a.vid.Video ? b : new a.vid.Video(b, c)).fetch()
	}, a.vid.defaults = {
		autoPlay: !1,
		youTubeKey: null,
		mimeTypes: {"video/mp4": /\.mp4/i, "video/webm": /\.webm/i, "video/wmv": /\.wmv/i, "video/ogg": /\.ogv/i},
		mimeTypeGroup: {
			custom: ["video/mp4", "video/wmv", "video/ogg", "video/webm"],
			ie: ["video/mp4", "video/wmv"],
			other: ["video/mp4", "video/ogg", "video/webm"]
		}
	}, a.vid.config = function (b) {
		a.extend(!0, a.vid.defaults, b)
	}, a.vid.registerMimeType = function (b, c) {
		"string" == typeof b && (c instanceof RegExp || "object" == typeof c && c.regex instanceof RegExp) ? a.vid.defaults.mimeTypes[b] = c : console.log("Failed to register mime type: ", b, c)
	}
}(jQuery), function (a, b) {
	b.Url = function (a) {
		return this instanceof b.Url ? (this.url = "", this.base = "", this.hash = "", this.protocol = "", this.params = [], void this.init(a)) : new b.Url(a)
	}, b.Url.prototype.init = function (a) {
		if (!("string" != typeof a || a.length < 5)) {
			this.url = a;
			var b = a.split("#");
			this.hash = 2 == b.length ? "#" + b[1] : "", b = b[0].split("?"), this.base = b[0], this.protocol = "https" == a.substring(0, 5) ? "https:" : "http:", this.params = [];
			for (var c, d = (2 == b.length ? b[1] : "").split(/[&;]/g), e = 0,
					 f = d.length; f > e; e++)c = d[e].split("="), 2 == c.length && this.param(decodeURIComponent(c[0]), decodeURIComponent(c[1]))
		}
	}, b.Url.prototype.param = function (a, b) {
		for (var c = "undefined" == typeof b, d = "string" == typeof b && "" === b,
				 e = this.params.length; e-- > 0;)if (this.params[e].key == a)return c ? this.params[e].value : (d ? this.params.splice(e, 1) : this.params[e].value = b, this);
		return c ? null : (d || this.params.push({key: a, value: b}), this)
	}, b.Url.prototype.toString = function () {
		for (var a = this.params.length > 0 ? "?" : "", b = 0,
				 c = this.params.length; c > b; b++)0 != b && (a += "&"), a += encodeURIComponent(this.params[b].key) + "=" + encodeURIComponent(this.params[b].value);
		return this.base + a + this.hash
	}
}(jQuery, jQuery.vid), function (a, b) {
	b.Video = function (a, c) {
		return this instanceof b.Video ? (this.options = {}, this.url = null, this.id = "", this.api = "", this.mimeType = "", this.mimeTypeOptions = null, this.custom = !1, this.supported = !1, this.fetched = !1, this.title = "", this.description = "", this.credits = "", this.thumb_small = "", this.thumb_large = "", void this.init(a, c)) : new b.Video(a, c)
	}, b.Video.prototype.init = function (c, d) {
		if (this.url = new b.Url(c), "" !== this.url.url) {
			this.options = a.extend(!0, {}, a.vid.defaults, d);
			var e = this.url.base.match(/.*\/(.*)$/);
			this.id = e && e.length >= 2 ? e[1] : null, this.parseMimeType(c);
			var f = navigator.userAgent.toLowerCase(),
				g = f.indexOf("msie ") > -1 || f.indexOf("trident/") > -1 || f.indexOf("edge/") > -1;
			this.custom = -1 !== a.inArray(this.mimeType, this.options.mimeTypeGroup.custom), this.supported = -1 !== a.inArray(this.mimeType, g ? this.options.mimeTypeGroup.ie : this.options.mimeTypeGroup.other), this.mimeTypeOptions && a.isFunction(this.mimeTypeOptions.init) && this.mimeTypeOptions.init.call(this.mimeTypeOptions, this)
		}
	}, b.Video.prototype.parseMimeType = function (a) {
		for (var b in this.options.mimeTypes)if (this.options.mimeTypes.hasOwnProperty(b)) {
			var c = this.options.mimeTypes[b], d = c instanceof RegExp ? c : c.regex;
			d.test(a) && (this.mimeType = b, this.mimeTypeOptions = c)
		}
	}, b.Video.prototype.fetch = function () {
		var b = this;
		return a.Deferred(function (c) {
			return b.fetched ? void c.resolve(b) : "" === b.api || !b.mimeTypeOptions || !a.isFunction(b.mimeTypeOptions.parse) || a.isFunction(b.mimeTypeOptions.enabled) && !b.mimeTypeOptions.enabled.call(b.mimeTypeOptions, b) ? (b.fetched = !0, void c.reject(Error("No additional information can be retrieved for this video."), b)) : void a.ajax({
				url: b.api,
				type: "GET",
				dataType: "jsonp"
			}).then(function (a) {
				b.mimeTypeOptions.parse.call(b.mimeTypeOptions, a, b), b.fetched = !0, c.resolve(b)
			}, function (a) {
				b.fetched = !0, c.reject(a, b)
			})
		})
	}
}(jQuery, jQuery.vid), function (a, b) {
	b.registerMimeType("video/daily", {
		regex: /(www.)?dailymotion\.com|dai\.ly/i, init: function (a) {
			a.supported = !0, a.id = /\/video\//i.test(a.url.base) ? a.url.base.split(/\/video\//i)[1].split(/[?&]/)[0].split(/[_]/)[0] : a.url.url.split(/dai\.ly\//i)[1].split(/[?&]/)[0], a.embed = a.url.protocol + "//www.dailymotion.com/embed/video/" + a.id + "?wmode=opaque&info=0&logo=0&related=0" + (a.options.autoPlay ? "&autoplay=1" : ""), a.api = "https://www.dailymotion.com/services/oembed?url=https://dai.ly/" + a.id
		}, parse: function (a, b) {
			a.title && a.author_name && a.thumbnail_url && (b.title = a.title, b.description = a.description, b.credits = a.author_name, b.thumb_small = a.thumbnail_url, b.thumb_large = a.thumbnail_url)
		}
	})
}(jQuery, jQuery.vid), function (a, b) {
	b.registerMimeType("video/vimeo", {
		regex: /(player.)?vimeo\.com/i, init: function (a) {
			a.supported = !0, a.id = a.url.base.substr(a.url.base.lastIndexOf("/") + 1), a.embed = a.url.protocol + "//player.vimeo.com/video/" + a.id + "?badge=0&portrait=0" + (a.options.autoPlay ? "&autoplay=1" : ""), a.api = "https://vimeo.com/api/v2/video/" + a.id + ".json"
		}, parse: function (a, b) {
			a.length && (b.title = a[0].title, b.description = a[0].description, b.credits = a[0].user_name, b.thumb_small = a[0].thumbnail_small, b.thumb_large = a[0].thumbnail_large)
		}
	})
}(jQuery, jQuery.vid), function (a, b) {
	b.registerMimeType("video/wistia", {
		regex: /(.+)?(wistia\.(com|net)|wi\.st)\/.*/i, init: function (a) {
			a.supported = !0, a.id = /embed\//i.test(a.url.base) ? a.url.base.split(/embed\/.*?\//i)[1].split(/[?&]/)[0] : a.url.base.split(/medias\//)[1].split(/[?&]/)[0];
			var b = /playlists\//i.test(a.url.base);
			a.embed = a.url.protocol + "//fast.wistia.net/embed/" + (b ? "playlists" : "iframe") + "/" + a.id + "?theme=" + (a.options.autoPlay ? b ? "&media_0_0[autoPlay]=1" : "$autoPlay=1" : ""), a.api = "https://fast.wistia.net/oembed.json?url=" + a.url.url
		}, parse: function (b, c) {
			if (b.title && b.provider_name && b.thumbnail_url) {
				var d = new a.vid.Url(b.thumbnail_url);
				c.title = b.title, c.credits = b.provider_name, c.thumb_small = d.param("image_crop_resized", "100x60").toString(), c.thumb_large = d.param("image_crop_resized", "800x480").toString()
			}
		}
	})
}(jQuery, jQuery.vid), function (a, b) {
	b.registerMimeType("video/youtube", {
		regex: /(www.)?youtube|youtu\.be/i, enabled: function (a) {
			return null !== a.options.youTubeKey
		}, init: function (a) {
			a.supported = !0, a.id = /embed\//i.test(a.url.base) ? a.url.base.split(/embed\//i)[1].split(/[?&]/)[0] : a.url.url.split(/v\/|v=|youtu\.be\//i)[1].split(/[?&]/)[0], a.embed = a.url.protocol + "//www.youtube.com/embed/" + a.id + "?modestbranding=1&rel=0&wmode=transparent&showinfo=0" + (a.options.autoPlay ? "&autoplay=1" : ""), a.api = "https://www.googleapis.com/youtube/v3/videos?id=" + a.id + "&fields=items(snippet(title,description,channelTitle,thumbnails))&part=snippet&key=" + a.options.youTubeKey
		}, parse: function (a, b) {
			if (a.items && a.items.length) {
				var c = a.items[0].snippet;
				b.title = c.title, b.description = c.description, b.credits = c.channelTitle, b.thumb_small = this.thumb(c), b.thumb_large = this.thumb(c, !0)
			}
		}, thumb_sizes: ["default", "medium", "high", "standard", "maxres"], thumb: function (a, b) {
			var c = JSON.parse(JSON.stringify(this.thumb_sizes)), d = a.thumbnails;
			b && c.reverse();
			for (var e = 0, f = c.length; f > e; e++)if (d.hasOwnProperty(c[e]))return d[c[e]].url;
			return ""
		}
	})
}(jQuery, jQuery.vid);

//
// $('#element').donetyping(callback[, timeout=1000])
// Fires callback when a user has finished typing. This is determined by the time elapsed
// since the last keystroke and timeout parameter or the blur event--whichever comes first.
//   @callback: function to be called when even triggers
//   @timeout:  (default=1000) timeout, in ms, to to wait before triggering event if not
//              caused by blur.
// Requires jQuery 1.7+
//
;(function ($) {
	$.fn.extend({
		donetyping: function (callback, timeout) {
			timeout = timeout || 500; // 1 second default timeout
			var timeoutReference,
				doneTyping = function (el) {
					if (!timeoutReference) return;
					timeoutReference = null;
					callback.call(el);
				};
			return this.each(function (i, el) {
				var $el = $(el);
				// Chrome Fix (Use keyup over keypress to detect backspace)
				// thank you @palerdot
				$el.is(':input') && $el.on('keyup keypress paste', function (e) {
					// This catches the backspace button in chrome, but also prevents
					// the event from triggering too preemptively. Without this line,
					// using tab/shift+tab will make the focused element fire the callback.
					if (e.type == 'keyup' && e.keyCode != 8) return;

					// Check if timeout has been set. If it has, "reset" the clock and
					// start over again.
					if (timeoutReference) clearTimeout(timeoutReference);
					timeoutReference = setTimeout(function () {
						// if we made it here, our timeout has elapsed. Fire the
						// callback
						doneTyping(el);
					}, timeout);
				}).on('blur', function () {
					// If we can, fire the event since we're leaving the field
					doneTyping(el);
				});
			});
		}
	});
})(jQuery);

(function (FOOVIDEO, FOOGALLERY, $, undefined) {

	var importPlaylistData, importPlaylistItems;

	FOOVIDEO.bindMediaTabControl = function () {
		$('.foovideo-type-select input').change(function (e) {
			var selectedType = $(this).val(), $root = $(this).closest(".media-modal");

			$root.find('.foovideo-browser').hide();

			$root.find('#foovideo-type-' + selectedType + '-content').show();

			FOOVIDEO.resetSelectedVideos($root);

			setTimeout(function () {
				$('.foovideo-searchbox:visible').focus();
			}, 250);

		})
	};

	FOOVIDEO.bindVideoObjectToResult = function (video) {
		var $result = $('<div class="foovideo-result foovideo-youtube">' +
			'<div class="foovideo-thumbnail attachment selected">' +
			'<img src="' + video.thumb_large + '" title="' + video.title + '">' +
			'</div><div class="foovideo-details">' +
			'<h4><a href="' + (video.url || video.embed) + '" target="_blank">' + video.title + '</a></h4><div class="foovideo-meta">' +
			'</div>' +
			'<div class="foovideo-description">' + video.description + '</div>' +
			'<input type="hidden" class="foovideo-import" /></div></div>');

		$result.find('.foovideo-import').data('video-data', video);

		var $root = $('.foovideo-other-results').html($result).show().closest(".media-modal");

		//clear previous selection
		FOOVIDEO.resetSelectedVideos($root);

		//force add to selection
		$('.foovideo-other-results .foovideo-thumbnail.attachment').click();
	};

	FOOVIDEO.bindOtherVideo = function () {
		$('.foovideo-toolbar.foovideo-other .foovideo-searchbox').donetyping(function (e) {
			var $input = $(this),
				url = $input.val(),
				spinner = $('.video-search-spinner:visible'),
				youTubeKey = $('#foovideo-youTubeKey').val();

			spinner.css('visibility', 'visible');

			jQuery.vid.config({youTubeKey: youTubeKey});
			try {

				jQuery.vid(url).then(function (video) {
					//we got back something so use it
					spinner.css('visibility', 'hidden');
					$('#foovideo_detail_URL').val(url);
					$('#foovideo_detail_ID').val(video.id);
					$('#foovideo_detail_Title').val(video.title);
					$('#foovideo_detail_Description').val(video.description);
					$('#foovideo_detail_Thumbnail').val(video.thumb_large);
					FOOVIDEO.bindVideoObjectToResult(video);
					$('.foovideo-other-sidebar-inner').show();

				}, function (err, video) {
					spinner.css('visibility', 'hidden');
					if (video.custom) {
						$('#foovideo_detail_URL').val(url);
						$('#foovideo_detail_ID').val(video.id);
						$('#foovideo_detail_Title').val('');
						$('#foovideo_detail_Description').val('');
						$('#foovideo_detail_Thumbnail').val('');
						$('.foovideo-other-results:visible').html('We detected that your video is self-hosted. Please provide more details on the right...');
						$('.foovideo-other-sidebar-inner').show();
						$('#foovideo_detail_Title').focus();
					} else {
						$('.foovideo-other-results:visible').html('The video is not supported!');
					}
				});

			} catch (e) {
				$('.foovideo-other-results:visible').html('There was a problem getting any video info!');
				spinner.css('visibility', 'hidden');
			}
		});

		$('.foovideo-other-sidebar .foovideo_detail').donetyping(function () {
			FOOVIDEO.updateVideoObject();
		}, 100);

		$('.show-foovideo-other-sidebar-inner').click(function () {
			$('.foovideo-other-sidebar-inner').show();
		});

		// Hooking on the uploader queue (on reset):
		if (typeof wp.Uploader !== 'undefined' && typeof wp.Uploader.queue !== 'undefined') {
			wp.Uploader.queue.on('reset', function () {
				//an image was uploaded!
			});
		}

		$('.foovideo_browse').click(function (e) {
			e.preventDefault();

			var page = $(this).data('page'),
				attachmentData = {
					foo_video_nonce: $('#foo_video_nonce').val(),
					action: 'foo_video_attachments',
					page: page
				},
				$attachment_container = $('.foovideo_select_attachment');

			$(this).data('page', page + 1);

			$attachment_container.addClass('loading').slideDown();

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: attachmentData,
				success: function (response) {
					if (response.success) {
						for (var i = 0; i < response.data.length; i++) {
							var attachment = response.data[i],
								$img = $(attachment.html);
							$img.data('image', attachment.image);
							$attachment_container.find('.spinner').before($img);
						}
					}
				},
				error: function (response) {
					alert('There was a problem fetching attachments!');
				},
				complete: function () {
					$attachment_container.removeClass('loading');
				}
			});
		});

		$('.foovideo_select_attachment').on('click', 'img', function (e) {
			$('.foovideo_select_attachment').slideUp();
			$('#foovideo_detail_Thumbnail').val($(this).data('image'));
			FOOVIDEO.updateVideoObject();
		});
	};

	FOOVIDEO.updateVideoObject = function () {
		//first validate that we have all the fields we need
		var video = {
			"id": $('#foovideo_detail_ID').val(),
			"custom": true,
			"embed": $("#foovideo_detail_URL").val(),
			"thumb_large": $("#foovideo_detail_Thumbnail").val(),
			"title": $("#foovideo_detail_Title").val(),
			"description": $("#foovideo_detail_Description").val()
		};

		FOOVIDEO.bindVideoObjectToResult(video);
	};

	FOOVIDEO.initImport = function () {
		var loading_line = $('.upload_image_button > span');
		var loading_text = $('#import-playlist-id').data('loading');
		var image_button = $('.upload_image_button > div');
		// gallery list
		importPlaylistData.prev_text = loading_line.text();

		loading_line.text(loading_text);
		image_button.removeClass('dashicons-format-gallery').addClass('spinner').css({
			"background-position": "center center",
			"float": "none",
			"visibility": "visible",
		});
		$(loading_line).after('<span class="foovideo-import-progress"><span class="foovideo-import-progress-bar" style="width: 0%;"></span></span>');
		importPlaylistItems = [];

		wp.media.frame.close();
		$('.foovideo-results').empty();
		$('.foovideo-searchbox').val('');

		FOOVIDEO.doImportCall();
	};

	FOOVIDEO.closeImport = function () {
		var image_button = $('.upload_image_button > div'),
			loading_line = $('.upload_image_button > span'),
			progress_bar = $('.foovideo-import-progress');

		progress_bar.remove();
		loading_line.text(importPlaylistData.prev_text);
		image_button.removeClass('spinner').addClass('dashicons-format-gallery').css({
			"background-position": "",
			"float": "",
			"visibility": "",
		});
		for (var i = 0; i < importPlaylistItems.length; i++) {
			FOOGALLERY.addAttachmentToGalleryList(importPlaylistItems[i]);
		}
	};

	FOOVIDEO.doImportCall = function () {
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: importPlaylistData,
			success: function (response) {
				if (response.success && response.data.ids.length) {
					for (var i = 0; i < response.data.ids.length; i++) {
						importPlaylistItems.push(response.data.ids[i]);
					}
					if (response.data.offset) {
						$('.foovideo-import-progress-bar').css('width', response.data.percent + '%');
						importPlaylistData.offset = response.data.offset;
						FOOVIDEO.doImportCall();
					} else {
						importPlaylistData.complete = true;
					}
				}
			},
			error: function (response) {
				alert('There was a problem importing the video!');
				importPlaylistData.complete = true;
			},
			complete: function () {
				if (importPlaylistData.complete) {
					$('.foovideo-import-progress-bar').css('width', '100%');
					setTimeout(function () {
						FOOVIDEO.closeImport();
					}, 100);
				}
			}
		});
	};

	FOOVIDEO.importPlaylist = function () {
		var playlist_id = $('#import-playlist-id').val();
		if (playlist_id == '') {
			$('#foovideo_gallery_id').css('background-color', 'red');
			return;
		}

		var nonce = $('#foo_video_nonce').val();
		var gallery_id = $('#foovideo_gallery_id').val();
		var type = $('#import-playlist-type').val();
		var action = 'foo_video_gallery_import_' + type;

		importPlaylistData = {
			playlist_id: playlist_id,
			foo_video_nonce: nonce,
			gallery_id: gallery_id,
			type: type,
			action: action,
			offset: 0
		};

		FOOVIDEO.initImport();
	};

	FOOVIDEO.importSelection = function (selection) {

		var nonce = $('#foo_video_nonce').val();
		var action = 'foo_video_gallery_import_selection';

		importPlaylistData = {
			selection: selection,
			foo_video_nonce: nonce,
			action: action,
			offset: 0
		};

		FOOVIDEO.initImport();
	};

	FOOVIDEO.bindImportPlaylist = function () {
		//bind import playlist
		$(document).on('click', '.foovideo-playlist-import', function (e) {
			e.preventDefault;
			FOOVIDEO.importPlaylist();
		});

	};

	FOOVIDEO.initMediaFrame = function () {
		//if we cant do anything then get out!
		if (typeof wp == 'undefined' || typeof wp.media == 'undefined') {
			return;
		}

		var l10n = wp.media.view.l10n;
		wp.media.view.MediaFrame.Select = wp.media.view.MediaFrame.Select.extend({
			browseRouter: function (routerView) {
				routerView.set({
					upload: {
						text: l10n.uploadFilesTitle,
						priority: 20
					},
					browse: {
						text: l10n.mediaLibraryTitle,
						priority: 40
					},
					video: {
						text: "Video",
						priority: 50
					}
				});
				routerView.select = function(id){
					wp.media.view.Router.prototype.select.apply(this, arguments);
					if (id === "video"){
						var view = this.get( id );
						setTimeout(function () {
							var $root = view.controller.$el;
							$root.find(".media-frame-content").html(jQuery('#video-search-tmpl').html());
							$root.find('.foovideo-searchbox:first').focus();
							FOOVIDEO.bindMediaTabControl();
							FOOVIDEO.bindOtherVideo();
							FOOVIDEO.bindVideoSearch();
						}, 20);
					}
				};
			}
		});
	};

	FOOVIDEO._doingSearch = false;

	FOOVIDEO.bindVideoSearch = function () {
		$('.foovideo-youtube .foovideo-searchbox, .foovideo-vimeo .foovideo-searchbox').donetyping(function (e) {
			var page = 1,
				adtype = 'html',
				clicked = $(this),
				field = $('.foovideo-searchbox:visible'),
				spinner = $('.video-search-spinner:visible'),
				data = {
					foo_video_nonce: $('#foo_video_nonce').val(),
					action: 'foo_video_search',
					type: field.data('type'),
					q: field.val(),
					vidpage: page
				};

			if (data.q.length < 3) {
				return;
			}

			if (FOOVIDEO._doingSearch) {
				FOOVIDEO._doingSearch.abort();
			}

			spinner.css('visibility', 'visible');
			FOOVIDEO._doingSearch = $.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function (response) {
					$('.foovideo-results:visible')[adtype](response);
				},
				complete: function () {
					spinner.css('visibility', 'hidden');
				}
			});

		});

		$(document).on('click', '.foovideo-loadmore', function (e) {
			var clicked = $(this),
				page = clicked.data('page'),
				adtype = 'append',
				field = $('.foovideo-searchbox:visible'),
				spinner = $('.video-search-spinner:visible'),
				data = {
					foo_video_nonce: $('#foo_video_nonce').val(),
					action: 'foo_video_search',
					type: field.data('type'),
					q: field.val(),
					vidpage: page
				};

			if (!clicked.hasClass('foovideo-loadmore')) {
				return;
			}

			if (FOOVIDEO._doingSearch) {
				FOOVIDEO._doingSearch.abort();
			}

			spinner.css('visibility', 'visible');
			FOOVIDEO._doingSearch = $.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function (response) {
					$('.foovideo-results:visible')[adtype](response);
					clicked.remove();
				},
				complete: function () {
					spinner.css('visibility', 'hidden');
				}
			});
		});
	};

	FOOVIDEO.bindSearchResults = function () {
		$(document).on('click', '.foovideo-thumbnail.attachment', function () {
			var clicked = $(this), $root = $(this).closest(".media-modal");
			if (clicked.hasClass('playlist')) {
				return;
			}
			if (clicked.hasClass('details')) {
				clicked.removeClass('details');
			} else {
				clicked.addClass('details');
			}
			if ($('.foovideo-thumbnail.attachment.details').length) {
				$root.find('.media-button-select').prop('disabled', false).addClass('foovideo-import');
			} else {
				$root.find('.media-button-select').prop('disabled', true).removeClass('foovideo-import');
			}
		});
	};

	FOOVIDEO.bindImportVideo = function () {
		$(document).on('click', '.foovideo-import', function () {
			var videos = $('.foovideo-thumbnail.attachment.details'),
				selection = [];
			for (var v = 0; v < videos.length; v++) {
				var video = $(videos[v]).closest('.foovideo-result').find('.foovideo-import');
				if (video.data('video-data')) {
					var jsonData = JSON.stringify(video.data('video-data'));
					selection.push(JSON.parse(jsonData));
				} else {
					selection.push(video.val());
				}
			}

			// send
			if (selection.length) {
				//$('.media-button-select').prop( 'disabled', true );
				FOOVIDEO.importSelection(selection);
			}
		});
	};

	FOOVIDEO.resetSelectedVideos = function ($root) {
		//toggle any selected items by removing the details class
		$root.find('.foovideo-thumbnail.attachment.details').removeClass('details');

		//make the toggle button unclickable
		$root.find('.media-button-select').prop('disabled', true).removeClass('foovideo-import');
	};

}(window.FOOVIDEO = window.FOOVIDEO || {}, window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));

jQuery(function ($) {
	FOOVIDEO.bindImportPlaylist();
	FOOVIDEO.initMediaFrame();
	FOOVIDEO.bindSearchResults();
	FOOVIDEO.bindImportVideo();
});
