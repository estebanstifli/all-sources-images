<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine active tab for styling
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab parameter is used for display purposes only, no data modification
$allsi_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'source';
?>
<div class="wrap main-mpt">
	<!--begin::Main-->
	<div class="d-flex flex-column flex-root">
		<!--begin::Page-->
		<div class="d-flex flex-row flex-column-fluid page">
			<!--begin::Wrapper (full width, no sidebar)-->
			<div class="d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper" style="padding-left: 0;">

				<!--begin::Content-->
				<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

					<!--begin::Entry-->
					<div class="d-flex flex-column-fluid">
						<!--begin::Container-->
						<div class="container">
							<div class="row">
								<div class="col-md-12">

									<div class="alert alert-custom alert-primary alert-shadow fade show mb-5 rounded" role="alert">
										<div class="alert-icon">
											<span class="dashicons dashicons-format-image text-white icon-2x" aria-hidden="true"></span>
										</div>
										<div class="alert-text">
											<div class="font-size-h3 font-weight-bold text-white mb-1">
												<?php esc_html_e( 'All Sources Images', 'all-sources-images' ); ?>
											</div>
											<div class="text-white">
												<?php esc_html_e( 'You can use it in several places: Media > All Sources Images, the ASI Images block in Gutenberg, or the ASI Image widget in Elementor.', 'all-sources-images' ); ?>
											</div>
										</div>
									</div>

									<!-- Tabs BEFORE the card -->
									<ul class="nav nav-tabs nav-tabs-line nav-tabs-line-2x nav-tabs-primary" role="tablist" style="margin-bottom: 0; border-bottom: none;">
										<li class="nav-item">
											<a class="nav-link <?php echo esc_attr( $allsi_active_tab === 'source' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=allsi-new-settings&tab=source' ) ); ?>">
												<?php esc_html_e( 'Source', 'all-sources-images' ); ?>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link <?php echo esc_attr( $allsi_active_tab === 'proxy' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=allsi-new-settings&tab=proxy' ) ); ?>">
												<?php esc_html_e( 'Proxy', 'all-sources-images' ); ?>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link <?php echo esc_attr( $allsi_active_tab === 'others' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=allsi-new-settings&tab=others' ) ); ?>">
												<?php esc_html_e( 'Others', 'all-sources-images' ); ?>
											</a>
										</li>
									</ul>

									<!--begin::Card-->
									<div class="card card-custom" style="border-top-left-radius: 0;">
