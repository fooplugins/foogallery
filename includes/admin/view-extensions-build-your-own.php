<?php

$link_actions_filters = 'http://foo.gallery/developers#actions_filters';
$link_tutorial = 'http://foo.gallery/developers#extensions';
$link_submit = 'http://foo.gallery/submit-extension/';

$nonce = safe_get_from_request( 'foogallery_boilerplate_nonce' );

if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'foogallery_boilerplate' ) ) {

	$boilerplate_type = $_POST['boilerplate_type'];
	$boilerplate_name = $_POST['boilerplate_name'];
	$boilerplate_desc = $_POST['boilerplate_desc'];
	$boilerplate_author = $_POST['boilerplate_author'];
	$boilerplate_author_link = $_POST['boilerplate_author_link'];
	$boilerplate_error = '';

	if ( empty( $boilerplate_type ) ||
		empty( $boilerplate_name ) ||
		empty( $boilerplate_desc ) ||
		empty( $boilerplate_author ) ||
		empty( $boilerplate_author_link ) ) {
		$boilerplate_error = __( 'Please fill in all form fields!', 'foogallery' );
	} else {

	}

} else {
	$current_user = wp_get_current_user();
	$boilerplate_name = __( 'Cool Thing', 'foogallery' );
	$boilerplate_type = 'template';
	$boilerplate_desc = __( 'A cool description about what your cool thing can do', 'foogallery' );
	$boilerplate_author = $current_user->user_firstname . ' ' . $current_user->user_lastname;
	$boilerplate_author_link = site_url();
}
?>
<style>
	.build_your_own_main_content {
		width:600px;
		float:left;
		margin-right:50px;
		clear: both;
	}

	.build_your_own_sidebar {
		width:400px;
		float:left;
	}

	.extension-page-build_your_own form {
		width:100%;
		margin-top:10px;
		border: solid 3px #aaa;
		border-radius: 5px;
		padding:0;
		background: #ddd;
	}

	.extension-page-build_your_own form h2 {
		margin: 0;
		background: #aaa;
		color: #fff;
		padding: 10px;
	}

	.extension-page-build_your_own form div {
		padding: 10px;
	}

	.extension-page-build_your_own form label{
		margin-top: 5px;
		display: block;
		font-weight: bold;
	}
	.extension-page-build_your_own form .button-row input {
		margin-right: 5px;
	}

	.extension-page-build_your_own form textarea {
		height: 50px;
	}

	.extension-page-build_your_own form input[type="text"],
	.extension-page-build_your_own form textarea,
	.extension-page-build_your_own form select {
		width: 100%;
	}

	.extension-page-build_your_own form .boilerplate-error {
		color: #800;
	}

