<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * New Settings - Source Tab
 * Same HTML structure as original source.php
 *
 * @since 6.2.0
 */

settings_errors();
?>

    <form method="post" action="options.php">

            <?php 
settings_fields( 'ASI-plugin-banks-settings' );
$options = wp_parse_args( get_option( 'ALLSI_plugin_banks_settings' ), $this->ALLSI_default_options_banks_settings( FALSE ) );
// Remove 'envato' from saved options if present (Envato Elements no longer working)
if ( isset( $options['api_chosen_auto'] ) && is_array( $options['api_chosen_auto'] ) ) {
    unset($options['api_chosen_auto']['envato']);
    unset($options['api_chosen_auto']['google_translate']);
}
if ( isset( $options['api_chosen_manual'] ) && is_array( $options['api_chosen_manual'] ) ) {
    unset($options['api_chosen_manual']['envato']);
}
if ( isset( $options['api_chosen'] ) && 'envato' === $options['api_chosen'] ) {
    $options['api_chosen'] = '';
}

/* Banks for Manual Search with Gutenberg Block - Only Manual for this page */
$list_api_manual = $this->ALLSI_banks_name_manual();
$ai_sources = method_exists( $this, 'ALLSI_ai_source_codes' ) ? $this->ALLSI_ai_source_codes() : array('dallev1', 'stability', 'replicate');
$render_source_badge = function( $slug ) use ( $ai_sources ) {
    if ( ! in_array( $slug, $ai_sources, true ) ) {
        return '';
    }

    return '<span class="allsi-source-badge"><span class="allsi-source-dot"></span><span class="allsi-source-badge__label">' . esc_html__( 'AI', 'all-sources-images' ) . '</span></span>';
};

$render_checkboxes = function( $items, $option_key ) use ( $options, $render_source_badge ) {
    if ( empty( $items ) ) {
        echo '<p class="description">' . esc_html__( 'No sources available yet.', 'all-sources-images' ) . '</p>';
        return;
    }

    $default_bank_order = 99;
    $rendered_list      = array();
    $selected_options   = ( isset( $options[$option_key] ) && is_array( $options[$option_key] ) ) ? $options[$option_key] : array();
    $order_api_chosen   = array_keys( $selected_options );

    foreach ( $items as $api => $api_code ) {
        $slug = isset( $api_code[0] ) ? $api_code[0] : '';
        $checked = ( !empty( $selected_options ) && in_array( $slug, $selected_options, true ) ) ? 'checked="checked"' : '';
        $key_api_chosen = is_array( $order_api_chosen ) ? array_search( $slug, $order_api_chosen, true ) : false;
        if ( false === $key_api_chosen || ( isset( $rendered_list[0] ) && 0 == $key_api_chosen ) ) {
            $key_api_chosen = $default_bank_order;
            $default_bank_order++;
        }

        $disabled       = 'disabled';
        $class_disabled = 'checkbox-disabled';
        if ( true === $api_code[1] ) {
            $disabled       = '';
            $class_disabled = '';
        } else {
            $checked = '';
        }

        $badge_markup = $render_source_badge( $slug );

        $rendered_list[$key_api_chosen] = '<li><label class="checkbox ' . $class_disabled . '"><input class="ordered-checkbox" data-order="' . $key_api_chosen . '" type="checkbox" ' . $checked . ' ' . $disabled . ' value="' . esc_attr( $slug ) . '" name="ALLSI_plugin_banks_settings[' . esc_attr( $option_key ) . '][' . esc_attr( $slug ) . ']"> <span></span> ' . esc_html( $api ) . ' ' . $badge_markup . '</label></li>';
    }

    ksort( $rendered_list );

    echo '<ul class="radio-list">';
    foreach ( $rendered_list as $rendered_item ) {
        echo $rendered_item; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    echo '</ul>';
};
?>

            <div class="alert alert-custom alert-default" role="alert">
                <div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <rect x="0" y="0" width="24" height="24"></rect>
                        <path d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z" fill="#000000" opacity="0.3"></path>
                        <path d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z" fill="#000000" fill-rule="nonzero"></path>
                    </g>
                </svg><!--end::Svg Icon--></span>
                </div>
                <div class="alert-text">
                    <?php 
esc_html_e( 'Choose the sources you want to use for manual search (Gutenberg block, Elementor widget, Media Library). Drag to set priority. BANK pulls existing photos, AI creates images from prompts.', 'all-sources-images' );
?>
                </div>
            </div>

            <table id="general-options" class="form-table">
	            <tbody>
	              <tr valign="top">
	                <th scope="row">
					<label for="hseparator"><?php 
esc_html_e( 'Image Sources', 'all-sources-images' );
?></label>
	                </th>
	                <td class="chosen_api checkbox-list">

				   <h5><?php 
esc_html_e( 'Gutenberg Block / Elementor / Media', 'all-sources-images' );
?></h5>
                    <p class="description text-muted"><?php 
esc_html_e( 'Used when picking images manually inside the editor.', 'all-sources-images' );
?></p>

				   <?php 
$render_checkboxes( $list_api_manual, 'api_chosen_manual' );
?>
                 </td>
	              </tr>
	            </tbody>
            </table>

			<hr/>

			<h3><?php 
esc_html_e( 'Source Settings', 'all-sources-images' );
?></h3>

			<div id="tabs" class="allsi-tabs">
				<ul>
					<?php 
$a = 0;
foreach ( $list_api_manual as $api => $api_code ) {
    if ( false === $api_code[1] ) {
        continue;
    }
    echo '<li><a href="#tab-' . esc_attr( $a ) . '">' . esc_html( $api ) . '</a></li>';
    $a++;
}
?>
				</ul>
				<?php 
$a = 0;
foreach ( $list_api_manual as $api => $api_code ) {
    if ( false === $api_code[1] ) {
        continue;
    }
    echo '<table id="tab-' . esc_attr( $a ) . '" class="form-table" >';
    echo '<tbody>';
    include_once plugin_dir_path( __FILE__ ) . '../../tabs/banks/' . $api_code[0] . '.php';
    echo '</tbody>';
    echo '</table>';
    $a++;
}
?>
			</div>

            <?php 
submit_button();
?>

    </form>
