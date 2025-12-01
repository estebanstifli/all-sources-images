<?php
/**
 * Bulk Generation - Jobs List Tab
 * 
 * View and manage existing bulk generation jobs
 *
 * @package All_Sources_Images
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}
?>

<!-- TAB: Jobs List -->
<div class="tab-pane fade" id="jobs-list" role="tabpanel" aria-labelledby="jobs-list-tab">
    
    <div class="card card100 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title m-0">
                    <i class="bi bi-list-task me-2 text-secondary"></i><?php esc_html_e( 'Generation Jobs', 'magic-post-thumbnail' ); ?>
                </h5>
                <button type="button" id="asi-refresh-jobs" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i><?php esc_html_e( 'Refresh', 'magic-post-thumbnail' ); ?>
                </button>
            </div>

            <div id="asi-jobs-table-container">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'ID', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Job Name', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Progress', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Created', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'magic-post-thumbnail' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="asi-jobs-tbody">
                        <tr>
                            <td colspan="6" class="text-center text-muted"><?php esc_html_e( 'No jobs found.', 'magic-post-thumbnail' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="asi-jobs-pagination" class="mt-3"></div>
        </div>
    </div>

    <!-- Job Details Modal/Panel -->
    <div id="asi-job-details" class="card card100 shadow-sm mb-4" style="display:none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title m-0">
                    <i class="bi bi-info-circle me-2 text-secondary"></i><span id="asi-job-details-title"><?php esc_html_e( 'Job Details', 'magic-post-thumbnail' ); ?></span>
                </h5>
                <button type="button" id="asi-close-job-details" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x me-1"></i><?php esc_html_e( 'Close', 'magic-post-thumbnail' ); ?>
                </button>
            </div>

            <!-- Progress -->
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span><?php esc_html_e( 'Progress', 'magic-post-thumbnail' ); ?></span>
                    <span id="asi-job-progress-text">0%</span>
                </div>
                <div class="progress" style="height: 20px;">
                    <div id="asi-job-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-primary" id="asi-job-stat-total">0</div>
                        <small class="text-muted"><?php esc_html_e( 'Total', 'magic-post-thumbnail' ); ?></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-warning" id="asi-job-stat-pending">0</div>
                        <small class="text-muted"><?php esc_html_e( 'Pending', 'magic-post-thumbnail' ); ?></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-success" id="asi-job-stat-completed">0</div>
                        <small class="text-muted"><?php esc_html_e( 'Completed', 'magic-post-thumbnail' ); ?></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-danger" id="asi-job-stat-failed">0</div>
                        <small class="text-muted"><?php esc_html_e( 'Failed', 'magic-post-thumbnail' ); ?></small>
                    </div>
                </div>
            </div>

            <!-- Posts Table -->
            <h6><?php esc_html_e( 'Posts in this job', 'magic-post-thumbnail' ); ?></h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Post', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Type', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Images', 'magic-post-thumbnail' ); ?></th>
                            <th><?php esc_html_e( 'Source', 'magic-post-thumbnail' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="asi-job-posts-tbody">
                    </tbody>
                </table>
            </div>
            <div id="asi-job-posts-pagination" class="mt-2"></div>
        </div>
    </div>

</div>
