<div class="stripe_top" style="background-color: #0085ba;"></div>
<div class="wrap about-wrap bbt-about-wrap bbt-registration-wrap">

    <?php require_once('global/pages-header.php'); ?>

    <?php

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['bbt_product_key']))
        {
           $rsp = BBT_Plugin_Installer::bbt_validate_license(trim($_POST['bbt_product_key']));
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST['bbt_product_key']) && empty($_POST['action']))
        {
            $rsp = esc_html__('Please fill out the product key.','BigBangThemesFramework');
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'bbt-delkey')
        {
            delete_option("bbt_".THEME_FOLDER_NAME."_license");
            delete_option("bbt_". THEME_FOLDER_NAME ."_valid_key");
        }

    $activated = get_option("bbt_".THEME_FOLDER_NAME."_license");

    if ($activated == ''):
    ?>
    	<div class="bbt-registration-form">
            <div class="inner">
                <div class="center">
                    <p>
                        <?php echo sprintf(
                            esc_html__('Follow the steps below to validate your product. This will unlock automatic updates for your theme and %1$s all plugins included with the theme%2$s, additional Extensions, and full theme features.', 'BigBangThemesFramework'),
                            '<b>',
                            '</b>'
                        );
                        ?>
                    </p>
                </div>

                <!-- <div class="right">
                    <a href="#" class="button"><span class="dashicons dashicons-info"></span> <?php esc_html_e('Help with Product Keys', "BigBangThemesFramework"); ?></a>
                </div> -->

                <div class="clear"></div>
            </div>

            <?php
                if ( ! empty( $rsp['text'] ))
                {
                    echo '<div class="key_error">'.$rsp['text'].'</div>';
                }
            ?>

            <div class="inner steps clearfix <?php if (!empty($rsp['domain'])) echo "has-error"; ?>">
                <div class="step step-1">
                    <img src="<?php echo BBT_PL_URL . '/plugins-installer/img/step-1.png'; ?>" />
                    <div class="title"><?php esc_html_e('Step 1', "BigBangThemesFramework"); ?></div>
                    <div class="note"><?php esc_html_e('Generate a product key for this domain name.', "BigBangThemesFramework"); ?></div>
                    <a class="button generate" href="<?php echo esc_url(BBT_Plugin_Installer::$bbt_api_url); ?>?page=license&ref=<?php echo urlencode(site_url()); ?>" target="_blank"><?php echo __('Generate Key', "BigBangThemesFramework"); ?></a>
                </div>

                <div class="step step-1 step-error">
                    <?php esc_html_e("Site URL Mismatch. You generated a key for:", "BigBangThemesFramework"); ?><br/>
                    <strong><?php echo $rsp['domain']->urls; ?></strong><br/><br/>
                    <?php esc_html_e("but the correct domain name is:", "BigBangThemesFramework"); ?><br/>
                    <strong><?php echo get_site_url(); ?></strong><br/><br/>
                    <?php esc_html_e("It's the same thing when you type it in your browser because you're being redirected but for the Product Keys system, it's a different URL.", "BigBangThemesFramework"); ?>
                    <br/><br/><?php esc_html_e("Go back to Generate A New Product Key and if you won't be able to figure it out, reach out to the support team.", "BigBangThemesFramework"); ?>
                    <a class="button generate" href="<?php echo esc_url(BBT_Plugin_Installer::$bbt_api_url); ?>" target="_blank"><?php esc_html_e('Generate a Product Key', "BigBangThemesFramework"); ?></a>
                </div>

                <div class="step step-2">
                    <img src="<?php echo BBT_PL_URL . '/plugins-installer/img/step-2.png'; ?>" />
                    <div class="title"><?php esc_html_e('Step 2', "BigBangThemesFramework"); ?></div>
                    <form id="bbt_product_registration" action="" method="POST">
                        <input type="hidden" name="register" value="true" />
                        <input type="text" name="bbt_product_key" id="bbt_product_key" placeholder="<?php esc_html_e('Paste your Product Key Here', "BigBangThemesFramework");?>" value="" />
                        <button class="button button-primary bbt-register" type="submit"><?php esc_html_e( "Activate Key", "BigBangThemesFramework" ); ?></button>
                    </form>
                </div>
            </div>

    <?php elseif ($activated && $activated != ''): ?>
        <style>.about-wrap div.error.bbt_update_notices.notice_product_key{display:none !important;}</style>
        <div class="bbt-registration-done">

            <img src="<?php echo BBT_PL_URL . '/plugins-installer/img/hand-ok.svg'; ?>" />
            <h2><?php esc_html_e("Product Key Active!", "BigBangThemesFramework"); ?></h2>

            <div class="bbt_product_key_wrapper">
                <span class="bbt_product_key">
                    <?php echo get_option("bbt_".THEME_FOLDER_NAME."_license");?>
                </span>

                <a class="button button-primary" href="<?php echo esc_url(BBT_Plugin_Installer::$bbt_api_url); ?>" target="_blank">
                    <?php esc_html_e("Change Domain Name", "BigBangThemesFramework"); ?>
                </a>

                <form class="delete_key" method="POST" action="">
                    <input type="hidden" name="action" value="bbt-delkey">
                    <button class="button" alt="<?php esc_html_e('Remove this key', "BigBangThemesFramework"); ?>" type="submit" value="submit">
                        <?php esc_html_e("Remove Key", "BigBangThemesFramework"); ?>
                    </button>
                </form>

                <div class="clear"></div>
            </div>

        </div>

    <?php elseif ($activated != ''): ?>

        <div class="bbt-registration-form expired">

            <div class="inner steps has-error">

            <?php
                if ( ! empty( $rsp['text'] ))
                {
                    echo '<div class="key_error">'.$rsp['text'].'</div><br/><br/>';
                }
            ?>

                <div class="step step-1 step-error">
                <?php
                    echo sprintf(
                        esc_html__('Your %1$sProduct Key%2$s  is no longer active on this domain. 
                        Your site will no longer receive automatic theme updates.%3$s
                        That\'s fine if you moved your site and activated it for a new domain.','BigBangThemesFramework'),
                        '<b>', '</b>','<br/><br/>'
                    );
                ?>
                <br/><br/><br/><br/><br/><br/><br/><br/>
                <a class="button generate" href="<?php echo esc_url(BBT_Plugin_Installer::$bbt_api_url); ?>" target="_blank"><?php esc_html_e('Generate a New Product Key', "BigBangThemesFramework"); ?></a>

                </div>

                <div class="step step-2">
                    <?php esc_html_e('Step 2', "BigBangThemesFramework"); ?><br/>
                    <img src="<?php echo BBT_PL_URL . '/plugins-installer/img/step2.png'; ?>" />
                    <form id="bbt_product_registration" action="" method="POST">
                        <input type="hidden" name="register" value="true" />
                        <input type="text" name="bbt_product_key" id="bbt_product_key" placeholder="<?php esc_html_e('Paste your Product Key Here', "BigBangThemesFramework"); ?>" value="<?php echo get_option("getbowtied_".THEME_SLUG."_license");?>" />
                        <br /><br />
                        <button class="button button-primary bbt-register" type="submit"><?php esc_html_e( "Activate Product Key", "BigBangThemesFramework" ); ?></button>
                    </form>
                </div>

                <div class="clear"></div>
            </div>

        </div>

    <?php endif; ?>

    <div class="bbt_footer">
    <a href="#" target="_blank"><span class="dashicons dashicons-info"></span> <?php esc_html_e("Product Key — Common Issues & FAQs", "BigBangThemesFramework"); ?></a>
    <a href="#" target="_blank"><span class="dashicons dashicons-info"></span> <?php esc_html_e("Can I change the domain name later?", "BigBangThemesFramework"); ?></a>
    <a href="#" target="_blank"><span class="dashicons dashicons-info"></span> <?php esc_html_e("Can I activate a local / development site?", "BigBangThemesFramework"); ?></a>
    </div>

    <!-- <i> TESTING BUTTON </i>

    <form method="POST" action="">
    <input type="hidden" name="action" value="delkey">
    <button type="submit" value="submit">DELETE KEY</button>
    </form> -->

</div>