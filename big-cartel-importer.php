<?php
/**
 * Plugin Name: Big Cartel Product Importer
 * Plugin URI: http://www.pluginize.com
 * Description: Import your products from Big Cartel to a Product custom post type in WordPress.
 * Version: 1.1.0
 * Author: Pluginize
 * Author URI: http://www.pluginize.com
 * License: GPLv2+
 * Text Domain: big-cartel-product-importer
 */

function wdsbc_load_importer() {
	$plugin_dir_path = dirname( __FILE__ );

	load_plugin_textdomain( 'big-cartel-product-importer' );

	require_once $plugin_dir_path . '/classes/class-wdsbc-import.php';
	require_once $plugin_dir_path . '/includes/content-types.php';
	require_once $plugin_dir_path . '/includes/metaboxes.php';
	require_once $plugin_dir_path . '/includes/settings.php';
}
add_action( 'plugins_loaded', 'wdsbc_load_importer' );
