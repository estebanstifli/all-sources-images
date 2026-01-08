<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Flickr Logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/flickr.png' ); ?>"></td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="ALLSI_plugin_banks_settings_flickr_apikey"><?php esc_html_e( 'API Key', 'all-sources-images' ); ?></label>
	</th>
	<td id="password-flickr" class="password">
		<input type="password" id="ALLSI_plugin_banks_settings_flickr_apikey" name="ALLSI_plugin_banks_settings[flickr][apikey]" class="form-control" value="<?php echo ( isset( $options['flickr']['apikey'] ) && ! empty( $options['flickr']['apikey'] ) ) ? esc_attr( $options['flickr']['apikey'] ) : ''; ?>" />
		<i id="togglePassword"></i>
		<p class="description">
			<?php esc_html_e( 'Create a Flickr App at flickr.com/services/apps/create/ to obtain your API key. Each site needs its own key.', 'all-sources-images' ); ?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Rights', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<p class="description">
			<?php esc_html_e( 'Choose which licence works for pictures. Licences chosen are cumulative.', 'all-sources-images' ); ?><br/>
			<?php esc_html_e( 'If none of these options are chosen, every licences will be used.', 'all-sources-images' ); ?>
		</p>
		<?php 
			$rights_array = array(
				__( 'All Rights Reserved', 'all-sources-images' )																																	=> '0',
				__( 'Attribution-NonCommercial-ShareAlike License - <i><a href="http://creativecommons.org/licenses/by-nc-sa/2.0/" target="_blank">More detail</a></i>', 'all-sources-images' )	=> '1',
				__( 'Attribution-NonCommercial License - <i><a href="http://creativecommons.org/licenses/by-nc/2.0/" target="_blank">More detail</a></i>', 'all-sources-images' )					=> '2',
				__( 'Attribution-NonCommercial-NoDerivs License - <i><a href="http://creativecommons.org/licenses/by-nc-nd/2.0/" target="_blank">More detail</a></i>', 'all-sources-images' )		=> '3',
				__( 'Attribution License - <i><a href="http://creativecommons.org/licenses/by/2.0/" target="_blank">More detail</a></i>', 'all-sources-images' )									=> '4',
				__( 'Attribution License - <i><a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">More detail</a></i>', 'all-sources-images' )								=> '5',
				__( 'Attribution-NoDerivs License - <i><a href="http://creativecommons.org/licenses/by-nd/2.0/" target="_blank">More detail</a></i>', 'all-sources-images' )						=> '6',
				__( 'No known copyright restrictions - <i><a href="http://flickr.com/commons/usage/" target="_blank">More detail</a></i>', 'all-sources-images' )									=> '7',
				__( 'United States Government Work - <i><a href="http://www.usa.gov/copyright.shtml" target="_blank">More detail</a></i>', 'all-sources-images' )									=> '8',
				
			);
		
		
			foreach ( $rights_array  as $right => $right_code ) {
				$is_checked = ( isset( $options['flickr']['rights'] ) && ! empty( $options['flickr']['rights'] ) && is_array( $options['flickr']['rights'] ) && in_array( $right_code, $options['flickr']['rights'], true ) );
				echo '
				<label class="checkbox">
					<input ' . checked( $is_checked, true, false ) . ' name="ALLSI_plugin_banks_settings[flickr][rights][' . esc_attr( $right_code ) . ']" type="checkbox" value="' . esc_attr( $right_code ) . '"> <span></span> ' . wp_kses_post( $right ) . '
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
		<select name="ALLSI_plugin_banks_settings[flickr][imgtype]" class="form-control" >
			<?php
			$selected = $options['flickr']['imgtype'];
			
			$imgtype = array(
				__( '-- All --', 'all-sources-images' )				=> '7',
				__( 'Photo', 'all-sources-images' )					=> '1',
				__( 'Screenshot', 'all-sources-images' )				=> '2',
				__( 'Other', 'all-sources-images' )					=> '3',
				__( 'Photo and screenshot', 'all-sources-images' )		=> '4',
				__( 'Screenshot and "other"', 'all-sources-images' )	=> '5',
				__( 'Photo and "other"', 'all-sources-images' ) 		=> '6',
			);

			foreach( $imgtype as $name_imgtype => $code_imgtype ) {
				$choose=($selected == $code_imgtype)?'selected="selected"': '';
				echo '<option ' . esc_attr( $choose ) . ' value="' . esc_attr( $code_imgtype ) . '">' . esc_html( $name_imgtype ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>