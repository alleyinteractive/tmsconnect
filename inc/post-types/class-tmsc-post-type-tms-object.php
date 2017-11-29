<?php
/**
 * Custom post type for Tms Objects.
 */
class Tmsc_Post_Type_TMS_Object extends Tmsc_Post_Type {

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
				'name'               => __( 'TMS Objects', 'tmsc' ),
				'singular_name'      => __( 'TMS Object', 'tmsc' ),
				'add_new'            => __( 'Add New TMS Object', 'tmsc' ),
				'add_new_item'       => __( 'Add New TMS Object', 'tmsc' ),
				'edit_item'          => __( 'Edit TMS Object', 'tmsc' ),
				'new_item'           => __( 'New TMS Object', 'tmsc' ),
				'view_item'          => __( 'View TMS Object', 'tmsc' ),
				'search_items'       => __( 'Search TMS Objects', 'tmsc' ),
				'not_found'          => __( 'No TMS Objects found', 'tmsc' ),
				'not_found_in_trash' => __( 'No TMS Objects found in Trash', 'tmsc' ),
				'parent_item_colon'  => __( 'Parent TMS Object:', 'tmsc' ),
				'menu_name'          => __( 'TMS Objects', 'tmsc' ),
			),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-art',
			'supports' => array( 'title', 'excerpt', 'editor', 'thumbnail' ),
			'rewrite' => array(
				'slug' => 'object',
			),
		) );
	}
}

$post_type_tms_object = new Tmsc_Post_Type_Tms_Object();
