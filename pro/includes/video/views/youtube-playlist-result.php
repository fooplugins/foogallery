 <?php
	/*
	* Template for a single vimeo result item ( multiple )
	*/
	echo '<div class="foovideo-result foovideo-playlist">';
		echo '<div class="foovideo-thumbnail attachment selected playlist">';
			echo '<img src="' . esc_attr( $isplaylist['thumbnail_url'] ) . '" title="' . esc_attr( $isplaylist['title'] ) . '">';
		echo '</div>';
		echo '<div class="foovideo-details">';
			echo '<h4><a href="https://www.youtube.com/playlist?list=' . $isplaylist['playlist_id'] . '" target="_blank">' . $isplaylist['title'] . '</a></h4>';
			echo '<div class="foovideo-meta">';
				echo __('by', 'foogallery' ) . ' ' .  $isplaylist['author_name'];
				echo ' &bull; ' . sprintf( _n( '%s view', '%s views', $results['views'], 'foogallery' ), $results['views'] );
				echo ' &bull; ' . sprintf( _n( '%s video', '%s videos', count( $results['video'] ), 'foogallery' ), count( $results['video'] ) );
			echo '</div>';
			echo '<br><div class="foovideo-meta">';
				echo '<button type="button" id="foovideo-playlist-import" class="button foovideo-playlist-import">' . __(' Import Playlist', 'foogallery') . '</button>';
			echo '</div>';
			echo '<input type="hidden" value="' . esc_attr( $isplaylist['playlist_id'] ) . '" id="import-playlist-id" data-loading="' . esc_attr( __('Importing Playlist') ) . '">';
			echo '<input type="hidden" value="youtube" id="import-playlist-type">';

		echo '</div>';
	echo '</div>';
	echo '<hr>';
