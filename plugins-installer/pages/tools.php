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
		<!--<div class="header-section">
			<h2> <?php /*esc_html_e("Plugins", "BigBangThemesFramework"); */?> </h2>
			<a href="#" target="_blank" class="video-guide"><span class="dashicons dashicons-video-alt3"></span>
				<?php /*echo sprintf(
				esc_html__('Installation & Setup %1$s Video Guide','BigBangThemesFramework'),
				'<span class=\'dashicons dashicons-minus\'></span>'); */?>
			</a>
			<div class="clear"></div>
		</div>-->

		<div class="bbt-plugin-browser rendered clearfix">

			<?php
			foreach( $plugins as $plugin ):
				//if (!$plugin['required']) continue;
				//if($plugin['slug'] == 'bbt_fw_plugin') continue;

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
					<div class="plugin-image">
						<!--<img src="<?php /*echo isset($plugin['external_image']) ? esc_url($plugin['external_image']) : ''; */?>" alt="<?php /*echo esc_attr($plugin['name']);*/?>" />-->
						<img src="<?php echo esc_url($plugin['external_image']); ?>" alt="<?php echo esc_attr($plugin['name']);?>" />
					</div>

					<div class="plugin-content">
						<h4 class="plugin-title"><?php echo esc_html($plugin['name']);?></h4>

						<?php if (isset($installed_plugins[$plugin['file_path']]['Version'])): ?>
							<div class="plugin-version">
								<span class="label">Version:</span>
								<span class="value"><?php echo sprintf('V. %s', $installed_plugins[$plugin['file_path']]['Version'] ); ?></span>
							</div>
						<?php endif; ?>

						<div class="plugin-description">
							<?php echo esc_html($plugin['description']);?>
						</div>

						<div class="plugin-author">
							<span class="label">Author:</span>
							<span class="value"><?php echo esc_html($plugin['author']);?></span>
						</div>

						<div class="plugin-buttons">
							<?php foreach( $plugin_action as $key => $action ) {
								if($plugin['slug'] == 'bbt_fw_plugin')
								{
									echo ($key == 'update') ? $action : '';
								}
								else
								{
									bbt_print($action);
								}
							} ?>
						</div>

						<?php if( isset( $plugin_action['update'] ) && $plugin_action['update'] ): ?>
							<div class="plugin-update"><?php esc_html_e("New Update Available: Version ", "BigBangThemesFramework"); ?> <?php echo esc_html($plugin['version']); ?></div>
						<?php endif; ?>
					</div>
				</div>

			<?php endforeach; ?>
		</div>
	</div>

</div>