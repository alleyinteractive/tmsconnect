<?php
/**
 * Shared functions for TMSConnect
 */
/**
 * Set the sync status value
 * @param string. $message.
 * @return boolean.
 */
function tmsc_set_sync_status( $message ) {
	wp_cache_delete( 'tmsc-last-sync-date', 'options' );
	return update_option( 'tmsc-last-sync-date', $message, false );
}

/**
 * Create a unique string for a data field.
 * This allows us to quickly check if data has been changed on sync.
 * @param mixed $data. Can be any data type.
 * @return string
 */
function tmsc_hash_data( $data ) {
	$serialize = function_exists( 'igbinary_serialize' ) ? 'igbinary_serialize' : 'serialize';
	return md5( $serialize( $data ) );
}

/**
 * Stop the insanity; clean up memory.
 */
function tmsc_stop_the_insanity() {
	global $wpdb, $wp_object_cache, $wp_actions;

	$wpdb->queries = array(); // or define( 'WP_IMPORTING', true );
	$wp_actions = array();

	if ( ! is_object( $wp_object_cache ) ) {
		return;
	}

	$wp_object_cache->group_ops = array();
	$wp_object_cache->stats = array();
	$wp_object_cache->memcache_debug = array();
	$wp_object_cache->cache = array();

	if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
		$wp_object_cache->__remoteset(); // important
	}
}
