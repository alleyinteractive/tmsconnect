<?php
/**
 * This file contains all helper functions for getting specific post level data.
 *
 * @package Freer_Sackler
 */

/**
 * General function to get post meta data.
 *
 * @param  int    $post_id  The post ID.
 * @param  string $meta_key The meta key.
 * @return string           The meta value.
 */
function tmsc_get_object_meta( int $post_id, string $meta_key ) {
	$value = get_post_meta( $post_id, $meta_key, true );

	/**
	 * Filter the object meta data.
	 *
	 * @param  mixed  $value    The post meta value.
	 * @param  int    $post_id  The post ID.
	 * @return string $meta_key The meta value.
	 */
	return apply_filters( 'tmsc_get_object_meta', $value, $post_id, $meta_key );
}

/**
 * General function to get term meta data.
 *
 * @param  int    $term_id  The term ID.
 * @param  string $meta_key The meta key.
 * @return string $value    The meta value.
 */
function tmsc_get_term_meta( int $term_id, string $meta_key ) {
	$value = get_term_meta( $term_id, $meta_key, true );

	/**
	 * Filter the term meta data.
	 *
	 * @param  mixed  $value    The term meta value.
	 * @param  int    $term_id  The term ID.
	 * @return string $meta_key The meta value.
	 */
	return apply_filters( 'tmsc_get_term_meta', $value, $term_id, $meta_key );
}

/**
 * Get a post's classifcations.
 *
 * @param  int    $post_id  The post ID.
 * @param  mixed  $taxonomy The taxonomies to get the terms from.
 * @return array            An array of WP_Term objects.
 */
function tmsc_get_object_terms( int $post_id, $taxonomy ) {
	$terms = wp_get_object_terms( $post_id, $taxonomy );

	/**
	 * Filter the object terms.
	 *
	 * @param mixed  $terms    The object terms.
	 * @param int    $post_id  The post ID.
	 * @param mixed  $taxonomy The taxonomies to get the terms from.
	 */
	return apply_filters( 'tmsc_get_object_terms', $terms, $post_id, $taxonomy );
}

/**
 * Get a post's classifcations.
 *
 * @param  int    $post_id  The post ID.
 * @param  mixed  $taxonomy The taxonomies to get the terms from.
 * @return array            An array of WP_Post objects.
 */
function tmsc_get_linked_objects( int $post_id, $taxonomy ) {
	$terms = wp_get_object_terms( $post_id, $taxonomy );
	$cache_key = 'tmsc_linked_objects_' . md5( $post_id . $taxonomy );
	$linked_objects = get_transient( $cache_key );

	if ( false === $linked_objects ) {
		$linked_objects = [];

		// Loop through all terms and get the linked post.
		foreach ( $terms as $term ) {
			if ( ! empty( $term->term_id ) ) {
				$linked_post = tmsc_get_linked_post( $term->term_id );

				// Add it to the linked objects.
				if ( $linked_post instanceof \WP_Post ) {
					$linked_objects[] = $linked_post;
				}
			}
		}

		set_transient( $cache_key, $linked_objects, DAY_IN_SECONDS );
	}

	/**
	 * Filter the linked objects.
	 *
	 * @param mixed  $linked_terms The linked objects.
	 * @param int    $post_id      The post ID.
	 * @param mixed  $taxonomy     The taxonomies to get the terms from.
	 */
	return apply_filters( 'tmsc_get_linked_objects', $linked_objects, $post_id, $taxonomy );
}

/**
 * Clear the `tmsc_get_linked_objects` cache on post save.
 *
 * @param  int $post_id The post ID.
 */
function tmsc_get_linked_objects_clear_cache( $post_id ) {
	// Get all of the post taxonomies.
	$taxonomy_names = get_post_taxonomies( $post_id );

	// Delete the transient for each taxonomy on the post.
	foreach ( $taxonomy_names as $taxonomy_name ) {
		delete_transient( 'tmsc_linked_objects_' . md5( $post_id . $taxonomy_name ) );
	}
}
add_action( 'save_post', 'tmsc_get_linked_objects_clear_cache' );

/**
 * Get an image caption from an attachment.
 *
 * @param  int    $attachment The attachment ID.
 * @return string             The attachment image caption.
 */
function tmsc_get_image_public_caption( $attachment_id ) : string {
	$attachment = get_post( $attachment_id );

	// The public image caption is saved as post content.
	if ( $attachment instanceof \WP_Post ) {
		return $attachment->post_content;
	}

	return '';
}
