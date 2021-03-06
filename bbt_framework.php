<?php
/**
 * Plugin Name: BBT Framework
 * Plugin URI: https://bigbangthemes.com/
 * Description: BBTFramework plugin part (registers custom post types, shortcodes and other features of the theme required to be in a plugin)
 * Version: 1.6.2
 * Author: BigBangThemes
 * Author URI: https://bigbangthemes.com/
 * License: GPL2
 */
use Abraham\TwitterOAuth\TwitterOAuth;
define("BBT_PL_DIR", plugin_dir_path( __FILE__ ));
define( 'BBT_PL_URL', trailingslashit( plugins_url() ) . trailingslashit( 'bbt_fw_plugin' ));


if(!class_exists('BBT_Plugin_Installer')){
	require_once BBT_PL_DIR . '/plugins-installer/bbt_plugin_installer.php';
	add_action('after_setup_theme','bbt_plugin_installer');
	function bbt_plugin_installer(){
		new BBT_Plugin_Installer();
	}
}

if(!class_exists('BBT_Custom_Posts')){
	require_once BBT_PL_DIR . 'custom_posts.php';
	add_action('after_setup_theme','bbt_custom_posts_plugin');
	function bbt_custom_posts_plugin(){
		BBT_Custom_Posts::init();
	}
}

if(!class_exists('BBT_Category_Builder')){
	require_once BBT_PL_DIR . 'bbt_category_builder.php';
	add_action('after_setup_theme','bbt_category_builder');
	function bbt_category_builder(){
		$bbt_builder_instance = new BBT_Category_Builder();
	}
}

if(!class_exists('BBT_Shortcoder')){
	require_once BBT_PL_DIR . 'bbt_shortcoder.php';
	add_action('after_setup_theme','bbt_shortcoder_plugin');
	function bbt_shortcoder_plugin(){
		BBT_Shortcoder::init();
	}
}

if(!class_exists('BBT_Demo_Import')){
	require_once BBT_PL_DIR . 'demo-import/demo-import-class.php';
	add_action('after_setup_theme','bbt_demo_import_plugin');
	function bbt_demo_import_plugin(){
			new BBT_Demo_Import();
	}
}

if(!class_exists('BBT_Changelog')){
    require_once BBT_PL_DIR . 'changelog/changelog-class.php';
    add_action('after_setup_theme','bbt_changelog_plugin');
    function bbt_changelog_plugin(){
        if(file_exists(get_template_directory() . '/changelog/changelog.txt')) {
            new BBT_Changelog(get_template_directory() . '/changelog/changelog.txt');
        }
    }
}

if(!function_exists('getConnectionWithAccessToken')){
	function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
		require BBT_PL_DIR . 'twitteroauth/autoload.php';
		$connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
		return $connection;
	}
}

if(!class_exists('BBT_Post_By_Email')){
    require_once BBT_PL_DIR . 'post_by_email/bbt_post_by_email.php';

    add_action('after_setup_theme','bbt_post_by_email');
    function bbt_post_by_email(){
        BBT_Post_By_Email::get_instance();
    }
}

require_once BBT_PL_DIR . 'framework_functions/framework_functions.php';

require_once BBT_PL_DIR . 'helpers.php';