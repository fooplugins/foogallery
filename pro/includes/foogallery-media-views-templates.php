<script type="text/html" id="tmpl-fg-importer">
	<?php
	$nonce = wp_create_nonce('fgi_nonce');
	echo "<input type=\"hidden\" class=\"fgi_nonce\" value=\"{$nonce}\"/>";
	?>

	<div class="fgi-region-query"></div>
	<div class="fgi-region-content"></div>
	<div class="fgi-region-help"></div>

</script>
<script type="text/html" id="tmpl-fgi-error">

	<div class="fgi-splash fgi-error-message">
		<h2>{{data.title}}</h2>
		<p>{{data.message}}</p>
		<div class="button-hero-container">
			<button type="button" class="button button-hero fgi-ok"><?php _e("OK", "foogallery") ?></button>
		</div>
	</div>

</script>
<script type="text/html" id="tmpl-fgi-help">

	<h2>Video Help</h2>
	<p>FooGallery supports a number of providers for importing videos and provides additional methods of importing multiple videos from YouTube and Vimeo.</p>
	<dl class="fgi-providers">
		<dt class="fgi-provider-title">YouTube</dt>
		<dd class="fgi-provider-content">
			<p>There are a number of ways to import YouTube videos.</p>

			<h4>Single Video</h4>
			<p>Simply enter the videos url into the search input to fetch a single video. The following URL formats are supported:</p>
			<ul>
				<li>http(s)://www.youtube.com/watch?v=[VIDEO_ID]</li>
				<li>http(s)://youtu.be/[VIDEO_ID]</li>
				<li>http(s)://www.youtube.com/embed/[VIDEO_ID]</li>
			</ul>
			<p>You can also provide just the videos' ID in the search input.</p>

			<h4>Playlists</h4>
			<p>Import an entire playlist or a subselection of its' videos by entering its' url into the search input. The following URL format is supported:</p>
			<ul>
				<li>http(s)://www.youtube.com/playlist?list=[PLAYLIST_ID]</li>
			</ul>
			<p>You can also provide just the playlists' ID in the search input.</p>

			<h4>Search</h4>
			<p>You can also perform a search on YouTube and select the videos to import from the results by simply typing your query into the search input.</p>

            <h4>API Key</h4>
            <p>If you want to import multiple videos from a playlist or search result you will need an API key. You can generate one by following the basic instructions below or take a look at our <a href="https://fooplugins.link/youtube-api-key" target="_blank">documentation</a> or the original <a href="https://developers.google.com/youtube/v3/getting-started" target="_blank">Google documentation</a> for more detailed steps.</p>
            <ul>
                <li>You will need a <a href="https://www.google.com/accounts/NewAccount">Google Account</a> to be able to generate an API key.</li>
                <li>
                    Create a new project in your <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a> and fill in the form. If you are not signed in you should be prompted to using your Google account.
                    <ul>
                        <li>In the <b>Project name</b> field enter a value like <b>My FooGallery</b>.</li>
                        <li>You can leave the <b>Location</b> field as its default value of <b>No organization</b>.</li>
                    </ul>
                </li>
                <li>Click the <b>Create</b> button and wait for the new projects dashboard to be displayed.</li>
                <li>You should see a prompt informing you that you don't have any APIs available to use yet. Click the <b>Enable APIs and Services</b> button displayed at the top of the page.</li>
                <li>You should now be in the <b>API Library</b>. Simply enter <b>YouTube</b> into the search input and then select the <b>YouTube Data API v3</b> from the results.</li>
                <li>You will be redirected to the home page for the <b>YouTube Data API v3</b>, click the <b>Enable</b> button and wait for the operation to complete.</li>
                <li>You will now be back on your projects <b>Overview</b> page. Click on the <b>Credentials</b> tab on the left of the screen.</li>
                <li>At the top of the <b>Credentials</b> page click on the <b>Create Credentials</b> button displayed at the top of the page and select the <b>API key</b> option.</li>
                <li>You should get a pop up titled <b>API key created</b> where you can copy <b>Your API key</b> and optionally restrict its usage. For more information about restricting the usage please see the <a href="https://cloud.google.com/docs/authentication/api-keys?hl=en&visit_id=637488474405711980-3007411940&rd=1#securing_an_api_key" target="_blank">Securing an API key</a> documentation.</li>
                <li>Finally take your API key and enter it into either the prompt displayed during import or the <b>FooGallery > Settings > Video > YouTube API Key</b> option.</li>
            </ul>
		</dd>
		<dt class="fgi-provider-title">Vimeo</dt>
		<dd class="fgi-provider-content">
			<p>There are a number of ways to import Vimeo videos.</p>

			<h4>Single Video</h4>
			<p>Simply enter the videos url into the search input to fetch a single video. The following URL formats are supported:</p>
			<ul>
				<li>http(s)://vimeo.com/[VIDEO_ID]</li>
				<li>http(s)://player.vimeo.com/video/[VIDEO_ID]</li>
                <li>http(s)://vimeo.com/showcase/[SHOWCASE_ID]/video/[VIDEO_ID]</li>
				<li>http(s)://vimeo.com/channels/[CHANNEL_ID]/[VIDEO_ID]</li>
			</ul>

			<h4>Showcases</h4>
			<p>Import an entire showcase or a subselection of its' videos by entering its' url into the search input. The following URL formats are supported:</p>
			<ul>
                <li>http(s)://vimeo.com/showcase/[SHOWCASE_ID]</li>
			</ul>

			<h4>Channels</h4>
			<p>Import an entire channel or a subselection of its' videos by entering its' url into the search input. The following URL format is supported:</p>
			<ul>
				<li>http(s)://vimeo.com/channel/[CHANNEL_ID]</li>
			</ul>

			<h4>User Videos</h4>
			<p>Import all videos for a specific user or a subselection of their videos by entering their user url into the search input. The following URL formats are supported:</p>
			<ul>
				<li>http(s)://vimeo.com/[USER_ID]</li>
				<li>http(s)://vimeo.com/[USER_ID]/videos</li>
			</ul>

			<h4>Access Token</h4>
			<p>If you want to import multiple videos from an album, showcase, channel or user then you will need an API access token. You can generate one by following the basic instructions below or take a look at our <a href="https://fooplugins.link/vimeo-access-token" target="_blank">documentation</a> for more detailed steps.</p>
			<ul>
				<li>
					Visit <a href="https://developer.vimeo.com/apps/new" target="_blank">https://developer.vimeo.com/apps/new</a> and fill in the form. If you are not signed in you should be prompted to using your Vimeo account.
					<ul>
						<li>In the <b>App Name</b> field enter a value like <b>My FooGallery</b>.</li>
						<li>In the <b>App Description</b> field enter a value like <b>Allows importing of multiple videos from an album, channel or user into FooGallery</b>.</li>
						<li>In the <b>App URL</b> field enter the URL of your website.</li>
						<li>You can then ignore the <b>App Logo URL</b> and <b>App Callback URLs</b> fields.</li>
						<li>Set the <b>Will you charge users to utilize your app?</b> value to <b>No</b>.</li>
						<li>Set the <b>Will your integration be ad supported?</b> value to <b>No</b>.</li>
						<li>For the <b>What platform(s) will be running this app?</b> checklist simply select <b>Web server</b>.</li>
					</ul>
				</li>
				<li>Agree to the license agreement and terms of service and then click the <b>Create app</b> button.</li>
				<li>Once the app is created select the <b>Authentication</b> tab and scroll down to the <b>Generate an Access Token</b> section.</li>
				<li>For the <b>Scopes</b> checklist the default of <b>Public</b> and <b>Private</b> should be enough however if you are a PRO subscriber or higher and can't access your videos you may also need to select the <b>Video Files</b> option as well.</li>
				<li>Finally click the <b>Generate Token</b> button and then copy the value from the <b>Your new Access token</b> field and enter it into either the prompt displayed during import or the <b>FooGallery > Settings > Video > Vimeo Access Token</b> option.</li>
			</ul>
		</dd>
		<dt class="fgi-provider-title">Self Hosted</dt>
		<dd class="fgi-provider-content">
			<p>FooGallery supports displaying .mp4, .webm and .ogg files hosted either locally on this site or externally using a service like Amazon Web Services.</p>
			<h4>Locally Hosted</h4>
			<p>
				If your video file is hosted locally you can simply click the <b>Choose a video from your Media Library</b> button displayed on the <b>Getting Started</b> screen.
			</p>
			<p>
				You can also just enter the full video url into the search input and you will be prompted to supply any additional information required.
			</p>

			<h4>Externally Hosted</h4>
			<p>If your video file is hosted externally you can just enter the full video url into the search input and you will be prompted to supply any additional information required.</p>

			<h4>URL Formats</h4>
			<p>When entering a URL directly into the search or URL(s) inputs the following formats are supported:</p>
			<ul>
				<li>http(s)://site/path/file_name.mp4</li>
				<li>http(s)://site/path/file_name.webm</li>
				<li>http(s)://site/path/file_name.ogg</li>
				<li>http(s)://site/path/file_name.ogv</li>
			</ul>

			<h4>Additional Information</h4>
			<p>When using either locally or externally hosted files you will be prompted to supply some additional information that FooGallery needs in order to display the video as an item within a gallery.</p>
			<ul>
				<li><b>Thumbnail</b> - The image used to represent the video within a gallery. We recommend larger images as additional sizes are auto-generated and downscaled images look better than upscaled ones.</li>
				<li><b>Title</b> - The title for the video. While not required it is recommended to supply a simple title.</li>
				<li><b>Description</b> - The description for the video.</li>
				<li><b>URL(s)</b> - The URLs are combined and used as required to give the best cross browser compatibility possible. If you are only using a single video format we recommend using .mp4 videos as they cover the majority of modern browsers however the more types you supply the better the compatibility becomes.</li>
			</ul>
		</dd>
		<dt class="fgi-provider-title">WordPress oEmbed</dt>
		<dd class="fgi-provider-content">
			<p>
				FooGallery supports importing videos from all registered WordPress oEmbed video providers by entering the supported video URL into the search input.
				For a full list of these providers please see the <a href="https://codex.wordpress.org/Embeds#oEmbed" target="_blank">official WordPress documentation on oEmbed providers</a>.
			</p>
			<p>Please note that if any required information to import a video as a gallery item is missing you will be prompted to supply it.</p>
			<p>Also note if a providers content can not be moved within a page, for example they use an IFRAME with no SRC, then there may be issues displaying the content within a lightbox.</p>
		</dd>
	</dl>

