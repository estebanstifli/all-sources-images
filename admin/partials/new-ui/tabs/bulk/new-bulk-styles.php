<?php
/**
 * Bulk Generation - Styles
 * 
 * This file is deprecated. Styles are now loaded via wp_enqueue_style() in new-ui-assets.php
 * The CSS file is located at admin/css/asi-bulk-generation.css
 *
 * @package All_Sources_Images
 * @deprecated Use wp_enqueue_style( 'asi-bulk-generation' ) instead
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

// Styles are now enqueued via wp_enqueue_style() in new-ui-assets.php
// See: admin/css/asi-bulk-generation.css
