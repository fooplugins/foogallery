jQuery(document).ready(function ($) {

	// Generate google account token on button click
	$(document).on('click','#foogallery-google-photos-token-btn',function(e){
		var $clicked = $(this);
		var provider = 'google';
		var result = $('#foogalery-' + provider + '-result');
		var args = {
			'action': 'foogallery_google_photos_token', 
			'provider': provider, 
			'code': $('#foogallery-' + provider + '-oauth-code').val(), 
			'state': $('#foogallery-' + provider + '-oauth-state').val(), 
			'_ajax_nonce': $clicked.data('foogallery-nonce') 
		};

		// Send ajax call to set code and state value in database and save toeken after generate
		$.post(ajaxurl, args, function(data) {
			//data = $.parseJSON(data);
			console.log(data);
			$("<span class='button button-disabled'></span>").insertBefore(result);
			$(result).html('<strong>Refresh Token:</strong> <code id="' + provider + '-token">' + data['refresh_token'] + '</code>');
            var a = $("<a href='#' class='button button-primary' data-foogallery-provider='" + provider + "' data-foogallery-nonce='" + data['nonce'] +"'>Save Token</a>");
			a.insertAfter(result);
			
			var token_status = data['status'];
			var result = '';
			// Show token status before redirect
			if ( token_status == 'success' ) {
				result = '<p><strong>Status: </strong>Token generate successfully.</p><p><strong>Token: </strong>' + data['refresh_token'] + '</p>';
			} else {
				result = '<p><strong>Status: </strong>Token could not generate.</p>';
			}
			$('#foogallery-google-result').html(result);

			// Redirect to google photos tab on settings page after success or fail in generate token
			setTimeout(function(){
				window.location.href = google_photos.setting_url
			}, 3000);
		});
	});
	
});