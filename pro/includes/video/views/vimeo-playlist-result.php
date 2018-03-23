<?php
	/*
	* Template for a single vimeo result item ( multiple )
	*/
	echo '<div class="foovideo-result foovideo-playlist">';
		echo '<div class="foovideo-thumbnail attachment selected playlist">';
			echo '<img src="' . esc_attr( $results['stream']['owner']['thumbnail'] ) . '" title="' . esc_attr( $results['stream']['owner']['display_name'] ) . '">';
		echo '</div>';
		echo '<div class="foovideo-details">';
			echo '<h4><a href="' . $query_str . '" target="_blank">' . $results['stream']['title'] . '</a></h4>';
			echo '<div class="foovideo-meta">';
				echo __('by', 'foo-video' ) . ' <a href="' . $results['stream']['owner']['url'] . '" target="_blank">' . $results['stream']['owner']['display_name'] . '</a>';
				echo ' &bull; ' . sprintf( _n( '%s video', '%s videos', $results['stream']['total_clips'], 'foo-video' ), $results['stream']['total_clips'] );
			echo '</div>';
			echo '<br><div class="foovideo-meta">';
				echo '<button type="button" id="foovideo-playlist-import" class="button foovideo-playlist-import">' . __(' Import Playlist', 'foo-video') . '</button>';
			echo '</div>';
			echo '<input type="hidden" value="' . foogallery_foovideo_esc_json_encode( $results['stream'] ) . '" id="import-playlist-id" data-loading="' . esc_attr( __('Importing Playlist') ) . '">';
			echo '<input type="hidden" value="vimeo" id="import-playlist-type">';

		echo '</div>';
	echo '</div>';
	echo '<hr>';