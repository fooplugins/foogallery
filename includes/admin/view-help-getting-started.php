<?php
$migrator_install_url = wp_nonce_url(
    add_query_arg(
        array(
            'action' => 'install-plugin',
            'plugin' => 'foogallery-migrate'
        ),
        admin_url( 'update.php' )
    ),
    'install-plugin_foogallery-migrate'
);

$migrator_link = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://wordpress.org/plugins/foogallery-migrate/', __( 'Find out more!', 'foogallery' ) );
$migrator_admin_url = foogallery_admin_url_for_page( 'foogallery-migrate' );
$demos_created = foogallery_get_setting( 'demo_content' ) === 'on';

?>
<div id="help_section" class="foogallery-admin-help-section">
	<section class="fgah-feature">
		<header>
			<h3><?php printf( esc_html__(  'Thank you for choosing %s!', 'foogallery' ), esc_html( foogallery_plugin_name() ) );?></h3>
			<p><?php esc_html_e( 'Better galleries for WordPress, that are faster, more flexible and beautiful!', 'foogallery' ); ?></p>
            <a href="#create" class="foogallery-admin-help-button"><?php esc_html_e( 'Getting Started', 'foogallery' ); ?></a>
            <a href="#demo-content" class="foogallery-admin-help-button"><?php esc_html_e( 'Create Demos', 'foogallery' ); ?></a>
            <a href="#migrator" class="foogallery-admin-help-button"><?php esc_html_e( 'Migrate', 'foogallery' ); ?></a>
            <a target="_blank" class="foogallery-admin-help-button foogallery-admin-help-button-active" href="<?php echo esc_url ( $plugin_url ); ?>"><?php echo esc_html__( 'Visit our Homepage', 'foogallery' ); ?><i class="dashicons dashicons-external"></i></a>
		</header>
	</section>

    <section class="fgah-feature" id="migrator">
        <header>
            <h3><?php esc_html_e(  'Are you migrating from another gallery plugin?', 'foogallery' ); ?></h3>
            <p>
                <?php printf( esc_html__( 'We have built a separate migration tool to help you seamlessly migrate from other gallery plugins to %s.', 'foogallery' ), esc_html( foogallery_plugin_name() ) ); ?>
                <?php echo wp_kses_post( $migrator_link ); ?>
            </p>
        </header>
        <footer>
            <?php if ( class_exists( 'FooPlugins\FooGalleryMigrate\Init' ) ) { ?>
                <a class="foogallery-admin-help-button-cta" target="_blank" href="<?php echo esc_url ( $migrator_admin_url ); ?>"><?php echo esc_html( sprintf( esc_html__( 'Run the migrator!', 'foogallery' ), esc_html( $plugin_name ) ) ); ?></a>
            <?php } else { ?>
                <a class="foogallery-admin-help-button-cta" target="_blank" href="<?php echo esc_url ( $migrator_install_url ); ?>"><?php echo esc_html( sprintf( esc_html__( 'Install our migrator!', 'foogallery' ), esc_html( $plugin_name ) ) ); ?></a>
            <?php } ?>
        </footer>
    </section>

    <section class="fgah-feature foogallery-admin-help-create-demos" id="demo-content">
        <header class="fgah-create-demos">
            <h3><?php esc_html_e( 'Demo Galleries', 'foogallery' );?></h3>
            <p><?php esc_html_e( 'It\'s always best to see what is possible by looking at the real thing. If you want to get started really quickly without any hassle, then we can import some demo galleries for you. This will create a number of pre-defined galleries which you can easily edit and make your own.', 'foogallery' ); ?></p>
        </header>
        <footer class="fgah-create-demos">
            <?php if ( $demos_created ) { ?>
                <a class="foogallery-admin-help-button-cta" href="<?php echo esc_attr( foogallery_admin_gallery_listing_url() ); ?>"><?php esc_html_e( 'View Galleries', 'foogallery' ); ?></a>
            <?php } ?>

            <button class="foogallery-admin-help-button-cta foogallery-admin-help-import-demos"
                    data-action="foogallery_admin_import_demos"
                    data-working="<?php esc_html_e( 'Please wait...', 'foogallery' ); ?>"
                    data-complete="<?php esc_html_e( 'Done!', 'foogallery' ); ?>"
                    data-error="<?php esc_html_e( 'Error!', 'foogallery' ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_admin_import_demos' ) ); ?>">
	            <span class="progress"></span>
	            <span class="fgah-create-demos-text" style="position: relative;"><?php esc_html_e( 'Create Demos *', 'foogallery' ); ?></span>
            </button>

            <?php if ( foogallery_is_pro() ) : ?>
                <button class="foogallery-admin-help-button-cta foogallery-admin-help-import-demos"
                        data-action="foogallery_admin_import_pro_demos"
                        data-working="<?php esc_html_e( 'Please wait...', 'foogallery' ); ?>"
                        data-complete="<?php esc_html_e( 'Done!', 'foogallery' ); ?>"
                        data-error="<?php esc_html_e( 'Error!', 'foogallery' ); ?>"
                        data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_admin_import_pro_demos' ) ); ?>">
                    <span class="progress"></span>
                    <span class="fgah-create-demos-text" style="position: relative;"><?php esc_html_e( 'Create PRO Demos *', 'foogallery' ); ?></span>
                </button>
            <?php endif; ?>

            <small class="fgah-demo-result"><?php esc_html_e( '* Sample images will be imported into your media library', 'foogallery' ); ?></small>
        </footer>
    </section>

	<section class="fgah-feature" id="create">
        <header>
            <h3><?php esc_html_e( 'Getting Started : Create Your First Gallery', 'foogallery' );?></h3>
            <p><?php esc_html_e( 'It couldn\'t be any easier:', 'foogallery' ); ?></p>
        </header>
        <div>
            <figure>
                <img width="650" height="552" src="<?php echo esc_url( 'https://assets.fooplugins.com/foogallery/plugin/admin/help-getting-started.jpg' ); ?>" alt="Create a gallery" />
            </figure>
            <ol>
                <li><?php esc_html_e( 'Enter a gallery title', 'foogallery' );?></li>
                <li><?php esc_html_e( 'Choose a gallery layout', 'foogallery' );?></li>
                <li><?php esc_html_e( 'Add images to your gallery', 'foogallery' );?></li>
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