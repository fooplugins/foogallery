<?php
	/*
	* Template for a single ID based item result
	*/
		echo '<div class="foovideo-result foovideo-vimeo">';
			echo '<div class="foovideo-thumbnail attachment selected">';
				echo '<img src="' . esc_attr( $video['thumbnail_url'] ) . '" title="' . esc_attr( $video['title'] ) . '">';
			echo '</div>';
			echo '<div class="foovideo-details">';
				echo '<h4><a href="' . $video['provider_url'] . $video['video_id'] . '" target="_blank">' . $video['title'] . '</a></h4>';
				echo '<div class="foovideo-meta">';
					echo __('by', 'foogallery' ) . ' <a href="' . $video['author_url'] . '" target="_blank">' . $video['author_name'] . '</a>';
				echo '</div>';
				echo '<div class="foovideo-description">';
					echo wpautop( substr( $video['description'],0, 280 ) . '&hellip;' );
				echo '</div>';
				echo '<input type="hidden" class="foovideo-import" value="' . foogallery_foovideo_esc_json_encode( $video ) . '">';
			echo '</div>';
		echo '</div>';