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

define( 'TMSCONNECT_PATH', dirname( __FILE__ ) );

require_once( TMSCONNECT_PATH . '/inc/class-tmsc.php' );
require_once( TMSCONNECT_PATH . '/inc/class-tmsc-plugin-dependency.php' );

function tmsc_init() {
	// Custom Post Types
	require_once( TMSCONNECT_PATH . '/inc/post-types/class-tmsc-post-type.php' );
	require_once( TMSCONNECT_PATH . '/inc/post-types/class-tmsc-post-type-tms-object.php' );

	// Custom Taxonomies
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-catalog.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-classification.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-collection.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-constituent.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-exhibition.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-geography.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-period.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-location.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-maker.php' );
	require_once( TMSCONNECT_PATH . '/inc/taxonomies/class-tmsc-taxonomy-material.php' );

	// Metabox FM Fields
	require_once( TMSCONNECT_PATH . '/inc/fields.php' );

	// Global functions
	require_once( TMSCONNECT_PATH . '/functions.php' );

	add_action( 'wp_enqueue_scripts', 'tmsc_enqueue_assets' );
	add_action( 'admin_enqueue_scripts', 'tmsc_enqueue_assets' );
}
add_action( 'plugins_loaded', 'tmsc_init' );

function tmsc_dependency() {
	$tmsc_dependencies = array(
		new TMSC_Plugin_Dependency( 'TMS Connect', 'Fieldmanager', 'https://github.com/alleyinteractive/wordpress-fieldmanager' ),
		new TMSC_Plugin_Dependency( 'TMS Connect', 'Zone Manager (Zoninator)', 'https://github.com/Automattic/zoninator' ),
	);
	foreach ( $tmsc_dependencies as $tmsc_dependency ) {
		if ( ! $tmsc_dependency->verify() ) {
			// Cease activation
		 	die( $tmsc_dependency->message() );
		}
	}
}
register_activation_hook( __FILE__, 'tmsc_dependency' );

/**
 * Check to ensure correct zones exist.
 */
function tmsc_zone_init() {
	$default_zones = TMSC()->default_zones;
	if ( function_exists( 'z_get_zoninator' ) ) {

		// Iterate through the defined zones. If any exist already, skip them.
		foreach ( $default_zones as $zone ) {
			// Make sure these zones are defined correctly.
			if ( ! isset( $zone['slug'] ) || ! isset( $zone['name'] ) ) {
				continue;
			}

			// Check if the zone exists before adding.
			if ( false === z_get_zone( $zone['slug'] ) ) {
				// Add the zone.
				$result = z_get_zoninator()->insert_zone( $zone['slug'], $zone['name'], array( 'description' => $zone['description'] ) );

				// Set the message to display to the user based on the result.
				if ( is_wp_error( $result ) ) {
					die( sprintf( __( 'There was an error adding the default zone "%s".', 'tmsc' ), $zone['name'] ) );
				}
			}
		}
	}
}
register_activation_hook( __FILE__, 'tmsc_zone_init' );

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

