<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
$allsi_options = wp_parse_args( get_option( 'ALLSI_plugin_banks_settings' ), $this->ALLSI_default_options_banks_settings( false ) );
// Remove 'envato' from saved options if present (Envato Elements no longer working)
if ( isset( $allsi_options['api_chosen_auto'] ) && is_array( $allsi_options['api_chosen_auto'] ) ) {
    unset( $allsi_options['api_chosen_auto']['envato'] );
    unset( $allsi_options['api_chosen_auto']['google_translate'] );
}
if ( isset( $allsi_options['api_chosen_manual'] ) && is_array( $allsi_options['api_chosen_manual'] ) ) {
    unset( $allsi_options['api_chosen_manual']['envato'] );
}
if ( isset( $allsi_options['api_chosen'] ) && 'envato' === $allsi_options['api_chosen'] ) {
    $allsi_options['api_chosen'] = '';
}

/* Banks for Manual Search with Gutenberg Block - Only Manual for this page */
$allsi_list_api_manual = $this->ALLSI_banks_name_manual();
$allsi_ai_sources      = method_exists( $this, 'ALLSI_ai_source_codes' ) ? $this->ALLSI_ai_source_codes() : array( 'dallev1', 'stability', 'replicate' );
$allsi_render_source_badge = function( $allsi_slug ) use ( $allsi_ai_sources ) {
    if ( ! in_array( $allsi_slug, $allsi_ai_sources, true ) ) {
        return '';
    }

    return '<span class="allsi-source-badge"><span class="allsi-source-dot"></span><span class="allsi-source-badge__label">' . esc_html__( 'AI', 'all-sources-images' ) . '</span></span>';
};

$allsi_render_checkboxes = function( $allsi_items, $allsi_option_key ) use ( $allsi_options, $allsi_render_source_badge ) {
    if ( empty( $allsi_items ) ) {
        echo '<p class="description">' . esc_html__( 'No sources available yet.', 'all-sources-images' ) . '</p>';
        return;
    }

    $allsi_default_bank_order = 99;
    $allsi_rendered_list      = array();
    $allsi_selected_options   = ( isset( $allsi_options[ $allsi_option_key ] ) && is_array( $allsi_options[ $allsi_option_key ] ) ) ? $allsi_options[ $allsi_option_key ] : array();
    $allsi_order_api_chosen   = array_keys( $allsi_selected_options );

    foreach ( $allsi_items as $allsi_api => $allsi_api_code ) {
        $allsi_slug        = isset( $allsi_api_code[0] ) ? $allsi_api_code[0] : '';
        $allsi_is_selected = ( ! empty( $allsi_selected_options ) && in_array( $allsi_slug, $allsi_selected_options, true ) );

        $allsi_key_api_chosen = is_array( $allsi_order_api_chosen ) ? array_search( $allsi_slug, $allsi_order_api_chosen, true ) : false;
        if ( false === $allsi_key_api_chosen || ( isset( $allsi_rendered_list[0] ) && 0 == $allsi_key_api_chosen ) ) {
            $allsi_key_api_chosen = $allsi_default_bank_order;
            $allsi_default_bank_order++;
        }

        $allsi_is_disabled  = true;
        $allsi_class_disabled = 'checkbox-disabled';
        if ( true === $allsi_api_code[1] ) {
            $allsi_is_disabled    = false;
            $allsi_class_disabled = '';
        } else {
            $allsi_is_selected = false;
        }

        $allsi_badge_markup = $allsi_render_source_badge( $allsi_slug );

        $allsi_rendered_list[ $allsi_key_api_chosen ] = '<li><label class="checkbox ' . esc_attr( $allsi_class_disabled ) . '"><input class="ordered-checkbox" data-order="' . esc_attr( $allsi_key_api_chosen ) . '" type="checkbox" ' . checked( $allsi_is_selected, true, false ) . ' ' . disabled( $allsi_is_disabled, true, false ) . ' value="' . esc_attr( $allsi_slug ) . '" name="ALLSI_plugin_banks_settings[' . esc_attr( $allsi_option_key ) . '][' . esc_attr( $allsi_slug ) . ']"> <span></span> ' . esc_html( $allsi_api ) . ' ' . $allsi_badge_markup . '</label></li>';
    }

    ksort( $allsi_rendered_list );

    echo '<ul class="radio-list">';
    foreach ( $allsi_rendered_list as $allsi_rendered_item ) {
        echo $allsi_rendered_item; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    echo '</ul>';
};

$allsi_include_bank_partial = function( $allsi_bank_slug ) use ( $allsi_options ) {
    // Bank partials expect "$options" to be available.
    $options = $allsi_options;
    include plugin_dir_path( __FILE__ ) . '../../tabs/banks/' . $allsi_bank_slug . '.php';
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
$allsi_render_checkboxes( $allsi_list_api_manual, 'api_chosen_manual' );
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
$allsi_tab_index = 0;
foreach ( $allsi_list_api_manual as $allsi_api => $allsi_api_code ) {
    if ( false === $allsi_api_code[1] ) {
        continue;
    }
    echo '<li><a href="#tab-' . esc_attr( $allsi_tab_index ) . '">' . esc_html( $allsi_api ) . '</a></li>';
    $allsi_tab_index++;
}
?>
				</ul>
				<?php 
$allsi_tab_index = 0;
foreach ( $allsi_list_api_manual as $allsi_api => $allsi_api_code ) {
    if ( false === $allsi_api_code[1] ) {
        continue;
    }
    echo '<table id="tab-' . esc_attr( $allsi_tab_index ) . '" class="form-table" >';
    echo '<tbody>';
    $allsi_include_bank_partial( $allsi_api_code[0] );
    echo '</tbody>';
    echo '</table>';
    $allsi_tab_index++;
}
?>
			</div>

            <?php 
submit_button();
?>

    </form>
