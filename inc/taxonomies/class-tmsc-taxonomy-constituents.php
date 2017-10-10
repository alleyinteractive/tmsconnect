<?php

/**
 * Taxonomy for Constituents.
 */
class Tmsc_Taxonomy_Constituents extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'constituents';

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
				'name'                  => __( 'Constituents', 'tmsc' ),
				'singular_name'         => __( 'Constituents', 'tmsc' ),
				'search_items'          => __( 'Search Constituents', 'tmsc' ),
				'popular_items'         => __( 'Popular Constituents', 'tmsc' ),
				'all_items'             => __( 'All Constituents', 'tmsc' ),
				'parent_item'           => __( 'Parent Constituents', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Constituents', 'tmsc' ),
				'edit_item'             => __( 'Edit Constituents', 'tmsc' ),
				'view_item'             => __( 'View Constituents', 'tmsc' ),
				'update_item'           => __( 'Update Constituents', 'tmsc' ),
				'add_new_item'          => __( 'Add New Constituents', 'tmsc' ),
				'new_item_name'         => __( 'New Constituents Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Constituents', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Constituents', 'tmsc' ),
				'menu_name'             => __( 'Constituents', 'tmsc' ),
			),
			'hierarchical' => true,
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_constituents = new Tmsc_Taxonomy_Constituents();
