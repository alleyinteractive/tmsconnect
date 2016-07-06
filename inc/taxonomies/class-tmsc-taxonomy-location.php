<?php

/**
 * Taxonomy for Locations.
 */
class Tmsc_Taxonomy_Location extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'location';

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
	 * Creates the taxonomy.
	 */
	public function create_taxonomy() {
		register_taxonomy( $this->name, $this->object_types, array(
			'labels' => array(
				'name'                  => __( 'Locations', 'tmsc' ),
				'singular_name'         => __( 'Location', 'tmsc' ),
				'search_items'          => __( 'Search Locations', 'tmsc' ),
				'popular_items'         => __( 'Popular Locations', 'tmsc' ),
				'all_items'             => __( 'All Locations', 'tmsc' ),
				'parent_item'           => __( 'Parent Location', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Location', 'tmsc' ),
				'edit_item'             => __( 'Edit Location', 'tmsc' ),
				'view_item'             => __( 'View Location', 'tmsc' ),
				'update_item'           => __( 'Update Location', 'tmsc' ),
				'add_new_item'          => __( 'Add New Location', 'tmsc' ),
				'new_item_name'         => __( 'New Location Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Locations', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Locations', 'tmsc' ),
				'menu_name'             => __( 'Locations', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_location = new Tmsc_Taxonomy_Location();
