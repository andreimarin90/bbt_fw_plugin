<?php
/**
 * Plugin Name: BBT Framework
 * Plugin URI: http://bigbangthemes.com/
 * Description: BBTFramework plugin part (registers custom post types, shortcodes and other features of the theme required to be in a plugin)
 * Version: 1.1
 * Author: BigBangThemes
 * Author URI: http://bigbangthemes.com/
 * License: GPL2
 */
if(!class_exists('BBT_Custom_Posts')){
	require_once 'custom_posts.php';
	add_action('after_setup_theme','bbt_custom_posts_plugin');
	function bbt_custom_posts_plugin(){
		BBT_Custom_Posts::init();
	}
}