</script>
<script type="text/html" id="tmpl-fgi-getting-started">

<?php
	$help = sprintf('<a href="#toggle-help">%s</a>', __("help", "foogallery"));
?>

	<div class="fgi-splash fgi-getting-started-instructions">
		<h2><?php _e("Enter a supported URL or a YouTube search term, playlist or video ID in the input above.", "foogallery") ?></h2>
		<p><?php _e("or", "foogallery") ?></p>
		<button type="button" class="button button-hero fgi-select"
						data-options='{"type": "video/mp4,video/ogg,video/ogv,video/webm", "title":"<?php _e("Select a video", "foogallery") ?>", "button":"<?php _e("Select Video", "foogallery") ?>"}'>
			<?php _e("Choose a video from your Media Library", "foogallery") ?>
		</button>
		<p><small><?php printf(__("See the %s for more information.", "foogallery"), $help) ?></small></p>
	</div>

</script>
<script type="text/html" id="tmpl-fgi-query">

	<div class="fgi-query-left">
		<input type="text" class="fgi-query-input" placeholder="<?php _e("Enter a supported URL or a YouTube search term, playlist or video ID", "foogallery") ?>" spellcheck="false"/>
		<span class="fgi-query-input-spinner"></span>
	</div>

	<div class="fgi-query-right">
		<a href="#toggle-help"><?php _e("Help", "foogallery") ?></a>
	</div>

