jQuery(function ($) {
	$('.gallery_datasources_button').on('click', function(e) {
		e.preventDefault();
		$('.foogallery-datasources-modal-wrapper').show();
	});

	$('.foogallery-datasources-modal-wrapper').on('click', '.media-modal-close, .foogallery-datasource-modal-cancel', function(e) {
		$('.foogallery-datasources-modal-wrapper').hide();
	});

	$('.foogallery-datasources-modal-wrapper').on('click', '.foogallery-datasource-modal-insert', function(e) {
		var activeDatasource = $('.foogallery-datasource-modal-selector.active').data('datasource');

		// Validate folder path before insertion using enhanced security
		if (validateFolderPath(activeDatasource)) {
			// Set the datasource
			$('#foogallery_datasource').val(activeDatasource);

			// Raise a general event so that other datasources can clean up
			$(document).trigger('foogallery-datasource-changed', activeDatasource);

			// Raise a specific event for the new datasource so that things can be done
			$(document).trigger('foogallery-datasource-changed-' + activeDatasource);

			// Hide the datasource modal
			$('.foogallery-datasources-modal-wrapper').hide();
		} else {
			alert("Invalid folder path detected. Please ensure the path is valid and does not contain any traversal sequences (e.g., ../).");
		}
	});

	$('.foogallery-datasources-modal-wrapper').on('click', '.foogallery-datasource-modal-reload', function(e) {
		e.preventDefault();

		var $wrapper = $('.foogallery-datasources-modal-wrapper'),
			datasource = $wrapper.data('datasource'),
			$content = $('.foogallery-datasource-modal-container-inner.' + datasource);

		$content.addClass('not-loaded');

		// Force the refresh
		$('.foogallery-datasource-modal-selector.active').click();
	});

	$('.foogallery-datasource-modal-selector').on('click', function(e) {
		e.preventDefault();

		var datasource = $(this).data('datasource'),
			$content = $('.foogallery-datasource-modal-container-inner.' + datasource),
			$wrapper = $('.foogallery-datasources-modal-wrapper');

		$('.foogallery-datasource-modal-selector').removeClass('active');
		$(this).addClass('active');

		$('.foogallery-datasource-modal-container-inner').hide();

		$content.show();

		var datasource_value = $('#_foogallery_datasource_value').val();

		if ( $content.hasClass('not-loaded') ) {
			$content.find('.spinner').addClass('is-active');

			$content.removeClass('not-loaded');

			var data = 'action=foogallery_load_datasource_content' +
				'&datasource=' + datasource +
				'&datasource_value=' + encodeURIComponent(datasource_value) +
				'&foogallery_id=' + $wrapper.data('foogalleryid') +
				'&nonce=' + $wrapper.data('nonce');

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					$('.foogallery-datasource-modal-reload').show();
					$wrapper.data('datasource', datasource );

					$content.html(data);
					// Raise an event so that datasource-specific code can run
					$(document).trigger('foogallery-datasource-content-loaded-' + datasource);
				}
			});
		}
	});

	/**
	 * Validates the folder path to prevent path traversal attacks.
	 * This function performs several checks to ensure the path is safe.
	 *
	 * @param {string} folderPath - The folder path to validate.
	 * @return {boolean} - Returns true if the path is safe, false otherwise.
	 */
	function validateFolderPath(folderPath) {
		// Ensure the input is a non-empty string
		if (!folderPath || typeof folderPath !== 'string') {
			console.error('Invalid folder path type provided');
			return false;
		}

		try {
			// Decode the path to catch encoded traversal attempts
			const decodedPath = decodeURIComponent(folderPath);

			// Define patterns to detect invalid characters and traversal attempts
			const invalidPatterns = [
				/\.\./,                    // Simple directory traversal
				/%2e%2e|%252e%252e/i,      // Encoded traversal
				/[<>:"\\|?*\x00-\x1F]/,    // Invalid characters for filenames
				/\/\/+|\\\\+/,             // Multiple consecutive slashes
				/^\/|^[a-zA-Z]:[\/\\]/,    // Absolute paths or drive letters (Windows)
				/^~|^%7e/i                 // Home directory shortcut
			];

			// Test decoded path against all invalid patterns
			for (const pattern of invalidPatterns) {
				if (pattern.test(decodedPath)) {
					console.warn('Security validation failed for pattern:', pattern);
					return false;
				}
			}

			// Normalize and clean up the path
			const normalizedPath = decodedPath
				.replace(/\\/g, '/')      // Convert backslashes to forward slashes
				.replace(/\/+/g, '/')     // Collapse multiple slashes
				.replace(/^\.\//, '')     // Remove leading single dot paths
				.trim();

			// Additional check to ensure path length and segment restrictions
			if (
				normalizedPath.length === 0 ||
				normalizedPath.length > 255 ||
				normalizedPath.split('/').some(part => part.length > 255)
			) {
				console.warn('Path exceeds allowed length limits');
				return false;
			}

			// Whitelist approach: allow alphanumeric characters, hyphens, underscores, and slashes
			const safePathPattern = /^[a-zA-Z0-9-_\/\s.]+$/;
			if (!safePathPattern.test(normalizedPath)) {
				console.warn('Path contains invalid characters');
				return false;
			}

			// Save the last validated path if needed for debugging or future use
			window.lastValidatedPath = normalizedPath;
			return true;

		} catch (e) {
			console.error('Path validation error:', e);
			return false;
		}
	}
});
