<?php
/**
 * Custom post type for Collections.
 */
class Tmsc_Post_Type_Collection extends Tmsc_Post_Type {

	/**
	 * Name of the custom post type.
	 *
	 * @var string
	 */
	public $name = 'collection';

	/**
	 * Creates the post type.
	 */
	public function create_post_type() {
		register_post_type( $this->name, array(
			'labels' => array(
				'name'               => __( 'Collections', 'tmsc' ),
				'singular_name'      => __( 'Collection', 'tmsc' ),
				'add_new'            => __( 'Add New Collection', 'tmsc' ),
				'add_new_item'       => __( 'Add New Collection', 'tmsc' ),
				'edit_item'          => __( 'Edit Collection', 'tmsc' ),
				'new_item'           => __( 'New Collection', 'tmsc' ),
				'view_item'          => __( 'View Collection', 'tmsc' ),
				'search_items'       => __( 'Search Collections', 'tmsc' ),
				'not_found'          => __( 'No Collections found', 'tmsc' ),
				'not_found_in_trash' => __( 'No Collections found in Trash', 'tmsc' ),
				'parent_item_colon'  => __( 'Parent Collection:', 'tmsc' ),
				'menu_name'          => __( 'Collections', 'tmsc' ),
			),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-archive',
			'supports' => array( 'title', 'revisions', 'excerpt' ),
		) );
	}
}

$post_type_collection = new Tmsc_Post_Type_Collection();