</script>
<script type="text/html" id="tmpl-fgi-self-hosted">

<?php
	$webm = '<a target="_blank" href="https://caniuse.com/#feat=webm">.webm</a>';
	$mp4 = '<a target="_blank" href="https://caniuse.com/#feat=mp4">.mp4</a>';
	$ogg = '<a target="_blank" href="https://caniuse.com/#feat=ogv">.ogg</a>';
?>

	<div class="fgi-self-hosted-notification">
		<p><?php _e("Enter the details for your video below and then click the Import button.", "foogallery") ?></p>
	</div>
	<div class="fgi-self-hosted-details">
		<div class="fgi-form">
			<input type="hidden" name="id" value="{{data.id}}"/>
			<input type="hidden" name="provider" value="{{data.mode}}"/>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("Thumbnail", "foogallery") ?> *</span>
				<span class="fgi-col-input">
					<span class="fgi-browse">
						<span class="fgi-browse-inner">
							<span class="fgi-browse-col-input">
								<input type="text" name="thumbnail" value="{{data.thumbnail}}" spellcheck="false"
											 data-messages='{"required":"<?php _e("You must supply a thumbnail for the video.", "foogallery") ?>","pattern":"<?php _e("Please enter a .jpg, .jpeg, .png or .gif file.", "foogallery") ?>"}'
											 data-pattern="(?:\/|=)(?<name>[^\/]+?)\.(?<ext>jpg|jpeg|png|gif)(?:$|\?|&|#|,)" data-required="true"/>
							</span>
							<span class="fgi-browse-col-button">
								<button type="button" class="button button-secondary"
												data-options='{"type": "image/png,image/jpg,image/jpeg,image/gif", "title":"<?php _e("Select a thumbnail for the video", "foogallery") ?>", "button":"<?php _e("Select Image", "foogallery") ?>"}'
								><?php _e("Select", "foogallery") ?></button>
							</span>
						</span>
					</span>
				</span>
			</label>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("Title", "foogallery") ?></span>
				<span class="fgi-col-input">
					<input type="text" name="title" value="{{data.title}}"/>
				</span>
			</label>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("Description", "foogallery") ?></span>
				<span class="fgi-col-input">
					<textarea name="description" rows="5">{{data.description}}</textarea>
				</span>
			</label>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("URL(s)", "foogallery") ?> *</span>
				<span class="fgi-col-input">
					<# var first = true; _(data.urls).each(function(url, name){ var type = data.types[name]; #>
						<span class="fgi-browse">
							<span class="fgi-browse-inner">
								<span class="fgi-browse-col-type">
									<span>{{name}}</span>
								</span>
								<span class="fgi-browse-col-input">
									<# if (first){ first = false; #>
									<input type="text" name="urls[{{name}}]" value="{{url}}" spellcheck="false" data-type="{{name}}"
												 data-messages='{"required":"<?php _e("You must supply at least one URL.", "foogallery") ?>","pattern":"<?php printf(__("Please enter a %s file.", "foogallery"), "{{type.text}}") ?>"}'
												 data-pattern="(?:\/|=)(?<name>[^\/]+?)\.(?<ext>{{type.pattern}})(?:$|\?|&|#|,)" data-required="[name^='urls[']"/>
									<# } else { #>
									<input type="text" name="urls[{{name}}]" value="{{url}}" spellcheck="false" data-type="{{name}}"
												 data-messages='{"pattern":"<?php printf(__("Please enter a %s file.", "foogallery"), "{{type.text}}") ?>"}'
												 data-pattern="(?:\/|=)(?<name>[^\/]+?)\.(?<ext>{{type.pattern}})(?:$|\?|&|#|,)"/>
									<# } #>
								</span>
								<span class="fgi-browse-col-button">
									<button type="button" class="button button-secondary"
													data-options='{"type": {{type.mime}}, "title":"<?php printf(__("Select a %s video", "foogallery"), "{{type.text}}") ?>", "button":"<?php _e("Select Video", "foogallery") ?>"}'
									><?php _e("Select", "foogallery") ?></button>
								</span>
							</span>
						</span>
					<# }) #>
					<span class="fgi-input-description">
						<?php _e("We recommend using .mp4 videos for the best cross browser compatibility however the more types you supply the better the compatibility becomes. If you already have the URL you can simply paste it into the appropriate input.", "foogallery") ?>
					</span>
					<span class="fgi-input-description">
						<?php _e("As you enter URLs above the below compatibility list will be updated giving you an idea of which browsers will be able to play your video.", "foogallery") ?>
					</span>
					<span class="fgi-video-compatibility"></span>
					<span class="fgi-input-description">
						<?php printf(__("For more information on compatibility please see the following links: %s, %s and %s", "foogallery"), $mp4, $webm, $ogg) ?>
					</span>
				</span>
			</label>
		</div>
	</div>

