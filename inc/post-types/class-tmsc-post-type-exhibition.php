<?php
/**
 * Custom post type for Exhibitions.
 */
class Tmsc_Post_Type_Exhibition extends Tmsc_Post_Type {

	/**
	 * Name of the custom post type.
	 *
	 * @var string
	 */
	public $name = 'exhibition';

	/**
	 * Creates the post type.
	 */
	public function create_post_type() {
		register_post_type( $this->name, array(
			'labels' => array(
				'name'               => __( 'Exhibitions', 'tmsc' ),
				'singular_name'      => __( 'Exhibition', 'tmsc' ),
				'add_new'            => __( 'Add New Exhibition', 'tmsc' ),
				'add_new_item'       => __( 'Add New Exhibition', 'tmsc' ),
				'edit_item'          => __( 'Edit Exhibition', 'tmsc' ),
				'new_item'           => __( 'New Exhibition', 'tmsc' ),
				'view_item'          => __( 'View Exhibition', 'tmsc' ),
				'search_items'       => __( 'Search Exhibitions', 'tmsc' ),
				'not_found'          => __( 'No Exhibitions found', 'tmsc' ),
				'not_found_in_trash' => __( 'No Exhibitions found in Trash', 'tmsc' ),
				'parent_item_colon'  => __( 'Parent Exhibition:', 'tmsc' ),
				'menu_name'          => __( 'Exhibitions', 'tmsc' ),
			),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-format-gallery',
			'supports' => array( 'title', 'revisions', 'excerpt', 'editor' ),
		) );
	}
}

$post_type_exhibition = new Tmsc_Post_Type_Exhibition();
