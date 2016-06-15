<?php
/**
 * @package TMS Connect
 * @subpackage Plugin
 * @version 0.1
 */
/*
Plugin Name: tmsconnect
Plugin URI: http://github.com/alleyinteractive/freersackler
Description: Connect & Search Freer & Sackler TMS
Author: Alley Interactive
Version: 0.1
Author URI: http://www.alleyinteractive.com/
*/

function tmsc_init() {
	require_once( dirname( __FILE__ ) . '/php/class-tmsc.php' );
	require_once( dirname( __FILE__ ) . '/functions.php' );

	add_action( 'wp_enqueue_scripts', 'tmsc_enqueue_assets' );
	add_action( 'admin_enqueue_scripts', 'tmsc_enqueue_assets' );
}
add_action( 'plugins_loaded', 'tmsc_init' );

/**
 * Get the base URL for this plugin.
 * @return string URL pointing to Fieldmanager Plugin top directory.
 */
function tmsc_get_baseurl() {
	return plugin_dir_url( __FILE__ );
}

/**
 * Enqueue scripts and styles
 */
function tmsc_enqueue_assets() {
}