</script>
<script type="text/html" id="tmpl-fgi-compatibility">
	<# _.each(data, function(result, device){ #>
		<span class="fgi-compat-device fgi-{{device}}" title="{{result.title}}">
			<# _.each(result.browsers, function(current, browser){ #>
				<# if (current.value === 0){ #>
					<span class="fgi-compat-browser fgi-{{device}} fgi-{{browser}} fgi-partial" title="{{current.title}}"></span>
				<# } else if (current.value === 1){ #>
					<span class="fgi-compat-browser fgi-{{device}} fgi-{{browser}} fgi-supported" title="{{current.title}}"></span>
				<# } else { #>
					<span class="fgi-compat-browser fgi-{{device}} fgi-{{browser}} fgi-not-supported" title="{{current.title}}"></span>
				<# } #>
			<# }) #>
		</span>
	<# }) #>
</script>
<script type="text/html" id="tmpl-fgi-query-result">

	<div class="fgi-query-result-notification"></div>
	<ul class="fgi-query-result-list"></ul>
	<div class="fgi-query-result-status"></div>

</script>
<script type="text/html" id="tmpl-fgi-query-result-notification">

	<# if (data.total === 1){ #>
		<p><?php _e("Confirm the video is correct and then click the Import button.", "foogallery") ?></p>
	<# } else { #>
		<p><?php _e("Select the videos to import by clicking the thumbnails and then click the Import button.", "foogallery") ?></p>
	<# } #>

