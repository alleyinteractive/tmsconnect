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
 * @return mixed            The object terms.
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
 * Get an image caption from an attachment.
 *
 * @param  int    $attachment The attachment ID.
 * @return string             The attachment image caption.
 */
function tmsc_get_image_public_caption( $attachment_id ) : string {
	$attachment = get_post( $attachment_id );

	// The public image caption is saved as post content.
	if ( $attachment instanceof WP_Post ) {
		return $attachment->post_content;
	}

	return '';
}
