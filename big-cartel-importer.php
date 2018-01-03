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
	require_once $plugin_dir_path . '/classes/wp-background-processing/wp-async-request.php';
	require_once $plugin_dir_path . '/classes/wp-background-processing/wp-background-process.php';
	require_once $plugin_dir_path . '/classes/class-wdsbc-importer.php';
	require_once $plugin_dir_path . '/includes/content-types.php';
	require_once $plugin_dir_path . '/includes/metaboxes.php';
	require_once $plugin_dir_path . '/includes/settings.php';
}
add_action( 'plugins_loaded', 'wdsbc_load_importer' );

function cron_spawn() {
	global $wp_version;
	if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
		// translators: placeholder will be a timestamp for the current time.
		return sprintf( 'The DISABLE_WP_CRON constant is set to true as of %s. WP-Cron is disabled and will not run.', current_time( 'm/d/Y g:i:s a' ) );
	}
	if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
		// translators: placeholder will be a timestamp for the current time.
		return sprintf( 'The ALTERNATE_WP_CRON constant is set to true as of %s.  This plugin cannot determine the status of your WP-Cron system.', current_time( 'm/d/Y g:i:s a' ) );
	}
	$sslverify     = version_compare( $wp_version, 4.0, '<' );
	$doing_wp_cron = sprintf( '%.22F', microtime( true ) );
	$cron_request = apply_filters( 'cron_request', array(
		'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
		'key'  => $doing_wp_cron,
		'args' => array(
			'timeout'   => 3,
			'blocking'  => true,
			'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
		),
	) );
	$cron_request['args']['blocking'] = true;
	$result = wp_remote_post( $cron_request['url'], $cron_request['args'] );
	if ( is_wp_error( $result ) ) {
		return $result;
	} elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
		return sprintf(
			'Unexpected HTTP response code: %s',
			intval( wp_remote_retrieve_response_code( $result ) )
		);
	}
	return 'Cron spawn ok';
}