</script>
<script type="text/html" id="tmpl-fgi-query-result-items">

	<# _(data.videos).each(function(video){ #>
		<# if (data.total === 1){ #>
			<li class="mode-selected mode-current" data-id="{{video.id}}">
		<# } else { #>
			<li data-id="{{video.id}}">
		<# } #>
				<div class="fgi-query-result-video">
					<div class="fgi-query-result-video-thumbnail" style="background-image: url('{{video.thumbnail}}')">
						<button type="button" class="fgi-query-result-video-check"></button>
					</div>
					<h2><a href="{{video.url}}" target="_blank">{{{video.title}}}</a></h2>
					<pre>{{{video.description}}}</pre>
				</div>
		</li>
	<# }) #>

</script>
<script type="text/html" id="tmpl-fgi-query-result-status">

<?php
	$offset = '<span class="fgi-query-result-offset">{{data.offset}}</span>';
	$total = '<span class="fgi-query-result-total">{{data.total}}</span>';
	$load_more = sprintf('<a href="#load-more">%s</a>', __("load more", "foogallery"));
	$try_again = sprintf('<a href="#try-again">%s</a>', __("try again", "foogallery"));
?>

	<p>
		<?php printf(__("Displaying %s of %s results.", "foogallery"), $offset, $total) ?>
		<# if (data.total !== 1 && data.nextPage !== 0){ #>
			<span class="fgi-query-result-paged">
				<span class="fgi-query-result-loading"><?php _e("Loading...", "foogallery") ?></span>
				<span class="fgi-query-result-load-more"><?php printf(__("Would you like to %s?", "foogallery"), $load_more) ?></span>
				<span class="fgi-query-result-try-again"><?php printf(__("An error occurred loading additional results, %s?", "foogallery"), $try_again) ?></span>
			</span>
		<# } #>
	</p>

</script>
<script type="text/html" id="tmpl-fgi-album-notification">

<?php
	$import_album = sprintf('<a href="#import-album">%s</a>', __("import the entire showcase", "foogallery"));
