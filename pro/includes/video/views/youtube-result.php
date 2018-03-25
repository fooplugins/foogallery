<?php
	/*
	* Template for a single youtube result item
	*/
	echo '<div class="foovideo-result foovideo-youtube">';
		echo '<div class="foovideo-thumbnail attachment selected">';
			echo '<img src="' . esc_attr( $video['thumbnail'] ) . '" title="' . esc_attr( $video['title'] ) . '">';
		echo '</div>';
		echo '<div class="foovideo-details">';
			echo '<h4><a href="https://www.youtube.com/watch?v=' . $video['encrypted_id'] . '" target="_blank">' . $video['title'] . '</a></h4>';
			echo '<div class="foovideo-meta">';
				if( empty( $isplaylist ) ){
					echo __('by', 'foogallery' ) . ' ' . $video['author'] . '&bull; ';
				}
				echo sprintf( _x( '%s ago', '%s = human-readable time difference', 'foogallery' ), human_time_diff( $video['time_created'], current_time( 'timestamp' ) ) );
				echo ' &bull; ' . sprintf( _n( '%s view', '%s views', $video['views'], 'foogallery' ), $video['views'] );
			echo '</div>';
			echo '<div class="foovideo-description">';
				echo substr( $video['description'],0, 280 ) . '&hellip;';
			echo '</div>';
			echo '<input type="hidden" class="foovideo-import" value="' . foogallery_foovideo_esc_json_encode( $video ) . '">';
		echo '</div>';
	echo '</div>';