</style>
<div class="build_your_own_main_content">
	<h1><?php _e( 'Build Your Own FooGallery Extensions!', 'foogallery' ); ?></h1>

	<p><?php _e( 'FooGallery was built with developers in mind. If you can build your own WordPress plugin, then you will have no problem building your own FooGallery extension.', 'foogallery' ); ?></p>

	<h2><?php _e( 'Extension Ideas', 'foogallery' ); ?></h2>
	<ul class="ul-disc">
		<li><?php _e( 'Build your own unique Gallery Template.', 'foogallery' ); ?></li>
		<li><?php _e( 'Adding support for your favourite lightbox.', 'foogallery' ); ?></li>
		<li><?php _e( 'Why not white-label FooGallery for your clients?', 'foogallery' ); ?></li>
		<li><?php _e( 'Add your own options to the settings page?', 'foogallery' ); ?></li>
	</ul>
	<p><strong><?php _e( 'There is no limit to the number of ways you can change or alter FooGallery!', 'foogallery' ); ?></strong></p>

	<h2><?php _e( 'Developer Tips', 'foogallery' ); ?></h2>
	<ul class="ul-disc">
		<li><?php _e( 'An extension is essentially a WordPress plugin.', 'foogallery' ); ?></li>
		<li><?php _e( 'Extension functionality must be wrapped in a PHP class. (This class is included when the extension is activated)', 'foogallery' ); ?></li>
		<li><?php printf( __( 'There are several dozen actions and filters built in for you. (See all %s)', 'foogallery' ), '<a href="' . esc_url( $link_actions_filters ) . '" target="_blank">' . __( 'FooGallery actions and filters', 'foogallery' ) . '</a>' ); ?></li>
		<li><?php printf( __( 'Read our %s on how to build your own extension in 2	minutes.', 'foogallery' ), '<a href="' . esc_url( $link_tutorial ) . '" target="_blank">' . __( 'step-by-step tutorial', 'foogallery' ) . '</a>' ); ?></li>
	</ul>

	<h2><?php _e( 'Submit Your Extension', 'foogallery' ); ?></h2>

	<p><?php _e( 'Have you built your own extension that you are proud of? Do you want to share it with the community of FooGallery	users?', 'foogallery' ); ?></p>

	<p><?php printf( __( '%s to get it listed in our extension store.', 'foogallery' ), '<a href="' . esc_url( $link_submit ) . '" target="_blank">' . __( 'Follow these simple instructions', 'foogallery' ) . '</a>' ); ?></p>

	<h2><?php _e( 'Extension Boilerplates', 'foogallery' ); ?></h2>

	<p><?php _e( 'We really want to make it easy for you to get started and building your own extensions. Generate an extension boilerplate in seconds using the provided form.', 'foogallery' ); ?></p>

</div>
<div class="build_your_own_sidebar">
	<form method="post">
		<h2><?php _e( 'Extension Boilerplate Generator', 'foogallery' ); ?></h2>

		<div>
			<label><?php _e( 'Type Of Extension', 'foogallery' ); ?></label>
			<select name="boilerplate_type">
				<option <?php echo 'template' === $boilerplate_type ? 'selected="selected"' : ''; ?> value="template"><?php _e( 'Gallery Template', 'foogallery' ); ?></option>
				<option <?php echo 'lightbox' === $boilerplate_type ? 'selected="selected"' : ''; ?> value="lightbox"><?php _e( 'Lightbox', 'foogallery' ); ?></option>
			</select>
		</div>
		<div>
			<label><?php _e( 'Name', 'foogallery' ); ?></label>
			<input type="text" value="<?php echo $boilerplate_name; ?>" name="boilerplate_name"/>
		</div>
		<div>
			<label><?php _e( 'Description', 'foogallery' ); ?></label>
			<textarea name="boilerplate_desc"><?php echo $boilerplate_desc; ?></textarea>
		</div>
		<div>
			<label><?php _e( 'Author Name', 'foogallery' ); ?></label>
			<input type="text" value="<?php echo $boilerplate_author; ?>" name="boilerplate_author"/>
		</div>
		<div>
			<label><?php _e( 'Author URL', 'foogallery' ); ?></label>
			<input type="text" value="<?php echo $boilerplate_author_link; ?>" name="boilerplate_author_link"/>
		</div>
		<div class="button-row">
			<button name="action" class="button button-primary" value="download"><?php _e( 'Generate &amp; download .zip', 'foogallery' ); ?></button>
			<!--<button name="action" class="button button-primary" value="install">Generate &amp; install now!</button>-->
			<?php if ( ! empty( $boilerplate_error ) ) {
				echo "<p class=\"boilerplate-error\">{$boilerplate_error}</p>";
			} ?>
			<?php wp_nonce_field( 'foogallery_boilerplate', 'foogallery_boilerplate_nonce' ); ?>
			<p><?php _e( 'Once you have downloaded the zip file, install and activate it like a normal WordPress plugin so that it shows up in the list of extensions!', 'foogallery' ); ?></p>
		</div>
	</form>
</div>