?>

	<# if (data.total === 1){ #>
		<p><?php _e("Confirm the video is correct and then click the Import button.", "foogallery") ?></p>
	<# } else { #>
		<p><?php printf(__("Select the videos to import by clicking the thumbnails and then click the Import button, or would you like to %s?", "foogallery"), $import_album) ?></p>
	<# } #>

</script>
<script type="text/html" id="tmpl-fgi-channel-notification">

<?php
	$import_channel = sprintf('<a href="#import-channel">%s</a>', __("import the entire channel", "foogallery"));
?>

	<# if (data.total === 1){ #>
		<p><?php _e("Confirm the video is correct and then click the Import button.", "foogallery") ?></p>
	<# } else { #>
		<p><?php printf(__("Select the videos to import by clicking the thumbnails and then click the Import button, or would you like to %s?", "foogallery"), $import_channel) ?></p>
	<# } #>

</script>
<script type="text/html" id="tmpl-fgi-playlist-notification">

<?php
	$import_playlist = sprintf('<a href="#import-playlist">%s</a>', __("import the entire playlist", "foogallery"));
?>

	<# if (data.total === 1){ #>
		<p><?php _e("Confirm the video is correct and then click the Import button.", "foogallery") ?></p>
	<# } else { #>
		<p><?php printf(__("Select the videos to import by clicking the thumbnails and then click the Import button, or would you like to %s?", "foogallery"), $import_playlist) ?></p>
	<# } #>

</script>
<script type="text/html" id="tmpl-fgi-user-notification">

<?php
	$import_user = sprintf('<a href="#import-user">%s</a>', __("import all videos for the user", "foogallery"));
?>

	<# if (data.total === 1){ #>
		<p><?php _e("Confirm the video is correct and then click the Import button.", "foogallery") ?></p>
	<# } else { #>
		<p><?php printf(__("Select the videos to import by clicking the thumbnails and then click the Import button, or would you like to %s?", "foogallery"), $import_user) ?></p>
	<# } #>

</script>
<script type="text/html" id="tmpl-fgi-oembed">

	<div class="fgi-oembed-notification">
		<p><?php _e("Enter any missing details for the oEmbed video below and then click the Import button.", "foogallery") ?></p>
	</div>
	<div class="fgi-oembed-details">
		<div class="fgi-form">
			<input type="hidden" name="id" value="{{data.id}}"/>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("URL", "foogallery") ?></span>
				<span class="fgi-col-input">
					<input type="text" name="url" value="{{data.url}}" readonly="readonly"/>
				</span>
			</label>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("Provider", "foogallery") ?></span>
				<span class="fgi-col-input">
					<input type="text" name="provider" value="{{data.provider}}" readonly="readonly"/>
					<span class="fgi-input-description">
						<?php _e("The oEmbed standards do not specify that a provider must supply a thumbnail, title or description but to display a video within a gallery it must have an image to use as a thumbnail. The inputs below allow you to edit or supply any missing information and then continue with the import.", "foogallery") ?>
					</span>
				</span>
			</label>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("Thumbnail", "foogallery") ?></span>
				<span class="fgi-col-input">
					<span class="fgi-browse">
						<span class="fgi-browse-inner">
							<span class="fgi-browse-col-input">
								<input type="text" name="thumbnail" value="{{data.thumbnail}}" spellcheck="false"
											 data-messages='{"required":"<?php _e("You must supply a thumbnail for the video.", "foogallery") ?>","pattern":"<?php _e("Please enter a .jpg, .jpeg, .png or .gif file.", "foogallery") ?>"}'
											 data-pattern="(?:\/|=)(?<name>[^\/]+?)\.(?<ext>jpg|jpeg|png|gif)(?:$|\?|&|#|,)" data-required="true"/>
							</span>
							<span class="fgi-browse-col-button">
								<button type="button" class="button button-secondary"
												data-options='{"type": "image/png,image/jpg,image/jpeg,image/gif", "title":"<?php _e("Select a thumbnail for the video", "foogallery") ?>", "button":"<?php _e("Select Image", "foogallery") ?>"}'
								><?php _e("Select", "foogallery") ?></button>
							</span>
						</span>
					</span>
				</span>
			</label>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("Title", "foogallery") ?></span>
				<span class="fgi-col-input">
					<input type="text" name="title" value="{{data.title}}"/>
				</span>
			</label>
			<label class="fgi-row">
				<span class="fgi-col-label"><?php _e("Description", "foogallery") ?></span>
				<span class="fgi-col-input">
					<textarea name="description" rows="5">{{data.description}}</textarea>
				</span>
			</label>
		</div>
	</div>

