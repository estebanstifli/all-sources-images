<?php
/**
 * Bulk Generation - Create New Job Tab
 * 
 * Interface to select posts/pages/products for bulk image generation
 *
 * @package All_Sources_Images
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if WooCommerce is active
$has_woocommerce = class_exists( 'WooCommerce' );

// Get configured post types from settings
$options = get_option( 'ASI_plugin_main_settings' );
if ( isset( $this ) && method_exists( $this, 'ASI_default_options_main_settings' ) ) {
    $options = wp_parse_args( $options, $this->ASI_default_options_main_settings() );
}

// Get categories for posts
$post_categories = get_categories( array( 'hide_empty' => false ) );

// Get categories for products if WooCommerce is active
$product_categories = array();
if ( $has_woocommerce ) {
    $product_categories = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ) );
}
?>

<!-- TAB: Create New Job -->
<div class="tab-pane fade show active" id="create-job" role="tabpanel" aria-labelledby="create-job-tab">
    
    <?php
    // Get image blocks count for hidden field
    $image_blocks = isset( $options['image_block'] ) ? $options['image_block'] : array();
    $block_count = max( 1, count( $image_blocks ) );
    ?>

    <!-- Select Sources Card -->
    <div class="card card100 shadow-sm mb-4">
        <!-- Hidden fields for job creation -->
        <input type="hidden" id="asi-job-name" name="job_name" value="">
        <input type="hidden" id="asi-images-per-post" name="images_per_post" value="<?php echo esc_attr( $block_count ); ?>">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="bi bi-check2-square me-2 text-secondary"></i><?php esc_html_e( 'Select Content', 'all-sources-images' ); ?>
            </h5>

            <!-- POSTS Section -->
            <div class="mb-4 border-bottom pb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-file-text me-2 text-primary"></i>
                    <span class="fw-semibold"><?php esc_html_e( 'Posts', 'all-sources-images' ); ?></span>
                </div>
                <div class="d-flex gap-3 flex-wrap mb-2">
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_posts_mode" value="all" class="asi-mode-checkbox" data-type="post">
                        <span><?php esc_html_e( 'All Posts', 'all-sources-images' ); ?></span>
                    </label>
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_posts_mode" value="no_featured" class="asi-mode-checkbox" data-type="post">
                        <span><?php esc_html_e( 'Without Featured Image', 'all-sources-images' ); ?></span>
                    </label>
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_posts_mode" value="custom" class="asi-mode-checkbox" data-type="post">
                        <span><?php esc_html_e( 'Custom Selection', 'all-sources-images' ); ?></span>
                    </label>
                </div>

                <!-- Accordion for custom selection -->
                <div class="accordion" id="asi-post-accordion" style="display:none;">
                    <div class="accordion-item" data-post-type="post">
                        <h2 class="accordion-header" id="heading-post">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-post" aria-expanded="false" aria-controls="collapse-post">
                                <?php esc_html_e( 'Select Posts', 'all-sources-images' ); ?>
                            </button>
                        </h2>
                        <div id="collapse-post" class="accordion-collapse collapse" aria-labelledby="heading-post" data-bs-parent="#asi-post-accordion">
                            <div class="accordion-body">
                                <!-- Sub-tabs -->
                                <ul class="nav nav-tabs mb-3" id="tabs-post" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="recent-tab-post" data-bs-toggle="tab" data-bs-target="#recent-post" type="button" role="tab" aria-controls="recent-post" aria-selected="true">
                                            <i class="bi bi-clock-history me-1"></i><?php esc_html_e( 'Most Recent', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="all-tab-post" data-bs-toggle="tab" data-bs-target="#all-post" type="button" role="tab" aria-controls="all-post" aria-selected="false">
                                            <i class="bi bi-card-list me-1"></i><?php esc_html_e( 'View All', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="search-tab-post" data-bs-toggle="tab" data-bs-target="#search-post" type="button" role="tab" aria-controls="search-post" aria-selected="false">
                                            <i class="bi bi-search me-1"></i><?php esc_html_e( 'Search', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="category-tab-post" data-bs-toggle="tab" data-bs-target="#category-post" type="button" role="tab" aria-controls="category-post" aria-selected="false">
                                            <i class="bi bi-folder me-1"></i><?php esc_html_e( 'Categories', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content" id="tabs-content-post">
                                    <!-- Recent -->
                                    <div class="tab-pane fade show active" id="recent-post" role="tabpanel" aria-labelledby="recent-tab-post">
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#recent-items-post"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="recent-items-post" class="asi-items"></div>
                                    </div>
                                    <!-- All -->
                                    <div class="tab-pane fade" id="all-post" role="tabpanel" aria-labelledby="all-tab-post">
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#all-items-post"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="all-items-post" class="asi-items"></div>
                                        <div id="all-pagination-post" class="asi-pagination mt-2"></div>
                                    </div>
                                    <!-- Search -->
                                    <div class="tab-pane fade" id="search-post" role="tabpanel" aria-labelledby="search-tab-post">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="search-input-post" class="form-control asi-search-input" placeholder="<?php esc_attr_e( 'Search posts...', 'all-sources-images' ); ?>" data-post-type="post">
                                        </div>
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#search-items-post"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="search-items-post" class="asi-items"></div>
                                        <div id="search-pagination-post" class="asi-pagination mt-2"></div>
                                    </div>
                                    <!-- Categories -->
                                    <div class="tab-pane fade" id="category-post" role="tabpanel" aria-labelledby="category-tab-post">
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <select id="category-select-post" class="form-select asi-category-select" data-post-type="post">
                                                    <option value=""><?php esc_html_e( '-- Select Category --', 'all-sources-images' ); ?></option>
                                                    <?php foreach ( $post_categories as $cat ) : ?>
                                                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#category-items-post"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="category-items-post" class="asi-items"></div>
                                        <div id="category-pagination-post" class="asi-pagination mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAGES Section -->
            <div class="mb-4 border-bottom pb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-file-earmark-text me-2 text-success"></i>
                    <span class="fw-semibold"><?php esc_html_e( 'Pages', 'all-sources-images' ); ?></span>
                </div>
                <div class="d-flex gap-3 flex-wrap mb-2">
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_pages_mode" value="all" class="asi-mode-checkbox" data-type="page">
                        <span><?php esc_html_e( 'All Pages', 'all-sources-images' ); ?></span>
                    </label>
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_pages_mode" value="no_featured" class="asi-mode-checkbox" data-type="page">
                        <span><?php esc_html_e( 'Without Featured Image', 'all-sources-images' ); ?></span>
                    </label>
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_pages_mode" value="custom" class="asi-mode-checkbox" data-type="page">
                        <span><?php esc_html_e( 'Custom Selection', 'all-sources-images' ); ?></span>
                    </label>
                </div>

                <!-- Accordion for custom selection -->
                <div class="accordion" id="asi-page-accordion" style="display:none;">
                    <div class="accordion-item" data-post-type="page">
                        <h2 class="accordion-header" id="heading-page">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-page" aria-expanded="false" aria-controls="collapse-page">
                                <?php esc_html_e( 'Select Pages', 'all-sources-images' ); ?>
                            </button>
                        </h2>
                        <div id="collapse-page" class="accordion-collapse collapse" aria-labelledby="heading-page" data-bs-parent="#asi-page-accordion">
                            <div class="accordion-body">
                                <!-- Sub-tabs -->
                                <ul class="nav nav-tabs mb-3" id="tabs-page" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="recent-tab-page" data-bs-toggle="tab" data-bs-target="#recent-page" type="button" role="tab" aria-controls="recent-page" aria-selected="true">
                                            <i class="bi bi-clock-history me-1"></i><?php esc_html_e( 'Most Recent', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="all-tab-page" data-bs-toggle="tab" data-bs-target="#all-page" type="button" role="tab" aria-controls="all-page" aria-selected="false">
                                            <i class="bi bi-card-list me-1"></i><?php esc_html_e( 'View All', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="search-tab-page" data-bs-toggle="tab" data-bs-target="#search-page" type="button" role="tab" aria-controls="search-page" aria-selected="false">
                                            <i class="bi bi-search me-1"></i><?php esc_html_e( 'Search', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content" id="tabs-content-page">
                                    <!-- Recent -->
                                    <div class="tab-pane fade show active" id="recent-page" role="tabpanel" aria-labelledby="recent-tab-page">
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#recent-items-page"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="recent-items-page" class="asi-items"></div>
                                    </div>
                                    <!-- All -->
                                    <div class="tab-pane fade" id="all-page" role="tabpanel" aria-labelledby="all-tab-page">
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#all-items-page"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="all-items-page" class="asi-items"></div>
                                        <div id="all-pagination-page" class="asi-pagination mt-2"></div>
                                    </div>
                                    <!-- Search -->
                                    <div class="tab-pane fade" id="search-page" role="tabpanel" aria-labelledby="search-tab-page">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="search-input-page" class="form-control asi-search-input" placeholder="<?php esc_attr_e( 'Search pages...', 'all-sources-images' ); ?>" data-post-type="page">
                                        </div>
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#search-items-page"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="search-items-page" class="asi-items"></div>
                                        <div id="search-pagination-page" class="asi-pagination mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRODUCTS Section (WooCommerce) -->
            <?php if ( $has_woocommerce ) : ?>
            <div class="mb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-bag-check me-2 text-warning"></i>
                    <span class="fw-semibold"><?php esc_html_e( 'Products', 'all-sources-images' ); ?></span>
                    <span class="badge bg-info ms-2">WooCommerce</span>
                </div>
                <div class="d-flex gap-3 flex-wrap mb-2">
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_products_mode" value="all" class="asi-mode-checkbox" data-type="product">
                        <span><?php esc_html_e( 'All Products', 'all-sources-images' ); ?></span>
                    </label>
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_products_mode" value="no_featured" class="asi-mode-checkbox" data-type="product">
                        <span><?php esc_html_e( 'Without Featured Image', 'all-sources-images' ); ?></span>
                    </label>
                    <label class="d-flex align-items-center gap-2 m-0">
                        <input type="checkbox" name="asi_select_products_mode" value="custom" class="asi-mode-checkbox" data-type="product">
                        <span><?php esc_html_e( 'Custom Selection', 'all-sources-images' ); ?></span>
                    </label>
                </div>

                <!-- Accordion for custom selection -->
                <div class="accordion" id="asi-product-accordion" style="display:none;">
                    <div class="accordion-item" data-post-type="product">
                        <h2 class="accordion-header" id="heading-product">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-product" aria-expanded="false" aria-controls="collapse-product">
                                <?php esc_html_e( 'Select Products', 'all-sources-images' ); ?>
                            </button>
                        </h2>
                        <div id="collapse-product" class="accordion-collapse collapse" aria-labelledby="heading-product" data-bs-parent="#asi-product-accordion">
                            <div class="accordion-body">
                                <!-- Sub-tabs -->
                                <ul class="nav nav-tabs mb-3" id="tabs-product" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="recent-tab-product" data-bs-toggle="tab" data-bs-target="#recent-product" type="button" role="tab" aria-controls="recent-product" aria-selected="true">
                                            <i class="bi bi-clock-history me-1"></i><?php esc_html_e( 'Most Recent', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="all-tab-product" data-bs-toggle="tab" data-bs-target="#all-product" type="button" role="tab" aria-controls="all-product" aria-selected="false">
                                            <i class="bi bi-card-list me-1"></i><?php esc_html_e( 'View All', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="search-tab-product" data-bs-toggle="tab" data-bs-target="#search-product" type="button" role="tab" aria-controls="search-product" aria-selected="false">
                                            <i class="bi bi-search me-1"></i><?php esc_html_e( 'Search', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="category-tab-product" data-bs-toggle="tab" data-bs-target="#category-product" type="button" role="tab" aria-controls="category-product" aria-selected="false">
                                            <i class="bi bi-folder me-1"></i><?php esc_html_e( 'Categories', 'all-sources-images' ); ?>
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content" id="tabs-content-product">
                                    <!-- Recent -->
                                    <div class="tab-pane fade show active" id="recent-product" role="tabpanel" aria-labelledby="recent-tab-product">
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#recent-items-product"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="recent-items-product" class="asi-items"></div>
                                    </div>
                                    <!-- All -->
                                    <div class="tab-pane fade" id="all-product" role="tabpanel" aria-labelledby="all-tab-product">
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#all-items-product"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="all-items-product" class="asi-items"></div>
                                        <div id="all-pagination-product" class="asi-pagination mt-2"></div>
                                    </div>
                                    <!-- Search -->
                                    <div class="tab-pane fade" id="search-product" role="tabpanel" aria-labelledby="search-tab-product">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="search-input-product" class="form-control asi-search-input" placeholder="<?php esc_attr_e( 'Search products...', 'all-sources-images' ); ?>" data-post-type="product">
                                        </div>
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#search-items-product"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="search-items-product" class="asi-items"></div>
                                        <div id="search-pagination-product" class="asi-pagination mt-2"></div>
                                    </div>
                                    <!-- Categories -->
                                    <div class="tab-pane fade" id="category-product" role="tabpanel" aria-labelledby="category-tab-product">
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <select id="category-select-product" class="form-select asi-category-select" data-post-type="product">
                                                    <option value=""><?php esc_html_e( '-- Select Category --', 'all-sources-images' ); ?></option>
                                                    <?php if ( ! is_wp_error( $product_categories ) ) : ?>
                                                        <?php foreach ( $product_categories as $cat ) : ?>
                                                            <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)</option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <label class="d-block mb-2">
                                            <input type="checkbox" class="asi-select-all me-1" data-target="#category-items-product"> <?php esc_html_e( 'Select All', 'all-sources-images' ); ?>
                                        </label>
                                        <div id="category-items-product" class="asi-items"></div>
                                        <div id="category-pagination-product" class="asi-pagination mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else : ?>
            <div class="mb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-bag-check me-2 text-muted"></i>
                    <span class="fw-semibold text-muted"><?php esc_html_e( 'Products', 'all-sources-images' ); ?></span>
                    <span class="badge bg-secondary ms-2"><?php esc_html_e( 'WooCommerce not active', 'all-sources-images' ); ?></span>
                </div>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <?php esc_html_e( 'Install and activate WooCommerce to enable product image generation.', 'all-sources-images' ); ?>
                </p>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Selection Summary & Actions Card -->
    <div class="card card100 shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-list-check me-2 text-secondary"></i><?php esc_html_e( 'Selection Summary', 'all-sources-images' ); ?>
                    </h5>
                    <div id="asi-selection-summary" class="p-3 bg-light rounded">
                        <span class="text-muted"><?php esc_html_e( 'No content selected yet.', 'all-sources-images' ); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-play-circle me-2 text-secondary"></i><?php esc_html_e( 'Actions', 'all-sources-images' ); ?>
                    </h5>
                    <button type="button" id="asi-create-job-btn" class="btn btn-primary btn-lg" disabled>
                        <i class="bi bi-plus-circle me-2"></i><?php esc_html_e( 'Create Job', 'all-sources-images' ); ?>
                    </button>
                    <button type="button" id="asi-create-start-btn" class="btn btn-success btn-lg ms-2" disabled>
                        <i class="bi bi-play-fill me-2"></i><?php esc_html_e( 'Create & Start', 'all-sources-images' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
