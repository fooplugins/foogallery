<?php
/*
Default settings page used by Foo_PluginBase
*/
global $wp_version, $settings_data, $wp_settings_sections, $wp_settings_fields;

$summary = $settings_data['settings_summary'];
$tabs = $settings_data['settings_tabs'];
$plugin_slug = $settings_data['plugin_info']['slug'];

?>
<div class="wrap" id="<?php echo $plugin_slug; ?>-settings">
	<div id="icon-options-general" class="icon32">
            <br />
	</div>

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<?php
        echo $summary;
        //only show the settings messages if less than WP3.5
        if (version_compare($wp_version, '3.5') < 0) {
            settings_errors();
        }

        if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$plugin_slug]) )
            return;

        print_r('BOB');
    ?>
	<form action="options.php" method="post">
		<?php settings_fields($plugin_slug); ?>
                <?php
                if (!empty($tabs)) {
                    //we have tabs - woot!
                ?>
                <div style="float:left;height:16px;width:16px;"><!-- spacer for tabs --></div>
                <h3 class="nav-tab-wrapper">
                <?php
                    //loop through the tabs to render the actual tabs at the top
                    $first = true;
                    foreach ($tabs as $tab) {
                        $class = $first ? "nav-tab nav-tab-active" : "nav-tab";
                        echo "<a href='#{$tab['id']}' class='$class'>{$tab['title']}</a>";
                        if ($first) { $first = false; }
                    }
                ?>
                </h3>
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
								if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$plugin_slug]) || !isset($wp_settings_fields[$plugin_slug][$section['id']]) )
									continue;
								echo '<table class="form-table">';
								do_settings_fields($plugin_slug, $section['id']);
								echo '</table>';
							}
						}

                        echo "</div>";
                        if ($first) { $first = false; }
                    }
                ?>
                <?php
                } else {
                    //no tabs so just render the sections
                    do_settings_sections($plugin_slug);
                }
                ?>
		<p class="submit">
			<input name="Submit" class="button-primary" type="submit" value="<?php _e( 'Save Changes', $plugin_slug); ?>" />
                        <input name="<?php echo $plugin_slug; ?>[reset-defaults]" onclick="return confirm('<?php _e( 'Are you sure you want to restore all settings back to their default values?', $plugin_slug); ?>');" class="button-secondary" type="submit" value="<?php _e( 'Restore Defaults', $plugin_slug); ?>" />
		</p>
	</form>
</div>
