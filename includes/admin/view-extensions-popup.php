<script id="tmpl-extension-single" type="text/template">
	<div class="extension-backdrop"></div>
	<div class="extension-wrap">
		<div class="extension-header">
			<button class="left dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show previous extension' ); ?></span></button>
			<button class="right dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show next extension' ); ?></span></button>
			<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close overlay' ); ?></span></button>
		</div>
		<div class="extension-about">
			<div class="extension-screenshots">
				<# if ( data.screenshot[0] ) { #>
					<div class="screenshot"><img src="{{ data.screenshot[0] }}" alt="" /></div>
					<# } else { #>
						<div class="screenshot blank"></div>
						<# } #>
			</div>

			<div class="extension-info">
				<# if ( data.active ) { #>
					<span class="current-label"><?php _e( 'Current extension' ); ?></span>
					<# } #>
						<h3 class="extension-name">{{{ data.name }}}<span class="extension-version"><?php printf( __( 'Version: %s' ), '{{{ data.version }}}' ); ?></span></h3>
						<h4 class="extension-author"><?php printf( __( 'By %s' ), '{{{ data.authorAndUri }}}' ); ?></h4>

						<# if ( data.hasUpdate ) { #>
							<div class="extension-update-message">
								<h4 class="extension-update"><?php _e( 'Update Available' ); ?></h4>
								{{{ data.update }}}
							</div>
							<# } #>
								<p class="extension-description">{{{ data.description }}}</p>

								<# if ( data.parent ) { #>
									<p class="parent-extension"><?php printf( __( 'This is a child extension of %s.' ), '<strong>{{{ data.parent }}}</strong>' ); ?></p>
									<# } #>

										<# if ( data.tags ) { #>
											<p class="extension-tags"><span><?php _e( 'Tags:' ); ?></span> {{{ data.tags }}}</p>
											<# } #>
			</div>
		</div>

		<div class="extension-actions">
			<div class="active-extension">
				<a href="{{{ data.actions.customize }}}" class="button button-primary customize load-customize hide-if-no-customize"><?php _e( 'Customize' ); ?></a>
				<?php echo implode( ' ', $current_extension_actions ); ?>
			</div>
			<div class="inactive-extension">
				<# if ( data.actions.activate ) { #>
					<a href="{{{ data.actions.activate }}}" class="button button-primary activate"><?php _e( 'Activate' ); ?></a>
					<# } #>
						<a href="{{{ data.actions.customize }}}" class="button button-secondary load-customize hide-if-no-customize"><?php _e( 'Live Preview' ); ?></a>
						<a href="{{{ data.actions.preview }}}" class="button button-secondary hide-if-customize"><?php _e( 'Preview' ); ?></a>
			</div>

			<# if ( ! data.active && data.actions['delete'] ) { #>
				<a href="{{{ data.actions['delete'] }}}" class="button button-secondary delete-extension"><?php _e( 'Delete' ); ?></a>
				<# } #>
		</div>
	</div>
</script>
