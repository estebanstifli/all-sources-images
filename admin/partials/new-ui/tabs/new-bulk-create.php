<?php
/**
 * Bulk Generation Main Page
 * 
 * Main container that includes tabs for Create New Job and Jobs List
 *
 * @package All_Sources_Images
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap asi-bulk-generation-wrap">
    
    <!-- Header -->
    <div class="d-flex align-items-center mb-3">
        <i class="bi bi-images fs-3 me-2 text-primary"></i>
        <h1 class="m-0"><?php esc_html_e( 'Bulk Image Generation', 'all-sources-images' ); ?></h1>
    </div>

    <!-- Main Tabs -->
    <ul class="nav nav-tabs" id="bulk-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="create-job-tab" data-bs-toggle="tab" data-bs-target="#create-job" type="button" role="tab" aria-controls="create-job" aria-selected="true">
                <i class="bi bi-plus-circle me-1"></i> <?php esc_html_e( 'Create New Job', 'all-sources-images' ); ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="jobs-list-tab" data-bs-toggle="tab" data-bs-target="#jobs-list" type="button" role="tab" aria-controls="jobs-list" aria-selected="false">
                <i class="bi bi-list-task me-1"></i> <?php esc_html_e( 'Jobs List', 'all-sources-images' ); ?>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="bulk-tab-content">
        
        <?php 
        // Include Create New Job tab
        include plugin_dir_path( __FILE__ ) . 'bulk/new-bulk-create-tab.php';
        
        // Include Jobs List tab
        include plugin_dir_path( __FILE__ ) . 'bulk/new-bulk-jobs-tab.php';
        ?>

    </div>
</div>

<?php
// Include styles
include plugin_dir_path( __FILE__ ) . 'bulk/new-bulk-styles.php';
?>
