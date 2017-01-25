<?php
/**
 * Plugin Name: BBT Framework
 * Plugin URI: http://bigbangthemes.com/
 * Description: BBTFramework plugin part (registers custom post types, shortcodes and other features of the theme required to be in a plugin)
 * Version: 1.2.8
 * Author: BigBangThemes
 * Author URI: http://bigbangthemes.com/
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
		//check if demo content flder from our theme exists
		/*$dir = '';
		if(defined('BBT_THEME_DIR')) {
			$dir = BBT_THEME_DIR . '/theme_config/demo-config';
		}

		if(is_dir( $dir ))
		{*/
			//load main demo import class
			new BBT_Demo_Import();
		//}
	}
}

if(!function_exists('getConnectionWithAccessToken')){
	function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
		require BBT_PL_DIR . 'twitteroauth/autoload.php';
		$connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
		return $connection;
	}
}
require_once BBT_PL_DIR . 'helpers.php';