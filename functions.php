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

/**
 * Return the post object of a legacy TMS object ID
 * @param string $legacy_id of TMS object.
 * @param string $post_type. Optional post type to grab legacy item from.
 * @return object
 */
function tmsc_get_object_by_legacy_id( $legacy_id, $post_type = 'tms_object' ) {
	$args = array(
		'post_status' => 'any',
		'post_type' => $post_type,
		'meta_key' => 'tmsc_legacy_id',
		'meta_value' => $legacy_id, // With the uncertainty of the legacy id format, this is being saved as a string always so don't use meta_value_num.
		'suppress_filters' => false,
	);

	$posts = get_posts( $args );

	if ( ! empty( $posts ) ) {
		return reset( $posts );
	}
	return false;

}

/**
 * Get terms with a legeacy CN for a given taxonomy
 * @param string $legacy_id CN of TMS term.
 * @param string $taxonomy
 * @return object
 */
function tmsc_get_term_by_legacy_id( $legacy_id, $taxonomy = null ) {
	$args = array(
		'taxonomy' => $taxonomy,
		'meta_key' => 'tmsc_legacy_id',
		'meta_value' => $legacy_id,
		'hide_empty' => false,
	);
	$terms = get_terms( $args );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		return reset( $terms );
	}
	return;
}

/**
 * Get the corresponding linked term for a post id.
 * @param int $post_id.
 * @return object. WP_Term Object.
 */
function tmsc_get_linked_term( $post_id ) {
	return TMSC_Linked_Taxonomy_Posts()->get_linked_term( $post_id );
}

/**
 * Get the corresponding linked post for a term id.
 * @param int $term_id.
 * @return object. WP_Post Object.
 */
function tmsc_get_linked_post( $term_id ) {
	return TMSC_Linked_Taxonomy_Posts()->get_linked_post( $term_id );
}

/**
 * Get the linked types.
 * @return array. array( 'post_type_slug' => 'taxonomy_slug' ).
 */
function tmsc_get_linked_types() {
	return TMSC_Linked_Taxonomy_Posts()->linked_types;
}

/**
 * Get the associated TMS objects to a term
 * @param int $term_id
 * @return array. Array of tms_object Post Objects.
 */
function tmsc_get_related_tms_objects() {
}
