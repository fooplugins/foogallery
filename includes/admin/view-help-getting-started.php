<?php
$migrator_url = wp_nonce_url(
    add_query_arg(
        array(
            'action' => 'install-plugin',
            'plugin' => 'foogallery-migrate'
        ),
        admin_url( 'update.php' )
    ),
    'install-plugin_foogallery-migrate'
);

$migrator_link = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://wordpress.org/plugins/foogallery-migrate/', esc_html__( 'Find out more!', 'foogallery' ) );

?>
<div id="help_section" class="foogallery-admin-help-section">
	<section class="fgah-feature">
		<header>
        <h3><?php printf( esc_html__( 'Thank you for choosing %s!', 'foogallery' ), esc_html( foogallery_plugin_name() ) ); ?></h3>
        <p><?php esc_html_e( 'Better galleries for WordPress, that are faster, more flexible and beautiful!', 'foogallery' ); ?></p>
		</header>
		<footer>
			<a class="foogallery-admin-help-button-cta" target="_blank" href="<?php echo esc_url ( $plugin_url ); ?>"><?php echo sprintf( esc_html__( 'Visit the %s Homepage', 'foogallery' ), esc_html( $plugin_name ) ); ?></a>
		</footer>
	</section>

    <section class="fgah-feature">
        <header>
            <h3><?php esc_html_e(  'Are you migrating from another gallery plugin?', 'foogallery' ); ?></h3>
            <p>
                <?php
                printf(
                    esc_html__( 'We have built a separate migration tool to help you seamlessly migrate from other gallery plugins to %s.', 'foogallery' ),
                    esc_html( foogallery_plugin_name() )
                );
                ?>
                <?php echo wp_kses_post( $migrator_link ); ?>
            </p>
        </header>
        <footer>
            <a class="foogallery-admin-help-button-cta" target="_blank" href="<?php echo esc_url ( $migrator_url ); ?>"><?php echo sprintf( esc_html__( 'Install our migrator!', 'foogallery' ), esc_html( $plugin_name ) ); ?></a>
        </footer>
    </section>

    <section class="fgah-feature foogallery-admin-help-create-demos">
        <header class="fgah-create-demos">
            <h3><?php esc_html_e( 'Demo Galleries', 'foogallery' );?></h3>
            <p><?php esc_html_e( 'It\'s always best to see what is possible by looking at the real thing. If you want to get started really quickly without any hassle, then we can import some demo galleries for you. This will create a number of pre-defined galleries which you can easily edit and make your own.', 'foogallery' ); ?></p>
        </header>
        <footer class="fgah-create-demos">
            <button class="foogallery-admin-help-button-cta foogallery-admin-help-import-demos"
                    data-working="<?php esc_html_e( 'Please wait...', 'foogallery' ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_admin_import_demos' ) ); ?>">
	            <span class="progress"></span>
	            <span class="fgah-create-demos-text" style="position: relative;"><?php esc_html_e( 'Create Demo Galleries *', 'foogallery' ); ?></span>
            </button>

            <small><?php esc_html_e( '* Sample images will be imported into your media library', 'foogallery' ); ?></small>
        </footer>

        <header class="fgah-created-demos">
            <h3><?php esc_html_e( 'Demo Galleries', 'foogallery' );?></h3>
            <p><?php esc_html_e( 'We have created a number of pre-defined galleries which you can easily edit to test out all the plugin features.', 'foogallery' ); ?></p>
        </header>
        <footer class="fgah-created-demos">
            <a class="foogallery-admin-help-button-cta" href="<?php echo esc_attr( foogallery_admin_gallery_listing_url() ); ?>"><?php esc_html_e( 'View Galleries', 'foogallery' ); ?></a>
	        <small class="fgah-demo-result"></small>
        </footer>
    </section>

	<section class="fgah-feature">
        <header>
            <h3><?php esc_html_e( 'Create Your First Gallery', 'foogallery' );?></h3>
            <p><?php esc_html_e( 'It couldn\'t be any easier:', 'foogallery' ); ?></p>
        </header>
        <div>
            <figure>
                <img width="650" height="552" src="<?php echo esc_url( 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-create.png' ); ?>" alt="Create a gallery" />
            </figure>
            <ol>
                <li><?php esc_html_e( 'Enter a title', 'foogallery' );?></li>
                <li><?php esc_html_e( 'Add images to your gallery', 'foogallery' );?></li>
                <li><?php esc_html_e( 'Choose a gallery layout', 'foogallery' );?></li>
                <li><?php esc_html_e( 'Customize settings', 'foogallery' );?></li>
                <li><?php esc_html_e( 'Publish!', 'foogallery' );?></li>
            </ol>
        </div>
        <footer>
            <a class="foogallery-admin-help-button-cta" target="_blank" href="<?php echo esc_url ( foogallery_admin_add_gallery_url() ); ?>"><?php esc_html_e( 'Add a Gallery Now!', 'foogallery' ); ?></a>
        </footer>
	</section>

    <section class="fgah-feature fgah-feature-right">
        <header>
            <h3><?php esc_html_e( 'Show Off Your Gallery', 'foogallery' );?></h3>
            <p><?php esc_html_e( 'Once created, easily embed your gallery on any page or post:', 'foogallery' ); ?></p>
        </header>
        <div>
            <figure>
                <img width="556" height="407" src="<?php echo esc_url( 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-embed.png' ); ?>" alt="Show off your gallery" />
            </figure>
            <dl>
                <dt><?php printf(  esc_html__( '%s Block','foogallery' ), esc_html( foogallery_plugin_name() ) ); ?></dt>
                <dd><?php esc_html_e( 'Use our block to embed a gallery in the Gutenberg editor. Live previews help you visualize how the gallery will really look on the frontend.', 'foogallery' );?></dd>
                <dt><?php printf( esc_html__( 'The <code>[%s]</code> Shortcode','foogallery' ), esc_html( foogallery_gallery_shortcode_tag() ) );?></dt>
                <dd><?php esc_html_e( 'Copy and paste the gallery shortcode into any page. You can find the shortcode from the gallery listing or within the Gallery Shortcode metabox when you edit a gallery.', 'foogallery' );?></dd>
            </dl>
        </div>
    </section>
</div>