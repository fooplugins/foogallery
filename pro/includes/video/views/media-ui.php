<script type="text/html" id="video-search-tmpl">
	<?php
	// set nonce
	wp_nonce_field( 'foo_video_nonce', 'foo_video_nonce' );

	?>
	<div class="foovideo-type-select">
		<input name="foovideo-type-select" checked="checked" type="radio" id="foovideo-type-youtube" value="youtube">
		<label for="foovideo-type-youtube"><?php _e( 'YouTube', 'foogallery' ); ?></label>

		<input name="foovideo-type-select" type="radio" id="foovideo-type-vimeo" value="vimeo">
		<label for="foovideo-type-vimeo"><?php _e( 'Vimeo', 'foogallery' ); ?></label>

		<input name="foovideo-type-select" type="radio" id="foovideo-type-other" value="other">
		<label for="foovideo-type-other"><?php _e( 'Other Video', 'foogallery' ); ?></label>
	</div>
	<div class="foovideo-browser" id="foovideo-type-youtube-content">
		<div class="foovideo-toolbar foovideo-youtube foovideo-search-enabled">
			<input type="search" placeholder="<?php _e('Search term, URL, Playlist ID or video ID','foogallery'); ?>" class="foovideo-searchbox search" data-type="youtube"><span class="spinner video-search-spinner" style="float: none; display: inline-block; margin-left: 12px; padding: 2px;"></span>
		</div>
		<div class="foovideo-results foovideo-youtube" data-loading="<?php echo esc_attr('Importing Video(s)', 'foogallery'); ?>">
			<div style="padding:12px;">
				<p> <?php _e('There are three ways to add videos from YouTube.', 'foogallery' ); ?></p>
				<ol>
					<li><?php _e('Search using any keywords, highlight one or more videos, then choose Add Media', 'foogallery' ); ?></li>
					<li><?php _e('Paste an individual video ID, or URL and hit Enter', 'foogallery' ); ?></li>
					<li><?php _e('Paste a Playlist URL to import selected or all videos included in the Playlist', 'foogallery' ); ?></li>
				</ol>
			</div>
		</div>
	</div>
	<div class="foovideo-browser" id="foovideo-type-vimeo-content" style="display: none;">
		<div class="foovideo-toolbar foovideo-vimeo foovideo-search-enabled">
			<input type="search" placeholder="<?php _e('Video, Album or User URL','foogallery'); ?>" class="foovideo-searchbox search" data-type="vimeo"><span class="spinner video-search-spinner" style="float: none; display: inline-block; margin-left: 12px; padding: 2px;"></span>
		</div>
		<div class="foovideo-results foovideo-vimeo" data-loading="<?php echo esc_attr('Importing Video(s)', 'foogallery'); ?>">
			<div style="padding:12px;">
				<p> <?php _e('There are three ways to add videos from Vimeo.', 'foogallery' ); ?></p>
				<em>
					<a href="https://vimeo.com/search" title="<?php _e( 'Search Vimeo', 'foogallery' ); ?>" target="_blank">
						<?php _e( 'Click Here To Search For Your Video On Vimeo', 'foogallery' ); ?>
					</a>
				</em>
				<ol>
					<li><?php _e('Paste an individual video URL and hit Enter.', 'foogallery' ); ?></li>
					<li><?php _e('Paste an Album URL to import selected or all videos included in the Playlist.', 'foogallery' ); ?></li>
					<li><?php _e('Paste a User\'s URL to import selected or all videos uploaded by the user.', 'foogallery' ); ?></li>
				</ol>
			</div>
		</div>
	</div>
	<div class="foovideo-browser" id="foovideo-type-other-content" style="display: none;">
		<div class="foovideo-toolbar foovideo-other">
			<input type="search" placeholder="<?php _e('Enter a Vimeo, Wistia, YouTube, Dailymotion or other supported video url','foogallery'); ?>" class="foovideo-searchbox search" data-type="other">
			<span class="spinner video-search-spinner" style="float: none; display: inline-block; margin-left: 12px; padding: 2px;"></span>
			<input type="hidden" id="foovideo-youTubeKey" value="<?php echo foogallery_foovideo_youtubekey(); ?>" />
		</div>
		<div class="foovideo-other-container">
			<div class="foovideo-other-results">
				<h3><?php _e('Quick Start', 'foogallery'); ?></h3>
				<ol>
					<li><?php _e('Simply paste your video URL in the above textbox', 'foogallery'); ?></li>
					<li>Or <a class="show-foovideo-other-sidebar-inner" href="#"><?php _e('manually enter your video details', 'foogallery'); ?></a></li>
				</ol>
				<h3><?php _e('Supported URLs Include:', 'foogallery' ); ?></h3>
				<ul class="ul-disc">
					<li><strong><?php _e('YouTube (including unlisted videos)', 'foogallery' ); ?></strong></li>
					<li><strong><?php _e('Vimeo', 'foogallery' ); ?></strong></li>
					<li><strong><?php _e('Wistia', 'foogallery' ); ?></strong></li>
					<li><strong><?php _e('Dailymotion', 'foogallery' ); ?></strong></li>
					<li><strong><?php _e('Self hosted *.mp4, *.webm and *.ogv videos' ); ?></strong></li>
				</ul>
			</div>
			<div class="foovideo-other-sidebar">
				<div class="foovideo-other-sidebar-inner">
					<h3><?php _e('Video Details', 'foogallery'); ?></h3>
					<p><?php _e('Enter all the information about the video, and then click "Preview Video". Once you are happy with the preview, you can click "Add Media" to import it into your gallery.', 'foogallery' ); ?></p>
					<input type="hidden" id="foovideo_detail_ID" />
					<input type="hidden" id="foovideo_detail_custom" />

					<label for="foovideo_detail_URL"><?php _e('Video URL','foogallery'); ?></label>
					<input class="foovideo_detail" id="foovideo_detail_URL" type="text" />

					<label for="foovideo_detail_Title"><?php _e('Video Title','foogallery'); ?></label>
					<input class="foovideo_detail" id="foovideo_detail_Title" type="text" />

					<label for="foovideo_detail_Description"><?php _e('Video Description','foogallery'); ?></label>
					<textarea class="foovideo_detail" id="foovideo_detail_Description"></textarea>

					<label for="foovideo_detail_Thumbnail"><?php _e('Video Thumbnail URL','foogallery'); ?></label>
					<div class="foovideo_thumbnail_input">
						<input class="foovideo_detail" id="foovideo_detail_Thumbnail" type="text" />
						<a class="foovideo_browse button" data-page="1">select image</a>
					</div>
					<div class="foovideo_select_attachment">
						<span class="spinner"></span>
						<a href="#load_more" class="foovideo_browse" data-page="1"><?php _e('More','foogallery'); ?></a>
						<div style="clear: both" />
					</div>

					<h3><?php _e('Please Note', 'foogallery'); ?></h3>
					<ul class="ul-disc">
						<li><?php _e('When using an existing attachment from your media library, any existing metadata will be overridden with the above info!', 'foogallery' ); ?></li>
						<li>
							<?php _e('For cross-browser support when using self-hosted videos, provide multiple formats using a comma separated list, e.g.', 'foogallery' ); ?>
							<code>http://foo/clip.webm, http://foo/clip.ogv, http://foo/clip.mp4</code>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</script>
