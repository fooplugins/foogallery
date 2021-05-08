<?php
/*
Default settings page used by Foo_Plugin_Base
*/
global $wp_version, $wp_settings_sections, $wp_settings_fields;

//need to make sure are included correctly
if ( !isset($this) || !is_subclass_of( $this, 'Foo_Plugin_Base_v2_4' ) ) {
	throw new Exception("This settings view has not been included correctly!");
}

$tabs = $this->settings()->get_tabs();
$plugin_info = $this->get_plugin_info();
$plugin_slug = $plugin_info['slug'];
$summary = $this->apply_filters( $plugin_slug . '_admin_settings_page_summary', '' );

?>
<div class="wrap" id="<?php echo $plugin_slug; ?>-settings">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<?php
        //only show the settings messages if less than WP3.5
        if (version_compare($wp_version, '3.5') < 0) {
            settings_errors();
        }

	if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$plugin_slug]) ) {
            return;
	}

		if ( !empty($summary) ) {
			echo esc_html( $summary );
		}
    ?>
	<div id="<?php echo $plugin_slug; ?>-settings-wrapper">
		<div id="<?php echo $plugin_slug; ?>-settings-main">
	<form action="options.php" method="post">
		<?php settings_fields($plugin_slug); ?>
                <?php
                if (!empty($tabs)) {
                    //we have tabs - woot!
                ?>
                <div style="float:left;height:16px;width:16px;"><!-- spacer for tabs --></div>
                <h2 class="foo-nav-tabs nav-tab-wrapper">
                <?php
                    //loop through the tabs to render the actual tabs at the top
                    $first = true;
                    foreach ($tabs as $tab) {
                        $class = $first ? "nav-tab nav-tab-active" : "nav-tab";
                        echo "<a href='#{$tab['id']}' class='$class'>{$tab['title']}</a>";
							if ( $first ) {
								$first = false;
							}
                    }
                ?>
                </h2>
                <?php
                    //now loop through the tabs to render the content containers
                    $first = true;
                    foreach ($tabs as $tab) {
                        $style = $first ? "" : "style='display:none'";

                        echo "<div class='nav-container' id='{$tab['id']}_tab' $style>";

						foreach ( (array) $wp_settings_sections[$plugin_slug] as $section ) {
							if (in_array($section['id'], $tab['sections'])) {
								echo "<h3>{$section['title']}</h3>\n";
								call_user_func($section['callback'], $section);
								if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$plugin_slug]) || !isset($wp_settings_fields[$plugin_slug][$section['id']]) ) {
									continue;
								}
								echo '<table class="form-table">';
								do_settings_fields($plugin_slug, $section['id']);
								echo '</table>';
							}
						}

                        echo "</div>";
						if ( $first ) {
							$first = false;
						}
                    }
                ?>
                <?php
                } else {
                    //no tabs so just render the sections
                    do_settings_sections($plugin_slug);
                }
                ?>
		<p class="submit">
					<input name="submit" class="button-primary" type="submit"
						   value="<?php _e( 'Save Changes', $plugin_slug ); ?>"/>
					<input name="<?php echo $plugin_slug; ?>[reset-defaults]"
						   onclick="return confirm('<?php _e( 'Are you sure you want to restore all settings back to their default values?', $plugin_slug ); ?>');"
						   class="button-secondary" type="submit"
						   value="<?php _e( 'Restore Defaults', $plugin_slug ); ?>"/>
			<?php do_action($plugin_slug . '_admin_settings_buttons') ?>
		</p>
	</form>
		</div>
		<div id="<?php echo $plugin_slug; ?>-settings-sidebar" class="postbox-container">
			<?php do_action($plugin_slug . '_admin_settings_sidebar'); ?>
		</div>
	</div>
</div>
