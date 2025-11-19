<!--begin::Aside-->
<div class="aside aside-left  aside-fixed  d-flex flex-column flex-row-auto" id="kt_aside">
	<!--begin::Brand-->
	<div class="brand flex-column-auto " id="kt_brand">
		<!--begin::Logo-->
    <a href="#" class="brand-logo">
        <!--<img alt="Logo" src="<?php echo plugin_dir_url( __FILE__ ) . '../img/icon-settings.png'; ?>" />-->
        <img alt="Logo Magic Post Thumbnail"  src="<?php echo plugin_dir_url( __FILE__ ) . '../img/logo.png'; ?>" />

    </a>
		<!--end::Logo-->
	</div>
	<!--end::Brand-->
	<!--begin::Aside Menu-->
	<div class="aside-menu-wrapper flex-column-fluid">
		<!--begin::Menu Container-->
		<div class="aside-menu my-4 " data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">
			<!--begin::Menu Nav-->
			<ul class="menu-nav ">

      			<?php $this->ASI_submenu( esc_html__( 'Dashboard', 'all-sources-images' ), 'dashboard', 'home.png' ); ?>

				<li class="menu-section ">
					<h4 class="menu-text"><?php esc_html_e( 'Settings', 'all-sources-images' ); ?></h4> <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
				</li>

				<?php $this->ASI_submenu( esc_html__( 'Source', 'all-sources-images' ), 'source', 'source.png' ); ?>
				<?php $this->ASI_submenu( esc_html__( 'Automatic', 'all-sources-images' ), 'automatic', 'automatic.png' ); ?>
				<?php $this->ASI_submenu( esc_html__( 'Gutenberg Block', 'all-sources-images' ), 'block', 'block.png' ); ?>

				<li class="menu-section ">
					<h4 class="menu-text"><?php esc_html_e( 'Advanced Features', 'all-sources-images' ); ?></h4>
				</li>

				<?php $this->ASI_submenu( esc_html__( 'Compatibility', 'all-sources-images' ), 'compatibility', 'compatibility.png' ); ?>
				<?php $this->ASI_submenu( esc_html__( 'Cron', 'all-sources-images' ), 'cron', 'cron.png' ); ?>
				<?php $this->ASI_submenu( esc_html__( 'Proxy', 'all-sources-images' ), 'proxy', 'proxy.png' ); ?>

				<li class="menu-section ">
					<h4 class="menu-text"><?php esc_html_e( 'Miscellaneous', 'all-sources-images' ); ?></h4>
				</li>

				<?php $this->ASI_submenu( esc_html__( 'Rights', 'all-sources-images' ), 'rights', 'rights.png' ); ?>
				<?php $this->ASI_submenu( esc_html__( 'Logs', 'all-sources-images' ), 'logs', 'logs.png' ); ?>

				<li class="menu-section ">
					<h4 class="menu-text"><?php esc_html_e( 'Generation', 'all-sources-images' ); ?></h4>
				</li>

				<?php
					$options = wp_parse_args( get_option( 'ASI_plugin_main_settings' ), $this->ASI_default_options_main_settings( TRUE ) );
					$value_bulk_generation_interval = ( isset( $options['bulk_generation_interval'] ) )? (int)$options['bulk_generation_interval'] : 0;

					if( ( 0 !== $value_bulk_generation_interval ) ) {
						$bulk_url = 'bulk-generation-interval';
					} else {
						$bulk_url = 'bulk-generation';
					}
				?>

        		<?php $this->ASI_submenu(esc_html__( 'Bulk Generation', 'all-sources-images' ), $bulk_url, 'generate.png' ); ?>

			</ul>
			<!--end::Menu Nav-->
		</div>
		<!--end::Menu Container-->
	</div>
	<!--end::Aside Menu-->
</div>
<!--end::Aside-->
