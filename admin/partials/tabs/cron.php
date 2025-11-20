<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>
<div class="wrap">

		<?php 
settings_errors();
?>

    <form method="post" action="options.php" id="tabs">

            <?php 
settings_fields( 'ASI-plugin-cron-settings' );
$options = wp_parse_args( get_option( 'ASI_plugin_cron_settings' ), $this->ASI_default_options_cron_settings( FALSE ) );
?>
		<table id="general-options" class="form-table tabs-content">
			<tbody>
				<tr>
					<td colspan="3">
						<div class="alert alert-custom alert-default" role="alert">
							<div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl">
								<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
									<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
										<rect x="0" y="0" width="24" height="24"></rect>
										<path d="M12,22 C6.4771525,22 2,17.5228475 2,12 C2,6.4771525 6.4771525,2 12,2 C17.5228475,2 22,6.4771525 22,12 C22,17.5228475 17.5228475,22 12,22 Z M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"></path>
									</g>
								</svg>
							</span></div>
							<div class="alert-text">
								<?php _e( 'This option allows you to set crons. If enabled, the plugin will generate images at regular intervals.<br><br><strong>Note:</strong> The WordPress cron system is not the same as a server cron. Therefore, the run may not be as regular as defined in the options.', 'all-sources-images' ); ?>
							</div>
						</div>
					</td>
				</tr>

				<tr>
					<td>
						<?php esc_html_e( 'Enable cron', 'all-sources-images' ); ?>
					</td>
					<td>
						<label class="checkbox">
							<input data-switch="true" type="checkbox" name="ASI_plugin_cron_settings[enable_cron]" id="enable_cron" value="enable" <?php echo( !empty( $options['enable_cron']) && $options['enable_cron'] == 'enable' )? 'checked': ''; ?> />
						</label>
					</td>
					<td>
						<p class="description">
							<?php esc_html_e( 'Enable automatic scheduled image generation.', 'all-sources-images' ); ?>
						</p>
					</td>
				</tr>

				<tr class="cron-settings" <?php echo ( empty( $options['enable_cron']) || $options['enable_cron'] != 'enable' ) ? 'style="display:none;"' : ''; ?>>
					<td>
						<?php esc_html_e( 'Relevant post type', 'all-sources-images' ); ?>
					</td>
					<td class="checkbox-list">
						<?php
						$post_types = get_post_types( array('public' => true), 'objects' );
						$selected_post_types = isset($options['cron_post_types']) ? $options['cron_post_types'] : array('post', 'page');
						foreach ( $post_types as $post_type ) {
							if ( in_array( $post_type->name, array('attachment') ) ) continue;
							$checked = ( is_array($selected_post_types) && in_array($post_type->name, $selected_post_types) ) ? 'checked' : '';
							?>
							<label class="checkbox">
								<input type="checkbox" name="ASI_plugin_cron_settings[cron_post_types][]" value="<?php echo esc_attr($post_type->name); ?>" <?php echo $checked; ?>>
								<span></span> <?php echo esc_html($post_type->labels->name); ?>
							</label>
							<?php
						}
						?>
					</td>
					<td>
						<p class="description">
							<?php esc_html_e( 'The post types (articles, pages etc.) involved.', 'all-sources-images' ); ?>
						</p>
					</td>
				</tr>

				<tr class="cron-settings" <?php echo ( empty( $options['enable_cron']) || $options['enable_cron'] != 'enable' ) ? 'style="display:none;"' : ''; ?>>
					<td>
						<?php esc_html_e( 'Interval', 'all-sources-images' ); ?>
					</td>
					<td>
						<span><?php esc_html_e( 'Every', 'all-sources-images' ); ?></span>
						<input type="number" name="ASI_plugin_cron_settings[cron_interval_value]" class="form-control" style="width:80px; display:inline-block;" min="1" max="60" value="<?php echo isset($options['cron_interval_value']) ? esc_attr($options['cron_interval_value']) : '5'; ?>" />
						<select name="ASI_plugin_cron_settings[cron_interval_unit]" class="form-control" style="width:150px; display:inline-block;">
							<?php
							$interval_unit = isset($options['cron_interval_unit']) ? $options['cron_interval_unit'] : 'minutes';
							$units = array(
								'minutes' => __( 'Minutes', 'all-sources-images' ),
								'hours'   => __( 'Hours', 'all-sources-images' ),
								'days'    => __( 'Days', 'all-sources-images' ),
							);
							foreach ( $units as $unit_key => $unit_name ) {
								$selected = ( $interval_unit == $unit_key ) ? 'selected="selected"' : '';
								echo '<option ' . $selected . ' value="' . $unit_key . '">' . $unit_name . '</option>';
							}
							?>
						</select>
					</td>
					<td>
						<p class="description">
							<?php esc_html_e( 'Interval at which the cron will run.', 'all-sources-images' ); ?>
						</p>
					</td>
				</tr>

				<tr class="cron-settings" <?php echo ( empty( $options['enable_cron']) || $options['enable_cron'] != 'enable' ) ? 'style="display:none;"' : ''; ?>>
					<td>
						<?php esc_html_e( 'Posts date', 'all-sources-images' ); ?>
					</td>
					<td>
						<label class="radio">
							<input type="radio" name="ASI_plugin_cron_settings[posts_date_mode]" value="all" <?php echo ( !isset($options['posts_date_mode']) || $options['posts_date_mode'] == 'all' ) ? 'checked' : ''; ?>>
							<span></span> <?php esc_html_e( 'All Posts', 'all-sources-images' ); ?>
						</label>
						<br>
						<label class="radio">
							<input type="radio" name="ASI_plugin_cron_settings[posts_date_mode]" value="recent" <?php echo ( isset($options['posts_date_mode']) && $options['posts_date_mode'] == 'recent' ) ? 'checked' : ''; ?>>
							<span></span>
							<input type="number" name="ASI_plugin_cron_settings[posts_date_value]" class="form-control" style="width:80px; display:inline-block;" min="1" max="365" value="<?php echo isset($options['posts_date_value']) ? esc_attr($options['posts_date_value']) : '5'; ?>" />
							<select name="ASI_plugin_cron_settings[posts_date_unit]" class="form-control" style="width:120px; display:inline-block;">
								<?php
								$date_unit = isset($options['posts_date_unit']) ? $options['posts_date_unit'] : 'days';
								$date_units = array(
									'days'   => __( 'Days', 'all-sources-images' ),
									'weeks'  => __( 'Weeks', 'all-sources-images' ),
									'months' => __( 'Months', 'all-sources-images' ),
								);
								foreach ( $date_units as $dunit_key => $dunit_name ) {
									$selected = ( $date_unit == $dunit_key ) ? 'selected="selected"' : '';
									echo '<option ' . $selected . ' value="' . $dunit_key . '">' . $dunit_name . '</option>';
								}
								?>
							</select>
							<?php esc_html_e( 'ago and newer', 'all-sources-images' ); ?>
						</label>
					</td>
					<td>
						<p class="description">
							<?php esc_html_e( 'Deadline for images to be generated. For example, if you select "5 days ago and newer", all posts created up to 5 days ago will be affected.', 'all-sources-images' ); ?>
						</p>
					</td>
				</tr>

				<tr class="cron-settings" <?php echo ( empty( $options['enable_cron']) || $options['enable_cron'] != 'enable' ) ? 'style="display:none;"' : ''; ?>>
					<td>
						<?php esc_html_e( 'Number of posts', 'all-sources-images' ); ?>
					</td>
					<td>
						<select name="ASI_plugin_cron_settings[posts_per_run]" class="form-control form-control-lg">
							<?php
							$posts_per_run = isset($options['posts_per_run']) ? $options['posts_per_run'] : 5;
							for ( $i = 1; $i <= 20; $i++ ) {
								$selected = ( $posts_per_run == $i ) ? 'selected="selected"' : '';
								echo '<option ' . $selected . ' value="' . $i . '">' . $i . '</option>';
							}
							?>
						</select>
					</td>
					<td>
						<p class="description">
							<?php esc_html_e( 'Limit the number of generations for each cron pass. This prevents several hundred images being generated in a row in a single pass. Recommended number: 5 or 10.', 'all-sources-images' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button(); ?>

		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#enable_cron').on('switchChange.bootstrapSwitch', function(event, state) {
					if (state) {
						$('.cron-settings').show('fast');
					} else {
						$('.cron-settings').hide('fast');
					}
				});
			});
		</script>

    </form>
</div>
