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

	if( is_callable( $wp_object_cache, '__remoteset' ) ) {
		$wp_object_cache->__remoteset(); // important
	}
}

/**
 * Converts a "dirty" URL that might have GET params or width/height attributes
 * to a clean GUID.
 */
function tmsc_strip_url_params( $url ) {
	if ( false !== ( $pos = strpos( $url, '?' ) ) ) {
		return substr( $url, 0, $pos );
	}
	return $url;
}

function tmsc_clean_crashed_posts() {
	$posts = get_posts( array(
		'posts_per_page' => -1,
		'post_status' => 'migrating',
	) );
	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

function tmsc_get_mapping( $type, $term, $find_dest = false ) {
	return \TMSC\Util\MappingFile::get_mapping( $type, $term, $find_dest );
}

/**
 * Register a custom processor with TMSC
 */
function tmsc_register_processor( $classname = null ) {
	return \TMSC\TMSC::instance()->register_processor( $classname );
}

/*
function tmsc_post_id_by_legacy_id( $guid ) {
	global $wpdb;
	$map = \TMSC\Util\Map::get( 'legacy_post_ids' );
	if ( $map->count() == 0 ) {
		$results = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'tmsc_legacy_id'" );
		foreach ( $results as $result ) {
			$map->map( $result->meta_value, $result->post_id );
		}
	}
	return $map->map( $guid, null, true );
}

function tmsc_post_by_legacy_id( $guid, $args = array() ) {
	$post_id = tmsc_post_id_by_legacy_id( $guid );
	if ( ! empty( $post_id ) ) {
		if ( $post = get_post( $post_id ) ) {
			return $post;
		}
	}
	return null;
}
*/
function tmsc_update_term_count() {
	// Note the start time and keep track of how many fields have been converted for script output
	$timestamp_start = microtime( true );

	echo "Starting update of term counts for all taxonomies\n";

	// Get all taxonomies
	$taxonomies = get_taxonomies();
	foreach( $taxonomies as $taxonomy ) {
		// Get all terms for the taxonomy
		// Use special handling for the 'author' taxonomy due to Co-Authors Plus custom post count function
		if( $taxonomy == 'author' && function_exists( 'coauthors' ) ) {
			$args = array(
				'hide_empty' => 0
			);
			$terms = get_terms( $taxonomy, $args );
			$tt_ids = array();
			foreach( $terms as $term ) {
				$tt_ids[] = $term->term_taxonomy_id;
			}
			$terms = $tt_ids;
		} else {
			$args = array(
				'hide_empty' => 0,
				'fields' => 'ids'
			);
			$terms = get_terms( $taxonomy, $args );
		}
		if( is_array( $terms ) && !empty( $terms ) ) wp_update_term_count_now( $terms, $taxonomy );
	}
}

/**
 * Get a \WP_Term by its legacy ID.
 *
 * @param int $legacy_id
 * @return \WP_Term|null Null on failure.
 */
function tmsc_term_id_by_legacy_id( $legacy_id ) {
	$terms = get_terms( array(
		'fields'     => 'ids',
		'hide_empty' => false,
		'meta_key'   => 'tmsc_legacy_id',
		'meta_value' => $legacy_id,
		'number'     => 1,
	) );

	if ( empty( $terms ) || ! is_array( $terms ) ) {
		return null;
	}

	return array_shift( $terms );
}

/**
 * Meta mapping helpers
 */

/**
 * Map a legacy post ID contained in postmeta to a new post ID
 */
function tmsc_map_meta_legacy_post_id( $post_id, $meta_key, $meta_value ) {
	return tmsc_post_id_by_legacy_id( $meta_value );
}
