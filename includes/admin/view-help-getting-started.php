<div id="help_section" class="foogallery-admin-help-section">
	<div class="foogallery-admin-help-section-feature foogallery-admin-help-centered foogallery-admin-help-create-demos">
		<h2><?php _e( 'Create Demo Galleries', 'foogallery' );?></h2>
		<p><?php _e( 'It\'s always best to see what is possible by looking at the real thing. If you want to get started really quickly without any hassle, then we can import some demo galleries for you. This will create a number of pre-defined galleries which you can easily edit and make your own.', 'foogallery' );?></p>
		<a class="foogallery-admin-help-button-cta foogallery-admin-help-import-demos" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_admin_import_demos' ) ); ?>" href="#demo_content"><?php _e( 'Create Demo Galleries *', 'foogallery' ); ?></a>
		<p><?php _e( '* Please note : sample images will be imported into your media library', 'foogallery' ); ?></p>
		<br />
	</div>
	<div class="foogallery-admin-help-section-feature foogallery-admin-help-centered foogallery-admin-help-created-demos">
		<h2><?php _e( 'Your Galleries Are Ready!', 'foogallery' );?></h2>
		<p><?php _e( 'We have created a number of pre-defined galleries which you can easily edit to test out all the plugin features.', 'foogallery' );?></p>
		<a class="foogallery-admin-help-button-cta" href="<?php echo esc_attr( foogallery_admin_gallery_listing_url() ); ?>"><?php _e( 'View Galleries', 'foogallery' ); ?></a>
		<br />
	</div>

	<div class="foogallery-admin-help-section-feature">

		<div class="foogallery-admin-help-2-columns">
			<div class="foogallery-admin-help-column">
				<h2><?php _e( 'Create A Gallery', 'foogallery' );?></h2>
				<p><?php _e( 'It couldn\'t be any easier:', 'foogallery' ); ?></p>
				<h4><?php _e( '1. Enter a Title', 'foogallery' );?></h4>
				<h4><?php _e( '2. Add images to your gallery', 'foogallery' );?></h4>
				<h4><?php _e( '3. Choose a gallery layout', 'foogallery' );?></h4>
				<h4><?php _e( '4. Customize settings', 'foogallery' );?></h4>
				<h4><?php _e( '5. Publish!', 'foogallery' );?></h4>
				<br />
				<a class="foogallery-admin-help-button-cta target="_blank" href="<?php echo esc_url ( foogallery_admin_add_gallery_url() ); ?>"><?php _e( 'Add A Gallery Now!', 'foogallery' ); ?></a>
			</div>
			<div class="foogallery-admin-help-column">
				<img width="650" src="<?php echo esc_url( 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-create.png' ); ?>" />
			</div>
		</div>
	</div>

	<div class="foogallery-admin-help-section-feature">
		<div class="feature-section">

			<div class="foogallery-admin-help-2-columns">
				<div class="foogallery-admin-help-column">
					<h2><?php _e( 'Show Off Your Gallery', 'foogallery' );?></h2>
					<p><?php _e( 'Once created, easily embed your gallery on any page or post:', 'foogallery' );?></p>

					<h3><?php _e( 'FooGallery Block','foogallery' ); ?></h3>
					<p><?php _e( 'Use our block to embed a gallery in the Gutenberg editor. Live previews help you visualize how the gallery will really look on the frontend.', 'foogallery' );?></p>

					<h3><?php printf( __( 'The <em>[%s]</em> Short Code','foogallery' ), foogallery_gallery_shortcode_tag() );?></h3>
					<p><?php _e( 'Copy and paste the gallery shortcode into any page. You can find the shortcode from the gallery listing or within the Gallery Shortcode metabox when you edit a gallery.', 'foogallery' );?></p>
				</div>
				<div class="foogallery-admin-help-column">
					<img src="<?php echo esc_url( 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-embed.png' ); ?>" />
				</div>
			</div>
		</div>
	</div>
</div>