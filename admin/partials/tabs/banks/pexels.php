<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Pexels Logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/pexels.png' ); ?>"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php echo wp_kses_post( __( '<b>It\'s required</b> to provide your own <b>API key</b>. You can register for free at <a target="_blank" href="https://www.pexels.com/api/">Pexels API</a> and get instant access to your API key.', 'all-sources-images' ) ); ?>
		</div>
		<div class="update-nag">
			<?php echo wp_kses_post( __( 'Pexels is free to use with <b>attribution required</b>. Free tier: 200 requests/hour, 20,000 requests/month. Contact Pexels for higher limits at no cost if you provide proper attribution.', 'all-sources-images' ) ); ?>
		</div>
		<div class="update-nag">
			<?php echo wp_kses_post( __( '<b>Attribution:</b> Photos will automatically include photographer credit. You must display "Photo by [Name] on Pexels" or similar attribution according to <a target="_blank" href="https://www.pexels.com/license/">Pexels License</a>.', 'all-sources-images' ) ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'API Key', 'all-sources-images' ); ?></label>
	</th>
	<td id="password-pexels" class="password">
		<input type="password" name="ALLSI_plugin_banks_settings[pexels][apikey]" class="form-control" value="<?php echo( isset( $options['pexels']['apikey'] ) && !empty( $options['pexels']['apikey']) )? esc_attr( $options['pexels']['apikey'] ) : ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<button type="button" class="btn btn-primary" id="btnPexels">
			<?php esc_html_e( 'API testing', 'all-sources-images' ); ?>
		</button>
		<span id="resultPexels"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden"/></span>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Orientation', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pexels][orientation]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_orientation = isset( $options['pexels']['orientation'] ) ? $options['pexels']['orientation'] : 'all';

			$allsi_orientations = array(
				esc_html__( 'All Orientations', 'all-sources-images' ) => 'all',
				esc_html__( 'Landscape', 'all-sources-images' )        => 'landscape',
				esc_html__( 'Portrait', 'all-sources-images' )         => 'portrait',
				esc_html__( 'Square', 'all-sources-images' )           => 'square',
			);

			foreach ( $allsi_orientations as $allsi_name_orientation => $allsi_code_orientation ) {
				echo '<option ' . selected( $allsi_selected_orientation, $allsi_code_orientation, false ) . ' value="' . esc_attr( $allsi_code_orientation ) . '">' . esc_html( $allsi_name_orientation ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Filter photos by orientation. Choose the aspect ratio that best fits your layout.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Minimum Size', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pexels][size]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_size = isset( $options['pexels']['size'] ) ? $options['pexels']['size'] : 'all';

			$allsi_sizes = array(
				esc_html__( 'All Sizes', 'all-sources-images' )       => 'all',
				esc_html__( 'Large (24MP+)', 'all-sources-images' )   => 'large',
				esc_html__( 'Medium (12MP+)', 'all-sources-images' )  => 'medium',
				esc_html__( 'Small (4MP+)', 'all-sources-images' )    => 'small',
			);

			foreach ( $allsi_sizes as $allsi_name_size => $allsi_code_size ) {
				echo '<option ' . selected( $allsi_selected_size, $allsi_code_size, false ) . ' value="' . esc_attr( $allsi_code_size ) . '">' . esc_html( $allsi_name_size ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Minimum photo resolution. Higher resolutions provide better quality but may have fewer results.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Color Filter', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pexels][color]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_color = isset( $options['pexels']['color'] ) ? $options['pexels']['color'] : 'all';

			$allsi_colors = array(
				esc_html__( 'All Colors', 'all-sources-images' )      => 'all',
				esc_html__( 'Red', 'all-sources-images' )             => 'red',
				esc_html__( 'Orange', 'all-sources-images' )          => 'orange',
				esc_html__( 'Yellow', 'all-sources-images' )          => 'yellow',
				esc_html__( 'Green', 'all-sources-images' )           => 'green',
				esc_html__( 'Turquoise', 'all-sources-images' )       => 'turquoise',
				esc_html__( 'Blue', 'all-sources-images' )            => 'blue',
				esc_html__( 'Violet', 'all-sources-images' )          => 'violet',
				esc_html__( 'Pink', 'all-sources-images' )            => 'pink',
				esc_html__( 'Brown', 'all-sources-images' )           => 'brown',
				esc_html__( 'Black', 'all-sources-images' )           => 'black',
				esc_html__( 'Gray', 'all-sources-images' )            => 'gray',
				esc_html__( 'White', 'all-sources-images' )           => 'white',
			);

			foreach ( $allsi_colors as $allsi_name_color => $allsi_code_color ) {
				echo '<option ' . selected( $allsi_selected_color, $allsi_code_color, false ) . ' value="' . esc_attr( $allsi_code_color ) . '">' . esc_html( $allsi_name_color ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Filter photos by dominant color. Useful for matching your site\'s color scheme.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Locale', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pexels][locale]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_locale = isset( $options['pexels']['locale'] ) ? $options['pexels']['locale'] : 'en-US';

			$allsi_locales = array(
				esc_html__( 'English (US)', 'all-sources-images' )    => 'en-US',
				esc_html__( 'Spanish (ES)', 'all-sources-images' )    => 'es-ES',
				esc_html__( 'French (FR)', 'all-sources-images' )     => 'fr-FR',
				esc_html__( 'German (DE)', 'all-sources-images' )     => 'de-DE',
				esc_html__( 'Italian (IT)', 'all-sources-images' )    => 'it-IT',
				esc_html__( 'Portuguese (BR)', 'all-sources-images' ) => 'pt-BR',
				esc_html__( 'Russian (RU)', 'all-sources-images' )    => 'ru-RU',
				esc_html__( 'Japanese (JP)', 'all-sources-images' )   => 'ja-JP',
				esc_html__( 'Chinese (CN)', 'all-sources-images' )    => 'zh-CN',
				esc_html__( 'Korean (KR)', 'all-sources-images' )     => 'ko-KR',
			);

			foreach ( $allsi_locales as $allsi_name_locale => $allsi_code_locale ) {
				echo '<option ' . selected( $allsi_selected_locale, $allsi_code_locale, false ) . ' value="' . esc_attr( $allsi_code_locale ) . '">' . esc_html( $allsi_name_locale ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Language for search queries. Pexels supports 28 languages for better regional search results.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Results Per Search', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pexels][per_page]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_per_page = isset( $options['pexels']['per_page'] ) ? $options['pexels']['per_page'] : '15';

			$allsi_per_pages = array(
				esc_html__( '10 results', 'all-sources-images' ) => '10',
				esc_html__( '15 results', 'all-sources-images' ) => '15',
				esc_html__( '30 results', 'all-sources-images' ) => '30',
				esc_html__( '50 results', 'all-sources-images' ) => '50',
				esc_html__( '80 results', 'all-sources-images' ) => '80',
			);

			foreach ( $allsi_per_pages as $allsi_name_per_page => $allsi_code_per_page ) {
				echo '<option ' . selected( $allsi_selected_per_page, $allsi_code_per_page, false ) . ' value="' . esc_attr( $allsi_code_per_page ) . '">' . esc_html( $allsi_name_per_page ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Number of photos to fetch per search query. More results = better selection but higher API usage. Maximum: 80', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Preferred Image Size', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pexels][preferred_size]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_preferred_size = isset( $options['pexels']['preferred_size'] ) ? $options['pexels']['preferred_size'] : 'large';

			$allsi_preferred_sizes = array(
				esc_html__( 'Original (Full resolution)', 'all-sources-images' ) => 'original',
				esc_html__( 'Large 2x (High DPI)', 'all-sources-images' )        => 'large2x',
				esc_html__( 'Large (940px width)', 'all-sources-images' )        => 'large',
				esc_html__( 'Medium (350px height)', 'all-sources-images' )      => 'medium',
				esc_html__( 'Small (130px height)', 'all-sources-images' )       => 'small',
				esc_html__( 'Portrait (800x1200)', 'all-sources-images' )        => 'portrait',
				esc_html__( 'Landscape (1200x627)', 'all-sources-images' )       => 'landscape',
			);

			foreach ( $allsi_preferred_sizes as $allsi_name_preferred_size => $allsi_code_preferred_size ) {
				echo '<option ' . selected( $allsi_selected_preferred_size, $allsi_code_preferred_size, false ) . ' value="' . esc_attr( $allsi_code_preferred_size ) . '">' . esc_html( $allsi_name_preferred_size ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Which image size variant to download from Pexels. Original provides best quality, smaller sizes load faster.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Advanced Options', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<label class="checkbox ">
			<input name="ALLSI_plugin_banks_settings[pexels][use_curated]" type="checkbox" value="true" <?php checked( ! empty( $options['pexels']['use_curated'] ) && $options['pexels']['use_curated'] == 'true' ); ?>><span></span>
			<?php esc_html_e( 'Prefer curated photos', 'all-sources-images' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'When enabled, fallback to Pexels curated photos if search query returns no results. Curated photos are hand-picked daily.', 'all-sources-images' ); ?></p>
		
		<label class="checkbox ">
			<input name="ALLSI_plugin_banks_settings[pexels][add_attribution]" type="checkbox" value="true" <?php checked( ! empty( $options['pexels']['add_attribution'] ) && $options['pexels']['add_attribution'] == 'true' ); ?>><span></span>
			<?php esc_html_e( 'Automatically add attribution to image caption', 'all-sources-images' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'Adds photographer credit to image caption: "Photo by [Name] on Pexels". Required by Pexels license.', 'all-sources-images' ); ?></p>
	</td>
</tr>