</script>
<script type="text/html" id="tmpl-fgi-vimeo">

	<?php
	$help = sprintf('<a href="#toggle-help">%s</a>', __("help", "foogallery"));
	?>

	<div class="fgi-splash fgi-vimeo-access-token">
		<h2><?php _e("Vimeo API access token is required.", "foogallery") ?></h2>
		<p><?php _e("Create a new app on Vimeo to generate an access token.", "foogallery") ?></p>
		<div class="button-hero-container">
			<input type="text" name="access_token" value="{{data.access_token}}" spellcheck="false" placeholder="<?php _e("Your access token", "foogallery") ?>"/>
			<button type="button" class="button fgi-save" disabled="disabled"><?php _e("Save", "foogallery") ?></button>
			<p class="fgi-vimeo-error-message"></p>
		</div>
		<p><small><?php printf(__("See the %s for more information.", "foogallery"), $help) ?></small></p>
	</div>

</script>
<script type="text/html" id="tmpl-fgi-youtube">

	<?php
	$help = sprintf('<a href="#toggle-help">%s</a>', __("help", "foogallery"));
	$developers_console = sprintf('<a href="https://console.developers.google.com/" target="_blank">%s</a>', __("Google Developers Console", "foogallery"));
	?>

	<div class="fgi-splash fgi-youtube-api-key">
		<h2><?php _e("YouTube API key is required.", "foogallery") ?></h2>
		<p><?php printf(__("Create a new project in your %s to generate an api key.", "foogallery"), $developers_console) ?></p>
		<div class="button-hero-container">
			<input type="text" name="api_key" value="{{data.api_key}}" spellcheck="false" placeholder="<?php _e("Your api key", "foogallery") ?>"/>
			<button type="button" class="button fgi-save" disabled="disabled"><?php _e("Save", "foogallery") ?></button>
			<p class="fgi-youtube-error-message"></p>
		</div>
		<p><small><?php printf(__("See the %s for more information.", "foogallery"), $help) ?></small></p>
	</div>

</script>
<script type="text/html" id="tmpl-fgi-import">
<?php
	$single = __("video", "foogallery");
	$plural = __("videos", "foogallery");
	$video = sprintf("{{data.total > 1 ? \"%s\" : \"%s\"}}", $plural, $single);
?>
	<div class="fgi-splash fgi-import-confirm">
		<h2><?php _e("Confirm multiple video import", "foogallery") ?></h2>
		<p><?php _e("Please note that depending on the source of the videos this process may take some time. If you like you can click the No button and change your selection.", "foogallery") ?></p>
		<p><?php printf(__("Are you sure you want to import %s videos?", "foogallery"), "{{data.total}}") ?></p>
		<div class="button-hero-container">
			<button type="button" class="button button-hero fgi-import-back"><?php _e("No - Change Selection", "foogallery") ?></button>
			<button type="button" class="button button-hero button-primary fgi-import-yes"><?php _e("Yes - Import Videos", "foogallery") ?></button>
		</div>
	</div>

	<div class="fgi-splash fgi-import-status">
		<h2><?php printf(__("Importing %s please wait...", "foogallery"), $video) ?></h2>
		<div class="fgi-import-progress">
			<div class="fgi-import-progress-value"></div>
			<div class="fgi-import-progress-text"></div>
		</div>
		<# if (data.total !== 1){ #>
			<p><?php _e("Cancelling the import will not remove any videos already imported into your Media Library, it simply stops the process as soon as possible.", "foogallery") ?></p>
			<div class="button-hero-container">
				<button type="button" class="button button-hero fgi-import-cancel"><?php _e("Cancel", "foogallery") ?></button>
			</div>
		<# } #>
	</div>

</script>
<script type="text/html" id="tmpl-fgi-import-result">

