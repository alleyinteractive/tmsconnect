<?php

/**
 * Taxonomy for References.
 */
class Tmsc_Taxonomy_Reference extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'reference';

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
				'name'                  => __( 'References', 'tmsc' ),
				'singular_name'         => __( 'Reference', 'tmsc' ),
				'search_items'          => __( 'Search References', 'tmsc' ),
				'popular_items'         => __( 'Popular References', 'tmsc' ),
				'all_items'             => __( 'All References', 'tmsc' ),
				'parent_item'           => __( 'Parent Reference', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Reference', 'tmsc' ),
				'edit_item'             => __( 'Edit Reference', 'tmsc' ),
				'view_item'             => __( 'View Reference', 'tmsc' ),
				'update_item'           => __( 'Update Reference', 'tmsc' ),
				'add_new_item'          => __( 'Add New Reference', 'tmsc' ),
				'new_item_name'         => __( 'New Reference Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove References', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used References', 'tmsc' ),
				'menu_name'             => __( 'References', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_reference = new Tmsc_Taxonomy_Reference();
