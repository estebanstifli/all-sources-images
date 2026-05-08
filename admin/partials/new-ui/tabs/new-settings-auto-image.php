<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * New Settings - Auto Image Tab
 *
 * @since 6.2.0
 */

settings_errors();

$allsi_auto_settings = wp_parse_args(
    get_option( 'ALLSI_plugin_auto_image_settings' ),
    $this->ALLSI_default_options_auto_image_settings( true )
);

$allsi_auto_enabled = ( ! empty( $allsi_auto_settings['enabled'] ) && 'enable' === $allsi_auto_settings['enabled'] );

$allsi_selected_post_types = array();
if ( ! empty( $allsi_auto_settings['post_types'] ) && is_array( $allsi_auto_settings['post_types'] ) ) {
    $allsi_selected_post_types = array_map( 'sanitize_key', $allsi_auto_settings['post_types'] );
}

$allsi_selected_term_ids = array();
if ( ! empty( $allsi_auto_settings['term_ids'] ) && is_array( $allsi_auto_settings['term_ids'] ) ) {
    $allsi_selected_term_ids = array_map( 'absint', $allsi_auto_settings['term_ids'] );
}

$allsi_excluded_post_ids = isset( $allsi_auto_settings['exclude_post_ids'] ) ? sanitize_text_field( $allsi_auto_settings['exclude_post_ids'] ) : '';

$allsi_post_types = get_post_types( array( 'public' => true ), 'objects' );
unset( $allsi_post_types['attachment'] );

$allsi_taxonomies = get_taxonomies(
    array(
        'public'       => true,
        'hierarchical' => true,
        'show_ui'      => true,
    ),
    'objects'
);

$allsi_terms_by_taxonomy = array();
foreach ( $allsi_taxonomies as $allsi_taxonomy ) {
    $allsi_terms = get_terms(
        array(
            'taxonomy'   => $allsi_taxonomy->name,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );

    if ( is_wp_error( $allsi_terms ) || empty( $allsi_terms ) ) {
        continue;
    }

    $allsi_terms_by_taxonomy[ $allsi_taxonomy->name ] = array(
        'object' => $allsi_taxonomy,
        'terms'  => $allsi_terms,
    );
}
?>

<form method="post" action="options.php">
    <?php settings_fields( 'ASI-plugin-auto-image-settings' ); ?>

    <div class="allsi-alert allsi-alert-info" style="margin-bottom: 20px;">
        <div class="allsi-alert-icon">
            <span class="dashicons dashicons-info-outline"></span>
        </div>
        <div class="allsi-alert-content">
            <strong><?php esc_html_e( 'Auto Image on Publish', 'all-sources-images' ); ?></strong>
            <p><?php esc_html_e( 'Enable automatic image generation when a post is published and restrict execution using filters below.', 'all-sources-images' ); ?></p>
            <p>
                <?php esc_html_e( 'Image placement, source priority, prompts and post-processing are configured in Bulk Settings.', 'all-sources-images' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=allsi-new-automatic' ) ); ?>">
                    <?php esc_html_e( 'Open Bulk Settings', 'all-sources-images' ); ?>
                </a>
            </p>
        </div>
    </div>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="allsi-auto-image-enabled"><?php esc_html_e( 'Enable Auto Image', 'all-sources-images' ); ?></label>
                </th>
                <td>
                    <label class="allsi-switch">
                        <input
                            type="checkbox"
                            id="allsi-auto-image-enabled"
                            name="ALLSI_plugin_auto_image_settings[enabled]"
                            value="enable"
                            <?php checked( $allsi_auto_enabled ); ?>
                        />
                        <span class="allsi-switch-slider"></span>
                    </label>
                    <p class="description"><?php esc_html_e( 'When enabled, the plugin schedules image generation after publication for supported workflows (manual publish, importers, RSS/autobloggers).', 'all-sources-images' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Post Type Filter', 'all-sources-images' ); ?></th>
                <td class="checkbox-list">
                    <?php if ( empty( $allsi_post_types ) ) : ?>
                        <p class="description"><?php esc_html_e( 'No public post types found.', 'all-sources-images' ); ?></p>
                    <?php else : ?>
                        <ul class="radio-list">
                            <?php foreach ( $allsi_post_types as $allsi_post_type ) : ?>
                                <?php
                                $allsi_pt_name = isset( $allsi_post_type->name ) ? $allsi_post_type->name : '';
                                $allsi_pt_label = isset( $allsi_post_type->labels->singular_name ) ? $allsi_post_type->labels->singular_name : $allsi_pt_name;
                                if ( '' === $allsi_pt_name ) {
                                    continue;
                                }
                                ?>
                                <li>
                                    <label class="checkbox">
                                        <input
                                            type="checkbox"
                                            name="ALLSI_plugin_auto_image_settings[post_types][]"
                                            value="<?php echo esc_attr( $allsi_pt_name ); ?>"
                                            <?php checked( in_array( $allsi_pt_name, $allsi_selected_post_types, true ) ); ?>
                                        />
                                        <span></span>
                                        <?php echo esc_html( $allsi_pt_label ); ?>
                                        <small class="description" style="margin-left: 6px;">(<?php echo esc_html( $allsi_pt_name ); ?>)</small>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="description"><?php esc_html_e( 'Select where auto generation should run. If none is selected, all public post types are allowed.', 'all-sources-images' ); ?></p>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Taxonomy Filter', 'all-sources-images' ); ?></th>
                <td class="checkbox-list">
                    <?php if ( empty( $allsi_terms_by_taxonomy ) ) : ?>
                        <p class="description"><?php esc_html_e( 'No hierarchical taxonomy terms found.', 'all-sources-images' ); ?></p>
                    <?php else : ?>
                        <?php foreach ( $allsi_terms_by_taxonomy as $allsi_tax_name => $allsi_tax_data ) : ?>
                            <p style="margin: 12px 0 6px;"><strong><?php echo esc_html( $allsi_tax_data['object']->labels->name ); ?></strong></p>
                            <ul class="radio-list" style="margin-bottom: 6px;">
                                <?php foreach ( $allsi_tax_data['terms'] as $allsi_term ) : ?>
                                    <?php $allsi_term_id = absint( $allsi_term->term_id ); ?>
                                    <li>
                                        <label class="checkbox">
                                            <input
                                                type="checkbox"
                                                name="ALLSI_plugin_auto_image_settings[term_ids][]"
                                                value="<?php echo esc_attr( $allsi_term_id ); ?>"
                                                <?php checked( in_array( $allsi_term_id, $allsi_selected_term_ids, true ) ); ?>
                                            />
                                            <span></span>
                                            <?php echo esc_html( $allsi_term->name ); ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endforeach; ?>
                        <p class="description"><?php esc_html_e( 'Optional: if terms are selected, only posts containing at least one selected term will trigger generation.', 'all-sources-images' ); ?></p>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="allsi-excluded-post-ids"><?php esc_html_e( 'Exclude Post IDs', 'all-sources-images' ); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        id="allsi-excluded-post-ids"
                        name="ALLSI_plugin_auto_image_settings[exclude_post_ids]"
                        value="<?php echo esc_attr( $allsi_excluded_post_ids ); ?>"
                        placeholder="12,34,56"
                    />
                    <p class="description"><?php esc_html_e( 'Comma-separated list of post IDs that should never trigger auto image generation.', 'all-sources-images' ); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <?php submit_button( __( 'Save Auto Image Settings', 'all-sources-images' ), 'primary' ); ?>
</form>
