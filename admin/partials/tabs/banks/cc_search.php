<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="CC Search Logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/cc_search.png' ); ?>"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php echo wp_kses_post( __( '<a target="_blank" href="https://oldsearch.creativecommons.org/">CC Search</a> provides Creative Commons images. You can search these images manually from <a target="_blank" href="https://wordpress.org/openverse/">Openverse (WordPress)</a>.', 'all-sources-images' ) ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Source', 'all-sources-images' ); ?></label>
	</th>
	<td>

		<select name="ALLSI_plugin_banks_settings[cc_search][source]" class="form-control form-control-lg" >
			<?php
				$allsi_selected = $options['cc_search']['source'];
				$allsi_source_choose = array(
					esc_html__( 'All sources', 'all-sources-images' )          => 1,
					esc_html__( 'All except flickr', 'all-sources-images' )    => 2,
					esc_html__( 'Other', 'all-sources-images' )                => 3
				);
				//ksort( $country_choose );

				foreach( $allsi_source_choose as $allsi_name_source => $allsi_code_source ) {
					echo '<option ' . selected( $allsi_selected, $allsi_code_source, false ) . ' value="' . esc_attr( $allsi_code_source ) . '">' . esc_html( $allsi_name_source ) . '</option>';
				}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Rights', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<p class="description">
			<?php esc_html_e( 'Choose these options can reduce relevance of results, but permit to use free-to-use images.', 'all-sources-images' ); ?>
		</p>
		<?php
			$allsi_rights_array = array(
        		esc_html__( 'Zéro', 'all-sources-images' )                                            => 'CC0',
				esc_html__( 'Attribution-NoDerivs', 'all-sources-images' )                     => 'BY-ND',
				esc_html__( 'Attribution-NonCommercial', 'all-sources-images' )                => 'BY-NC',
				esc_html__( 'Attribution-NonCommercial-ShareAlike', 'all-sources-images' )     => 'BY-NC-SA',
				esc_html__( 'Attribution-NonCommercial-NoDerivs', 'all-sources-images' )       => 'BY-NC-ND',
				esc_html__( 'Attribution-ShareAlike', 'all-sources-images' )                   => 'BY-SA',
				esc_html__( 'Attribution', 'all-sources-images' )                              => 'BY',
				esc_html__( 'Public Domain Mark', 'all-sources-images' )                       => 'PDM',
			);

			foreach ( $allsi_rights_array as $allsi_right => $allsi_right_code ) {
				$allsi_is_checked = ( isset( $options['cc_search']['rights'] ) && ! empty( $options['cc_search']['rights'] ) && in_array( $allsi_right_code, $options['cc_search']['rights'], true ) );
				echo '<label class="checkbox">
					<input ' . checked( $allsi_is_checked, true, false ) . ' name="ALLSI_plugin_banks_settings[cc_search][rights][' . esc_attr( $allsi_right_code ) . ']" type="checkbox" value="' . esc_attr( $allsi_right_code ) . '"> <span></span> ' . esc_html( $allsi_right ) . '
				</label>';
			}
		?>

	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image Type', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[cc_search][imgtype]" class="form-control form-control-lg" >
			<?php
			$allsi_selected = $options['cc_search']['imgtype'];

			$allsi_formats = array(
				esc_html__( '-- Default --', 'all-sources-images' )            => '',
				esc_html__( 'Illustration', 'all-sources-images' )             => 'illustration',
				esc_html__( 'Photography', 'all-sources-images' )             => 'photograph',
				esc_html__( 'Digitized Artwork', 'all-sources-images' )        => 'digitized_artwork'
			);

			// format
			foreach( $allsi_formats as $allsi_name_format => $allsi_code_format ) {
				echo '<option ' . selected( $allsi_selected, $allsi_code_format, false ) . ' value="' . esc_attr( $allsi_code_format ) . '">' . esc_html( $allsi_name_format ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Aspect Ratio', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[cc_search][aspect_ratio]" class="form-control form-control-lg" >
			<?php
			$allsi_selected = $options['cc_search']['aspect_ratio'];

			$allsi_aspect_ratio = array(
				esc_html__( 'Tall', 'all-sources-images' )     => 'tall',
				esc_html__( 'Wide', 'all-sources-images' )     => 'wide',
				esc_html__( 'Square', 'all-sources-images' )   => 'square'
			);

			foreach( $allsi_aspect_ratio as $allsi_name_aspect_ratio => $allsi_code_aspect_ratio ) {
				echo '<option ' . selected( $allsi_selected, $allsi_code_aspect_ratio, false ) . ' value="' . esc_attr( $allsi_code_aspect_ratio ) . '">' . esc_html( $allsi_name_aspect_ratio ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>
