<?php

/**
 * Taxonomy for Collections.
 */
class Tmsc_Taxonomy_Collection extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'collection';

	/**
	 * Object types for this taxonomy
	 *
	 * @var array
	 */
	public $object_types;


	/**
	 * Build the taxonomy object.
	 */
	public function __construct() {
		$this->object_types = array( 'tms_object' );

		parent::__construct();
	}

	/**
	 * Args passed to register taxonomy.
	 * Allows for a filter.
	 * @param array.
	 * @return array.
	 */
	public function register_taxonomy_args( $args = array() ) {
		return array(
			'labels' => array(
				'name'                  => __( 'Collections', 'tmsc' ),
				'singular_name'         => __( 'Collection', 'tmsc' ),
				'search_items'          => __( 'Search Collections', 'tmsc' ),
				'popular_items'         => __( 'Popular Collections', 'tmsc' ),
				'all_items'             => __( 'All Collections', 'tmsc' ),
				'parent_item'           => __( 'Parent Collection', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Collection', 'tmsc' ),
				'edit_item'             => __( 'Edit Collection', 'tmsc' ),
				'view_item'             => __( 'View Collection', 'tmsc' ),
				'update_item'           => __( 'Update Collection', 'tmsc' ),
				'add_new_item'          => __( 'Add New Collection', 'tmsc' ),
				'new_item_name'         => __( 'New Collection Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Collections', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Collections', 'tmsc' ),
				'menu_name'             => __( 'Collections', 'tmsc' ),
			),
			'hierarchical' => true,
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_collection = new Tmsc_Taxonomy_Collection();
