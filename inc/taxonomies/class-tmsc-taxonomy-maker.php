<?php

/**
 * Taxonomy for Makers.
 */
class Tmsc_Taxonomy_Maker extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'maker';

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
				'name'                  => __( 'Makers', 'tmsc' ),
				'singular_name'         => __( 'Maker', 'tmsc' ),
				'search_items'          => __( 'Search Makers', 'tmsc' ),
				'popular_items'         => __( 'Popular Makers', 'tmsc' ),
				'all_items'             => __( 'All Makers', 'tmsc' ),
				'parent_item'           => __( 'Parent Maker', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Maker', 'tmsc' ),
				'edit_item'             => __( 'Edit Maker', 'tmsc' ),
				'view_item'             => __( 'View Maker', 'tmsc' ),
				'update_item'           => __( 'Update Maker', 'tmsc' ),
				'add_new_item'          => __( 'Add New Maker', 'tmsc' ),
				'new_item_name'         => __( 'New Maker Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Makers', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Makers', 'tmsc' ),
				'menu_name'             => __( 'Makers', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_maker = new Tmsc_Taxonomy_Maker();
