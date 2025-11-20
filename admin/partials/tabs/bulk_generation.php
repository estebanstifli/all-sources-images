<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>
<div class="wrap">

        <?php 

                $options_banks = wp_parse_args( get_option( 'ASI_plugin_banks_settings' ), $this->ASI_default_options_banks_settings( TRUE ) );
                $value_bulk_generation_interval = ( isset( $options['bulk_generation_interval'] ) )? (int)$options['bulk_generation_interval'] : 0;

                $bulk_generation_interval = false;
                if( 
                        ( 'bulk-generation' === $module ) && ( 0 !== $value_bulk_generation_interval ) ||
                        ( 'bulk-generation-interval' === $module )
                ) {
                        $bulk_generation_interval	= true;
                        $remaining_seconds 		= $this->cron_scheduled();
                }

                if (
                        ( ! empty( $_POST['all-sources-images'] ) || ! empty( $_REQUEST['ids_mpt_generation'] ) || ! empty( $_REQUEST['cats'] ) ) &&
                        ( empty( $_REQUEST['settings-updated'] ) || $_REQUEST['settings-updated'] != 'true' )
                ) {

                if( !empty( $_REQUEST['cats'] ) ) {

                $taxo_term = get_term( $_REQUEST['cats'] );
                if( empty( $taxo_term ) )
                        return false;

                $cpts = get_post_types( array( 'public' => true ), 'names' );

                $post_ids = get_posts( array(
                        'numberposts'   => -1, // get all posts.
                        'tax_query'			=> array(
                                array(
                                'taxonomy' => $taxo_term->taxonomy,
                                'field'    => 'slug',
                                'terms'    => $taxo_term->slug,
                                ),
                        ),
                        'post_type'     => array(),
                        'post_status'		=> array( 'publish', 'draft', 'pending', 'future', 'private' ),
                        'fields'        => 'ids', // Only get post IDs
                ) );

                $ids = '';
                foreach( $post_ids as $post_id ) {
                        $ids .= $post_id.',';
                }

                $_GET['ids']                    = substr_replace($ids ,'', -1);
                $_REQUEST['ids_mpt_generation'] = $_GET['ids'];
                }

                $ids_mpt_generation = esc_attr( $_REQUEST['ids_mpt_generation'] );
                $ids_mpt_generation = explode( ',', $ids_mpt_generation );
    ?>
            <div id="hide-before-import" style="display:none">

                <div class="progressionbar clearfix ">
                        <div class="progressionbar-title"><span><?php esc_html_e( 'Progress', 'all-sources-images' ); ?></span></div>
                        <div class="progressionbar-bar"></div>
                        <div class="skill-bar-percent"><span>0</span>%</div>
                </div>

                <?php if( $bulk_generation_interval && ( TRUE == $bulk_generation_interval ) ) { ?>
                        <div class="time-progress">
                                <div class="progressionbar-title">
                                        <span><?php esc_html_e( 'Time Before Next Generation', 'all-sources-images' ); ?></span>
                                        <span class="remaining-time">
                                                <?php echo $remaining_seconds; ?>
                                                <?php esc_html_e( 'seconds', 'all-sources-images' ); ?>
                                        </span>
                                </div>
                        </div>
                <?php } ?>

            </div>

        <?php
                if ( 
                        ( true === in_array( 'dallev1', $options_banks['api_chosen_auto'] ) ) && 
                        ( 'dallev1' === reset( $options_banks['api_chosen_auto'] ) ) 
                ) {
        ?>
            <p class="dalle-wait">
                <?php esc_html_e( 'Dall-e v3 Generation may take 20 to 40 seconds. Please be patient', 'all-sources-images' ); ?>.
            </p>
                <?php } ?>

            <table class="wp-list-mpt wp-list-table widefat fixed striped posts">
              <thead>
                <tr>
                  <th scope="row"></th>
                  <th scope="col" id="title" class="manage-column column-primary"><?php esc_html_e( 'Title', 'all-sources-images' ); ?></th>
                  <th scope="col" id="status" class="manage-column"><?php esc_html_e( 'Status', 'all-sources-images' ); ?></th>
                  <th scope="col" id="categories" class="manage-column"><?php esc_html_e( 'Post Links', 'all-sources-images' ); ?></th>
                  <th scope="col" id="tags" class="manage-column"><?php esc_html_e( 'Images', 'all-sources-images' ); ?></th>
                </tr>
              </thead>

              <tbody id="mpt-list">
                <?php
                  foreach( $ids_mpt_generation as $id ) {
                          if( !get_post_status( $id ) )
                                  continue;
                  ?>
                <tr id="post-<?php echo $id; ?>" class="post-<?php echo $id; ?>">
                        <th scope="row"></th>
                        <td class="column-title">
                                <strong>
                                        <span class="row-title"><?php echo get_the_title( $id ); ?></span>
                                </strong>
                        </td>
                        <td class="column-status column-primary">
                                <div class="row-status">
                                        <span class="status raw successful"><?php esc_html_e( 'Successful', 'all-sources-images' ); ?></span>
                                        <span class="status raw failed"><?php esc_html_e( 'Failed', 'all-sources-images' ); ?></span>
                                        <span class="status raw error"><?php esc_html_e( 'Error', 'all-sources-images' ); ?></span>
                                        <span class="status raw already-done"><?php esc_html_e( 'Image Already Exists', 'all-sources-images' ); ?></span>
                                        <span class="status raw no-rewrite"><?php esc_html_e( 'No image rewriting into post content', 'all-sources-images' ); ?></span>
                                </div>
                                <span class="empty-content"><img src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>" title="<?php esc_html_e( 'Waiting for generation', 'all-sources-images' ); ?>" /></span>
                                <p class="show-image-details"><a href="#"><?php esc_html_e( 'Show Image Details', 'all-sources-images' ); ?></a></p>
                                <p class="hide-image-details"><a href="#"><?php esc_html_e( 'Hide Image Details', 'all-sources-images' ); ?></a></p>
                                <div class="image-details"></div>
                        </td>
                        <td class="column-edit-links">
                                <span class="empty-content">-</span>
                                <div class="row-actions">
                                        <span class="edit"><a href="<?php echo get_edit_post_link( $id ); ?>" target="_blank"> <span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit', 'all-sources-images' ); ?></a> | </span>
                                        <span class="view"><a href="<?php echo get_permalink( $id ); ?>" target="_blank"> <span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'View', 'all-sources-images' ); ?></a></span>
                                </div>
                        </td>
                        <td class="column-image">
                                <span class="empty-content">-</span>
                                <div class="row-image">
                                </div>
                        </td>
                </tr>
                <?php } ?>

                <tr class="successful-generation">
                        <th scope="row"></th>
                        <td class="title has-row-actions column-primary" colspan="4">
                                <strong><?php esc_html_e( 'Successful Generation', 'all-sources-images' ); ?></strong>
                        </td>
                </tr>
              </tbody>
            </table>

        <?php } else { ?>
        
                <p><?php esc_html_e( 'Currently no generation.', 'all-sources-images' ); ?></p>
                <p><?php esc_html_e( 'Steps to generate images:', 'all-sources-images' ); ?></p>
                <p><img src="<?php echo plugin_dir_url( __FILE__ ) . '../../img/generation-step-1.png'; ?>" title="<?php esc_html_e( 'Choose your posts', 'all-sources-images' ); ?>" /></p>
                <p><img src="<?php echo plugin_dir_url( __FILE__ ) . '../../img/generation-step-2.png'; ?>" title="<?php esc_html_e( 'Generate images', 'all-sources-images' ); ?>" /></p>
        <?php } ?>

</div>
