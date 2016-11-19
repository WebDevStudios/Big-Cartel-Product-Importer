<?php
/*
Plugin Name: Big Cartel Product Importer
Plugin URI: http://www.webdevstudios.com
Description: Import your products from Big Cartel to a Product custom post type in WordPress.
Version: 1.0.2
Author: WebDevStudios
Author URI: http://www.webdevstudios.com
License: GPLv2
*/

function wdsbc_load_importer() {
	$plugin_dir_path = dirname( __FILE__ );
	require_once $plugin_dir_path . '/classes/class-wdsbc-import.php';
	require_once $plugin_dir_path . '/includes/content-types.php';
	require_once $plugin_dir_path . '/includes/metaboxes.php';
	require_once $plugin_dir_path . '/includes/settings.php';
}
add_action( 'plugins_loaded', 'wdsbc_load_importer' );
