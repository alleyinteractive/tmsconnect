<?php

/**
 * Taxonomy for Exhibition Types.
 */
class Tmsc_Taxonomy_Exhibition_Types extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'exhibition-types';

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
				'name'                  => __( 'Exhibition Types', 'tmsc' ),
				'singular_name'         => __( 'Exhibition Types', 'tmsc' ),
				'search_items'          => __( 'Search Exhibition Types', 'tmsc' ),
				'popular_items'         => __( 'Popular Exhibition Types', 'tmsc' ),
				'all_items'             => __( 'All Exhibition Types', 'tmsc' ),
				'parent_item'           => __( 'Parent Exhibition Types', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Exhibition Types', 'tmsc' ),
				'edit_item'             => __( 'Edit Exhibition Types', 'tmsc' ),
				'view_item'             => __( 'View Exhibition Types', 'tmsc' ),
				'update_item'           => __( 'Update Exhibition Types', 'tmsc' ),
				'add_new_item'          => __( 'Add New Exhibition Types', 'tmsc' ),
				'new_item_name'         => __( 'New Exhibition Types Name', 'tmsc' ),
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

$taxonomy_exhibition_types = new Tmsc_Taxonomy_Exhibition_Types();
