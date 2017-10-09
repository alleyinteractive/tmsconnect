<?php

/**
 * Taxonomy for Exhibitions.
 */
class Tmsc_Taxonomy_Exhibitions extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'exhibitions';

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
				'name'                  => __( 'Exhibitions', 'tmsc' ),
				'singular_name'         => __( 'Exhibitions', 'tmsc' ),
				'search_items'          => __( 'Search Exhibitions', 'tmsc' ),
				'popular_items'         => __( 'Popular Exhibitions', 'tmsc' ),
				'all_items'             => __( 'All Exhibitions', 'tmsc' ),
				'parent_item'           => __( 'Parent Exhibitions', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Exhibitions', 'tmsc' ),
				'edit_item'             => __( 'Edit Exhibitions', 'tmsc' ),
				'view_item'             => __( 'View Exhibitions', 'tmsc' ),
				'update_item'           => __( 'Update Exhibitions', 'tmsc' ),
				'add_new_item'          => __( 'Add New Exhibitions', 'tmsc' ),
				'new_item_name'         => __( 'New Exhibitions Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Exhibitions', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Exhibitions', 'tmsc' ),
				'menu_name'             => __( 'Exhibitions', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_exhibitions = new Tmsc_Taxonomy_Exhibitions();
