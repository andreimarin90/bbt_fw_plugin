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
<<<<<<< HEAD
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
=======
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
>>>>>>> a5606e3c4d920fa7d360c6ba2d6010efa0f4f74a
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
<<<<<<< HEAD

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

=======
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
                            <?php foreach( $plugin_action as $action ) { bbt_print($action); } ?>
                        </div>

                        <?php if( isset( $plugin_action['update'] ) && $plugin_action['update'] ): ?>
                        <div class="plugin-update"><?php esc_html_e("New Update Available: Version ", "BigBangThemesFramework"); ?> <?php echo esc_html($plugin['version']); ?></div>
                        <?php endif; ?>
                    </div>
>>>>>>> a5606e3c4d920fa7d360c6ba2d6010efa0f4f74a
				</div>

			<?php endforeach; ?>
		</div>
	</div>

</div>




