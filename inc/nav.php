<?php
/**
 * Customizations to the admin nav
 */

/**
 * Add in the custom types to Exhibitions.
 * @param string $post_type.
 * @param string $taxonomy.
 * @param string $edit_slug.
 * @param array $labels.
 * @return void.
 */
function tmsc_add_type_taxonomies( $post_type, $taxonomy, $edit_slug, $labels ) {
	if ( 'exhibition' === $post_type ) {
		$tag_url = add_query_arg( array( 'taxonomy' => 'exhibition-type', 'post_type' => 'exhibition' ), 'edit-tags.php' );
		add_submenu_page(
			$edit_slug,
			__( 'Exhibition Types', 'tmsc' ),
			__( 'Exhibition Types', 'tmsc' ),
			'edit_posts',
			$tag_url
		);
	}
}
add_action( 'tmsc_add_custom_landing_submenus', 'tmsc_add_type_taxonomies', 20, 4 );

/**
 * Highlight exhibition type taxonomy submenu.
 * @param string $post_type.
 * @param string $taxonomy.
 * @param string $edit_slug.
 * @param object $current_post.
 * @return void.
 */
function tmsc_highlight_edit_tag() {
	global $submenu_file, $parent_file, $typenow, $taxnow;
	if ( 'exhibition' === $typenow && 'exhibition-type' === $taxnow ) {
		$parent_file = add_query_arg( array( 'post_type' => 'exhibition', 'taxonomy' => 'exhibitions' ), 'edit.php' );
		$submenu_file = add_query_arg( array( 'taxonomy' => 'exhibition-type', 'post_type' => 'exhibition' ), 'edit-tags.php' );
	}
}
add_action( 'admin_enqueue_scripts', 'tmsc_highlight_edit_tag' );