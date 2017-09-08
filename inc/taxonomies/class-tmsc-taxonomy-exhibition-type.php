<?php

/**
 * Taxonomy for Exhibition Types.
 */
class Tmsc_Taxonomy_Exhibition_Type extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'exhibition-type';

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
		$this->object_types = array( 'exhibition' );

		parent::__construct();
	}

	/**
	 * Creates the taxonomy.
	 */
	public function create_taxonomy() {
		register_taxonomy( $this->name, $this->object_types, array(
			'labels' => array(
				'name'                  => __( 'Exhibition Types', 'tmsc' ),
				'singular_name'         => __( 'Exhibition Type', 'tmsc' ),
				'search_items'          => __( 'Search Exhibition Types', 'tmsc' ),
				'popular_items'         => __( 'Popular Exhibition Types', 'tmsc' ),
				'all_items'             => __( 'All Exhibition Types', 'tmsc' ),
				'parent_item'           => __( 'Parent Exhibition Type', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Exhibition Type', 'tmsc' ),
				'edit_item'             => __( 'Edit Exhibition Type', 'tmsc' ),
				'view_item'             => __( 'View Exhibition Type', 'tmsc' ),
				'update_item'           => __( 'Update Exhibition Type', 'tmsc' ),
				'add_new_item'          => __( 'Add New Exhibition Type', 'tmsc' ),
				'new_item_name'         => __( 'New Exhibition Type Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Exhibition Types', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Exhibition Types', 'tmsc' ),
				'menu_name'             => __( 'Exhibition Types', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_exhibition_type = new Tmsc_Taxonomy_Exhibition_Type();
