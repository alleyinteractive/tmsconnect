<?php
/**
 * Custom post type for Constituents.
 */
class Tmsc_Post_Type_Constituent extends Tmsc_Post_Type {

	/**
	 * Name of the custom post type.
	 *
	 * @var string
	 */
	public $name = 'constituent';

	/**
	 * Creates the post type.
	 */
	public function create_post_type() {
		register_post_type( $this->name, array(
			'labels' => array(
				'name'               => __( 'Constituents', 'tmsc' ),
				'singular_name'      => __( 'Constituent', 'tmsc' ),
				'add_new'            => __( 'Add New Constituent', 'tmsc' ),
				'add_new_item'       => __( 'Add New Constituent', 'tmsc' ),
				'edit_item'          => __( 'Edit Constituent', 'tmsc' ),
				'new_item'           => __( 'New Constituent', 'tmsc' ),
				'view_item'          => __( 'View Constituent', 'tmsc' ),
				'search_items'       => __( 'Search Constituents', 'tmsc' ),
				'not_found'          => __( 'No Constituents found', 'tmsc' ),
				'not_found_in_trash' => __( 'No Constituents found in Trash', 'tmsc' ),
				'parent_item_colon'  => __( 'Parent Constituent:', 'tmsc' ),
				'menu_name'          => __( 'Constituents', 'tmsc' ),
			),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-groups',
			'supports' => array( 'title', 'excerpt', 'editor' ),
		) );
	}
}

$post_type_constituent = new Tmsc_Post_Type_Constituent();
