<?php $activated = get_option("bbt_".THEME_FOLDER_NAME."_license"); ?>
<?php if($activated == ''): ?>
    <div class="settings-error notice">
        <br>
        <h2>Please enter your product key to be able to import demo content - <?php echo sprintf( __( 'Please visit <a href="%1$s" target="_blank">Product Activation</a> to activate your theme.', 'BigBangThemesFramework' ), admin_url( 'admin.php?page=bbt_product_key_page' ) );?></h2>
        <br>
    </div>
<?php else: ?>
<div class="settings-error notice">
	<br>
	<strong>In case your demo import doesn't work, please make sure to contact your web hosting support and ask them to check the following limits in PHP.ini and make sure they match:
	<br><br>
	<span style="color: #0073aa;">max_execution_time</span> = <span style="color: #dc3232;">300</span><br>
	<span style="color: #0073aa;">memory_limit</span> = <span style="color: #dc3232;">256M</span><br>
	<span style="color: #0073aa;">post_max_size</span> = <span style="color: #dc3232;">32M</span><br>
	<span style="color: #0073aa;">upload_max_filesize</span> = <span style="color: #dc3232;">32M</span><br>
	<span style="color: #0073aa;">max_input_vars</span> = <span style="color: #dc3232;">2000</span><br>
	</strong>
	<br>
</div>
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
						<?php // esc_html_e('If you already have posts, pages, and categories setup in your wordpress skip the import. It will overite your data..','BigBangThemesFramework')"?>
						<form action="admin.php?page=bbt_demo_content&paged=import" method="post">
							<?php wp_nonce_field( 'bbt_demo_import_nonce_action', 'bbt_demo_import_nonce_field' ); ?>
							<input type="hidden" name="bbt_demo_id" value="<?php echo esc_attr($id);?>">
							<button class="button button-primary" >
								<?php esc_html_e('Install','BigBangThemesFramework'); ?>
							</button>
						</form>
					</div>
				</div>
			<?php endforeach;?>

		<?php else: ?>
			<div class="bbt_popup_description"><?php esc_html_e('Hey! It seems that we forgot to include the demo content. Can you please let us know asap at','BigBangThemesFramework'); ?> <a href="https://www.bigbangthemes.net/contact-us/">https://www.bigbangthemes.net/contact-us/</a> ?</div>
		<?php endif;?>
	</div>
	<!--<div id="bbt_popup">
		<div class="bbt_popup_content">
			<span class="bbt_close_icon dashicons dashicons-no"></span>

			<h2 class="bbt_popup_title">
				<span class="spinner is-active"></span>
				<span class="dashicons dashicons-no"></span>
				<span class="dashicons dashicons-yes"></span>
				<?php /*esc_html_e('Installing','BigBangThemesFramework'); */?>
			</h2>
			<div class="bbt_popup_description"><?php /*esc_html_e('We are currently installing your content.','BigBangThemesFramework'); */?></div>

			<div id="bbt_popup_action" class="bbt_popup_description"
			     data-begin="<?php /*esc_html_e('Installing Demo Content...','BigBangThemesFramework'); */?>"
			     data-time="<?php /*esc_html_e('Please wait and do not refresh your page.','BigBangThemesFramework'); */?>"
				 data-estimated-time="<?php /*esc_html_e('Estimated time - up to 10 min','BigBangThemesFramework'); */?>"
			     data-timer="<?php /*esc_html_e('Elapsed Time','BigBangThemesFramework'); */?>:  ">
			</div>
		</div>
	</div>-->
</div>
<?php endif; ?>