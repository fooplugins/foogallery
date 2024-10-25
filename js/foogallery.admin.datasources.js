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
	 * Enhanced folder path validation to prevent path traversal attempts.
	 * Implements multiple layers of security checks.
	 */
	function validateFolderPath(folderPath) {
		// Guard against null or undefined input
		if (!folderPath || typeof folderPath !== 'string') {
			console.error('Invalid folder path type provided');
			return false;
		}

		// Decode the path first to catch encoded traversal attempts
		try {
			const decodedPath = decodeURIComponent(folderPath);
			
			// Comprehensive validation rules
			const invalidPatterns = [
				/\.\./,                    // Directory traversal
				/\.\.\\|\\\.\./,           // Windows-style traversal
				/%2e%2e/i,                // URL encoded ..
				/%252e%252e/i,            // Double URL encoded ..
				/\.\.%2f/i,               // Mixed encoding
				/\.\.%5c/i,               // Encoded backslash
				/\.\.[\/\\]/,             // Both slash types
				/^\/|^[a-zA-Z]:[\/\\]/,   // Absolute paths
				/^~|^%7e/i,               // Home directory
				/[<>:"\\|?*\x00-\x1F]/,   // Invalid filename chars
				/\/\/+|\\\\+/,            // Multiple slashes
				/^[\s.]|[\s.]$/           // Leading/trailing dots or spaces
			];

			// Check against all invalid patterns
			for (const pattern of invalidPatterns) {
				if (pattern.test(decodedPath)) {
					console.warn('Security validation failed:', pattern);
					return false;
				}
			}

			// Normalize the path
			const normalizedPath = decodedPath
				.replace(/\\/g, '/') // Convert backslashes to forward slashes
				.replace(/\/+/g, '/') // Remove multiple slashes
				.replace(/^\.|\.$/g, '') // Remove single dots
				.trim();

			// Additional security checks
			if (normalizedPath.length === 0 || 
				normalizedPath.length > 255 || // Max path length
				normalizedPath.split('/').some(part => part.length > 255)) { // Max segment length
				return false;
			}

			// Whitelist approach: only allow specific characters
			const safePathPattern = /^[a-zA-Z0-9-_/][a-zA-Z0-9-_/\s.]*$/;
			if (!safePathPattern.test(normalizedPath)) {
				console.warn('Path contains invalid characters');
				return false;
			}

			// Store the sanitized path for later use
			window.lastValidatedPath = normalizedPath;
			
			return true;

		} catch (e) {
			console.error('Path validation error:', e);
			return false;
		}
	}
});
