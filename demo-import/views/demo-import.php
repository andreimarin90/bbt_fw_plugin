<div class="wrap">
	<h2><?php esc_html_e('BBT Demo Content','BigBangThemesFramework'); ?></h2>
	<div style="margin-top:15px;"></div>
	<div class="theme-browser rendered" id="bbt_demo_content_list">
		<?php if(!empty($configs)):?>
			<?php foreach($configs as $id => $demo_config):?>
				<div class="theme bbt-demo-item" id="">
					<div class="theme-screenshot">
						<?php if(isset($demo_config['screenshot']) && !empty($demo_config['screenshot'])):?>
							<img src="<?php echo esc_url($demo_config['screenshot']);?>" alt="<?php esc_html_e('Screenshot','BigBangThemesFramework'); ?>">
						<?php endif;?>
					</div>
					<?php if(isset($demo_config['preview_link']) && !empty($demo_config['preview_link'])):?>
						<a class="more-details" target="_blank" href="<?php echo esc_url($demo_config['preview_link']);?>">
							<?php esc_html_e('Live Preview','BigBangThemesFramework'); ?>
						</a>
					<?php endif;?>
					<h3 class="theme-name"><?php echo esc_html($demo_config['title']);?></h3>
					<div class="theme-actions">
						<a class="button button-primary" href="#" onclick="return false;"
						   data-confirm="<?php esc_html_e('If you already have posts, pages, and categories setup in your wordpress skip the import. It will overite your data..','BigBangThemesFramework')?>"
						   data-install="<?php echo esc_attr($id);?>">
							<?php esc_html_e('Install','BigBangThemesFramework'); ?>
						</a>
					</div>
				</div>
			<?php endforeach;?>
		<?php endif;?>
	</div>
	<div id="bbt_popup">
		<div class="bbt_popup_content">
			<span class="bbt_close_icon dashicons dashicons-no"></span>

			<h2 class="bbt_popup_title">
				<span class="spinner is-active"></span>
				<span class="dashicons dashicons-no"></span>
				<span class="dashicons dashicons-yes"></span>
				<?php esc_html_e('Installing','BigBangThemesFramework'); ?>
			</h2>
			<div class="bbt_popup_description"><?php esc_html_e('We are currently installing your content.','BigBangThemesFramework'); ?></div>

			<div id="bbt_popup_action" class="bbt_popup_description"
			     data-begin="<?php esc_html_e('Installing Demo Content...','BigBangThemesFramework'); ?>"
			     data-time="<?php esc_html_e('Please wait and do not refresh your page.','BigBangThemesFramework'); ?>"
			     data-timer="<?php esc_html_e('Elapsed Time','BigBangThemesFramework'); ?>:  ">
			</div>
		</div>
	</div>
	<div id="bbt_cover_popup"></div>
</div>