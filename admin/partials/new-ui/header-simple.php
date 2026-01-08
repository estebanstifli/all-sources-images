<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine active tab for styling
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab parameter is used for display purposes only, no data modification
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'source';
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

					<!--begin::Subheader-->
					<div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
						<div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
							<!--begin::Info-->
							<div class="d-flex align-items-center flex-wrap mr-1">
								<!--begin::Page Heading-->
								<div class="d-flex align-items-baseline flex-wrap mr-5">
									<!--begin::Page Title-->
									<h5 class="text-dark font-weight-bold my-1 mr-5">All Sources Images : <?php echo esc_html( $title ); ?></h5>
									<!--end::Page Title-->
								</div>
								<!--end::Page Heading-->
							</div>
							<!--end::Info-->
						</div>
					</div>
					<!--end::Subheader-->

					<!--begin::Entry-->
					<div class="d-flex flex-column-fluid">
						<!--begin::Container-->
						<div class="container">
							<div class="row">
								<div class="col-md-12">

									<!-- Tabs BEFORE the card -->
									<ul class="nav nav-tabs nav-tabs-line nav-tabs-line-2x nav-tabs-primary" role="tablist" style="margin-bottom: 0; border-bottom: none;">
										<li class="nav-item">
											<a class="nav-link <?php echo esc_attr( $active_tab === 'source' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=allsi-new-settings&tab=source' ) ); ?>">
												<?php esc_html_e( 'Source', 'all-sources-images' ); ?>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link <?php echo esc_attr( $active_tab === 'proxy' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=allsi-new-settings&tab=proxy' ) ); ?>">
												<?php esc_html_e( 'Proxy', 'all-sources-images' ); ?>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link <?php echo esc_attr( $active_tab === 'others' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=allsi-new-settings&tab=others' ) ); ?>">
												<?php esc_html_e( 'Others', 'all-sources-images' ); ?>
											</a>
										</li>
									</ul>

									<!--begin::Card-->
									<div class="card card-custom" style="border-top-left-radius: 0;">
