<?php

/**
 * Taxonomy for Constituents.
 */
class Tmsc_Taxonomy_Constituent extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'constituent';

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
				'name'                  => __( 'Constituents', 'tmsc' ),
				'singular_name'         => __( 'Constituent', 'tmsc' ),
				'search_items'          => __( 'Search Constituents', 'tmsc' ),
				'popular_items'         => __( 'Popular Constituents', 'tmsc' ),
				'all_items'             => __( 'All Constituents', 'tmsc' ),
				'parent_item'           => __( 'Parent Constituent', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Constituent', 'tmsc' ),
				'edit_item'             => __( 'Edit Constituent', 'tmsc' ),
				'view_item'             => __( 'View Constituent', 'tmsc' ),
				'update_item'           => __( 'Update Constituent', 'tmsc' ),
				'add_new_item'          => __( 'Add New Constituent', 'tmsc' ),
				'new_item_name'         => __( 'New Constituent Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Constituents', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Constituents', 'tmsc' ),
				'menu_name'             => __( 'Constituents', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_constituent = new Tmsc_Taxonomy_Constituent();
