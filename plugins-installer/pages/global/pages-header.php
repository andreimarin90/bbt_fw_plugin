<?php 
	$active_page 		= isset($_GET["page"]) ? $_GET["page"] : 'bbt_welcome_theme';

	$registration_page 	= "bbt_product_key_page";
	$tools_page 		= "bbt_welcome_theme";
	
	$getbowtied_settings = array('theme_docs','customize_link','release_notes')//Getbowtied_Admin_Pages::settings();

?>
<h1>
	<?php echo bbt_parent_theme_name(); ?>
	<?php if(defined('BBT_THEME_DOCS')):?>
		<a class="button" href="<?php echo esc_url(BBT_THEME_DOCS); ?>" target="_blank"><span class="dashicons dashicons-info"></span> <?php esc_html_e("Documentation", "BigBangThemesFramework"); ?></a>
	<?php endif;?>
	<?php if ( is_plugin_active( 'toco/toco.php' )): ?>
		<a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=bigbangthemes_settings' )); ?>"><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e("Customize", "BigBangThemesFramework"); ?></a>
	<?php endif;?>
</h1>
<p class="version">
	<a href="<?php echo ''; ?>" target="_blank">
		<span class="dashicons dashicons-update"></span> 
		<?php esc_html_e( "Version", "BigBangThemesFramework" ); ?> <?php bbt_print(BBT_Plugin_Installer::bbt_theme_version()); ?>
	</a>
</p>

<h2 class="nav-tab-wrapper bbt-tab-wrapper">
	<?php
	printf( '<a href="%s" class="nav-tab ' . ($active_page == $tools_page ? 		'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-settings"></span> %s</a>', admin_url( 'admin.php?page=' . $tools_page ), 		esc_html__( "Tools", "BigBangThemesFramework" ) );
	printf( '<a href="%s" class="nav-tab ' . ($active_page == $registration_page ? 	'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-network"></span> %s</a>', admin_url( 'admin.php?page=' . $registration_page ), 	esc_html__( "Product Key", "BigBangThemesFramework" ) );
	?>
</h2>