<?php

/**
 * Taxonomy for Catalogs.
 */
class Tmsc_Taxonomy_Catalog extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'catalog';

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
				'name'                  => __( 'Catalogs', 'tmsc' ),
				'singular_name'         => __( 'Catalog', 'tmsc' ),
				'search_items'          => __( 'Search Catalogs', 'tmsc' ),
				'popular_items'         => __( 'Popular Catalogs', 'tmsc' ),
				'all_items'             => __( 'All Catalogs', 'tmsc' ),
				'parent_item'           => __( 'Parent Catalog', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Catalog', 'tmsc' ),
				'edit_item'             => __( 'Edit Catalog', 'tmsc' ),
				'view_item'             => __( 'View Catalog', 'tmsc' ),
				'update_item'           => __( 'Update Catalog', 'tmsc' ),
				'add_new_item'          => __( 'Add New Catalog', 'tmsc' ),
				'new_item_name'         => __( 'New Catalog Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Catalogs', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Catalogs', 'tmsc' ),
				'menu_name'             => __( 'Catalogs', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_catalog = new Tmsc_Taxonomy_Catalog();
