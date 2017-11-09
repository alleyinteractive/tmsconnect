<?php

/**
 * Taxonomy for Collections.
 */
class Tmsc_Taxonomy_Collection_Area extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'collection-area';

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
	 * @return array.
	 */
	public function register_taxonomy_args() {
		return array(
			'labels' => array(
				'name'                  => __( 'Collection Areas', 'tmsc' ),
				'singular_name'         => __( 'Collection Area', 'tmsc' ),
				'search_items'          => __( 'Search Collections', 'tmsc' ),
				'popular_items'         => __( 'Popular Collection Areas', 'tmsc' ),
				'all_items'             => __( 'All Collection Areas', 'tmsc' ),
				'parent_item'           => __( 'Parent Collection Area', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Collection Area', 'tmsc' ),
				'edit_item'             => __( 'Edit Collection Area', 'tmsc' ),
				'view_item'             => __( 'View Collection Area', 'tmsc' ),
				'update_item'           => __( 'Update Collection Area', 'tmsc' ),
				'add_new_item'          => __( 'Add New Collection Area', 'tmsc' ),
				'new_item_name'         => __( 'New Collection Area Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Collection Areas', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Collection Areas', 'tmsc' ),
				'menu_name'             => __( 'Collection Areas', 'tmsc' ),
			),
			'hierarchical' => true,
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_collection_area = new Tmsc_Taxonomy_Collection_Area();
