<?php
	/*
	* Template for a single vimeo result item ( multiple )
	*/
	echo '<div class="foovideo-result foovideo-vimeo">';
		echo '<div class="foovideo-thumbnail attachment selected">';
			echo '<img src="' . esc_attr( $video['thumbnail'] ) . '" title="' . esc_attr( $video['title'] ) . '">';
		echo '</div>';
		echo '<div class="foovideo-details">';
			echo '<h4><a href="https://www.vimeo.com/' . $video['id'] . '" target="_blank">' . $video['title'] . '</a></h4>';
			echo '<div class="foovideo-meta">';
				if( empty( $isplaylist ) ){
					echo __('by', 'foo-video' ) . ' <a href="https://www.vimeo.com/channel/' . $video['owner']['url'] . '" target="_blank">' . $video['owner']['display_name'] . '</a> &bull; ';
				}
				echo $video['uploaded_ago'];
				echo ' &bull; ' . sprintf( _n( '%s play', '%s plays', $video['stats']['plays'], 'foo-video' ), $video['stats']['plays'] );
			echo '</div>';
			echo '<div class="foovideo-description">';
				echo substr( $video['description'],0, 280 ) . '&hellip;';
			echo '</div>';
			echo '<input type="hidden" class="foovideo-import" value="' . foogallery_foovideo_esc_json_encode( $video ) . '">';
		echo '</div>';
	echo '</div>';