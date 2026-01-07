<?php
/**
 * Bulk Generation - Jobs List Tab
 * 
 * View and manage existing bulk generation jobs
 *
 * @package All_Sources_Images
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- TAB: Jobs List -->
<div class="tab-pane fade" id="jobs-list" role="tabpanel" aria-labelledby="jobs-list-tab">
    
    <div class="card card100 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title m-0">
                    <i class="bi bi-list-task me-2 text-secondary"></i><?php esc_html_e( 'Generation Jobs', 'all-sources-images' ); ?>
                </h5>
                <button type="button" id="allsi-refresh-jobs" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i><?php esc_html_e( 'Refresh', 'all-sources-images' ); ?>
                </button>
            </div>

            <div id="allsi-jobs-table-container">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'ID', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Job Name', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Progress', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Created', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'all-sources-images' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="allsi-jobs-tbody">
                        <tr>
                            <td colspan="6" class="text-center text-muted"><?php esc_html_e( 'No jobs found.', 'all-sources-images' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="allsi-jobs-pagination" class="mt-3"></div>
        </div>
    </div>

    <!-- Job Details Modal/Panel -->
    <div id="allsi-job-details" class="card card100 shadow-sm mb-4" style="display:none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title m-0">
                    <i class="bi bi-info-circle me-2 text-secondary"></i><span id="allsi-job-details-title"><?php esc_html_e( 'Job Details', 'all-sources-images' ); ?></span>
                </h5>
                <button type="button" id="allsi-close-job-details" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x me-1"></i><?php esc_html_e( 'Close', 'all-sources-images' ); ?>
                </button>
            </div>

            <!-- Progress -->
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span><?php esc_html_e( 'Progress', 'all-sources-images' ); ?></span>
                    <span id="allsi-job-progress-text">0%</span>
                </div>
                <div class="progress" style="height: 20px;">
                    <div id="allsi-job-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-primary" id="allsi-job-stat-total">0</div>
                        <small class="text-muted"><?php esc_html_e( 'Total', 'all-sources-images' ); ?></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-warning" id="allsi-job-stat-pending">0</div>
                        <small class="text-muted"><?php esc_html_e( 'Pending', 'all-sources-images' ); ?></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-success" id="allsi-job-stat-completed">0</div>
                        <small class="text-muted"><?php esc_html_e( 'Completed', 'all-sources-images' ); ?></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-danger" id="allsi-job-stat-failed">0</div>
                        <small class="text-muted"><?php esc_html_e( 'Failed', 'all-sources-images' ); ?></small>
                    </div>
                </div>
            </div>

            <!-- Posts Table -->
            <h6><?php esc_html_e( 'Posts in this job', 'all-sources-images' ); ?></h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Post', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Type', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Images', 'all-sources-images' ); ?></th>
                            <th><?php esc_html_e( 'Source', 'all-sources-images' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="allsi-job-posts-tbody">
                    </tbody>
                </table>
            </div>
            <div id="allsi-job-posts-pagination" class="mt-2"></div>
        </div>
    </div>

</div>
