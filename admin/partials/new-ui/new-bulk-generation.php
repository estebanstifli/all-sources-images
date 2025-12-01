<?php
/**
 * New Bulk Generation Page
 * 
 * Main entry point for bulk generation feature
 * Part of the new admin UI structure
 *
 * @package All_Sources_Images
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

// Ensure tables exist
ASI_Bulk_Generation_DB::maybe_create_tables();

// Include the bulk generation create page
include_once plugin_dir_path( __FILE__ ) . 'tabs/new-bulk-create.php';
