<div class="stripe_top" style="background-color: #0085ba"></div>
<?php
$plugins = TGM_Plugin_Activation::$instance->plugins;
$installed_plugins = get_plugins();
//bbt_print_r($plugins);
?>

<div class="wrap about-wrap bbt-about-wrap bbt-tools">

    <?php include_once('global/pages-header.php'); ?>

    <!-- PLUGINS -->
	<div class="bbt-install-plugins">
		<div class="header-section">
			<h2> <?php esc_html_e("Plugins", "BigBangThemesFramework"); ?> </h2>
			<a href="javascript:void(0)" class="plugin-tab-switch required active"><?php esc_html_e("Required Plugins", "BigBangThemesFramework"); ?></a>
			<a href="javascript:void(0)" class="plugin-tab-switch recommended"><?php echo esc_html_e("Recommended Plugins", "BigBangThemesFramework"); ?></a>
			<a href="#" target="_blank" class="video-guide"><span class="dashicons dashicons-video-alt3"></span>
				<?php echo sprintf(
				esc_html__('Installation & Setup %1$s Video Guide','BigBangThemesFramework'),
				'<span class=\'dashicons dashicons-minus\'></span>'); ?>
			</a>
			<div class="clear"></div>
		</div>		
		<div class="bbt-plugin-browser rendered required">
			
			<?php
			foreach( $plugins as $plugin ):
				if (!$plugin['required']) continue;
				if($plugin['slug'] == 'bbt_fw_plugin') continue;

				$class = '';
				$plugin_status = '';
				$file_path = $plugin['file_path'];
				$plugin_action = $this->bbt_theme_plugin_links( $plugin );

				if( is_plugin_active( $file_path ) ) {
					$plugin_status = 'active';
					$class = 'active';
				}
			?>
			
				<div class="bbt-plugin <?php echo esc_attr($class); ?>">

					<div class="theme-screenshot">
						<!--de comendat-->
						<?php echo esc_html($plugin['name']);?>
						<img src="<?php echo esc_url($plugin['external_image']); ?>" alt="<?php echo esc_attr($plugin['name']);?>" />
					</div>

					<?php if (isset($installed_plugins[$plugin['file_path']]['Version'])): ?>
					<div class="plugin-version">
						<?php echo sprintf('V. %s', $installed_plugins[$plugin['file_path']]['Version'] ); ?>
					</div>
					<?php endif; ?>

					<div class="theme-actions">
						<?php foreach( $plugin_action as $action ) { bbt_print($action); } ?>
					</div>

					<?php if( isset( $plugin_action['update'] ) && $plugin_action['update'] ): ?>
					<div class="plugin-update"><span class="dashicons dashicons-update"></span> <?php esc_html_e("New Update Available: Version ", "BigBangThemesFramework"); ?> <?php echo esc_html($plugin['version']); ?></div>
					<?php endif; ?>

				</div>

			<?php endforeach; ?>
		</div>

		<div class="bbt-plugin-browser rendered recommended">
			
			<?php
			foreach( $plugins as $plugin ):
				if ($plugin['required']) continue;
				if($plugin['slug'] == 'bbt_fw_plugin') continue;

				$class = '';
				$plugin_status = '';
				$file_path = $plugin['file_path'];
				$plugin_action = $this->bbt_theme_plugin_links( $plugin );

				if( is_plugin_active( $file_path ) ) {
					$plugin_status = 'active';
					$class = 'active';
				}
			?>
			
				<div class="bbt-plugin <?php echo esc_attr($class); ?>">

					<div class="theme-screenshot">
						<!--de comentat-->
						<?php echo esc_html($plugin['name']);?>
						<img src="<?php echo esc_url($plugin['external_image']); ?>" alt="<?php echo esc_attr($plugin['name']);?>" />
					</div>

					<?php if (isset($installed_plugins[$plugin['file_path']]['Version'])): ?>
					<div class="plugin-version">
						<?php echo sprintf('V. %s', $installed_plugins[$plugin['file_path']]['Version'] ); ?>
					</div>
					<?php endif; ?>

					<div class="theme-actions">
						<?php foreach( $plugin_action as $action ) { bbt_print($action); } ?>
					</div>

					<?php if( isset( $plugin_action['update'] ) && $plugin_action['update'] ): ?>
						<div class="plugin-update"><span class="dashicons dashicons-update"></span> <?php esc_html_e("New Update Available: Version ", "BigBangThemesFramework"); ?> <?php echo esc_html($plugin['version']); ?></div>
					<?php endif; ?>

				</div>

			<?php endforeach; ?>
		</div>
	</div>

</div>