<?php
	$single = __("video", "foogallery");
	$plural = __("videos", "foogallery");
	$video_imported = sprintf("{{imported > 1 ? \"%s\" : \"%s\"}}", $plural, $single);
	$video_failed = sprintf("{{failed > 1 ? \"%s\" : \"%s\"}}", $plural, $single);
	$video_cancelled = sprintf("{{cancelled > 1 ? \"%s\" : \"%s\"}}", $plural, $single);
	$media_library = sprintf('<a href="#media-library">%s</a>', __("Media Library", "foogallery"));
	$single_1 = __("this video", "foogallery");
	$plural_1 = __("these videos", "foogallery");
	$video_try_again = sprintf("{{failed > 1 ? \"%s\" : \"%s\"}}", $plural_1, $single_1);
	$try_again_failed = sprintf('<a href="#try-again-failed">%s</a>', sprintf(__("try importing %s again", "foogallery"), $video_try_again));
	$toggle_failed_title = __("Toggle failed list", "foogallery");
	$toggle_failed_message = sprintf(__("failed to import. Would you like to %s?", "foogallery"), $try_again_failed);
	$toggle_failed = sprintf('<a href="#toggle-failed" title="%s">{{failed}} %s</a> %s', $toggle_failed_title, $video_failed, $toggle_failed_message);
	$video_cancelled_1 = sprintf("{{cancelled > 1 ? \"%s\" : \"%s\"}}", $plural_1, $single_1);
	$import_cancelled = sprintf('<a href="#import-cancelled">%s</a>', sprintf(__("import %s", "foogallery"), $video_cancelled_1));
	$toggle_cancelled_title = __("Toggle cancelled list", "foogallery");
	$toggle_cancelled_message = sprintf(__("were cancelled. Would you like to %s?", "foogallery"), $import_cancelled);
	$toggle_cancelled = sprintf('<a href="#toggle-cancelled" title="%s">{{cancelled}} %s</a> %s', $toggle_cancelled_title, $video_cancelled, $toggle_cancelled_message);
?>
	<div class="fgi-splash fgi-import-notification">

		<# if (data.imported.length){ var imported = data.imported.length; #>
			<h2><?php printf(__("Successfully imported %s %s", "foogallery"), "{{imported}}", $video_imported) ?></h2>
			<# if (imported !== 1){ #>
				<p><?php printf(__("All imported videos have been added to the current %s selection.", "foogallery"), $media_library) ?></p>
			<# } else { #>
				<p><?php printf(__("The imported video has been added to the current %s selection.", "foogallery"), $media_library) ?></p>
			<# } #>
		<# } else { #>
			<h2><?php _e("No videos were imported.", "foogallery") ?></h2>
		<# } #>

		<# if (data.failed.length){ var failed = data.failed.length; #>
			<p><?php echo $toggle_failed ?></p>
			<ul class="fgi-import-failed">
				<# _.each(data.failed, function(video, i){ #>
					<# if (video.urls){ #>
						<li><span>{{video.title}}</span> - <small>{{data.errors[i]}}</small></li>
					<# } else { #>
						<li><a href="{{video.url}}" target="_blank">{{video.title}}</a> - <small>{{data.errors[i]}}</small></li>
					<# } #>
				<# }) #>
			</ul>
		<# } #>

		<# if (data.cancelled.length){ var cancelled = data.cancelled.length; #>
			<p><?php echo $toggle_cancelled ?></p>
			<ul class="fgi-import-cancelled">
				<# _.each(data.cancelled, function(video){ #>
					<# if (video.urls){ #>
						<li><span>{{video.title}}</span></li>
					<# } else { #>
						<li><a href="{{video.url}}" target="_blank">{{video.title}}</a></li>
					<# } #>
				<# }) #>
			</ul>
		<# } #>

		<div class="button-hero-container">
			<button type="button" class="button button-hero button-secondary fgi-import-more-videos"><?php _e("Import More Videos", "foogallery") ?></button>
			<button type="button" class="button button-hero button-primary fgi-import-add-videos"><?php _e("Add Videos to Gallery", "foogallery") ?></button>
		</div>
	</div>

</script>