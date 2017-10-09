<?php

/**
 * Taxonomy for Geographies.
 */
class Tmsc_Taxonomy_Geography extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'geography';

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
				'name'                  => __( 'Geographies', 'tmsc' ),
				'singular_name'         => __( 'Geography', 'tmsc' ),
				'search_items'          => __( 'Search Geographies', 'tmsc' ),
				'popular_items'         => __( 'Popular Geographies', 'tmsc' ),
				'all_items'             => __( 'All Geographies', 'tmsc' ),
				'parent_item'           => __( 'Parent Geography', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Geography', 'tmsc' ),
				'edit_item'             => __( 'Edit Geography', 'tmsc' ),
				'view_item'             => __( 'View Geography', 'tmsc' ),
				'update_item'           => __( 'Update Geography', 'tmsc' ),
				'add_new_item'          => __( 'Add New Geography', 'tmsc' ),
				'new_item_name'         => __( 'New Geography Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Geographies', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Geographies', 'tmsc' ),
				'menu_name'             => __( 'Geographies', 'tmsc' ),
			),
			'hierarchical' => true,
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_geography = new Tmsc_Taxonomy_Geography();
