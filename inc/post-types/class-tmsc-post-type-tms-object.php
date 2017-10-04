<?php
/**
 * Custom post type for Tms Objects.
 */
class Tmsc_Post_Type_Tms_Object extends Tmsc_Post_Type {

	/**
	 * Name of the custom post type.
	 *
	 * @var string
	 */
	public $name = 'tms_object';

	/**
	 * Creates the post type.
	 */
	public function create_post_type() {
		register_post_type( $this->name, array(
			'labels' => array(
				'name'               => __( 'Tms Objects', 'tmsc' ),
				'singular_name'      => __( 'Tms Object', 'tmsc' ),
				'add_new'            => __( 'Add New Tms Object', 'tmsc' ),
				'add_new_item'       => __( 'Add New Tms Object', 'tmsc' ),
				'edit_item'          => __( 'Edit Tms Object', 'tmsc' ),
				'new_item'           => __( 'New Tms Object', 'tmsc' ),
				'view_item'          => __( 'View Tms Object', 'tmsc' ),
				'search_items'       => __( 'Search Tms Objects', 'tmsc' ),
				'not_found'          => __( 'No Tms Objects found', 'tmsc' ),
				'not_found_in_trash' => __( 'No Tms Objects found in Trash', 'tmsc' ),
				'parent_item_colon'  => __( 'Parent Tms Object:', 'tmsc' ),
				'menu_name'          => __( 'Tms Objects', 'tmsc' ),
			),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-book-alt',
			'supports' => array( 'title', 'excerpt', 'editor' ),
			'rewrite' => array(
				'slug' => 'objects',
			),
		) );
	}
}

$post_type_tms_object = new Tmsc_Post_Type_Tms_Object();
