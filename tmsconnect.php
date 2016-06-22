<?php
/**
 * @package TMS Connect
 * @subpackage Plugin
 * @version 0.1
 */
/*
Plugin Name: TMS Connect
Plugin URI: http://github.com/alleyinteractive/freersackler
Description: Connect & Search Freer & Sackler TMS
Author: Alley Interactive
Version: 0.1
Author URI: http://www.alleyinteractive.com/
*/

require_once( dirname( __FILE__ ) . '/php/class-tmsc-plugin-dependency.php' );

function tmsc_init() {
	require_once( dirname( __FILE__ ) . '/php/class-tmsc.php' );

	// Custom Post Types
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-post-type-tms-object.php' );

	// Custom Taxonomies
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-catalog.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-classification.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-collection.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-constituent.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-exhibition.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-geography.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-historical-period.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-location.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-maker.php' );
	require_once( dirname( __FILE__ ) . '/php/class-tmsc-taxonomy-material.php' );

	// Global functions
	require_once( dirname( __FILE__ ) . '/functions.php' );

	add_action( 'wp_enqueue_scripts', 'tmsc_enqueue_assets' );
	add_action( 'admin_enqueue_scripts', 'tmsc_enqueue_assets' );
}
add_action( 'plugins_loaded', 'tmsc_init' );

function tmsc_dependency() {
	$tmsc_dependency = new TMSC_Plugin_Dependency( 'Fieldmanager', 'Fieldmanager', 'https://github.com/alleyinteractive/wordpress-fieldmanager' );
	if ( ! $tmsc_dependency->verify() ) {
		// Cease activation
	 	die( $tmsc_dependency->message() );
	}
}
register_activation_hook( __FILE__, 'tmsc_dependency' );

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

