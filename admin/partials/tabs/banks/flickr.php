<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Flickr Logo" src="<?php echo plugin_dir_url( __FILE__ ); ?>/img/flickr.png"></td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php _e( 'Rights', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<p class="description">
			<?php _e( 'Choose which licence works for pictures. Licences chosen are cumulative.', 'all-sources-images' ); ?><br/>
			<?php _e( 'If none of these options are chosen, every licences will be used.', 'all-sources-images' ); ?>
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
				$checked= ( isset( $options['flickr']['rights'] ) && !empty( $options['flickr']['rights'] ) && in_array( $right_code, $options['flickr']['rights'] ) )? 'checked="checked""' : '';
				echo '
				<label class="checkbox">
					<input '. $checked .' name="ASI_plugin_banks_settings[flickr][rights]['. $right_code .']" type="checkbox" value="'. $right_code .'"> <span></span> '. $right .'
				</label>';
			}
		?>
		
	</td>
</tr>


<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php _e( 'Image Type', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[flickr][imgtype]" class="form-control" >
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
				echo '<option '. $choose .' value="'. $code_imgtype .'">'. $name_imgtype .'</option>';
			}
			?>
		</select>
	</td>
</tr>